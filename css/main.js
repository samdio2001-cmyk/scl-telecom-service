/* =====================================
   SCROLL SUAVE PARA ENLACES INTERNOS
===================================== */
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth'
            });
        }
    });
});

/* =====================================
   EFECTO AL CARGAR LA PÁGINA
===================================== */
window.addEventListener('load', () => {
    document.body.classList.add('loaded');
});

/* =====================================
   ANIMACIÓN AL HACER SCROLL (REVEAL)
===================================== */
const reveals = document.querySelectorAll('.reveal');

function revealOnScroll() {
    const windowHeight = window.innerHeight;
    const revealPoint = 100;

    reveals.forEach(element => {
        const elementTop = element.getBoundingClientRect().top;

        if (elementTop < windowHeight - revealPoint) {
            element.classList.add('active');
        }
    });
}

window.addEventListener('scroll', revealOnScroll);
revealOnScroll(); // Ejecutar al cargar

