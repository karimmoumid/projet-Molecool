const toggles = document.querySelectorAll('.favorite-toggle');

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
                    if (data.isFavorite) {
                        button.textContent = '🟢';
                    } else {
                        li.remove(); // supprime le message de la liste
                    }
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
