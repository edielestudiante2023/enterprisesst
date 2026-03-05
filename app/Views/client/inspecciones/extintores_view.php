<div class="page-header">
    <h1><i class="fas fa-fire-extinguisher me-2"></i> Inspección de Extintores</h1>
    <a href="<?= base_url('client/inspecciones/extintores') ?>" class="btn-back">
        <i class="fas fa-arrow-left me-1"></i> Volver
    </a>
</div>

<!-- Datos generales -->
<div class="card mb-3">
    <div class="card-body">
        <h6 class="card-title">DATOS GENERALES</h6>
        <table class="table table-sm mb-0" style="font-size:14px;">
            <tr><td class="text-muted" style="width:45%;">Cliente</td><td><strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong></td></tr>
            <tr><td class="text-muted">Consultor</td><td><?= esc($consultor['nombre_consultor'] ?? '') ?></td></tr>
            <tr><td class="text-muted">Fecha inspección</td><td><?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td></tr>
            <?php if (!empty($inspeccion['fecha_vencimiento_global'])): ?>
            <tr><td class="text-muted">Vencimiento global</td><td><?= date('d/m/Y', strtotime($inspeccion['fecha_vencimiento_global'])) ?></td></tr>
            <?php endif; ?>
            <tr><td class="text-muted">Total extintores</td><td><strong><?= $inspeccion['numero_extintores_totales'] ?? 0 ?></strong></td></tr>
        </table>
    </div>
</div>

<!-- Inventario -->
<div class="card mb-3">
    <div class="card-body">
        <h6 class="card-title">INVENTARIO</h6>
        <div class="row" style="font-size:13px;">
            <div class="col-6 mb-1"><span class="text-muted">ABC:</span> <strong><?= $inspeccion['cantidad_abc'] ?? 0 ?></strong></div>
            <div class="col-6 mb-1"><span class="text-muted">CO2:</span> <strong><?= $inspeccion['cantidad_co2'] ?? 0 ?></strong></div>
            <div class="col-6 mb-1"><span class="text-muted">Solkaflam:</span> <strong><?= $inspeccion['cantidad_solkaflam'] ?? 0 ?></strong></div>
            <div class="col-6 mb-1"><span class="text-muted">Agua:</span> <strong><?= $inspeccion['cantidad_agua'] ?? 0 ?></strong></div>
            <?php if (!empty($inspeccion['capacidad_libras'])): ?>
            <div class="col-12 mb-1"><span class="text-muted">Capacidad:</span> <?= esc($inspeccion['capacidad_libras']) ?></div>
            <?php endif; ?>
        </div>
        <hr style="margin:8px 0;">
        <h6 class="card-title" style="font-size:12px;">DISTRIBUCIÓN POR UBICACIÓN</h6>
        <div class="row" style="font-size:12px;">
            <div class="col-6 mb-1"><span class="text-muted">Und. residenciales:</span> <?= $inspeccion['cantidad_unidades_residenciales'] ?? 0 ?></div>
            <div class="col-6 mb-1"><span class="text-muted">Portería:</span> <?= $inspeccion['cantidad_porteria'] ?? 0 ?></div>
            <div class="col-6 mb-1"><span class="text-muted">Oficina admin:</span> <?= $inspeccion['cantidad_oficina_admin'] ?? 0 ?></div>
            <div class="col-6 mb-1"><span class="text-muted">Shut basuras:</span> <?= $inspeccion['cantidad_shut_basuras'] ?? 0 ?></div>
            <div class="col-6 mb-1"><span class="text-muted">Salones comunales:</span> <?= $inspeccion['cantidad_salones_comunales'] ?? 0 ?></div>
            <div class="col-6 mb-1"><span class="text-muted">Cuarto bombas:</span> <?= $inspeccion['cantidad_cuarto_bombas'] ?? 0 ?></div>
            <div class="col-6 mb-1"><span class="text-muted">Planta eléctrica:</span> <?= $inspeccion['cantidad_planta_electrica'] ?? 0 ?></div>
        </div>
    </div>
</div>

<!-- Extintores inspeccionados (accordion) -->
<?php if (!empty($extintores)): ?>
<div class="accordion mb-3" id="accordionExtClient">
    <?php foreach ($extintores as $i => $ext): ?>
    <div class="accordion-item" style="border:none; margin-bottom:2px;">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button"
                    data-bs-toggle="collapse" data-bs-target="#cext_<?= $i ?>"
                    style="background: linear-gradient(135deg, #1c2437 0%, #2c3e50 100%); color:white; font-weight:600; font-size:14px;">
                Extintor #<?= $i + 1 ?>
                <?php if (!empty($ext['observaciones'])): ?>
                <small class="ms-2" style="font-size:11px; opacity:0.7;"><?= esc(mb_substr($ext['observaciones'], 0, 30)) ?>...</small>
                <?php endif; ?>
            </button>
        </h2>
        <div id="cext_<?= $i ?>" class="accordion-collapse collapse" data-bs-parent="#accordionExtClient">
            <div class="accordion-body p-2" style="background:white;">
                <table class="table table-sm mb-2" style="font-size:12px;">
                    <?php foreach ($criterios as $key => $cfg): ?>
                    <tr>
                        <td class="text-muted" style="width:55%;"><?= $cfg['label'] ?></td>
                        <td>
                            <?php
                            $val = $ext[$key] ?? '';
                            $color = 'text-dark';
                            if (in_array($val, ['BUENO', 'CARGADO', 'NO'])) $color = 'text-success';
                            elseif ($val === 'REGULAR') $color = 'text-warning';
                            elseif (in_array($val, ['MALO', 'SI', 'DESCARGADO'])) $color = 'text-danger';
                            elseif (in_array($val, ['NO APLICA', 'NO TIENE'])) $color = 'text-muted';
                            ?>
                            <strong class="<?= $color ?>"><?= esc($val) ?></strong>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (!empty($ext['fecha_vencimiento'])): ?>
                    <tr><td class="text-muted">Fecha vencimiento</td><td><?= date('d/m/Y', strtotime($ext['fecha_vencimiento'])) ?></td></tr>
                    <?php endif; ?>
                </table>
                <?php if (!empty($ext['foto'])): ?>
                <div class="mb-2">
                    <img src="<?= base_url($ext['foto']) ?>" class="img-fluid rounded"
                         style="max-height:100px; object-fit:cover; cursor:pointer; border:1px solid #ddd;"
                         onclick="openPhoto(this.src)">
                </div>
                <?php endif; ?>
                <?php if (!empty($ext['observaciones'])): ?>
                <p class="text-muted" style="font-size:12px; margin:0;"><i class="fas fa-comment-alt me-1"></i> <?= esc($ext['observaciones']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Recomendaciones generales -->
<?php if (!empty($inspeccion['recomendaciones_generales'])): ?>
<div class="card mb-3">
    <div class="card-body">
        <h6 class="card-title">RECOMENDACIONES GENERALES</h6>
        <p style="font-size:14px; margin:0;"><?= nl2br(esc($inspeccion['recomendaciones_generales'])) ?></p>
    </div>
</div>
<?php endif; ?>

<!-- Botón PDF -->
<?php if (!empty($inspeccion['ruta_pdf'])): ?>
<div class="text-center mb-4">
    <a href="/inspecciones/extintores/pdf/<?= $inspeccion['id'] ?>" class="btn btn-pdf" target="_blank">
        <i class="fas fa-file-pdf me-2"></i> Descargar PDF
    </a>
</div>
<?php endif; ?>

<!-- Modal foto -->
<div class="modal fade" id="photoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark">
            <div class="modal-body p-1 text-center">
                <img id="photoFull" src="" class="img-fluid" style="max-height:80vh;">
            </div>
        </div>
    </div>
</div>
<script>
function openPhoto(src) {
    document.getElementById('photoFull').src = src;
    new bootstrap.Modal(document.getElementById('photoModal')).show();
}
</script>

<style>
    .accordion-button::after {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23ffffff'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e");
    }
    .accordion-button:not(.collapsed) {
        background: linear-gradient(135deg, #bd9751 0%, #d4af37 100%) !important;
    }
</style>
