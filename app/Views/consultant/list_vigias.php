<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Vigías</title>
    <!-- CSS de Bootstrap -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- CSS de DataTables -->
    <link href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <!-- CSS de Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }

        h2 {
            color: #333;
            margin-top: 20px;
        }

        table {
            background-color: #fff;
        }

        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #ced4da;
            padding: 5px;
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
                <a href="<?= base_url('/addVigia') ?>" style="display: inline-block; padding: 10px 20px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px; font-size: 14px; margin-top: 5px;" target="_blank">Añadir Registro</a>
            </div>
        </div>
    </nav>

    <!-- Ajustar el espaciado para evitar que el contenido se oculte bajo el navbar fijo -->
    <div style="height: 160px;"></div>


    <div class="container-fluid px-3">
        <h2 class="text-center mb-4">Lista de Vigías</h2>

        <!-- Filtro de Cliente -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="clienteFilter" class="form-label">Filtrar por Cliente:</label>
                <select id="clienteFilter" class="form-control">
                    <option value="">Todos los clientes</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?= $cliente['nombre_cliente'] ?>"><?= $cliente['nombre_cliente'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <table id="vigiaTable" class="table table-bordered table-hover w-100">
            <thead class="thead-light">
                <tr>
                    <th>Nombre del Vigía</th>
                    <th>Cédula</th>
                    <th>Período</th>
                    <th>Firma</th>
                    <th>Cliente</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vigias as $vigia): ?>
                    <tr>
                        <td><?= $vigia['nombre_vigia'] ?></td>
                        <td><?= $vigia['cedula_vigia'] ?></td>
                        <td><?= $vigia['periodo_texto'] ?></td>
                        <td>
                            <img src="<?= base_url('uploads/' . $vigia['firma_vigia']) ?>" alt="Firma del Vigía" style="max-width: 100px;">
                        </td>
                        <td>
                            <?php
                            // Obtener el nombre del cliente según el id_cliente
                            foreach ($clientes as $cliente) {
                                if ($cliente['id_cliente'] == $vigia['id_cliente']) {
                                    echo $cliente['nombre_cliente'];
                                    break;
                                }
                            }
                            ?>
                        </td>
                        <td>
                            <a href="<?= base_url('editVigia/' . $vigia['id_vigia']) ?>" class="btn btn-sm btn-primary">Editar</a>
                            <a href="<?= base_url('deleteVigia/' . $vigia['id_vigia']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de eliminar este vigía?');">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th><input type="text" class="form-control" placeholder="Buscar nombre..." /></th>
                    <th><input type="text" class="form-control" placeholder="Buscar cédula..." /></th>
                    <th><input type="text" class="form-control" placeholder="Buscar período..." /></th>
                    <th></th>
                    <th><input type="text" class="form-control" placeholder="Buscar cliente..." /></th>
                    <th></th>
                </tr>
            </tfoot>
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


    <!-- JS de jQuery, Bootstrap y DataTables -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <!-- JS de Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            var table = $('#vigiaTable').DataTable({
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json"
                },
                initComplete: function () {
                    // Aplicar filtros de búsqueda en cada columna del tfoot
                    this.api().columns().every(function (index) {
                        var that = this;
                        // Excluir la columna de Firma (índice 3) y Acciones (índice 5)
                        if (index !== 3 && index !== 5) {
                            $('input', this.footer()).on('keyup change clear', function () {
                                if (that.search() !== this.value) {
                                    that.search(this.value).draw();
                                }
                            });
                        }
                    });
                }
            });

            // Inicializar Select2 para el filtro de clientes
            $('#clienteFilter').select2({
                theme: 'bootstrap4',
                placeholder: 'Seleccionar cliente...',
                allowClear: true,
                width: '100%'
            });

            // Filtro por cliente
            $('#clienteFilter').on('change', function() {
                var selectedClient = this.value;
                if (selectedClient) {
                    table.column(4).search('^' + selectedClient + '$', true, false).draw();
                } else {
                    table.column(4).search('').draw();
                }
            });
        });
    </script>

</body>

</html>