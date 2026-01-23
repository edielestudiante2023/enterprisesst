<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cumplimiento PHVA - Seleccionar Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            padding: 3rem 0;
            color: white;
        }
        .select2-container--bootstrap-5 .select2-selection {
            min-height: 50px;
            font-size: 1.1rem;
            padding: 0.5rem;
        }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            line-height: 32px;
        }
        .quick-access-card {
            transition: all 0.3s ease;
        }
        .quick-access-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .phva-badge {
            display: inline-flex;
            gap: 0.25rem;
        }
        .phva-badge span {
            width: 28px;
            height: 28px;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: bold;
            color: white;
        }
        .phva-p { background-color: #3B82F6; }
        .phva-h { background-color: #10B981; }
        .phva-v { background-color: #F59E0B; }
        .phva-a { background-color: #EF4444; }
    </style>
</head>
<body class="bg-light">
    <div class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="phva-badge mb-2">
                        <span class="phva-p">P</span>
                        <span class="phva-h">H</span>
                        <span class="phva-v">V</span>
                        <span class="phva-a">A</span>
                    </div>
                    <h1 class="mb-2">
                        <i class="bi bi-check2-circle me-2"></i>Cumplimiento Estandares PHVA
                    </h1>
                    <p class="mb-0 opacity-75">Resolucion 0312 de 2019 - 60 Estandares Minimos del SG-SST</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="<?= base_url('consultant/dashboard') ?>" class="btn btn-outline-light">
                        <i class="bi bi-arrow-left me-1"></i>Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-5">
        <!-- Selector de cliente -->
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8">
                <div class="card border-0 shadow">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-3">
                            <i class="bi bi-building me-2 text-success"></i>Seleccionar Cliente
                        </h5>

                        <?php if (empty($clientes)): ?>
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                No tiene clientes asignados. Contacte al administrador.
                            </div>
                        <?php else: ?>
                            <select id="selectCliente" class="form-select form-select-lg" style="width: 100%;">
                                <option value="">Buscar cliente por nombre o NIT...</option>
                                <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?= $cliente['id_cliente'] ?>"
                                            data-nit="<?= esc($cliente['nit_cliente'] ?? '') ?>"
                                            data-ciudad="<?= esc($cliente['ciudad_cliente'] ?? '') ?>">
                                        <?= esc($cliente['nombre_cliente']) ?>
                                        <?php if (!empty($cliente['nit_cliente'])): ?>
                                            - NIT: <?= esc($cliente['nit_cliente']) ?>
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted mt-2 d-block">
                                <i class="bi bi-search me-1"></i>
                                <?= count($clientes) ?> cliente(s) activo(s). Escriba para buscar.
                            </small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Accesos rapidos -->
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <h5 class="text-muted mb-3">
                    <i class="bi bi-lightning-fill me-2"></i>Accesos Rapidos
                </h5>
                <div class="row g-3">
                    <div class="col-md-4">
                        <a href="<?= base_url('estandares/catalogo') ?>" class="card quick-access-card border-0 shadow-sm text-decoration-none h-100">
                            <div class="card-body text-center py-4">
                                <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                    <i class="bi bi-book text-primary fs-4"></i>
                                </div>
                                <h6 class="text-dark mb-1">Catalogo 60 Estandares</h6>
                                <small class="text-muted">Resolucion 0312/2019</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="<?= base_url('documentacion/seleccionar-cliente') ?>" class="card quick-access-card border-0 shadow-sm text-decoration-none h-100">
                            <div class="card-body text-center py-4">
                                <div class="rounded-circle bg-warning bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                    <i class="bi bi-folder text-warning fs-4"></i>
                                </div>
                                <h6 class="text-dark mb-1">Documentacion SST</h6>
                                <small class="text-muted">Gestion documental</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="<?= base_url('contexto') ?>" class="card quick-access-card border-0 shadow-sm text-decoration-none h-100">
                            <div class="card-body text-center py-4">
                                <div class="rounded-circle bg-info bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                    <i class="bi bi-gear text-info fs-4"></i>
                                </div>
                                <h6 class="text-dark mb-1">Contexto SST</h6>
                                <small class="text-muted">Configurar datos del cliente</small>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info de niveles -->
        <div class="row justify-content-center mt-5">
            <div class="col-lg-10">
                <div class="card border-0 bg-light">
                    <div class="card-body">
                        <h6 class="text-muted mb-3">
                            <i class="bi bi-info-circle me-2"></i>Niveles de Estandares segun Resolucion 0312/2019
                        </h6>
                        <div class="row text-center">
                            <div class="col-md-4">
                                <span class="badge bg-success fs-6 mb-2">7 Estandares</span>
                                <p class="small mb-0">Hasta 10 trabajadores<br>Riesgo I, II o III</p>
                            </div>
                            <div class="col-md-4">
                                <span class="badge bg-warning text-dark fs-6 mb-2">21 Estandares</span>
                                <p class="small mb-0">11 a 50 trabajadores<br>Riesgo I, II o III</p>
                            </div>
                            <div class="col-md-4">
                                <span class="badge bg-primary fs-6 mb-2">60 Estandares</span>
                                <p class="small mb-0">Mas de 50 trabajadores<br>O Riesgo IV o V</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#selectCliente').select2({
                theme: 'bootstrap-5',
                placeholder: 'Buscar cliente por nombre o NIT...',
                allowClear: true,
                language: {
                    noResults: function() {
                        return "No se encontraron clientes";
                    },
                    searching: function() {
                        return "Buscando...";
                    }
                }
            });

            // Redirigir al seleccionar cliente
            $('#selectCliente').on('select2:select', function(e) {
                var idCliente = e.params.data.id;
                if (idCliente) {
                    window.location.href = '<?= base_url('estandares') ?>/' + idCliente;
                }
            });
        });
    </script>
</body>
</html>
