<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Cronogramas de Capacitación</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CSS -->
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

        #clearState {
            margin-bottom: 15px;
        }

        table tbody tr td,
        table thead tr th,
        table tfoot tr th {
            height: 50px;
            vertical-align: middle;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .editable {
            background-color: #fff3cd;
            cursor: pointer;
        }

        .editable-select {
            background-color: #e2f0fb;
            cursor: pointer;
        }

        .editable-date {
            background-color: #d1ecf1;
            cursor: pointer;
        }

        td.details-control {
            text-align: center;
            cursor: pointer;
        }

        tr.shown td.details-control i {
            transform: rotate(90deg);
        }
    </style>
</head>

<body>

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

    <!-- Espaciador para el navbar fijo -->
    <div style="height: 20px;"></div>

    <!-- Contenedor Principal -->
    <div class="container-fluid my-4">
        <h2 class="text-center mb-4">Lista de Cronogramas de Capacitación</h2>

        <!-- Buscador Global -->
      <!--   <div class="mb-3">
            <input type="text" id="globalSearch" class="form-control" placeholder="Buscar en todos los campos...">
        </div> -->

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
                    <!-- Única fila de encabezados -->
                    <tr>
                        <th></th>
                        <th>#</th>
                        <th>Nombre de la Capacitación</th>
                        <th>Objetivo de la Capacitación</th>
                        <th>Nombre del Cliente</th>
                        <th>*Fecha Programada</th>
                        <th>*Fecha de Realización</th>
                        <th>*Estado</th>
                        <th>*Perfil de Asistentes</th>
                        <th>*Capacitador</th>
                        <th>*Horas de Duración</th>
                        <th>*Indicador de Realización</th>
                        <th>*Número de Asistentes</th>
                        <th>*Total Programados</th>
                        <th>Porcentaje de Cobertura</th>
                        <th>*Personas Evaluadas</th>
                        <th>*Promedio de Calificaciones</th>
                        <th>*Observaciones</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tfoot class="text-center">
                    <!-- Fila de filtros en el pie de la tabla -->
                    <tr>
                        <th></th>
                        <th><input type="text" class="form-control form-control-sm column-search" placeholder="Filtrar ID"></th>
                        <th><input type="text" class="form-control form-control-sm column-search" placeholder="Filtrar capacitación"></th>
                        <th><input type="text" class="form-control form-control-sm column-search" placeholder="Filtrar fases"></th>
                        <th><input type="text" class="form-control form-control-sm column-search" placeholder="Filtrar cliente"></th>
                        <th><input type="text" class="form-control form-control-sm column-search" placeholder="Filtrar fecha"></th>
                        <th><input type="text" class="form-control form-control-sm column-search" placeholder="Filtrar fecha"></th>
                        <th>
                            <select class="form-select form-select-sm column-search">
                                <option value="">Todos</option>
                                <option value="PROGRAMADA">PROGRAMADA</option>
                                <option value="EJECUTADA">EJECUTADA</option>
                                <option value="CANCELADA POR EL CLIENTE">CANCELADA POR EL CLIENTE</option>
                                <option value="REPROGRAMADA">REPROGRAMADA</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-select form-select-sm column-search">
                                <option value="">Todos</option>
                                <option value="CONTRATISTAS">CONTRATISTAS</option>
                                <option value="RESIDENTES">RESIDENTES</option>
                                <option value="TODOS">TODOS</option>
                                <option value="ASAMBLEA">ASAMBLEA</option>
                                <option value="CONSEJO DE ADMINISTRACIÓN">CONSEJO DE ADMINISTRACIÓN</option>
                                <option value="ADMINISTRADOR">ADMINISTRADOR</option>
                            </select>
                        </th>
                        <th><input type="text" class="form-control form-control-sm column-search" placeholder="Filtrar capacitador"></th>
                        <th><input type="text" class="form-control form-control-sm column-search" placeholder="Filtrar horas"></th>
                        <th>
                            <select class="form-select form-select-sm column-search">
                                <option value="">Todos</option>
                                <option value="SE EJECUTO EN LA FECHA O ANTES DE LA FECHA">SE EJECUTO EN LA FECHA O ANTES DE LA FECHA</option>
                                <option value="SE EJECUTO DESPUES DE LA FECHA ACORDADA A CAUSA DEL CLIENTE">SE EJECUTO DESPUES DE LA FECHA ACORDADA A CAUSA DEL CLIENTE</option>
                                <option value="DECLINADA POR EL CLIENTE">DECLINADA POR EL CLIENTE</option>
                                <option value="NO HAY JUSTIFICACION PORQUE NO SE REALIZÓ">NO HAY JUSTIFICACION PORQUE NO SE REALIZÓ</option>
                                <option value="SE EJECUTO DESPUES DE LA FECHA POR CAUSA DEL CAPACITADOR">SE EJECUTO DESPUES DE LA FECHA POR CAUSA DEL CAPACITADOR</option>
                            </select>
                        </th>
                        <th><input type="text" class="form-control form-control-sm column-search" placeholder="Filtrar asistentes"></th>
                        <th><input type="text" class="form-control form-control-sm column-search" placeholder="Filtrar total"></th>
                        <th><input type="text" class="form-control form-control-sm column-search" placeholder="Filtrar % cobertura"></th>
                        <th><input type="text" class="form-control form-control-sm column-search" placeholder="Filtrar evaluadas"></th>
                        <th><input type="text" class="form-control form-control-sm column-search" placeholder="Filtrar calificaciones"></th>
                        <th><input type="text" class="form-control form-control-sm column-search" placeholder="Filtrar observaciones"></th>
                        <th></th>
                    </tr>
                </tfoot>
                <tbody>
                    <?php if (!empty($cronogramas) && is_array($cronogramas)): ?>
                        <?php foreach ($cronogramas as $cronograma): ?>
                            <tr data-id="<?= esc($cronograma['id_cronograma_capacitacion']) ?>">
                                <td class="details-control"><i class="bi bi-plus-circle"></i></td>
                                <td><?= esc($cronograma['id_cronograma_capacitacion']) ?></td>
                                <td data-field="nombre_capacitacion" data-bs-toggle="tooltip" title="<?= esc($cronograma['nombre_capacitacion']); ?>">
                                    <?= esc($cronograma['nombre_capacitacion']) ?>
                                </td>
                                <td data-field="objetivo_capacitacion" data-bs-toggle="tooltip" title="<?= esc($cronograma['objetivo_capacitacion']); ?>">
                                    <?= esc($cronograma['objetivo_capacitacion']) ?>
                                </td>
                                <td data-field="nombre_cliente" data-bs-toggle="tooltip" title="<?= esc($cronograma['nombre_cliente']); ?>">
                                    <?= esc($cronograma['nombre_cliente']) ?>
                                </td>
                                <td class="editable-date" data-field="fecha_programada" data-bs-toggle="tooltip" title="<?= esc($cronograma['fecha_programada']); ?>">
                                    <?= esc($cronograma['fecha_programada']) ?>
                                </td>
                                <td class="editable-date" data-field="fecha_de_realizacion" data-bs-toggle="tooltip" title="<?= esc($cronograma['fecha_de_realizacion']); ?>">
                                    <?= esc($cronograma['fecha_de_realizacion']) ?>
                                </td>
                                <td class="editable-select" data-field="estado" data-bs-toggle="tooltip" title="<?= esc($cronograma['estado']); ?>">
                                    <?= esc($cronograma['estado']) ?>
                                </td>
                                <td class="editable-select" data-field="perfil_de_asistentes" data-bs-toggle="tooltip" title="<?= esc($cronograma['perfil_de_asistentes']); ?>">
                                    <?= esc($cronograma['perfil_de_asistentes']) ?>
                                </td>
                                <td class="editable" data-field="nombre_del_capacitador" data-bs-toggle="tooltip" title="<?= esc($cronograma['nombre_del_capacitador']); ?>">
                                    <?= esc($cronograma['nombre_del_capacitador']) ?>
                                </td>
                                <td class="editable" data-field="horas_de_duracion_de_la_capacitacion" data-bs-toggle="tooltip" title="<?= esc($cronograma['horas_de_duracion_de_la_capacitacion']); ?>">
                                    <?= esc($cronograma['horas_de_duracion_de_la_capacitacion']) ?>
                                </td>
                                <td class="editable-select" data-field="indicador_de_realizacion_de_la_capacitacion" data-bs-toggle="tooltip" title="<?= esc($cronograma['indicador_de_realizacion_de_la_capacitacion']); ?>">
                                    <?= esc($cronograma['indicador_de_realizacion_de_la_capacitacion']) ?>
                                </td>
                                <td class="editable" data-field="numero_de_asistentes_a_capacitacion" data-bs-toggle="tooltip" title="<?= esc($cronograma['numero_de_asistentes_a_capacitacion']); ?>">
                                    <?= esc($cronograma['numero_de_asistentes_a_capacitacion']) ?>
                                </td>
                                <td class="editable" data-field="numero_total_de_personas_programadas" data-bs-toggle="tooltip" title="<?= esc($cronograma['numero_total_de_personas_programadas']); ?>">
                                    <?= esc($cronograma['numero_total_de_personas_programadas']) ?>
                                </td>
                                <td data-bs-toggle="tooltip" title="<?= esc($cronograma['porcentaje_cobertura']); ?>">
                                    <?= esc($cronograma['porcentaje_cobertura']) ?>
                                </td>
                                <td class="editable" data-field="numero_de_personas_evaluadas" data-bs-toggle="tooltip" title="<?= esc($cronograma['numero_de_personas_evaluadas']); ?>">
                                    <?= esc($cronograma['numero_de_personas_evaluadas']) ?>
                                </td>
                                <td class="editable" data-field="promedio_de_calificaciones" data-bs-toggle="tooltip" title="<?= esc($cronograma['promedio_de_calificaciones']); ?>">
                                    <?= esc($cronograma['promedio_de_calificaciones']) ?>%
                                </td>
                                <td class="editable" data-field="observaciones" data-bs-toggle="tooltip" title="<?= esc($cronograma['observaciones']); ?>">
                                    <?= esc($cronograma['observaciones']) ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?= base_url('/editcronogCapacitacion/' . esc($cronograma['id_cronograma_capacitacion'])) ?>" class="btn btn-warning btn-sm">Editar</a>
                                    <a href="<?= base_url('/deletecronogCapacitacion/' . esc($cronograma['id_cronograma_capacitacion'])) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este cronograma?');">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="19" class="text-center">No hay cronogramas de capacitación registrados</td>
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
                Sitio oficial:
                <a href="https://cycloidtalent.com/" target="_blank" class="text-primary text-decoration-none">https://cycloidtalent.com/</a>
            </p>
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
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
    <!-- DataTables Buttons JS -->
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.colVis.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.html5.min.js"></script>

    <script>
        function format(rowData) {
            var table = '<table class="table table-sm table-borderless">';
            // Obtenemos los headers del thead (tomando la primera fila de encabezados)
            var headers = $('#cronogramaTable thead tr').first().find('th');

            // Iteramos sobre los datos, comenzando en el índice 1 (omitiendo el control) y hasta el penúltimo (omitiendo la columna de acciones)
            for (var i = 1; i < rowData.length - 1; i++) {
                // Obtenemos el texto del header correspondiente
                var headerText = $(headers[i]).text().trim();
                // Construimos una fila de la tabla de detalles
                table += '<tr><td><strong>' + headerText + ':</strong></td><td>' + rowData[i] + '</td></tr>';
            }
            table += '</table>';
            return table;
        }

        $(document).ready(function() {
            var table = $('#cronogramaTable').DataTable({
                dom: 'Bfrtip',
                buttons: [{
                        extend: 'excelHtml5',
                        title: 'Cronogramas de Capacitación',
                        text: '<i class="bi bi-file-earmark-excel"></i> Exportar a Excel',
                        className: 'btn btn-success btn-sm',
                        exportOptions: {
                            columns: ':not(:first-child)' // Se excluye la columna de control
                        }
                    },
                    'colvis'
                ],
                order: [
                    [1, 'asc']
                ],
                columnDefs: [{
                    targets: 0,
                    orderable: false,
                    searchable: false,
                    className: 'details-control'
                }]
            });

            // Buscador global
            $('#globalSearch').on('keyup', function() {
                table.search(this.value).draw();
            });

            // Buscador por columna (si los filtros están en el thead o tfoot según convenga)
            $('.column-search').on('keyup change', function() {
                var index = $(this).parent().index();
                table.column(index).search(this.value).draw();
            });

            // Botón para restablecer filtros
            $('#clearState').click(function() {
                $('#globalSearch').val('');
                $('.column-search').each(function() {
                    $(this).val('');
                });
                table.search('').columns().search('').draw();
            });

            // Evento para expandir/contraer la fila
            $('#cronogramaTable tbody').on('click', 'td.details-control', function() {
                var tr = $(this).closest('tr');
                var row = table.row(tr);

                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                    $(this).html('<i class="bi bi-plus-circle"></i>');
                } else {
                    row.child(format(row.data())).show();
                    tr.addClass('shown');
                    $(this).html('<i class="bi bi-dash-circle"></i>');
                }
            });
        });

        // Edición en línea
        $(document).on('click', '.editable, .editable-date, .editable-select', function() {
            if ($(this).find('input, select').length) return;
            var cell = $(this);
            var field = cell.data('field');
            var id = cell.closest('tr').data('id');
            if (cell.hasClass('editable-date')) {
                var currentValue = cell.text().trim();
                var input = $('<input>', {
                    type: 'date',
                    class: 'form-control',
                    value: currentValue
                });
                cell.html(input);
                input.focus();
                input.on('blur change', function() {
                    var newValue = input.val();
                    if (newValue) {
                        cell.text(newValue);
                        updateField(id, field, newValue);
                    } else {
                        cell.text(currentValue);
                    }
                });
            } else if (cell.hasClass('editable-select')) {
                var currentValue = cell.text().trim();
                var options = [];
                if (field === 'estado') {
                    options = ['PROGRAMADA', 'EJECUTADA', 'CANCELADA POR EL CLIENTE', 'REPROGRAMADA'];
                } else if (field === 'perfil_de_asistentes') {
                    options = ['CONTRATISTAS', 'RESIDENTES', 'TODOS', 'ASAMBLEA', 'CONSEJO DE ADMINISTRACIÓN', 'ADMINISTRADOR'];
                } else if (field === 'indicador_de_realizacion_de_la_capacitacion') {
                    options = [
                        'SE EJECUTO EN LA FECHA O ANTES DE LA FECHA',
                        'SE EJECUTO DESPUES DE LA FECHA ACORDADA A CAUSA DEL CLIENTE',
                        'DECLINADA POR EL CLIENTE',
                        'NO HAY JUSTIFICACION PORQUE NO SE REALIZÓ',
                        'SE EJECUTO DESPUES DE LA FECHA POR CAUSA DEL CAPACITADOR'
                    ];
                }
                var select = $('<select>', {
                    class: 'form-select form-select-sm'
                });
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
                    setTimeout(function() {
                        var newValue = select.val();
                        if (newValue) {
                            cell.text(newValue);
                            updateField(id, field, newValue);
                        } else {
                            cell.text(currentValue);
                        }
                    }, 200);
                });
            } else {
                var currentValue = cell.text().trim();
                var input = $('<input>', {
                    type: 'text',
                    class: 'form-control',
                    value: currentValue
                });
                cell.html(input);
                input.focus();
                input.on('blur', function() {
                    var newValue = input.val();
                    if (newValue) {
                        cell.text(newValue);
                        updateField(id, field, newValue);
                    } else {
                        cell.text(currentValue);
                    }
                });
            }
        });

        function updateField(id, field, value) {
            $.ajax({
                url: '<?= base_url('/updatecronogCapacitacion') ?>',
                method: 'POST',
                data: {
                    id: id,
                    field: field,
                    value: value
                },
                success: function(response) {
                    if (response.success) {
                        console.log('Registro actualizado correctamente');
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error al comunicarse con el servidor:', error);
                    console.error('Detalles:', xhr.responseText);
                    alert('Error al comunicarse con el servidor: ' + error);
                }
            });
        }
    </script>
</body>

</html>