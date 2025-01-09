<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2.5.2 Procedimiento de Rendición de Cuentas en Seguridad y Salud en el Trabajo (SG-SST)</title>
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

        .alfa-title {
            font-size: 1.5em;
            font-weight: bold;
            text-align: center;
            margin-top: 20px;
        }

        .beta-subtitle {
            font-size: 1.2em;
            font-weight: bold;
            margin-top: 15px;
        }

        .beta-parrafo {
            margin-bottom: 10px;
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

    <div class="alpha-section">
        <h2 class="alpha-title">1. OBJETIVO</h2>
        <p class="alpha-paragraph">Establecer el procedimiento con el cual se definirá la metodología para la rendición de cuentas frente a las responsabilidades definidas en el Sistema de Gestión de Seguridad y Salud en el Trabajo por cada uno de los niveles de la Organización, con el fin de evaluar los avances en materia de Seguridad y Salud en el Trabajo, con miras a introducir mejoras en el Sistema de Gestión.</p>

        <h2 class="alpha-title">2. ALCANCE</h2>
        <p class="alpha-paragraph">El proceso de rendición de cuentas parte de las responsabilidades asignadas en el SG-SST. Aplica a todos los niveles de la Organización incluyendo la Alta Dirección, responsable del Sistema de Gestión de Seguridad y Salud en el Trabajo, COPASST, Comité de convivencia, Brigadistas de emergencias.</p>

        <h2 class="alpha-title">3. DEFINICIONES</h2>
        <p class="alpha-paragraph"><strong>Alta Dirección:</strong> Persona o grupo de personas que dirigen y controlan una empresa.</p>
        <p class="alpha-paragraph"><strong>Efectividad:</strong> Logro de los objetivos del sistema de gestión de seguridad y salud en el trabajo con la máxima eficacia y la máxima eficiencia.</p>
        <p class="alpha-paragraph"><strong>Eficacia:</strong> Es la capacidad de alcanzar el efecto que espera o se desea tras la realización de una acción.</p>
        <p class="alpha-paragraph"><strong>Eficiencia:</strong> Relación entre el resultado alcanzado y los recursos utilizados.</p>
        <p class="alpha-paragraph"><strong>Rendición de cuentas:</strong> Mecanismo por medio del cual las personas e instituciones informan sobre su desempeño a todos los interesados.</p>
        <p class="alpha-paragraph"><strong>COPASST:</strong> Comité Paritario de Seguridad y Salud en el Trabajo.</p>
        <p class="alpha-paragraph"><strong>Sistema de gestión de la seguridad y salud en el trabajo-SG-SST:</strong> El sistema de gestión de la seguridad y salud en el trabajo SG-SST consiste en el desarrollo de un proceso lógico y por etapas, basado en la mejora continua y que incluye la política, la organización, la planificación, la aplicación, la evaluación, la auditoría y las acciones de mejora.</p>

        <h2 class="alpha-title">4. ELEMENTOS DE PROTECCIÓN PERSONAL Y SEGURIDAD INDUSTRIAL</h2>
        <p class="alpha-paragraph">N/A</p>

        <h2 class="alpha-title">5. RESPONSABLES</h2>
        <p class="alpha-paragraph"><strong>QUIEN DEBE CONOCERLO:</strong> Todos los colaboradores.</p>
        <p class="alpha-paragraph"><strong>QUIEN DEBE EJECUTARLO:</strong> Todos los colaboradores.</p>
        <p class="alpha-paragraph"><strong>QUIEN DEBE HACERLO CUMPLIR:</strong> Gerente General, Gerente de Gestión Humana, Jefes de Área.</p>

        <h2 class="alpha-title">6. PROCEDIMIENTO</h2>
        <table class="alpha-table" style="width: 100%; text-align: left;">
            <tr>
                <th style="text-align: center;" class="beta-th">PROCEDIMIENTO</th>
                <th class="beta-th">RESPONSABLE</th>
            </tr>
            <tr>
                <td class="beta-td">6.1 CONDICIONES GENERALES</td>
                <td class="beta-td">NA</td>
            </tr>
            <tr>
                <td class="beta-td">6.1.1 A quienes se les haya delegado responsabilidades en el Sistema de Gestión de la Seguridad y Salud en el Trabajo SG-SST, deben rendir cuentas internamente en relación con su desempeño. Esta rendición de cuentas se podrá hacer a través de medios escritos, electrónicos, verbales o los que sean considerados por los responsables.</td>
                <td class="beta-td">NA</td>
            </tr>
            <tr>
                <td class="beta-td">6.1.3 La rendición se hará como mínimo anualmente y deberá quedar documentada en el formato FT-SST-014.</td>
                <td class="beta-td"></td>
            </tr>
            <tr>
                <td class="beta-td">6.1.4 Para facilitar el proceso de rendición de cuentas al interior de la empresa, los líderes de proceso o jefes de departamento o de área, rendirán cuentas sobre la participación de sus subordinados en las actividades de seguridad y salud en el trabajo.</td>
                <td class="beta-td"></td>
            </tr>
            <tr>
                <td class="beta-td">6.1.5 El informe general de rendición de cuentas será tenido en cuenta en los procesos de auditoría interna del SG-SST, así como en la revisión anual realizada por la Alta Dirección.</td>
                <td class="beta-td"></td>
            </tr>
            <tr>
                <td class="beta-td">6.1.6 Antes de iniciar la elaboración del informe consolidado de rendición de cuentas, el Analista de SST debe contar con la siguiente información:</td>
                <td class="beta-td">Asesor SST</td>
            </tr>
            <tr>
                <td class="beta-td">
                    <ul class="gamma-list">
                        <li>Evaluación inicial del SG-SST al inicio del periodo y al final del periodo.</li>
                        <li>El plan de trabajo programado y el realmente ejecutado.</li>
                        <li>Todas las evidencias de las actividades realizadas en el año.</li>
                        <li>El cálculo de los indicadores del SG-SST y de los programas implementados.</li>
                        <li>Informes de rendición de cuentas de quienes tienen responsabilidad en SST.</li>
                        <li>La ejecución presupuestal en seguridad y salud en el trabajo.</li>
                        <li>Informes de investigación de accidentes, incidentes y enfermedades laborales.</li>
                        <li>Informes de visitas realizadas por la ARL o entidades de control.</li>
                        <li>Reuniones del COPASST y Comité de Convivencia Laboral.</li>
                        <li>Acciones correctivas, preventivas y de mejora del periodo.</li>
                    </ul>
                </td>
                <td class="beta-td"></td>
            </tr>
            <tr>
                <td class="beta-td">6.2 IDENTIFICACIÓN Y EVALUACIÓN DE RESPONSABILIDADES DEL SISTEMA DE GESTIÓN SST</td>
                <td class="beta-td"></td>
            </tr>
            <tr>
                <td class="beta-td">6.2.1 Identificar las responsabilidades en seguridad y salud en el trabajo por cada uno de los niveles de la Organización. Formato FT-SST-028</td>
                <td class="beta-td">Gestión Humana Asesor SST</td>
            </tr>
            <tr>
                <td class="beta-td">6.2.2 Identificar los objetivos de los planes y programas en los cuales participa cada rol con base en los objetivos del SG SST.</td>
                <td class="beta-td">Gestión Humana Asesor SST</td>
            </tr>
            <tr>
                <td class="beta-td">6.2.3 Remitir los formatos diligenciados de la evaluación de las responsabilidades en SST al Analista del Sistema de Gestión SST.</td>
                <td class="beta-td">Gerentes, Líderes de proceso, Jefes de área</td>
            </tr>
            <tr>
                <td class="beta-td">6.3 INFORME DE RENDICIÓN DE CUENTAS</td>
                <td class="beta-td"></td>
            </tr>
            <tr>
                <td class="beta-td">6.3.1 Programar en el plan de trabajo anual la rendición de cuentas para cada uno de los niveles de la organización.</td>
                <td class="beta-td">Asesor SST</td>
            </tr>
            <tr>
                <td class="beta-td">6.3.2 Identificar las actividades programadas durante el año en las que participó y sus colaboradores, recolectando las evidencias respectivas.</td>
                <td class="beta-td">Líderes de Proceso, Jefes de área, COPASST, Comité de convivencia Laboral, Brigadistas</td>
            </tr>
            <tr>
                <td class="beta-td">6.3.3 Realizar la rendición de cuentas de su proceso diligenciando el formato FT-SST-014 y presentar el informe al Asesor de SST para su consolidación.</td>
                <td class="beta-td">Líderes de Proceso, Jefes de área, COPASST, Comité de convivencia Laboral, Brigadistas</td>
            </tr>
            <tr>
                <td class="beta-td">
                    <ul class="gamma-list">
                        <li>Cumplimiento y participación en las actividades del plan de trabajo Anual.</li>
                        <li>Identificación de peligros, evaluación y control de los riesgos.</li>
                        <li>Participación en actividades de capacitación, inducción y reinducción.</li>
                        <li>Asistencia a las reuniones programas por el COPASST y Comité de Convivencia Laboral.</li>
                        <li>Accidentes de Trabajo y enfermedades laborales que ocurrieron en el área y/o proceso.</li>
                        <li>Investigaciones de accidentes e incidentes laborales.</li>
                        <li>Entrega y uso de elementos de protección personal.</li>
                        <li>Participación en las inspecciones de seguridad.</li>
                        <li>Reporte de actos y condiciones inseguros.</li>
                        <li>Cumplimiento de los requisitos legales.</li>
                        <li>Cumplimiento de las recomendaciones médicas.</li>
                        <li>Cumplimiento de las medidas de prevención y recomendaciones de las inspecciones de seguridad.</li>
                        <li>Implementación del Plan de emergencias y participación en simulacros de evacuación.</li>
                        <li>Acciones correctivas, preventivas y de mejora.</li>
                        <li>Cumplimiento en las responsabilidades asignadas.</li>
                        <li>Cumplimiento de indicadores del SG SST.</li>
                    </ul>
                </td>
                <td class="beta-td"></td>
            </tr>
            <tr>
                <td class="beta-td">6.3.4 Enviar los registros digitales en Excel de rendición de cuentas vía E-mail a Gerente de Gestión Humana / Asesor de Seguridad y Salud en el trabajo.</td>
                <td class="beta-td">Líderes de Proceso, Jefes de área, COPASST, Comité de convivencia Laboral, Brigadistas</td>
            </tr>
            <tr>
                <td class="beta-td">6.4 CONSOLIDACIÓN INFORME DE RENDICIÓN DE CUENTAS</td>
                <td class="beta-td"></td>
            </tr>
            <tr>
                <td class="beta-td">6.4.1 Consolidar los resultados de la rendición de cuentas de cada uno de los niveles de la organización y elaborar un informe gerencial en una presentación en Word o PowerPoint en el que se tendrá en cuenta los siguientes aspectos:</td>
                <td class="beta-td">Asesor SST</td>
            </tr>
            <tr>
                <td class="beta-td">
                    <ul class="gamma-list">
                        <li>Comparación inicial y final de la autoevaluación de los estándares mínimos del SG SST.</li>
                        <li>Descripción de las actividades realizadas con respecto al plan de trabajo, medidas de intervención de la matriz de peligros y riesgos, planes y programas ejecutados durante el año, matriz de requisitos legales, plan de emergencias, COPASST, Comité de Convivencia Laboral, recomendaciones a la alta dirección.</li>
                        <li>Evaluación de responsabilidades por cada uno de los niveles de la organización.</li>
                    </ul>
                </td>
                <td class="beta-td"></td>
            </tr>
            <tr>
                <td class="beta-td">6.5 COMUNICACIÓN DE RESULTADOS DE LA RENDICIÓN DE CUENTAS</td>
                <td class="beta-td"></td>
            </tr>
            <tr>
                <td class="beta-td">6.5.1 Comunicar los resultados de la rendición de cuentas a la Gerencia General.</td>
                <td class="beta-td">Gestión Humana, Asesor SST</td>
            </tr>
            <tr>
                <td class="beta-td">6.5.2 Definir en conjunto con la Gerencia General las acciones de mejora en el desempeño del SG-SST, las cuales se dejan registradas en un acta de reunión.</td>
                <td class="beta-td">Gerente, Gestión Humana, Asesor SST</td>
            </tr>
            <tr>
                <td class="beta-td">6.5.3 Divulgar los resultados a todo el personal en los medios de comunicación definidos por la empresa.</td>
                <td class="beta-td">Asesor SST</td>
            </tr>
            <tr>
                <td class="beta-td">6.5.4 Archivar el registro de rendición de cuentas de cada uno de los procesos en el sistema de gestión documental de SST.</td>
                <td class="beta-td">Asesor SST</td>
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
        <a href="<?= base_url('/generatePdf_rendicionCuentas') ?>" target="_blank">
            <button type="button">PDF</button>
        </a>
    </div> -->

</body>

</html>