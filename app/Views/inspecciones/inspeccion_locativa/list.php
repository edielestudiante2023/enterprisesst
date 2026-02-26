<div class="container-fluid px-3">
    <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
        <h6 class="mb-0">Inspecciones Locativas</h6>
        <a href="/inspecciones/inspeccion-locativa/create" class="btn btn-sm btn-pwa-primary" style="width:auto; padding: 8px 16px;">
            <i class="fas fa-plus"></i> Nueva
        </a>
    </div>

    <!-- Filtro por cliente -->
    <div class="mb-3">
        <select id="filterCliente" class="form-select" style="width:100%;">
            <option value="">Todos los clientes</option>
        </select>
    </div>

    <!-- Lista de inspecciones -->
    <?php if (empty($inspecciones)): ?>
        <div class="text-center text-muted py-5">
            <i class="fas fa-building fa-3x mb-3" style="opacity:0.3;"></i>
            <p>No hay inspecciones locativas aun</p>
            <a href="/inspecciones/inspeccion-locativa/create" class="btn btn-pwa-primary" style="width:auto; padding: 8px 24px;">
                Crear primera inspeccion
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
                                <?= date('d/m/Y', strtotime($insp['fecha_inspeccion'])) ?>
                            </div>
                            <div style="font-size: 13px; color: #666; margin-top: 2px;">
                                <i class="fas fa-exclamation-triangle"></i>
                                <?= (int)($insp['total_hallazgos'] ?? 0) ?> hallazgo<?= ($insp['total_hallazgos'] ?? 0) != 1 ? 's' : '' ?>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="badge badge-<?= esc($insp['estado']) ?>">
                                <?= $insp['estado'] === 'completo' ? 'Completo' : 'Borrador' ?>
                            </span>
                        </div>
                    </div>
                    <div class="mt-2 d-flex gap-2">
                        <?php if ($insp['estado'] === 'borrador'): ?>
                            <a href="/inspecciones/inspeccion-locativa/edit/<?= $insp['id'] ?>" class="btn btn-sm btn-outline-dark">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <a href="/inspecciones/inspeccion-locativa/delete/<?= $insp['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete" data-id="<?= $insp['id'] ?>">
                                <i class="fas fa-trash"></i>
                            </a>
                        <?php else: ?>
                            <a href="/inspecciones/inspeccion-locativa/pdf/<?= $insp['id'] ?>" class="btn btn-sm btn-outline-success" target="_blank">
                                <i class="fas fa-file-pdf"></i> PDF
                            </a>
                            <a href="/inspecciones/inspeccion-locativa/view/<?= $insp['id'] ?>" class="btn btn-sm btn-outline-dark">
                                <i class="fas fa-eye"></i>
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
        url: '/inspecciones/api/clientes',
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

    // Filtrar por cliente
    $('#filterCliente').on('change', function() {
        var selected = (this.value || '').toLowerCase();
        $('.insp-item').each(function() {
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
            title: 'Eliminar inspeccion?',
            text: 'Se eliminaran todos los hallazgos y fotos asociadas',
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
