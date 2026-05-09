<div class="container-fluid px-3">
    <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
        <h6 class="mb-0">Entregas de Dotación</h6>
        <a href="<?= site_url('inspecciones/entrega-dotacion/create') ?>" class="btn btn-sm btn-pwa-primary" style="width:auto; padding:8px 16px;">
            <i class="fas fa-plus"></i> Nueva
        </a>
    </div>

    <?php if (empty($entregas)): ?>
        <div class="text-center text-muted py-5">
            <i class="fas fa-helmet-safety fa-3x mb-3" style="opacity:0.3;"></i>
            <p>No hay entregas de dotación registradas</p>
            <a href="<?= site_url('inspecciones/entrega-dotacion/create') ?>" class="btn btn-pwa-primary" style="width:auto; padding:8px 24px;">
                Registrar primera entrega
            </a>
        </div>
    <?php else: ?>
        <?php foreach ($entregas as $e): ?>
        <div class="card card-inspeccion <?= esc($e['estado']) ?>">
            <div class="card-body py-3 px-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div style="flex:1;">
                        <strong><?= esc($e['tipo_dotacion'] ?? 'Entrega de Dotación') ?></strong>
                        <div class="text-muted" style="font-size:13px;">
                            <i class="fas fa-building"></i> <?= esc($e['nombre_cliente'] ?? 'Sin cliente') ?>
                        </div>
                        <div class="text-muted" style="font-size:13px;">
                            <i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($e['fecha_entrega'])) ?>
                            <?php if (!empty($e['responsable_entrega'])): ?>
                                &middot; <i class="fas fa-user-tie"></i> <?= esc($e['responsable_entrega']) ?>
                            <?php endif; ?>
                        </div>
                        <div style="font-size:13px; color:#666; margin-top:2px;">
                            <i class="fas fa-users"></i>
                            <?= (int)($e['total_firmados'] ?? 0) ?> / <?= (int)($e['total_asistentes'] ?? 0) ?> firmas
                        </div>
                    </div>
                    <span class="badge badge-<?= esc($e['estado']) ?>">
                        <?= $e['estado'] === 'completo' ? 'Completo' : 'Borrador' ?>
                    </span>
                </div>
                <div class="mt-2 d-flex gap-2 flex-wrap">
                    <a href="<?= site_url('inspecciones/entrega-dotacion/view/' . $e['id']) ?>" class="btn btn-sm btn-outline-dark">
                        <i class="fas fa-eye"></i> Ver
                    </a>
                    <?php if ($e['estado'] === 'borrador'): ?>
                        <a href="<?= site_url('inspecciones/entrega-dotacion/edit/' . $e['id']) ?>" class="btn btn-sm btn-outline-dark">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
