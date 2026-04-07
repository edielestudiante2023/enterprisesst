<?php
$mesVisita = (int) date('m', strtotime($acta['fecha_visita']));
$totalActividades = count($actividades);
?>
<div class="container-fluid px-3 pb-5">

    <!-- Encabezado -->
    <div class="mt-2 mb-3">
        <h6 class="mb-1" style="font-size:15px;">Actividades PTA del Cliente</h6>
        <small class="text-muted"><?= esc($cliente['nombre_cliente'] ?? '') ?> &middot; <?= date('d/m/Y', strtotime($acta['fecha_visita'])) ?></small>
    </div>

    <?php if ($totalActividades === 0): ?>
    <div class="alert alert-info" style="font-size:14px;">
        <i class="fas fa-info-circle me-1"></i>
        No hay actividades PTA abiertas para este periodo.
        <a href="<?= base_url('/inspecciones/acta-visita/firma/' . $acta['id']) ?>" class="alert-link">Continuar a firmas</a>
    </div>
    <?php else: ?>

    <form method="post" action="<?= base_url('/inspecciones/acta-visita/save-pta/' . $acta['id']) ?>" id="ptaForm">
        <?= csrf_field() ?>

        <!-- Barra de acciones masivas -->
        <div class="card mb-3" style="border:none; background:#1c2437; border-radius:10px;">
            <div class="card-body py-2 px-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <label class="form-check-label text-white" style="font-size:13px;">
                        <input type="checkbox" id="selectAll" class="form-check-input me-1"> Seleccionar todas
                    </label>
                    <span class="badge bg-warning text-dark" id="selectedCount">0 seleccionadas</span>
                </div>
                <div class="input-group input-group-sm">
                    <input type="text" id="bulkComment" class="form-control" placeholder="Comentario masivo para seleccionadas..." style="font-size:13px;">
                    <button type="button" class="btn btn-warning btn-sm" id="btnApplyBulk" style="font-size:12px; white-space:nowrap;">
                        <i class="fas fa-paste"></i> Aplicar
                    </button>
                </div>
            </div>
        </div>

        <!-- Lista de actividades -->
        <?php foreach ($actividades as $i => $a):
            $idPta = $a['id_ptacliente'];
            $yaCerrada = !empty($a['_ya_cerrada']);
            $prev = $prevLinks[$idPta] ?? null;
            $isChecked = $yaCerrada || ($prev && $prev['cerrada']);
            $justificacion = ($prev && !empty($prev['justificacion'])) ? $prev['justificacion'] : '';
            $mesPta = $a['fecha_propuesta'] ? (int) date('m', strtotime($a['fecha_propuesta'])) : 0;
            $rezagada = $mesPta > 0 && $mesPta < $mesVisita;
        ?>
        <div class="card mb-2 pta-card" data-id="<?= $idPta ?>" style="border-left:4px solid <?= $yaCerrada ? '#28a745' : ($rezagada ? '#fd7e14' : '#bd9751') ?>; border-radius:8px;">
            <div class="card-body py-2 px-3">
                <input type="hidden" name="pta_actividad_id[]" value="<?= $idPta ?>">

                <div class="d-flex align-items-start gap-2">
                    <!-- Checkbox cerrada -->
                    <div class="pt-1">
                        <input type="checkbox" class="form-check-input pta-check"
                            name="pta_actividad_checked[]" value="<?= $idPta ?>"
                            id="pta-<?= $idPta ?>"
                            <?= $isChecked ? 'checked' : '' ?>
                            <?= $yaCerrada ? 'disabled' : '' ?>>
                    </div>

                    <!-- Info actividad -->
                    <div class="flex-grow-1">
                        <label for="pta-<?= $idPta ?>" style="font-size:13px; cursor:pointer; margin:0;">
                            <strong><?= esc($a['numeral_plandetrabajo'] ?? '') ?></strong> — <?= esc($a['actividad_plandetrabajo'] ?? '') ?>
                        </label>
                        <div style="font-size:11px; color:#999; margin-top:2px;">
                            <?php if ($a['fecha_propuesta']): ?>
                                <i class="fas fa-calendar-alt me-1"></i><?= date('d/m/Y', strtotime($a['fecha_propuesta'])) ?>
                            <?php endif; ?>
                            <?php if ($rezagada): ?>
                                <span class="badge bg-warning text-dark ms-1" style="font-size:9px;">REZAGADA</span>
                            <?php endif; ?>
                            <?php if ($yaCerrada): ?>
                                <span class="badge bg-success ms-1" style="font-size:9px;">CERRADA</span>
                            <?php endif; ?>
                        </div>

                        <!-- Campo comentario (visible si NO esta cerrada) -->
                        <?php if (!$yaCerrada): ?>
                        <div class="mt-2 comment-field" style="<?= $isChecked ? 'display:none;' : '' ?>">
                            <textarea name="pta_justificacion[<?= $idPta ?>]" class="form-control form-control-sm pta-comment"
                                placeholder="Comentario / justificacion..."
                                style="font-size:12px; min-height:40px; resize:vertical;"
                                data-id="<?= $idPta ?>"><?= esc($justificacion) ?></textarea>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Checkbox seleccion masiva -->
                    <?php if (!$yaCerrada): ?>
                    <div class="pt-1">
                        <input type="checkbox" class="form-check-input bulk-select" data-id="<?= $idPta ?>" title="Seleccionar para comentario masivo" style="accent-color:#bd9751;">
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Boton guardar -->
        <div class="mt-3">
            <button type="button" id="btnGuardarPta" class="btn btn-pwa btn-pwa-primary py-3" style="font-size:16px; font-weight:600;">
                <i class="fas fa-save me-1"></i> Guardar todo y continuar a firmas
            </button>
        </div>

        <!-- Boton saltar si no hay actividades que comentar -->
        <div class="mt-2">
            <a href="<?= base_url('/inspecciones/acta-visita/firma/' . $acta['id']) ?>" class="btn btn-pwa btn-pwa-outline py-2" style="font-size:14px;">
                <i class="fas fa-forward me-1"></i> Saltar e ir directo a firmas
            </a>
        </div>
    </form>

    <!-- Volver al acta -->
    <div class="mt-2 mb-4">
        <a href="<?= base_url('/inspecciones/acta-visita/edit/' . $acta['id']) ?>" class="btn btn-sm btn-outline-dark">
            <i class="fas fa-arrow-left"></i> Volver al acta
        </a>
    </div>

    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var bulkSelects = document.querySelectorAll('.bulk-select');
    var ptaChecks = document.querySelectorAll('.pta-check:not([disabled])');
    var selectAll = document.getElementById('selectAll');
    var selectedCount = document.getElementById('selectedCount');
    var btnApplyBulk = document.getElementById('btnApplyBulk');
    var bulkComment = document.getElementById('bulkComment');

    // Toggle campo comentario al marcar/desmarcar cerrada
    ptaChecks.forEach(function(cb) {
        cb.addEventListener('change', function() {
            var card = this.closest('.pta-card');
            var commentField = card.querySelector('.comment-field');
            if (commentField) {
                commentField.style.display = this.checked ? 'none' : '';
            }
        });
    });

    // Seleccionar todas para comentario masivo
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            bulkSelects.forEach(function(bs) { bs.checked = selectAll.checked; });
            updateSelectedCount();
        });
    }

    // Contar seleccionadas
    bulkSelects.forEach(function(bs) {
        bs.addEventListener('change', updateSelectedCount);
    });

    function updateSelectedCount() {
        var count = document.querySelectorAll('.bulk-select:checked').length;
        selectedCount.textContent = count + ' seleccionada' + (count !== 1 ? 's' : '');
    }

    // Guardar con validacion aritmetica
    document.getElementById('btnGuardarPta').addEventListener('click', function() {
        var a = Math.floor(Math.random() * 15) + 2;
        var b = Math.floor(Math.random() * 15) + 2;

        Swal.fire({
            title: 'Esta accion no se puede deshacer',
            html: '<p style="font-size:14px; color:#666;">Los comentarios y cierres de actividades PTA se guardaran definitivamente. No podra volver a esta pantalla para esta acta.</p>'
                + '<p style="font-size:20px; font-weight:bold; margin:12px 0;">Cuanto es ' + a + ' + ' + b + '?</p>',
            input: 'number',
            inputPlaceholder: 'Resultado',
            showCancelButton: true,
            confirmButtonText: 'Guardar y continuar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#bd9751',
            inputValidator: function(val) {
                if (!val) return 'Ingrese un numero';
                if (parseInt(val) !== (a + b)) return 'Respuesta incorrecta';
            }
        }).then(function(result) {
            if (result.isConfirmed) {
                var btn = document.getElementById('btnGuardarPta');
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Guardando...';
                document.getElementById('ptaForm').submit();
            }
        });
    });

    // Aplicar comentario masivo
    if (btnApplyBulk) {
        btnApplyBulk.addEventListener('click', function() {
            var text = bulkComment.value.trim();
            if (!text) {
                Swal.fire({ icon: 'warning', title: 'Escriba un comentario', text: 'Ingrese el texto antes de aplicar.', confirmButtonColor: '#bd9751' });
                return;
            }

            var selected = document.querySelectorAll('.bulk-select:checked');
            if (selected.length === 0) {
                Swal.fire({ icon: 'warning', title: 'Ninguna seleccionada', text: 'Seleccione al menos una actividad con el checkbox de la derecha.', confirmButtonColor: '#bd9751' });
                return;
            }

            var applied = 0;
            selected.forEach(function(bs) {
                var idPta = bs.dataset.id;
                var textarea = document.querySelector('.pta-comment[data-id="' + idPta + '"]');
                if (textarea) {
                    textarea.value = text;
                    applied++;
                }
            });

            Swal.fire({
                icon: 'success',
                title: 'Aplicado',
                text: 'Comentario aplicado a ' + applied + ' actividad(es).',
                timer: 1500,
                showConfirmButton: false
            });

            // Limpiar seleccion
            selected.forEach(function(bs) { bs.checked = false; });
            if (selectAll) selectAll.checked = false;
            updateSelectedCount();
            bulkComment.value = '';
        });
    }
});
</script>
