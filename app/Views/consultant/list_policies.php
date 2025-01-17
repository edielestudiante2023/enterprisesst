<!DOCTYPE html>
<html>

<head>
    <title>Lista de Contenidos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <!-- CSS para Buttons -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <!-- JS para Buttons y exportación a Excel -->
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>

    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }

        h1 {
            color: #343a40;
        }
    </style>
</head>

<body>

    <div class="container my-4">
        <h1>Lista de Contenidos</h1>
        <button id="clearState" class="btn btn-danger btn-sm">Restablecer Filtros</button>

        <table id="contentTable" class="table table-striped table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Cliente</th>
                    <th>Tipo de Contenido</th>
                    <th>Texto del Contenido</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th>Cliente</th>
                    <th>Tipo de Contenido</th>
                    <th>Texto del Contenido</th>
                    <th>Acciones</th>
                </tr>
            </tfoot>
            <tbody>
                <?php foreach ($policies as $policy): ?>
                <tr>
                    <td><?= $clients[array_search($policy['client_id'], array_column($clients, 'id_cliente'))]['nombre_cliente'] ?></td>
                    <td><?= $policyTypes[array_search($policy['policy_type_id'], array_column($policyTypes, 'id'))]['type_name'] ?></td>
                    <td><?= $policy['policy_content'] ?></td>
                    <td>
                        <a href="<?= base_url('/editPolicy/' . $policy['id']) ?>" class="btn btn-outline-secondary btn-sm">Editar</a>
                        <a href="<?= base_url('/deletePolicy/' . $policy['id']) ?>" class="btn btn-outline-danger btn-sm"
                            onclick="return confirm('¿Estás seguro de que deseas eliminar esta política?');">Eliminar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        $(document).ready(function () {
            // Inicialización de DataTable con Buttons
            let table = $('#contentTable').DataTable({
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
                },
                stateSave: true, // Habilitar guardar estado
                // Definir estructura DOM para incluir botones
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: 'Exportar a Excel',
                        titleAttr: 'Exportar a Excel'
                    }
                ],
                initComplete: function () {
                    // Añadir filtros dinámicos en cada columna
                    this.api().columns().every(function () {
                        let column = this;
                        let select = $('<select class="form-select form-select-sm"><option value="">Todos</option></select>')
                            .appendTo($(column.footer()).empty())
                            .on('change', function () {
                                let val = $.fn.dataTable.util.escapeRegex($(this).val());
                                column.search(val ? '^' + val + '$' : '', true, false).draw();
                            });

                        // Añadir opciones únicas del contenido de la columna
                        column.data().unique().sort().each(function (d, j) {
                            if (d) {
                                select.append('<option value="' + d + '">' + d + '</option>');
                            }
                        });
                    });
                }
            });

            // Restaurar filtros desde localStorage
            let state = table.state.loaded();
            if (state) {
                table.columns().every(function (index) {
                    let column = this;
                    let val = state.columns[index].search.search;
                    if (val) {
                        $('select', column.footer()).val(val.replace(/^(\^|\$)+|(\^|\$)+$/g, '')).change();
                    }
                });
            }

            // Botón para borrar el estado
            $('#clearState').on('click', function () {
                // Borrar estado guardado en localStorage
                localStorage.removeItem('DataTables_contentTable_/');
                table.state.clear(); // Limpiar estado en DataTables
                location.reload(); // Recargar la página
            });
        });
    </script>
</body>

</html>
