<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Contenidos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome para íconos -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" rel="stylesheet">
    <!-- DataTables Bootstrap 5 CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Buttons CSS -->
    <link href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        /* Asegura que la tabla ocupe todo el ancho */
        .table-responsive {
            overflow-x: auto;
        }
        /* Filtros compactos */
        tfoot select {
            width: 100%;
            box-sizing: border-box;
            padding: 3px;
            height: calc(1.5em + .75rem + 2px);
            font-size: 0.875rem;
        }
        /* Mejora visual para los filtros */
        tfoot th {
            padding: 5px !important;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <h1 class="mb-4 text-dark">Lista de Contenidos</h1>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <a href="<?= base_url('addPolicy') ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus-circle me-1"></i> Añadir Nuevo Texto Particularizado
                </a>
                <p>Este es el código para adicionar texto particularizado: <code><?= htmlspecialchars("<?= \$clientPolicy['policy_content'] ?>") ?></code></p>

                <button id="clearState" class="btn btn-danger btn-sm ms-2">
                    <i class="fas fa-eraser me-1"></i> Restablecer Filtros
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table id="contentTable" class="table table-striped table-bordered table-hover w-100">
                <thead class="table-light">
                    <tr>
                        <th>Cliente</th>
                        <th>Tipo de Contenido</th>
                        <th>Texto del Contenido</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tfoot class="table-light">
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
                        <td>
                            <?= $clients[array_search($policy['client_id'], array_column($clients, 'id_cliente'))]['nombre_cliente'] ?>
                        </td>
                        <td>
                            <?= $policyTypes[array_search($policy['policy_type_id'], array_column($policyTypes, 'id'))]['type_name'] ?>
                        </td>
                        <td><?= esc($policy['policy_content']) ?></td>
                        <td>
                            <a href="<?= base_url('/editPolicy/' . $policy['id']) ?>"
                               class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="<?= base_url('/deletePolicy/' . $policy['id']) ?>"
                               class="btn btn-outline-danger btn-sm"
                               onclick="return confirm('¿Seguro que deseas eliminar esta política?');">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Librerías JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <!-- Buttons JS -->
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>

    <script>
    $(document).ready(function () {
        const table = $('#contentTable').DataTable({
            responsive: true,
            autoWidth: false,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
            },
            stateSave: true,
            stateDuration: 60 * 60 * 24, // 24 horas
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel me-1"></i> Exportar a Excel',
                    titleAttr: 'Exportar a Excel',
                    className: 'btn btn-success btn-sm'
                }
            ],
            initComplete: function () {
                const api = this.api();
                
                // Crear filtros para cada columna excepto la última (Acciones)
                api.columns().every(function (index) {
                    const column = this;
                    const columnCount = api.columns().count();
                    
                    // No agregamos filtro en la última columna (Acciones)
                    if (index === columnCount - 1) {
                        $(column.footer()).html('');
                        return;
                    }

                    // Crear el select
                    const select = $('<select class="form-control form-control-sm"><option value="">Todos</option></select>')
                        .appendTo($(column.footer()).empty())
                        .on('change', function () {
                            const val = $(this).val();
                            column.search(val ? '^' + $.fn.dataTable.util.escapeRegex(val) + '$' : '', true, false).draw();
                        });

                    // Obtener valores únicos y ordenarlos
                    const uniqueValues = [];
                    column.data().unique().each(function (d) {
                        if (d && d.trim() !== '') {
                            uniqueValues.push(d.trim());
                        }
                    });
                    
                    // Ordenar valores
                    uniqueValues.sort();
                    
                    // Agregar opciones al select
                    uniqueValues.forEach(function(value) {
                        select.append('<option value="' + value + '">' + value + '</option>');
                    });
                });
                
                // Restaurar filtros del estado guardado
                setTimeout(function() {
                    const state = table.state.loaded();
                    if (state && state.columns) {
                        api.columns().every(function (index) {
                            const columnState = state.columns[index];
                            if (columnState && columnState.search && columnState.search.search) {
                                const searchValue = columnState.search.search;
                                // Extraer el valor del patrón regex
                                const cleanValue = searchValue.replace(/[\^\$]/g, '');
                                const select = $('select', this.footer());
                                if (select.length && cleanValue) {
                                    select.val(cleanValue);
                                }
                            }
                        });
                    }
                }, 100);
            }
        });

        // Botón para limpiar estado y filtros
        $('#clearState').on('click', function () {
            // Limpiar todos los filtros
            table.columns().every(function () {
                const select = $('select', this.footer());
                if (select.length) {
                    select.val('');
                }
                this.search('');
            });
            
            // Redibujar tabla
            table.draw();
            
            // Limpiar estado guardado
            table.state.clear();
            
            // Limpiar localStorage específico
            const tableId = 'DataTables_contentTable_' + window.location.pathname;
            localStorage.removeItem(tableId);
            
            // Mensaje de confirmación
            alert('Filtros restablecidos correctamente');
        });

        // Evento para guardar estado cuando se cambian los filtros
        table.on('search.dt', function() {
            table.state.save();
        });
    });
    </script>
</body>
</html>