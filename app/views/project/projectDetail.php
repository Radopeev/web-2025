<?php
// app/Views/project_details.php
// Variables expected to be set by the Controller:
// $project, $projectFiles, $projectInstruments, $errorMessage, $configBasePath, $sourceBasePath
// $selectedFileContent, $selectedFileName, $selectedFileMimeType
?>

<?php include __DIR__ . '/../partials/header.php'; // Adjust path based on your structure ?>

    <div>
        <header>
            <div>
                <a href="/landingPage">&larr; Back to Projects</a>
                <?php if (isset($project) && $project): ?>
                    <h1>Project: <?php echo htmlspecialchars($project['title']); ?></h1>
                <?php else: ?>
                    <h1>Project Details</h1>
                <?php endif; ?>
            </div>
        </header>

        <section>
            <?php if (!empty($errorMessage)): ?>
                <div>
                    <p><?php echo htmlspecialchars($errorMessage); ?></p>
                </div>
            <?php elseif (isset($project) && $project): ?>
                <div>
                    <h2>
                        Project Information
                        <a href="/project/edit?id=<?php echo htmlspecialchars($project['id']); ?>" style="margin-left: 15px; padding: 5px 10px; background-color: #007bff; color: white; border-radius: 5px; text-decoration: none;">Edit Project</a>
                    </h2>
                    <p><strong>Description:</strong> <?php echo htmlspecialchars($project['description']); ?></p>
                    <p><strong>Created At:</strong> <?php echo htmlspecialchars(date('F j, Y, g:i a', strtotime($project['created_at']))); ?></p>

                    <?php if (isset($project['config_file']) && $project['config_file']): ?>
                        <p>
                        <strong>Configuration File:</strong>
                        <?php
                        $configFileName = basename($project['config_file']);
                        $parts = explode('_', $configFileName,2);
                        $configFileName = $parts[1];
                        $configIsViewableInline = $project['config_is_viewable_inline'] ?? false;
                        $configViewType = $project['config_view_type'] ?? '';
                        $configViewableContent = $project['config_viewable_content'] ?? null;
                        $configElementId = 'config_content_' . htmlspecialchars($project['id']);
                        ?>

                        <?php if ($configIsViewableInline && $configViewType === 'text'): ?>
                            <span style="font-weight: bold;"><?php echo $configFileName; ?></span>
                            (<a href="<?php echo htmlspecialchars($configBasePath . $configFileName); ?>" download="<?php echo $configFileName; ?>">Download</a>)
                            <div style="border: 1px solid #ccc; padding: 10px; margin-top: 5px;">
                                <pre style="white-space: pre-wrap; word-wrap: break-word;"><?php echo htmlspecialchars($configViewableContent); ?></pre>
                            </div>
                        <?php else: ?>
                            <?php echo $configFileName; ?>
                            (<a href="<?php echo htmlspecialchars($configBasePath . $configFileName); ?>" download="<?php echo $configFileName; ?>">Download</a>)
                            <?php if ($configIsViewableInline): // If it's technically viewable but not text, offer toggle ?>
                                <a href="javascript:void(0);" onclick="toggleVisibility('<?php echo $configElementId; ?>');"> (View/Hide)</a>
                                <div id="<?php echo $configElementId; ?>" style="display:none; border: 1px solid #ccc; padding: 10px; margin-top: 5px;">
                                    <?php if ($configViewType === 'image'): ?>
                                        <img src="<?php echo htmlspecialchars($configBasePath . $configFileName); ?>" alt="<?php echo $configFileName; ?>" style="max-width: 100%; height: auto;">
                                    <?php elseif ($configViewType === 'pdf'): ?>
                                        <iframe src="<?php echo htmlspecialchars($configBasePath . $configFileName); ?>" style="width:100%; height:600px; border:none;"></iframe>
                                    <?php else: ?>
                                        <p>This file type can be viewed inline but content not prepared. Please download.</p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        </p>
                    <?php else: ?>
                        <p><strong>Configuration File:</strong> Not specified.</p>
                    <?php endif; ?>
                </div>

                <hr>

                <div>
                    <h2>Associated Files</h2>
                    <?php
                    if (!is_array($projectFiles)) {
                        error_log("WARNING: \$projectFiles is not an array in project_details.php view. Type: " . gettype($projectFiles));
                        $projectFiles = [];
                    }
                    ?>
                    <?php if (empty($projectFiles)): ?>
                        <p>No files associated with this project.</p>
                    <?php else: ?>
                        <ul>
                            <?php foreach ($projectFiles as $file): ?>
                                <?php
                                if (!is_array($file)) {
                                    error_log("WARNING: Element in \$projectFiles is not an array. Skipping. Value: " . print_r($file, true));
                                    continue;
                                }

                                $fileNameToDisplay = $file['original_name'] ?? 'Unknown File';
                                $filePath = $file['file_path'] ?? '';
                                $uploadedTimestamp = strtotime($file['uploaded_at'] ?? '');
                                $uploadedDateDisplay = ($uploadedTimestamp !== false && $uploadedTimestamp > 0)
                                    ? date('F j, Y, g:i a', $uploadedTimestamp)
                                    : 'Date N/A';

                                $isViewableInline = $file['is_viewable_inline'] ?? false;
                                $viewableContent = $file['viewable_content'] ?? null;
                                $viewType = $file['view_type'] ?? '';
                                $fileId = $file['id'] ?? uniqid(); // Use unique ID for toggling if ID is missing
                                $elementId = 'file_content_' . htmlspecialchars($fileId);
                                ?>
                                <li>
                                    <?php if (!empty($filePath)): ?>
                                        <?php if ($isViewableInline && $viewType === 'text'): ?>
                                            <span style="font-weight: bold;"><?php echo htmlspecialchars($fileNameToDisplay); ?></span>
                                            (<a href="<?php echo htmlspecialchars($sourceBasePath . $filePath); ?>" download="<?php echo htmlspecialchars($fileNameToDisplay); ?>">Download</a>)
                                            <div style="border: 1px solid #ccc; padding: 10px; margin-top: 5px;">
                                                <pre style="white-space: pre-wrap; word-wrap: break-word;"><?php echo htmlspecialchars($viewableContent); ?></pre>
                                            </div>
                                        <?php else: ?>
                                            <?php echo htmlspecialchars($fileNameToDisplay); ?>
                                            (<a href="<?php echo htmlspecialchars($sourceBasePath . $filePath); ?>" download="<?php echo htmlspecialchars($fileNameToDisplay); ?>">Download</a>)
                                            <?php if ($isViewableInline): // If it's image/pdf, offer toggle ?>
                                                <a href="javascript:void(0);" onclick="toggleVisibility('<?php echo $elementId; ?>');"> (View/Hide)</a>
                                                <div id="<?php echo $elementId; ?>" style="display:none; border: 1px solid #ccc; padding: 10px; margin-top: 5px;">
                                                    <?php if ($viewType === 'image'): ?>
                                                        <img src="<?php echo htmlspecialchars($sourceBasePath . $filePath); ?>" alt="<?php echo htmlspecialchars($fileNameToDisplay); ?>" style="max-width: 100%; height: auto;">
                                                    <?php elseif ($viewType === 'pdf'): ?>
                                                        <iframe src="<?php echo htmlspecialchars($sourceBasePath . $filePath); ?>" style="width:100%; height:600px; border:none;"></iframe>
                                                    <?php else: ?>
                                                        <p>This file type can be viewed inline but content not prepared. Please download.</p>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($fileNameToDisplay); ?> (No file path)
                                    <?php endif; ?>
                                    (Uploaded: <?php echo htmlspecialchars($uploadedDateDisplay); ?>)
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

                <hr>

                <?php // This section is for the single selected file from a GET parameter, not needed if displaying all text files directly. ?>
                <?php if (isset($selectedFileContent) && $selectedFileContent !== null): ?>
                    <div id="file-content-viewer">
                        <h2>Content of: <?php echo htmlspecialchars($selectedFileName); ?></h2>
                        <?php
                        // This logic is for the _single_ file requested via GET parameter, potentially redundant now
                        // if all text files are shown inline. Keep it if you have other ways to select a file.
                        if (strpos($selectedFileMimeType, 'text/') === 0 || $selectedFileMimeType === 'application/json' || $selectedFileMimeType === 'application/xml'):
                            ?>
                            <pre style="white-space: pre-wrap; word-wrap: break-word; background-color: #f0f0f0; padding: 10px; border: 1px solid #ccc; max-height: 400px; overflow-y: auto;"><?php echo htmlspecialchars($selectedFileContent); ?></pre>
                        <?php elseif (strpos($selectedFileMimeType, 'image/') === 0): ?>
                            <img src="data:<?php echo htmlspecialchars($selectedFileMimeType); ?>;base64,<?php echo base64_encode($selectedFileContent); ?>" alt="<?php echo htmlspecialchars($selectedFileName); ?>" style="max-width: 100%; height: auto; display: block; margin: 0 auto;">
                        <?php elseif ($selectedFileMimeType === 'application/pdf'): ?>
                            <p>PDF files are best viewed in a new tab or downloaded. Please use the Download link or implement a dedicated PDF viewer.</p>
                        <?php else: ?>
                            <p>This file type cannot be displayed directly. Please use the Download link.</p>
                        <?php endif; ?>
                        <hr>
                    </div>
                <?php endif; ?>

                <hr>

                <div>
                    <h2>Associated Instruments</h2>
                    <?php
                    if (!is_array($projectInstruments)) {
                        error_log("WARNING: \$projectInstruments is not an array in project_details.php view. Type: " . gettype($projectInstruments));
                        $projectInstruments = [];
                    }
                    ?>
                    <?php if (!empty($projectInstruments)): ?>
                        <ul>
                            <?php foreach ($projectInstruments as $instrument): ?>
                                <?php
                                if (!is_array($instrument)) {
                                    error_log("WARNING: Element in \$projectInstruments is not an array. Skipping. Value: " . print_r($instrument, true));
                                    continue;
                                }
                                ?>
                                <li>
                                    <strong><?php echo htmlspecialchars($instrument['name'] ?? 'Unknown'); ?></strong> (Type: <?php echo htmlspecialchars($instrument['type'] ?? 'N/A'); ?>)
                                    <p><?php echo htmlspecialchars($instrument['description'] ?? ''); ?></p>
                                    <?php if (!empty($instrument['access_link'])): ?>
                                        <p>Access: <a href="<?php echo htmlspecialchars($instrument['access_link']); ?>" target="_blank" rel="noopener noreferrer"><?php echo htmlspecialchars($instrument['access_link']); ?></a></p>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>No instruments associated with this project.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <script>
        function toggleVisibility(elementId) {
            var element = document.getElementById(elementId);
            if (element) {
                if (element.style.display === 'none') {
                    element.style.display = 'block';
                } else {
                    element.style.display = 'none';
                }
            }
        }
    </script>

<?php include __DIR__ . '/../partials/footer.php'; ?>