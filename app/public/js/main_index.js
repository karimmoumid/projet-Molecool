// Sélection des éléments du DOM nécessaires pour le carrousel
const track = document.querySelector('.carousel-track'); // Conteneur des diapositives du carrousel
const slides = document.querySelectorAll('.carousel-slide'); // Liste des diapositives du carrousel
const nextBtn = document.querySelector('.next'); // Bouton pour passer à la diapositive suivante
const prevBtn = document.querySelector('.prev'); // Bouton pour revenir à la diapositive précédente

let currentIndex = 0; // Index de la diapositive actuellement affichée
const slideCount = slides.length; // Nombre total de diapositives

// Fonction pour mettre à jour l'affichage du carrousel
function updateCarousel() {
    // Calcul du décalage en pourcentage pour le déplacement du conteneur des diapositives
    const offset = -currentIndex * 100;
    // Application du décalage pour déplacer le conteneur des diapositives
    track.style.transform = `translateX(${offset}%)`;
}

// Écouteur d'événement pour le bouton "suivant"
nextBtn.addEventListener('click', () => {
    // Incrémentation de l'index courant avec gestion du dépassement
    currentIndex = (currentIndex + 1) % slideCount;
    // Mise à jour de l'affichage du carrousel
    updateCarousel();
});

// Écouteur d'événement pour le bouton "précédent"
prevBtn.addEventListener('click', () => {
    // Décrémentation de l'index courant avec gestion du dépassement
    currentIndex = (currentIndex - 1 + slideCount) % slideCount;
    // Mise à jour de l'affichage du carrousel
    updateCarousel();
});

// Défilement automatique des diapositives
setInterval(() => {
    // Incrémentation de l'index courant avec gestion du dépassement
    currentIndex = (currentIndex + 1) % slideCount;
    // Mise à jour de l'affichage du carrousel
    updateCarousel();
}, 10000); // Changement toutes les 10 secondes
