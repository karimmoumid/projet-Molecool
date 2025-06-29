const track = document.querySelector('.carousel-track');
const slides = document.querySelectorAll('.carousel-slide');
const nextBtn = document.querySelector('.next');
const prevBtn = document.querySelector('.prev');

let currentIndex = 0;
const slideCount = slides.length;

function updateCarousel() {
    const offset = -currentIndex * 100;
    track.style.transform = `translateX(${offset}%)`;
}

nextBtn.addEventListener('click', () => {
    currentIndex = (currentIndex + 1) % slideCount;
    updateCarousel();
});

prevBtn.addEventListener('click', () => {
    currentIndex = (currentIndex - 1 + slideCount) % slideCount;
    updateCarousel();
});

// Auto slide
setInterval(() => {
    currentIndex = (currentIndex + 1) % slideCount;
    updateCarousel();
}, 10000); // change every 5 seconds
