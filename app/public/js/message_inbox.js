// Sélection de tous les éléments avec la classe 'favorite-toggle'
const toggles = document.querySelectorAll('.favorite-toggle');

// Sélection de tous les éléments avec la classe 'delete-button'
const deleteButtons = document.querySelectorAll('.delete-button');

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
                    button.textContent = data.isFavorite ? '❤️' : '🤍';
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

// Parcours de chaque bouton de suppression
for (let button of deleteButtons) {
    // Ajout d'un écouteur d'événement pour le clic sur chaque bouton
    button.addEventListener('click', function(e) {
        // Empêche le comportement par défaut du bouton
        e.preventDefault();

        // Récupère l'ID du message à partir de l'attribut 'data-id'
        const messageId = button.dataset.id;

        // Récupère le token CSRF à partir de l'attribut 'data-csrf'
        const csrfToken = button.dataset.csrf;

        // Demande confirmation avant de supprimer le message
        if (!confirm("Voulez-vous vraiment supprimer ce message ?")) return;

        // Envoie une requête POST pour supprimer le message
        fetch(`/messages/${messageId}/delete`, {
            method: 'POST', // Méthode HTTP POST
            headers: {
                'Content-Type': 'application/json', // Type de contenu de la requête
                'X-Requested-With': 'XMLHttpRequest', // Indique que la requête est une requête AJAX
                'X-CSRF-TOKEN': csrfToken // Token CSRF pour la sécurité
            },
            body: JSON.stringify({}) // Corps de la requête vide
        })
            .then(function(response) {
                // Vérifie si la réponse est OK, sinon lève une erreur
                if (!response.ok) {
                    throw new Error('Échec de la requête');
                }
                // Convertit la réponse en JSON
                return response.json();
            })
            .then(function(data) {
                // Vérifie si la suppression a réussi
                if (data.success) {
                    // Supprime l'élément du message de la liste
                    button.closest('.message-item').remove();
                } else {
                    // Affiche une alerte en cas d'erreur lors de la suppression
                    alert("Erreur : " + (data.error || "Suppression impossible"));
                }
            })
            .catch(function(error) {
                // Affiche une erreur dans la console et une alerte en cas d'échec de la requête
                console.error('Erreur fetch :', error);
                alert("Une erreur est survenue.");
            });
    });
}
