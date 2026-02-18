<?php
/**
 * Vista de Tipo: 2.5.1.1 Listado Maestro de Documentos Externos
 * Sub-carpeta de 2.5.1 para gestionar documentos externos del SG-SST
 * (Normativas, guias tecnicas, regulaciones, documentos de entidades externas)
 * Variables: $carpeta, $cliente, $soportesAdicionales, $subcarpetas
 */
?>

<!-- Card de Carpeta con Boton Adjuntar -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-7">
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
            <div class="col-md-5 text-end">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAdjuntarDocExterno">
                    <i class="bi bi-cloud-upload me-1"></i>Adjuntar Documento Externo
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Informacion sobre documentos externos -->
<div class="alert alert-info mb-4">
    <div class="d-flex align-items-start">
        <i class="bi bi-info-circle me-3 fs-4"></i>
        <div>
            <h6 class="mb-1">Listado Maestro de Documentos Externos</h6>
            <p class="mb-0 small">
                Registre aqui los documentos de origen externo que aplican al SG-SST de la empresa:
                normativas legales, guias tecnicas, circulares, resoluciones, documentos de la ARL,
                EPS, AFP, o cualquier entidad externa relevante.
            </p>
        </div>
    </div>
</div>

<!-- Tabla de Soportes (documentos externos adjuntados) -->
<?= view('documentacion/_components/tabla_soportes', [
    'soportes' => $soportesAdicionales ?? [],
    'titulo' => 'Documentos Externos del SG-SST',
    'subtitulo' => 'Normativas, guias y documentos de entidades externas',
    'icono' => 'bi-file-earmark-arrow-up',
    'colorHeader' => 'info',
    'columnaCodigoLabel' => 'Origen / Entidad',
    'codigoDefault' => 'Sin especificar',
    'emptyIcon' => 'bi-file-earmark-arrow-up',
    'emptyMessage' => 'No hay documentos externos registrados aun.',
    'emptyHint' => 'Use el boton "Adjuntar Documento Externo" para agregar normativas, guias u otros documentos externos.'
]) ?>

<!-- Subcarpetas (si las hay) -->
<div class="row">
    <?= view('documentacion/_components/lista_subcarpetas', [
        'subcarpetas' => $subcarpetas ?? []
    ]) ?>
</div>

<!-- Modal para Adjuntar Documento Externo -->
<div class="modal fade" id="modalAdjuntarDocExterno" tabindex="-1" aria-labelledby="modalAdjuntarDocExternoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalAdjuntarDocExternoLabel">
                    <i class="bi bi-cloud-upload me-2"></i>Adjuntar Documento Externo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formAdjuntarDocExterno" action="<?= base_url('documentos-sst/adjuntar-soporte-documento-externo') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <input type="hidden" name="id_cliente" value="<?= $cliente['id_cliente'] ?>">
                    <input type="hidden" name="id_carpeta" value="<?= $carpeta['id_carpeta'] ?>">

                    <!-- Tipo de carga -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de carga</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="tipo_carga" id="tipoCargaArchivoDext" value="archivo" checked>
                            <label class="btn btn-outline-info" for="tipoCargaArchivoDext">
                                <i class="bi bi-file-earmark-arrow-up me-1"></i>Subir Archivo
                            </label>
                            <input type="radio" class="btn-check" name="tipo_carga" id="tipoCargaEnlaceDext" value="enlace">
                            <label class="btn btn-outline-info" for="tipoCargaEnlaceDext">
                                <i class="bi bi-link-45deg me-1"></i>Pegar Enlace
                            </label>
                        </div>
                    </div>

                    <!-- Campo para archivo -->
                    <div class="mb-3" id="campoArchivoDext">
                        <label for="archivo_soporte_dext" class="form-label">
                            <i class="bi bi-file-earmark-pdf me-1"></i>Archivo (PDF, Excel, Imagen, Word)
                        </label>
                        <input type="file" class="form-control" id="archivo_soporte_dext" name="archivo_soporte"
                               accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx,.doc,.docx">
                        <div class="form-text">Formatos: PDF, JPG, PNG, Excel, Word. Maximo: 10MB</div>
                    </div>

                    <!-- Campo para enlace -->
                    <div class="mb-3 d-none" id="campoEnlaceDext">
                        <label for="url_externa_dext" class="form-label">
                            <i class="bi bi-link-45deg me-1"></i>Enlace (Google Drive, OneDrive, etc.)
                        </label>
                        <input type="url" class="form-control" id="url_externa_dext" name="url_externa"
                               placeholder="https://drive.google.com/...">
                        <div class="form-text">Pegue el enlace compartido del archivo en la nube</div>
                    </div>

                    <!-- Descripcion -->
                    <div class="mb-3">
                        <label for="descripcion_dext" class="form-label">Descripcion del documento <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="descripcion_dext" name="descripcion" required
                               placeholder="Ej: Resolucion 0312 de 2019, Guia tecnica ARL, Circular MinTrabajo...">
                    </div>

                    <!-- Origen del documento -->
                    <div class="mb-3">
                        <label for="origen_dext" class="form-label">Origen / Entidad emisora</label>
                        <input type="text" class="form-control" id="origen_dext" name="origen_fuente"
                               placeholder="Ej: Ministerio de Trabajo, ARL Sura, Secretaria de Salud...">
                        <div class="form-text">Entidad u organizacion que emite o proporciona el documento</div>
                    </div>

                    <!-- Ano -->
                    <div class="mb-3">
                        <label for="anio_dext" class="form-label">Ano</label>
                        <select class="form-select" id="anio_dext" name="anio">
                            <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                <option value="<?= $y ?>" <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <!-- Observaciones -->
                    <div class="mb-3">
                        <label for="observaciones_dext" class="form-label">Observaciones (opcional)</label>
                        <textarea class="form-control" id="observaciones_dext" name="observaciones" rows="2"
                                  placeholder="Notas adicionales: vigencia, aplicabilidad, requisitos..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info text-white" id="btnAdjuntarDext">
                        <i class="bi bi-cloud-upload me-1"></i>Adjuntar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Toggle entre archivo y enlace para el modal de documentos externos
document.querySelectorAll('#modalAdjuntarDocExterno input[name="tipo_carga"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const isArchivo = this.value === 'archivo';
        document.getElementById('campoArchivoDext').classList.toggle('d-none', !isArchivo);
        document.getElementById('campoEnlaceDext').classList.toggle('d-none', isArchivo);
        document.getElementById('archivo_soporte_dext').required = isArchivo;
        document.getElementById('url_externa_dext').required = !isArchivo;
        if (!isArchivo) document.getElementById('archivo_soporte_dext').value = '';
        if (isArchivo) document.getElementById('url_externa_dext').value = '';
    });
});

// Manejar envio del formulario
document.getElementById('formAdjuntarDocExterno')?.addEventListener('submit', function(e) {
    const btn = document.getElementById('btnAdjuntarDext');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Subiendo...';
});
</script>
