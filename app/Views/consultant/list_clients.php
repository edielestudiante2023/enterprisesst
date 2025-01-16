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
    <!-- Custom Styles -->
    <style>
        /* Asegura que todas las filas tengan la misma altura */
        table.dataTable tbody tr {
            height: 60px; /* Ajusta este valor según tus necesidades */
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
    </style>
</head>

<body class="bg-light text-dark">

    <!-- Navbar Fijo -->
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

    <div class="container mt-5">
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
            <div class="table-responsive">
                <table id="clientsTable" class="table table-bordered table-striped nowrap" style="width:100%">
                    <thead class="table-light">
                        <tr>
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
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <!-- Genera un <select> para cada columna excepto las que no requieren filtro -->
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
                            <th>Acciones</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        <?php foreach ($clients as $client): ?>
                            <tr>
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
                                <td>
                                    <a href="<?= base_url('/editClient/' . htmlspecialchars($client['id_cliente'])) ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Editar Cliente">Editar</a>
                                    <a href="<?= base_url('/deleteClient/' . htmlspecialchars($client['id_cliente'])) ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar este cliente?')" data-bs-toggle="tooltip" title="Eliminar Cliente">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>No hay clientes disponibles.</p>
        <?php endif; ?>

        <!-- <a href="<?= base_url('/dashboardconsultant') ?>" class="btn btn-secondary mt-3">Volver al Dashboard</a> -->
    </div>

    <!-- Footer -->
    <footer style="background-color: white; padding: 20px 0; border-top: 1px solid #B0BEC5; margin-top: 40px; color: #3A3F51; font-size: 14px; text-align: center;">
        <div style="max-width: 1200px; margin: 0 auto; display: flex; flex-direction: column; align-items: center;">
            <!-- Company and Rights -->
            <p style="margin: 0; font-weight: bold;">Cycloid Talent SAS</p>
            <p style="margin: 5px 0;">Todos los derechos reservados © 2024</p>
            <p style="margin: 5px 0;">NIT: 901.653.912</p>

            <!-- Website Link -->
            <p style="margin: 5px 0;">
                Sitio oficial: <a href="https://cycloidtalent.com/" target="_blank" style="color: #007BFF; text-decoration: none;">https://cycloidtalent.com/</a>
            </p>

            <!-- Social Media Links -->
            <p style="margin: 15px 0 5px;"><strong>Nuestras Redes Sociales:</strong></p>
            <div style="display: flex; gap: 15px; justify-content: center;">
                <a href="https://www.facebook.com/CycloidTalent" target="_blank" style="color: #3A3F51; text-decoration: none;">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" alt="Facebook" style="height: 24px; width: 24px;">
                </a>
                <a href="https://co.linkedin.com/company/cycloid-talent" target="_blank" style="color: #3A3F51; text-decoration: none;">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733561.png" alt="LinkedIn" style="height: 24px; width: 24px;">
                </a>
                <a href="https://www.instagram.com/cycloid_talent?igsh=Nmo4d2QwZDg5dHh0" target="_blank" style="color: #3A3F51; text-decoration: none;">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733558.png" alt="Instagram" style="height: 24px; width: 24px;">
                </a>
                <a href="https://www.tiktok.com/@cycloid_talent?_t=8qBSOu0o1ZN&_r=1" target="_blank" style="color: #3A3F51; text-decoration: none;">
                    <img src="https://cdn-icons-png.flaticon.com/512/3046/3046126.png" alt="TikTok" style="height: 24px; width: 24px;">
                </a>
            </div>
        </div>
    </footer>

    <!-- jQuery 3.6.0 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- Bootstrap 5 Bundle JS (Incluye Popper) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables 1.13.1 JS -->
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <!-- DataTables Buttons Extension 2.3.3 JS -->
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/dataTables.buttons.min.js"></script>
    <!-- JSZip para exportar a Excel -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <!-- Buttons HTML5 Export JS -->
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.html5.min.js"></script>
    <!-- Buttons ColVis JS -->
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.colVis.min.js"></script>

    <script>
        $(document).ready(function () {
            // Inicialización de DataTables con opciones avanzadas
            var table = $('#clientsTable').DataTable({
                // Traducción al español
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.1/i18n/es-ES.json"
                },
                // Guardar el estado de la tabla (filtros, paginación, etc.)
                "stateSave": true,
                // Forzar orden al cargar el estado guardado
                "stateLoadParams": function(settings, data) {
                    data.order = [[0, "desc"]];
                },
                // Configuración de los botones
                "dom": 'Bfrtip', // Posicionamiento de los botones
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
                            columns: ':visible' // Exporta solo las columnas visibles
                        }
                    }
                ],
                // Inicialización responsiva
                "responsive": true,
                // Configuración para ajustar automáticamente el ancho de las columnas
                "autoWidth": false,
                // Orden inicial (opcional)
                "order": [[0, "desc"]],
                // Callback después de cada redibujado de la tabla
                "pageLength": 40,  // Número de filas por página
                "initComplete": function () {
                    // Agregar filtros desplegables en el footer
                    this.api().columns().every(function () {
                        var column = this;
                        var select = $('<select class="form-select form-select-sm"><option value="">Todos</option></select>')
                            .appendTo($(column.footer()).empty())
                            .on('change', function () {
                                var val = $.fn.dataTable.util.escapeRegex($(this).val());
                                column
                                    .search(val ? '^' + val + '$' : '', true, false)
                                    .draw();
                            });

                        // Obtener valores únicos para cada columna
                        column.data().unique().sort().each(function (d, j) {
                            if (d) { // Evita agregar opciones vacías
                                select.append('<option value="' + d + '">' + d + '</option>')
                            }
                        });
                    });

                    // Inicializar los tooltips después de agregar los filtros
                    initializeTooltips();
                }
            });

            // Función para inicializar los tooltips de Bootstrap
            function initializeTooltips() {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                    new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }

            // Re-inicializar los tooltips cada vez que la tabla se redibuja
            table.on('draw', function () {
                initializeTooltips();
            });

            // Manejar el botón "Restablecer Filtros"
            $('#clearState').on('click', function () {
                // Remover el estado guardado de DataTables en localStorage
                localStorage.removeItem('DataTables_clientsTable_/');
                table.state.clear();
                // Recargar la página para aplicar los cambios
                location.reload();
            });
        });
    </script>
</body>

</html>
