<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Acceso Rapido</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        :root {
            --primary-dark: #1c2437;
            --secondary-dark: #2c3e50;
            --gold-primary: #bd9751;
            --gold-secondary: #d4af37;
            --white-primary: #ffffff;
            --gradient-bg: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }

        body {
            background: var(--gradient-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .navbar-custom {
            background: #fff;
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
        }

        .content-wrapper {
            margin-top: 120px;
            padding: 40px 20px;
        }

        .welcome-banner {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--secondary-dark) 50%, var(--gold-primary) 100%);
            padding: 40px;
            border-radius: 25px;
            text-align: center;
            color: var(--white-primary);
            box-shadow: 0 20px 60px rgba(28, 36, 55, 0.4);
            margin-bottom: 40px;
        }

        .welcome-banner h1 {
            font-size: 2.5rem;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .card-custom {
            background: var(--white-primary);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(28, 36, 55, 0.15);
            padding: 30px;
            margin-bottom: 30px;
        }

        .btn-open-all {
            background: linear-gradient(135deg, var(--gold-primary) 0%, var(--gold-secondary) 100%);
            border: none;
            color: var(--white-primary);
            border-radius: 12px;
            padding: 15px 40px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(189, 151, 81, 0.3);
        }

        .btn-open-all:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(189, 151, 81, 0.5);
            color: var(--white-primary);
        }

        .btn-sync-all {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--secondary-dark) 100%);
            border: 2px solid var(--gold-primary);
            color: var(--white-primary);
            border-radius: 12px;
            padding: 15px 30px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(28, 36, 55, 0.3);
        }

        .btn-sync-all:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(28, 36, 55, 0.5);
            color: var(--gold-secondary);
            border-color: var(--gold-secondary);
        }

        .btn-sync-all.syncing i {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .view-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            border: 2px solid rgba(189, 151, 81, 0.2);
        }

        .view-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(28, 36, 55, 0.15);
            border-color: var(--gold-primary);
        }

        .view-card h5 {
            color: var(--primary-dark);
            font-weight: 600;
            margin-bottom: 10px;
        }

        .view-card p {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0;
        }

        .view-icon {
            font-size: 2rem;
            color: var(--gold-primary);
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <div class="header-logos-custom d-flex justify-content-between align-items-center w-100">
                <div>
                    <a href="<?= base_url('/dashboard') ?>">
                        <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo">
                    </a>
                </div>
                <div>
                    <a href="https://cycloidtalent.com/index.php/consultoria-sst" target="_blank">
                        <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo">
                    </a>
                </div>
                <div>
                    <a href="https://cycloidtalent.com/" target="_blank">
                        <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo">
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="content-wrapper">
        <div class="container">
            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <i class="fas fa-rocket fa-3x mb-3"></i>
                <h1><i class="fas fa-bolt me-2"></i>Dashboard de Acceso Rapido</h1>
                <p>Accede a todas tus vistas de gestion con un solo clic</p>
                <p><i class="fas fa-user me-2"></i>Sesion: <strong><?= session()->get('nombre_usuario') ?? 'Usuario' ?></strong></p>
            </div>

            <!-- Cliente Selector Card -->
            <div class="card-custom">
                <h3 class="mb-4"><i class="fas fa-user-check me-2" style="color: var(--gold-primary);"></i>Seleccionar Cliente Global</h3>
                <div class="row align-items-end">
                    <div class="col-md-6">
                        <label for="globalClientSelect" class="form-label fw-bold">Cliente:</label>
                        <select id="globalClientSelect" class="form-select">
                            <option value="">Seleccione un cliente</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?= $client['id_cliente'] ?>"><?= esc($client['nombre_cliente']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">El cliente seleccionado se aplicara a todas las vistas al abrirlas</small>
                    </div>
                    <div class="col-md-6 text-end">
                        <button id="openAllViews" class="btn btn-open-all">
                            <i class="fas fa-external-link-alt me-2"></i>Abrir Todas las Vistas
                        </button>
                    </div>
                </div>
                <div class="alert alert-warning mt-3 mb-0" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Importante:</strong> Para abrir todas las vistas, debe habilitar los pop-ups en su navegador.
                    Si solo se abre una ventana, busque el icono de pop-up bloqueado en la barra de direcciones y seleccione "Permitir siempre".
                </div>
            </div>

            <!-- Vistas Disponibles -->
            <div class="card-custom">
                <h3 class="mb-4"><i class="fas fa-th-large me-2" style="color: var(--gold-primary);"></i>Vistas Disponibles</h3>
                <div class="row">
                    <div class="col-md-6 col-lg-4">
                        <div class="view-card text-center">
                            <i class="fas fa-file-alt view-icon"></i>
                            <h5>Lista de Reportes</h5>
                            <p>Gestion de reportes del sistema</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="view-card text-center">
                            <i class="fas fa-tasks view-icon"></i>
                            <h5>Plan de Trabajo</h5>
                            <p>Administracion del plan de trabajo anual</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="view-card text-center">
                            <i class="fas fa-calendar-alt view-icon"></i>
                            <h5>Cronograma Capacitacion</h5>
                            <p>Programacion de capacitaciones</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="view-card text-center">
                            <i class="fas fa-clock view-icon"></i>
                            <h5>Vencimientos</h5>
                            <p>Control de vencimientos y fechas</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="view-card text-center">
                            <i class="fas fa-exclamation-circle view-icon"></i>
                            <h5>Pendientes</h5>
                            <p>Tareas y actividades pendientes</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="view-card text-center">
                            <i class="fas fa-clipboard-check view-icon"></i>
                            <h5>Evaluaciones</h5>
                            <p>Evaluaciones y estandares</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Boton Regresar -->
            <div class="text-center">
                <a href="<?= base_url('/consultor/dashboard') ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Regresar al Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#globalClientSelect').select2({
                placeholder: 'Seleccione un cliente',
                allowClear: true,
                width: '100%'
            });

            var storedClient = localStorage.getItem('selectedClient');
            if (storedClient) {
                $('#globalClientSelect').val(storedClient).trigger('change');
            }

            $('#globalClientSelect').on('change', function() {
                var clientId = $(this).val();
                if (clientId) {
                    localStorage.setItem('selectedClient', clientId);
                } else {
                    localStorage.removeItem('selectedClient');
                }
            });

            var syncChannel = new BroadcastChannel('quick_access_sync');

            $('#openAllViews').on('click', function() {
                var clientId = $('#globalClientSelect').val();

                if (!clientId) {
                    alert('Por favor seleccione un cliente primero');
                    return;
                }

                localStorage.setItem('selectedClient', clientId);

                var views = [
                    '<?= base_url('/reportList') ?>',
                    '<?= base_url('/pta-cliente-nueva/list') ?>',
                    '<?= base_url('/listcronogCapacitacion') ?>',
                    '<?= base_url('/vencimientos') ?>',
                    '<?= base_url('/listPendientes') ?>',
                    '<?= base_url('/listEvaluaciones') ?>'
                ];

                views.forEach(function(url, index) {
                    setTimeout(function() {
                        window.open(url, '_blank');
                    }, index * 100);
                });
            });
        });
    </script>
</body>

</html>
