<?php
/**
 * Vista de Tipo: 4.2.5 Mantenimiento periodico de instalaciones, equipos, maquinas, herramientas
 * Programa TIPO B con flujo de 3 FASES:
 *
 * FLUJO:
 * 1. Fase 1: Actividades Mantenimiento -> van al PTA (tipo_servicio = "Mantenimiento Periodico")
 * 2. Fase 2: Indicadores Mantenimiento -> van a tbl_indicadores_sst (categoria = "mantenimiento_periodico")
 * 3. Fase 3: Documento IA -> se genera alimentado de la BD
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
                    <i class="bi bi-tools text-primary me-2"></i>
                    <?= esc($carpeta['nombre']) ?>
                </h4>
                <?php if (!empty($carpeta['codigo'])): ?>
                    <span class="badge bg-light text-dark me-2"><?= esc($carpeta['codigo']) ?></span>
                <?php endif; ?>
                <?php if (!empty($carpeta['descripcion'])): ?>
                    <p class="text-muted mb-0 mt-2"><?= esc($carpeta['descripcion']) ?></p>
                <?php else: ?>
                    <p class="text-muted mb-0 mt-2">
                        Programa de mantenimiento preventivo, correctivo y periodico de las instalaciones,
                        equipos, maquinas y herramientas de <?= esc($cliente['nombre_cliente'] ?? 'la organizacion') ?>.
                        <strong>Requiere completar fases previas.</strong>
                    </p>
                <?php endif; ?>
            </div>
            <div class="col-md-4 text-end">
                <?php if (isset($fasesInfo) && $fasesInfo && !$fasesInfo['puede_generar_documento']): ?>
                    <button type="button" class="btn btn-secondary" disabled title="Complete las fases previas">
                        <i class="bi bi-lock me-1"></i>Crear con IA
                    </button>
                    <small class="d-block text-muted mt-1">Complete las fases primero</small>
                <?php elseif (!$hayAprobadoAnioActual): ?>
                    <a href="<?= base_url('documentos/generar/programa_mantenimiento_periodico/' . $cliente['id_cliente']) ?>"
                       class="btn btn-success">
                        <i class="bi bi-magic me-1"></i>Crear con IA <?= $anioActual ?>
                    </a>
                <?php else: ?>
                    <a href="<?= base_url('documentos/generar/programa_mantenimiento_periodico/' . $cliente['id_cliente']) ?>"
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
    'tipoCarpetaFases' => 'mantenimiento_periodico',
    'cliente' => $cliente,
    'carpeta' => $carpeta,
    'documentoExistente' => $documentoExistente ?? null
]) ?>

<!-- Tabla de Documentos SST -->
<?= view('documentacion/_components/tabla_documentos_sst', [
    'tipoCarpetaFases' => 'mantenimiento_periodico',
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
<!-- SECCION ADICIONAL: SOPORTES (ReportList)                     -->
<!-- Permite adjuntar evidencias adicionales sin afectar fases    -->
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
                    Adjunte evidencias complementarias: ordenes de trabajo, cronogramas de mantenimiento,
                    fichas tecnicas de equipos, registros fotograficos u otros soportes del programa.
                </p>
            </div>
            <div class="col-md-4 text-end">
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalAdjuntarMTP">
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
                                <td><code><?= esc($soporte['codigo'] ?? 'SOP-MTP') ?></code></td>
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
<div class="modal fade" id="modalAdjuntarMTP" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-cloud-upload me-2"></i>Adjuntar Soporte Mantenimiento Periodico</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAdjuntarMTP" action="<?= base_url('documentos-sst/adjuntar-soporte-mantenimiento-periodico') ?>" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id_cliente" value="<?= $cliente['id_cliente'] ?>">
                    <input type="hidden" name="id_carpeta" value="<?= $carpeta['id_carpeta'] ?>">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de carga</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="tipo_carga_mtp" id="tipoCargaArchivoMTP" value="archivo" checked>
                            <label class="btn btn-outline-primary" for="tipoCargaArchivoMTP">
                                <i class="bi bi-file-earmark-arrow-up me-1"></i>Subir Archivo
                            </label>
                            <input type="radio" class="btn-check" name="tipo_carga_mtp" id="tipoCargaEnlaceMTP" value="enlace">
                            <label class="btn btn-outline-primary" for="tipoCargaEnlaceMTP">
                                <i class="bi bi-link-45deg me-1"></i>Pegar Enlace
                            </label>
                        </div>
                    </div>

                    <div class="mb-3" id="campoArchivoMTP">
                        <label for="archivo_soporte_mtp" class="form-label">Archivo</label>
                        <input type="file" class="form-control" id="archivo_soporte_mtp" name="archivo_soporte" accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx,.doc,.docx">
                        <div class="form-text">PDF, JPG, PNG, Excel, Word. Max: 10MB</div>
                    </div>

                    <div class="mb-3 d-none" id="campoEnlaceMTP">
                        <label for="url_externa_mtp" class="form-label">Enlace</label>
                        <input type="url" class="form-control" id="url_externa_mtp" name="url_externa" placeholder="https://drive.google.com/...">
                    </div>

                    <div class="mb-3">
                        <label for="descripcion_mtp" class="form-label">Descripcion <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="descripcion_mtp" name="descripcion" required placeholder="Ej: Orden de trabajo correctivo, Ficha tecnica equipo...">
                    </div>

                    <div class="mb-3">
                        <label for="anio_mtp" class="form-label">Ano</label>
                        <select class="form-select" id="anio_mtp" name="anio">
                            <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                <option value="<?= $y ?>" <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="observaciones_mtp" class="form-label">Observaciones (opcional)</label>
                        <textarea class="form-control" id="observaciones_mtp" name="observaciones" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnAdjuntarMTP">
                        <i class="bi bi-cloud-upload me-1"></i>Adjuntar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('input[name="tipo_carga_mtp"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const isArchivo = this.value === 'archivo';
        document.getElementById('campoArchivoMTP').classList.toggle('d-none', !isArchivo);
        document.getElementById('campoEnlaceMTP').classList.toggle('d-none', isArchivo);
        document.getElementById('archivo_soporte_mtp').required = isArchivo;
        document.getElementById('url_externa_mtp').required = !isArchivo;
        if (!isArchivo) {
            document.getElementById('archivo_soporte_mtp').value = '';
        } else {
            document.getElementById('url_externa_mtp').value = '';
        }
    });
});

document.getElementById('formAdjuntarMTP')?.addEventListener('submit', function() {
    const btn = document.getElementById('btnAdjuntarMTP');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Subiendo...';
});
</script>
