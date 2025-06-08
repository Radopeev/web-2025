<?php
require_once 'config/database.php';

class UploadController
{
    public static function handleUpload()
    {
        global $conn;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $user_id = $_SESSION['user_id'];

        $title = $_POST['title'];
        $description = $_POST['description'];

        $configPath = '';
        if (!empty($_FILES['config_file']['tmp_name'])) {
            $originalConfigName = basename($_FILES['config_file']['name']);
            $uniqueConfigName = uniqid() . '_' . $originalConfigName;
            $configPath = 'public/uploads/configs/' . $uniqueConfigName;
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
                $uniqueName = uniqid() . '_' . $originalName;
                $filePath = 'public/uploads/sources/' . $uniqueName;
                move_uploaded_file($tmpName, $filePath);

                // Assign to variables before bind_param  
                $dbFilePath = $filePath;
                $dbOriginalName = $originalName;
                $stmt = $conn->prepare("INSERT INTO files (project_id, file_path, original_name) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $project_id, $dbFilePath, $dbOriginalName);
                $stmt->execute();
                $stmt->close();
            }
        }

        // Process directory files and flatten structure
        if (!empty($_FILES['directory_files']['tmp_name'])) {
            $uploadDir = 'public/uploads/sources/';
            foreach ($_FILES['directory_files']['tmp_name'] as $index => $tmpName) {
                if ($tmpName) {
                    $originalName = basename($_FILES['directory_files']['name'][$index]);
                    $uniqueName = uniqid() . '_' . $originalName;
                    $destinationPath = $uploadDir . $uniqueName;
                    move_uploaded_file($tmpName, $destinationPath);

                    // Assign to variables before bind_param  
                    $dbDestinationPath = $destinationPath;
                    $dbOriginalName = $originalName;
                    $stmt = $conn->prepare("INSERT INTO files (project_id, file_path, original_name) VALUES (?, ?, ?)");
                    $stmt->bind_param("iss", $project_id, $dbDestinationPath, $dbOriginalName);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }

        $names = isset($_POST['instrument_name']) ? $_POST['instrument_name'] : [];
        $types = isset($_POST['instrument_type']) ? $_POST['instrument_type'] : [];
        $descriptions = isset($_POST['instrument_description']) ? $_POST['instrument_description'] : [];
        $access_links = isset($_POST['instrument_access']) ? $_POST['instrument_access'] : [];

        $count = count($names);
        for ($i = 0; $i < $count; $i++) {
            $name = $names[$i] ?? '';
            $type = $types[$i] ?? '';
            $desc = $descriptions[$i] ?? '';
            $access = $access_links[$i] ?? '';
            if (trim($name) !== '') {
                $stmt = $conn->prepare("INSERT INTO instruments (project_id, name, type, description, access_link) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("issss", $project_id, $name, $type, $desc, $access);
                $stmt->execute();
                $stmt->close();
            }
        }

        header("Location: /landingPage");
    }
}
