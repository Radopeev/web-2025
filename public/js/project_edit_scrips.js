function addInstrument(name = '', type = '', description = '', access_link = '') {
    const container = document.getElementById('instruments-container');
    const instrumentHTML = `
    <div class="instrument">
        <input type="hidden" name="instrument_id[]" value=""> <input type="text" name="instrument_name[]" placeholder="Name" value="${name}">
        <input type="text" name="instrument_type[]" placeholder="Type" value="${type}">
        <input type="text" name="instrument_description[]" placeholder="Description" value="${description}">
        <input type="url" name="instrument_access[]" placeholder="Access URL" value="${access_link}">
        <button type="button" onclick="removeInstrument(this)">Remove</button>
    </div>`;
    container.insertAdjacentHTML('beforeend', instrumentHTML);
}

function removeInstrument(button) {
    button.closest('.instrument').remove();
}

function addFileInput() {
    const container = document.getElementById('file-inputs-container');
    const input = document.createElement('input');
    input.type = 'file';
    input.name = 'new_source_files[]';
    input.multiple = true;
    input.onchange = updateFileList;
    container.appendChild(input);
}

function updateFileList() {
    const inputs = document.querySelectorAll('input[type="file"][name="new_source_files[]"]');
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

const initialFileInput = document.querySelector('input[type="file"][name="new_source_files[]"]');
if (initialFileInput) {
    initialFileInput.onchange = updateFileList;
}