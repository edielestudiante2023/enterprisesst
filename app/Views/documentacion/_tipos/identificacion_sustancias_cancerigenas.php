<?php
/**
 * Vista de Tipo: 4.1.3 Identificacion de Sustancias Cancerigenas o con Toxicidad Aguda
 * Carpeta con dropdown dual: procedimiento (IA) o certificacion de NO maneja (auto-generada).
 * Variables: $carpeta, $cliente, $documentosSSTAprobados, $soportesAdicionales, $subcarpetas
 */

// Detectar documentos existentes del anio actual para condicionar el dropdown
$docsExistentesTipos = [];
if (!empty($documentosSSTAprobados)) {
    foreach ($documentosSSTAprobados as $d) {
        if (($d['anio'] ?? '') == date('Y')) {
            $docsExistentesTipos[$d['tipo_documento']] = true;
        }
    }
}
?>

<!-- Card de Carpeta con Dropdown -->
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
                <button type="button" class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#modalAdjuntarSoporteISC">
                    <i class="bi bi-cloud-upload me-1"></i>Adjuntar Soporte
                </button>
                <div class="dropdown d-inline-block">
                    <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-plus-lg me-1"></i>Nuevo Documento
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <?php if (!isset($docsExistentesTipos['identificacion_sustancias_cancerigenas'])): ?>
                        <li>
                            <a href="<?= base_url('documentos/generar/identificacion_sustancias_cancerigenas/' . $cliente['id_cliente']) ?>" class="dropdown-item">
                                <i class="bi bi-radioactive me-2 text-warning"></i>Procedimiento Identificacion (IA)
                            </a>
                        </li>
                        <?php else: ?>
                        <li><span class="dropdown-item text-muted"><i class="bi bi-check-circle me-2"></i>Procedimiento creado <?= date('Y') ?></span></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <?php if (!isset($docsExistentesTipos['certificacion_no_sustancias_cancerigenas'])): ?>
                        <li>
                            <form action="<?= base_url('documentos-sst/' . $cliente['id_cliente'] . '/crear-certificacion-sustancias-cancerigenas') ?>" method="post" style="display:inline;">
                                <button type="submit" class="dropdown-item">
                                    <i class="bi bi-file-earmark-check me-2 text-success"></i>Certificacion No Sustancias Cancerigenas
                                </button>
                            </form>
                        </li>
                        <?php else: ?>
                        <li><span class="dropdown-item text-muted"><i class="bi bi-check-circle me-2"></i>Certificacion creada <?= date('Y') ?></span></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Marco normativo aplicable -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-light">
        <h6 class="mb-0"><i class="bi bi-journal-text me-2"></i>Marco Normativo Aplicable</h6>
    </div>
    <div class="card-body">
        <ul class="list-unstyled mb-0 small">
            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i><strong>Resolucion 0312/2019:</strong> Estandar 4.1.3 — Identificacion de sustancias cancerigenas o con toxicidad aguda</li>
            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i><strong>Decreto 1496/2018:</strong> Sistema Globalmente Armonizado de Clasificacion (SGA / GHS)</li>
            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i><strong>Resolucion 2400/1979:</strong> Estatuto de Seguridad Industrial</li>
            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i><strong>Decreto 1072/2015:</strong> Decreto Unico Reglamentario del Sector Trabajo</li>
            <li><i class="bi bi-check-circle text-success me-2"></i><strong>IARC:</strong> Clasificacion de cancerigenos (Grupos 1, 2A, 2B)</li>
        </ul>
    </div>
</div>

<!-- Informacion sobre el modulo -->
<div class="alert alert-warning mb-4">
    <div class="d-flex align-items-start">
        <i class="bi bi-radioactive me-3 fs-4"></i>
        <div>
            <h6 class="mb-1">Identificacion de Sustancias Cancerigenas o con Toxicidad Aguda</h6>
            <p class="mb-0 small">
                Documento que identifica si <?= esc($cliente['nombre_cliente'] ?? 'la organizacion') ?>
                procesa, manipula o trabaja con agentes o sustancias catalogadas como cancerigenas o con toxicidad aguda,
                causantes de enfermedades incluidas en la Tabla de Enfermedades Laborales (Decreto 1477/2014),
                priorizando los riesgos asociados y estableciendo acciones de prevencion e intervencion.
                Cumplimiento del estandar 4.1.3 de la Resolucion 0312/2019.
            </p>
        </div>
    </div>
</div>

<!-- Tabla de Documentos SST -->
<?= view('documentacion/_components/tabla_documentos_sst', [
    'tipoCarpetaFases' => 'identificacion_sustancias_cancerigenas',
    'documentosSSTAprobados' => $documentosSSTAprobados ?? [],
    'cliente' => $cliente
]) ?>

<!-- Subcarpetas -->
<?php if (!empty($subcarpetas)): ?>
<div class="row">
    <?= view('documentacion/_components/lista_subcarpetas', ['subcarpetas' => $subcarpetas ?? []]) ?>
</div>
<?php endif; ?>

<!-- ============================================================ -->
<!-- SECCION ADICIONAL: SOPORTES (ReportList)                     -->
<!-- ============================================================ -->

<hr class="my-5">

<!-- Card de Soportes Adicionales -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5 class="mb-1">
                    <i class="bi bi-file-earmark-plus text-primary me-2"></i>
                    Soportes Adicionales
                </h5>
                <p class="text-muted mb-0 small">
                    Adjunte evidencias: inventario de sustancias, Fichas de Datos de Seguridad (FDS),
                    resultados de mediciones ambientales, certificados de capacitacion en riesgo quimico u otros soportes.
                </p>
            </div>
            <div class="col-md-4 text-end">
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalAdjuntarSoporteISC">
                    <i class="bi bi-cloud-upload me-1"></i>Adjuntar Soporte
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Soportes Adjuntados -->
<?= view('documentacion/_components/tabla_soportes', [
    'soportes' => $soportesAdicionales ?? [],
    'titulo' => 'Soportes Sustancias Cancerigenas',
    'subtitulo' => 'Sustancias con toxicidad aguda',
    'icono' => 'bi-radioactive',
    'colorHeader' => 'warning',
    'codigoDefault' => 'SOP-CAN',
    'emptyIcon' => 'bi-radioactive',
    'emptyMessage' => 'No hay soportes adjuntados aun.',
    'emptyHint' => 'Use el boton "Adjuntar Soporte" para agregar evidencias.'
]) ?>

<!-- Modal de Adjuntar Soporte -->
<div class="modal fade" id="modalAdjuntarSoporteISC" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-cloud-upload me-2"></i>Adjuntar Soporte - Sustancias Cancerigenas
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="formAdjuntarSoporteISC"
                  action="<?= base_url('documentos-sst/adjuntar-soporte-sustancias-cancerigenas') ?>"
                  method="post"
                  enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <input type="hidden" name="id_cliente" value="<?= $cliente['id_cliente'] ?>">
                    <input type="hidden" name="id_carpeta" value="<?= $carpeta['id_carpeta'] ?>">

                    <!-- Toggle Archivo/Enlace -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de carga</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="tipo_carga_isc"
                                   id="tipoCargaISCArchivo" value="archivo" checked>
                            <label class="btn btn-outline-primary" for="tipoCargaISCArchivo">
                                <i class="bi bi-file-earmark-arrow-up me-1"></i>Subir Archivo
                            </label>
                            <input type="radio" class="btn-check" name="tipo_carga_isc"
                                   id="tipoCargaISCEnlace" value="enlace">
                            <label class="btn btn-outline-primary" for="tipoCargaISCEnlace">
                                <i class="bi bi-link-45deg me-1"></i>Pegar Enlace
                            </label>
                        </div>
                    </div>

                    <!-- Campo Archivo -->
                    <div class="mb-3" id="campoArchivoISC">
                        <label for="archivo_soporte_isc" class="form-label">Archivo</label>
                        <input type="file" class="form-control" id="archivo_soporte_isc" name="archivo_soporte"
                               accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx,.doc,.docx">
                        <div class="form-text">PDF, JPG, PNG, Excel, Word. Max: 10MB</div>
                    </div>

                    <!-- Campo Enlace (oculto por defecto) -->
                    <div class="mb-3 d-none" id="campoEnlaceISC">
                        <label for="url_externa_isc" class="form-label">Enlace</label>
                        <input type="url" class="form-control" id="url_externa_isc" name="url_externa"
                               placeholder="https://drive.google.com/...">
                    </div>

                    <!-- Descripcion -->
                    <div class="mb-3">
                        <label for="descripcion_isc" class="form-label">Descripcion <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="descripcion_isc" name="descripcion" required
                               placeholder="Ej: Inventario sustancias, FDS benceno, Medicion ambiental...">
                    </div>

                    <!-- Ano -->
                    <div class="mb-3">
                        <label for="anio_isc" class="form-label">Ano</label>
                        <select class="form-select" id="anio_isc" name="anio">
                            <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                <option value="<?= $y ?>" <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <!-- Observaciones -->
                    <div class="mb-3">
                        <label for="observaciones_isc" class="form-label">Observaciones (opcional)</label>
                        <textarea class="form-control" id="observaciones_isc" name="observaciones" rows="2"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnAdjuntarISC">
                        <i class="bi bi-cloud-upload me-1"></i>Adjuntar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript para Toggle Archivo/Enlace -->
<script>
document.querySelectorAll('input[name="tipo_carga_isc"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const isArchivo = this.value === 'archivo';
        document.getElementById('campoArchivoISC').classList.toggle('d-none', !isArchivo);
        document.getElementById('campoEnlaceISC').classList.toggle('d-none', isArchivo);
        document.getElementById('archivo_soporte_isc').required = isArchivo;
        document.getElementById('url_externa_isc').required = !isArchivo;
        if (!isArchivo) {
            document.getElementById('archivo_soporte_isc').value = '';
        } else {
            document.getElementById('url_externa_isc').value = '';
        }
    });
});

document.getElementById('formAdjuntarSoporteISC')?.addEventListener('submit', function() {
    const btn = document.getElementById('btnAdjuntarISC');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Subiendo...';
});
</script>
