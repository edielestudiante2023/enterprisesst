<?php
/**
 * Vista de Tipo: 1.1.8 Conformación Comité de Convivencia Laboral (COCOLAB)
 * Incluye: Sistema de elecciones + Manual de Convivencia + adjuntar soportes
 *
 * Basado en Resolucion 3461 de 2025, Ley 1010 de 2006
 */

// Buscar si ya existe el Manual de Convivencia Laboral
$manualConvivencia = null;
foreach ($documentosSSTAprobados ?? [] as $doc) {
    if (($doc['tipo_documento'] ?? '') === 'manual_convivencia_laboral') {
        $manualConvivencia = $doc;
        break;
    }
}

// Determinar composición según número de trabajadores
$numTrabajadores = $cliente['trabajadores'] ?? 10;
$composicion = $numTrabajadores <= 19 ? '1 principal + 1 suplente' : '2 principales + 2 suplentes';
?>

<!-- Card de Carpeta con Botones Principales -->
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
                <a href="<?= base_url('comites-elecciones/' . $cliente['id_cliente']) ?>" class="btn btn-warning me-2">
                    <i class="bi bi-chat-heart me-1"></i>Sistema de Conformacion
                </a>
                <!-- Botón secundario: Adjuntar Soporte -->
                <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalAdjuntarConvivencia">
                    <i class="bi bi-cloud-upload me-1"></i>Adjuntar Soporte
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Card del Manual de Convivencia Laboral (Resolución 3461 de 2025) -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-info text-white">
        <h6 class="mb-0">
            <i class="bi bi-book me-2"></i>Manual de Convivencia Laboral
            <span class="badge bg-light text-dark ms-2">Resolución 3461 de 2025</span>
        </h6>
    </div>
    <div class="card-body">
        <p class="text-muted mb-3">
            El Manual de Convivencia Laboral establece las normas de comportamiento, conductas aceptables
            y no aceptables, así como los mecanismos de resolución de conflictos en el entorno laboral.
            Es un documento obligatorio según la Resolución 3461 de 2025 del Ministerio del Trabajo.
        </p>

        <?php if (!$manualConvivencia): ?>
            <div class="d-flex gap-2 align-items-center">
                <a href="<?= base_url('documentos/generar/manual_convivencia_laboral/' . $cliente['id_cliente']) ?>"
                   class="btn btn-info">
                    <i class="bi bi-file-earmark-plus me-1"></i>Generar Manual de Convivencia
                </a>
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Contenido normativo pre-cargado basado en la normatividad vigente.
                </small>
            </div>
        <?php else: ?>
            <div class="alert alert-success py-2 mb-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-check-circle me-2"></i>
                        <strong>Manual de Convivencia Laboral generado</strong>
                        <?php if ($manualConvivencia['estado'] === 'firmado'): ?>
                            <span class="badge bg-success ms-2">Firmado</span>
                        <?php elseif ($manualConvivencia['estado'] === 'pendiente_firma'): ?>
                            <span class="badge bg-warning ms-2">Pendiente de Firmas</span>
                        <?php elseif ($manualConvivencia['estado'] === 'aprobado'): ?>
                            <span class="badge bg-primary ms-2">Aprobado</span>
                        <?php endif; ?>
                    </div>
                    <div class="btn-group btn-group-sm">
                        <a href="<?= base_url('documentos-sst/' . $cliente['id_cliente'] . '/manual-convivencia-laboral/' . ($manualConvivencia['anio'] ?? date('Y'))) ?>"
                           class="btn btn-outline-primary" title="Ver documento">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="<?= base_url('documentos/generar/manual_convivencia_laboral/' . $cliente['id_cliente']) ?>"
                           class="btn btn-outline-secondary" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Información sobre soportes manuales -->
<div class="alert alert-secondary mb-4">
    <div class="d-flex align-items-start">
        <i class="bi bi-paperclip me-3 fs-4"></i>
        <div>
            <h6 class="mb-1">Soportes Adicionales</h6>
            <p class="mb-0 small">
                Use esta sección para adjuntar soportes adicionales del Comité de Convivencia:
                resoluciones, reglamento interno, capacitaciones, denuncias atendidas, etc.
            </p>
        </div>
    </div>
</div>

<!-- Tabla de Soportes Adjuntados -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-success text-white">
        <h6 class="mb-0">
            <i class="bi bi-heart-pulse me-2"></i>Soportes Comité de Convivencia
        </h6>
    </div>
    <div class="card-body">
        <?php
        // Filtrar documentos que NO son el manual de convivencia (los soportes adjuntados)
        $soportes = array_filter($documentosSSTAprobados ?? [], function($doc) {
            return ($doc['tipo_documento'] ?? '') !== 'manual_convivencia_laboral';
        });
        ?>
        <?php if (!empty($soportes)): ?>
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
                        <?php foreach ($soportes as $soporte): ?>
                            <?php
                            $esEnlace = !empty($soporte['url_externa']);
                            $urlArchivo = $esEnlace ? $soporte['url_externa'] : ($soporte['archivo_pdf'] ?? '#');
                            ?>
                            <tr>
                                <td><code><?= esc($soporte['codigo'] ?? 'SOP-CONV') ?></code></td>
                                <td>
                                    <strong><?= esc($soporte['titulo']) ?></strong>
                                    <?php if (!empty($soporte['observaciones'])): ?>
                                        <br><small class="text-muted"><?= esc($soporte['observaciones']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-secondary"><?= esc($soporte['anio']) ?></span></td>
                                <td>
                                    <small><?= date('d/m/Y', strtotime($soporte['created_at'] ?? $soporte['fecha_aprobacion'] ?? 'now')) ?></small>
                                </td>
                                <td>
                                    <?php if ($esEnlace): ?>
                                        <span class="badge bg-info"><i class="bi bi-link-45deg me-1"></i>Enlace</span>
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
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-4">
                <i class="bi bi-heart-pulse text-muted" style="font-size: 2.5rem;"></i>
                <p class="text-muted mt-2 mb-0">No hay soportes del Comité de Convivencia adjuntados aún.</p>
                <small class="text-muted">Use el botón "Adjuntar Soporte" para agregar documentos.</small>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Subcarpetas (si las hay) -->
<div class="row">
    <?= view('documentacion/_components/lista_subcarpetas', [
        'subcarpetas' => $subcarpetas ?? []
    ]) ?>
</div>

<!-- Modal para Adjuntar Soporte Comité de Convivencia -->
<div class="modal fade" id="modalAdjuntarConvivencia" tabindex="-1" aria-labelledby="modalAdjuntarConvivenciaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalAdjuntarConvivenciaLabel">
                    <i class="bi bi-cloud-upload me-2"></i>Adjuntar Soporte Comité de Convivencia
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formAdjuntarConvivencia" action="<?= base_url('documentos-sst/adjuntar-soporte-convivencia') ?>" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id_cliente" value="<?= $cliente['id_cliente'] ?>">
                    <input type="hidden" name="id_carpeta" value="<?= $carpeta['id_carpeta'] ?>">

                    <!-- Tipo de carga -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de carga</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="tipo_carga" id="tipoCargaArchivoConv" value="archivo" checked>
                            <label class="btn btn-outline-success" for="tipoCargaArchivoConv">
                                <i class="bi bi-file-earmark-arrow-up me-1"></i>Subir Archivo
                            </label>
                            <input type="radio" class="btn-check" name="tipo_carga" id="tipoCargaEnlaceConv" value="enlace">
                            <label class="btn btn-outline-success" for="tipoCargaEnlaceConv">
                                <i class="bi bi-link-45deg me-1"></i>Pegar Enlace
                            </label>
                        </div>
                    </div>

                    <!-- Campo para archivo -->
                    <div class="mb-3" id="campoArchivoConv">
                        <label for="archivo_convivencia" class="form-label">
                            <i class="bi bi-file-earmark-pdf me-1"></i>Archivo (PDF, Excel, Imagen)
                        </label>
                        <input type="file" class="form-control" id="archivo_convivencia" name="archivo_soporte"
                               accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx,.doc,.docx">
                        <div class="form-text">Formatos: PDF, JPG, PNG, Excel, Word. Máximo: 10MB</div>
                    </div>

                    <!-- Campo para enlace -->
                    <div class="mb-3 d-none" id="campoEnlaceConv">
                        <label for="url_externa_conv" class="form-label">
                            <i class="bi bi-link-45deg me-1"></i>Enlace (Google Drive, OneDrive, etc.)
                        </label>
                        <input type="url" class="form-control" id="url_externa_conv" name="url_externa"
                               placeholder="https://drive.google.com/...">
                        <div class="form-text">Pegue el enlace compartido del archivo en la nube</div>
                    </div>

                    <!-- Descripción -->
                    <div class="mb-3">
                        <label for="descripcion_conv" class="form-label">Descripción</label>
                        <input type="text" class="form-control" id="descripcion_conv" name="descripcion" required
                               placeholder="Ej: Acta conformación Comité Convivencia 2026, Resolución...">
                    </div>

                    <!-- Año -->
                    <div class="mb-3">
                        <label for="anio_conv" class="form-label">Año</label>
                        <select class="form-select" id="anio_conv" name="anio">
                            <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                <option value="<?= $y ?>" <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <!-- Observaciones -->
                    <div class="mb-3">
                        <label for="observaciones_conv" class="form-label">Observaciones (opcional)</label>
                        <textarea class="form-control" id="observaciones_conv" name="observaciones" rows="2"
                                  placeholder="Notas adicionales sobre el documento..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="btnAdjuntarConv">
                        <i class="bi bi-cloud-upload me-1"></i>Adjuntar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Toggle entre archivo y enlace
document.querySelectorAll('#modalAdjuntarConvivencia input[name="tipo_carga"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const isArchivo = this.value === 'archivo';
        document.getElementById('campoArchivoConv').classList.toggle('d-none', !isArchivo);
        document.getElementById('campoEnlaceConv').classList.toggle('d-none', isArchivo);
        document.getElementById('archivo_convivencia').required = isArchivo;
        document.getElementById('url_externa_conv').required = !isArchivo;
        if (!isArchivo) document.getElementById('archivo_convivencia').value = '';
        if (isArchivo) document.getElementById('url_externa_conv').value = '';
    });
});

// Manejar envío del formulario
document.getElementById('formAdjuntarConvivencia')?.addEventListener('submit', function(e) {
    const btn = document.getElementById('btnAdjuntarConv');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Subiendo...';
});
</script>
