<?php
$tipoLabel = $tiposReunion[$inspeccion['tipo_reunion'] ?? ''] ?? $inspeccion['tipo_reunion'] ?? '';
?>
<div class="container-fluid px-3 mt-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Registro de Asistencia</h5>
        <a href="/client/inspecciones/registro-asistencia" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <!-- Datos generales -->
    <div class="card mb-3">
        <div class="card-header"><strong>Datos Generales</strong></div>
        <div class="card-body">
            <table class="table table-sm mb-0">
                <tr><td class="text-muted" style="width:35%;">Fecha sesion</td><td><?= date('d/m/Y', strtotime($inspeccion['fecha_sesion'])) ?></td></tr>
                <tr><td class="text-muted">Consultor</td><td><?= esc($consultor['nombre_consultor'] ?? '') ?></td></tr>
                <?php if (!empty($inspeccion['tema'])): ?>
                <tr><td class="text-muted">Tema</td><td><?= esc($inspeccion['tema']) ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($tipoLabel)): ?>
                <tr><td class="text-muted">Tipo</td><td><?= esc($tipoLabel) ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($inspeccion['lugar'])): ?>
                <tr><td class="text-muted">Lugar</td><td><?= esc($inspeccion['lugar']) ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($inspeccion['objetivo'])): ?>
                <tr><td class="text-muted">Objetivo</td><td><?= nl2br(esc($inspeccion['objetivo'])) ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($inspeccion['capacitador'])): ?>
                <tr><td class="text-muted">Capacitador</td><td><?= esc($inspeccion['capacitador']) ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($inspeccion['material'])): ?>
                <tr><td class="text-muted">Material</td><td><?= esc($inspeccion['material']) ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($inspeccion['tiempo_horas'])): ?>
                <tr><td class="text-muted">Tiempo (horas)</td><td><?= esc($inspeccion['tiempo_horas']) ?></td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <!-- Asistentes -->
    <div class="card mb-3">
        <div class="card-header"><strong>Asistentes (<?= count($asistentes) ?>)</strong></div>
        <div class="card-body p-0">
            <?php if (!empty($asistentes)): ?>
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0">
                    <thead>
                        <tr><th>#</th><th>Nombre</th><th>Cedula</th><th>Cargo</th><th>Firma</th></tr>
                    </thead>
                    <tbody>
                    <?php $num = 1; foreach ($asistentes as $a): ?>
                        <tr>
                            <td><?= $num++ ?></td>
                            <td><?= esc($a['nombre']) ?></td>
                            <td><?= esc($a['cedula']) ?></td>
                            <td><?= esc($a['cargo']) ?></td>
                            <td>
                                <?php if (!empty($a['firma'])): ?>
                                <img src="<?= base_url($a['firma']) ?>" style="max-width:80px; max-height:40px; border:1px solid #ddd;">
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-muted p-3 mb-0">No hay asistentes registrados.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Observaciones -->
    <?php if (!empty($inspeccion['observaciones'])): ?>
    <div class="card mb-3">
        <div class="card-header"><strong>Observaciones</strong></div>
        <div class="card-body">
            <p class="mb-0"><?= nl2br(esc($inspeccion['observaciones'])) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- PDF -->
    <?php if (!empty($inspeccion['ruta_pdf_asistencia'])): ?>
    <a href="<?= base_url('/inspecciones/registro-asistencia/pdf/' . $inspeccion['id']) ?>" class="btn btn-primary" target="_blank">
        <i class="fas fa-file-pdf"></i> Ver PDF
    </a>
    <?php endif; ?>
</div>
