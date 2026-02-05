<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acciones Correctivas - Seleccionar Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        body { background-color: #f8f9fa; }
        .cliente-card { transition: all 0.2s; cursor: pointer; }
        .cliente-card:hover { transform: translateY(-3px); box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important; }
        .resumen-badge { font-size: 0.75rem; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= base_url('dashboard') ?>">
                <i class="bi bi-shield-check me-2"></i>EnterpriseSST
            </a>
            <div class="d-flex align-items-center">
                <span class="text-light me-3">
                    <i class="bi bi-person-circle me-1"></i><?= esc($usuario['nombre'] ?? 'Usuario') ?>
                </span>
                <a href="<?= base_url('dashboard') ?>" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="mb-1">
                    <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                    Modulo de Acciones Correctivas
                </h2>
                <p class="text-muted">
                    Gestion de hallazgos y acciones CAPA - Numerales 7.1.1, 7.1.2, 7.1.3, 7.1.4 (Resolucion 0312/2019)
                </p>
            </div>
        </div>

        <!-- Buscador rapido -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Seleccionar Cliente</label>
                        <select class="form-select select2-cliente" id="cliente_rapido">
                            <option value="">Buscar cliente...</option>
                            <?php foreach ($clientes as $cliente): ?>
                            <option value="<?= $cliente['id_cliente'] ?>">
                                <?= esc($cliente['nombre_cliente']) ?> - NIT: <?= esc($cliente['nit_cliente']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mt-3 mt-md-0">
                        <label class="form-label">&nbsp;</label>
                        <button class="btn btn-primary w-100 d-block" id="btn_ir_cliente" disabled>
                            <i class="bi bi-arrow-right-circle me-1"></i>Ir al Dashboard del Cliente
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Clientes con Resumen -->
        <div class="row">
            <?php foreach ($clientes as $cliente): ?>
            <?php
            $resumen = $cliente['resumen_acciones'] ?? [];
            $tieneVencidas = ($resumen['acciones_vencidas'] ?? 0) > 0;
            $tieneAbiertas = ($resumen['hallazgos_abiertos'] ?? 0) > 0;
            ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card border-0 shadow-sm cliente-card h-100 <?= $tieneVencidas ? 'border-danger border-2' : '' ?>"
                     onclick="window.location.href='<?= base_url("acciones-correctivas/{$cliente['id_cliente']}") ?>'">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="mb-0"><?= esc($cliente['nombre_cliente']) ?></h6>
                            <?php if ($tieneVencidas): ?>
                            <span class="badge bg-danger">
                                <i class="bi bi-exclamation-triangle"></i>
                            </span>
                            <?php endif; ?>
                        </div>
                        <small class="text-muted d-block mb-3">NIT: <?= esc($cliente['nit_cliente']) ?></small>

                        <div class="row g-2">
                            <div class="col-6">
                                <div class="bg-light rounded p-2 text-center">
                                    <div class="fw-bold text-primary"><?= $resumen['total_hallazgos'] ?? 0 ?></div>
                                    <small class="text-muted resumen-badge">Hallazgos</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-light rounded p-2 text-center">
                                    <div class="fw-bold text-info"><?= $resumen['total_acciones'] ?? 0 ?></div>
                                    <small class="text-muted resumen-badge">Acciones</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-light rounded p-2 text-center">
                                    <div class="fw-bold <?= $tieneVencidas ? 'text-danger' : 'text-success' ?>">
                                        <?= $resumen['acciones_vencidas'] ?? 0 ?>
                                    </div>
                                    <small class="text-muted resumen-badge">Vencidas</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-light rounded p-2 text-center">
                                    <div class="fw-bold text-warning"><?= $resumen['hallazgos_abiertos'] ?? 0 ?></div>
                                    <small class="text-muted resumen-badge">Abiertos</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 text-center">
                        <small class="text-primary">
                            <i class="bi bi-arrow-right me-1"></i>Ver Dashboard
                        </small>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($clientes)): ?>
        <div class="text-center py-5">
            <i class="bi bi-inbox display-1 text-muted"></i>
            <h4 class="text-muted mt-3">No hay clientes activos</h4>
            <p class="text-muted">No se encontraron clientes activos en el sistema.</p>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    $(document).ready(function() {
        $('.select2-cliente').select2({
            theme: 'bootstrap-5',
            placeholder: 'Buscar cliente por nombre o NIT...',
            allowClear: true
        });

        $('#cliente_rapido').on('change', function() {
            const idCliente = $(this).val();
            $('#btn_ir_cliente').prop('disabled', !idCliente);
        });

        $('#btn_ir_cliente').on('click', function() {
            const idCliente = $('#cliente_rapido').val();
            if (idCliente) {
                window.location.href = '<?= base_url("acciones-correctivas") ?>/' + idCliente;
            }
        });
    });
    </script>
</body>
</html>
