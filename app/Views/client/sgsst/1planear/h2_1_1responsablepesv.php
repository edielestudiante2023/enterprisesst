<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2.12.0 Formato de Asignación de Responsable del PESV</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
            background-color: white;
            color: #333;
        }

        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        @media print {
            .no-print {
                position: absolute;
                top: -9999px;
                /* Mueve el botón fuera de la página */
            }
        }



        h1,
        h2 {
            text-align: center;
            color: #2c3e50;
        }

        p {
            margin: 15px 0;
            text-align: justify;
        }

        .section {
            margin-bottom: 20px;
        }

        .section-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #34495e;
        }

        .signature,
        .logo {
            margin-top: 20px;
            text-align: center;
        }

        .signature img,
        .logo img {
            max-width: 200px;
            display: block;
            margin: 0 auto;
        }

        .signature p,
        .logo p {
            margin-top: 5px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
        }

        .logo {
            width: 20%;
            text-align: center;
        }

        .main-title {
            width: 50%;
            font-weight: bold;
            font-size: 14px;
        }

        .code {
            width: 30%;
            font-weight: bold;
            font-size: 14px;
        }

        .subtitle {
            font-weight: bold;
            font-size: 16px;
        }

        .right {
            text-align: left;
            padding-left: 10px;
        }

        footer {
            margin-top: 50px;
            background-color: white;
            padding: 20px;
            border-top: 1px solid #ccc;
            font-size: 14px;
            text-align: left;
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

        .alfa-contenedor {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .gamma-titulo {
            font-size: 1.5em;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }

        .zeta-tabla {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .zeta-tabla th,
        .zeta-tabla td {
            border: 1px solid #000;
            padding: 10px;
            text-align: left;
        }

        .zeta-tabla th {
            background-color: #f2f2f2;
        }

        .delta-lista {
            list-style-type: none;
            padding-left: 0;
            text-align: justify;
        }

        .delta-lista li::before {
            content: "• ";
            font-weight: bold;
            text-align: justify;
        }

        .beta-subtitulo {
            font-size: 1.2em;
            font-weight: bold;
            margin-top: 20px;
        }
    </style>
</head>

<body>

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
                <?= $policyType['type_name'] ?> <!-- Aquí se muestra el Nombre del Tipo de Política desde la tabla policy_types -->
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

    <div class="container">

        <h3 style="text-align: center;">NOMBRAMIENTO LÍDER DEL PLAN ESTRATÉGICO DE SEGURIDAD VIAL</h3>

        <br>
        <?php
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'Spanish_Spain'); // Configura el idioma español
?>

<p>FECHA: <strong><?= strftime('%d de %B de %Y', strtotime($client['fecha_ingreso'])); ?></strong></p>

        <p>
        <p>En las instalaciones de <strong><?= $client['nombre_cliente'] ?></strong>, el representante legal <strong><?= $client['nombre_rep_legal'] ?></strong>, con documento de identidad número <strong><?= $client['cedula_rep_legal'] ?></strong>, nombra al responsable y líder del diseño e implementación del Plan Estratégico de Seguridad Vial en observancia con la Ley 1503 de 2011 y la Resolución 40595 del 12 de julio de 2022 a <strong><?= $latestVigia['nombre_vigia'] ?></strong> con documento de identidad número <strong><?= $latestVigia['cedula_vigia'] ?></strong> Por un periodo de <strong><?= $latestVigia['periodo_texto'] ?></strong>, dando cumplimiento a la normativa vigente en materia de Seguridad y Salud en el Trabajo, incluyendo el <strong>Decreto 1072 de 2015</strong> y la <strong>Resolución 0312 de 2019</strong>, así como a las exigencias de la división de salud ocupacional del Ministerio de Trabajo. El empleador está obligado a proporcionar al menos cuatro horas semanales dentro de la jornada laboral normal para el funcionamiento de las actividades del vigía.

            <br><br>
            <?= $clientPolicy['policy_content'] ?>
        </p>

        <p><strong>OBJETIVO:</strong> Nombrar el líder del diseño e implementación del Plan Estratégico de Seguridad Vial de la empresa.</p>

        

        <p>El líder del Plan Estratégico de Seguridad Vial tiene poder de decisión en todo lo relacionado con la gestión de la seguridad vial. Es responsable del diseño e implementación del Plan de acuerdo con lo establecido en el capítulo I de la metodología para el diseño, implementación y verificación del PESV, cumpliendo con los requisitos legales aplicables en materia de seguridad vial y otros requisitos definidos por la empresa.</p>

              

        <h3 class="gamma-titulo">RESPONSABILIDADES</h3>
        <ul class="delta-lista">
            <li>Velar por el cumplimiento de la Política de Seguridad Vial de la Empresa.</li>
            <li>Realizar seguimiento y evaluación del PESV mediante el control de los indicadores diseñados para tal fin.</li>
            <li>Informar a la Gerencia del cumplimiento de objetivos y metas del PESV.</li>
            <li>Generar estrategias de formación para crear hábitos y conductas seguras en el uso de vías públicas o privadas.</li>
            <li>Elaborar informes periódicos para la Gerencia, el Ministerio de Transporte, organismos de tránsito u otros interesados, analizando el impacto y costo-beneficio.</li>
            <li>Establecer planes de ayuda mutua con autoridades de Tránsito, Policía Nacional, Defensa Civil, y otros organismos.</li>
            <li>Incluir los requisitos del PESV en el programa de auditorías internas.</li>
        </ul>

        <p>Además, sus responsabilidades pueden incluir la relación con partes externas en asuntos relacionados con seguridad vial y seguridad y salud en el trabajo.</p>

        <p>La Gerencia de la Empresa será la encargada de liderar la gestión del PESV, asignando y destinando los recursos necesarios para su diseño e implementación.</p>

       
        
    </div>

    
    <div class="signature-container">
        <div class="signature">
            <img src="<?= base_url('uploads/' . $client['firma_representante_legal']) ?>" alt="Firma rep. legal">
            <div class="name"><b><?= $client['nombre_rep_legal'] ?></b></div>
            <div class="title">Representante Legal</div>
        </div>
        <div class="signature">
            <img src="<?= base_url('uploads/' . $latestVigia['firma_vigia']) ?>" alt="Firma Vigía">
            <div class="name"><b><?= $latestVigia['nombre_vigia'] ?></b></div>
            <div class="title">Vigía</div>
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
    <!-- <div class="no-print">
        <a href="<?= base_url('/generatePdf_responsablePesv') ?>" target="_blank">
            <button type="button">PDF</button>
        </a>
    </div> -->

</body>

</html>