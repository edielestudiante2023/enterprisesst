<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar KPI del Cliente</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap5.min.css">
</head>

<body class="bg-light text-dark">

    <nav style="background-color: white; position: fixed; top: 0; width: 100%; z-index: 1000; padding: 10px 0; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; max-width: 1200px; margin: 0 auto; padding: 0 20px;">

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

            <!-- Botón -->
            <div style="text-align: center;">
                <h2 style="margin: 0; font-size: 16px;">Ir a Dashboard</h2>
                <a href="<?= base_url('/dashboardconsultant') ?>" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; font-size: 14px; margin-top: 5px;">Ir a DashBoard</a>
            </div>
        </div>
    </nav>

    <!-- Ajustar el espaciado para evitar que el contenido se oculte bajo el navbar fijo -->
    <div style="height: 160px;"></div>

    <div class="container mt-5">
        <h2 class="text-center mb-4">Editar KPI del Cliente</h2>

        <form action="<?= base_url('/editClientKpiPost/' . $clientKpi['id_client_kpi']) ?>" method="post" class="bg-white p-4 shadow-sm rounded">

            <?php if (session()->has('errors')) : ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach (session('errors') as $error) : ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach ?>
                    </ul>
                </div>
            <?php endif ?>

            <!-- Selección del Cliente -->
            <div class="mb-3">
                <label for="id_cliente" class="form-label">Cliente:</label>
                <select name="id_cliente" id="id_cliente" class="form-select" required>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?= $cliente['id_cliente'] ?>" <?= $cliente['id_cliente'] == $clientKpi['id_cliente'] ? 'selected' : '' ?>>
                            <?= $cliente['nombre_cliente'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Mes -->
            <div class="mb-3">
                <label for="month" class="form-label">Mes:</label>
                <select name="month" id="month" class="form-select" required>
                    <option value="01" <?= ($clientKpi['month'] == '01') ? 'selected' : '' ?>>Enero</option>
                    <option value="02" <?= ($clientKpi['month'] == '02') ? 'selected' : '' ?>>Febrero</option>
                    <option value="03" <?= ($clientKpi['month'] == '03') ? 'selected' : '' ?>>Marzo</option>
                    <option value="04" <?= ($clientKpi['month'] == '04') ? 'selected' : '' ?>>Abril</option>
                    <option value="05" <?= ($clientKpi['month'] == '05') ? 'selected' : '' ?>>Mayo</option>
                    <option value="06" <?= ($clientKpi['month'] == '06') ? 'selected' : '' ?>>Junio</option>
                    <option value="07" <?= ($clientKpi['month'] == '07') ? 'selected' : '' ?>>Julio</option>
                    <option value="08" <?= ($clientKpi['month'] == '08') ? 'selected' : '' ?>>Agosto</option>
                    <option value="09" <?= ($clientKpi['month'] == '09') ? 'selected' : '' ?>>Septiembre</option>
                    <option value="10" <?= ($clientKpi['month'] == '10') ? 'selected' : '' ?>>Octubre</option>
                    <option value="11" <?= ($clientKpi['month'] == '11') ? 'selected' : '' ?>>Noviembre</option>
                    <option value="12" <?= ($clientKpi['month'] == '12') ? 'selected' : '' ?>>Diciembre</option>
                </select>
            </div>

            <!-- Año -->
            <div class="mb-3">
                <label for="year" class="form-label">Año:</label>
                <select name="year" id="year" class="form-select" required>
                    <?php for ($i = date('Y'); $i >= 2000; $i--): ?>
                        <option value="<?= $i ?>" <?= ($clientKpi['year'] == $i) ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <!-- Política KPI -->
            <div class="mb-3">
                <label for="id_kpi_policy" class="form-label">Política KPI:</label>
                <select name="id_kpi_policy" id="id_kpi_policy" class="form-select" required>
                    <?php foreach ($kpiPolicies as $policy): ?>
                        <option value="<?= $policy['id_kpi_policy'] ?>" <?= $policy['id_kpi_policy'] == $clientKpi['id_kpi_policy'] ? 'selected' : '' ?>>
                            <?= $policy['policy_kpi_definition'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="kpi_formula" class="form-label">Fórmula KPI:</label>
                <input type="text" name="kpi_formula" id="kpi_formula" class="form-control" value="<?= $clientKpi['kpi_formula'] ?>" required>
            </div>

            <!-- Objetivo -->
            <div class="mb-3">
                <label for="id_objectives" class="form-label">Objetivo:</label>
                <select name="id_objectives" id="id_objectives" class="form-select" required>
                    <?php foreach ($objectives as $objective): ?>
                        <option value="<?= $objective['id_objectives'] ?>" <?= $objective['id_objectives'] == $clientKpi['id_objectives'] ? 'selected' : '' ?>>
                            <?= $objective['name_objectives'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- KPI -->
            <div class="mb-3">
                <label for="id_kpis" class="form-label">KPI:</label>
                <select name="id_kpis" id="id_kpis" class="form-select" required>
                    <?php foreach ($kpis as $kpi): ?>
                        <option value="<?= $kpi['id_kpis'] ?>" <?= $kpi['id_kpis'] == $clientKpi['id_kpis'] ? 'selected' : '' ?>>
                            <?= $kpi['kpi_name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Tipo de KPI -->
            <div class="mb-3">
                <label for="id_kpi_type" class="form-label">Tipo de KPI:</label>
                <select name="id_kpi_type" id="id_kpi_type" class="form-select" required>
                    <?php foreach ($kpiTypes as $type): ?>
                        <option value="<?= $type['id_kpi_type'] ?>" <?= $type['id_kpi_type'] == $clientKpi['id_kpi_type'] ? 'selected' : '' ?>>
                            <?= $type['kpi_type'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Definición del KPI -->
            <div class="mb-3">
                <label for="id_kpi_definition" class="form-label">Definición del KPI:</label>
                <select name="id_kpi_definition" id="id_kpi_definition" class="form-select" required>
                    <?php foreach ($kpiDefinitions as $definition): ?>
                        <option value="<?= $definition['id_kpi_definition'] ?>" <?= $definition['id_kpi_definition'] == $clientKpi['id_kpi_definition'] ? 'selected' : '' ?>>
                            <?= $definition['name_kpi_definition'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Fuente de datos -->
            <div class="mb-3">
                <label for="data_source" class="form-label">Fuente de datos:</label>
                <textarea name="data_source" id="data_source" class="form-control" required><?= $clientKpi['data_source'] ?></textarea>
            </div>

            <!-- Meta del KPI -->
            <div class="mb-3">
                <label for="kpi_target" class="form-label">Meta del KPI:</label>
                <input type="number" name="kpi_target" id="kpi_target" class="form-control" value="<?= number_format($clientKpi['kpi_target'], 0, '', '') ?>" required>
            </div>

            <!-- Interpretación del KPI -->
            <div class="mb-3">
                <label for="kpi_interpretation" class="form-label">Interpretación del KPI:</label>
                <textarea name="kpi_interpretation" id="kpi_interpretation" class="form-control" required><?= $clientKpi['kpi_interpretation'] ?></textarea>
            </div>

            <!-- Periodicidad -->
            <div class="mb-3">
                <label for="periodicidad" class="form-label">Periodicidad:</label>
                <select name="periodicidad" id="periodicidad" class="form-select" required>
                    <option value="mensual" <?= ($clientKpi['periodicidad'] == 'mensual') ? 'selected' : '' ?>>Mensual</option>
                    <option value="bimensual" <?= ($clientKpi['periodicidad'] == 'bimensual') ? 'selected' : '' ?>>Bimensual</option>
                    <option value="trimestral" <?= ($clientKpi['periodicidad'] == 'trimestral') ? 'selected' : '' ?>>Trimestral</option>
                    <option value="cuatrimestral" <?= ($clientKpi['periodicidad'] == 'cuatrimestral') ? 'selected' : '' ?>>Cuatrimestral</option>
                    <option value="semestral" <?= ($clientKpi['periodicidad'] == 'semestral') ? 'selected' : '' ?>>Semestral</option>
                    <option value="anual" <?= ($clientKpi['periodicidad'] == 'anual') ? 'selected' : '' ?>>Anual</option>
                </select>
            </div>

            <!-- Periodos de medición -->
            <h3 class="mt-4">Periodos de medición</h3>
            <?php foreach ($periodos as $periodo): ?>
                <h4 class="mt-3">Periodo <?= $periodo ?></h4>

                <div class="mb-3">
                    <label for="variable_numerador_<?= $periodo ?>" class="form-label">Variable Numerador <?= $periodo ?>:</label>
                    <select name="variable_numerador_<?= $periodo ?>" id="variable_numerador_<?= $periodo ?>" class="form-select">
                        <?php foreach ($numerators as $numerator): ?>
                            <option value="<?= $numerator['id_numerator_variable'] ?>" <?= $numerator['id_numerator_variable'] == $clientKpi['variable_numerador_' . $periodo] ? 'selected' : '' ?>>
                                <?= $numerator['numerator_variable_text'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="dato_variable_numerador_<?= $periodo ?>" class="form-label">Dato Variable Numerador <?= $periodo ?>:</label>
                    <input type="number" name="dato_variable_numerador_<?= $periodo ?>" id="dato_variable_numerador_<?= $periodo ?>" class="form-control" value="<?= $clientKpi['dato_variable_numerador_' . $periodo] ?>">
                </div>

                <div class="mb-3">
                    <label for="variable_denominador_<?= $periodo ?>" class="form-label">Variable Denominador <?= $periodo ?>:</label>
                    <select name="variable_denominador_<?= $periodo ?>" id="variable_denominador_<?= $periodo ?>" class="form-select">
                        <?php foreach ($denominators as $denominator): ?>
                            <option value="<?= $denominator['id_denominator_variable'] ?>" <?= $denominator['id_denominator_variable'] == $clientKpi['variable_denominador_' . $periodo] ? 'selected' : '' ?>>
                                <?= $denominator['denominator_variable_text'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="dato_variable_denominador_<?= $periodo ?>" class="form-label">Dato Variable Denominador <?= $periodo ?>:</label>
                    <input type="number" name="dato_variable_denominador_<?= $periodo ?>" id="dato_variable_denominador_<?= $periodo ?>" class="form-control" value="<?= $clientKpi['dato_variable_denominador_' . $periodo] ?>">
                </div>

            <?php endforeach; ?>

            <div class="mb-3">
                <label for="analisis_datos" class="form-label">Análisis de los datos:</label>
                <textarea name="analisis_datos" id="analisis_datos" class="form-control"><?= isset($clientKpi['analisis_datos']) ? esc($clientKpi['analisis_datos']) : '' ?></textarea>
            </div>

            <div class="mb-3">
                <label for="seguimiento1" class="form-label">Seguimiento 1:</label>
                <textarea name="seguimiento1" id="seguimiento1" class="form-control"><?= isset($clientKpi['seguimiento1']) ? esc($clientKpi['seguimiento1']) : '' ?></textarea>
            </div>

            <div class="mb-3">
                <label for="seguimiento2" class="form-label">Seguimiento 2:</label>
                <textarea name="seguimiento2" id="seguimiento2" class="form-control"><?= isset($clientKpi['seguimiento2']) ? esc($clientKpi['seguimiento2']) : '' ?></textarea>
            </div>

            <div class="mb-3">
                <label for="seguimiento3" class="form-label">Seguimiento 3:</label>
                <textarea name="seguimiento3" id="seguimiento3" class="form-control"><?= isset($clientKpi['seguimiento3']) ? esc($clientKpi['seguimiento3']) : '' ?></textarea>
            </div>

            <!-- Botón para enviar el formulario -->
            <div class="text-center">
                <button type="submit" class="btn btn-primary mt-3">Guardar Cambios</button>
            </div>
        </form>

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

    <!-- Bootstrap and DataTables JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#datatable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.3/i18n/es_es.json"
                }
            });
        });
    </script>

</body>

</html>