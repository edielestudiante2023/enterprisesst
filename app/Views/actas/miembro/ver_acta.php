<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="<?= base_url('miembro/actas/' . $comite['id_comite']) ?>">Mis Actas</a></li>
                    <li class="breadcrumb-item active">Acta <?= esc($acta['numero_acta']) ?></li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 class="h3 mb-1">Acta <?= esc($acta['numero_acta']) ?></h1>
                    <p class="text-muted mb-0"><?= esc($comite['tipo_nombre']) ?></p>
                </div>
                <div>
                    <?php
                    $estadoClases = [
                        'borrador' => 'bg-secondary',
                        'pendiente_firma' => 'bg-warning text-dark',
                        'firmada' => 'bg-success',
                        'cerrada' => 'bg-primary'
                    ];
                    ?>
                    <span class="badge <?= $estadoClases[$acta['estado']] ?? 'bg-secondary' ?> fs-6">
                        <?= ucfirst(str_replace('_', ' ', $acta['estado'])) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Información de la reunión -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informacion de la Reunion</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <small class="text-muted d-block">Tipo de Acta</small>
                            <strong><?= ucfirst($acta['tipo_acta']) ?></strong>
                        </div>
                        <div class="col-md-3 mb-3">
                            <small class="text-muted d-block">Fecha</small>
                            <strong><?= date('d/m/Y', strtotime($acta['fecha_reunion'])) ?></strong>
                        </div>
                        <div class="col-md-3 mb-3">
                            <small class="text-muted d-block">Modalidad</small>
                            <strong><?= ucfirst($acta['modalidad']) ?></strong>
                        </div>
                        <div class="col-md-3 mb-3">
                            <small class="text-muted d-block">Horario</small>
                            <strong><?= $acta['hora_inicio'] ?> - <?= $acta['hora_fin'] ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Orden del día -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="bi bi-list-ol me-2"></i>Orden del Dia</h5>
                </div>
                <div class="card-body">
                    <ol class="mb-0">
                        <?php
                        $ordenDia = is_string($acta['orden_del_dia']) ? json_decode($acta['orden_del_dia'], true) : $acta['orden_del_dia'];
                        if (!empty($ordenDia)):
                            foreach ($ordenDia as $punto):
                        ?>
                        <li class="mb-2"><?= esc($punto['tema'] ?? '') ?></li>
                        <?php
                            endforeach;
                        endif;
                        ?>
                    </ol>
                </div>
            </div>

            <!-- Desarrollo -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="bi bi-journal-text me-2"></i>Desarrollo</h5>
                </div>
                <div class="card-body">
                    <?php
                    $desarrollo = is_string($acta['desarrollo']) ? json_decode($acta['desarrollo'], true) : $acta['desarrollo'];
                    if (!empty($ordenDia)):
                        foreach ($ordenDia as $punto):
                            $numPunto = $punto['punto'] ?? '';
                            $contenido = $desarrollo[$numPunto] ?? '';
                    ?>
                    <div class="mb-4">
                        <h6 class="fw-bold"><?= $numPunto ?>. <?= esc($punto['tema'] ?? '') ?></h6>
                        <?php if (!empty($contenido)): ?>
                            <p class="mb-0" style="white-space: pre-line;"><?= esc($contenido) ?></p>
                        <?php else: ?>
                            <p class="text-muted mb-0 fst-italic">Sin desarrollo</p>
                        <?php endif; ?>
                    </div>
                    <?php
                        endforeach;
                    endif;
                    ?>
                </div>
            </div>

            <!-- Compromisos -->
            <?php if (!empty($compromisos)): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Compromisos</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Descripcion</th>
                                    <th>Responsable</th>
                                    <th>Fecha Limite</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($compromisos as $comp): ?>
                                <tr class="<?= $comp['id_responsable'] == $miAsistente['id_asistente'] ? 'table-info' : '' ?>">
                                    <td>
                                        <?= esc($comp['descripcion']) ?>
                                        <?php if ($comp['id_responsable'] == $miAsistente['id_asistente']): ?>
                                            <span class="badge bg-primary">Mi compromiso</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($comp['responsable_nombre'] ?? 'Sin asignar') ?></td>
                                    <td><?= date('d/m/Y', strtotime($comp['fecha_limite'])) ?></td>
                                    <td>
                                        <?php
                                        $estadoComp = [
                                            'pendiente' => '<span class="badge bg-secondary">Pendiente</span>',
                                            'en_progreso' => '<span class="badge bg-info">En progreso</span>',
                                            'completado' => '<span class="badge bg-success">Completado</span>',
                                            'vencido' => '<span class="badge bg-danger">Vencido</span>'
                                        ];
                                        echo $estadoComp[$comp['estado']] ?? '<span class="badge bg-secondary">-</span>';
                                        ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Mi estado de firma -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="bi bi-pen me-2"></i>Mi Firma</h5>
                </div>
                <div class="card-body text-center">
                    <?php if (!empty($miAsistente['firma_fecha'])): ?>
                        <div class="text-success mb-3">
                            <i class="bi bi-check-circle fs-1"></i>
                        </div>
                        <p class="mb-2"><strong>Firmada</strong></p>
                        <p class="text-muted mb-0">
                            <?= date('d/m/Y H:i', strtotime($miAsistente['firma_fecha'])) ?>
                        </p>
                        <?php if (!empty($miAsistente['firma_imagen'])): ?>
                        <hr>
                        <img src="data:image/png;base64,<?= $miAsistente['firma_imagen'] ?>" alt="Mi firma" class="img-fluid" style="max-height: 100px;">
                        <?php endif; ?>
                    <?php elseif ($acta['estado'] === 'pendiente_firma'): ?>
                        <div class="text-warning mb-3">
                            <i class="bi bi-clock fs-1"></i>
                        </div>
                        <p class="mb-3"><strong>Pendiente de firma</strong></p>
                        <?php if (!empty($miAsistente['token_firma'])): ?>
                        <a href="<?= base_url('actas/firmar/' . $miAsistente['token_firma']) ?>" class="btn btn-warning">
                            <i class="bi bi-pen me-1"></i> Firmar Ahora
                        </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-muted mb-3">
                            <i class="bi bi-dash-circle fs-1"></i>
                        </div>
                        <p class="mb-0 text-muted">El acta aun no esta en proceso de firma</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Asistentes -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="bi bi-people me-2"></i>Asistentes</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php foreach ($asistentes as $asist): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center <?= $asist['id_asistente'] == $miAsistente['id_asistente'] ? 'bg-light' : '' ?>">
                            <div>
                                <strong><?= esc($asist['nombre_completo']) ?></strong>
                                <?php if ($asist['id_asistente'] == $miAsistente['id_asistente']): ?>
                                    <span class="badge bg-primary">Yo</span>
                                <?php endif; ?>
                                <?php if (!empty($asist['rol_comite']) && $asist['rol_comite'] !== 'miembro'): ?>
                                    <span class="badge bg-warning text-dark"><?= ucfirst($asist['rol_comite']) ?></span>
                                <?php endif; ?>
                                <br><small class="text-muted"><?= esc($asist['cargo'] ?? '') ?></small>
                            </div>
                            <?php if (!empty($asist['firma_fecha'])): ?>
                                <span class="badge bg-success"><i class="bi bi-check"></i></span>
                            <?php elseif ($acta['estado'] === 'pendiente_firma'): ?>
                                <span class="badge bg-secondary"><i class="bi bi-clock"></i></span>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <!-- Acciones -->
            <div class="d-grid gap-2">
                <a href="<?= base_url('miembro/actas/' . $comite['id_comite']) ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Volver
                </a>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
