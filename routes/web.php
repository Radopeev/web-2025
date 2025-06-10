<?php
// const APP_ROOT = '../';
// require_once APP_ROOT . 'config/app.php'; // <-- Add this

require_once APP_ROOT . 'app/controllers/AuthController.php';
require_once APP_ROOT . 'app/controllers/LandingPageController.php';
require_once APP_ROOT . 'app/controllers/UploadController.php';
require_once APP_ROOT . 'app/controllers/ProjectController.php';
require_once APP_ROOT . 'app/controllers/ProfileController.php';

function require_auth()
{
    if (!isset($_SESSION)) {
        session_start();
    }
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header('Location: ' . BASE_PATH . '/login');
        exit;
    }
}

// Get request path
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove BASE_PATH from request for routing
if (BASE_PATH && strpos($request, BASE_PATH) === 0) {
    $request = substr($request, strlen(BASE_PATH));
}
if ($request === false || $request === '') $request = '/';

// Normalize double slashes and trailing slashes
$request = '/' . ltrim($request, '/');

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
        LandingPageController::showLandingPage();
        break;
    case '/upload':
        require_auth();

        $upload = new UploadController();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $upload->handleUpload();
        } elseif ($request === '/upload') {
            require_once APP_ROOT . 'app/views/upload.php';
        }
        break;
    case '/project/details':
        require_auth();
        ProjectController::showProjectDetails();
        break;
    case '/project/edit':
        require_auth();
        ProjectController::editProject();
        break;
    case '/profile':
        require_auth();
        ProfileController::showProfile();
        break;
    case '/update_profile':
        require_auth();
        ProfileController::updateProfile();
        break;
    case '/delete_project':
        require_auth();
        ProfileController::deleteProject();
        break;
    case '/upload_profile_picture':
        require_auth();
        ProfileController::uploadProfilePicture();
        break;
    case '/project/star':
        require_auth();
        ProjectController::starProject();
        break;
    case '/project/unstar':
        require_auth();
        ProjectController::unstarProject();
        break;
    case '/favorites':
        require_auth();
        ProjectController::showFavorites();
        break;
    default:
        http_response_code(404);
        echo "Page not found";
        break;
}
