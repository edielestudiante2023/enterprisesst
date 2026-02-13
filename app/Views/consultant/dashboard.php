<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Consultor 2025</title>
    <link rel="icon" href="<?= base_url('favicon.ico') ?>" type="image/x-icon">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        :root {
            --primary-dark: #1c2437;
            --secondary-dark: #2c3e50;
            --gold-primary: #bd9751;
            --gold-secondary: #d4af37;
            --white-primary: #ffffff;
            --white-secondary: #f8f9fa;
            --gradient-bg: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            --logout-red: #ff4d4d;
            --logout-red-hover: #e63939;
            --shadow-deep: 0 10px 30px rgba(0, 0, 0, 0.3);
            --shadow-medium: 0 5px 20px rgba(0, 0, 0, 0.15);
            --shadow-light: 0 2px 10px rgba(0, 0, 0, 0.1);
            --border-radius: 12px;
            --border-radius-large: 25px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: var(--gradient-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--primary-dark);
            line-height: 1.6;
            overflow-x: hidden;
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

        @keyframes shimmer {
            0% {
                background-position: -200% center;
            }

            100% {
                background-position: 200% center;
            }
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        /* Navbar moderna */
        .navbar-custom {
            background: #fffafa;
            box-shadow: var(--shadow-deep);
            padding: 20px 0;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            backdrop-filter: blur(10px);
            border-bottom: 2px solid var(--gold-primary);
        }

        .header-logos-custom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }

        .header-logos-custom img {
            max-height: 70px;
            margin-right: 15px;
            transition: var(--transition);
        }

        .header-logos-custom img:hover {
            transform: translateY(-3px) scale(1.05);
        }

        /* Content wrapper */
        .content-wrapper-custom {
            margin-top: 120px;
            padding: 0 15px;
            animation: fadeInUp 0.8s ease;
        }

        /* Banner de bienvenida moderno */
        .welcome-banner-custom {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--secondary-dark) 50%, var(--gold-primary) 100%);
            padding: 40px 30px;
            border-radius: var(--border-radius-large);
            text-align: center;
            color: var(--white-primary);
            box-shadow: var(--shadow-deep);
            margin-bottom: 30px;
            animation: fadeInUp 0.8s ease 0.2s both;
        }

        .welcome-banner-custom::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: repeating-conic-gradient(from 0deg at 50% 50%,
                    transparent 0deg,
                    rgba(255, 255, 255, 0.1) 10deg,
                    transparent 20deg);
            animation: float 6s ease-in-out infinite;
            z-index: 1;
        }

        .welcome-banner-custom::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(189, 151, 81, 0.1) 50%, transparent 70%);
            animation: shimmer 3s ease-in-out infinite;
            z-index: 1;
        }

        .welcome-banner-custom .content-custom {
            position: relative;
            z-index: 2;
        }

        .welcome-banner-custom h3 {
            font-size: 2.5rem;
            margin-bottom: 15px;
            font-weight: 700;
            background: linear-gradient(45deg, var(--white-primary), var(--gold-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .welcome-banner-custom h4 {
            font-size: 1.8rem;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--white-secondary);
        }

        .welcome-banner-custom p {
            font-size: 1.4rem;
            font-weight: 500;
            color: var(--white-primary);
        }

        /* Contenedor de tabla moderno */
        .table-container-custom {
            background: var(--white-primary);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-medium);
            padding: 30px;
            margin-bottom: 30px;
            animation: fadeInUp 0.8s ease 0.4s both;
            backdrop-filter: blur(10px);
        }

        .table-responsive {
            border-radius: var(--border-radius);
            overflow: hidden;
        }

        .table-custom {
            width: 100% !important;
            margin: 0;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-light);
        }

        .table-custom thead {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--secondary-dark) 100%);
            color: var(--white-primary);
        }

        .table-custom thead th {
            padding: 20px 15px;
            font-weight: 600;
            border: none;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.9rem;
        }

        .table-custom tbody td {
            padding: 15px;
            border-color: #e9ecef;
            transition: var(--transition);
        }

        .table-custom tbody tr:hover {
            background-color: rgba(189, 151, 81, 0.1);
            transform: translateY(-2px);
            box-shadow: var(--shadow-light);
        }

        /* Botón de acción mejorado */
        .btn-action-custom {
            background: linear-gradient(135deg, var(--gold-primary), var(--gold-secondary));
            border: none;
            color: var(--white-primary);
            border-radius: 50px;
            padding: 8px 15px;
            font-weight: 500;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .btn-action-custom::before {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: var(--transition);
        }

        .btn-action-custom:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-medium);
            color: var(--white-primary);
        }

        .btn-action-custom:hover::before {
            left: 100%;
        }

        /* Botón de Cerrar Sesión mejorado */
        .logout-container-custom {
            text-align: center;
            animation: fadeInUp 0.8s ease 0.6s both;
        }

        .btn-logout-custom {
            background: linear-gradient(135deg, var(--logout-red), var(--logout-red-hover));
            border: none;
            color: var(--white-primary);
            border-radius: var(--border-radius-large);
            padding: 15px 40px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-logout-custom::before {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: var(--transition);
        }

        .btn-logout-custom:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-deep);
            color: var(--white-primary);
        }

        .btn-logout-custom:hover::before {
            left: 100%;
        }

        /* Footer moderno */
        .footer-custom {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--secondary-dark) 100%);
            color: var(--white-primary);
            padding: 40px 0;
            margin-top: 50px;
            text-align: center;
            box-shadow: 0 -5px 20px rgba(0, 0, 0, 0.1);
        }

        .footer-custom a {
            color: var(--gold-primary);
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-custom a:hover {
            color: var(--gold-secondary);
            text-decoration: underline;
        }

        .social-icons-custom {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }

        .social-icons-custom a {
            display: inline-block;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--gold-primary), var(--gold-secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            text-decoration: none;
        }

        .social-icons-custom a:hover {
            transform: translateY(-5px) scale(1.1);
            box-shadow: var(--shadow-medium);
        }

        .social-icons-custom img {
            width: 24px;
            height: 24px;
            filter: brightness(1.2);
        }

        /* DataTables personalizados */
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border: 2px solid var(--gold-primary);
            border-radius: var(--border-radius);
            padding: 8px 12px;
            transition: var(--transition);
        }

        .dataTables_wrapper .dataTables_length select:focus,
        .dataTables_wrapper .dataTables_filter input:focus {
            outline: none;
            border-color: var(--gold-secondary);
            box-shadow: 0 0 0 3px rgba(189, 151, 81, 0.2);
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: linear-gradient(135deg, var(--gold-primary), var(--gold-secondary)) !important;
            border: none !important;
            color: var(--white-primary) !important;
            border-radius: var(--border-radius) !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--secondary-dark)) !important;
            border: none !important;
            color: var(--white-primary) !important;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .header-logos-custom {
                flex-direction: column;
                gap: 15px;
            }

            .header-logos-custom img {
                max-height: 50px;
            }

            .content-wrapper-custom {
                margin-top: 180px;
            }

            .welcome-banner-custom h3 {
                font-size: 2rem;
            }

            .welcome-banner-custom h4 {
                font-size: 1.4rem;
            }

            .welcome-banner-custom p {
                font-size: 1.1rem;
            }

            .table-container-custom {
                padding: 15px;
            }

            .social-icons-custom {
                flex-wrap: wrap;
                gap: 15px;
            }
        }

        @media (max-width: 480px) {
            .welcome-banner-custom {
                padding: 25px 20px;
            }

            .table-container-custom {
                padding: 10px;
            }

            .btn-logout-custom {
                padding: 12px 30px;
                font-size: 1rem;
            }
        }
    </style>
</head>

<body>
    <!-- Cabecera -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-custom">
            <div class="container">
                <div class="header-logos-custom">
                    <!-- Logo izquierdo -->
                    <div>
                        <a href="https://dashboard.cycloidtalent.com/login" target="_blank" rel="noopener noreferrer">
                            <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo">
                        </a>
                    </div>
                    <!-- Logo centro -->
                    <div>
                        <a href="https://cycloidtalent.com/index.php/consultoria-sst" target="_blank" rel="noopener noreferrer">
                            <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo">
                        </a>
                    </div>
                    <!-- Logo derecho -->
                    <div>
                        <a href="https://cycloidtalent.com/" target="_blank" rel="noopener noreferrer">
                            <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo">
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Contenido principal -->
    <main class="container-fluid content-wrapper-custom">
        <!-- Banner de Bienvenida -->
        <div class="welcome-banner-custom">
            <div class="content-custom">
                <h3><i class="fas fa-shield-alt me-3"></i>Enterprisesst - PH // Consultor</h3>
                <h4><i class="fas fa-users me-2"></i>Dash Board de Administración</h4>
                <p class="mb-0"><i class="fas fa-globe me-2"></i>Enterprisesst - Sistemas que Evolucionan</p>
            </div>
        </div>

        <!-- Usuario en sesión -->
        <?php if (isset($usuario) && $usuario): ?>
        <div class="text-center mb-4">
            <div class="d-inline-block px-4 py-2 rounded-pill" style="background: linear-gradient(135deg, var(--primary-dark), var(--secondary-dark)); box-shadow: var(--shadow-medium);">
                <span class="text-white">
                    <i class="fas fa-user-circle me-2"></i>
                    <strong>Usuario:</strong> <?= esc($usuario['nombre_completo'] ?? $usuario['email']) ?>
                    <span class="badge bg-warning text-dark ms-2"><?= ucfirst(esc($usuario['tipo_usuario'] ?? 'N/A')) ?></span>
                </span>
            </div>
        </div>
        <?php endif; ?>

        <!-- Botones de Acceso Rápido -->
        <div class="text-center mb-4">
            <a href="<?= base_url('/consultor/selector-cliente') ?>" target="_blank" rel="noopener noreferrer">
                <button type="button" class="btn btn-logout-custom me-3" style="background: linear-gradient(135deg, var(--gold-primary), var(--gold-secondary)); border: none;">
                    <i class="fas fa-eye me-2"></i>Ver Vista del Cliente
                </button>
            </a>
            <a href="<?= base_url('/admin/users') ?>" target="_blank" rel="noopener noreferrer">
                <button type="button" class="btn btn-logout-custom me-3" style="background: linear-gradient(135deg, #667eea, #764ba2); border: none;" aria-label="Gestión de Usuarios">
                    <i class="fas fa-users-cog me-2"></i>Gestión de Usuarios
                </button>
            </a>
            <a href="<?= base_url('/admin/usage') ?>" target="_blank" rel="noopener noreferrer">
                <button type="button" class="btn btn-logout-custom" style="background: linear-gradient(135deg, #11998e, #38ef7d); border: none;">
                    <i class="fas fa-chart-line me-2"></i>Consumo de Plataforma
                </button>
            </a>
        </div>

        <!-- Dashboards Analíticos -->
        <div class="mb-5">
            <h4 class="text-center mb-4" style="color: var(--primary-dark); font-weight: 700;">
                <i class="fas fa-chart-bar me-2"></i>Dashboards Analíticos
            </h4>
            <div class="row justify-content-center">
                <div class="col-lg-3 col-md-6 mb-3">
                    <a href="<?= base_url('consultant/dashboard-estandares') ?>" target="_blank" class="btn w-100 py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; font-weight: 600; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);">
                        <i class="fas fa-chart-pie fa-lg mb-2 d-block"></i>
                        Estándares Mínimos
                    </a>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <a href="<?= base_url('consultant/dashboard-capacitaciones') ?>" target="_blank" class="btn w-100 py-3" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border: none; font-weight: 600; box-shadow: 0 4px 15px rgba(240, 147, 251, 0.3);">
                        <i class="fas fa-graduation-cap fa-lg mb-2 d-block"></i>
                        Capacitaciones
                    </a>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <a href="<?= base_url('consultant/dashboard-plan-trabajo') ?>" target="_blank" class="btn w-100 py-3" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; border: none; font-weight: 600; box-shadow: 0 4px 15px rgba(79, 172, 254, 0.3);">
                        <i class="fas fa-tasks fa-lg mb-2 d-block"></i>
                        Plan de Trabajo
                    </a>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <a href="<?= base_url('consultant/dashboard-pendientes') ?>" target="_blank" class="btn w-100 py-3" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; border: none; font-weight: 600; box-shadow: 0 4px 15px rgba(250, 112, 154, 0.3);">
                        <i class="fas fa-clipboard-list fa-lg mb-2 d-block"></i>
                        Pendientes
                    </a>
                </div>
            </div>
        </div>

        <!-- Módulo Documentación SST -->
        <div class="mb-5">
            <h4 class="text-center mb-4" style="color: var(--primary-dark); font-weight: 700;">
                <i class="fas fa-folder-open me-2"></i>Documentación SST - Resolución 0312/2019
            </h4>
            <div class="row justify-content-center">
                <div class="col-lg-2 col-md-4 mb-3">
                    <a href="<?= base_url('documentacion/instructivo') ?>" target="_blank" class="btn w-100 py-3" style="background: linear-gradient(135deg, #ff6b6b 0%, #feca57 100%); color: white; border: none; font-weight: 600; box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);">
                        <i class="fas fa-book-reader fa-lg mb-2 d-block"></i>
                        Instructivo SST
                    </a>
                </div>
                <div class="col-lg-2 col-md-4 mb-3">
                    <a href="<?= base_url('contexto') ?>" target="_blank" class="btn w-100 py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; font-weight: 600; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);">
                        <i class="fas fa-cog fa-lg mb-2 d-block"></i>
                        Contexto Cliente
                    </a>
                </div>
                <div class="col-lg-2 col-md-4 mb-3">
                    <a href="<?= base_url('estandares') ?>" target="_blank" class="btn w-100 py-3" style="background: linear-gradient(135deg, #f2994a 0%, #f2c94c 100%); color: white; border: none; font-weight: 600; box-shadow: 0 4px 15px rgba(242, 153, 74, 0.3);">
                        <i class="fas fa-sync-alt fa-lg mb-2 d-block"></i>
                        Cumplimiento PHVA
                    </a>
                </div>
                <div class="col-lg-2 col-md-4 mb-3">
                    <a href="<?= base_url('documentacion') ?>" target="_blank" class="btn w-100 py-3" style="background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%); color: white; border: none; font-weight: 600; box-shadow: 0 4px 15px rgba(106, 17, 203, 0.3);">
                        <i class="fas fa-file-alt fa-lg mb-2 d-block"></i>
                        Documentación
                    </a>
                </div>
                <div class="col-lg-2 col-md-4 mb-3">
                    <a href="<?= base_url('estandares/catalogo') ?>" target="_blank" class="btn w-100 py-3" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; border: none; font-weight: 600; box-shadow: 0 4px 15px rgba(17, 153, 142, 0.3);">
                        <i class="fas fa-book fa-lg mb-2 d-block"></i>
                        Catálogo 60 Estándares
                    </a>
                </div>
                <div class="col-lg-2 col-md-4 mb-3">
                    <a href="<?= base_url('matriz-legal') ?>" target="_blank" class="btn w-100 py-3" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); color: white; border: none; font-weight: 600; box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);">
                        <i class="fas fa-balance-scale fa-lg mb-2 d-block"></i>
                        Matriz Legal
                    </a>
                </div>
            </div>
        </div>

        <!-- Módulo de Comités y Actas -->
        <div class="mb-5">
            <h4 class="text-center mb-4" style="color: var(--primary-dark); font-weight: 700;">
                <i class="fas fa-users me-2"></i>Gestión de Comités y Actas
            </h4>
            <div class="row justify-content-center">
                <!-- Card 1: Gestión de Actas -->
                <div class="col-lg-5 col-md-6 mb-3">
                    <div class="card shadow-sm border-0" style="border-radius: 15px; overflow: hidden;">
                        <div class="card-body p-4" style="background: linear-gradient(135deg, #00b894 0%, #00cec9 100%);">
                            <h5 class="text-white text-center mb-3">
                                <i class="fas fa-clipboard-list me-2"></i>Actas de Reunión
                            </h5>
                            <div class="row align-items-center">
                                <div class="col-12 mb-3">
                                    <label class="text-white fw-bold mb-2">
                                        <i class="fas fa-building me-2"></i>Seleccione un Cliente
                                    </label>
                                    <select id="selectClienteActas" class="form-select" style="width: 100%;">
                                        <option value="">-- Buscar cliente --</option>
                                        <?php foreach ($clientes ?? [] as $cliente): ?>
                                            <option value="<?= esc($cliente['id_cliente']) ?>">
                                                <?= esc($cliente['nombre_cliente']) ?> - NIT: <?= esc($cliente['nit_cliente']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <button type="button" id="btnIrActas" class="btn btn-light w-100 py-2 fw-bold" disabled style="border-radius: 10px;">
                                        <i class="fas fa-clipboard-list me-2"></i>Ir a Gestión de Actas
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Card 2: Conformación de Comités (Elecciones) -->
                <div class="col-lg-5 col-md-6 mb-3">
                    <div class="card shadow-sm border-0" style="border-radius: 15px; overflow: hidden;">
                        <div class="card-body p-4" style="background: linear-gradient(135deg, #6c5ce7 0%, #a29bfe 100%);">
                            <h5 class="text-white text-center mb-3">
                                <i class="fas fa-vote-yea me-2"></i>Conformación de Comités
                            </h5>
                            <div class="row align-items-center">
                                <div class="col-12 mb-3">
                                    <label class="text-white fw-bold mb-2">
                                        <i class="fas fa-building me-2"></i>Seleccione un Cliente
                                    </label>
                                    <select id="selectClienteComites" class="form-select" style="width: 100%;">
                                        <option value="">-- Buscar cliente --</option>
                                        <?php foreach ($clientes ?? [] as $cliente): ?>
                                            <option value="<?= esc($cliente['id_cliente']) ?>">
                                                <?= esc($cliente['nombre_cliente']) ?> - NIT: <?= esc($cliente['nit_cliente']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <button type="button" id="btnIrComites" class="btn btn-light w-100 py-2 fw-bold" disabled style="border-radius: 10px;">
                                        <i class="fas fa-vote-yea me-2"></i>Ir a Conformación
                                    </button>
                                </div>
                            </div>
                            <small class="text-white-50 d-block text-center mt-2">
                                COPASST, COCOLAB, Brigada, Vigía
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fila 2: Acciones Correctivas e Indicadores SST -->
            <div class="row justify-content-center mt-3">
                <!-- Card: Acciones Correctivas -->
                <div class="col-lg-5 col-md-6 mb-3">
                    <div class="card shadow-sm border-0" style="border-radius: 15px; overflow: hidden;">
                        <div class="card-body p-4" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                            <h5 class="text-white text-center mb-3">
                                <i class="fas fa-exclamation-triangle me-2"></i>Acciones Correctivas, Preventivas y de Mejora
                            </h5>
                            <div class="row align-items-center">
                                <div class="col-12 mb-3">
                                    <label class="text-white fw-bold mb-2">
                                        <i class="fas fa-building me-2"></i>Seleccione un Cliente
                                    </label>
                                    <select id="selectClienteAcciones" class="form-select" style="width: 100%;">
                                        <option value="">-- Buscar cliente --</option>
                                        <?php foreach ($clientes ?? [] as $cliente): ?>
                                            <option value="<?= esc($cliente['id_cliente']) ?>">
                                                <?= esc($cliente['nombre_cliente']) ?> - NIT: <?= esc($cliente['nit_cliente']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <button type="button" id="btnIrAcciones" class="btn btn-light w-100 py-2 fw-bold" disabled style="border-radius: 10px;">
                                        <i class="fas fa-tasks me-2"></i>Ir a Acciones Correctivas
                                    </button>
                                </div>
                            </div>
                            <small class="text-white-50 d-block text-center mt-2">
                                Numerales 7.1.1, 7.1.2, 7.1.3, 7.1.4
                            </small>
                        </div>
                    </div>
                </div>
                <!-- Card: Indicadores SST -->
                <div class="col-lg-5 col-md-6 mb-3">
                    <div class="card shadow-sm border-0" style="border-radius: 15px; overflow: hidden;">
                        <div class="card-body p-4" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);">
                            <h5 class="text-white text-center mb-3">
                                <i class="fas fa-chart-line me-2"></i>Indicadores del SG-SST
                            </h5>
                            <div class="row align-items-center">
                                <div class="col-12 mb-3">
                                    <label class="text-white fw-bold mb-2">
                                        <i class="fas fa-building me-2"></i>Seleccione un Cliente
                                    </label>
                                    <select id="selectClienteIndicadores" class="form-select" style="width: 100%;">
                                        <option value="">-- Buscar cliente --</option>
                                        <?php foreach ($clientes ?? [] as $cliente): ?>
                                            <option value="<?= esc($cliente['id_cliente']) ?>">
                                                <?= esc($cliente['nombre_cliente']) ?> - NIT: <?= esc($cliente['nit_cliente']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <button type="button" id="btnIrIndicadores" class="btn btn-light w-100 py-2 fw-bold" disabled style="border-radius: 10px;">
                                        <i class="fas fa-chart-line me-2"></i>Ir a Indicadores SST
                                    </button>
                                </div>
                            </div>
                            <small class="text-white-50 d-block text-center mt-2">
                                Estructura, Proceso y Resultado
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modulo de Firmas Electronicas -->
        <div class="mb-5">
            <h4 class="text-center mb-4" style="color: var(--primary-dark); font-weight: 700;">
                <i class="fas fa-file-signature me-2"></i>Firmas Electronicas
            </h4>
            <div class="row justify-content-center">
                <div class="col-lg-5 col-md-6 mb-3">
                    <a href="<?= base_url('firma/dashboard') ?>" target="_blank" class="text-decoration-none">
                        <div class="card shadow-sm border-0" style="border-radius: 15px; overflow: hidden;">
                            <div class="card-body p-4 text-center" style="background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);">
                                <i class="fas fa-pen-nib" style="font-size: 2.5rem; color: white;"></i>
                                <h5 class="text-white mt-3 mb-2">Gestion de Firmas</h5>
                                <p class="mb-0 small" style="color: rgba(255,255,255,0.7);">
                                    Ver estado de todas las solicitudes de firma de sus clientes
                                </p>
                                <button type="button" class="btn btn-light w-100 py-2 fw-bold mt-3" style="border-radius: 10px;">
                                    <i class="fas fa-pen-nib me-2"></i>Ir al Dashboard de Firmas
                                </button>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Tabla a pantalla completa -->
        <div class="table-container-custom">
            <div class="table-responsive">
                <table id="itemTable" class="table table-striped table-bordered table-custom">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag me-2"></i>ID</th>
                            <th><i class="fas fa-cogs me-2"></i>Tipo de Proceso</th>
                            <th><i class="fas fa-info-circle me-2"></i>Detalle</th>
                            <th><i class="fas fa-file-alt me-2"></i>Descripción</th>
                            <th><i class="fas fa-external-link-alt me-2"></i>Acción URL</th>
                            <th><i class="fas fa-sort-numeric-up me-2"></i>Orden</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>ID</th>
                            <th>Tipo de Proceso</th>
                            <th>Detalle</th>
                            <th>Descripción</th>
                            <th>Acción URL</th>
                            <th>Orden</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        <?php foreach ($items ?? [] as $item): ?>
                            <tr>
                                <td><?= esc($item['id']) ?></td>
                                <td><i class="fas fa-tag me-2 text-primary"></i><?= esc($item['tipo_proceso']) ?></td>
                                <td><i class="fas fa-bookmark me-2 text-warning"></i><?= esc($item['detalle']) ?></td>
                                <td><?= esc($item['descripcion']) ?></td>
                                <td>
                                    <a href="<?= base_url($item['accion_url']) ?>" target="_blank" class="btn btn-action-custom" title="Ir a <?= esc($item['detalle']) ?>">
                                        <i class="fas fa-external-link-alt me-1"></i>Acceder
                                    </a>
                                </td>
                                <td><?= esc($item['orden']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Botón de Cerrar Sesión -->
        <div class="logout-container-custom">
            <a href="<?= base_url('/logout') ?>" rel="noopener noreferrer">
                <button type="button" class="btn btn-logout-custom">
                    <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                </button>
            </a>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer-custom">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h5 class="fw-bold mb-3"><i class="fas fa-building me-2"></i>Cycloid Talent SAS</h5>
                    <p class="mb-2">Todos los derechos reservados © <span id="currentYear"></span></p>
                    <p class="mb-2"><i class="fas fa-id-card me-2"></i>NIT: 901.653.912</p>
                    <p class="mb-3">
                        <i class="fas fa-globe me-2"></i>Sitio oficial:
                        <a href="https://cycloidtalent.com/" target="_blank" rel="noopener noreferrer">https://cycloidtalent.com/</a>
                    </p>

                    <div class="mt-4">
                        <strong><i class="fas fa-share-alt me-2"></i>Nuestras Redes Sociales:</strong>
                        <div class="social-icons-custom">
                            <a href="https://www.facebook.com/CycloidTalent" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                                <img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" alt="Facebook">
                            </a>
                            <a href="https://co.linkedin.com/company/cycloid-talent" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn">
                                <img src="https://cdn-icons-png.flaticon.com/512/733/733561.png" alt="LinkedIn">
                            </a>
                            <a href="https://www.instagram.com/cycloid_talent?igsh=Nmo4d2QwZDg5dHh0" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                                <img src="https://cdn-icons-png.flaticon.com/512/733/733558.png" alt="Instagram">
                            </a>
                            <a href="https://www.tiktok.com/@cycloid_talent?_t=8qBSOu0o1ZN&_r=1" target="_blank" rel="noopener noreferrer" aria-label="TikTok">
                                <img src="https://cdn-icons-png.flaticon.com/512/3046/3046126.png" alt="TikTok">
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#itemTable').DataTable({
                dom: 'lfrtip', // length + filter + table + info + pagination
                order: [
                    [5, 'asc']
                ], // orden inicial por la sexta columna
                columnDefs: [{
                        targets: [0, 5],
                        visible: false
                    } // oculta ID y Orden
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                },
                initComplete: function() {
                    this.api().columns().every(function() {
                        var column = this;
                        if (!column.visible()) return;
                        var footerCell = $(column.footer()).empty();

                        // Aquí añadimos "Todos" como texto de la opción vacía
                        var select = $(
                                '<select class="form-select form-select-sm">' +
                                '<option value="">Todos</option>' +
                                '</select>'
                            ).appendTo(footerCell)
                            .on('change', function() {
                                var val = $.fn.dataTable.util.escapeRegex($(this).val());
                                column.search(val ? '^' + val + '$' : '', true, false).draw();
                            });

                        column.data().unique().sort().each(function(d) {
                            if (d) select.append('<option>' + d + '</option>');
                        });
                    });
                }

            });
        });
    </script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar Select2 para selector de clientes - Actas
            $('#selectClienteActas').select2({
                theme: 'bootstrap-5',
                placeholder: '-- Buscar cliente por nombre o NIT --',
                allowClear: true,
                width: '100%'
            });

            // Inicializar Select2 para selector de clientes - Comités
            $('#selectClienteComites').select2({
                theme: 'bootstrap-5',
                placeholder: '-- Buscar cliente por nombre o NIT --',
                allowClear: true,
                width: '100%'
            });

            // Habilitar/deshabilitar botón Actas según selección
            $('#selectClienteActas').on('change', function() {
                var clienteId = $(this).val();
                $('#btnIrActas').prop('disabled', !clienteId);
            });

            // Habilitar/deshabilitar botón Comités según selección
            $('#selectClienteComites').on('change', function() {
                var clienteId = $(this).val();
                $('#btnIrComites').prop('disabled', !clienteId);
            });

            // Abrir página de actas en nueva pestaña
            $('#btnIrActas').on('click', function() {
                var clienteId = $('#selectClienteActas').val();
                if (clienteId) {
                    window.open('<?= base_url('actas/') ?>' + clienteId, '_blank');
                }
            });

            // Abrir página de conformación de comités en nueva pestaña
            $('#btnIrComites').on('click', function() {
                var clienteId = $('#selectClienteComites').val();
                if (clienteId) {
                    window.open('<?= base_url('comites-elecciones/') ?>' + clienteId, '_blank');
                }
            });

            // Inicializar Select2 para selector de clientes - Acciones Correctivas
            $('#selectClienteAcciones').select2({
                theme: 'bootstrap-5',
                placeholder: '-- Buscar cliente por nombre o NIT --',
                allowClear: true,
                width: '100%'
            });

            // Habilitar/deshabilitar botón Acciones según selección
            $('#selectClienteAcciones').on('change', function() {
                var clienteId = $(this).val();
                $('#btnIrAcciones').prop('disabled', !clienteId);
            });

            // Abrir página de acciones correctivas en nueva pestaña
            $('#btnIrAcciones').on('click', function() {
                var clienteId = $('#selectClienteAcciones').val();
                if (clienteId) {
                    window.open('<?= base_url('acciones-correctivas/') ?>' + clienteId, '_blank');
                }
            });

            // Inicializar Select2 para selector de clientes - Indicadores SST
            $('#selectClienteIndicadores').select2({
                theme: 'bootstrap-5',
                placeholder: '-- Buscar cliente por nombre o NIT --',
                allowClear: true,
                width: '100%'
            });

            // Habilitar/deshabilitar botón Indicadores según selección
            $('#selectClienteIndicadores').on('change', function() {
                var clienteId = $(this).val();
                $('#btnIrIndicadores').prop('disabled', !clienteId);
            });

            // Abrir página de indicadores SST en nueva pestaña
            $('#btnIrIndicadores').on('click', function() {
                var clienteId = $('#selectClienteIndicadores').val();
                if (clienteId) {
                    window.open('<?= base_url('indicadores-sst/') ?>' + clienteId, '_blank');
                }
            });
        });
    </script>

</body>

</html>