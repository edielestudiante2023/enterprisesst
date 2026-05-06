<style>
    .table-actions { white-space: nowrap; }
    .table-actions .btn { padding: 4px 8px; font-size: 12px; }
    .badge-borrador { background-color: #6c757d; color: white; }
    .badge-completo { background-color: #28a745; color: white; }
    .filters-row th { background-color: #f1f3f5; padding: 6px !important; }
    .filters-row input, .filters-row select { width: 100%; font-size: 12px; padding: 4px; }
    table.dataTable { font-size: 13px; }
    table.dataTable thead th { background-color: #1c2437; color: white; }
</style>

<div class="container-fluid px-3">
    <?php if (session()->getFlashdata('msg')): ?>
        <div class="alert alert-success mt-2"><?= session()->getFlashdata('msg') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger mt-2"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
        <h6 class="mb-0">Inspecciones de Botiquin</h6>
        <a href="<?= site_url('inspecciones/botiquin/create') ?>" class="btn btn-sm btn-pwa-primary" style="width:auto; padding: 8px 16px;">
            <i class="fas fa-plus"></i> Nueva
        </a>
    </div>

    <div class="table-responsive">
        <table id="tablaInspecciones" class="table table-striped table-bordered table-hover" style="width:100%">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>Ubicacion</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
                <tr class="filters-row">
                    <th><input type="text" placeholder="Cliente"></th>
                    <th><input type="text" placeholder="Fecha"></th>
                    <th><input type="text" placeholder="Ubicacion"></th>
                    <th>
                        <select>
                            <option value="">Todos</option>
                            <option value="Borrador">Borrador</option>
                            <option value="Completo">Completo</option>
                        </select>
                    </th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach (($inspecciones ?? []) as $insp): ?>
                <tr>
                    <td><?= esc($insp['nombre_cliente'] ?? 'Sin cliente') ?></td>
                    <td data-order="<?= $insp['fecha_inspeccion'] ?>"><?= date('d/m/Y', strtotime($insp['fecha_inspeccion'])) ?></td>
                    <td><?= esc($insp['ubicacion_botiquin'] ?? '-') ?></td>
                    <td><span class="badge badge-<?= esc($insp['estado']) ?>"><?= $insp['estado'] === 'completo' ? 'Completo' : 'Borrador' ?></span></td>
                    <td class="table-actions">
                        <a href="<?= site_url('inspecciones/botiquin/edit/' . $insp['id']) ?>" class="btn btn-outline-dark" title="Editar"><i class="fas fa-edit"></i></a>
                        <?php if ($insp['estado'] === 'completo'): ?>
                            <a href="<?= site_url('inspecciones/botiquin/view/' . $insp['id']) ?>" class="btn btn-outline-dark" title="Ver"><i class="fas fa-eye"></i></a>
                            <a href="<?= site_url('inspecciones/botiquin/pdf/' . $insp['id']) ?>" class="btn btn-outline-success" target="_blank" title="PDF"><i class="fas fa-file-pdf"></i></a>
                        <?php endif; ?>
                        <a href="<?= site_url('inspecciones/botiquin/delete/' . $insp['id']) ?>" class="btn btn-outline-danger btn-delete" title="Eliminar"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
whenDtReady(function($) {
    $('#tablaInspecciones').DataTable(dtConfigBase({
        order: [[1, 'desc']],
        initComplete: function () {
            this.api().columns().every(function (idx) {
                var column = this;
                $('input, select', $('.filters-row th').eq(idx)).on('keyup change', function () {
                    if (column.search() !== this.value) column.search(this.value).draw();
                });
            });
        }
    }));

    $('.btn-delete').on('click', function(e) {
        e.preventDefault();
        const url = this.href;
        Swal.fire({
            title: 'Eliminar inspeccion?',
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#dc3545', confirmButtonText: 'Si, eliminar', cancelButtonText: 'Cancelar',
        }).then(result => { if (result.isConfirmed) window.location.href = url; });
    });
});
</script>
