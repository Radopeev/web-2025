<?php

// Assume Project class and database connection are properly included/autoloaded
// For example:
// require_once APP_ROOT . '/app/models/Project.php';

class LandingPageController {
    public static function showLandingPage(): void
    {
        $username = $_SESSION['username'] ?? 'Guest';

        $searchQuery = $_GET['search'] ?? '';

        $itemsPerPage = 5;
        $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        $totalProjects = Project::getTotalProjectCount($searchQuery);

        $totalPages = ceil($totalProjects / $itemsPerPage);

        if ($currentPage < 1) {
            $currentPage = 1;
        } elseif ($currentPage > $totalPages && $totalPages > 0) {
            $currentPage = $totalPages;
        } elseif ($totalPages === 0) {
            $currentPage = 1;
        }

        $offset = ($currentPage - 1) * $itemsPerPage;
        if ($offset < 0) {
            $offset = 0;
        }

        $projects = Project::getPaginatedProjects($searchQuery, $itemsPerPage, $offset);

        $pagination = [
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'totalProjects' => $totalProjects,
            'itemsPerPage' => $itemsPerPage,
            'searchQuery' => $searchQuery
        ];

        require_once APP_ROOT . '/app/views/landingPage.php';
    }
}