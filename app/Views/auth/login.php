<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Enterprise SST</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(to top, #0f0c29 0%, #302b63 30%, #24243e 50%, #4a3f6b 70%, #ff6b6b 85%, #ffc371 95%, #ffe259 100%);
            height: 100vh;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            animation: backgroundShift 20s ease-in-out infinite;
        }

        @keyframes backgroundShift {
            0%, 100% {
                background: linear-gradient(to top, #0f0c29 0%, #302b63 30%, #24243e 50%, #4a3f6b 70%, #ff6b6b 85%, #ffc371 95%, #ffe259 100%);
            }
            50% {
                background: linear-gradient(to top, #1a1a2e 0%, #16213e 30%, #1f3a5f 50%, #5d4e7a 70%, #ff8e6b 85%, #ffd371 95%, #fff259 100%);
            }
        }

        /* Estrellas en el firmamento */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 70%;
            pointer-events: none;
            z-index: 1;
        }

        .particle {
            position: absolute;
            width: 3px;
            height: 3px;
            background: #ffffff;
            border-radius: 50%;
            animation: twinkle 3s ease-in-out infinite;
            box-shadow: 0 0 6px #ffffff, 0 0 12px rgba(255, 255, 255, 0.5);
        }

        .particle.large {
            width: 4px;
            height: 4px;
            box-shadow: 0 0 8px #ffffff, 0 0 16px rgba(255, 255, 255, 0.7);
        }

        .particle.bright {
            width: 5px;
            height: 5px;
            background: #fffacd;
            box-shadow: 0 0 10px #fffacd, 0 0 20px rgba(255, 250, 205, 0.8), 0 0 30px rgba(255, 215, 0, 0.4);
        }

        @keyframes twinkle {
            0%, 100% {
                opacity: 0.3;
                transform: scale(1);
            }
            50% {
                opacity: 1;
                transform: scale(1.2);
            }
        }

        /* Horizonte del amanecer */
        .waves {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 35%;
            background: linear-gradient(to top,
                rgba(255, 140, 0, 0.4) 0%,
                rgba(255, 99, 71, 0.3) 30%,
                rgba(255, 165, 0, 0.2) 60%,
                transparent 100%
            );
            z-index: 1;
        }

        .waves::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(ellipse at 50% 100%,
                rgba(255, 200, 100, 0.5) 0%,
                rgba(255, 140, 0, 0.3) 30%,
                transparent 70%
            );
            animation: sunGlow 8s ease-in-out infinite;
        }

        @keyframes sunGlow {
            0%, 100% {
                opacity: 0.6;
                transform: scale(1);
            }
            50% {
                opacity: 1;
                transform: scale(1.1);
            }
        }

        /* Container principal con glassmorphism */
        .main-container {
            position: relative;
            z-index: 10;
            background: linear-gradient(145deg,
                rgba(255, 255, 255, 0.85) 0%,
                rgba(255, 200, 150, 0.15) 25%,
                rgba(255, 255, 255, 0.9) 50%,
                rgba(255, 180, 100, 0.1) 75%,
                rgba(255, 255, 255, 0.85) 100%
            );
            backdrop-filter: blur(25px);
            border-radius: 25px;
            border: 2px solid rgba(255, 180, 100, 0.4);
            box-shadow:
                0 25px 50px rgba(0, 0, 0, 0.3),
                inset 0 1px 0 rgba(255, 200, 150, 0.3),
                0 0 40px rgba(255, 140, 0, 0.2);
            overflow: hidden;
            max-width: 1000px;
            width: 90%;
            min-height: 600px;
            animation: fadeInUp 1s ease-out;
            display: flex;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(50px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Panel de logos con efectos */
        .logos-panel {
            background: linear-gradient(45deg,
                rgba(15, 12, 41, 0.95) 0%,
                rgba(48, 43, 99, 0.9) 40%,
                rgba(74, 63, 107, 0.85) 70%,
                rgba(255, 140, 0, 0.6) 100%
            );
            width: 45%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            position: relative;
            overflow: hidden;
        }

        .logos-panel::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg,
                transparent,
                rgba(255, 200, 100, 0.2),
                transparent,
                rgba(255, 140, 0, 0.15),
                transparent
            );
            animation: shimmer 4s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        .logo-container {
            position: relative;
            z-index: 2;
            text-align: center;
        }

        .logo-image-wrapper {
            position: relative;
            margin: 15px auto;
            animation: logoFloat 3s ease-in-out infinite;
            transform-style: preserve-3d;
            transition: all 0.3s ease;
            padding: 15px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3), 0 0 15px rgba(255, 180, 100, 0.3);
            max-width: 200px;
            border: 1px solid rgba(255, 180, 100, 0.4);
        }

        .logo-image-wrapper:nth-child(2) {
            animation-delay: 0.5s;
        }

        .logo-image-wrapper:nth-child(3) {
            animation-delay: 1s;
        }

        .logo-image-wrapper:hover {
            transform: translateY(-10px) rotateY(10deg);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .logo-image {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.1));
            transition: all 0.3s ease;
        }

        .logo-image-wrapper:hover .logo-image {
            filter: drop-shadow(0 8px 16px rgba(0, 0, 0, 0.2)) brightness(1.1);
        }

        @keyframes logoFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        /* Panel de login */
        .login-panel {
            width: 55%;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: linear-gradient(135deg,
                rgba(255, 255, 255, 0.95) 0%,
                rgba(255, 220, 180, 0.1) 25%,
                rgba(255, 255, 255, 0.92) 50%,
                rgba(255, 180, 100, 0.08) 75%,
                rgba(255, 255, 255, 0.95) 100%
            );
            backdrop-filter: blur(15px);
            position: relative;
        }

        .login-panel::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg,
                transparent 30%,
                rgba(255, 180, 100, 0.05) 50%,
                transparent 70%
            );
            pointer-events: none;
            animation: panelShimmer 6s ease-in-out infinite;
        }

        @keyframes panelShimmer {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 1; }
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-title {
            font-size: 2.2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #302b63, #4a3f6b, #ff8c00);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
            animation: titleGlow 2s ease-in-out infinite alternate;
        }

        @keyframes titleGlow {
            0% { filter: brightness(1); }
            100% { filter: brightness(1.2); }
        }

        .login-subtitle {
            color: #1c2437;
            font-size: 1.1rem;
            opacity: 0;
            animation: fadeIn 1s ease-out 0.5s forwards;
        }

        @keyframes fadeIn {
            to { opacity: 1; }
        }

        /* Efectos de formulario */
        .form-group {
            position: relative;
            margin-bottom: 25px;
            opacity: 0;
            animation: slideInLeft 0.6s ease-out forwards;
        }

        .form-group:nth-child(1) { animation-delay: 0.7s; }
        .form-group:nth-child(2) { animation-delay: 0.9s; }
        .form-group:nth-child(3) { animation-delay: 1.1s; }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .form-control, .form-select {
            background: #ffffff;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 15px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
            position: relative;
            color: #1c2437;
        }

        .form-control:focus, .form-select:focus {
            background: #ffffff;
            border-color: #ff8c00;
            box-shadow: 0 0 0 4px rgba(255, 140, 0, 0.25), 0 10px 25px rgba(255, 140, 0, 0.15);
            transform: translateY(-2px);
        }

        .form-label {
            font-weight: 600;
            color: #1c2437;
            margin-bottom: 8px;
            display: block;
        }

        /* Botón dinámico */
        .btn-dynamic {
            background: linear-gradient(135deg, #302b63, #4a3f6b, #ff8c00);
            border: none;
            border-radius: 12px;
            color: white;
            font-weight: 700;
            font-size: 1.1rem;
            padding: 15px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            margin-top: 20px;
            animation: slideInUp 0.6s ease-out 1.3s both;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .btn-dynamic::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }

        .btn-dynamic:hover::before {
            left: 100%;
        }

        .btn-dynamic:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(48, 43, 99, 0.5), 0 0 20px rgba(255, 140, 0, 0.3);
            background: linear-gradient(135deg, #4a3f6b, #ff8c00, #ffc371);
        }

        .btn-dynamic:active {
            transform: translateY(-1px);
        }

        /* Footer dinámico */
        .login-footer {
            text-align: center;
            margin-top: 30px;
            opacity: 0;
            animation: fadeIn 1s ease-out 1.5s forwards;
        }

        .footer-text {
            color: #1c2437;
            font-size: 0.9rem;
            animation: pulse 2s ease-in-out infinite alternate;
        }

        @keyframes pulse {
            0% { opacity: 0.7; }
            100% { opacity: 1; }
        }

        /* Alerta mejorada */
        .alert-enhanced {
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(255, 140, 0, 0.9), rgba(255, 195, 113, 0.9));
            backdrop-filter: blur(10px);
            border-left: 4px solid #ff8c00;
            animation: alertSlideIn 0.5s ease-out;
            color: #302b63;
        }

        @keyframes alertSlideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-container {
                flex-direction: column;
                max-width: 95%;
            }
            
            .logos-panel, .login-panel {
                width: 100%;
            }
            
            .logos-panel {
                min-height: 200px;
            }
            
            .login-title {
                font-size: 1.8rem;
            }
        }

        /* Efectos de carga */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to top, #0f0c29, #302b63, #24243e);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            animation: fadeOut 1s ease-out 2s forwards;
        }

        .loader {
            width: 60px;
            height: 60px;
            border: 3px solid rgba(255, 200, 150, 0.3);
            border-top: 3px solid #ff8c00;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes fadeOut {
            to {
                opacity: 0;
                visibility: hidden;
            }
        }
    </style>
</head>
<body>

<!-- Overlay de carga -->
<div class="loading-overlay">
    <div class="loader"></div>
</div>

<!-- Partículas animadas -->
<div class="particles"></div>

<!-- Ondas de fondo -->
<div class="waves"></div>

<!-- Container principal -->
<div class="main-container">
    <!-- Panel de logos -->
    <div class="logos-panel">
        <div class="logo-container">
            <div class="logo-image-wrapper">
                <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Logo Enterprise SST" class="logo-image">
            </div>
            <div class="logo-image-wrapper">
                <img src="<?= base_url('uploads/logocycloid.png') ?>" alt="Logo Cycloid" class="logo-image">
            </div>
            <div class="logo-image-wrapper">
                <img src="<?= base_url('uploads/logosst.png') ?>" alt="Logo SST" class="logo-image">
            </div>
        </div>
    </div>

    <!-- Panel de login -->
    <div class="login-panel">
        <div class="login-header">
            <h2 class="login-title">Aplicativo Enterprisesst</h2>
            <h4 class="login-subtitle">Inicio de Sesión Empresas</h4>
        </div>

        <!-- Mensaje de éxito -->
        <?php if (session()->getFlashdata('msg_success')): ?>
            <div class="alert alert-dismissible fade show" role="alert" style="background: linear-gradient(135deg, rgba(40, 167, 69, 0.9), rgba(32, 134, 55, 0.9)); border: none; border-radius: 12px; color: white;">
                <?= session()->getFlashdata('msg_success') ?>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Mensaje de error -->
        <?php if (session()->getFlashdata('msg')): ?>
            <div class="alert alert-enhanced alert-dismissible fade show" role="alert">
                <?= session()->getFlashdata('msg') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Formulario de login -->
        <form action="<?= base_url('/loginPost') ?>" method="post" id="loginForm">
            <div class="form-group">
                <label for="email" class="form-label">Correo Electrónico</label>
                <input type="email" name="username" id="email" class="form-control" placeholder="Ingrese su correo" required>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Contraseña</label>
                <div class="position-relative">
                    <input type="password" name="password" id="password" class="form-control" placeholder="Ingrese su contraseña" required style="padding-right: 50px;">
                    <button type="button" id="togglePassword" class="btn position-absolute" style="right: 5px; top: 50%; transform: translateY(-50%); border: none; background: transparent; color: #6c757d; padding: 5px 10px;">
                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                            <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-dynamic w-100">
                <span>Iniciar Sesión</span>
            </button>

            <div class="text-center mt-3">
                <a href="<?= base_url('/forgot-password') ?>" style="color: #ff8c00; text-decoration: none; font-weight: 500; transition: color 0.3s;">
                    ¿Olvidaste tu contraseña?
                </a>
            </div>
        </form>

        <div class="login-footer">
            <p class="footer-text">Empowered By Cycloid Talent S.A.S.</p>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Crear estrellas en el firmamento
    function createParticles() {
        const particlesContainer = document.querySelector('.particles');
        const numberOfStars = 80;

        for (let i = 0; i < numberOfStars; i++) {
            const star = document.createElement('div');
            const size = Math.random();

            // Clasificar estrellas por tamaño
            if (size > 0.9) {
                star.className = 'particle bright';
            } else if (size > 0.7) {
                star.className = 'particle large';
            } else {
                star.className = 'particle';
            }

            // Posicionar en la parte superior (cielo nocturno)
            star.style.left = Math.random() * 100 + '%';
            star.style.top = Math.random() * 60 + '%';
            star.style.animationDelay = Math.random() * 3 + 's';
            star.style.animationDuration = (Math.random() * 2 + 2) + 's';
            particlesContainer.appendChild(star);
        }
    }

    // Efecto de typing en el título
    function typeWriter(element, text, speed = 100) {
        let i = 0;
        element.innerHTML = '';
        function type() {
            if (i < text.length) {
                element.innerHTML += text.charAt(i);
                i++;
                setTimeout(type, speed);
            }
        }
        type();
    }

    // Animación del formulario
    function animateForm() {
        const form = document.getElementById('loginForm');
        const inputs = form.querySelectorAll('.form-control, .form-select');
        
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });
    }

    // Efectos de hover en el botón
    function enhanceButton() {
        const button = document.querySelector('.btn-dynamic');
        
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px) scale(1.02)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    }

    // Validación dinámica
    function setupValidation() {
        const form = document.getElementById('loginForm');
        const inputs = form.querySelectorAll('input, select');
        
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                if (this.checkValidity()) {
                    this.style.borderColor = '#ff8c00';
                    this.style.boxShadow = '0 0 0 2px rgba(255, 140, 0, 0.2)';
                } else {
                    this.style.borderColor = '#e63939';
                    this.style.boxShadow = '0 0 0 2px rgba(230, 57, 57, 0.2)';
                }
            });
        });
    }

    // Inicializar efectos
    document.addEventListener('DOMContentLoaded', function() {
        createParticles();
        animateForm();
        enhanceButton();
        setupValidation();
        
        // Efecto de typewriter en el título después de la carga
        setTimeout(() => {
            const title = document.querySelector('.login-title');
            const originalText = title.textContent;
            typeWriter(title, originalText, 150);
        }, 2500);
    });

    // Toggle mostrar/ocultar contraseña
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            // Icono de ojo tachado (ocultar)
            eyeIcon.innerHTML = '<path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.944 5.944 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z"/><path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829l.822.822zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829z"/><path d="M3.35 5.47c-.18.16-.353.322-.518.487A13.134 13.134 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7.029 7.029 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12-.708.708z"/>';
        } else {
            passwordInput.type = 'password';
            // Icono de ojo normal (mostrar)
            eyeIcon.innerHTML = '<path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>';
        }
    });

    // Efecto de envío del formulario
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        const button = document.querySelector('.btn-dynamic');
        const originalText = button.innerHTML;
        
        button.innerHTML = '<div class="spinner-border spinner-border-sm me-2" role="status"></div>Iniciando...';
        button.disabled = true;
        
        // El formulario se enviará normalmente al servidor
    });
</script>

</body>
</html>