<?php
/**
 * Vista de Tipo: 2.7.1 Matriz de Requisitos Legales
 * Procedimiento simple con IA (sin fases previas)
 * Variables: $carpeta, $cliente, $documentosSSTAprobados
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
                    <a href="<?= base_url('documentos/generar/procedimiento_matriz_legal/' . $cliente['id_cliente']) ?>"
                       class="btn btn-outline-success">
                        <i class="bi bi-arrow-repeat me-1"></i>Nueva version <?= date('Y') ?>
                    </a>
                <?php else: ?>
                    <a href="<?= base_url('documentos/generar/procedimiento_matriz_legal/' . $cliente['id_cliente']) ?>"
                       class="btn btn-success">
                        <i class="bi bi-magic me-1"></i>Crear con IA <?= date('Y') ?>
                    </a>
                <?php endif; ?>
                <button type="button" class="btn btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#modalAdjuntarML">
                    <i class="bi bi-cloud-upload me-1"></i>Adjuntar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Informacion sobre el modulo -->
<div class="alert alert-primary mb-4">
    <div class="d-flex align-items-start">
        <i class="bi bi-journal-bookmark me-3 fs-4"></i>
        <div>
            <h6 class="mb-1">Procedimiento para Identificacion de Requisitos Legales</h6>
            <p class="mb-0 small">
                Documento que establece como <?= esc($cliente['nombre_cliente'] ?? 'la organizacion') ?> identifica, accede y
                mantiene actualizados los requisitos legales aplicables en Seguridad y Salud en el Trabajo.
            </p>
        </div>
    </div>
</div>

<!-- Acceso rapido al modulo de Matriz Legal -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
        <h6 class="mb-0"><i class="bi bi-table me-2"></i>Modulo de Matriz Legal</h6>
    </div>
    <div class="card-body">
        <p class="mb-3">Gestione las normas aplicables a <?= esc($cliente['nombre_cliente'] ?? 'la organizacion') ?> en el modulo de Matriz Legal.</p>
        <a href="<?= base_url('matriz-legal') ?>" class="btn btn-outline-primary" target="_blank">
            <i class="bi bi-box-arrow-up-right me-1"></i>Abrir Matriz Legal
        </a>
    </div>
</div>

<!-- Tabla de Documentos SST -->
<?= view('documentacion/_components/tabla_documentos_sst', [
    'tipoCarpetaFases' => 'matriz_legal',
    'documentosSSTAprobados' => $documentosSSTAprobados ?? [],
    'cliente' => $cliente
]) ?>

<!-- Subcarpetas -->
<div class="row">
    <?= view('documentacion/_components/lista_subcarpetas', ['subcarpetas' => $subcarpetas ?? []]) ?>
</div>

<?= view('documentacion/_components/tabla_soportes', [
    'soportes' => $soportesAdicionales ?? [],
    'titulo' => 'Soportes Matriz Legal',
    'subtitulo' => 'Requisitos legales y normativos',
    'icono' => 'bi-journal-bookmark',
    'colorHeader' => 'primary',
    'codigoDefault' => 'SOP-MLG',
    'emptyIcon' => 'bi-journal-bookmark',
    'emptyMessage' => 'No hay soportes adjuntados aun.',
    'emptyHint' => 'Use el boton "Adjuntar Soporte" para agregar evidencias.'
]) ?>

<!-- Modal para Adjuntar Soporte -->
<div class="modal fade" id="modalAdjuntarML" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-cloud-upload me-2"></i>Adjuntar Soporte Matriz Legal</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAdjuntarML" action="<?= base_url('documentos-sst/adjuntar-soporte-matriz-legal') ?>" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id_cliente" value="<?= $cliente['id_cliente'] ?>">
                    <input type="hidden" name="id_carpeta" value="<?= $carpeta['id_carpeta'] ?>">
                    <input type="hidden" name="codigo_soporte" value="SOP-MRL">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de carga</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="tipo_carga" id="tipoCargaArchivoML" value="archivo" checked>
                            <label class="btn btn-outline-primary" for="tipoCargaArchivoML">
                                <i class="bi bi-file-earmark-arrow-up me-1"></i>Subir Archivo
                            </label>
                            <input type="radio" class="btn-check" name="tipo_carga" id="tipoCargaEnlaceML" value="enlace">
                            <label class="btn btn-outline-primary" for="tipoCargaEnlaceML">
                                <i class="bi bi-link-45deg me-1"></i>Pegar Enlace
                            </label>
                        </div>
                    </div>

                    <div class="mb-3" id="campoArchivoML">
                        <label class="form-label">Archivo (PDF, Excel, Word)</label>
                        <input type="file" class="form-control" name="archivo_soporte" accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx,.doc,.docx">
                    </div>

                    <div class="mb-3 d-none" id="campoEnlaceML">
                        <label class="form-label">Enlace (Google Drive, OneDrive)</label>
                        <input type="url" class="form-control" name="url_externa" placeholder="https://...">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripcion</label>
                        <input type="text" class="form-control" name="descripcion" required placeholder="Ej: Matriz legal exportada, Evaluacion cumplimiento...">
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
                    <button type="submit" class="btn btn-primary" id="btnAdjuntarML">
                        <i class="bi bi-cloud-upload me-1"></i>Adjuntar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('#modalAdjuntarML input[name="tipo_carga"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const isArchivo = this.value === 'archivo';
        document.getElementById('campoArchivoML').classList.toggle('d-none', !isArchivo);
        document.getElementById('campoEnlaceML').classList.toggle('d-none', isArchivo);
    });
});
document.getElementById('formAdjuntarML')?.addEventListener('submit', function() {
    const btn = document.getElementById('btnAdjuntarML');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Subiendo...';
});
</script>
