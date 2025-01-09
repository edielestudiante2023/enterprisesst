<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2.1.6 Reglamento de Higiene y Seguridad Industrial</title>
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

        .gamma-section {
            margin-bottom: 20px;
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

    <div class="alpha-title">Reglamento de Higiene y Seguridad Industrial</div> <br>

    <div class="gamma-section">
        <table width=100% border="1" cellpadding="10" cellspacing="0">
            <tr>
                <td><strong>EMPRESA:</strong></td>
                <td><strong><?= $client['nombre_cliente'] ?></strong></td>
            </tr>
            <tr>
                <td><strong>IDENTIFICACION:</strong></td>
                <td><?= $client['nit_cliente'] ?></td>
            </tr>
            <tr>
                <td><strong>DIRECCION:</strong></td>
                <td><?= $client['direccion_cliente'] ?></td>
            </tr>
            <tr>
                <td><strong>TELEFONO:</strong></td>
                <td><?= $client['telefono_1_cliente'] ?></td>
            </tr>
            <tr>
                <td><strong>CIUDAD:</strong></td>
                <td><?= $client['ciudad_cliente'] ?></td>
            </tr>
            <tr>
                <td><strong>SUCURSALES:</strong></td>
                <td><?= $clientPolicy['policy_content'] ?></td>
            </tr>
            <tr>
                <td><strong>CODIGO DE LA ACTIVIDAD ECONOMICA:</strong></td>
                <td><?= $client['codigo_actividad_economica'] ?></td>
            </tr>
        </table>

    </div>

    <div class="gamma-section">
        <p class="beta-parrafo"><strong>ARTÍCULO PRIMERO:</strong> <strong><?= $client['nombre_cliente'] ?></strong> se compromete a dar cumplimiento a las disposiciones legales vigentes, tendientes a garantizar los mecanismos que aseguren una oportuna y adecuada prevención de los accidentes de trabajo y enfermedades profesionales, de conformidad con el Código Sustantivo del Trabajo y demás normas relacionadas.</p>
    </div>

    <div class="gamma-section">
        <p class="beta-parrafo"><strong>ARTÍCULO SEGUNDO:</strong> <strong><?= $client['nombre_cliente'] ?></strong> se obliga a promover y garantizar la constitución y funcionamiento del Comité Paritario de Salud Ocupacional, de acuerdo con las disposiciones legales.</p>
    </div>

    <div class="gamma-section">
        <p class="beta-parrafo"><strong>ARTÍCULO TERCERO:</strong> <strong><?= $client['nombre_cliente'] ?></strong> se compromete a destinar los recursos necesarios para desarrollar actividades permanentes en conformidad con el Sistema de Gestión en seguridad y salud en el trabajo, que contempla, entre otros:</p>
        <ul class="delta-lista">
            <li class="beta-parrafo">Subprograma de Medicina Preventiva y del Trabajo, orientado a promover y mantener el más alto grado de bienestar físico, mental y social de los trabajadores.</li>
            <li class="beta-parrafo">Subprograma de Higiene y Seguridad Industrial, enfocado en establecer las mejores condiciones de saneamiento básico y eliminar o controlar los factores de riesgo en los lugares de trabajo.</li>
        </ul>
    </div>

    <div class="gamma-section">
        <p class="beta-parrafo"><strong>ARTÍCULO CUARTO:</strong> Los riesgos existentes en <strong><?= $client['nombre_cliente'] ?></strong> están constituidos principalmente por:</p>
        <ul class="delta-lista">
            <li class="beta-parrafo"><strong>Riesgo Biomecánico:</strong> Posturas prolongadas, cargas físicas, esfuerzo repetitivo, entre otros.</li>
            <li class="beta-parrafo"><strong>Riesgo Biológico:</strong> Parásitos, hongos, bacterias, virus.</li>
            <li class="beta-parrafo"><strong>Riesgo Público:</strong> Asaltos, desórdenes públicos, violencia.</li>
            <li class="beta-parrafo"><strong>Riesgo Mecánico:</strong> Manipulación de máquinas y herramientas.</li>
            <li class="beta-parrafo"><strong>Riesgo Locativo:</strong> Almacenamiento, superficies de trabajo, condiciones de orden y aseo.</li>
            <li class="beta-parrafo"><strong>Riesgo Eléctrico:</strong> Contacto directo e indirecto con cableado de media y baja tensión.</li>
            <li class="beta-parrafo"><strong>Riesgos Naturales:</strong> Precipitaciones, inundaciones, terremotos.</li>
            <li class="beta-parrafo"><strong>Riesgo Físico:</strong> Ruido, iluminación, radiaciones no ionizantes.</li>
            <li class="beta-parrafo"><strong>Riesgo Psicosocial:</strong> Exceso de responsabilidades, trabajo bajo presión, problemas laborales.</li>
            <li class="beta-parrafo"><strong>Riesgo Químico:</strong> Líquidos, fibras, material particulado.</li>
            <li class="beta-parrafo"><strong>Riesgo de Tránsito:</strong> Colisiones, volcamientos, atropellamientos.</li>
            <li class="beta-parrafo"><strong>Tareas de Alto Riesgo:</strong> Trabajo en alturas, trabajos en caliente, incendios, explosiones.</li>
        </ul>
        <p class="beta-parrafo">PARÁGRAFO: Para evitar accidentes de trabajo o enfermedades laborales, <strong><?= $client['nombre_cliente'] ?></strong> ejerce control en la fuente, el medio transmisor o el trabajador, conforme a lo estipulado en su Programa de Salud Ocupacional.</p>
    </div>

    <div class="gamma-section">
        <p class="beta-parrafo"><strong>ARTÍCULO QUINTO:</strong> <strong><?= $client['nombre_cliente'] ?></strong> y sus trabajadores darán estricto cumplimiento a las disposiciones legales, normas técnicas e internas relacionadas con la Seguridad Industrial y Salud Ocupacional.</p>
    </div>

    <div class="gamma-section">
        <p class="beta-parrafo"><strong>ARTÍCULO SEXTO:</strong> <strong><?= $client['nombre_cliente'] ?></strong> ha implantado un proceso de inducción para capacitar a los trabajadores en las medidas de prevención y seguridad exigidas en su ambiente laboral.</p>
    </div>

    <div class="gamma-section">
        <p class="beta-parrafo"><strong>ARTÍCULO SÉPTIMO:</strong> Este Reglamento permanecerá exhibido en al menos dos lugares visibles en los locales de trabajo, junto con la Resolución Aprobatoria, y su contenido se dará a conocer a todos los trabajadores al momento de su ingreso.</p>
    </div>

    <div class="gamma-section">
        <p class="beta-parrafo"><strong>ARTÍCULO OCTAVO:</strong> El presente Reglamento entra en vigor a partir de la aprobación del Ministerio de la Protección Social y se revisará anualmente.</p>
        <p class="beta-parrafo">Fecha de publicación: <p>FECHA: <strong><?= strftime('%d de %B de %Y', strtotime($client['fecha_ingreso'])); ?></strong></p>
        <p class="beta-parrafo">Publíquese, comuníquese y cúmplase.</p>
    </div>

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

    <!-- <div>
        <a href="<?= base_url('/generatePdf_regHigsegind') ?>" target="_blank">
            <button type="button">PDF</button>
        </a>
    </div>
 -->
</body>

</html>