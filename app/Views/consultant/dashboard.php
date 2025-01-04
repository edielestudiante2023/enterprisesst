<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Consultor</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- DataTables Buttons CSS -->
    <link href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #F8F9FA;
            color: #343A40;
        }

        .navbar {
            background-color: #F8F9FA;
            border-bottom: 1px solid #E9ECEF;
        }

        .navbar-brand img {
            max-height: 50px;
        }

        .header-logos img {
            max-height: 50px;
            margin-right: 10px;
        }

        .content {
            padding: 20px;
        }

        .table th {
            background-color: #6C757D;
            color: #FFF;
        }

        .table td a {
            color: #495057;
            text-decoration: none;
        }

        .table td a:hover {
            color: #007BFF;
        }

        .logout-button {
            text-align: center;
            margin-top: 20px;
        }

        /* Estilo para truncar el texto en celdas específicas */
        .enlace-col {
            max-width: 200px;
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

<body>
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container-fluid">
            <div class="header-logos d-flex justify-content-between align-items-center w-100">
                <!-- Logo izquierdo -->
                <div>
                    <a href="https://dashboard.cycloidtalent.com/login" target="_blank">
                        <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo">
                    </a>
                </div>
                <!-- Logo centro -->
                <div>
                    <a href="https://cycloidtalent.com/index.php/consultoria-sst" target="_blank">
                        <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo">
                    </a>
                </div>
                <!-- Logo derecho -->
                <div>
                    <a href="https://cycloidtalent.com/" target="_blank">
                        <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo">
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Ajustar el espaciado para evitar que el contenido se oculte bajo el navbar fijo -->
    <div style="height: 160px;"></div>

    <div class="container-fluid content">
        <div class="welcome-banner p-4 mb-4 rounded" style="background-color: #E9F7EF; border-left: 5px solid #28A745; color: #2D3436;">
            <h3 class="mb-3" style="color: #28A745;">¡Bienvenido al Dashboard de Consultores de Cycloid Talent!</h3>
            <p class="mt-3">Explora las diferentes secciones y aprovecha las herramientas disponibles para optimizar tu desempeño.</p>
        </div>

        <!-- Tabla con DataTables -->
        <div class="table-responsive">
            <!-- Botones para Exportar y Visibilidad de Columnas -->
            <div class="mb-3 d-flex justify-content-between">
                <div>
                    <button id="clearState" class="btn btn-danger btn-sm">Restablecer Filtros</button>
                    <button id="exportExcel" class="btn btn-success btn-sm ms-2">Exportar a Excel</button>
                </div>
                <div>
                    <!-- Botón ColVis (Opcional si no se usa el botón integrado) -->
                </div>
            </div>

            <table id="consultorTable" class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th>Detalle</th>
                        <th>Descripción/Funcionalidad</th> <!-- Nueva columna -->
                        <th>Acción</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>Detalle</th>
                        <th>Descripción/Funcionalidad</th>
                        <th>Acción</th>
                    </tr>
                </tfoot>
                <tbody>
                    <!-- Capacitaciones y Evaluaciones -->
                    <tr>
                        <td>Capacitaciones</td>
                        <td>Accede a la lista de capacitaciones disponibles.</td>
                        <td>
                            <a href="<?= base_url('listCapacitaciones') ?>" target="_blank" data-bs-toggle="tooltip" title="Abrir Capacitaciones">
                                <button type="button" class="btn btn-outline-secondary">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Cronogramas de Capacitación</td>
                        <td>Accede a los cronogramas detallados de capacitaciones programadas.</td>
                        <td>
                            <a href="<?= base_url('listcronogCapacitacion') ?>" target="_blank" data-bs-toggle="tooltip" title="Abrir Cronogramas de Capacitación">
                                <button type="button" class="btn btn-outline-secondary">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Evaluaciones</td>
                        <td>Accede a la lista de evaluaciones realizadas y sus resultados.</td>
                        <td>
                            <a href="<?= base_url('listEvaluaciones') ?>" target="_blank" data-bs-toggle="tooltip" title="Abrir Evaluaciones">
                                <button type="button" class="btn btn-outline-secondary">Abrir</button>
                            </a>
                        </td>
                    </tr>

                   

                    <!-- Documentación -->
                    <tr>
                        <td>Cargue de PDF´S a clientes</td>
                        <td>Modulo de cargue de soportes de gestión</td>
                        <td>
                            <a href="<?= base_url('/reportList') ?>" target="_blank" data-bs-toggle="tooltip" title="Abrir Cargue de PDF's">
                                <button type="button" class="btn btn-outline-secondary">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Documentos y Matrices</td>
                        <td>Consulta los tipos de documentos y matrices disponibles.</td>
                        <td>
                            <a href="<?= base_url('listReportTypes') ?>" target="_blank" data-bs-toggle="tooltip" title="Abrir Documentos y Matrices">
                                <button type="button" class="btn btn-outline-secondary">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Matrices</td>
                        <td>Matrices Interactivas de Gestión</td>
                        <td>
                            <a href="<?= base_url('matrices/list') ?>" target="_blank" data-bs-toggle="tooltip" title="Abrir Matrices">
                                <button type="button" class="btn btn-outline-secondary">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Looker Studio</td>
                        <td>Tableros de Indicadores del cliente</td>
                        <td>
                            <a href="<?= base_url('lookerstudio/list') ?>" target="_blank" data-bs-toggle="tooltip" title="Abrir Looker Studio">
                                <button type="button" class="btn btn-outline-secondary">Abrir</button>
                            </a>
                        </td>
                    </tr>
                  
                    <tr>
                        <td>Políticas</td>
                        <td>Consulta las políticas asociadas a la empresa.</td>
                        <td>
                            <a href="<?= base_url('/listPolicies') ?>" target="_blank" data-bs-toggle="tooltip" title="Abrir Políticas">
                                <button type="button" class="btn btn-outline-secondary">Abrir</button>
                            </a>
                        </td>
                    </tr>
                   

                    <!-- Indicadores y KPIs -->
                    <tr>
                        <td>Indicadores de Clientes</td>
                        <td>Consulta los indicadores clave de desempeño para clientes.</td>
                        <td>
                            <a href="<?= base_url('listClientKpis') ?>" target="_blank" data-bs-toggle="tooltip" title="Abrir Indicadores de Clientes">
                                <button type="button" class="btn btn-outline-secondary">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Tipos de Indicadores y Significados</td>
                        <td>Accede a la lista de tipos de indicadores y sus significados.</td>
                        <td>
                            <a href="<?= base_url('listKpiTypes') ?>" target="_blank" data-bs-toggle="tooltip" title="Abrir Tipos de Indicadores y Significados">
                                <button type="button" class="btn btn-outline-secondary">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Definiciones de Indicadores</td>
                        <td>Consulta las definiciones y detalles de los indicadores utilizados.</td>
                        <td>
                            <a href="<?= base_url('listKpiDefinitions') ?>" target="_blank" data-bs-toggle="tooltip" title="Abrir Definiciones de Indicadores">
                                <button type="button" class="btn btn-outline-secondary">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Nombres de Indicadores</td>
                        <td>Consulta la lista de nombres de indicadores configurados.</td>
                        <td>
                            <a href="<?= base_url('listKpis') ?>" target="_blank" data-bs-toggle="tooltip" title="Abrir Nombres de Indicadores">
                                <button type="button" class="btn btn-outline-secondary">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Responsables de Indicadores</td>
                        <td>Consulta la lista de responsables de cada indicador.</td>
                        <td>
                            <a href="<?= base_url('listDataOwners') ?>" target="_blank" data-bs-toggle="tooltip" title="Abrir Responsables de Indicadores">
                                <button type="button" class="btn btn-outline-secondary">Abrir</button>
                            </a>
                        </td>
                    </tr>
                  
                  
                    <tr>
                        <td>Objetivos de Indicadores</td>
                        <td>Consulta los objetivos de los indicadores establecidos.</td>
                        <td>
                            <a href="<?= base_url('listObjectives') ?>" target="_blank" data-bs-toggle="tooltip" title="Abrir Objetivos de Indicadores">
                                <button type="button" class="btn btn-outline-secondary">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Políticas de SST</td>
                        <td>Accede a las políticas de Seguridad y Salud en el Trabajo.</td>
                        <td>
                            <a href="<?= base_url('listKpiPolicies') ?>" target="_blank" data-bs-toggle="tooltip" title="Abrir Políticas de SST">
                                <button type="button" class="btn btn-outline-secondary">Abrir</button>
                            </a>
                        </td>
                    </tr>

                    <!-- Otros -->
                    <tr>
                        <td>Vigías</td>
                        <td>Consulta la lista de vigías asociados a la empresa.</td>
                        <td>
                            <a href="<?= base_url('listVigias') ?>" target="_blank" data-bs-toggle="tooltip" title="Abrir Vigías">
                                <button type="button" class="btn btn-outline-secondary">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Actividades del Plan Anual</td>
                        <td>Accede a las actividades del plan anual para la empresa.</td>
                        <td>
                            <a href="<?= base_url('listPlanDeTrabajoAnual') ?>" target="_blank" data-bs-toggle="tooltip" title="Abrir Actividades del Plan Anual">
                                <button type="button" class="btn btn-outline-secondary">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Pendientes</td>
                        <td>Consulta la lista de tareas pendientes dentro de la plataforma.</td>
                        <td>
                            <a href="<?= base_url('listPendientes') ?>" target="_blank" data-bs-toggle="tooltip" title="Abrir Pendientes">
                                <button type="button" class="btn btn-outline-secondary">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Matrices Cycloid</td>
                        <td>Consulta las matrices de datos de Cycloid.</td>
                        <td>
                            <a href="<?= base_url('listMatricesCycloid') ?>" target="_blank" data-bs-toggle="tooltip" title="Abrir Matrices Cycloid">
                                <button type="button" class="btn btn-outline-secondary">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Evaluacion de Estándares Mínimos</td>
                        <td>Accede a la lista de evaluaciones realizadas y sus resultados.</td>
                        <td>
                            <a href="<?= base_url('listEvaluaciones') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Evaluaciones">
                                <button type="button" class="btn btn-outline-secondary btn-sm">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Lista de Detalles de Reporte</td>
                        <td>Sub Clasificación del gestor documental, es la rama derivada</td>
                        <td>
                            <a href="<?= base_url('detailreportlist') ?>" target="_blank" data-bs-toggle="tooltip" title="Abrir Detalles de Reporte">
                                <button type="button" class="btn btn-outline-secondary">Abrir</button>
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="logout-button">
            <a href="<?= base_url('/logout') ?>" target="_blank">
                <button type="button" class="btn btn-danger">Cerrar Sesión</button>
            </a>
        </div>
    </div>

    <footer class="footer mt-auto py-3 bg-white border-top">
        <div class="container text-center">
            <!-- Company and Rights -->
            <p class="fw-bold mb-0">Cycloid Talent SAS</p>
            <p class="mb-0">Todos los derechos reservados © 2024</p>
            <p class="mb-0">NIT: 901.653.912</p>

            <!-- Website Link -->
            <p class="mb-0">
                Sitio oficial: <a href="https://cycloidtalent.com/" target="_blank" class="text-primary text-decoration-none">https://cycloidtalent.com/</a>
            </p>

            <!-- Social Media Links -->
            <p class="mt-3 mb-0"><strong>Nuestras Redes Sociales:</strong></p>
            <div class="d-flex justify-content-center gap-3">
                <a href="https://www.facebook.com/CycloidTalent" target="_blank" class="text-secondary text-decoration-none">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" alt="Facebook" style="height: 24px; width: 24px;">
                </a>
                <a href="https://co.linkedin.com/company/cycloid-talent" target="_blank" class="text-secondary text-decoration-none">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733561.png" alt="LinkedIn" style="height: 24px; width: 24px;">
                </a>
                <a href="https://www.instagram.com/cycloid_talent?igsh=Nmo4d2QwZDg5dHh0" target="_blank" class="text-secondary text-decoration-none">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733558.png" alt="Instagram" style="height: 24px; width: 24px;">
                </a>
                <a href="https://www.tiktok.com/@cycloid_talent?_t=8qBSOu0o1ZN&_r=1" target="_blank" class="text-secondary text-decoration-none">
                    <img src="https://cdn-icons-png.flaticon.com/512/3046/3046126.png" alt="TikTok" style="height: 24px; width: 24px;">
                </a>
            </div>
        </div>
    </footer>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <!-- DataTables Buttons JS -->
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.colVis.min.js"></script>
    <!-- DataTables Spanish Translation -->
    <script src="https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"></script>
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
            const table = $('#consultorTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
                },
                "pageLength": 50, // Mostrar 50 filas por defecto
                "order": [
                    [0, "asc"]
                ], // Ordenar por la primera columna (Detalle)
                "columnDefs": [{
                    "targets": 2, // Índice de la columna Acción
                    "orderable": false, // Deshabilitar ordenamiento
                    "searchable": false // Deshabilitar búsqueda
                }],
                "stateSave": true, // Habilitar guardado de estado
                "stateSaveCallback": function (settings, data) {
                    // Guardar el estado en localStorage con una clave única
                    localStorage.setItem('DataTables_consultorTable', JSON.stringify(data));
                },
                "stateLoadCallback": function (settings) {
                    // Cargar el estado desde localStorage
                    return JSON.parse(localStorage.getItem('DataTables_consultorTable'));
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
                        title: 'Lista_de_Reportes',
                        filename: 'Lista_de_Reportes',
                        titleAttr: 'Exportar a Excel',
                        customize: function (xlsx) {
                            var sheet = xlsx.xl.worksheets['sheet1.xml'];

                            // Agregar estilo al título (opcional)
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
                        if (columnIdx === 2) { // Índice 2 corresponde a 'Acción'
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

            // Re-inicializar tooltips después de cada dibujo de la tabla
            table.on('draw.dt', function () {
                initializeTooltips();
            });

            // Botón para borrar el estado
            $('#clearState').on('click', function () {
                // Borrar estado guardado en localStorage
                localStorage.removeItem('DataTables_consultorTable');
                table.state.clear(); // Limpiar estado en DataTables
                location.reload(); // Recargar la página
            });

            // Botón para exportar a Excel
            $('#exportExcel').on('click', function () {
                table.button('.buttons-excel').trigger();
            });

            // Inicializar tooltips al cargar la página
            initializeTooltips();
        });
    </script>

</body>

</html>
