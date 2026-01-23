<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cumplimiento Estándares - <?= esc($cliente['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .progress-ring {
            width: 150px;
            height: 150px;
        }
        .progress-ring__circle {
            stroke-dasharray: 440;
            stroke-dashoffset: 440;
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
            transition: stroke-dashoffset 0.5s;
        }
        .estado-cumple { background-color: #D1FAE5; color: #065F46; }
        .estado-no_cumple { background-color: #FEE2E2; color: #991B1B; }
        .estado-en_proceso { background-color: #FEF3C7; color: #92400E; }
        .estado-no_aplica { background-color: #E5E7EB; color: #374151; }
        .estado-pendiente { background-color: #DBEAFE; color: #1E40AF; }
        .phva-card { border-left: 4px solid; }
        .phva-planear { border-color: #3B82F6; }
        .phva-hacer { border-color: #10B981; }
        .phva-verificar { border-color: #F59E0B; }
        .phva-actuar { border-color: #EF4444; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="/documentacion/<?= $cliente['id_cliente'] ?>">
                <i class="bi bi-check2-square me-2"></i>Cumplimiento Estándares
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text text-white me-3">
                    <i class="bi bi-building me-1"></i>
                    <?= esc($cliente['nombre_cliente']) ?>
                </span>
                <a class="nav-link" href="/documentacion/<?= $cliente['id_cliente'] ?>">
                    <i class="bi bi-arrow-left me-1"></i>Volver
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Alertas -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Resumen General -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-3">Cumplimiento General</h6>
                        <svg class="progress-ring" viewBox="0 0 150 150">
                            <circle cx="75" cy="75" r="70" fill="none" stroke="#E5E7EB" stroke-width="10"/>
                            <circle class="progress-ring__circle" cx="75" cy="75" r="70" fill="none"
                                    stroke="#10B981" stroke-width="10"
                                    style="stroke-dashoffset: <?= 440 - (440 * ($cumplimientoPonderado ?? 0) / 100) ?>"/>
                        </svg>
                        <h2 class="mt-3 mb-0"><?= number_format($cumplimientoPonderado ?? 0, 1) ?>%</h2>
                        <small class="text-muted">Ponderado según pesos</small>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="row h-100">
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-check-circle text-success fs-1"></i>
                                <h3 class="mt-2 mb-0"><?= $resumen['cumple'] ?? 0 ?></h3>
                                <small class="text-muted">Cumple</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-hourglass-split text-warning fs-1"></i>
                                <h3 class="mt-2 mb-0"><?= $resumen['en_proceso'] ?? 0 ?></h3>
                                <small class="text-muted">En Proceso</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-x-circle text-danger fs-1"></i>
                                <h3 class="mt-2 mb-0"><?= $resumen['no_cumple'] ?? 0 ?></h3>
                                <small class="text-muted">No Cumple</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-dash-circle text-secondary fs-1"></i>
                                <h3 class="mt-2 mb-0"><?= $resumen['no_aplica'] ?? 0 ?></h3>
                                <small class="text-muted">No Aplica</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contexto del Cliente -->
        <?php if (!empty($contexto)): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Contexto SST del Cliente</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Trabajadores:</strong> <?= $contexto['numero_trabajadores'] ?? 'N/D' ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Nivel de Riesgo:</strong> <?= $contexto['nivel_riesgo'] ?? 'N/D' ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Nivel Estándares:</strong>
                            <span class="badge bg-primary"><?= $contexto['nivel_estandares'] ?? 60 ?></span>
                        </div>
                        <div class="col-md-3">
                            <strong>Actividad:</strong> <?= esc($contexto['actividad_economica'] ?? 'N/D') ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Transiciones Pendientes -->
        <?php if (!empty($transicionesPendientes)): ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Transición de Nivel Pendiente:</strong>
                Hay cambios en el número de trabajadores o nivel de riesgo que requieren actualizar los estándares aplicables.
                <a href="/estandares/transiciones/<?= $cliente['id_cliente'] ?>" class="alert-link">Ver detalles</a>
            </div>
        <?php endif; ?>

        <!-- Estándares por Ciclo PHVA -->
        <?php if (empty($estandares)): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-clipboard-x text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-3">No hay estándares inicializados para este cliente</p>
                    <a href="/estandares/inicializar/<?= $cliente['id_cliente'] ?>" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i>Inicializar Estándares
                    </a>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($estandares as $ciclo => $items): ?>
                <?php
                    $phvaClass = match(strtolower($ciclo)) {
                        'planear' => 'phva-planear',
                        'hacer' => 'phva-hacer',
                        'verificar' => 'phva-verificar',
                        'actuar' => 'phva-actuar',
                        default => ''
                    };
                ?>
                <div class="card border-0 shadow-sm mb-4 phva-card <?= $phvaClass ?>">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?= strtoupper($ciclo) ?></h5>
                        <span class="badge bg-secondary"><?= count($items) ?> estándares</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 60px;">Núm.</th>
                                        <th>Estándar</th>
                                        <th style="width: 120px;">Estado</th>
                                        <th style="width: 60px;">Peso</th>
                                        <th style="width: 100px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                        <tr>
                                            <td><span class="badge bg-secondary"><?= $item['numero_estandar'] ?></span></td>
                                            <td>
                                                <strong><?= esc($item['nombre']) ?></strong>
                                                <?php if (!empty($item['observaciones'])): ?>
                                                    <br><small class="text-muted"><?= esc($item['observaciones']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <select class="form-select form-select-sm estado-select"
                                                        data-id-estandar="<?= $item['id_estandar'] ?>"
                                                        data-id-cliente="<?= $cliente['id_cliente'] ?>">
                                                    <option value="pendiente" <?= ($item['estado'] ?? 'pendiente') === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                                    <option value="cumple" <?= ($item['estado'] ?? '') === 'cumple' ? 'selected' : '' ?>>Cumple</option>
                                                    <option value="no_cumple" <?= ($item['estado'] ?? '') === 'no_cumple' ? 'selected' : '' ?>>No Cumple</option>
                                                    <option value="en_proceso" <?= ($item['estado'] ?? '') === 'en_proceso' ? 'selected' : '' ?>>En Proceso</option>
                                                    <option value="no_aplica" <?= ($item['estado'] ?? '') === 'no_aplica' ? 'selected' : '' ?>>No Aplica</option>
                                                </select>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark"><?= $item['peso'] ?>%</span>
                                            </td>
                                            <td>
                                                <a href="/estandares/<?= $cliente['id_cliente'] ?>/<?= $item['id_estandar'] ?>"
                                                   class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Actualizar estado vía AJAX
        document.querySelectorAll('.estado-select').forEach(select => {
            select.addEventListener('change', function() {
                const idEstandar = this.dataset.idEstandar;
                const idCliente = this.dataset.idCliente;
                const estado = this.value;

                fetch('/estandares/actualizar-estado', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `id_cliente=${idCliente}&id_estandar=${idEstandar}&estado=${estado}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Actualizar visualmente
                        this.classList.add('is-valid');
                        setTimeout(() => this.classList.remove('is-valid'), 2000);
                    } else {
                        alert(data.message || 'Error al actualizar');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        });
    </script>
</body>
</html>
