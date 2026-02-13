<?php
/**
 * Vista de Tipo: 4.1.1 Metodologia para la Identificacion de Peligros, Evaluacion y Valoracion de Riesgos
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
                    <a href="<?= base_url('documentos/generar/metodologia_identificacion_peligros/' . $cliente['id_cliente']) ?>"
                       class="btn btn-outline-success">
                        <i class="bi bi-arrow-repeat me-1"></i>Nueva version <?= date('Y') ?>
                    </a>
                <?php else: ?>
                    <a href="<?= base_url('documentos/generar/metodologia_identificacion_peligros/' . $cliente['id_cliente']) ?>"
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
        <i class="bi bi-shield-exclamation me-3 fs-4"></i>
        <div>
            <h6 class="mb-1">Metodologia de Identificacion de Peligros y Valoracion de Riesgos</h6>
            <p class="mb-0 small">
                Documento que establece la metodologia adoptada por <?= esc($cliente['nombre_cliente'] ?? 'la organizacion') ?>
                para identificar peligros, evaluar y valorar riesgos, y establecer controles con alcance a todos los procesos,
                actividades rutinarias y no rutinarias, maquinas, equipos y todos los trabajadores independientemente de su forma
                de vinculacion. Basada en la GTC 45:2012 y en cumplimiento del estandar 4.1.1 de la Resolucion 0312/2019.
            </p>
        </div>
    </div>
</div>

<!-- Tabla de Documentos SST -->
<?= view('documentacion/_components/tabla_documentos_sst', [
    'tipoCarpetaFases' => 'metodologia_identificacion_peligros',
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
                    Adjunte evidencias de la metodologia aplicada: Matriz de Peligros y Riesgos (GTC 45),
                    actas de socializacion, registros de participacion de trabajadores, inspecciones u otros soportes complementarios.
                </p>
            </div>
            <div class="col-md-4 text-end">
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalAdjuntarSoporteMIP">
                    <i class="bi bi-cloud-upload me-1"></i>Adjuntar Soporte
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Soportes Adjuntados -->
<?= view('documentacion/_components/tabla_soportes', [
    'soportes' => $soportesAdicionales ?? [],
    'titulo' => 'Soportes Metodologia de Peligros',
    'subtitulo' => 'Identificacion y valoracion de riesgos',
    'icono' => 'bi-exclamation-triangle',
    'colorHeader' => 'warning',
    'codigoDefault' => 'SOP-PEL',
    'emptyIcon' => 'bi-exclamation-triangle',
    'emptyMessage' => 'No hay soportes adjuntados aun.',
    'emptyHint' => 'Use el boton "Adjuntar Soporte" para agregar evidencias.'
]) ?>

<!-- Modal de Adjuntar Soporte Metodologia Identificacion Peligros -->
<div class="modal fade" id="modalAdjuntarSoporteMIP" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-cloud-upload me-2"></i>Adjuntar Soporte - Identificacion de Peligros
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="formAdjuntarSoporteMIP"
                  action="<?= base_url('documentos-sst/adjuntar-soporte-metodologia-peligros') ?>"
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
                            <input type="radio" class="btn-check" name="tipo_carga_mip"
                                   id="tipoCargaMIPArchivo" value="archivo" checked>
                            <label class="btn btn-outline-primary" for="tipoCargaMIPArchivo">
                                <i class="bi bi-file-earmark-arrow-up me-1"></i>Subir Archivo
                            </label>
                            <input type="radio" class="btn-check" name="tipo_carga_mip"
                                   id="tipoCargaMIPEnlace" value="enlace">
                            <label class="btn btn-outline-primary" for="tipoCargaMIPEnlace">
                                <i class="bi bi-link-45deg me-1"></i>Pegar Enlace
                            </label>
                        </div>
                    </div>

                    <!-- Campo Archivo -->
                    <div class="mb-3" id="campoArchivoMIP">
                        <label for="archivo_soporte_mip" class="form-label">Archivo</label>
                        <input type="file" class="form-control" id="archivo_soporte_mip" name="archivo_soporte"
                               accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx,.doc,.docx">
                        <div class="form-text">PDF, JPG, PNG, Excel, Word. Max: 10MB</div>
                    </div>

                    <!-- Campo Enlace (oculto por defecto) -->
                    <div class="mb-3 d-none" id="campoEnlaceMIP">
                        <label for="url_externa_mip" class="form-label">Enlace</label>
                        <input type="url" class="form-control" id="url_externa_mip" name="url_externa"
                               placeholder="https://drive.google.com/...">
                    </div>

                    <!-- Descripcion -->
                    <div class="mb-3">
                        <label for="descripcion_mip" class="form-label">Descripcion <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="descripcion_mip" name="descripcion" required
                               placeholder="Ej: Matriz de Peligros GTC 45, Acta socializacion, Inspeccion planeada...">
                    </div>

                    <!-- Ano -->
                    <div class="mb-3">
                        <label for="anio_mip" class="form-label">Ano</label>
                        <select class="form-select" id="anio_mip" name="anio">
                            <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                <option value="<?= $y ?>" <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <!-- Observaciones -->
                    <div class="mb-3">
                        <label for="observaciones_mip" class="form-label">Observaciones (opcional)</label>
                        <textarea class="form-control" id="observaciones_mip" name="observaciones" rows="2"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnAdjuntarMIP">
                        <i class="bi bi-cloud-upload me-1"></i>Adjuntar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript para Toggle Archivo/Enlace -->
<script>
document.querySelectorAll('input[name="tipo_carga_mip"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const isArchivo = this.value === 'archivo';
        document.getElementById('campoArchivoMIP').classList.toggle('d-none', !isArchivo);
        document.getElementById('campoEnlaceMIP').classList.toggle('d-none', isArchivo);
        document.getElementById('archivo_soporte_mip').required = isArchivo;
        document.getElementById('url_externa_mip').required = !isArchivo;
        if (!isArchivo) {
            document.getElementById('archivo_soporte_mip').value = '';
        } else {
            document.getElementById('url_externa_mip').value = '';
        }
    });
});

document.getElementById('formAdjuntarSoporteMIP')?.addEventListener('submit', function() {
    const btn = document.getElementById('btnAdjuntarMIP');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Subiendo...';
});
</script>
