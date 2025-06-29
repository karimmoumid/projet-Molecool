const toggles = document.querySelectorAll('.favorite-toggle');
const deleteButtons = document.querySelectorAll('.delete-button');

    for(let button of toggles) {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            const li = button.closest('li[data-message-id]');
            const messageId = li.getAttribute('data-message-id');

            fetch(`/messages/toggle-favorite/${messageId}`, {
                method: 'POST'
            })
                .then(function(response) {
                    if (!response.ok) {
                        throw new Error('Erreur réseau');
                    }
                    return response.json();
                })
                .then(function(data) {
                    if (data.success) {
                        button.textContent = data.isFavorite ? '❤️' : '🤍';
                    } else {
                        alert('Erreur lors de la mise à jour du favori');
                    }
                })
                .catch(function(error) {
                    console.error('Erreur fetch:', error);
                    alert('Erreur lors de la requête');
                });
        });
    }

    for(let button of deleteButtons ){
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const messageId = button.dataset.id;
            const csrfToken = button.dataset.csrf;

            if (!confirm("Voulez-vous vraiment supprimer ce message ?")) return;

            fetch(`/messages/${messageId}/delete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({})
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Échec de la requête');
                    }
                    return response.json();
                })
                .then(function (data) {
                    if (data.success) {
                        button.closest('.message-item').remove();
                    } else {
                        alert("Erreur : " + (data.error || "Suppression impossible"));
                    }
                })
                .catch(function (error) {
                    console.error('Erreur fetch :', error);
                    alert("Une erreur est survenue.");
                });
        });
    }

