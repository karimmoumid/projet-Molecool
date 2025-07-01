// Sélection de tous les éléments avec la classe 'favorite-toggle'
const toggles = document.querySelectorAll('.favorite-toggle');

// Parcours de chaque bouton de basculement de favori
for (let button of toggles) {
    // Ajout d'un écouteur d'événement pour le clic sur chaque bouton
    button.addEventListener('click', function(event) {
        // Empêche le comportement par défaut du bouton
        event.preventDefault();

        // Trouve l'élément parent 'li' avec l'attribut 'data-message-id'
        const li = button.closest('li[data-message-id]');

        // Récupère l'ID du message à partir de l'attribut 'data-message-id'
        const messageId = li.getAttribute('data-message-id');

        // Envoie une requête POST pour basculer l'état de favori du message
        fetch(`/messages/toggle-favorite/${messageId}`, {
            method: 'POST' // Méthode HTTP POST
        })
            .then(function(response) {
                // Vérifie si la réponse est OK, sinon lève une erreur
                if (!response.ok) {
                    throw new Error('Erreur réseau');
                }
                // Convertit la réponse en JSON
                return response.json();
            })
            .then(function(data) {
                // Vérifie si la requête a réussi
                if (data.success) {
                    // Met à jour l'icône du bouton en fonction de l'état de favori
                    if (data.isFavorite) {
                        button.textContent = '🟢'; // Change le texte du bouton pour indiquer qu'il est favori
                    } else {
                        li.remove(); // Supprime le message de la liste s'il n'est plus favori
                    }
                } else {
                    // Affiche une alerte en cas d'erreur lors de la mise à jour du favori
                    alert('Erreur lors de la mise à jour du favori');
                }
            })
            .catch(function(error) {
                // Affiche une erreur dans la console et une alerte en cas d'échec de la requête
                console.error('Erreur fetch:', error);
                alert('Erreur lors de la requête');
            });
    });
}
