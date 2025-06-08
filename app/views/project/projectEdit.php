<?php include __DIR__ . '/../partials/header.php'; ?>

<link rel="stylesheet" href="/public/styles/project_edit_styles.css">

<main class="project-edit-main">
<form action="/project/edit?id=<?php echo htmlspecialchars($project['id']); ?>" method="POST"
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
                        <span>(<a href="<?php echo htmlspecialchars($file['path']); ?>" download="<?php echo htmlspecialchars($file['original_name'] ?? basename($file['path'])); ?>">Download</a>)</span>
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
                        value="<?php echo htmlspecialchars($instrument['access']); ?>">
                    <button type="button" onclick="removeInstrument(this)">Remove</button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <button type="button" class="add-instrument-btn" onclick="addInstrument()">Add Instrument</button><br>

    <input type="submit" value="Save Changes">
</form>
</main>

<script>
    // --- JavaScript for Instruments ---
    function addInstrument(name = '', type = '', description = '', access = '') {
        const container = document.getElementById('instruments-container');
        const instrumentHTML = `
    <div class="instrument">
        <input type="hidden" name="instrument_id[]" value=""> <input type="text" name="instrument_name[]" placeholder="Name" value="${name}">
        <input type="text" name="instrument_type[]" placeholder="Type" value="${type}">
        <input type="text" name="instrument_description[]" placeholder="Description" value="${description}">
        <input type="url" name="instrument_access[]" placeholder="Access URL" value="${access}">
        <button type="button" onclick="removeInstrument(this)">Remove</button>
    </div>`;
        container.insertAdjacentHTML('beforeend', instrumentHTML);
    }

    function removeInstrument(button) {
        button.closest('.instrument').remove();
    }

    // --- JavaScript for Source Files ---
    function addFileInput() {
        const container = document.getElementById('file-inputs-container');
        const input = document.createElement('input');
        input.type = 'file';
        input.name = 'new_source_files[]'; // Name changed to distinguish from existing files
        input.multiple = true;
        input.onchange = updateFileList;
        container.appendChild(input);

        // Add a visual indicator or a "remove" button for this new input field if desired
        // For simplicity, just adding the input here.
    }

    function updateFileList() {
        // This function now tracks *newly selected files* from the dynamic inputs.
        // It does not track existing files from the database.
        const inputs = document.querySelectorAll('input[type="file"][name="new_source_files[]"]');
        const list = document.getElementById('selected-files-list');
        list.innerHTML = ''; // Clear previous list
        inputs.forEach(input => {
            if (input.files.length > 0) {
                for (const file of input.files) {
                    const li = document.createElement('li');
                    li.textContent = file.name;
                    list.appendChild(li);
                }
            }
        });
    }

    // Attach updateFileList to the initial input only if it exists (for new project creation)
    // For edit page, it's safer to ensure this input is present and attach
    const initialFileInput = document.querySelector('input[type="file"][name="new_source_files[]"]');
    if (initialFileInput) {
        initialFileInput.onchange = updateFileList;
    }

    // --- Initialize the form with existing data when editing ---
    // If you pre-fill instruments dynamically, you might not need this.
    // If the PHP loop already generates the initial instruments, this is fine.
    // Example of how you *would* programmatically add them if not done by PHP:
    /*
    document.addEventListener('DOMContentLoaded', () => {
        // Assuming `existingInstruments` is a JS array if loaded from PHP
        // This part is likely not needed if PHP already renders them.
        // if (typeof existingInstruments !== 'undefined' && existingInstruments.length > 0) {
        //     existingInstruments.forEach(instrument => {
        //         addInstrument(instrument.name, instrument.type, instrument.description, instrument.access);
        //     });
        // }
    });
    */
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>