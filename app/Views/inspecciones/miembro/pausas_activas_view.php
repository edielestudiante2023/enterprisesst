<div class="container-fluid px-3">
    <div class="mt-2 mb-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Pausa Activa</h6>
        <span class="badge badge-<?= esc($inspeccion['estado']) ?>">
            <?= $inspeccion['estado'] === 'completo' ? 'Completo' : 'Borrador' ?>
        </span>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">DATOS GENERALES</h6>
            <table class="table table-sm mb-0" style="font-size:14px;">
                <tr><td class="text-muted" style="width:40%;">Cliente</td><td><strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong></td></tr>
                <tr><td class="text-muted">Fecha</td><td><?= date('d/m/Y', strtotime($inspeccion['fecha_actividad'])) ?></td></tr>
                <tr>
                    <td class="text-muted">Realizado por</td>
                    <td>
                        <?php if (!empty($realizadoPor)): ?>
                            <?= esc($realizadoPor) ?> <span class="badge bg-info" style="font-size:10px;">COPASST</span>
                        <?php elseif (!empty($consultor)): ?>
                            <?= esc($consultor['nombre_consultor'] ?? '') ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <?php if (!empty($registros)): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">REGISTROS (<?= count($registros) ?>)</h6>
            <?php foreach ($registros as $i => $r): ?>
            <div class="mb-3 pb-3" style="border-bottom:1px solid #eee;">
                <strong style="font-size:13px;">Registro #<?= $i + 1 ?></strong>
                <p style="font-size:14px; margin-bottom:8px;"><?= esc($r['tipo_pausa']) ?></p>
                <?php if (!empty($r['imagen'])): ?>
                <img src="<?= base_url($r['imagen']) ?>" class="img-fluid rounded"
                     style="width:100%; max-height:200px; object-fit:cover; cursor:pointer; border:1px solid #ddd;"
                     onclick="openPhoto(this.src, 'Registro #<?= $i + 1 ?>')">
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($inspeccion['observaciones'])): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">OBSERVACIONES</h6>
            <p style="font-size:14px; margin:0;"><?= nl2br(esc($inspeccion['observaciones'])) ?></p>
        </div>
    </div>
    <?php endif; ?>

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

    <div class="mb-4">
        <?php if ($inspeccion['estado'] === 'completo' && !empty($inspeccion['ruta_pdf'])): ?>
        <a href="/miembro/inspecciones/pausas-activas/pdf/<?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-primary" target="_blank">
            <i class="fas fa-file-pdf"></i> Ver PDF
        </a>
        <?php endif; ?>
        <a href="/miembro/inspecciones/pausas-activas" class="btn btn-pwa btn-pwa-outline">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
</div>
