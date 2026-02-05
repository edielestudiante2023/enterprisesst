<?php
/**
 * Vista de Tipo: 4.2.2 Verificacion de aplicacion de medidas de prevencion y control
 * Carpeta para adjuntar soportes de verificacion (inspecciones, listas chequeo, observaciones)
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
                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modalAdjuntarVerificacion">
                    <i class="bi bi-cloud-upload me-1"></i>Adjuntar Soporte
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Informacion sobre el modulo -->
<div class="alert alert-warning mb-4">
    <div class="d-flex align-items-start">
        <i class="bi bi-clipboard-check me-3 fs-4"></i>
        <div>
            <h6 class="mb-1">Soportes de Verificacion de Medidas de Prevencion y Control</h6>
            <p class="mb-0 small">
                Adjunte los registros de verificacion del cumplimiento de medidas de prevencion por parte de los trabajadores:
                inspecciones de EPP, listas de chequeo, observaciones de comportamiento seguro, actas de seguimiento, etc.
            </p>
        </div>
    </div>
</div>

<!-- Tabla de Soportes Adjuntados -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-warning text-dark">
        <h6 class="mb-0">
            <i class="bi bi-clipboard-check me-2"></i>Soportes de Verificacion
        </h6>
    </div>
    <div class="card-body">
        <?php if (!empty($documentosSSTAprobados)): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 120px;">Codigo</th>
                            <th>Descripcion</th>
                            <th style="width: 80px;">Ano</th>
                            <th style="width: 100px;">Fecha</th>
                            <th style="width: 100px;">Tipo</th>
                            <th style="width: 150px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documentosSSTAprobados as $soporte): ?>
                            <?php
                            $esEnlace = !empty($soporte['url_externa']);
                            $urlArchivo = $esEnlace ? $soporte['url_externa'] : ($soporte['archivo_pdf'] ?? '#');
                            ?>
                            <tr>
                                <td><code><?= esc($soporte['codigo'] ?? 'SOP-VMP') ?></code></td>
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
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-4">
                <i class="bi bi-clipboard-x text-muted" style="font-size: 2.5rem;"></i>
                <p class="text-muted mt-2 mb-0">No hay soportes de verificacion adjuntados aun.</p>
                <small class="text-muted">Use el boton "Adjuntar Soporte" para agregar registros.</small>
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

<!-- Modal para Adjuntar Soporte de Verificacion -->
<div class="modal fade" id="modalAdjuntarVerificacion" tabindex="-1" aria-labelledby="modalAdjuntarVerificacionLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="modalAdjuntarVerificacionLabel">
                    <i class="bi bi-cloud-upload me-2"></i>Adjuntar Soporte de Verificacion
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formAdjuntarVerificacion" action="<?= base_url('documentos-sst/adjuntar-soporte-verificacion') ?>" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id_cliente" value="<?= $cliente['id_cliente'] ?>">
                    <input type="hidden" name="id_carpeta" value="<?= $carpeta['id_carpeta'] ?>">

                    <!-- Tipo de carga -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de carga</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="tipo_carga" id="tipoCargaArchivoVerif" value="archivo" checked>
                            <label class="btn btn-outline-warning" for="tipoCargaArchivoVerif">
                                <i class="bi bi-file-earmark-arrow-up me-1"></i>Subir Archivo
                            </label>
                            <input type="radio" class="btn-check" name="tipo_carga" id="tipoCargaEnlaceVerif" value="enlace">
                            <label class="btn btn-outline-warning" for="tipoCargaEnlaceVerif">
                                <i class="bi bi-link-45deg me-1"></i>Pegar Enlace
                            </label>
                        </div>
                    </div>

                    <!-- Campo para archivo -->
                    <div class="mb-3" id="campoArchivoVerif">
                        <label for="archivo_verificacion" class="form-label">
                            <i class="bi bi-file-earmark-pdf me-1"></i>Archivo (PDF, Excel, Imagen)
                        </label>
                        <input type="file" class="form-control" id="archivo_verificacion" name="archivo_soporte"
                               accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx,.doc,.docx">
                        <div class="form-text">Formatos: PDF, JPG, PNG, Excel, Word. Maximo: 10MB</div>
                    </div>

                    <!-- Campo para enlace -->
                    <div class="mb-3 d-none" id="campoEnlaceVerif">
                        <label for="url_externa_verif" class="form-label">
                            <i class="bi bi-link-45deg me-1"></i>Enlace (Google Drive, OneDrive, etc.)
                        </label>
                        <input type="url" class="form-control" id="url_externa_verif" name="url_externa"
                               placeholder="https://drive.google.com/...">
                        <div class="form-text">Pegue el enlace compartido del archivo en la nube</div>
                    </div>

                    <!-- Descripcion -->
                    <div class="mb-3">
                        <label for="descripcion_verificacion" class="form-label">Descripcion</label>
                        <input type="text" class="form-control" id="descripcion_verificacion" name="descripcion" required
                               placeholder="Ej: Inspeccion EPP Enero 2026, Lista chequeo area produccion...">
                    </div>

                    <!-- Ano -->
                    <div class="mb-3">
                        <label for="anio_verificacion" class="form-label">Ano</label>
                        <select class="form-select" id="anio_verificacion" name="anio">
                            <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                <option value="<?= $y ?>" <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <!-- Observaciones -->
                    <div class="mb-3">
                        <label for="observaciones_verificacion" class="form-label">Observaciones (opcional)</label>
                        <textarea class="form-control" id="observaciones_verificacion" name="observaciones" rows="2"
                                  placeholder="Notas adicionales sobre el documento..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning" id="btnAdjuntarVerificacion">
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
        document.getElementById('campoArchivoVerif').classList.toggle('d-none', !isArchivo);
        document.getElementById('campoEnlaceVerif').classList.toggle('d-none', isArchivo);
        document.getElementById('archivo_verificacion').required = isArchivo;
        document.getElementById('url_externa_verif').required = !isArchivo;
        if (!isArchivo) document.getElementById('archivo_verificacion').value = '';
        if (isArchivo) document.getElementById('url_externa_verif').value = '';
    });
});

// Manejar envio del formulario
document.getElementById('formAdjuntarVerificacion')?.addEventListener('submit', function(e) {
    const btn = document.getElementById('btnAdjuntarVerificacion');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Subiendo...';
});
</script>
