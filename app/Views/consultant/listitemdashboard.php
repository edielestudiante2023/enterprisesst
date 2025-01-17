<!DOCTYPE html>
<html>
<head>
    <title>List Dashboard Items</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <style>
        /* Estilos personalizados para filtros y texto */
        tfoot select {
            width: 100%;
            padding: 4px;
            box-sizing: border-box;
            background-color: #f8f9fa;
            color: #333;
        }
        tfoot th {
            background-color: #e9ecef;
        }
        /* Estilo para la cabecera del sitio */
        .header-link {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <!-- Enlace con icono al sitio principal -->
    <div class="header-link">
        <a href="<?= base_url('') ?>" class="btn btn-outline-primary">
            <i class="bi bi-house-fill"></i> Ir al Sitio Principal
        </a>
    </div>

    <h2>Dashboard Items</h2>
    <a href="<?= base_url('consultant/additemdashboard') ?>" class="btn btn-primary mb-3">
        <i class="bi bi-plus-circle"></i> Add New Item
    </a>

    <div class="table-responsive">
        <table id="itemTable" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Rol</th>
                    <th>Tipo de Proceso</th>
                    <th>Detalle</th>
                    <th>Descripción</th>
                    <th>Acción URL</th>
                    <th>Orden</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th>ID</th>
                    <th>Rol</th>
                    <th>Tipo de Proceso</th>
                    <th>Detalle</th>
                    <th>Descripción</th>
                    <th>Acción URL</th>
                    <th>Orden</th>
                    <th></th>
                </tr>
            </tfoot>
            <tbody>
                <?php foreach($items as $item): ?>
                <tr>
                    <td><?= esc($item['id']) ?></td>
                    <td><?= esc($item['rol']) ?></td>
                    <td><?= esc($item['tipo_proceso']) ?></td>
                    <td><?= esc($item['detalle']) ?></td>
                    <td><?= esc($item['descripcion']) ?></td>
                    <td>
                        <a href="<?= base_url($item['accion_url']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm" title="Ir a <?= esc($item['detalle']) ?>">
                            <i class="bi bi-box-arrow-up-right"></i>
                        </a>
                    </td>
                    <td><?= esc($item['orden']) ?></td>
                    <td>
                        <a href="<?= base_url('consultant/edititemdashboar/'.$item['id']) ?>" class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil-square"></i> Edit
                        </a>
                        <a href="<?= base_url('consultant/deleteitemdashboard/'.$item['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                            <i class="bi bi-trash"></i> Delete
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- jQuery, Bootstrap JS y DataTables JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    $('#itemTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
        },
        initComplete: function () {
            this.api().columns().every(function () {
                var column = this;
                var select = $('<select><option value=""></option></select>')
                    .appendTo($(column.footer()).empty())
                    .on('change', function () {
                        var val = $.fn.dataTable.util.escapeRegex($(this).val());
                        column.search(val ? '^' + val + '$' : '', true, false).draw();
                    });

                column.data().unique().sort().each(function (d, j) {
                    if(d) {
                        select.append('<option value="' + d + '">' + d + '</option>');
                    }
                });
            });
        }
    });
});
</script>
</body>
</html>
