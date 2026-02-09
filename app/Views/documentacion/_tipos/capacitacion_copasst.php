<?php
/**
 * Vista de Tipo: 1.1.7 Capacitación COPASST
 * Carpeta para gestionar las capacitaciones del COPASST/Vigía
 * Incluye: Adjuntar soportes de capacitación (archivos o enlaces)
 * Variables: $carpeta, $cliente, $documentosSSTAprobados, $soportesAdicionales
 */

// Determinar si la empresa requiere COPASST o Vigía
$numTrabajadores = $cliente['trabajadores'] ?? 10;
$requiereCopasst = $numTrabajadores >= 10;
$tipoComite = $requiereCopasst ? 'COPASST' : 'Vigía SST';
?>

<!-- Card de Carpeta con Botones -->
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
                <!-- Botón principal: Adjuntar Soporte -->
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAdjuntarCapacitacionCopasst">
                    <i class="bi bi-cloud-upload me-1"></i>Adjuntar Soporte
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Información sobre el numeral -->
<div class="alert alert-info mb-4">
    <div class="d-flex align-items-start">
        <i class="bi bi-mortarboard me-3 fs-4"></i>
        <div>
            <h6 class="mb-1">1.1.7. Capacitación <?= $tipoComite ?></h6>
            <p class="mb-0 small">
                Registro de las capacitaciones realizadas a los integrantes del <strong><?= $tipoComite ?></strong>.
                Adjunte actas de asistencia, certificados de capacitación, material de formación, evaluaciones, entre otros.
            </p>
        </div>
    </div>
</div>

<!-- Tabla de Soportes Adjuntados -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
        <h6 class="mb-0">
            <i class="bi bi-mortarboard me-2"></i>Soportes de Capacitación <?= $tipoComite ?>
        </h6>
    </div>
    <div class="card-body">
        <?php
        // Usar soportesAdicionales si está disponible, sino documentosSSTAprobados
        $soportes = !empty($soportesAdicionales) ? $soportesAdicionales : ($documentosSSTAprobados ?? []);
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
                                <td><code><?= esc($soporte['codigo'] ?? 'SOP-CCP') ?></code></td>
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
                <i class="bi bi-mortarboard text-muted" style="font-size: 2.5rem;"></i>
                <p class="text-muted mt-2 mb-0">No hay soportes de capacitación adjuntados aún.</p>
                <small class="text-muted">Use el botón "Adjuntar Soporte" para agregar documentos de capacitación.</small>
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

<!-- Modal para Adjuntar Soporte Capacitación COPASST -->
<div class="modal fade" id="modalAdjuntarCapacitacionCopasst" tabindex="-1" aria-labelledby="modalAdjuntarCapacitacionCopasstLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalAdjuntarCapacitacionCopasstLabel">
                    <i class="bi bi-cloud-upload me-2"></i>Adjuntar Soporte de Capacitación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formAdjuntarCapacitacionCopasst" action="<?= base_url('documentos-sst/adjuntar-soporte-capacitacion-copasst') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <input type="hidden" name="id_cliente" value="<?= $cliente['id_cliente'] ?>">
                    <input type="hidden" name="id_carpeta" value="<?= $carpeta['id_carpeta'] ?>">

                    <!-- Tipo de carga -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de carga</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="tipo_carga" id="tipoCargaArchivoCCP" value="archivo" checked>
                            <label class="btn btn-outline-primary" for="tipoCargaArchivoCCP">
                                <i class="bi bi-file-earmark-arrow-up me-1"></i>Subir Archivo
                            </label>
                            <input type="radio" class="btn-check" name="tipo_carga" id="tipoCargaEnlaceCCP" value="enlace">
                            <label class="btn btn-outline-primary" for="tipoCargaEnlaceCCP">
                                <i class="bi bi-link-45deg me-1"></i>Pegar Enlace
                            </label>
                        </div>
                    </div>

                    <!-- Campo para archivo -->
                    <div class="mb-3" id="campoArchivoCCP">
                        <label for="archivo_ccp" class="form-label">
                            <i class="bi bi-file-earmark-pdf me-1"></i>Archivo (PDF, Excel, Imagen, Word)
                        </label>
                        <input type="file" class="form-control" id="archivo_ccp" name="archivo_soporte"
                               accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx,.doc,.docx">
                        <div class="form-text">Formatos: PDF, JPG, PNG, Excel, Word. Máximo: 10MB</div>
                    </div>

                    <!-- Campo para enlace -->
                    <div class="mb-3 d-none" id="campoEnlaceCCP">
                        <label for="url_externa_ccp" class="form-label">
                            <i class="bi bi-link-45deg me-1"></i>Enlace (Google Drive, OneDrive, etc.)
                        </label>
                        <input type="url" class="form-control" id="url_externa_ccp" name="url_externa"
                               placeholder="https://drive.google.com/...">
                        <div class="form-text">Pegue el enlace compartido del archivo en la nube</div>
                    </div>

                    <!-- Descripción -->
                    <div class="mb-3">
                        <label for="descripcion_ccp" class="form-label">Descripción <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="descripcion_ccp" name="descripcion" required
                               placeholder="Ej: Acta capacitación funciones COPASST - Enero 2026">
                    </div>

                    <!-- Año -->
                    <div class="mb-3">
                        <label for="anio_ccp" class="form-label">Año</label>
                        <select class="form-select" id="anio_ccp" name="anio">
                            <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                <option value="<?= $y ?>" <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <!-- Observaciones -->
                    <div class="mb-3">
                        <label for="observaciones_ccp" class="form-label">Observaciones (opcional)</label>
                        <textarea class="form-control" id="observaciones_ccp" name="observaciones" rows="2"
                                  placeholder="Notas adicionales sobre la capacitación..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="btnAdjuntarCCP">
                        <i class="bi bi-cloud-upload me-1"></i>Adjuntar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Toggle entre archivo y enlace
document.querySelectorAll('#modalAdjuntarCapacitacionCopasst input[name="tipo_carga"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const isArchivo = this.value === 'archivo';
        document.getElementById('campoArchivoCCP').classList.toggle('d-none', !isArchivo);
        document.getElementById('campoEnlaceCCP').classList.toggle('d-none', isArchivo);
        document.getElementById('archivo_ccp').required = isArchivo;
        document.getElementById('url_externa_ccp').required = !isArchivo;
        if (!isArchivo) document.getElementById('archivo_ccp').value = '';
        if (isArchivo) document.getElementById('url_externa_ccp').value = '';
    });
});

// Manejar envío del formulario
document.getElementById('formAdjuntarCapacitacionCopasst')?.addEventListener('submit', function(e) {
    const btn = document.getElementById('btnAdjuntarCCP');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Subiendo...';
});
</script>
