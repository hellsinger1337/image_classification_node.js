<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Classification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
            text-align: center;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        form {
            margin-bottom: 20px;
        }

        input[type="file"] {
            display: none;
        }

        label {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #007BFF;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        label:hover {
            background-color: #0056b3;
        }

        input[type="submit"] {
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #28a745;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #218838;
        }

        #result {
            margin-top: 20px;
        }

        #result h2 {
            font-size: 20px;
            color: #333;
        }

        .preview {
            margin-top: 20px;
        }

        .preview img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Upload an Image for Classification</h1>
        <form action="predict.php" method="post" enctype="multipart/form-data">
            <input type="file" name="image" id="fileInput" accept="image/*" required onchange="previewImage(event)">
            <label for="fileInput">Choose Image</label>
            <br>
            <input type="submit" value="Upload Image">
        </form>
        <div class="preview" id="preview">
            <p>No image chosen</p>
        </div>
        <div id="result">
            <?php
            if (isset($_GET['prediction'])) {
                echo "<h2>Prediction: " . htmlspecialchars($_GET['prediction']) . "</h2>";
            }
            ?>
        </div>
    </div>

    <script>
        function previewImage(event) {
            const preview = document.getElementById('preview');
            preview.innerHTML = ''; // Clear previous content

            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    preview.appendChild(img);
                }
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = '<p>No image chosen</p>';
            }
        }
    </script>
</body>
</html>