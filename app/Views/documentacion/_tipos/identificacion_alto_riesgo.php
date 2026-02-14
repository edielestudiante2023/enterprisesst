<?php
/**
 * Vista de Tipo: 1.1.5 Identificacion de Trabajadores de Alto Riesgo
 * Carpeta con dropdown para documentos de alto riesgo y pension especial
 * + Funcionalidad de adjuntar soportes (archivos/enlaces)
 * Variables: $carpeta, $cliente, $fasesInfo, $documentosSSTAprobados, $soportesAdicionales, $contextoCliente
 */

// Detectar documentos existentes del ano actual para el dropdown
$docsExistentesTipos = [];
if (!empty($documentosSSTAprobados)) {
    foreach ($documentosSSTAprobados as $d) {
        if ($d['anio'] == date('Y')) {
            $docsExistentesTipos[$d['tipo_documento']] = true;
        }
    }
}
?>

<!-- Card de Carpeta con Dropdown de Documentos -->
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
                <!-- Boton Adjuntar Soporte -->
                <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#modalAdjuntarAltoRiesgo">
                    <i class="bi bi-cloud-upload me-1"></i>Adjuntar Soporte
                </button>

                <!-- Dropdown Nuevo Procedimiento -->
                <?php if (isset($fasesInfo) && $fasesInfo && !$fasesInfo['puede_generar_documento']): ?>
                    <button type="button" class="btn btn-secondary" disabled title="Complete las fases previas">
                        <i class="bi bi-lock me-1"></i>Nuevo Procedimiento
                    </button>
                <?php else: ?>
                    <div class="dropdown d-inline-block">
                        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-plus-lg me-1"></i>Nuevo Procedimiento
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if (!isset($docsExistentesTipos['identificacion_alto_riesgo'])): ?>
                            <li>
                                <a href="<?= base_url('documentos/generar/identificacion_alto_riesgo/' . $cliente['id_cliente']) ?>" class="dropdown-item">
                                    <i class="bi bi-exclamation-diamond me-2 text-warning"></i>Procedimiento Identificacion Alto Riesgo
                                </a>
                            </li>
                            <?php endif; ?>
                            <?php if (isset($docsExistentesTipos['identificacion_alto_riesgo'])): ?>
                            <li><span class="dropdown-item text-muted"><i class="bi bi-check-circle me-2"></i>Procedimiento creado <?= date('Y') ?></span></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Informacion del Marco Normativo -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-light">
        <h6 class="mb-0"><i class="bi bi-journal-text me-2"></i>Marco Normativo Aplicable</h6>
    </div>
    <div class="card-body">
        <ul class="list-unstyled mb-0">
            <li class="mb-2">
                <i class="bi bi-check-circle text-success me-2"></i>
                <strong>Decreto 2090 de 2003:</strong> Define actividades de alto riesgo para pension especial
            </li>
            <li class="mb-2">
                <i class="bi bi-check-circle text-success me-2"></i>
                <strong>Resolucion 0312 de 2019:</strong> Estandares Minimos del SG-SST (Estandar 1.1.5)
            </li>
            <li>
                <i class="bi bi-check-circle text-success me-2"></i>
                <strong>Decreto 1072 de 2015:</strong> Decreto Unico Reglamentario del Sector Trabajo
            </li>
        </ul>
    </div>
</div>

<!-- Panel de Fases -->
<?= view('documentacion/_components/panel_fases', [
    'fasesInfo' => $fasesInfo ?? null,
    'tipoCarpetaFases' => 'identificacion_alto_riesgo',
    'cliente' => $cliente,
    'carpeta' => $carpeta,
    'documentoExistente' => $documentoExistente ?? null
]) ?>

<!-- Tabla de Procedimientos SST (documentos generados) -->
<?= view('documentacion/_components/tabla_documentos_sst', [
    'tipoCarpetaFases' => 'identificacion_alto_riesgo',
    'documentosSSTAprobados' => $documentosSSTAprobados ?? [],
    'cliente' => $cliente
]) ?>

<!-- Tabla de Soportes Adjuntados -->
<?= view('documentacion/_components/tabla_soportes', [
    'soportes' => $soportesAdicionales ?? [],
    'titulo' => 'Soportes de Identificacion Alto Riesgo',
    'subtitulo' => 'Listados de trabajadores y documentos adjuntos',
    'icono' => 'bi-exclamation-triangle',
    'colorHeader' => 'warning',
    'codigoDefault' => 'SOP-AR',
    'emptyIcon' => 'bi-people',
    'emptyMessage' => 'No hay soportes adjuntados aun.',
    'emptyHint' => 'Use el boton "Adjuntar Soporte" para agregar listados de trabajadores u otros documentos.'
]) ?>

<!-- Subcarpetas -->
<div class="row">
    <?= view('documentacion/_components/lista_subcarpetas', [
        'subcarpetas' => $subcarpetas ?? []
    ]) ?>
</div>

<!-- Modal para Adjuntar Soporte Alto Riesgo -->
<div class="modal fade" id="modalAdjuntarAltoRiesgo" tabindex="-1" aria-labelledby="modalAdjuntarAltoRiesgoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalAdjuntarAltoRiesgoLabel">
                    <i class="bi bi-cloud-upload me-2"></i>Adjuntar Soporte Alto Riesgo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formAdjuntarAltoRiesgo" action="<?= base_url('documentos-sst/adjuntar-soporte-alto-riesgo') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <input type="hidden" name="id_cliente" value="<?= $cliente['id_cliente'] ?>">
                    <input type="hidden" name="id_carpeta" value="<?= $carpeta['id_carpeta'] ?>">

                    <!-- Informacion -->
                    <div class="alert alert-info mb-3">
                        <small>
                            <i class="bi bi-info-circle me-1"></i>
                            Adjunte el listado de trabajadores identificados con actividades de alto riesgo segun Decreto 2090 de 2003.
                            Puede subir archivos Excel, PDF o pegar enlaces de Google Drive.
                        </small>
                    </div>

                    <!-- Tipo de carga -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de carga</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="tipo_carga" id="tipoCargaArchivoAR" value="archivo" checked>
                            <label class="btn btn-outline-primary" for="tipoCargaArchivoAR">
                                <i class="bi bi-file-earmark-arrow-up me-1"></i>Subir Archivo
                            </label>
                            <input type="radio" class="btn-check" name="tipo_carga" id="tipoCargaEnlaceAR" value="enlace">
                            <label class="btn btn-outline-primary" for="tipoCargaEnlaceAR">
                                <i class="bi bi-link-45deg me-1"></i>Pegar Enlace
                            </label>
                        </div>
                    </div>

                    <!-- Campo para archivo -->
                    <div class="mb-3" id="campoArchivoAR">
                        <label for="archivo_soporte_ar" class="form-label">
                            <i class="bi bi-file-earmark-pdf me-1"></i>Archivo (PDF, Excel, Imagen, Word)
                        </label>
                        <input type="file" class="form-control" id="archivo_soporte_ar" name="archivo_soporte"
                               accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx,.doc,.docx">
                        <div class="form-text">Formatos: PDF, JPG, PNG, Excel, Word. Maximo: 10MB</div>
                    </div>

                    <!-- Campo para enlace -->
                    <div class="mb-3 d-none" id="campoEnlaceAR">
                        <label for="url_externa_ar" class="form-label">
                            <i class="bi bi-link-45deg me-1"></i>Enlace (Google Drive, OneDrive, etc.)
                        </label>
                        <input type="url" class="form-control" id="url_externa_ar" name="url_externa"
                               placeholder="https://drive.google.com/...">
                        <div class="form-text">Pegue el enlace compartido del archivo en la nube</div>
                    </div>

                    <!-- Descripcion -->
                    <div class="mb-3">
                        <label for="descripcion_ar" class="form-label">Descripcion <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="descripcion_ar" name="descripcion" required
                               placeholder="Ej: Listado trabajadores alto riesgo 2026, Certificado pension especial...">
                    </div>

                    <!-- Ano -->
                    <div class="mb-3">
                        <label for="anio_ar" class="form-label">Ano</label>
                        <select class="form-select" id="anio_ar" name="anio">
                            <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                <option value="<?= $y ?>" <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <!-- Observaciones -->
                    <div class="mb-3">
                        <label for="observaciones_ar" class="form-label">Observaciones (opcional)</label>
                        <textarea class="form-control" id="observaciones_ar" name="observaciones" rows="2"
                                  placeholder="Notas adicionales: cantidad de trabajadores, actividades identificadas..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="btnAdjuntarAR">
                        <i class="bi bi-cloud-upload me-1"></i>Adjuntar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Toggle entre archivo y enlace para el modal de alto riesgo
document.querySelectorAll('#modalAdjuntarAltoRiesgo input[name="tipo_carga"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const isArchivo = this.value === 'archivo';
        document.getElementById('campoArchivoAR').classList.toggle('d-none', !isArchivo);
        document.getElementById('campoEnlaceAR').classList.toggle('d-none', isArchivo);
        document.getElementById('archivo_soporte_ar').required = isArchivo;
        document.getElementById('url_externa_ar').required = !isArchivo;
        if (!isArchivo) document.getElementById('archivo_soporte_ar').value = '';
        if (isArchivo) document.getElementById('url_externa_ar').value = '';
    });
});

// Manejar envio del formulario
document.getElementById('formAdjuntarAltoRiesgo')?.addEventListener('submit', function(e) {
    const btn = document.getElementById('btnAdjuntarAR');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Subiendo...';
});
</script>
