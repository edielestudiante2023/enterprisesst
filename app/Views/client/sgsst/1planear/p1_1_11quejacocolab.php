<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>1.1.11 Formato quejas de situaciones que pueden constituir acoso laboral</title>
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

        /* ********************ESTILOS DEL FORMATO************************************* */

        .container_alfa {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0px;
        }

        .table_container_beta {
            width: 100%;
            border-collapse: collapse;
        }

        .table_header_gamma {
            background-color: #f2f2f2;
            text-align: left;
        }

        .table_row_delta {
            border: 1px solid #000;
        }

        .table_cell_epsilon {
            padding: 10px;
            border: 1px solid #000;
        }

        .signature_container_eta {
            display: flex;
            justify-content: center;
            /* Centrar horizontalmente */
            margin-top: 20px;
        }

        .signature_label_theta {
            display: block;
            margin-bottom: 5px;
        }

        .signature_box_zeta {
            border-bottom: 1px solid #000;
            width: 200px;
            height: 30px;
        }

        .full_width_iota {
            width: 100%;
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


    <div class="container_alfa">
        <h3 style="text-align: center;">FORMATO QUEJAS DE SITUACIONES QUE PUEDEN CONSTITUIR ACOSO LABORAL O PARA PRESENTAR SUGERENCIAS</h3>
        <table class="table_container_beta">
            <tr class="table_row_delta">
                <th class="table_cell_epsilon table_header_gamma" style="width: 50%;">Campo</th>
                <th class="table_cell_epsilon table_header_gamma" style="width: 50%;">Detalle</th>
            </tr>
            <tr class="table_row_delta">
                <td class="table_cell_epsilon" style="width: 50%;">Nombre del empleado:</td>
                <td class="table_cell_epsilon" style="width: 50%;"></td>
            </tr>
            <tr class="table_row_delta">
                <td class="table_cell_epsilon" style="width: 50%;">Documento Identidad:</td>
                <td class="table_cell_epsilon" style="width: 50%;"></td>
            </tr>
            <tr class="table_row_delta">
                <td class="table_cell_epsilon" style="width: 50%;">Área:</td>
                <td class="table_cell_epsilon" style="width: 50%;"></td>
            </tr>
            <tr class="table_row_delta">
                <td class="table_cell_epsilon" style="width: 50%;">Cargo:</td>
                <td class="table_cell_epsilon" style="width: 50%;"></td>
            </tr>
            <tr class="table_row_delta">
                <td class="table_cell_epsilon" style="width: 50%;">Correo electrónico:</td>
                <td class="table_cell_epsilon" style="width: 50%;"></td>
            </tr>
            <tr class="table_row_delta">
                <td class="table_cell_epsilon" style="width: 50%;">Fecha diligenciamiento (dd/mm/aaaa):</td>
                <td class="table_cell_epsilon" style="width: 50%;"></td>
            </tr>
        </table>

        <h3>INSTRUCCIONES DE DILIGENCIAMIENTO</h3>
        <p>Para presentar queja diligencia el numeral 1. Para sugerencia diligencie numeral 2.</p>

        <h3>1. HECHOS QUE CONSTITUYEN LA QUEJA</h3>
        <table class="table_container_beta">
            <tr class="table_row_delta">
                <td class="table_cell_epsilon" colspan="2">Describa todas las situaciones en los que se identifiquen las circunstancias, personas (quién o quiénes), tiempo (cuándo), modo (cómo), lugar (dónde) y cualquier otra que considere pertinente.</td>
            </tr>
            <tr class="table_row_delta">
                <td class="table_cell_epsilon" colspan="2" style="height: 150px;"></td>
            </tr>
        </table>

        <h3>¿CUENTA USTED CON ALGUNA PRUEBA? ¿CUÁLES? Relaciónelas y adjúntelas</h3>
        <table class="table_container_beta">
            <tr class="table_row_delta">
                <td class="table_cell_epsilon" colspan="2" style="height: 80px;"></td>
            </tr>
        </table>

        <div style="align-items: center;" class="signature_container_eta">
            <div>
                <label class="signature_label_theta">Firma empleado:</label>
                <div class="signature_box_zeta"></div>
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

   <!--  <div>
        <a href="<?= base_url('/generatePdf_quejaCocolab') ?>" target="_blank">
            <button type="button">PDF</button>
        </a>
    </div> -->

</body>

</html>