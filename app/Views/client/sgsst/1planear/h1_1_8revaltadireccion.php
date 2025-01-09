<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>4.1.1 Procedimiento de Revisión por la Alta Dirección del SG-SST</title>
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
        .primus-container {
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
            padding: 00px;
            
        }

        

        .secundus-paragraph {
            font-size: 16px;
            text-align: justify;
            margin-bottom: 20px;
        }

        .tertia-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .tertia-table td,
        .tertia-table th {
            border: 1px solid #000;
            padding: 10px;
            text-align: left;
        }

        .tertia-table th {
            background-color: #f2f2f2;
        }

       

        .primus-title {
            font-size: 15px;
            font-weight: bold;
          
            margin-bottom: 20px;
        }

        .secundus-paragraph {
            font-size: 16px;
            text-align: justify;
            margin-bottom: 20px;
        }

        .tertia-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .tertia-table td,
        .tertia-table th {
            border: 1px solid #000;
            padding: 10px;
            text-align: left;
        }

        .tertia-table th {
            background-color: #f2f2f2;
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

    <div class="primus-container">
        

        <!-- OBJETIVO -->
        <h3 class="primus-title">1. OBJETIVO</h3>
        <p class="secundus-paragraph">
            Definir los lineamientos para el desarrollo de la revisión del Sistema de Gestión SST, por parte de la Alta Dirección con el objeto de verificar el grado de implementación y cumplimiento de la política, los objetivos de Seguridad y Salud en el Trabajo y el control de los riesgos.
        </p>

        <!-- ALCANCE -->
        <h3 class="primus-title">2. ALCANCE</h3>
        <p class="secundus-paragraph">
            Aplica a todas las actividades y procesos que hacen parte del Sistema de Gestión de SST y es liderada por la Alta Gerencia.
        </p>

        <!-- DEFINICIONES -->
        <h3 class="primus-title">3. DEFINICIONES</h3>
        <p class="secundus-paragraph">
            • Acciones Correctivas: Acción tomada para eliminar la causa de una no conformidad detectada u otra situación indeseable.<br>
            • Acciones Preventivas: Acción tomada para eliminar la causa de una no conformidad potencial u otra situación potencialmente indeseable.<br>
            • Auditoría: proceso sistemático, independiente y documentado para obtener evidencias y evaluarlas de manera objetiva con el fin de determinar el grado en que se cumplen los criterios de auditoria y eficiente.<br>
            • Alta Dirección: Persona o grupo de personas que dirigen y controlan una organización.<br>
            • Efectividad: Medida del impacto de la gestión tanto en el logro de los resultados planificados, como en el manejo de los recursos utilizados y disponibles.<br>
            • Eficacia: Grado en el que se realizan las actividades planificadas y se alcanzan los resultados planificados.<br>
            • Eficiencia: Relación entre el resultado alcanzado y los recursos utilizados.<br>
            • Mejora Continua: Acción permanente realizada, con el fin de aumentar la capacidad para cumplir los requisitos y optimizar el desempeño de los procesos.<br>
            • Revisión: Actividad emprendida para asegurar la conveniencia, adecuación, eficacia, eficiencia y efectividad del tema objeto de la revisión, para alcanzar unos objetivos establecidos.<br>
            • Verificación: Confirmación, mediante la aportación de evidencia objetiva, de que se han cumplido los requisitos específicos.
        </p>

        <!-- RESPONSABLES -->
        <h3 class="primus-title">4. RESPONSABLES</h3>
        <table class="tertia-table">
            <tr>
                <th>Rol</th>
                <th>Responsabilidad</th>
            </tr>
            <tr>
                <td>Gerente General, Asesor SST, COPASST, Gerente de Gestión Humana</td>
                <td>Quien debe conocerlo</td>
            </tr>
            <tr>
                <td>Gerente General, Asesor de SST</td>
                <td>Quien debe ejecutarlo</td>
            </tr>
            <tr>
                <td>Gerente General</td>
                <td>Quien debe hacerlo cumplir</td>
            </tr>
        </table>
    </div>

    <div class="primus-container">

        <!-- PROCEDIMIENTO -->
        <h3 class="primus-title">5. PROCEDIMIENTO</h3>

        <table class="tertia-table">
            <tr>
                <th>Procedimiento</th>
                <th>Responsable</th>
            </tr>
            <tr>
                <td>5.1.1 La alta dirección debe revisar el SG-SST al menos una vez al año para asegurar su conveniencia, adecuación y eficacia continua, basándose en modificaciones de procesos, resultados de auditorías y otros informes.</td>
                <td>Gerencia General</td>
            </tr>
            <tr>
                <td>5.1.2 La revisión debe permitir analizar estrategias, recursos, cumplimiento de objetivos, y la efectividad del SG-SST.</td>
                <td>NA</td>
            </tr>
            <tr>
                <td>5.2.1 Definir la fecha de la revisión por la alta dirección posterior al ciclo de auditorías.</td>
                <td>Alta Dirección, Gerente Gestión Humana</td>
            </tr>
            <tr>
                <td>5.3.1 Recolectar información necesaria para la revisión, incluyendo política SST, auditorías, NC, acciones correctivas, entre otros.</td>
                <td>Asesor SST, COPASST</td>
            </tr>
            <tr>
                <td>5.4 Organizar la reunión y la información para la revisión por la Alta Dirección.</td>
                <td>Asesor SST</td>
            </tr>
            <tr>
                <td>5.5 Conducir la reunión de revisión, presentar informes y validar información.</td>
                <td>Gerente General, Asesor SST</td>
            </tr>
            <tr>
                <td>5.6 Comunicar los resultados de la revisión a las partes interesadas.</td>
                <td>Gerente General</td>
            </tr>
            <tr>
                <td>5.7 Definir e implementar un plan de acción basado en los resultados de la revisión.</td>
                <td>Asesor SST</td>
            </tr>
            <tr>
                <td>5.8 Verificar y hacer seguimiento al cumplimiento del plan de acción.</td>
                <td>Gerente General</td>
            </tr>
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

   <!--  <div>
        <a href="<?= base_url('/generatePdf_revisionAltagerencia') ?>" target="_blank">
            <button type="button">PDF</button>
        </a>
    </div> -->

</body>

</html>