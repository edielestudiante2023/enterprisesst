<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Actividades - Plan de Trabajo Anual</title>
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

        h2 {
            margin: 20px 0;
            text-align: center;
            color: #333;
        }

        .btn {
            margin-bottom: 20px;
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
            height: 50px;
        }

        .tooltip-inner {
            max-width: 300px;
            white-space: normal;
        }

        /* Filtros en tfoot */
        tfoot select,
        tfoot input {
            width: 100%;
            padding: 4px;
        }

        /* Navbar logos y botones */
        .navbar img {
            height: 60px;
        }

        footer a {
            color: #007BFF;
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
        }

        .social-icons img {
            height: 24px;
            width: 24px;
        }

        /* Estilos para celdas editables */
        .editable {
            background-color: #fff3cd;
            cursor: pointer;
        }

        .editable-date {
            background-color: #d1ecf1;
            cursor: pointer;
        }

        .editable-select {
            background-color: #e2f0fb;
            cursor: pointer;
        }

        /* Fila expandible */
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
                    <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo">
                </a>
                <a href="https://cycloidtalent.com/index.php/consultoria-sst" class="me-3">
                    <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo">
                </a>
                <a href="https://cycloidtalent.com/">
                    <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo">
                </a>
            </div>
            <div class="ms-auto d-flex">
                <div class="text-center me-3">
                    <h6 class="mb-1" style="font-size: 16px;">Ir a Dashboard</h6>
                    <a href="<?= base_url('/dashboardconsultant') ?>" class="btn btn-primary btn-sm">Ir a DashBoard</a>
                </div>
                <div class="text-center">
                    <h6 class="mb-1" style="font-size: 16px;">Añadir Registro</h6>
                    <a href="<?= base_url('/addPlanDeTrabajoAnual') ?>" class="btn btn-success btn-sm">Añadir Registro</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Espacio para el navbar fijo -->
    <div style="height: 100px;"></div>

    <div class="container-fluid mt-5">
        <h2 class="text-center mb-4">Lista de Actividades del Plan de Trabajo Anual</h2>

        <!-- Filtro de cliente -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="clienteSelect">Seleccionar Cliente:</label>
                <select id="clienteSelect" class="form-select">
                    <option value="">Seleccione un cliente</option>
                </select>
            </div>
            <div class="col-md-2 align-self-end">
                <button id="loadData" class="btn btn-primary">Cargar Datos</button>
            </div>
        </div>

        <button id="clearState" class="btn btn-danger btn-sm mb-3">Restablecer Filtros</button>
        <div id="notification" class="alert alert-success" style="display: none;" role="alert"></div>

        <div class="table-responsive">
            <table id="actividadesTable" class="table table-striped table-bordered" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th></th>
                        <th>ID</th>
                        <th>Acciones</th>
                        <th>Cliente</th>
                        <th>PHVA</th>
                        <th>Numeral</th>
                        <th>Actividad</th>
                        <th>*Responsable</th>
                        <th>*Fecha Propuesta</th>
                        <th>*Fecha Cierre</th>
                        <th>*Estado Actividad</th>
                        <th>*Porcentaje Avance</th>
                        <th>Semana</th>
                        <th>*Observaciones</th>
                        <th>Creado en</th>
                        <th>Actualizado en</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th><input type="text" class="form-control form-control-sm filter-text" placeholder="Buscar Cliente"></th>
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
                        <th><input type="text" class="form-control form-control-sm filter-text" placeholder="Buscar Actividad"></th>
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
                <tbody>
                    <!-- Los datos se cargarán vía AJAX -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white py-4 border-top mt-4">
        <div class="container text-center">
            <p class="fw-bold mb-1">Cycloid Talent SAS</p>
            <p class="mb-1">Todos los derechos reservados © 2024</p>
            <p class="mb-1">NIT: 901.653.912</p>
            <p class="mb-3">Sitio oficial: <a href="https://cycloidtalent.com/" target="_blank">https://cycloidtalent.com/</a></p>
            <p><strong>Nuestras Redes Sociales:</strong></p>
            <div class="social-icons d-flex justify-content-center gap-3">
                <a href="https://www.facebook.com/CycloidTalent" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" alt="Facebook">
                </a>
                <a href="https://co.linkedin.com/company/cycloid-talent" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733561.png" alt="LinkedIn">
                </a>
                <a href="https://www.instagram.com/cycloid_talent?igsh=Nmo4d2QwZDg5dHh0" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733558.png" alt="Instagram">
                </a>
                <a href="https://www.tiktok.com/@cycloid_talent?_t=8qBSOu0o1ZN&_r=1" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/3046/3046126.png" alt="TikTok">
                </a>
            </div>
        </div>
    </footer>

    <!-- Scripts: jQuery, Bootstrap, DataTables, Buttons y Select2 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.colVis.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Función para formatear la fila expandible (detalles)
        function format(rowData) {
            var html = '<table class="table table-sm table-borderless" style="width:100%;">';
            html += '<tr><td><strong>ID:</strong></td><td>' + rowData.id_ptacliente + '</td></tr>';
            html += '<tr><td><strong>Cliente:</strong></td><td>' + (rowData.nombre_cliente || '') + '</td></tr>';
            html += '<tr><td><strong>PHVA:</strong></td><td>' + rowData.phva_plandetrabajo + '</td></tr>';
            html += '<tr><td><strong>Numeral:</strong></td><td>' + rowData.numeral_plandetrabajo + '</td></tr>';
            html += '<tr><td><strong>Actividad:</strong></td><td>' + rowData.actividad_plandetrabajo + '</td></tr>';
            html += '<tr><td><strong>Responsable:</strong></td><td>' + rowData.responsable_sugerido_plandetrabajo + '</td></tr>';
            html += '<tr><td><strong>Fecha Propuesta:</strong></td><td>' + rowData.fecha_propuesta + '</td></tr>';
            html += '<tr><td><strong>Fecha Cierre:</strong></td><td>' + rowData.fecha_cierre + '</td></tr>';
            html += '<tr><td><strong>Estado Actividad:</strong></td><td>' + rowData.estado_actividad + '</td></tr>';
            html += '<tr><td><strong>Porcentaje Avance:</strong></td><td>' + rowData.porcentaje_avance + '%</td></tr>';
            html += '<tr><td><strong>Semana:</strong></td><td>' + rowData.semana + '</td></tr>';
            html += '<tr><td><strong>Observaciones:</strong></td><td>' + rowData.observaciones + '</td></tr>';
            html += '<tr><td><strong>Creado en:</strong></td><td>' + rowData.created_at + '</td></tr>';
            html += '<tr><td><strong>Actualizado en:</strong></td><td>' + rowData.updated_at + '</td></tr>';
            html += '</table>';
            return html;
        }

        $(document).ready(function() {
            // Inicializar select2 en el filtro de cliente
            $('#clienteSelect').select2({
                placeholder: 'Seleccione un cliente',
                allowClear: true,
                width: '100%'
            });

            // Cargar clientes vía AJAX
            $.ajax({
                url: "<?= base_url('/api/getClientes') ?>",
                method: "GET",
                dataType: "json",
                success: function(data) {
                    data.forEach(function(cliente) {
                        $("#clienteSelect").append('<option value="' + cliente.id + '">' + cliente.nombre + '</option>');
                    });
                    // Si hay cliente seleccionado guardado en localStorage, lo cargamos
                    var storedClient = localStorage.getItem('selectedClient');
                    if (storedClient) {
                        $("#clienteSelect").val(storedClient).trigger('change');
                    }
                },
                error: function() {
                    alert('Error al cargar la lista de clientes.');
                }
            });

            // Determinar URL para AJAX según el cliente seleccionado
            var storedClient = localStorage.getItem('selectedClient');
            var tableAjaxUrl = "<?= base_url('/api/getActividadesAjax') ?>";
            if (storedClient) {
                tableAjaxUrl += "?cliente=" + storedClient;
            }

            // Inicializar DataTable
            var table = $('#actividadesTable').DataTable({
                stateSave: true,
                stateSaveCallback: function(settings, data) {
                    localStorage.setItem('DataTables_actividadesTable', JSON.stringify(data));
                },
                stateLoadCallback: function(settings) {
                    return JSON.parse(localStorage.getItem('DataTables_actividadesTable'));
                },
                order: [
                    [8, 'asc']
                ], // Ordenar por Fecha Propuesta (índice 8)
                ajax: {
                    url: tableAjaxUrl,
                    dataSrc: 'data'
                },
                columns: [{
                        data: null,
                        orderable: false,
                        className: 'details-control',
                        defaultContent: ''
                    },
                    {
                        data: "id_ptacliente"
                    },
                    {
                        data: null,
                        orderable: false,
                        render: function(data, type, row) {
                            return '<a href="<?= base_url("editPlanDeTrabajoAnual") ?>/' + row.id_ptacliente +
                                '" class="btn btn-warning btn-sm">Editar</a> ' +
                                '<a href="<?= base_url("deletePlanDeTrabajoAnual") ?>/' + row.id_ptacliente +
                                '" class="btn btn-danger btn-sm" onclick="return confirm(\'¿Estás seguro de eliminar esta actividad?\')">Eliminar</a>';
                        }
                    },
                    {
                        data: "nombre_cliente",
                        render: function(data, type, row) {
                            return '<span data-bs-toggle="tooltip" title="' + (data || '') + '">' + (data || '') + '</span>';
                        }
                    },
                    {
                        data: "phva_plandetrabajo"
                    },
                    {
                        data: "numeral_plandetrabajo"
                    },
                    {
                        data: "actividad_plandetrabajo",
                        render: function(data, type, row) {
                            return '<span data-bs-toggle="tooltip" title="' + data + '">' + data + '</span>';
                        }
                    },
                    {
                        data: "responsable_sugerido_plandetrabajo",
                        render: function(data, type, row) {
                            return '<span contenteditable="true" class="editable" data-field="responsable_sugerido_plandetrabajo" data-id="' + row.id_ptacliente + '" data-bs-toggle="tooltip" title="' + data + '">' + data + '</span>';
                        }
                    },
                    {
                        data: "fecha_propuesta",
                        render: function(data, type, row) {
                            return '<span class="editable-date" data-field="fecha_propuesta" data-id="' + row.id_ptacliente + '" data-bs-toggle="tooltip" title="' + data + '">' + data + '</span>';
                        }
                    },
                    {
                        data: "fecha_cierre",
                        render: function(data, type, row) {
                            return '<span class="editable-date" data-field="fecha_cierre" data-id="' + row.id_ptacliente + '" data-bs-toggle="tooltip" title="' + data + '">' + data + '</span>';
                        }
                    },
                    {
                        data: "estado_actividad",
                        render: function(data, type, row) {
                            return '<span class="editable-select" data-field="estado_actividad" data-id="' + row.id_ptacliente + '" data-bs-toggle="tooltip" title="' + data + '">' + data + '</span>';
                        }
                    },
                    {
                        data: "porcentaje_avance",
                        render: function(data, type, row) {
                            return '<span class="editable-select" data-field="porcentaje_avance" data-id="' + row.id_ptacliente + '" data-bs-toggle="tooltip" title="' + data + '">' + data + '%</span>';
                        }
                    },
                    {
                        data: "semana"
                    },
                    {
                        data: "observaciones",
                        render: function(data, type, row) {
                            return '<span contenteditable="true" class="editable" data-field="observaciones" data-id="' + row.id_ptacliente + '" data-bs-toggle="tooltip" title="' + data + '">' + data + '</span>';
                        }
                    },
                    {
                        data: "created_at",
                        visible: false
                    },
                    {
                        data: "updated_at",
                        visible: false
                    }
                ],
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.1/i18n/es-ES.json"
                },
                pagingType: "full_numbers",
                responsive: true,
                autoWidth: true,
                dom: 'Bfltip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: 'Exportar a Excel',
                        className: 'btn btn-success btn-sm me-2',
exportOptions: {
    columns: ':visible',
    format: {
        body: function(data, row, column, node) {
            // Return the full content from the title attribute if it exists
            if (column === 6) { // Assuming "Actividad" is the 6th column (0-indexed)
                return $(node).attr('title') || data;
            }
            return data;
        }
    }
                        }
                    },
                    {
                        extend: 'colvis',
                        text: 'Seleccionar Columnas',
                        className: 'btn btn-secondary btn-sm'
                    }
                ],
                initComplete: function() {
                    var api = this.api();
                    api.columns().every(function() {
                        var column = this;
                        var footer = $(column.footer());
                        var select = footer.find('select');
                        if (select.length) {
                            column.data().unique().sort().each(function(d) {
                                if (d !== null && d !== '') {
                                    select.append('<option value="' + d + '">' + d + '</option>');
                                }
                            });
                        }
                    });
                    // Vincular eventos a los inputs y selects del footer
                    $('#actividadesTable tfoot input').on('keyup change', function() {
                        var colIndex = $(this).parent().index();
                        table.column(colIndex).search(this.value).draw();
                    });
                    $('#actividadesTable tfoot select').on('change', function() {
                        var colIndex = $(this).parent().index();
                        table.column(colIndex).search(this.value).draw();
                    });
                }
            });

            // Actualizar selects del footer en cada redraw y re-inicializar tooltips
            table.on('draw', function() {
                table.columns().every(function() {
                    var column = this;
                    var footer = $(column.footer());
                    var select = footer.find('select');
                    if (select.length) {
                        select.empty().append('<option value="">Todos</option>');
                        column.data().unique().sort().each(function(d) {
                            if (d !== null && d !== '') {
                                select.append('<option value="' + d + '">' + d + '</option>');
                            }
                        });
                    }
                });
                initializeTooltips();
            });

            // Botón para cargar datos filtrados por cliente y guardar la selección
            $("#loadData").click(function() {
                var clienteID = $("#clienteSelect").val();
                if (clienteID) {
                    localStorage.setItem('selectedClient', clienteID);
                    table.ajax.url("<?= base_url('/api/getActividadesAjax') ?>?cliente=" + clienteID).load();
                } else {
                    alert('Por favor, seleccione un cliente.');
                }
            });

            // Botón para restablecer filtros y estado guardado (incluyendo el cliente seleccionado)
            $('#clearState').on('click', function() {
                localStorage.removeItem('selectedClient');
                localStorage.removeItem('DataTables_actividadesTable');
                table.state.clear();
                location.reload();
            });

            // Fila expandible
            $('#actividadesTable tbody').on('click', 'td.details-control', function() {
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

            // Edición inline para celdas editables (texto)
            $('#actividadesTable tbody').on('blur', 'span.editable', function() {
                var cell = $(this);
                var value = cell.text().trim();
                var field = cell.data('field');
                var id = cell.data('id');

                $.ajax({
                    url: '<?= base_url("/api/updatePlanDeTrabajo") ?>',
                    method: 'POST',
                    data: {
                        id: id,
                        field: field,
                        value: value
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#notification').text('Registro actualizado correctamente').fadeIn().delay(3000).fadeOut();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error al comunicarse con el servidor');
                    }
                });
            });

            // Edición inline para celdas de fecha
            $('#actividadesTable tbody').on('click', 'span.editable-date', function() {
                var cell = $(this);
                if (cell.find('input').length === 0) {
                    var currentValue = cell.text().trim();
                    var input = $('<input>', {
                        type: 'date',
                        value: currentValue,
                        class: 'form-control form-control-sm'
                    });
                    cell.html(input);
                    input.focus();
                    input.on('blur change', function() {
                        var newValue = $(this).val();
                        var field = cell.data('field');
                        var id = cell.data('id');
                        cell.text(newValue);
                        cell.attr('title', newValue).tooltip('dispose').tooltip();

                        $.ajax({
                            url: '<?= base_url("/api/updatePlanDeTrabajo") ?>',
                            method: 'POST',
                            data: {
                                id: id,
                                field: field,
                                value: newValue
                            },
                            success: function(response) {
                                if (response.success) {
                                    $('#notification').text('Registro actualizado correctamente').fadeIn().delay(3000).fadeOut();
                                } else {
                                    alert('Error: ' + response.message);
                                }
                            },
                            error: function() {
                                alert('Error al comunicarse con el servidor');
                            }
                        });
                    });
                }
            });

            // Edición inline para celdas con select (estado_actividad y porcentaje_avance)
            $('#actividadesTable tbody').on('click', 'span.editable-select', function() {
                var cell = $(this);
                if (cell.find('select').length === 0) {
                    var field = cell.data('field');
                    var currentValue = cell.text().trim().replace('%', '');
                    var id = cell.data('id');
                    var select = $('<select>', {
                        class: 'form-select form-select-sm'
                    });

                    if (field === 'estado_actividad') {
                        var options = ['ABIERTA', 'CERRADA', 'GESTIONANDO'];
                        $.each(options, function(index, option) {
                            var optionElem = $('<option>', {
                                value: option,
                                text: option
                            });
                            if (option === currentValue) optionElem.prop('selected', true);
                            select.append(optionElem);
                        });
                    } else if (field === 'porcentaje_avance') {
                        for (var i = 0; i <= 100; i += 10) {
                            var optionElem = $('<option>', {
                                value: i,
                                text: i + '%'
                            });
                            if (i == currentValue) optionElem.prop('selected', true);
                            select.append(optionElem);
                        }
                    }

                    cell.html(select);
                    select.focus();
                    select.on('blur change', function() {
                        var newValue = $(this).val();
                        cell.text(field === 'porcentaje_avance' ? newValue + '%' : newValue);
                        cell.attr('title', newValue).tooltip('dispose').tooltip();

                        $.ajax({
                            url: '<?= base_url("/api/updatePlanDeTrabajo") ?>',
                            method: 'POST',
                            data: {
                                id: id,
                                field: field,
                                value: newValue
                            },
                            success: function(response) {
                                if (!response.success) {
                                    alert('Error: ' + response.message);
                                }
                            },
                            error: function() {
                                alert('Error al comunicarse con el servidor');
                            }
                        });
                    });
                }
            });

            // Inicializar tooltips de Bootstrap
            function initializeTooltips() {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
            initializeTooltips();
        });
    </script>
</body>

</html>