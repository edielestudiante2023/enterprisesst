<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles Completos del KPI</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            color: #333;
            /* Espacio para el nav fijo */
            padding-top: 120px;
        }

        h2 {
            text-align: center;
            margin-top: 20px;
            font-weight: bold;
        }

        .table-container {
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .btn-dashboard,
        .btn-edit {
            display: block;
            width: fit-content;
            margin: 0 auto;
            font-weight: bold;
        }

        th {
            background-color: #e9ecef !important;
        }
    </style>
    <!-- jQuery y DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
</head>

<body>
    <!-- Nav fijo con formulario y dropdown en grid -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
        <div class="container">
            <div class="row w-100">
                <!-- Columna 1: Formulario de selección de cliente -->
                <div class="col-md-6">
                    <form method="GET" action="<?= base_url('/listClientKpisFull') ?>">
                        <div class="form-group">
                            <label for="id_cliente"><strong>Seleccione Cliente:</strong></label>
                            <select name="id_cliente" id="id_cliente" class="form-control">
                                <option value="">-- Seleccione --</option>
                                <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?= $cliente['id_cliente'] ?>" <?= (isset($selectedClient) && $selectedClient == $cliente['id_cliente']) ? 'selected' : '' ?>>
                                        <?= $cliente['nombre_cliente'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">Consultar</button>
                        </div>
                    </form>
                </div>
                <!-- Columna 2: Dropdown para navegación interna entre KPIs -->
                <div class="col-md-6">
                    <?php if (!empty($selectedClient) && count($clientKpis) > 0): ?>
                        <div class="form-group">
                            <label for="select-section"><strong>Ir a KPI:</strong></label>
                            <select id="select-section" class="form-control">
                                <option value="">-- Seleccione --</option>
                                <?php foreach ($clientKpis as $kpi): ?>
                                    <!-- Se utiliza el id del KPI para la consulta AJAX -->
                                    <option value="<?= $kpi['id_client_kpi'] ?>">KPI: <?= $kpi['kpi'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <h2>Detalles Completos del KPI</h2>

    <!-- Contenedor para mostrar el KPI seleccionado vía AJAX -->
    <div id="kpi-container">
        <div class="table-container">
            <p class="text-center">Seleccione un KPI para ver sus detalles.</p>
        </div>
    </div>

    <footer style="background-color: white; padding: 20px 0; border-top: 1px solid #B0BEC5; margin-top: 40px; color: #3A3F51; font-size: 14px; text-align: center;">
        <div style="max-width: 1200px; margin: 0 auto; display: flex; flex-direction: column; align-items: center;">
            <p style="margin: 0; font-weight: bold;">Cycloid Talent SAS</p>
            <p style="margin: 5px 0;">Todos los derechos reservados © 2024</p>
            <p style="margin: 5px 0;">NIT: 901.653.912</p>
            <p style="margin: 5px 0;">Sitio oficial: <a href="https://cycloidtalent.com/" target="_blank" style="color: #007BFF; text-decoration: none;">https://cycloidtalent.com/</a></p>
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

    <!-- Bootstrap y DataTables JavaScript -->
    <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            // Manejo de la selección del cliente con sessionStorage
            var storedClient = sessionStorage.getItem('selectedClient');
            var urlParams = new URLSearchParams(window.location.search);
            if (storedClient && !urlParams.has('id_cliente')) {
                window.location.href = window.location.pathname + '?id_cliente=' + storedClient;
            }
            if (storedClient) {
                $('#id_cliente').val(storedClient);
            }
            $('#id_cliente').on('change', function() {
                sessionStorage.setItem('selectedClient', $(this).val());
            });

            // Inicializar DataTable si hubiese alguna tabla ya visible (por ejemplo, en el contenido cargado dinámicamente)
            $('.datatable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json'
                }
            });

            // Cuando se selecciona un KPI del dropdown, se realiza la petición AJAX para cargar sus detalles.
            $('#select-section').on('change', function() {
                var kpiId = $(this).val();
                if (kpiId) {
                    $.ajax({
                        url: '<?= base_url("/getKpiDetails") ?>', // Reemplaza por la URL real de tu endpoint
                        method: 'GET',
                        data: {
                            kpi_id: kpiId
                        },
                        success: function(data) {
                            // Se actualiza el contenedor con el HTML recibido
                            $('#kpi-container').html(data);

                            // Re-inicializar DataTable en el nuevo contenido, si es necesario
                            $('.datatable').DataTable({
                                language: {
                                    url: '//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json'
                                }
                            });

                            // Opcional: scroll suave hacia el contenedor del KPI
                            $('html, body').animate({
                                scrollTop: $('#kpi-container').offset().top - 150
                            }, 500);
                        },
                        error: function(error) {
                            console.error('Error al cargar el KPI:', error);
                        }
                    });
                }
            });
        });
    </script>
</body>

</html>
