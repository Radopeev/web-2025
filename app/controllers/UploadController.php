<?php
require_once 'config/database.php';

class UploadController {
    public function handleUpload() {
        global $conn;
        session_start();

        $user_id = $_SESSION['user_id'];

        $title = $_POST['title'];
        $description = $_POST['description'];

        $configPath = '';
        if (!empty($_FILES['config_file']['tmp_name'])) {
            $originalConfigName = basename($_FILES['source_files']['name'][$index]);
            $uniqueConfigName = uniqid() . '_' . $originalConfiName;
            $configPath = 'public/uploads/configs/' . $originalConfiName;
            move_uploaded_file($_FILES['config_file']['tmp_name'], $configPath);
        }

        $stmt = $conn->prepare("INSERT INTO projects (user_id, title, description, config_file) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $title, $description, $configPath);
        $stmt->execute();
        $project_id = $stmt->insert_id;
        $stmt->close();

        foreach ($_FILES['source_files']['tmp_name'] as $index => $tmpName) {
            if ($tmpName) {
                $originalName = basename($_FILES['source_files']['name'][$index]);
                $uniqueName = uniqid() . '_' . basename($_FILES['source_files']['name'][$index]);
                $filePath = 'public/uploads/sources/' . $uniqueName;
                move_uploaded_file($tmpName, $filePath);

                // Insert both the saved file path and the original file name
                $stmt = $conn->prepare("INSERT INTO files (project_id, file_path, original_name) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $project_id, $filePath, $originalName);
                $stmt->execute();
                $stmt->close();
            }
        }

        // Process directory files and preserve directory structure
        if (!empty($_FILES['directory_files']['tmp_name'])) {
            $uploadDir = 'public/uploads/sources/';
            $uniqueFolder = uniqid() . '_folder/'; // One folder per upload
            foreach ($_FILES['directory_files']['tmp_name'] as $index => $tmpName) {
                if ($tmpName) {
                    // The name may include subdirectories, e.g., "subdir/file.txt"
                    $relativePath = $_FILES['directory_files']['name'][$index];
                    $destinationPath = $uploadDir . $uniqueFolder . uniqid() . '_' . $relativePath;

                    // Create the directory if it doesn't exist
                    $destinationDir = dirname($destinationPath);
                    if (!is_dir($destinationDir)) {
                        mkdir($destinationDir, 0777, true);
                    }

                    // Move the uploaded file
                    move_uploaded_file($tmpName, $destinationPath);

                    // Optionally, insert into DB
                    $stmt = $conn->prepare("INSERT INTO files (project_id, file_path, original_name) VALUES (?, ?, ?)");
                    $stmt->bind_param("iss", $project_id, $destinationPath, basename($relativePath));
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }

        $names = isset($_POST['instrument_name']) ? $_POST['instrument_name'] : [];
        $types = isset($_POST['instrument_type']) ? $_POST['instrument_type'] : [];
        $descriptions = isset($_POST['instrument_description']) ? $_POST['instrument_description'] : [];
        $access_links = isset($_POST['instrument_access']) ? $_POST['instrument_access'] : [];

        for ($i = 0; $i < count($names); $i++) {
            if (trim($names[$i]) !== '') {
                $stmt = $conn->prepare("INSERT INTO instruments (project_id, name, type, description, access_link) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("issss", $project_id, $names[$i], $types[$i], $descriptions[$i], $access_links[$i]);
                $stmt->execute();
                $stmt->close();
            }
        }

        header("Location: /landingPage");
    }
}
