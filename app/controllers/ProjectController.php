<?php

// Ensure your Project model is correctly included and accessible.
// Adjust the path based on your exact directory structure.
require_once __DIR__ . '/../models/Project.php';

// Assuming your Project class is defined within Project.php and is globally accessible.
// If you are using namespaces (e.g., `namespace App\Controllers; use App\Models\Project;`),
// you would add those declarations here. For this example, we assume no namespaces
// unless explicitly provided by you.

class ProjectController
{
    /**
     * Handles displaying the project details, including associated files and instruments.
     * It also prepares file content for inline viewing for supported file types.
     */
    public static function showProjectDetails(): void
    {
        $projectId = $_GET['id'] ?? null;
        $project = null;
        $projectFiles = []; // This will hold processed file data, including content for viewable files
        $projectInstruments = [];
        $errorMessage = ''; // Stores any error message to be displayed in the view

        // --- Define absolute paths for uploaded files on the server's file system ---
        // IMPORTANT: You MUST adjust these paths to the actual absolute locations
        //            of your 'public/upload/configs' and 'public/upload/sources'
        //            directories on your web server.
        //            Using realpath() helps resolve '..' and symbolic links for security.
        $configUploadAbsPath = realpath(__DIR__ . '/../../public/uploads/configs/');
        $sourceUploadAbsPath = realpath(__DIR__ . '/../../public/uploads/sources/');

        // Basic check to ensure the absolute paths are valid directories
        if (!$configUploadAbsPath || !is_dir($configUploadAbsPath)) {
            error_log("Error: Configuration upload path is invalid or not a directory: " . $configUploadAbsPath);
            $errorMessage = "Server configuration error: Upload directory for configs is invalid or missing.";
            require_once __DIR__ . '/../views/project/projectDetail.php'; // Load view to show error
            return; // Stop execution
        }
        if (!$sourceUploadAbsPath || !is_dir($sourceUploadAbsPath)) {
            error_log("Error: Source file upload path is invalid or not a directory: " . $sourceUploadAbsPath);
            $errorMessage = "Server configuration error: Upload directory for source files is invalid or missing.";
            require_once __DIR__ . '/../views/project/projectDetail.php'; // Load view to show error
            return; // Stop execution
        }

        // Validate the project ID from the GET request
        if ($projectId && is_numeric($projectId)) {
            $projectId = (int) $projectId; // Cast to integer for security

            try {
                // Attempt to retrieve project details from the database
                $project = Project::getById($projectId);

                if ($project) {
                    // Project found, now get its associated files and instruments

                    // --- Process Associated Files ---
                    $rawProjectFiles = Project::getProjectSourceFiles($projectId);

                    foreach ($rawProjectFiles as $file) {
                        $isViewableInline = false;
                        $viewType = '';     // 'text', 'image', 'pdf', or empty
                        $viewableContent = null; // Stores content for text files (null for others)

                        // Ensure file_path is not empty and construct full path safely
                        $filePathOnDisk = $file['file_path'] ? basename($file['file_path']) : ''; // basename helps prevent directory traversal
                        $fullFilePath = $sourceUploadAbsPath . DIRECTORY_SEPARATOR . $filePathOnDisk;

                        // Security check: Verify file exists and is within the allowed upload directory
                        // realpath() resolves symlinks and '.' for robust checking
                        $resolvedFullPath = realpath($fullFilePath);

                        if ($resolvedFullPath && str_starts_with($resolvedFullPath, $sourceUploadAbsPath) && file_exists($resolvedFullPath) && is_readable($resolvedFullPath)) {
                            // Determine MIME type for the file
                            $mimeType = 'application/octet-stream'; // Default to unknown/binary
                            if (function_exists('mime_content_type')) {
                                // Prefer mime_content_type for accuracy if 'fileinfo' extension is enabled
                                $mimeType = mime_content_type($resolvedFullPath);
                            } else {
                                // Fallback: Guess MIME type based on file extension
                                $extension = strtolower(pathinfo($file['original_name'] ?? '', PATHINFO_EXTENSION));

                                // Handle files with no explicit extensions (like 'Dockerfile', 'LICENSE')
                                if (empty($extension)) {
                                    $fileNameLower = strtolower($file['original_name'] ?? '');
                                    if ($fileNameLower === 'dockerfile' || $fileNameLower === 'license' || $fileNameLower === 'readme') {
                                        $mimeType = 'text/plain';
                                    }
                                } else {
                                    switch ($extension) {
                                        // Common text-based files / code files
                                        case 'txt':
                                        case 'log':
                                        case 'md':
                                        case 'csv':
                                        case 'json':
                                        case 'xml':
                                        case 'html':
                                        case 'css':
                                        case 'js':
                                        case 'php':
                                        case 'sh':
                                        case 'py':
                                        case 'yaml':
                                        case 'yml':
                                        case 'ini':
                                        case 'conf':
                                        case 'env':
                                        case 'gitignore':
                                        case 'editorconfig':
                                            $mimeType = 'text/plain';
                                            break; // Treat as plain text for displaying code/configs

                                        // Image files
                                        case 'jpg':
                                        case 'jpeg':
                                            $mimeType = 'image/jpeg';
                                            break;
                                        case 'png':
                                            $mimeType = 'image/png';
                                            break;
                                        case 'gif':
                                            $mimeType = 'image/gif';
                                            break;
                                        case 'svg':
                                            $mimeType = 'image/svg+xml';
                                            break;

                                        // PDF files
                                        case 'pdf':
                                            $mimeType = 'application/pdf';
                                            break;// Fallback for unknown
                                    }
                                }
                            }

                            // Decide if and how to display inline based on detected MIME type
                            // This array includes common text-like MIME types that mime_content_type might return
                            // but don't strictly start with 'text/' (e.g., application/json)
                            $textMimeTypesForInline = [
                                'application/json',
                                'application/xml',
                                'application/javascript',
                                'application/x-php',
                                'image/svg+xml', // SVG is XML-based text
                                'application/x-python',
                                'text/x-python', // Specific Python MIME types
                                'application/x-yaml',
                                'text/yaml',
                                'text/x-yaml', // Specific YAML MIME types
                                'application/x-dockerfile', // Specific Dockerfile MIME type
                                'text/plain', // Universal text mime type
                                'text/x-sh', // Shell script MIME type
                            ];

                            if (str_starts_with($mimeType, 'text/') || in_array($mimeType, $textMimeTypesForInline)) {
                                // For text files, read content if within size limit
                                // 500 KB limit to prevent performance issues with very large files
                                if (filesize($resolvedFullPath) < (1024 * 500)) {
                                    $viewableContent = file_get_contents($resolvedFullPath);
                                    $isViewableInline = true;
                                    $viewType = 'text';
                                } else {
                                    // File too large for inline display
                                    error_log("File " . $file['original_name'] . " too large for inline text display.");
                                }
                            } else if (str_starts_with($mimeType, 'image/')) {
                                // Images are typically displayed via <img> tag in the view
                                $isViewableInline = true;
                                $viewType = 'image';
                            } else if ($mimeType === 'application/pdf') {
                                // PDFs are typically displayed via <iframe> tag in the view
                                $isViewableInline = true;
                                $viewType = 'pdf';
                            }
                        } else {
                            // Log if a file cannot be found, is not readable, or fails security checks
                            error_log("File security check failed, or file not found/readable: " . $fullFilePath . " (Original: " . ($file['original_name'] ?? 'N/A') . ")");
                            // You could set an error message for this specific file in the array here if needed
                        }

                        // Add processed data to the current file's array for the view
                        $file['is_viewable_inline'] = $isViewableInline;
                        $file['view_type'] = $viewType;
                        $file['viewable_content'] = $viewableContent; // Will be null for non-text types
                        $projectFiles[] = $file; // Add this processed file to the list
                    }

                    if (!empty($project['config_file'])) {
                        $configFileName = basename($project['config_file']); // Get just the filename
                        $fullConfigPath = $configUploadAbsPath . DIRECTORY_SEPARATOR . $configFileName;

                        // Initialize config-specific view parameters (will be set on the $project array itself)
                        $project['config_is_viewable_inline'] = false;
                        $project['config_view_type'] = '';
                        $project['config_viewable_content'] = null;

                        $resolvedFullConfigPath = realpath($fullConfigPath);

                        if ($resolvedFullConfigPath && str_starts_with($resolvedFullConfigPath, $configUploadAbsPath) && file_exists($resolvedFullConfigPath) && is_readable($resolvedFullConfigPath)) {
                            $mimeType = 'application/octet-stream';
                            if (function_exists('mime_content_type')) {
                                $mimeType = mime_content_type($resolvedFullConfigPath);
                            } else {
                                $extension = strtolower(pathinfo($configFileName, PATHINFO_EXTENSION));
                                if (empty($extension)) {
                                    $fileNameLower = strtolower($configFileName);
                                    if ($fileNameLower === 'dockerfile' || $fileNameLower === 'license' || $fileNameLower === 'readme') {
                                        $mimeType = 'text/plain';
                                    }
                                } else {
                                    switch ($extension) {
                                        case 'txt':
                                        case 'log':
                                        case 'md':
                                        case 'csv':
                                        case 'json':
                                        case 'xml':
                                        case 'html':
                                        case 'css':
                                        case 'js':
                                        case 'php':
                                        case 'sh':
                                        case 'py':
                                        case 'yaml':
                                        case 'yml':
                                        case 'ini':
                                        case 'conf':
                                        case 'env':
                                        case 'gitignore':
                                        case 'editorconfig':
                                            $mimeType = 'text/plain';
                                            break;
                                        case 'jpg':
                                        case 'jpeg':
                                            $mimeType = 'image/jpeg';
                                            break;
                                        case 'png':
                                            $mimeType = 'image/png';
                                            break;
                                        case 'gif':
                                            $mimeType = 'image/gif';
                                            break;
                                        case 'svg':
                                            $mimeType = 'image/svg+xml';
                                            break;
                                        case 'pdf':
                                            $mimeType = 'application/pdf';
                                            break;
                                    }
                                }
                            }

                            if (str_starts_with($mimeType, 'text/') || in_array($mimeType, $textMimeTypesForInline)) { // Use the same list of text MIME types
                                if (filesize($resolvedFullConfigPath) < (1024 * 500)) {
                                    $project['config_viewable_content'] = file_get_contents($resolvedFullConfigPath);
                                    $project['config_is_viewable_inline'] = true;
                                    $project['config_view_type'] = 'text';
                                } else {
                                    error_log("Config file " . $configFileName . " too large for inline text display.");
                                }
                            } else if (str_starts_with($mimeType, 'image/')) {
                                $project['config_is_viewable_inline'] = true;
                                $project['config_view_type'] = 'image';
                            } else if ($mimeType === 'application/pdf') {
                                $project['config_is_viewable_inline'] = true;
                                $project['config_view_type'] = 'pdf';
                            }
                        } else {
                            error_log("Config file security check failed, or file not found/readable: " . $fullConfigPath . " (Original: " . $configFileName . ")");
                        }
                    }

                    // Get associated instruments (this remains unchanged)
                    $projectInstruments = Project::getProjectInstruments($projectId);

                } else {
                    // Project was not found in the database
                    $errorMessage = "Project with ID '$projectId' not found.";
                }
            } catch (mysqli_sql_exception $e) { // Catch specific MySQLi database exceptions
                $errorMessage = "A database error occurred. Please try again later.";
                error_log("Database error in ProjectController::showProjectDetails - Project ID: $projectId: " . $e->getMessage());
                // In a production environment, you might log $e->getTraceAsString() as well.
            } catch (Exception $e) { // Catch any other unexpected PHP exceptions
                $errorMessage = "An unexpected error occurred. Please contact support.";
                error_log("General error in ProjectController::showProjectDetails - Project ID: $projectId: " . $e->getMessage());
            }
        } else {
            // Invalid or missing project ID in the URL
            $errorMessage = "Invalid project ID provided. Please specify a valid project ID.";
        }

        // --- Define web paths for direct use in HTML (e.g., for <img> src, <iframe> src, or download links) ---
        // These paths are relative to your web server's document root (e.g., public/).
        $configBasePath = '/upload/configs/';
        $sourceBasePath = '/upload/sources/';

        // Load the view file, making all prepared variables available to it
        require_once __DIR__ . '/../views/project/projectDetail.php';
    }

    public static function editProject(): void
    {
        global $conn; // Access the global database connection

        error_log("--- Starting editProject controller (Direct File Ops) ---");
        $projectId = $_GET['id'] ?? null;
        $project = null;
        $existingSourceFiles = [];
        $existingInstruments = [];
        $errorMessage = '';
        $successMessage = ''; // This variable is only relevant for displaying success on the redirect target page, or if you choose to not redirect on success

        // --- 1. Validate Project ID ---
        if (!$projectId || !is_numeric($projectId)) {
            $errorMessage = "Invalid project ID provided for editing.";
            error_log("Edit (Direct): Invalid project ID: " . $projectId);
            require_once APP_ROOT . '/app/views/project/projectEdit.php';
            return;
        }
        $projectId = (int) $projectId;
        error_log("Edit (Direct): Project ID: " . $projectId);

        try {
            // --- 2. Handle POST Request (Form Submission) ---
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                error_log("Edit (Direct): Handling POST request for Project ID: " . $projectId);
                //error_log("Edit (Direct): FILES array: " . print_r($_FILES, true)); // DUMP FILES ARRAY for debugging

                // Basic Project Details
                $newTitle = trim($_POST['title'] ?? '');
                $newDescription = trim($_POST['description'] ?? '');

                // Validation
                if (empty($newTitle)) {
                    $errorMessage = "Project title cannot be empty.";
                    error_log("Edit (Direct): Validation failed: Title empty.");
                } else {
                    // Start a transaction for atomicity of DB operations
                    if ($conn instanceof mysqli) {
                        $conn->begin_transaction();
                    }

                    try {
                        // Attempt to update basic project details in the database
                        $updateData = [
                            'title' => $newTitle,
                            'description' => $newDescription,
                        ];
                        error_log("Updated data " . implode(', ', $updateData));
                        $projectUpdated = Project::update($projectId, $updateData);

                        if (!$projectUpdated) {
                            $errorMessage = "Failed to update project basic details. No changes were made or an error occurred.";
                            error_log("Edit (Direct): Failed to update basic project details for ID: " . $projectId);
                            throw new Exception("Project basic details update failed."); // Throw to trigger rollback
                        } else {
                            error_log("Edit (Direct): Basic project details updated for ID: " . $projectId);

                            // --- Handle Instruments (Purely DB operations) ---
                            $submittedInstrumentIds = $_POST['instrument_id'] ?? [];
                            $submittedInstrumentNames = $_POST['instrument_name'] ?? [];
                            $submittedInstrumentTypes = $_POST['instrument_type'] ?? [];
                            $submittedInstrumentDescriptions = $_POST['instrument_description'] ?? [];
                            $submittedInstrumentAccesses = $_POST['instrument_access'] ?? [];

                            $currentInstruments = Project::getProjectInstruments($projectId);
                            $currentInstrumentIds = array_column($currentInstruments, 'id');
                            error_log("Edit (Direct): Current Instruments from DB: " . print_r($currentInstrumentIds, true));

                            $instrumentsToKeep = [];
                            foreach ($submittedInstrumentIds as $key => $id) {
                                $instrumentData = [
                                    'name' => $submittedInstrumentNames[$key] ?? '',
                                    'type' => $submittedInstrumentTypes[$key] ?? '',
                                    'description' => $submittedInstrumentDescriptions[$key] ?? '',
                                    'access_link' => $submittedInstrumentAccesses[$key] ?? '',
                                ];
                                error_log("Edit (Direct): Current Instruments in a loop: " . print_r($instrumentData, true));
                                if (!empty($id)) { // Existing instrument (update it)
                                    $instrumentsToKeep[] = (int) $id;
                                    $result = Project::updateInstrument((int) $id, $instrumentData);
                                    if (!$result)
                                        throw new Exception("Failed to update instrument ID {$id}.");
                                    error_log("Edit (Direct): Updated instrument ID {$id}: Success");
                                } else { // New instrument (add it)
                                    $result = Project::addInstrument($projectId, $instrumentData);
                                    if (!$result)
                                        throw new Exception("Failed to add new instrument.");
                                    error_log("Edit (Direct): Added new instrument: Success");
                                }
                            }

                            // Delete instruments that were removed from the form
                            $instrumentsToDelete = array_diff($currentInstrumentIds, $instrumentsToKeep);
                            if (!empty($instrumentsToDelete)) {
                                $result = Project::deleteInstruments($instrumentsToDelete);
                                if (!$result)
                                    throw new Exception("Failed to delete instruments.");
                                error_log("Edit (Direct): Deleted instruments from DB: " . implode(', ', $instrumentsToDelete) . " -> Success");
                            }

                            // --- Handle Configuration File (Direct File Ops) ---
                            $projectCurrentState = Project::getById($projectId);
                            $oldConfigFile = $projectCurrentState['config_file'] ?? null;
                            error_log("Edit (Direct): Old config file path: " . ($oldConfigFile ?? 'None'));

                            $deleteConfigFile = isset($_POST['delete_config_file']) && $_POST['delete_config_file'] == '1';
                            if ($deleteConfigFile) {
                                if ($oldConfigFile && file_exists(APP_ROOT . '/' . $oldConfigFile)) {
                                    if (unlink(APP_ROOT . '/' . $oldConfigFile)) {
                                        error_log("Edit (Direct): Deleted old config file '{$oldConfigFile}' from storage.");
                                    } else {
                                        error_log("Edit (Direct): FAILED to delete old config file '{$oldConfigFile}' from storage.");
                                        // Not throwing an error here, as file deletion failure might not be critical enough to abort
                                    }
                                    Project::deleteConfigFile($projectId); // Clear path in DB
                                    error_log("Edit (Direct): Cleared config_file path in DB for ID: " . $projectId);
                                }
                            }

                            if (!empty($_FILES['config_file']['tmp_name'])) {
                                error_log("Edit (Direct): New config file detected for upload.");
                                $originalFileName = basename($_FILES['config_file']['name']);
                                $uniqueFileName = uniqid() . '_' . $originalFileName; // Use _ for better readability than just concatenation
                                // Construct target path, ensure 'public/uploads/configs' exists
                                $targetDir = APP_ROOT . '/public/uploads/configs/';
                                if (!is_dir($targetDir)) {
                                    mkdir($targetDir, 0755, true); // Create recursively with proper permissions
                                }
                                $newConfigFilePath = 'public/uploads/configs/' . $uniqueFileName;
                                $fullNewConfigFilePath = APP_ROOT . '/' . $newConfigFilePath;

                                if (move_uploaded_file($_FILES['config_file']['tmp_name'], $fullNewConfigFilePath)) {
                                    error_log("Edit (Direct): New config file moved successfully to: " . $fullNewConfigFilePath);
                                    // If old file was not explicitly deleted but new one is uploaded, delete it now
                                    if ($oldConfigFile && !$deleteConfigFile && file_exists(APP_ROOT . '/' . $oldConfigFile)) {
                                        if (unlink(APP_ROOT . '/' . $oldConfigFile)) {
                                            error_log("Edit (Direct): Replaced/deleted previous config file '{$oldConfigFile}' from storage.");
                                        } else {
                                            error_log("Edit (Direct): FAILED to delete previous config file '{$oldConfigFile}' during replacement.");
                                        }
                                    }
                                    error_log("New config file detected for upload." . $newConfigFilePath);
                                    Project::updateConfigFile($projectId, $newConfigFilePath); // Update DB with relative path
                                    error_log("Edit (Direct): Updated config_file path in DB for ID: " . $projectId . " to " . $newConfigFilePath);
                                } else {
                                    error_log("Edit (Direct): FAILED to move new config file. Temp: " . $_FILES['config_file']['tmp_name'] . ", Target: " . $fullNewConfigFilePath);
                                    $errorMessage .= " Failed to upload new configuration file.";
                                    throw new Exception("Config file upload failed."); // Throw to trigger rollback
                                }
                            }


                            // --- Handle Source Files (Direct File Ops) ---
                            $deleteSourceFiles = $_POST['delete_source_files'] ?? []; // IDs of files to delete
                            if (!empty($deleteSourceFiles)) {
                                error_log("Edit (Direct): Source files marked for deletion: " . implode(', ', $deleteSourceFiles));
                                $filesToDeleteFromStorage = Project::getSourceFilePathsByIds($deleteSourceFiles);
                                error_log("Edit (Direct): Actual paths for source file deletion: " . implode(', ', $filesToDeleteFromStorage));
                                if (!empty($filesToDeleteFromStorage)) {
                                    foreach ($filesToDeleteFromStorage as $filePath) {
                                        if (file_exists(APP_ROOT . '/' . $filePath)) {
                                            if (unlink(APP_ROOT . '/' . $filePath)) {
                                                error_log("Edit (Direct): Deleted source file '{$filePath}' from storage.");
                                            } else {
                                                error_log("Edit (Direct): FAILED to delete source file '{$filePath}' from storage.");
                                            }
                                        } else {
                                            error_log("Edit (Direct): Source file '{$filePath}' not found on disk for deletion.");
                                        }
                                    }
                                }
                                $dbDeleteSuccess = Project::deleteSourceFiles($deleteSourceFiles);
                                error_log("Edit (Direct): Deleted source files from DB for ID: " . implode(', ', $deleteSourceFiles));
                                if (!$dbDeleteSuccess)
                                    throw new Exception("Failed to delete source file records from DB.");
                                error_log("Edit (Direct): Deleted source file records from DB: Success");
                            }

                            if (!empty($_FILES['new_source_files']['tmp_name'][0])) { // Check if new files were selected
                                error_log("Edit (Direct): New source files detected for upload.");
                                $targetDir = APP_ROOT . '/public/uploads/sources/';
                                if (!is_dir($targetDir)) {
                                    mkdir($targetDir, 0755, true); // Create recursively with proper permissions
                                }

                                foreach ($_FILES['new_source_files']['tmp_name'] as $index => $tmpName) {
                                    // Ensure only valid uploads are processed
                                    if ($tmpName && $_FILES['new_source_files']['error'][$index] === UPLOAD_ERR_OK) {
                                        $originalName = basename($_FILES['new_source_files']['name'][$index]);
                                        $uniqueName = uniqid() . '_' . $originalName;
                                        $newSourceFilePath = 'public/uploads/sources/' . $uniqueName;
                                        $fullNewSourceFilePath = APP_ROOT . '/' . $newSourceFilePath;

                                        if (move_uploaded_file($tmpName, $fullNewSourceFilePath)) {
                                            error_log("Edit (Direct): New source file '{$originalName}' moved to: " . $fullNewSourceFilePath);
                                            // Add new record to DB
                                            Project::addSourceFile($projectId, $originalName, $newSourceFilePath);
                                            error_log("Edit (Direct): Added new source file record to DB: " . $originalName);
                                        } else {
                                            error_log("Edit (Direct): FAILED to move new source file '{$originalName}'. Temp: " . $tmpName . ", Target: " . $fullNewSourceFilePath);
                                            $errorMessage .= " Failed to upload source file '{$originalName}'.";
                                            // We'll let this continue but log the error, as one failed file might not stop others
                                        }
                                    } else {
                                        error_log("Edit (Direct): Skipping invalid new source file upload. Error: " . ($_FILES['new_source_files']['error'][$index] ?? 'N/A') . ", TmpName: " . ($tmpName ?? 'N/A'));
                                    }
                                }
                            }

                            // If we reached here without critical errors, commit the transaction
                            if (empty($errorMessage)) { // Check $errorMessage before committing
                                if ($conn instanceof mysqli) {
                                    $conn->commit();
                                }
                                $successMessage = "Project updated successfully!";
                                error_log("Edit (Direct): Project update SUCCESS for ID: " . $projectId);
                                header("Location: /project/details?id=" . $projectId . "&success=1");
                                exit();
                            } else {
                                if ($conn instanceof mysqli) {
                                    $conn->rollback();
                                }
                                error_log("Edit (Direct): Project update FAILED for ID: " . $projectId . ". Errors accumulated.");
                                // The error message is already set by now, view will display it.
                            }
                        }
                    } catch (Exception $e) {
                        // Catch any explicit exceptions thrown from within the try block
                        error_log("Edit (Direct): Transactional error during update: " . $e->getMessage());
                        $errorMessage = "An error occurred during the update: " . $e->getMessage();
                        if ($conn instanceof mysqli) {
                            $conn->rollback(); // Rollback all DB changes
                        }
                    }
                }
            } // END OF if ($_SERVER['REQUEST_METHOD'] === 'POST')

            // --- 3. Fetch Project Details (for GET request or if POST failed) ---
            // This section runs for GET requests or if the POST request failed validation/processing
            $project = Project::getById($projectId);
            if (!$project) {
                $errorMessage = "Project not found or you do not have permission to edit it.";
                error_log("Edit (Direct): Project not found for display: " . $projectId);
            } else {
                $existingInstruments = Project::getProjectInstruments($projectId);
                $existingSourceFiles = Project::getProjectSourceFiles($projectId);
                error_log("Edit (Direct): Fetched project data for display. Instruments: " . count($existingInstruments) . ", Source Files: " . count($existingSourceFiles));
            }

        } catch (\mysqli_sql_exception $e) {
            $errorMessage = "Database error: Could not process your request. Please try again later.";
            error_log("Database error in LandingPageController::editProject: " . $e->getMessage());
            if ($conn instanceof mysqli) {
                $conn->rollback();
            }
        } catch (\Exception $e) {
            $errorMessage = "An unexpected error occurred. Please contact support.";
            error_log("General error in LandingPageController::editProject: " . $e->getMessage());
            if ($conn instanceof mysqli) {
                $conn->rollback();
            }
        }

        // --- 4. Load the View ---
        // This line runs for GET requests, or if a POST request failed and didn't redirect.
        require_once APP_ROOT . '/app/views/project/projectEdit.php';
        error_log("--- Ending editProject controller (Direct File Ops) ---");
    }
}