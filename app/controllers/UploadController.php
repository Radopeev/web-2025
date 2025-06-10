<?php
require_once 'config/database.php';
$PATHS = require __DIR__ . '/../../config/paths.php';

class UploadController
{
    public static function handleUpload()
    {
        global $conn, $PATHS;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $user_id = $_SESSION['user_id'];

        $title = $_POST['title'];
        $description = $_POST['description'];

        // Ensure upload directories exist
        if (!is_dir($PATHS['upload_configs_dir'])) {
            mkdir($PATHS['upload_configs_dir'], 0777, true);
        }
        if (!is_dir($PATHS['upload_sources_dir'])) {
            mkdir($PATHS['upload_sources_dir'], 0777, true);
        }

        $configPath = '';
        if (!empty($_FILES['config_file']['tmp_name'])) {
            $originalConfigName = basename($_FILES['config_file']['name']);
            $uniqueConfigName = uniqid() . '_' . $originalConfigName;
            $configPath = $PATHS['upload_configs_dir'] . $uniqueConfigName;
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
                $filePath = $PATHS['upload_sources_dir'] . $uniqueName;
                move_uploaded_file($tmpName, $filePath);

                $dbFilePath = $filePath;
                $dbOriginalName = $originalName;
                $stmt = $conn->prepare("INSERT INTO files (project_id, file_path, original_name) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $project_id, $dbFilePath, $dbOriginalName);
                $stmt->execute();
                $stmt->close();
            }
        }

        if (!empty($_FILES['directory_files']['tmp_name'])) {
            foreach ($_FILES['directory_files']['tmp_name'] as $index => $tmpName) {
                if ($tmpName) {
                    $originalName = basename($_FILES['directory_files']['name'][$index]);
                    $uniqueName = uniqid() . '_' . $originalName;
                    $destinationPath = $PATHS['upload_sources_dir'] . $uniqueName;
                    move_uploaded_file($tmpName, $destinationPath);

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
