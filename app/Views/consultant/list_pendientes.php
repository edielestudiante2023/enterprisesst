<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Pendientes</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap4.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
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


    <div class="container mt-5">
        <h2 class="text-center mb-4">Lista de Pendientes</h2>
        <a href="<?= base_url('/addPendiente') ?>" class="btn btn-primary mb-3">Añadir Nuevo Pendiente</a>
        <table id="pendientesTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Fecha Creación</th>
                    <th>Responsable</th>
                    <th>Tarea Actividad</th>
                    <th>Fecha Cierre</th>
                    <th>Estado</th>
                    <th>Conteo Días</th>
                    <th>Estado Avance</th>
                    <th>Evidencia para Cerrarla</th>
                    <th>Acciones</th>
                </tr>
                <tr>
                    <th></th>
                    <th><select class="form-control form-control-sm filter-select">
                            <option value="">Todos</option>
                        </select></th>
                    <th></th>
                    <th><select class="form-control form-control-sm filter-select">
                            <option value="">Todos</option>
                        </select></th>
                    <th></th>
                    <th></th>
                    <th><select class="form-control form-control-sm filter-select">
                            <option value="">Todos</option>
                        </select></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
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
                            <td title="<?= $pendiente['tarea_actividad'] ?>"><?= $pendiente['tarea_actividad'] ?></td>
                            <td title="<?= $pendiente['fecha_cierre'] ?>"><?= $pendiente['fecha_cierre'] ?></td>
                            <td title="<?= $pendiente['estado'] ?>"><?= $pendiente['estado'] ?></td>
                            <td title="<?= $pendiente['conteo_dias'] ?>"><?= $pendiente['conteo_dias'] ?></td>
                            <td title="<?= $pendiente['estado_avance'] ?>"><?= $pendiente['estado_avance'] ?></td>
                            <td title="<?= $pendiente['evidencia_para_cerrarla'] ?>"><?= $pendiente['evidencia_para_cerrarla'] ?></td>
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
        </table>
    </div>


    <footer style="background-color: white; padding: 20px 0; border-top: 1px solid #B0BEC5; margin-top: 40px; color: #3A3F51; font-size: 14px; text-align: center;">
        <div style="max-width: 1200px; margin: 0 auto; display: flex; flex-direction: column; align-items: center;">
            <!-- Company and Rights -->
            <p style="margin: 0; font-weight: bold;">Cycloid Talent SAS</p>
            <p style="margin: 5px 0;">Todos los derechos reservados © 2024</p>
            <p style="margin: 5px 0;">NIT: 901.653.912</p>

            <!-- Website Link -->
            <p style="margin: 5px 0;">
                Sitio oficial: <a href="https://cycloidtalent.com/" target="_blank" style="color: #007BFF; text-decoration: none;">https://cycloidtalent.com/</a>
            </p>

            <!-- Social Media Links -->
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


    <script>
        $(document).ready(function() {
            var table = $('#pendientesTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.3/i18n/es-ES.json"
                },
                "paging": true,
                "responsive": true,
                "autoWidth": false,
                "initComplete": function() {
                    // Llenar selectores de filtros en las columnas Cliente, Responsable y Estado
                    this.api().columns([1, 3, 6]).every(function() {
                        var column = this;
                        var select = $(column.header()).find('select');
                        column.data().unique().sort().each(function(d, j) {
                            select.append('<option value="' + d + '">' + d + '</option>');
                        });
                    });
                }
            });

            // Aplicar los filtros de búsqueda al cambiar el valor de los selectores
            $('.filter-select').on('change', function() {
                var column = $(this).parent().index();
                table.column(column).search($(this).val()).draw();
            });

            // Inicializar tooltips de Bootstrap después de que la tabla se haya renderizado
            $('body').tooltip({
                selector: '[title]',
                placement: 'top',
                trigger: 'hover'
            });
        });
    </script>
</body>

</html>