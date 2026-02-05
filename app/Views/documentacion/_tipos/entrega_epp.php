<?php
/**
 * Vista de Tipo: 4.2.6 Entrega de EPP, verificacion con contratistas y subcontratistas
 * Carpeta para adjuntar soportes de entrega de EPP
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
                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modalAdjuntarEPP">
                    <i class="bi bi-cloud-upload me-1"></i>Adjuntar Soporte
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Informacion sobre el modulo -->
<div class="alert alert-warning mb-4">
    <div class="d-flex align-items-start">
        <i class="bi bi-shield-check me-3 fs-4"></i>
        <div>
            <h6 class="mb-1">Soportes de Entrega de Elementos de Proteccion Personal (EPP)</h6>
            <p class="mb-0 small">
                Adjunte los registros de entrega de EPP a trabajadores, contratistas y subcontratistas:
                formatos de entrega firmados, actas, verificaciones de uso, matrices de EPP, etc.
            </p>
        </div>
    </div>
</div>

<!-- Tabla de Soportes Adjuntados -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-warning text-dark">
        <h6 class="mb-0">
            <i class="bi bi-shield-check me-2"></i>Soportes de Entrega EPP
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
                                <td><code><?= esc($soporte['codigo'] ?? 'SOP-EPP') ?></code></td>
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
                                        <a href="<?= esc($urlArchivo) ?>" class="btn btn-outline-primary" target="_blank" title="Ver">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <?php if (!$esEnlace): ?>
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
                <i class="bi bi-shield-x text-muted" style="font-size: 2.5rem;"></i>
                <p class="text-muted mt-2 mb-0">No hay soportes de entrega de EPP adjuntados aun.</p>
                <small class="text-muted">Use el boton "Adjuntar Soporte" para agregar documentos.</small>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Subcarpetas -->
<div class="row">
    <?= view('documentacion/_components/lista_subcarpetas', ['subcarpetas' => $subcarpetas ?? []]) ?>
</div>

<!-- Modal para Adjuntar -->
<div class="modal fade" id="modalAdjuntarEPP" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="bi bi-cloud-upload me-2"></i>Adjuntar Soporte EPP</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAdjuntarEPP" action="<?= base_url('documentos-sst/adjuntar-soporte-epp') ?>" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id_cliente" value="<?= $cliente['id_cliente'] ?>">
                    <input type="hidden" name="id_carpeta" value="<?= $carpeta['id_carpeta'] ?>">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de carga</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="tipo_carga" id="tipoCargaArchivoEPP" value="archivo" checked>
                            <label class="btn btn-outline-warning" for="tipoCargaArchivoEPP">
                                <i class="bi bi-file-earmark-arrow-up me-1"></i>Subir Archivo
                            </label>
                            <input type="radio" class="btn-check" name="tipo_carga" id="tipoCargaEnlaceEPP" value="enlace">
                            <label class="btn btn-outline-warning" for="tipoCargaEnlaceEPP">
                                <i class="bi bi-link-45deg me-1"></i>Pegar Enlace
                            </label>
                        </div>
                    </div>

                    <div class="mb-3" id="campoArchivoEPP">
                        <label class="form-label">Archivo (PDF, Excel, Imagen)</label>
                        <input type="file" class="form-control" id="archivo_epp" name="archivo_soporte" accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx,.doc,.docx">
                    </div>

                    <div class="mb-3 d-none" id="campoEnlaceEPP">
                        <label class="form-label">Enlace (Google Drive, OneDrive)</label>
                        <input type="url" class="form-control" id="url_externa_epp" name="url_externa" placeholder="https://drive.google.com/...">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripcion</label>
                        <input type="text" class="form-control" name="descripcion" required placeholder="Ej: Entrega EPP Enero 2026, Matriz EPP area produccion...">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ano</label>
                        <select class="form-select" name="anio">
                            <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                <option value="<?= $y ?>" <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Observaciones (opcional)</label>
                        <textarea class="form-control" name="observaciones" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning" id="btnAdjuntarEPP">
                        <i class="bi bi-cloud-upload me-1"></i>Adjuntar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('input[name="tipo_carga"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const isArchivo = this.value === 'archivo';
        document.getElementById('campoArchivoEPP').classList.toggle('d-none', !isArchivo);
        document.getElementById('campoEnlaceEPP').classList.toggle('d-none', isArchivo);
    });
});
document.getElementById('formAdjuntarEPP')?.addEventListener('submit', function() {
    const btn = document.getElementById('btnAdjuntarEPP');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Subiendo...';
});
</script>
