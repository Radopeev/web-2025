<?php
const APP_ROOT = __DIR__ . '/../';

require_once APP_ROOT . 'app/controllers/AuthController.php';
require_once APP_ROOT . 'app/controllers/LandingPageController.php';
require_once APP_ROOT . 'app/controllers/UploadController.php';

$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

switch ($request) {
    case '/':
    case '/login':
        AuthController::login();
        break;
    case '/register':
        AuthController::register();
        break;
    case '/logout':
        AuthController::logout();
        break;
    case '/landingPage':
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            header('Location: /login');
            exit;
        }
        LandingPageController::showLandingPage();
        break;
    case '/upload':
        $upload = new UploadController();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $upload->handleUpload();
        } elseif ($_SERVER['REQUEST_URI'] === '/upload') {
            require 'views/upload.php';
        }
        break;
    default:
        http_response_code(404);
        echo "Page not found";
        break;
}
