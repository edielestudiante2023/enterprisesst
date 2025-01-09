<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>1.1.2 Asignación de Responsabilidades</title>
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

    <h3 style="text-align: center;">ASIGNACIÓN DE RESPONSABILIDADES EN SG-SST</h3>

        <br>

        <?php
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'Spanish_Spain'); // Configura el idioma español
?>

<p>FECHA: <strong><?= strftime('%d de %B de %Y', strtotime($client['fecha_ingreso'])); ?></strong></p>


        <p>

            El Sistema de Gestión en Seguridad y Salud en el trabajo de <strong><?= $client['nombre_cliente'] ?></strong> parte de la alta gerencia y su desarrollo efectivo se alcanzará en la medida que logre una concepción clara de la importancia de este en todos los niveles de la organización. Por ello, se plantean los siguientes niveles de participación, sus funciones y responsabilidades de acuerdo con la <strong>Resolución 0312 de 2019</strong> y demás normatividades vigentes.

            <?= $clientPolicy['policy_content'] ?>
        </p>

        <section>
            <h3>Empleadores</h3>
            <ul>
                <li><strong>Obligaciones:</strong> El empleador está obligado a proteger la seguridad y salud de los trabajadores, en cumplimiento con la <strong>Resolución 0312 de 2019</strong>.</li>
                <li><strong>Política de Seguridad y Salud en el Trabajo:</strong> Definir, firmar y divulgar la política escrita y asegurar que esté alineada con la normativa vigente.</li>
                <li><strong>Asignación de Responsabilidades:</strong> Asignar, documentar y comunicar responsabilidades específicas en SST a todos los niveles de la organización, incluyendo la alta dirección.</li>
                <li><strong>Recursos:</strong> Definir y asignar recursos financieros, técnicos y humanos necesarios para el diseño, implementación, revisión y mejora continua del SG-SST.</li>
                <li><strong>Normatividad:</strong> Operar bajo el cumplimiento de las normativas nacionales vigentes en materia de SST.</li>
                <li><strong>Rendición de cuentas:</strong> Implementar mecanismos de rendición de cuentas anuales de las personas con responsabilidades en el SG-SST.</li>
                <li><strong>Gestión de Peligros y Riesgos:</strong> Establecer procedimientos para identificar peligros, evaluar y valorar los riesgos, implementando controles preventivos.</li>
                <li><strong>Plan de Trabajo Anual:</strong> Desarrollar un plan anual que incluya objetivos, metas, recursos y cronograma de actividades, asegurando el cumplimiento de los estándares mínimos establecidos en la Resolución 0312.</li>
                <li><strong>Prevención de Riesgos:</strong> Implementar actividades de prevención de accidentes de trabajo y enfermedades laborales, en cumplimiento con la legislación.</li>
                <li><strong>Participación de Trabajadores:</strong> Fomentar la participación activa de los trabajadores y sus representantes en la implementación del SG-SST a través del Comité Paritario de Seguridad y Salud en el Trabajo (COPASST).</li>
                <li><strong>Capacitación:</strong> Garantizar un programa de capacitación basado en la identificación de riesgos y necesidades de los trabajadores, incluyendo un programa de inducción para nuevos empleados y contratistas.</li>
                <li><strong>Comunicación:</strong> Establecer canales de comunicación para asegurar la oportuna recolección de información y retroalimentación de los trabajadores en relación con los riesgos laborales.</li>
            </ul>
        </section>

        <section>
            <h3>Responsable del Sistema de Gestión de SST</h3>
            <ul>
                <li><strong>Planificación y Dirección:</strong> Planear, organizar, dirigir y aplicar el SG-SST, realizando una evaluación formal y documentada al menos una vez al año, siguiendo el ciclo de mejora continua <strong>PHVA (Planificar, Hacer, Verificar, Actuar)</strong>.</li>
                <li><strong>Informe a la alta dirección:</strong> Informar de manera documentada sobre el funcionamiento y resultados del SG-SST, incluyendo los hallazgos de las auditorías internas.</li>
                <li><strong>Promoción de Participación:</strong> Fomentar la participación de todos los trabajadores en la implementación y mejora del SG-SST, creando una cultura de seguridad proactiva.</li>
                <li><strong>Identificación de Riesgos:</strong> Elaborar y actualizar periódicamente la matriz de identificación de peligros, evaluación y valoración de riesgos, priorizando los controles necesarios.</li>
                <li><strong>Capacitación:</strong> Informar y coordinar sobre las necesidades de capacitación y entrenamiento en SST según los riesgos identificados en la matriz de riesgos.</li>
                <li><strong>Accidentes e Incidentes:</strong> Participar activamente en la investigación de accidentes de trabajo y enfermedades laborales, implementando planes de acción correctiva y preventiva.</li>
                <li><strong>Gestión de Recursos:</strong> Gestionar los recursos necesarios para el cumplimiento de los planes de SST y hacer seguimiento a los indicadores de desempeño del SG-SST.</li>
                <li><strong>Participación en el COPASST:</strong> Asistir y contribuir en las reuniones del Comité Paritario de Seguridad y Salud en el Trabajo.</li>
            </ul>
        </section>

        <section>
            <h3>Trabajadores</h3>
            <ul>
                <li><strong>Autocuidado:</strong> Procurar el cuidado integral de su salud y el cumplimiento de las normativas y medidas de prevención establecidas en el SG-SST.</li>
                <li><strong>Información:</strong> Proveer información clara, veraz y completa sobre su estado de salud y condiciones laborales.</li>
                <li><strong>Cumplimiento:</strong> Cumplir con las normas, reglamentos y procedimientos del SG-SST establecidos por la empresa.</li>
                <li><strong>Comunicación:</strong> Informar oportunamente a los superiores sobre los riesgos y peligros identificados en su lugar de trabajo, utilizando los canales de reporte establecidos.</li>
                <li><strong>Capacitación:</strong> Participar en las actividades de capacitación en SST definidas en el plan de capacitación anual del SG-SST.</li>
                <li><strong>Reporte de Incidentes:</strong> Reportar inmediatamente cualquier accidente, incidente o condición insegura en el lugar de trabajo.</li>
            </ul>
        </section>

        <section>
            <h3>Vigía de Seguridad y Salud en el Trabajo</h3>
            <ul>
                <li><strong>Investigación:</strong> Investigar accidentes, incidentes y enfermedades laborales, manteniendo registros detallados.</li>
                <li><strong>Capacitación:</strong> Participar y retroalimentar a los trabajadores sobre las actividades de capacitación en SST, asegurando que las necesidades de formación se cubran adecuadamente.</li>
                <li><strong>Inspección:</strong> Realizar inspecciones periódicas a las instalaciones, máquinas, equipos y herramientas para prevenir accidentes laborales.</li>
                <li><strong>Vigilancia:</strong> Vigilar el cumplimiento del SG-SST, el Reglamento de Higiene y Seguridad Industrial y demás normativas aplicables.</li>
            </ul>
        </section>

        <section>
            <h3>Brigadistas de Emergencia</h3>
            <ul>
                <li><strong>Brigada de Evacuación y Rescate:</strong> Señalizar y mantener despejadas las vías de evacuación, dirigir la evacuación de manera segura y técnica, y verificar que todos los trabajadores hayan salido de las instalaciones en simulacros y emergencias.</li>
                <li><strong>Brigada de Primeros Auxilios:</strong> Atender al personal lesionado, evaluar la gravedad de las lesiones y coordinar la evacuación hacia centros médicos especializados.</li>
                <li><strong>Brigada contra Incendios:</strong> Proceder de manera técnica y segura en la extinción de incendios y realizar inspecciones periódicas de los equipos de protección contra incendios.</li>
            </ul>
        </section>

        <div class="signature-container">
            <div class="signature">
                <img src="<?= base_url('uploads/' . $client['firma_representante_legal']) ?>" alt="Firma rep. legal">
                <div class="name"><b><?= $client['nombre_rep_legal'] ?></b></div>
                <div class="title">Representante Legal</div>
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
   <!--  <div class="no-print">
        <a href="<?= base_url('/generatePdf_asignacionResponsabilidades') ?>" target="_blank">
            <button type="button">PDF</button>
        </a>
    </div> -->

</body>

</html>