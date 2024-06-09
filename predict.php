<?php
if ($_FILES['image']['error'] != UPLOAD_ERR_OK) {
    die("Upload failed with error code " . $_FILES['image']['error']);
}

$imageData = file_get_contents($_FILES['image']['tmp_name']);

$temp_file = tempnam(sys_get_temp_dir(), 'img_');
file_put_contents($temp_file, $imageData);

$command = escapeshellcmd("node predict.js " . escapeshellarg($temp_file));
$output = shell_exec($command);

unlink($temp_file);

if ($output === null) {
    die("Command execution failed. Please check your Node.js script and command.");
}

$classes = ['banner', 'document', 'humans_photo', 'logo', 'object_photo', 'other', 'portret_photo'];

$predicted_class_index = intval($output);
file_put_contents("debug.log", "predicted_class_index: " . $predicted_class_index . "\n", FILE_APPEND);

if ($predicted_class_index >= 0 && $predicted_class_index < count($classes)) {
    $predicted_class = $classes[$predicted_class_index];
} else {
    $predicted_class = "Unknown";
}

header("Location: index.php?prediction=" . urlencode($predicted_class));
exit();