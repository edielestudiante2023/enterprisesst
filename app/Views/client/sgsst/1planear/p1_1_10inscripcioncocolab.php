<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>1.1.10 Formato de Inscripción al COCOLAB</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }

        .container1 {
            width: 95%;
            margin: 0 auto;
            
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

        .info-table td {
            padding: 5px;
            text-align: left;
        }

        .signature {
            margin-top: 20px;
            text-align: right;
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
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid black;
            padding: 10px;
            text-align: left;
            height: 30px;
        }

        th {
            background-color: #f2f2f2;
        }

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
            text-align: center;
            /* Asegura que la tabla ocupe el 100% del div */
        }

        .no-border {
            border: none;
        }

        .center {
            text-align: center;
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

    <div class="container1">
        
        <h2>Hoja de Inscripción al Comité de Convivencia Laboral</h2>

        <table class="info-table">
            <tr>
                <td><strong>Responsable de Gestionar la Inscripción:</strong> </td>
                <td><strong>Fecha:</strong> </td>
            </tr>
            <tr>
                <td><strong>Empresa:</strong> <?= $client['nombre_cliente'] ?></p></td>
                <td><strong>Período:</strong> </td>
            </tr>
        </table>

        <table>
            <thead>
                <tr>
                    <th>NOMBRE</th>
                    <th>CARGO</th>
                    <th>ÁREA</th>
                    <th>FECHA</th>
                </tr>
            </thead>
            <tbody>
                <!-- Filas para los registros de inscripciones, deja estas vacías para que se llenen -->
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <p><strong>Protección de Datos Personales:</strong> En cumplimiento de la Ley 1581 de 2012, el registro de sus datos personales en el presente documento se realiza con el objetivo de autorizar a <strong><?= $client['nombre_cliente'] ?></strong> en la recolección, almacenamiento y uso de la información que se registra en este documento.</p>

        <p>Como titular de la información, tiene derecho a conocer, actualizar y rectificar sus datos personales, solicitar prueba de la autorización otorgada para su tratamiento, ser informado sobre el uso que se le ha dado a estos, presentar quejas ante entidades regulatorias locales por infracción al Régimen de Habeas Data, revocar la autorización y/o solicitar la supresión de sus datos en los casos en que sea procedente, así como acceder a ellos en forma gratuita.</p>

        

        <div class="signature">
            <p><strong>Responsable:</strong> ______________________</p>
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

  <!--   <div>
        <a href="<?= base_url('/generatePdf_inscripcionCocolab') ?>" target="_blank">
            <button type="button">PDF</button>
        </a>
    </div> -->

</body>

</html>