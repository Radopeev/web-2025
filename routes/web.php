<?php
require_once __DIR__ . '/../app/controllers/AuthController.php';

require_once 'controllers/UploadController.php';

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
    
    case '/upload':
        $upload = new UploadController();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $upload->handleUpload();
        } elseif ($_SERVER['REQUEST_URI'] === '/upload') {
            require 'views/upload.php';
        }
    default:
        http_response_code(404);
        echo "Page not found";
        break;
}
