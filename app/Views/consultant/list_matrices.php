<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Matrices Interactivas Cliente</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .table tfoot th {
            vertical-align: top !important;
            padding-top: 15px !important;
        }
        .table tfoot th select,
        .table tfoot th input {
            margin-top: 5px;
            width: 100%;
            font-size: 0.9em;
        }
        #datatable th {
            min-width: 120px;
        }
        #datatable th:first-child {
            min-width: 60px;
            width: 60px;
        }
        .container {
            max-width: 100% !important;
            padding: 0 30px;
        }
        .table {
            width: 100% !important;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-primary">Lista de Matrices</h1>
            <div>
                <a href="<?= base_url('matrices/list?export=excel') ?>" class="btn btn-primary me-2">
                    <i class="fas fa-file-excel me-1"></i> Exportar a Excel
                </a>
                <a href="<?= base_url('matrices/add') ?>" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i> Agregar Enlace de Matriz o Carpeta
                </a>
            </div>
        </div>

        <table id="datatable" class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Tipo</th>
                    <th>Descripción</th>
                    <th>Observaciones</th>
                    <th>Enlace</th>
                    <th>Cliente</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($matrices as $matriz): ?>
                    <tr>
                        <td><?= $matriz['id_matriz'] ?></td>
                        <td><?= htmlspecialchars($matriz['tipo'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($matriz['descripcion'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($matriz['observaciones'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <a href="<?= htmlspecialchars($matriz['enlace'], ENT_QUOTES, 'UTF-8') ?>" 
                               target="_blank" 
                               rel="noopener noreferrer" 
                               class="btn btn-link text-decoration-none">Ver</a>
                        </td>
                        <td>
                            <?= $clients[array_search($matriz['id_cliente'], array_column($clients, 'id_cliente'))]['nombre_cliente'] ?>
                        </td>
                        <td>
                            <a href="<?= base_url('matrices/edit/' . $matriz['id_matriz']) ?>" class="btn btn-warning btn-sm">Editar</a>
                            <a href="<?= base_url('matrices/delete/' . $matriz['id_matriz']) ?>" 
                               class="btn btn-danger btn-sm" 
                               onclick="return confirm('¿Estás seguro de eliminar este registro?')">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>ID</th>
                    <th>Tipo</th>
                    <th>Descripción</th>
                    <th>Observaciones</th>
                    <th>Enlace</th>
                    <th>Cliente</th>
                    <th>Acciones</th>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#datatable').DataTable({
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
                },
                responsive: true,
                lengthMenu: [5, 10, 25, 50],
                pageLength: 10,
                order: [[0, 'desc']], // Ordenar por ID de forma descendente
                initComplete: function () {
                    // Dropdown filtering para columnas "tipo" y "descripción"
                    this.api().columns([1, 2]).each(function (index) {
                        var column = this;
                        var select = $('<select class="form-select form-select-sm"><option value="">Todos</option></select>')
                            .appendTo($(column.footer()))
                            .on('click', function(e) {
                                e.stopPropagation();
                            })
                            .on('change', function () {
                                var val = $.fn.dataTable.util.escapeRegex($(this).val());
                                column
                                    .search(val ? '^'+val+'$' : '', true, false)
                                    .draw();
                            });
 
                        column.data().unique().sort().each(function (d, j) {
                            select.append('<option value="'+d+'">'+d+'</option>')
                        });
                    });

                    // Text input filtering para columnas "observaciones" y "cliente"
                    this.api().columns([3, 5]).each(function (index) {
                        var column = this;
                        var input = $('<input type="text" class="form-control form-control-sm" placeholder="Buscar...">')
                            .appendTo($(column.footer()))
                            .on('click', function(e) {
                                e.stopPropagation();
                            })
                            .on('keyup change', function () {
                                if (column.search() !== this.value) {
                                    column
                                        .search(this.value)
                                        .draw();
                                }
                            });
                    });
                }
            });
        });
    </script>
</body>
</html>
