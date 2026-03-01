<?php
$califColor = '#bd9751';
$calif = (float)($inspeccion['calificacion'] ?? 0);
if ($calif <= 40) $califColor = '#dc3545';
elseif ($calif <= 60) $califColor = '#fd7e14';
elseif ($calif <= 80) $califColor = '#ffc107';
elseif ($calif <= 90) $califColor = '#28a745';
else $califColor = '#28a745';
?>
<div class="container-fluid px-3">
    <div class="mt-2 mb-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Inspeccion de Senalizacion</h6>
        <span class="badge badge-<?= esc($inspeccion['estado']) ?>">
            <?= $inspeccion['estado'] === 'completo' ? 'Completo' : 'Borrador' ?>
        </span>
    </div>

    <!-- Datos generales -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">DATOS GENERALES</h6>
            <table class="table table-sm mb-0" style="font-size:14px;">
                <tr><td class="text-muted" style="width:40%;">Cliente</td><td><strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong></td></tr>
                <tr><td class="text-muted">Fecha</td><td><?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td></tr>
                <tr><td class="text-muted">Consultor</td><td><?= esc($consultor['nombre_consultor'] ?? '') ?></td></tr>
            </table>
        </div>
    </div>

    <!-- Resumen calificación -->
    <div class="card mb-3" style="border:2px solid <?= $califColor ?>;">
        <div class="card-body text-center py-2">
            <strong style="font-size:14px;">Calificacion:</strong>
            <span style="font-size:22px; font-weight:700; color:<?= $califColor ?>;"><?= number_format($calif, 1) ?>%</span>
            <br>
            <small style="font-size:12px; color:#666;"><?= esc($inspeccion['descripcion_cualitativa'] ?? '') ?></small>
            <br>
            <small style="font-size:11px; color:#999;">
                NA: <?= $inspeccion['conteo_no_aplica'] ?? 0 ?> |
                NC: <?= $inspeccion['conteo_no_cumple'] ?? 0 ?> |
                CP: <?= $inspeccion['conteo_parcial'] ?? 0 ?> |
                CT: <?= $inspeccion['conteo_total'] ?? 0 ?>
            </small>
        </div>
    </div>

    <!-- Items por grupo -->
    <?php if (!empty($itemsGrouped)): ?>
    <div class="accordion mb-3" id="accordionView">
        <?php foreach ($itemsGrouped as $grupo => $items):
            $grupoId = 'vg_' . preg_replace('/[^a-z0-9]/', '_', strtolower($grupo));
            $cumple = 0;
            foreach ($items as $it) {
                if ($it['estado_cumplimiento'] === 'CUMPLE TOTALMENTE') $cumple++;
            }
        ?>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $grupoId ?>">
                    <?= esc($grupo) ?>
                    <span class="badge bg-secondary ms-2" style="font-size:10px;"><?= $cumple ?>/<?= count($items) ?></span>
                </button>
            </h2>
            <div id="<?= $grupoId ?>" class="accordion-collapse collapse" data-bs-parent="#accordionView">
                <div class="accordion-body p-2">
                    <?php foreach ($items as $item):
                        $estadoBg = 'bg-danger';
                        if ($item['estado_cumplimiento'] === 'NO APLICA') { $estadoBg = 'bg-secondary'; }
                        elseif ($item['estado_cumplimiento'] === 'CUMPLE PARCIALMENTE') { $estadoBg = 'bg-warning text-dark'; }
                        elseif ($item['estado_cumplimiento'] === 'CUMPLE TOTALMENTE') { $estadoBg = 'bg-success'; }
                    ?>
                    <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom:1px solid #eee;">
                        <div style="font-size:12px; flex:1;"><?= esc($item['nombre_item']) ?></div>
                        <span class="badge <?= $estadoBg ?>" style="font-size:10px; white-space:nowrap;"><?= esc($item['estado_cumplimiento']) ?></span>
                    </div>
                    <?php if (!empty($item['foto'])): ?>
                    <div class="mb-1 mt-1">
                        <img src="<?= base_url($item['foto']) ?>" class="img-fluid rounded"
                             style="max-height:80px; object-fit:cover; cursor:pointer; border:1px solid #ddd;"
                             onclick="openPhoto(this.src, '<?= esc($item['nombre_item']) ?>')">
                    </div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Observaciones generales -->
    <?php if (!empty($inspeccion['observaciones'])): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">OBSERVACIONES GENERALES</h6>
            <p style="font-size:14px; margin:0;"><?= nl2br(esc($inspeccion['observaciones'])) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Modal foto -->
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

    <!-- Acciones -->
    <div class="mb-4">
        <?php if ($inspeccion['estado'] === 'completo' && !empty($inspeccion['ruta_pdf'])): ?>
        <a href="/inspecciones/senalizacion/pdf/<?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-primary" target="_blank">
            <i class="fas fa-file-pdf"></i> Ver PDF
        </a>
        <?php endif; ?>
        <a href="/inspecciones/senalizacion/edit/<?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline">
            <i class="fas fa-edit"></i> Editar
        </a>
    </div>
</div>
