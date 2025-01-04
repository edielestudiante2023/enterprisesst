<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Lista de Consultores</title>
    <!-- Bootstrap 4.5.2 CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
    <!-- DataTables Buttons CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.7.0/css/buttons.dataTables.min.css">
    <!-- Custom Styles -->
    <style>
        body {
            background-color: #f8f9fa;
            color: #343a40;
        }

        .container {
            margin-top: 80px; /* Aumentado para acomodar el navbar fijo */
        }

        table {
            background-color: #ffffff;
        }

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

        /* Estilizar los botones de DataTables con Bootstrap */
        .dt-buttons .btn {
            margin-right: 0px;
        }
    </style>
</head>

<body>

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
                <a href="<?= base_url('/addConsultant') ?>" class="btn btn-success btn-sm mt-2" target="_blank">Añadir Registro</a>
            </div>
        </div>
    </nav>

    <!-- Espaciador para el Navbar Fijo -->
    <div style="height: 200px;"></div>

    <div class="container-fluid">
        <!-- Mensajes Flash -->
        <?php if (session()->getFlashdata('msg')): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <?= session()->getFlashdata('msg') ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <h2 class="mb-4">Lista de Consultores</h2>

        <!-- Botón Restablecer Filtros -->
        <div class="mb-3">
            <button id="clearState" class="btn btn-warning">Restablecer Filtros</button>
        </div>

        <div class="table-responsive">
            <table id="consultantsTable" class="table table-striped table-bordered nowrap" style="width:100%">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Cédula</th>
                        <th>Usuario</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Número de Licencia</th>
                        <th>Foto</th>
                        <th>Firma</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Cédula</th>
                        <th>Usuario</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Número de Licencia</th>
                        <th>Foto</th>
                        <th>Firma</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </tfoot>
                <tbody>
                    <?php foreach ($consultants as $consultant): ?>
                        <tr>
                            <td><?= htmlspecialchars($consultant['id_consultor']) ?></td>
                            <td><?= htmlspecialchars($consultant['nombre_consultor']) ?></td>
                            <td><?= htmlspecialchars($consultant['cedula_consultor']) ?></td>
                            <td><?= htmlspecialchars($consultant['usuario']) ?></td>
                            <td><?= htmlspecialchars($consultant['correo_consultor']) ?></td>
                            <td><?= htmlspecialchars($consultant['telefono_consultor']) ?></td>
                            <td><?= htmlspecialchars($consultant['numero_licencia']) ?></td>
                            <td>
                                <?php if (!empty($consultant['foto_consultor'])): ?>
                                    <img src="<?= base_url('uploads/' . htmlspecialchars($consultant['foto_consultor'])) ?>" alt="Foto del Consultor" width="50" data-toggle="tooltip" data-placement="top" title="Foto del Consultor">
                                <?php else: ?>
                                    No disponible
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($consultant['firma_consultor'])): ?>
                                    <img src="<?= base_url('uploads/' . htmlspecialchars($consultant['firma_consultor'])) ?>" alt="Firma del Consultor" width="50" data-toggle="tooltip" data-placement="top" title="Firma del Consultor">
                                <?php else: ?>
                                    No disponible
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($consultant['rol']) ?></td>
                            <td>
                                <a href="<?= base_url('/editConsultant/' . htmlspecialchars($consultant['id_consultor'])) ?>" class="btn btn-sm btn-warning" data-toggle="tooltip" data-placement="top" title="Editar Consultor">Editar</a>
                                <a href="<?= base_url('/deleteConsultant/' . htmlspecialchars($consultant['id_consultor'])) ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar este consultor?')" data-toggle="tooltip" data-placement="top" title="Eliminar Consultor">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

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

    <!-- jQuery 3.5.1 -->
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <!-- Bootstrap 4.5.2 JS -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- DataTables 1.10.24 JS -->
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <!-- DataTables Bootstrap 4 Integration JS -->
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
    <!-- DataTables Buttons Extension JS -->
    <script src="https://cdn.datatables.net/buttons/1.7.0/js/dataTables.buttons.min.js"></script>
    <!-- JSZip para exportar a Excel -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <!-- Buttons HTML5 Export JS -->
    <script src="https://cdn.datatables.net/buttons/1.7.0/js/buttons.html5.min.js"></script>
    <!-- Buttons ColVis JS -->
    <script src="https://cdn.datatables.net/buttons/1.7.0/js/buttons.colVis.min.js"></script>

    <script>
        $(document).ready(function () {
            // Inicialización de DataTables con opciones avanzadas
            var table = $('#consultantsTable').DataTable({
                // Traducción al español
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
                },
                // Guardar el estado de la tabla (filtros, paginación, etc.)
                "stateSave": true,
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
                "order": [[0, "asc"]],
                // Callback después de cada redibujado de la tabla
                "initComplete": function () {
                    // Agregar filtros desplegables en el footer
                    this.api().columns().every(function () {
                        var column = this;
                        var select = $('<select class="form-control form-control-sm"><option value="">Todos</option></select>')
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
                $('[data-toggle="tooltip"]').tooltip();
            }

            // Re-inicializar los tooltips cada vez que la tabla se redibuja
            table.on('draw', function () {
                initializeTooltips();
            });

            // Manejar el botón "Restablecer Filtros"
            $('#clearState').on('click', function () {
                // Remover el estado guardado de DataTables en localStorage
                localStorage.removeItem('DataTables_consultantsTable_/');
                table.state.clear();
                // Recargar la página para aplicar los cambios
                location.reload();
            });
        });
    </script>

</body>

</html>
