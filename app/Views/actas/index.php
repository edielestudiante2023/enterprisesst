<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">Comites y Actas</h1>
                    <p class="text-muted mb-0"><?= esc($cliente['nombre_cliente']) ?></p>
                </div>
                <a href="<?= base_url('actas/' . $cliente['id_cliente'] . '/nuevo-comite') ?>" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>Nuevo Comite
                </a>
            </div>
        </div>
    </div>

    <!-- Alertas -->
    <?php if (!empty($sinActaMes)): ?>
    <div class="alert alert-warning" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>Atencion:</strong> Hay <?= count($sinActaMes) ?> comite(s) sin acta del mes actual.
        <?php foreach ($sinActaMes as $c): ?>
            <span class="badge bg-warning text-dark ms-1"><?= esc($c['codigo']) ?></span>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($actasPendientes)): ?>
    <div class="alert alert-info" role="alert">
        <i class="bi bi-pen me-2"></i>
        <strong><?= count($actasPendientes) ?></strong> acta(s) pendiente(s) de firma.
        <a href="#pendientes" class="alert-link">Ver detalle</a>
    </div>
    <?php endif; ?>

    <?php if (!empty($compromisosVencidos)): ?>
    <div class="alert alert-danger" role="alert">
        <i class="bi bi-clock-history me-2"></i>
        <strong><?= count($compromisosVencidos) ?></strong> compromiso(s) vencido(s).
        <a href="<?= base_url('actas/' . $cliente['id_cliente'] . '/compromisos') ?>" class="alert-link">Gestionar</a>
    </div>
    <?php endif; ?>

    <!-- Comites -->
    <div class="row">
        <?php if (empty($comites)): ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-people display-1 text-muted"></i>
                    <h4 class="mt-3">No hay comites registrados</h4>
                    <p class="text-muted">Comience creando el primer comite para esta empresa.</p>
                    <a href="<?= base_url('actas/' . $cliente['id_cliente'] . '/nuevo-comite') ?>" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i>Crear Comite
                    </a>
                </div>
            </div>
        </div>
        <?php else: ?>
            <?php foreach ($comites as $comite): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="badge bg-primary"><?= esc($comite['codigo']) ?></span>
                            <?php if ($comite['estado'] === 'activo'): ?>
                                <span class="badge bg-success">Activo</span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?= ucfirst($comite['estado']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?= esc($comite['tipo_nombre']) ?></h5>

                        <div class="row text-center my-3">
                            <div class="col-4">
                                <div class="h4 mb-0"><?= $comite['miembros'] ?? 0 ?></div>
                                <small class="text-muted">Miembros</small>
                            </div>
                            <div class="col-4">
                                <div class="h4 mb-0"><?= $comite['stats']['total'] ?? 0 ?></div>
                                <small class="text-muted">Actas <?= $anioActual ?></small>
                            </div>
                            <div class="col-4">
                                <div class="h4 mb-0 <?= ($comite['compromisos_pendientes'] ?? 0) > 0 ? 'text-warning' : '' ?>">
                                    <?= $comite['compromisos_pendientes'] ?? 0 ?>
                                </div>
                                <small class="text-muted">Pendientes</small>
                            </div>
                        </div>

                        <!-- Barra de cumplimiento -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small>Cumplimiento <?= $anioActual ?></small>
                                <small><?= $comite['stats']['cumplimiento'] ?? 0 ?>%</small>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <?php
                                $cumplimiento = $comite['stats']['cumplimiento'] ?? 0;
                                $colorBarra = $cumplimiento >= 80 ? 'bg-success' : ($cumplimiento >= 50 ? 'bg-warning' : 'bg-danger');
                                ?>
                                <div class="progress-bar <?= $colorBarra ?>" style="width: <?= $cumplimiento ?>%"></div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="bi bi-calendar3 me-1"></i>
                                Conformado: <?= date('d/m/Y', strtotime($comite['fecha_conformacion'])) ?>
                            </small>
                            <?php if (!empty($comite['fecha_vencimiento'])): ?>
                                <?php
                                $diasRestantes = (strtotime($comite['fecha_vencimiento']) - time()) / 86400;
                                $claseVencimiento = $diasRestantes < 30 ? 'text-danger' : ($diasRestantes < 90 ? 'text-warning' : 'text-muted');
                                ?>
                                <small class="<?= $claseVencimiento ?>">
                                    <i class="bi bi-hourglass-split me-1"></i>
                                    Vence: <?= date('d/m/Y', strtotime($comite['fecha_vencimiento'])) ?>
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0">
                        <div class="d-grid gap-2">
                            <a href="<?= base_url('actas/' . $cliente['id_cliente'] . '/comite/' . $comite['id_comite']) ?>" class="btn btn-outline-primary">
                                <i class="bi bi-eye me-1"></i>Ver Comite
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Actas pendientes de firma -->
    <?php if (!empty($actasPendientes)): ?>
    <div class="row mt-4" id="pendientes">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="bi bi-pen me-2"></i>Actas Pendientes de Firma</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Acta</th>
                                    <th>Comite</th>
                                    <th>Fecha Reunion</th>
                                    <th>Firmas</th>
                                    <th>Cerrada</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($actasPendientes as $acta): ?>
                                <tr>
                                    <td><strong><?= esc($acta['numero_acta']) ?></strong></td>
                                    <td><span class="badge bg-secondary"><?= esc($acta['tipo_comite']) ?></span></td>
                                    <td><?= date('d/m/Y', strtotime($acta['fecha_reunion'])) ?></td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?= $acta['firmantes_completados'] ?> / <?= $acta['total_firmantes'] ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($acta['fecha_cierre'])) ?></td>
                                    <td>
                                        <a href="<?= base_url('actas/firmas/' . $acta['id_acta']) ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Estado
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
