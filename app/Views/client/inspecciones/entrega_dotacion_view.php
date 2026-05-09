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
            <h6 class="card-title" style="font-size:14px; color:#999;">OPERARIOS Y ELEMENTOS (<?= count($asistentes) ?>)</h6>
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

                    <?php if (!empty($a['items'])): ?>
                    <div class="mt-1">
                        <table class="table table-sm mb-0" style="font-size:12px;">
                            <thead style="background:#f8f9fa;">
                                <tr>
                                    <th>Elemento</th>
                                    <th style="width:70px;">Cantidad</th>
                                    <th style="width:60px;">Talla</th>
                                    <th style="width:90px;">Marca</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($a['items'] as $it): ?>
                                <tr>
                                    <td><?= esc($it['descripcion']) ?></td>
                                    <td><?= esc($it['cantidad']) ?></td>
                                    <td><?= esc($it['talla'] ?? '-') ?></td>
                                    <td><?= esc($it['marca'] ?? '-') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
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
