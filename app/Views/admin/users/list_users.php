<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.7.0/css/buttons.dataTables.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            color: #343a40;
        }

        .container {
            margin-top: 80px;
        }

        table {
            background-color: #ffffff;
        }

        table.dataTable tbody tr {
            height: 60px;
        }

        table.dataTable th,
        table.dataTable td {
            white-space: nowrap;
        }

        .badge-activo {
            background-color: #28a745;
            color: white;
        }

        .badge-inactivo {
            background-color: #dc3545;
            color: white;
        }

        .badge-pendiente {
            background-color: #ffc107;
            color: black;
        }

        .badge-bloqueado {
            background-color: #6c757d;
            color: white;
        }

        .badge-admin {
            background-color: #007bff;
            color: white;
        }

        .badge-consultant {
            background-color: #17a2b8;
            color: white;
        }

        .badge-client {
            background-color: #6f42c1;
            color: white;
        }

        @media (max-width: 768px) {
            table.dataTable thead th,
            table.dataTable tbody td {
                padding: 8px;
            }
        }

        .dt-buttons .btn {
            margin-right: 0px;
        }

        .filters-row th {
            background-color: #e9ecef;
            padding: 8px !important;
        }

        .filters-row input,
        .filters-row select {
            width: 100%;
            font-size: 12px;
        }
    </style>
</head>

<body>

    <!-- Navbar Fijo -->
    <nav style="background-color: white; position: fixed; top: 0; width: 100%; z-index: 1000; padding: 10px 0; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; max-width: 1200px; margin: 0 auto;">
            <div>
                <a href="https://dashboard.cycloidtalent.com/login">
                    <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo" style="height: 100px;">
                </a>
            </div>
            <div>
                <a href="https://cycloidtalent.com/index.php/consultoria-sst">
                    <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo" style="height: 100px;">
                </a>
            </div>
            <div>
                <a href="https://cycloidtalent.com/">
                    <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo" style="height: 100px;">
                </a>
            </div>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 10px auto 0; padding: 0 20px;">
            <div style="text-align: center;">
                <h2 style="margin: 0; font-size: 16px;">Ir a Dashboard</h2>
                <a href="<?= base_url('/admin/dashboard') ?>" class="btn btn-primary btn-sm mt-2">Ir a Dashboard</a>
            </div>
            <div style="text-align: center;">
                <h2 style="margin: 0; font-size: 16px;">Agregar Usuario</h2>
                <a href="<?= base_url('/admin/users/add') ?>" class="btn btn-success btn-sm mt-2">Agregar Usuario</a>
            </div>
        </div>
    </nav>

    <div style="height: 200px;"></div>

    <div class="container-fluid">
        <?php if (session()->getFlashdata('msg')): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <?= session()->getFlashdata('msg') ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <h2 class="mb-4">Gestión de Usuarios del Sistema</h2>

        <div class="mb-3">
            <button id="clearState" class="btn btn-warning">Restablecer Filtros</button>
        </div>

        <div class="table-responsive">
            <table id="usersTable" class="table table-striped table-bordered nowrap" style="width:100%">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Último Login</th>
                        <th>Creado</th>
                        <th>Acciones</th>
                    </tr>
                    <tr class="filters-row">
                        <th><input type="text" class="form-control form-control-sm" placeholder="Filtrar ID"></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Filtrar Nombre"></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Filtrar Email"></th>
                        <th>
                            <select class="form-control form-control-sm">
                                <option value="">Todos</option>
                                <option value="Admin">Admin</option>
                                <option value="Consultant">Consultant</option>
                                <option value="Client">Client</option>
                            </select>
                        </th>
                        <th>
                            <select class="form-control form-control-sm">
                                <option value="">Todos</option>
                                <option value="Activo">Activo</option>
                                <option value="Inactivo">Inactivo</option>
                                <option value="Pendiente">Pendiente</option>
                                <option value="Bloqueado">Bloqueado</option>
                            </select>
                        </th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Filtrar"></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Filtrar"></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['id_usuario']) ?></td>
                            <td><?= htmlspecialchars($user['nombre_completo']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <span class="badge badge-<?= $user['tipo_usuario'] ?>">
                                    <?= ucfirst($user['tipo_usuario']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?= $user['estado'] ?>">
                                    <?= ucfirst($user['estado']) ?>
                                </span>
                            </td>
                            <td>
                                <?= $user['ultimo_login'] ? date('d/m/Y H:i', strtotime($user['ultimo_login'])) : 'Nunca' ?>
                            </td>
                            <td>
                                <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                            </td>
                            <td>
                                <a href="<?= base_url('/admin/users/edit/' . $user['id_usuario']) ?>" class="btn btn-sm btn-warning" title="Editar">
                                    Editar
                                </a>
                                <a href="<?= base_url('/admin/users/toggle/' . $user['id_usuario']) ?>" class="btn btn-sm btn-info" title="Cambiar Estado">
                                    <?= $user['estado'] === 'activo' ? 'Desactivar' : 'Activar' ?>
                                </a>
                                <a href="<?= base_url('/admin/users/reset-password/' . $user['id_usuario']) ?>" class="btn btn-sm btn-secondary" onclick="return confirm('¿Resetear contraseña de este usuario?')" title="Resetear Contraseña">
                                    Reset Pass
                                </a>
                                <a href="<?= base_url('/admin/users/delete/' . $user['id_usuario']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de eliminar este usuario?')" title="Eliminar">
                                    Eliminar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Footer -->
    <footer style="background-color: white; padding: 20px 0; border-top: 1px solid #B0BEC5; margin-top: 40px; color: #3A3F51; font-size: 14px; text-align: center;">
        <div style="max-width: 1200px; margin: 0 auto; display: flex; flex-direction: column; align-items: center;">
            <p style="margin: 0; font-weight: bold;">Cycloid Talent SAS</p>
            <p style="margin: 5px 0;">Todos los derechos reservados © 2024</p>
            <p style="margin: 5px 0;">NIT: 901.653.912</p>
            <p style="margin: 5px 0;">
                Sitio oficial: <a href="https://cycloidtalent.com/" target="_blank" style="color: #007BFF; text-decoration: none;">https://cycloidtalent.com/</a>
            </p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.0/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.0/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.0/js/buttons.colVis.min.js"></script>

    <script>
        $(document).ready(function () {
            // Configurar DataTables para usar la segunda fila del thead como filtros
            var table = $('#usersTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
                },
                "stateSave": true,
                "dom": 'Bfrtip',
                "buttons": [
                    {
                        extend: 'colvis',
                        text: 'Visibilidad de Columnas',
                        className: 'btn btn-secondary btn-sm'
                    },
                    {
                        extend: 'excelHtml5',
                        text: 'Descargar Excel',
                        className: 'btn btn-success btn-sm',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ],
                "responsive": true,
                "autoWidth": false,
                "order": [[0, "desc"]],
                "orderCellsTop": true,
                "initComplete": function () {
                    var api = this.api();

                    // Vincular los inputs de texto para filtrar
                    $('.filters-row th input').each(function (i) {
                        var that = this;
                        $(this).on('keyup change clear', function () {
                            if (api.column(i).search() !== this.value) {
                                api.column(i).search(this.value).draw();
                            }
                        });
                    });

                    // Vincular los selects para filtrar
                    $('.filters-row th select').each(function (i) {
                        var colIndex = $(this).closest('th').index();
                        var that = this;
                        $(this).on('change', function () {
                            var val = $.fn.dataTable.util.escapeRegex($(this).val());
                            api.column(colIndex).search(val ? val : '', true, false).draw();
                        });
                    });
                }
            });

            $('#clearState').on('click', function () {
                localStorage.removeItem('DataTables_usersTable_/');
                table.state.clear();
                // Limpiar los filtros manualmente
                $('.filters-row input').val('');
                $('.filters-row select').val('');
                location.reload();
            });
        });
    </script>

</body>

</html>
