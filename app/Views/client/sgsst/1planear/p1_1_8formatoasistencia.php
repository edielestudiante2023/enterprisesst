<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>1.1.8 Formato de Asistencia</title>
    <style>
        /* Estilos aplicados al body */
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

        /* ********************ESTILOS DEL FORMATO************************************* */

        body_alfa {
            font-family: Arial, sans-serif;
            margin: 0 auto;
            padding: 20px;
        }

        table_beta {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
            /* Permitir que las columnas se ajusten */
        }

        th_gamma,
        td_delta {
            border: 1px solid black;
            padding: 0 0 5px 0;
            /* Aumentamos el padding para mayor visibilidad */
            text-align: center;
        }

        th_gamma {
            background-color: #f2f2f2;
            text-align: center;
        }

        .center_epsilon {
            text-align: left;
        }

        .header-table_eta td_delta {
            padding: 1px;
            text-align: center;
        }

        .objective-box_theta {
            height: 5px;
            text-align: left;
            vertical-align:text-top;
        }

        .attendees-table_iota td_delta {
            height: 5px;
        }

        .full-width {
            width: 100%;
            align-items:last center;
        }

        .comentarios-table {
            width: 100%;
            border-collapse: collapse;
        }

        .comentarios-table th,
        .comentarios-table td {
            border: 1px solid black;
            padding: 1px;
            text-align: left;
        }

        .comentarios-table th {
            background-color: #d3d3d3;
            text-align: left;
        }

        .nota {
            background-color: #d3d3d3;
            text-align: center;
            padding: 10px;
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

    <body class="body_alfa">

        <table class="header-table_eta table_beta full-width">
            <tr>
                <th class="center_epsilon" colspan="3">INDUCCIÓN  ______</th>
                <th class="center_epsilon" colspan="3">COMITÉ  ______</th>
                <th class="center_epsilon" colspan="3">PAUSA ACTIVA  ______</th>
                <th class="center_epsilon" colspan="3">OTRO  ________________________ </th>
            </tr>
            <tr>
                <th class="center_epsilon" colspan="3">FECHA</th>
                <th class="center_epsilon" colspan="3">LUGAR:</th>
                <th class="center_epsilon" colspan="3">HORA:</th>
                <th class="center_epsilon" colspan="3">CIUDAD:</th>
            </tr>
            <tr>
                <td class="center_epsilon">DÍA</td>
                <td class="center_epsilon">MES</td>
                <td class="center_epsilon">AÑO</td>
                <td colspan="3">TEMA:</td> <!-- Lugar -->
                <td colspan="3">DURACIÓN:</td> <!-- Hora -->
                <td colspan="3"># ASISTENTES:</td> <!-- Ciudad -->
            </tr>
            <tr>
                <td colspan="6">Responsable:</td>
                <td colspan="6">Cargo / Proveedor:</td>
            </tr>
            <!-- <tr>
                <td colspan="6">Formador y/o Capacitador 2:</td>
                <td colspan="6">Cargo / Proveedor:</td>
            </tr> -->
            
            <tr>
                <td colspan="12" class="objective-box_theta">Objetivo de la Actividad:</td>
            </tr>
        </table>

        <br>

        <table class="attendees-table_iota table_beta full-width">
            <thead>
                <tr>
                    <th class="center_epsilon">Nº</th>
                    <th class="center_epsilon">CEDULA</th>
                    <th class="center_epsilon">ASISTENTES (Nombre y Apellidos)</th>
                    <th class="center_epsilon">CARGO</th>
                    <th class="center_epsilon">FIRMA</th>
                </tr>
            </thead>
            <tbody>
                <!-- Repite las filas vacías para el listado de asistentes -->
                <tr>
                    <td class="center_epsilon">1</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td class="center_epsilon">2</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td class="center_epsilon">3</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td class="center_epsilon">4</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td class="center_epsilon">5</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td class="center_epsilon">6</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td class="center_epsilon">7</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td class="center_epsilon">8</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td class="center_epsilon">9</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td class="center_epsilon">10</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>



        <p><strong>Protección de Datos Personales:</strong> En cumplimiento de la Ley 1581 de 2012, el registro de sus datos personales en el presente documento se realiza con el objetivo de autorizar a <strong><?= $client['nombre_cliente'] ?></strong> en la recolección, almacenamiento y uso de la información que se registra en este documento.</p>

        <p>Como titular de la información, tiene derecho a conocer, actualizar y rectificar sus datos personales, solicitar prueba de la autorización otorgada para su tratamiento, ser informado sobre el uso que se le ha dado a estos, presentar quejas ante entidades regulatorias locales por infracción al Régimen de Habeas Data, revocar la autorización y/o solicitar la supresión de sus datos en los casos en que sea procedente, así como acceder a ellos en forma gratuita.</p>

        <table class="comentarios-table">
            <tr>
                <th style="width: 20%;">COMENTARIOS</th>
                <td style="width: 80%;"></td>
            </tr>
            <tr>
                <td colspan="2" class="nota">
                    Nota: Este documento deberá ser enviado a Recursos Humanos con el objetivo de dejar constancia de las actividades y/o entrenamientos realizados a los colaboradores de la compañía.
                </td>
            </tr>
        </table>
    </body>



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
        <a href="<?= base_url('/generatePdf_formatoAsistencia') ?>" target="_blank">
            <button type="button">PDF</button>
        </a>
    </div> -->

</body>

</html>