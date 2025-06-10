<?php

require_once APP_ROOT . 'app/models/Project.php';
 
class LandingPageController {  
    public static function showLandingPage(): void
    {
        $username = $_SESSION['username'] ?? 'Guest';
        $searchQuery = $_GET['search'] ?? ''; 
  
        if (!empty($searchQuery)) {   
            $projects = Project::searchProjects($searchQuery);  
        } else {   
            $projects = Project::getAllProjects(); 
        } 
  
        require_once APP_ROOT . '/app/views/landingPage.php'; 
    }  
} 