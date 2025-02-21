<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Plan de Trabajo Anual</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    <style>
        body {
            padding: 20px;
        }
        .dataTables_wrapper .dataTables_filter {
            float: right;
            text-align: right;
        }
        td.editable {
            cursor: pointer;
        }
    </style>
</head>
<body>


    <div class="container-fluid">
        <!-- Enlace a Dashboard -->
        <a href="<?= base_url('/dashboardconsultant') ?>" class="btn btn-primary btn-sm mb-3">Ir a DashBoard</a>
        <h1 class="mb-4">Plan de Trabajo Anual Cliente</h1>

        <!-- FORMULARIO DE FILTROS -->
        <form id="filterForm" method="get" action="<?= site_url('/pta-cliente-nueva/list') ?>">
            <div class="row mb-3">
                <!-- Seleccionar Cliente -->
                <div class="col-md-3">
                    <label for="cliente" class="form-label">Cliente</label>
                    <select name="cliente" id="cliente" class="form-select">
                        <option value="">Seleccione un Cliente</option>
                        <?php if (isset($clients) && !empty($clients)): ?>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?= esc($client['id_cliente']) ?>"
                                    <?= (service('request')->getGet('cliente') == $client['id_cliente']) ? 'selected' : '' ?>>
                                    <?= esc($client['nombre_cliente']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <!-- Rango de Fechas -->
                <div class="col-md-3">
                    <label for="fecha_desde" class="form-label">Fecha Desde</label>
                    <input type="date" name="fecha_desde" id="fecha_desde" class="form-control" value="<?= esc(service('request')->getGet('fecha_desde')) ?>">
                </div>
                <div class="col-md-3">
                    <label for="fecha_hasta" class="form-label">Fecha Hasta</label>
                    <input type="date" name="fecha_hasta" id="fecha_hasta" class="form-control" value="<?= esc(service('request')->getGet('fecha_hasta')) ?>">
                </div>
                <!-- Estado de Actividad -->
                <div class="col-md-3">
                    <label for="estado" class="form-label">Estado de Actividad</label>
                    <select name="estado" id="estado" class="form-select">
                        <option value="">Todas</option>
                        <option value="ABIERTA" <?= (service('request')->getGet('estado') == 'ABIERTA') ? 'selected' : '' ?>>ABIERTA</option>
                        <option value="CERRADA" <?= (service('request')->getGet('estado') == 'CERRADA') ? 'selected' : '' ?>>CERRADA</option>
                    </select>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary" id="btnBuscar">Buscar</button>
                    <button type="reset" id="resetFilters" class="btn btn-secondary">Resetear Filtros</button>
                    <!-- Botón para descargar Excel (visible solo si hay registros) -->
                    <?php if (!empty($records)): ?>
                        <a href="<?= site_url('pta-cliente-nueva/excel') . '?' . http_build_query($filters) ?>" class="btn btn-success">Descargar Excel</a>
                    <?php endif; ?>
                    <!-- Botón para Añadir Registro con filtros en la URL -->
                    <a href="<?= base_url('/pta-cliente-nueva/add?' . http_build_query($filters)) ?>" class="btn btn-info">Añadir Registro</a>
                </div>
            </div>
        </form>

        <!-- Mostrar la tabla solo si existen registros -->
        <?php if (!empty($records)): ?>
            <div class="table-responsive">
                <table id="ptaTable" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>Acciones</th>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th class="d-none">Tipo Servicio</th>
                            <th>PHVA</th>
                            <th>Numeral Plan Trabajo</th>
                            <th>Actividad</th>
                            <th>Responsable Sugerido</th>
                            <th>Fecha Propuesta</th>
                            <th>Fecha Cierre</th>
                            <th>Estado Actividad</th>
                            <th>Porcentaje Avance</th>
                            <th>Observaciones</th>
                            <th class="d-none">Responsable Definido</th>
                            <th class="d-none">Semana</th>
                            <th class="d-none">Created At</th>
                            <th class="d-none">Updated At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $row): ?>
                            <tr>
                                <td>
                                    <!-- Se incluyen los filtros en los enlaces de editar y eliminar -->
                                    <a href="<?= base_url('/pta-cliente-nueva/edit/' . esc($row['id_ptacliente']) . '?' . http_build_query($filters)) ?>" class="btn btn-warning btn-sm">Editar</a>
                                    <a href="<?= base_url('/pta-cliente-nueva/delete/' . esc($row['id_ptacliente']) . '?' . http_build_query($filters)) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que deseas eliminar este registro?')">Eliminar</a>
                                </td>
                                <td><?= esc($row['id_ptacliente']) ?></td>
                                <td class="editable"><?= esc($row['nombre_cliente']) ?></td>
                                <td class="d-none"><?= esc($row['tipo_servicio']) ?></td>
                                <td class="editable"><?= esc($row['phva_plandetrabajo']) ?></td>
                                <td class="editable"><?= esc($row['numeral_plandetrabajo']) ?></td>
                                <td class="editable"><?= esc($row['actividad_plandetrabajo']) ?></td>
                                <td class="editable"><?= esc($row['responsable_sugerido_plandetrabajo']) ?></td>
                                <td class="editable"><?= esc($row['fecha_propuesta']) ?></td>
                                <td class="editable"><?= esc($row['fecha_cierre']) ?></td>
                                <td class="editable"><?= esc($row['estado_actividad']) ?></td>
                                <td class="editable"><?= esc($row['porcentaje_avance']) ?></td>
                                <td class="editable"><?= esc($row['observaciones']) ?></td>
                                <td class="d-none"><?= esc($row['responsable_definido_paralaactividad']) ?></td>
                                <td class="d-none"><?= esc($row['semana']) ?></td>
                                <td class="d-none"><?= esc($row['created_at']) ?></td>
                                <td class="d-none"><?= esc($row['updated_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Acciones</th>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th class="d-none">Tipo Servicio</th>
                            <th>PHVA</th>
                            <th>Numeral Plan Trabajo</th>
                            <th>Actividad</th>
                            <th>Responsable Sugerido</th>
                            <th>Fecha Propuesta</th>
                            <th>Fecha Cierre</th>
                            <th>Estado Actividad</th>
                            <th>Porcentaje Avance</th>
                            <th>Observaciones</th>
                            <th class="d-none">Responsable Definido</th>
                            <th class="d-none">Semana</th>
                            <th class="d-none">Created At</th>
                            <th class="d-none">Updated At</th>
                        </tr>
                    </tfoot>
                </table>
                <?= isset($pager) ? $pager->links() : '' ?>
            </div>
        <?php endif; ?>

        <!-- Mensajes flash -->
        <?php if (session()->has('message')): ?>
            <div class="alert alert-success mt-3"><?= session('message') ?></div>
        <?php endif; ?>
        <?php if (session()->has('error')): ?>
            <div class="alert alert-danger mt-3"><?= session('error') ?></div>
        <?php endif; ?>
        <?php if (session()->has('warning')): ?>
            <div class="alert alert-warning mt-3">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?= session('warning') ?>
            </div>
        <?php endif; ?>
        <?php if (session()->has('info')): ?>
            <div class="alert alert-info mt-3">
                <i class="fas fa-info-circle me-2"></i>
                <?= session('info') ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- jQuery, Bootstrap 5 y DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2 on client dropdown
            $('#cliente').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Buscar o seleccionar cliente...',
                allowClear: true,
                minimumInputLength: 0,
                language: {
                    noResults: function() {
                        return "No se encontraron resultados";
                    },
                    searching: function() {
                        return "Buscando...";
                    }
                }
            });

            $('#filterForm').on('submit', function(e) {
                var cliente = $('#cliente').val();
                var fechaDesde = $('#fecha_desde').val();
                var fechaHasta = $('#fecha_hasta').val();
                if (!cliente) {
                    alert('Debe seleccionar un Cliente.');
                    e.preventDefault();
                    return false;
                }
                if (!fechaDesde || !fechaHasta) {
                    alert('Debe seleccionar el rango de fechas (Fecha Desde y Fecha Hasta).');
                    e.preventDefault();
                    return false;
                }
            });

            if ($('#ptaTable').length) {
                $('#ptaTable tfoot th').each(function() {
                    var title = $(this).text();
                    if (title === 'Cliente' || title === 'Actividad') {
                        $(this).html('<input type="text" placeholder="Buscar ' + title + '" class="form-control form-control-sm" />');
                    } else if (title !== '') {
                        $(this).html('<select class="form-select form-select-sm"><option value="">Todos</option></select>');
                    }
                });

                var table = $('#ptaTable').DataTable({
                    "lengthChange": true,
                    "responsive": true,
                    "autoWidth": false,
                    "order": [],
                    "initComplete": function() {
                        this.api().columns().every(function() {
                            var column = this;
                            var select = $('select', column.footer());
                            var input = $('input', column.footer());
                            if (select.length) {
                                column.data().unique().sort().each(function(d) {
                                    if (d) {
                                        select.append('<option value="' + d + '">' + d + '</option>');
                                    }
                                });
                                select.on('change', function() {
                                    var val = $.fn.dataTable.util.escapeRegex($(this).val());
                                    column.search(val ? '^' + val + '$' : '', true, false).draw();
                                });
                            }
                            if (input.length) {
                                input.on('keyup change clear', function() {
                                    if (column.search() !== this.value) {
                                        column.search(this.value).draw();
                                    }
                                });
                            }
                        });
                    }
                });

                $('#ptaTable tbody').on('dblclick', 'td.editable', function() {
                    var cell = table.cell(this);
                    var originalValue = cell.data();
                    var $td = $(this);
                    if ($td.find('input, select').length > 0) return;
                    var colIndex = table.cell($td).index().column;
                    var editableMapping = {
                        4: 'phva_plandetrabajo',
                        5: 'numeral_plandetrabajo',
                        6: 'actividad_plandetrabajo',
                        7: 'responsable_sugerido_plandetrabajo',
                        8: 'fecha_propuesta',
                        9: 'fecha_cierre',
                        10: 'estado_actividad',
                        11: 'porcentaje_avance',
                        12: 'observaciones'
                    };
                    var disallowed = [0, 1, 2, 3, 13, 14, 15, 16];
                    if (disallowed.indexOf(colIndex) !== -1 || !editableMapping.hasOwnProperty(colIndex)) {
                        cell.data(originalValue).draw();
                        return;
                    }
                    
                    var inputElement;
                    if (colIndex === 8 || colIndex === 9) {
                        inputElement = $('<input type="date" class="form-control form-control-sm" />').val(originalValue);
                    } else if (colIndex === 10) {
                        inputElement = $('<select class="form-select form-select-sm"></select>');
                        var options = ["ABIERTA", "CERRADA", "GESTIONANDO"];
                        $.each(options, function(i, option) {
                            var selected = (originalValue === option) ? "selected" : "";
                            inputElement.append('<option value="' + option + '" ' + selected + '>' + option + '</option>');
                        });
                    } else {
                        inputElement = $('<input type="text" class="form-control form-control-sm" />').val(originalValue);
                    }
                    
                    $td.empty().append(inputElement);
                    inputElement.focus();
                    
                    inputElement.on('blur keydown', function(e) {
                        if (e.type === 'blur' || (e.type === 'keydown' && e.which === 13)) {
                            var newValue = (colIndex === 10) ? inputElement.find("option:selected").val() : $(this).val();
                            if (newValue === originalValue) {
                                cell.data(originalValue).draw();
                                return;
                            }
                            var fieldName = editableMapping[colIndex];
                            var rowData = table.row($td.closest('tr')).data();
                            var id = rowData[1];
                            var dataToSend = { id: id };
                            dataToSend[fieldName] = newValue;
                            dataToSend["<?= csrf_token() ?>"] = "<?= csrf_hash() ?>";
                            
                            $.ajax({
                                url: "<?= site_url('/pta-cliente-nueva/editinginline') ?>",
                                method: "POST",
                                data: dataToSend,
                                dataType: "json",
                                success: function(response) {
                                    if (response.status === 'success') {
                                        cell.data(newValue).draw();
                                    } else {
                                        alert('Error: ' + response.message);
                                        cell.data(originalValue).draw();
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error("AJAX error:", status, error);
                                    alert('Error en la comunicación con el servidor.');
                                    cell.data(originalValue).draw();
                                }
                            });
                        }
                    });
                });
            }

            $('#resetFilters').click(function() {
                $('#filterForm')[0].reset();
                window.location.href = "<?= site_url('/pta-cliente-nueva/list') ?>";
            });
        });
    </script>
</body>
</html>
