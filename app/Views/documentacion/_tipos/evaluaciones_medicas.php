<?php
/**
 * Vista de Tipo: 3.1.4 Realizacion de evaluaciones medicas ocupacionales
 * Hibrida: Programa formal con IA (3 partes) + Soportes adjuntos
 * Variables: $carpeta, $cliente, $documentosSSTAprobados, $soportesAdicionales, $subcarpetas,
 *            $fasesInfo, $tipoCarpetaFases, $contextoCliente, $documentoExistente
 */

// Verificar si hay documento IA aprobado para el año actual
$hayAprobadoAnioActual = false;
if (!empty($documentosSSTAprobados)) {
    foreach ($documentosSSTAprobados as $d) {
        if (($d['anio'] ?? '') == date('Y')) {
            $hayAprobadoAnioActual = true;
            break;
        }
    }
}

// Verificar si las fases están completas para habilitar el botón
$puedeGenerarDocumento = isset($fasesInfo) && $fasesInfo && $fasesInfo['puede_generar_documento'];
?>

<!-- Card de Carpeta con Boton IA -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="mb-1">
                    <i class="bi bi-clipboard2-pulse text-danger me-2"></i>
                    <?= esc($carpeta['nombre']) ?>
                </h4>
                <?php if (!empty($carpeta['codigo'])): ?>
                    <span class="badge bg-light text-dark me-2"><?= esc($carpeta['codigo']) ?></span>
                <?php endif; ?>
                <?php if (!empty($carpeta['descripcion'])): ?>
                    <p class="text-muted mb-0 mt-2"><?= esc($carpeta['descripcion']) ?></p>
                <?php else: ?>
                    <p class="text-muted mb-0 mt-2">
                        Programa que verifica la realizacion de evaluaciones medicas ocupacionales de acuerdo
                        con los peligros identificados. <strong>Requiere completar fases previas.</strong>
                    </p>
                <?php endif; ?>
            </div>
            <div class="col-md-4 text-end">
                <?php if (isset($fasesInfo) && $fasesInfo && !$fasesInfo['puede_generar_documento']): ?>
                    <!-- Botón bloqueado: fases no completas -->
                    <button type="button" class="btn btn-secondary" disabled title="Complete las fases previas">
                        <i class="bi bi-lock me-1"></i>Crear con IA
                    </button>
                    <small class="d-block text-muted mt-1">Complete las fases previas</small>
                <?php elseif (!$hayAprobadoAnioActual): ?>
                    <!-- Botón activo: puede generar -->
                    <a href="<?= base_url('documentos/generar/programa_evaluaciones_medicas_ocupacionales/' . $cliente['id_cliente']) ?>"
                       class="btn btn-success">
                        <i class="bi bi-magic me-1"></i>Crear con IA <?= date('Y') ?>
                    </a>
                <?php else: ?>
                    <!-- Ya existe documento -->
                    <a href="<?= base_url('documentos/generar/programa_evaluaciones_medicas_ocupacionales/' . $cliente['id_cliente']) ?>"
                       class="btn btn-outline-success">
                        <i class="bi bi-arrow-repeat me-1"></i>Nueva version <?= date('Y') ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Informacion sobre el modulo -->
<div class="alert alert-success mb-4">
    <div class="d-flex align-items-start">
        <i class="bi bi-clipboard2-pulse me-3 fs-4"></i>
        <div>
            <h6 class="mb-1">Evaluaciones Medicas Ocupacionales - Peligros, Periodicidad, Comunicacion</h6>
            <p class="mb-0 small">
                Programa que verifica la realizacion de evaluaciones medicas ocupacionales de acuerdo con los peligros
                identificados, con frecuencia acorde a la magnitud de los riesgos y el estado de salud del trabajador,
                comunicacion de resultados y articulacion con los Programas de Vigilancia Epidemiologica de
                <?= esc($cliente['nombre_cliente'] ?? 'la organizacion') ?>,
                conforme al estandar 3.1.4 de la Resolucion 0312/2019.
            </p>
        </div>
    </div>
</div>

<!-- Requisitos del Estandar -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-success text-white">
        <h6 class="mb-0"><i class="bi bi-check2-square me-2"></i>Requisitos del Estandar 3.1.4</h6>
    </div>
    <div class="card-body">
        <p class="mb-3">Segun la Resolucion 0312/2019, el auditor verifica:</p>
        <ul class="mb-0">
            <li>Se realizan evaluaciones medicas ocupacionales de acuerdo con la normatividad y los <strong>peligros</strong> a los que se expone el trabajador</li>
            <li>La <strong>frecuencia</strong> de evaluaciones esta definida acorde con la magnitud de los riesgos</li>
            <li>Se tiene en cuenta el <strong>estado de salud del trabajador</strong> para definir la periodicidad</li>
            <li>Se consideran las recomendaciones de los <strong>Programas de Vigilancia Epidemiologica</strong></li>
            <li>Se <strong>comunican los resultados</strong> por escrito a los trabajadores (Res. 2346/2007)</li>
        </ul>
    </div>
</div>

<!-- Panel de Fases (si aplica) -->
<?= view('documentacion/_components/panel_fases', [
    'fasesInfo' => $fasesInfo ?? null,
    'tipoCarpetaFases' => 'evaluaciones_medicas',
    'cliente' => $cliente,
    'carpeta' => $carpeta,
    'documentoExistente' => $documentoExistente ?? null
]) ?>

<!-- Tabla de Documentos SST generados con IA -->
<?= view('documentacion/_components/tabla_documentos_sst', [
    'tipoCarpetaFases' => 'evaluaciones_medicas',
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
<!-- SECCION ADICIONAL: SOPORTES (adjuntos)                       -->
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
                    Adjunte soportes de evaluaciones medicas: cronogramas EMO, certificados de aptitud,
                    profesiogramas, conceptos medicos, comunicaciones a trabajadores, contratos con IPS, etc.
                </p>
            </div>
            <div class="col-md-4 text-end">
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalAdjuntarEMO">
                    <i class="bi bi-cloud-upload me-1"></i>Adjuntar Soporte
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Soportes Adjuntados -->
<?php $soportes = $soportesAdicionales ?? []; ?>
<?php if (!empty($soportes)): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-secondary text-white">
        <h6 class="mb-0"><i class="bi bi-paperclip me-2"></i>Soportes Adjuntados</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 100px;">Codigo</th>
                        <th>Descripcion</th>
                        <th style="width: 100px;">Fecha</th>
                        <th style="width: 90px;">Tipo</th>
                        <th style="width: 120px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($soportes as $s): $esEnlace = !empty($s['url_externa']); $url = $esEnlace ? $s['url_externa'] : ($s['archivo_pdf'] ?? '#'); ?>
                    <tr>
                        <td><code><?= esc($s['codigo'] ?? 'SOP-EMO') ?></code></td>
                        <td>
                            <strong><?= esc($s['titulo']) ?></strong>
                            <?php if (!empty($s['observaciones'])): ?>
                                <br><small class="text-muted"><?= esc($s['observaciones']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><small><?= date('d/m/Y', strtotime($s['created_at'] ?? 'now')) ?></small></td>
                        <td>
                            <?= $esEnlace
                                ? '<span class="badge bg-info"><i class="bi bi-link-45deg"></i> Enlace</span>'
                                : '<span class="badge bg-dark"><i class="bi bi-file-earmark"></i> Archivo</span>'
                            ?>
                        </td>
                        <td>
                            <a href="<?= esc($url) ?>"
                               class="btn btn-sm btn-outline-primary" target="_blank" title="Ver/Descargar">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php else: ?>
<div class="alert alert-light border text-center">
    <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
    <p class="text-muted mb-0 mt-2">No hay soportes adjuntados aun.</p>
</div>
<?php endif; ?>

<!-- Modal Adjuntar Soporte -->
<div class="modal fade" id="modalAdjuntarEMO" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-cloud-upload me-2"></i>Adjuntar Soporte</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAdjuntarSoporteEMO" action="<?= base_url('documentos-sst/adjuntar-soporte-evaluaciones-medicas') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <input type="hidden" name="id_cliente" value="<?= $cliente['id_cliente'] ?>">
                    <input type="hidden" name="id_carpeta" value="<?= $carpeta['id_carpeta'] ?>">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de carga</label>
                        <div class="btn-group w-100">
                            <input type="radio" class="btn-check" name="tipo_carga" id="tcArchEMO" value="archivo" checked>
                            <label class="btn btn-outline-primary" for="tcArchEMO"><i class="bi bi-file-earmark-arrow-up me-1"></i>Archivo</label>
                            <input type="radio" class="btn-check" name="tipo_carga" id="tcEnlEMO" value="enlace">
                            <label class="btn btn-outline-primary" for="tcEnlEMO"><i class="bi bi-link-45deg me-1"></i>Enlace</label>
                        </div>
                    </div>
                    <div class="mb-3" id="cArchEMO">
                        <label class="form-label">Archivo</label>
                        <input type="file" class="form-control" name="archivo_soporte" id="archivo_soporte_emo" accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx,.doc,.docx">
                        <div class="form-text">PDF, JPG, PNG, Excel, Word. Max: 10MB</div>
                    </div>
                    <div class="mb-3 d-none" id="cEnlEMO">
                        <label class="form-label">Enlace</label>
                        <input type="url" class="form-control" name="url_externa" id="url_externa_emo" placeholder="https://...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripcion <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="descripcion" required placeholder="Ej: Cronograma EMO 2026, Certificado aptitud, Profesiograma...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ano</label>
                        <select class="form-select" name="anio">
                            <?php for($y=date('Y');$y>=2020;$y--): ?>
                            <option value="<?=$y?>" <?=$y==date('Y')?'selected':''?>><?=$y?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea class="form-control" name="observaciones" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="btnAdjEMO"><i class="bi bi-cloud-upload me-1"></i>Adjuntar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.querySelectorAll('#modalAdjuntarEMO input[name="tipo_carga"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const isArchivo = this.value === 'archivo';
        document.getElementById('cArchEMO').classList.toggle('d-none', !isArchivo);
        document.getElementById('cEnlEMO').classList.toggle('d-none', isArchivo);
        document.getElementById('archivo_soporte_emo').required = isArchivo;
        document.getElementById('url_externa_emo').required = !isArchivo;
        if (!isArchivo) {
            document.getElementById('archivo_soporte_emo').value = '';
        } else {
            document.getElementById('url_externa_emo').value = '';
        }
    });
});

document.getElementById('formAdjuntarSoporteEMO')?.addEventListener('submit', function() {
    const btn = document.getElementById('btnAdjEMO');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Subiendo...';
});
</script>
