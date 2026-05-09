<div class="container-fluid px-3">
    <div class="mt-2 mb-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Inspeccion de EPP</h6>
        <span class="badge bg-<?= $inspeccion['estado'] === 'completo' ? 'success' : 'warning text-dark' ?>">
            <?= $inspeccion['estado'] === 'completo' ? 'Completo' : 'Borrador' ?>
        </span>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">DATOS GENERALES</h6>
            <table class="table table-sm mb-0" style="font-size:14px;">
                <tr><td class="text-muted" style="width:40%;">Cliente</td><td><strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong></td></tr>
                <tr><td class="text-muted">Fecha</td><td><?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td></tr>
                <?php if (!empty($consultor['nombre_consultor'])): ?>
                <tr><td class="text-muted">Consultor</td><td><?= esc($consultor['nombre_consultor']) ?></td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <?php if (!empty($hallazgos)): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">HALLAZGOS DE EPP (<?= count($hallazgos) ?>)</h6>
            <?php foreach ($hallazgos as $i => $h): ?>
            <div class="mb-3 pb-3" style="border-bottom:1px solid #eee;">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <strong style="font-size:13px;">Hallazgo #<?= $i + 1 ?></strong>
                    <span class="badge <?= $h['estado'] === 'CERRADO' ? 'bg-success' : ($h['estado'] === 'ABIERTO' ? 'bg-warning text-dark' : 'bg-danger') ?>" style="font-size:11px;">
                        <?= esc($h['estado']) ?>
                    </span>
                </div>

                <?php if (!empty($h['tipo_epp']) || !empty($h['trabajador_area'])): ?>
                <div class="text-muted" style="font-size:12px; margin-bottom:6px;">
                    <?php if (!empty($h['tipo_epp'])): ?><i class="fas fa-helmet-safety"></i> <strong><?= esc($h['tipo_epp']) ?></strong><?php endif; ?>
                    <?php if (!empty($h['trabajador_area'])): ?> &middot; <i class="fas fa-user"></i> <?= esc($h['trabajador_area']) ?><?php endif; ?>
                </div>
                <?php endif; ?>

                <p style="font-size:14px; margin-bottom:8px;"><?= esc($h['descripcion']) ?></p>

                <div class="row g-2 mb-2">
                    <?php if (!empty($h['imagen'])): ?>
                    <div class="col-6">
                        <small class="text-muted d-block" style="font-size:11px;">Hallazgo</small>
                        <img src="<?= base_url($h['imagen']) ?>" class="img-fluid rounded" style="width:100%; height:120px; object-fit:cover; cursor:pointer; border:1px solid #ddd;" onclick="openPhoto(this.src)">
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($h['imagen_correccion'])): ?>
                    <div class="col-6">
                        <small class="text-muted d-block" style="font-size:11px;">Correccion</small>
                        <img src="<?= base_url($h['imagen_correccion']) ?>" class="img-fluid rounded" style="width:100%; height:120px; object-fit:cover; cursor:pointer; border:1px solid #ddd;" onclick="openPhoto(this.src)">
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

    <?php if (!empty($inspeccion['observaciones'])): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">OBSERVACIONES / RECOMENDACIONES</h6>
            <p style="font-size:14px; margin:0;"><?= nl2br(esc($inspeccion['observaciones'])) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <div class="modal fade" id="photoModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-dark">
                <div class="modal-body p-1 text-center">
                    <img id="photoModalImg" src="" class="img-fluid" style="max-height:80vh;">
                </div>
            </div>
        </div>
    </div>
    <script>
    function openPhoto(src) {
        document.getElementById('photoModalImg').src = src;
        new bootstrap.Modal(document.getElementById('photoModal')).show();
    }
    </script>

    <div class="mb-4">
        <a href="<?= site_url('client/inspecciones/inspeccion-epp') ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
</div>
