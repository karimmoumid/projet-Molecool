// Sélection de l'élément SVG dans l'en-tête, qui représente probablement l'icône du menu "burger"
let burger = document.querySelector('header svg');

// Ajout d'un écouteur d'événement pour le clic sur l'icône du menu "burger"
burger.addEventListener('click', function() {
    // Sélection de l'élément précédent, qui est probablement la liste du menu
    let list = this.previousElementSibling;

    // Vérification si la liste contient la classe 'hidden'
    if (list.classList.contains('hidden')) {
        // Si la liste est cachée, on retire la classe 'hidden' pour l'afficher
        list.classList.remove('hidden');
    } else {
        // Si la liste est visible, on ajoute la classe 'hidden' pour la cacher
        list.classList.add('hidden');
    }
});
