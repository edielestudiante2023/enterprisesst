<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Tipos de Documentos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
</head>

<body class="bg-light text-dark">

    <!-- Navbar fijo con logos y botones -->
    <nav style="background-color: white; position: fixed; top: 0; width: 100%; z-index: 1000; padding: 10px 0; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; max-width: 1200px; margin: 0 auto;">
            <!-- Logos -->
            <div><a href="https://dashboard.cycloidtalent.com/login"><img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo" style="height:100px;"></a></div>
            <div><a href="https://cycloidtalent.com/index.php/consultoria-sst"><img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo" style="height:100px;"></a></div>
            <div><a href="https://cycloidtalent.com/"><img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo" style="height:100px;"></a></div>
        </div>
        <div style="display:flex; justify-content:space-between; align-items:center; max-width:1200px; margin:10px auto 0; padding:0 20px;">
            <div style="text-align:center;"><h2 style="margin:0; font-size:16px;">Ir a Dashboard</h2><a href="<?= base_url('/dashboardconsultant') ?>" class="btn btn-primary btn-sm">Ir a DashBoard</a></div>
            <button id="clearState" class="btn btn-danger btn-sm">Restablecer Filtros</button>
            <div style="text-align:center;"><h2 style="margin:0; font-size:16px;">Añadir Registro</h2><a href="<?= base_url('/addPolicyType') ?>" class="btn btn-success btn-sm" target="_blank">Añadir Registro</a></div>
        </div>
    </nav>

    <!-- Espacio para navbar fijo -->
    <div style="height:160px;"></div>

    <div class="container my-5">
        <div class="table-responsive">
            <table id="documentTypesTable" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre del Tipo de Documento</th>
                        <th>Descripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>ID</th>
                        <th>Nombre del Tipo de Documento</th>
                        <th>Descripción</th>
                        <th></th>
                    </tr>
                </tfoot>
                <tbody>
                    <?php foreach ($policyTypes as $type): ?>
                        <tr>
                            <td><?= $type['id'] ?></td>
                            <td><?= $type['type_name'] ?></td>
                            <td><?= $type['description'] ?></td>
                            <td>
                                <a href="<?= base_url('/editPolicyType/' . $type['id']) ?>" class="btn btn-warning btn-sm">Editar</a>
                                <a href="<?= base_url('/deletePolicyType/' . $type['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que deseas eliminar este tipo de política?');">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer style="background-color:white; padding:20px 0; border-top:1px solid #B0BEC5; margin-top:40px; text-align:center; color:#3A3F51; font-size:14px;">
        <p style="margin:0; font-weight:bold;">Cycloid Talent SAS - © 2024</p>
        <p style="margin:5px 0;">NIT: 901.653.912</p>
        <p style="margin:5px 0;">Sitio oficial: <a href="https://cycloidtalent.com/" target="_blank">cycloidtalent.com</a></p>
        <div style="display:flex; gap:15px; justify-content:center; margin-top:10px;">
            <a href="https://www.facebook.com/CycloidTalent" target="_blank"><img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" alt="Facebook" style="height:24px;"></a>
            <a href="https://co.linkedin.com/company/cycloid-talent" target="_blank"><img src="https://cdn-icons-png.flaticon.com/512/733/733561.png" alt="LinkedIn" style="height:24px;"></a>
            <a href="https://www.instagram.com/cycloid_talent?igsh=Nmo4d2QwZDg5dHh0" target="_blank"><img src="https://cdn-icons-png.flaticon.com/512/733/733558.png" alt="Instagram" style="height:24px;"></a>
            <a href="https://www.tiktok.com/@cycloid_talent" target="_blank"><img src="https://cdn-icons-png.flaticon.com/512/3046/3046126.png" alt="TikTok" style="height:24px;"></a>
        </div>
    </footer>

    <!-- JS: jQuery y DataTables -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
    $(document).ready(function() {
        var table = $('#documentTypesTable').DataTable({
            stateSave: true,
            language: { url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/es_es.json" },
            initComplete: function() {
                var api = this.api();
                api.columns().every(function(index) {
                    var column = this;
                    var footer = $(column.footer());
                    footer.empty();

                    // Filtro por columna
                    if (index === 0) {
                        // ID: búsqueda libre
                        $('<input type="text" class="form-control form-control-sm" placeholder="Buscar ID" />')
                            .appendTo(footer)
                            .on('keyup change clear', function() {
                                column.search(this.value).draw();
                            });
                    } else if (index === 1) {
                        // Nombre: select de únicos
                        var select = $('<select class="form-select form-select-sm"><option value="">Todos</option></select>')
                            .appendTo(footer)
                            .on('change', function() {
                                var val = $.fn.dataTable.util.escapeRegex($(this).val());
                                column.search(val ? '^' + val + '$' : '', true, false).draw();
                            });
                        column.data().unique().sort().each(function(d) {
                            select.append('<option value="' + d + '">' + d + '</option>');
                        });
                    } else if (index === 2) {
                        // Descripción: búsqueda libre
                        $('<input type="text" class="form-control form-control-sm" placeholder="Buscar descripción" />')
                            .appendTo(footer)
                            .on('keyup change clear', function() {
                                column.search(this.value).draw();
                            });
                    }
                    // No filtro para la columna de acciones (index 3)
                });
            }
        });

        $('#clearState').on('click', function() {
            table.state.clear();
            table.search('').columns().search('').draw();
            $('#documentTypesTable tfoot input, #documentTypesTable tfoot select').val('');
        });
    });
    </script>

</body>
</html>
