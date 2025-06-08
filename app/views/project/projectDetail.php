<?php include __DIR__ . '/../partials/header.php'; ?>

<link rel="stylesheet" href="/public/styles/main_styles.css">
<link rel="stylesheet" href="/public/styles/project_detail_styles.css">

<div class="project-detail-container">
    <header class="project-detail-header">
        <div class="project-detail-header-bar">
            <a href="/landingPage" class="back-link">&larr; Back to Projects</a>
            <?php if (isset($project) && $project): ?>
                <h1 class="project-title">Project: <span class="project-title-name"><?php echo htmlspecialchars($project['title']); ?></span></h1>
            <?php else: ?>
                <h1 class="project-title">Project Details</h1>
            <?php endif; ?>
        </div>
    </header>

    <section class="project-detail-section">
        <?php if (!empty($errorMessage)): ?>
            <div class="error-message">
                <p><?php echo htmlspecialchars($errorMessage); ?></p>
            </div>
        <?php elseif (isset($project) && $project): ?>
            <div class="project-info-card">
                <h2 class="section-title">
                    Project Information
                    <a href="/project/edit?id=<?php echo htmlspecialchars($project['id']); ?>" class="edit-project-btn">Edit Project</a>
                </h2>
                <p><strong>Description:</strong> <?php echo htmlspecialchars($project['description']); ?></p>
                <p><strong>Created At:</strong> <?php echo htmlspecialchars(date('F j, Y, g:i a', strtotime($project['created_at']))); ?></p>

                <?php if (isset($project['config_file']) && $project['config_file']): ?>
                    <p>
                        <strong>Configuration File:</strong>
                        <?php
                        $configFileName = basename($project['config_file']);
                        $parts = explode('_', $configFileName, 2);
                        $configFileName = $parts[1];
                        $configIsViewableInline = $project['config_is_viewable_inline'] ?? false;
                        $configViewType = $project['config_view_type'] ?? '';
                        $configViewableContent = $project['config_viewable_content'] ?? null;
                        $configElementId = 'config_content_' . htmlspecialchars($project['id']);
                        ?>
                        <?php if ($configIsViewableInline && $configViewType === 'text'): ?>
                            <span class="file-name"><?php echo $configFileName; ?></span>
                            (<a href="<?php echo htmlspecialchars($configBasePath . $configFileName); ?>" download="<?php echo $configFileName; ?>">Download</a>)
                            <div class="file-content-box">
                                <pre><?php echo htmlspecialchars($configViewableContent); ?></pre>
                            </div>
                        <?php else: ?>
                            <?php echo $configFileName; ?>
                            (<a href="<?php echo htmlspecialchars($configBasePath . $configFileName); ?>" download="<?php echo $configFileName; ?>">Download</a>)
                            <?php if ($configIsViewableInline): ?>
                                <a href="javascript:void(0);" onclick="toggleVisibility('<?php echo $configElementId; ?>');">(View/Hide)</a>
                                <div id="<?php echo $configElementId; ?>" class="file-content-box" style="display:none;">
                                    <?php if ($configViewType === 'image'): ?>
                                        <img src="<?php echo htmlspecialchars($configBasePath . $configFileName); ?>" alt="<?php echo $configFileName; ?>" class="inline-image">
                                    <?php elseif ($configViewType === 'pdf'): ?>
                                        <iframe src="<?php echo htmlspecialchars($configBasePath . $configFileName); ?>" class="inline-pdf"></iframe>
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

            <div class="project-files-card">
                <h2 class="section-title">Source Files</h2>
                <?php if (!is_array($projectFiles)) $projectFiles = []; ?>
                <?php if (empty($projectFiles)): ?>
                    <p>No files associated with this project.</p>
                <?php else: ?>
                    <ul class="file-list">
                        <?php foreach ($projectFiles as $file): ?>
                            <?php
                            if (!is_array($file)) continue;
                            $fileNameToDisplay = $file['original_name'] ?? 'Unknown File';
                            $filePath = $file['file_path'] ?? '';
                            $uploadedTimestamp = strtotime($file['uploaded_at'] ?? '');
                            $uploadedDateDisplay = ($uploadedTimestamp !== false && $uploadedTimestamp > 0)
                                ? date('F j, Y, g:i a', $uploadedTimestamp)
                                : 'Date N/A';
                            $isViewableInline = $file['is_viewable_inline'] ?? false;
                            $viewableContent = $file['viewable_content'] ?? null;
                            $viewType = $file['view_type'] ?? '';
                            $fileId = $file['id'] ?? uniqid();
                            $elementId = 'file_content_' . htmlspecialchars($fileId);
                            ?>
                            <li class="file-list-item">
                                <?php if (!empty($filePath)): ?>
                                    <?php if ($isViewableInline && $viewType === 'text'): ?>
                                        <span class="file-name"><?php echo htmlspecialchars($fileNameToDisplay); ?></span>
                                        <span>(<a href="<?php echo htmlspecialchars($sourceBasePath . $filePath); ?>" download="<?php echo htmlspecialchars($fileNameToDisplay); ?>">Download</a>)</span>
                                        <div class="file-content-box">
                                            <pre><?php echo htmlspecialchars($viewableContent); ?></pre>
                                        </div>
                                    <?php else: ?>
                                        <span><?php echo htmlspecialchars($fileNameToDisplay); ?> (<a href="<?php echo htmlspecialchars($sourceBasePath . $filePath); ?>" download="<?php echo htmlspecialchars($fileNameToDisplay); ?>">Download</a>)</span>
                                        <?php if ($isViewableInline): ?>
                                            <a href="javascript:void(0);" onclick="toggleVisibility('<?php echo $elementId; ?>');">(View/Hide)</a>
                                            <div id="<?php echo $elementId; ?>" class="file-content-box" style="display:none;">
                                                <?php if ($viewType === 'image'): ?>
                                                    <img src="<?php echo htmlspecialchars($sourceBasePath . $filePath); ?>" alt="<?php echo htmlspecialchars($fileNameToDisplay); ?>" class="inline-image">
                                                <?php elseif ($viewType === 'pdf'): ?>
                                                    <iframe src="<?php echo htmlspecialchars($sourceBasePath . $filePath); ?>" class="inline-pdf"></iframe>
                                                <?php else: ?>
                                                    <p>This file type can be viewed inline but content not prepared. Please download.</p>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php echo htmlspecialchars($fileNameToDisplay); ?> (No file path)
                                <?php endif; ?>
                                <span class="file-uploaded-date">(Uploaded: <?php echo htmlspecialchars($uploadedDateDisplay); ?>)</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <hr>

            <?php if (isset($selectedFileContent) && $selectedFileContent !== null): ?>
                <div id="file-content-viewer" class="file-content-viewer">
                    <h2>Content of: <?php echo htmlspecialchars($selectedFileName); ?></h2>
                    <?php
                    if (strpos($selectedFileMimeType, 'text/') === 0 || $selectedFileMimeType === 'application/json' || $selectedFileMimeType === 'application/xml'):
                        ?>
                        <pre class="file-content-box"><?php echo htmlspecialchars($selectedFileContent); ?></pre>
                    <?php elseif (strpos($selectedFileMimeType, 'image/') === 0): ?>
                        <img src="data:<?php echo htmlspecialchars($selectedFileMimeType); ?>;base64,<?php echo base64_encode($selectedFileContent); ?>" alt="<?php echo htmlspecialchars($selectedFileName); ?>" class="inline-image">
                    <?php elseif ($selectedFileMimeType === 'application/pdf'): ?>
                        <p>PDF files are best viewed in a new tab or downloaded. Please use the Download link or implement a dedicated PDF viewer.</p>
                    <?php else: ?>
                        <p>This file type cannot be displayed directly. Please use the Download link.</p>
                    <?php endif; ?>
                    <hr>
                </div>
            <?php endif; ?>

            <hr>

            <div class="project-instruments-card">
                <h2 class="section-title">Associated Instruments</h2>
                <?php if (!is_array($projectInstruments)) $projectInstruments = []; ?>
                <?php if (!empty($projectInstruments)): ?>
                    <ul class="instrument-list">
                        <?php foreach ($projectInstruments as $instrument): ?>
                            <?php if (!is_array($instrument)) continue; ?>
                            <li class="instrument-list-item">
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