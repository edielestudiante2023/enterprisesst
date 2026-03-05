<div class="page-header">
    <h1><i class="fas fa-file-signature me-2"></i> Acta de Visita</h1>
    <a href="<?= base_url('client/inspecciones/actas-visita') ?>" class="btn-back">
        <i class="fas fa-arrow-left me-1"></i> Volver
    </a>
</div>

<!-- Datos generales -->
<div class="card mb-3">
    <div class="card-body">
        <h6 class="card-title">DATOS GENERALES</h6>
        <table class="table table-sm mb-0" style="font-size:14px;">
            <tr><td class="text-muted" style="width:40%;">Cliente</td><td><strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong></td></tr>
            <tr><td class="text-muted">Consultor</td><td><?= esc($consultor['nombre_consultor'] ?? '') ?></td></tr>
            <tr><td class="text-muted">Fecha</td><td><?= date('d/m/Y', strtotime($acta['fecha_visita'])) ?></td></tr>
            <tr><td class="text-muted">Hora</td><td><?= date('g:i A', strtotime($acta['hora_visita'])) ?></td></tr>
            <tr><td class="text-muted">Motivo</td><td><?= esc($acta['motivo']) ?></td></tr>
            <tr><td class="text-muted">Modalidad</td><td><?= esc($acta['modalidad'] ?? 'Presencial') ?></td></tr>
        </table>
    </div>
</div>

<!-- Integrantes -->
<?php if (!empty($integrantes)): ?>
<div class="card mb-3">
    <div class="card-body">
        <h6 class="card-title">INTEGRANTES</h6>
        <?php foreach ($integrantes as $int): ?>
        <div class="d-flex justify-content-between py-2" style="font-size:14px; border-bottom:1px solid #f0f0f0;">
            <span><?= esc($int['nombre']) ?></span>
            <span class="badge" style="background:#1c2437; font-size:11px;"><?= esc($int['rol']) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Temas -->
<?php if (!empty($temas)): ?>
<div class="card mb-3">
    <div class="card-body">
        <h6 class="card-title">TEMAS TRATADOS</h6>
        <?php foreach ($temas as $i => $tema): ?>
        <div style="font-size:14px; padding:6px 0; border-bottom:1px solid #f0f0f0;">
            <strong style="color:#bd9751;">Tema <?= $i + 1 ?>:</strong> <?= esc($tema['descripcion']) ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Observaciones -->
<?php if (!empty($acta['observaciones'])): ?>
<div class="card mb-3">
    <div class="card-body">
        <h6 class="card-title">OBSERVACIONES</h6>
        <p style="font-size:14px; margin:0;"><?= nl2br(esc($acta['observaciones'])) ?></p>
    </div>
</div>
<?php endif; ?>

<!-- Cartera -->
<?php if (!empty($acta['cartera'])): ?>
<div class="card mb-3">
    <div class="card-body">
        <h6 class="card-title">CARTERA</h6>
        <p style="font-size:14px; margin:0;"><?= nl2br(esc($acta['cartera'])) ?></p>
    </div>
</div>
<?php endif; ?>

<!-- Compromisos -->
<?php if (!empty($compromisos)): ?>
<div class="card mb-3">
    <div class="card-body">
        <h6 class="card-title">COMPROMISOS</h6>
        <?php foreach ($compromisos as $comp): ?>
        <div style="font-size:14px; padding:8px 0; border-bottom:1px solid #f0f0f0;">
            <strong><?= esc($comp['tarea_actividad']) ?></strong>
            <div class="text-muted" style="font-size:12px;">
                Responsable: <?= esc($comp['responsable'] ?? '-') ?>
                | Fecha cierre: <?= !empty($comp['fecha_cierre']) ? date('d/m/Y', strtotime($comp['fecha_cierre'])) : '-' ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Fotos -->
<?php if (!empty($fotos)): ?>
<div class="card mb-3">
    <div class="card-body">
        <h6 class="card-title">REGISTRO FOTOGRÁFICO</h6>
        <div class="row g-2">
            <?php foreach ($fotos as $foto): ?>
            <div class="col-4 col-md-3">
                <img src="/<?= esc($foto['ruta_archivo']) ?>"
                     class="img-fluid rounded"
                     style="width:100%; height:100px; object-fit:cover; cursor:pointer; border:1px solid #ddd;"
                     onclick="openPhoto(this.src, '<?= esc($foto['descripcion'] ?? '', 'js') ?>')"
                     alt="<?= esc($foto['descripcion'] ?? 'Foto') ?>">
                <?php if (!empty($foto['descripcion'])): ?>
                    <small class="text-muted d-block text-center" style="font-size:11px;"><?= esc($foto['descripcion']) ?></small>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Botón PDF -->
<?php if (!empty($acta['ruta_pdf'])): ?>
<div class="text-center mb-4">
    <a href="/inspecciones/acta-visita/pdf/<?= $acta['id'] ?>" class="btn btn-pdf" target="_blank">
        <i class="fas fa-file-pdf me-2"></i> Descargar PDF
    </a>
</div>
<?php endif; ?>

<!-- Modal foto ampliada -->
<div class="modal fade" id="photoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark">
            <div class="modal-header border-0 py-1">
                <small class="text-light" id="photoDesc"></small>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-1 text-center">
                <img id="photoFull" src="" class="img-fluid" style="max-height:80vh;">
            </div>
        </div>
    </div>
</div>
<script>
function openPhoto(src, desc) {
    document.getElementById('photoFull').src = src;
    document.getElementById('photoDesc').textContent = desc || '';
    new bootstrap.Modal(document.getElementById('photoModal')).show();
}
</script>
