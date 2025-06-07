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

    document.getElementById('source_files').addEventListener('change', function (event) {
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