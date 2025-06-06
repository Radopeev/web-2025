<?php include __DIR__ . '/partials/header.php'; ?>

<form action="/upload" method="POST" enctype="multipart/form-data">
    <h2>Upload New Project</h2>

    <input type="text" name="title" placeholder="Project Title" required><br>
    <textarea name="description" placeholder="Project Description"></textarea><br>

    <label>Source Files (multiple):</label>
    <input type="file" name="source_files[]" multiple id="source-files-input"><br>
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

document.getElementById('source-files-input').addEventListener('change', function() {
    const list = document.getElementById('selected-files-list');
    list.innerHTML = '';
    for (const file of this.files) {
        const li = document.createElement('li');
        li.textContent = file.name;
        list.appendChild(li);
    }
});
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
