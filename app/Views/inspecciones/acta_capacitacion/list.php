<div class="container-fluid px-3">
    <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
        <h6 class="mb-0">Actas de Capacitación</h6>
        <a href="<?= site_url('inspecciones/acta-capacitacion/create') ?>" class="btn btn-sm btn-pwa-primary" style="width:auto; padding:8px 16px;">
            <i class="fas fa-plus"></i> Nueva
        </a>
    </div>

    <?php if (empty($actas)): ?>
        <div class="text-center text-muted py-5">
            <i class="fas fa-graduation-cap fa-3x mb-3" style="opacity:0.3;"></i>
            <p>No hay actas de capacitación registradas</p>
            <a href="<?= site_url('inspecciones/acta-capacitacion/create') ?>" class="btn btn-pwa-primary" style="width:auto; padding:8px 24px;">
                Registrar primera capacitación
            </a>
        </div>
    <?php else: ?>
        <?php foreach ($actas as $a): ?>
        <div class="card card-inspeccion <?= esc($a['estado']) ?>">
            <div class="card-body py-3 px-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div style="flex:1;">
                        <strong><?= esc($a['tema']) ?></strong>
                        <div class="text-muted" style="font-size:13px;">
                            <i class="fas fa-building"></i> <?= esc($a['nombre_cliente'] ?? 'Sin cliente') ?>
                        </div>
                        <div class="text-muted" style="font-size:13px;">
                            <i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($a['fecha_capacitacion'])) ?>
                            &middot;
                            <i class="fas fa-chalkboard-teacher"></i> <?= esc($a['dictada_por']) ?>
                        </div>
                        <div style="font-size:13px; color:#666; margin-top:2px;">
                            <i class="fas fa-users"></i>
                            <?= (int)($a['total_firmados'] ?? 0) ?> / <?= (int)($a['total_asistentes'] ?? 0) ?> firmas
                        </div>
                    </div>
                    <span class="badge badge-<?= esc($a['estado']) ?>">
                        <?= $a['estado'] === 'completo' ? 'Completo' : 'Borrador' ?>
                    </span>
                </div>
                <div class="mt-2 d-flex gap-2 flex-wrap">
                    <a href="<?= site_url('inspecciones/acta-capacitacion/view/' . $a['id']) ?>" class="btn btn-sm btn-outline-dark">
                        <i class="fas fa-eye"></i> Ver
                    </a>
                    <?php if ($a['estado'] === 'borrador'): ?>
                        <a href="<?= site_url('inspecciones/acta-capacitacion/edit/' . $a['id']) ?>" class="btn btn-sm btn-outline-dark">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                    <?php endif; ?>
                    <?php if ($a['estado'] === 'completo'): ?>
                        <a href="<?= site_url('inspecciones/acta-capacitacion/pdf/' . $a['id']) ?>" class="btn btn-sm btn-outline-success" target="_blank">
                            <i class="fas fa-file-pdf"></i> PDF
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
