<div class="container-fluid px-3">
    <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
        <h5 class="mb-0" style="font-size:18px; font-weight:700;">Señalización</h5>
        <a href="<?= site_url('inspecciones/senalizacion/create') ?>" class="btn btn-sm btn-pwa btn-pwa-primary" style="width:auto; padding:8px 16px;">
            <i class="fas fa-plus"></i> Nueva
        </a>
    </div>

    <!-- Filtro Select2 -->
    <div class="mb-3">
        <select id="filtroCliente" class="form-select" style="font-size:14px;">
            <option value="">Todos los clientes</option>
        </select>
    </div>

    <?php if (empty($inspecciones)): ?>
    <div class="text-center py-5" style="color:#999;">
        <i class="fas fa-clipboard-check fa-3x mb-3" style="color:#ddd;"></i>
        <p>No hay inspecciones de señalización aún</p>
        <a href="<?= site_url('inspecciones/senalizacion/create') ?>" class="btn btn-pwa btn-pwa-outline" style="width:auto;">
            <i class="fas fa-plus"></i> Crear primera inspección
        </a>
    </div>
    <?php else: ?>
        <?php foreach ($inspecciones as $insp): ?>
        <div class="card mb-2 card-inspeccion" data-cliente="<?= $insp['id_cliente'] ?>"
             style="border-left: 4px solid <?= $insp['estado'] === 'completo' ? '#28a745' : '#ffc107' ?>;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <strong style="font-size:14px;"><?= esc($insp['nombre_cliente'] ?? 'Sin cliente') ?></strong>
                        <br>
                        <small class="text-muted">
                            <?= date('d/m/Y', strtotime($insp['fecha_inspeccion'])) ?>
                        </small>
                    </div>
                    <div class="text-end">
                        <?php if ($insp['estado'] === 'completo'): ?>
                            <span class="badge bg-success" style="font-size:11px;">Completo</span>
                            <?php if ($insp['calificacion'] > 0): ?>
                            <br><small style="font-size:12px; font-weight:700; color:<?= $insp['calificacion'] >= 80 ? '#28a745' : ($insp['calificacion'] >= 60 ? '#ffc107' : '#dc3545') ?>;">
                                <?= number_format($insp['calificacion'], 1) ?>%
                            </small>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark" style="font-size:11px;">Borrador</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-2 d-flex gap-2 flex-wrap">
                    <a href="<?= site_url('inspecciones/senalizacion/edit/' . $insp['id']) ?>" class="btn btn-sm btn-outline-dark">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <button class="btn btn-sm btn-outline-danger btn-delete" data-id="<?= $insp['id'] ?>" data-cliente="<?= esc($insp['nombre_cliente'] ?? '') ?>">
                        <i class="fas fa-trash"></i>
                    </button>
                    <?php if ($insp['estado'] === 'completo'): ?>
                        <a href="<?= site_url('inspecciones/senalizacion/view/' . $insp['id']) ?>" class="btn btn-sm btn-outline-dark">
                            <i class="fas fa-eye"></i> Ver
                        </a>
                        <a href="<?= site_url('inspecciones/senalizacion/pdf/' . $insp['id']) ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                            <i class="fas fa-file-pdf"></i> PDF
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    $.ajax({
        url: '<?= site_url('inspecciones/api/clientes') ?>',
        dataType: 'json',
        success: function(data) {
            const select = document.getElementById('filtroCliente');
            data.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id_cliente;
                opt.textContent = c.nombre_cliente;
                select.appendChild(opt);
            });
            $('#filtroCliente').select2({ placeholder: 'Filtrar por cliente...', allowClear: true, width: '100%' });
        }
    });

    $('#filtroCliente').on('change', function() {
        const val = this.value;
        document.querySelectorAll('.card-inspeccion').forEach(card => {
            card.style.display = (!val || card.dataset.cliente === val) ? '' : 'none';
        });
    });

    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const cliente = this.dataset.cliente;
            Swal.fire({
                title: 'Eliminar inspección?',
                html: 'Se eliminará la inspección de <strong>' + cliente + '</strong>.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Si, eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc3545',
            }).then(result => {
                if (result.isConfirmed) {
                    window.location.href = '<?= site_url('inspecciones/senalizacion/delete/') ?>' + id;
                }
            });
        });
    });
});
</script>
