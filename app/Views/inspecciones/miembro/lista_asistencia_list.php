<div class="container-fluid px-3">
    <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
        <div>
            <h6 class="mb-0">Listas de Asistencia</h6>
            <small class="text-muted"><?= esc($cliente['nombre_cliente'] ?? '') ?></small>
        </div>
        <a href="<?= site_url('miembro/lista-asistencia/create') ?>" class="btn btn-sm btn-pwa-primary" style="width:auto; padding: 8px 16px;">
            <i class="fas fa-plus"></i> Nueva
        </a>
    </div>

    <?php if (empty($listas)): ?>
        <div class="text-center text-muted py-5">
            <i class="fas fa-clipboard-list fa-3x mb-3" style="opacity:0.3;"></i>
            <p>No hay listas de asistencia registradas</p>
            <a href="<?= site_url('miembro/lista-asistencia/create') ?>" class="btn btn-pwa-primary" style="width:auto; padding: 8px 24px;">
                Registrar primera lista
            </a>
        </div>
    <?php else: ?>
        <?php foreach ($listas as $l): ?>
            <div class="card card-inspeccion <?= esc($l['estado']) ?>">
                <div class="card-body py-3 px-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div style="flex:1;">
                            <strong><?= esc($l['motivo']) ?></strong>
                            <div class="text-muted" style="font-size:13px;">
                                <i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($l['fecha_actividad'])) ?>
                                <?php if (!empty($l['convocada_por'])): ?>
                                    &middot;
                                    <i class="fas fa-user-tie"></i> <?= esc($l['convocada_por']) ?>
                                <?php endif; ?>
                            </div>
                            <div class="text-muted" style="font-size:13px;">
                                <i class="fas fa-user"></i> <?= esc($l['nombre_creador'] ?? '') ?>
                            </div>
                            <div style="font-size:13px; color:#666; margin-top:2px;">
                                <i class="fas fa-users"></i>
                                <?= (int)($l['total_firmados'] ?? 0) ?> / <?= (int)($l['total_asistentes'] ?? 0) ?> firmas
                            </div>
                        </div>
                        <span class="badge badge-<?= esc($l['estado']) ?>">
                            <?= $l['estado'] === 'completo' ? 'Completo' : 'Borrador' ?>
                        </span>
                    </div>
                    <div class="mt-2 d-flex gap-2 flex-wrap">
                        <?php if ($l['estado'] === 'borrador' && $l['creado_por_tipo'] === 'miembro' && (int)$l['id_miembro'] === (int)$miembro['id_miembro']): ?>
                            <a href="<?= site_url('miembro/lista-asistencia/edit/' . $l['id']) ?>" class="btn btn-sm btn-outline-dark">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                        <?php endif; ?>
                        <a href="<?= site_url('miembro/lista-asistencia/view/' . $l['id']) ?>" class="btn btn-sm btn-outline-dark">
                            <i class="fas fa-eye"></i> Ver
                        </a>
                        <?php if ($l['estado'] === 'completo'): ?>
                            <a href="<?= site_url('miembro/lista-asistencia/pdf/' . $l['id']) ?>" class="btn btn-sm btn-outline-success" target="_blank">
                                <i class="fas fa-file-pdf"></i> PDF
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
