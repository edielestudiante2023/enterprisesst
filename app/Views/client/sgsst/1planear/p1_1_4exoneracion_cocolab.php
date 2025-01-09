<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>1.1.4 Carta de Exoneración Comité de Convivencia Laboral</title>
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

        <h3 style="text-align: center;">ACLARACIÓN NO CONFORMACIÓN DE COMITÉ DE CONVIVENCIA LABORAL</h3>

        <br>

        <?php
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'Spanish_Spain'); // Configura el idioma español
?>

<p>FECHA: <strong><?= strftime('%d de %B de %Y', strtotime($client['fecha_ingreso'])); ?></strong></p>


        

        <p>
            <strong><?= $client['nombre_cliente'] ?></strong> con Nit <strong><?= $client['nit_cliente'] ?></strong> certifica que actualmente no cuenta con el número de trabajadores dependientes para la activación de un comité de convivencia. Por lo que no aplica
            conformar este organismo que regula y motiva la convivencia laboral. 

            <br><br>
            <?= $clientPolicy['policy_content'] ?>
        </p>

        <p>
            El artículo 3 de la <strong>Resolución 652 de 2012</strong> modificado por la <strong>Resolución 1356 de 2012</strong>
            establece que el Comité de Convivencia Laboral estará compuesto por un número igual de representantes del
            empleador y de los trabajadores con sus respectivos suplentes.
        </p>

        <p>En concreto, el artículo establece lo siguiente:</p>
        <p><strong>Artículo 3. Conformación</strong>: El Comité de Convivencia Laboral estará compuesto por dos (2) representantes
            del empleador y dos (2) de los trabajadores con sus respectivos suplentes.</p>

        <p>
            El Comité de Convivencia Laboral es un organismo de prevención y resolución de conflictos laborales que tiene
            como objetivo promover, proteger y velar por el ejercicio de los derechos fundamentales de los trabajadores en
            materia de prevención y atención del acoso laboral.
        </p>

        <p>
            Los miembros del Comité de Convivencia Laboral deben ser elegidos por votación secreta y universal por los
            representantes del empleador y de los trabajadores respectivamente. Los suplentes serán elegidos de la misma
            forma que los principales.
        </p>

        <p>
            Los miembros del Comité de Convivencia Laboral deben tener la capacidad de participar de manera constructiva
            en la solución de conflictos, así como de promover la convivencia laboral.
        </p>

        <p>
            El Comité de Convivencia Laboral se reunirá ordinariamente cada tres (3) meses y sesionará con la mitad más uno
            de sus integrantes. Se reunirá extraordinariamente cuando se presenten casos que requieran de su inmediata
            intervención y podrá ser convocado por cualquiera de sus integrantes.
        </p>

        <p>El Comité de Convivencia Laboral tiene las siguientes funciones:</p>
        <ul>
            <li>Recibir y dar trámite a las quejas de acoso laboral presentadas por los trabajadores.</li>
            <li>Realizar evaluaciones periódicas de clima organizacional.</li>
            <li>Difundir la política de prevención y atención del acoso laboral.</li>
            <li>Promover actividades de capacitación y sensibilización sobre el acoso laboral.</li>
            <li>Asesorar a los trabajadores sobre los derechos y deberes en materia de acoso laboral.</li>
        </ul>

        <p>
            El Comité de Convivencia Laboral es un organismo fundamental para la prevención y atención del acoso laboral.
            Su conformación y funcionamiento es una obligación de todas las empresas y entidades públicas con más de 10
            trabajadores.
        </p>

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
        <a href="<?= base_url('/generatePdf_exoneracionCocolab') ?>" target="_blank">
            <button type="button">PDF</button>
        </a>
    </div>
 -->
</body>

</html>