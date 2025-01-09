<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>1.1.5 Registro de Asistencia</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            width: 100%;
            border: 1px solid black;
            text-align: center;
            margin-bottom: 10px;
        }

        .header td {
            padding: 10px;
        }

        h2 {
            text-align: center;
            margin: 10px 0;
        }

        .info-table,
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .info-table td,
        .attendance-table th,
        .attendance-table td {
            border: 1px solid black;
            padding: 5px;
        }

        .attendance-table th,
        .attendance-table td {
            text-align: center;
        }

        .checkboxes {
            display: flex;
            justify-content: space-around;
            margin: 10px 0;
        }

        .signature-section {
            margin-top: 40px;
        }

        footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
        }

        footer table {
            width: 100%;
            border-collapse: collapse;
        }

        footer th,
        footer td {
            border: 1px solid black;
            padding: 8px;
        }

        .no-print {
            display: none;
        }

        @media print {
            .no-print {
                display: none;
            }
        }

        /* ********************************************** */

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
            height: 40px;
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

        .centered-content {
            width: 100%;
            /* Ajusta el ancho según tus necesidades */
            margin: 0 auto;
            /* Centra horizontalmente */
            padding: 20px;
            /* Opcional: añade espacio alrededor */
            background-color: #fff;
            /* Opcional: define un color de fondo */
            border-radius: 8px;
            /* Opcional: bordes redondeados */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            /* Opcional: sombra */
        }

        .centered-content table {
            width: 100%;
            /* Asegura que la tabla ocupe el 100% del div */
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


    <div class="container">
        <h3 style="text-align: center;">REGISTRO DE ASISTENCIA</h3>

        <table class="info-table">
            <tr>
                <td><strong>Tema:</strong> _____________________________</td>
                <td><strong>Lugar:</strong> _____________________________</td>
            </tr>
            <tr>
                <td><strong>Fecha:</strong> _____________________________</td>
                <td><strong></strong></td> <!-- Se añade una columna vacía para mantener la estructura -->
            </tr>
            <tr>
                <td colspan="2"><strong>Objetivo:</strong> _________________________________________________________</td>
            </tr>
            <tr>
                <td colspan="2"><strong>Capacitador:</strong> ______________________________________________________</td>
            </tr>
        </table>

        <div class="checkboxes">
            <label><input type="checkbox"> Reunión</label>
            <label><input type="checkbox"> Charla</label>
            <label><input type="checkbox"> Inducción</label>
            <label><input type="checkbox"> Reinducción</label>
            <label><input type="checkbox"> Capacitación</label>
            <label><input type="checkbox"> Otro: ________________</label>
        </div>

        <table class="attendance-table">
            <thead>
                <tr>
                    <th>ITEM</th>
                    <th>NOMBRE</th>
                    <th>CEDULA</th>
                    <th>CARGO</th>
                    <th>FIRMA</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>2</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>3</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>4</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>5</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>6</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>7</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>8</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>9</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>10</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>11</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>12</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <div class="signature-section">
            <strong>Observaciones:</strong> _________________________________________________ <br><br><br><br>
            _______________________________________<br>
            <strong>Firma del Capacitador</strong>
        </div>

        <footer>
            <h2>Historial de Versiones</h2>
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
                        <?php setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'Spanish_Spain'); ?>

<td><?= strftime('%d de %B de %Y', strtotime($version['created_at'])); ?></td>

                        <td><?= $version['change_control'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </footer>
        <br>
        <!-- <div >
            <a href="<?= base_url('/generatePdf_registroAsistencia') ?>" target="_blank">
                <button type="button">PDF</button>
            </a>
        </div> -->
    </div>
</body>

</html>