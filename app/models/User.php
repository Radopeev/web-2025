<?php
require_once __DIR__ . '/../../config/database.php';

class User {
    public static function findByEmail($email) {
        global $conn;
        
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_assoc();
    }

    public static function create($username, $email, $passwordHash) {
        global $conn;
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $passwordHash);
        return $stmt->execute();
    }

    public static function findById($id) {
        global $conn;

        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public static function updateProfilePicture($userId, $filePath) {
        global $conn;
        $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
        $stmt->bind_param("si", $filePath, $userId);
        $stmt->execute();
        $stmt->close();
    }
}
