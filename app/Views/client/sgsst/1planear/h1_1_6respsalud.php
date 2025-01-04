<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2.14.0 Divulgación de Recomendaciones Médicas</title>
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
            max-width: 100px;
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
        .alfa-contenedor {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .input-line {
            border-bottom: 1px solid #000;
            width: 300px;
            display: inline-block;
            margin-bottom: 5px;
        }

        .gamma-titulo {
            font-size: 1.5em;
            font-weight: bold;
            margin-top: 20px;
        }

        .firma-line {
            border-bottom: 1px solid #000;
            width: 400px;
            display: inline-block;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .firma-container {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        p,
        li {
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

    <div class="alfa-contenedor">
        <p>Señor(a): <span class="input-line"></span></p>
        <p>Cargo: <span class="input-line"></span></p>
        <p>Ciudad: <span class="input-line"></span></p>

        <p>
            Para la empresa <strong><strong><?= $client['nombre_cliente'] ?></strong></strong>, es muy importante velar por el cuidado de la salud y bienestar físico de sus colaboradores, motivo por el cual nos permitimos recordarle el cumplimiento de las responsabilidades para el cuidado de su salud, acorde con lo estipulado en el artículo 2.2.4.6.10 del Decreto 1072 del 2015, por medio del cual se expide el Decreto Único Reglamentario del Sector Trabajo, y que señala las siguientes responsabilidades por parte de los colaboradores:
        </p>

        <ol>
            <li>Procurar el cuidado integral de su salud.</li>
            <li>Suministrar información clara, veraz y completa sobre su estado de salud.</li>
            <li>Cumplir las normas, reglamentos e instrucciones del Sistema de Gestión de la Seguridad y Salud en el Trabajo de la Compañía.</li>
        </ol>

        <p>
            Por lo anteriormente mencionado y teniendo en cuenta los resultados de la evaluación médica ocupacional periódica que le fue realizada el pasado sábado, 8 de junio de 2024, a continuación, se describen las recomendaciones médicas que debe acatar y dar cumplimiento para la recuperación de su salud:
        </p>

        <h3 class="gamma-titulo">Recomendaciones Médicas</h3>
        <ul>
            <li><strong>Médico-Laborales:</strong> Se sugiere control programas vigilancia epidemiológica: condición visual.</li>
            <li><strong>Valoración optométrica:</strong> Control anual / requiere actualizar la corrección visual.</li>
        </ul>

        <p>
            Por lo anterior, solicitamos que tramite una cita con su médico tratante de la EPS para que pueda hacer el seguimiento a las recomendaciones, a favor del cumplimiento de lo establecido en el Decreto 1072 de 2015, la Resolución 0312 de 2019 y el seguimiento de nuestro Sistema de Gestión de Seguridad y Salud en el Trabajo.
        </p>

        <p>
            Agradecemos hacernos llegar la constancia de su asistencia y las recomendaciones emitidas por el médico para actualizar la información de los sistemas de vigilancia epidemiológica de la empresa, en un tiempo no mayor a 2 meses.
        </p>

        <p>
            En constancia de lo anterior, firmo la presente en señal de aceptación y compromiso de cumplimiento de las recomendaciones médico-laborales que me han sido comunicadas.
        </p>

        <div class="firma-container">
            <div>
                <p>RESPONSABLE EMPRESA</p>
                <span class="firma-line"></span>
            </div>
            <div>
                <p>FIRMA COLABORADOR</p>
                <span class="firma-line"></span>
            </div>
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

  <!--   <div>
        <a href="<?= base_url('/generatePdf_responsabilidadesSalud') ?>" target="_blank">
            <button type="button">PDF</button>
        </a>
    </div>
 -->
</body>

</html>