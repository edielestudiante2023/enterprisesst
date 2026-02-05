<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>

<?php
$tipoComiteNombre = [
    'COPASST' => 'COPASST',
    'COCOLAB' => 'Comité de Convivencia Laboral',
    'BRIGADA' => 'Brigada de Emergencias',
    'VIGIA' => 'Vigía SST'
][$proceso['tipo_comite']] ?? $proceso['tipo_comite'];

$motivosLabels = [
    'terminacion_contrato' => 'Terminación del contrato de trabajo',
    'renuncia_voluntaria' => 'Renuncia voluntaria al comité',
    'sancion_disciplinaria' => 'Sanción disciplinaria por falta grave',
    'violacion_confidencialidad' => 'Violación del deber de confidencialidad',
    'inasistencia_reiterada' => 'Inasistencia a más de 3 reuniones consecutivas',
    'incumplimiento_funciones' => 'Incumplimiento reiterado de obligaciones',
    'fallecimiento' => 'Fallecimiento',
    'otro' => 'Otro motivo'
];

$tiposIngreso = [
    'siguiente_votacion' => 'Siguiente candidato en votación',
    'designacion_empleador' => 'Designación del empleador',
    'asamblea_extraordinaria' => 'Asamblea extraordinaria de trabajadores'
];

// Datos del entrante (puede venir de candidatos o de campos manuales)
$nombreEntrante = $entrante
    ? trim($entrante['nombres'] . ' ' . $entrante['apellidos'])
    : trim(($recomposicion['entrante_nombres'] ?? '') . ' ' . ($recomposicion['entrante_apellidos'] ?? ''));
$documentoEntrante = $entrante
    ? $entrante['documento_identidad']
    : ($recomposicion['entrante_documento'] ?? '');
$cargoEntrante = $entrante
    ? $entrante['cargo']
    : ($recomposicion['entrante_cargo'] ?? '');
?>

<style>
.detail-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    margin-bottom: 20px;
}
.detail-card .card-header {
    border-radius: 12px 12px 0 0;
    padding: 15px 20px;
    font-weight: 600;
}
.persona-box {
    border: 2px solid;
    border-radius: 12px;
    padding: 20px;
    text-align: center;
}
.persona-box.saliente {
    border-color: #dc3545;
    background: linear-gradient(to bottom, #fff5f5, white);
}
.persona-box.entrante {
    border-color: #28a745;
    background: linear-gradient(to bottom, #f0fff4, white);
}
.persona-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    font-size: 1.5rem;
}
.info-label {
    font-size: 0.8rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.info-value {
    font-weight: 600;
    color: #333;
}
.arrow-container {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
}
.arrow-big {
    font-size: 3rem;
    color: #28a745;
}
.miembro-row {
    padding: 10px 15px;
    border-bottom: 1px solid #eee;
}
.miembro-row:last-child {
    border-bottom: none;
}
.badge-tipo {
    font-size: 0.7rem;
}
.timeline-item {
    padding-left: 20px;
    border-left: 2px solid #dee2e6;
    margin-left: 10px;
    padding-bottom: 15px;
}
.timeline-item:last-child {
    border-left-color: transparent;
}
.timeline-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    position: absolute;
    left: -7px;
    top: 5px;
}
</style>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="fas fa-exchange-alt text-secondary me-2"></i>
                Recomposicion #<?= $recomposicion['numero_recomposicion'] ?>
            </h4>
            <p class="text-muted mb-0">
                <?= esc($tipoComiteNombre) ?> - <?= esc($cliente['razon_social']) ?>
            </p>
        </div>
        <div>
            <a href="<?= base_url("comites-elecciones/proceso/{$proceso['id_proceso']}/recomposicion/{$recomposicion['id_recomposicion']}/acta-pdf") ?>"
               class="btn btn-danger me-2" target="_blank">
                <i class="fas fa-file-pdf me-1"></i> Ver Acta PDF
            </a>
            <a href="<?= base_url("comites-elecciones/proceso/{$proceso['id_proceso']}/recomposiciones") ?>"
               class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
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

    <div class="row">
        <!-- Columna izquierda: El cambio -->
        <div class="col-lg-8">
            <!-- Tarjeta del cambio -->
            <div class="detail-card card">
                <div class="card-header bg-secondary text-white">
                    <i class="fas fa-users me-2"></i>
                    Cambio de Miembro
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <!-- Saliente -->
                        <div class="col-md-5">
                            <div class="persona-box saliente">
                                <div class="persona-icon bg-danger text-white">
                                    <i class="fas fa-user-minus"></i>
                                </div>
                                <span class="badge bg-danger mb-2">SALE</span>
                                <h5 class="mb-1"><?= esc($saliente['nombres'] . ' ' . $saliente['apellidos']) ?></h5>
                                <p class="mb-1 text-muted">C.C. <?= esc($saliente['documento_identidad']) ?></p>
                                <small class="text-muted"><?= esc($saliente['cargo']) ?></small>
                                <hr>
                                <div class="info-label">Representacion</div>
                                <div class="info-value">
                                    <?= $saliente['representacion'] === 'trabajador' ? 'Trabajadores' : 'Empleador' ?>
                                    - <?= ucfirst($saliente['tipo_plaza'] ?? 'Principal') ?>
                                </div>
                            </div>
                        </div>

                        <!-- Flecha -->
                        <div class="col-md-2">
                            <div class="arrow-container">
                                <i class="fas fa-arrow-right arrow-big"></i>
                            </div>
                        </div>

                        <!-- Entrante -->
                        <div class="col-md-5">
                            <div class="persona-box entrante">
                                <div class="persona-icon bg-success text-white">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <span class="badge bg-success mb-2">INGRESA</span>
                                <h5 class="mb-1"><?= esc($nombreEntrante) ?></h5>
                                <p class="mb-1 text-muted">C.C. <?= esc($documentoEntrante) ?></p>
                                <small class="text-muted"><?= esc($cargoEntrante) ?></small>
                                <hr>
                                <div class="info-label">Tipo de ingreso</div>
                                <div class="info-value">
                                    <?= esc($tiposIngreso[$recomposicion['tipo_ingreso']] ?? $recomposicion['tipo_ingreso']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tarjeta del motivo -->
            <div class="detail-card card">
                <div class="card-header bg-warning text-dark">
                    <i class="fas fa-clipboard-list me-2"></i>
                    Motivo de la Salida
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="info-label">Causal de retiro</div>
                            <div class="info-value">
                                <?= esc($motivosLabels[$recomposicion['motivo_salida']] ?? $recomposicion['motivo_salida']) ?>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="info-label">Fecha efectiva salida</div>
                            <div class="info-value">
                                <?= date('d/m/Y', strtotime($recomposicion['fecha_efectiva_salida'])) ?>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="info-label">Fecha recomposicion</div>
                            <div class="info-value">
                                <?= date('d/m/Y', strtotime($recomposicion['fecha_recomposicion'])) ?>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($recomposicion['motivo_detalle'])): ?>
                    <div class="mt-3">
                        <div class="info-label">Detalle</div>
                        <div class="info-value"><?= esc($recomposicion['motivo_detalle']) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Miembros actuales del comite -->
            <div class="detail-card card">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-users me-2"></i>
                    Conformacion Actual del Comite
                </div>
                <div class="card-body p-0">
                    <!-- Representantes de trabajadores -->
                    <div class="bg-light p-2 px-3 fw-bold border-bottom">
                        <i class="fas fa-hard-hat text-primary me-2"></i>
                        Representantes de los Trabajadores
                    </div>
                    <?php
                    $trabajadores = array_filter($miembrosActuales, fn($m) => $m['representacion'] === 'trabajador');
                    $empleadores = array_filter($miembrosActuales, fn($m) => $m['representacion'] === 'empleador');
                    ?>
                    <?php foreach ($trabajadores as $m): ?>
                    <div class="miembro-row d-flex justify-content-between align-items-center">
                        <div>
                            <strong><?= esc($m['nombres'] . ' ' . $m['apellidos']) ?></strong>
                            <?php if (isset($m['es_nuevo']) && $m['es_nuevo']): ?>
                                <span class="badge bg-success ms-2">NUEVO (B)</span>
                            <?php else: ?>
                                <span class="badge bg-secondary ms-2">Continuante (A)</span>
                            <?php endif; ?>
                            <br>
                            <small class="text-muted">C.C. <?= esc($m['documento_identidad']) ?> - <?= esc($m['cargo']) ?></small>
                        </div>
                        <span class="badge badge-tipo <?= $m['tipo_plaza'] === 'suplente' ? 'bg-info' : 'bg-primary' ?>">
                            <?= ucfirst($m['tipo_plaza'] ?? 'Principal') ?>
                        </span>
                    </div>
                    <?php endforeach; ?>

                    <!-- Representantes del empleador -->
                    <div class="bg-light p-2 px-3 fw-bold border-bottom border-top">
                        <i class="fas fa-building text-success me-2"></i>
                        Representantes del Empleador
                    </div>
                    <?php foreach ($empleadores as $m): ?>
                    <div class="miembro-row d-flex justify-content-between align-items-center">
                        <div>
                            <strong><?= esc($m['nombres'] . ' ' . $m['apellidos']) ?></strong>
                            <?php if (isset($m['es_nuevo']) && $m['es_nuevo']): ?>
                                <span class="badge bg-success ms-2">NUEVO (B)</span>
                            <?php else: ?>
                                <span class="badge bg-secondary ms-2">Continuante (A)</span>
                            <?php endif; ?>
                            <br>
                            <small class="text-muted">C.C. <?= esc($m['documento_identidad']) ?> - <?= esc($m['cargo']) ?></small>
                        </div>
                        <span class="badge badge-tipo <?= $m['tipo_plaza'] === 'suplente' ? 'bg-info' : 'bg-success' ?>">
                            <?= ucfirst($m['tipo_plaza'] ?? 'Principal') ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Columna derecha: Info adicional -->
        <div class="col-lg-4">
            <!-- Estado -->
            <div class="detail-card card">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-info-circle me-2"></i>
                    Estado del Acta
                </div>
                <div class="card-body text-center">
                    <?php
                    $estadoBadge = match($recomposicion['estado']) {
                        'firmado' => 'bg-success',
                        'pendiente_firmas' => 'bg-info',
                        'borrador' => 'bg-warning text-dark',
                        'cancelado' => 'bg-danger',
                        default => 'bg-secondary'
                    };
                    $estadoLabel = match($recomposicion['estado']) {
                        'firmado' => 'Firmado',
                        'pendiente_firmas' => 'Pendiente de Firmas',
                        'borrador' => 'Borrador',
                        'cancelado' => 'Cancelado',
                        default => $recomposicion['estado']
                    };
                    ?>
                    <span class="badge <?= $estadoBadge ?> fs-5 mb-3"><?= $estadoLabel ?></span>

                    <?php if ($recomposicion['estado'] === 'borrador'): ?>
                    <div class="d-grid gap-2">
                        <a href="#" class="btn btn-primary">
                            <i class="fas fa-signature me-1"></i> Solicitar Firmas
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Justificacion legal -->
            <?php if (!empty($recomposicion['justificacion_legal'])): ?>
            <div class="detail-card card">
                <div class="card-header bg-dark text-white">
                    <i class="fas fa-gavel me-2"></i>
                    Justificacion Legal
                </div>
                <div class="card-body">
                    <p class="mb-0 small"><?= nl2br(esc($recomposicion['justificacion_legal'])) ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Observaciones -->
            <?php if (!empty($recomposicion['observaciones'])): ?>
            <div class="detail-card card">
                <div class="card-header bg-secondary text-white">
                    <i class="fas fa-sticky-note me-2"></i>
                    Observaciones
                </div>
                <div class="card-body">
                    <p class="mb-0"><?= nl2br(esc($recomposicion['observaciones'])) ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Registro -->
            <div class="detail-card card">
                <div class="card-header bg-light">
                    <i class="fas fa-history me-2"></i>
                    Registro
                </div>
                <div class="card-body">
                    <div class="small">
                        <div class="mb-2">
                            <span class="info-label">Creado</span><br>
                            <?= date('d/m/Y H:i', strtotime($recomposicion['created_at'])) ?>
                        </div>
                        <div>
                            <span class="info-label">Ultima actualizacion</span><br>
                            <?= date('d/m/Y H:i', strtotime($recomposicion['updated_at'])) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
