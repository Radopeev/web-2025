<?php
require_once 'config/database.php';

class UploadController {
    public function handleUpload() {
        global $conn;
        session_start();

        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            echo "Error: User not logged in or invalid user ID.";
            exit;
        }

        $user_id = $_SESSION['user_id'];

        // Check if user exists in the database
        $stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo "Error: User does not exist.";
            exit;
        }
        $stmt->close();

        $title = $_POST['title'];
        $description = $_POST['description'];

        // Ensure directories exist
        $projectDir = 'public/uploads/';
        $configsDir = $projectDir . '/configs';
        $sourcesDir = $projectDir . '/sources';

        if (!is_dir($projectDir)) {
            mkdir($projectDir, 0777, true);
        }

        if (!is_dir($configsDir)) {
            mkdir($configsDir, 0777, true);
        }

        if (!is_dir($sourcesDir)) {
            mkdir($sourcesDir, 0777, true);
        }

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
            foreach ($_FILES['directory_files']['tmp_name'] as $index => $tmpName) {
                if ($tmpName) {
                    $originalName = $_FILES['directory_files']['name'][$index];
                    $relativePath = dirname($originalName); // Get the relative directory path
                    if ($relativePath === '.' || $relativePath === '') {
                        $relativePath = ''; // Use empty string for root-level files
                    }
                    $targetDir = $sourcesDir . ($relativePath ? '/' . $relativePath : '');

                    // Create the directory structure if it doesn't exist
                    if (!is_dir($targetDir)) {
                        mkdir($targetDir, 0777, true);
                    }

                    $filePath = $targetDir . '/' . basename($originalName);
                    if (move_uploaded_file($tmpName, $filePath)) {
                        // Insert file details into the database with relative path
                        $relativeFilePath = ($relativePath ? $relativePath . '/' : '') . basename($originalName);
                        $stmt = $conn->prepare("INSERT INTO files (project_id, file_path, original_name) VALUES (?, ?, ?)");
                        $stmt->bind_param("iss", $project_id, $relativeFilePath, $originalName);
                        $stmt->execute();
                        $stmt->close();
                    } else {
                        error_log("Failed to move file: $tmpName to $filePath");
                    }
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
