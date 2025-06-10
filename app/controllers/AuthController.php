<?php
require_once __DIR__ . '/../models/User.php';

session_start();

class AuthController
{
    private static function loginUser($user)
    {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['logged_in'] = true;
        header('Location: /landingPage');
        exit;
    }

    public static function login()
    {
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
            header('Location: /landingPage');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];

            $user = User::findByEmail($email);
            if ($user && password_verify($password, $user['password'])) {
                self::loginUser($user);
            } else {
                $error = "Invalid credentials.";
            }
        }

        include __DIR__ . '/../views/auth/login.php';
    }

    public static function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $email = $_POST['email'];
            $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

            if (User::create($username, $email, $password)) {
                $user = User::findByEmail($email);
                if ($user) {
                    self::loginUser($user);
                }
            } else {
                $error = "Registration failed: Email is already in use.";
            }
        }

        include __DIR__ . '/../views/auth/register.php';
    }

    public static function logout()
    {
        session_destroy();
        header('Location: /login');
    }
}
