<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>4.1.2 Procedimiento para Acciones Correctivas, Preventivas y de Mejora (ACPM)</title>
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
            /* max-width: 1000px; */
            margin: 0 auto;
           /*  padding: 20px; */
           /*  border: 1px solid #000; */
        }

        .primus-title {
            font-size: 24px;
            font-weight: bold;
            /* text-align: center; */
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
        <h3 class="primus-title">1. Objetivo</h3>
        <p class="secundus-paragraph">
            Establecer la metodología para formular, implementar, hacer seguimiento y evaluar las acciones correctivas, preventivas y de mejora orientadas a eliminar las causas de las No Conformidades reales y potenciales y a atender las oportunidades de mejora generadas en el marco del Sistema de Gestión de Seguridad y Salud en el Trabajo.
        </p>

        <!-- ALCANCE -->
        <h3 class="primus-title">2. Alcance</h3>
        <p class="secundus-paragraph">
            Inicia con la documentación del hallazgo por las diferentes fuentes de identificación, y finaliza con la evaluación de la eficacia de las acciones implementadas.
        </p>

        <!-- DEFINICIONES -->
        <h3 class="primus-title">3. Definiciones</h3>
        <p class="secundus-paragraph">
            <strong>Acción Correctiva (AC):</strong> Conjunto de acciones tomadas para eliminar la(s) causa(s) de una no conformidad detectada u otra situación indeseable.<br>
            <strong>Acción de Mejora:</strong> Hecho o situación que puede ser potencializado para incrementar el grado de satisfacción de los clientes o de las partes interesadas.<br>
            <strong>Plan de contingencia:</strong> Acciones que se deben desarrollar si llegara a materializarse un riesgo.<br>
            <strong>Auditoría:</strong> Proceso sistemático, independiente y documentado para obtener evidencias.<br>
            <strong>Adecuación:</strong> Determinación de la suficiencia total de las acciones, decisiones, etc., para cumplir los requisitos.<br>
            <strong>Conveniencia:</strong> Grado de alineación o coherencia del objeto de revisión con las metas y políticas organizacionales.<br>
            <strong>Corrección:</strong> Acción tomada para eliminar una no conformidad detectada.<br>
            <strong>Conformidad:</strong> Cumplimiento de un requisito.<br>
            <strong>No Conformidad (NC):</strong> Incumplimiento de un requisito.<br>
            <strong>Eficacia:</strong> Grado en el que se realizan las actividades planificadas y se alcanzan los resultados planificados.<br>
            <strong>Eficiencia:</strong> Relación entre el resultado alcanzado y los recursos utilizados.<br>
            <strong>Efectividad:</strong> Medida del impacto de la gestión tanto en el logro de los resultados planificados, como en el manejo de los recursos.<br>
            <strong>Hallazgo:</strong> Hecho relevante que se constituye en un resultado determinante en la evaluación de la evidencia.<br>
            <strong>Proceso:</strong> Conjunto de actividades relacionadas que transforman elementos de entrada en resultados.<br>
            <strong>Requisito:</strong> Necesidad o expectativa establecida, generalmente implícita u obligatoria.<br>
            <strong>Revisión:</strong> Actividad emprendida para asegurar la conveniencia, adecuación, eficacia, eficiencia y efectividad.<br>
            <strong>Reformular:</strong> Definir nuevas acciones para resolver las causas del hallazgo.<br>
            <strong>Reprogramar:</strong> Determinar un nuevo plazo para la ejecución de la actividad.<br>
            <strong>Verificar:</strong> Confirmación de que se han cumplido los requisitos especificados.
        </p>

        <!-- GENERALIDADES DEL PROCEDIMIENTO -->
        <h3 class="primus-title">4. Generalidades del Procedimiento</h3>
        <p class="secundus-paragraph">
            Algunas de las fuentes utilizadas en el procedimiento para generar acciones preventivas, correctivas y de mejora son:<br>
            - Auditorías Internas.<br>
            - Auditorías Externas.<br>
            - Autoevaluación.<br>
            - Indicadores.<br>
            - Revisiones por la Dirección.
        </p>

        <!-- DESCRIPCIÓN DE ACTIVIDADES -->
        <h3 class="primus-title">5. Descripción de Actividades</h3>
        <table class="tertia-table">
            <tr>
                <th>No</th>
                <th>Responsable</th>
                
                <th>Observaciones</th>
            </tr>
            <tr>
                <td>1</td>
                <td>Inicio</td>
              
                <td></td>
            </tr>
            <tr>
                <td>2</td>
                <td>Cualquier persona dentro de la organización (Gestión Humana, COPASST, Brigadistas)</td>
               
                <td>Se identifican las acciones correctivas, preventivas u oportunidades de mejora en los procesos internos, prestación de servicios, o inspecciones.</td>
            </tr>
            <tr>
                <td>3</td>
                <td>Asesor SST</td>
                
                <td>Describir la acción en el formato de registro de Acciones Correctivas y Preventivas.</td>
            </tr>
            <tr>
                <td>4</td>
                <td>Asesor SST</td>
              
                <td>Registrar en el listado maestro de acciones para la mejora continua y asignar un número consecutivo.</td>
            </tr>
            <tr>
                <td>5</td>
                <td>Líder de proceso o quien interviene en la no conformidad, Asesor SST</td>
                
                <td>Determinar las causas de la no conformidad mediante el uso de herramientas de análisis de causas.</td>
            </tr>
            <tr>
                <td>6</td>
                <td>Líder de proceso o quien interviene en la no conformidad</td>
              
                <td>Determinar un plan de acción para eliminar las causas de la no conformidad.</td>
            </tr>
            <tr>
                <td>7</td>
                <td>Líder de proceso</td>
                
                <td>Se efectúa el seguimiento a las acciones implementadas evaluando si se resolvió la no conformidad.</td>
            </tr>
            <tr>
                <td>8</td>
                <td>Asesor SST y líder de proceso involucrado</td>
               
                <td>Revisar la conveniencia, adecuación y eficacia de las acciones correctivas, preventivas y de mejora tomadas.</td>
            </tr>
            <tr>
                <td>9</td>
                <td>Asesor SST</td>
              
                <td>Evaluar si los resultados fueron eficaces; si no, proponer nuevas acciones y registrarlas en el formato</td>
            </tr>
            <tr>
                <td>10</td>
                <td>Asesor SST</td>
                
                <td>Si los resultados obtenidos fueron eficaces, se cierra la acción.</td>
            </tr>
            <tr>
                <td>11</td>
                <td>Fin</td>
                
                <td></td>
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

    <!-- <div>
        <a href="<?= base_url('/generatePdf_accionCorrectiva') ?>" target="_blank">
            <button type="button">PDF</button>
        </a>
    </div> -->

</body>

</html>