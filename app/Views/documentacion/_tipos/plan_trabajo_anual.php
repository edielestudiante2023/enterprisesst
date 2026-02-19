<?php
/**
 * Vista de Tipo: 2.4.1 Plan de Trabajo Anual
 * Carpeta para adjuntar soportes del Plan de Trabajo Anual (PTA)
 * Modelo soporte/adjunto (igual a 1.1.4 Afiliación SRL)
 * Variables: $carpeta, $cliente, $documentosSSTAprobados
 */
?>

<!-- Card de Carpeta con Botón Adjuntar -->
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
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAdjuntarPTA">
                    <i class="bi bi-cloud-upload me-1"></i>Adjuntar Soporte
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Información sobre el módulo -->
<div class="alert alert-info mb-4">
    <div class="d-flex align-items-start">
        <i class="bi bi-info-circle-fill me-3 fs-4"></i>
        <div>
            <h6 class="mb-1">Plan de Trabajo Anual del SG-SST</h6>
            <p class="mb-0 small">
                Adjunte el Plan de Trabajo Anual firmado que identifica objetivos, metas, responsabilidades,
                recursos con cronograma. Puede subir archivos PDF/imágenes o pegar enlaces de Google Drive.
            </p>
        </div>
    </div>
</div>

<!-- Tabla de Soportes Adjuntados -->
<?= view('documentacion/_components/tabla_soportes', [
    'soportes' => $documentosSSTAprobados ?? [],
    'titulo' => 'Soportes Adjuntados',
    'subtitulo' => 'Plan de Trabajo Anual',
    'icono' => 'bi-calendar-check',
    'colorHeader' => 'success',
    'codigoDefault' => 'SOP-PTA',
    'emptyIcon' => 'bi-file-earmark-x',
    'emptyMessage' => 'No hay soportes del Plan de Trabajo Anual adjuntados aún.',
    'emptyHint' => 'Use el botón "Adjuntar Soporte" para agregar evidencias.'
]) ?>

<!-- Subcarpetas (si las hay) -->
<div class="row">
    <?= view('documentacion/_components/lista_subcarpetas', [
        'subcarpetas' => $subcarpetas ?? []
    ]) ?>
</div>

<!-- Modal para Adjuntar Soporte PTA -->
<div class="modal fade" id="modalAdjuntarPTA" tabindex="-1" aria-labelledby="modalAdjuntarPTALabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalAdjuntarPTALabel">
                    <i class="bi bi-cloud-upload me-2"></i>Adjuntar Soporte Plan de Trabajo Anual
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formAdjuntarPTA" action="<?= base_url('documentos-sst/adjuntar-soporte-plan-trabajo-anual') ?>" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id_cliente" value="<?= $cliente['id_cliente'] ?>">
                    <input type="hidden" name="id_carpeta" value="<?= $carpeta['id_carpeta'] ?>">

                    <!-- Tipo de carga -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de carga</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="tipo_carga" id="tipoCargaPTAArchivo" value="archivo" checked>
                            <label class="btn btn-outline-primary" for="tipoCargaPTAArchivo">
                                <i class="bi bi-file-earmark-arrow-up me-1"></i>Subir Archivo
                            </label>
                            <input type="radio" class="btn-check" name="tipo_carga" id="tipoCargaPTAEnlace" value="enlace">
                            <label class="btn btn-outline-primary" for="tipoCargaPTAEnlace">
                                <i class="bi bi-link-45deg me-1"></i>Pegar Enlace
                            </label>
                        </div>
                    </div>

                    <!-- Campo para archivo -->
                    <div class="mb-3" id="campoPTAArchivo">
                        <label for="archivo_soporte_pta" class="form-label">
                            <i class="bi bi-file-earmark-pdf me-1"></i>Archivo (PDF, Excel, Word, Imagen)
                        </label>
                        <input type="file" class="form-control" id="archivo_soporte_pta" name="archivo_soporte"
                               accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx,.doc,.docx">
                        <div class="form-text">Formatos: PDF, JPG, PNG, Excel, Word. Máximo: 10MB</div>
                    </div>

                    <!-- Campo para enlace -->
                    <div class="mb-3 d-none" id="campoPTAEnlace">
                        <label for="url_externa_pta" class="form-label">
                            <i class="bi bi-link-45deg me-1"></i>Enlace (Google Drive, OneDrive, etc.)
                        </label>
                        <input type="url" class="form-control" id="url_externa_pta" name="url_externa"
                               placeholder="https://drive.google.com/...">
                        <div class="form-text">Pegue el enlace compartido del archivo en la nube</div>
                    </div>

                    <!-- Descripción -->
                    <div class="mb-3">
                        <label for="descripcion_pta" class="form-label">Descripción <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="descripcion_pta" name="descripcion" required
                               placeholder="Ej: Plan de Trabajo Anual 2026 firmado, Cronograma SST...">
                    </div>

                    <!-- Año -->
                    <div class="mb-3">
                        <label for="anio_pta" class="form-label">Año</label>
                        <select class="form-select" id="anio_pta" name="anio">
                            <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                <option value="<?= $y ?>" <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <!-- Observaciones -->
                    <div class="mb-3">
                        <label for="observaciones_pta" class="form-label">Observaciones (opcional)</label>
                        <textarea class="form-control" id="observaciones_pta" name="observaciones" rows="2"
                                  placeholder="Notas adicionales sobre el documento..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="btnAdjuntarPTA">
                        <i class="bi bi-cloud-upload me-1"></i>Adjuntar y Publicar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Toggle entre archivo y enlace
document.querySelectorAll('input[name="tipo_carga"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const isArchivo = this.value === 'archivo';
        document.getElementById('campoPTAArchivo').classList.toggle('d-none', !isArchivo);
        document.getElementById('campoPTAEnlace').classList.toggle('d-none', isArchivo);
        document.getElementById('archivo_soporte_pta').required = isArchivo;
        document.getElementById('url_externa_pta').required = !isArchivo;
        if (!isArchivo) {
            document.getElementById('archivo_soporte_pta').value = '';
        } else {
            document.getElementById('url_externa_pta').value = '';
        }
    });
});

// Manejar envío del formulario
document.getElementById('formAdjuntarPTA')?.addEventListener('submit', function() {
    const btn = document.getElementById('btnAdjuntarPTA');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Subiendo...';
});
</script>
