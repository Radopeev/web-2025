<?php include __DIR__ . '/../partials/header.php'; ?>

<link rel="stylesheet" href="<?php echo BASE_PATH; ?>/public/styles/project_edit_styles.css">

<main class="project-edit-main">
<form action="<?php echo BASE_PATH; ?>/project/edit?id=<?php echo htmlspecialchars($project['id']); ?>" method="POST"
    enctype="multipart/form-data" class="project-edit-form">
    <h2>
        <?php if ($project['id']): ?>
            Edit Project: <span class="project-title-name"><?php echo htmlspecialchars($project['title']); ?></span>
        <?php else: ?>
            Create New Project
        <?php endif; ?>
    </h2>

    <input type="hidden" name="_method" value="PUT">
    <input type="hidden" name="project_id" value="<?php echo htmlspecialchars($project['id']); ?>">

    <label for="title">Project Title:</label>
    <input type="text" id="title" name="title" placeholder="Project Title"
        value="<?php echo htmlspecialchars($project['title']); ?>" required>

    <label for="description">Project Description:</label>
    <textarea id="description" name="description"
        placeholder="Project Description"><?php echo htmlspecialchars($project['description']); ?></textarea>

    <label>Source Files:</label>
    <div id="file-inputs-container">
        <input type="file" name="new_source_files[]" multiple onchange="updateFileList()">
    </div>
    <button type="button" class="add-file-btn" onclick="addFileInput()">Add Another File Input</button>
    <ul id="selected-files-list"></ul>

    <h4>Existing Source Files:</h4>
    <ul id="existing-files-list">
        <?php if (!empty($existingSourceFiles)): ?>
            <?php foreach ($existingSourceFiles as $file): ?>
                <li>
                    <span class="file-name"><?php echo htmlspecialchars($file['original_name'] ?? basename($file['path'])); ?></span>
                    <?php if (!empty($file['path'])): ?>
                        <span>(<a href="<?php echo BASE_PATH; ?>/public/uploads/sources/<?php echo htmlspecialchars(basename($file['path'])); ?>" download="<?php echo htmlspecialchars($file['original_name'] ?? basename($file['path'])); ?>">Download</a>)</span>
                    <?php endif; ?>
                    <label>
                        <input type="checkbox" name="delete_source_files[]"
                            value="<?php echo htmlspecialchars($file['id'] ?? $file['path']); ?>">
                        Delete
                    </label>
                    <input type="hidden" name="existing_source_file_ids[]"
                        value="<?php echo htmlspecialchars($file['id'] ?? $file['path']); ?>">
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li>No existing source files.</li>
        <?php endif; ?>
    </ul>

    <label>Configuration File:</label>
    <?php if ($project['config_file']): ?>
        <p>Current Config: <?php echo htmlspecialchars(basename($project['config_file'])); ?>
            <input type="checkbox" name="delete_config_file" value="1"> Delete Current
        </p>
    <?php else: ?>
        <p>No configuration file uploaded.</p>
    <?php endif; ?>
    <input type="file" name="config_file"><br>
    <h3>Instruments</h3>
    <div id="instruments-container">
        <?php if (!empty($existingInstruments)): ?>
            <?php foreach ($existingInstruments as $index => $instrument): ?>
                <div class="instrument" data-instrument-id="<?php echo htmlspecialchars($instrument['id'] ?? $index); ?>">
                    <input type="hidden" name="instrument_id[]"
                        value="<?php echo htmlspecialchars($instrument['id'] ?? ''); ?>">
                    <input type="text" name="instrument_name[]" placeholder="Name"
                        value="<?php echo htmlspecialchars($instrument['name']); ?>">
                    <input type="text" name="instrument_type[]" placeholder="Type"
                        value="<?php echo htmlspecialchars($instrument['type']); ?>">
                    <input type="text" name="instrument_description[]" placeholder="Description"
                        value="<?php echo htmlspecialchars($instrument['description']); ?>">
                    <input type="url" name="instrument_access[]" placeholder="Access URL"
                        value="<?php echo htmlspecialchars($instrument['access_link']); ?>">
                    <button type="button" onclick="removeInstrument(this)">Remove</button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <button type="button" class="add-instrument-btn" onclick="addInstrument()">Add Instrument</button><br>

    <input type="submit" value="Save Changes">
</form>
</main>

<script src="<?php echo BASE_PATH; ?>/public/js/project_edit_scrips.js"></script>

<?php include __DIR__ . '/../partials/footer.php'; ?>