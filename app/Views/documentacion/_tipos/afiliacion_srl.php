<?php
/**
 * Vista de Tipo: 1.1.4 Afiliación al Sistema General de Riesgos Laborales
 * Carpeta para adjuntar soportes de planillas de afiliación
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
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAdjuntarPlanilla">
                    <i class="bi bi-cloud-upload me-1"></i>Adjuntar Planilla
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
            <h6 class="mb-1">Soportes de Afiliación al Sistema General de Riesgos Laborales</h6>
            <p class="mb-0 small">
                Adjunte las planillas de pago de seguridad social (ARL, EPS, AFP) como soporte de cumplimiento.
                Puede subir archivos PDF/imágenes o pegar enlaces de Google Drive para archivos pesados.
            </p>
        </div>
    </div>
</div>

<!-- Tabla de Planillas Adjuntadas -->
<?= view('documentacion/_components/tabla_soportes', [
    'soportes' => $documentosSSTAprobados ?? [],
    'titulo' => 'Planillas Adjuntadas',
    'subtitulo' => 'Afiliacion al Sistema General de Riesgos Laborales',
    'icono' => 'bi-file-earmark-check',
    'colorHeader' => 'success',
    'codigoDefault' => 'PLA-SRL',
    'emptyIcon' => 'bi-file-earmark-x',
    'emptyMessage' => 'No hay planillas adjuntadas aun.',
    'emptyHint' => 'Use el boton "Adjuntar Planilla" para agregar soportes.'
]) ?>

<!-- Subcarpetas (si las hay) -->
<div class="row">
    <?= view('documentacion/_components/lista_subcarpetas', [
        'subcarpetas' => $subcarpetas ?? []
    ]) ?>
</div>

<!-- Modal para Adjuntar Planilla -->
<div class="modal fade" id="modalAdjuntarPlanilla" tabindex="-1" aria-labelledby="modalAdjuntarPlanillaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalAdjuntarPlanillaLabel">
                    <i class="bi bi-cloud-upload me-2"></i>Adjuntar Planilla de Afiliación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formAdjuntarPlanilla" action="<?= base_url('documentos-sst/adjuntar-planilla-srl') ?>" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id_cliente" value="<?= $cliente['id_cliente'] ?>">
                    <input type="hidden" name="id_carpeta" value="<?= $carpeta['id_carpeta'] ?>">

                    <!-- Tipo de carga -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de carga</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="tipo_carga" id="tipoCargaArchivo" value="archivo" checked>
                            <label class="btn btn-outline-primary" for="tipoCargaArchivo">
                                <i class="bi bi-file-earmark-arrow-up me-1"></i>Subir Archivo
                            </label>
                            <input type="radio" class="btn-check" name="tipo_carga" id="tipoCargaEnlace" value="enlace">
                            <label class="btn btn-outline-primary" for="tipoCargaEnlace">
                                <i class="bi bi-link-45deg me-1"></i>Pegar Enlace
                            </label>
                        </div>
                    </div>

                    <!-- Campo para archivo -->
                    <div class="mb-3" id="campoArchivo">
                        <label for="archivo_planilla" class="form-label">
                            <i class="bi bi-file-earmark-pdf me-1"></i>Archivo (PDF, Excel, Imagen)
                        </label>
                        <input type="file" class="form-control" id="archivo_planilla" name="archivo_planilla"
                               accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx">
                        <div class="form-text">Formatos: PDF, JPG, PNG, Excel. Máximo: 10MB</div>
                    </div>

                    <!-- Campo para enlace -->
                    <div class="mb-3 d-none" id="campoEnlace">
                        <label for="url_externa" class="form-label">
                            <i class="bi bi-link-45deg me-1"></i>Enlace (Google Drive, OneDrive, etc.)
                        </label>
                        <input type="url" class="form-control" id="url_externa" name="url_externa"
                               placeholder="https://drive.google.com/...">
                        <div class="form-text">Pegue el enlace compartido del archivo en la nube</div>
                    </div>

                    <!-- Descripción -->
                    <div class="mb-3">
                        <label for="descripcion_planilla" class="form-label">Descripción</label>
                        <input type="text" class="form-control" id="descripcion_planilla" name="descripcion" required
                               placeholder="Ej: Planilla PILA Enero 2026, Certificado ARL...">
                    </div>

                    <!-- Observaciones -->
                    <div class="mb-3">
                        <label for="observaciones_planilla" class="form-label">Observaciones (opcional)</label>
                        <textarea class="form-control" id="observaciones_planilla" name="observaciones" rows="2"
                                  placeholder="Notas adicionales sobre el documento..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="btnAdjuntarPlanilla">
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
        const campoArchivo = document.getElementById('campoArchivo');
        const campoEnlace = document.getElementById('campoEnlace');
        const inputArchivo = document.getElementById('archivo_planilla');
        const inputEnlace = document.getElementById('url_externa');

        if (this.value === 'archivo') {
            campoArchivo.classList.remove('d-none');
            campoEnlace.classList.add('d-none');
            inputArchivo.required = true;
            inputEnlace.required = false;
            inputEnlace.value = '';
        } else {
            campoArchivo.classList.add('d-none');
            campoEnlace.classList.remove('d-none');
            inputArchivo.required = false;
            inputEnlace.required = true;
        }
    });
});

// Manejar envío del formulario
document.getElementById('formAdjuntarPlanilla')?.addEventListener('submit', function(e) {
    const btn = document.getElementById('btnAdjuntarPlanilla');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Subiendo...';
});
</script>
