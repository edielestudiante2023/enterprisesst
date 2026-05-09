<div class="container-fluid px-3">
    <div class="mt-2 mb-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Entrega de Dotación</h6>
        <span class="badge bg-<?= $entrega['estado'] === 'completo' ? 'success' : 'warning text-dark' ?>">
            <?= $entrega['estado'] === 'completo' ? 'Completo' : 'Borrador' ?>
        </span>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">DATOS DE LA ENTREGA</h6>
            <table class="table table-sm mb-0" style="font-size:14px;">
                <tr><td class="text-muted" style="width:40%;">Cliente</td><td><strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong></td></tr>
                <tr><td class="text-muted">Fecha</td><td><?= date('d/m/Y', strtotime($entrega['fecha_entrega'])) ?></td></tr>
                <?php if (!empty($entrega['hora'])): ?>
                <tr><td class="text-muted">Hora</td><td><?= date('g:i A', strtotime($entrega['hora'])) ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($entrega['lugar'])): ?>
                <tr><td class="text-muted">Lugar</td><td><?= esc($entrega['lugar']) ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($entrega['responsable_entrega'])): ?>
                <tr><td class="text-muted">Responsable de entrega</td><td><?= esc($entrega['responsable_entrega']) ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($entrega['tipo_dotacion'])): ?>
                <tr><td class="text-muted">Tipo de dotación</td><td><?= esc($entrega['tipo_dotacion']) ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($consultor)): ?>
                <tr><td class="text-muted">Consultor</td><td><?= esc($consultor['nombre_consultor'] ?? '') ?></td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">ELEMENTOS ENTREGADOS (<?= count($items ?? []) ?>)</h6>
            <?php if (empty($items)): ?>
                <p class="text-muted" style="font-size:13px;">Sin elementos registrados.</p>
            <?php else: ?>
                <table class="table table-sm mb-0" style="font-size:13px;">
                    <thead style="background:#f8f9fa;">
                        <tr>
                            <th>Elemento</th>
                            <th style="width:80px;">Cantidad</th>
                            <th style="width:120px;">Marca</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $it): ?>
                        <tr>
                            <td><?= esc($it['descripcion']) ?></td>
                            <td><?= esc($it['cantidad']) ?></td>
                            <td><?= esc($it['marca'] ?? '-') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">OPERARIOS (<?= count($asistentes) ?>)</h6>
            <?php if (empty($asistentes)): ?>
                <p class="text-muted" style="font-size:13px;">Sin operarios registrados.</p>
            <?php else: ?>
                <?php foreach ($asistentes as $i => $a): ?>
                <div class="mb-3 pb-2" style="border-bottom:1px solid #eee;">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong style="font-size:14px;"><?= ($i + 1) ?>. <?= esc($a['nombre_completo']) ?></strong>
                            <div class="text-muted" style="font-size:12px;">
                                <?php if (!empty($a['numero_documento'])): ?><?= esc($a['tipo_documento']) ?> <?= esc($a['numero_documento']) ?> &middot; <?php endif; ?>
                                <?= esc($a['cargo'] ?? '') ?>
                            </div>
                        </div>
                        <span style="font-size:11px;">
                            <?php if (!empty($a['firma_path'])): ?>
                                <span class="badge bg-success"><i class="fas fa-check"></i> Firmado</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Pendiente</span>
                            <?php endif; ?>
                        </span>
                    </div>

                    <?php if (!empty($a['tallas_map'])): ?>
                    <div class="mt-1">
                        <small style="font-size:11px; color:#374151; font-weight:600;"><i class="fas fa-ruler"></i> Tallas:</small>
                        <table class="table table-sm mb-0" style="font-size:12px;">
                            <tbody>
                                <?php foreach (($items ?? []) as $itGlobal): ?>
                                    <?php $talla = $a['tallas_map'][$itGlobal['id']] ?? ''; ?>
                                    <?php if ($talla !== ''): ?>
                                    <tr>
                                        <td><?= esc($itGlobal['descripcion']) ?></td>
                                        <td style="width:80px;"><strong><?= esc($talla) ?></strong></td>
                                    </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($a['recibido_buen_estado'])): ?>
                    <div class="mt-2" style="font-size:12px;">
                        <strong>¿Recibió en buen estado?</strong>
                        <?php if ($a['recibido_buen_estado'] === 'si'): ?>
                            <span class="badge bg-success">SÍ</span>
                        <?php else: ?>
                            <span class="badge bg-danger">NO</span>
                            <?php if (!empty($a['observaciones_recibido'])): ?>
                                <div class="text-muted mt-1"><i class="fas fa-comment-alt"></i> <?= esc($a['observaciones_recibido']) ?></div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($a['firma_path'])): ?>
                    <img src="<?= base_url($a['firma_path']) ?>" style="max-height:50px; margin-top:6px;">
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($entrega['observaciones'])): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">OBSERVACIONES</h6>
            <p style="font-size:14px; margin:0;"><?= nl2br(esc($entrega['observaciones'])) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <div class="mb-4">
        <a href="<?= site_url('client/inspecciones/entrega-dotacion') ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
</div>
