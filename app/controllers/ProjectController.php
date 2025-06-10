<?php

require_once __DIR__ . '/../models/Project.php';
require_once __DIR__ . '/../../config/global.php';
global $PATHS;

define('MAX_INLINE_FILE_SIZE', 1024 * 500);

class ProjectController
{
    private static string $errorMessage = '';
    private static string $successMessage = '';

    private static ?array $project = null;
    private static array $projectFiles = [];
    private static array $projectInstruments = [];


    public static function showProjectDetails(): void
    {
        error_log("--- Starting showProjectDetails controller ---");

        $projectId = $_GET['id'] ?? null;

        if (!self::validateUploadDirectories()) {
            self::setErrorMessage("Server configuration error: Upload directories are invalid or missing.");
            self::renderDetailView();
            return;
        }

        if (!$projectId || !is_numeric($projectId)) {
            self::setErrorMessage("Invalid project ID provided. Please specify a valid project ID.");
        } else {
            $projectId = (int) $projectId;
            try {
                self::$project = Project::getById($projectId);

                if (self::$project) {
                    self::$projectFiles = self::processProjectSourceFiles(self::$project['id']);

                    if (!empty(self::$project['config_file'])) {
                        self::$project = self::processProjectConfigFile(self::$project);
                    }

                    self::$projectInstruments = Project::getProjectInstruments(self::$project['id']);

                } else {
                    self::setErrorMessage("Project with ID '$projectId' not found.");
                }
            } catch (mysqli_sql_exception $e) {
                self::setErrorMessage("A database error occurred. Please try again later.");
                error_log("Database error in ProjectController::showProjectDetails - Project ID: $projectId: " . $e->getMessage());
            } catch (Exception $e) {
                self::setErrorMessage("An unexpected error occurred. Please contact support.");
                error_log("General error in ProjectController::showProjectDetails - Project ID: $projectId: " . $e->getMessage());
            }
        }

        self::renderDetailView();
        error_log("--- Ending showProjectDetails controller ---");
    }

    public static function editProject(): void
    {
        global $conn;

        error_log("--- Starting editProject controller ---");

        $projectId = $_GET['id'] ?? null;

        if (!self::isValidProjectId($projectId)) {
            self::setErrorMessage("Invalid project ID provided for editing.");
            self::logError("Initial validation failed: Invalid project ID: " . ($projectId ?? 'null'));
            self::renderEditView();
            return;
        }
        $projectId = (int) $projectId;
        error_log("Project ID: " . $projectId);

        // Owner check: Only allow project owner to edit
        $project = Project::getById($projectId);
        if (!$project || !isset($_SESSION['user_id']) || $_SESSION['user_id'] != $project['user_id']) {
            header('Location: /landingPage');
            exit;
        }

        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                error_log("Handling POST request for Project ID: " . $projectId);
                self::handlePostRequest($projectId, $conn);
            }

            self::loadProjectDataForEditView($projectId);

        } catch (\mysqli_sql_exception $e) {
            self::setErrorMessage("A database error occurred. Please try again later.");
            self::logError("Database error in ProjectController::editProject - ID: $projectId: " . $e->getMessage());
            if ($conn instanceof mysqli) {
                $conn->rollback();
            }
        } catch (\Exception $e) {
            self::setErrorMessage("An unexpected error occurred. Please contact support.");
            self::logError("General error in ProjectController::editProject - ID: $projectId: " . $e->getMessage());
            if ($conn instanceof mysqli) {
                $conn->rollback();
            }
        }

        self::renderEditView();
        error_log("--- Ending editProject controller ---");
    }

    public static function starProject() {
        $userId = $_SESSION['user_id'];
        $projectId = $_POST['project_id'] ?? null;
        if ($projectId) {
            Project::addFavorite($userId, $projectId);
        }
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    public static function unstarProject() {
        $userId = $_SESSION['user_id'];
        $projectId = $_POST['project_id'] ?? null;
        if ($projectId) {
            Project::removeFavorite($userId, $projectId);
        }
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    public static function showFavorites() {
        $userId = $_SESSION['user_id'];
        $projects = Project::getFavoritesByUser($userId);
        include __DIR__ . '/../views/favorites.php';
    }

    private static function validateUploadDirectories(): bool
    {
        global $PATHS;
        if (!$PATHS['upload_configs_dir'] || !is_dir($PATHS['upload_configs_dir'])) {
            error_log("Error: Configuration upload path is invalid or not a directory: " . $PATHS['upload_configs_dir']);
            return false;
        }
        if (!$PATHS['upload_sources_dir'] || !is_dir($PATHS['upload_sources_dir'])) {
            error_log("Error: Source file upload path is invalid or not a directory: " . $PATHS['upload_sources_dir']);
            return false;
        }
        return true;
    }

    private static function getFileMimeType(string $filePath, string $originalFileName = ''): string
    {
        if (function_exists('mime_content_type')) {
            return mime_content_type($filePath);
        }

        $extension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
        $fileNameLower = strtolower(basename($originalFileName));

        if (empty($extension)) {
            switch ($fileNameLower) {
                case 'dockerfile': case 'license': case 'readme': return 'text/plain';
            }
        } else {
            switch ($extension) {
                case 'txt': case 'log': case 'md': case 'csv': case 'json': case 'xml':
                case 'html': case 'css': case 'js': case 'php': case 'sh': case 'py':
                case 'yaml': case 'yml': case 'ini': case 'conf': case 'env':
                case 'gitignore': case 'editorconfig': return 'text/plain';
                case 'jpg': case 'jpeg': return 'image/jpeg';
                case 'png': return 'image/png';
                case 'gif': return 'image/gif';
                case 'svg': return 'image/svg+xml';
                case 'pdf': return 'application/pdf';
            }
        }
        return 'application/octet-stream';
    }

    private static function getTextMimeTypesForInlineDisplay(): array
    {
        return [
            'application/json', 'application/xml', 'application/javascript', 'application/x-php',
            'image/svg+xml', 'application/x-python', 'text/x-python', 'application/x-yaml',
            'text/yaml', 'text/x-yaml', 'application/x-dockerfile', 'text/plain', 'text/x-sh',
        ];
    }

    private static function processFileForInlineDisplay(array $fileData, string $uploadAbsPath, string $fileNameKey, bool $isConfig = false): array
    {
        $processedFile = $fileData;
        $processedFile['is_viewable_inline'] = false;
        $processedFile['view_type'] = '';
        $processedFile['viewable_content'] = null;

        $fileNameOnDisk = basename($processedFile[$fileNameKey] ?? '');
        $fullFilePath = rtrim($uploadAbsPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileNameOnDisk;
        $resolvedFullPath = realpath($fullFilePath);

        if ($resolvedFullPath && str_starts_with($resolvedFullPath, $uploadAbsPath) && file_exists($resolvedFullPath) && is_readable($resolvedFullPath)) {
            $mimeType = self::getFileMimeType($resolvedFullPath, $isConfig ? $fileNameOnDisk : ($processedFile['original_name'] ?? ''));
            $textMimeTypesForInline = self::getTextMimeTypesForInlineDisplay();

            if (str_starts_with($mimeType, 'text/') || in_array($mimeType, $textMimeTypesForInline)) {
                if (filesize($resolvedFullPath) < MAX_INLINE_FILE_SIZE) {
                    $processedFile['viewable_content'] = file_get_contents($resolvedFullPath);
                    $processedFile['is_viewable_inline'] = true;
                    $processedFile['view_type'] = 'text';
                } else {
                    error_log("File " . ($isConfig ? $fileNameOnDisk : ($processedFile['original_name'] ?? 'N/A')) . " too large for inline text display.");
                }
            } else if (str_starts_with($mimeType, 'image/')) {
                $processedFile['is_viewable_inline'] = true;
                $processedFile['view_type'] = 'image';
            } else if ($mimeType === 'application/pdf') {
                $processedFile['is_viewable_inline'] = true;
                $processedFile['view_type'] = 'pdf';
            }
        } else {
            error_log("File security check failed, or file not found/readable: " . $fullFilePath . " (Original: " . ($isConfig ? $fileNameOnDisk : ($processedFile['original_name'] ?? 'N/A')) . ")");
        }

        return $processedFile;
    }

    private static function processProjectSourceFiles(int $projectId): array
    {
        global $PATHS;
        $processedFiles = [];
        $rawProjectFiles = Project::getProjectSourceFiles($projectId);
        foreach ($rawProjectFiles as $file) {
            $processedFiles[] = self::processFileForInlineDisplay($file, $PATHS['upload_sources_dir'], 'file_path', false);
        }
        return $processedFiles;
    }

    private static function processProjectConfigFile(array $project): array
    {
        global $PATHS;
        $processedConfig = self::processFileForInlineDisplay(
            $project, $PATHS['upload_configs_dir'], 'config_file', true
        );
        $project['config_is_viewable_inline'] = $processedConfig['is_viewable_inline'];
        $project['config_view_type'] = $processedConfig['view_type'];
        $project['config_viewable_content'] = $processedConfig['viewable_content'];
        return $project;
    }

    private static function isValidProjectId(?string $projectId): bool
    {
        return $projectId && is_numeric($projectId);
    }

    private static function handlePostRequest(int $projectId, mysqli $conn): void
    {
        error_log("FILES array: " . print_r($_FILES, true));

        $newTitle = trim($_POST['title'] ?? '');
        $newDescription = trim($_POST['description'] ?? '');

        if (empty($newTitle)) {
            self::setErrorMessage("Project title cannot be empty.");
            self::logError("Validation failed: Title empty.");
            return;
        }

        $conn->begin_transaction();
        try {
            self::updateProjectDetails($projectId, $newTitle, $newDescription);
            self::handleInstruments($projectId);
            self::handleConfigFile($projectId);
            self::handleSourceFiles($projectId);

            $conn->commit();
            self::setSuccessMessage("Project updated successfully!");
            self::logInfo("Project update SUCCESS for ID: " . $projectId);
            header("Location: /project/details?id=" . $projectId . "&success=1");
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            self::setErrorMessage("An error occurred during the update: " . $e->getMessage());
            self::logError("Transaction failed for Project ID: " . $projectId . " - " . $e->getMessage());
        }
    }

    private static function updateProjectDetails(int $projectId, string $newTitle, string $newDescription): void
    {
        $updateData = ['title' => $newTitle, 'description' => $newDescription];
        error_log("Updating basic details for Project ID: " . $projectId . " with: " . implode(', ', $updateData));
        if (!Project::update($projectId, $updateData)) {
            self::logError("Failed to update basic project details for ID: " . $projectId);
            throw new Exception("Failed to update project basic details. No changes were made or an error occurred.");
        }
        self::logInfo("Basic project details updated for ID: " . $projectId);
    }

    private static function handleInstruments(int $projectId): void
    {
        $submittedInstrumentIds = $_POST['instrument_id'] ?? [];
        $submittedInstrumentNames = $_POST['instrument_name'] ?? [];
        $submittedInstrumentTypes = $_POST['instrument_type'] ?? [];
        $submittedInstrumentDescriptions = $_POST['instrument_description'] ?? [];
        $submittedInstrumentAccesses = $_POST['instrument_access'] ?? [];

        $currentInstruments = Project::getProjectInstruments($projectId);
        $currentInstrumentIds = array_column($currentInstruments, 'id');
        self::logInfo("Current Instruments from DB: " . implode(', ', $currentInstrumentIds));

        $instrumentsToKeep = [];
        foreach ($submittedInstrumentIds as $key => $id) {
            $instrumentData = [
                'name' => $submittedInstrumentNames[$key] ?? '',
                'type' => $submittedInstrumentTypes[$key] ?? '',
                'description' => $submittedInstrumentDescriptions[$key] ?? '',
                'access_link' => $submittedInstrumentAccesses[$key] ?? '',
            ];
            if (!empty($id)) {
                $instrumentsToKeep[] = (int) $id;
                self::logInfo("Attempting to update instrument ID {$id}");
                if (!Project::updateInstrument((int) $id, $instrumentData)) {
                    throw new Exception("Failed to update instrument ID {$id}.");
                }
            } else {
                self::logInfo("Attempting to add new instrument");
                if (!Project::addInstrument($projectId, $instrumentData)) {
                    throw new Exception("Failed to add new instrument.");
                }
            }
        }

        $instrumentsToDelete = array_diff($currentInstrumentIds, $instrumentsToKeep);
        if (!empty($instrumentsToDelete)) {
            self::logInfo("Attempting to delete instruments: " . implode(', ', $instrumentsToDelete));
            if (!Project::deleteInstruments($instrumentsToDelete)) {
                throw new Exception("Failed to delete instruments.");
            }
            self::logInfo("Deleted instruments from DB: " . implode(', ', $instrumentsToDelete) . " -> Success");
        }
    }

    private static function handleConfigFile(int $projectId): void
    {
        global $PATHS;
        $projectCurrentState = Project::getById($projectId);
        $oldConfigFile = $projectCurrentState['config_file'] ?? null;
        self::logInfo("Old config file path: " . ($oldConfigFile ?? 'None'));

        $deleteConfigFile = isset($_POST['delete_config_file']) && $_POST['delete_config_file'] == '1';

        if ($deleteConfigFile && $oldConfigFile) {
            self::deleteFileFromDisk(APP_ROOT . '/' . $oldConfigFile, "old config file '{$oldConfigFile}'");
            if (!Project::deleteConfigFile($projectId)) {
                throw new Exception("Failed to clear config_file path in DB.");
            }
        }

        if (!empty($_FILES['config_file']['tmp_name'])) {
            self::logInfo("New config file detected for upload.");
            $originalFileName = basename($_FILES['config_file']['name']);
            $uniqueFileName = uniqid('config_') . '_' . $originalFileName;
            $targetDir = $PATHS['upload_configs_dir'];
            if (!is_dir($targetDir)) { mkdir($targetDir, 0755, true); }
            $newConfigFilePath = 'public/uploads/configs/' . $uniqueFileName;
            $fullNewConfigFilePath = $targetDir . $uniqueFileName;

            if (move_uploaded_file($_FILES['config_file']['tmp_name'], $fullNewConfigFilePath)) {
                self::logInfo("New config file moved successfully to: " . $fullNewConfigFilePath);
                if ($oldConfigFile && !$deleteConfigFile) {
                    self::deleteFileFromDisk(APP_ROOT . '/' . $oldConfigFile, "previous config file '{$oldConfigFile}' during replacement");
                }
                if (!Project::updateConfigFile($projectId, $newConfigFilePath)) {
                    throw new Exception("Failed to update config_file path in DB.");
                }
            } else {
                self::logError("FAILED to move new config file. Temp: " . $_FILES['config_file']['tmp_name'] . ", Target: " . $fullNewConfigFilePath);
                throw new Exception("Failed to upload new configuration file.");
            }
        }
    }

    private static function handleSourceFiles(int $projectId): void
    {
        global $PATHS;
        $deleteSourceFiles = $_POST['delete_source_files'] ?? [];
        if (!empty($deleteSourceFiles)) {
            self::logInfo("Source files marked for deletion: " . implode(', ', $deleteSourceFiles));
            $filesToDeleteFromStorage = Project::getSourceFilePathsByIds($deleteSourceFiles);
            foreach ($filesToDeleteFromStorage as $filePath) {
                self::deleteFileFromDisk(APP_ROOT . '/' . $filePath, "source file '{$filePath}'");
            }
            if (!Project::deleteSourceFiles($deleteSourceFiles)) {
                throw new Exception("Failed to delete source file records from DB.");
            }
        }

        if (!empty($_FILES['new_source_files']['tmp_name'][0])) {
            self::logInfo("New source files detected for upload.");
            $targetDir = $PATHS['upload_sources_dir'];
            if (!is_dir($targetDir)) { mkdir($targetDir, 0755, true); }

            foreach ($_FILES['new_source_files']['tmp_name'] as $index => $tmpName) {
                if ($tmpName && $_FILES['new_source_files']['error'][$index] === UPLOAD_ERR_OK) {
                    $originalName = basename($_FILES['new_source_files']['name'][$index]);
                    $uniqueName = uniqid('source_') . '_' . $originalName;
                    $newSourceFilePath = 'public/uploads/sources/' . $uniqueName;
                    $fullNewSourceFilePath = $targetDir . $uniqueName;

                    if (move_uploaded_file($tmpName, $fullNewSourceFilePath)) {
                        self::logInfo("New source file '{$originalName}' moved to: " . $fullNewSourceFilePath);
                        if (!Project::addSourceFile($projectId, $originalName, $newSourceFilePath)) {
                            throw new Exception("Failed to add new source file '{$originalName}' to database.");
                        }
                    } else {
                        self::logError("FAILED to move new source file '{$originalName}'. Temp: " . $tmpName . ", Target: " . $fullNewSourceFilePath);
                        throw new Exception("Failed to upload source file '{$originalName}'.");
                    }
                } else {
                    self::logWarning("Skipping invalid new source file upload. Original name: " . ($_FILES['new_source_files']['name'][$index] ?? 'N/A') . ", Error: " . ($_FILES['new_source_files']['error'][$index] ?? 'N/A'));
                }
            }
        }
    }

    private static function deleteFileFromDisk(string $filePath, string $description): void
    {
        if (file_exists($filePath)) {
            if (unlink($filePath)) {
                self::logInfo("Deleted {$description} from storage: {$filePath}");
            } else {
                self::logError("FAILED to delete {$description} from storage: {$filePath}");
            }
        } else {
            self::logInfo("{$description} not found on disk for deletion: {$filePath}");
        }
    }

    private static function loadProjectDataForEditView(int $projectId): void
    {
        self::$project = Project::getById($projectId);
        if (!self::$project) {
            self::setErrorMessage("Project not found or you do not have permission to edit it.");
            self::logError("Project not found for display: " . $projectId);
        } else {
            self::$projectInstruments = Project::getProjectInstruments($projectId);
            self::$projectFiles = Project::getProjectSourceFiles($projectId);
            self::logInfo("Fetched project data for display. Instruments: " . count(self::$projectInstruments) . ", Source Files: " . count(self::$projectFiles));
        }
    }

    private static function setErrorMessage(string $message): void
    {
        self::$errorMessage = $message;
    }

    private static function setSuccessMessage(string $message): void
    {
        self::$successMessage = $message;
    }

    private static function logError(string $message): void
    {
        error_log("[ProjectController ERROR]: " . $message);
    }

    private static function logInfo(string $message): void
    {
        error_log("[ProjectController INFO]: " . $message);
    }

    private static function logWarning(string $message): void
    {
        error_log("[ProjectController WARNING]: " . $message);
    }

    private static function renderDetailView(): void
    {
        global $PATHS;
        $project = self::$project;
        $projectFiles = self::$projectFiles;
        $projectInstruments = self::$projectInstruments;
        $errorMessage = self::$errorMessage;
        $successMessage = self::$successMessage;

        $configBasePath = $PATHS['url_configs'];
        $sourceBasePath = $PATHS['url_sources'];

        require_once APP_ROOT . '/app/views/project/projectDetail.php';
    }

    private static function renderEditView(): void
    {
        $project = self::$project;
        $existingSourceFiles = self::$projectFiles;
        $existingInstruments = self::$projectInstruments;
        $errorMessage = self::$errorMessage;
        $successMessage = self::$successMessage;
        require_once APP_ROOT . '/app/views/project/projectEdit.php';
    }
}