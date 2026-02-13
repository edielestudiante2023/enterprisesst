<?php
/**
 * Vista de Tipo: 5.1.1 Plan de Prevencion, Preparacion y respuesta ante emergencias
 * Carpeta para adjuntar soportes del plan de emergencias
 * Variables: $carpeta, $cliente, $documentosSSTAprobados
 */
?>

<!-- Card de Carpeta con Boton Adjuntar -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="mb-1">
                    <i class="bi bi-folder-fill text-warning me-2"></i>
                    <?= esc($carpeta['nombre']) ?>
                </h4>
                <?php if (!empty($carpeta['codigo'])): ?>
                    <span class="badge bg-light text-dark me-2"><?= esc($carpeta['codigo']) ?></span>
                <?php endif; ?>
                <?php if (!empty($carpeta['descripcion'])): ?>
                    <p class="text-muted mb-0 mt-1"><?= esc($carpeta['descripcion']) ?></p>
                <?php endif; ?>
            </div>
            <div class="col-md-4 text-end">
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalAdjuntarEmergencias">
                    <i class="bi bi-cloud-upload me-1"></i>Adjuntar Soporte
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Informacion sobre el modulo -->
<div class="alert alert-danger mb-4">
    <div class="d-flex align-items-start">
        <i class="bi bi-exclamation-triangle me-3 fs-4"></i>
        <div>
            <h6 class="mb-1">Plan de Prevencion, Preparacion y Respuesta ante Emergencias</h6>
            <p class="mb-0 small">
                Adjunte los documentos relacionados con el plan de emergencias:
                plan de emergencias, analisis de vulnerabilidad, planos de evacuacion, procedimientos, simulacros, etc.
            </p>
        </div>
    </div>
</div>

<!-- Tabla de Soportes Adjuntados -->
<?= view('documentacion/_components/tabla_soportes', [
    'soportes' => $documentosSSTAprobados ?? [],
    'titulo' => 'Soportes Plan de Emergencias',
    'subtitulo' => 'Prevencion, preparacion y respuesta',
    'icono' => 'bi-exclamation-triangle',
    'colorHeader' => 'warning',
    'codigoDefault' => 'SOP-EME',
    'emptyIcon' => 'bi-exclamation-triangle',
    'emptyMessage' => 'No hay soportes adjuntados aun.',
    'emptyHint' => 'Use el boton "Adjuntar Soporte" para agregar documentos.'
]) ?>

<!-- Subcarpetas -->
<div class="row">
    <?= view('documentacion/_components/lista_subcarpetas', ['subcarpetas' => $subcarpetas ?? []]) ?>
</div>

<!-- Modal para Adjuntar -->
<div class="modal fade" id="modalAdjuntarEmergencias" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-cloud-upload me-2"></i>Adjuntar Soporte Emergencias</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAdjuntarEmergencias" action="<?= base_url('documentos-sst/adjuntar-soporte-emergencias') ?>" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id_cliente" value="<?= $cliente['id_cliente'] ?>">
                    <input type="hidden" name="id_carpeta" value="<?= $carpeta['id_carpeta'] ?>">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de carga</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="tipo_carga" id="tipoCargaArchivoEme" value="archivo" checked>
                            <label class="btn btn-outline-danger" for="tipoCargaArchivoEme">
                                <i class="bi bi-file-earmark-arrow-up me-1"></i>Subir Archivo
                            </label>
                            <input type="radio" class="btn-check" name="tipo_carga" id="tipoCargaEnlaceEme" value="enlace">
                            <label class="btn btn-outline-danger" for="tipoCargaEnlaceEme">
                                <i class="bi bi-link-45deg me-1"></i>Pegar Enlace
                            </label>
                        </div>
                    </div>

                    <div class="mb-3" id="campoArchivoEme">
                        <label class="form-label">Archivo (PDF, Excel, Imagen)</label>
                        <input type="file" class="form-control" id="archivo_eme" name="archivo_soporte" accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx,.doc,.docx">
                    </div>

                    <div class="mb-3 d-none" id="campoEnlaceEme">
                        <label class="form-label">Enlace (Google Drive, OneDrive)</label>
                        <input type="url" class="form-control" id="url_externa_eme" name="url_externa" placeholder="https://drive.google.com/...">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripcion</label>
                        <input type="text" class="form-control" name="descripcion" required placeholder="Ej: Plan emergencias 2026, Simulacro evacuacion...">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ano</label>
                        <select class="form-select" name="anio">
                            <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                <option value="<?= $y ?>" <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Observaciones (opcional)</label>
                        <textarea class="form-control" name="observaciones" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger" id="btnAdjuntarEmergencias">
                        <i class="bi bi-cloud-upload me-1"></i>Adjuntar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('input[name="tipo_carga"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const isArchivo = this.value === 'archivo';
        document.getElementById('campoArchivoEme').classList.toggle('d-none', !isArchivo);
        document.getElementById('campoEnlaceEme').classList.toggle('d-none', isArchivo);
    });
});
document.getElementById('formAdjuntarEmergencias')?.addEventListener('submit', function() {
    const btn = document.getElementById('btnAdjuntarEmergencias');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Subiendo...';
});
</script>
