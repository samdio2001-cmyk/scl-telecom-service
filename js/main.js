/* =========================
   SCROLL SUAVE (ENLACES INTERNOS)
========================= */
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            e.preventDefault();
            target.scrollIntoView({
                behavior: 'smooth'
            });
        }
    });
});

/* =========================
   ANIMACIONES AL HACER SCROLL
========================= */
const revealElements = document.querySelectorAll(".reveal");

function revealOnScroll() {
    const windowHeight = window.innerHeight;

    revealElements.forEach(el => {
        const elementTop = el.getBoundingClientRect().top;

        if (elementTop < windowHeight - 120) {
            el.classList.add("active");
        }
    });
}

window.addEventListener("scroll", revealOnScroll);
window.addEventListener("load", revealOnScroll);

/* =========================
   NAVBAR: CERRAR MENÚ EN MÓVIL
========================= */
const navLinks = document.querySelectorAll(".navbar-nav .nav-link");
const navbarCollapse = document.querySelector(".navbar-collapse");

navLinks.forEach(link => {
    link.addEventListener("click", () => {
        if (navbarCollapse.classList.contains("show")) {
            new bootstrap.Collapse(navbarCollapse).hide();
        }
    });
});

/* =========================
   EFECTO DE CARGA DE PÁGINA
========================= */
window.addEventListener("load", () => {
    document.body.classList.add("page-loaded");
});

/* =========================
   PROTECCIÓN BÁSICA (OPCIONAL)
   Evita errores si algún elemento no existe
========================= */
window.addEventListener("error", function (e) {
    console.warn("Advertencia controlada:", e.message);
});
