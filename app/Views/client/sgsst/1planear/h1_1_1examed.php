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
        <p class="alfa-title">INTRODUCCIÓN</p>
        <p>Los exámenes ocupacionales son prácticas médicas que buscan el bienestar del colaborador de manera individual y que orientan las acciones de gestión para mejorar las condiciones de salud y de trabajo, asegurando su adecuado monitoreo y cumplimiento de la resolución 2346 de 2007.</p>

        <p class="alfa-title">OBJETIVO GENERAL:</p>
        <p>Definir un procedimiento para realizar los exámenes médicos ocupacionales de ingreso, periódicos y de egreso, aplicables a <strong><?= $client['nombre_cliente'] ?></strong>.</p>

        <p class="alfa-title">OBJETIVOS:</p>
        <ul class="delta-lista">
            <li>Garantizar que todo el personal que ingresa a <strong><?= $client['nombre_cliente'] ?></strong> cuente con el certificado de aptitud aprobado para su labor, antes de la firma del inicio del contrato.</li>
            <li>Contribuir al diagnóstico temprano de posibles enfermedades de origen laboral o común, que puedan agravarse por las condiciones de trabajo.</li>
            <li>Asegurar la autorización para realizar el examen de egreso del trabajador que finaliza su contrato laboral.</li>
        </ul>

        <p class="gamma-subtitulo">DESCRIPCIÓN DEL PROCEDIMIENTO</p>
        <p>El procedimiento para la práctica de exámenes médicos constará de las siguientes fases:</p>

        <p class="gamma-subtitulo">A. DEFINICIONES</p>
        <ul class="delta-lista">
            <li><b>Evaluación médica de ingreso:</b> Son aquellas evaluaciones que se realizan para determinar las condiciones de salud física, mental y social del colaborador antes de su contratación, en función de las condiciones de trabajo a las que estaría expuesto.</li>
            <li><b>Evaluaciones médicas ocupacionales periódicas:</b> Garantizan que el colaborador se mantenga en condiciones de salud acorde con los requerimientos de las tareas y sin que las nuevas condiciones de exposición afecten su salud.</li>
            <li><b>Evaluación médica de egreso:</b> Se realizan al terminar la relación laboral, con el objetivo de valorar y registrar las condiciones de salud del colaborador.</li>
            <li><b>Perfil del Cargo:</b> Conjunto de demandas físicas, mentales y condiciones específicas requeridas para el desempeño de determinadas funciones o tareas.</li>
            <li><b>Reintegro laboral:</b> Reincorporación del colaborador a sus funciones tras una incapacidad o ausencia, con o sin modificaciones en sus condiciones laborales.</li>
            <li><b>Resumen de Historia Clínica Ocupacional:</b> Documento que presenta en forma breve datos relevantes relacionados con la exposición a riesgos y antecedentes laborales del colaborador.</li>
            <li><b>Valoraciones o pruebas complementarias:</b> Exámenes clínicos o paraclínicos para complementar un estudio médico.</li>
            <li><b>Examen clínico:</b> Examen médico básico que no requiere análisis o instrumentos de laboratorio.</li>
            <li><b>Examen paraclínico:</b> Exploraciones complementarias como exámenes de laboratorio, audiometrías, espirometrías, entre otros.</li>
        </ul>

        <p class="gamma-subtitulo">B. REQUISITOS PARA REALIZAR EXÁMENES MÉDICOS OCUPACIONALES:</p>
        <ul class="delta-lista">
            <li>Verificar que los exámenes sean realizados por médicos especialistas en salud ocupacional con licencia vigente.</li>
            <li>El responsable del SG-SST debe informar al médico sobre el perfil del cargo y los factores de riesgo a los que estará expuesto el colaborador.</li>
            <li>El médico debe generar un certificado de aptitud y entregar los resultados tanto al trabajador como a la empresa.</li>
            <li>Se debe garantizar el cumplimiento de la normatividad vigente sobre la confidencialidad de la historia clínica ocupacional.</li>
        </ul>

        <p class="gamma-subtitulo">C. DESCRIPCIÓN DEL PROCEDIMIENTO DE EXAMEN PARA EL INGRESO A LA COMPAÑÍA:</p>
        <ul class="delta-lista">
            <li>Talento Humano solicita a la IPS la asignación de cita para el examen de ingreso una vez se tiene el perfil adecuado para el cargo.</li>
            <li>El aspirante debe asistir a la cita en la fecha y hora programada.</li>
            <li>La IPS notifica vía mail los resultados del examen a Talento Humano.</li>
            <li>Si el resultado es "No apto", se emite un concepto de no contratación.</li>
            <li>Si el resultado es "Apto", se procede a la contratación y se archiva el examen en la hoja de vida del nuevo empleado.</li>
        </ul>

        <p class="gamma-subtitulo">D. DESCRIPCIÓN DEL PROCEDIMIENTO DE EXÁMENES PERIÓDICOS:</p>
        <p>Se realizarán a todos los colaboradores de acuerdo con el tipo, severidad y frecuencia de exposición a factores de riesgo. De esta manera, a todo el personal de la empresa, se programará exámenes médicos cada año. Sin embargo, este concepto será revisado a medida que se actualicen o modifiquen las condiciones de trabajo.

            Las valoraciones deberán dar como resultado, recomendaciones individuales para el trabajador y un informe de condiciones de salud, con un resumen estadístico de los hallazgos. Si el médico remite al colaborador a la EPS, especialmente por causa de una presunta enfermedad laboral, se deberá hacer seguimiento al cumplimiento por parte del colaborador del proceso asistencial y de las recomendaciones.
        </p>

        <p class="gamma-subtitulo">E. DESCRIPCIÓN DEL PROCEDIMIENTO DE EXÁMENES DE EGRESO:</p>
        <p>Se realizará a todos los colaboradores que se retiren de la empresa con el objetivo de verificar las condiciones de salud al momento de su retiro y revisar si requieren remisión a la EPS o ARL por sospecha de enfermedad laboral.
            La empresa debe emitir la solicitud de examen de egreso, dejando constancia del recibido de la autorización del examen por parte del excolaborador. Sin embargo, es responsabilidad del excolaborador hacerse el examen, en este caso si no hay ningún reporte por parte de la IPS durante los 5 días después de su retiro, la empresa entenderá que la persona renunció de forma libre a su derecho.
        </p>

        <p class="gamma-subtitulo">F. PROFESIOGRAMA:</p>
        <p>El profesiograma consolidará información sobre los riesgos laborales a los que está o estará expuesto el colaborador y el tipo y contenido de las evaluaciones médicas ocupacionales y pruebas complementarias que se le deban realizar. Estas evaluaciones se definirán por cargo o labor y se realizarán con carácter obligatorio y a cargo en su totalidad del empleador, previas al ingreso (pre ocupacionales), periódicas y de retiro y su periodicidad estará definida por el tipo, magnitud y frecuencia de exposición a cada factor de riesgo, así como al estado de salud del colaborador y quedará registrada en los protocolos de los sistemas de vigilancia epidemiológica o programas de gestión que se adelanten, teniendo en cuenta criterios técnicos y normativos vigentes.</p>

        <p class="gamma-subtitulo">G. MATRIZ RELACIÓN DE EXAMENES EN PERFIL SOCIODEMOGRÁFICO:</p>
        <p>Se deben registrar los exámenes realizados por colaborador con la relación de sus conceptos de salud. Además de esto se especifican las recomendaciones y/o restricciones que se deben tener en cuenta tanto por parte del colaborador como por parte de la empresa.</p>
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
        <a href="<?= base_url('/generatePdf_examenMedico') ?>" target="_blank">
            <button type="button">PDF</button>
        </a>
    </div> -->

</body>

</html>