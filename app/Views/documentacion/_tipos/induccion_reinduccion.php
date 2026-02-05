<?php
/**
 * Vista de Tipo: 1.2.2 Programa de Inducción y Reinducción en SG-SST
 * Carpeta para el programa de inducción generado con IA
 *
 * Variables: $carpeta, $cliente, $fasesInfo, $documentosSSTAprobados, $documentoExistente
 *
 * Flujo de 3 fases:
 * 1. Etapas del proceso (5 etapas con temas personalizados según peligros)
 * 2. Plan de Trabajo Anual (actividades derivadas de las etapas)
 * 3. Indicadores (cobertura y cumplimiento de inducción)
 */

// Verificar si hay documento para el año actual
$hayAprobadoAnioActual = false;
if (!empty($documentosSSTAprobados)) {
    foreach ($documentosSSTAprobados as $d) {
        if (($d['anio'] ?? date('Y')) == date('Y')) {
            $hayAprobadoAnioActual = true;
            break;
        }
    }
}

$anioActual = date('Y');
?>

<!-- Card de Carpeta con Botón IA -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="mb-1">
                    <i class="bi bi-person-badge text-primary me-2"></i>
                    <?= esc($carpeta['nombre']) ?>
                </h4>
                <?php if (!empty($carpeta['codigo'])): ?>
                    <span class="badge bg-primary me-2"><?= esc($carpeta['codigo']) ?></span>
                <?php endif; ?>
                <span class="badge bg-info">Programa con PTA</span>
                <?php if (!empty($carpeta['descripcion'])): ?>
                    <p class="text-muted mb-0 mt-2"><?= esc($carpeta['descripcion']) ?></p>
                <?php else: ?>
                    <p class="text-muted mb-0 mt-2">
                        Programa que establece el proceso de induccion y reinduccion para todos los trabajadores,
                        incluyendo identificacion de peligros, evaluacion de riesgos y controles para prevencion de ATEL.
                    </p>
                <?php endif; ?>
            </div>
            <div class="col-md-4 text-end">
                <?php if (isset($fasesInfo) && $fasesInfo && !$fasesInfo['puede_generar_documento']): ?>
                    <button type="button" class="btn btn-secondary" disabled title="Complete las fases previas">
                        <i class="bi bi-lock me-1"></i>Crear Documento
                    </button>
                    <small class="d-block text-muted mt-1">Complete las 3 fases primero</small>
                <?php elseif (!$hayAprobadoAnioActual): ?>
                    <a href="<?= base_url('documentos/generar/programa_induccion_reinduccion/' . $cliente['id_cliente']) ?>"
                       class="btn btn-success">
                        <i class="bi bi-magic me-1"></i>Crear con IA <?= $anioActual ?>
                    </a>
                <?php else: ?>
                    <a href="<?= base_url('documentos/generar/programa_induccion_reinduccion/' . $cliente['id_cliente']) ?>"
                       class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-arrow-repeat me-1"></i>Nueva Version
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Informacion del Auditor -->
<div class="alert alert-info border-0 mb-4">
    <div class="d-flex">
        <div class="me-3">
            <i class="bi bi-clipboard-check fs-4"></i>
        </div>
        <div>
            <strong>Requisito del Auditor:</strong>
            <p class="mb-0 small">
                "Se evidencia el cumplimiento del programa anual de capacitacion y de los procesos de induccion
                y reinduccion en seguridad y salud en el trabajo previa al inicio de sus labores que cubre a todos
                los trabajadores independientemente de su forma de vinculacion y/o contratacion e incluye la
                descripcion de las actividades a realizar, informacion de la identificacion de riesgo, evaluacion
                y valoracion de riesgos y establecimiento de controles para prevencion de los ATEL"
            </p>
        </div>
    </div>
</div>

<!-- Panel de Fases -->
<?= view('documentacion/_components/panel_fases', [
    'fasesInfo' => $fasesInfo ?? null,
    'tipoCarpetaFases' => 'induccion_reinduccion',
    'cliente' => $cliente,
    'carpeta' => $carpeta,
    'documentoExistente' => $documentoExistente ?? null
]) ?>

<!-- Tabla de Documentos SST -->
<?= view('documentacion/_components/tabla_documentos_sst', [
    'tipoCarpetaFases' => 'induccion_reinduccion',
    'documentosSSTAprobados' => $documentosSSTAprobados ?? [],
    'cliente' => $cliente
]) ?>

<!-- Subcarpetas -->
<?php if (!empty($subcarpetas)): ?>
<div class="row">
    <?= view('documentacion/_components/lista_subcarpetas', [
        'subcarpetas' => $subcarpetas ?? []
    ]) ?>
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
                    Adjunte evidencias de induccion y reinduccion: registros de asistencia,
                    evaluaciones, actas u otros soportes complementarios.
                </p>
            </div>
            <div class="col-md-4 text-end">
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalAdjuntarSoporteInduccion">
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
        <?php
        $soportes = $soportesAdicionales ?? [];
        ?>
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
                                <td><code><?= esc($soporte['codigo'] ?? 'SOP-IND') ?></code></td>
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

<!-- Modal de Adjuntar Soporte -->
<div class="modal fade" id="modalAdjuntarSoporteInduccion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-cloud-upload me-2"></i>Adjuntar Soporte
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="formAdjuntarSoporteInduccion"
                  action="<?= base_url('documentos-sst/adjuntar-soporte-induccion') ?>"
                  method="post"
                  enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id_cliente" value="<?= $cliente['id_cliente'] ?>">
                    <input type="hidden" name="id_carpeta" value="<?= $carpeta['id_carpeta'] ?>">

                    <!-- Toggle Archivo/Enlace -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de carga</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="tipo_carga_soporte_induccion"
                                   id="tipoCargaSoporteInduccionArchivo" value="archivo" checked>
                            <label class="btn btn-outline-primary" for="tipoCargaSoporteInduccionArchivo">
                                <i class="bi bi-file-earmark-arrow-up me-1"></i>Subir Archivo
                            </label>
                            <input type="radio" class="btn-check" name="tipo_carga_soporte_induccion"
                                   id="tipoCargaSoporteInduccionEnlace" value="enlace">
                            <label class="btn btn-outline-primary" for="tipoCargaSoporteInduccionEnlace">
                                <i class="bi bi-link-45deg me-1"></i>Pegar Enlace
                            </label>
                        </div>
                    </div>

                    <!-- Campo Archivo -->
                    <div class="mb-3" id="campoArchivoSoporteInduccion">
                        <label for="archivo_soporte_induccion" class="form-label">Archivo</label>
                        <input type="file" class="form-control" id="archivo_soporte_induccion" name="archivo_soporte"
                               accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx,.doc,.docx">
                        <div class="form-text">PDF, JPG, PNG, Excel, Word. Max: 10MB</div>
                    </div>

                    <!-- Campo Enlace (oculto por defecto) -->
                    <div class="mb-3 d-none" id="campoEnlaceSoporteInduccion">
                        <label for="url_externa_soporte_induccion" class="form-label">Enlace</label>
                        <input type="url" class="form-control" id="url_externa_soporte_induccion" name="url_externa"
                               placeholder="https://drive.google.com/...">
                    </div>

                    <!-- Descripcion -->
                    <div class="mb-3">
                        <label for="descripcion_soporte_induccion" class="form-label">Descripcion <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="descripcion_soporte_induccion" name="descripcion" required
                               placeholder="Ej: Registro asistencia induccion, Evaluacion trabajador...">
                    </div>

                    <!-- Ano -->
                    <div class="mb-3">
                        <label for="anio_soporte_induccion" class="form-label">Ano</label>
                        <select class="form-select" id="anio_soporte_induccion" name="anio">
                            <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                <option value="<?= $y ?>" <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <!-- Observaciones -->
                    <div class="mb-3">
                        <label for="observaciones_soporte_induccion" class="form-label">Observaciones (opcional)</label>
                        <textarea class="form-control" id="observaciones_soporte_induccion" name="observaciones" rows="2"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnAdjuntarSoporteInduccion">
                        <i class="bi bi-cloud-upload me-1"></i>Adjuntar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript para Toggle -->
<script>
document.querySelectorAll('input[name="tipo_carga_soporte_induccion"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const isArchivo = this.value === 'archivo';
        document.getElementById('campoArchivoSoporteInduccion').classList.toggle('d-none', !isArchivo);
        document.getElementById('campoEnlaceSoporteInduccion').classList.toggle('d-none', isArchivo);
        document.getElementById('archivo_soporte_induccion').required = isArchivo;
        document.getElementById('url_externa_soporte_induccion').required = !isArchivo;
        if (!isArchivo) {
            document.getElementById('archivo_soporte_induccion').value = '';
        } else {
            document.getElementById('url_externa_soporte_induccion').value = '';
        }
    });
});

document.getElementById('formAdjuntarSoporteInduccion')?.addEventListener('submit', function() {
    const btn = document.getElementById('btnAdjuntarSoporteInduccion');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Subiendo...';
});
</script>
