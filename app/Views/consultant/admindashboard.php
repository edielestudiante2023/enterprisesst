<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administración PH</title>
    <!-- Favicon -->
    <link rel="icon" href="<?= base_url('favicon.ico') ?>" type="image/x-icon">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
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
            --warning-orange: #f39c12;
            --warning-orange-hover: #e67e22;
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
            min-height: 100vh;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--gradient-bg);
            z-index: -1;
        }

        /* Navbar moderna con estilo consistente */
        .navbar-custom {
            background: #fff;
            /* Fondo blanco */
            backdrop-filter: blur(20px);
            box-shadow: 0 8px 32px rgba(28, 36, 55, 0.3);
            padding: 20px 0;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            border-bottom: 2px solid var(--gold-primary);
        }

        .header-logos-custom img {
            max-height: 70px;
            margin-right: 20px;
            filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.3));
            transition: all 0.3s ease;
        }

        .header-logos-custom img:hover {
            transform: translateY(-3px) scale(1.05);
            filter: drop-shadow(0 8px 20px rgba(189, 151, 81, 0.4));
        }

        /* Content wrapper con margin para navbar fija */
        .content-wrapper-custom {
            margin-top: 120px;
            min-height: calc(100vh - 200px);
        }

        /* Banner de bienvenida moderno */
        .welcome-banner-custom {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--secondary-dark) 50%, var(--gold-primary) 100%);
            padding: 40px;
            border-radius: 25px;
            text-align: center;
            color: var(--white-primary);
            box-shadow: 0 20px 60px rgba(28, 36, 55, 0.4);
            margin-bottom: 40px;
            border: 1px solid rgba(189, 151, 81, 0.3);
            animation: fadeInUp-custom 1s ease-out;
        }

        .welcome-banner-custom::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: rotate-custom 12s linear infinite;
            z-index: 0;
        }

        .welcome-banner-custom::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(189, 151, 81, 0.1) 50%, transparent 70%);
            animation: shimmer-custom 3s ease-in-out infinite;
            z-index: 1;
        }

        .welcome-banner-custom h3 {
            font-size: 2.5rem;
            margin-bottom: 15px;
            font-weight: 700;
            position: relative;
            z-index: 2;
            background: linear-gradient(135deg, var(--white-primary) 0%, var(--gold-secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .welcome-banner-custom p {
            font-size: 1.4rem;
            position: relative;
            z-index: 2;
            color: var(--white-secondary);
            margin-bottom: 0;
        }

        /* Card contenedor de tabla */
        .table-card-custom {
            background: var(--white-primary);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(28, 36, 55, 0.15);
            padding: 30px;
            margin-bottom: 40px;
            border: 1px solid rgba(189, 151, 81, 0.2);
            backdrop-filter: blur(20px);
            animation: fadeInUp-custom 1s ease-out 0.3s both;
        }

        /* Tabla moderna */
        .table-custom {
            width: 100% !important;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(28, 36, 55, 0.1);
        }

        .table-custom thead {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--secondary-dark) 100%);
            color: var(--white-primary);
        }

        .table-custom thead th {
            padding: 20px 15px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
            position: relative;
            font-size: 0.9rem;
            cursor: pointer;
        }

        .table-custom thead th::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--gold-primary) 0%, var(--gold-secondary) 100%);
        }

        .table-custom tbody tr {
            transition: all 0.3s ease;
            border-bottom: 1px solid rgba(28, 36, 55, 0.1);
        }

        .table-custom tbody tr:hover {
            background: linear-gradient(135deg, rgba(189, 151, 81, 0.05) 0%, rgba(212, 175, 55, 0.05) 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(28, 36, 55, 0.1);
        }

        .table-custom tbody td {
            padding: 18px 15px;
            border: none;
            vertical-align: middle;
        }

        /* Footer de tabla para filtros */
        .table-custom tfoot {
            background: linear-gradient(135deg, var(--white-secondary) 0%, #e9ecef 100%);
        }

        .table-custom tfoot th {
            padding: 12px 8px;
            background: transparent;
            border: none;
            font-weight: 500;
        }

        .table-custom tfoot th::after {
            display: none;
        }

        /* Botones de acción modernos */
        .btn-action-custom {
            background: linear-gradient(135deg, var(--gold-primary) 0%, var(--gold-secondary) 100%);
            border: none;
            color: var(--white-primary);
            border-radius: 12px;
            padding: 10px 15px;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin-right: 5px;
        }

        .btn-action-custom::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .btn-action-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(189, 151, 81, 0.4);
            color: var(--white-primary);
        }

        .btn-action-custom:hover::before {
            left: 100%;
        }

        /* Botón de editar */
        .btn-edit-custom {
            background: linear-gradient(135deg, var(--warning-orange) 0%, var(--warning-orange-hover) 100%);
            border: none;
            color: var(--white-primary);
            border-radius: 12px;
            padding: 8px 12px;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin-right: 5px;
        }

        .btn-edit-custom::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .btn-edit-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(243, 156, 18, 0.4);
            color: var(--white-primary);
        }

        .btn-edit-custom:hover::before {
            left: 100%;
        }

        /* Badge para rol */
        .role-badge-custom {
            background: linear-gradient(135deg, var(--secondary-dark) 0%, var(--primary-dark) 100%);
            color: var(--white-primary);
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
        }

        /* Badge para tipo de proceso */
        .process-badge-custom {
            background: linear-gradient(135deg, var(--gold-primary) 0%, var(--gold-secondary) 100%);
            color: var(--white-primary);
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
        }

        /* Botón de logout moderno */
        .btn-logout-custom {
            background: linear-gradient(135deg, var(--logout-red) 0%, var(--logout-red-hover) 100%);
            border: none;
            color: var(--white-primary);
            border-radius: 25px;
            padding: 15px 40px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(255, 77, 77, 0.3);
            animation: fadeInUp-custom 1s ease-out 0.6s both;
        }

        .btn-logout-custom::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .btn-logout-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(255, 77, 77, 0.5);
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
            border-top: 3px solid var(--gold-primary);
            margin-top: 60px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .footer-custom::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--gold-primary) 0%, var(--gold-secondary) 50%, var(--gold-primary) 100%);
            animation: shimmer-custom 3s ease-in-out infinite;
        }

        .footer-custom a {
            color: var(--gold-secondary);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .footer-custom a:hover {
            color: var(--white-primary);
            text-shadow: 0 0 10px var(--gold-primary);
        }

        .social-icons-custom a {
            display: inline-block;
            transition: all 0.3s ease;
            margin: 0 10px;
        }

        .social-icons-custom a:hover {
            transform: translateY(-3px) scale(1.1);
            filter: drop-shadow(0 5px 15px rgba(189, 151, 81, 0.5));
        }

        /* DataTables styling */
        .dataTables_wrapper {
            margin-top: 20px;
        }

        .dataTables_filter input {
            border-radius: 12px;
            border: 2px solid rgba(189, 151, 81, 0.3);
            padding: 10px 15px;
            transition: all 0.3s ease;
        }

        .dataTables_filter input:focus {
            outline: none;
            border-color: var(--gold-primary);
            box-shadow: 0 0 15px rgba(189, 151, 81, 0.3);
        }

        .dataTables_length select {
            border-radius: 12px;
            border: 2px solid rgba(189, 151, 81, 0.3);
            padding: 8px 12px;
        }

        .page-link {
            border-radius: 12px;
            margin: 0 2px;
            border: 2px solid rgba(189, 151, 81, 0.3);
            color: var(--primary-dark);
            transition: all 0.3s ease;
        }

        .page-link:hover {
            background: var(--gold-primary);
            border-color: var(--gold-primary);
            color: var(--white-primary);
            transform: translateY(-2px);
        }

        .page-item.active .page-link {
            background: linear-gradient(135deg, var(--gold-primary) 0%, var(--gold-secondary) 100%);
            border-color: var(--gold-primary);
        }

        /* Filtros del footer de tabla */
        tfoot select {
            border-radius: 10px;
            border: 2px solid rgba(189, 151, 81, 0.3);
            padding: 8px 12px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            width: 100%;
            background-color: var(--white-primary);
            color: var(--primary-dark);
        }

        tfoot select:focus {
            outline: none;
            border-color: var(--gold-primary);
            box-shadow: 0 0 10px rgba(189, 151, 81, 0.3);
        }

        /* Descripción con ellipsis */
        .description-col-custom {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            position: relative;
        }

        .description-col-custom:hover {
            white-space: normal;
            overflow: visible;
            position: static;
            background: var(--white-secondary);
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(28, 36, 55, 0.1);
        }

        /* Animaciones */
        @keyframes fadeInUp-custom {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes rotate-custom {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        @keyframes shimmer-custom {

            0%,
            100% {
                opacity: 0;
                transform: translateX(-100%);
            }

            50% {
                opacity: 1;
                transform: translateX(100%);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .content-wrapper-custom {
                margin-top: 100px;
                padding: 15px;
            }

            .welcome-banner-custom {
                padding: 25px;
                margin-bottom: 25px;
            }

            .welcome-banner-custom h3 {
                font-size: 1.8rem;
            }

            .welcome-banner-custom p {
                font-size: 1.1rem;
            }

            .table-card-custom {
                padding: 20px;
                border-radius: 15px;
            }

            .header-logos-custom img {
                max-height: 50px;
                margin-right: 10px;
            }

            .navbar-custom {
                padding: 15px 0;
            }

            .header-logos-custom {
                flex-direction: column;
                align-items: center;
                gap: 10px;
            }

            .btn-edit-custom,
            .btn-action-custom {
                margin-bottom: 5px;
                font-size: 0.8rem;
                padding: 6px 10px;
            }
        }

        @media (max-width: 576px) {
            .content-wrapper-custom {
                margin-top: 200px;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar moderna -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-custom">
            <div class="container-fluid">
                <div class="header-logos-custom d-flex justify-content-between align-items-center w-100">
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

    <!-- Content wrapper -->
    <div class="content-wrapper-custom">
        <!-- Contenido principal -->
        <main class="container-fluid content">
            <!-- Banner de Bienvenida -->
            <div class="welcome-banner-custom">
                <i class="fas fa-shield-alt fa-3x mb-3" style="color: var(--gold-secondary);"></i>
                <h3><i class="fas fa-users-cog me-2"></i>Dashboard de Administración Propiedad Horizontal </h3>
                <p><i class="fas fa-tools me-2"></i>Centro de Control - Gestión Avanzada de Procesos SST</p>
            </div>

            <!-- Usuario en sesión -->
            <?php if (isset($usuario) && $usuario): ?>
            <div class="text-center mb-4">
                <div class="d-inline-block px-4 py-2 rounded-pill" style="background: linear-gradient(135deg, var(--primary-dark), var(--secondary-dark)); box-shadow: 0 4px 15px rgba(28, 36, 55, 0.3);">
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
                <a href="<?= base_url('/quick-access') ?>" target="_blank" rel="noopener noreferrer">
                    <button type="button" class="btn btn-logout-custom me-3" style="background: linear-gradient(135deg, var(--gold-primary), var(--gold-secondary)); border: none;" aria-label="Acceso Rápido">
                        <i class="fas fa-bolt me-2"></i>Acceso Rápido
                    </button>
                </a>
                <a href="<?= base_url('/admin/users') ?>" rel="noopener noreferrer">
                    <button type="button" class="btn btn-logout-custom me-3" style="background: linear-gradient(135deg, #667eea, #764ba2); border: none;" aria-label="Gestión de Usuarios">
                        <i class="fas fa-users-cog me-2"></i>Gestión de Usuarios
                    </button>
                </a>
                <a href="<?= base_url('/admin/usage') ?>" rel="noopener noreferrer">
                    <button type="button" class="btn btn-logout-custom" style="background: linear-gradient(135deg, #11998e, #38ef7d); border: none;" aria-label="Consumo de Plataforma">
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
                        <a href="<?= base_url('consultant/dashboard-estandares') ?>" target="_blank" class="btn w-100 py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; font-weight: 600; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3); border-radius: 12px; transition: all 0.3s ease;">
                            <i class="fas fa-chart-pie fa-lg mb-2 d-block"></i>
                            Estándares Mínimos
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="<?= base_url('consultant/dashboard-capacitaciones') ?>" target="_blank" class="btn w-100 py-3" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border: none; font-weight: 600; box-shadow: 0 4px 15px rgba(240, 147, 251, 0.3); border-radius: 12px; transition: all 0.3s ease;">
                            <i class="fas fa-graduation-cap fa-lg mb-2 d-block"></i>
                            Capacitaciones
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="<?= base_url('consultant/dashboard-plan-trabajo') ?>" target="_blank" class="btn w-100 py-3" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; border: none; font-weight: 600; box-shadow: 0 4px 15px rgba(79, 172, 254, 0.3); border-radius: 12px; transition: all 0.3s ease;">
                            <i class="fas fa-tasks fa-lg mb-2 d-block"></i>
                            Plan de Trabajo
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="<?= base_url('consultant/dashboard-pendientes') ?>" target="_blank" class="btn w-100 py-3" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; border: none; font-weight: 600; box-shadow: 0 4px 15px rgba(250, 112, 154, 0.3); border-radius: 12px; transition: all 0.3s ease;">
                            <i class="fas fa-clipboard-list fa-lg mb-2 d-block"></i>
                            Pendientes
                        </a>
                    </div>
                </div>
            </div>

            <!-- Card contenedor de tabla -->
            <div class="table-card-custom">
                <div class="d-flex align-items-center mb-4">
                    <i class="fas fa-database fa-2x me-3" style="color: var(--gold-primary);"></i>
                    <h2 class="mb-0" style="color: var(--primary-dark); font-weight: 700;">Panel de Administración de Procesos</h2>
                </div>

                <!-- Tabla moderna -->
                <div class="table-responsive">
                    <table id="itemTable" class="table table-striped table-bordered table-custom">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag me-2"></i>ID</th>
                                <th><i class="fas fa-user-tag me-2"></i>Rol</th>
                                <th><i class="fas fa-cogs me-2"></i>Tipo de Proceso</th>
                                <th><i class="fas fa-info-circle me-2"></i>Detalle</th>
                                <th><i class="fas fa-file-text me-2"></i>Descripción</th>
                                <th><i class="fas fa-external-link-alt me-2"></i>Acción URL</th>
                                <th><i class="fas fa-sort-numeric-up me-2"></i>Orden</th>
                                <th><i class="fas fa-tools me-2"></i>Actions</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                            </tr>
                        </tfoot>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?= esc($item['id']) ?></td>
                                    <td>
                                        <span class="role-badge-custom">
                                            <i class="fas fa-user-shield me-1"></i><?= esc($item['rol']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="process-badge-custom">
                                            <i class="fas fa-tag me-1"></i><?= esc($item['tipo_proceso']) ?>
                                        </span>
                                    </td>
                                    <td><?= esc($item['detalle']) ?></td>
                                    <td>
                                        <div class="description-col-custom" title="<?= esc($item['descripcion']) ?>">
                                            <?= esc($item['descripcion']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="<?= base_url($item['accion_url']) ?>" target="_blank" class="btn btn-action-custom btn-sm" title="Ir a <?= esc($item['detalle']) ?>">
                                            <i class="fas fa-external-link-alt me-1"></i>Abrir
                                        </a>
                                    </td>
                                    <td><?= esc($item['orden']) ?></td>
                                    <td>
                                        <a href="<?= base_url('consultant/edititemdashboar/' . $item['id']) ?>" class="btn btn-edit-custom btn-sm" target="_blank" title="Editar elemento">
                                            <i class="fas fa-edit me-1"></i>Editar
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Botón de Cerrar Sesión -->
            <div class="text-center">
                <a href="<?= base_url('/logout') ?>" rel="noopener noreferrer">
                    <button type="button" class="btn btn-logout-custom" aria-label="Cerrar Sesión">
                        <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                    </button>
                </a>
            </div>
        </main>
    </div>

    <!-- Footer moderno -->
    <footer class="footer-custom">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <p class="fw-bold mb-2 fs-5">
                        <i class="fas fa-building me-2"></i>Cycloid Talent SAS
                    </p>
                    <p class="mb-2">Todos los derechos reservados © <span id="currentYear"></span></p>
                    <p class="mb-2"><i class="fas fa-id-card me-2"></i>NIT: 901.653.912</p>
                    <p class="mb-0">
                        <i class="fas fa-globe me-2"></i>Sitio oficial:
                        <a href="https://cycloidtalent.com/" target="_blank" rel="noopener noreferrer">https://cycloidtalent.com/</a>
                    </p>
                </div>
                <div class="col-md-4">
                    <div class="mt-3">
                        <strong><i class="fas fa-share-alt me-2"></i>Nuestras Redes Sociales:</strong>
                        <div class="d-flex justify-content-center gap-3 mt-3 social-icons-custom">
                            <a href="https://www.facebook.com/CycloidTalent" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                                <img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" alt="Facebook" style="height: 32px; width: 32px;">
                            </a>
                            <a href="https://co.linkedin.com/company/cycloid-talent" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn">
                                <img src="https://cdn-icons-png.flaticon.com/512/733/733561.png" alt="LinkedIn" style="height: 32px; width: 32px;">
                            </a>
                            <a href="https://www.instagram.com/cycloid_talent?igsh=Nmo4d2QwZDg5dHh0" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                                <img src="https://cdn-icons-png.flaticon.com/512/733/733558.png" alt="Instagram" style="height: 32px; width: 32px;">
                            </a>
                            <a href="https://www.tiktok.com/@cycloid_talent?_t=8qBSOu0o1ZN&_r=1" target="_blank" rel="noopener noreferrer" aria-label="TikTok">
                                <img src="https://cdn-icons-png.flaticon.com/512/3046/3046126.png" alt="TikTok" style="height: 32px; width: 32px;">
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
            var table = $('#itemTable').DataTable({
                dom: 'lfrtip', // length, filter, processing, table, info, paging
                searching: true, // activa la búsqueda global
                order: [
                    [6, 'asc']
                ], // orden inicial por la columna "Orden" oculta
                columnDefs: [{
                        targets: 0,
                        visible: false
                    }, // oculta ID
                    {
                        targets: 6,
                        visible: false
                    }, // oculta Orden
                    {
                        targets: 7,
                        orderable: false,
                        searchable: false
                    } // sin orden ni búsqueda en acciones
                ],
                language: {
                    url: "//cdn.datatables.net/plug‑ins/1.13.4/i18n/es‑ES.json"
                },
                initComplete: function() {
                    // Aplica filtro a las columnas 1,2,3 y 4
                    this.api().columns([1, 2, 3, 4]).every(function() {
                        var column = this;
                        // Vaciamos el footer y creamos el select
                        var select = $('<select class="form-select form-select-sm"><option value="">Todos</option></select>')
                            .appendTo($(column.footer()).empty())
                            .on('change', function() {
                                var val = $.fn.dataTable.util.escapeRegex($(this).val());
                                // BUSCA exacto con regex ^valor$
                                column
                                    .search(val ? '^' + val + '$' : '', true, false)
                                    .draw();
                            });
                        // Rellena opciones únicas
                        column.data().unique().sort().each(function(d) {
                            if (d && d.trim() !== '') {
                                // si trae <span>…</span>, extrae solo el texto
                                var text = $('<div>').html(d).text().trim();
                                select.append('<option value="' + text + '">' + text + '</option>');
                            }
                        });
                    });
                }
            });

            // Actualiza año en el footer
            $('#currentYear').text(new Date().getFullYear());
        });
    </script>


</body>

</html>