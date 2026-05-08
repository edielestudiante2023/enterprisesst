<?php
/**
 * Componente: Vincular Reportes del reportList a una carpeta
 *
 * Permite al consultor traer un reporte existente del reportList del cliente
 * como REFERENCIA dentro de esta carpeta, sin duplicar el archivo PDF.
 *
 * Variables requeridas (pasadas desde DocumentacionController::carpeta):
 *   $carpeta           array  - row de tbl_doc_carpetas
 *   $cliente           array  - row de tbl_clientes
 *   $reportesVinculados array - lista de vinculos con detalle del reporte
 */

$carpeta            = $carpeta            ?? null;
$cliente            = $cliente            ?? null;
$reportesVinculados = $reportesVinculados ?? [];

// No mostrar el componente para carpetas raiz/phva (no son carpetas tipo)
if (!$carpeta || !$cliente) return;
if (in_array($carpeta['tipo'] ?? '', ['raiz', 'phva'], true)) return;
?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-secondary bg-gradient text-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-link-45deg me-2"></i>Documentos vinculados desde el reportList
        </h6>
        <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#modalVincularReporte">
            <i class="bi bi-plus-circle me-1"></i>Vincular documento existente
        </button>
    </div>
    <div class="card-body p-0">
        <?php if (empty($reportesVinculados)): ?>
            <div class="text-center py-4 text-muted">
                <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                <small>No hay documentos vinculados desde el reportList.</small><br>
                <small class="text-muted">
                    Use el boton para traer aqui un reporte existente del cliente como referencia
                    (no se crea un PDF nuevo).
                </small>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:36%;">Titulo del reporte</th>
                            <th style="width:18%;">Tipo</th>
                            <th style="width:18%;">Categoria</th>
                            <th style="width:10%;">Fecha</th>
                            <th style="width:18%;" class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reportesVinculados as $v): ?>
                        <tr>
                            <td>
                                <span class="fw-semibold"><?= esc($v['titulo_reporte'] ?? '(sin titulo)') ?></span>
                                <?php if (!empty($v['observacion'])): ?>
                                    <div class="small text-muted mt-1">
                                        <i class="bi bi-chat-left-text me-1"></i><?= esc($v['observacion']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge bg-info-subtle text-info-emphasis"><?= esc($v['report_type_nombre'] ?? '-') ?></span></td>
                            <td class="small text-muted"><?= esc($v['detail_report_nombre'] ?? '-') ?></td>
                            <td class="small">
                                <?= !empty($v['reporte_created_at']) ? date('d/m/Y', strtotime($v['reporte_created_at'])) : '-' ?>
                            </td>
                            <td class="text-end">
                                <?php if (!empty($v['enlace'])): ?>
                                <a href="<?= esc($v['enlace']) ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Ver en nueva pestana">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php endif; ?>
                                <form method="post" action="<?= base_url('documentacion/vinculo/' . $v['id_vinculo'] . '/quitar') ?>"
                                      class="d-inline" onsubmit="return confirm('Quitar este vinculo? El reporte original NO se borra del reportList.');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Quitar vinculo">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: Vincular reporte existente -->
<div class="modal fade" id="modalVincularReporte" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="<?= base_url('documentacion/vinculo/agregar') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="id_carpeta" value="<?= (int) $carpeta['id_carpeta'] ?>">

                <div class="modal-header bg-secondary text-white">
                    <h5 class="modal-title"><i class="bi bi-link-45deg me-2"></i>Vincular documento del reportList</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-info small mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Esta accion <strong>no crea un nuevo PDF</strong>. Solo registra una referencia
                        del documento existente para visualizarlo desde esta carpeta.
                        El archivo original se mantiene intacto en el reportList.
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-search me-1"></i>Buscar reporte del cliente
                            <strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong>
                        </label>
                        <select name="id_reporte" id="selectVincularReporte" class="form-select" required style="width:100%;">
                            <option value=""></option>
                        </select>
                        <div class="form-text">Busca por titulo, tipo o categoria del reporte. Los ya vinculados aparecen deshabilitados.</div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small">Observacion (opcional)</label>
                        <input type="text" name="observacion" class="form-control form-control-sm"
                               placeholder="Ej: Adjuntado como soporte de capacitacion 2026" maxlength="500">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-link-45deg me-1"></i>Vincular</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!--
  Select2 + jQuery: el componente garantiza el orden correcto de carga.
  En documentacion/carpeta.php, _components/scripts.php (que carga jQuery)
  esta DESPUES de este componente. Por eso aqui forzamos el orden:
  1) jQuery (solo si no esta ya cargado por otro lado)
  2) Select2 (depende de jQuery, asi que va despues)
  3) Select2 CSS
  4) Init diferido (espera a que ambos esten listos)
-->
<?php if (!defined('VINCULAR_REPORTES_ASSETS_LOADED')): ?>
    <?php define('VINCULAR_REPORTES_ASSETS_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <script>
        // Carga sincrona de jQuery si no esta ya cargado, con document.write
        // (el unico metodo que garantiza orden con scripts inline posteriores)
        if (typeof window.jQuery === 'undefined') {
            document.write('<scr' + 'ipt src="https://code.jquery.com/jquery-3.7.1.min.js"><\/scr' + 'ipt>');
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<?php endif; ?>

<script>
(function(){
    function init() {
        if (typeof window.jQuery === 'undefined' || typeof window.jQuery.fn.select2 === 'undefined') {
            return setTimeout(init, 80);
        }
        const $ = window.jQuery;
        const $sel = $('#selectVincularReporte');
        if (!$sel.length || $sel.data('select2-initialized')) return;
        $sel.data('select2-initialized', true);

        $sel.select2({
            dropdownParent: $('#modalVincularReporte'),
            placeholder: 'Escribe para buscar un reporte del cliente...',
            allowClear: true,
            minimumInputLength: 0,
            width: '100%',
            ajax: {
                url: '<?= base_url('documentacion/vinculo/reportes-disponibles/' . (int) $cliente['id_cliente']) ?>',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term || '',
                        id_carpeta: <?= (int) $carpeta['id_carpeta'] ?>
                    };
                },
                error: function (xhr, status, err) {
                    console.error('[VincularReporte] AJAX error:', xhr.status, status, err, xhr.responseText);
                }
            },
            templateResult: function (item) {
                if (!item.id) return item.text;
                const dis = item.ya_vinculado ? ' <span class="badge bg-warning text-dark ms-1">YA VINCULADO</span>' : '';
                const tipo = item.tipo_reporte ? `<span class="badge bg-info-subtle text-info-emphasis ms-1">${item.tipo_reporte}</span>` : '';
                const fecha = item.fecha ? `<small class="text-muted ms-1">${item.fecha}</small>` : '';
                return $(`<div><strong>${item.text}</strong>${dis}<br><small>${tipo} ${fecha}</small></div>`);
            },
            templateSelection: function (item) {
                return item.text || item.id;
            },
            language: { searching: () => 'Buscando...', noResults: () => 'Sin resultados', inputTooShort: () => 'Escribe para buscar' }
        });

        $sel.on('select2:selecting', function (e) {
            if (e.params.args.data && e.params.args.data.ya_vinculado) {
                e.preventDefault();
                alert('Este reporte ya esta vinculado a esta carpeta.');
            }
        });
    }

    // Iniciar cuando el modal se abra (asi dropdownParent existe y es visible)
    function bindOnModalShow() {
        if (typeof window.jQuery === 'undefined') return setTimeout(bindOnModalShow, 80);
        const $ = window.jQuery;
        $('#modalVincularReporte').on('shown.bs.modal', init);
        // Tambien intentar init inmediato (por si el modal ya estaba)
        init();
    }
    bindOnModalShow();
})();
</script>
