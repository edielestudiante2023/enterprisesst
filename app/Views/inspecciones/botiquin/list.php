<div class="container-fluid px-3">

    <?php if (session()->getFlashdata('msg')): ?>
    <div class="alert alert-success mt-2" style="font-size:14px;"><?= session()->getFlashdata('msg') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger mt-2" style="font-size:14px;"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <!-- Filtro cliente -->
    <div class="mb-3 mt-2">
        <select id="filtroCliente" class="form-select" style="font-size:14px;">
            <option value="">Todos los clientes</option>
        </select>
    </div>

    <!-- Boton nueva -->
    <a href="/inspecciones/botiquin/create" class="btn btn-pwa btn-pwa-primary mb-3">
        <i class="fas fa-plus"></i> Nueva inspeccion
    </a>

    <!-- Listado -->
    <div id="listaInspecciones">
    <?php if (empty($inspecciones)): ?>
        <div class="text-center text-muted py-4">
            <i class="fas fa-first-aid" style="font-size:40px; opacity:0.3;"></i>
            <p class="mt-2">No hay inspecciones de botiquin</p>
        </div>
    <?php else: ?>
        <?php foreach ($inspecciones as $insp): ?>
        <div class="card card-inspeccion mb-2 insp-card" data-cliente="<?= esc($insp['nombre_cliente'] ?? '') ?>">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div style="flex:1;">
                        <strong style="font-size:13px;"><?= esc($insp['nombre_cliente'] ?? 'Sin cliente') ?></strong>
                        <div class="text-muted" style="font-size:12px;">
                            <?= date('d/m/Y', strtotime($insp['fecha_inspeccion'])) ?>
                            <?php if (!empty($insp['ubicacion_botiquin'])): ?>
                                &middot; <?= esc($insp['ubicacion_botiquin']) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <span class="badge badge-<?= esc($insp['estado']) ?>" style="font-size:11px;">
                        <?= $insp['estado'] === 'completo' ? 'Completo' : 'Borrador' ?>
                    </span>
                </div>
                <div class="mt-2 d-flex gap-1 flex-wrap">
                    <!-- Doc 25: edit/delete buttons always visible regardless of estado -->
                    <a href="/inspecciones/botiquin/edit/<?= $insp['id'] ?>" class="btn btn-sm btn-outline-dark">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-insp" data-id="<?= $insp['id'] ?>" data-nombre="<?= esc($insp['nombre_cliente'] ?? '') ?>">
                        <i class="fas fa-trash"></i>
                    </button>
                    <?php if ($insp['estado'] === 'completo'): ?>
                        <a href="/inspecciones/botiquin/view/<?= $insp['id'] ?>" class="btn btn-sm btn-outline-dark">
                            <i class="fas fa-eye"></i> Ver
                        </a>
                        <a href="/inspecciones/botiquin/pdf/<?= $insp['id'] ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                            <i class="fas fa-file-pdf"></i> PDF
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select2 filtro
    $.ajax({
        url: '/inspecciones/api/clientes',
        dataType: 'json',
        success: function(data) {
            const select = document.getElementById('filtroCliente');
            data.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.nombre_cliente;
                opt.textContent = c.nombre_cliente;
                select.appendChild(opt);
            });
            $('#filtroCliente').select2({ placeholder: 'Todos los clientes', allowClear: true, width: '100%' });
        }
    });

    // Filtrar
    $('#filtroCliente').on('change', function() {
        const val = this.value.toLowerCase();
        document.querySelectorAll('.insp-card').forEach(card => {
            const cliente = card.dataset.cliente.toLowerCase();
            card.style.display = (!val || cliente.includes(val)) ? '' : 'none';
        });
    });

    // Eliminar con SweetAlert
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-delete-insp');
        if (!btn) return;
        Swal.fire({
            title: 'Eliminar inspeccion?',
            html: 'Se eliminara la inspeccion de <strong>' + btn.dataset.nombre + '</strong>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Si, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545',
        }).then(result => {
            if (result.isConfirmed) {
                window.location.href = '/inspecciones/botiquin/delete/' + btn.dataset.id;
            }
        });
    });
});
</script>
