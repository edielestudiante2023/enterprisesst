<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2.2.0 Procedimiento para la Toma de Exámenes Médicos Ocupacionales</title>
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
            justify-content: space-evenly;
            align-items: center;
            margin-top: 20px;
        }

        .signature {
            text-align: center;
            width: 90%;
        }

        .signature img {
            max-width: 200px;
            height: auto;
        }

        .signature .name {
            font-weight: bold;
        }

        .signature .title {
            font-style: italic;
        }

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
                    SISTEMA DE GESTIÓN EN SEGURIDAD Y SALUD EN EL TRABAJO
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
                    <?php setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'Spanish_Spain'); ?>
                    Fecha: <?= strftime('%d de %B de %Y', strtotime($latestVersion['created_at'])); ?>
                </td>
            </tr>
        </table>
    </div>

    <div class="beta-parrafo">
        <p class="alfa-title">INTRODUCCIÓN</p>
        <p>Los exámenes médicos ocupacionales se constituyen como evaluaciones técnicas y médicas diseñadas para determinar la capacidad física, mental y psicosocial del colaborador, en cumplimiento de la <strong>Resolución 1843 de 2025</strong> del Ministerio del Trabajo (derogada la Resolución 2346 de 2007). Esta norma establece lineamientos claros para la realización de exámenes de pre-ingreso, periódicos, post-incapacidad, retorno laboral y egreso, reforzando el enfoque preventivo, no discriminatorio y la trazabilidad en la implementación de recomendaciones médicas, en el marco del SG-SST (Decreto 1072 de 2015).</p>

        <p class="alfa-title">OBJETIVO GENERAL:</p>
        <p>Establecer un procedimiento técnico, profesional y conforme al SG-SST para la toma de exámenes médicos ocupacionales de ingreso, periódicos, post-incapacidad, retorno laboral y egreso de todos los colaboradores de <strong><?= $client['nombre_cliente'] ?></strong>, de acuerdo con lo dispuesto en la Resolución 1843 de 2025.</p>

        <p class="alfa-title">OBJETIVOS ESPECÍFICOS:</p>
        <ul class="delta-lista">
            <li>Garantizar la realización de exámenes pre-ingreso para verificar la aptitud del aspirante antes de la firma del contrato, conforme al Artículo 7 de la Res. 1843/2025.</li>
            <li>Programar evaluaciones médicas periódicas con periodicidad máxima de tres (3) años según Artículo 9 de la Res. 1843/2025, salvo intervalos menores autorizados por el médico ocupacional.</li>
            <li>Ejecutar exámenes post-incapacidad cuando la incapacidad médica exceda de treinta (30) días, de conformidad con el Artículo 10 de la Res. 1843/2025.</li>
            <li>Realizar exámenes de retorno laboral para ausencias no médicas igual o superiores a noventa (90) días, conforme al Artículo 11 de la Res. 1843/2025.</li>
            <li>Efectuar exámenes de egreso dentro de los cinco (5) días calendario posteriores al retiro del colaborador, según Artículo 12 de la Res. 1843/2025.</li>
            <li>Garantizar la implementación de las recomendaciones médicas en un plazo máximo de veinte (20) días calendario, con registro de trazabilidad.</li>
            <li>Proteger la confidencialidad de la historia clínica ocupacional durante un mínimo de veinte (20) años, y entregar al empleador únicamente el concepto de aptitud y restricciones sin datos clínicos sensibles.</li>
            <li>Proporcionar al médico ocupacional un perfil de cargo completo y una matriz de riesgos actualizada, conforme al Artículo 5 de la Res. 1843/2025.</li>
        </ul>

        <p class="gamma-subtitulo">DESCRIPCIÓN DEL PROCEDIMIENTO</p>
        <p>El procedimiento se compone de las siguientes fases, alineadas con los artículos de la Resolución 1843 de 2025:</p>

        <p class="gamma-subtitulo">A. DEFINICIONES</p>
        <ul class="delta-lista">
            <li><b>Pre-ingreso:</b> Evaluación técnica y médica de ingreso al cargo, para determinar la aptitud del aspirante.</li>
            <li><b>Periódico:</b> Examen ocupacional realizado con periodicidad máxima de tres (3) años.</li>
            <li><b>Post-incapacidad:</b> Examen obligatorio previo al reingreso tras una incapacidad médica superior a treinta (30) días.</li>
            <li><b>Retorno Laboral:</b> Examen previo al reingreso por ausencias no médicas de noventa (90) días o más.</li>
            <li><b>Egreso:</b> Evaluación médica de salida realizada dentro de los cinco (5) días calendario posteriores a la terminación de la relación laboral.</li>
            <li><b>Seguimiento:</b> Evaluaciones adicionales orientadas al control de restricciones temporales y plan de rehabilitación.</li>
            <li><b>Concepto de Aptitud:</b> Resultado médico ocupacional que contempla adaptaciones y restricciones, sin diagnósticos clínicos sensibles.</li>
            <li><b>Historia Clínica Ocupacional:</b> Documento confidencial custodiado por veinte (20) años, reservado al trabajador, profesional de salud y autoridades competentes.</li>
        </ul>

        <p class="gamma-subtitulo">B. REQUISITOS PREVIOS</p>
        <ul class="delta-lista">
            <li>Disponer de un perfil de cargo completo, con funciones, riesgos identificados y matriz de peligros actualizada.</li>
            <li>Proveer al médico ocupacional la descripción técnica del puesto y cualquier estudio epidemiológico o indicador biológico relevante.</li>
            <li>Contar con médicos especialistas en salud ocupacional con licencia vigente.</li>
            <li>Asegurar la confidencialidad de la información clínica, conforme a la Ley 1581 de 2012 y los artículos 14 y 15 de la Res. 1843/2025.</li>
            <li>Registrar y documentar todas las citas, resultados y recomendaciones en el SG-SST, garantizando trazabilidad.</li>
        </ul>

        <p class="gamma-subtitulo">C. PROCEDIMIENTO DE EXAMEN PRE-INGRESO</p>
        <ul class="delta-lista">
            <li>El área de Talento Humano solicita a la IPS la cita para examen pre-ingreso, de acuerdo con el perfil de cargo.</li>
            <li>El aspirante asiste en la fecha y hora programada; la cita se registra en el sistema.</li>
            <li>La IPS realiza anamnesis, examen físico y pruebas complementarias según riesgo.</li>
            <li>El médico emite un concepto de aptitud con adaptaciones o restricciones temporales.</li>
            <li>El área de Talento Humano archiva el concepto de aptitud y comunica al aspirante el resultado.</li>
        </ul>

        <p class="gamma-subtitulo">D. PROCEDIMIENTO DE EXÁMENES PERIÓDICOS</p>
        <p>Cada colaborador será convocado a exámenes periódicos con periodicidad máxima de tres (3) años, ajustable según criterio profesional y nivel de riesgo, garantizando la<br>detección temprana de condiciones de salud ocupacional.<br>Las recomendaciones médicas se implementarán en un plazo máximo de veinte (20) días calendario, con registro de evidencias en el SG-SST.</p>

        <p class="gamma-subtitulo">E. PROCEDIMIENTO DE EXÁMEN POST-INCAPACIDAD</p>
        <ul class="delta-lista">
            <li>Cuando un colaborador presenta una incapacidad médica superior a treinta (30) días, se programa el examen post-incapacidad antes de su reincorporación.</li>
            <li>La IPS remite el concepto de aptitud post-incapacidad y las recomendaciones de rehabilitación.</li>
            <li>El empleador verifica la implementación de ajustes y restricciones antes del reingreso.</li>
        </ul>

        <p class="gamma-subtitulo">F. PROCEDIMIENTO DE EXÁMEN DE RETORNO LABORAL</p>
        <ul class="delta-lista">
            <li>Para ausencias no médicas iguales o superiores a noventa (90) días, el colaborador debe cumplir examen de retorno.</li>
            <li>El médico evalúa capacidad funcional y emite recomendaciones para asegurar un reingreso seguro.</li>
        </ul>

        <p class="gamma-subtitulo">G. PROCEDIMIENTO DE EXÁMEN DE EGRESO</p>
        <ul class="delta-lista">
            <li>Al término de la relación laboral, se solicita el examen de egreso y se programa dentro de los cinco (5) días calendario.</li>
            <li>La IPS envía el concepto de egreso; el área de Talento Humano registra la información en el SG-SST.</li>
            <li>Si no se recibe respuesta de la IPS dentro de los cinco (5) días, se documenta el seguimiento de la solicitud.</li>
        </ul>

        <p class="gamma-subtitulo">H. PROFESIOGRAMA</p>
        <p>El profesiograma consolidará los riesgos laborales y determinará las pruebas médicas obligatorias por cargo, alineado con los protocolos de vigilancia epidemiológica establecidos en el SG-SST.</p>

        <p class="gamma-subtitulo">I. MATRIZ DE RELACIÓN DE EXÁMENES</p>
        <p>Se debe registrar en el sistema la relación de exámenes realizados, los conceptos de aptitud y las restricciones aplicables para cada colaborador, asegurando su trazabilidad y confidencialidad.</p>
    </div>

    <footer>
        <h2>Historial de Versiones</h2>
        <style>
            footer table {
                width: 100%;
                border-collapse: collapse;
                table-layout: fixed;
            }

            footer table th,
            footer table td {
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
                width: 35%;
            }

            footer table th:nth-child(1),
            footer table td:nth-child(1) {
                width: 10%;
            }

            footer table th:nth-child(2),
            footer table td:nth-child(2),
            footer table th:nth-child(3),
            footer table td:nth-child(3),
            footer table th:nth-child(4),
            footer table td:nth-child(4) {
                width: 15%;
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

</body>

</html>