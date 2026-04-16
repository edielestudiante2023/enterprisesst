<div class="container-fluid px-3">
    <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
        <h6 class="mb-0">Actas de Visita</h6>
        <a href="<?= site_url('inspecciones/acta-visita/create') ?>" class="btn btn-sm btn-pwa-primary" style="width:auto; padding: 8px 16px;">
            <i class="fas fa-plus"></i> Nueva
        </a>
    </div>

    <!-- Filtro por cliente -->
    <div class="mb-3">
        <select id="filterCliente" class="form-select" style="width:100%;">
            <option value="">Todos los clientes</option>
        </select>
    </div>

    <!-- Lista de actas -->
    <?php if (empty($actas)): ?>
        <div class="text-center text-muted py-5">
            <i class="fas fa-clipboard fa-3x mb-3" style="opacity:0.3;"></i>
            <p>No hay actas de visita aun</p>
            <a href="<?= site_url('inspecciones/acta-visita/create') ?>" class="btn btn-pwa-primary" style="width:auto; padding: 8px 24px;">
                Crear primera acta
            </a>
        </div>
    <?php else: ?>
        <div id="actasList">
        <?php foreach ($actas as $acta): ?>
            <div class="card card-inspeccion <?= esc($acta['estado']) ?> acta-item" data-cliente="<?= strtolower(esc($acta['nombre_cliente'] ?? '')) ?>">
                <div class="card-body py-3 px-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div style="flex:1;">
                            <strong><?= esc($acta['nombre_cliente'] ?? 'Sin cliente') ?></strong>
                            <div class="text-muted" style="font-size: 13px;">
                                <?= date('d/m/Y', strtotime($acta['fecha_visita'])) ?>
                                - <?= date('g:i A', strtotime($acta['hora_visita'])) ?>
                            </div>
                            <div style="font-size: 13px; color: #666; margin-top: 2px;">
                                <?= esc($acta['motivo']) ?>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="badge badge-<?= esc($acta['estado']) ?>">
                                <?php
                                switch ($acta['estado']) {
                                    case 'borrador': echo 'Borrador'; break;
                                    case 'pendiente_firma': echo 'Pend. Firma'; break;
                                    case 'completo': echo 'Completo'; break;
                                }
                                ?>
                            </span>
                        </div>
                    </div>
                    <div class="mt-2 d-flex gap-2 flex-wrap">
                        <a href="<?= site_url('inspecciones/acta-visita/edit/' . $acta['id']) ?>" class="btn btn-sm btn-outline-dark">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <a href="<?= site_url('inspecciones/acta-visita/delete/' . $acta['id']) ?>" class="btn btn-sm btn-outline-danger btn-delete" data-id="<?= $acta['id'] ?>">
                            <i class="fas fa-trash"></i>
                        </a>
                        <?php if ($acta['estado'] === 'pendiente_firma'): ?>
                            <a href="<?= site_url('inspecciones/acta-visita/firma/' . $acta['id']) ?>" class="btn btn-sm btn-outline-warning">
                                <i class="fas fa-signature"></i> Firmar
                            </a>
                        <?php endif; ?>
                        <?php if ($acta['estado'] === 'completo'): ?>
                            <a href="<?= site_url('inspecciones/acta-visita/view/' . $acta['id']) ?>" class="btn btn-sm btn-outline-dark">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?= site_url('inspecciones/acta-visita/pdf/' . $acta['id']) ?>" class="btn btn-sm btn-outline-success" target="_blank">
                                <i class="fas fa-file-pdf"></i> PDF
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    // Cargar clientes en Select2
    $.ajax({
        url: '<?= site_url('inspecciones/api/clientes') ?>',
        dataType: 'json',
        success: function(data) {
            var select = $('#filterCliente');
            data.forEach(function(c) {
                select.append('<option value="' + c.nombre_cliente.toLowerCase() + '">' + c.nombre_cliente + '</option>');
            });
            select.select2({ placeholder: 'Todos los clientes', allowClear: true, width: '100%' });
        },
        error: function() {
            $('#filterCliente').select2({ placeholder: 'Todos los clientes', allowClear: true, width: '100%' });
        }
    });

    // Filtrar actas por cliente seleccionado
    $('#filterCliente').on('change', function() {
        var selected = (this.value || '').toLowerCase();
        $('.acta-item').each(function() {
            if (!selected) {
                $(this).show();
            } else {
                $(this).toggle($(this).data('cliente') === selected);
            }
        });
    });
});

// Confirmar eliminacion
document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const url = this.href;
        Swal.fire({
            title: 'Eliminar acta?',
            text: 'Esta accion no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Si, eliminar',
            cancelButtonText: 'Cancelar',
        }).then(result => {
            if (result.isConfirmed) window.location.href = url;
        });
    });
});
</script>
