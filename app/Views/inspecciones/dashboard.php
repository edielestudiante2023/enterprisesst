<div class="container-fluid px-3">
    <!-- Saludo -->
    <div class="mt-2 mb-3">
        <h5 class="mb-0">Hola, <?= esc($nombre) ?></h5>
        <small class="text-muted"><?= date('d \d\e F, Y') ?></small>
    </div>

    <!-- Pendientes: Actas -->
    <?php if (!empty($pendientes)): ?>
    <div class="section-title">Pendientes</div>
    <?php foreach ($pendientes as $doc): ?>
    <div class="card card-inspeccion <?= esc($doc['estado']) ?>">
        <div class="card-body py-3 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong>
                        <?php if ($doc['estado'] === 'borrador'): ?>
                            <i class="fas fa-edit text-warning"></i>
                        <?php else: ?>
                            <i class="fas fa-signature text-orange"></i>
                        <?php endif; ?>
                        Acta - <?= esc($doc['nombre_cliente'] ?? 'Sin cliente') ?>
                    </strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?= date('d/m/Y', strtotime($doc['fecha_visita'])) ?>
                        &middot;
                        <span class="badge badge-<?= esc($doc['estado']) ?>" style="font-size: 11px;">
                            <?= $doc['estado'] === 'borrador' ? 'Borrador' : 'Pend. Firma' ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="mt-2 d-flex gap-2 flex-wrap">
                <?php if ($doc['estado'] === 'borrador'): ?>
                    <a href="<?= site_url('inspecciones/acta-visita/edit/' . $doc['id']) ?>" class="btn btn-sm btn-outline-dark">
                        Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                <?php else: ?>
                    <a href="<?= site_url('inspecciones/acta-visita/firma/' . $doc['id']) ?>" class="btn btn-sm btn-outline-warning">
                        Ir a firmas <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                <?php endif; ?>
                <button type="button" class="btn btn-sm btn-outline-danger btn-delete-doc"
                    data-url="<?= site_url('inspecciones/acta-visita/delete/' . $doc['id']) ?>"
                    data-tipo="acta de visita"
                    data-nombre="<?= esc($doc['nombre_cliente'] ?? 'Sin cliente') ?>">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Pendientes Locativas -->
    <?php if (!empty($pendientesLocativas)): ?>
    <div class="section-title">Pendientes Locativas</div>
    <?php foreach ($pendientesLocativas as $doc): ?>
    <div class="card card-inspeccion borrador">
        <div class="card-body py-3 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong>
                        <i class="fas fa-edit text-warning"></i>
                        Locativa - <?= esc($doc['nombre_cliente'] ?? 'Sin cliente') ?>
                    </strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?= date('d/m/Y', strtotime($doc['fecha_inspeccion'])) ?>
                        &middot;
                        <span class="badge badge-borrador" style="font-size: 11px;">Borrador</span>
                    </div>
                </div>
            </div>
            <div class="mt-2 d-flex gap-2 flex-wrap">
                <a href="<?= site_url('inspecciones/inspeccion-locativa/edit/' . $doc['id']) ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger btn-delete-doc"
                    data-url="<?= site_url('inspecciones/inspeccion-locativa/delete/' . $doc['id']) ?>"
                    data-tipo="inspeccion locativa"
                    data-nombre="<?= esc($doc['nombre_cliente'] ?? 'Sin cliente') ?>">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Pendientes Extintores -->
    <?php if (!empty($pendientesExtintores)): ?>
    <div class="section-title">Pendientes Extintores</div>
    <?php foreach ($pendientesExtintores as $doc): ?>
    <div class="card card-inspeccion borrador">
        <div class="card-body py-3 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong>
                        <i class="fas fa-edit text-warning"></i>
                        Extintores - <?= esc($doc['nombre_cliente'] ?? 'Sin cliente') ?>
                    </strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?= date('d/m/Y', strtotime($doc['fecha_inspeccion'])) ?>
                        &middot;
                        <span class="badge badge-borrador" style="font-size: 11px;">Borrador</span>
                    </div>
                </div>
            </div>
            <div class="mt-2 d-flex gap-2 flex-wrap">
                <a href="<?= site_url('inspecciones/extintores/edit/' . $doc['id']) ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger btn-delete-doc"
                    data-url="<?= site_url('inspecciones/extintores/delete/' . $doc['id']) ?>"
                    data-tipo="inspeccion de extintores"
                    data-nombre="<?= esc($doc['nombre_cliente'] ?? 'Sin cliente') ?>">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Pendientes Botiquin -->
    <?php if (!empty($pendientesBotiquin)): ?>
    <div class="section-title">Pendientes Botiquin</div>
    <?php foreach ($pendientesBotiquin as $doc): ?>
    <div class="card card-inspeccion borrador">
        <div class="card-body py-3 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong>
                        <i class="fas fa-edit text-warning"></i>
                        Botiquin - <?= esc($doc['nombre_cliente'] ?? 'Sin cliente') ?>
                    </strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?= date('d/m/Y', strtotime($doc['fecha_inspeccion'])) ?>
                        &middot;
                        <span class="badge badge-borrador" style="font-size: 11px;">Borrador</span>
                    </div>
                </div>
            </div>
            <div class="mt-2 d-flex gap-2 flex-wrap">
                <a href="<?= site_url('inspecciones/botiquin/edit/' . $doc['id']) ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger btn-delete-doc"
                    data-url="<?= site_url('inspecciones/botiquin/delete/' . $doc['id']) ?>"
                    data-tipo="inspeccion de botiquin"
                    data-nombre="<?= esc($doc['nombre_cliente'] ?? 'Sin cliente') ?>">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Pendientes Senalizacion -->
    <?php if (!empty($pendientesSenalizacion)): ?>
    <div class="section-title">Pendientes Senalizacion</div>
    <?php foreach ($pendientesSenalizacion as $doc): ?>
    <div class="card card-inspeccion borrador">
        <div class="card-body py-3 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong>
                        <i class="fas fa-edit text-warning"></i>
                        Senalizacion - <?= esc($doc['nombre_cliente'] ?? 'Sin cliente') ?>
                    </strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?= date('d/m/Y', strtotime($doc['fecha_inspeccion'])) ?>
                        &middot;
                        <span class="badge badge-borrador" style="font-size: 11px;">Borrador</span>
                    </div>
                </div>
            </div>
            <div class="mt-2 d-flex gap-2 flex-wrap">
                <a href="<?= site_url('inspecciones/senalizacion/edit/' . $doc['id']) ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger btn-delete-doc"
                    data-url="<?= site_url('inspecciones/senalizacion/delete/' . $doc['id']) ?>"
                    data-tipo="inspeccion de senalizacion"
                    data-nombre="<?= esc($doc['nombre_cliente'] ?? 'Sin cliente') ?>">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Pendientes Registro Asistencia -->
    <?php if (!empty($pendientesAsistencia)): ?>
    <div class="section-title">Pendientes Asistencia</div>
    <?php foreach ($pendientesAsistencia as $doc): ?>
    <div class="card card-inspeccion borrador">
        <div class="card-body py-3 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong>
                        <i class="fas fa-edit text-warning"></i>
                        Asistencia - <?= esc($doc['nombre_cliente'] ?? 'Sin cliente') ?>
                    </strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?= date('d/m/Y', strtotime($doc['fecha_sesion'])) ?>
                        &middot;
                        <span class="badge badge-borrador" style="font-size: 11px;">Borrador</span>
                    </div>
                </div>
            </div>
            <div class="mt-2 d-flex gap-2 flex-wrap">
                <a href="<?= site_url('inspecciones/registro-asistencia/edit/' . $doc['id']) ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger btn-delete-doc"
                    data-url="<?= site_url('inspecciones/registro-asistencia/delete/' . $doc['id']) ?>"
                    data-tipo="registro de asistencia"
                    data-nombre="<?= esc($doc['nombre_cliente'] ?? 'Sin cliente') ?>">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Grid de inspecciones -->
    <div class="section-title">Inspecciones</div>
    <div class="grid-inspecciones mb-4">
        <a href="<?= site_url('inspecciones/acta-visita') ?>" class="card-tipo">
            <i class="fas fa-clipboard-list"></i>
            <div><strong>Actas de Visita</strong></div>
            <div class="count">(<?= $totalActas ?>)</div>
        </a>
        <a href="<?= site_url('inspecciones/inspeccion-locativa') ?>" class="card-tipo">
            <i class="fas fa-hard-hat"></i>
            <div><strong>Locativas</strong></div>
            <div class="count">(<?= $totalLocativas ?>)</div>
        </a>
        <a href="<?= site_url('inspecciones/extintores') ?>" class="card-tipo">
            <i class="fas fa-fire-extinguisher"></i>
            <div><strong>Extintores</strong></div>
            <div class="count">(<?= $totalExtintores ?>)</div>
        </a>
        <a href="<?= site_url('inspecciones/botiquin') ?>" class="card-tipo">
            <i class="fas fa-first-aid"></i>
            <div><strong>Botiquin</strong></div>
            <div class="count">(<?= $totalBotiquin ?>)</div>
        </a>
        <a href="<?= site_url('inspecciones/senalizacion') ?>" class="card-tipo">
            <i class="fas fa-sign"></i>
            <div><strong>Senalizacion</strong></div>
            <div class="count">(<?= $totalSenalizacion ?>)</div>
        </a>
        <a href="<?= site_url('inspecciones/registro-asistencia') ?>" class="card-tipo">
            <i class="fas fa-clipboard-list"></i>
            <div><strong>Asistencia</strong></div>
            <div class="count">(<?= $totalAsistencia ?>)</div>
        </a>
        <a href="<?= site_url('inspecciones/pausas-activas') ?>" class="card-tipo">
            <i class="fas fa-heartbeat"></i>
            <div><strong>Pausas Activas</strong></div>
            <div class="count">(<?= $totalPausas ?>)</div>
        </a>
        <a href="<?= site_url('inspecciones/investigacion-accidente') ?>" class="card-tipo">
            <i class="fas fa-exclamation-triangle"></i>
            <div><strong>Investigacion AT/IT</strong></div>
            <div class="count">(<?= $totalInvestigaciones ?>)</div>
        </a>
        <a href="<?= site_url('inspecciones/acta-capacitacion') ?>" class="card-tipo">
            <i class="fas fa-graduation-cap"></i>
            <div><strong>Actas Capacitacion</strong></div>
            <div class="count">(<?= $totalCapacitaciones ?? 0 ?>)</div>
        </a>
    </div>
</div>

<script>
(function () {
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.btn-delete-doc');
        if (!btn) return;
        e.preventDefault();

        var url    = btn.dataset.url    || '';
        var tipo   = btn.dataset.tipo   || 'documento';
        var nombre = btn.dataset.nombre || '';

        if (!url) return;

        Swal.fire({
            icon: 'warning',
            title: '¿Eliminar este documento?',
            html: 'Vas a eliminar la <strong>' + tipo + '</strong> de '
                + '<strong>' + nombre + '</strong>.<br><br>'
                + '<span style="color:#dc3545;font-size:13px;">Esta acción no se puede deshacer.</span>',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
            focusCancel: true,
        }).then(function (result) {
            if (!result.isConfirmed) return;
            Swal.fire({
                title: 'Eliminando...',
                allowOutsideClick: false,
                didOpen: function () { Swal.showLoading(); }
            });
            window.location.href = url;
        });
    });
})();
</script>
