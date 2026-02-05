<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acciones Correctivas - <?= esc($cliente['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .kpi-card { transition: transform 0.2s; }
        .kpi-card:hover { transform: translateY(-2px); }
        .kpi-value { font-size: 2.5rem; font-weight: 700; }
        .kpi-meta { font-size: 0.75rem; }
        .table-acciones th { font-size: 0.85rem; }
        .badge-estado { font-size: 0.7rem; }
        .progress { height: 8px; }
        .nav-pills-custom .nav-link { color: #6c757d; border-radius: 20px; padding: 0.5rem 1rem; }
        .nav-pills-custom .nav-link.active { background-color: #0d6efd; color: white; }
        .alert-vencida { animation: pulse 2s infinite; }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
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
                    <i class="bi bi-building me-1"></i><?= esc($cliente['nombre_cliente']) ?>
                </span>
                <a href="<?= base_url('documentacion/' . $cliente['id_cliente']) ?>" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Volver
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-1">
                            <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                            Acciones Correctivas, Preventivas y de Mejora
                        </h2>
                        <p class="text-muted mb-0">
                            Gestión integral de hallazgos y acciones - Numerales 7.1.1 a 7.1.4
                        </p>
                    </div>
                    <div>
                        <a href="<?= base_url("acciones-correctivas/{$cliente['id_cliente']}/hallazgo/crear") ?>"
                           class="btn btn-success">
                            <i class="bi bi-plus-circle me-1"></i>Nuevo Hallazgo
                        </a>
                        <a href="<?= base_url("acciones-correctivas/{$cliente['id_cliente']}/reporte/pdf") ?>"
                           class="btn btn-outline-danger" target="_blank">
                            <i class="bi bi-file-pdf me-1"></i>Reporte PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alertas -->
        <?php if (!empty($acciones_vencidas)): ?>
        <div class="alert alert-danger alert-vencida d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-4"></i>
            <div>
                <strong><?= count($acciones_vencidas) ?> acción<?= count($acciones_vencidas) > 1 ? 'es' : '' ?> vencida<?= count($acciones_vencidas) > 1 ? 's' : '' ?></strong>
                - Requieren atención inmediata.
                <a href="#acciones-vencidas" class="alert-link ms-2">Ver detalles</a>
            </div>
        </div>
        <?php endif; ?>

        <!-- KPIs -->
        <div class="row mb-4">
            <?php foreach ($kpis as $key => $kpi): ?>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm kpi-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="kpi-value text-<?= $kpi['color'] ?>"><?= $kpi['valor'] ?><small class="fs-6"><?= $kpi['unidad'] ?></small></div>
                                <div class="text-muted"><?= $kpi['nombre'] ?></div>
                            </div>
                            <div class="bg-<?= $kpi['color'] ?> bg-opacity-10 rounded-circle p-2">
                                <i class="bi <?= $kpi['icono'] ?> fs-4 text-<?= $kpi['color'] ?>"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="progress">
                                <div class="progress-bar bg-<?= $kpi['color'] ?>"
                                     style="width: <?= min(100, ($kpi['valor'] / max(1, $kpi['meta'])) * 100) ?>%"></div>
                            </div>
                            <div class="kpi-meta text-muted mt-1">Meta: <?= $kpi['meta'] ?><?= $kpi['unidad'] ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Estadísticas por Numeral -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Distribución por Numeral</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <?php
                            $numerales = [
                                '7.1.1' => ['nombre' => 'Resultados SG-SST', 'icono' => 'bi-clipboard-data', 'color' => 'primary'],
                                '7.1.2' => ['nombre' => 'Efectividad Medidas', 'icono' => 'bi-shield-x', 'color' => 'danger'],
                                '7.1.3' => ['nombre' => 'Investigación ATEL', 'icono' => 'bi-bandaid', 'color' => 'warning'],
                                '7.1.4' => ['nombre' => 'ARL / Autoridades', 'icono' => 'bi-bank', 'color' => 'info']
                            ];
                            foreach ($numerales as $num => $info):
                                $cantidad = $estadisticas_hallazgos['por_numeral'][$num] ?? 0;
                            ?>
                            <div class="col-md-3">
                                <a href="<?= base_url("acciones-correctivas/{$cliente['id_cliente']}/numeral/{$num}") ?>"
                                   class="text-decoration-none">
                                    <div class="p-3 rounded bg-<?= $info['color'] ?> bg-opacity-10 h-100">
                                        <i class="bi <?= $info['icono'] ?> fs-2 text-<?= $info['color'] ?>"></i>
                                        <h3 class="mt-2 mb-0 text-<?= $info['color'] ?>"><?= $cantidad ?></h3>
                                        <small class="text-muted d-block"><?= $num ?></small>
                                        <small class="text-dark"><?= $info['nombre'] ?></small>
                                    </div>
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Columna Izquierda: Acciones Vencidas y Próximas -->
            <div class="col-md-6">
                <!-- Acciones Vencidas -->
                <div class="card border-0 shadow-sm mb-4" id="acciones-vencidas">
                    <div class="card-header bg-danger text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-exclamation-circle me-2"></i>
                            Acciones Vencidas (<?= count($acciones_vencidas) ?>)
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($acciones_vencidas)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach (array_slice($acciones_vencidas, 0, 5) as $accion): ?>
                            <a href="<?= base_url("acciones-correctivas/{$cliente['id_cliente']}/accion/{$accion['id_accion']}") ?>"
                               class="list-group-item list-group-item-action list-group-item-danger">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong class="d-block"><?= esc(substr($accion['descripcion_accion'], 0, 50)) ?>...</strong>
                                        <small>
                                            <span class="badge bg-<?= $accion['numeral_asociado'] === '7.1.3' ? 'warning' : 'secondary' ?>"><?= $accion['numeral_asociado'] ?></span>
                                            <?= esc($accion['responsable_usuario_nombre'] ?? 'Sin asignar') ?>
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-danger fw-bold">
                                            <?= date('d/m/Y', strtotime($accion['fecha_compromiso'])) ?>
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            <?php
                                            $dias = (int)((strtotime(date('Y-m-d')) - strtotime($accion['fecha_compromiso'])) / 86400);
                                            echo "{$dias} día" . ($dias > 1 ? 's' : '') . " vencida";
                                            ?>
                                        </small>
                                    </div>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-check-circle text-success fs-1"></i>
                            <p class="text-muted mt-2 mb-0">No hay acciones vencidas</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Próximas a Vencer -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">
                            <i class="bi bi-clock me-2"></i>
                            Próximas a Vencer (7 días) - <?= count($acciones_proximas_vencer) ?>
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($acciones_proximas_vencer)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach (array_slice($acciones_proximas_vencer, 0, 5) as $accion): ?>
                            <a href="<?= base_url("acciones-correctivas/{$cliente['id_cliente']}/accion/{$accion['id_accion']}") ?>"
                               class="list-group-item list-group-item-action list-group-item-warning">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong class="d-block"><?= esc(substr($accion['descripcion_accion'], 0, 50)) ?>...</strong>
                                        <small><?= esc($accion['responsable_usuario_nombre'] ?? 'Sin asignar') ?></small>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-warning fw-bold">
                                            <?= date('d/m/Y', strtotime($accion['fecha_compromiso'])) ?>
                                        </small>
                                    </div>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-calendar-check text-success fs-1"></i>
                            <p class="text-muted mt-2 mb-0">No hay acciones próximas a vencer</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Hallazgos Recientes y Resumen -->
            <div class="col-md-6">
                <!-- Hallazgos Recientes -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="bi bi-list-ul me-2"></i>Hallazgos Recientes</h6>
                        <a href="<?= base_url("acciones-correctivas/{$cliente['id_cliente']}/hallazgos") ?>" class="btn btn-sm btn-outline-primary">
                            Ver Todos
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($hallazgos_recientes)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($hallazgos_recientes as $hallazgo): ?>
                            <?php
                                $severidadClase = match($hallazgo['severidad']) {
                                    'critica' => 'danger',
                                    'alta' => 'warning',
                                    'media' => 'info',
                                    'baja' => 'secondary',
                                    default => 'secondary'
                                };
                            ?>
                            <a href="<?= base_url("acciones-correctivas/{$cliente['id_cliente']}/hallazgo/{$hallazgo['id_hallazgo']}") ?>"
                               class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <span class="badge bg-<?= $severidadClase ?> me-1"><?= ucfirst($hallazgo['severidad']) ?></span>
                                        <span class="badge bg-light text-dark"><?= $hallazgo['numeral_asociado'] ?></span>
                                        <strong class="d-block mt-1"><?= esc($hallazgo['titulo']) ?></strong>
                                        <small class="text-muted">
                                            <i class="bi <?= $hallazgo['icono'] ?? 'bi-exclamation-triangle' ?> me-1"></i>
                                            <?= esc($hallazgo['tipo_origen_nombre'] ?? $hallazgo['tipo_origen']) ?>
                                        </small>
                                    </div>
                                    <small class="text-muted"><?= date('d/m', strtotime($hallazgo['fecha_deteccion'])) ?></small>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-inbox text-muted fs-1"></i>
                            <p class="text-muted mt-2 mb-0">No hay hallazgos registrados</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Resumen de Estados -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0">
                        <h6 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Resumen de Acciones</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="d-flex justify-content-between mb-2">
                                    <span><span class="badge bg-info me-1">&nbsp;</span> Asignadas</span>
                                    <strong><?= $estadisticas_acciones['por_estado']['asignada'] ?? 0 ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span><span class="badge bg-primary me-1">&nbsp;</span> En Ejecución</span>
                                    <strong><?= $estadisticas_acciones['por_estado']['en_ejecucion'] ?? 0 ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span><span class="badge bg-warning me-1">&nbsp;</span> En Revisión</span>
                                    <strong><?= $estadisticas_acciones['por_estado']['en_revision'] ?? 0 ?></strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex justify-content-between mb-2">
                                    <span><span class="badge bg-purple me-1">&nbsp;</span> Verificando</span>
                                    <strong><?= $estadisticas_acciones['por_estado']['en_verificacion'] ?? 0 ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span><span class="badge bg-success me-1">&nbsp;</span> Cerradas</span>
                                    <strong><?= $estadisticas_acciones['por_estado']['cerrada_efectiva'] ?? 0 ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span><span class="badge bg-danger me-1">&nbsp;</span> No Efectivas</span>
                                    <strong><?= $estadisticas_acciones['por_estado']['cerrada_no_efectiva'] ?? 0 ?></strong>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="h4 mb-0 text-danger"><?= $estadisticas_acciones['por_tipo']['correctiva'] ?? 0 ?></div>
                                <small class="text-muted">Correctivas</small>
                            </div>
                            <div class="col-4">
                                <div class="h4 mb-0 text-warning"><?= $estadisticas_acciones['por_tipo']['preventiva'] ?? 0 ?></div>
                                <small class="text-muted">Preventivas</small>
                            </div>
                            <div class="col-4">
                                <div class="h4 mb-0 text-success"><?= $estadisticas_acciones['por_tipo']['mejora'] ?? 0 ?></div>
                                <small class="text-muted">Mejora</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pendientes de Verificación -->
        <?php if (!empty($verificaciones_pendientes)): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-purple text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-clipboard-check me-2"></i>
                            Pendientes de Verificación de Efectividad (<?= count($verificaciones_pendientes) ?>)
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Acción</th>
                                        <th>Hallazgo</th>
                                        <th>Numeral</th>
                                        <th>Responsable</th>
                                        <th class="text-center">Verificar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($verificaciones_pendientes as $accion): ?>
                                    <tr>
                                        <td><?= esc(substr($accion['descripcion_accion'], 0, 40)) ?>...</td>
                                        <td><small><?= esc($accion['hallazgo_titulo'] ?? '-') ?></small></td>
                                        <td><span class="badge bg-secondary"><?= $accion['numeral_asociado'] ?></span></td>
                                        <td><?= esc($accion['responsable_usuario_nombre'] ?? '-') ?></td>
                                        <td class="text-center">
                                            <a href="<?= base_url("acciones-correctivas/{$cliente['id_cliente']}/accion/{$accion['id_accion']}") ?>"
                                               class="btn btn-sm btn-purple">
                                                <i class="bi bi-check2-square"></i> Verificar
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
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .bg-purple { background-color: #6f42c1 !important; }
        .text-purple { color: #6f42c1 !important; }
        .btn-purple { background-color: #6f42c1; border-color: #6f42c1; color: white; }
        .btn-purple:hover { background-color: #5a32a3; border-color: #5a32a3; color: white; }
    </style>
</body>
</html>
