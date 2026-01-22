<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Lista de Clientes</title>
    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <!-- DataTables Buttons CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <!-- DataTables Select CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/select/1.7.0/css/select.bootstrap5.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom Styles -->
    <style>
        /* Full screen table styles */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .table-container {
            margin-top: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
        }

        /* Child row styles */
        td.details-control {
            background: url('https://www.datatables.net/examples/resources/details_open.png') no-repeat center center;
            cursor: pointer;
            width: 30px;
        }
        tr.shown td.details-control {
            background: url('https://www.datatables.net/examples/resources/details_close.png') no-repeat center center;
        }

        .child-details {
            padding: 15px;
            background-color: #f8f9fa;
        }

        .child-details .label {
            width: 30%;
            font-weight: bold;
            display: inline-block;
        }

        .child-details .value {
            width: 70%;
            display: inline-block;
            overflow: auto;
        }

        /* Enhanced footer input styles */
        tfoot input, tfoot select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            font-size: 13px;
            transition: all 0.2s;
            box-sizing: border-box;
        }
        
        tfoot input:focus, tfoot select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13,110,253,.25);
            outline: 0;
        }
        
        tfoot th {
            padding: 8px !important;
            background-color: #f8f9fa;
            border-top: 2px solid #dee2e6;
        }

        /* Enhanced table styling */
        table.dataTable {
            border-collapse: separate;
            border-spacing: 0;
        }
        
        table.dataTable tbody tr {
            transition: background-color 0.2s;
        }
        
        table.dataTable tbody tr:hover {
            background-color: #f8f9fa !important;
        }
        
        table.dataTable th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
            text-align: center;
            border: none;
            position: relative;
        }
        
        table.dataTable td {
            vertical-align: middle;
            padding: 12px 8px;
            border-bottom: 1px solid #dee2e6;
        }

        /* Ajustes responsivos */
        @media (max-width: 768px) {
            table.dataTable thead th,
            table.dataTable tbody td {
                padding: 8px;
            }
        }

        /* Estilos para la columna fija */
        .dt-fixedcolumns .table-bordered > :not(caption) > * > * {
            border-width: 0 1px;
        }
        
        /* Ajuste del contenedor de la tabla */
        .table-responsive {
            overflow-x: auto;
            width: 100%;
        }
        
        /* Action buttons styling */
        .action-buttons {
            display: flex;
            gap: 5px;
            justify-content: center;
            flex-wrap: nowrap;
        }
        
        .action-buttons .btn {
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 4px;
            transition: all 0.2s;
        }
        
        .action-buttons .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        /* Enhanced DataTables controls */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 15px;
        }
        
        .dataTables_wrapper .dataTables_info {
            padding-top: 15px;
            font-weight: 500;
        }
        
        /* Button styling */
        .dt-button {
            margin-right: 5px;
            border-radius: 4px;
        }
        
        .btn-reset {
            background: linear-gradient(45deg, #f093fb 0%, #f5576c 100%);
            border: none;
            color: white;
            margin-bottom: 15px;
        }
    </style>
</head>

<body class="bg-light text-dark">

<nav style="background-color: white; position: fixed; top: 0; width: 100%; z-index: 1000; padding: 10px 0; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; max-width: 1200px; margin: 0 auto;">

            <!-- Logo Izquierdo -->
            <div>
                <a href="https://dashboard.cycloidtalent.com/login">
                    <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo" style="height: 100px;">
                </a>
            </div>

            <!-- Logo Centro -->
            <div>
                <a href="https://cycloidtalent.com/index.php/consultoria-sst">
                    <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo" style="height: 100px;">
                </a>
            </div>

            <!-- Logo Derecho -->
            <div>
                <a href="https://cycloidtalent.com/">
                    <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo" style="height: 100px;">
                </a>
            </div>

        </div>

        <!-- Fila de Botones -->
        <div style="display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 10px auto 0; padding: 0 20px;">
            <!-- Botón Izquierdo -->
            <div style="text-align: center;">
                <h2 style="margin: 0; font-size: 16px;">Ir a Dashboard</h2>
                <a href="<?= base_url('/dashboardconsultant') ?>" class="btn btn-primary btn-sm mt-2">Ir a Dashboard</a>
            </div>

            <!-- Botón Centro - Gestión de Contratos -->
            <div style="text-align: center;">
                <h2 style="margin: 0; font-size: 16px;">Gestión de Contratos</h2>
                <a href="<?= base_url('/contracts') ?>" class="btn btn-warning btn-sm mt-2" title="Ver historial y gestionar contratos">
                    <i class="fas fa-file-contract"></i> Contratos
                </a>
            </div>

            <!-- Botón Derecho -->
            <div style="text-align: center;">
                <h2 style="margin: 0; font-size: 16px;">Añadir Registro</h2>
                <a href="<?= base_url('/addClient') ?>" class="btn btn-success btn-sm mt-2" target="_blank">Añadir Registro</a>
            </div>
        </div>
    </nav>

    <!-- Espaciador para el Navbar Fijo -->
    <div style="height: 160px;"></div>

    <div class="container-fluid mt-5">
        <!-- Mensajes Flash -->
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('msg')): ?>
            <div class="alert alert-success">
                <?= session()->getFlashdata('msg') ?>
            </div>
        <?php endif; ?>

        <h2 class="mb-4">Lista de Clientes</h2>

        <?php if (isset($clients) && !empty($clients)): ?>
            <div class="mb-3 d-flex justify-content-between align-items-center">
                <div>
                    <button id="clearState" class="btn btn-reset"><i class="fas fa-undo"></i> Restablecer Filtros</button>
                    <button id="clearFilters" class="btn btn-outline-secondary ms-2"><i class="fas fa-times"></i> Limpiar Búsqueda</button>
                </div>
                <div class="table-info">
                    <span class="badge bg-primary">Total: <span id="totalRecords">0</span></span>
                    <span class="badge bg-success ms-2">Filtrados: <span id="filteredRecords">0</span></span>
                </div>
            </div>
            <div class="table-container">
                <table id="clientsTable" class="table table-bordered table-striped nowrap" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th></th> <!-- Column for expand/collapse -->
                            <th>Acciones</th>
                            <th>ID</th>
                            <th>Fecha de Ingreso</th>
                            <th>NIT Cliente</th>
                            <th>Nombre Cliente</th>
                            <th>Usuario</th>
                            <th>Correo Cliente</th>
                            <th>Teléfono 1</th>
                            <th>Teléfono 2</th>
                            <th>Dirección</th>
                            <th>Persona de Contacto</th>
                            <th>Código Actividad Económica</th>
                            <th>Nombre Representante Legal</th>
                            <th>Cédula Representante Legal</th>
                            <th>Fecha Fin de Contrato</th>
                            <th>Ciudad</th>
                            <th>Estado</th>
                            <th>Consultor</th>
                            <th>Logo</th>
                            <th>Firma Representante Legal</th>
                            <th>Estándares</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th></th>
                            <th></th>
                            <th><input type="number" placeholder="ID" class="form-control form-control-sm" /></th>
                            <th><input type="date" class="form-control form-control-sm" /></th>
                            <th><input type="text" placeholder="NIT" class="form-control form-control-sm" /></th>
                            <th><input type="text" placeholder="Cliente" class="form-control form-control-sm" /></th>
                            <th><input type="text" placeholder="Usuario" class="form-control form-control-sm" /></th>
                            <th><input type="email" placeholder="Correo" class="form-control form-control-sm" /></th>
                            <th><input type="tel" placeholder="Teléfono" class="form-control form-control-sm" /></th>
                            <th><input type="tel" placeholder="Teléfono 2" class="form-control form-control-sm" /></th>
                            <th><input type="text" placeholder="Dirección" class="form-control form-control-sm" /></th>
                            <th><input type="text" placeholder="Contacto" class="form-control form-control-sm" /></th>
                            <th><input type="text" placeholder="Código" class="form-control form-control-sm" /></th>
                            <th><input type="text" placeholder="Representante" class="form-control form-control-sm" /></th>
                            <th><input type="text" placeholder="Cédula" class="form-control form-control-sm" /></th>
                            <th><input type="date" class="form-control form-control-sm" /></th>
                            <th><input type="text" placeholder="Ciudad" class="form-control form-control-sm" /></th>
                            <th>
                                <select class="form-select form-select-sm">
                                    <option value="">Estado</option>
                                    <option value="Activo">Activo</option>
                                    <option value="Inactivo">Inactivo</option>
                                    <option value="Suspendido">Suspendido</option>
                                </select>
                            </th>
                            <th><input type="text" placeholder="Consultor" class="form-control form-control-sm" /></th>
                            <th></th>
                            <th></th>
                            <th><input type="text" placeholder="Estándares" class="form-control form-control-sm" /></th>
                        </tr>
                    </tfoot>
                    <tbody>
                        <?php foreach ($clients as $client): ?>
                            <tr>
                                <td class="details-control"></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="<?= base_url('/editClient/' . htmlspecialchars($client['id_cliente'])) ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Editar Cliente">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= base_url('/contracts/client-history/' . htmlspecialchars($client['id_cliente'])) ?>" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Ver Historial de Contratos">
                                            <i class="fas fa-file-contract"></i>
                                        </a>
                                        <a href="<?= base_url('/deleteClient/' . htmlspecialchars($client['id_cliente'])) ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar este cliente?')" data-bs-toggle="tooltip" title="Eliminar Cliente">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($client['id_cliente']) ?></td>
                                <td><?= htmlspecialchars($client['fecha_ingreso']) ?></td>
                                <td><?= htmlspecialchars($client['nit_cliente']) ?></td>
                                <td><?= htmlspecialchars($client['nombre_cliente']) ?></td>
                                <td><?= htmlspecialchars($client['usuario']) ?></td>
                                <td><?= htmlspecialchars($client['correo_cliente']) ?></td>
                                <td><?= htmlspecialchars($client['telefono_1_cliente']) ?></td>
                                <td><?= htmlspecialchars($client['telefono_2_cliente']) ?></td>
                                <td><?= htmlspecialchars($client['direccion_cliente']) ?></td>
                                <td><?= htmlspecialchars($client['persona_contacto_compras']) ?></td>
                                <td><?= htmlspecialchars($client['codigo_actividad_economica']) ?></td>
                                <td><?= htmlspecialchars($client['nombre_rep_legal']) ?></td>
                                <td><?= htmlspecialchars($client['cedula_rep_legal']) ?></td>
                                <td><?= htmlspecialchars($client['fecha_fin_contrato']) ?></td>
                                <td><?= htmlspecialchars($client['ciudad_cliente']) ?></td>
                                <td><?= htmlspecialchars($client['estado']) ?></td>
                                <td><?= htmlspecialchars($client['nombre_consultor']) ?></td>
                                <td>
                                    <?php if (!empty($client['logo'])): ?>
                                        <img src="<?= base_url('uploads/' . htmlspecialchars($client['logo'])) ?>" alt="Logo" width="50" data-bs-toggle="tooltip" title="Logo del Cliente">
                                    <?php else: ?>
                                        No disponible
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($client['firma_representante_legal'])): ?>
                                        <img src="<?= base_url('uploads/' . htmlspecialchars($client['firma_representante_legal'])) ?>" alt="Firma" width="50" data-bs-toggle="tooltip" title="Firma del Representante Legal">
                                    <?php else: ?>
                                        No disponible
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($client['estandares']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>No hay clientes disponibles.</p>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer style="background-color: white; padding: 20px 0; border-top: 1px solid #B0BEC5; margin-top: 40px; color: #3A3F51; font-size: 14px; text-align: center;">
        <!-- ... (rest of the footer code remains the same) ... -->
    </footer>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/select/1.7.0/js/dataTables.select.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>

    <script>
        $(document).ready(function () {
            // Function to format the expanded details
            function formatDetails(d) {
                return '<div class="child-details">' +
                    '<div><span class="label">Nombre Cliente:</span><span class="value">' + d[5] + '</span></div>' +
                    '<div><span class="label">NIT:</span><span class="value">' + d[4] + '</span></div>' +
                    '<div><span class="label">Dirección:</span><span class="value">' + d[10] + '</span></div>' +
                    '<div><span class="label">Correo:</span><span class="value">' + d[7] + '</span></div>' +
                    '<div><span class="label">Teléfonos:</span><span class="value">' + d[8] + ' / ' + d[9] + '</span></div>' +
                    '<div><span class="label">Representante Legal:</span><span class="value">' + d[13] + '</span></div>' +
                    '<div><span class="label">Estándares:</span><span class="value">' + d[21] + '</span></div>' +
                    '</div>';
            }

            // Setup - add a text input to each footer cell
            $('#clientsTable tfoot th input').each(function () {
                var title = $(this).attr('placeholder');
                $(this).attr('title', title);
            });

            var table = $('#clientsTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json",
                    "searchPlaceholder": "Buscar en toda la tabla..."
                },
                "stateSave": true,
                "stateDuration": 60 * 60 * 24, // 24 hours
                "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                       '<"row"<"col-sm-12"B>>' +
                       '<"row"<"col-sm-12"tr>>' +
                       '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                "buttons": [
                    {
                        extend: 'colvis',
                        text: '<i class="fas fa-eye"></i> Columnas',
                        className: 'btn btn-outline-secondary btn-sm'
                    },
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success btn-sm',
                        exportOptions: {
                            columns: ':visible:not(:first-child)'
                        },
                        filename: 'clientes_' + new Date().toISOString().split('T')[0]
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger btn-sm',
                        exportOptions: {
                            columns: [2, 4, 5, 7, 16, 17, 18]
                        },
                        filename: 'clientes_' + new Date().toISOString().split('T')[0]
                    }
                ],
                "processing": true,
                "responsive": true,
                "autoWidth": false,
                "scrollX": true,
                "scrollY": "60vh",
                "scrollCollapse": true,
                "columnDefs": [
                    {
                        "targets": [0, 1],
                        "orderable": false,
                        "searchable": false,
                        "className": "text-center"
                    },
                    {
                        "targets": [19, 20],
                        "orderable": false,
                        "searchable": false
                    }
                ],
                "order": [[2, "desc"]],
                "pageLength": 25,
                "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
                "searchDelay": 500,
                "initComplete": function () {
                    var api = this.api();
                    
                    // Update record counts
                    $('#totalRecords').text(api.data().length);
                    $('#filteredRecords').text(api.rows({search: 'applied'}).data().length);
                    
                    // Apply column filters
                    api.columns().every(function (index) {
                        var that = this;
                        var input = $('input, select', this.footer());
                        
                        if (input.length > 0) {
                            input.on('keyup change clear', function () {
                                if (that.search() !== this.value) {
                                    that.search(this.value).draw();
                                }
                            });
                        }
                    });
                },
                "drawCallback": function () {
                    var api = this.api();
                    $('#filteredRecords').text(api.rows({search: 'applied'}).data().length);
                }
            });

            // Add event listener for opening and closing details
            $('#clientsTable tbody').on('click', 'td.details-control', function () {
                var tr = $(this).closest('tr');
                var row = table.row(tr);

                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                } else {
                    row.child(formatDetails(row.data())).show();
                    tr.addClass('shown');
                }
            });

            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Clear state button handler
            $('#clearState').on('click', function () {
                localStorage.removeItem('DataTables_clientsTable_/');
                table.state.clear();
                location.reload();
            });
            
            // Clear filters button handler
            $('#clearFilters').on('click', function () {
                // Clear all column filters
                table.columns().every(function () {
                    this.search('');
                });
                
                // Clear footer inputs
                $('#clientsTable tfoot input, #clientsTable tfoot select').val('');
                
                // Clear global search
                table.search('').draw();
            });
        });
    </script>
</body>
</html>
