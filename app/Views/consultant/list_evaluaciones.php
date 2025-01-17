<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Evaluaciones</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- DataTables Buttons CSS -->
    <link href="https://cdn.datatables.net/buttons/2.3.3/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <style>
        /* Tus estilos personalizados */
        body {
            background-color: #f9f9f9;
            font-family: Arial, sans-serif;
        }

        h1 {
            margin: 20px 0;
            text-align: center;
            color: #333;
        }

        .btn-custom {
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
            /* Limita el contenido visible a 20 caracteres */
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            height: 25px;
            /* Reduce la altura de las filas */
        }

        .tooltip-inner {
            max-width: 300px;
            /* Ajusta el ancho máximo del tooltip */
            word-wrap: break-word;
            z-index: 1050;
            /* Asegura que el tooltip se muestre correctamente */
        }

        .filters select {
            width: 100%;
            padding: 4px;
            border-radius: 4px;
            border: 1px solid #ccc;
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
                    <a href="<?= base_url('/addEvaluacion') ?>" class="btn btn-success btn-sm" target="_blank">Añadir Registro</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Espaciado para el navbar fijo -->
    <div style="height: 100px;"></div>

    <div class="container mt-5">
        <!-- Botón para restablecer filtros y DataTables Buttons -->
        <h1 class="text-center mb-4">Lista de Evaluaciones</h1>
        <div class="d-flex justify-content-between mb-3">
            <button id="clearState" class="btn btn-danger btn-sm">Restablecer Filtros</button>
            <div id="buttonsContainer"></div>
        </div>
        <div class="table-responsive">
            <table id="evaluacionesTable" class="table table-striped table-bordered nowrap" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th>Acciones</th>
                        <th>Cliente</th>
                        <th>Ciclo</th>
                        <th>Estándar</th>
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
                            <!-- Filtro para "Acciones" - Opcionalmente puedes dejarlo vacío o con "Todos" -->
                            <select class="form-select form-select-sm filter-select" aria-label="Filtro Acciones" disabled>
                                <option value="">Todos</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm filter-select" aria-label="Filtro Cliente">
                                <option value="">Todos</option>
                            </select>
                        </th>
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
                            <select class="form-select form-select-sm filter-select" aria-label="Filtro Puntaje">
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
                                <td>
                                    <a href="<?= base_url('editEvaluacion/' . $evaluacion['id_ev_ini']); ?>" class="btn btn-sm btn-warning">Editar</a>
                                    <a href="<?= base_url('deleteEvaluacion/' . $evaluacion['id_ev_ini']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de que quieres eliminar esta evaluación?');">Eliminar</a>
                                </td>
                                <td data-bs-toggle="tooltip" title="<?= esc($evaluacion['nombre_cliente']); ?>"><?= esc($evaluacion['nombre_cliente']); ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($evaluacion['ciclo']); ?>"><?= esc($evaluacion['ciclo']); ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($evaluacion['estandar']); ?>"><?= esc($evaluacion['estandar']); ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($evaluacion['item_del_estandar']); ?>"><?= esc($evaluacion['item_del_estandar']); ?></td>

                                <!-- Celda editable para "Evaluación Inicial" -->
                                <td class="editable-select"
                                    data-field="evaluacion_inicial"
                                    data-id="<?= $evaluacion['id_ev_ini']; ?>"
                                    
                                    title="<?= esc($evaluacion['evaluacion_inicial']); ?>">
                                    <?= esc($evaluacion['evaluacion_inicial']); ?>
                                </td>

                                <td data-bs-toggle="tooltip" title="<?= esc($evaluacion['valor']); ?>"><?= esc($evaluacion['valor']); ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($evaluacion['puntaje_cuantitativo']); ?>"><?= esc($evaluacion['puntaje_cuantitativo']); ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($evaluacion['item']); ?>"><?= esc($evaluacion['item']); ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($evaluacion['criterio']); ?>"><?= esc($evaluacion['criterio']); ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($evaluacion['modo_de_verificacion']); ?>"><?= esc($evaluacion['modo_de_verificacion']); ?></td>

                                <!-- Celda editable para "Observaciones" -->
                                <td class="editable"
                                    data-field="observaciones"
                                    data-id="<?= $evaluacion['id_ev_ini']; ?>"
                                    data-bs-toggle="tooltip"
                                    title="<?= esc($evaluacion['observaciones']); ?>">
                                    <?= esc($evaluacion['observaciones']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="12" class="text-center">No se encontraron evaluaciones.</td>
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
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
    <!-- DataTables Buttons JS -->
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.colVis.min.js"></script>

    <script>
$(document).ready(function() {
    // 1. Configuración para edición en línea
    $(document).on('click', '.editable-select, .editable', function() {
        if ($(this).find('input, select').length) return; // Evitar duplicados

        var cell = $(this);
        var field = cell.data('field');
        var id = cell.data('id');
        var currentValue = cell.text().trim();

        if (field === 'evaluacion_inicial') {
            var options = ['CUMPLE TOTALMENTE', 'NO CUMPLE', 'NO APLICA'];
            var select = $('<select>', { class: 'form-select form-select-sm' });

            options.forEach(function(option) {
                select.append($('<option>', {
                    value: option,
                    text: option,
                    selected: option === currentValue
                }));
            });

            cell.html(select);
            select.focus();

            select.on('blur change', function() {
                var newValue = select.val();
                cell.text(newValue);
                updateField(id, field, newValue);
            });

        } else if (field === 'observaciones') {
            var input = $('<input>', {
                type: 'text',
                class: 'form-control',
                value: currentValue
            });

            cell.html(input);
            input.focus();

            input.on('blur', function() {
                var newValue = input.val();
                cell.text(newValue);
                updateField(id, field, newValue);
            });
        }
    });

    function updateField(id, field, value) {
        $.ajax({
            url: '<?= base_url('/updateEvaluacion') ?>',
            method: 'POST',
            data: { id: id, field: field, value: value },
            success: function(response) {
                if (response.success) {
                    console.log(response.message);
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al comunicarse con el servidor:', error);
                alert('Error al comunicarse con el servidor: ' + error);
            }
        });
    }

    // 2. Inicialización de DataTables
    var table = $('#evaluacionesTable').DataTable({
        stateSave: true,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.1/i18n/es-ES.json"
        },
        pagingType: "full_numbers",
        responsive: true,
        autoWidth: false,
        dom: 'Bfltip',        // Incluye 'f' para el buscador global
        pageLength: 10,       // Limita a 10 registros por página
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

                if (filterElement.length && !filterElement.prop('disabled')) {
                    // Obtener los valores únicos de la columna
                    column.data().unique().sort().each(function(d) {
                        if (d) { // Evitar valores vacíos
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
        table.column(columnIndex).search(value).draw();
    });

    // Inicializar tooltips de Bootstrap 5 y re-inicializar en cada redibujado
    function initializeTooltips() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    initializeTooltips();
    table.on('draw.dt', function() {
        initializeTooltips();
    });

    // Botón para borrar el estado y restablecer filtros
    $('#clearState').on('click', function() {
        var storageKey = 'DataTables_' + table.table().node().id + '_' + window.location.pathname;
        localStorage.removeItem(storageKey);
        table.state.clear();

        $('tfoot .filter-select').each(function() {
            $(this).val('');
        });

        table.columns().search('').draw();
    });
});
</script>


</body>

</html>