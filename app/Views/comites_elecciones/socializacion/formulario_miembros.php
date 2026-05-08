<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1"><i class="bi bi-megaphone me-2"></i>Socializar Miembros del <?= esc($tipoComite) ?></h1>
            <p class="text-muted mb-0"><?= esc($cliente['nombre_cliente'] ?? '') ?> &middot; Codigo <?= esc($codigoFt) ?></p>
        </div>
        <a href="<?= base_url('comites-elecciones/' . $proceso['id_cliente'] . '/proceso/' . $proceso['id_proceso']) ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Volver al proceso
        </a>
    </div>

    <?php if (session()->getFlashdata('warning')): ?>
        <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-1"></i><?= session()->getFlashdata('warning') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><i class="bi bi-x-circle me-1"></i><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <form method="post" action="<?= base_url('comites-elecciones/proceso/' . $proceso['id_proceso'] . '/socializar/miembros/enviar') ?>" enctype="multipart/form-data" id="formSocializar">
        <div class="row g-3">

            <!-- Columna izquierda: datos del PDF -->
            <div class="col-lg-7">
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-file-pdf me-1"></i>Datos del PDF a generar
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Periodo - Inicio <span class="text-danger">*</span></label>
                                <input type="date" name="periodo_inicio" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Periodo - Fin <span class="text-danger">*</span></label>
                                <input type="date" name="periodo_fin" class="form-control" required>
                            </div>
                        </div>

                        <h6 class="mb-2">Miembros electos que se incluiran en el PDF</h6>
                        <?php if (empty($miembros)): ?>
                            <div class="alert alert-warning small mb-0">
                                No hay candidatos electos en este proceso. No se puede generar el PDF.
                            </div>
                        <?php else: ?>
                            <ul class="list-group list-group-flush small">
                                <?php foreach ($miembros as $m): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <span>
                                            <?php if (!empty($m['foto_base64'])): ?>
                                                <img src="<?= $m['foto_base64'] ?>" style="width:30px;height:30px;border-radius:50%;object-fit:cover;" class="me-2">
                                            <?php endif; ?>
                                            <strong><?= esc($m['nombre']) ?></strong>
                                            <?php if (!empty($m['cargo'])): ?> - <?= esc($m['cargo']) ?><?php endif; ?>
                                        </span>
                                        <span>
                                            <span class="badge bg-<?= ($m['representacion'] ?? '') === 'empleador' ? 'primary' : 'success' ?>">
                                                <?= esc(ucfirst($m['representacion'] ?? '-')) ?>
                                            </span>
                                            <?php if (!empty($m['rol_comite']) && $m['rol_comite'] !== 'miembro'): ?>
                                                <span class="badge bg-info"><?= esc(ucfirst($m['rol_comite'])) ?></span>
                                            <?php endif; ?>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-info text-white">
                        <i class="bi bi-envelope me-1"></i>Email a enviar
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Asunto</label>
                            <input type="text" name="asunto" class="form-control" value="<?= esc($asuntoPreset) ?>">
                            <div class="form-text">Si lo dejas vacio se usara el preset.</div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Cuerpo del email (HTML)</label>
                            <textarea name="cuerpo" class="form-control" rows="9"><?= esc($cuerpoPreset) ?></textarea>
                            <div class="form-text">Editable. Si lo dejas vacio se usara el preset. El PDF se adjunta automaticamente.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna derecha: destinatarios CSV + envio -->
            <div class="col-lg-5">
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-warning">
                        <i class="bi bi-people me-1"></i>Destinatarios (CSV)
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Archivo CSV con emails <span class="text-danger">*</span></label>
                            <input type="file" name="csv_emails" accept=".csv,text/csv" class="form-control" required>
                            <div class="form-text">
                                Columnas: <code>nombre</code>, <code>email</code>. Separadores aceptados: <code>,</code> <code>;</code> <code>|</code>. UTF-8.
                            </div>
                        </div>
                        <a href="<?= base_url('comites-elecciones/socializaciones/plantilla-csv') ?>" class="btn btn-outline-secondary btn-sm w-100">
                            <i class="bi bi-download me-1"></i>Descargar plantilla CSV
                        </a>
                    </div>
                </div>

                <button type="submit" class="btn btn-success w-100 btn-lg" <?= empty($miembros) ? 'disabled' : '' ?>>
                    <i class="bi bi-send-fill me-1"></i>Generar PDF y enviar emails
                </button>

                <?php if (!empty($historial)): ?>
                <div class="card shadow-sm mt-3">
                    <div class="card-header"><i class="bi bi-clock-history me-1"></i>Historial</div>
                    <div class="card-body p-2 small">
                        <?php foreach ($historial as $h): ?>
                            <div class="d-flex justify-content-between align-items-center border-bottom py-1">
                                <span><?= esc(date('d/m/Y H:i', strtotime($h['created_at']))) ?></span>
                                <span>
                                    <span class="badge bg-success"><?= (int)$h['enviados_ok'] ?> OK</span>
                                    <?php if ((int)$h['fallidos'] > 0): ?>
                                        <span class="badge bg-danger"><?= (int)$h['fallidos'] ?> fallidos</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<script>
// Anti doble-submit
document.getElementById('formSocializar')?.addEventListener('submit', function(e) {
    const btn = this.querySelector('button[type="submit"]');
    if (!btn) return;
    if (btn.dataset.submitted === '1') { e.preventDefault(); return false; }
    btn.dataset.submitted = '1';
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando... esto puede tardar unos segundos';
});
</script>

<?= $this->endSection() ?>
