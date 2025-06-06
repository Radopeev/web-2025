<?php include __DIR__ . '/partials/header.php'; ?>

<form action="/upload" method="POST" enctype="multipart/form-data">
    <h2>Upload New Project</h2>

    <input type="text" name="title" placeholder="Project Title" required><br>
    <textarea name="description" placeholder="Project Description"></textarea><br>

    <label>Source Files:</label>
    <div id="file-inputs-container">
        <input type="file" name="source_files[]" multiple>
    </div>
    <button type="button" onclick="addFileInput()">Add Another File</button>
    <ul id="selected-files-list"></ul>

    <label>Configuration File:</label>
    <input type="file" name="config_file"><br>

    <h3>Instruments</h3>
    <div id="instruments-container">
        <!-- No initial instrument fields -->
    </div>
    <button type="button" onclick="addInstrument()">Add Instrument</button><br>

    <input type="submit" value="Upload Project">
</form>

<script>
function addInstrument() {
    const container = document.getElementById('instruments-container');
    const instrumentHTML = `
    <div class="instrument">
        <input type="text" name="instrument_name[]" placeholder="Name">
        <input type="text" name="instrument_type[]" placeholder="Type">
        <input type="text" name="instrument_description[]" placeholder="Description">
        <input type="url" name="instrument_access[]" placeholder="Access URL">
    </div>`;
    container.insertAdjacentHTML('beforeend', instrumentHTML);
}

function addFileInput() {
    const container = document.getElementById('file-inputs-container');
    const input = document.createElement('input');
    input.type = 'file';
    input.name = 'source_files[]';
    input.multiple = true;
    input.onchange = updateFileList;
    container.appendChild(input);
}

function updateFileList() {
    const inputs = document.querySelectorAll('input[type="file"][name="source_files[]"]');
    const list = document.getElementById('selected-files-list');
    list.innerHTML = '';
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

// Attach updateFileList to the initial input
document.querySelector('input[type="file"][name="source_files[]"]').onchange = updateFileList;
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
