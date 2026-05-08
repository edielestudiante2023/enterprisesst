<div class="container-fluid px-3">
    <div class="mt-2 mb-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Lista de Asistencia</h6>
        <span class="badge badge-<?= esc($lista['estado']) ?>">
            <?= $lista['estado'] === 'completo' ? 'Completo' : 'Borrador' ?>
        </span>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">DATOS DE LA CONVOCATORIA</h6>
            <table class="table table-sm mb-0" style="font-size:14px;">
                <tr><td class="text-muted" style="width:40%;">Cliente</td><td><strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong></td></tr>
                <tr><td class="text-muted">Motivo</td><td><?= esc($lista['motivo']) ?></td></tr>
                <tr><td class="text-muted">Fecha</td><td><?= date('d/m/Y', strtotime($lista['fecha_actividad'])) ?></td></tr>
                <?php if (!empty($lista['hora_inicio'])): ?>
                <tr><td class="text-muted">Hora</td><td><?= date('g:i A', strtotime($lista['hora_inicio'])) ?> – <?= !empty($lista['hora_fin']) ? date('g:i A', strtotime($lista['hora_fin'])) : '' ?></td></tr>
                <?php endif; ?>
                <tr><td class="text-muted">Modalidad</td><td><?= ucfirst($lista['modalidad']) ?></td></tr>
                <?php if (!empty($lista['convocada_por'])): ?>
                <tr><td class="text-muted">Convocada por</td><td><?= esc($lista['convocada_por']) ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($lista['lugar'])): ?>
                <tr><td class="text-muted">Lugar</td><td><?= esc($lista['lugar']) ?></td></tr>
                <?php endif; ?>
                <tr>
                    <td class="text-muted">Registrada por</td>
                    <td>
                        <?php if (!empty($realizadoPor)): ?>
                            <?= esc($realizadoPor) ?> <span class="badge bg-info" style="font-size:10px;">Comité</span>
                        <?php elseif (!empty($consultor)): ?>
                            <?= esc($consultor['nombre_consultor'] ?? '') ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
                <?php if (!empty($lista['enlace_grabacion'])): ?>
                <tr><td class="text-muted">Grabación</td><td><a href="<?= esc($lista['enlace_grabacion']) ?>" target="_blank" rel="noopener">Ver enlace</a></td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <?php if (!empty($lista['agenda'])): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">AGENDA / ORDEN DEL DÍA</h6>
            <p style="font-size:14px; margin:0;"><?= nl2br(esc($lista['agenda'])) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">ASISTENTES (<?= count($asistentes) ?>)</h6>
            <?php if (empty($asistentes)): ?>
                <p class="text-muted" style="font-size:13px;">Sin asistentes registrados.</p>
            <?php else: ?>
                <?php foreach ($asistentes as $i => $a): ?>
                <div class="mb-2 pb-2" style="border-bottom:1px solid #eee;">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong style="font-size:14px;"><?= ($i + 1) ?>. <?= esc($a['nombre_completo']) ?></strong>
                            <div class="text-muted" style="font-size:12px;">
                                <?php if (!empty($a['numero_documento'])): ?><?= esc($a['tipo_documento']) ?> <?= esc($a['numero_documento']) ?> &middot; <?php endif; ?>
                                <?= esc($a['cargo'] ?? '') ?>
                                <?= !empty($a['area_dependencia']) ? ' &middot; ' . esc($a['area_dependencia']) : '' ?>
                            </div>
                        </div>
                        <span style="font-size:11px;">
                            <?php if (!empty($a['firma_path'])): ?>
                                <span class="badge bg-success"><i class="fas fa-check"></i> Firmado <?= !empty($a['firmado_at']) ? date('d/m H:i', strtotime($a['firmado_at'])) : '' ?></span>
                            <?php elseif (!empty($a['token_firma'])): ?>
                                <span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> Pendiente</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Sin enlace</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <?php if (!empty($a['firma_path'])): ?>
                    <img src="<?= base_url($a['firma_path']) ?>" style="max-height:50px; margin-top:4px;">
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($lista['observaciones'])): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">OBSERVACIONES</h6>
            <p style="font-size:14px; margin:0;"><?= nl2br(esc($lista['observaciones'])) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <div class="mb-4 d-grid gap-2">
        <?php if ($lista['estado'] === 'completo' && !empty($lista['ruta_pdf'])): ?>
        <a href="<?= site_url('miembro/lista-asistencia/pdf/' . $lista['id']) ?>" class="btn btn-pwa btn-pwa-primary" target="_blank">
            <i class="fas fa-file-pdf"></i> Ver PDF
        </a>
        <?php endif; ?>
        <?php if ($lista['estado'] === 'borrador'): ?>
        <a href="<?= site_url('miembro/lista-asistencia/edit/' . $lista['id']) ?>" class="btn btn-pwa btn-pwa-primary">
            <i class="fas fa-edit"></i> Continuar editando
        </a>
        <?php endif; ?>
        <a href="<?= site_url('miembro/lista-asistencia') ?>" class="btn btn-pwa btn-pwa-outline">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
</div>
