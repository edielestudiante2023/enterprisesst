<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hallazgo - <?= esc($hallazgo['titulo']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .timeline { position: relative; padding-left: 30px; }
        .timeline::before { content: ''; position: absolute; left: 8px; top: 0; bottom: 0; width: 2px; background: #dee2e6; }
        .timeline-item { position: relative; margin-bottom: 1rem; }
        .timeline-item::before { content: ''; position: absolute; left: -26px; width: 12px; height: 12px; border-radius: 50%; background: #0d6efd; border: 2px solid white; }
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
            <div class="d-flex align-items-center">
                <a href="<?= base_url("acciones-correctivas/{$cliente['id_cliente']}") ?>" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Volver al Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url("acciones-correctivas/{$cliente['id_cliente']}") ?>">Acciones Correctivas</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url("acciones-correctivas/{$cliente['id_cliente']}/hallazgos") ?>">Hallazgos</a></li>
                <li class="breadcrumb-item active"><?= esc(substr($hallazgo['titulo'], 0, 30)) ?>...</li>
            </ol>
        </nav>

        <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row">
            <!-- Columna Principal: Detalles del Hallazgo -->
            <div class="col-lg-8">
                <!-- Card del Hallazgo -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-start">
                        <div>
                            <?php
                            $severidadClase = match($hallazgo['severidad']) {
                                'critica' => 'danger',
                                'alta' => 'warning',
                                'media' => 'info',
                                'baja' => 'secondary',
                                default => 'secondary'
                            };
                            $estadoClase = match($hallazgo['estado']) {
                                'abierto' => 'warning',
                                'en_tratamiento' => 'primary',
                                'en_verificacion' => 'info',
                                'cerrado' => 'success',
                                'cerrado_no_efectivo' => 'danger',
                                default => 'secondary'
                            };
                            ?>
                            <span class="badge bg-<?= $severidadClase ?> me-1">Severidad: <?= ucfirst($hallazgo['severidad']) ?></span>
                            <span class="badge bg-<?= $estadoClase ?>"><?= ucfirst(str_replace('_', ' ', $hallazgo['estado'])) ?></span>
                            <span class="badge bg-light text-dark"><?= $hallazgo['numeral_asociado'] ?></span>
                            <h4 class="mt-2 mb-0"><?= esc($hallazgo['titulo']) ?></h4>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#"><i class="bi bi-pencil me-2"></i>Editar</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#"><i class="bi bi-trash me-2"></i>Eliminar</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <small class="text-muted d-block">Tipo de Origen</small>
                                <span>
                                    <i class="bi <?= $hallazgo['tipo_origen_icono'] ?? 'bi-exclamation-triangle' ?> me-1"
                                       style="color: <?= $hallazgo['tipo_origen_color'] ?? '#6c757d' ?>"></i>
                                    <?= esc($hallazgo['tipo_origen_nombre'] ?? ucfirst(str_replace('_', ' ', $hallazgo['tipo_origen']))) ?>
                                </span>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Área/Proceso</small>
                                <span><?= esc($hallazgo['area_proceso'] ?? 'No especificado') ?></span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <small class="text-muted d-block">Fecha de Detección</small>
                                <span><?= date('d/m/Y', strtotime($hallazgo['fecha_deteccion'])) ?></span>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Fecha Límite para Acción</small>
                                <span class="<?= (!empty($hallazgo['fecha_limite_accion']) && $hallazgo['fecha_limite_accion'] < date('Y-m-d')) ? 'text-danger fw-bold' : '' ?>">
                                    <?= !empty($hallazgo['fecha_limite_accion']) ? date('d/m/Y', strtotime($hallazgo['fecha_limite_accion'])) : 'No definida' ?>
                                </span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Descripción del Hallazgo</small>
                            <p class="mb-0"><?= nl2br(esc($hallazgo['descripcion'])) ?></p>
                        </div>
                        <?php if (!empty($hallazgo['evidencia_inicial'])): ?>
                        <div class="mb-3">
                            <small class="text-muted d-block">Evidencia Inicial</small>
                            <a href="<?= esc($hallazgo['evidencia_inicial']) ?>" target="_blank"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-file-earmark-arrow-down me-1"></i>Ver Archivo
                            </a>
                        </div>
                        <?php endif; ?>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">Reportado por:</small>
                                <span class="ms-1"><?= esc($hallazgo['reportado_por_nombre'] ?? 'Usuario #' . $hallazgo['reportado_por']) ?></span>
                            </div>
                            <div class="col-md-6 text-end">
                                <small class="text-muted">Creado:</small>
                                <span class="ms-1"><?= date('d/m/Y H:i', strtotime($hallazgo['created_at'])) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista de Acciones del Hallazgo -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-list-task me-2"></i>
                            Acciones (<?= $hallazgo['total_acciones'] ?>)
                        </h5>
                        <a href="<?= base_url("acciones-correctivas/{$cliente['id_cliente']}/hallazgo/{$hallazgo['id_hallazgo']}/accion/crear") ?>"
                           class="btn btn-success btn-sm">
                            <i class="bi bi-plus-circle me-1"></i>Nueva Acción
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($hallazgo['acciones'])): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Descripción</th>
                                        <th>Responsable</th>
                                        <th>Fecha Compromiso</th>
                                        <th>Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($hallazgo['acciones'] as $accion): ?>
                                    <?php
                                    $tipoClase = match($accion['tipo_accion']) {
                                        'correctiva' => 'danger',
                                        'preventiva' => 'warning',
                                        'mejora' => 'success',
                                        default => 'secondary'
                                    };
                                    $estadoAccionClase = match($accion['estado']) {
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
                                    $estaVencida = !in_array($accion['estado'], ['cerrada_efectiva', 'cerrada_no_efectiva', 'cancelada']) &&
                                                   $accion['fecha_compromiso'] < date('Y-m-d');
                                    ?>
                                    <tr class="<?= $estaVencida ? 'table-danger' : '' ?>">
                                        <td>
                                            <span class="badge bg-<?= $tipoClase ?>"><?= ucfirst($accion['tipo_accion']) ?></span>
                                        </td>
                                        <td>
                                            <small><?= esc(substr($accion['descripcion_accion'], 0, 50)) ?>...</small>
                                        </td>
                                        <td>
                                            <small><?= esc($accion['responsable_usuario_nombre'] ?? $accion['responsable_nombre'] ?? '-') ?></small>
                                        </td>
                                        <td>
                                            <small class="<?= $estaVencida ? 'text-danger fw-bold' : '' ?>">
                                                <?= date('d/m/Y', strtotime($accion['fecha_compromiso'])) ?>
                                                <?php if ($estaVencida): ?><i class="bi bi-exclamation-triangle-fill"></i><?php endif; ?>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $estadoAccionClase ?>"><?= ucfirst(str_replace('_', ' ', $accion['estado'])) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= base_url("acciones-correctivas/{$cliente['id_cliente']}/accion/{$accion['id_accion']}") ?>"
                                               class="btn btn-sm btn-outline-primary" title="Ver detalle">
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
                            <i class="bi bi-clipboard-plus display-4 text-muted"></i>
                            <p class="text-muted mt-2">No hay acciones registradas para este hallazgo</p>
                            <a href="<?= base_url("acciones-correctivas/{$cliente['id_cliente']}/hallazgo/{$hallazgo['id_hallazgo']}/accion/crear") ?>"
                               class="btn btn-success">
                                <i class="bi bi-plus-circle me-1"></i>Crear Primera Acción
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Columna Lateral: Información y Acciones Rápidas -->
            <div class="col-lg-4">
                <!-- Resumen -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h6 class="mb-0"><i class="bi bi-speedometer2 me-2"></i>Resumen</h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="h4 mb-0 text-primary"><?= $hallazgo['total_acciones'] ?></div>
                                <small class="text-muted">Total</small>
                            </div>
                            <div class="col-4">
                                <div class="h4 mb-0 text-warning"><?= $hallazgo['acciones_pendientes'] ?></div>
                                <small class="text-muted">Pendientes</small>
                            </div>
                            <div class="col-4">
                                <div class="h4 mb-0 text-success"><?= $hallazgo['acciones_cerradas'] ?></div>
                                <small class="text-muted">Cerradas</small>
                            </div>
                        </div>
                        <?php if ($hallazgo['total_acciones'] > 0): ?>
                        <div class="progress mt-3" style="height: 10px;">
                            <div class="progress-bar bg-success"
                                 style="width: <?= ($hallazgo['acciones_cerradas'] / $hallazgo['total_acciones']) * 100 ?>%"
                                 title="Cerradas"></div>
                        </div>
                        <small class="text-muted"><?= round(($hallazgo['acciones_cerradas'] / $hallazgo['total_acciones']) * 100) ?>% completado</small>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Acciones Rápidas -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h6 class="mb-0"><i class="bi bi-lightning me-2"></i>Acciones Rápidas</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="<?= base_url("acciones-correctivas/{$cliente['id_cliente']}/hallazgo/{$hallazgo['id_hallazgo']}/accion/crear") ?>"
                               class="btn btn-success">
                                <i class="bi bi-plus-circle me-2"></i>Nueva Acción
                            </a>
                            <?php if ($hallazgo['estado'] !== 'cerrado'): ?>
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalCerrarHallazgo"
                                    <?= $hallazgo['acciones_pendientes'] > 0 ? 'disabled' : '' ?>>
                                <i class="bi bi-check-circle me-2"></i>Cerrar Hallazgo
                            </button>
                            <?php endif; ?>
                        </div>
                        <?php if ($hallazgo['acciones_pendientes'] > 0 && $hallazgo['estado'] !== 'cerrado'): ?>
                        <small class="text-muted d-block mt-2 text-center">
                            <i class="bi bi-info-circle me-1"></i>
                            Debe cerrar todas las acciones antes de cerrar el hallazgo
                        </small>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Info del Cliente -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0">
                        <h6 class="mb-0"><i class="bi bi-building me-2"></i>Cliente</h6>
                    </div>
                    <div class="card-body">
                        <strong><?= esc($hallazgo['nombre_cliente']) ?></strong>
                        <br>
                        <small class="text-muted">NIT: <?= esc($hallazgo['nit_cliente']) ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .bg-purple { background-color: #6f42c1 !important; }
    </style>
</body>
</html>
