<?php
require_once APP_ROOT . 'app/models/Project.php';

if (!isset($_GET['id'])) {
    echo "Project ID is missing.";
    exit;
}

$projectId = (int)$_GET['id'];
$project = Project::getProjectById($projectId);
$files = Project::getFilesByProjectId($projectId);
$instruments = Project::getInstrumentsByProjectId($projectId);

if (!$project) {
    echo "Project not found.";
    exit;
}
?>

<?php include __DIR__ . '/partials/header.php'; ?>

<?php include __DIR__ . '/partials/footer.php'; ?>