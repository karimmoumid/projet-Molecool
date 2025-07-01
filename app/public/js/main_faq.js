// Sélection de tous les éléments avec la classe 'faq-question'
const questions = document.querySelectorAll('.faq-question');

// Parcours de chaque élément sélectionné
for (const button of questions) {
    // Ajout d'un écouteur d'événement pour le clic sur chaque question
    button.addEventListener('click', () => {
        // Sélection de l'élément suivant, qui est probablement la réponse à la question
        const answer = button.nextElementSibling;

        // Vérification si la réponse contient la classe 'hidden'
        if (answer.classList.contains('hidden')) {
            // Si la réponse est cachée, on retire la classe 'hidden' pour l'afficher
            answer.classList.remove('hidden');
        } else {
            // Si la réponse est visible, on ajoute la classe 'hidden' pour la cacher
            answer.classList.add('hidden');
        }
    });
}
