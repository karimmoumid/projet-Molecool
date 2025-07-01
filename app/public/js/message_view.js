// Sélection de l'élément d'entrée de fichier avec l'ID 'answer_form_files'
const inputFile = document.querySelector('#answer_form_files');

// Sélection du conteneur pour afficher les noms des fichiers sélectionnés
const fileNamesContainer = document.getElementById('file-names');

// Ajout d'un écouteur d'événement pour le changement de sélection de fichiers
inputFile.addEventListener('change', () => {
    // Récupération de la liste des fichiers sélectionnés
    const files = inputFile.files;

    // Vérification si aucun fichier n'est sélectionné
    if (files.length === 0) {
        // Affichage d'un message indiquant qu'aucun fichier n'est sélectionné
        fileNamesContainer.textContent = 'Aucun fichier sélectionné';
        return;
    }

    // Création d'un tableau des noms de fichiers à partir de la liste des fichiers
    const names = Array.from(files).map(file => file.name);

    // Affichage des noms des fichiers sélectionnés, séparés par des virgules
    fileNamesContainer.textContent = names.join(', ');
});
