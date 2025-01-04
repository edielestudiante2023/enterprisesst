<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2.5.1 Procedimiento para el Control y Conservación de Documentos del SG-SST</title>
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

        .zeta-table, .zeta-th, .zeta-td {
            border: 1px solid black;
        }

        .zeta-th, .zeta-td {
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

    <div class="alfa-title" style="text-align: center;"><h2>Procedimiento para Control y Conservación de Documentos del SG-SST</h2></div>

    <p class="beta-subtitle"><strong>1. OBJETIVO</strong></p>
    <p class="beta-parrafo">Establecer el método para controlar y retener la documentación del Sistema de Gestión de la Seguridad y Salud en el Trabajo de la organización <strong><?= $client['nombre_cliente'] ?></strong></p>

    <p class="beta-subtitle"><strong>2. ALCANCE</strong></p>
    <p class="beta-parrafo">Este procedimiento aplica a toda la documentación del Sistema de Gestión de la Seguridad y Salud en el Trabajo de la organización <strong><?= $client['nombre_cliente'] ?></strong></p>

    <p class="beta-subtitle"><strong>3. DEFINICIONES</strong></p>
    <ul class="delta-lista">
        <li><strong>3.1 Documento:</strong> Información y su medio de soporte. Un documento es un testimonio material de un hecho o acto realizado en el ejercicio de sus funciones por instituciones o personas físicas, jurídicas, públicas o privadas, registrado en cualquier tipo de soporte.</li>
        <li><strong>3.2 Documento interno:</strong> Todos los documentos del SG-SST que se generen en la organización.</li>
        <li><strong>3.3 Documento externo:</strong> Documentos requeridos y utilizados en el SG-SST que son generados por entidades diferentes a la organización.</li>
        <li><strong>3.4 Documento obsoleto:</strong> Documento que no tiene vigencia o ha sido reemplazado por otro. Se identifica como tal en caso de que se conserven.</li>
        <li><strong>3.5 Control:</strong> El control es primordial en la administración, pues, aunque una organización cuente con magníficos procedimientos, se requiere organización en sus documentos.</li>
        <li><strong>3.6 Procedimiento:</strong> Forma específica para llevar a cabo una actividad o un proceso.</li>
        <li><strong>3.7 Registro:</strong> Documento que presenta resultados obtenidos o proporciona evidencia de actividades desempeñadas.</li>
    </ul>

    <p class="beta-subtitle"><strong>4. RESPONSABLES</strong></p>
    <ul class="delta-lista">
        <li><strong>4.1 Responsable del mantenimiento:</strong> Responsable del SG - Seguridad y Salud en el Trabajo.</li>
        <li><strong>4.2 Responsable de la ejecución:</strong> Responsable del SG - Salud en el Trabajo.</li>
    </ul>

    <p class="beta-subtitle"><strong>5. DOCUMENTOS RELACIONADOS</strong></p>
    <ul class="delta-lista">
        <li>5.1 Legislación vigente.</li>
        <li>5.2 Decreto 1072 de 2015.</li>
    </ul>

    <p class="beta-subtitle"><strong>6. ELABORACIÓN Y CONTROL DE DOCUMENTOS</strong></p>

    <p class="beta-subtitle">6.1 Elaboración de un documento</p>
    <p class="beta-parrafo">Al elaborar un nuevo documento del SG-SST o revisar uno existente, se debe cumplir con los siguientes requisitos: como por ejemplo el encabezado y el historial de versiones</p>

    <h2>Encabezado:</h2>

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

    <h2>Historial de Versiones:</h2>


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

    <p class="beta-subtitle">6.2 Codificación de los documentos</p>
    <p class="beta-parrafo">La identificación de los documentos se realizará mediante código y título, excepto los registros. A continuación, la tabla sinóptica de codificación:</p>

    <table style="margin: 0 auto; width: 80%; text-align: center;" class="zeta-table">
        <tr>
            <th class="zeta-th">Tipo de documento</th>
            <th class="zeta-th">Codificación</th>
        </tr>
        <tr>
            <td class="zeta-td">Sistema de Gestión de Seguridad y Salud en el Trabajo</td>
            <td class="zeta-td">SG-SST-XXX</td>
        </tr>
        <tr>
            <td class="zeta-td">Procedimientos</td>
            <td class="zeta-td">PRC-SST-XXX</td>
        </tr>
        <tr>
            <td class="zeta-td">Programas</td>
            <td class="zeta-td">PRG-SST-XXX</td>
        </tr>
        <tr>
            <td class="zeta-td">Planes</td>
            <td class="zeta-td">PLA-SST-XXX</td>
        </tr>
        <tr>
            <td class="zeta-td">Políticas</td>
            <td class="zeta-td">PLT-SST-XXX</td>
        </tr>
        <tr>
            <td class="zeta-td">Formatos</td>
            <td class="zeta-td">FT-SST-XXX</td>
        </tr>
        <tr>
            <td class="zeta-td">Reglamentos</td>
            <td class="zeta-td">REG-SST-XXX</td>
        </tr>
        <tr>
            <td class="zeta-td">Matriz</td>
            <td class="zeta-td">MZ-SST-XXX</td>
        </tr>
    </table>

    <p class="beta-parrafo"><strong>NOTA:</strong> Los programas y procedimientos de programas tendrán la misma estructura que los procedimientos descritos.</p>

    <p class="beta-subtitle">6.3 Revisión y aprobación</p>
    <p class="beta-parrafo">Una vez elaborado, el documento pasará a la fase de revisión y aprobación, siguiendo las normas establecidas.</p>

    <p class="beta-subtitle">6.4 Distribución controlada</p>
    <p class="beta-parrafo">La distribución se realizará con listas de distribución controladas, asegurando que los responsables reciban las versiones correspondientes.</p>

    <p class="beta-subtitle">6.5 Actualización</p>
    <p class="beta-parrafo">Cada cambio en el SG-SST implicará la revisión y actualización de los documentos afectados. Se mantendrá una lista de referencia con la última revisión aprobada y los responsables.</p>

    <p class="beta-subtitle">6.6 Archivo y conservación</p>
    <p class="beta-parrafo">Los documentos se archivarán en formato físico y digital, con un almacenamiento mínimo de 20 años, según lo estipulado por el Decreto 1072 de 2015.</p>



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
        <a href="<?= base_url('/generatePdf_documentosSgsst') ?>" target="_blank">
            <button type="button">PDF</button>
        </a>
    </div> -->

</body>

</html>