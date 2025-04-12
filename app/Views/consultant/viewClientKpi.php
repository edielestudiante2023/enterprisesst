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
                <!-- Columna 2: Dropdown para navegación interna -->
                <div class="col-md-6">
                    <?php if (!empty($selectedClient) && count($clientKpis) > 0): ?>
                        <div class="form-group">
                            <label for="select-section"><strong>Ir a KPI:</strong></label>
                            <select id="select-section" class="form-control">
                                <option value="">-- Seleccione --</option>
                                <?php foreach ($clientKpis as $kpi): ?>
                                    <option value="#kpi-<?= $kpi['id_client_kpi'] ?>">KPI: <?= $kpi['kpi'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <h2>Detalles Completos del KPI</h2>

    <!-- Mostrar los KPIs filtrados si se ha seleccionado un cliente -->
    <?php if (!empty($selectedClient)): ?>
        <?php if (count($clientKpis) > 0): ?>
            <?php foreach ($clientKpis as $kpi): ?>
                <div class="table-container">
                    <table class="table table-bordered">
                        <tbody>
                            <!-- Se asigna id para navegación interna -->
                            <tr id="kpi-<?= $kpi['id_client_kpi'] ?>">
                                <th>KPI</th>
                                <td><?= $kpi['kpi'] ?></td>
                            </tr>
                            <tr>
                                <th>ID</th>
                                <td><?= $kpi['id_client_kpi'] ?></td>
                            </tr>
                            <tr>
                                <th>Año</th>
                                <td><?= $kpi['year'] ?></td>
                            </tr>
                            <tr>
                                <th>Mes</th>
                                <td><?= $kpi['month'] ?></td>
                            </tr>
                            <tr>
                                <th>Cliente</th>
                                <td><?= $kpi['cliente'] ?></td>
                            </tr>
                            <tr>
                                <th>Formula KPI</th>
                                <td><?= $kpi['kpi_formula'] ?></td>
                            </tr>
                            <tr>
                                <th>Objetivo</th>
                                <td><?= $kpi['objective'] ?></td>
                            </tr>
                            <tr>
                                <th>Tipo de KPI</th>
                                <td><?= $kpi['kpi_type'] ?></td>
                            </tr>
                            <tr>
                                <th>Definición</th>
                                <td><?= $kpi['kpi_definition'] ?></td>
                            </tr>
                            <tr>
                                <th>Meta</th>
                                <td><?= $kpi['kpi_target'] ?></td>
                            </tr>
                            <tr>
                                <th>Fuente de Datos</th>
                                <td><?= $kpi['data_source'] ?></td>
                            </tr>
                            <tr>
                                <th>Responsable</th>
                                <td><?= $kpi['data_owner'] ?></td>
                            </tr>
                            <tr>
                                <th>Interpretación</th>
                                <td><?= $kpi['kpi_interpretation'] ?></td>
                            </tr>
                            <tr>
                                <th>Periodicidad</th>
                                <td><?= $kpi['periodicidad'] ?></td>
                            </tr>
                            <tr>
                                <th>Gran Total del Indicador</th>
                                <td><?= number_format($kpi['promedio_indicadores'] * 100, 2) ?>%</td>
                            </tr>
                        </tbody>
                    </table>

                    <h3 class="text-center mt-4">PERIODOS</h3>
                    <table class="table table-striped table-bordered datatable" style="width:100%">
                        <thead>
                            <tr>
                                <th>Numerador</th>
                                <th>Valor Numerador</th>
                                <th>Denominador</th>
                                <th>Valor Denominador</th>
                                <th>Indicador</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($kpi['periodos'] as $periodo): ?>
                                <tr>
                                    <td><?= $periodo['numerador'] ?></td>
                                    <td class="text-center"><?= $periodo['dato_variable_numerador'] ?></td>
                                    <td><?= $periodo['denominador'] ?></td>
                                    <td class="text-center"><?= $periodo['dato_variable_denominador'] ?></td>
                                    <td class="text-center"><?= $periodo['valor_indicador'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <h3 class="text-center mt-4">ANÁLISIS Y SEGUIMIENTOS</h3>
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th>Análisis de los Datos</th>
                                <td><?= $kpi['analisis_datos'] ?></td>
                            </tr>
                            <tr>
                                <th>Seguimiento 1 - Plan de Acción</th>
                                <td><?= $kpi['seguimiento1'] ?></td>
                            </tr>
                            <tr>
                                <th>Seguimiento 2 - Plan de Acción</th>
                                <td><?= $kpi['seguimiento2'] ?></td>
                            </tr>
                            <tr>
                                <th>Seguimiento 3 - Plan de Acción</th>
                                <td><?= $kpi['seguimiento3'] ?></td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Botón de edición para cada KPI -->
                    <div class="text-center mt-3">
                        <a href="<?= base_url('/editClientKpi/' . $kpi['id_client_kpi'] . '?id_cliente=' . $selectedClient) ?>" class="btn btn-secondary btn-edit">Editar Indicador</a>
                    </div>
                </div>
                <br>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center">No se encontraron KPIs para el cliente seleccionado.</p>
        <?php endif; ?>
    <?php endif; ?>

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
            // Si existe un valor en sessionStorage y la URL no tiene el parámetro id_cliente, redirige para incluirlo
            var storedClient = sessionStorage.getItem('selectedClient');
            var urlParams = new URLSearchParams(window.location.search);
            if (storedClient && !urlParams.has('id_cliente')) {
                window.location.href = window.location.pathname + '?id_cliente=' + storedClient;
            }
            
            // Si hay valor en sessionStorage, se asigna al select
            if (storedClient) {
                $('#id_cliente').val(storedClient);
            }
            
            // Al cambiar la selección, se guarda el valor en sessionStorage
            $('#id_cliente').on('change', function() {
                sessionStorage.setItem('selectedClient', $(this).val());
            });
            
            // Inicializar DataTable
            $('.datatable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json'
                }
            });

            // Navegación interna con scroll suave
            $('#select-section').change(function() {
                var target = $(this).val();
                if (target) {
                    $('html, body').animate({
                        scrollTop: $(target).offset().top - 150
                    }, 500);
                }
            });
        });
    </script>
</body>

</html>
