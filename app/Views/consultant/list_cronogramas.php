<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Cronogramas de Capacitación</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons CSS (Opcional) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <!-- DataTables Buttons CSS -->
    <link href="https://cdn.datatables.net/buttons/2.3.3/css/buttons.bootstrap5.min.css" rel="stylesheet">
    
    <style>
        body {
            font-size: 0.9rem;
            background-color: #f9f9f9;
        }

        table thead {
            background-color: #f8f9fa;
        }

        table tbody tr:nth-child(odd) {
            background-color: #f2f2f2;
        }

        table {
            font-size: 0.85rem;
        }

        /* Ajustes para el botón "Restablecer Filtros" */
        #clearState {
            margin-bottom: 15px;
        }

        /* Establecer altura fija para las filas de la tabla */
        /* Asegúrate de que el contenido de las celdas no exceda esta altura */
        table tbody tr td, table thead tr th, table tfoot tr th {
            height: 50px; /* Ajusta este valor según tus necesidades */
            vertical-align: middle;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Opcional: Ajustar el ancho de las columnas para mejorar la apariencia */
        /* table th, table td {
            max-width: 200px;
        } */
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center w-100">
                <!-- Logos -->
                <div class="d-flex">
                    <a href="https://dashboard.cycloidtalent.com/login" class="me-3">
                        <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo" style="height: 50px;">
                    </a>
                    <a href="https://cycloidtalent.com/index.php/consultoria-sst" class="me-3">
                        <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo" style="height: 50px;">
                    </a>
                    <a href="https://cycloidtalent.com/">
                        <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo" style="height: 50px;">
                    </a>
                </div>
            </div>
            <!-- Botones -->
            <div class="d-flex justify-content-between align-items-center w-100 mt-3">
                <div class="text-center me-3">
                    <h5 class="mb-1">Ir a Dashboard</h5>
                    <a href="<?= base_url('/dashboardconsultant') ?>" class="btn btn-primary btn-sm">Ir a DashBoard</a>
                </div>
                <div class="text-center">
                    <h5 class="mb-1">Añadir Registro</h5>
                    <a href="<?= base_url('/addcronogCapacitacion') ?>" class="btn btn-success btn-sm" target="_blank">Añadir Registro</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Espaciador para evitar que el contenido se oculte bajo el navbar fijo -->
    <div style="height: 160px;"></div>

    <!-- Contenedor Principal -->
    <div class="container my-4">
        <h2 class="text-center mb-4">Lista de Cronogramas de Capacitación</h2>

        <!-- Mensajes Flash -->
        <?php if (session()->getFlashdata('msg')): ?>
            <div class="alert alert-info">
                <?= session()->getFlashdata('msg') ?>
            </div>
        <?php endif; ?>

        <!-- Botón para Restablecer Filtros -->
        <button id="clearState" class="btn btn-danger btn-sm">Restablecer Filtros</button>

        <!-- Tabla Responsive -->
        <div class="table-responsive">
            <table id="cronogramaTable" class="table table-bordered table-hover" style="width:100%">
                <thead class="text-center">
                    <tr>
                        <th>#</th>
                        <th>Nombre de la Capacitación</th>
                        <th>Objetivo de la Capacitación</th>
                        <th>Nombre del Cliente</th>
                        <th>Fecha Programada</th>
                        <th>Fecha de Realización</th>
                        <th>Estado</th>
                        <th>Perfil de Asistentes</th>
                        <th>Capacitador</th>
                        <th>Horas de Duración</th>
                        <th>Indicador de Realización</th>
                        <th>Número de Asistentes</th>
                        <th>Total Programados</th>
                        <th>Porcentaje de Cobertura</th>
                        <th>Personas Evaluadas</th>
                        <th>Promedio de Calificaciones</th>
                        <th>Observaciones</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <!-- Generaremos los filtros desplegables mediante JavaScript -->
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                </tfoot>
                <tbody>
                    <?php if (!empty($cronogramas) && is_array($cronogramas)): ?>
                        <?php foreach ($cronogramas as $cronograma): ?>
                            <tr>
                                <td><?= esc($cronograma['id_cronograma_capacitacion']) ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($cronograma['nombre_capacitacion']); ?>"><?= esc($cronograma['nombre_capacitacion']) ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($cronograma['objetivo_capacitacion']); ?>"><?= esc($cronograma['objetivo_capacitacion']) ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($cronograma['nombre_cliente']); ?>"><?= esc($cronograma['nombre_cliente']) ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($cronograma['fecha_programada']); ?>"><?= esc($cronograma['fecha_programada']) ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($cronograma['fecha_de_realizacion']); ?>"><?= esc($cronograma['fecha_de_realizacion']) ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($cronograma['estado']); ?>"><?= esc($cronograma['estado']) ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($cronograma['perfil_de_asistentes']); ?>"><?= esc($cronograma['perfil_de_asistentes']) ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($cronograma['nombre_del_capacitador']); ?>"><?= esc($cronograma['nombre_del_capacitador']) ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($cronograma['horas_de_duracion_de_la_capacitacion']); ?>"><?= esc($cronograma['horas_de_duracion_de_la_capacitacion']) ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($cronograma['indicador_de_realizacion_de_la_capacitacion']); ?>"><?= esc($cronograma['indicador_de_realizacion_de_la_capacitacion']) ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($cronograma['numero_de_asistentes_a_capacitacion']); ?>"><?= esc($cronograma['numero_de_asistentes_a_capacitacion']) ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($cronograma['numero_total_de_personas_programadas']); ?>"><?= esc($cronograma['numero_total_de_personas_programadas']) ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($cronograma['porcentaje_cobertura']); ?>"><?= esc($cronograma['porcentaje_cobertura']) ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($cronograma['numero_de_personas_evaluadas']); ?>"><?= esc($cronograma['numero_de_personas_evaluadas']) ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($cronograma['promedio_de_calificaciones']); ?>"><?= esc($cronograma['promedio_de_calificaciones']) ?>%</td>
                                <td data-bs-toggle="tooltip" title="<?= esc($cronograma['observaciones']); ?>"><?= esc($cronograma['observaciones']) ?></td>
                                <td class="text-center">
                                    <a href="<?= base_url('/editcronogCapacitacion/' . esc($cronograma['id_cronograma_capacitacion'])) ?>" class="btn btn-warning btn-sm">Editar</a>
                                    <a href="<?= base_url('/deletecronogCapacitacion/' . esc($cronograma['id_cronograma_capacitacion'])) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este cronograma?');">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="18" class="text-center">No hay cronogramas de capacitación registrados</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white py-4 border-top mt-4">
        <div class="container text-center">
            <!-- Company and Rights -->
            <p class="fw-bold mb-1">Cycloid Talent SAS</p>
            <p class="mb-1">Todos los derechos reservados © 2024</p>
            <p class="mb-1">NIT: 901.653.912</p>

            <!-- Website Link -->
            <p class="mb-3">
                Sitio oficial: <a href="https://cycloidtalent.com/" target="_blank" class="text-primary text-decoration-none">https://cycloidtalent.com/</a>
            </p>

            <!-- Social Media Links -->
            <p class="mb-2"><strong>Nuestras Redes Sociales:</strong></p>
            <div class="d-flex justify-content-center gap-3">
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

    <!-- jQuery 3.6.0 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap 5 JS Bundle (Includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- DataTables Buttons JS -->
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.colVis.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script> <!-- Biblioteca necesaria para Excel -->
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.html5.min.js"></script> <!-- Botones HTML5 -->
    
    <!-- DataTables Spanish Language -->
    <script src="https://cdn.datatables.net/plug-ins/1.13.1/i18n/es-ES.json"></script>

    <script>
        $(document).ready(function() {
            // Inicializar DataTable con las opciones requeridas
            var table = $('#cronogramaTable').DataTable({
                // Activar la extensión de botones
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="bi bi-file-earmark-excel-fill"></i> Excel', // Icono de Bootstrap Icons
                        titleAttr: 'Exportar a Excel',
                        className: 'btn btn-success btn-sm'
                    },
                    {
                        extend: 'colvis',
                        text: '<i class="bi bi-columns"></i> Mostrar/Ocultar Columnas',
                        className: 'btn btn-secondary btn-sm'
                    }
                ],
                // Configurar el idioma a español
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.1/i18n/es-ES.json"
                },
                // Activar el guardado del estado para persistencia de filtros
                stateSave: true,
                // Definir el callback para inicializar los filtros después de la creación de la tabla
                initComplete: function () {
                    this.api().columns().every(function () {
                        var column = this;
                        var select = $('<select class="form-select form-select-sm"><option value="">Todos</option></select>')
                            .appendTo($(column.footer()).empty())
                            .on('change', function () {
                                var val = $.fn.dataTable.util.escapeRegex(
                                    $(this).val()
                                );

                                column
                                    .search(val ? '^' + val + '$' : '', true, false)
                                    .draw();
                            });

                        // Obtener los valores únicos de cada columna
                        column.data().unique().sort().each(function (d, j) {
                            if(d !== null && d !== ""){
                                select.append('<option value="' + d + '">' + d + '</option>')
                            }
                        });

                        // Restaurar el valor del filtro si existe en el estado guardado
                        var state = table.state.loaded();
                        if(state && state.columns && state.columns[column.index()].search && state.columns[column.index()].search.search) {
                            var searchValue = state.columns[column.index()].search.search.replace('^','').replace('$','');
                            select.val(searchValue);
                        }
                    });
                }
            });

            // Inicializar los tooltips de Bootstrap
            function initializeTooltips() {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                    new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }

            // Inicializar tooltips al cargar la página
            initializeTooltips();

            // Re-inicializar los tooltips después de cada redibujado de la tabla
            table.on('draw', function () {
                initializeTooltips();
            });

            // Botón para restablecer filtros
            $('#clearState').on('click', function () {
                // Limpiar el estado guardado de DataTables
                table.state.clear();
                // Recargar la página para aplicar los cambios
                location.reload();
            });
        });
    </script>
</body>

</html>
