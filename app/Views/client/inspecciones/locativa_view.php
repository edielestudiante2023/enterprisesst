<div class="page-header">
    <h1><i class="fas fa-building me-2"></i> Inspección Locativa</h1>
    <a href="<?= base_url('client/inspecciones/locativas') ?>" class="btn-back">
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
            <tr><td class="text-muted">Fecha</td><td><?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td></tr>
        </table>
    </div>
</div>

<!-- Hallazgos -->
<?php if (!empty($hallazgos)): ?>
<div class="card mb-3">
    <div class="card-body">
        <h6 class="card-title">HALLAZGOS (<?= count($hallazgos) ?>)</h6>
        <?php foreach ($hallazgos as $i => $h): ?>
        <div class="mb-3 pb-3" style="border-bottom:1px solid #f0f0f0;">
            <div class="d-flex justify-content-between align-items-start mb-1">
                <strong style="font-size:13px; color:#1c2437;">Hallazgo #<?= $i + 1 ?></strong>
                <?php
                $estadoColor = 'bg-warning text-dark';
                if ($h['estado'] === 'CERRADO') $estadoColor = 'bg-success';
                elseif ($h['estado'] !== 'ABIERTO') $estadoColor = 'bg-danger';
                ?>
                <span class="badge <?= $estadoColor ?>" style="font-size:11px;">
                    <?= esc($h['estado']) ?>
                </span>
            </div>

            <p style="font-size:14px; margin-bottom:8px;"><?= esc($h['descripcion']) ?></p>

            <!-- Fotos antes/después -->
            <div class="row g-2 mb-2">
                <?php if (!empty($h['imagen'])): ?>
                <div class="col-6">
                    <small class="text-muted d-block" style="font-size:11px;">Hallazgo</small>
                    <img src="<?= base_url($h['imagen']) ?>"
                         class="img-fluid rounded"
                         style="width:100%; height:120px; object-fit:cover; cursor:pointer; border:1px solid #ddd;"
                         onclick="openPhoto(this.src, 'Hallazgo #<?= $i + 1 ?>')">
                </div>
                <?php endif; ?>
                <?php if (!empty($h['imagen_correccion'])): ?>
                <div class="col-6">
                    <small class="text-muted d-block" style="font-size:11px;">Corrección</small>
                    <img src="<?= base_url($h['imagen_correccion']) ?>"
                         class="img-fluid rounded"
                         style="width:100%; height:120px; object-fit:cover; cursor:pointer; border:1px solid #ddd;"
                         onclick="openPhoto(this.src, 'Corrección hallazgo #<?= $i + 1 ?>')">
                </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($h['observaciones'])): ?>
            <p class="text-muted" style="font-size:12px; margin:0;">
                <i class="fas fa-comment-alt me-1"></i> <?= esc($h['observaciones']) ?>
            </p>
            <?php endif; ?>

            <?php if (!empty($h['fecha_hallazgo'])): ?>
            <small class="text-muted" style="font-size:11px;">
                <i class="fas fa-calendar me-1"></i> Hallazgo: <?= date('d/m/Y', strtotime($h['fecha_hallazgo'])) ?>
                <?php if (!empty($h['fecha_correccion'])): ?>
                    | Corrección: <?= date('d/m/Y', strtotime($h['fecha_correccion'])) ?>
                <?php endif; ?>
            </small>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Observaciones generales -->
<?php if (!empty($inspeccion['observaciones'])): ?>
<div class="card mb-3">
    <div class="card-body">
        <h6 class="card-title">OBSERVACIONES GENERALES</h6>
        <p style="font-size:14px; margin:0;"><?= nl2br(esc($inspeccion['observaciones'])) ?></p>
    </div>
</div>
<?php endif; ?>

<!-- Botón PDF -->
<?php if (!empty($inspeccion['ruta_pdf'])): ?>
<div class="text-center mb-4">
    <a href="/inspecciones/inspeccion-locativa/pdf/<?= $inspeccion['id'] ?>" class="btn btn-pdf" target="_blank">
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
