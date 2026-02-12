<?php
/**
 * Vista de Tipo: 3.1.7 Estilos de vida y entornos saludables
 * Programa TIPO B con flujo de 3 FASES:
 *
 * FLUJO:
 * 1. Fase 1: Actividades Estilos de Vida → van al PTA (tipo_servicio = "Estilos de Vida Saludable")
 * 2. Fase 2: Indicadores Estilos de Vida → van a tbl_indicadores_sst (categoria = "estilos_vida_saludable")
 * 3. Fase 3: Documento IA → se genera alimentado de la BD
 *
 * Variables: $carpeta, $cliente, $fasesInfo, $documentosSSTAprobados, $documentoExistente
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

// Verificar si las fases estan completas para habilitar el boton
$puedeGenerarDocumento = isset($fasesInfo) && $fasesInfo && $fasesInfo['puede_generar_documento'];

$anioActual = date('Y');
?>

<!-- Card de Carpeta con Boton IA -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="mb-1">
                    <i class="bi bi-heart-pulse text-success me-2"></i>
                    <?= esc($carpeta['nombre']) ?>
                </h4>
                <?php if (!empty($carpeta['codigo'])): ?>
                    <span class="badge bg-light text-dark me-2"><?= esc($carpeta['codigo']) ?></span>
                <?php endif; ?>
                <p class="text-muted mb-0 mt-2">
                    Programa que establece actividades de promocion de estilos de vida saludables,
                    controles de tabaquismo, alcoholismo, farmacodependencia y fomento de entornos
                    saludables para <?= esc($cliente['nombre_cliente'] ?? 'la organizacion') ?>.
                    <strong>Requiere completar fases previas.</strong>
                </p>
            </div>
            <div class="col-md-4 text-end">
                <?php if (isset($fasesInfo) && $fasesInfo && !$fasesInfo['puede_generar_documento']): ?>
                    <button type="button" class="btn btn-secondary" disabled title="Complete las fases previas">
                        <i class="bi bi-lock me-1"></i>Crear con IA
                    </button>
                    <small class="d-block text-muted mt-1">Complete las fases primero</small>
                <?php elseif (!$hayAprobadoAnioActual): ?>
                    <a href="<?= base_url('documentos/generar/programa_estilos_vida_saludable/' . $cliente['id_cliente']) ?>"
                       class="btn btn-success">
                        <i class="bi bi-magic me-1"></i>Crear con IA <?= $anioActual ?>
                    </a>
                <?php else: ?>
                    <a href="<?= base_url('documentos/generar/programa_estilos_vida_saludable/' . $cliente['id_cliente']) ?>"
                       class="btn btn-outline-success">
                        <i class="bi bi-arrow-repeat me-1"></i>Nueva version <?= $anioActual ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Panel de Fases (OBLIGATORIO para este programa) -->
<?= view('documentacion/_components/panel_fases', [
    'fasesInfo' => $fasesInfo ?? null,
    'tipoCarpetaFases' => 'estilos_vida_saludable',
    'cliente' => $cliente,
    'carpeta' => $carpeta,
    'documentoExistente' => $documentoExistente ?? null
]) ?>

<!-- Tabla de Documentos SST -->
<?= view('documentacion/_components/tabla_documentos_sst', [
    'tipoCarpetaFases' => 'estilos_vida_saludable',
    'documentosSSTAprobados' => $documentosSSTAprobados ?? [],
    'cliente' => $cliente
]) ?>

<!-- Subcarpetas -->
<?php if (!empty($subcarpetas)): ?>
<div class="row">
    <?= view('documentacion/_components/lista_subcarpetas', [
        'subcarpetas' => $subcarpetas
    ]) ?>
</div>
<?php endif; ?>

<!-- ============================================================ -->
<!-- SECCION ADICIONAL: SOPORTES                                  -->
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
                    Adjunte evidencias complementarias: encuestas de habitos, registros de campanas,
                    informes de tamizaje, actas de sensibilizacion u otros soportes del programa.
                </p>
            </div>
            <div class="col-md-4 text-end">
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalAdjuntarEVS">
                    <i class="bi bi-cloud-upload me-1"></i>Adjuntar Soporte
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Soportes Adjuntados (GRIS) -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-secondary text-white">
        <h6 class="mb-0">
            <i class="bi bi-paperclip me-2"></i>Soportes Adjuntados
        </h6>
    </div>
    <div class="card-body p-0">
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
                            $urlArchivo = $esEnlace ? $soporte['url_externa'] : ($soporte['archivo_pdf'] ?? '#');
                            ?>
                            <tr>
                                <td><code><?= esc($soporte['codigo'] ?? 'SOP-EVS') ?></code></td>
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

<!-- Modal para Adjuntar Soporte -->
<div class="modal fade" id="modalAdjuntarEVS" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-cloud-upload me-2"></i>Adjuntar Soporte Estilos de Vida Saludable</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAdjuntarEVS" action="<?= base_url('documentos-sst/adjuntar-soporte-estilos-vida') ?>" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id_cliente" value="<?= $cliente['id_cliente'] ?>">
                    <input type="hidden" name="id_carpeta" value="<?= $carpeta['id_carpeta'] ?>">
                    <input type="hidden" name="codigo_soporte" value="SOP-EVS">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de carga</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="tipo_carga_evs" id="tipoCargaArchivoEVS" value="archivo" checked>
                            <label class="btn btn-outline-primary" for="tipoCargaArchivoEVS">
                                <i class="bi bi-file-earmark-arrow-up me-1"></i>Subir Archivo
                            </label>
                            <input type="radio" class="btn-check" name="tipo_carga_evs" id="tipoCargaEnlaceEVS" value="enlace">
                            <label class="btn btn-outline-primary" for="tipoCargaEnlaceEVS">
                                <i class="bi bi-link-45deg me-1"></i>Pegar Enlace
                            </label>
                        </div>
                    </div>

                    <div class="mb-3" id="campoArchivoEVS">
                        <label for="archivo_soporte_evs" class="form-label">Archivo</label>
                        <input type="file" class="form-control" id="archivo_soporte_evs" name="archivo_soporte" accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx,.doc,.docx">
                        <div class="form-text">PDF, JPG, PNG, Excel, Word. Max: 10MB</div>
                    </div>

                    <div class="mb-3 d-none" id="campoEnlaceEVS">
                        <label for="url_externa_evs" class="form-label">Enlace</label>
                        <input type="url" class="form-control" id="url_externa_evs" name="url_externa" placeholder="https://drive.google.com/...">
                    </div>

                    <div class="mb-3">
                        <label for="descripcion_evs" class="form-label">Descripcion <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="descripcion_evs" name="descripcion" required placeholder="Ej: Encuesta habitos saludables, Informe campana antitabaco...">
                    </div>

                    <div class="mb-3">
                        <label for="anio_evs" class="form-label">Ano</label>
                        <select class="form-select" id="anio_evs" name="anio">
                            <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                <option value="<?= $y ?>" <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="observaciones_evs" class="form-label">Observaciones (opcional)</label>
                        <textarea class="form-control" id="observaciones_evs" name="observaciones" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnAdjuntarEVS">
                        <i class="bi bi-cloud-upload me-1"></i>Adjuntar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('input[name="tipo_carga_evs"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const isArchivo = this.value === 'archivo';
        document.getElementById('campoArchivoEVS').classList.toggle('d-none', !isArchivo);
        document.getElementById('campoEnlaceEVS').classList.toggle('d-none', isArchivo);
        document.getElementById('archivo_soporte_evs').required = isArchivo;
        document.getElementById('url_externa_evs').required = !isArchivo;
        if (!isArchivo) {
            document.getElementById('archivo_soporte_evs').value = '';
        } else {
            document.getElementById('url_externa_evs').value = '';
        }
    });
});

document.getElementById('formAdjuntarEVS')?.addEventListener('submit', function() {
    const btn = document.getElementById('btnAdjuntarEVS');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Subiendo...';
});
</script>
