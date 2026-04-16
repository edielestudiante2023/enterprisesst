<div class="container-fluid px-3">
    <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
        <h6 class="mb-0">Pausas Activas</h6>
        <a href="<?= site_url('inspecciones/pausas-activas/create') ?>" class="btn btn-sm btn-pwa-primary" style="width:auto; padding: 8px 16px;">
            <i class="fas fa-plus"></i> Nueva
        </a>
    </div>

    <div class="mb-3">
        <select id="filterCliente" class="form-select" style="width:100%;">
            <option value="">Todos los clientes</option>
        </select>
    </div>

    <?php if (empty($inspecciones)): ?>
        <div class="text-center text-muted py-5">
            <i class="fas fa-hands-clapping fa-3x mb-3" style="opacity:0.3;"></i>
            <p>No hay pausas activas registradas</p>
            <a href="<?= site_url('inspecciones/pausas-activas/create') ?>" class="btn btn-pwa-primary" style="width:auto; padding: 8px 24px;">
                Crear primera pausa activa
            </a>
        </div>
    <?php else: ?>
        <div id="inspeccionesList">
        <?php foreach ($inspecciones as $insp): ?>
            <div class="card card-inspeccion <?= esc($insp['estado']) ?> insp-item" data-cliente="<?= strtolower(esc($insp['nombre_cliente'] ?? '')) ?>">
                <div class="card-body py-3 px-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div style="flex:1;">
                            <strong><?= esc($insp['nombre_cliente'] ?? 'Sin cliente') ?></strong>
                            <div class="text-muted" style="font-size: 13px;">
                                <?= date('d/m/Y', strtotime($insp['fecha_actividad'])) ?>
                            </div>
                            <div style="font-size: 13px; color: #666; margin-top: 2px;">
                                <i class="fas fa-clipboard-list"></i>
                                <?= (int)($insp['total_registros'] ?? 0) ?> registro<?= ($insp['total_registros'] ?? 0) != 1 ? 's' : '' ?>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="badge badge-<?= esc($insp['estado']) ?>">
                                <?= $insp['estado'] === 'completo' ? 'Completo' : 'Borrador' ?>
                            </span>
                        </div>
                    </div>
                    <div class="mt-2 d-flex gap-2 flex-wrap">
                        <a href="<?= site_url('inspecciones/pausas-activas/edit/' . $insp['id']) ?>" class="btn btn-sm btn-outline-dark">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <a href="<?= site_url('inspecciones/pausas-activas/delete/' . $insp['id']) ?>" class="btn btn-sm btn-outline-danger btn-delete">
                            <i class="fas fa-trash"></i>
                        </a>
                        <?php if ($insp['estado'] === 'completo'): ?>
                            <a href="<?= site_url('inspecciones/pausas-activas/view/' . $insp['id']) ?>" class="btn btn-sm btn-outline-dark">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?= site_url('inspecciones/pausas-activas/pdf/' . $insp['id']) ?>" class="btn btn-sm btn-outline-success" target="_blank">
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
    $.ajax({
        url: '<?= site_url('inspecciones/api/clientes') ?>',
        dataType: 'json',
        success: function(data) {
            var select = $('#filterCliente');
            data.forEach(function(c) {
                select.append('<option value="' + c.nombre_cliente.toLowerCase() + '">' + c.nombre_cliente + '</option>');
            });
            select.select2({ placeholder: 'Todos los clientes', allowClear: true, width: '100%' });
        }
    });

    $('#filterCliente').on('change', function() {
        var selected = (this.value || '').toLowerCase();
        $('.insp-item').each(function() {
            if (!selected) $(this).show();
            else $(this).toggle($(this).data('cliente') === selected);
        });
    });
});

document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const url = this.href;
        Swal.fire({
            title: 'Eliminar pausa activa?',
            text: 'Se eliminaran todos los registros y fotos asociadas',
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Si, eliminar', cancelButtonText: 'Cancelar',
        }).then(result => { if (result.isConfirmed) window.location.href = url; });
    });
});
</script>
