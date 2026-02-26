<div class="container-fluid px-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
        <h5 class="mb-0">Acta de Visita</h5>
        <span class="badge bg-success" style="font-size:13px;">
            <i class="fas fa-check-circle"></i> <?= ucfirst($acta['estado']) ?>
        </span>
    </div>

    <!-- Toolbar -->
    <div class="d-flex gap-2 mb-3">
        <?php if (!empty($acta['ruta_pdf'])): ?>
        <a href="/inspecciones/acta-visita/pdf/<?= $acta['id'] ?>" target="_blank" class="btn btn-sm btn-pwa-primary" style="background:#bd9751; color:#fff; border:none; border-radius:6px;">
            <i class="fas fa-file-pdf"></i> Ver PDF
        </a>
        <?php endif; ?>
        <button type="button" class="btn btn-sm btn-outline-dark" id="btnCompartir">
            <i class="fas fa-share-alt"></i> Compartir
        </button>
        <a href="/inspecciones/acta-visita" class="btn btn-sm btn-outline-dark">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <!-- Datos Generales -->
    <div class="card mb-3">
        <div class="card-body p-3">
            <h6 class="mb-2" style="color:#bd9751;"><i class="fas fa-info-circle"></i> Datos Generales</h6>
            <table class="table table-sm table-borderless mb-0" style="font-size:14px;">
                <tr>
                    <td class="text-muted" style="width:40%;">Cliente</td>
                    <td class="fw-bold"><?= esc($cliente['nombre_cliente'] ?? '—') ?></td>
                </tr>
                <tr>
                    <td class="text-muted">Fecha</td>
                    <td><?= date('d/m/Y', strtotime($acta['fecha_visita'])) ?></td>
                </tr>
                <tr>
                    <td class="text-muted">Hora</td>
                    <td><?= date('h:i A', strtotime($acta['hora_visita'])) ?></td>
                </tr>
                <tr>
                    <td class="text-muted">Motivo</td>
                    <td><?= esc($acta['motivo']) ?></td>
                </tr>
                <tr>
                    <td class="text-muted">Modalidad</td>
                    <td><?= esc($acta['modalidad'] ?? 'Presencial') ?></td>
                </tr>
                <?php if (!empty($acta['ubicacion_gps'])): ?>
                <tr>
                    <td class="text-muted">Ubicacion</td>
                    <td><i class="fas fa-map-marker-alt text-success"></i> <?= esc($acta['ubicacion_gps']) ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <!-- Integrantes -->
    <?php if (!empty($integrantes)): ?>
    <div class="card mb-3">
        <div class="card-body p-3">
            <h6 class="mb-2" style="color:#bd9751;"><i class="fas fa-users"></i> Integrantes (<?= count($integrantes) ?>)</h6>
            <?php foreach ($integrantes as $integrante): ?>
            <div class="d-flex justify-content-between align-items-center py-1 border-bottom" style="font-size:14px;">
                <span><?= esc($integrante['nombre']) ?></span>
                <span class="badge bg-secondary" style="font-size:11px;"><?= esc($integrante['rol']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Temas -->
    <?php if (!empty($temas)): ?>
    <div class="card mb-3">
        <div class="card-body p-3">
            <h6 class="mb-2" style="color:#bd9751;"><i class="fas fa-list-ul"></i> Temas (<?= count($temas) ?>)</h6>
            <ol style="font-size:14px; padding-left:20px; margin-bottom:0;">
                <?php foreach ($temas as $tema): ?>
                <li class="mb-1"><?= esc($tema['descripcion']) ?></li>
                <?php endforeach; ?>
            </ol>
        </div>
    </div>
    <?php endif; ?>

    <!-- Observaciones -->
    <?php if (!empty($acta['observaciones'])): ?>
    <div class="card mb-3">
        <div class="card-body p-3">
            <h6 class="mb-2" style="color:#bd9751;"><i class="fas fa-comment-alt"></i> Observaciones</h6>
            <p class="mb-0" style="font-size:14px;"><?= nl2br(esc($acta['observaciones'])) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Cartera -->
    <?php if (!empty($acta['cartera'])): ?>
    <div class="card mb-3">
        <div class="card-body p-3">
            <h6 class="mb-2" style="color:#bd9751;"><i class="fas fa-wallet"></i> Cartera</h6>
            <p class="mb-0" style="font-size:14px;"><?= nl2br(esc($acta['cartera'])) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Compromisos -->
    <?php if (!empty($compromisos)): ?>
    <div class="card mb-3">
        <div class="card-body p-3">
            <h6 class="mb-2" style="color:#bd9751;"><i class="fas fa-tasks"></i> Compromisos (<?= count($compromisos) ?>)</h6>
            <?php foreach ($compromisos as $comp): ?>
            <div class="border-bottom py-2" style="font-size:14px;">
                <div class="fw-bold"><?= esc($comp['tarea_actividad']) ?></div>
                <div class="text-muted" style="font-size:12px;">
                    <?php if (!empty($comp['fecha_cierre'])): ?>
                        <i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($comp['fecha_cierre'])) ?>
                    <?php endif; ?>
                    <?php if (!empty($comp['responsable'])): ?>
                        &middot; <i class="fas fa-user"></i> <?= esc($comp['responsable']) ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Próxima reunión -->
    <?php if (!empty($acta['proxima_reunion_fecha'])): ?>
    <div class="card mb-3">
        <div class="card-body p-3">
            <h6 class="mb-2" style="color:#bd9751;"><i class="fas fa-calendar-alt"></i> Proxima Reunion</h6>
            <p class="mb-0" style="font-size:14px;">
                <?= date('d/m/Y', strtotime($acta['proxima_reunion_fecha'])) ?>
                <?= !empty($acta['proxima_reunion_hora']) ? ' a las ' . date('h:i A', strtotime($acta['proxima_reunion_hora'])) : '' ?>
            </p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Fotos -->
    <?php if (!empty($fotos)): ?>
    <div class="card mb-3">
        <div class="card-body p-3">
            <h6 class="mb-2" style="color:#bd9751;"><i class="fas fa-camera"></i> Fotos (<?= count($fotos) ?>)</h6>
            <div class="row g-2">
                <?php foreach ($fotos as $foto): ?>
                <div class="col-4">
                    <a href="<?= base_url($foto['ruta_archivo']) ?>" target="_blank">
                        <img src="<?= base_url($foto['ruta_archivo']) ?>" class="img-fluid rounded"
                             style="max-height:120px; object-fit:cover; width:100%;">
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="mb-4"></div>
</div>

<script>
document.getElementById('btnCompartir')?.addEventListener('click', function() {
    <?php if (!empty($acta['ruta_pdf'])): ?>
    const pdfUrl = '<?= base_url('/inspecciones/acta-visita/pdf/' . $acta['id']) ?>';
    if (navigator.share) {
        navigator.share({
            title: 'Acta de Visita - <?= esc($cliente['nombre_cliente'] ?? '') ?>',
            text: 'Acta de visita del <?= date('d/m/Y', strtotime($acta['fecha_visita'])) ?>',
            url: pdfUrl
        }).catch(() => {});
    } else {
        navigator.clipboard.writeText(pdfUrl).then(() => {
            Swal.fire({ icon: 'success', title: 'Link copiado', timer: 1500, showConfirmButton: false, toast: true, position: 'top-end' });
        });
    }
    <?php else: ?>
    Swal.fire({ icon: 'info', title: 'PDF no disponible', text: 'El acta no tiene PDF generado aún.' });
    <?php endif; ?>
});
</script>
