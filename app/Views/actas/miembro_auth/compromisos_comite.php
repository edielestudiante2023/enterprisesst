<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="<?= base_url('miembro/dashboard') ?>">Mis Comites</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('miembro/comite/' . $comite['id_comite']) ?>"><?= esc($comite['tipo_nombre']) ?></a></li>
                    <li class="breadcrumb-item active">Compromisos</li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">Compromisos - <?= esc($comite['tipo_nombre']) ?></h1>
                    <p class="text-muted mb-0"><?= esc($cliente['nombre_cliente']) ?></p>
                </div>
                <a href="<?= base_url('miembro/comite/' . $comite['id_comite']) ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Volver
                </a>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-6 col-lg-3 mb-3">
            <div class="card border-0 shadow-sm border-start border-4 border-secondary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Pendientes</h6>
                            <h3 class="mb-0"><?= $stats['pendientes'] ?? 0 ?></h3>
                        </div>
                        <i class="bi bi-clock text-secondary fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <div class="card border-0 shadow-sm border-start border-4 border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">En Progreso</h6>
                            <h3 class="mb-0"><?= $stats['en_progreso'] ?? 0 ?></h3>
                        </div>
                        <i class="bi bi-arrow-repeat text-info fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <div class="card border-0 shadow-sm border-start border-4 border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Completados</h6>
                            <h3 class="mb-0"><?= $stats['completados'] ?? 0 ?></h3>
                        </div>
                        <i class="bi bi-check-circle text-success fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <div class="card border-0 shadow-sm border-start border-4 border-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Vencidos</h6>
                            <h3 class="mb-0"><?= $stats['vencidos'] ?? 0 ?></h3>
                        </div>
                        <i class="bi bi-exclamation-triangle text-danger fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de compromisos -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent">
            <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Compromisos del Comite</h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($compromisos)): ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox text-muted fs-1"></i>
                <p class="text-muted mt-2">No hay compromisos registrados</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Acta</th>
                            <th>Descripcion</th>
                            <th>Responsable</th>
                            <th>Fecha Limite</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($compromisos as $comp): ?>
                        <tr class="<?= ($comp['estado'] ?? '') === 'vencido' ? 'table-danger' : '' ?>">
                            <td>
                                <?php if (!empty($comp['id_acta'])): ?>
                                <a href="<?= base_url('miembro/acta/' . $comp['id_acta']) ?>" class="text-decoration-none">
                                    <?= esc($comp['numero_acta'] ?? '#' . $comp['id_acta']) ?>
                                </a>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= esc($comp['descripcion']) ?>
                                <?php if (!empty($comp['observaciones'])): ?>
                                <br><small class="text-muted"><i class="bi bi-chat-dots me-1"></i><?= esc(substr($comp['observaciones'], 0, 80)) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= esc($comp['responsable_nombre'] ?? 'Sin asignar') ?></td>
                            <td>
                                <?php
                                $fechaVenc = strtotime($comp['fecha_vencimiento'] ?? 'now');
                                $hoy = strtotime('today');
                                $diasRestantes = floor(($fechaVenc - $hoy) / 86400);
                                $estadoComp = $comp['estado'] ?? 'pendiente';
                                ?>
                                <?= date('d/m/Y', $fechaVenc) ?>
                                <?php if (!in_array($estadoComp, ['cumplido', 'cancelado'])): ?>
                                    <?php if ($diasRestantes < 0): ?>
                                        <br><small class="text-danger"><i class="bi bi-exclamation-circle"></i> Vencido hace <?= abs($diasRestantes) ?> dias</small>
                                    <?php elseif ($diasRestantes <= 7): ?>
                                        <br><small class="text-warning"><i class="bi bi-clock"></i> <?= $diasRestantes ?> dias restantes</small>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $estadoBadge = [
                                    'pendiente' => 'bg-secondary',
                                    'en_proceso' => 'bg-info',
                                    'cumplido' => 'bg-success',
                                    'vencido' => 'bg-danger',
                                    'cancelado' => 'bg-dark'
                                ];
                                ?>
                                <span class="badge <?= $estadoBadge[$estadoComp] ?? 'bg-secondary' ?>">
                                    <?= ucfirst(str_replace('_', ' ', $estadoComp)) ?>
                                </span>
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
