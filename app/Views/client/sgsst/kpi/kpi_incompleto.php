<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Indicador no disponible | Cycloid Talent</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Estilos personalizados -->
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', sans-serif;
            padding-top: 140px; /* Espacio para el nav fijo */
        }
        nav {
            background-color: white; 
            position: fixed; 
            top: 0; 
            width: 100%; 
            z-index: 1000; 
            padding: 10px 0; 
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        nav div.container {
            display: flex; 
            flex-direction: column;
            gap: 10px;
        }
        nav .nav-logos {
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            max-width: 1200px; 
            margin: 0 auto;
        }
        nav .nav-logos img {
            height: 100px;
        }
        nav .nav-buttons {
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            max-width: 1200px; 
            margin: 10px auto 0; 
            padding: 0 20px;
        }
        nav .nav-buttons div {
            text-align: center;
        }
        nav .nav-buttons h2 {
            margin: 0; 
            font-size: 16px;
        }
        nav .nav-buttons a {
            display: inline-block; 
            padding: 10px 20px;
            text-decoration: none; 
            border-radius: 5px; 
            font-size: 14px; 
            margin-top: 5px;
        }
        .card-warning {
            max-width: 600px;
            margin: 80px auto;
            border-left: 6px solid #ffc107;
            background-color: #fff3cd;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }
        .card-warning .logo {
            max-width: 160px;
            margin-bottom: 20px;
        }
        footer {
            background-color: white; 
            padding: 20px 0; 
            border-top: 1px solid #B0BEC5; 
            margin-top: 40px; 
            color: #3A3F51; 
            font-size: 14px; 
            text-align: center;
        }
        footer div {
            max-width: 1200px; 
            margin: 0 auto; 
            display: flex; 
            flex-direction: column; 
            align-items: center;
        }
        footer a {
            color: #007BFF; 
            text-decoration: none;
        }
    </style>
</head>
<body>

    <!-- Nav Fijo -->
    <nav>
        <div class="container">
            <div class="nav-logos">
                <!-- Logo izquierdo -->
                <div>
                    <a href="https://dashboard.cycloidtalent.com/login">
                        <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo">
                    </a>
                </div>
                <!-- Logo centro -->
                <div>
                    <a href="https://cycloidtalent.com/index.php/consultoria-sst">
                        <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo">
                    </a>
                </div>
                <!-- Logo derecho -->
                <div>
                    <a href="https://cycloidtalent.com/">
                        <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo">
                    </a>
                </div>
            </div>
         
        </div>
    </nav>

    <!-- Contenido principal: Indicador no disponible -->
    <div class="card card-warning text-center">
        <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Logo Cycloid Talent" class="logo">
        <h3 class="text-warning mb-3">Indicador no disponible</h3>
        <p><?= esc($advertencia) ?></p>
        
        <p class="mb-4">Estaremos atentos para resolver sus inquietudes o ampliar tu alcance del servicio si es necesario.</p>
        <a href="https://dashboard.cycloidtalent.com/login" class="btn btn-outline-warning">
            Volver al Panel Principal
        </a>
    </div>

    <!-- Footer -->
    <footer>
        <div>
            <p style="margin: 0; font-weight: bold;">Cycloid Talent SAS</p>
            <p style="margin: 5px 0;">Todos los derechos reservados © 2024</p>
            <p style="margin: 5px 0;">NIT: 901.653.912</p>
            <p style="margin: 5px 0;">
                Sitio oficial: <a href="https://cycloidtalent.com/" target="_blank">https://cycloidtalent.com/</a>
            </p>
            <p style="margin: 15px 0 5px;"><strong>Nuestras Redes Sociales:</strong></p>
            <div style="display: flex; gap: 15px; justify-content: center;">
                <a href="https://www.facebook.com/CycloidTalent" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" alt="Facebook" style="height: 24px; width: 24px;">
                </a>
                <a href="https://co.linkedin.com/company/cycloid-talent" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733561.png" alt="LinkedIn" style="height: 24px; width: 24px;">
                </a>
                <a href="https://www.instagram.com/cycloid_talent?igsh=Nmo4d2QwZDg5dHh0" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733558.png" alt="Instagram" style="height: 24px; width: 24px;">
                </a>
                <a href="https://www.tiktok.com/@cycloid_talent?_t=8qBSOu0o1ZN&_r=1" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/3046/3046126.png" alt="TikTok" style="height: 24px; width: 24px;">
                </a>
            </div>
        </div>
    </footer>

    <!-- SweetAlert2 al cargar la página -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                icon: 'warning',
                title: 'Indicador no disponible',
                text: 'Este indicador aún no ha sido configurado para tu empresa. Puedes continuar usando el panel o contactar a nuestro equipo de soporte.',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#ffc107'
            });
        });
    </script>

    <!-- Bootstrap Bundle con Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
