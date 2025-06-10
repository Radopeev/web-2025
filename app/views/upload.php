<?php include __DIR__ . '/partials/header.php'; ?>

<link rel="stylesheet" href="/public/styles/upload_page_styles.css">

<main class="upload-main">
    <form action="/upload" method="POST" enctype="multipart/form-data" class="upload-form">
        <h2>Upload New Project</h2>

        <label for="title">Project Title:</label>
        <input type="text" id="title" name="title" placeholder="Project Title" required>

        <label for="description">Project Description:</label>
        <textarea id="description" name="description" placeholder="Project Description"></textarea>

        <label for="source_files">Source Files:</label>
        <input type="file" id="source_files" name="source_files[]" multiple>
        <ul id="selected-files-list"></ul>

        <label for="directory_files">Or Select a Directory:</label>
        <input type="file" id="directory_files" name="directory_files[]" webkitdirectory directory multiple>

        <label for="config_file">Configuration File:</label>
        <input type="file" id="config_file" name="config_file">

        <h3>Instruments</h3>
        <div id="instruments-container"></div>
        <button type="button" class="add-instrument-btn" onclick="addInstrument()">Add Instrument</button>

        <input type="submit" value="Upload Project" class="submit-btn">
    </form>
</main>

<script src="/public/js/upload_scripts.js"></script>

<?php include __DIR__ . '/partials/footer.php'; ?>