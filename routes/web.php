<?php
require_once __DIR__ . '/../app/controllers/AuthController.php';

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
    default:
        http_response_code(404);
        echo "Page not found";
        break;
}
