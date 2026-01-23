<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contexto SST - Seleccionar Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 100%);
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
    </style>
</head>
<body class="bg-light">
    <div class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <span class="badge bg-white text-primary mb-2">
                        <i class="bi bi-database-fill me-1"></i>Datos Base para IA
                    </span>
                    <h1 class="mb-2">
                        <i class="bi bi-gear-fill me-2"></i>Contexto SST del Cliente
                    </h1>
                    <p class="mb-0 opacity-75">Configuracion de informacion base para generacion de documentos personalizados</p>
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
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i><?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Selector de cliente -->
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8">
                <div class="card border-0 shadow">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-3">
                            <i class="bi bi-building me-2 text-primary"></i>Seleccionar Cliente
                        </h5>

                        <?php if (empty($clientes)): ?>
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-circle me-2"></i>
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

        <!-- Informacion del modulo -->
        <div class="row justify-content-center mb-5">
            <div class="col-lg-10">
                <div class="alert alert-info border-0">
                    <h5 class="alert-heading"><i class="bi bi-info-circle me-2"></i>Modulo de Contexto del Cliente</h5>
                    <p class="mb-2">Este modulo permite configurar la informacion base de cada cliente que sera utilizada para:</p>
                    <ul class="mb-0">
                        <li><strong>Generacion de documentos con IA:</strong> Programas, politicas, procedimientos personalizados</li>
                        <li><strong>Calculo automatico de estandares:</strong> 7, 21 o 60 segun Resolucion 0312/2019</li>
                        <li><strong>Identificacion de peligros:</strong> Para programas de prevencion especificos</li>
                        <li><strong>Deteccion de transiciones:</strong> Alertas cuando cambia el nivel de estandares</li>
                        <li><strong>Configuracion de firmantes:</strong> Delegado SST y Representante Legal para firmas electronicas</li>
                    </ul>
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
                                <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                    <i class="bi bi-book text-success fs-4"></i>
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
                        <a href="<?= base_url('estandares/seleccionar-cliente') ?>" class="card quick-access-card border-0 shadow-sm text-decoration-none h-100">
                            <div class="card-body text-center py-4">
                                <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                    <i class="bi bi-check2-circle text-primary fs-4"></i>
                                </div>
                                <h6 class="text-dark mb-1">Cumplimiento PHVA</h6>
                                <small class="text-muted">Estado de estandares</small>
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

        <!-- Tabla de clientes con estado de contexto -->
        <div class="row justify-content-center mt-5">
            <div class="col-lg-12">
                <div class="card border-0 shadow">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-table me-2 text-primary"></i>Estado del Contexto SST por Cliente
                        </h5>
                        <span class="badge bg-secondary"><?= count($clientes) ?> cliente(s)</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="tablaClientes">
                                <thead class="table-light">
                                    <tr>
                                        <th>Cliente</th>
                                        <th>NIT</th>
                                        <th class="text-center">Trabajadores</th>
                                        <th class="text-center">Nivel Riesgo</th>
                                        <th class="text-center">Estandares</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($clientes as $cliente): ?>
                                        <tr>
                                            <td>
                                                <strong><?= esc($cliente['nombre_cliente']) ?></strong>
                                                <?php if (!empty($cliente['ciudad_cliente'])): ?>
                                                    <br><small class="text-muted"><?= esc($cliente['ciudad_cliente']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= esc($cliente['nit_cliente'] ?? '-') ?></td>
                                            <td class="text-center">
                                                <?php if ($cliente['tiene_contexto'] && !empty($cliente['contexto']['total_trabajadores'])): ?>
                                                    <span class="fw-bold"><?= $cliente['contexto']['total_trabajadores'] ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($cliente['tiene_contexto']):
                                                    $niveles = json_decode($cliente['contexto']['niveles_riesgo_arl'] ?? '[]', true) ?: [];
                                                    if (empty($niveles) && !empty($cliente['contexto']['nivel_riesgo_arl'])) {
                                                        $niveles = [$cliente['contexto']['nivel_riesgo_arl']];
                                                    }
                                                    if (!empty($niveles)):
                                                        foreach ($niveles as $nivel):
                                                            $colorRiesgo = in_array($nivel, ['IV', 'V']) ? 'danger' : (in_array($nivel, ['III']) ? 'warning' : 'success');
                                                ?>
                                                    <span class="badge bg-<?= $colorRiesgo ?>"><?= $nivel ?></span>
                                                <?php
                                                        endforeach;
                                                    else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php
                                                    endif;
                                                else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($cliente['tiene_contexto'] && !empty($cliente['contexto']['estandares_aplicables'])):
                                                    $est = $cliente['contexto']['estandares_aplicables'];
                                                    $colorEst = $est == 7 ? 'success' : ($est == 21 ? 'warning' : 'primary');
                                                ?>
                                                    <span class="badge bg-<?= $colorEst ?> fs-6"><?= $est ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($cliente['tiene_contexto']): ?>
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-check-circle me-1"></i>Configurado
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="bi bi-exclamation-circle me-1"></i>Pendiente
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <a href="<?= base_url('contexto/' . $cliente['id_cliente']) ?>"
                                                   class="btn btn-sm <?= $cliente['tiene_contexto'] ? 'btn-outline-primary' : 'btn-primary' ?>">
                                                    <?php if ($cliente['tiene_contexto']): ?>
                                                        <i class="bi bi-pencil me-1"></i>Editar
                                                    <?php else: ?>
                                                        <i class="bi bi-plus-circle me-1"></i>Configurar
                                                    <?php endif; ?>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
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
                    window.location.href = '<?= base_url('contexto') ?>/' + idCliente;
                }
            });

            // Inicializar DataTables
            $('#tablaClientes').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                pageLength: 10,
                order: [[5, 'asc'], [0, 'asc']], // Primero pendientes, luego alfabetico
                columnDefs: [
                    { orderable: false, targets: [6] }
                ]
            });
        });
    </script>
</body>
</html>
