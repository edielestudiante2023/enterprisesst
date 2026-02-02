<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="<?= base_url('miembro/dashboard') ?>">Mis Comites</a></li>
                    <li class="breadcrumb-item active">Mis Compromisos</li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">Mis Compromisos</h1>
                    <p class="text-muted mb-0">Compromisos asignados a <?= esc($miembro['nombre_completo'] ?? 'mi') ?></p>
                </div>
                <a href="<?= base_url('miembro/dashboard') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Volver
                </a>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <?php
        $pendientes = 0;
        $enProgreso = 0;
        $completados = 0;
        $vencidos = 0;

        foreach ($compromisos as $comp) {
            switch ($comp['estado']) {
                case 'pendiente':
                    $pendientes++;
                    break;
                case 'en_progreso':
                    $enProgreso++;
                    break;
                case 'completado':
                    $completados++;
                    break;
                case 'vencido':
                    $vencidos++;
                    break;
            }
        }
        ?>
        <div class="col-6 col-lg-3 mb-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <div class="h2 mb-0 text-secondary"><?= $pendientes ?></div>
                    <small class="text-muted">Pendientes</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <div class="h2 mb-0 text-info"><?= $enProgreso ?></div>
                    <small class="text-muted">En Progreso</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <div class="h2 mb-0 text-success"><?= $completados ?></div>
                    <small class="text-muted">Completados</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <div class="h2 mb-0 text-danger"><?= $vencidos ?></div>
                    <small class="text-muted">Vencidos</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de compromisos -->
    <?php if (!empty($compromisos)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent">
            <h6 class="mb-0"><i class="bi bi-list-check me-2"></i>Listado de Compromisos</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Compromiso</th>
                            <th>Acta</th>
                            <th>Fecha Limite</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($compromisos as $comp): ?>
                        <tr>
                            <td>
                                <div class="fw-medium"><?= esc($comp['descripcion']) ?></div>
                                <?php if (!empty($comp['observaciones'])): ?>
                                    <small class="text-muted"><?= esc($comp['observaciones']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($comp['id_acta'])): ?>
                                    <a href="<?= base_url('miembro/acta/' . $comp['id_acta']) ?>" class="text-decoration-none">
                                        Acta <?= esc($comp['numero_acta'] ?? '#' . $comp['id_acta']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($comp['fecha_limite'])): ?>
                                    <?php
                                    $fechaLimite = strtotime($comp['fecha_limite']);
                                    $hoy = strtotime(date('Y-m-d'));
                                    $diasRestantes = floor(($fechaLimite - $hoy) / (60 * 60 * 24));
                                    $claseTexto = '';

                                    if ($diasRestantes < 0 && $comp['estado'] !== 'completado') {
                                        $claseTexto = 'text-danger fw-bold';
                                    } elseif ($diasRestantes <= 3 && $comp['estado'] !== 'completado') {
                                        $claseTexto = 'text-warning';
                                    }
                                    ?>
                                    <span class="<?= $claseTexto ?>">
                                        <?= date('d/m/Y', $fechaLimite) ?>
                                    </span>
                                    <?php if ($diasRestantes < 0 && $comp['estado'] !== 'completado'): ?>
                                        <br><small class="text-danger">Vencido hace <?= abs($diasRestantes) ?> dias</small>
                                    <?php elseif ($diasRestantes == 0 && $comp['estado'] !== 'completado'): ?>
                                        <br><small class="text-warning">Vence hoy</small>
                                    <?php elseif ($diasRestantes > 0 && $diasRestantes <= 3 && $comp['estado'] !== 'completado'): ?>
                                        <br><small class="text-warning">Faltan <?= $diasRestantes ?> dias</small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">Sin fecha</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $estadoBadges = [
                                    'pendiente' => 'bg-secondary',
                                    'en_progreso' => 'bg-info',
                                    'completado' => 'bg-success',
                                    'vencido' => 'bg-danger'
                                ];
                                ?>
                                <span class="badge <?= $estadoBadges[$comp['estado']] ?? 'bg-secondary' ?>">
                                    <?= ucfirst(str_replace('_', ' ', $comp['estado'])) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-check-circle text-success fs-1"></i>
            <h5 class="mt-3">Sin compromisos pendientes</h5>
            <p class="text-muted mb-0">No tienes compromisos asignados actualmente</p>
        </div>
    </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
