<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2.2.1 Programa de Medicina Preventiva y del Trabajo</title>
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

        .alfa-title {
            font-size: 1.5em;
            font-weight: bold;

            margin-top: 20px;
        }

        .beta-titulo {
            font-size: 1.2em;
            font-weight: bold;
            margin-top: 15px;
        }

        .beta-parrafo {
            margin-bottom: 10px;
            text-align: justify;
        }

        .gamma-subtitulo {
            font-size: 1.1em;
            font-weight: bold;
            margin-top: 10px;
        }

        .delta-lista {
            margin-left: 20px;
        }

        .zeta-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .zeta-table,
        .zeta-th,
        .zeta-td {
            border: 1px solid black;
        }

        .zeta-th,
        .zeta-td {
            padding: 8px;
            text-align: left;
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

    <div class="beta-parrafo">
        <p class="alfa-title">1. OBJETO</p>
        <p>Mantener y promover la salud de los trabajadores, frente a los riesgos ocupacionales que pueden desencadenar enfermedades de origen laboral, mediante la consolidación de un sistema de información de seguridad y salud en el trabajo que permita obtener los insumos necesarios que alimenten el mismo.</p>

        <p class="alfa-title">2. ALCANCE</p>
        <p>Este procedimiento es aplicable a todos los procesos, servicios y/o actividades que desarrolle la organización <strong><?= $client['nombre_cliente'] ?></strong>.</p>

        <p class="alfa-title">3. DEFINICIONES</p>
        <ul class="delta-lista">
            <li><b>Medicina Preventiva:</b> Es el conjunto de actividades que se encargan de la promoción, protección, recuperación y rehabilitación de la salud de los trabajadores, así como la correcta ubicación del trabajador en una ocupación adaptada a su constitución fisiológica y sicológica. Se encarga del diagnóstico, prevención y control de enfermedades de origen común.</li>
            <li><b>Medicina del Trabajo:</b> Conjunto de actividades médicas y paramédicas destinadas a promover y mejorar la salud del trabajador. Evaluar su capacidad laboral y ubicarlo en lugares de trabajo de acuerdo a sus condiciones psicobiológicas. Se encarga del diagnóstico, prevención y control de enfermedades de origen laboral.</li>
            <li><b>Seguridad y Salud en el Trabajo:</b> Disciplina que trata de la prevención de las lesiones y enfermedades causadas por las condiciones de trabajo, así como de la promoción y el mantenimiento del bienestar físico, mental y social de los trabajadores.</li>
        </ul>

        <p class="alfa-title">4. RESPONSABLES</p>
        <ul class="delta-lista">
            <li><b>Responsable por el Mantenimiento y Control de este Procedimiento:</b> Coordinador de Seguridad y Salud en el Trabajo.</li>
            <li><b>Responsable por la Ejecución de este Procedimiento:</b> Coordinador de Seguridad y Salud en el Trabajo.</li>
        </ul>

        <p class="alfa-title">5. DOCUMENTOS RELACIONADOS</p>
        <ul class="delta-lista">
            <li><b>5.1.</b> Legislación vigente.</li>
            <li><b>5.2.</b> Norma Técnica Colombiana NTC 4115.</li>
            <li><b>5.3.</b> Decreto 1072 de 2015.</li>
        </ul>

        <p class="alfa-title">6. DESCRIPCIÓN</p>
        <p>En el Programa de Gestión en Medicina Preventiva y del Trabajo se deben diseñar, implementar y/o optimizar los siguientes procesos:</p>

        <ul class="delta-lista">
            <li><b>6.1. Realización y/o seguimiento al diagnóstico de condiciones de salud (DX):</b> La organización <strong><?= $client['nombre_cliente'] ?></strong> debe elaborar y/o actualizar el diagnóstico de condiciones de salud de sus trabajadores conforme a las resoluciones 2346 de 2007 y 1918 de 2009, para lo cual los trabajadores deben realizarse los exámenes médicos ocupacionales de ingreso, periódicos y de egreso.</li>
            <li><b>6.2. Sistemas de vigilancia epidemiológica ocupacional (SVE):</b> A través de los SVE se hará seguimiento a casos sospechosos de enfermedad laboral. Cada caso registrado deberá continuar con los procesos de diseño, implementación, seguimiento, intervención y evaluación.</li>
        </ul>

        <p class="gamma-subtitulo">Lesiones musculoesqueléticas:</p>
        <p>La intervención ergonómica analiza los esfuerzos y posturas en el entorno de trabajo, así como las demandas físicas y cognitivas que impone la tarea y el entorno laboral.</p>
        <p>El médico ocupacional debe seguir todos los casos pendientes de calificación por pérdida de capacidad laboral y notificar oportunamente cualquier enfermedad laboral calificada como tal. Es crucial cumplir con las recomendaciones y restricciones emitidas por las EPS o ARL.</p>

        <ul class="delta-lista">
            <li><b>1) Diseño del profesiograma:</b> El médico ocupacional diseña un profesiograma que relaciona el cargo, las funciones y los riesgos ocupacionales, para guiar las evaluaciones médicas.</li>
            <li><b>2) Enfermedades generales de interés ocupacional:</b> La organización busca prevenir no solo las enfermedades laborales, sino también las enfermedades generales importantes en el ámbito ocupacional, incluyendo acciones concertadas con las EPS.</li>
        </ul>

        <p class="gamma-subtitulo">Programas de promoción y prevención incluidos a través de las EPS:</p>
        <ul class="delta-lista">
            <li>1) Aplicación de vacunas en adultos según el programa ampliado de inmunización de la Secretaría de Salud local.</li>
            <li>2) Campañas de detección temprana de alteraciones de la agudeza visual.</li>
            <li>3) Campañas de detección temprana de alteraciones de la salud en población hasta 29 años.</li>
            <li>4) Campañas de detección temprana de alteraciones de la salud en población mayor de 45 años.</li>
            <li>5) Campañas de detección temprana del cáncer de cuello uterino.</li>
            <li>6) Campañas de detección temprana del cáncer de seno.</li>
            <li>7) Campañas de prevención en salud oral.</li>
            <li>8) Campañas de promoción de alimentación y nutrición saludables (ley 1355 de 2009).</li>
            <li>9) Campañas de promoción de hábitos de vida saludables, prevención de farmacodependencia, alcoholismo y tabaquismo (Resolución 1075 de 1992).</li>
            <li>10) Campañas y capacitación en temas relacionados con la sexualidad, salud reproductiva y anticoncepción.</li>
            <li>11) Cursos psico-profilácticos para mujeres gestantes.</li>
            <li>12) Inclusión de afiliados en grupos de hipertensión arterial y diabetes.</li>
            <li>13) Realizar visitas a los puestos de trabajo para identificar riesgos laborales y emitir informes correctivos a la gerencia.</li>
            <li>14) Diseñar y ejecutar programas para la prevención, detección y control de enfermedades relacionadas con el trabajo.</li>
            <li>15) Investigar y analizar enfermedades laborales para establecer medidas correctivas.</li>
            <li>16) Organizar e implantar un servicio eficiente de primeros auxilios.</li>
            <li>17) Mantener actualizadas las estadísticas de morbilidad y mortalidad, investigando sus posibles relaciones con las actividades laborales.</li>
            <li>18) Capacitar a los empleados en la promoción y prevención de enfermedades de origen común.</li>
            <li>19) Realizar actividades de recreación y deporte con todos los empleados de la organización.</li>
        </ul>

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
        <a href="<?= base_url('/generatePdf_medPreventiva') ?>" target="_blank">
            <button type="button">PDF</button>
        </a>
    </div>
 -->
</body>

</html>