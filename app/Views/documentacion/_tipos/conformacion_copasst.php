<?php
/**
 * Vista de Tipo: 1.1.6 Conformación COPASST
 * Carpeta para gestionar la conformación del COPASST
 * Incluye: Sistema de elecciones + adjuntar soportes manuales
 * Variables: $carpeta, $cliente, $documentosSSTAprobados
 */

// Determinar si la empresa requiere COPASST o Vigía
$numTrabajadores = $cliente['trabajadores'] ?? 10;
$requiereCopasst = $numTrabajadores >= 10;
$tipoComiteRequerido = $requiereCopasst ? 'COPASST' : 'VIGIA';
?>

<!-- Card de Carpeta con Botones -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-6">
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
            <div class="col-md-6 text-end">
                <!-- Botón principal: Sistema de Conformación -->
                <a href="<?= base_url('comites-elecciones/' . $cliente['id_cliente']) ?>" class="btn btn-success me-2">
                    <i class="bi bi-people-fill me-1"></i>Sistema de Conformacion
                </a>
                <!-- Botón secundario: Adjuntar Soporte -->
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalAdjuntarCopasst">
                    <i class="bi bi-cloud-upload me-1"></i>Adjuntar Soporte
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Información sobre soportes manuales -->
<div class="alert alert-secondary mb-4">
    <div class="d-flex align-items-start">
        <i class="bi bi-paperclip me-3 fs-4"></i>
        <div>
            <h6 class="mb-1">Soportes Adicionales</h6>
            <p class="mb-0 small">
                Use esta seccion para adjuntar soportes adicionales de conformacion del COPASST:
                convocatorias fisicas, actas escaneadas, capacitaciones de miembros, etc.
            </p>
        </div>
    </div>
</div>

<!-- Tabla de Soportes Adjuntados -->
<?= view('documentacion/_components/tabla_soportes', [
    'soportes' => $documentosSSTAprobados ?? [],
    'titulo' => 'Soportes COPASST',
    'subtitulo' => 'Documentos de conformacion',
    'icono' => 'bi-people-fill',
    'colorHeader' => 'primary',
    'codigoDefault' => 'SOP-COPASST',
    'emptyIcon' => 'bi-people',
    'emptyMessage' => 'No hay soportes de COPASST adjuntados aun.',
    'emptyHint' => 'Use el boton "Adjuntar Soporte" para agregar documentos.'
]) ?>

<!-- Subcarpetas (si las hay) -->
<div class="row">
    <?= view('documentacion/_components/lista_subcarpetas', [
        'subcarpetas' => $subcarpetas ?? []
    ]) ?>
</div>

<!-- Modal para Adjuntar Soporte COPASST -->
<div class="modal fade" id="modalAdjuntarCopasst" tabindex="-1" aria-labelledby="modalAdjuntarCopasstLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalAdjuntarCopasstLabel">
                    <i class="bi bi-cloud-upload me-2"></i>Adjuntar Soporte COPASST
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formAdjuntarCopasst" action="<?= base_url('documentos-sst/adjuntar-soporte-copasst') ?>" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id_cliente" value="<?= $cliente['id_cliente'] ?>">
                    <input type="hidden" name="id_carpeta" value="<?= $carpeta['id_carpeta'] ?>">

                    <!-- Tipo de carga -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de carga</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="tipo_carga" id="tipoCargaArchivoCopasst" value="archivo" checked>
                            <label class="btn btn-outline-primary" for="tipoCargaArchivoCopasst">
                                <i class="bi bi-file-earmark-arrow-up me-1"></i>Subir Archivo
                            </label>
                            <input type="radio" class="btn-check" name="tipo_carga" id="tipoCargaEnlaceCopasst" value="enlace">
                            <label class="btn btn-outline-primary" for="tipoCargaEnlaceCopasst">
                                <i class="bi bi-link-45deg me-1"></i>Pegar Enlace
                            </label>
                        </div>
                    </div>

                    <!-- Campo para archivo -->
                    <div class="mb-3" id="campoArchivoCopasst">
                        <label for="archivo_copasst" class="form-label">
                            <i class="bi bi-file-earmark-pdf me-1"></i>Archivo (PDF, Excel, Imagen)
                        </label>
                        <input type="file" class="form-control" id="archivo_copasst" name="archivo_soporte"
                               accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx,.doc,.docx">
                        <div class="form-text">Formatos: PDF, JPG, PNG, Excel, Word. Máximo: 10MB</div>
                    </div>

                    <!-- Campo para enlace -->
                    <div class="mb-3 d-none" id="campoEnlaceCopasst">
                        <label for="url_externa_copasst" class="form-label">
                            <i class="bi bi-link-45deg me-1"></i>Enlace (Google Drive, OneDrive, etc.)
                        </label>
                        <input type="url" class="form-control" id="url_externa_copasst" name="url_externa"
                               placeholder="https://drive.google.com/...">
                        <div class="form-text">Pegue el enlace compartido del archivo en la nube</div>
                    </div>

                    <!-- Descripción -->
                    <div class="mb-3">
                        <label for="descripcion_copasst" class="form-label">Descripción</label>
                        <input type="text" class="form-control" id="descripcion_copasst" name="descripcion" required
                               placeholder="Ej: Acta conformación COPASST 2026, Elección representantes...">
                    </div>

                    <!-- Año -->
                    <div class="mb-3">
                        <label for="anio_copasst" class="form-label">Año</label>
                        <select class="form-select" id="anio_copasst" name="anio">
                            <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                <option value="<?= $y ?>" <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <!-- Observaciones -->
                    <div class="mb-3">
                        <label for="observaciones_copasst" class="form-label">Observaciones (opcional)</label>
                        <textarea class="form-control" id="observaciones_copasst" name="observaciones" rows="2"
                                  placeholder="Notas adicionales sobre el documento..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnAdjuntarCopasst">
                        <i class="bi bi-cloud-upload me-1"></i>Adjuntar
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
        document.getElementById('campoArchivoCopasst').classList.toggle('d-none', !isArchivo);
        document.getElementById('campoEnlaceCopasst').classList.toggle('d-none', isArchivo);
        document.getElementById('archivo_copasst').required = isArchivo;
        document.getElementById('url_externa_copasst').required = !isArchivo;
        if (!isArchivo) document.getElementById('archivo_copasst').value = '';
        if (isArchivo) document.getElementById('url_externa_copasst').value = '';
    });
});

// Manejar envío del formulario
document.getElementById('formAdjuntarCopasst')?.addEventListener('submit', function(e) {
    const btn = document.getElementById('btnAdjuntarCopasst');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Subiendo...';
});
</script>
