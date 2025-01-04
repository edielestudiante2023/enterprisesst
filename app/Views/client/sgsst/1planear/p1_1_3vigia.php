<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>1.1.3 Asignación de Vigía</title>
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

        <h3 style="text-align: center;">ACTA DE ASIGNACIÓN DE VIGÍA EN SEGURIDAD Y SALUD EN EL TRABAJO</h3>

        <br>
        <?php
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'Spanish_Spain'); // Configura el idioma español
?>

<p>FECHA: <strong><?= strftime('%d de %B de %Y', strtotime($client['fecha_ingreso'])); ?></strong></p>

        <p>
        <p>En las instalaciones de <strong><?= $client['nombre_cliente'] ?></strong>, el representante legal <strong><?= $client['nombre_rep_legal'] ?></strong>, con documento de identidad número <strong><?= $client['cedula_rep_legal'] ?></strong>, asigna como vigía en Seguridad y Salud en el Trabajo a <strong><?= $latestVigia['nombre_vigia'] ?></strong> con documento de identidad número <strong><?= $latestVigia['cedula_vigia'] ?></strong> Por un periodo de <strong><?= $latestVigia['periodo_texto'] ?></strong>, dando cumplimiento a la normativa vigente en materia de Seguridad y Salud en el Trabajo, incluyendo el <strong>Decreto 1072 de 2015</strong> y la <strong>Resolución 0312 de 2019</strong>, así como a las exigencias de la división de salud ocupacional del Ministerio de Trabajo. El empleador está obligado a proporcionar al menos cuatro horas semanales dentro de la jornada laboral normal para el funcionamiento de las actividades del vigía.
    
        <br><br>
        <?= $clientPolicy['policy_content'] ?>
    </p>

        </p>

        <h2>Funciones del Vigía</h2>

        <ol>
            <li>Proponer a la administración de la empresa o establecimiento de trabajo la adopción de medidas y el desarrollo de actividades que procuren y mantengan la salud en los lugares y ambientes de trabajo.</li>
            <li>Proponer y participar en actividades de capacitación en salud ocupacional dirigidas a trabajadores, supervisores y directivos de la empresa o establecimiento de trabajo.</li>
            <li>Colaborar con los funcionarios de entidades gubernamentales de salud ocupacional en las actividades que éstos adelanten en la empresa y recibir por derecho propio los informes correspondientes.</li>
            <li>Vigilar el desarrollo de las actividades que en materia de medicina, higiene y seguridad industrial debe realizar la empresa de acuerdo con el Reglamento de Higiene y Seguridad Industrial y las normas vigentes, promover su divulgación y observancia.</li>
            <li>Colaborar en el análisis de las causas de los accidentes de trabajo y enfermedades profesionales y proponer al empleador las medidas correctivas a que haya lugar para evitar su ocurrencia. Evaluar los programas que se hayan realizado.</li>
            <li>Visitar periódicamente los lugares de trabajo e inspeccionar los ambientes, máquinas, equipos, aparatos y las operaciones realizadas por el personal de trabajadores en cada área o sección de la empresa e informar al empleador sobre la existencia de factores de riesgo y sugerir las medidas correctivas y de control.</li>
            <li>Estudiar y considerar las sugerencias que presenten los trabajadores, en materia de medicina, higiene y seguridad industrial.</li>
            <li>Servir como organismo de coordinación entre empleador y los trabajadores en la solución de los problemas relativos a la salud ocupacional. Tramitar los reclamos de los trabajadores relacionados con la salud ocupacional.</li>
            <li>Solicitar periódicamente a la empresa informes sobre accidentalidad y enfermedades profesionales con el objeto de dar cumplimiento a lo estipulado en la presente resolución.</li>
            <li>Mantener un archivo de las actas de cada reunión y demás actividades que se desarrollen, el cual estará en cualquier momento a disposición del empleador, los trabajadores y las autoridades competentes.</li>
            <li>Recibir por parte de la alta dirección la comunicación de la política de seguridad y salud en el trabajo (artículo 2.2.4.6.5).</li>
            <li>Recibir por parte del empleador información sobre el desarrollo de todas las etapas del Sistema de Gestión de Seguridad de la Salud en el Trabajo (SG-SST) (artículo 2.2.4.6.8).</li>
            <li>Rendir cuentas internamente en relación con su desempeño (artículo 2.2.4.6.8).</li>
            <li>Dar recomendaciones para el mejoramiento del SG-SST (artículo 2.2.4.6.8).</li>
            <li>Participar en las capacitaciones que realice la Administradora de Riesgos Laborales (artículo 2.2.4.6.9).</li>
            <li>Revisión del programa de capacitación en Seguridad y Salud en el Trabajo (artículo 2.2.4.6.11).</li>
            <li>Recibir los resultados de las evaluaciones de los ambientes de trabajo y emitir recomendaciones (artículo 2.2.4.6.15).</li>
            <li>Apoyar la adopción de las medidas de prevención y control derivadas de la gestión del cambio (artículo 2.2.4.6.26).</li>
            <li>Participar en la planificación de las auditorías (artículo 2.2.4.6.29).</li>
            <li>Tener conocimiento de los resultados de la revisión de la alta dirección (artículo 2.2.4.6.31).</li>
            <li>Formar parte del equipo investigador de incidentes, accidentes de trabajo y enfermedades laborales (artículos 2.2.4.1.6 y 2.2.4.6.32).</li>
            <!-- Agregando funciones adicionales -->
            <li>Elaborar y mantener un plan de seguimiento de acciones correctivas y preventivas derivadas de los riesgos identificados en la organización.</li>
            <li>Realizar reportes periódicos de avances en la implementación de las recomendaciones de seguridad y salud en el trabajo a la alta dirección.</li>
        </ol>


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
        <a href="<?= base_url('/generatePdf_asignacionVigia') ?>" target="_blank">
            <button type="button">PDF</button>
        </a>
    </div> -->

</body>

</html>