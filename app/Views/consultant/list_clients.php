<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Lista de Clientes</title>
    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css">
    <!-- DataTables Buttons CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.3/css/buttons.dataTables.min.css">
    <!-- DataTables Fixed Columns CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/fixedcolumns/4.3.0/css/fixedColumns.dataTables.min.css">
    <!-- Custom Styles -->
    <style>
        /* Full screen table styles */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        
        .table-container {
            height: calc(100vh - 250px); /* Adjust based on navbar and footer height */
            overflow: auto;
            margin-top: 20px;
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

        /* Table footer input styles */
        tfoot input {
            width: 100%;
            padding: 3px;
            box-sizing: border-box;
        }

        /* Asegura que todas las filas tengan la misma altura */
        table.dataTable tbody tr {
            height: 60px;
        }

        /* Ajusta el ancho de las columnas para evitar espacios excesivos */
        table.dataTable th,
        table.dataTable td {
            white-space: nowrap;
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
        
        /* Asegura que los botones de acción se mantengan en una línea */
        .btn-group {
            white-space: nowrap;
            display: flex;
            gap: 5px;
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
            <div class="mb-3">
                <button id="clearState" class="btn btn-warning">Restablecer Filtros</button>
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
                            <th><input type="text" placeholder="Buscar acciones" /></th>
                            <th><input type="text" placeholder="Buscar ID" /></th>
                            <th><input type="text" placeholder="Buscar fecha" /></th>
                            <th><input type="text" placeholder="Buscar NIT" /></th>
                            <th><input type="text" placeholder="Buscar nombre" /></th>
                            <th><input type="text" placeholder="Buscar usuario" /></th>
                            <th><input type="text" placeholder="Buscar correo" /></th>
                            <th><input type="text" placeholder="Buscar teléfono 1" /></th>
                            <th><input type="text" placeholder="Buscar teléfono 2" /></th>
                            <th><input type="text" placeholder="Buscar dirección" /></th>
                            <th><input type="text" placeholder="Buscar contacto" /></th>
                            <th><input type="text" placeholder="Buscar código" /></th>
                            <th><input type="text" placeholder="Buscar representante" /></th>
                            <th><input type="text" placeholder="Buscar cédula" /></th>
                            <th><input type="text" placeholder="Buscar fecha fin" /></th>
                            <th><input type="text" placeholder="Buscar ciudad" /></th>
                            <th><input type="text" placeholder="Buscar estado" /></th>
                            <th><input type="text" placeholder="Buscar consultor" /></th>
                            <th><input type="text" placeholder="Buscar logo" /></th>
                            <th><input type="text" placeholder="Buscar firma" /></th>
                            <th><input type="text" placeholder="Buscar estándares" /></th>
                        </tr>
                    </tfoot>
                    <tbody>
                        <?php foreach ($clients as $client): ?>
                            <tr>
                                <td class="details-control"></td>
                                <td>
                                    <a href="<?= base_url('/editClient/' . htmlspecialchars($client['id_cliente'])) ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Editar Cliente">Editar</a>
                                    <a href="<?= base_url('/deleteClient/' . htmlspecialchars($client['id_cliente'])) ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar este cliente?')" data-bs-toggle="tooltip" title="Eliminar Cliente">Eliminar</a>
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
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.colVis.min.js"></script>
    <script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js"></script>

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
                    "url": "//cdn.datatables.net/plug-ins/1.13.1/i18n/es-ES.json"
                },
                "stateSave": true,
                "dom": 'Blfrtip',
                "buttons": [
                    {
                        extend: 'colvis',
                        text: 'Visibilidad de Columnas',
                        className: 'btn btn-secondary btn-sm'
                    },
                    {
                        extend: 'excelHtml5',
                        text: 'Descargar Excel',
                        className: 'btn btn-success btn-sm',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ],
                "responsive": true,
                "autoWidth": false,
                "scrollX": true,
                "scrollY": true,
                "scrollCollapse": true,
                "fixedColumns": {
                    "left": 2
                },
                "columnDefs": [
                    {
                        "targets": 0,
                        "orderable": false,
                        "width": "30px"
                    }
                ],
                "order": [[2, "desc"]],
                "pageLength": 10,
                "lengthMenu": [[10, 20, 50, 100], [10, 20, 50, 100]],
                "initComplete": function () {
                    // Apply the search
                    var api = this.api();
                    api.columns().every(function () {
                        var that = this;
                        $('input', this.footer()).on('keyup change clear', function () {
                            if (that.search() !== this.value) {
                                that
                                    .search(this.value)
                                    .draw();
                            }
                        });
                    });
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
        });
    </script>
</body>
</html>
