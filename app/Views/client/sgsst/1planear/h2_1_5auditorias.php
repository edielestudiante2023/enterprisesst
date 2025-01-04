<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>4.1.5 Procedimiento de Auditorías Internas en Seguridad y Salud en el Trabajo (SST)</title>
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
        .gamma-container {
            width: 100%;
            padding: 20px;
            font-family: Arial, sans-serif;
        }

        .gamma-table {
            width: 100%;
            border-collapse: collapse;
        }

        .gamma-table th,
        .gamma-table td {
            border: 1px solid black;
            padding: 10px;
            text-align: left;
            /* width: 100%; */
        }

        .gamma-table th {
            background-color: #f2f2f2;
        }

        .gamma-paragraph {
            margin: 20px 0;
            text-align: justify;
        }

        h3 {
            font-size: 18px;
            /* text-align: center; */
            margin-bottom: 20px;
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

    <div class="gamma-container">
        <h3>OBJETIVO</h3>
        <p class="gamma-paragraph">
            Desarrollar auditorías a los procesos, procedimientos, programas del Sistema de gestión de Seguridad y salud en el Trabajo, con el fin de determinar su estado dentro del Sistema de Control Interno de COMPAÑIA INTERAMERICANA DE FIANZAS S.A.S, bajo la Planeación y Gestión por el mejoramiento continuo de los mismos.
        </p>

        <h3>ALCANCE</h3>
        <p class="gamma-paragraph">
            Este procedimiento es aplicable a los procesos de auditoria en seguridad y salud en el trabajo de la empresa.
        </p>

        <h3>DEFINICIONES</h3>
        <table class="gamma-table">
            <tr>
                <th>Término</th>
                <th>Definición</th>
            </tr>
            <tr>
                <td>Evaluación del Sistema de Control Interno</td>
                <td>Actividad desarrollada cuyo objetivo es verificar la existencia, nivel de desarrollo y el grado de efectividad del Control Interno en el cumplimiento de los objetivos del Sistema de Gestión de Seguridad y Salud en el Trabajo.</td>
            </tr>
            <tr>
                <td>Efectividad</td>
                <td>Medida del impacto de la gestión tanto en el logro de los resultados planificados, como en el manejo de los recursos utilizados y disponibles.</td>
            </tr>
            <tr>
                <td>Eficacia</td>
                <td>Grado en el que se realizan las actividades planificadas y se alcanzan los resultados planificados.</td>
            </tr>
            <tr>
                <td>Eficiencia</td>
                <td>Capacidad de producir el máximo de resultado con el mínimo de recursos, energía y tiempo.</td>
            </tr>
            <tr>
                <td>Papeles de trabajo</td>
                <td>Registros físicos o magnéticos que se pueden obtener del auditado y que sirven para evidenciar una observación o el cumplimiento de una actividad.</td>
            </tr>
            <tr>
                <td>Plan de Mejoramiento</td>
                <td>Es aquel aspecto que permite el mejoramiento continuo y el cumplimiento de los objetivos de la empresa. Integra las acciones de mejoramiento que, a nivel de su misión, objetivos, procesos, etc., deben operar en la empresa para fortalecer integralmente su desempeño institucional, y cumplir con su función, en los términos establecidos en la Constitución, y la Ley, teniendo en cuenta los compromisos adquiridos con los organismos de control fiscal, de control político y con los diferentes grupos de interés.</td>
            </tr>
            <tr>
                <td>Sistema de Control Interno</td>
                <td>Se entiende como el sistema interno por el esquema de la empresa, y es el conjunto de planes, métodos, principios, normas, procedimientos y mecanismos de verificación y evaluación adoptados por una entidad, con el fin de procurar que todas las actividades, operaciones y actuaciones, así como la administración de la información y los recursos, se realicen de acuerdo con las normas constitucionales y legales vigentes dentro de la políticas trazadas por la Dirección y en atención a las metas y objetivos previstos.</td>
            </tr>
        </table>

        <h3>GENERALIDADES DEL PROCEDIMIENTO</h3>
        <p class="gamma-paragraph">
            Las auditorías, clasificadas como Auditorías de Gestión - AG, se realizan a procesos, procedimientos, dependencias, Comités o cualquier tema que sea susceptible de evaluar.
            El estudio de la información que será objeto de auditoría debe realizarse de acuerdo con lo establecido en la documentación oficial, políticas, Sistema de Gestión SST e informes de las últimas auditorías realizadas, entre otros.
        </p>

        <ul class="gamma-paragraph">
            <li>Las auditorías que se realicen se deben orientar de acuerdo con lo siguiente:
                <ul>
                    <li>Desarrollo del alcance de la auditoría, aplicando técnicas como la revisión de registros y archivos, realización de pruebas o mesas de trabajo, verificación y confirmación de hechos y actuaciones de la empresa hasta cuando considere que se han cumplido los objetivos de la auditoría.</li>
                    <li>Obtención de evidencias mediante la aplicación de pruebas de control, analíticas o sustantivas, al funcionamiento de las actividades clave del sistema y los controles identificados durante la revisión efectuada.</li>
                    <li>Determinación y desarrollo de las observaciones, las cuales deben ser objetivas, relevantes, claras, precisas y verificables, contando con los soportes necesarios para sustentarlas.</li>
                    <li>Organización del archivo, el cual hace referencia a la recopilación de la información que sustenta o soporta el trabajo de la auditoría.</li>
                </ul>
            </li>
            <li>Las Auditoría de Gestión deben estar basadas en riesgos, lo que significa que el Auditor al momento de analizar lo relacionado con el tema objeto de auditoría y al elaborar el Programa de Auditoría debe establecer los riesgos que impedirían lograr las metas o resultados deseados del tema auditado. Esto con el fin de analizar en el desarrollo de la auditoría si el auditado posee los controles efectivos para mitigarlos.</li>
            <li>Al inicio de la auditoría de gestión, se debe enviar un correo al Auditado, con copia al Superior Jerárquico, indicando que se realizará una reunión con el fin de presentarle el objetivo, alcance, tiempo estimado y etapas de la auditoría.</li>
            <li>El Informe Preliminar se enviará al Auditado, con el fin de conocer los comentarios que considere pertinentes para aclarar u objetar los aspectos presentados o para manifestar su conformidad con el contenido de este. Es pertinente mencionar que este informe además de las observaciones incluirá las recomendaciones.</li>
            <li>En los casos en los cuales el auditado no esté de acuerdo con alguna observación, deberá enviar al Auditor la evidencia respectiva que la subsana y si hay diferencias significativas con el contenido del Informe, puede solicitar una reunión para debatirlas.</li>
            <li>En la comunicación en la que se remite el Informe Preliminar se le solicitará al Auditado que realice los comentarios que considere pertinentes y que relacione las actividades para subsanar las observaciones detectadas o evidenciadas, indicando el responsable y fecha de cumplimiento. El plazo para revisión de dicho informe y definición de las acciones de mejora por parte del Auditado estará entre tres (3) y cinco (5) días hábiles, dependiendo de la magnitud de este.</li>
            <li>Una vez recibidos los comentarios y las acciones, el Auditor deberá revisarlas y aprobarlas, verificando que sean pertinentes para subsanar lo observado y que las fechas de cumplimiento no superen doce (12) meses.</li>
            <li>En caso de que no haya observaciones que ameriten la formulación de acciones, se le informará al Auditado que puede realizar los comentarios que estime convenientes a través del correo electrónico, dentro de los dos (2) días siguientes al envío; transcurrido este término se emitirá el Informe Final.</li>
            <li>El Informe Final de la Auditoría interna incluirá las observaciones y las acciones que serán registradas en SST por el Auditado; así mismo, será enviado al auditado y a su superior jerárquico.</li>
            <li>Las conclusiones incluidas en los Informes deberán reflejar con claridad lo observado durante la realización de la auditoría, tanto positivo como negativo y se constituirán en un resumen ejecutivo del mismo.</li>
            <li>Los Informes Finales de la Auditoría deben ser precisos, concisos, objetivos, claros, soportados y oportunos, e incluirán las acciones definidas por el auditado para subsanar las debilidades detectadas.</li>
            <li>Cuando en la auditoría se detecte una situación de otra dependencia, que esté afectando la gestión del tema auditado, se remitirá por correo electrónico el Informe Final, indicando la sección que corresponda, con el fin de dar a conocer la debilidad y para que adelanten las acciones del caso para subsanarlas.</li>
        </ul>

        <h3>REALIZACIÓN DE LA AUDITORÍA</h3>

        <table class="gamma-table">
            <tr>
                <th>No.</th>
                <th>Actividad</th>
                <th>Responsable</th>
                <th>Registro</th>
            </tr>
            <tr>
                <td>1</td>
                <td>Realizar el estudio del tema relacionado con la actividad a auditar, a través de la consulta de la normatividad vigente aplicable a Seguridad y Salud en el Trabajo.</td>
                <td>Auditor</td>
                <td></td>
            </tr>
            <tr>
                <td>2</td>
                <td>Preparar el plan de Auditoría en el cual se detalla: Objetivo, alcance, riesgos a analizar, criterios, fuentes y actividades a realizar.</td>
                <td>Auditor</td>
                <td>Plan</td>
            </tr>
            <tr>
                <td>3</td>
                <td>Enviar comunicación al Auditado, adjuntando el Programa de Auditoría, con copia al Superior Jerárquico, indicando que se realizará una reunión con el fin de presentar el objetivo, alcance, tiempo estimado y etapas de la auditoría.</td>
                <td>Auditor</td>
                <td>Correo electrónico</td>
            </tr>
            <tr>
                <td>4</td>
                <td>Ejecutar el Programa de Auditoría, a través de la aplicación de pruebas y de diferentes técnicas de auditoría, que conduzcan a determinar los aspectos positivos y negativos. Analizar la información obtenida y verificar que lo establecido en el Programa de Auditoría se realice. Identificar y analizar los soportes, comentarios y notas tomadas para determinar cada observación y recomendación asociada. Redactar el Informe Preliminar y presentarlo.</td>
                <td>Auditor</td>
                <td>Papeles de trabajo, Informe Preliminar</td>
            </tr>
            <tr>
                <td>5</td>
                <td>Revisar y enviar el Informe Preliminar. Analizar el Informe Preliminar, en caso de ser necesario solicitar los ajustes respectivos al auditor. Una vez esté de acuerdo con el contenido del informe.</td>
                <td>Auditor</td>
                <td>Informe Preliminar</td>
            </tr>
            <tr>
                <td>6</td>
                <td>Revisar y enviar el Informe Final. Analizar el Informe Final y en caso de ser necesario solicitar ajustes. Una vez se encuentre conforme con el contenido del informe, enviarlo al Auditado.</td>
                <td>Auditor</td>
                <td>Correo electrónico, enviando el Informe Final</td>
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
        <a href="<?= base_url('/generatePdf_procedimientoAuditoria') ?>" target="_blank">
            <button type="button">PDF</button>
        </a>
    </div> -->

</body>

</html>