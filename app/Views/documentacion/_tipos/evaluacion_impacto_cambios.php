<?php
/**
 * Vista de Tipo: 2.11.1 Evaluacion del Impacto de Cambios Internos y Externos
 * Procedimiento simple con IA (sin fases previas)
 * Variables: $carpeta, $cliente, $documentosSSTAprobados, $soportesAdicionales, $subcarpetas
 */

// Verificar si hay documento aprobado para el ano actual
$hayAprobadoAnioActual = false;
if (!empty($documentosSSTAprobados)) {
    foreach ($documentosSSTAprobados as $d) {
        if (($d['anio'] ?? '') == date('Y')) {
            $hayAprobadoAnioActual = true;
            break;
        }
    }
}
?>

<!-- Card de Carpeta con Boton IA -->
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
                <?php if ($hayAprobadoAnioActual): ?>
                    <a href="<?= base_url('documentos/generar/procedimiento_gestion_cambio/' . $cliente['id_cliente']) ?>"
                       class="btn btn-outline-success">
                        <i class="bi bi-arrow-repeat me-1"></i>Nueva version <?= date('Y') ?>
                    </a>
                <?php else: ?>
                    <a href="<?= base_url('documentos/generar/procedimiento_gestion_cambio/' . $cliente['id_cliente']) ?>"
                       class="btn btn-success">
                        <i class="bi bi-magic me-1"></i>Crear con IA <?= date('Y') ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Informacion sobre el modulo -->
<div class="alert alert-primary mb-4">
    <div class="d-flex align-items-start">
        <i class="bi bi-arrow-left-right me-3 fs-4"></i>
        <div>
            <h6 class="mb-1">Procedimiento de Gestion del Cambio</h6>
            <p class="mb-0 small">
                Documento que establece como <?= esc($cliente['nombre_cliente'] ?? 'la organizacion') ?> identifica, evalua y
                gestiona los cambios internos y externos que puedan impactar la seguridad y salud en el trabajo,
                garantizando que se informe y capacite a los trabajadores. Cumple el estandar 2.11.1 de la Resolucion 0312/2019.
            </p>
        </div>
    </div>
</div>

<!-- Tabla de Documentos SST -->
<?= view('documentacion/_components/tabla_documentos_sst', [
    'tipoCarpetaFases' => 'evaluacion_impacto_cambios',
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
                    Adjunte evidencias de evaluacion de impacto de cambios: formatos diligenciados,
                    actas de socializacion, registros de cambios evaluados u otros soportes complementarios.
                </p>
            </div>
            <div class="col-md-4 text-end">
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalAdjuntarSoporteGC">
                    <i class="bi bi-cloud-upload me-1"></i>Adjuntar Soporte
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Soportes Adjuntados -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-secondary text-white">
        <h6 class="mb-0">
            <i class="bi bi-paperclip me-2"></i>Soportes Adjuntados
        </h6>
    </div>
    <div class="card-body">
        <?php $soportes = $soportesAdicionales ?? []; ?>
        <?php if (!empty($soportes)): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 100px;">Codigo</th>
                            <th>Descripcion</th>
                            <th style="width: 80px;">Ano</th>
                            <th style="width: 100px;">Fecha</th>
                            <th style="width: 90px;">Tipo</th>
                            <th style="width: 120px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($soportes as $soporte): ?>
                            <?php
                            $esEnlace = !empty($soporte['url_externa']);
                            $urlArchivo = $esEnlace
                                ? $soporte['url_externa']
                                : ($soporte['archivo_pdf'] ?? '#');
                            ?>
                            <tr>
                                <td><code><?= esc($soporte['codigo'] ?? 'SOP-GDC') ?></code></td>
                                <td>
                                    <strong><?= esc($soporte['titulo']) ?></strong>
                                    <?php if (!empty($soporte['observaciones'])): ?>
                                        <br><small class="text-muted"><?= esc($soporte['observaciones']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-secondary"><?= esc($soporte['anio']) ?></span></td>
                                <td><small><?= date('d/m/Y', strtotime($soporte['created_at'] ?? 'now')) ?></small></td>
                                <td>
                                    <?php if ($esEnlace): ?>
                                        <span class="badge bg-info"><i class="bi bi-link-45deg"></i> Enlace</span>
                                    <?php else: ?>
                                        <span class="badge bg-dark"><i class="bi bi-file-earmark"></i> Archivo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= esc($urlArchivo) ?>" class="btn btn-outline-primary" target="_blank" title="Ver">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <?php if (!$esEnlace): ?>
                                            <a href="<?= esc($urlArchivo) ?>" class="btn btn-outline-danger" download title="Descargar">
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
                <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                <p class="text-muted mt-2 mb-0">No hay soportes adjuntados.</p>
                <small class="text-muted">Use el boton "Adjuntar Soporte" para agregar evidencias.</small>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de Adjuntar Soporte Gestion del Cambio -->
<div class="modal fade" id="modalAdjuntarSoporteGC" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-cloud-upload me-2"></i>Adjuntar Soporte Gestion del Cambio
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="formAdjuntarSoporteGC"
                  action="<?= base_url('documentos-sst/adjuntar-soporte-gestion-cambio') ?>"
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
                            <input type="radio" class="btn-check" name="tipo_carga_gc"
                                   id="tipoCargaGCArchivo" value="archivo" checked>
                            <label class="btn btn-outline-primary" for="tipoCargaGCArchivo">
                                <i class="bi bi-file-earmark-arrow-up me-1"></i>Subir Archivo
                            </label>
                            <input type="radio" class="btn-check" name="tipo_carga_gc"
                                   id="tipoCargaGCEnlace" value="enlace">
                            <label class="btn btn-outline-primary" for="tipoCargaGCEnlace">
                                <i class="bi bi-link-45deg me-1"></i>Pegar Enlace
                            </label>
                        </div>
                    </div>

                    <!-- Campo Archivo -->
                    <div class="mb-3" id="campoArchivoGC">
                        <label for="archivo_soporte_gc" class="form-label">Archivo</label>
                        <input type="file" class="form-control" id="archivo_soporte_gc" name="archivo_soporte"
                               accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx,.doc,.docx">
                        <div class="form-text">PDF, JPG, PNG, Excel, Word. Max: 10MB</div>
                    </div>

                    <!-- Campo Enlace (oculto por defecto) -->
                    <div class="mb-3 d-none" id="campoEnlaceGC">
                        <label for="url_externa_gc" class="form-label">Enlace</label>
                        <input type="url" class="form-control" id="url_externa_gc" name="url_externa"
                               placeholder="https://drive.google.com/...">
                    </div>

                    <!-- Descripcion -->
                    <div class="mb-3">
                        <label for="descripcion_gc" class="form-label">Descripcion <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="descripcion_gc" name="descripcion" required
                               placeholder="Ej: Formato evaluacion de impacto, Acta de socializacion...">
                    </div>

                    <!-- Ano -->
                    <div class="mb-3">
                        <label for="anio_gc" class="form-label">Ano</label>
                        <select class="form-select" id="anio_gc" name="anio">
                            <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                <option value="<?= $y ?>" <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <!-- Observaciones -->
                    <div class="mb-3">
                        <label for="observaciones_gc" class="form-label">Observaciones (opcional)</label>
                        <textarea class="form-control" id="observaciones_gc" name="observaciones" rows="2"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnAdjuntarGC">
                        <i class="bi bi-cloud-upload me-1"></i>Adjuntar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript para Toggle Archivo/Enlace -->
<script>
document.querySelectorAll('input[name="tipo_carga_gc"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const isArchivo = this.value === 'archivo';
        document.getElementById('campoArchivoGC').classList.toggle('d-none', !isArchivo);
        document.getElementById('campoEnlaceGC').classList.toggle('d-none', isArchivo);
        document.getElementById('archivo_soporte_gc').required = isArchivo;
        document.getElementById('url_externa_gc').required = !isArchivo;
        if (!isArchivo) {
            document.getElementById('archivo_soporte_gc').value = '';
        } else {
            document.getElementById('url_externa_gc').value = '';
        }
    });
});

document.getElementById('formAdjuntarSoporteGC')?.addEventListener('submit', function() {
    const btn = document.getElementById('btnAdjuntarGC');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Subiendo...';
});
</script>
