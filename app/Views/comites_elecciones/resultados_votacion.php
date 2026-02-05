<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('admindashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('comites-elecciones/' . $cliente['id_cliente']) ?>">Comites</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('comites-elecciones/' . $cliente['id_cliente'] . '/proceso/' . $proceso['id_proceso']) ?>">Proceso</a></li>
            <li class="breadcrumb-item active">Resultados</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="bi bi-bar-chart-fill text-primary me-2"></i>Resultados de Votacion
            </h2>
            <p class="text-muted mb-0"><?= esc($cliente['nombre_cliente']) ?> - <?= esc($proceso['tipo_comite']) ?></p>
        </div>
        <a href="<?= base_url('comites-elecciones/' . $cliente['id_cliente'] . '/proceso/' . $proceso['id_proceso']) ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Volver al Proceso
        </a>
    </div>

    <div class="row">
        <!-- Estadisticas de Participacion -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Participacion</h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <div class="display-3 text-primary fw-bold"><?= $participacion ?>%</div>
                        <p class="text-muted">Participacion Total</p>
                    </div>

                    <div class="progress mb-3" style="height: 25px;">
                        <div class="progress-bar bg-success" style="width: <?= $participacion ?>%">
                            <?= $votosEmitidos ?> votos
                        </div>
                    </div>

                    <div class="row text-center">
                        <div class="col-6 border-end">
                            <h4 class="text-success mb-0"><?= $votosEmitidos ?></h4>
                            <small class="text-muted">Votos Emitidos</small>
                        </div>
                        <div class="col-6">
                            <h4 class="text-secondary mb-0"><?= $totalVotantes ?></h4>
                            <small class="text-muted">Total Habilitados</small>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($proceso['estado'] === 'votacion'): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="<?= base_url('comites-elecciones/proceso/' . $proceso['id_proceso'] . '/finalizar-votacion') ?>" method="post"
                          onsubmit="return confirm('¿Finalizar la votacion y determinar los elegidos?');">
                        <button type="submit" class="btn btn-warning btn-lg w-100">
                            <i class="bi bi-stop-circle me-2"></i>Finalizar Votacion
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Resultados por Candidato -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-trophy me-2"></i>Resultados por Candidato</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($candidatos)): ?>
                    <div class="alert alert-warning">
                        No hay resultados disponibles.
                    </div>
                    <?php else: ?>

                    <?php $posicion = 1; ?>
                    <?php foreach ($candidatos as $c): ?>
                    <div class="d-flex align-items-center mb-4 p-3 border rounded <?= $c['estado'] === 'elegido' ? 'border-success bg-light' : '' ?>">
                        <!-- Posicion -->
                        <div class="me-3 text-center" style="min-width: 50px;">
                            <?php if ($posicion <= 3 && $c['votos_obtenidos'] > 0): ?>
                            <span class="badge bg-<?= $posicion == 1 ? 'warning' : ($posicion == 2 ? 'secondary' : 'danger') ?> fs-5">
                                <?= $posicion ?>
                            </span>
                            <?php else: ?>
                            <span class="badge bg-light text-dark fs-5"><?= $posicion ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Foto -->
                        <div class="me-3">
                            <?php if (!empty($c['foto'])): ?>
                            <img src="<?= base_url($c['foto']) ?>" alt="Foto"
                                 class="rounded-circle" style="width: 60px; height: 60px; object-fit: cover;">
                            <?php else: ?>
                            <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center"
                                 style="width: 60px; height: 60px;">
                                <i class="bi bi-person fs-4"></i>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Info -->
                        <div class="flex-grow-1">
                            <h5 class="mb-1">
                                <?= esc($c['nombres'] . ' ' . $c['apellidos']) ?>
                                <?php if ($c['estado'] === 'elegido'): ?>
                                <span class="badge bg-success ms-2">
                                    <i class="bi bi-check-circle me-1"></i>
                                    <?= $c['tipo_plaza'] === 'principal' ? 'Principal' : 'Suplente' ?>
                                </span>
                                <?php endif; ?>
                            </h5>
                            <p class="text-muted mb-2"><?= esc($c['cargo']) ?></p>

                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-primary" style="width: <?= $c['porcentaje'] ?>%">
                                    <?= $c['porcentaje'] ?>%
                                </div>
                            </div>
                        </div>

                        <!-- Votos -->
                        <div class="text-end ms-3" style="min-width: 100px;">
                            <h3 class="mb-0 text-primary"><?= $c['votos_obtenidos'] ?></h3>
                            <small class="text-muted">votos</small>
                        </div>
                    </div>
                    <?php $posicion++; ?>
                    <?php endforeach; ?>

                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
