<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Dashboards Looker Studio</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/select/1.7.0/css/select.dataTables.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <!-- Título y Botón Agregar -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-primary">Lista de Dashboards</h1>
            <a href="<?= base_url('lookerstudio/add') ?>" class="btn btn-success">Agregar Dashboard</a>
        </div>

        <!-- Tabla de Dashboards -->
        <table id="datatable" class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Tipo de Dashboard</th>
                    <th>Enlace</th>
                    <th>Cliente</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lookerStudios as $looker): ?>
                    <tr>
                        <td><?= $looker['id_looker'] ?></td>
                        <td><?= htmlspecialchars($looker['tipodedashboard'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <a href="<?= htmlspecialchars($looker['enlace'], ENT_QUOTES, 'UTF-8') ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="btn btn-link text-decoration-none">Ver</a>
                        </td>
                        <td>
                            <?= $clients[array_search($looker['id_cliente'], array_column($clients, 'id_cliente'))]['nombre_cliente'] ?>
                        </td>
                        <td>
                            <a href="<?= base_url('lookerstudio/edit/' . $looker['id_looker']) ?>" class="btn btn-warning btn-sm">Editar</a>
                            <a href="<?= base_url('lookerstudio/delete/' . $looker['id_looker']) ?>"
                                class="btn btn-danger btn-sm"
                                onclick="return confirm('¿Estás seguro de eliminar este registro?')">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th></th>
                    <th>Tipo de Dashboard</th>
                    <th></th>
                    <th><input type="text" placeholder="Buscar Cliente" class="form-control form-control-sm" /></th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/select/1.7.0/js/dataTables.select.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar DataTable con configuración deseada
            var table = $('#datatable').DataTable({
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
                },
                responsive: true,
                lengthMenu: [10, 20, 25, 50],
                pageLength: 10,
                order: [[0, "desc"]],
                initComplete: function () {
                    this.api()
                        .columns([1])
                        .every(function () {
                            var column = this;
                            var select = $('<select class="form-select form-select-sm"><option value="">Todos</option></select>')
                                .appendTo($(column.footer()).empty())
                                .on('change', function () {
                                    var val = $.fn.dataTable.util.escapeRegex($(this).val());
                                    column.search(val ? '^' + val + '$' : '', true, false).draw();
                                });

                            column
                                .data()
                                .unique()
                                .sort()
                                .each(function (d, j) {
                                    select.append('<option value="' + d + '">' + d + '</option>');
                                });
                        });
                }
            });

            // Aplicar el filtrado para la columna de Cliente que mantiene el input de texto
            $('#datatable tfoot input').on('keyup change', function() {
                table
                    .column($(this).parent().index())
                    .search(this.value)
                    .draw();
            });
        });
    </script>
</body>

</html>