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
    'terminacion_contrato' => 'Terminación del contrato',
    'renuncia_voluntaria' => 'Renuncia voluntaria',
    'sancion_disciplinaria' => 'Sanción disciplinaria',
    'violacion_confidencialidad' => 'Violación de confidencialidad',
    'inasistencia_reiterada' => 'Inasistencia reiterada',
    'incumplimiento_funciones' => 'Incumplimiento de funciones',
    'fallecimiento' => 'Fallecimiento',
    'otro' => 'Otro motivo'
];
?>

<style>
.header-recomposicion {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    border-radius: 12px;
    padding: 25px;
    color: white;
    margin-bottom: 25px;
}
.card-recomposicion {
    border: none;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    margin-bottom: 15px;
    transition: transform 0.2s;
}
.card-recomposicion:hover {
    transform: translateY(-2px);
}
.card-recomposicion.borrador {
    border-left: 4px solid #ffc107;
}
.card-recomposicion.pendiente_firmas {
    border-left: 4px solid #17a2b8;
}
.card-recomposicion.firmado {
    border-left: 4px solid #28a745;
}
.card-recomposicion.cancelado {
    border-left: 4px solid #dc3545;
    opacity: 0.7;
}
.badge-motivo {
    font-size: 0.75rem;
    padding: 4px 10px;
}
.arrow-reemplazo {
    font-size: 1.5rem;
    color: #28a745;
}
.stat-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}
.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
    font-size: 1.25rem;
}
</style>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="header-recomposicion">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="mb-1">
                    <i class="fas fa-exchange-alt me-2"></i>
                    Recomposiciones del <?= esc($tipoComiteNombre) ?>
                </h4>
                <p class="mb-0 opacity-75">
                    <?= esc($cliente['razon_social']) ?> - Periodo <?= esc($proceso['anio']) ?>
                </p>
            </div>
            <div class="col-md-4 text-end">
                <a href="<?= base_url("comites-elecciones/proceso/{$proceso['id_proceso']}/recomposicion/nueva") ?>"
                   class="btn btn-light">
                    <i class="fas fa-plus me-1"></i> Nueva Recomposicion
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

    <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i>
        <?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Estadisticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <h3 class="mb-0"><?= count($recomposiciones) ?></h3>
                <small class="text-muted">Total Recomposiciones</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 class="mb-0"><?= count(array_filter($recomposiciones, fn($r) => $r['estado'] === 'firmado')) ?></h3>
                <small class="text-muted">Firmadas</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="fas fa-clock"></i>
                </div>
                <h3 class="mb-0"><?= count(array_filter($recomposiciones, fn($r) => in_array($r['estado'], ['borrador', 'pendiente_firmas']))) ?></h3>
                <small class="text-muted">Pendientes</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="mb-0"><?= count(array_filter($recomposiciones, fn($r) => $r['estado'] !== 'cancelado')) ?></h3>
                <small class="text-muted">Cambios Efectivos</small>
            </div>
        </div>
    </div>

    <!-- Lista de recomposiciones -->
    <?php if (empty($recomposiciones)): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-exchange-alt fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No hay recomposiciones registradas</h5>
            <p class="text-muted">
                El comite mantiene su conformacion original desde la eleccion.
            </p>
            <a href="<?= base_url("comites-elecciones/proceso/{$proceso['id_proceso']}/recomposicion/nueva") ?>"
               class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Registrar primera recomposicion
            </a>
        </div>
    </div>
    <?php else: ?>

    <?php foreach ($recomposiciones as $r): ?>
    <?php
        $nombreSaliente = trim(($r['saliente_nombres'] ?? '') . ' ' . ($r['saliente_apellidos'] ?? ''));
        $nombreEntrante = $r['id_candidato_entrante']
            ? trim(($r['entrante_nombres_db'] ?? '') . ' ' . ($r['entrante_apellidos_db'] ?? ''))
            : trim(($r['entrante_nombres'] ?? '') . ' ' . ($r['entrante_apellidos'] ?? ''));
        $docEntrante = $r['id_candidato_entrante']
            ? ($r['entrante_documento_db'] ?? '')
            : ($r['entrante_documento'] ?? '');
    ?>
    <div class="card card-recomposicion <?= $r['estado'] ?>">
        <div class="card-body">
            <div class="row align-items-center">
                <!-- Numero y fecha -->
                <div class="col-md-2">
                    <div class="text-center">
                        <span class="badge bg-dark fs-6 mb-2">
                            #<?= $r['numero_recomposicion'] ?>
                        </span>
                        <br>
                        <small class="text-muted">
                            <?= date('d/m/Y', strtotime($r['fecha_recomposicion'])) ?>
                        </small>
                    </div>
                </div>

                <!-- Miembro saliente -->
                <div class="col-md-3">
                    <div class="bg-danger bg-opacity-10 rounded p-2">
                        <small class="text-danger fw-bold">
                            <i class="fas fa-user-minus me-1"></i> SALE
                        </small>
                        <br>
                        <strong><?= esc($nombreSaliente) ?></strong>
                        <br>
                        <small class="text-muted">C.C. <?= esc($r['saliente_documento'] ?? 'N/A') ?></small>
                    </div>
                </div>

                <!-- Flecha -->
                <div class="col-md-1 text-center">
                    <i class="fas fa-arrow-right arrow-reemplazo"></i>
                </div>

                <!-- Miembro entrante -->
                <div class="col-md-3">
                    <div class="bg-success bg-opacity-10 rounded p-2">
                        <small class="text-success fw-bold">
                            <i class="fas fa-user-plus me-1"></i> INGRESA
                        </small>
                        <br>
                        <strong><?= esc($nombreEntrante) ?></strong>
                        <br>
                        <small class="text-muted">C.C. <?= esc($docEntrante) ?></small>
                    </div>
                </div>

                <!-- Motivo y estado -->
                <div class="col-md-2 text-center">
                    <span class="badge bg-secondary badge-motivo mb-2">
                        <?= esc($motivosLabels[$r['motivo_salida']] ?? $r['motivo_salida']) ?>
                    </span>
                    <br>
                    <?php
                    $estadoBadge = match($r['estado']) {
                        'firmado' => 'bg-success',
                        'pendiente_firmas' => 'bg-info',
                        'borrador' => 'bg-warning text-dark',
                        'cancelado' => 'bg-danger',
                        default => 'bg-secondary'
                    };
                    $estadoLabel = match($r['estado']) {
                        'firmado' => 'Firmado',
                        'pendiente_firmas' => 'Pendiente firmas',
                        'borrador' => 'Borrador',
                        'cancelado' => 'Cancelado',
                        default => $r['estado']
                    };
                    ?>
                    <span class="badge <?= $estadoBadge ?>">
                        <?= $estadoLabel ?>
                    </span>
                </div>

                <!-- Acciones -->
                <div class="col-md-1 text-end">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item"
                                   href="<?= base_url("comites-elecciones/proceso/{$proceso['id_proceso']}/recomposicion/{$r['id_recomposicion']}") ?>">
                                    <i class="fas fa-eye me-2"></i> Ver detalle
                                </a>
                            </li>
                            <?php if ($r['estado'] !== 'cancelado'): ?>
                            <li>
                                <a class="dropdown-item"
                                   href="<?= base_url("comites-elecciones/proceso/{$proceso['id_proceso']}/recomposicion/{$r['id_recomposicion']}/acta-pdf") ?>"
                                   target="_blank">
                                    <i class="fas fa-file-pdf me-2 text-danger"></i> Ver Acta PDF
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php endif; ?>

    <!-- Boton volver -->
    <div class="mt-4">
        <a href="<?= base_url("comites-elecciones/{$cliente['id_cliente']}/proceso/{$proceso['id_proceso']}") ?>"
           class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver al Proceso
        </a>
    </div>
</div>

<?= $this->endSection() ?>
