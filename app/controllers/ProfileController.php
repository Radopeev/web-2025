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
        $created_at = $user['created_at'];

        // Fetch user projects, files, and instruments
        $projects = Project::getProjectsByUserId($userId);
        $files = [];
        $instruments = [];

        foreach ($projects as $project) {
            $projectFiles = Project::getFilesByProjectId($project['id']);
            $projectInstruments = Project::getInstrumentsByProjectId($project['id']);

            $files = array_merge($files, $projectFiles);
            $instruments = array_merge($instruments, $projectInstruments);
        }

        // Include the profile view
        include APP_ROOT . 'app/views/profile.php';
    }
}