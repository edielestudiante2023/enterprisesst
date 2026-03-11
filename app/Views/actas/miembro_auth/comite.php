<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="<?= base_url('miembro/dashboard') ?>">Mis Comites</a></li>
                    <li class="breadcrumb-item active"><?= esc($comite['tipo_nombre']) ?></li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1"><?= esc($comite['tipo_nombre']) ?></h1>
                    <p class="text-muted mb-0"><?= esc($cliente['nombre_cliente']) ?></p>
                </div>
                <a href="<?= base_url('miembro/dashboard') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Volver
                </a>
            </div>
        </div>
    </div>

    <!-- Info del miembro + Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Mi participacion</h6>
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="badge bg-info"><?= ucfirst($miembroEnComite['rol_comite'] ?? 'Miembro') ?></span>
                        <span class="badge bg-<?= ($miembroEnComite['representacion'] ?? '') === 'empleador' ? 'primary' : 'success' ?>">
                            <?= ucfirst($miembroEnComite['representacion'] ?? '-') ?>
                        </span>
                        <span class="badge bg-secondary"><?= ucfirst($miembroEnComite['tipo_miembro'] ?? 'Principal') ?></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Cumplimiento <?= date('Y') ?></h6>
                    <?php
                    $cumplimiento = $estadisticas['cumplimiento'] ?? 0;
                    $colorBarra = $cumplimiento >= 80 ? 'bg-success' : ($cumplimiento >= 50 ? 'bg-warning' : 'bg-danger');
                    $periodosEsperados = $estadisticas['periodos_esperados'] ?? 0;
                    $totalActas = $estadisticas['total'] ?? 0;
                    ?>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="h4 mb-0"><?= $cumplimiento ?>%</span>
                        <small class="text-muted align-self-end"><?= $totalActas ?> de <?= $periodosEsperados ?> posibles en <?= date('Y') ?></small>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar <?= $colorBarra ?>" style="width: <?= $cumplimiento ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <a href="<?= base_url('miembro/comite/' . $comite['id_comite'] . '/compromisos') ?>" class="card border-0 shadow-sm h-100 text-decoration-none">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Compromisos</h6>
                    <div class="d-flex align-items-center">
                        <div class="h4 mb-0 text-warning me-2"><?= count($compromisosPendientes ?? []) ?></div>
                        <small class="text-muted">pendientes</small>
                    </div>
                    <small class="text-primary"><i class="bi bi-arrow-right me-1"></i>Ver todos los compromisos</small>
                </div>
            </a>
        </div>
    </div>

    <!-- Actas del año -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-file-text me-2"></i>Actas <?= date('Y') ?></h5>
            <?php if (!empty($miembroEnComite['puede_crear_actas'])): ?>
            <a href="<?= base_url('miembro/comite/' . $comite['id_comite'] . '/nueva-acta') ?>" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i>Nueva Acta
            </a>
            <?php endif; ?>
        </div>
        <div class="card-body p-0">
            <?php if (empty($actas)): ?>
            <div class="text-center py-5">
                <i class="bi bi-file-earmark-text display-4 text-muted"></i>
                <p class="mt-2 text-muted">No hay actas registradas este ano</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No. Acta</th>
                            <th>Tipo</th>
                            <th>Fecha Reunion</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($actas as $acta): ?>
                        <tr>
                            <td><strong><?= esc($acta['numero_acta']) ?></strong></td>
                            <td><span class="badge bg-secondary"><?= ucfirst($acta['tipo_acta']) ?></span></td>
                            <td><?= date('d/m/Y', strtotime($acta['fecha_reunion'])) ?></td>
                            <td>
                                <?php
                                $estadoClases = [
                                    'borrador' => 'bg-secondary',
                                    'en_edicion' => 'bg-info',
                                    'pendiente_firma' => 'bg-warning text-dark',
                                    'firmada' => 'bg-success',
                                    'cerrada' => 'bg-primary'
                                ];
                                ?>
                                <span class="badge <?= $estadoClases[$acta['estado']] ?? 'bg-secondary' ?>">
                                    <?= ucfirst(str_replace('_', ' ', $acta['estado'])) ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= base_url('miembro/acta/' . $acta['id_acta']) ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-eye me-1"></i>Ver
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
