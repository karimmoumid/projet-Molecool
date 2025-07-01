// Sélection des éléments du DOM
const date = document.querySelector('#day'); // Sélection de l'élément de date
const button = document.querySelector('button'); // Sélection du bouton
const patient = document.querySelector('#patient-name'); // Sélection de l'élément du nom du patient

// Fonction pour afficher tous les rendez-vous
function showAllAppointments() {
    // Sélection de tous les articles de rendez-vous
    const appointments = document.querySelectorAll(".appointment article");

    // Parcours de chaque rendez-vous
    for (let appointment of appointments) {
        // Suppression des classes 'hidden' et 'hidden_patient' pour afficher les rendez-vous
        appointment.classList.remove('hidden');
        appointment.classList.remove('hidden_patient');

        // Sélection de tous les éléments de la liste avec l'attribut data-name
        const items = appointment.querySelectorAll('li[data-name]');

        // Parcours de chaque élément de la liste
        for (let item of items) {
            // Suppression de la classe 'hidden_patient' pour afficher les éléments
            item.closest('.box').classList.remove('hidden_patient');
        }
    }
}

// Écouteur d'événement pour le changement de date
date.addEventListener('change', function () {
    // Sélection de tous les articles de rendez-vous
    const appointments = document.querySelectorAll(".appointment article");
    let date = this.value; // Récupération de la valeur de la date sélectionnée

    // Parcours de chaque rendez-vous
    for (let appointment of appointments) {
        // Récupération de la date du rendez-vous
        let appointmentDate = appointment.getAttribute('data-id');

        // Comparaison de la date du rendez-vous avec la date sélectionnée
        if (appointmentDate === date) {
            // Suppression de la classe 'hidden' pour afficher le rendez-vous
            appointment.classList.remove('hidden');
        } else {
            // Ajout de la classe 'hidden' pour masquer le rendez-vous
            appointment.classList.add('hidden');
        }
    }
});

// Écouteur d'événement pour le clic sur le bouton
button.addEventListener('click', function (e) {
    e.preventDefault(); // Empêche le comportement par défaut du bouton
    showAllAppointments(); // Appel de la fonction pour afficher tous les rendez-vous

    // Réinitialisation des filtres
    date.value = ""; // Réinitialisation de la valeur de la date
    patient.value = ""; // Réinitialisation de la valeur du nom du patient
});

// Écouteur d'événement pour le changement du nom du patient
patient.addEventListener('change', function () {
    // Sélection de tous les articles de rendez-vous
    const appointments = document.querySelectorAll(".appointment article");
    let name = this.value; // Récupération de la valeur du nom du patient sélectionné

    // Parcours de chaque rendez-vous
    for (let appointment of appointments) {
        // Sélection de tous les éléments de la liste avec l'attribut data-name
        const items = appointment.querySelectorAll('li[data-name]');
        let hasVisiblePatient = false; // Variable pour vérifier si un patient correspondant est trouvé

        // Parcours de chaque élément de la liste
        for (let item of items) {
            // Comparaison du nom du patient avec le nom sélectionné
            if (item.getAttribute('data-name') === name) {
                // Suppression de la classe 'hidden_patient' pour afficher l'élément
                item.closest('.box').classList.remove('hidden_patient');
                hasVisiblePatient = true; // Un patient correspondant a été trouvé
            } else {
                // Ajout de la classe 'hidden_patient' pour masquer l'élément
                item.closest('.box').classList.add('hidden_patient');
            }
        }

        // Si aucun patient ne correspond dans cet article, on le cache
        if (hasVisiblePatient) {
            // Suppression des classes 'hidden_patient' pour afficher l'article
            appointment.classList.remove('hidden_patient');
            appointment.classList.remove('hidden_patient');
        } else {
            // Ajout des classes 'hidden_patient' pour masquer l'article
            appointment.classList.add('hidden_patient');
            appointment.classList.add('hidden_patient');
        }
    }
});
