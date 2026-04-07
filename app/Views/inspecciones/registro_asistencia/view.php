<?php
$tipoLabel = $tiposReunion[$inspeccion['tipo_reunion'] ?? ''] ?? $inspeccion['tipo_reunion'] ?? '';
?>
<div class="container-fluid px-3">
    <div class="mt-2 mb-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Registro de Asistencia</h6>
        <span class="badge badge-<?= esc($inspeccion['estado']) ?>">
            <?= $inspeccion['estado'] === 'completo' ? 'Completo' : 'Borrador' ?>
        </span>
    </div>

    <!-- Datos generales -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">DATOS GENERALES</h6>
            <table class="table table-sm mb-0" style="font-size:14px;">
                <tr><td class="text-muted" style="width:40%;">Cliente</td><td><strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong></td></tr>
                <tr><td class="text-muted">Fecha sesion</td><td><?= date('d/m/Y', strtotime($inspeccion['fecha_sesion'])) ?></td></tr>
                <tr><td class="text-muted">Consultor</td><td><?= esc($consultor['nombre_consultor'] ?? '') ?></td></tr>
            </table>
        </div>
    </div>

    <!-- Info sesion -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">INFORMACION DE LA SESION</h6>
            <table class="table table-sm mb-0" style="font-size:14px;">
                <?php if (!empty($inspeccion['tema'])): ?>
                <tr><td class="text-muted" style="width:40%;">Tema</td><td><?= esc($inspeccion['tema']) ?></td></tr>
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
                <?php if (!empty($tipoLabel)): ?>
                <tr><td class="text-muted">Tipo de reunion</td><td><?= esc($tipoLabel) ?></td></tr>
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
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">ASISTENTES (<?= count($asistentes) ?>)</h6>
            <?php if (!empty($asistentes)): ?>
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0" style="font-size:12px;">
                    <thead class="table-light">
                        <tr>
                            <th style="width:5%;">#</th>
                            <th>Nombre</th>
                            <th>Cedula</th>
                            <th>Cargo</th>
                            <th style="width:15%;">Firma</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $num = 1; foreach ($asistentes as $a): ?>
                        <tr>
                            <td class="text-center"><?= $num++ ?></td>
                            <td><?= esc($a['nombre']) ?></td>
                            <td><?= esc($a['cedula']) ?></td>
                            <td><?= esc($a['cargo']) ?></td>
                            <td class="text-center">
                                <?php if (!empty($a['firma'])): ?>
                                <img src="<?= base_url($a['firma']) ?>" style="max-width:120px; max-height:60px; border:1px solid #ddd; border-radius:4px;">
                                <?php else: ?>
                                <span class="text-muted" style="font-size:10px;">Sin firma</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-muted mb-0" style="font-size:13px;">No hay asistentes registrados</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Observaciones -->
    <?php if (!empty($inspeccion['observaciones'])): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">OBSERVACIONES</h6>
            <p style="font-size:14px; margin:0;"><?= nl2br(esc($inspeccion['observaciones'])) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Acciones -->
    <div class="mb-4 d-flex gap-2 flex-wrap">
        <?php if ($inspeccion['estado'] === 'completo' && !empty($inspeccion['ruta_pdf_asistencia'])): ?>
        <a href="<?= base_url('/inspecciones/registro-asistencia/pdf/') ?><?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-primary" target="_blank">
            <i class="fas fa-file-pdf"></i> Ver PDF
        </a>
        <?php endif; ?>
    <?php if ($inspeccion['estado'] === 'completo'): ?>
    <a href="<?= base_url('/inspecciones/registro-asistencia/regenerar/') ?><?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline" onclick="return confirm('Regenerar el PDF con la plantilla actual?')">
        <i class="fas fa-sync-alt me-2"></i>Regenerar PDF
    </a>
    <a href="<?= base_url('/inspecciones/registro-asistencia/enviar-email/') ?><?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline" onclick="return confirm('Enviar el PDF por email al cliente, consultor y consultor externo?')">
        <i class="fas fa-envelope me-2"></i>Enviar por Email
    </a>
    <?php endif; ?>
        <?php if ($inspeccion['estado'] !== 'completo'): ?>
        <a href="<?= base_url('/inspecciones/registro-asistencia/firmas/') ?><?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline">
            <i class="fas fa-signature"></i> Ir a Firmas
        </a>
        <a href="<?= base_url('/inspecciones/registro-asistencia/edit/') ?><?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline">
            <i class="fas fa-edit"></i> Editar
        </a>
        <?php endif; ?>
    </div>
</div>
