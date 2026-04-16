<div class="container-fluid px-3">
    <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
        <div>
            <h6 class="mb-0">Inspecciones Locativas</h6>
            <small class="text-muted"><?= esc($cliente['nombre_cliente'] ?? '') ?></small>
        </div>
        <a href="<?= site_url('miembro/inspecciones/locativa/create') ?>" class="btn btn-sm btn-pwa-primary" style="width:auto; padding: 8px 16px;">
            <i class="fas fa-plus"></i> Nueva
        </a>
    </div>

    <?php if (empty($inspecciones)): ?>
        <div class="text-center text-muted py-5">
            <i class="fas fa-building fa-3x mb-3" style="opacity:0.3;"></i>
            <p>No hay inspecciones locativas aun</p>
            <a href="<?= site_url('miembro/inspecciones/locativa/create') ?>" class="btn btn-pwa-primary" style="width:auto; padding: 8px 24px;">
                Crear primera inspeccion
            </a>
        </div>
    <?php else: ?>
        <div id="inspeccionesList">
        <?php foreach ($inspecciones as $insp): ?>
            <div class="card card-inspeccion <?= esc($insp['estado']) ?>">
                <div class="card-body py-3 px-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div style="flex:1;">
                            <strong><?= date('d/m/Y', strtotime($insp['fecha_inspeccion'])) ?></strong>
                            <div class="text-muted" style="font-size: 13px;">
                                <i class="fas fa-user"></i> <?= esc($insp['nombre_creador'] ?? '') ?>
                            </div>
                            <div style="font-size: 13px; color: #666; margin-top: 2px;">
                                <i class="fas fa-exclamation-triangle"></i>
                                <?= (int)($insp['total_hallazgos'] ?? 0) ?> hallazgo<?= ($insp['total_hallazgos'] ?? 0) != 1 ? 's' : '' ?>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="badge badge-<?= esc($insp['estado']) ?>">
                                <?= $insp['estado'] === 'completo' ? 'Completo' : 'Borrador' ?>
                            </span>
                        </div>
                    </div>
                    <div class="mt-2 d-flex gap-2 flex-wrap">
                        <?php if ($insp['estado'] === 'borrador' && $insp['creado_por_tipo'] === 'miembro' && (int)$insp['id_miembro'] === (int)$miembro['id_miembro']): ?>
                        <a href="<?= site_url('miembro/inspecciones/locativa/edit/' . $insp['id']) ?>" class="btn btn-sm btn-outline-dark">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <?php endif; ?>
                        <?php if ($insp['estado'] === 'completo'): ?>
                            <a href="<?= site_url('miembro/inspecciones/locativa/view/' . $insp['id']) ?>" class="btn btn-sm btn-outline-dark">
                                <i class="fas fa-eye"></i> Ver
                            </a>
                            <a href="<?= site_url('miembro/inspecciones/locativa/pdf/' . $insp['id']) ?>" class="btn btn-sm btn-outline-success" target="_blank">
                                <i class="fas fa-file-pdf"></i> PDF
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
