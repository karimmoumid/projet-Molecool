// Écouteur d'événement pour s'assurer que le DOM est complètement chargé avant d'exécuter le script
document.addEventListener('DOMContentLoaded', function () {
    // Récupération des éléments du DOM
    const dateInput = document.getElementById('appointment_form_date');
    const timeSelect = document.getElementById('time_select');
    const hiddenTimeInput = document.getElementById('appointment_form_time');

    // Vérification de l'existence des éléments
    if (!dateInput || !timeSelect || !hiddenTimeInput) return;

    // Configuration de l'internationalisation pour le sélecteur de date
    const i18n = {
        previousMonth: 'Mois précédent',
        nextMonth: 'Mois suivant',
        months: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
        weekdays: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
        weekdaysShort: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam']
    };

    // Fonction pour gérer la sélection de la date
    function handleDateSelection(date) {
        // Vérification de la validité de la date
        if (!(date instanceof Date) || isNaN(date)) {
            console.warn('Date invalide:', date);
            return;
        }

        // Formatage de la date en jour/mois/année
        const day = ('0' + date.getDate()).slice(-2);
        const month = ('0' + (date.getMonth() + 1)).slice(-2);
        const year = date.getFullYear();
        dateInput.value = `${day}/${month}/${year}`;

        // Formatage de la date en format ISO pour l'API
        const dateISO = `${year}-${month}-${day}`;

        // Réinitialisation de la valeur de l'heure cachée et des options de sélection de l'heure
        hiddenTimeInput.value = '';
        while (timeSelect.options.length > 1) {
            timeSelect.remove(1);
        }

        // Désactivation de la sélection de l'heure pendant le chargement
        timeSelect.disabled = true;

        // Récupération des créneaux disponibles pour la date sélectionnée
        fetch('/api/available-slots?date=' + dateISO)
            .then(response => {
                if (!response.ok) throw new Error('Erreur réseau');
                return response.json();
            })
            .then(data => {
                // Ajout des créneaux disponibles comme options dans le sélecteur de temps
                data.forEach(slot => {
                    const option = document.createElement('option');
                    option.value = slot.value;
                    option.textContent = slot.label;
                    timeSelect.appendChild(option);
                });
                // Activation ou désactivation du sélecteur de temps en fonction des créneaux disponibles
                timeSelect.disabled = data.length === 0;
            })
            .catch(err => {
                console.error('Erreur lors de la récupération des créneaux:', err);
                timeSelect.disabled = true;
            });
    }

    // Initialisation du sélecteur de date Pikaday
    const picker = new Pikaday({
        field: dateInput,
        format: 'DD/MM/YYYY',
        i18n: i18n,
        minDate: new Date(),
        disableDayFn: function(date) {
            // Désactivation des dimanches
            return date.getDay() === 0;
        },
        onSelect: handleDateSelection,
        onOpen: function() {
            // Gestion de l'ouverture du sélecteur de date si une date est pré-remplie
            if (dateInput.value) {
                const parts = dateInput.value.split('/');
                if (parts.length === 3) {
                    const d = new Date(parts[2], parts[1] - 1, parts[0]);
                    handleDateSelection(d);
                }
            }
        }
    });

    // Écouteur d'événement pour la sélection de l'heure
    timeSelect.addEventListener('change', function() {
        hiddenTimeInput.value = this.value;
    });

    // Appel initial si une date est pré-remplie
    if (dateInput.value) {
        const parts = dateInput.value.split('/');
        if (parts.length === 3) {
            const d = new Date(parts[2], parts[1] - 1, parts[0]);
            handleDateSelection(d);
        }
    }
});
