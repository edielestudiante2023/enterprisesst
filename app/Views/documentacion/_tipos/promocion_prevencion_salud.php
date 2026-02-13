<?php
/**
 * Vista de Tipo: 3.1.2 Programa de Promoción y Prevención en Salud
 * Carpeta para el programa de PyP en salud con flujo de FASES
 *
 * FLUJO:
 * 1. Fase 1: Actividades PyP → van al PTA (tipo_servicio = "Programa PyP Salud")
 * 2. Fase 2: Indicadores PyP → van a tbl_indicadores_sst (categoria = "pyp_salud")
 * 3. Fase 3: Documento IA → se genera alimentado de la BD
 *
 * Variables: $carpeta, $cliente, $fasesInfo, $documentosSSTAprobados, $documentoExistente
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

// Verificar si las fases están completas para habilitar el botón
$puedeGenerarDocumento = isset($fasesInfo) && $fasesInfo && $fasesInfo['puede_generar_documento'];
?>

<!-- Card de Carpeta con Botón IA -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="mb-1">
                    <i class="bi bi-heart-pulse text-danger me-2"></i>
                    <?= esc($carpeta['nombre']) ?>
                </h4>
                <?php if (!empty($carpeta['codigo'])): ?>
                    <span class="badge bg-light text-dark me-2"><?= esc($carpeta['codigo']) ?></span>
                <?php endif; ?>
                <p class="text-muted mb-0 mt-2">
                    Programa que establece las actividades de promoción de la salud y prevención de
                    enfermedades laborales. <strong>Requiere completar fases previas.</strong>
                </p>
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
                    <a href="<?= base_url('documentos/generar/programa_promocion_prevencion_salud/' . $cliente['id_cliente']) ?>"
                       class="btn btn-success">
                        <i class="bi bi-magic me-1"></i>Crear con IA <?= date('Y') ?>
                    </a>
                <?php else: ?>
                    <!-- Ya existe documento -->
                    <a href="<?= base_url('documentos/generar/programa_promocion_prevencion_salud/' . $cliente['id_cliente']) ?>"
                       class="btn btn-outline-success">
                        <i class="bi bi-arrow-repeat me-1"></i>Nueva versión <?= date('Y') ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Panel de Fases (OBLIGATORIO para este programa) -->
<?= view('documentacion/_components/panel_fases', [
    'fasesInfo' => $fasesInfo ?? null,
    'tipoCarpetaFases' => 'promocion_prevencion_salud',
    'cliente' => $cliente,
    'carpeta' => $carpeta,
    'documentoExistente' => $documentoExistente ?? null
]) ?>

<!-- Tabla de Documentos SST -->
<?= view('documentacion/_components/tabla_documentos_sst', [
    'tipoCarpetaFases' => 'promocion_prevencion_salud',
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
                    Adjunte evidencias complementarias: registros fotográficos, actas de reunión,
                    listados de asistencia, certificados u otros soportes del programa PyP.
                </p>
            </div>
            <div class="col-md-4 text-end">
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalAdjuntarSoportePyP">
                    <i class="bi bi-cloud-upload me-1"></i>Adjuntar Soporte
                </button>
            </div>
        </div>
    </div>
</div>

<?= view('documentacion/_components/tabla_soportes', [
    'soportes' => $soportesAdicionales ?? [],
    'titulo' => 'Soportes Promocion y Prevencion',
    'subtitulo' => 'Programa de salud',
    'icono' => 'bi-heart-pulse',
    'colorHeader' => 'success',
    'codigoDefault' => 'SOP-PYP',
    'emptyIcon' => 'bi-heart-pulse',
    'emptyMessage' => 'No hay soportes adjuntados aun.',
    'emptyHint' => 'Use el boton "Adjuntar Soporte" para agregar evidencias.'
]) ?>

<!-- Modal de Adjuntar Soporte PyP -->
<div class="modal fade" id="modalAdjuntarSoportePyP" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-cloud-upload me-2"></i>Adjuntar Soporte PyP
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="formAdjuntarSoportePyP"
                  action="<?= base_url('documentos-sst/adjuntar-soporte-pyp-salud') ?>"
                  method="post"
                  enctype="multipart/form-data">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id_cliente" value="<?= $cliente['id_cliente'] ?>">
                    <input type="hidden" name="id_carpeta" value="<?= $carpeta['id_carpeta'] ?>">

                    <!-- Toggle Archivo/Enlace -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de carga</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="tipo_carga_pyp"
                                   id="tipoCargaPyPArchivo" value="archivo" checked>
                            <label class="btn btn-outline-primary" for="tipoCargaPyPArchivo">
                                <i class="bi bi-file-earmark-arrow-up me-1"></i>Subir Archivo
                            </label>
                            <input type="radio" class="btn-check" name="tipo_carga_pyp"
                                   id="tipoCargaPyPEnlace" value="enlace">
                            <label class="btn btn-outline-primary" for="tipoCargaPyPEnlace">
                                <i class="bi bi-link-45deg me-1"></i>Pegar Enlace
                            </label>
                        </div>
                    </div>

                    <!-- Campo Archivo -->
                    <div class="mb-3" id="campoArchivoPyP">
                        <label for="archivo_soporte_pyp" class="form-label">Archivo</label>
                        <input type="file" class="form-control" id="archivo_soporte_pyp" name="archivo_soporte"
                               accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx,.doc,.docx">
                        <div class="form-text">PDF, JPG, PNG, Excel, Word. Máx: 10MB</div>
                    </div>

                    <!-- Campo Enlace (oculto por defecto) -->
                    <div class="mb-3 d-none" id="campoEnlacePyP">
                        <label for="url_externa_pyp" class="form-label">Enlace</label>
                        <input type="url" class="form-control" id="url_externa_pyp" name="url_externa"
                               placeholder="https://drive.google.com/...">
                    </div>

                    <!-- Descripción -->
                    <div class="mb-3">
                        <label for="descripcion_pyp" class="form-label">Descripción <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="descripcion_pyp" name="descripcion" required
                               placeholder="Ej: Registro fotográfico jornada de salud, Listado asistencia...">
                    </div>

                    <!-- Año -->
                    <div class="mb-3">
                        <label for="anio_pyp" class="form-label">Año</label>
                        <select class="form-select" id="anio_pyp" name="anio">
                            <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                <option value="<?= $y ?>" <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <!-- Observaciones -->
                    <div class="mb-3">
                        <label for="observaciones_pyp" class="form-label">Observaciones (opcional)</label>
                        <textarea class="form-control" id="observaciones_pyp" name="observaciones" rows="2"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnAdjuntarPyP">
                        <i class="bi bi-cloud-upload me-1"></i>Adjuntar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript para Toggle Archivo/Enlace (IDs únicos para PyP) -->
<script>
document.querySelectorAll('input[name="tipo_carga_pyp"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const isArchivo = this.value === 'archivo';
        document.getElementById('campoArchivoPyP').classList.toggle('d-none', !isArchivo);
        document.getElementById('campoEnlacePyP').classList.toggle('d-none', isArchivo);
        document.getElementById('archivo_soporte_pyp').required = isArchivo;
        document.getElementById('url_externa_pyp').required = !isArchivo;
        if (!isArchivo) {
            document.getElementById('archivo_soporte_pyp').value = '';
        } else {
            document.getElementById('url_externa_pyp').value = '';
        }
    });
});

document.getElementById('formAdjuntarSoportePyP')?.addEventListener('submit', function() {
    const btn = document.getElementById('btnAdjuntarPyP');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Subiendo...';
});
</script>
