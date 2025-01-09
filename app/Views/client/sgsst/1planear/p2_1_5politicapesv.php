<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2.1.5 Política de Seguridad Vial</title>
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





        .alpha-title {
            font-size: 1.5em;
            font-weight: bold;
            text-align: center;
            margin-top: 20px;
        }

        .beta-parrafo {
            margin-bottom: 10px;
            font-size: 1.1em;
            text-align: justify;
        }

        .delta-lista {
            margin-left: 20px;
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

    <div class="alpha-title">Política de Seguridad Vial</div>

    <p class="beta-parrafo">
        <strong><?= $client['nombre_cliente'] ?></strong> se compromete a planificar e implementar estrategias y acciones de educación, promoción y prevención encaminadas a la reducción y eliminación de accidentes viales, fomentando el comportamiento seguro de los diferentes actores viales. En consecuencia, todas las personas que laboran en la empresa son responsables de participar activamente en las actividades que programe y desarrolle el líder de Seguridad Vial, tendientes a disminuir la probabilidad de ocurrencia de accidentes que puedan afectar la integridad física, mental y social de los funcionarios, contratistas, de la comunidad en general y el medio ambiente.
    </p>

    <p class="beta-parrafo">
        Para cumplir esta política, <strong><?= $client['nombre_cliente'] ?></strong> adopta los requisitos del Plan Nacional de Seguridad Vial Colombia (Ley 1503 de 2011-2021) y la Resolución 40595 del 12 de julio de 2022, comprometiéndose a las siguientes medidas:
    </p>

    <ul class="delta-lista">
        <li class="beta-parrafo">Cumplir los requisitos legales aplicables en materia de seguridad vial, con un enfoque en la mejora continua de su plan estratégico de seguridad vial.</li>
        <li class="beta-parrafo">Cumplir con la reglamentación vigente establecida en Colombia de Tránsito Terrestre y demás normas relacionadas que las adicionen, complementen o modifiquen en seguridad vial y medio ambiente.</li>
        <li class="beta-parrafo">Vigilar y respetar los turnos de conducción y descanso establecidos en <strong><?= $client['nombre_cliente'] ?></strong>, sin permitir exceder las horas previstas.</li>
        <li class="beta-parrafo">Fomentar que nuestros conductores respeten los límites de velocidad establecidos en la ley: 80 km/h en carretera, 60 km/h en zonas perimetrales y 30 km/h en zonas escolares, residenciales y hospitalarias. Estos límites solo podrán excederse de acuerdo con el artículo 64 de la ley 769 de 2002 (CNT), en los casos debidamente autorizados.</li>
        <li class="beta-parrafo">Promover el uso obligatorio del cinturón de seguridad entre los colaboradores que, en el ejercicio de su actividad misional, conducen con la finalidad de prevenir los riesgos asociados con la seguridad vial.</li>
        <li class="beta-parrafo">Prohibir el uso de distractores durante la conducción, tales como teléfonos móviles, asistentes digitales personales, computadores portátiles y demás equipos electrónicos, aunque se cuente con manos libres. En caso de ser necesario realizar o contestar una llamada, el conductor deberá detener el vehículo en un lugar seguro.</li>
        <li class="beta-parrafo">Establecer estrategias de concientización y educación para todos sus colaboradores mediante acciones formativas orientadas a la prevención de accidentes de tránsito y al respeto por las normas y señales de tránsito vehicular. Estas acciones permitirán la adopción de conductas proactivas frente al manejo defensivo, la creación de un plan de contingencia y la divulgación del Plan Estratégico de Seguridad Vial a las partes interesadas.</li>
        <li class="beta-parrafo">Diseñar e implementar actividades de promoción del manejo responsable y adecuado de vehículos, enfatizando en la seguridad de todos los actores viales (peatones, conductores, pasajeros, ciclistas y motociclistas).</li>
        <li class="beta-parrafo">Prohibir expresamente operar un vehículo bajo los efectos del alcohol, drogas y/o sustancias psicotrópicas o psicoactivas que afecten la capacidad cognitiva y física necesarias para la operación segura de cualquier tipo de vehículo.</li>
    </ul>

    <p class="beta-parrafo">
        Los anteriores lineamientos se implementarán bajo el enfoque de Mejora Continua, y el incumplimiento de esta política puede resultar en una acción disciplinaria.
    </p>


    <div class="signature-container">
        <div class="signature">
            <img src="<?= base_url('uploads/' . $client['firma_representante_legal']) ?>" alt="Firma rep. legal">
            <div class="name"><b><?= $client['nombre_rep_legal'] ?></b></div>
            <div class="title">Representante Legal</div>
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
        <a href="<?= base_url('/generatePdf_politicaPesv') ?>" target="_blank">
            <button type="button">PDF</button>
        </a>
    </div> -->

</body>

</html>