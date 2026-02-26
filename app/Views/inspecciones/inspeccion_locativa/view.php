<div class="container-fluid px-3">
    <div class="mt-2 mb-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Inspeccion Locativa</h6>
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

    <!-- Hallazgos -->
    <?php if (!empty($hallazgos)): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">HALLAZGOS (<?= count($hallazgos) ?>)</h6>
            <?php foreach ($hallazgos as $i => $h): ?>
            <div class="mb-3 pb-3" style="border-bottom:1px solid #eee;">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <strong style="font-size:13px;">Hallazgo #<?= $i + 1 ?></strong>
                    <span class="badge <?= $h['estado'] === 'CERRADO' ? 'bg-success' : ($h['estado'] === 'ABIERTO' ? 'bg-warning text-dark' : 'bg-danger') ?>" style="font-size:11px;">
                        <?= esc($h['estado']) ?>
                    </span>
                </div>

                <p style="font-size:14px; margin-bottom:8px;"><?= esc($h['descripcion']) ?></p>

                <!-- Fotos -->
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
                        <small class="text-muted d-block" style="font-size:11px;">Correccion</small>
                        <img src="<?= base_url($h['imagen_correccion']) ?>"
                             class="img-fluid rounded"
                             style="width:100%; height:120px; object-fit:cover; cursor:pointer; border:1px solid #ddd;"
                             onclick="openPhoto(this.src, 'Correccion hallazgo #<?= $i + 1 ?>')">
                    </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($h['observaciones'])): ?>
                <p class="text-muted" style="font-size:12px; margin:0;"><i class="fas fa-comment-alt"></i> <?= esc($h['observaciones']) ?></p>
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
            <h6 class="card-title" style="font-size:14px; color:#999;">OBSERVACIONES GENERALES</h6>
            <p style="font-size:14px; margin:0;"><?= nl2br(esc($inspeccion['observaciones'])) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Modal para ver foto ampliada -->
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
        <a href="/inspecciones/inspeccion-locativa/pdf/<?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-primary" target="_blank">
            <i class="fas fa-file-pdf"></i> Ver PDF
        </a>
        <?php endif; ?>

        <?php if ($inspeccion['estado'] !== 'completo'): ?>
        <a href="/inspecciones/inspeccion-locativa/edit/<?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline">
            <i class="fas fa-edit"></i> Editar
        </a>
        <?php endif; ?>
    </div>
</div>
