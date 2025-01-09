<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2.9.0 Procedimiento de Inspecciones Planeadas y No Planeadas en Seguridad y Salud en el Trabajo</title>
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

        .alfa-contenedor {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .gamma-titulo {
            font-size: 1.5em;
            font-weight: bold;
            
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
        }

        .delta-lista li::before {
            content: "• ";
            font-weight: bold;
        }

        .beta-subtitulo {
            font-size: 1.2em;
            font-weight: bold;
            margin-top: 20px;
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
        <h3 class="gamma-titulo">1. OBJETIVO</h3>
        <p>
            Establecer un método sistemático para el desarrollo, implementación, realización y seguimiento de las Inspecciones Planeadas y No Planeadas en cumplimiento con la normatividad legal vigente.
        </p>

        <h3 class="gamma-titulo">2. ALCANCE</h3>
        <p>
            Este procedimiento aplica desde la planeación hasta el seguimiento y ejecución de las acciones que resulten de las inspecciones realizadas.
        </p>

        <h3 class="gamma-titulo">3. BASE LEGAL</h3>
        <ul class="delta-lista">
            <li>Decreto 1072 del 2015</li>
            <li>Resolución 2013 de 1986, artículo 11 literal f</li>
            <li>Resolución 1016 de 1989, artículo 11 numeral 5, 8, 11 y 14</li>
            <li>Resolución 312 del 13 de febrero de 2019; capítulo III, artículo 16</li>
            <li>Norma Técnica Colombiana NTC 4114</li>
        </ul>

        <h3 class="gamma-titulo">4. DEFINICIONES</h3>
        <ul class="delta-lista">
            <li><strong>Inspecciones Planeadas:</strong> Se entiende por inspección planeada el procedimiento mediante el cual los trabajadores desarrollan de manera organizada, periódica, y minuciosa, revisiones e las instalaciones, equipos, herramientas, mobiliario y comportamientos, con la finalidad de determinar sus condiciones generales de seguridad y salud durante el desempeño de la actividad laboral.</li>
            <li><strong>Inspección No Planeada:</strong> Búsqueda de condiciones inseguras que no obedecen a una periodicidad y deben realizarse cada vez que se requiera disminuir fallas, anomalías, se reporte una condición insegura o se presente un incidente o accidente de trabajo</li>
            <li><strong>Condición Insegura:</strong> Toda circunstancia física que presente una desviación de lo establecido y que facilite la ocurrencia de un accidente.</li>
            <li><strong>Condición Subestándar:</strong> toda circunstancia física que presente una desviación de lo estándar o establecido y que facilite la ocurrencia de un accidente.</li>
            <li><strong>Inspección:</strong> Recorrido sistemático por un área, esto es con una periodicidad, instrumentos y responsables determinados previamente a su realización, durante el cual se pretende identificar condiciones subestándares.</li>
        </ul>

        <h3 class="gamma-titulo">5. INSTRUCTIVO Y RESPONSABILIDADES</h3>
        <table class="zeta-tabla">
            <thead>
                <tr>
                    <th>Instructivo</th>
                    <th>Responsable</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Realizar inspecciones planeadas y no planeadas de manera trimestral.</td>
                    <td>Comité Paritario de Seguridad y Salud en el Trabajo, Brigada de Emergencia</td>
                </tr>
                <tr>
                    <td>Inspección de condiciones de seguridad en instalaciones locativas (oficinas, áreas comunes).</td>
                    <td>Comité Paritario de Seguridad y Salud en el Trabajo, Brigada de Emergencia</td>
                </tr>
                <tr>
                    <td>Inspección de botiquín (Maletín de primeros auxilios, Camilla de Emergencia).</td>
                    <td>Comité Paritario de Seguridad y Salud en el Trabajo, Brigada de Emergencia</td>
                </tr>
                <tr>
                    <td>Inspección de equipos contra incendios (Extintores).</td>
                    <td>Comité Paritario de Seguridad y Salud en el Trabajo, Brigada de Emergencia</td>
                </tr>
                <tr>
                    <td>Elaborar anualmente el Cronograma de inspecciones y enviarlo a los miembros de la Brigada de emergencia y del Copasst.</td>
                    <td>Asesor de Seguridad y Salud en el Trabajo</td>
                </tr>
                <tr>
                    <td>Preparación de la inspección.</td>
                    <td>Comité Paritario de Seguridad y Salud en el Trabajo, Brigada de Emergencia</td>
                </tr>
                <tr>
                    <td>Revisar inspecciones anteriores para generar trazabilidad.</td>
                    <td>Comité Paritario de Seguridad y Salud en el Trabajo, Brigada de Emergencia</td>
                </tr>
                <tr>
                    <td>Realizar la inspección aplicando el formato correspondiente.</td>
                    <td>Comité Paritario de Seguridad y Salud en el Trabajo, Brigada de Emergencia</td>
                </tr>
                <tr>
                    <td>Verificar que las condiciones del lugar sean seguras.</td>
                    <td>Comité Paritario de Seguridad y Salud en el Trabajo, Brigada de Emergencia</td>
                </tr>
                <tr>
                    <td>Resaltar aspectos positivos e identificar mejoras generadas de la inspección.</td>
                    <td>Comité Paritario de Seguridad y Salud en el Trabajo, Brigada de Emergencia</td>
                </tr>
                <tr>
                    <td>Entregar el informe debidamente diligenciado.</td>
                    <td>Comité Paritario de Seguridad y Salud en el Trabajo, Brigada de Emergencia</td>
                </tr>
                <tr>
                    <td>Verificar planes de acción y compartir el informe con Gestión Humana si es necesario.</td>
                    <td>Comité Paritario de Seguridad y Salud en el Trabajo, Brigada de Emergencia</td>
                </tr>
                <tr>
                    <td>Reportar daños en instalaciones y/o equipos a la Gerente de Gestión Humana.</td>
                    <td>Comité Paritario de Seguridad y Salud en el Trabajo, Brigada de Emergencia</td>
                </tr>
                <tr>
                    <td>Para equipos de emergencia, ejecutar los planes de acción establecidos.</td>
                    <td>Comité Paritario de Seguridad y Salud en el Trabajo, Brigada de Emergencia</td>
                </tr>
                <tr>
                    <td>Realizar seguimiento y verificar la efectividad de los planes de acción.</td>
                    <td>Asesor de Seguridad y Salud en el Trabajo</td>
                </tr>
                <tr>
                    <td>Archivar el informe de inspección en el sistema de gestión de Seguridad y Salud en el Trabajo.</td>
                    <td>Analista de Gestión Humana</td>
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

    <!-- <div>
        <a href="<?= base_url('/generatePdf_inspeccionPlanynoplan') ?>" target="_blank">
            <button type="button">PDF</button>
        </a>
    </div> -->

</body>

</html>