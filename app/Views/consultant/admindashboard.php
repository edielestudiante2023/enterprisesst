<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administración</title>
    <!-- Favicon -->
    <link rel="icon" href="<?= base_url('path/to/favicon.ico') ?>" type="image/x-icon">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa; /* Fondo claro */
            color: #333333; /* Texto oscuro para mejor legibilidad */
            font-family: Arial, sans-serif;
        }

        .navbar {
            background-color: #ffffff;
            border-bottom: 1px solid #dee2e6;
            padding: 15px 0; /* Ajuste de padding para un navbar más amplio */
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
            background-color: #e9ecef; /* Gris claro */
            border-left: 5px solid #0d6efd; /* Azul corporativo */
            color: #333333;
        }

        .welcome-banner h3 {
            color: #0d6efd; /* Azul corporativo */
        }

        .table th {
            background-color: #0d6efd; /* Azul corporativo */
            color: #ffffff;
        }

        .table td a {
            color: #0d6efd;
            text-decoration: none;
        }

        .table td a:hover {
            color: #0b5ed7;
            text-decoration: underline;
        }

        .logout-button {
            text-align: center;
            margin-top: 20px;
        }

        /* Botones personalizados */
        .btn-outline-secondary {
            border-color: #6c757d;
            color: #6c757d;
        }

        .btn-outline-secondary:hover {
            background-color: #6c757d;
            color: #ffffff;
        }

        .btn-primary-custom {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: #ffffff;
        }

        .btn-primary-custom:hover {
            background-color: #ffffff;
            color: #0d6efd;
            border-color: #0d6efd;
        }

        .btn-danger-custom {
            background-color: #dc3545; /* Rojo corporativo */
            border-color: #dc3545;
            color: #ffffff;
        }

        .btn-danger-custom:hover {
            background-color: #ffffff;
            color: #dc3545;
            border-color: #dc3545;
        }

        .footer {
            background-color: #ffffff;
            color: #333333;
        }

        .footer a {
            color: #0d6efd;
            text-decoration: none;
        }

        .footer a:hover {
            color: #0b5ed7;
            text-decoration: underline;
        }

        /* Estilo para el espacio debajo del navbar */
        .navbar-spacing {
            height: 100px; /* Ajuste según la altura real del navbar */
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

        /* Estilo para resaltar la fila */
        .highlighted {
            background-color: #cce5ff !important; /* Azul claro */
            transition: background-color 0.5s ease;
        }

        /* Estilo para la casilla de búsqueda personalizada */
        .custom-search {
            margin-bottom: 15px;
        }

        /* Estilo para los select de filtros en el pie de la tabla */
        tfoot th {
            padding: 8px 10px;
            background-color: #f8f9fa;
        }

        /* Ajuste para la longitud máxima de las celdas de descripción */
        .description-col {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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
            <h3 class="mb-3">¡Bienvenido al Dashboard de Administración en Empresas de Cycloid Talent!</h3>
            <p class="mt-3">Explora las diferentes secciones y aprovecha las herramientas disponibles para optimizar tu desempeño.</p>
        </div>

        <!-- Casilla de Búsqueda Personalizada -->
        <div class="custom-search">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" id="wordSearch" class="form-control" placeholder="Buscar por palabra...">
                <button class="btn btn-outline-secondary" id="clearWordSearch" type="button">Limpiar</button>
            </div>
        </div>

        <!-- Tabla con DataTables -->
        <div class="table-responsive">
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
                    <tr data-detail="Capacitaciones">
                        <td>Capacitaciones</td>
                        <td class="description-col">Accede a la lista de capacitaciones disponibles.</td>
                        <td>
                            <a href="<?= base_url('listCapacitaciones') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Capacitaciones">
                                <button type="button" class="btn btn-outline-secondary btn-sm abrir-btn">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr data-detail="Cronogramas de Capacitación">
                        <td>Cronogramas de Capacitación</td>
                        <td class="description-col">Accede a los cronogramas detallados de capacitaciones programadas.</td>
                        <td>
                            <a href="<?= base_url('listcronogCapacitacion') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Cronogramas de Capacitación">
                                <button type="button" class="btn btn-outline-secondary btn-sm abrir-btn">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr data-detail="Evaluacion de Estándares Mínimos">
                        <td>Evaluacion de Estándares Mínimos</td>
                        <td class="description-col">Accede a la lista de evaluaciones realizadas y sus resultados.</td>
                        <td>
                            <a href="<?= base_url('listEvaluaciones') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Evaluaciones">
                                <button type="button" class="btn btn-outline-secondary btn-sm abrir-btn">Abrir</button>
                            </a>
                        </td>
                    </tr>

                    <!-- Gestión de Clientes y Consultores -->
                    <tr data-detail="Clientes">
                        <td>Clientes</td>
                        <td class="description-col">Consulta los detalles de los clientes registrados.</td>
                        <td>
                            <a href="<?= base_url('/listClients') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Clientes">
                                <button type="button" class="btn btn-outline-secondary btn-sm abrir-btn">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr data-detail="Consultores">
                        <td>Consultores</td>
                        <td class="description-col">Consulta la información de los consultores activos.</td>
                        <td>
                            <a href="<?= base_url('/index.php/listConsultants') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Consultores">
                                <button type="button" class="btn btn-outline-secondary btn-sm abrir-btn">Abrir</button>
                            </a>
                        </td>
                    </tr>

                    <!-- Documentación -->
                    <tr data-detail="Cargue de PDF´S a clientes">
                        <td>Cargue de PDF´S a clientes</td>
                        <td class="description-col">Módulo de cargue de soportes de gestión.</td>
                        <td>
                            <a href="<?= base_url('/reportList') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Cargue de PDF's">
                                <button type="button" class="btn btn-outline-secondary btn-sm abrir-btn">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr data-detail="Documentos y Matrices">
                        <td>Documentos y Matrices</td>
                        <td class="description-col">Consulta los tipos de documentos y matrices disponibles.</td>
                        <td>
                            <a href="<?= base_url('listReportTypes') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Documentos y Matrices">
                                <button type="button" class="btn btn-outline-secondary btn-sm abrir-btn">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr data-detail="Matrices">
                        <td>Matrices</td>
                        <td class="description-col">Matrices Interactivas de Gestión.</td>
                        <td>
                            <a href="<?= base_url('matrices/list') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Matrices">
                                <button type="button" class="btn btn-outline-secondary btn-sm abrir-btn">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr data-detail="Looker Studio">
                        <td>Looker Studio</td>
                        <td class="description-col">Tableros de Indicadores del cliente.</td>
                        <td>
                            <a href="<?= base_url('lookerstudio/list') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Looker Studio">
                                <button type="button" class="btn btn-outline-secondary btn-sm abrir-btn">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr data-detail="Versiones">
                        <td>Versiones</td>
                        <td class="description-col">Accede a la lista de versiones de los documentos generados.</td>
                        <td>
                            <a href="<?= base_url('listVersions') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Versiones">
                                <button type="button" class="btn btn-outline-secondary btn-sm abrir-btn">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr data-detail="Políticas">
                        <td>Políticas</td>
                        <td class="description-col">Consulta las políticas asociadas a la empresa.</td>
                        <td>
                            <a href="<?= base_url('/listPolicies') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Políticas">
                                <button type="button" class="btn btn-outline-secondary btn-sm abrir-btn">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr data-detail="Tipos de Documentos">
                        <td>Tipos de Documentos</td>
                        <td class="description-col">Consulta los diferentes tipos de documentos configurados.</td>
                        <td>
                            <a href="<?= base_url('/listPolicyTypes') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Tipos de Documentos">
                                <button type="button" class="btn btn-outline-secondary btn-sm abrir-btn">Abrir</button>
                            </a>
                        </td>
                    </tr>

                    <!-- Indicadores y KPIs -->
                    <tr data-detail="Indicadores de Clientes">
                        <td>Indicadores de Clientes</td>
                        <td class="description-col">Consulta los indicadores clave de desempeño para clientes.</td>
                        <td>
                            <a href="<?= base_url('listClientKpis') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Indicadores de Clientes">
                                <button type="button" class="btn btn-outline-secondary btn-sm abrir-btn">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr data-detail="Tipos de Indicadores y Significados">
                        <td>Tipos de Indicadores y Significados</td>
                        <td class="description-col">Accede a la lista de tipos de indicadores y sus significados.</td>
                        <td>
                            <a href="<?= base_url('listKpiTypes') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Tipos de Indicadores y Significados">
                                <button type="button" class="btn btn-outline-secondary btn-sm abrir-btn">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr data-detail="Definiciones de Indicadores">
                        <td>Definiciones de Indicadores</td>
                        <td class="description-col">Consulta las definiciones y detalles de los indicadores utilizados.</td>
                        <td>
                            <a href="<?= base_url('listKpiDefinitions') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Definiciones de Indicadores">
                                <button type="button" class="btn btn-outline-secondary btn-sm abrir-btn">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr data-detail="Nombres de Indicadores">
                        <td>Nombres de Indicadores</td>
                        <td class="description-col">Consulta la lista de nombres de indicadores configurados.</td>
                        <td>
                            <a href="<?= base_url('listKpis') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Nombres de Indicadores">
                                <button type="button" class="btn btn-outline-secondary btn-sm abrir-btn">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr data-detail="Responsables de Indicadores">
                        <td>Responsables de Indicadores</td>
                        <td class="description-col">Consulta la lista de responsables de cada indicador.</td>
                        <td>
                            <a href="<?= base_url('listDataOwners') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Responsables de Indicadores">
                                <button type="button" class="btn btn-outline-secondary btn-sm abrir-btn">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr data-detail="Numeradores de Indicadores">
                        <td>Numeradores de Indicadores</td>
                        <td class="description-col">Consulta los numeradores utilizados en los indicadores.</td>
                        <td>
                            <a href="<?= base_url('listNumeratorVariables') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Numeradores de Indicadores">
                                <button type="button" class="btn btn-outline-secondary btn-sm abrir-btn">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr data-detail="Denominadores de Indicadores">
                        <td>Denominadores de Indicadores</td>
                        <td class="description-col">Consulta los denominadores utilizados en los indicadores.</td>
                        <td>
                            <a href="<?= base_url('listDenominatorVariables') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Denominadores de Indicadores">
                                <button type="button" class="btn btn-outline-secondary btn-sm abrir-btn">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr data-detail="Objetivos de Indicadores">
                        <td>Objetivos de Indicadores</td>
                        <td class="description-col">Consulta los objetivos de los indicadores establecidos.</td>
                        <td>
                            <a href="<?= base_url('listObjectives') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Objetivos de Indicadores">
                                <button type="button" class="btn btn-outline-secondary btn-sm abrir-btn">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr data-detail="Políticas de SST">
                        <td>Políticas de SST</td>
                        <td class="description-col">Accede a las políticas de Seguridad y Salud en el Trabajo.</td>
                        <td>
                            <a href="<?= base_url('listKpiPolicies') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Políticas de SST">
                                <button type="button" class="btn btn-outline-secondary btn-sm abrir-btn">Abrir</button>
                            </a>
                        </td>
                    </tr>

                    <!-- Otros -->
                    <tr data-detail="Vigías">
                        <td>Vigías</td>
                        <td class="description-col">Consulta la lista de vigías asociados a la empresa.</td>
                        <td>
                            <a href="<?= base_url('listVigias') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Vigías">
                                <button type="button" class="btn btn-outline-secondary btn-sm abrir-btn">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr data-detail="Actividades del Plan Anual">
                        <td>Actividades del Plan Anual</td>
                        <td class="description-col">Accede a las actividades del plan anual para la empresa.</td>
                        <td>
                            <a href="<?= base_url('listPlanDeTrabajoAnual') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Actividades del Plan Anual">
                                <button type="button" class="btn btn-outline-secondary btn-sm abrir-btn">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr data-detail="Pendientes">
                        <td>Pendientes</td>
                        <td class="description-col">Consulta la lista de tareas pendientes dentro de la plataforma.</td>
                        <td>
                            <a href="<?= base_url('listPendientes') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Pendientes">
                                <button type="button" class="btn btn-outline-secondary btn-sm abrir-btn">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr data-detail="Matrices Cycloid">
                        <td>Matrices Cycloid</td>
                        <td class="description-col">Consulta las matrices de datos de Cycloid.</td>
                        <td>
                            <a href="<?= base_url('listMatricesCycloid') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Matrices Cycloid">
                                <button type="button" class="btn btn-outline-secondary btn-sm abrir-btn">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr data-detail="Períodos de Medición">
                        <td>Períodos de Medición</td>
                        <td class="description-col">Consulta los períodos de medición configurados para los indicadores.</td>
                        <td>
                            <a href="<?= base_url('listMeasurementPeriods') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Períodos de Medición">
                                <button type="button" class="btn btn-outline-secondary btn-sm abrir-btn">Abrir</button>
                            </a>
                        </td>
                    </tr>
                    <tr data-detail="Lista de Detalles de Reporte">
                        <td>Lista de Detalles de Reporte</td>
                        <td class="description-col">Sub Clasificación del gestor documental, es la rama derivada.</td>
                        <td>
                            <a href="<?= base_url('detailreportlist') ?>" target="_blank" rel="noopener noreferrer" data-bs-toggle="tooltip" title="Abrir Detalles de Reporte">
                                <button type="button" class="btn btn-outline-secondary btn-sm abrir-btn">Abrir</button>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <!-- DataTables Buttons JS (Eliminado si ya no se usan) -->
    <!--
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.colVis.min.js"></script>
    -->

    <!-- DataTables Spanish Translation -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Actualizar dinámicamente el año en el footer
            document.getElementById('currentYear').textContent = new Date().getFullYear();
        });
    </script>
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

            // Función para resaltar una fila
            function highlightRow(detail) {
                var row = $('#consultorTable tbody tr').filter(function () {
                    return $(this).data('detail') === detail;
                });
                row.addClass('highlighted');

                // Guardar en localStorage con timestamp
                var highlights = JSON.parse(localStorage.getItem('highlightedRows')) || {};
                highlights[detail] = new Date().getTime();
                localStorage.setItem('highlightedRows', JSON.stringify(highlights));

                // Establecer timeout para quitar el resaltado después de 15 minutos
                setTimeout(function () {
                    row.removeClass('highlighted');
                    delete highlights[detail];
                    localStorage.setItem('highlightedRows', JSON.stringify(highlights));
                }, 15 * 60 * 1000); // 15 minutos en milisegundos
            }

            // Inicializar DataTable con configuraciones personalizadas
            const table = $('#consultorTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
                },
                "pageLength": 40, // Mostrar 40 filas por defecto
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
                "dom": 'lrtip', // Quitar los botones y solo dejar la tabla, el filtro y la paginación
                /*
                   'lrtip' significa:
                   l - length changing input control
                   r - processing display element
                   t - The table!
                   i - Table information summary
                   p - pagination control
                */
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

                    // Restaurar filas resaltadas desde localStorage
                    var highlights = JSON.parse(localStorage.getItem('highlightedRows')) || {};
                    var currentTime = new Date().getTime();
                    for (var detail in highlights) {
                        if (highlights.hasOwnProperty(detail)) {
                            var timestamp = highlights[detail];
                            // Verificar si el resaltado aún es válido (15 minutos)
                            if (currentTime - timestamp < 15 * 60 * 1000) {
                                highlightRow(detail);
                            } else {
                                // Si ha pasado más de 15 minutos, eliminar el resaltado
                                delete highlights[detail];
                                localStorage.setItem('highlightedRows', JSON.stringify(highlights));
                            }
                        }
                    }
                }
            });

            // Re-inicializar tooltips después de cada dibujo de la tabla
            table.on('draw.dt', function () {
                initializeTooltips();
            });

            // Manejar clic en botones "Abrir"
            $('#consultorTable tbody').on('click', 'button.abrir-btn', function (e) {
                var tr = $(this).closest('tr');
                var detail = tr.data('detail');

                // Resaltar la fila
                highlightRow(detail);

                // Opcional: Puedes manejar la lógica adicional del botón aquí
            });

            // Funcionalidad de Búsqueda Personalizada por Palabra
            $('#wordSearch').on('keyup change', function () {
                var searchTerm = $(this).val().trim();

                // Aplicar la búsqueda personalizada utilizando expresiones regulares para buscar palabras completas
                if (searchTerm) {
                    // Escapar caracteres especiales para las expresiones regulares
                    var escapedSearchTerm = searchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                    table.search('\\b' + escapedSearchTerm + '\\b', true, false).draw();
                } else {
                    table.search('').draw();
                }
            });

            // Botón para Limpiar la Búsqueda Personalizada
            $('#clearWordSearch').on('click', function () {
                $('#wordSearch').val('');
                table.search('').draw();
            });

            // Inicializar tooltips al cargar la página
            initializeTooltips();
        });
    </script>

</body>

</html>
