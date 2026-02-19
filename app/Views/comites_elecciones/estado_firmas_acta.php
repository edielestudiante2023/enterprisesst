<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>

<?php
$tipoComiteNombre = [
    'COPASST' => 'COPASST',
    'COCOLAB' => 'Comite de Convivencia Laboral',
    'BRIGADA' => 'Brigada de Emergencias',
    'VIGIA' => 'Vigia SST'
][$proceso['tipo_comite']] ?? $proceso['tipo_comite'];
?>

<style>
.header-estado {
    background: linear-gradient(135deg, #059669 0%, #10b981 100%);
    border-radius: 12px;
    padding: 25px;
    color: white;
    margin-bottom: 25px;
}

.progress-firmas {
    height: 25px;
    border-radius: 12px;
    background-color: #e9ecef;
}

.progress-firmas .progress-bar {
    border-radius: 12px;
    font-weight: 600;
}

.card-solicitud {
    border: none;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    margin-bottom: 15px;
    transition: transform 0.2s, box-shadow 0.2s;
}

.card-solicitud:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.card-solicitud.firmado {
    border-left: 4px solid #198754;
}

.card-solicitud.pendiente {
    border-left: 4px solid #ffc107;
}

.card-solicitud.esperando {
    border-left: 4px solid #6c757d;
}

.card-solicitud.cancelado {
    border-left: 4px solid #dc3545;
    opacity: 0.7;
}

.firma-imagen {
    max-width: 150px;
    max-height: 60px;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    background: white;
}

.timeline-firma {
    font-size: 0.8rem;
    color: #6c757d;
}

.timeline-firma i {
    width: 20px;
}

.stats-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.stats-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
    font-size: 1.25rem;
}

/* Panel de desarrollo */
.dev-panel {
    background: #fff3cd;
    border: 2px dashed #ffc107;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
}
</style>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="header-estado">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="mb-1">
                    <i class="fas fa-tasks me-2"></i>
                    Estado de Firmas Electronicas
                </h4>
                <p class="mb-2 opacity-75">
                    Acta de Constitucion <?= esc($tipoComiteNombre) ?> - <?= esc($proceso['anio']) ?>
                </p>
                <!-- Barra de progreso -->
                <div class="progress progress-firmas mt-3" style="max-width: 400px;">
                    <div class="progress-bar bg-success"
                         role="progressbar"
                         style="width: <?= $porcentaje ?>%"
                         aria-valuenow="<?= $porcentaje ?>"
                         aria-valuemin="0"
                         aria-valuemax="100">
                        <?= $porcentaje ?>% Completado
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-end">
                <a href="<?= base_url("comites-elecciones/proceso/{$proceso['id_proceso']}/firmas") ?>"
                   class="btn btn-outline-light btn-sm me-2">
                    <i class="fas fa-plus me-1"></i> Agregar Firmantes
                </a>
                <a href="<?= base_url("comites-elecciones/proceso/{$proceso['id_proceso']}/acta") ?>"
                   class="btn btn-outline-light btn-sm">
                    <i class="fas fa-file-alt me-1"></i> Ver Acta
                </a>
            </div>
        </div>
    </div>

    <!-- Alertas -->
    <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>
        <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Panel de desarrollo (solo en development) -->
    <?php if (ENVIRONMENT === 'development' && !empty($solicitudes)): ?>
    <div class="dev-panel">
        <h6 class="mb-2"><i class="fas fa-flask me-2"></i>Panel de Desarrollo - Enlaces Directos para Pruebas</h6>
        <p class="small text-muted mb-2">Estos enlaces permiten probar las firmas sin enviar correos reales:</p>
        <div class="row">
            <?php foreach ($solicitudes as $sol): ?>
                <?php if (in_array($sol['estado'], ['pendiente', 'esperando'])): ?>
                <div class="col-md-6 col-lg-4 mb-2">
                    <a href="<?= base_url('firma/firmar/' . $sol['token']) ?>"
                       target="_blank"
                       class="btn btn-sm btn-outline-warning w-100 text-start">
                        <i class="fas fa-external-link-alt me-1"></i>
                        <?= esc($sol['firmante_nombre']) ?>
                        <small class="d-block text-muted"><?= esc($sol['firmante_cargo']) ?></small>
                    </a>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Estadisticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-icon bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="mb-0"><?= $totalSolicitudes ?></h3>
                <small class="text-muted">Total Solicitudes</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-icon bg-success bg-opacity-10 text-success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 class="mb-0"><?= $firmados ?></h3>
                <small class="text-muted">Firmados</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-icon bg-warning bg-opacity-10 text-warning">
                    <i class="fas fa-clock"></i>
                </div>
                <h3 class="mb-0"><?= $pendientes ?></h3>
                <small class="text-muted">Pendientes</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-icon bg-info bg-opacity-10 text-info">
                    <i class="fas fa-percentage"></i>
                </div>
                <h3 class="mb-0"><?= $porcentaje ?>%</h3>
                <small class="text-muted">Completado</small>
            </div>
        </div>
    </div>

    <!-- Lista de solicitudes -->
    <div class="row">
        <div class="col-12">
            <h5 class="mb-3">
                <i class="fas fa-list me-2 text-muted"></i>
                Detalle de Solicitudes
            </h5>

            <?php if (empty($solicitudes)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                No hay solicitudes de firma creadas aun.
                <a href="<?= base_url("comites-elecciones/proceso/{$proceso['id_proceso']}/firmas") ?>" class="alert-link">
                    Crear solicitudes
                </a>
            </div>
            <?php else: ?>

            <?php foreach ($solicitudes as $sol): ?>
            <?php
                $estadoClase = match($sol['estado']) {
                    'firmado' => 'firmado',
                    'pendiente' => 'pendiente',
                    'esperando' => 'esperando',
                    'cancelado' => 'cancelado',
                    default => ''
                };
                $evidencia = $evidencias[$sol['id_solicitud']] ?? null;
            ?>
            <div class="card card-solicitud <?= $estadoClase ?>">
                <div class="card-body">
                    <div class="row align-items-center">
                        <!-- Info del firmante -->
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <?php if ($sol['estado'] === 'firmado'): ?>
                                        <span class="badge bg-success rounded-pill p-2">
                                            <i class="fas fa-check fa-lg"></i>
                                        </span>
                                    <?php elseif ($sol['estado'] === 'pendiente'): ?>
                                        <span class="badge bg-warning rounded-pill p-2">
                                            <i class="fas fa-clock fa-lg"></i>
                                        </span>
                                    <?php elseif ($sol['estado'] === 'esperando'): ?>
                                        <span class="badge bg-secondary rounded-pill p-2">
                                            <i class="fas fa-hourglass-half fa-lg"></i>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger rounded-pill p-2">
                                            <i class="fas fa-times fa-lg"></i>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h6 class="mb-0"><?= esc($sol['firmante_nombre']) ?></h6>
                                    <small class="text-muted"><?= esc($sol['firmante_cargo']) ?></small>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-envelope me-1"></i>
                                        <?= esc($sol['firmante_email']) ?>
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Estado y timeline -->
                        <div class="col-md-4">
                            <div class="timeline-firma">
                                <div class="mb-1">
                                    <i class="fas fa-paper-plane text-primary"></i>
                                    Enviado: <?= date('d/m/Y H:i', strtotime($sol['created_at'])) ?>
                                </div>
                                <?php if ($sol['estado'] === 'firmado' && $sol['fecha_firma']): ?>
                                <div class="text-success">
                                    <i class="fas fa-signature"></i>
                                    Firmado: <?= date('d/m/Y H:i', strtotime($sol['fecha_firma'])) ?>
                                </div>
                                <?php endif; ?>
                                <?php if ($evidencia): ?>
                                <div class="mt-1">
                                    <small>
                                        <i class="fas fa-globe me-1"></i>
                                        IP: <?= esc($evidencia['ip_address'] ?? 'N/A') ?>
                                    </small>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Firma/Acciones -->
                        <div class="col-md-4 text-end">
                            <?php if ($sol['estado'] === 'firmado' && $evidencia && !empty($evidencia['firma_imagen'])): ?>
                                <img src="<?= $evidencia['firma_imagen'] ?>"
                                     alt="Firma"
                                     class="firma-imagen">
                            <?php elseif (in_array($sol['estado'], ['pendiente', 'esperando'])): ?>
                                <?php $urlFirmaCopasst = base_url('firma/firmar/' . $sol['token']); ?>
                                <button type="button" class="btn btn-sm btn-outline-info me-1"
                                        onclick="copiarEnlaceFirma('<?= $urlFirmaCopasst ?>', '<?= esc($sol['firmante_nombre']) ?>')"
                                        title="Copiar enlace de firma">
                                    <i class="fas fa-copy me-1"></i>Copiar enlace
                                </button>
                                <?php if ($sol['estado'] === 'pendiente'): ?>
                                <form action="<?= base_url("firma/reenviar/{$sol['id_solicitud']}") ?>" method="post" class="d-inline">
                                    <button type="submit" class="btn btn-sm btn-outline-warning me-1"
                                            onclick="return confirm('Reenviar solicitud de firma?')">
                                        <i class="fas fa-redo me-1"></i>Reenviar
                                    </button>
                                </form>
                                <button type="button" class="btn btn-sm btn-outline-warning me-1"
                                        onclick="modalEmailAlternativo('<?= base_url("firma/reenviar/{$sol['id_solicitud']}") ?>', '<?= esc($sol['firmante_nombre']) ?>', '<?= esc($sol['firmante_email']) ?>')"
                                        title="Enviar a email alternativo">
                                    <i class="fas fa-at me-1"></i>Email alt.
                                </button>
                                <a href="<?= base_url("firma/cancelar/{$sol['id_solicitud']}") ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Cancelar esta solicitud de firma?')">
                                    <i class="fas fa-times"></i>
                                </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <?php endif; ?>
        </div>
    </div>

    <!-- Botones inferiores -->
    <div class="d-flex justify-content-between mt-4">
        <a href="<?= base_url("comites-elecciones/{$cliente['id_cliente']}/proceso/{$proceso['id_proceso']}") ?>"
           class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver al Proceso
        </a>
        <div>
            <?php if ($firmados === $totalSolicitudes && $totalSolicitudes > 0): ?>
            <div class="alert alert-success d-inline-block mb-0 py-2 px-3">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Todas las firmas completadas!</strong>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= base_url('js/firma-helpers.js') ?>"></script>

<?= $this->endSection() ?>
