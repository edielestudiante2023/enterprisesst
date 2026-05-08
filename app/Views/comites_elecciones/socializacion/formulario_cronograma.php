<?= $this->extend('layouts/base') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1"><i class="bi bi-calendar-event me-2"></i>Socializar Cronograma <?= esc($tipoComite) ?> <?= esc($anio) ?></h1>
            <p class="text-muted mb-0"><?= esc($cliente['nombre_cliente'] ?? '') ?> &middot; Codigo <?= esc($codigoFt) ?></p>
        </div>
        <a href="<?= base_url('comites-elecciones/' . $proceso['id_cliente'] . '/proceso/' . $proceso['id_proceso']) ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Volver al proceso
        </a>
    </div>

    <?php if (session()->getFlashdata('warning')): ?>
        <div class="alert alert-warning"><?= session()->getFlashdata('warning') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <form method="post" action="<?= base_url('comites-elecciones/proceso/' . $proceso['id_proceso'] . '/socializar/cronograma/enviar') ?>" enctype="multipart/form-data" id="formSocializarCron">
        <div class="row g-3">

            <div class="col-lg-7">
                <!-- Cronograma -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-list-check me-1"></i>Fechas del cronograma</span>
                        <button type="button" class="btn btn-sm btn-light" onclick="agregarFila()">
                            <i class="bi bi-plus-circle me-1"></i>Agregar fila
                        </button>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:50%;">Fecha</th>
                                    <th style="width:35%;">Hora</th>
                                    <th style="width:15%;"></th>
                                </tr>
                            </thead>
                            <tbody id="filasCron">
                                <!-- 4 filas iniciales por defecto -->
                                <?php for ($i = 0; $i < 4; $i++): ?>
                                    <tr>
                                        <td><input type="date" name="fecha[]" class="form-control form-control-sm"></td>
                                        <td><input type="time" name="hora[]" class="form-control form-control-sm" value="09:00"></td>
                                        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()"><i class="bi bi-trash"></i></button></td>
                                    </tr>
                                <?php endfor; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Email -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-info text-white"><i class="bi bi-envelope me-1"></i>Email a enviar</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Asunto</label>
                            <input type="text" name="asunto" class="form-control" value="<?= esc($asuntoPreset) ?>">
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Cuerpo del email (HTML)</label>
                            <textarea name="cuerpo" class="form-control" rows="8"><?= esc($cuerpoPreset) ?></textarea>
                            <div class="form-text">El PDF se adjunta automaticamente.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-warning"><i class="bi bi-people me-1"></i>Destinatarios (CSV)</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Archivo CSV <span class="text-danger">*</span></label>
                            <input type="file" name="csv_emails" accept=".csv,text/csv" class="form-control" required>
                            <div class="form-text">Columnas: <code>nombre</code>, <code>email</code>. Separadores: <code>,</code> <code>;</code> <code>|</code>. UTF-8.</div>
                        </div>
                        <a href="<?= base_url('comites-elecciones/socializaciones/plantilla-csv') ?>" class="btn btn-outline-secondary btn-sm w-100">
                            <i class="bi bi-download me-1"></i>Descargar plantilla CSV
                        </a>
                    </div>
                </div>

                <button type="submit" class="btn btn-success w-100 btn-lg">
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
function agregarFila() {
    const tbody = document.getElementById('filasCron');
    const row = document.createElement('tr');
    row.innerHTML = `
        <td><input type="date" name="fecha[]" class="form-control form-control-sm"></td>
        <td><input type="time" name="hora[]" class="form-control form-control-sm" value="09:00"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()"><i class="bi bi-trash"></i></button></td>
    `;
    tbody.appendChild(row);
}

document.getElementById('formSocializarCron')?.addEventListener('submit', function(e) {
    const btn = this.querySelector('button[type="submit"]');
    if (!btn) return;
    if (btn.dataset.submitted === '1') { e.preventDefault(); return false; }
    btn.dataset.submitted = '1';
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';
});
</script>

<?= $this->endSection() ?>
