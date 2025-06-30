
    const inputFile = document.querySelector('#answer_form_files')
    const fileNamesContainer = document.getElementById('file-names');

    inputFile.addEventListener('change', () => {
    const files = inputFile.files;
    if (files.length === 0) {
    fileNamesContainer.textContent = 'Aucun fichier sélectionné';
    return;
}

    const names = Array.from(files).map(file => file.name);
    fileNamesContainer.textContent = names.join(', ');
});

