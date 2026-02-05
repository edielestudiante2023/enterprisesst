<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($numeral) ?> - <?= esc($cliente['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .bg-purple { background-color: #6f42c1 !important; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= base_url('dashboard') ?>">
                <i class="bi bi-shield-check me-2"></i>EnterpriseSST
            </a>
            <div class="d-flex align-items-center gap-2">
                <a href="<?= base_url("acciones-correctivas/{$cliente['id_cliente']}") ?>" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Dashboard Acciones
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Header -->
        <div class="row mb-4">
            <div class="col-lg-8">
                <h3 class="mb-1">
                    <span class="badge bg-primary me-2"><?= esc($numeral) ?></span>
                    <?= esc($nombre_numeral) ?>
                </h3>
                <p class="text-muted"><?= esc($descripcion_numeral) ?></p>
                <p class="mb-0">
                    Cliente: <strong><?= esc($cliente['nombre_cliente']) ?></strong>
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <a href="<?= base_url("acciones-correctivas/{$cliente['id_cliente']}/hallazgo/crear/{$numeral}") ?>"
                   class="btn btn-success">
                    <i class="bi bi-plus-circle me-1"></i>Nuevo Hallazgo
                </a>
            </div>
        </div>

        <!-- KPIs -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="display-6 text-primary"><?= $estadisticas['total_hallazgos'] ?? 0 ?></div>
                        <small class="text-muted">Hallazgos</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="display-6 text-info"><?= $estadisticas['total_acciones'] ?? 0 ?></div>
                        <small class="text-muted">Acciones</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="display-6 text-warning"><?= $estadisticas['acciones_abiertas'] ?? 0 ?></div>
                        <small class="text-muted">En Proceso</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="display-6 <?= ($estadisticas['acciones_vencidas'] ?? 0) > 0 ? 'text-danger' : 'text-success' ?>">
                            <?= $estadisticas['acciones_vencidas'] ?? 0 ?>
                        </div>
                        <small class="text-muted">Vencidas</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Acciones -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="bi bi-list-task me-2"></i>Acciones del Numeral
                        </h6>
                        <span class="badge bg-secondary"><?= count($acciones) ?></span>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($acciones)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Hallazgo</th>
                                        <th>Accion</th>
                                        <th>Tipo</th>
                                        <th>Responsable</th>
                                        <th>Compromiso</th>
                                        <th>Estado</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($acciones as $a): ?>
                                    <?php
                                    $tipoClase = match($a['tipo_accion']) {
                                        'correctiva' => 'danger',
                                        'preventiva' => 'warning',
                                        'mejora' => 'success',
                                        default => 'secondary'
                                    };
                                    $estadoClase = match($a['estado']) {
                                        'borrador' => 'secondary',
                                        'asignada' => 'info',
                                        'en_ejecucion' => 'primary',
                                        'en_revision' => 'warning',
                                        'en_verificacion' => 'purple',
                                        'cerrada_efectiva' => 'success',
                                        'cerrada_no_efectiva' => 'danger',
                                        'reabierta' => 'warning',
                                        'cancelada' => 'dark',
                                        default => 'secondary'
                                    };
                                    $vencida = !in_array($a['estado'], ['cerrada_efectiva', 'cerrada_no_efectiva', 'cancelada']) &&
                                               isset($a['fecha_compromiso']) && $a['fecha_compromiso'] < date('Y-m-d');
                                    ?>
                                    <tr class="<?= $vencida ? 'table-danger' : '' ?>">
                                        <td>
                                            <small class="text-truncate d-block" style="max-width: 150px;">
                                                <?= esc($a['hallazgo_titulo'] ?? '-') ?>
                                            </small>
                                        </td>
                                        <td>
                                            <small class="text-truncate d-block" style="max-width: 180px;">
                                                <?= esc(substr($a['descripcion_accion'], 0, 50)) ?>...
                                            </small>
                                        </td>
                                        <td><span class="badge bg-<?= $tipoClase ?>"><?= ucfirst($a['tipo_accion']) ?></span></td>
                                        <td><small><?= esc($a['responsable_usuario_nombre'] ?? $a['responsable_nombre'] ?? '-') ?></small></td>
                                        <td>
                                            <small class="<?= $vencida ? 'text-danger fw-bold' : '' ?>">
                                                <?= !empty($a['fecha_compromiso']) ? date('d/m/Y', strtotime($a['fecha_compromiso'])) : '-' ?>
                                            </small>
                                        </td>
                                        <td><span class="badge bg-<?= $estadoClase ?>"><?= ucwords(str_replace('_', ' ', $a['estado'])) ?></span></td>
                                        <td>
                                            <a href="<?= base_url("acciones-correctivas/{$cliente['id_cliente']}/accion/{$a['id_accion']}") ?>"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <p class="text-muted mt-2">No hay acciones registradas para este numeral</p>
                            <a href="<?= base_url("acciones-correctivas/{$cliente['id_cliente']}/hallazgo/crear/{$numeral}") ?>"
                               class="btn btn-success">
                                <i class="bi bi-plus-circle me-1"></i>Registrar Primer Hallazgo
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Hallazgos -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0">
                        <h6 class="mb-0">
                            <i class="bi bi-exclamation-diamond me-2 text-warning"></i>Hallazgos
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($hallazgos)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($hallazgos as $h): ?>
                            <?php
                            $severidadClase = match($h['severidad']) {
                                'critica' => 'danger',
                                'alta' => 'warning',
                                'media' => 'info',
                                'baja' => 'secondary',
                                default => 'secondary'
                            };
                            ?>
                            <a href="<?= base_url("acciones-correctivas/{$cliente['id_cliente']}/hallazgo/{$h['id_hallazgo']}") ?>"
                               class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <span class="badge bg-<?= $severidadClase ?> me-2"><?= ucfirst($h['severidad']) ?></span>
                                        <strong class="d-block mt-1"><?= esc(substr($h['titulo'], 0, 40)) ?>...</strong>
                                        <small class="text-muted"><?= date('d/m/Y', strtotime($h['fecha_deteccion'])) ?></small>
                                    </div>
                                    <span class="badge bg-<?= $h['estado'] === 'cerrado' ? 'success' : 'warning' ?>">
                                        <?= ucfirst(str_replace('_', ' ', $h['estado'])) ?>
                                    </span>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-check-circle display-4 text-success"></i>
                            <p class="text-muted mt-2 mb-0">No hay hallazgos registrados</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tipos de Origen -->
                <?php if (!empty($tipos_origen)): ?>
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-white border-0">
                        <h6 class="mb-0">
                            <i class="bi bi-tags me-2"></i>Tipos de Origen
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($tipos_origen as $tipo): ?>
                            <span class="badge rounded-pill" style="background-color: <?= $tipo['color'] ?? '#6c757d' ?>">
                                <i class="bi <?= $tipo['icono'] ?? 'bi-circle' ?> me-1"></i>
                                <?= esc($tipo['nombre_mostrar']) ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
