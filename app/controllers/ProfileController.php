<?php

require_once APP_ROOT . 'app/models/User.php';
require_once APP_ROOT . 'app/models/Project.php';

class ProfileController
{
    public static function showProfile()
    {
        $userId = $_SESSION['user_id'];
        $user = User::findById($userId);

        if (!$user) {
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/login');
            exit;
        }

        $username = $user['username'];
        $email = $user['email'];

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $projectsPerPage = 5;
        $offset = ($page - 1) * $projectsPerPage;

        $totalProjects = Project::countProjectsByUserId($userId);
        $totalPages = ceil($totalProjects / $projectsPerPage);

        $projects = Project::getAllProjectsForUser($userId);

        include APP_ROOT . 'app/views/profile.php';
    }

    public static function updateProfile()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];

        global $conn;
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $username, $email, $userId);
        $stmt->execute();
        $stmt->close();

        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashedPassword, $userId);
            $stmt->execute();
            $stmt->close();
        }

        if (!empty($username)) {
            $_SESSION['username'] = $username;
        }
        if (!empty($_FILES['profile_picture']['tmp_name'])) {
            $targetDir = 'public/uploads/profile_pictures/';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            $fileName = uniqid() . '_' . basename($_FILES['profile_picture']['name']);
            $targetFile = $targetDir . $fileName;

            $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $currentProfilePicture = $result->fetch_assoc()['profile_picture'];
            $stmt->close();


            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile)) {
                $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
                $stmt->bind_param("si", $targetFile, $userId);
                $stmt->execute();
                $stmt->close();

                if (!empty($currentProfilePicture) && file_exists($currentProfilePicture)) {
                    unlink($currentProfilePicture);
                }
            } else {
                echo "Error uploading profile picture.";
            }
        }

        header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/profile');
    }

    public static function deleteProject()
    {
        $projectId = $_POST['project_id'];

        global $conn;

        $stmt = $conn->prepare("SELECT file_path FROM files WHERE project_id = ?");
        if (!$stmt) {
            error_log("Failed to prepare statement for fetching files.");
            return;
        }
        $stmt->bind_param("i", $projectId);
        $stmt->execute();
        $result = $stmt->get_result();

        $directoriesToDelete = [];

        while ($row = $result->fetch_assoc()) {
            $filePath = $row['file_path'];
            error_log("Deleting file: " . $filePath);

            if ($filePath && file_exists($filePath)) {
                unlink($filePath);
                error_log("File deleted: " . $filePath);

                $directoryPath = dirname($filePath);
                if (!in_array($directoryPath, $directoriesToDelete)) {
                    $directoriesToDelete[] = $directoryPath;
                }
            }
        }
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM files WHERE project_id = ?");
        if (!$stmt) {
            error_log("Failed to prepare statement for deleting files.");
            return;
        }
        $stmt->bind_param("i", $projectId);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM instruments WHERE project_id = ?");
        $stmt->bind_param("i", $projectId);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->bind_param("i", $projectId);
        $stmt->execute();
        $stmt->close();

        foreach ($directoriesToDelete as $directoryPath) {
            if (is_dir($directoryPath) && count(scandir($directoryPath)) == 2) {
                if (rmdir($directoryPath)) {
                    error_log("Directory deleted: " . $directoryPath);
                } else {
                    error_log("Failed to delete directory: " . $directoryPath);
                }
            }
        }

        header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/profile');
    }

    public static function uploadProfilePicture()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/login');
            exit;
        }

        $userId = $_SESSION['user_id'];

        if (!empty($_FILES['profile_picture']['tmp_name'])) {
            $targetDir = 'public/uploads/profile_pictures/';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $fileName = uniqid() . '_' . basename($_FILES['profile_picture']['name']);
            error_log("Uploading profile picture: " . $fileName);
            $targetFile = $targetDir . $fileName;

            global $conn;

            $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $currentProfilePicture = $result->fetch_assoc()['profile_picture'];
            $stmt->close();

            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile)) {
                $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
                $stmt->bind_param("si", $targetFile, $userId);
                $stmt->execute();
                $stmt->close();

                error_log("Profile picture uploaded successfully: " . $targetFile);
                error_log("Current profile picture: " . $currentProfilePicture);

                if (!empty($currentProfilePicture) && file_exists($currentProfilePicture)) {
                    error_log("Current profile picture: " . $currentProfilePicture);
                    unlink($currentProfilePicture);
                }

                header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/profile');
                exit;
            } else {
                echo "Error uploading file.";
            }
        } else {
            echo "No file uploaded.";
        }
    }
}