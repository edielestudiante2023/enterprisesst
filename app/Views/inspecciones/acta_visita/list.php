<style>
    .table-actions { white-space: nowrap; }
    .table-actions .btn { padding: 4px 8px; font-size: 12px; }
    .badge-borrador { background-color: #6c757d; color: white; }
    .badge-pendiente_firma { background-color: #ffc107; color: black; }
    .badge-completo { background-color: #28a745; color: white; }
    .filters-row th { background-color: #f1f3f5; padding: 6px !important; }
    .filters-row input, .filters-row select { width: 100%; font-size: 12px; padding: 4px; }
    table.dataTable { font-size: 13px; }
    table.dataTable thead th { background-color: #1c2437; color: white; }
</style>

<div class="container-fluid px-3">
    <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
        <h6 class="mb-0">Actas de Visita</h6>
        <a href="<?= site_url('inspecciones/acta-visita/create') ?>" class="btn btn-sm btn-pwa-primary" style="width:auto; padding: 8px 16px;">
            <i class="fas fa-plus"></i> Nueva
        </a>
    </div>

    <div class="table-responsive">
        <table id="tablaActas" class="table table-striped table-bordered table-hover" style="width:100%">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Motivo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
                <tr class="filters-row">
                    <th><input type="text" placeholder="Cliente"></th>
                    <th><input type="text" placeholder="Fecha"></th>
                    <th><input type="text" placeholder="Hora"></th>
                    <th><input type="text" placeholder="Motivo"></th>
                    <th>
                        <select>
                            <option value="">Todos</option>
                            <option value="Borrador">Borrador</option>
                            <option value="Pend. Firma">Pend. Firma</option>
                            <option value="Completo">Completo</option>
                        </select>
                    </th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach (($actas ?? []) as $acta): ?>
                <?php
                    $estado = $acta['estado'];
                    $estadoLabel = ['borrador' => 'Borrador', 'pendiente_firma' => 'Pend. Firma', 'completo' => 'Completo'][$estado] ?? $estado;
                ?>
                <tr>
                    <td><?= esc($acta['nombre_cliente'] ?? 'Sin cliente') ?></td>
                    <td data-order="<?= $acta['fecha_visita'] ?>"><?= date('d/m/Y', strtotime($acta['fecha_visita'])) ?></td>
                    <td><?= date('g:i A', strtotime($acta['hora_visita'])) ?></td>
                    <td><?= esc($acta['motivo']) ?></td>
                    <td><span class="badge badge-<?= esc($estado) ?>"><?= $estadoLabel ?></span></td>
                    <td class="table-actions">
                        <a href="<?= site_url('inspecciones/acta-visita/edit/' . $acta['id']) ?>" class="btn btn-outline-dark" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <?php if ($estado === 'pendiente_firma'): ?>
                            <a href="<?= site_url('inspecciones/acta-visita/firma/' . $acta['id']) ?>" class="btn btn-outline-warning" title="Firmar">
                                <i class="fas fa-signature"></i>
                            </a>
                        <?php endif; ?>
                        <?php if ($estado === 'completo'): ?>
                            <a href="<?= site_url('inspecciones/acta-visita/view/' . $acta['id']) ?>" class="btn btn-outline-dark" title="Ver">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?= site_url('inspecciones/acta-visita/pdf/' . $acta['id']) ?>" class="btn btn-outline-success" target="_blank" title="PDF">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                        <?php endif; ?>
                        <a href="<?= site_url('inspecciones/acta-visita/delete/' . $acta['id']) ?>" class="btn btn-outline-danger btn-delete" data-id="<?= $acta['id'] ?>" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
whenDtReady(function($) {
    var table = $('#tablaActas').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
        order: [[1, 'desc']],
        pageLength: 25,
        dom: 'Bfrtip',
        buttons: [{ extend: 'excelHtml5', text: 'Excel', className: 'btn btn-success btn-sm' }],
        orderCellsTop: true,
        initComplete: function () {
            this.api().columns().every(function (idx) {
                var column = this;
                $('input, select', $('.filters-row th').eq(idx)).on('keyup change', function () {
                    if (column.search() !== this.value) column.search(this.value).draw();
                });
            });
        }
    });

    $('.btn-delete').on('click', function(e) {
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
        }).then(result => { if (result.isConfirmed) window.location.href = url; });
    });
});
</script>
