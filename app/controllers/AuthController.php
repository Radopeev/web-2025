<?php
require_once __DIR__ . '/../models/User.php';

session_start();

class AuthController {
    public static function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];

            $user = User::findByEmail($email);
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header('Location: /home');
                exit;
            } else {
                $error = "Invalid credentials.";
            }
        }
        
        include __DIR__ . '/../views/auth/login.php';
    }

    public static function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $email = $_POST['email'];
            $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

            if (User::create($username, $email, $password)) {
                header('Location: /login');
                exit;
            } else {
                $error = "Registration failed.";
            }
        }
        
        include __DIR__ . '/../views/auth/register.php';
    }

    public static function logout() {
        session_destroy();
        header('Location: /login');
    }
}
