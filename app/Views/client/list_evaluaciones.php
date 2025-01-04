<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluación del Cliente</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- DataTables Buttons CSS -->
    <link href="https://cdn.datatables.net/buttons/2.3.3/css/buttons.bootstrap5.min.css" rel="stylesheet">
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
            background-color: #f9f9f9;
            font-family: Arial, sans-serif;
        }

        h2 {
            color: #3A3F51;
        }

        .card-title {
            font-size: 1.1rem;
        }

        footer a {
            color: #007BFF;
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
        }

        .social-icons img {
            height: 24px;
            width: 24px;
        }

        /* Estilos para el navbar */
        nav {
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top">
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
                    <a href="<?= base_url('/addEvaluacion') ?>" class="btn btn-success btn-sm" target="_blank">Añadir Registro</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Espaciado para el navbar fijo -->
    <div style="height: 150px;"></div>

    <div class="container my-5">
        <h2 class="mb-4 text-dark">Evaluaciones del Cliente: <?= esc($client['nombre_cliente']) ?></h2>

        <!-- Tarjetas de indicadores -->
        <div class="row text-center mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-light">
                    <div class="card-body bg-white">
                        <h5 class="card-title text-secondary">Total Puntaje Cuantitativo</h5>
                        <p class="display-4 font-weight-bold"><?= esc($sum_puntaje_cuantitativo) ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-light">
                    <div class="card-body bg-white">
                        <h5 class="card-title text-secondary">Total Valor</h5>
                        <p class="display-4 font-weight-bold"><?= esc($sum_valor) ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-light">
                    <div class="card-body bg-white">
                        <h5 class="card-title text-secondary">Indicador General</h5>
                        <p class="display-4 font-weight-bold"><?= number_format($indicador_general, 2) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Indicadores por categoría -->
        <div class="row text-center mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-light bg-light-green">
                    <div class="card-body bg-white">
                        <h5 class="card-title text-success">Cumple Totalmente</h5>
                        <p>Conteo: <?= esc($count_cumple) ?></p>
                        <p>Puntaje: <?= esc($sum_puntaje_cumple) ?></p>
                        <p>Valor: <?= esc($sum_valor_cumple) ?></p>
                        <p>Indicador: <?= number_format($indicador_cumple, 2) ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-light bg-light-red">
                    <div class="card-body bg-white">
                        <h5 class="card-title text-danger">No Cumple</h5>
                        <p>Conteo: <?= esc($count_no_cumple) ?></p>
                        <p>Puntaje: <?= esc($sum_puntaje_no_cumple) ?></p>
                        <p>Valor: <?= esc($sum_valor_no_cumple) ?></p>
                        <p>Indicador: <?= number_format($indicador_no_cumple, 2) ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-light bg-light-yellow">
                    <div class="card-body bg-white">
                        <h5 class="card-title text-warning">No Aplica</h5>
                        <p>Conteo: <?= esc($count_no_aplica) ?></p>
                        <p>Puntaje: <?= esc($sum_puntaje_no_aplica) ?></p>
                        <p>Valor: <?= esc($sum_valor_no_aplica) ?></p>
                        <p>Indicador: <?= number_format($indicador_no_aplica, 2) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estado del cliente -->
        <div class="alert <?php if ($indicador_general < 0.6): ?>alert-danger<?php elseif ($indicador_general <= 0.85): ?>alert-warning<?php else: ?>alert-success<?php endif; ?> text-center" role="alert">
            <h5 class="alert-heading">
                Estado: <?php if ($indicador_general < 0.6): ?>CRÍTICO<?php elseif ($indicador_general <= 0.85): ?>MODERADAMENTE ACEPTABLE<?php else: ?>ACEPTABLE<?php endif; ?>
            </h5>
            <p><?php if ($indicador_general < 0.6): ?>
                    Si el puntaje obtenido es menor al 60%, debe realizar un plan de mejoramiento inmediato.
                <?php elseif ($indicador_general <= 0.85): ?>
                    Si el puntaje obtenido está entre el 60% y 85%, debe realizar un plan de mejoramiento.
                <?php else: ?>
                    Si el puntaje obtenido es mayor a 85%, debe mantener la calificación y continuar mejorando.
                <?php endif; ?>
            </p>
            <ol class="text-left mx-auto" style="max-width: 600px;">
                <?php if ($indicador_general < 0.6): ?>
                    <li>Realizar y tener a disposición del Ministerio del Trabajo un Plan de Mejoramiento de inmediato.</li>
                    <li>Enviar a la ARL un reporte de avances dentro de tres (3) meses después de realizada la autoevaluación.</li>
                    <li>Seguimiento anual y plan de visita a la empresa con valoración crítica, por parte del Ministerio del Trabajo.</li>
                <?php elseif ($indicador_general <= 0.85): ?>
                    <li>Realizar y tener a disposición del Ministerio del Trabajo un Plan de Mejoramiento.</li>
                    <li>Enviar a la ARL un reporte de avances dentro de seis (6) meses después de realizada la autoevaluación.</li>
                    <li>Plan de visita por parte del Ministerio del Trabajo.</li>
                <?php else: ?>
                    <li>Mantener la calificación y evidencias a disposición del Ministerio del Trabajo.</li>
                    <li>Incluir en el Plan Anual de Trabajo las mejoras que se establezcan de acuerdo con la evaluación.</li>
                <?php endif; ?>
            </ol>
        </div>

        <!-- Botón para Exportar a Excel y Tabla de Evaluaciones -->
        <div>
            <!-- Botones de DataTables se insertarán aquí -->
            <div class="d-flex justify-content-between mb-3">
                <button id="clearState" class="btn btn-danger btn-sm">Restablecer Filtros</button>
                <div id="buttonsContainer"></div>
            </div>

            <table id="evaluacionesTable" class="styled-table table table-striped table-bordered nowrap" style="width:100%">
                <thead class="table-light">
                    <tr>
                        
                        
                        <th>Ciclo</th>
                        <th>Estándar</th>
                        <th>Detalle Estándar</th>
                        <th>Item del Estándar</th>
                        <th>Evaluación Inicial</th>
                        <th>Valor</th>
                        <th>Puntaje Cuantitativo</th>
                        <th>Item</th>
                        <th>Criterio</th>
                        <th>Modo de Verificación</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tfoot class="table-light">
                    <tr class="filters">
                        
                       
                        <th>
                            <select class="form-select form-select-sm filter-select" aria-label="Filtro Ciclo">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select" aria-label="Filtro Estándar">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select" aria-label="Filtro Detalle Estándar">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select" aria-label="Filtro Item del Estándar">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select" aria-label="Filtro Evaluación Inicial">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select" aria-label="Filtro Valor">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select" aria-label="Filtro Puntaje Cuantitativo">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select" aria-label="Filtro Item">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select" aria-label="Filtro Criterio">
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select" aria-label="Filtro Modo de Verificación">
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
                    <?php if (!empty($evaluaciones) && is_array($evaluaciones)): ?>
                        <?php foreach ($evaluaciones as $evaluacion): ?>
                            <tr>
                                
                               
                                <td data-bs-toggle="tooltip" title="<?= esc($evaluacion['ciclo']); ?>"><?= esc($evaluacion['ciclo']); ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($evaluacion['estandar']); ?>"><?= esc($evaluacion['estandar']); ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($evaluacion['detalle_estandar']); ?>"><?= esc($evaluacion['detalle_estandar']); ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($evaluacion['item_del_estandar']); ?>"><?= esc($evaluacion['item_del_estandar']); ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($evaluacion['evaluacion_inicial']); ?>"><?= esc($evaluacion['evaluacion_inicial']); ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($evaluacion['valor']); ?>"><?= esc($evaluacion['valor']); ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($evaluacion['puntaje_cuantitativo']); ?>"><?= esc($evaluacion['puntaje_cuantitativo']); ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($evaluacion['item']); ?>"><?= esc($evaluacion['item']); ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($evaluacion['criterio']); ?>"><?= esc($evaluacion['criterio']); ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($evaluacion['modo_de_verificacion']); ?>"><?= esc($evaluacion['modo_de_verificacion']); ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($evaluacion['observaciones']); ?>"><?= esc($evaluacion['observaciones']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="13" class="text-center">No hay evaluaciones para este cliente.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

    <!-- Footer -->
    <footer class="bg-white py-4 border-top">
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
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" alt="Facebook">
                </a>
                <a href="https://co.linkedin.com/company/cycloid-talent" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733561.png" alt="LinkedIn">
                </a>
                <a href="https://www.instagram.com/cycloid_talent?igsh=Nmo4d2QwZDg5dHh0" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733558.png" alt="Instagram">
                </a>
                <a href="https://www.tiktok.com/@cycloid_talent?_t=8qBSOu0o1ZN&_r=1" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/3046/3046126.png" alt="TikTok">
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
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.colVis.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inicializar DataTables con Buttons y Configuración de Filtros en <tfoot>
            var table = $('#evaluacionesTable').DataTable({
                stateSave: true, // Habilitar la persistencia del estado de la tabla
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
                },
                pagingType: "full_numbers",
                responsive: true,
                autoWidth: false,
                dom: 'Bfltip', // Integrar Buttons en el DOM
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
                initComplete: function() {
                    var api = this.api();

                    // Para cada columna, crear un filtro en el <tfoot>
                    api.columns().every(function() {
                        var column = this;
                        var headerIndex = column.index();
                        var filterElement = $('tfoot tr.filters th').eq(headerIndex).find('.filter-select');

                        if (filterElement.length && !filterElement.prop('disabled')) { // Solo si existe un filtro select y no está deshabilitado
                            // Obtener los valores únicos de la columna
                            column.data().unique().sort().each(function(d, j) {
                                if (d) { // Evitar valores vacíos
                                    // Verificar si la opción ya existe para evitar duplicados
                                    if (filterElement.find('option[value="' + d + '"]').length === 0) {
                                        filterElement.append('<option value="' + d + '">' + d + '</option>');
                                    }
                                }
                            });

                            // Restaurar el valor del filtro si existe en el estado guardado
                            var search = column.search();
                            if (search) {
                                filterElement.val(search);
                            }
                        }
                    });
                }
            });

            // Colocar los botones de DataTables en el contenedor específico
            table.buttons().container().appendTo('#buttonsContainer');

            // Evento al cambiar cualquier filtro (select)
            $('tfoot .filter-select').on('change', function() {
                var columnIndex = $(this).closest('th').index();
                var value = $(this).val();
                table.column(columnIndex).search(value ? '^' + value + '$' : '', true, false).draw();
            });

            // Inicializar tooltips de Bootstrap 5
            function initializeTooltips() {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }

            initializeTooltips(); // Inicializar al cargar la página

            // Re-inicializar tooltips después de cada redibujado de la tabla
            table.on('draw.dt', function() {
                initializeTooltips();
            });

            // Botón para borrar el estado y restablecer filtros
            $('#clearState').on('click', function() {
                // Construir la clave de localStorage utilizada por DataTables
                var storageKey = 'DataTables_' + table.table().node().id + '_' + window.location.pathname;

                // Borrar estado guardado en localStorage
                localStorage.removeItem(storageKey);

                // Limpiar estado en DataTables
                table.state.clear();

                // Restablecer todos los filtros a sus valores predeterminados
                $('tfoot .filter-select').each(function() {
                    $(this).val('');
                });

                // Restablecer las búsquedas de las columnas y redibujar la tabla
                table.columns().search('').draw();
            });
        });
    </script>
</body>

</html>
