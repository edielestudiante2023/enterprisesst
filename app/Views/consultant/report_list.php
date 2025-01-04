<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Reportes</title>
    <!-- Enlaces de Bootstrap CSS y DataTables -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Enlace de DataTables Buttons CSS -->
    <link href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <style>
        /* Estilo para truncar el texto en la columna "Enlace" */
        td.enlace-col {
            max-width: 150px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Estilo para los select de filtros en el pie de la tabla */
        tfoot th {
            padding: 8px 10px;
            background-color: #f8f9fa;
        }
    </style>
</head>

<body class="bg-light">

    <nav style="background-color: white; position: fixed; top: 0; width: 100%; z-index: 1000; padding: 10px 0; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; max-width: 1200px; margin: 0 auto;">

            <!-- Logo izquierdo -->
            <div>
                <a href="https://dashboard.cycloidtalent.com/login">
                    <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo" style="height: 100px;">
                </a>
            </div>

            <!-- Logo centro -->
            <div>
                <a href="https://cycloidtalent.com/index.php/consultoria-sst">
                    <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo" style="height: 100px;">
                </a>
            </div>

            <!-- Logo derecho -->
            <div>
                <a href="https://cycloidtalent.com/">
                    <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo" style="height: 100px;">
                </a>
            </div>

        </div>

        <!-- Fila de botones -->
        <div style="display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 10px auto 0; padding: 0 20px;">
            <!-- Botón izquierdo -->
            <div style="text-align: center;">
                <h2 style="margin: 0; font-size: 16px;">Ir a Dashboard</h2>
                <a href="<?= base_url('/dashboardconsultant') ?>" class="btn btn-primary btn-sm mt-1">Ir a DashBoard</a>
            </div>

            <!-- Botón derecho -->
            <div style="text-align: center;">
                <h2 style="margin: 0; font-size: 16px;">Añadir Registro</h2>
                <a href="<?= base_url('/addReport') ?>" class="btn btn-success btn-sm mt-1" target="_blank">Añadir Registro</a>
            </div>
        </div>
    </nav>

    <!-- Ajustar el espaciado para evitar que el contenido se oculte bajo el navbar fijo -->
    <div style="height: 200px;"></div>

    <div class="container my-4">
        <h2 class="text-center mb-4">Lista de Reportes</h2>

        <!-- Tabla de Reportes -->
        <h3 class="mb-3">Reportes</h3>

        <?php if (session()->get('msg')): ?>
            <div class="alert alert-info">
                <?= session()->get('msg') ?>
            </div>
        <?php endif; ?>

        <?php if (isset($reports) && !empty($reports)) : ?>
            <!-- Botón para restablecer filtros y exportar a Excel -->
            <div class="mb-3">
                <button id="clearState" class="btn btn-danger btn-sm">Restablecer Filtros</button>
                <!-- <button id="exportExcel" class="btn btn-success btn-sm ms-2">Exportar a Excel</button> -->
            </div>

            <table id="reportTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título del Reporte</th>
                        <th>Tipo de Documento</th>
                        <th>Enlace</th>
                        <th>Estado</th>
                        <th>Observaciones</th>
                        <th>ID Cliente</th>
                        <th>Nombre del Cliente</th>
                        <th>Tipo de Reporte</th>
                        <th>Fecha de Creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>ID</th>
                        <th>Título del Reporte</th>
                        <th>Tipo de Documento</th>
                        <th>Enlace</th>
                        <th>Estado</th>
                        <th>Observaciones</th>
                        <th>ID Cliente</th>
                        <th>Nombre del Cliente</th>
                        <th>Tipo de Reporte</th>
                        <th>Fecha de Creación</th>
                        <th>Acciones</th>
                    </tr>
                </tfoot>
                <tbody>
                    <?php foreach ($reports as $report) : ?>
                        <tr>
                            <td data-bs-toggle="tooltip" title="ID Reporte: <?= $report['id_reporte'] ?>"><?= $report['id_reporte'] ?></td>
                            <td data-bs-toggle="tooltip" title="Título: <?= htmlspecialchars($report['titulo_reporte']) ?>"><?= htmlspecialchars($report['titulo_reporte']) ?></td>
                            <td data-bs-toggle="tooltip" title="Tipo de Documento: <?= htmlspecialchars($details[array_search($report['id_detailreport'], array_column($details, 'id_detailreport'))]['detail_report'] ?? 'N/A') ?>">
                                <?= htmlspecialchars($details[array_search($report['id_detailreport'], array_column($details, 'id_detailreport'))]['detail_report'] ?? 'N/A') ?>
                            </td>

                            <td class="enlace-col" data-bs-toggle="tooltip" title="Enlace: <?= htmlspecialchars($report['enlace']) ?>">
                                <a href="<?= htmlspecialchars($report['enlace']) ?>" target="_blank" data-bs-toggle="tooltip" title="<?= htmlspecialchars($report['enlace']) ?>"><?= htmlspecialchars($report['enlace']) ?></a>
                            </td>

                            <td data-bs-toggle="tooltip" title="Estado: <?= htmlspecialchars($report['estado']) ?>"><?= htmlspecialchars($report['estado']) ?></td>
                            <td data-bs-toggle="tooltip" title="Observaciones: <?= htmlspecialchars($report['observaciones']) ?>"><?= htmlspecialchars($report['observaciones']) ?></td>
                            <td data-bs-toggle="tooltip" title="ID Cliente: <?= htmlspecialchars($report['id_cliente']) ?>"><?= htmlspecialchars($report['id_cliente']) ?></td>
                            <td data-bs-toggle="tooltip" title="Nombre del Cliente: <?= htmlspecialchars($clients[array_search($report['id_cliente'], array_column($clients, 'id_cliente'))]['nombre_cliente'] ?? '') ?>">
                                <?= htmlspecialchars($clients[array_search($report['id_cliente'], array_column($clients, 'id_cliente'))]['nombre_cliente'] ?? '') ?>
                            </td>
                            <td data-bs-toggle="tooltip" title="Tipo de Reporte: <?= htmlspecialchars($reportTypes[array_search($report['id_report_type'], array_column($reportTypes, 'id_report_type'))]['report_type'] ?? '') ?>">
                                <?= htmlspecialchars($reportTypes[array_search($report['id_report_type'], array_column($reportTypes, 'id_report_type'))]['report_type'] ?? '') ?>
                            </td>
                            <td data-bs-toggle="tooltip" title="Fecha de Creación: <?= htmlspecialchars($report['created_at']) ?>"><?= htmlspecialchars($report['created_at']) ?></td>
                            <td>
                                <a href="<?= base_url('/editReport/' . $report['id_reporte']) ?>" class="btn btn-warning btn-sm" data-bs-toggle="tooltip" title="Editar Reporte">Editar</a>
                                <a href="<?= base_url('/deleteReport/' . $report['id_reporte']) ?>" class="btn btn-danger btn-sm" data-bs-toggle="tooltip" title="Eliminar Reporte" onclick="return confirm('¿Está seguro de eliminar este reporte?');">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p class="text-muted">No hay reportes disponibles.</p>
        <?php endif; ?>
    </div>

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

    <!-- Scripts de jQuery, Bootstrap, DataTables y DataTables Buttons -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <!-- Scripts de DataTables Buttons -->
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.colVis.min.js"></script>
    <!-- Iconos de Bootstrap para los botones -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script>
        $(document).ready(function () {
            // Función para inicializar los tooltips
            function initializeTooltips() {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
                console.log("Tooltips inicializados: " + tooltipList.length);
            }

            // Inicializar DataTable con configuraciones personalizadas
            const table = $('#reportTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
                },
                "pageLength": 50, // Mostrar 50 filas por defecto
                "order": [
                    [9, "desc"]
                ], // Ordenar por Fecha de Creación (índice 9)
                "columnDefs": [{
                    "targets": 3, // Índice de la columna Enlace
                    "width": "15%", // Fijar el ancho a 15%
                    "className": "text-truncate enlace-col" // Clase CSS para truncar el texto
                }, {
                    "targets": -1, // Última columna (Acciones)
                    "orderable": false, // Deshabilitar ordenamiento
                    "searchable": false // Deshabilitar búsqueda
                }],
                "stateSave": true, // Habilitar guardado de estado
                "stateSaveCallback": function (settings, data) {
                    // Guardar el estado en localStorage con una clave única
                    localStorage.setItem('DataTables_reportTable', JSON.stringify(data));
                },
                "stateLoadCallback": function (settings) {
                    // Cargar el estado desde localStorage
                    return JSON.parse(localStorage.getItem('DataTables_reportTable'));
                },
                "dom": 'Bfrtip', // Posición de los botones
                "buttons": [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="bi bi-file-earmark-excel"></i> Exportar a Excel',
                        className: 'btn btn-success btn-sm',
                        exportOptions: {
                            columns: ':not(:last-child)' // Excluir la columna de Acciones
                        },
                        title: 'Lista de Reportes',
                        filename: 'Lista_de_Reportes',
                        titleAttr: 'Exportar a Excel',
                        customize: function (xlsx) {
                            var sheet = xlsx.xl.worksheets['sheet1.xml'];

                            // Agregar estilo al título
                            $('row:first c', sheet).attr('s', '2'); // Aplicar estilo 2 a la primera fila

                            // Aquí puedes agregar más personalizaciones al XML de Excel si lo deseas
                        }
                    },
                    {
                        extend: 'colvis',
                        text: '<i class="bi bi-eye"></i> Visibilidad de Columnas',
                        className: 'btn btn-secondary btn-sm',
                        titleAttr: 'Mostrar u Ocultar Columnas'
                    }
                ],
                "initComplete": function () {
                    var api = this.api();

                    // Configurar los filtros en <tfoot>
                    api.columns().every(function () {
                        var column = this;
                        var columnIdx = column.index();

                        // Excluir la columna de Acciones de los filtros
                        if (columnIdx === 10) { // Índice 10 corresponde a 'Acciones'
                            $(column.footer()).empty();
                            return;
                        }

                        var select = $('<select class="form-select form-select-sm"><option value="">Todos</option></select>')
                            .appendTo($(column.footer()).empty())
                            .on('change', function () {
                                var val = $.fn.dataTable.util.escapeRegex($(this).val());
                                column.search(val ? '^' + val + '$' : '', true, false).draw();
                            });

                        // Precargar los datos únicos de la columna en el filtro
                        column.data().unique().sort().each(function (d, j) {
                            // Manejar valores nulos o vacíos
                            if (d === null || d === undefined) {
                                d = '';
                            }
                            // Escapar caracteres especiales para evitar problemas en HTML
                            var escapedData = $('<div>').text(d).html();
                            select.append('<option value="' + escapedData + '">' + escapedData + '</option>');
                        });

                        // Si hay un valor guardado en el estado, seleccionarlo
                        var state = api.state.loaded();
                        if (state && state.columns && state.columns[columnIdx].search && state.columns[columnIdx].search.search) {
                            var searchVal = state.columns[columnIdx].search.search.replace(/^\^|\$$/g, ''); // Eliminar ^ y $ de la búsqueda
                            select.val(searchVal);
                        }
                    });

                    // Inicializar los tooltips después de configurar los filtros
                    initializeTooltips();
                }
            });

            // Inicializar tooltips de Bootstrap
            function initializeTooltips() {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
                console.log("Tooltips inicializados: " + tooltipList.length);
            }

            // Re-inicializar tooltips después de cada dibujo de la tabla
            table.on('draw.dt', function () {
                initializeTooltips();
            });

            // Botón para borrar el estado
            $('#clearState').on('click', function () {
                // Borrar estado guardado en localStorage
                localStorage.removeItem('DataTables_reportTable');
                table.state.clear(); // Limpiar estado en DataTables
                location.reload(); // Recargar la página
            });

            // Botón para exportar a Excel (opcional si usas el botón de DataTables)
            $('#exportExcel').on('click', function () {
                table.button('.buttons-excel').trigger();
            });
        });
    </script>

</body>

</html>
