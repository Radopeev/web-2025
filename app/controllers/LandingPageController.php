<?php  
require_once APP_ROOT . 'app/models/Project.php'; // Ensure Project model is included  
 
class LandingPageController {  
    public static function showLandingPage(): void
    {
        // No need to instantiate Project anymore, call static methods directly 
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