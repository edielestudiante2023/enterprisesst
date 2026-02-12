<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?: ($title ?? 'EnterpriseSST') ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary-color: #1c2437;
            --secondary-color: #bd9751;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar-brand img {
            max-height: 50px;
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, #2c3e50 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .card {
            border: none;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            border-radius: 12px;
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid #eee;
            font-weight: 600;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, #2c3e50 100%);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2c3e50 0%, var(--primary-color) 100%);
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--secondary-color) 0%, #d4af37 100%);
            border: none;
            color: white;
        }

        .btn-warning:hover {
            background: linear-gradient(135deg, #d4af37 0%, var(--secondary-color) 100%);
            color: white;
        }

        .badge {
            font-weight: 500;
        }

        .table th {
            background: #f8f9fa;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .breadcrumb {
            background: transparent;
            padding: 0;
        }

        .breadcrumb-item a {
            color: var(--secondary-color);
            text-decoration: none;
        }

        .breadcrumb-item a:hover {
            color: var(--primary-color);
        }

        footer {
            background: var(--primary-color);
            color: #ffffff;
            padding: 15px 0;
            margin-top: auto;
        }

        .alert-dismissible .btn-close {
            padding: 1rem;
        }

        /* Estilos para formularios */
        .form-control:focus, .form-select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(189, 151, 81, 0.25);
        }

        .form-label {
            font-weight: 500;
            color: #495057;
        }

        /* Animaciones */
        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Colores custom para categorias que no tienen clase Bootstrap bg-* */
        .bg-orange { background-color: #fd7e14 !important; color: #fff !important; }
        .text-orange { color: #fd7e14 !important; }
        .bg-purple { background-color: #6f42c1 !important; color: #fff !important; }
        .text-purple { color: #6f42c1 !important; }
        .bg-teal { background-color: #20c997 !important; color: #fff !important; }
        .text-teal { color: #20c997 !important; }
        .border-orange { border-color: #fd7e14 !important; }
        .border-purple { border-color: #6f42c1 !important; }
        .border-teal { border-color: #20c997 !important; }
    </style>

    <?= $this->renderSection('styles') ?>
</head>
<body class="d-flex flex-column min-vh-100">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= base_url() ?>">
                <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="EnterpriseSST" onerror="this.onerror=null; this.src=''; this.alt='EnterpriseSST';">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($cliente)): ?>
                    <li class="nav-item">
                        <span class="nav-link text-light">
                            <i class="bi bi-building me-1"></i>
                            <?= esc($cliente['nombre_cliente'] ?? '') ?>
                        </span>
                    </li>
                    <?php endif; ?>
                    <?php
                    $sessionRole = session()->get('role');
                    $dashboardUrl = match($sessionRole) {
                        'miembro' => 'miembro/dashboard',
                        'consultant' => 'consultor/dashboard',
                        'admin' => 'admin/dashboard',
                        default => 'dashboard'
                    };
                    ?>
                    <?php if ($sessionRole === 'miembro' && isset($miembro)): ?>
                    <li class="nav-item">
                        <span class="nav-link text-light">
                            <i class="bi bi-person-circle me-1"></i>
                            <?= esc($miembro['nombre_completo'] ?? session()->get('email_miembro')) ?>
                        </span>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url($dashboardUrl) ?>">
                            <i class="bi bi-house me-1"></i> Dashboard
                        </a>
                    </li>
                    <?php if (in_array($sessionRole, ['admin', 'consultant'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('/admin/users') ?>">
                            <i class="bi bi-people me-1"></i> Usuarios
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (session()->get('isLoggedIn')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('logout') ?>">
                            <i class="bi bi-box-arrow-right me-1"></i> Salir
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <div class="container-fluid mt-3">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('warning')): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>
                <?= session()->getFlashdata('warning') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('info')): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle me-2"></i>
                <?= session()->getFlashdata('info') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Main Content -->
    <main class="flex-grow-1">
        <?= $this->renderSection('content') ?>
    </main>

    <!-- Footer -->
    <footer class="text-center">
        <p class="mb-0">&copy; <?= date('Y') ?> Cycloid Talent SAS. Todos los derechos reservados.</p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery (opcional, para algunas funcionalidades) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <?= $this->renderSection('scripts') ?>
</body>
</html>
