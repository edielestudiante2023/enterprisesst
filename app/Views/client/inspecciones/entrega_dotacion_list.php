<div class="container-fluid px-3">
    <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
        <h6 class="mb-0">Entregas de Dotación</h6>
    </div>

    <?php if (empty($entregas)): ?>
        <div class="text-center text-muted py-5">
            <i class="fas fa-helmet-safety fa-3x mb-3" style="opacity:0.3;"></i>
            <p>No hay entregas de dotación registradas.</p>
        </div>
    <?php else: ?>
        <?php foreach ($entregas as $e): ?>
        <div class="card mb-2">
            <div class="card-body py-3 px-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div style="flex:1;">
                        <strong><?= esc($e['tipo_dotacion'] ?? 'Entrega de Dotación') ?></strong>
                        <div class="text-muted" style="font-size:13px;">
                            <i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($e['fecha_entrega'])) ?>
                            <?php if (!empty($e['responsable_entrega'])): ?>
                                &middot; <i class="fas fa-user-tie"></i> <?= esc($e['responsable_entrega']) ?>
                            <?php endif; ?>
                        </div>
                        <div style="font-size:13px; color:#666; margin-top:2px;">
                            <i class="fas fa-users"></i>
                            <?= (int)($e['total_firmados'] ?? 0) ?> / <?= (int)($e['total_asistentes'] ?? 0) ?> firmados
                        </div>
                    </div>
                    <span class="badge bg-<?= $e['estado'] === 'completo' ? 'success' : 'warning text-dark' ?>">
                        <?= $e['estado'] === 'completo' ? 'Completo' : 'Borrador' ?>
                    </span>
                </div>
                <div class="mt-2">
                    <a href="<?= site_url('client/inspecciones/entrega-dotacion/' . $e['id']) ?>" class="btn btn-sm btn-outline-dark">
                        <i class="fas fa-eye"></i> Ver detalle
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
