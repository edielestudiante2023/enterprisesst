<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2.1.3 Política de Prevención, Preparación y Respuesta ante Emergencias</title>
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





        .alpha-title {
            font-size: 1.5em;
            font-weight: bold;
            text-align: center;
            margin-top: 20px;
        }

        .beta-parrafo {
            margin-bottom: 10px;
            font-size: 0.9em;
            text-align: justify;
        }

        .delta-lista {
            margin-left: 20px;
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

    <div class="alpha-title">Política de Prevención, Preparación y Respuesta Ante Emergencias</div>

    <p class="beta-parrafo">
        Para <strong><?= $client['nombre_cliente'] ?></strong> es de vital importancia proveer un óptimo nivel de seguridad a sus trabajadores, contratistas y visitantes. Para ello, dispondrá de elementos e instalaciones adecuadas, fomentará una cultura de prevención y brindará el entrenamiento necesario para asegurar el control de emergencias de cualquier naturaleza.
    </p>

    <p class="beta-parrafo">
        En concordancia con lo anterior, la Gerencia se compromete a proporcionar todo su apoyo, colaboración y gestión necesaria para adelantar todos los procesos tendientes al cumplimiento de los objetivos propuestos en el Plan de Emergencias adoptado por la empresa.
    </p>

    <p class="beta-parrafo">
        Con el fin de:
    </p>

    <ul class="delta-lista">
        <li class="beta-parrafo">Preservar la vida e integridad de todas las personas (empleados, contratistas, estudiantes, visitantes y comunidad).</li>
        <li class="beta-parrafo">Garantizar la preservación de los bienes y activos de la <strong><?= $client['nombre_cliente'] ?></strong>.</li>
        <li class="beta-parrafo">Velar por que todas las acciones que se adelanten garanticen la protección del medio ambiente.</li>
        <li class="beta-parrafo">Garantizar la continuidad operativa de las actividades en las instalaciones y una rápida recuperación de los sectores afectados por eventuales emergencias.</li>
        <li class="beta-parrafo">Dispondrá todos sus esfuerzos para dar cumplimiento a la legislación vigente en materia de prevención y atención de emergencias.</li>
    </ul>

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

    <!-- <div>
        <a href="<?= base_url('/generatePdf_politicaEmergencias') ?>" target="_blank">
            <button type="button">PDF</button>
        </a>
    </div>
 -->
</body>

</html>