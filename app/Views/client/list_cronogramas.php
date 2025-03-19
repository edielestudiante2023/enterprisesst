<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Cronogramas de Capacitación</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- DataTables Buttons CSS -->
    <link href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <style>
        /* Configuración para ajustar ancho de columnas a 50 caracteres */
        .styled-table thead th,
        .styled-table tbody td,
        .styled-table tfoot th {
            max-width: 50ch;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Estilos personalizados adicionales */
        body {
            background-color: #f8f9fa;
            color: #333;
        }

        .container {
            margin-top: 30px;
            max-width: 100%;
        }

        h2 {
            color: #333;
            font-weight: bold;
            text-align: center;
            margin-bottom: 30px;
        }

        .table-container {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .table thead th {
            background-color: #007bff;
            color: #fff;
            text-align: center;
        }

        .table tbody td {
            text-align: center;
            font-size: 15px;
            vertical-align: middle;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            font-weight: bold;
            border-radius: 5px;
        }

        .empty-message {
            color: #333;
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            padding: 20px;
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm">
        <div class="container-fluid">
            <!-- Logos -->
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

            <!-- Botones Dashboard y Añadir Registro -->
            <div class="ms-auto d-flex">
                <div class="text-center me-3">
                    <h6 class="mb-1" style="font-size: 16px;">Ir a Dashboard</h6>
                    <a href="<?= base_url('/dashboardconsultant') ?>" class="btn btn-primary btn-sm">Ir a DashBoard</a>
                </div>
                <div class="text-center">
                    <h6 class="mb-1" style="font-size: 16px;">Añadir Registro</h6>
                    <a href="<?= base_url('/addCronograma') ?>" class="btn btn-success btn-sm" target="_blank">Añadir Registro</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Espaciado para el navbar fijo -->
    <div style="height: 120px;"></div>

    <div class="container">
        <h2>Listado de Cronogramas de Capacitación</h2>

        <?php if (session()->getFlashdata('msg')) : ?>
            <div class="alert alert-success text-center"><?= session()->getFlashdata('msg') ?></div>
        <?php endif; ?>

        <div class="table-container">
            <!-- Botones de DataTables -->
            <div class="d-flex justify-content-between mb-3">
                <button id="clearState" class="btn btn-danger btn-sm">Restablecer Filtros</button>
                <div id="buttonsContainer"></div>
            </div>

            <table id="cronogramasTable" class="styled-table table table-hover table-bordered nowrap" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <!-- Se muestran las columnas definidas -->
                        <th>Capacitación</th>
                        <th>Fecha Programada</th>
                        <th>Fecha de Realización</th>
                        <th>Estado</th>
                        <th>Perfil de Asistentes</th>
                        <th>Nombre del Capacitador</th>
                        <th>Horas de Duración</th>
                        <th>Indicador de Realización</th>
                        <th>Número de Asistentes</th>
                        <th>Número Total de Personas Programadas</th>
                        <th>Porcentaje de Cobertura</th>
                        <th>Número de Personas Evaluadas</th>
                        <th>Promedio de Calificaciones</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tfoot class="table-light">
                    <tr class="filters">
                        <!-- Filtros para cada columna -->
                        <th>
                            <select class="form-select form-select-sm filter-select" aria-label="Filtro Capacitación">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select" aria-label="Filtro Fecha Programada">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select" aria-label="Filtro Fecha de Realización">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select" aria-label="Filtro Estado">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select" aria-label="Filtro Perfil de Asistentes">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select" aria-label="Filtro Nombre del Capacitador">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select" aria-label="Filtro Horas de Duración">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select" aria-label="Filtro Indicador de Realización">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select" aria-label="Filtro Número de Asistentes">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select" aria-label="Filtro Número Total de Personas Programadas">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select" aria-label="Filtro Porcentaje de Cobertura">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select" aria-label="Filtro Número de Personas Evaluadas">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select" aria-label="Filtro Promedio de Calificaciones">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select" aria-label="Filtro Observaciones">
                                <option value="">Todos</option>
                            </select>
                        </th>
                    </tr>
                </tfoot>

                <tbody>
                    <?php if (!empty($cronogramas) && is_array($cronogramas)): ?>
                        <?php foreach ($cronogramas as $cronograma): ?>
                            <tr>
                                <td data-bs-toggle="tooltip" title="<?= esc($cronograma['nombre_capacitacion']); ?>">
                                    <?= esc($cronograma['nombre_capacitacion']); ?>
                                </td>
                                <td data-bs-toggle="tooltip" title="<?= esc($cronograma['fecha_programada']); ?>">
                                    <?= esc($cronograma['fecha_programada']); ?>
                                </td>
                                <td data-bs-toggle="tooltip" title="<?= esc($cronograma['fecha_de_realizacion']); ?>">
                                    <?= esc($cronograma['fecha_de_realizacion']); ?>
                                </td>
                                <td data-bs-toggle="tooltip" title="<?= esc($cronograma['estado']); ?>">
                                    <?= esc($cronograma['estado']); ?>
                                </td>
                                <td data-bs-toggle="tooltip" title="<?= esc($cronograma['perfil_de_asistentes']); ?>">
                                    <?= esc($cronograma['perfil_de_asistentes']); ?>
                                </td>
                                <td data-bs-toggle="tooltip" title="<?= esc($cronograma['nombre_del_capacitador']); ?>">
                                    <?= esc($cronograma['nombre_del_capacitador']); ?>
                                </td>
                                <td data-bs-toggle="tooltip" title="<?= esc($cronograma['horas_de_duracion_de_la_capacitacion']); ?>">
                                    <?= esc($cronograma['horas_de_duracion_de_la_capacitacion']); ?>
                                </td>
                                <td data-bs-toggle="tooltip" title="<?= esc($cronograma['indicador_de_realizacion_de_la_capacitacion']); ?>">
                                    <?= esc($cronograma['indicador_de_realizacion_de_la_capacitacion']); ?>
                                </td>
                                <td data-bs-toggle="tooltip" title="<?= esc($cronograma['numero_de_asistentes_a_capacitacion']); ?>">
                                    <?= esc($cronograma['numero_de_asistentes_a_capacitacion']); ?>
                                </td>
                                <td data-bs-toggle="tooltip" title="<?= esc($cronograma['numero_total_de_personas_programadas']); ?>">
                                    <?= esc($cronograma['numero_total_de_personas_programadas']); ?>
                                </td>
                                <td data-bs-toggle="tooltip" title="<?= esc($cronograma['porcentaje_cobertura']); ?>">
                                    <?= esc($cronograma['porcentaje_cobertura']); ?>%
                                </td>
                                <td data-bs-toggle="tooltip" title="<?= esc($cronograma['numero_de_personas_evaluadas']); ?>">
                                    <?= esc($cronograma['numero_de_personas_evaluadas']); ?>
                                </td>
                                <td data-bs-toggle="tooltip" title="<?= esc($cronograma['promedio_de_calificaciones']); ?>">
                                    <?= esc($cronograma['promedio_de_calificaciones']); ?>
                                </td>
                                <td data-bs-toggle="tooltip" title="<?= esc($cronograma['observaciones']); ?>">
                                    <?= esc($cronograma['observaciones']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="14" class="empty-message">No hay cronogramas de capacitación registrados.</td>
                        </tr>
                    <?php endif; ?>
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
            <p class="mb-3">
                Sitio oficial: <a href="https://cycloidtalent.com/" target="_blank">https://cycloidtalent.com/</a>
            </p>
            <p><strong>Nuestras Redes Sociales:</strong></p>
            <div class="social-icons d-flex justify-content-center gap-3">
                <a href="https://www.facebook.com/CycloidTalent" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" alt="Facebook" style="height: 24px; width: 24px;">
                </a>
                <a href="https://co.linkedin.com/company/cycloid-talent" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733561.png" alt="LinkedIn" style="height: 24px; width: 24px;">
                </a>
                <a href="https://www.instagram.com/cycloid_talent?igsh=Nmo4d2QwZDg5dHh0" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733558.png" alt="Instagram" style="height: 24px; width: 24px;">
                </a>
                <a href="https://www.tiktok.com/@cycloid_talent?_t=8qBSOu0o1ZN&_r=1" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/3046/3046126.png" alt="TikTok" style="height: 24px; width: 24px;">
                </a>
            </div>
        </div>
    </footer>

    <!-- Scripts al final del body para mejor rendimiento -->
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap Bundle (Incluye Popper.js) -->
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

    <script>
        $(document).ready(function () {
            // Inicializar DataTables con Buttons, filtros en el <tfoot> y configuración de columnas
            var table = $('#cronogramasTable').DataTable({
                stateSave: true,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
                },
                paging: true,
                searching: true,
                lengthChange: true,
                pageLength: 5,
                responsive: true,
                autoWidth: false,
                dom: 'Bfltip',
                buttons: [
                    {
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
                initComplete: function () {
                    var api = this.api();
                    // Para cada columna, crear un filtro en el <tfoot>
                    api.columns().every(function () {
                        var column = this;
                        var headerIndex = column.index();
                        var filterElement = $('tfoot tr.filters th').eq(headerIndex).find('.filter-select');
                        if (filterElement.length && !filterElement.prop('disabled')) {
                            column.data().unique().sort().each(function (d) {
                                if (d) {
                                    if (filterElement.find('option[value="' + d + '"]').length === 0) {
                                        filterElement.append('<option value="' + d + '">' + d + '</option>');
                                    }
                                }
                            });
                            var search = column.search();
                            if (search) {
                                var cleanedSearch = search.replace(/[\^\$(){}.+*?\\|]/g, '');
                                filterElement.val(cleanedSearch);
                            }
                        }
                    });
                }
            });

            // Colocar los botones de DataTables en el contenedor específico
            table.buttons().container().appendTo('#buttonsContainer');

            // Evento para los filtros del <tfoot>
            $('tfoot .filter-select').on('change', function () {
                var columnIndex = $(this).closest('th').index();
                var value = $(this).val();
                table.column(columnIndex).search(value ? '^' + value + '$' : '', true, false).draw();
            });

            // Inicializar tooltips de Bootstrap 5
            function initializeTooltips() {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }

            initializeTooltips();
            table.on('draw.dt', function () {
                initializeTooltips();
            });

            // Botón para restablecer el estado y filtros
            $('#clearState').on('click', function () {
                var storageKey = 'DataTables_' + table.table().node().id + '_' + window.location.pathname;
                localStorage.removeItem(storageKey);
                table.state.clear();
                $('tfoot .filter-select').each(function () {
                    $(this).val('');
                });
                table.columns().search('').draw();
            });
        });
    </script>
</body>

</html>
