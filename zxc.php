<?php
set_time_limit(10000); 
$prefix = 'school73';
$save_dir = 'downloaded_images';
$classes = ['banner', 'document', 'humans_photo', 'logo', 'object_photo', 'other', 'portret_photo'];
$delay = 10;
try {
    $db_path = 'C:\\projects\\image-classification-demo\\database.db3';
    $db = new PDO('sqlite:' . $db_path);
    
    $db->exec("CREATE TABLE IF NOT EXISTS classified_images (
        id INTEGER PRIMARY KEY,
        id_resource TEXT,
        classification TEXT
    )");


    function fetch_images($prefix, $page = 0) {
        $url = "https://$prefix.edu.yar.ru/admin/?module=images&action=getlist&mode=ajax&page=$page";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("Failed to fetch images: $error");
        }
        curl_close($ch);
        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Failed to decode JSON response: " . json_last_error_msg());
        }
        return $data;
    }

    function download_image($image_url, $save_path) {
        $fp = fopen($save_path, 'wb');
        if (!$fp) {
            throw new Exception("Failed to open file: $save_path");
        }
        $ch = curl_init($image_url);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Следовать перенаправлениям
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10); // Максимальное количество перенаправлений
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
        }
        curl_close($ch);
        fclose($fp);
        if (isset($error_msg)) {
            throw new Exception("cURL error: $error_msg\nResponse: $response\nHTTP Code: $http_code");
        }
        return $http_code == 200;
    }

    if (!file_exists($save_dir)) {
        mkdir($save_dir, 0777, true);
    }

    try {
        $data = fetch_images($prefix, 0);
    } catch (Exception $e) {
        echo $e->getMessage() . "\n";
        exit();
    }

    if (!isset($data['count']) || !isset($data['data'])) {
        echo "Unexpected response structure\n";
        exit();
    }

    $total_count = $data['count'];
    $total_pages = ceil($total_count / 50);

    for ($page = 0; $page < $total_pages; $page++) {
        echo "Fetching images from page $page for prefix $prefix\n";
        try {
            $data = fetch_images($prefix, $page);
        } catch (Exception $e) {
            echo $e->getMessage() . "\n";
            continue;
        }

        foreach ($data['data'] as $image) {
            if (!isset($image['src'])) {
                echo "Unexpected image data structure\n";
                continue;
            }
            $image_url = $image['src'];
            $id_resource = basename($image_url, '.' . pathinfo($image_url, PATHINFO_EXTENSION)); 

            $extension = pathinfo($image_url, PATHINFO_EXTENSION);
            $save_path = "$save_dir/$id_resource.$extension";

            try {
                if (download_image($image_url, $save_path)) {
                } else {
                    echo "Failed to download $image_url (HTTP code not 200)\n";
                    continue;
                }
            } catch (Exception $e) {
                echo $e->getMessage() . "\n";
                continue;
            }

            $classification_index = classify_image($save_path);
            if ($classification_index === null) {
                echo "Classification failed for $image_url\n";
                continue;
            }
            $classification_index = intval($classification_index); 

            if (0>$classification_index  or $classification_index>6) {
                echo "Invalid classification index $classification_index for $image_url\n";
                $classification = 'Unknown';
            } else {
                $classification = $classes[$classification_index];
            }

            $stmt = $db->prepare("INSERT INTO classified_images (id_resource, classification) VALUES (:id_resource, :classification)");
            $stmt->bindParam(':id_resource', $id_resource);        
            $stmt->bindParam(':classification', $classification);
            $stmt->execute();

            echo "Classified $image_url as $classification\n".PHP_EOL;
        }

        echo "Waiting for $delay seconds before fetching next page...\n";
        sleep($delay);
    }

    echo "Classification completed.\n";
} catch (PDOException $e) {
    echo "Could not connect to the database: " . $e->getMessage();
}


function classify_image($image_path) {
    $command = escapeshellcmd("node predict.js " . escapeshellarg($image_path));
    $output = shell_exec($command);
    if($output===null)
        return false;
    $output = trim($output);
    $class_number = number_format($output);
    return $class_number;
}