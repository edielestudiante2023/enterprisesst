<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2.5.3 Procedimiento de Comunicación Interna y Externa en Seguridad y Salud en el Trabajo (SG-SST)</title>
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
        <h2 class="beta-title">1. OBJETIVO</h2>
        <p class="gamma-parrafo">
            Establecer los mecanismos de comunicación entre los diferentes niveles internos de la organización y de origen externo, que permitan mantener el control permanente sobre las comunicaciones relativas a la seguridad y salud en el trabajo.
        </p>

        <h2 class="beta-title">2. ALCANCE</h2>
        <p class="gamma-parrafo">
            Este procedimiento aplica a todos los procesos y a las comunicaciones relativas al Sistema de Gestión de Seguridad y Salud en el Trabajo, que involucra a los trabajadores, contratistas, visitantes, proveedores, entidades de seguridad social y otros relacionados con el negocio.
        </p>

        <h2 class="beta-title">3. DEFINICIONES</h2>
        <ul class="delta-list">
            <li class="gamma-parrafo">Comunicación interna: Es toda la información verbal o escrita, que se da entre los diferentes niveles de la empresa, generada para la difusión, aclaración y transmisión de información, que busca mejorar el Sistema de Gestión.</li>
            <li class="gamma-parrafo">Comunicación externa: Son las directrices, regulaciones, peticiones e inquietudes emitidas por terceras partes como el gobierno, comunidad, contratistas, administradoras de riesgos laborales (ARL), entidades promotoras de salud (EPS), entidades, clientes o proveedores de la empresa, que deben llevar un proceso de recepción, control, resolución y acciones preventivas o correctivas si es el caso.</li>
            <li class="gamma-parrafo">Consulta: Es el acto de tratar un asunto con una o varias personas.</li>
            <li class="gamma-parrafo">Participación: Mecanismo establecido al interior de la empresa que tiene como finalidad involucrar a las partes interesadas en la toma de decisiones y en el desarrollo del sistema.</li>
            <li class="gamma-parrafo">Partes interesadas o Grupo de interés: Es cualquier organización, grupo o individuo que puede afectar o ser afectado por las actividades de una empresa u organización de referencia.</li>
        </ul>

        <h2 class="beta-title">4. ELEMENTOS DE PROTECCIÓN PERSONAL Y SEGURIDAD INDUSTRIAL</h2>
        <p class="gamma-parrafo">
            No Aplica.
        </p>

        <h2 class="beta-title">5. RESPONSABLES</h2>
        <ul class="delta-list">
            <li class="gamma-parrafo">Alta dirección: Dar las directrices y definir las comunicaciones a nivel gerencial.</li>
            <li class="gamma-parrafo">Gestión Humana: Encargado de identificar los mecanismos de las comunicaciones externas e internas y de definir los canales de comunicación.</li>
            <li class="gamma-parrafo">Líderes del proceso o jefes de áreas: Encargados de realizar la divulgación de las comunicaciones y los canales de comunicación, participación y consulta que tiene definida la organización.</li>
            <li class="gamma-parrafo">Responsable del Sistema de Gestión: Asegurar que se aplique el procedimiento en cada uno de los niveles de la organización y que se utilicen adecuadamente los diferentes mecanismos de comunicación.</li>
        </ul>

        <h2 class="beta-title">6. PROCEDIMIENTO</h2>

        <table class="zeta-table" style="width: 100%; text-align: left;">
            <tr>
                <th class="zeta-th">PROCEDIMIENTO</th>
                <th class="zeta-th">RESPONSABLE</th>
            </tr>
            <tr>
                <td class="zeta-td">6.1 GENERALIDADES</td>
                <td class="zeta-td">NA</td>
            </tr>
            <tr>
                <td class="zeta-td">
                    Las comunicaciones internas se pueden dar de la siguiente manera:<br>
                    <strong>a)</strong> Comunicación emitida por Directivos: Busca informar, dar instrucciones, objetivos o políticas de la empresa. Se debe seleccionar los medios para transmitir la comunicación y a quienes va dirigida.
                    <br>Mecanismos de comunicación:
                    <ul>
                        <li>Publicaciones institucionales (Cartelera)</li>
                        <li>Reuniones informativas</li>
                        <li>Correo electrónico</li>
                    </ul>
                    <br><strong>b)</strong> Comunicación emitida por empleados: Busca plantear ideas y sugerencias, así como dar retroalimentación a la comunicación emitida por directivos.
                    <br>Mecanismos de comunicación:
                    <ul>
                        <li>Reuniones periódicas</li>
                        <li>Correo electrónico</li>
                    </ul>
                    <br><strong>c)</strong> Comunicación entre trabajadores iguales: Es la que se genera entre cargos del mismo nivel. Su objetivo es unificar ideas para colaboración y trabajo en equipo.
                    <br>Mecanismos de comunicación:
                    <ul>
                        <li>Reuniones de áreas o grupos de trabajo</li>
                        <li>Charlas informativas</li>
                        <li>Correo electrónico</li>
                        <li>Reuniones con otras áreas</li>
                    </ul>
                </td>
                <td class="zeta-td">NA</td>
            </tr>
            <tr>
                <td class="zeta-td">
                    Las comunicaciones externas pueden ser:<br>
                    <strong>a)</strong> Comunicación estratégica: Aquella que direcciona la solicitud de información y permite ampliar el conocimiento en temas de seguridad y salud en el trabajo para avanzar en la ejecución del sistema de gestión.
                    <br>Mecanismos de comunicación:
                    <ul>
                        <li>Datos de evolución de las variables económicas</li>
                        <li>Cambios en la legislación nacional</li>
                        <li>Estudios realizados respecto a la salud de los trabajadores del mismo sector</li>
                    </ul>
                    <br><strong>b)</strong> Comunicación operativa: Se efectúa con personas fuera de la empresa que requieren comunicación para el desarrollo del Sistema de Gestión.
                    <br>Mecanismos de comunicación:
                    <ul>
                        <li>Oficios, comunicados, reglamentos, protocolos y manuales</li>
                    </ul>
                    <br><strong>c)</strong> Comunicación de difusión: Su finalidad es mostrar a la empresa como una institución que informa, dando a conocer los logros y avances de su Sistema de Gestión.
                    <br><strong>Mecanismos de comunicación:</strong>
                    <ul>
                        <li>Auditorías externas</li>
                    </ul>
                </td>
                <td class="zeta-td">NA</td>
            </tr>
            <tr>
                <td class="zeta-td">5.2 Identificar la necesidad y los temas de comunicaciones en la organización relacionados con la Seguridad y Salud en Trabajo.</td>
                <td class="zeta-td">Gerente de Gestión Humana, Analista Gestión Humana</td>
            </tr>
            <tr>
                <td class="zeta-td">5.3 Definir los canales y mecanismos de comunicación</td>
                <td class="zeta-td">Gerente de Gestión Humana, Analista Gestión Humana</td>
            </tr>
            <tr>
                <td class="zeta-td">
                    <ul>
                        <li>Correo electrónico</li>
                        <li>Celular</li>
                        <li>WhatsApp</li>
                        <li>Carteleras</li>
                        <li>Cartas, comunicados, folletos, boletines</li>
                        <li>Comité Gerencial</li>
                        <li>Reuniones del COPASST y Comité de Convivencia Laboral</li>
                        <li>Reuniones de proceso</li>
                    </ul>
                </td>
                <td class="zeta-td">NA</td>
            </tr>
            <tr>
                <td class="zeta-td">5.4 Mecanismos de participación y consulta</td>
                <td class="zeta-td">Gerente de Gestión Humana, Analista Gestión Humana</td>
            </tr>
            <tr>
                <td class="zeta-td">
                    <ul>
                        <li>Reuniones de equipos de trabajo</li>
                        <li>Formato de reporte de condiciones seguras</li>
                        <li>Formato de sugerencias</li>
                        <li>Novedades al COPASST y Comité de Convivencia Laboral</li>
                    </ul>
                </td>
                <td class="zeta-td">NA</td>
            </tr>
            <tr>
                <td class="zeta-td">5.5 Implementación de los mecanismos de comunicación</td>
                <td class="zeta-td">Líderes de Proceso, Analista de Gestión Humana</td>
            </tr>
            <tr>
                <td class="zeta-td">5.6 Gestión de las comunicaciones</td>
                <td class="zeta-td">Líderes de Proceso, Analista de Gestión Humana</td>
            </tr>
            <tr>
                <td class="zeta-td">5.7 Seguimiento al proceso de comunicaciones</td>
                <td class="zeta-td">Líderes de Proceso</td>
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
        <a href="<?= base_url('/generatePdf_comunicacionInterna') ?>" target="_blank">
            <button type="button">PDF</button>
        </a>
    </div> -->

</body>

</html>