<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Evaluaciones</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- DataTables Buttons CSS -->
    <link href="https://cdn.datatables.net/buttons/2.3.3/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <!-- Select2 CSS para select buscable -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        body {
            background-color: #f9f9f9;
            font-family: Arial, sans-serif;
        }

        h1 {
            margin: 20px 0;
            text-align: center;
            color: #333;
        }

        table {
            width: 100%;
        }

        .dataTables_filter input {
            background-color: #f0f0f0;
            border-radius: 5px;
            border: 1px solid #ccc;
            padding: 6px;
        }

        .dataTables_length select {
            background-color: #f0f0f0;
            border-radius: 5px;
            padding: 6px;
        }

        td,
        th {
            max-width: 20ch;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            height: 25px;
        }

        .tooltip-inner {
            max-width: 300px;
            word-wrap: break-word;
            z-index: 1050;
        }

        .filters select {
            width: 100%;
            padding: 4px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        /* Estilo para la columna de fila expandible */
        td.details-control {
            background: url('https://www.datatables.net/examples/resources/details_open.png') no-repeat center center;
            cursor: pointer;
        }

        tr.shown td.details-control {
            background: url('https://www.datatables.net/examples/resources/details_close.png') no-repeat center center;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm">
        <div class="container-fluid">
            <div class="d-flex align-items-center">
                <a href="https://dashboard.cycloidtalent.com/login" class="me-3">
                    <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo" height="60">
                </a>
                <a href="https://cycloidtalent.com/index.php/consultoria-sst" class="me-3">
                    <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo" height="60">
                </a>
                <a href="https://cycloidtalent.com/">
                    <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo" height="60">
                </a>
            </div>
            <div class="ms-auto d-flex">
                <div class="text-center me-3">
                    <h6 class="mb-1" style="font-size: 16px;">Ir a Dashboard</h6>
                    <a href="<?= base_url('/dashboardconsultant') ?>" class="btn btn-primary btn-sm">Ir a DashBoard</a>
                </div>
                <div class="text-center">
                    <h6 class="mb-1" style="font-size: 16px;">Añadir Registro</h6>
                    <a href="<?= base_url('/addEvaluacion') ?>" class="btn btn-success btn-sm" target="_blank">Añadir Registro</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Espaciado para el navbar fijo -->
    <div style="height: 100px;"></div>

    <div class="container-fluid mt-5">
        <h1 class="text-center mb-4">Lista de Evaluaciones</h1>
        <!-- Bloque para seleccionar cliente -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="clientSelect">Selecciona un Cliente:</label>
                <select id="clientSelect" class="form-select">
                    <option value="">Seleccione un cliente</option>
                </select>
            </div>
            <div class="col-md-2 align-self-end">
                <button id="loadData" class="btn btn-primary">Cargar Datos</button>
            </div>
        </div>
        <button id="clearState" class="btn btn-danger btn-sm mb-3">Restablecer Filtros</button>
        <div id="buttonsContainer"></div>
        <div class="table-responsive">
            <table id="evaluacionesTable" class="table table-striped table-bordered nowrap" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <!-- Columna para fila expandible -->
                        <th></th>
                        <th>Acciones</th>
                        <th>Cliente</th>
                        <th>Ciclo</th>
                        <th>Estándar</th>
                        <th>Item del Estándar</th>
                        <th>*Evaluación Inicial</th>
                        <th>Valor</th>
                        <th>Puntaje Cuantitativo</th>
                        <th>Item</th>
                        <th>Criterio</th>
                        <th>Modo de Verificación</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Los datos se cargarán vía AJAX -->
                </tbody>
                <!-- Se reubica el tfoot debajo del tbody -->
                <tfoot class="table-light">
                    <tr class="filters">
                        <th></th>
                        <th>
                            <select class="form-select form-select-sm filter-select" disabled>
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select">
                                <option value="">Todos</option>
                            </select>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white py-4 border-top mt-5">
        <div class="container text-center">
            <p class="fw-bold mb-1">Cycloid Talent SAS</p>
            <p class="mb-1">Todos los derechos reservados © 2024</p>
            <p class="mb-1">NIT: 901.653.912</p>
            <p class="mb-3">Sitio oficial:
                <a href="https://cycloidtalent.com/" target="_blank">https://cycloidtalent.com/</a>
            </p>
        </div>
    </footer>

    <!-- Scripts -->
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap Bundle (incluye Popper.js) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
    <!-- DataTables Buttons JS -->
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.colVis.min.js"></script>

    <script>
        // Función para formatear la fila expandible (detalles)
        function format(rowData) {
            var html = '<table cellpadding="5" cellspacing="0" border="0" style="width: 60%; table-layout: auto; word-wrap: break-word;">';
            var fields = [{
                    label: 'Cliente',
                    value: rowData.nombre_cliente
                },
                {
                    label: 'Ciclo',
                    value: rowData.ciclo
                },
                {
                    label: 'Estándar',
                    value: rowData.estandar
                },
                {
                    label: 'Item del Estándar',
                    value: rowData.item_del_estandar
                },
                {
                    label: 'Evaluación Inicial',
                    value: rowData.evaluacion_inicial
                },
                {
                    label: 'Valor',
                    value: rowData.valor
                },
                {
                    label: 'Puntaje Cuantitativo',
                    value: rowData.puntaje_cuantitativo
                },
                {
                    label: 'Item',
                    value: rowData.item
                },
                {
                    label: 'Criterio',
                    value: rowData.criterio
                },
                {
                    label: 'Modo de Verificación',
                    value: rowData.modo_de_verificacion
                },
                {
                    label: 'Observaciones',
                    value: rowData.observaciones
                }
            ];

            fields.forEach(function(field) {
                html += '<tr>';
                html += '<td style="white-space: normal; padding: 5px;"><strong>' + field.label + ':</strong></td>';
                html += '<td style="white-space: normal; padding: 5px;">' + (field.value || '') + '</td>';
                html += '</tr>';
            });

            html += '</table>';
            return html;
        }

        // Función para actualizar los filtros en el <tfoot>
        function updateFilters(api) {
            api.columns().every(function() {
                var column = this;
                var headerIndex = column.index();
                var filterElement = $('tfoot tr.filters th').eq(headerIndex).find('.filter-select');
                if (filterElement.length && !filterElement.prop('disabled')) {
                    filterElement.empty().append('<option value="">Todos</option>');
                    column.data().unique().sort().each(function(d) {
                        if (d && filterElement.find('option[value="' + d + '"]').length === 0) {
                            filterElement.append('<option value="' + d + '">' + d + '</option>');
                        }
                    });
                    var search = column.search();
                    if (search) {
                        filterElement.val(search);
                    }
                }
            });
        }

        // Inicialización una vez cargado el documento
        $(document).ready(function() {
            // Inicializar el select con Select2
            $('#clientSelect').select2({
                placeholder: 'Seleccione un cliente',
                allowClear: true,
                width: '100%'
            });

            // Cargar clientes vía AJAX usando las claves 'id' y 'nombre'
            $.ajax({
                url: "<?= base_url('/api/getClientes') ?>",
                method: "GET",
                dataType: "json",
                success: function(data) {
                    data.forEach(function(cliente) {
                        $("#clientSelect").append('<option value="' + cliente.id + '">' + cliente.nombre + '</option>');
                    });
                    var storedClient = localStorage.getItem('selectedClient');
                    if (storedClient) {
                        $("#clientSelect").val(storedClient).trigger('change');
                    }
                },
                error: function() {
                    alert('Error al cargar la lista de clientes.');
                }
            });

            // Inicializar DataTable con fila expandible y render para inline editing
            var table = $('#evaluacionesTable').DataTable({
                stateSave: true,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.1/i18n/es-ES.json"
                },
                pagingType: "full_numbers",
                responsive: true,
                autoWidth: false,
                dom: 'Bfltip',
                pageLength: 10,
                buttons: [{
                        extend: 'excelHtml5',
                        text: 'Exportar a Excel',
                        className: 'btn btn-success btn-sm'
                    },
                    {
                        extend: 'colvis',
                        text: 'Seleccionar Columnas',
                        className: 'btn btn-secondary btn-sm'
                    }
                ],
                ajax: {
                    url: "<?= base_url('/api/getEvaluaciones') ?>",
                    data: function(d) {
                        d.cliente = $("#clientSelect").val();
                    },
                    dataSrc: ''
                },
                columns: [{
                        data: null,
                        orderable: false,
                        className: 'details-control',
                        defaultContent: ''
                    },
                    {
                        data: 'acciones',
                        orderable: false
                    },
                    {
                        data: 'nombre_cliente'
                    },
                    {
                        data: 'ciclo'
                    },
                    {
                        data: 'estandar'
                    },
                    {
                        data: 'item_del_estandar'
                    },
                    {
                        data: 'evaluacion_inicial',
                        render: function(data, type, row) {
                            if (type === 'filter') {
                                return data;
                            }
                            data = (data === null || data === "") ? "-" : data;
                            var displayText = data || '&nbsp;';
                            return '<span class="editable-select" data-field="evaluacion_inicial" data-id="' + row.id_ev_ini + '" data-bs-toggle="tooltip" title="' + data + '">' + displayText + '</span>';
                        }
                    },
                    {
                        data: 'valor'
                    },
                    {
                        data: 'puntaje_cuantitativo'
                    },
                    {
                        data: 'item'
                    },
                    {
                        data: 'criterio'
                    },
                    {
                        data: 'modo_de_verificacion'
                    },
                    {
                        data: 'observaciones',
                        render: function(data, type, row) {
                            if (type === 'filter') {
                                return data;
                            }
                            data = (data === null || data === "") ? "-" : data;
                            var displayText = data || '&nbsp;';
                            return '<span class="editable" data-field="observaciones" data-id="' + row.id_ev_ini + '" data-bs-toggle="tooltip" title="' + data + '">' + displayText + '</span>';
                        }
                    }
                ],
                initComplete: function() {
                    var api = this.api();
                    updateFilters(api);
                }
            });

            table.buttons().container().appendTo('#buttonsContainer');

            // Evento para actualizar filtro al cambiar alguna opción en el <tfoot>
            $('tfoot').on('change', '.filter-select', function() {
                var columnIndex = $(this).closest('th').index();
                var value = $(this).val();
                table.column(columnIndex).search(value).draw();
            });

            // Evento para fila expandible (row child details)
            $('#evaluacionesTable tbody').on('click', 'td.details-control', function() {
                var tr = $(this).closest('tr');
                var row = table.row(tr);
                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                } else {
                    row.child(format(row.data())).show();
                    tr.addClass('shown');
                }
            });

            // Funcionalidad de edición en línea con stopPropagation
            $(document).on('click', '.editable-select, .editable', function(e) {
                e.stopPropagation(); // Evita que se active la fila expandible
                if ($(this).find('input, select').length) return;

                var cell = $(this);
                var field = cell.data('field');
                var id = cell.data('id');
                var currentValue = cell.text().trim();

                if (field === 'evaluacion_inicial') {
                    var options = ['CUMPLE TOTALMENTE', 'NO CUMPLE', 'NO APLICA'];
                    var select = $('<select>', {
                        class: 'form-select form-select-sm'
                    });
                    options.forEach(function(option) {
                        select.append($('<option>', {
                            value: option,
                            text: option,
                            selected: option === currentValue
                        }));
                    });
                    cell.html(select);
                    select.focus();
                    select.on('blur change', function() {
                        var newValue = select.val();
                        cell.text(newValue);
                        updateField(id, field, newValue, cell);
                    });
                } else if (field === 'observaciones') {
                    var input = $('<input>', {
                        type: 'text',
                        class: 'form-control',
                        value: currentValue
                    });
                    cell.html(input);
                    input.focus();
                    input.on('blur', function() {
                        var newValue = input.val();
                        cell.text(newValue);
                        updateField(id, field, newValue, cell);
                    });
                }
            });

            function updateField(id, field, value, cell) {
                $.ajax({
                    url: '<?= base_url('/api/updateEvaluacion') ?>',
                    method: 'POST',
                    data: {
                        id: id,
                        field: field,
                        value: value
                    },
                    success: function(response) {
                        if (response.success) {
                            console.log(response.message);
                            if (field === 'evaluacion_inicial' && response.puntaje_cuantitativo !== undefined) {
                                var row = cell.closest('tr');
                                row.find('td').eq(8).text(response.puntaje_cuantitativo);
                            }
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al comunicarse con el servidor:', error);
                        alert('Error al comunicarse con el servidor: ' + error);
                    }
                });
            }

            $("#loadData").click(function() {
                var clientId = $("#clientSelect").val();
                if (clientId) {
                    localStorage.setItem('selectedClient', clientId);
                    table.ajax.reload();
                } else {
                    alert('Por favor, seleccione un cliente.');
                }
            });

            $('#clientSelect').on('change', function() {
                var clientId = $(this).val();
                if (clientId) {
                    localStorage.setItem('selectedClient', clientId);
                    table.ajax.reload();
                }
            });

            $("#clearState").on("click", function() {
                localStorage.removeItem('selectedClient');
                var storageKey = 'DataTables_' + table.table().node().id + '_' + window.location.pathname;
                localStorage.removeItem(storageKey);
                table.state.clear();
                $('tfoot .filter-select').each(function() {
                    $(this).val('');
                });
                table.columns().search('').draw();
                $("#clientSelect").val(null).trigger("change");
            });

            function initializeTooltips() {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
            initializeTooltips();

            // Actualizar filtros y tooltips en cada redibujado de la tabla
            table.on('draw.dt', function() {
                updateFilters(table);
                initializeTooltips();
            });
        });
    </script>
</body>

</html>