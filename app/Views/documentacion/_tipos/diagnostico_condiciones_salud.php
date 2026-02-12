<?php
/**
 * Vista de Tipo: 3.1.1 Descripcion sociodemografica - Diagnostico de Condiciones de Salud
 * Hibrida: Documento formal con IA + Soportes adjuntos
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
                    <a href="<?= base_url('documentos/generar/procedimiento_evaluaciones_medicas/' . $cliente['id_cliente']) ?>"
                       class="btn btn-outline-success">
                        <i class="bi bi-arrow-repeat me-1"></i>Nueva version <?= date('Y') ?>
                    </a>
                <?php else: ?>
                    <a href="<?= base_url('documentos/generar/procedimiento_evaluaciones_medicas/' . $cliente['id_cliente']) ?>"
                       class="btn btn-success">
                        <i class="bi bi-magic me-1"></i>Crear con IA <?= date('Y') ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Informacion sobre el modulo -->
<div class="alert alert-success mb-4">
    <div class="d-flex align-items-start">
        <i class="bi bi-heart-pulse me-3 fs-4"></i>
        <div>
            <h6 class="mb-1">Descripcion Sociodemografica y Diagnostico de Condiciones de Salud</h6>
            <p class="mb-0 small">
                Documento que contiene la descripcion sociodemografica de los trabajadores y el diagnostico
                de condiciones de salud de <?= esc($cliente['nombre_cliente'] ?? 'la organizacion') ?>,
                incluyendo el procedimiento de evaluaciones medicas ocupacionales (ingreso, periodicas, egreso),
                conforme al estandar 3.1.1 de la Resolucion 0312/2019.
            </p>
        </div>
    </div>
</div>

<!-- Requisitos del Estandar -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-success text-white">
        <h6 class="mb-0"><i class="bi bi-check2-square me-2"></i>Requisitos del Estandar 3.1.1</h6>
    </div>
    <div class="card-body">
        <p class="mb-3">Segun la Resolucion 0312/2019, el auditor verifica:</p>
        <ul class="mb-0">
            <li>Se realizan evaluaciones medicas ocupacionales de acuerdo con la normatividad y los peligros/riesgos</li>
            <li>Esta definida la frecuencia de evaluaciones acorde con la magnitud de los riesgos y el estado de salud</li>
            <li>Se comunican los resultados por escrito a los trabajadores</li>
            <li>Los resultados constan en la historia medica del trabajador</li>
            <li>Se cuenta con profesiograma y diagnostico de condiciones de salud</li>
        </ul>
    </div>
</div>

<!-- Panel de Fases (si aplica) -->
<?= view('documentacion/_components/panel_fases', [
    'fasesInfo' => $fasesInfo ?? null,
    'tipoCarpetaFases' => 'diagnostico_condiciones_salud',
    'cliente' => $cliente,
    'carpeta' => $carpeta,
    'documentoExistente' => $documentoExistente ?? null
]) ?>

<!-- Tabla de Documentos SST generados con IA -->
<?= view('documentacion/_components/tabla_documentos_sst', [
    'tipoCarpetaFases' => 'diagnostico_condiciones_salud',
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
                    Adjunte soportes del perfil sociodemografico y diagnostico de salud: encuestas,
                    analisis estadisticos, informes de morbilidad, ausentismo, condiciones de salud, etc.
                </p>
            </div>
            <div class="col-md-4 text-end">
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalAdjuntarDCS">
                    <i class="bi bi-cloud-upload me-1"></i>Adjuntar Soporte
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Soportes Adjuntados -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-secondary text-white">
        <h6 class="mb-0"><i class="bi bi-paperclip me-2"></i>Soportes Adjuntados</h6>
    </div>
    <div class="card-body">
        <?php $soportes = $soportesAdicionales ?? []; ?>
        <?php if (!empty($soportes)): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:120px">Codigo</th>
                            <th>Descripcion</th>
                            <th style="width:80px">Ano</th>
                            <th style="width:100px">Fecha</th>
                            <th style="width:100px">Tipo</th>
                            <th style="width:150px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($soportes as $s): $esEnlace = !empty($s['url_externa']); $url = $esEnlace ? $s['url_externa'] : ($s['archivo_pdf'] ?? '#'); ?>
                        <tr>
                            <td><code><?= esc($s['codigo'] ?? 'SOP-DCS') ?></code></td>
                            <td>
                                <strong><?= esc($s['titulo']) ?></strong>
                                <?php if (!empty($s['observaciones'])): ?>
                                    <br><small class="text-muted"><?= esc($s['observaciones']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge bg-secondary"><?= esc($s['anio']) ?></span></td>
                            <td><small><?= date('d/m/Y', strtotime($s['created_at'] ?? 'now')) ?></small></td>
                            <td>
                                <?= $esEnlace
                                    ? '<span class="badge bg-primary"><i class="bi bi-link-45deg me-1"></i>Enlace</span>'
                                    : '<span class="badge bg-secondary"><i class="bi bi-file-earmark me-1"></i>Archivo</span>'
                                ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= esc($url) ?>" class="btn btn-outline-primary" target="_blank"><i class="bi bi-eye"></i></a>
                                    <?php if (!$esEnlace): ?>
                                    <a href="<?= esc($url) ?>" class="btn btn-outline-danger" download><i class="bi bi-download"></i></a>
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
                <i class="bi bi-inbox text-muted" style="font-size:2rem"></i>
                <p class="text-muted mt-2 mb-0">No hay soportes adjuntados aun.</p>
                <small class="text-muted">Use el boton "Adjuntar Soporte" para agregar evidencias.</small>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Adjuntar Soporte -->
<div class="modal fade" id="modalAdjuntarDCS" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-cloud-upload me-2"></i>Adjuntar Soporte</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAdjuntarSoporteDCS" action="<?= base_url('documentos-sst/adjuntar-soporte-diagnostico-salud') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <input type="hidden" name="id_cliente" value="<?= $cliente['id_cliente'] ?>">
                    <input type="hidden" name="id_carpeta" value="<?= $carpeta['id_carpeta'] ?>">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de carga</label>
                        <div class="btn-group w-100">
                            <input type="radio" class="btn-check" name="tipo_carga" id="tcArchDCS" value="archivo" checked>
                            <label class="btn btn-outline-primary" for="tcArchDCS"><i class="bi bi-file-earmark-arrow-up me-1"></i>Archivo</label>
                            <input type="radio" class="btn-check" name="tipo_carga" id="tcEnlDCS" value="enlace">
                            <label class="btn btn-outline-primary" for="tcEnlDCS"><i class="bi bi-link-45deg me-1"></i>Enlace</label>
                        </div>
                    </div>
                    <div class="mb-3" id="cArchDCS">
                        <label class="form-label">Archivo</label>
                        <input type="file" class="form-control" name="archivo_soporte" id="archivo_soporte_dcs" accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx,.doc,.docx">
                        <div class="form-text">PDF, JPG, PNG, Excel, Word. Max: 10MB</div>
                    </div>
                    <div class="mb-3 d-none" id="cEnlDCS">
                        <label class="form-label">Enlace</label>
                        <input type="url" class="form-control" name="url_externa" id="url_externa_dcs" placeholder="https://...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripcion <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="descripcion" required placeholder="Ej: Perfil sociodemografico 2026, Diagnostico salud...">
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
                    <button type="submit" class="btn btn-primary" id="btnAdjDCS"><i class="bi bi-cloud-upload me-1"></i>Adjuntar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.querySelectorAll('#modalAdjuntarDCS input[name="tipo_carga"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const isArchivo = this.value === 'archivo';
        document.getElementById('cArchDCS').classList.toggle('d-none', !isArchivo);
        document.getElementById('cEnlDCS').classList.toggle('d-none', isArchivo);
        document.getElementById('archivo_soporte_dcs').required = isArchivo;
        document.getElementById('url_externa_dcs').required = !isArchivo;
        if (!isArchivo) {
            document.getElementById('archivo_soporte_dcs').value = '';
        } else {
            document.getElementById('url_externa_dcs').value = '';
        }
    });
});

document.getElementById('formAdjuntarSoporteDCS')?.addEventListener('submit', function() {
    const btn = document.getElementById('btnAdjDCS');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Subiendo...';
});
</script>
