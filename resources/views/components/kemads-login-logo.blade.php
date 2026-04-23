<style>
    /* Oculta el logo por defecto de Filament */
    .fi-logo { display: none !important; }

    /* Lógica de visibilidad modo claro/oscuro */
    .kemads-logo-dark, .kemads-logo-light { display: none; }
    .dark .kemads-logo-dark { display: block; }
    html:not(.dark) .kemads-logo-light { display: block; }

    .kemads-login-wrap {
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        /* Ajustamos el margen negativo para compensar el espacio vacío del PNG */
        margin-top: -20px; 
        margin-bottom: -10px;
        overflow: hidden;
    }

    .kemads-login-wrap img {
        /* Aumentamos la altura para compensar el "aire" de tus imágenes */
        height: 180px; 
        width: auto;
        object-fit: contain;
        /* Este scale hace que el logo se vea más grande sin que el contenedor crezca demasiado */
        transform: scale(1.4); 
    }
</style>

<div class="kemads-login-wrap">
    <img
        src="{{ asset('images/kemads-login-dark.png') }}"
        alt="KEMADS"
        class="kemads-logo-dark"
    >
    <img
        src="{{ asset('images/kemads-login-light.png') }}"
        alt="KEMADS"
        class="kemads-logo-light"
    >
</div>