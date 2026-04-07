<?php
$isEdit = !empty($inspeccion);
$action = $isEdit ? base_url('/inspecciones/registro-asistencia/update/') . $inspeccion['id'] : base_url('/inspecciones/registro-asistencia/store');
?>

<div class="container-fluid px-3">
    <form method="post" action="<?= $action ?>" id="regAsistForm">
        <?= csrf_field() ?>

        <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger mt-2" style="font-size:14px;">
            <ul class="mb-0">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('msg')): ?>
        <div class="alert alert-success mt-2" style="font-size:14px;">
            <?= session()->getFlashdata('msg') ?>
        </div>
        <?php endif; ?>

        <!-- DATOS GENERALES -->
        <div class="card mt-2 mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">DATOS GENERALES</h6>
                <div class="mb-3">
                    <label class="form-label">Cliente *</label>
                    <select name="id_cliente" id="selectCliente" class="form-select" required>
                        <option value="">Seleccionar cliente...</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Fecha sesion *</label>
                    <input type="date" name="fecha_sesion" class="form-control"
                        value="<?= $inspeccion['fecha_sesion'] ?? date('Y-m-d') ?>" required>
                </div>
            </div>
        </div>

        <!-- INFORMACION DE LA SESION -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">INFORMACION DE LA SESION</h6>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Tema *</label>
                    <input type="text" name="tema" class="form-control form-control-sm"
                        value="<?= esc($inspeccion['tema'] ?? '') ?>" placeholder="Tema de la sesion" required>
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Lugar</label>
                    <input type="text" name="lugar" class="form-control form-control-sm"
                        value="<?= esc($inspeccion['lugar'] ?? '') ?>" placeholder="Lugar de la sesion">
                </div>
                <div class="mb-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label mb-0" style="font-size:12px;">Objetivo</label>
                        <button type="button" id="btnGenerarObjetivo" class="btn btn-sm" onclick="generarObjetivoIA()"
                            style="font-size:11px; padding:2px 8px; background:#6c63ff; color:#fff; border:none; border-radius:4px;">
                            Generar con IA
                        </button>
                    </div>
                    <textarea name="objetivo" id="objetivo" class="form-control form-control-sm" rows="2"
                        placeholder="Objetivo de la sesion..."><?= esc($inspeccion['objetivo'] ?? '') ?></textarea>
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Capacitador / Facilitador</label>
                    <input type="text" name="capacitador" class="form-control form-control-sm"
                        value="<?= esc($inspeccion['capacitador'] ?? '') ?>" placeholder="Nombre del capacitador o facilitador">
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Tipo de reunion *</label>
                    <select name="tipo_reunion" class="form-select form-select-sm" required>
                        <option value="">Seleccionar...</option>
                        <?php foreach ($tiposReunion as $val => $label): ?>
                        <option value="<?= $val ?>" <?= ($inspeccion['tipo_reunion'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Material</label>
                    <input type="text" name="material" class="form-control form-control-sm"
                        value="<?= esc($inspeccion['material'] ?? '') ?>" placeholder="Material utilizado">
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Tiempo (horas)</label>
                    <input type="number" name="tiempo_horas" class="form-control form-control-sm"
                        value="<?= esc($inspeccion['tiempo_horas'] ?? '') ?>" placeholder="0.0" step="0.5" min="0">
                </div>
            </div>
        </div>

        <!-- OBSERVACIONES -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">OBSERVACIONES</h6>
                <div class="mb-2">
                    <textarea name="observaciones" class="form-control form-control-sm" rows="3"
                        placeholder="Observaciones..."><?= esc($inspeccion['observaciones'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Indicador autoguardado -->
        <div id="autoSaveStatus" style="font-size:12px; color:#999; text-align:center; padding:4px 0;">
            <i class="fas fa-cloud"></i> Autoguardado activado
        </div>

        <!-- BOTONES -->
        <div class="d-grid gap-3 mt-3 mb-5 pb-3">
            <button type="submit" name="accion" value="borrador" class="btn btn-pwa btn-pwa-outline py-3" style="font-size:17px;">
                <i class="fas fa-save"></i> Guardar borrador
            </button>
            <button type="submit" name="accion" value="registrar" class="btn btn-pwa btn-pwa-primary py-3" style="font-size:17px;">
                <i class="fas fa-users"></i> <?= $isEdit ? 'Guardar y registrar asistentes' : 'Crear y registrar asistentes' ?>
            </button>
            <?php if ($isEdit): ?>
            <a href="<?= base_url('/inspecciones/registro-asistencia/registrar/') ?><?= $inspeccion['id'] ?>"
               class="btn btn-pwa py-3" style="font-size:17px;">
                <i class="fas fa-users me-1"></i> Ir a registro de asistentes
            </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectedCliente = '<?= $idCliente ?? '' ?>';

    $.ajax({
        url: '<?= base_url('/inspecciones/api/clientes') ?>',
        dataType: 'json',
        success: function(data) {
            const select = document.getElementById('selectCliente');
            data.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id_cliente;
                opt.textContent = c.nombre_cliente;
                if (c.id_cliente == selectedCliente) opt.selected = true;
                select.appendChild(opt);
            });
            $('#selectCliente').select2({ placeholder: 'Seleccionar cliente...', width: '100%' });
        }
    });

    // AUTOGUARDADO SERVIDOR
    initAutosave({
        formId: 'regAsistForm',
        storeUrl: base_url('/inspecciones/registro-asistencia/store'),
        updateUrlBase: base_url('/inspecciones/registro-asistencia/update/'),
        editUrlBase: base_url('/inspecciones/registro-asistencia/edit/'),
        recordId: <?= $inspeccion['id'] ?? 'null' ?>,
        isEdit: <?= $isEdit ? 'true' : 'false' ?>,
        storageKey: 'reg_asist_draft_<?= $isEdit ? $inspeccion['id'] : 'new' ?>',
        intervalSeconds: 60,
        minFieldsCheck: function() {
            var cliente = document.querySelector('[name="id_cliente"]');
            var fecha = document.querySelector('[name="fecha_sesion"]');
            return cliente && cliente.value && fecha && fecha.value;
        },
    });
});

function generarObjetivoIA() {
    var tema = document.querySelector('[name="tema"]').value.trim();
    if (!tema) {
        Swal.fire({ icon: 'warning', title: 'Falta el tema', text: 'Escribe primero el tema de la sesion.', confirmButtonColor: '#6c63ff' });
        return;
    }
    var btn = document.getElementById('btnGenerarObjetivo');
    btn.disabled = true;
    btn.textContent = 'Generando...';

    fetch('<?= base_url('/inspecciones/registro-asistencia/generar-objetivo') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ tema: tema })
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.textContent = 'Generar con IA';
        if (data.objetivo) {
            document.getElementById('objetivo').value = data.objetivo;
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.error || 'No se pudo generar el objetivo.', confirmButtonColor: '#6c63ff' });
        }
    })
    .catch(function() {
        btn.disabled = false;
        btn.textContent = 'Generar con IA';
        Swal.fire({ icon: 'error', title: 'Error de conexion', text: 'No se pudo conectar con el servidor.', confirmButtonColor: '#6c63ff' });
    });
}
</script>
