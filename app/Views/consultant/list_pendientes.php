<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Pendientes</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- DataTables CSS y Buttons -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.3/css/buttons.bootstrap4.min.css">
    <style>
        td,
        th {
            max-width: 100px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        td[title],
        th[title] {
            cursor: help;
        }

        .tooltip-inner {
            max-width: 300px;
            white-space: normal;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.html5.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</head>

<body>


<nav style="background-color: white; position: fixed; top: 0; width: 100%; z-index: 1000; padding: 10px 0; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; max-width: 1200px; margin: 0 auto;">

            <!-- Logo izquierdo -->
            <div>
                <a href="https://dashboard.cycloidtalent.com/login">
                    <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo" style="height: 100px;">
                </a>
            </div>

            <!-- Logo centro -->
            <div>
                <a href="https://cycloidtalent.com/index.php/consultoria-sst">
                    <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo" style="height: 100px;">
                </a>
            </div>

            <!-- Logo derecho -->
            <div>
                <a href="https://cycloidtalent.com/">
                    <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo" style="height: 100px;">
                </a>
            </div>

        </div>

        <!-- Fila de botones -->
        <div style="display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 10px auto 0; padding: 0 20px;">
            <!-- Botón izquierdo -->
            <div style="text-align: center;">
                <h2 style="margin: 0; font-size: 16px;">Ir a Dashboard</h2>
                <a href="<?= base_url('/dashboardconsultant') ?>" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; font-size: 14px; margin-top: 5px;">Ir a DashBoard</a>
            </div>

            <!-- Botón derecho -->
            <div style="text-align: center;">
                <h2 style="margin: 0; font-size: 16px;">Añadir Registro</h2>
                <a href="<?= base_url('/addPendiente') ?>" style="display: inline-block; padding: 10px 20px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px; font-size: 14px; margin-top: 5px;" target="_blank">Añadir Registro</a>
            </div>
        </div>
    </nav>

    <!-- Ajustar el espaciado para evitar que el contenido se oculte bajo el navbar fijo -->
    <div style="height: 160px;"></div>

    
    <div class="container-fluid mt-5">
        <h2 class="text-center mb-4">Lista de Pendientes</h2>
        <a href="<?= base_url('/addPendiente') ?>" class="btn btn-primary mb-3">Añadir Nuevo Pendiente</a>
        <button id="resetFilters" class="btn btn-secondary mb-3">Restablecer filtros</button>
        <table id="pendientesTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Fecha Creación</th>
                    <th>Responsable</th>
                    <th>*Tarea Actividad</th>
                    <th>*Fecha Cierre</th>
                    <th>*Estado</th>
                    <th>Conteo Días</th>
                    <th>*Estado Avance</th>
                    <th>*Evidencia para Cerrarla</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($pendientes)) : ?>
                    <?php foreach ($pendientes as $pendiente) : ?>
                        <tr>
                            <td title="<?= $pendiente['id_pendientes'] ?>"><?= $pendiente['id_pendientes'] ?></td>
                            <td title="<?= $pendiente['nombre_cliente'] ?>"><?= $pendiente['nombre_cliente'] ?></td>
                            <td title="<?= $pendiente['created_at'] ?>"><?= $pendiente['created_at'] ?></td>
                            <td title="<?= $pendiente['responsable'] ?>"><?= $pendiente['responsable'] ?></td>
                            <td class="editable" data-field="tarea_actividad" data-id="<?= $pendiente['id_pendientes'] ?>" title="<?= $pendiente['tarea_actividad'] ?>">
                                <?= $pendiente['tarea_actividad'] ?>
                            </td>
                            <td class="editable-date" data-field="fecha_cierre" data-id="<?= $pendiente['id_pendientes'] ?>" title="<?= $pendiente['fecha_cierre'] ?>">
                                <?= $pendiente['fecha_cierre'] ?>
                            </td>
                            <td class="editable-select" data-field="estado" data-id="<?= $pendiente['id_pendientes'] ?>" title="<?= $pendiente['estado'] ?>">
                                <?= $pendiente['estado'] ?>
                            </td>
                            <td title="<?= $pendiente['conteo_dias'] ?>"><?= $pendiente['conteo_dias'] ?></td>
                            <td class="editable" data-field="estado_avance" data-id="<?= $pendiente['id_pendientes'] ?>" title="<?= $pendiente['estado_avance'] ?>">
                                <?= $pendiente['estado_avance'] ?>
                            </td>
                            <td class="editable" data-field="evidencia_para_cerrarla" data-id="<?= $pendiente['id_pendientes'] ?>" title="<?= $pendiente['evidencia_para_cerrarla'] ?>">
                                <?= $pendiente['evidencia_para_cerrarla'] ?>
                            </td>
                            <td>
                                <a href="<?= base_url('/editPendiente/' . $pendiente['id_pendientes']) ?>" class="btn btn-warning btn-sm">Editar</a>
                                <a href="<?= base_url('/deletePendiente/' . $pendiente['id_pendientes']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este pendiente?')">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="11" class="text-center">No se encontraron pendientes.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <!-- Para cada columna, excepto "Acciones", se coloca un select para el filtro -->
                    <th>
                        <select class="form-control form-control-sm filter-select">
                            <option value="">Todos</option>
                        </select>
                    </th>
                    <th>
                        <select class="form-control form-control-sm filter-select">
                            <option value="">Todos</option>
                        </select>
                    </th>
                    <th>
                        <select class="form-control form-control-sm filter-select">
                            <option value="">Todos</option>
                        </select>
                    </th>
                    <th>
                        <select class="form-control form-control-sm filter-select">
                            <option value="">Todos</option>
                        </select>
                    </th>
                    <th>
                        <select class="form-control form-control-sm filter-select">
                            <option value="">Todos</option>
                        </select>
                    </th>
                    <th>
                        <select class="form-control form-control-sm filter-select">
                            <option value="">Todos</option>
                        </select>
                    </th>
                    <th>
                        <select class="form-control form-control-sm filter-select">
                            <option value="">Todos</option>
                        </select>
                    </th>
                    <th>
                        <select class="form-control form-control-sm filter-select">
                            <option value="">Todos</option>
                        </select>
                    </th>
                    <th>
                        <select class="form-control form-control-sm filter-select">
                            <option value="">Todos</option>
                        </select>
                    </th>
                    <th>
                        <select class="form-control form-control-sm filter-select">
                            <option value="">Todos</option>
                        </select>
                    </th>
                    <th>
                        <!-- No se aplica filtro en la columna de acciones -->
                    </th>
                </tr>
            </tfoot>
        </table>
    </div>

    <footer style="background-color: white; padding: 20px 0; border-top: 1px solid #B0BEC5; margin-top: 40px; color: #3A3F51; font-size: 14px; text-align: center;">
        <div style="max-width: 1200px; margin: 0 auto; display: flex; flex-direction: column; align-items: center;">
            <p style="margin: 0; font-weight: bold;">Cycloid Talent SAS</p>
            <p style="margin: 5px 0;">Todos los derechos reservados © 2024</p>
            <p style="margin: 5px 0;">NIT: 901.653.912</p>
            <p style="margin: 5px 0;">
                Sitio oficial: <a href="https://cycloidtalent.com/" target="_blank" style="color: #007BFF; text-decoration: none;">https://cycloidtalent.com/</a>
            </p>
            <p style="margin: 15px 0 5px;"><strong>Nuestras Redes Sociales:</strong></p>
            <div style="display: flex; gap: 15px; justify-content: center;">
                <a href="https://www.facebook.com/CycloidTalent" target="_blank" style="color: #3A3F51; text-decoration: none;">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" alt="Facebook" style="height: 24px; width: 24px;">
                </a>
                <a href="https://co.linkedin.com/company/cycloid-talent" target="_blank" style="color: #3A3F51; text-decoration: none;">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733561.png" alt="LinkedIn" style="height: 24px; width: 24px;">
                </a>
                <a href="https://www.instagram.com/cycloid_talent?igsh=Nmo4d2QwZDg5dHh0" target="_blank" style="color: #3A3F51; text-decoration: none;">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733558.png" alt="Instagram" style="height: 24px; width: 24px;">
                </a>
                <a href="https://www.tiktok.com/@cycloid_talent?_t=8qBSOu0o1ZN&_r=1" target="_blank" style="color: #3A3F51; text-decoration: none;">
                    <img src="https://cdn-icons-png.flaticon.com/512/3046/3046126.png" alt="TikTok" style="height: 24px; width: 24px;">
                </a>
            </div>
        </div>
    </footer>

    <!-- Inline Editing Script -->
    <script>
        $(document).on('click', '.editable, .editable-date, .editable-select', function() {
            if ($(this).find('input, select').length) return;

            var cell = $(this);
            var field = cell.data('field');
            var id = cell.data('id');
            var currentValue = cell.text().trim();

            if (field === 'fecha_cierre') {
                var input = $('<input>', {
                    type: 'date',
                    class: 'form-control',
                    value: currentValue
                });
                cell.html(input);
                input.focus();
                input.on('blur change', function() {
                    var newValue = input.val();
                    cell.text(newValue);
                    updatePendienteField(id, field, newValue);
                });
            } else if (field === 'estado') {
                var options = ['ABIERTA', 'CERRADA'];
                var select = $('<select>', {
                    class: 'form-control form-control-sm'
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
                    var newValue = select.val();
                    cell.text(newValue);
                    updatePendienteField(id, field, newValue);
                });
            } else if (field === 'evidencia_para_cerrarla') {
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
                    updatePendienteField(id, field, newValue);
                });
            } else {
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
                    updatePendienteField(id, field, newValue);
                });
            }
        });

        function updatePendienteField(id, field, value) {
            $.ajax({
                url: '<?= base_url('/updatePendiente') ?>',
                method: 'POST',
                data: {
                    id: id,
                    field: field,
                    value: value
                },
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
    </script>

<script>
        $(document).ready(function() {
            var table = $('#pendientesTable').DataTable({
                stateSave: true,
                dom: 'Bfltip',
                pageLength: 10,
                buttons: [{
                    extend: 'excelHtml5',
                    text: 'Exportar a Excel',
                    className: 'btn btn-success btn-sm'
                }],
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.11.3/i18n/es-ES.json"
                },
                initComplete: function() {
                    var api = this.api();
                    api.columns().every(function() {
                        var column = this;
                        // Buscar el select en el footer de cada columna
                        var select = $(column.footer()).find('select.filter-select');
                        if (select.length) {
                            column.data().unique().sort().each(function(d) {
                                if (d) {
                                    select.append('<option value="' + d + '">' + d + '</option>');
                                }
                            });
                        }
                    });
                }
            });

            $('.filter-select').on('change', function() {
                var columnIndex = $(this).parent().index();
                table.column(columnIndex).search($(this).val()).draw();
            });

            // Evento para el botón "Restablecer filtros"
            $('#resetFilters').on('click', function() {
                // Restablece todos los selects
                $('.filter-select').each(function() {
                    $(this).val('');
                });
                // Borra todas las búsquedas de columnas
                table.columns().search('');
                // Borra la búsqueda global
                table.search('');
                // Redibuja la tabla con filtros reseteados
                table.draw();
            });

            $('body').tooltip({
                selector: '[title]',
                placement: 'top',
                trigger: 'hover'
            });
        });
    </script>
</body>

</html>