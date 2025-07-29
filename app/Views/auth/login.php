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
            background: linear-gradient(135deg, #bd9751 0%, #d4af37 25%, #f5f7fa 50%, #c3cfe2 75%, #bd9751 100%);
            height: 100vh;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            animation: backgroundShift 15s ease-in-out infinite;
        }

        @keyframes backgroundShift {
            0%, 100% { 
                background: linear-gradient(135deg, #bd9751 0%, #d4af37 25%, #f5f7fa 50%, #c3cfe2 75%, #bd9751 100%);
            }
            50% { 
                background: linear-gradient(135deg, #d4af37 0%, #bd9751 25%, #c3cfe2 50%, #f5f7fa 75%, #d4af37 100%);
            }
        }

        /* Partículas de fondo animadas */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .particle {
            position: absolute;
            width: 6px;
            height: 6px;
            background: radial-gradient(circle, #d4af37, #bd9751);
            border-radius: 50%;
            animation: float 15s linear infinite;
            box-shadow: 0 0 10px rgba(189, 151, 81, 0.5);
        }

        @keyframes float {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100vh) rotate(360deg);
                opacity: 0;
            }
        }

        /* Ondas de fondo animadas */
        .waves {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none"><path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" opacity=".4" fill="%23bd9751"/><path d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z" opacity=".6" fill="%23d4af37"/><path d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z" fill="%23bd9751"/></svg>') repeat-x;
            background-size: 1200px 120px;
            animation: wave 10s ease-in-out infinite alternate;
            opacity: 0.35;
            z-index: 1;
        }

        @keyframes wave {
            0% { transform: translateX(0); }
            100% { transform: translateX(-200px); }
        }

        /* Container principal con glassmorphism */
        .main-container {
            position: relative;
            z-index: 10;
            background: linear-gradient(145deg, 
                rgba(255, 255, 255, 0.9) 0%, 
                rgba(189, 151, 81, 0.1) 25%,
                rgba(255, 255, 255, 0.95) 50%,
                rgba(212, 175, 55, 0.1) 75%,
                rgba(255, 255, 255, 0.9) 100%
            );
            backdrop-filter: blur(25px);
            border-radius: 25px;
            border: 2px solid rgba(189, 151, 81, 0.3);
            box-shadow: 
                0 25px 50px rgba(0, 0, 0, 0.1),
                inset 0 1px 0 rgba(189, 151, 81, 0.2),
                0 0 30px rgba(189, 151, 81, 0.15);
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
                rgba(189, 151, 81, 0.95) 0%, 
                rgba(212, 175, 55, 0.9) 50%,
                rgba(28, 36, 55, 0.85) 100%
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
                rgba(212, 175, 55, 0.3), 
                transparent,
                rgba(189, 151, 81, 0.2),
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
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            max-width: 200px;
            border: 1px solid rgba(189, 151, 81, 0.3);
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
                rgba(255, 255, 255, 0.98) 0%,
                rgba(189, 151, 81, 0.05) 25%,
                rgba(255, 255, 255, 0.95) 50%,
                rgba(212, 175, 55, 0.08) 75%,
                rgba(255, 255, 255, 0.98) 100%
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
                rgba(189, 151, 81, 0.03) 50%,
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
            background: linear-gradient(135deg, #1c2437, #2c3e50);
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
            border-color: #bd9751;
            box-shadow: 0 0 0 4px rgba(189, 151, 81, 0.3), 0 10px 25px rgba(189, 151, 81, 0.2);
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
            background: linear-gradient(135deg, #1c2437, #2c3e50);
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
            box-shadow: 0 15px 35px rgba(28, 36, 55, 0.4);
            background: linear-gradient(135deg, #2c3e50, #bd9751);
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
            background: linear-gradient(135deg, rgba(189, 151, 81, 0.9), rgba(212, 175, 55, 0.9));
            backdrop-filter: blur(10px);
            border-left: 4px solid #bd9751;
            animation: alertSlideIn 0.5s ease-out;
            color: #1c2437;
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
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            animation: fadeOut 1s ease-out 2s forwards;
        }

        .loader {
            width: 60px;
            height: 60px;
            border: 3px solid rgba(28, 36, 55, 0.3);
            border-top: 3px solid #bd9751;
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

        <!-- Mensaje de error original -->
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
                <input type="password" name="password" id="password" class="form-control" placeholder="Ingrese su contraseña" required>
            </div>

            <div class="form-group">
                <label for="role" class="form-label">Rol</label>
                <select name="role" id="role" class="form-select" required>
                    <option value="client">Cliente</option>
                    <option value="consultant">Consultor</option>
                </select>
            </div>

            <button type="submit" class="btn btn-dynamic w-100">
                <span>Iniciar Sesión</span>
            </button>
        </form>

        <div class="login-footer">
            <p class="footer-text">Empowered By Cycloid Talent S.A.S.</p>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Crear partículas animadas
    function createParticles() {
        const particlesContainer = document.querySelector('.particles');
        const numberOfParticles = 50;

        for (let i = 0; i < numberOfParticles; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.animationDelay = Math.random() * 15 + 's';
            particle.style.animationDuration = (Math.random() * 10 + 10) + 's';
            particlesContainer.appendChild(particle);
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
                    this.style.borderColor = '#bd9751';
                    this.style.boxShadow = '0 0 0 2px rgba(189, 151, 81, 0.2)';
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