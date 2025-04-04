<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CUMPLIMIENTO MEDIDAS DE INTERVENCIÓN DE LA MATRIZ DE IDENTIFICACIÓN DE PELIGROS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
            line-height: 1.6;
            background-color: white;
        }

        /* Estilos aplicados al footer */
        footer {
            text-align: center;
            margin-top: 50px;
            background-color: white;
            padding: 20px;
            border-top: 1px solid #ccc;
            font-size: 14px;
        }

        footer table {
            width: 100%;
            border-collapse: collapse;
        }

        footer th,
        footer td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        /* Estilos aplicados a la sección .centered-content */
        .centered-content {
            width: 100%;
            margin: 0 auto;
            padding: 0 0 20px 0;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .centered-content table {
            width: 100%;
            text-align: center;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid black;
            padding: 10px;
            text-align: left;
            height: 30px;
        }

        /* Estilos aplicados a las clases internas de la tabla */
        .logo {
            width: 20%;
            text-align: center;
        }

        .main-title {
            width: 50%;
            font-weight: bold;
            font-size: 14px;
            text-align: center;
        }

        .code {
            width: 30%;
            font-weight: bold;
            font-size: 14px;
        }

        .subtitle {
            font-weight: bold;
            font-size: 16px;
            text-align: center;
        }

        .right {
            text-align: left;
            padding-left: 10px;
        }

        .signature-container {
            display: flex;
            /* Ensures that the divs are displayed in a row */
            justify-content: space-evenly;
            /* Adds space between the items */
            align-items: center;
            /* Aligns the items vertically in the center */
            margin-top: 20px;
        }

        .signature {
            text-align: center;
            width: 90%;
            /* Adjust the width of each signature block */
        }

        .signature img {
            max-width: 200px;
            /* Adjust the size of the images as needed */
            height: auto;
        }

        .signature .name {
            font-weight: bold;
        }

        .signature .title {
            font-style: italic;
        }

        /* ***************************************************************************** */
        .alpha-container {
            width: 96%;
            padding: 20px;
        }

        .alpha-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .alpha-table th,
        .alpha-table td {
            border: 1px solid black;
            padding: 10px;
            text-align: left;
        }

        .alpha-table th {
            background-color: #f2f2f2;
        }

        .alpha-title {
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 20px;
        }

        .beta-section-title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin-top: 30px;
            margin-bottom: 10px;
        }

        .gamma-p {
            font-size: 14px;
            margin-bottom: 15px;
        }
    </style>


</head>

<body>
    <div class="centered-content">
        <table>
            <tr>
                <td rowspan="2" class="logo">
                    <img src="<?= base_url('uploads/' . $client['logo']) ?>" alt="Logo de <?= $client['nombre_cliente'] ?>" width="100%">
                </td>
                <td class="main-title">
                    SISTEMA DE GESTION EN SEGURIDAD Y SALUD EN EL TRABAJO
                </td>
                <td class="code">
                    <?= $latestVersion['document_type'] ?>-<?= $latestVersion['acronym'] ?>
                </td>
            </tr>
            <tr>
                <td class="subtitle">
                    <?= $policyType['type_name'] ?>
                </td>
                <td class="code right">
                    Versión: <?= $latestVersion['version_number'] ?><br>
                    <?php
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'Spanish_Spain'); // Configura el idioma español
?>

Fecha: <?= strftime('%d de %B de %Y', strtotime($latestVersion['created_at'])); ?>

                </td>
            </tr>
        </table>
    </div>

    <div class="alpha-container">
        <!-- 1. Información del Indicador -->
        <h3 class="alpha-title">1. INFORMACIÓN DEL INDICADOR</h3>
        <table class="alpha-table">
            <tr>
                <th>Nombre del Indicador</th>
                <td colspan="3"><?= $kpiData['kpi_name']. ' - ' . $kpiType['kpi_type']?></td>
            </tr>
            <tr>
                <th>Definición del Indicador</th>
                <td colspan="3"><?= $kpiDefinition['name_kpi_definition'] ?></td>
            </tr>
            <tr>
                <th>Interpretación del Indicador</th>
                <td colspan="3"><?= $clientKpi['kpi_interpretation'] ?></td>
            </tr>
            <tr>
                <th>Meta</th>
                <td><?= $clientKpi['kpi_target'] ?>%</td>
                <th>Frecuencia de Reporte</th>
                <td><?= $clientKpi['periodicidad'] ?></td>
            </tr>
            <tr>
                <th>Fórmula</th>
                <td colspan="3"><?= $clientKpi['kpi_formula'] ?></td>
            </tr>
            <tr>
                <th>Origen de Datos</th>
                <td colspan="3"><?= $clientKpi['data_source'] ?></td>
            </tr>
            <tr>
                <th>Cargo Responsable del Cálculo</th>
                <td><?= $dataOwner['data_owner'] ?></td>
                <th>Cargos que deben conocer el resultado</th>
                <td><?= $clientKpi['positions_should_know_result'] ?></td>
            </tr>
        </table>

        <!-- 2. Medición del Indicador -->
        <h3 class="alpha-title">2. MEDICIÓN DEL INDICADOR</h3>
        <table class="alpha-table">
            <thead>
                <tr>
                    <th>Variables</th>
                    <th>Ene-Jun</th>
                    <th>Jul-Dic</th>
                    <th>Media Aritmética</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= $periodos[0]['numerador'] ?></td>
                    <td><?= $periodos[0]['dato_variable_numerador'] ?></td>
                    <td><?= $periodos[1]['dato_variable_numerador'] ?></td>
                    <td><?= $promedioNumerador ?></td>
                </tr>
                <tr>
                    <td><?= $periodos[0]['denominador'] ?></td>
                    <td><?= $periodos[0]['dato_variable_denominador'] ?></td>
                    <td><?= $periodos[1]['dato_variable_denominador'] ?></td>
                    <td><?= $promedioDenominador ?></td>
                </tr>
                <tr>
                    <td>Indicador</td>
                    <td><?= number_format($periodos[0]['valor_indicador'], 2) ?></td>
                    <td><?= number_format($periodos[1]['valor_indicador'], 2) ?></td>
                    <td><?= number_format($promedioIndicadores * 100, 2) ?>%</td> <!-- Mostrar el promedio del indicador -->
                </tr>
                <tr>
                    <td>Meta</td>
                    <td><?= $clientKpi['kpi_target'] ?>%</td>
                    <td><?= $clientKpi['kpi_target'] ?>%</td>
                    <td><?= $clientKpi['kpi_target'] ?>%</td>
                </tr>
            </tbody>
        </table>

        <!-- 3. Gráfica -->
        <!-- <h3 class="alpha-title">3. GRÁFICA</h3>
        <p class="gamma-p">[Gráfica omitida en esta versión]</p> -->

        <!-- 4. Análisis de Datos -->
        <h3 class="alpha-title">3. ANÁLISIS DE DATOS</h3>
        <h3 style="text-align: center; color:green"><?= $analisis_datos ?></h3>

        <!-- 5. Seguimiento del Indicador - Plan de Acción -->
        <h3 class="alpha-title">4. SEGUIMIENTO DEL INDICADOR - PLAN DE ACCIÓN</h3>
        <table class="alpha-table">
            <thead>
                <tr>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th>Seguimiento 1</th>
                    <td><?= $seguimiento1 ?></td>
                </tr>
                <tr>
                    <th>Seguimiento 2</th>
                    <td><?= $seguimiento2 ?></td>

                </tr>
                <tr>
                    <th>Seguimiento 3</th>
                    <td><?= $seguimiento3 ?></td>

                </tr>
            </tbody>
        </table>
    </div>

<footer>
    <h2>Historial de Versiones</h2>
    <style>
        footer table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        footer table th, footer table td {
            border: 1px solid #ddd;
            text-align: center;
            vertical-align: middle;
            padding: 8px;
            word-wrap: break-word;
        }
        footer table th {
            background-color: #f4f4f4;
            font-weight: bold;
        }
        footer table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        footer table tr:hover {
            background-color: #f1f1f1;
        }
        /* Ajuste del ancho de las columnas */
        footer table th:nth-child(5),
        footer table td:nth-child(5) {
            width: 35%; /* Más ancho para la columna Observaciones */
        }
        footer table th:nth-child(1),
        footer table td:nth-child(1) {
            width: 10%; /* Más estrecho para la columna Versión */
        }
        footer table th:nth-child(2),
        footer table td:nth-child(2),
        footer table th:nth-child(3),
        footer table td:nth-child(3),
        footer table th:nth-child(4),
        footer table td:nth-child(4) {
            width: 15%; /* Ancho uniforme para las demás columnas */
        }
    </style>
    <table>
        <tr>
            <th>Versión</th>
            <th>Tipo de Documento</th>
            <th>Acrónimo</th>
            <th>Fecha de Creación</th>
            <th>Observaciones</th>
        </tr>
        <?php foreach ($allVersions as $version): ?>
            <tr>
                <td><?= $version['version_number'] ?></td>
                <td><?= $version['document_type'] ?></td>
                <td><?= $version['acronym'] ?></td>
                <td><?= strftime('%d de %B de %Y', strtotime($version['created_at'])); ?></td>
                <td><?= $version['change_control'] ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</footer>

    <br>

   

</body>

</html>