<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enterprisesst, SG-SST Empresas</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Estilo global */
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: #1c2437;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        /* Navbar mejorada */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 3px solid #bd9751;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1030;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }

        .navbar-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 20px;
        }

        .navbar-content img {
            max-height: 70px;
            transition: transform 0.3s ease;
        }

        .navbar-content img:hover {
            transform: scale(1.05);
        }

        /* Espaciado del contenido principal */
        .content-wrapper {
            margin-top: 120px;
            padding-bottom: 100px;
        }

        /* Header mejorado */
        .welcome-header {
            background: linear-gradient(135deg, #1c2437 0%, #2c3e50 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .welcome-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        /* Botones de acceso rápido mejorados */
        .quick-access .btn {
            padding: 1rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }

        .quick-access .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .quick-access .btn:hover::before {
            left: 100%;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #1c2437 0%, #2c3e50 100%);
            color: #ffffff;
            border: none;
        }

        .btn-primary-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(28, 36, 55, 0.3);
            color: #ffffff;
        }

        .btn-success-custom {
            background: linear-gradient(135deg, #bd9751 0%, #d4af37 100%);
            color: #ffffff;
            border: none;
        }

        .btn-success-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(189, 151, 81, 0.3);
            color: #ffffff;
        }

        .btn-info-custom {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            color: #1c2437;
            border: 2px solid #1c2437;
        }

        .btn-info-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(28, 36, 55, 0.2);
            color: #1c2437;
        }

        /* Título de sección */
        .section-title {
            background: linear-gradient(135deg, #bd9751 0%, #d4af37 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 1.4rem;
            font-weight: 700;
            text-align: center;
            margin: 2rem 0;
        }

        /* Acordeón personalizado */
        .custom-accordion {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .accordion-item {
            border: none;
            margin-bottom: 1px;
        }

        .accordion-header .accordion-button {
            background: linear-gradient(135deg, #1c2437 0%, #2c3e50 100%);
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            padding: 1.2rem 1.5rem;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .accordion-header .accordion-button:not(.collapsed) {
            background: linear-gradient(135deg, #bd9751 0%, #d4af37 100%);
            color: white;
            box-shadow: none;
        }

        .accordion-header .accordion-button::after {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23ffffff'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e");
        }

        .accordion-body {
            background: white;
            padding: 0;
        }

        /* Items del acordeón */
        .access-item {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            text-decoration: none;
            color: #1c2437;
            border-bottom: 1px solid #e9ecef;
            transition: all 0.3s ease;
            position: relative;
        }

        .access-item:last-child {
            border-bottom: none;
        }

        .access-item:hover {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            color: #1c2437;
            transform: translateX(10px);
            text-decoration: none;
        }

        .access-item .item-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #bd9751 0%, #d4af37 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            color: white;
            font-size: 1.1rem;
        }

        .access-item .item-content {
            flex: 1;
        }

        .access-item .item-number {
            background: #1c2437;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            font-weight: 600;
        }

        /* Contador de elementos por dimensión */
        .dimension-counter {
            background: rgba(255,255,255,0.2);
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.9rem;
            margin-left: auto;
            margin-right: 1rem;
        }

        /* Botón de cerrar sesión mejorado */
        .btn-logout {
            background: linear-gradient(135deg, #ff4d4d 0%, #e63939 100%);
            color: white;
            font-weight: 600;
            padding: 12px 30px;
            border-radius: 25px;
            border: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 77, 77, 0.3);
        }

        .btn-logout:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255, 77, 77, 0.4);
            color: white;
        }

        /* Sección de asesoría mejorada */
        .asesoria-card {
            background: linear-gradient(135deg, white 0%, #f8f9fa 100%);
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            text-align: center;
            margin-top: 3rem;
            border: 2px solid #bd9751;
        }

        .asesoria-card h2 {
            color: #1c2437;
            margin-bottom: 1rem;
        }

        .asesoria-card img {
            max-height: 80px;
            margin: 1rem auto;
        }

        .contact-info {
            display: flex;
            justify-content: space-around;
            margin-top: 1.5rem;
            flex-wrap: wrap;
        }

        .contact-item {
            display: flex;
            align-items: center;
            margin: 0.5rem;
        }

        .contact-item i {
            color: #bd9751;
            margin-right: 0.5rem;
            font-size: 1.2rem;
        }

        /* Footer mejorado */
        footer {
            background: linear-gradient(135deg, #1c2437 0%, #2c3e50 100%);
            color: #ffffff;
            padding: 20px 0;
            margin-top: 3rem;
        }

        /* Animaciones */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .welcome-header h1 {
                font-size: 2rem;
            }

            .contact-info {
                flex-direction: column;
                align-items: center;
            }

            .navbar-content img {
                max-height: 50px;
            }

            .section-title {
                font-size: 1.2rem;
            }
        }
    </style>
</head>

<body>

    <!-- Navbar fija -->
    <nav class="navbar">
        <div class="container navbar-content">
            <!-- Logo izquierdo -->
            <a href="https://dashboard.cycloidtalent.com/login" target="_blank">
                <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Logo Enterprisesst">
            </a>
            <!-- Logo central -->
            <a href="https://cycloidtalent.com/index.php/consultoria-sst" target="_blank">
                <img src="<?= base_url('uploads/logosst.png') ?>" alt="Logo SST">
            </a>
            <!-- Logo derecho -->
            <a href="https://cycloidtalent.com/" target="_blank">
                <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Logo Cycloid">
            </a>
        </div>
    </nav>

    <!-- Contenido principal -->
    <div class="content-wrapper">
        <div class="container">
            <!-- Header -->
            <div class="welcome-header text-center fade-in-up">
                <h1><i class="fas fa-building"></i> ¡<?= esc($client['nombre_cliente']) ?>!</h1>
                <p class="lead">Bienvenido a Enterprisesst, tu aplicativo especializado en SG-SST</p>
            </div>

            <!-- Quick Access Buttons -->
            <div class="quick-access text-center fade-in-up">
                <div class="row justify-content-center">
                    <div class="col-lg-4 col-md-6 mb-3">
                        <a href="<?= base_url('nuevoListPlanTrabajoCliente/' . $client['id_cliente']) ?>" target="_blank" class="btn btn-primary-custom w-100">
                            <i class="fas fa-calendar-alt me-2"></i> Plan de Trabajo
                        </a>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-3">
                        <a href="<?= base_url('/report_dashboard') ?>" target="_blank" class="btn btn-success-custom w-100">
                            <i class="fas fa-file-alt me-2"></i> Documentos
                        </a>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-3">
                        <a href="<?= base_url('client/panel') ?>" target="_blank" class="btn btn-info-custom w-100">
                            <i class="fas fa-chart-line me-2"></i> Panel de Gestión
                        </a>
                    </div>
                </div>
            </div>

            <!-- Título -->
            <h4 class="section-title fade-in-up">
                <i class="fas fa-shield-alt"></i> Dispositivos Documentales Sistema de Gestión en Seguridad y Salud en el Trabajo
            </h4>

            <!-- Acordeón de Accesos -->
            <div class="accordion custom-accordion fade-in-up" id="accessAccordion">
                <?php
                $current_dimension = '';
                $index = 1;
                $dimension_items = [];
                $dimension_count = [];
                
                // Primero agrupamos los accesos por dimensión
                foreach ($accesos as $acceso) {
                    $dimension_items[$acceso['dimension']][] = $acceso;
                    if (!isset($dimension_count[$acceso['dimension']])) {
                        $dimension_count[$acceso['dimension']] = 0;
                    }
                    $dimension_count[$acceso['dimension']]++;
                }
                
                $accordion_index = 0;
                foreach ($dimension_items as $dimension => $items):
                    $accordion_id = 'dimension' . $accordion_index;
                    $show_class = ($accordion_index === 0) ? 'show' : '';
                    $collapsed_class = ($accordion_index === 0) ? '' : 'collapsed';
                    $expanded = ($accordion_index === 0) ? 'true' : 'false';
                ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button <?= $collapsed_class ?>" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $accordion_id ?>" aria-expanded="<?= $expanded ?>">
                                <i class="fas fa-folder me-3"></i>
                                <span><?= esc($dimension) ?></span>
                                <span class="dimension-counter"><?= $dimension_count[$dimension] ?> elementos</span>
                            </button>
                        </h2>
                        <div id="<?= $accordion_id ?>" class="accordion-collapse collapse <?= $show_class ?>">
                            <div class="accordion-body">
                                <?php foreach ($items as $item): ?>
                                    <a href="<?= base_url($item['url']) ?>" target="_blank" class="access-item">
                                        <div class="item-icon">
                                            <i class="fas fa-file-alt"></i>
                                        </div>
                                        <div class="item-content">
                                            <strong><?= esc($item['nombre']) ?></strong>
                                        </div>
                                        <div class="item-number"><?= $index++ ?></div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php 
                $accordion_index++;
                endforeach; 
                ?>
            </div>

            <!-- Botón de cerrar sesión -->
            <div class="text-center mt-4 fade-in-up">
                <a href="<?= base_url('/logout') ?>" rel="noopener noreferrer">
                    <button type="button" class="btn btn-logout" aria-label="Cerrar Sesión">
                        <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                    </button>
                </a>
            </div>

            <!-- Asesoría Section -->
            <div class="asesoria-card fade-in-up">
                <h2><i class="fas fa-headset"></i> ¿Necesitas Asesoría?</h2>
                <p class="lead">Contáctanos para obtener ayuda en la gestión de tu SST.</p>
                <div>
                    <img src="<?= base_url('uploads/logocycloid.png') ?>" alt="Cycloid">
                </div>
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <strong>diana.cuestas@cycloidtalent.com</strong>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <strong>3229074371</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center">
        <p>&copy; 2024 Cycloid Talent SAS. Todos los derechos reservados.</p>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function logout() {
            alert('Cerrar sesión presionado');
        }

        // Animaciones al cargar
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.fade-in-up');
            elements.forEach((el, index) => {
                setTimeout(() => {
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, index * 200);
            });
        });
    </script>
</body>

</html>