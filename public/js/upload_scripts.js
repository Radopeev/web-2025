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