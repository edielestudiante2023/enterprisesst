<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administración</title>
    <!-- Favicon -->
    <link rel="icon" href="<?= base_url('path/to/favicon.ico') ?>" type="image/x-icon">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" defer>
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet" defer>
    <!-- DataTables Buttons CSS -->
    <link href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css" rel="stylesheet" defer>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" defer>
    <style>
        body {
            background-color: #ffffff;
            color: #1c2437;
            font-family: Arial, sans-serif;
        }

        a:visited {
            color: #bd9751;
            /* Color del enlace visitado */
        }

        .navbar {
            background-color: #ffffff;
            border-bottom: 1px solid #e9ecef;
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

        .welcome-banner {
            background-color: #bd9751;
            border-left: 5px solid #1c2437;
            color: #ffffff;
        }

        .welcome-banner h3 {
            color: #ffffff;
        }

        .table th {
            background-color: #bd9751;
            color: #ffffff;
        }

        .table td a {
            color: #1c2437;
            text-decoration: none;
        }

        .table td a:hover {
            color: #bd9751;
            text-decoration: underline;
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
            color: #1c2437;
        }

        /* Botones personalizados */
        .btn-outline-secondary {
            border-color: #bd9751;
            color: #bd9751;
        }

        .btn-outline-secondary:hover {
            background-color: #bd9751;
            color: #ffffff;
        }

        .btn-primary-custom {
            background-color: #1c2437;
            border-color: #1c2437;
            color: #ffffff;
        }

        .btn-primary-custom:hover {
            background-color: #ffffff;
            color: #1c2437;
            border-color: #1c2437;
        }

        .btn-danger-custom {
            background-color: #bd9751;
            border-color: #bd9751;
            color: #ffffff;
        }

        .btn-danger-custom:hover {
            background-color: #ffffff;
            color: #bd9751;
            border-color: #bd9751;
        }

        .footer {
            background-color: #f8f9fa;
            color: #1c2437;
        }

        .footer a {
            color: #bd9751;
            text-decoration: none;
        }

        .footer a:hover {
            color: #1c2437;
            text-decoration: underline;
        }

        /* Estilo para el espacio debajo del navbar */
        .navbar-spacing {
            height: 160px;
        }

        /* Estilos Responsivos */
        @media (max-width: 768px) {
            .header-logos {
                flex-direction: column;
                align-items: center;
            }

            .header-logos img {
                margin-bottom: 10px;
            }
        }
    </style>
</head>

<body>
    <header>
        <nav class="navbar navbar-expand-lg fixed-top">
            <div class="container-fluid">
                <div class="header-logos d-flex justify-content-between align-items-center w-100">
                    <!-- Logo izquierdo -->
                    <div>
                        <a href="https://dashboard.cycloidtalent.com/login" target="_blank" rel="noopener noreferrer">
                            <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo">
                        </a>
                    </div>
                    <!-- Logo centro -->
                    <div>
                        <a href="https://cycloidtalent.com/index.php/consultoria-sst" target="_blank" rel="noopener noreferrer">
                            <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo">
                        </a>
                    </div>
                    <!-- Logo derecho -->
                    <div>
                        <a href="https://cycloidtalent.com/" target="_blank" rel="noopener noreferrer">
                            <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo">
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Espaciador para evitar que el contenido se oculte bajo el navbar fijo -->
    <div class="navbar-spacing"></div>

    <main class="container-fluid content">
        <div class="welcome-banner p-4 mb-4 rounded">
            <h3 class="mb-3">¡Bienvenido al Dashboard de Administración de Cycloid Talent!</h3>
            <p class="mt-3">Explora las diferentes secciones y aprovecha las herramientas disponibles para optimizar tu desempeño.</p>
        </div>

        <!-- Tabla con DataTables -->
        <div class="table-responsive">
            <!-- Botones para Exportar y Visibilidad de Columnas -->
            <div class="mb-3 d-flex justify-content-between">
                <div>
                    <button id="clearState" class="btn btn-danger-custom btn-sm" aria-label="Restablecer Filtros">Restablecer Filtros</button>
                    <button id="exportExcel" class="btn btn-primary-custom btn-sm ms-2" aria-label="Exportar a Excel">Exportar a Excel</button>
                </div>
                <div>
                    <!-- Botón ColVis (Opcional si no se usa el botón integrado) -->
                </div>
            </div>

            <table id="consultorTable" class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th>Detalle</th>
                        <th>Descripción/Funcionalidad</th>
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
                            <a href="<?= base_url('listCapacitaciones') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Capacitaciones">
                                <button type="button" class="btn btn-outline-secondary btn-sm">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Cronogramas de Capacitación</td>
                        <td>Accede a los cronogramas detallados de capacitaciones programadas.</td>
                        <td>
                            <a href="<?= base_url('listcronogCapacitacion') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Cronogramas de Capacitación">
                                <button type="button" class="btn btn-outline-secondary btn-sm">Abrir</button>
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

                    <!-- Gestión de Clientes y Consultores -->
                    <tr>
                        <td>Clientes</td>
                        <td>Consulta los detalles de los clientes registrados.</td>
                        <td>
                            <a href="<?= base_url('/listClients') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Clientes">
                                <button type="button" class="btn btn-outline-secondary btn-sm">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Consultores</td>
                        <td>Consulta la información de los consultores activos.</td>
                        <td>
                            <a href="<?= base_url('/index.php/listConsultants') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Consultores">
                                <button type="button" class="btn btn-outline-secondary btn-sm">Abrir</button>
                            </a>
                        </td>
                    </tr>

                    <!-- Documentación -->
                    <tr>
                        <td>Cargue de PDF´S a clientes</td>
                        <td>Modulo de cargue de soportes de gestión</td>
                        <td>
                            <a href="<?= base_url('/reportList') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Cargue de PDF's">
                                <button type="button" class="btn btn-outline-secondary btn-sm">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Documentos y Matrices</td>
                        <td>Consulta los tipos de documentos y matrices disponibles.</td>
                        <td>
                            <a href="<?= base_url('listReportTypes') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Documentos y Matrices">
                                <button type="button" class="btn btn-outline-secondary btn-sm">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Matrices</td>
                        <td>Matrices Interactivas de Gestión</td>
                        <td>
                            <a href="<?= base_url('matrices/list') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Matrices">
                                <button type="button" class="btn btn-outline-secondary btn-sm">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Looker Studio</td>
                        <td>Tableros de Indicadores del cliente</td>
                        <td>
                            <a href="<?= base_url('lookerstudio/list') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Looker Studio">
                                <button type="button" class="btn btn-outline-secondary btn-sm">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Versiones</td>
                        <td>Accede a la lista de versiones de los documentos generados.</td>
                        <td>
                            <a href="<?= base_url('listVersions') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Versiones">
                                <button type="button" class="btn btn-outline-secondary btn-sm">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Políticas</td>
                        <td>Consulta las políticas asociadas a la empresa.</td>
                        <td>
                            <a href="<?= base_url('/listPolicies') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Políticas">
                                <button type="button" class="btn btn-outline-secondary btn-sm">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Tipos de Documentos</td>
                        <td>Consulta los diferentes tipos de documentos configurados.</td>
                        <td>
                            <a href="<?= base_url('/listPolicyTypes') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Tipos de Documentos">
                                <button type="button" class="btn btn-outline-secondary btn-sm">Abrir</button>
                            </a>
                        </td>
                    </tr>

                    <!-- Indicadores y KPIs -->
                    <tr>
                        <td>Indicadores de Clientes</td>
                        <td>Consulta los indicadores clave de desempeño para clientes.</td>
                        <td>
                            <a href="<?= base_url('listClientKpis') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Indicadores de Clientes">
                                <button type="button" class="btn btn-outline-secondary btn-sm">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Tipos de Indicadores y Significados</td>
                        <td>Accede a la lista de tipos de indicadores y sus significados.</td>
                        <td>
                            <a href="<?= base_url('listKpiTypes') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Tipos de Indicadores y Significados">
                                <button type="button" class="btn btn-outline-secondary btn-sm">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Definiciones de Indicadores</td>
                        <td>Consulta las definiciones y detalles de los indicadores utilizados.</td>
                        <td>
                            <a href="<?= base_url('listKpiDefinitions') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Definiciones de Indicadores">
                                <button type="button" class="btn btn-outline-secondary btn-sm">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Nombres de Indicadores</td>
                        <td>Consulta la lista de nombres de indicadores configurados.</td>
                        <td>
                            <a href="<?= base_url('listKpis') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Nombres de Indicadores">
                                <button type="button" class="btn btn-outline-secondary btn-sm">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Responsables de Indicadores</td>
                        <td>Consulta la lista de responsables de cada indicador.</td>
                        <td>
                            <a href="<?= base_url('listDataOwners') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Responsables de Indicadores">
                                <button type="button" class="btn btn-outline-secondary btn-sm">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Numeradores de Indicadores</td>
                        <td>Consulta los numeradores utilizados en los indicadores.</td>
                        <td>
                            <a href="<?= base_url('listNumeratorVariables') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Numeradores de Indicadores">
                                <button type="button" class="btn btn-outline-secondary btn-sm">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Denominadores de Indicadores</td>
                        <td>Consulta los denominadores utilizados en los indicadores.</td>
                        <td>
                            <a href="<?= base_url('listDenominatorVariables') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Denominadores de Indicadores">
                                <button type="button" class="btn btn-outline-secondary btn-sm">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Objetivos de Indicadores</td>
                        <td>Consulta los objetivos de los indicadores establecidos.</td>
                        <td>
                            <a href="<?= base_url('listObjectives') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Objetivos de Indicadores">
                                <button type="button" class="btn btn-outline-secondary btn-sm">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Políticas de SST</td>
                        <td>Accede a las políticas de Seguridad y Salud en el Trabajo.</td>
                        <td>
                            <a href="<?= base_url('listKpiPolicies') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Políticas de SST">
                                <button type="button" class="btn btn-outline-secondary btn-sm">Abrir</button>
                            </a>
                        </td>
                    </tr>

                    <!-- Otros -->
                    <tr>
                        <td>Vigías</td>
                        <td>Consulta la lista de vigías asociados a la empresa.</td>
                        <td>
                            <a href="<?= base_url('listVigias') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Vigías">
                                <button type="button" class="btn btn-outline-secondary btn-sm">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Actividades del Plan Anual</td>
                        <td>Accede a las actividades del plan anual para la empresa.</td>
                        <td>
                            <a href="<?= base_url('listPlanDeTrabajoAnual') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Actividades del Plan Anual">
                                <button type="button" class="btn btn-outline-secondary btn-sm">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Pendientes</td>
                        <td>Consulta la lista de tareas pendientes dentro de la plataforma.</td>
                        <td>
                            <a href="<?= base_url('listPendientes') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Pendientes">
                                <button type="button" class="btn btn-outline-secondary btn-sm">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Matrices Cycloid</td>
                        <td>Consulta las matrices de datos de Cycloid.</td>
                        <td>
                            <a href="<?= base_url('listMatricesCycloid') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Matrices Cycloid">
                                <button type="button" class="btn btn-outline-secondary btn-sm">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Períodos de Medición</td>
                        <td>Consulta los períodos de medición configurados para los indicadores.</td>
                        <td>
                            <a href="<?= base_url('listMeasurementPeriods') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Períodos de Medición">
                                <button type="button" class="btn btn-outline-secondary btn-sm">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Lista de Detalles de Reporte</td>
                        <td>Sub Clasificación del gestor documental, es la rama derivada</td>
                        <td>
                            <a href="<?= base_url('detailreportlist') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Detalles de Reporte">
                                <button type="button" class="btn btn-outline-secondary btn-sm">Abrir</button>
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="logout-button">
            <a href="<?= base_url('/logout') ?>" rel="noopener noreferrer">
                <button type="button" class="btn btn-danger-custom" aria-label="Cerrar Sesión">Cerrar Sesión</button>
            </a>
        </div>
    </main>

    <footer class="footer mt-auto py-3 border-top">
        <div class="container text-center">
            <!-- Company and Rights -->
            <p class="fw-bold mb-0">Cycloid Talent SAS</p>
            <p class="mb-0">Todos los derechos reservados © <span id="currentYear"></span></p>
            <p class="mb-0">NIT: 901.653.912</p>

            <!-- Website Link -->
            <p class="mb-0">
                Sitio oficial: <a href="https://cycloidtalent.com/" target="_blank" rel="noopener noreferrer">https://cycloidtalent.com/</a>
            </p>

            <!-- Social Media Links -->
            <p class="mt-3 mb-0"><strong>Nuestras Redes Sociales:</strong></p>
            <div class="d-flex justify-content-center gap-3">
                <a href="https://www.facebook.com/CycloidTalent" target="_blank" rel="noopener noreferrer" class="text-secondary text-decoration-none" aria-label="Facebook">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" alt="Facebook" style="height: 24px; width: 24px;">
                </a>
                <a href="https://co.linkedin.com/company/cycloid-talent" target="_blank" rel="noopener noreferrer" class="text-secondary text-decoration-none" aria-label="LinkedIn">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733561.png" alt="LinkedIn" style="height: 24px; width: 24px;">
                </a>
                <a href="https://www.instagram.com/cycloid_talent?igsh=Nmo4d2QwZDg5dHh0" target="_blank" rel="noopener noreferrer" class="text-secondary text-decoration-none" aria-label="Instagram">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733558.png" alt="Instagram" style="height: 24px; width: 24px;">
                </a>
                <a href="https://www.tiktok.com/@cycloid_talent?_t=8qBSOu0o1ZN&_r=1" target="_blank" rel="noopener noreferrer" class="text-secondary text-decoration-none" aria-label="TikTok">
                    <img src="https://cdn-icons-png.flaticon.com/512/3046/3046126.png" alt="TikTok" style="height: 24px; width: 24px;">
                </a>
            </div>
        </div>
    </footer>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" defer></script>
    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js" defer></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js" defer></script>
    <!-- DataTables Buttons JS -->
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js" defer></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js" defer></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js" defer></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.colVis.min.js" defer></script>
    <!-- DataTables Spanish Translation -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Actualizar dinámicamente el año en el footer
            document.getElementById('currentYear').textContent = new Date().getFullYear();
        });
    </script>
    <script>
        $(document).ready(function() {
            // Función para inicializar los tooltips
            function initializeTooltips() {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
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
                "stateSaveCallback": function(settings, data) {
                    // Guardar el estado en localStorage con una clave única
                    localStorage.setItem('DataTables_consultorTable', JSON.stringify(data));
                },
                "stateLoadCallback": function(settings) {
                    // Cargar el estado desde localStorage
                    return JSON.parse(localStorage.getItem('DataTables_consultorTable'));
                },
                "dom": 'Bfrtip', // Posición de los botones
                "buttons": [{
                        extend: 'excelHtml5',
                        text: '<i class="bi bi-file-earmark-excel"></i> Exportar a Excel',
                        className: 'btn btn-primary-custom btn-sm',
                        exportOptions: {
                            columns: ':not(:last-child)' // Excluir la columna de Acciones
                        },
                        title: 'Lista_de_Reportes',
                        filename: 'Lista_de_Reportes',
                        titleAttr: 'Exportar a Excel',
                        customize: function(xlsx) {
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
                "initComplete": function() {
                    var api = this.api();

                    // Configurar los filtros en <tfoot>
                    api.columns().every(function() {
                        var column = this;
                        var columnIdx = column.index();

                        // Excluir la columna de Acciones de los filtros
                        if (columnIdx === 2) { // Índice 2 corresponde a 'Acción'
                            $(column.footer()).empty();
                            return;
                        }

                        var select = $('<select class="form-select form-select-sm"><option value="">Todos</option></select>')
                            .appendTo($(column.footer()).empty())
                            .on('change', function() {
                                var val = $.fn.dataTable.util.escapeRegex($(this).val());
                                column.search(val ? '^' + val + '$' : '', true, false).draw();
                            });

                        // Precargar los datos únicos de la columna en el filtro
                        column.data().unique().sort().each(function(d, j) {
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
            table.on('draw.dt', function() {
                initializeTooltips();
            });

            // Botón para borrar el estado
            $('#clearState').on('click', function() {
                // Borrar estado guardado en localStorage
                localStorage.removeItem('DataTables_consultorTable');
                table.state.clear(); // Limpiar estado en DataTables
                table.search('').columns().search('').draw(); // Limpiar filtros y búsquedas
                initializeTooltips();
            });

            // Botón para exportar a Excel
            $('#exportExcel').on('click', function() {
                table.button('.buttons-excel').trigger();
            });

            // Inicializar tooltips al cargar la página
            initializeTooltips();
        });
    </script>

</body>

</html>