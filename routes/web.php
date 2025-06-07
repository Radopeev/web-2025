<?php
const APP_ROOT = __DIR__ . '/../';

require_once APP_ROOT . 'app/controllers/AuthController.php';
require_once APP_ROOT . 'app/controllers/LandingPageController.php';
require_once APP_ROOT . 'app/controllers/UploadController.php';
require_once APP_ROOT . 'app/controllers/ProfileController.php';

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
        // A: We should allow access to the landing page for all users, including guests.
        // ... I recommend forcing authentication only for details pages or upload page.

        // if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        //     header('Location: /login');
        //     exit;
        // }

        LandingPageController::showLandingPage();
        break;
    case '/upload':
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            header('Location: /login');
            exit;
        }
        
        $upload = new UploadController();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $upload->handleUpload();
        } elseif ($_SERVER['REQUEST_URI'] === '/upload') {
            require_once APP_ROOT . 'app/views/upload.php';
        }
        break;
    case '/profile':
        ProfileController::showProfile();
        break;
    case '/update_profile':
        ProfileController::updateProfile();
        break;
    case '/delete_project':
        ProfileController::deleteProject();
        break;
    case '/upload_profile_picture':
        ProfileController::uploadProfilePicture();
        break;
    default:
        http_response_code(404);
        echo "Page not found";
        break;
}
