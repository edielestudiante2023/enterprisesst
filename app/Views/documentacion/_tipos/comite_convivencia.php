<?php
/**
 * Vista de Tipo: 1.1.8 Conformación Comité de Convivencia Laboral (COCOLAB)
 * Carpeta para gestionar la conformación del COCOLAB
 * Incluye:
 *   - Sistema de elecciones
 *   - Generar Informe Trimestral / Anual del COCOLAB con IA (lee /actas/20)
 *   - Adjuntar soportes manuales
 * Variables: $carpeta, $cliente, $documentosSSTAprobados, $informesCocolab
 */

// Determinar composición según número de trabajadores
$numTrabajadores = $cliente['trabajadores'] ?? 10;
$composicion = $numTrabajadores <= 19 ? '1 principal + 1 suplente' : '2 principales + 2 suplentes';

$informesCocolab = $informesCocolab ?? [];
$anioActual = (int) date('Y');
$trimestreActual = (int) ceil(((int) date('n')) / 3);
?>

<!-- Card de Carpeta con Botones -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-6">
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
            <div class="col-md-6 text-end">
                <!-- Botón principal: Sistema de Conformación -->
                <a href="<?= base_url('comites-elecciones/' . $cliente['id_cliente']) ?>" class="btn btn-warning me-2">
                    <i class="bi bi-chat-heart me-1"></i>Sistema de Conformacion
                </a>
                <!-- Botón secundario: Adjuntar Soporte -->
                <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalAdjuntarConvivencia">
                    <i class="bi bi-cloud-upload me-1"></i>Adjuntar Soporte
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- INFORMES DE GESTION DEL COCOLAB (IA)         -->
<!-- ============================================ -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-success text-white">
        <h6 class="mb-0">
            <i class="bi bi-clipboard-data me-2"></i>Informes de Gestion del COCOLAB con IA
        </h6>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">
            Genere informes <strong>trimestrales</strong> y <strong>anuales</strong> de la gestion del Comite de Convivencia Laboral.
            La IA usa los datos reales del modulo de Actas (reuniones, asistencia, casos atendidos en agregado anonimo, compromisos)
            y propone recomendaciones que el consultor puede ajustar. Mantiene confidencialidad sobre los casos.
        </p>
        <div class="row g-2">
            <div class="col-md-6">
                <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#modalGenerarTrimestralCocolab">
                    <i class="bi bi-calendar3-range me-1"></i>Ir a Informes Trimestrales
                </button>
            </div>
            <div class="col-md-6">
                <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalGenerarAnualCocolab">
                    <i class="bi bi-graph-up me-1"></i>Ir a Informe Anual
                </button>
            </div>
        </div>

        <?php if (!empty($informesCocolab)): ?>
            <hr class="my-3">
            <h6 class="text-muted mb-2"><i class="bi bi-list-check me-1"></i>Informes generados</h6>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tipo</th>
                            <th>Anio</th>
                            <th>Trim</th>
                            <th>Codigo</th>
                            <th>Estado</th>
                            <th>Actualizado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($informesCocolab as $inf): ?>
                            <?php
                                $esTrim = $inf['tipo_documento'] === 'informe_trimestral_cocolab';
                                $tipoLbl = $esTrim ? 'Trimestral' : 'Anual';
                                $tipoBadge = $esTrim ? 'bg-success' : 'bg-primary';
                                $trimNum = $inf['trimestre'] ?? null;
                                $kebab = str_replace('_', '-', $inf['tipo_documento']);
                                $verUrl = base_url('documentos-sst/' . $cliente['id_cliente'] . '/' . $kebab . '/' . $inf['anio'])
                                          . ($esTrim && $trimNum ? '?trimestre=' . $trimNum : '');
                                $editarUrl = base_url('documentos/generar/' . $inf['tipo_documento'] . '/' . $cliente['id_cliente'])
                                             . '?anio=' . $inf['anio']
                                             . ($esTrim && $trimNum ? '&trimestre=' . $trimNum : '');
                            ?>
                            <tr>
                                <td><span class="badge <?= $tipoBadge ?>"><?= $tipoLbl ?></span></td>
                                <td><?= esc($inf['anio']) ?></td>
                                <td><?= $esTrim ? ('T' . esc($trimNum ?? '?')) : '—' ?></td>
                                <td><code class="small"><?= esc($inf['codigo'] ?? '—') ?></code></td>
                                <td><span class="badge bg-secondary"><?= esc($inf['estado']) ?></span></td>
                                <td class="small text-muted"><?= esc(date('d/m/Y H:i', strtotime($inf['updated_at'] ?? $inf['created_at'] ?? 'now'))) ?></td>
                                <td class="text-end">
                                    <a href="<?= esc($verUrl) ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Ver">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= esc($editarUrl) ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: anio + trimestre para generar trimestral COCOLAB -->
<div class="modal fade" id="modalGenerarTrimestralCocolab" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-calendar3-range me-2"></i>Informe Trimestral del COCOLAB</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted">Seleccione el anio y trimestre del informe a generar (o continuar editando si ya existe).</p>
                <div class="mb-3">
                    <label for="trimAnioCoc" class="form-label">Anio</label>
                    <select id="trimAnioCoc" class="form-select">
                        <?php for ($y = $anioActual + 1; $y >= 2022; $y--): ?>
                            <option value="<?= $y ?>" <?= $y === $anioActual ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Trimestre</label>
                    <div class="btn-group w-100" role="group">
                        <?php for ($t = 1; $t <= 4; $t++): ?>
                            <input type="radio" class="btn-check" name="trimNumeroOptCoc" id="trimNumCoc<?= $t ?>" value="<?= $t ?>" <?= $t === $trimestreActual ? 'checked' : '' ?>>
                            <label class="btn btn-outline-success" for="trimNumCoc<?= $t ?>">T<?= $t ?></label>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a id="btnIrTrimestralCoc" href="#" class="btn btn-success">
                    <i class="bi bi-arrow-right-circle me-1"></i>Continuar
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal: anio para generar anual COCOLAB -->
<div class="modal fade" id="modalGenerarAnualCocolab" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-graph-up me-2"></i>Informe Anual del COCOLAB</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted">Seleccione el anio del informe anual a generar (o continuar editando si ya existe).</p>
                <div class="mb-3">
                    <label for="anuAnioCoc" class="form-label">Anio</label>
                    <select id="anuAnioCoc" class="form-select">
                        <?php for ($y = $anioActual; $y >= 2022; $y--): ?>
                            <option value="<?= $y ?>" <?= $y === $anioActual ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a id="btnIrAnualCoc" href="#" class="btn btn-primary">
                    <i class="bi bi-arrow-right-circle me-1"></i>Continuar
                </a>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const idClienteCocolab = <?= (int)($cliente['id_cliente'] ?? 0) ?>;
    const baseGenerar = <?= json_encode(base_url('documentos/generar')) ?>;

    function actualizarUrlTrim() {
        const anio = document.getElementById('trimAnioCoc').value;
        const trimRadio = document.querySelector('input[name="trimNumeroOptCoc"]:checked');
        const trim = trimRadio ? trimRadio.value : 1;
        const url = `${baseGenerar}/informe_trimestral_cocolab/${idClienteCocolab}?anio=${anio}&trimestre=${trim}`;
        document.getElementById('btnIrTrimestralCoc').setAttribute('href', url);
    }
    function actualizarUrlAnu() {
        const anio = document.getElementById('anuAnioCoc').value;
        const url = `${baseGenerar}/informe_anual_cocolab/${idClienteCocolab}?anio=${anio}`;
        document.getElementById('btnIrAnualCoc').setAttribute('href', url);
    }
    document.addEventListener('change', function (e) {
        if (e.target && (e.target.id === 'trimAnioCoc' || e.target.name === 'trimNumeroOptCoc')) actualizarUrlTrim();
        if (e.target && e.target.id === 'anuAnioCoc') actualizarUrlAnu();
    });
    document.getElementById('modalGenerarTrimestralCocolab')?.addEventListener('shown.bs.modal', actualizarUrlTrim);
    document.getElementById('modalGenerarAnualCocolab')?.addEventListener('shown.bs.modal', actualizarUrlAnu);
})();
</script>

<!-- Información sobre soportes manuales -->
<div class="alert alert-secondary mb-4">
    <div class="d-flex align-items-start">
        <i class="bi bi-paperclip me-3 fs-4"></i>
        <div>
            <h6 class="mb-1">Soportes Adicionales</h6>
            <p class="mb-0 small">
                Use esta seccion para adjuntar soportes adicionales del Comite de Convivencia:
                resoluciones, reglamento interno, capacitaciones, denuncias atendidas, etc.
            </p>
        </div>
    </div>
</div>

<?= view('documentacion/_components/tabla_soportes', [
    'soportes' => $documentosSSTAprobados ?? [],
    'titulo' => 'Soportes Comite de Convivencia',
    'subtitulo' => 'Documentos del COCOLAB',
    'icono' => 'bi-heart-pulse',
    'colorHeader' => 'success',
    'codigoDefault' => 'SOP-CONV',
    'emptyIcon' => 'bi-heart-pulse',
    'emptyMessage' => 'No hay soportes del Comite de Convivencia adjuntados aun.',
    'emptyHint' => 'Use el boton "Adjuntar Soporte" para agregar documentos.'
]) ?>

<!-- Subcarpetas (si las hay) -->
<div class="row">
    <?= view('documentacion/_components/lista_subcarpetas', [
        'subcarpetas' => $subcarpetas ?? []
    ]) ?>
</div>

<!-- Modal para Adjuntar Soporte Comité de Convivencia -->
<div class="modal fade" id="modalAdjuntarConvivencia" tabindex="-1" aria-labelledby="modalAdjuntarConvivenciaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalAdjuntarConvivenciaLabel">
                    <i class="bi bi-cloud-upload me-2"></i>Adjuntar Soporte Comité de Convivencia
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formAdjuntarConvivencia" action="<?= base_url('documentos-sst/adjuntar-soporte-convivencia') ?>" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id_cliente" value="<?= $cliente['id_cliente'] ?>">
                    <input type="hidden" name="id_carpeta" value="<?= $carpeta['id_carpeta'] ?>">

                    <!-- Tipo de carga -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de carga</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="tipo_carga" id="tipoCargaArchivoConv" value="archivo" checked>
                            <label class="btn btn-outline-success" for="tipoCargaArchivoConv">
                                <i class="bi bi-file-earmark-arrow-up me-1"></i>Subir Archivo
                            </label>
                            <input type="radio" class="btn-check" name="tipo_carga" id="tipoCargaEnlaceConv" value="enlace">
                            <label class="btn btn-outline-success" for="tipoCargaEnlaceConv">
                                <i class="bi bi-link-45deg me-1"></i>Pegar Enlace
                            </label>
                        </div>
                    </div>

                    <!-- Campo para archivo -->
                    <div class="mb-3" id="campoArchivoConv">
                        <label for="archivo_convivencia" class="form-label">
                            <i class="bi bi-file-earmark-pdf me-1"></i>Archivo (PDF, Excel, Imagen)
                        </label>
                        <input type="file" class="form-control" id="archivo_convivencia" name="archivo_soporte"
                               accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx,.doc,.docx">
                        <div class="form-text">Formatos: PDF, JPG, PNG, Excel, Word. Máximo: 10MB</div>
                    </div>

                    <!-- Campo para enlace -->
                    <div class="mb-3 d-none" id="campoEnlaceConv">
                        <label for="url_externa_conv" class="form-label">
                            <i class="bi bi-link-45deg me-1"></i>Enlace (Google Drive, OneDrive, etc.)
                        </label>
                        <input type="url" class="form-control" id="url_externa_conv" name="url_externa"
                               placeholder="https://drive.google.com/...">
                        <div class="form-text">Pegue el enlace compartido del archivo en la nube</div>
                    </div>

                    <!-- Descripción -->
                    <div class="mb-3">
                        <label for="descripcion_conv" class="form-label">Descripción</label>
                        <input type="text" class="form-control" id="descripcion_conv" name="descripcion" required
                               placeholder="Ej: Acta conformación Comité Convivencia 2026, Resolución...">
                    </div>

                    <!-- Año -->
                    <div class="mb-3">
                        <label for="anio_conv" class="form-label">Año</label>
                        <select class="form-select" id="anio_conv" name="anio">
                            <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                <option value="<?= $y ?>" <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <!-- Observaciones -->
                    <div class="mb-3">
                        <label for="observaciones_conv" class="form-label">Observaciones (opcional)</label>
                        <textarea class="form-control" id="observaciones_conv" name="observaciones" rows="2"
                                  placeholder="Notas adicionales sobre el documento..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="btnAdjuntarConv">
                        <i class="bi bi-cloud-upload me-1"></i>Adjuntar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Toggle entre archivo y enlace
document.querySelectorAll('#modalAdjuntarConvivencia input[name="tipo_carga"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const isArchivo = this.value === 'archivo';
        document.getElementById('campoArchivoConv').classList.toggle('d-none', !isArchivo);
        document.getElementById('campoEnlaceConv').classList.toggle('d-none', isArchivo);
        document.getElementById('archivo_convivencia').required = isArchivo;
        document.getElementById('url_externa_conv').required = !isArchivo;
        if (!isArchivo) document.getElementById('archivo_convivencia').value = '';
        if (isArchivo) document.getElementById('url_externa_conv').value = '';
    });
});

// Manejar envío del formulario
document.getElementById('formAdjuntarConvivencia')?.addEventListener('submit', function(e) {
    const btn = document.getElementById('btnAdjuntarConv');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Subiendo...';
});
</script>
