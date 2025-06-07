<?php include __DIR__ . '/partials/header.php'; ?>

<form action="/upload" method="POST" enctype="multipart/form-data">
    <h2>Upload New Project</h2>

    <input type="text" name="title" placeholder="Project Title" required><br>
    <textarea name="description" placeholder="Project Description"></textarea><br>

    <label>Source Files:</label>
    <input type="file" id="source_files" name="source_files[]" multiple><br>
    <ul id="selected-files-list"></ul>

    <label>Or Select a Directory:</label>
    <input type="file" id="directory_files" name="directory_files[]" webkitdirectory directory multiple><br>

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

document.getElementById('source_files').addEventListener('change', function(event) {
    const files = event.target.files;
    const list = document.getElementById('selected-files-list');
    list.innerHTML = '';
    for (const file of files) {
        const li = document.createElement('li');
        li.textContent = file.name;
        list.appendChild(li);
    }
});
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
