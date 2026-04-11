<div class="container-fluid px-3">
    <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
        <div>
            <h6 class="mb-0">Investigacion de Accidentes e Incidentes</h6>
            <small class="text-muted"><?= esc($cliente['nombre_cliente'] ?? '') ?></small>
        </div>
        <a href="/miembro/inspecciones/investigacion-accidente/create" class="btn btn-sm btn-pwa-primary" style="width:auto; padding: 8px 16px;">
            <i class="fas fa-plus"></i> Nueva
        </a>
    </div>

    <?php if (empty($investigaciones)): ?>
        <div class="text-center text-muted py-5">
            <i class="fas fa-search fa-3x mb-3" style="opacity:0.3;"></i>
            <p>No hay investigaciones registradas aun</p>
            <a href="/miembro/inspecciones/investigacion-accidente/create" class="btn btn-pwa-primary" style="width:auto; padding: 8px 24px;">
                Crear primera investigacion
            </a>
        </div>
    <?php else: ?>
        <div id="investigacionesList">
        <?php foreach ($investigaciones as $inv): ?>
            <div class="card card-inspeccion <?= esc($inv['estado']) ?>">
                <div class="card-body py-3 px-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div style="flex:1;">
                            <strong><?= date('d/m/Y', strtotime($inv['fecha_evento'])) ?></strong>
                            <div style="font-size: 13px; margin-top: 2px;">
                                <?php if (($inv['tipo_evento'] ?? '') === 'accidente'): ?>
                                    <span class="badge bg-danger">Accidente</span>
                                    <?php if (!empty($inv['gravedad'])): ?>
                                        <span class="text-muted" style="font-size:12px;">(<?= ucfirst(esc($inv['gravedad'])) ?>)</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Incidente</span>
                                <?php endif; ?>
                            </div>
                            <div class="text-muted" style="font-size: 13px;">
                                <i class="fas fa-user"></i> <?= esc($inv['nombre_trabajador'] ?? 'Sin trabajador') ?>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="badge badge-<?= esc($inv['estado']) ?>">
                                <?= $inv['estado'] === 'completo' ? 'Completo' : 'Borrador' ?>
                            </span>
                        </div>
                    </div>
                    <div class="mt-2 d-flex gap-2 flex-wrap">
                        <?php if ($inv['estado'] === 'borrador' && ($inv['creado_por_tipo'] ?? '') === 'miembro' && (int)($inv['id_miembro'] ?? 0) === (int)$miembro['id_miembro']): ?>
                        <a href="/miembro/inspecciones/investigacion-accidente/edit/<?= $inv['id'] ?>" class="btn btn-sm btn-outline-dark">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <?php endif; ?>
                        <?php if ($inv['estado'] === 'completo'): ?>
                            <a href="/miembro/inspecciones/investigacion-accidente/view/<?= $inv['id'] ?>" class="btn btn-sm btn-outline-dark">
                                <i class="fas fa-eye"></i> Ver
                            </a>
                            <a href="/miembro/inspecciones/investigacion-accidente/pdf/<?= $inv['id'] ?>" class="btn btn-sm btn-outline-success" target="_blank">
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
