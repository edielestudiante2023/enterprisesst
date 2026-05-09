<div class="container-fluid px-3">
    <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
        <h6 class="mb-0">Inspecciones de EPP</h6>
    </div>

    <?php if (empty($inspecciones)): ?>
        <div class="text-center text-muted py-5">
            <i class="fas fa-helmet-safety fa-3x mb-3" style="opacity:0.3;"></i>
            <p>No hay inspecciones de EPP registradas.</p>
        </div>
    <?php else: ?>
        <?php foreach ($inspecciones as $insp): ?>
        <div class="card mb-2">
            <div class="card-body py-3 px-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div style="flex:1;">
                        <strong><?= date('d/m/Y', strtotime($insp['fecha_inspeccion'])) ?></strong>
                        <div class="text-muted" style="font-size:13px;">
                            <i class="fas fa-user"></i> <?= esc($insp['nombre_consultor'] ?? '-') ?>
                        </div>
                        <div style="font-size:13px; color:#666; margin-top:2px;">
                            <i class="fas fa-exclamation-triangle"></i>
                            <?= (int)($insp['total_hallazgos'] ?? 0) ?> hallazgo<?= ($insp['total_hallazgos'] ?? 0) != 1 ? 's' : '' ?>
                        </div>
                    </div>
                    <span class="badge bg-<?= $insp['estado'] === 'completo' ? 'success' : 'warning text-dark' ?>">
                        <?= $insp['estado'] === 'completo' ? 'Completo' : 'Borrador' ?>
                    </span>
                </div>
                <div class="mt-2">
                    <a href="<?= site_url('client/inspecciones/inspeccion-epp/' . $insp['id']) ?>" class="btn btn-sm btn-outline-dark">
                        <i class="fas fa-eye"></i> Ver detalle
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
