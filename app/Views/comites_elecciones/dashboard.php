<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="bi bi-people-fill text-primary me-2"></i>
                        Conformacion de Comites SST
                    </h1>
                    <p class="text-muted mb-0"><?= esc($cliente['nombre_cliente']) ?></p>
                </div>
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-plus-lg me-1"></i>Nuevo Proceso
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <?php if ($puedeCrear['COPASST']): ?>
                        <li>
                            <a class="dropdown-item" href="<?= base_url('comites-elecciones/' . $cliente['id_cliente'] . '/nuevo/COPASST') ?>">
                                <i class="bi bi-shield-check text-success me-2"></i>
                                COPASST
                                <small class="text-muted d-block">Comite Paritario SST (10+ trabajadores)</small>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if ($puedeCrear['VIGIA']): ?>
                        <li>
                            <a class="dropdown-item" href="<?= base_url('comites-elecciones/' . $cliente['id_cliente'] . '/nuevo/VIGIA') ?>">
                                <i class="bi bi-person-badge text-info me-2"></i>
                                Vigia SST
                                <small class="text-muted d-block">Designacion directa (1-9 trabajadores)</small>
                            </a>
                        </li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="<?= base_url('comites-elecciones/' . $cliente['id_cliente'] . '/nuevo/COCOLAB') ?>">
                                <i class="bi bi-chat-heart text-warning me-2"></i>
                                COCOLAB
                                <small class="text-muted d-block">Comite de Convivencia Laboral</small>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="<?= base_url('comites-elecciones/' . $cliente['id_cliente'] . '/nuevo/BRIGADA') ?>">
                                <i class="bi bi-fire text-danger me-2"></i>
                                Brigada de Emergencias
                                <small class="text-muted d-block">Voluntariado + Designacion</small>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Info de trabajadores -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info d-flex align-items-center">
                <i class="bi bi-info-circle-fill me-2"></i>
                <div>
                    <strong><?= $numTrabajadores ?> trabajadores</strong> registrados.
                    <?php if ($numTrabajadores < 10): ?>
                        Esta empresa requiere <strong>Vigia SST</strong> en lugar de COPASST.
                    <?php elseif ($numTrabajadores <= 49): ?>
                        COPASST con <strong>1 principal + 1 suplente</strong> por cada parte.
                    <?php elseif ($numTrabajadores <= 499): ?>
                        COPASST con <strong>2 principales + 2 suplentes</strong> por cada parte.
                    <?php elseif ($numTrabajadores <= 999): ?>
                        COPASST con <strong>3 principales + 3 suplentes</strong> por cada parte.
                    <?php else: ?>
                        COPASST con <strong>4 principales + 4 suplentes</strong> por cada parte.
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Mensajes flash -->
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

    <!-- Procesos Activos -->
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="mb-3"><i class="bi bi-clock-history me-2"></i>Procesos Activos</h5>
        </div>

        <?php
        $procesosActivos = array_filter($procesos, fn($p) => !in_array($p['estado'], ['completado', 'cancelado']));
        ?>

        <?php if (empty($procesosActivos)): ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <h4 class="mt-3">No hay procesos activos</h4>
                    <p class="text-muted">Use el boton "Nuevo Proceso" para iniciar la conformacion de un comite.</p>
                </div>
            </div>
        </div>
        <?php else: ?>
            <?php foreach ($procesosActivos as $proceso): ?>
            <?php
            $etiqueta = \App\Controllers\ComitesEleccionesController::getEtiquetaEstado($proceso['estado']);
            $iconoTipo = [
                'COPASST' => 'bi-shield-check text-success',
                'COCOLAB' => 'bi-chat-heart text-warning',
                'BRIGADA' => 'bi-fire text-danger',
                'VIGIA' => 'bi-person-badge text-info'
            ][$proceso['tipo_comite']] ?? 'bi-people';
            ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="badge bg-secondary">
                                <i class="bi <?= $iconoTipo ?> me-1"></i>
                                <?= esc($proceso['tipo_comite']) ?>
                            </span>
                            <span class="badge <?= $etiqueta['clase'] ?>"><?= $etiqueta['texto'] ?></span>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?= esc($proceso['nombre_comite'] ?? $proceso['tipo_comite']) ?> <?= $proceso['anio'] ?></h5>

                        <!-- Progress steps -->
                        <div class="mb-3">
                            <?php
                            $pasos = ['configuracion', 'inscripcion', 'votacion', 'escrutinio', 'designacion_empleador', 'firmas', 'completado'];
                            if ($proceso['tipo_comite'] === 'VIGIA' || $proceso['tipo_comite'] === 'BRIGADA') {
                                $pasos = ['designacion_empleador', 'firmas', 'completado'];
                            }
                            $pasoActual = array_search($proceso['estado'], $pasos);
                            $totalPasos = count($pasos);
                            $progreso = $pasoActual !== false ? (($pasoActual + 1) / $totalPasos) * 100 : 0;
                            ?>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-primary" style="width: <?= $progreso ?>%"></div>
                            </div>
                            <small class="text-muted">Paso <?= ($pasoActual !== false ? $pasoActual + 1 : 1) ?> de <?= $totalPasos ?></small>
                        </div>

                        <!-- Estadisticas -->
                        <div class="row text-center my-3">
                            <div class="col-6">
                                <div class="h4 mb-0"><?= $proceso['plazas_principales'] ?></div>
                                <small class="text-muted">Principales</small>
                            </div>
                            <div class="col-6">
                                <div class="h4 mb-0"><?= $proceso['plazas_suplentes'] ?></div>
                                <small class="text-muted">Suplentes</small>
                            </div>
                        </div>

                        <!-- Fechas -->
                        <div class="small text-muted">
                            <?php if ($proceso['fecha_inicio_votacion']): ?>
                            <div><i class="bi bi-calendar-check me-1"></i>Votacion: <?= date('d/m/Y H:i', strtotime($proceso['fecha_inicio_votacion'])) ?></div>
                            <?php endif; ?>
                            <div>
                                <i class="bi bi-calendar3 me-1"></i>
                                Periodo: <?= date('d/m/Y', strtotime($proceso['fecha_inicio_periodo'] ?? $proceso['created_at'])) ?>
                                - <?= date('d/m/Y', strtotime($proceso['fecha_fin_periodo'] ?? '+2 years')) ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0">
                        <a href="<?= base_url('comites-elecciones/' . $cliente['id_cliente'] . '/proceso/' . $proceso['id_proceso']) ?>"
                           class="btn btn-primary w-100">
                            <i class="bi bi-gear me-1"></i>Gestionar Proceso
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Procesos Completados -->
    <?php
    $procesosCompletados = array_filter($procesos, fn($p) => $p['estado'] === 'completado');
    ?>
    <?php if (!empty($procesosCompletados)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="mb-3"><i class="bi bi-check-circle me-2 text-success"></i>Procesos Completados</h5>
        </div>
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tipo</th>
                                    <th>Ano</th>
                                    <th>Periodo</th>
                                    <th>Completado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($procesosCompletados as $pc): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-success"><?= esc($pc['tipo_comite']) ?></span>
                                    </td>
                                    <td><?= $pc['anio'] ?></td>
                                    <td>
                                        <?= date('d/m/Y', strtotime($pc['fecha_inicio_periodo'])) ?>
                                        - <?= date('d/m/Y', strtotime($pc['fecha_fin_periodo'])) ?>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($pc['fecha_completado'])) ?></td>
                                    <td>
                                        <a href="<?= base_url('comites-elecciones/' . $cliente['id_cliente'] . '/proceso/' . $pc['id_proceso']) ?>"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye me-1"></i>Ver
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

    <!-- Comites Existentes -->
    <?php if (!empty($comitesExistentes)): ?>
    <div class="row">
        <div class="col-12">
            <h5 class="mb-3"><i class="bi bi-building me-2"></i>Comites Conformados</h5>
        </div>
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Comite</th>
                                    <th>Tipo</th>
                                    <th>Estado</th>
                                    <th>Vigencia</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($comitesExistentes as $ce): ?>
                                <tr>
                                    <td><strong><?= esc($ce['tipo_nombre'] ?? 'Comité') ?></strong></td>
                                    <td><span class="badge bg-secondary"><?= esc($ce['tipo_nombre']) ?></span></td>
                                    <td>
                                        <?php if ($ce['estado'] === 'activo'): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php elseif ($ce['estado'] === 'en_conformacion'): ?>
                                            <span class="badge bg-warning text-dark">En Conformacion</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= ucfirst($ce['estado']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($ce['fecha_conformacion']) && !empty($ce['fecha_vencimiento'])): ?>
                                            <?= date('d/m/Y', strtotime($ce['fecha_conformacion'])) ?>
                                            - <?= date('d/m/Y', strtotime($ce['fecha_vencimiento'])) ?>
                                        <?php elseif (!empty($ce['fecha_conformacion'])): ?>
                                            Desde <?= date('d/m/Y', strtotime($ce['fecha_conformacion'])) ?>
                                        <?php else: ?>
                                            <span class="text-muted">No definido</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= base_url('actas/' . $cliente['id_cliente'] . '/comite/' . $ce['id_comite']) ?>"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-journal-text me-1"></i>Ver Actas
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

<?= $this->endSection() ?>
