// Chat Flotante WhatsApp
document.addEventListener('DOMContentLoaded', function() {
    const whatsappBubble = document.querySelector('.whatsapp-bubble');
    const whatsappPopup = document.querySelector('.whatsapp-popup');
    const closeBtn = document.querySelector('.whatsapp-close');
    const btnContactar = document.querySelector('.whatsapp-btn-primary');

    // Abrir/Cerrar popup al hacer clic en el bubble
    if (whatsappBubble) {
        whatsappBubble.addEventListener('click', function() {
            whatsappPopup.classList.toggle('active');
        });
    }

    // Cerrar popup al hacer clic en el botón X
    if (closeBtn) {
        closeBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            whatsappPopup.classList.remove('active');
        });
    }

    // Cerrar popup si se hace clic fuera de él
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.whatsapp-container')) {
            whatsappPopup.classList.remove('active');
        }
    });

    // Abrir WhatsApp al hacer clic en botón de contacto
    if (btnContactar) {
        btnContactar.addEventListener('click', function(e) {
            e.preventDefault();
            // Número sin + ni espacios
            const phoneNumber = '5077589716';
            const message = '¡Hola! Me gustaría solicitar información sobre tus servicios.';
            const url = `https://wa.me/${phoneNumber}?text=${encodeURIComponent(message)}`;
            window.open(url, '_blank');
        });
    }

    // Auto-abrir popup después de 3 segundos (opcional)
    setTimeout(function() {
        if (whatsappPopup && window.innerWidth > 768) {
            // Comentar esta línea si no quieres que se abra automáticamente
            // whatsappPopup.classList.add('active');
        }
    }, 3000);
});
