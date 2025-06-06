<?php include __DIR__ . '/partials/header.php'; ?>

<form action="/upload" method="POST" enctype="multipart/form-data">
    <h2>Upload New Project</h2>

    <input type="text" name="title" placeholder="Project Title" required><br>
    <textarea name="description" placeholder="Project Description"></textarea><br>

    <label>Source Files (multiple):</label>
    <input type="file" name="source_files[]" multiple><br>

    <label>Configuration File:</label>
    <input type="file" name="config_file"><br>

    <h3>Instruments</h3>
    <div id="instruments-container">
        <div class="instrument">
            <input type="text" name="instrument_name[]" placeholder="Name" required>
            <input type="text" name="instrument_type[]" placeholder="Type">
            <input type="text" name="instrument_description[]" placeholder="Description">
            <input type="url" name="instrument_access[]" placeholder="Access URL">
        </div>
    </div>
    <button type="button" onclick="addInstrument()">Add Another Instrument</button><br>

    <input type="submit" value="Upload Project">
</form>

<script>
function addInstrument() {
    const container = document.getElementById('instruments-container');
    const instrumentHTML = `
    <div class="instrument">
        <input type="text" name="instrument_name[]" placeholder="Name" required>
        <input type="text" name="instrument_type[]" placeholder="Type">
        <input type="text" name="instrument_description[]" placeholder="Description">
        <input type="url" name="instrument_access[]" placeholder="Access URL">
    </div>`;
    container.insertAdjacentHTML('beforeend', instrumentHTML);
}
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
