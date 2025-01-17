<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Actividades - Plan de Trabajo Anual</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- DataTables Buttons CSS -->
    <link href="https://cdn.datatables.net/buttons/2.3.3/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f9f9f9;
            font-family: Arial, sans-serif;
        }

        h2 {
            margin: 20px 0;
            text-align: center;
            color: #333;
        }

        .btn {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
        }

        .dataTables_filter input {
            background-color: #f0f0f0;
            border-radius: 5px;
            border: 1px solid #ccc;
            padding: 6px;
        }

        .dataTables_length select {
            background-color: #f0f0f0;
            border-radius: 5px;
            padding: 6px;
        }

        td,
        th {
            max-width: 20ch;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            height: 50px;
        }

        .tooltip-inner {
            max-width: 300px;
            white-space: normal;
        }

        table tfoot {
            display: table-header-group;
        }

        /* Ajustes adicionales para mejorar la apariencia de los filtros */
        tfoot select {
            width: 100%;
            padding: 4px;
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

        /* Estilo para celdas editables de texto */
        .editable {
            background-color: #fff3cd;
            /* Color suave, por ejemplo amarillo claro */
            cursor: pointer;
        }

        /* Estilo para celdas editables con selector de fecha */
        .editable-date {
            background-color: #d1ecf1;
            /* Color suave, por ejemplo azul claro */
            cursor: pointer;
        }

        .editable-select {
            background-color: #e2f0fb;
            /* Azul claro para distinguir celdas con select */
            cursor: pointer;
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
                    <a href="<?= base_url('/addPlanDeTrabajoAnual') ?>" class="btn btn-success btn-sm">Añadir Registro</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Espaciado para el navbar fijo -->
    <div style="height: 100px;"></div>

    <div class="container mt-5">
        <h2 class="text-center mb-4">Lista de Actividades del Plan de Trabajo Anual</h2>

        <button id="clearState" class="btn btn-danger btn-sm mb-3">Restablecer Filtros</button>
        <div id="notification" class="alert alert-success" style="display: none;" role="alert"></div>


        <div class="table-responsive">
            <table id="actividadesTable" class="table table-striped table-bordered nowrap" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>ID Plan de Trabajo</th>
                        <th>PHVA</th>
                        <th>Numeral</th>
                        <th>Actividad</th>
                        <th>Responsable Sugerido</th>
                        <th>Fecha Propuesta</th>
                        <th>Fecha Cierre</th>
                        <th>Responsable Definido</th>
                        <th>Estado Actividad</th>
                        <th>Porcentaje Avance</th>
                        <th>Semana</th>
                        <th>Observaciones</th>
                        <th>Creado en</th>
                        <th>Actualizado en</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <!-- Filtros para cada columna -->
                        <th></th>
                        <th><select class="form-select form-select-sm filter-select" aria-label="Filtro Cliente">
                                <option value="">Todos</option>
                            </select></th>
                        <th><select class="form-select form-select-sm filter-select" aria-label="Filtro ID Plan de Trabajo">
                                <option value="">Todos</option>
                            </select></th>
                        <th><select class="form-select form-select-sm filter-select" aria-label="Filtro PHVA">
                                <option value="">Todos</option>
                            </select></th>
                        <th><select class="form-select form-select-sm filter-select" aria-label="Filtro Numeral">
                                <option value="">Todos</option>
                            </select></th>
                        <th><select class="form-select form-select-sm filter-select" aria-label="Filtro Actividad">
                                <option value="">Todos</option>
                            </select></th>
                        <th><select class="form-select form-select-sm filter-select" aria-label="Filtro Responsable Sugerido">
                                <option value="">Todos</option>
                            </select></th>
                        <th><select class="form-select form-select-sm filter-select" aria-label="Filtro Fecha Propuesta">
                                <option value="">Todos</option>
                            </select></th>
                        <th><select class="form-select form-select-sm filter-select" aria-label="Filtro Fecha Cierre">
                                <option value="">Todos</option>
                            </select></th>
                        <th><select class="form-select form-select-sm filter-select" aria-label="Filtro Responsable Definido">
                                <option value="">Todos</option>
                            </select></th>
                        <th><select class="form-select form-select-sm filter-select" aria-label="Filtro Estado Actividad">
                                <option value="">Todos</option>
                            </select></th>
                        <th><select class="form-select form-select-sm filter-select" aria-label="Filtro Porcentaje Avance">
                                <option value="">Todos</option>
                            </select></th>
                        <th><select class="form-select form-select-sm filter-select" aria-label="Filtro Semana">
                                <option value="">Todos</option>
                            </select></th>
                        <th><select class="form-select form-select-sm filter-select" aria-label="Filtro Observaciones">
                                <option value="">Todos</option>
                            </select></th>
                        <th><select class="form-select form-select-sm filter-select" aria-label="Filtro Creado en">
                                <option value="">Todos</option>
                            </select></th>
                        <th><select class="form-select form-select-sm filter-select" aria-label="Filtro Actualizado en">
                                <option value="">Todos</option>
                            </select></th>
                        <th><select class="form-select form-select-sm filter-select" aria-label="Filtro Acciones">
                                <option value="">Todos</option>
                            </select></th>
                    </tr>
                </tfoot>
                <tbody>
                    <?php if (!empty($actividades)) : ?>
                        <?php foreach ($actividades as $actividad) : ?>
                            <tr data-id="<?= esc($actividad['id_ptacliente']) ?>">
                                <td><?= esc($actividad['id_ptacliente']) ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($actividad['nombre_cliente']) ?>"><?= esc($actividad['nombre_cliente']) ?></td>
                                <td><?= esc($actividad['id_plandetrabajo']) ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($actividad['phva_plandetrabajo']) ?>"><?= esc($actividad['phva_plandetrabajo']) ?></td>
                                <td><?= esc($actividad['numeral_plandetrabajo']) ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($actividad['actividad_plandetrabajo']) ?>"><?= esc($actividad['actividad_plandetrabajo']) ?></td>


                                <td contenteditable="true"
                                    class="editable"
                                    data-field="responsable_sugerido_plandetrabajo"
                                    data-bs-toggle="tooltip"
                                    title="<?= esc($actividad['responsable_sugerido_plandetrabajo']) ?>">
                                    <?= esc($actividad['responsable_sugerido_plandetrabajo']) ?>
                                </td>




                                <!-- Celda editable para Fecha Propuesta con calendario -->
                                <td contenteditable="false" class="editable-date" data-field="fecha_propuesta" data-bs-toggle="tooltip" title="<?= esc($actividad['fecha_propuesta']) ?>">
                                    <?= esc($actividad['fecha_propuesta']) ?>
                                </td>
                                <td contenteditable="false" class="editable-date" data-field="fecha_cierre" data-bs-toggle="tooltip" title="<?= esc($actividad['fecha_cierre']) ?>">
                                    <?= esc($actividad['fecha_cierre']) ?>
                                </td>
                                <td contenteditable="true"
                                    class="editable"
                                    data-field="responsable_definido_paralaactividad"
                                    data-bs-toggle="tooltip"
                                    title="<?= esc($actividad['responsable_definido_paralaactividad']) ?>">
                                    <?= esc($actividad['responsable_definido_paralaactividad']) ?>
                                </td>


                                <td contenteditable="false"
                                    class="editable-select"
                                    data-field="estado_actividad"
                                    data-bs-toggle="tooltip"
                                    title="<?= esc($actividad['estado_actividad']) ?>">
                                    <?= esc($actividad['estado_actividad']) ?>
                                </td>
                                <td contenteditable="false"
                                    class="editable-select"
                                    data-field="porcentaje_avance"
                                    data-bs-toggle="tooltip"
                                    title="<?= esc($actividad['porcentaje_avance']) ?>%">
                                    <?= esc($actividad['porcentaje_avance']) ?>%
                                </td>
                                <td><?= esc($actividad['semana']) ?></td>

                                <td contenteditable="true" class="editable" data-field="observaciones" data-bs-toggle="tooltip" title="<?= esc($actividad['observaciones']) ?>">
                                    <?= esc($actividad['observaciones']) ?>
                                </td>

                                <td data-bs-toggle="tooltip" title="<?= esc($actividad['created_at']) ?>"><?= esc($actividad['created_at']) ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($actividad['updated_at']) ?>"><?= esc($actividad['updated_at']) ?></td>
                                <td>
                                    <a href="<?= base_url('editPlanDeTrabajoAnual/' . $actividad['id_ptacliente']) ?>" class="btn btn-warning btn-sm">Editar</a>
                                    <a href="<?= base_url('deletePlanDeTrabajoAnual/' . $actividad['id_ptacliente']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar esta actividad?')">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="17" class="text-center">No se encontraron actividades.</td>
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
            <p class="mb-3">Sitio oficial: <a href="https://cycloidtalent.com/" target="_blank">https://cycloidtalent.com/</a></p>
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

    <!-- Scripts unificados -->
    <!-- jQuery, Bootstrap, DataTables y extensiones se cargan aquí arriba en el bloque unificado -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.colVis.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inicializar DataTables con Buttons
            var table = $('#actividadesTable').DataTable({
                stateSave: true,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.1/i18n/es-ES.json"
                },
                pagingType: "full_numbers",
                responsive: true,
                autoWidth: false,
                dom: 'Bfltip', // Añadir Buttons al DOM
                buttons: [{
                        extend: 'excelHtml5',
                        text: 'Exportar a Excel',
                        className: 'btn btn-success btn-sm me-2'
                    },
                    {
                        extend: 'colvis',
                        text: 'Seleccionar Columnas',
                        className: 'btn btn-secondary btn-sm'
                    }
                ],
                initComplete: function() {
                    var api = this.api();
                    // Para cada columna, crear un filtro desplegable en el <tfoot>
                    api.columns().every(function() {
                        var column = this;
                        var select = $(column.footer()).find('select');
                        if (select.length) {
                            column.data().unique().sort().each(function(d, j) {
                                if (d) {
                                    select.append('<option value="' + d + '">' + d + '</option>');
                                }
                            });
                            var search = column.search();
                            if (search) {
                                select.val(search);
                            }
                        }
                    });
                }
            });

            // Colocar los botones de DataTables antes del botón de restablecer filtros
            table.buttons().container().prependTo('.container');

            // Evento al cambiar cualquier filtro
            $('.filter-select').on('change', function() {
                var columnIndex = $(this).closest('th').index();
                var value = $(this).val();
                table.column(columnIndex).search(value).draw();
            });

            // Inicializar tooltips de Bootstrap 5
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Botón para borrar el estado
            $('#clearState').on('click', function() {
                localStorage.removeItem('DataTables_actividadesTable_/');
                table.state.clear();
                location.reload();
            });

            // Manejador para campos editables estándar (no fechas)
            $('.editable').off('blur').on('blur', function() {
                const cell = $(this);
                const value = cell.text().trim();
                const field = cell.data('field');
                const id = cell.closest('tr').data('id');

                $.ajax({
                    url: '<?= base_url('/updatePlanDeTrabajo') ?>',
                    method: 'POST',
                    data: {
                        id: id,
                        field: field,
                        value: value
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#notification').text('Registro actualizado correctamente').fadeIn();
                            setTimeout(function() {
                                $('#notification').fadeOut();
                            }, 3000); // Oculta la notificación después de 3 segundos
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },

                    error: function() {
                        alert('Error al comunicarse con el servidor');
                    }
                });
            });

            // Manejador para celdas de fecha propuesta con selector de fecha
            $(document).on('click', '.editable-date', function() {
                var cell = $(this);
                console.log("Editando fecha propuesta en registro:", cell.closest('tr').data('id'));
                // Evitar reinicializar si ya existe un input
                if (cell.find('input').length === 0) {
                    var currentValue = cell.text().trim();
                    var input = $('<input>', {
                        type: 'date',
                        value: currentValue,
                        class: 'form-control'
                    });
                    cell.html(input);
                    input.focus();
                    input.on('blur change', function() {
                        var newValue = $(this).val();
                        const field = cell.data('field');
                        const id = cell.closest('tr').data('id');

                        // Actualiza la celda con el nuevo valor
                        cell.text(newValue);
                        // Reinicializar tooltip si es necesario
                        cell.attr('title', newValue).tooltip('dispose').tooltip();

                        // Enviar actualización vía AJAX
                        $.ajax({
                            url: '<?= base_url('/updatePlanDeTrabajo') ?>',
                            method: 'POST',
                            data: {
                                id: id,
                                field: field,
                                value: newValue
                            },
                            success: function(response) {
                                if (response.success) {
                                    alert('Registro actualizado correctamente');
                                } else {
                                    alert('Error: ' + response.message);
                                }
                            },
                            error: function() {
                                alert('Error al comunicarse con el servidor');
                            }
                        });
                    });
                }
            });
            $(document).on('click', '.editable-select', function() {
                var cell = $(this);
                // Evitar reinicializar si ya existe un <select> en la celda
                if (cell.find('select').length === 0) {
                    var field = cell.data('field');
                    var currentValue = cell.text().trim();
                    var select = $('<select>', {
                        class: 'form-select form-select-sm'
                    });

                    if (field === 'estado_actividad') {
                        var options = ['ABIERTA', 'CERRADA', 'GESTIONANDO'];
                        $.each(options, function(index, option) {
                            var optionElem = $('<option>', {
                                value: option,
                                text: option
                            });
                            if (option === currentValue) optionElem.attr('selected', 'selected');
                            select.append(optionElem);
                        });
                    } else if (field === 'porcentaje_avance') {
                        for (var i = 0; i <= 100; i += 10) {
                            var perc = i + '%';
                            var optionElem = $('<option>', {
                                value: perc,
                                text: perc
                            });
                            // Eliminar porcentaje del texto de la celda para comparar correctamente
                            if (perc === currentValue || perc === currentValue.replace('%', '') + '%') {
                                optionElem.attr('selected', 'selected');
                            }
                            select.append(optionElem);
                        }
                    }

                    cell.html(select);
                    select.focus();

                    select.on('blur change', function() {
                        var newValue = $(this).val();
                        cell.text(newValue);
                        cell.attr('title', newValue).tooltip('dispose').tooltip();

                        var field = cell.data('field');
                        var id = cell.closest('tr').data('id');

                        $.ajax({
                            url: '<?= base_url('/updatePlanDeTrabajo') ?>',
                            method: 'POST',
                            data: {
                                id: id,
                                field: field,
                                value: newValue
                            },
                            success: function(response) {
                                if (response.success) {
                                    console.log('Actualizado correctamente');
                                } else {
                                    alert('Error: ' + response.message);
                                }
                            },
                            error: function() {
                                alert('Error al comunicarse con el servidor');
                            }
                        });
                    });
                }
            });

        });
    </script>
</body>

</html>