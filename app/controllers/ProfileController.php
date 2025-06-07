<?php

require_once APP_ROOT . 'app/models/User.php';
require_once APP_ROOT . 'app/models/Project.php';

class ProfileController
{
    public static function showProfile()
    {
        // Fetch user data from the database
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $user = User::findById($userId);

        if (!$user) {
            header('Location: /login');
            exit;
        }

        $username = $user['username'];
        $email = $user['email'];

        $projects = Project::getProjectsByUserId($userId);

        // Include the profile view
        include APP_ROOT . 'app/views/profile.php';
    }

    public static function updateProfile()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
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

        header('Location: /profile');
    }

    public static function deleteProject()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $projectId = $_POST['project_id'];

        global $conn;

        // Delete associated files first
        $stmt = $conn->prepare("DELETE FROM files WHERE project_id = ?");
        $stmt->bind_param("i", $projectId);
        $stmt->execute();
        $stmt->close();

        // Then delete the project
        $stmt = $conn->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->bind_param("i", $projectId);
        $stmt->execute();
        $stmt->close();

        header('Location: /profile');
    }

    public static function getProjectStatistics($userId)
    {
        global $conn;

        $stats = [
            'active' => 0,
            'in_progress' => 0,
            'completed' => 0
        ];

        $stmt = $conn->prepare("SELECT status, COUNT(*) as count FROM projects WHERE user_id = ? GROUP BY status");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $status = strtolower($row['status']);
            if (isset($stats[$status])) {
                $stats[$status] = $row['count'];
            }
        }

        $stmt->close();

        return $stats;
    }

    public static function uploadProfilePicture()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $userId = $_SESSION['user_id'];

        if (!empty($_FILES['profile_picture']['tmp_name'])) {
            $targetDir = 'public/uploads/profile_pictures/';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $fileName = uniqid() . '_' . basename($_FILES['profile_picture']['name']);
            $targetFile = $targetDir . $fileName;

            global $conn;

            // Fetch the current profile picture path
            $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $currentProfilePicture = $result->fetch_assoc()['profile_picture'];
            $stmt->close();

            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile)) {
                // Update the database with the new profile picture path
                $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
                $stmt->bind_param("si", $targetFile, $userId);
                $stmt->execute();
                $stmt->close();

                // Delete the old profile picture if it exists
                if (!empty($currentProfilePicture) && file_exists($currentProfilePicture)) {
                    unlink($currentProfilePicture);
                }

                header('Location: /profile');
                exit;
            } else {
                echo "Error uploading file.";
            }
        } else {
            echo "No file uploaded.";
        }
    }
}