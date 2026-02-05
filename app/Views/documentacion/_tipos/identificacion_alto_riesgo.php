<?php
/**
 * Vista de Tipo: 1.1.5 Identificación de Trabajadores de Alto Riesgo
 * Carpeta con dropdown para documentos de alto riesgo y pensión especial
 * + Funcionalidad de carga de listados de trabajadores (archivos/enlaces)
 * Variables: $carpeta, $cliente, $fasesInfo, $documentosSSTAprobados, $contextoCliente
 */

// Separar documentos por tipo
$procedimientos = [];
$listadosTrabajadores = [];
$docsExistentesTipos = [];

if (!empty($documentosSSTAprobados)) {
    foreach ($documentosSSTAprobados as $d) {
        if ($d['tipo_documento'] === 'listado_trabajadores_alto_riesgo') {
            $listadosTrabajadores[] = $d;
        } else {
            $procedimientos[] = $d;
        }
        if ($d['anio'] == date('Y')) {
            $docsExistentesTipos[$d['tipo_documento']] = true;
        }
    }
}
$totalEsperado = 1; // Solo 1 procedimiento para esta carpeta
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
                <!-- Botón Adjuntar Listado -->
                <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#modalAdjuntarListado">
                    <i class="bi bi-cloud-upload me-1"></i>Adjuntar Listado
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
                                    <i class="bi bi-exclamation-diamond me-2 text-warning"></i>Procedimiento Identificación Alto Riesgo
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
                <strong>Decreto 2090 de 2003:</strong> Define actividades de alto riesgo para pensión especial
            </li>
            <li class="mb-2">
                <i class="bi bi-check-circle text-success me-2"></i>
                <strong>Resolución 0312 de 2019:</strong> Estándares Mínimos del SG-SST (Estándar 1.1.5)
            </li>
            <li>
                <i class="bi bi-check-circle text-success me-2"></i>
                <strong>Decreto 1072 de 2015:</strong> Decreto Único Reglamentario del Sector Trabajo
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
    'documentosSSTAprobados' => $procedimientos ?? [],
    'cliente' => $cliente
]) ?>

<!-- Tabla de Listados de Trabajadores Adjuntados -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-warning text-dark">
        <h6 class="mb-0">
            <i class="bi bi-people-fill me-2"></i>Listados de Trabajadores de Alto Riesgo
        </h6>
    </div>
    <div class="card-body">
        <?php if (!empty($listadosTrabajadores)): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 120px;">Código</th>
                            <th>Descripción</th>
                            <th style="width: 80px;">Año</th>
                            <th style="width: 100px;">Fecha</th>
                            <th style="width: 100px;">Tipo</th>
                            <th style="width: 150px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($listadosTrabajadores as $listado): ?>
                            <?php
                            $esEnlace = !empty($listado['url_externa']);
                            $urlArchivo = $esEnlace ? $listado['url_externa'] : ($listado['archivo_pdf'] ?? '#');
                            ?>
                            <tr>
                                <td><code><?= esc($listado['codigo'] ?? 'LST-AR') ?></code></td>
                                <td>
                                    <strong><?= esc($listado['titulo']) ?></strong>
                                    <?php if (!empty($listado['observaciones'])): ?>
                                        <br><small class="text-muted"><?= esc($listado['observaciones']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-secondary"><?= esc($listado['anio']) ?></span></td>
                                <td>
                                    <small><?= date('d/m/Y', strtotime($listado['created_at'] ?? $listado['fecha_aprobacion'] ?? 'now')) ?></small>
                                </td>
                                <td>
                                    <?php if ($esEnlace): ?>
                                        <span class="badge bg-primary"><i class="bi bi-link-45deg me-1"></i>Enlace</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><i class="bi bi-file-earmark me-1"></i>Archivo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= esc($urlArchivo) ?>" class="btn btn-outline-primary" target="_blank" title="Ver/Descargar">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <?php if ($esEnlace): ?>
                                        <a href="<?= esc($urlArchivo) ?>" class="btn btn-outline-info" target="_blank" title="Abrir enlace externo">
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                        <?php else: ?>
                                        <a href="<?= esc($urlArchivo) ?>" class="btn btn-danger" download title="Descargar">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        <?php endif; ?>
                                        <!-- Botón publicar en reportList -->
                                        <a href="<?= base_url('documentos-sst/publicar-pdf/' . $listado['id_documento']) ?>"
                                           class="btn btn-outline-dark" title="Publicar en Reportes"
                                           onclick="return confirm('¿Publicar este listado en Reportes?')">
                                            <i class="bi bi-cloud-upload"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-4">
                <i class="bi bi-people text-muted" style="font-size: 2.5rem;"></i>
                <p class="text-muted mt-2 mb-0">No hay listados de trabajadores adjuntados aún.</p>
                <small class="text-muted">Use el botón "Adjuntar Listado" para agregar soportes de trabajadores identificados.</small>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Subcarpetas -->
<div class="row">
    <?= view('documentacion/_components/lista_subcarpetas', [
        'subcarpetas' => $subcarpetas ?? []
    ]) ?>
</div>

<!-- Modal para Adjuntar Listado de Trabajadores -->
<div class="modal fade" id="modalAdjuntarListado" tabindex="-1" aria-labelledby="modalAdjuntarListadoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="modalAdjuntarListadoLabel">
                    <i class="bi bi-cloud-upload me-2"></i>Adjuntar Listado de Trabajadores Alto Riesgo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formAdjuntarListado" action="<?= base_url('documentos-sst/adjuntar-listado-alto-riesgo') ?>" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id_cliente" value="<?= $cliente['id_cliente'] ?>">
                    <input type="hidden" name="id_carpeta" value="<?= $carpeta['id_carpeta'] ?>">

                    <!-- Información -->
                    <div class="alert alert-info mb-3">
                        <small>
                            <i class="bi bi-info-circle me-1"></i>
                            Adjunte el listado de trabajadores identificados con actividades de alto riesgo según Decreto 2090 de 2003.
                            Puede subir archivos Excel, PDF o pegar enlaces de Google Drive.
                        </small>
                    </div>

                    <!-- Tipo de carga -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de carga</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="tipo_carga" id="tipoCargaArchivoAR" value="archivo" checked>
                            <label class="btn btn-outline-warning" for="tipoCargaArchivoAR">
                                <i class="bi bi-file-earmark-arrow-up me-1"></i>Subir Archivo
                            </label>
                            <input type="radio" class="btn-check" name="tipo_carga" id="tipoCargaEnlaceAR" value="enlace">
                            <label class="btn btn-outline-warning" for="tipoCargaEnlaceAR">
                                <i class="bi bi-link-45deg me-1"></i>Pegar Enlace
                            </label>
                        </div>
                    </div>

                    <!-- Campo para archivo -->
                    <div class="mb-3" id="campoArchivoAR">
                        <label for="archivo_listado" class="form-label">
                            <i class="bi bi-file-earmark-spreadsheet me-1"></i>Archivo (Excel, PDF, Imagen)
                        </label>
                        <input type="file" class="form-control" id="archivo_listado" name="archivo_listado"
                               accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx">
                        <div class="form-text">Formatos: PDF, JPG, PNG, Excel. Máximo: 10MB</div>
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

                    <!-- Descripción -->
                    <div class="mb-3">
                        <label for="descripcion_listado" class="form-label">Descripción</label>
                        <input type="text" class="form-control" id="descripcion_listado" name="descripcion" required
                               placeholder="Ej: Listado trabajadores alto riesgo 2026, Matriz identificación...">
                    </div>

                    <!-- Año -->
                    <div class="mb-3">
                        <label for="anio_listado" class="form-label">Año</label>
                        <select class="form-select" id="anio_listado" name="anio" required>
                            <?php for ($y = 2026; $y <= 2030; $y++): ?>
                                <option value="<?= $y ?>" <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <!-- Observaciones -->
                    <div class="mb-3">
                        <label for="observaciones_listado" class="form-label">Observaciones (opcional)</label>
                        <textarea class="form-control" id="observaciones_listado" name="observaciones" rows="2"
                                  placeholder="Notas adicionales: cantidad de trabajadores, actividades identificadas..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning" id="btnAdjuntarListado">
                        <i class="bi bi-cloud-upload me-1"></i>Adjuntar y Publicar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Toggle entre archivo y enlace para el modal de alto riesgo
document.querySelectorAll('input[name="tipo_carga"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const isArchivo = this.value === 'archivo';
        const campoArchivo = document.getElementById('campoArchivoAR');
        const campoEnlace = document.getElementById('campoEnlaceAR');
        const inputArchivo = document.getElementById('archivo_listado');
        const inputEnlace = document.getElementById('url_externa_ar');

        if (isArchivo) {
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
document.getElementById('formAdjuntarListado')?.addEventListener('submit', function(e) {
    const btn = document.getElementById('btnAdjuntarListado');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Subiendo...';
});
</script>
