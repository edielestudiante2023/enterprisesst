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

    <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
        <h6 class="mb-0">Investigaciones de Accidentes / Incidentes</h6>
        <a href="<?= site_url('inspecciones/investigacion-accidente/create') ?>" class="btn btn-sm btn-pwa-primary" style="width:auto; padding: 8px 16px;">
            <i class="fas fa-plus"></i> Nueva
        </a>
    </div>

    <div class="table-responsive">
        <table id="tablaInvestigaciones" class="table table-striped table-bordered table-hover" style="width:100%">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Fecha Evento</th>
                    <th>Tipo</th>
                    <th>Gravedad</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
                <tr class="filters-row">
                    <th><input type="text" placeholder="Cliente"></th>
                    <th><input type="text" placeholder="Fecha"></th>
                    <th>
                        <select>
                            <option value="">Todos</option>
                            <option value="Accidente">Accidente</option>
                            <option value="Incidente">Incidente</option>
                        </select>
                    </th>
                    <th><input type="text" placeholder="Gravedad"></th>
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
            <?php foreach (($investigaciones ?? []) as $inv): ?>
                <?php $tipoLabel = ($inv['tipo_evento'] ?? '') === 'accidente' ? 'Accidente' : 'Incidente'; ?>
                <tr>
                    <td><?= esc($inv['nombre_cliente'] ?? 'Sin cliente') ?></td>
                    <td data-order="<?= $inv['fecha_evento'] ?>"><?= date('d/m/Y', strtotime($inv['fecha_evento'])) ?></td>
                    <td>
                        <span class="badge <?= ($inv['tipo_evento'] ?? '') === 'accidente' ? 'bg-danger' : 'bg-warning text-dark' ?>"><?= $tipoLabel ?></span>
                    </td>
                    <td><?= esc(ucfirst($inv['gravedad'] ?? '-')) ?></td>
                    <td><span class="badge badge-<?= esc($inv['estado']) ?>"><?= $inv['estado'] === 'completo' ? 'Completo' : 'Borrador' ?></span></td>
                    <td class="table-actions">
                        <a href="<?= site_url('inspecciones/investigacion-accidente/edit/' . $inv['id']) ?>" class="btn btn-outline-dark" title="Editar"><i class="fas fa-edit"></i></a>
                        <?php if ($inv['estado'] === 'completo'): ?>
                            <a href="<?= site_url('inspecciones/investigacion-accidente/view/' . $inv['id']) ?>" class="btn btn-outline-dark" title="Ver"><i class="fas fa-eye"></i></a>
                            <a href="<?= site_url('inspecciones/investigacion-accidente/pdf/' . $inv['id']) ?>" class="btn btn-outline-success" target="_blank" title="PDF"><i class="fas fa-file-pdf"></i></a>
                        <?php endif; ?>
                        <a href="<?= site_url('inspecciones/investigacion-accidente/delete/' . $inv['id']) ?>" class="btn btn-outline-danger btn-delete" title="Eliminar"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
whenDtReady(function($) {
    $('#tablaInvestigaciones').DataTable(dtConfigBase({
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
            title: 'Eliminar investigacion?',
            text: 'Esta accion no se puede deshacer',
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#dc3545', confirmButtonText: 'Si, eliminar', cancelButtonText: 'Cancelar',
        }).then(result => { if (result.isConfirmed) window.location.href = url; });
    });
});
</script>
