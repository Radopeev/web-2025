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

    public static function create($username, $email, $passwordHash,$role) {
        global $conn;
        $stmt = $conn->prepare("INSERT INTO users (username, email, password,role) VALUES (?, ?, ?,?)");
        $stmt->bind_param("ssss", $username, $email, $passwordHash,$role);
        return $stmt->execute();
    }
}
