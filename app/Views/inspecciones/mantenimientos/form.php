<?php
$isEdit = !empty($edit) && !empty($vencimiento);
$action = $isEdit
    ? site_url('inspecciones/mantenimientos/update/' . $vencimiento['id_vencimientos_mmttos'])
    : site_url('inspecciones/mantenimientos/store');
?>

<div class="container-fluid px-3">
    <form method="post" action="<?= $action ?>" id="formVencimiento">
        <?= csrf_field() ?>

        <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger mt-2" style="font-size:14px;">
            <?= session()->getFlashdata('error') ?>
        </div>
        <?php endif; ?>

        <!-- Cliente -->
        <div class="mb-3 mt-2">
            <label class="form-label">Cliente *</label>
            <?php if ($isEdit): ?>
                <input type="text" class="form-control" value="<?= esc($cliente['nombre_cliente'] ?? '') ?>" readonly>
            <?php else: ?>
                <select name="id_cliente" id="selectCliente" class="form-select" required>
                    <option value="">Seleccionar cliente...</option>
                </select>
            <?php endif; ?>
        </div>

        <!-- Tipo de mantenimiento -->
        <div class="mb-3">
            <label class="form-label">Tipo de mantenimiento *</label>
            <?php if ($isEdit): ?>
                <input type="text" class="form-control" value="<?= esc($vencimiento['detalle_mantenimiento'] ?? '') ?>" readonly>
            <?php else: ?>
                <div class="d-flex gap-2">
                    <select name="id_mantenimiento" id="selectMantenimiento" class="form-select" required style="flex:1;">
                        <option value="">Seleccionar tipo...</option>
                    </select>
                    <button type="button" id="btnNuevoTipo" class="btn btn-outline-dark" style="white-space:nowrap;">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <!-- Fecha vencimiento -->
        <div class="mb-3">
            <label class="form-label">Fecha de vencimiento *</label>
            <input type="date" name="fecha_vencimiento" class="form-control"
                value="<?= esc($vencimiento['fecha_vencimiento'] ?? '') ?>" required>
        </div>

        <!-- Observaciones -->
        <div class="mb-3">
            <label class="form-label">Observaciones</label>
            <textarea name="observaciones" class="form-control" rows="3"
                placeholder="Observaciones opcionales..."><?= esc($vencimiento['observaciones'] ?? '') ?></textarea>
        </div>

        <!-- Botones -->
        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="btn btn-pwa btn-pwa-primary">
                <i class="fas fa-save"></i> Guardar
            </button>
            <a href="<?= site_url('inspecciones/mantenimientos' . $idCliente ? '/cliente/' . $idCliente : '') ?>" class="btn btn-outline-secondary">
                Cancelar
            </a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    <?php if (!$isEdit): ?>
    // Select2 cliente via AJAX
    $('#selectCliente').select2({
        placeholder: 'Buscar cliente...',
        allowClear: true,
        ajax: {
            url: '<?= site_url('inspecciones/api/clientes') ?>',
            dataType: 'json',
            delay: 250,
            processResults: function(data) {
                return {
                    results: data.map(function(c) {
                        return { id: c.id_cliente, text: c.nombre_cliente };
                    })
                };
            },
            cache: true
        },
        minimumInputLength: 0
    });

    // Pre-seleccionar cliente si viene en URL
    <?php if ($idCliente && $cliente): ?>
    var optCliente = new Option('<?= esc($cliente['nombre_cliente']) ?>', '<?= $idCliente ?>', true, true);
    $('#selectCliente').append(optCliente).trigger('change');
    <?php endif; ?>

    // Select2 catalogo de mantenimientos
    $('#selectMantenimiento').select2({
        placeholder: 'Buscar tipo...',
        allowClear: true,
        ajax: {
            url: '<?= site_url('inspecciones/api/mantenimientos-catalog') ?>',
            dataType: 'json',
            delay: 250,
            processResults: function(data) {
                return {
                    results: data.map(function(m) {
                        return { id: m.id_mantenimiento, text: m.detalle_mantenimiento };
                    })
                };
            },
            cache: true
        },
        minimumInputLength: 0
    });

    // Agregar nuevo tipo de mantenimiento
    document.getElementById('btnNuevoTipo').addEventListener('click', function() {
        Swal.fire({
            title: 'Nuevo tipo de mantenimiento',
            input: 'text',
            inputPlaceholder: 'Ej: Fumigacion, Impermeabilizacion...',
            showCancelButton: true,
            confirmButtonText: 'Agregar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#bd9751',
            inputValidator: function(value) {
                if (!value || !value.trim()) {
                    return 'Ingrese un nombre';
                }
            }
        }).then(function(result) {
            if (result.isConfirmed) {
                fetch('<?= site_url('inspecciones/api/mantenimientos-catalog') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ detalle_mantenimiento: result.value.trim() })
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        // Agregar al Select2 y seleccionar
                        var opt = new Option(data.detalle_mantenimiento, data.id_mantenimiento, true, true);
                        $('#selectMantenimiento').append(opt).trigger('change');

                        Swal.fire({
                            icon: 'success',
                            title: 'Tipo agregado',
                            toast: true,
                            position: 'top',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    } else {
                        Swal.fire('Error', data.error || 'No se pudo crear', 'error');
                    }
                })
                .catch(function() {
                    Swal.fire('Error', 'Error de conexion', 'error');
                });
            }
        });
    });
    <?php endif; ?>

});
</script>
