<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2.1.2 Política de No Alcohol, Drogas Ni Tabaco</title>
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





        .alpha-title {
            font-size: 1.5em;
            font-weight: bold;
            text-align: center;
            margin-top: 0px;
        }

        .beta-parrafo {
            margin-bottom: 0px;
            font-size: 0.8em;
            text-align: justify;
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

    <div class="alpha-title">Política de No Alcohol, Drogas ni Tabaco</div>

    <p class="beta-parrafo">
        <strong><?= $client['nombre_cliente'] ?></strong> ha establecido una política de control para el alcohol, tabaco y drogas con el objetivo de prevenir, mejorar y conservar el bienestar de los trabajadores y su calidad de vida. Adicionalmente, esta política busca garantizar un adecuado desempeño, competitividad del personal y de la institución, así como fomentar estilos de vida saludables y el autocuidado.
    </p>

    <p class="beta-parrafo">
        Es política de <strong><?= $client['nombre_cliente'] ?></strong> mantener los lugares de trabajo en óptimas condiciones que permitan alcanzar los más altos estándares de seguridad y bienestar. La empresa es consciente de que el consumo de alcohol, el abuso de tabaco y el uso de sustancias psicoactivas por parte de los funcionarios tiene efectos adversos considerables en la salud y en la capacidad de ejecución de sus labores, afectando tanto a la empresa como a su integridad. Por lo tanto, la indebida utilización de medicamentos no formulados; la posesión y distribución de sustancias psicoactivas; y la posesión, uso y distribución de bebidas alcohólicas están estrictamente prohibidos durante el horario laboral y dentro de las instalaciones.
    </p>

    <p class="beta-parrafo">
        Asimismo, se prohíbe el uso de tabaco en las instalaciones de <strong><?= $client['nombre_cliente'] ?></strong> y en lugares no autorizados.
    </p>

    <p class="beta-parrafo">
        La violación a esta política se considera una falta grave, y en consecuencia, la empresa adoptará medidas disciplinarias, inclusive la terminación del contrato de trabajo por justa causa, conforme al Código Sustantivo del Trabajo (artículo 62, numeral 12) y como se establece en el reglamento interno de trabajo. Por lo anterior, se ha designado el recurso humano necesario para dar cumplimiento a esta política, y se establecen responsabilidades para todos los trabajadores, quienes deberán participar en los programas de capacitación y sensibilización.
    </p>

    <p class="beta-parrafo">
        Esta política aplica a todos los trabajadores, contratistas y visitantes de <strong><?= $client['nombre_cliente'] ?></strong>. Por este motivo, no se permitirá el ingreso a las instalaciones de la empresa a personas que se encuentren en estado de embriaguez o que hayan ingerido cualquier tipo de sustancias psicoactivas.
    </p>

    <div class="signature-container">
        <div class="signature">
            <img src="<?= base_url('uploads/' . $client['firma_representante_legal']) ?>" alt="Firma rep. legal">
            <div class="name"><b><?= $client['nombre_rep_legal'] ?></b></div>
            <div class="title">Representante Legal</div>
        </div>

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

   <!--  <div>
        <a href="<?= base_url('/generatePdf_politicaAlcohol') ?>" target="_blank">
            <button type="button">PDF</button>
        </a>
    </div> -->

</body>

</html>