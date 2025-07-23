<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2.1.5 Política de Prevención del Acoso Sexual Laboral</title>
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
            table-layout: fixed;
        }

        footer th,
        footer td {
            border: 1px solid #ddd;
            text-align: center;
            vertical-align: middle;
            padding: 8px;
            word-wrap: break-word;
        }

        footer th {
            background-color: #f4f4f4;
            font-weight: bold;
        }

        footer tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        footer tr:hover {
            background-color: #f1f1f1;
        }

        /* Ajuste de anchos en el historial */
        footer th:nth-child(1),
        footer td:nth-child(1) { width: 10%; }
        footer th:nth-child(2),
        footer td:nth-child(2),
        footer th:nth-child(3),
        footer td:nth-child(3),
        footer th:nth-child(4),
        footer td:nth-child(4) { width: 15%; }
        footer th:nth-child(5),
        footer td:nth-child(5) { width: 35%; }

        /* Estilos del contenido central */
        .centered-content {
            width: 100%;
            margin: 0 auto 20px;
            padding: 0 0 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .centered-content table {
            width: 100%;
            text-align: center;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid black;
            padding: 10px;
            text-align: left;
            height: 30px;
        }

        .logo { width: 20%; text-align: center; }
        .main-title { width: 50%; font-weight: bold; font-size: 14px; text-align: center; }
        .code { width: 30%; font-weight: bold; font-size: 14px; }
        .subtitle { font-weight: bold; font-size: 16px; text-align: center; }
        .right { text-align: left; padding-left: 10px; }

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
            margin-top: 10px;
        }

        .signature .title {
            font-style: italic;
        }
    </style>
</head>

<body>
    <div class="centered-content">
        <table>
            <tr>
                <td rowspan="2" class="logo">
                    <img src="<?= base_url('uploads/' . $client['logo']) ?>"
                         alt="Logo de <?= $client['nombre_cliente'] ?>" width="100%">
                </td>
                <td class="main-title">
                    SISTEMA DE GESTIÓN EN SEGURIDAD Y SALUD EN EL TRABAJO
                </td>
                <td class="code">
                    <?= $latestVersion['document_type'] ?> - <?= $latestVersion['acronym'] ?>
                </td>
            </tr>
            <tr>
                <td class="subtitle">
                    POLÍTICA DE PREVENCIÓN DEL ACOSO SEXUAL LABORAL
                </td>
                <td class="code right">
                    Versión: <?= $latestVersion['version_number'] ?><br>
                    <?php
                        setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'Spanish_Spain');
                    ?>
                    Fecha: <?= strftime('%d de %B de %Y', strtotime($latestVersion['created_at'])); ?>
                </td>
            </tr>
        </table>
    </div>

    <div class="alpha-title">POLÍTICA DE PREVENCIÓN DEL ACOSO SEXUAL LABORAL</div>

    <p class="beta-parrafo">
        <strong><?= strtoupper($client['nombre_cliente']) ?></strong> ha establecido una política clara de prevención del acoso sexual laboral, con el fin de garantizar un ambiente de trabajo seguro, respetuoso y libre de cualquier manifestación de violencia, discriminación o conductas que atenten contra la dignidad de las personas. Esta política busca preservar la integridad física, psicológica y emocional de los trabajadores, promoviendo relaciones laborales sanas y basadas en el respeto mutuo.
    </p>

    <p class="beta-parrafo">
        Es política de <strong><?= strtoupper($client['nombre_cliente']) ?></strong> prevenir, atender, sancionar y erradicar cualquier tipo de conducta de acoso sexual laboral que pueda generarse dentro del entorno de trabajo o en ocasión del mismo, en cumplimiento de lo establecido en la Ley 1010 de 2006, la Ley 1257 de 2008, la Ley 2365 de 2024 y demás normativas vigentes relacionadas con la convivencia laboral y la protección de los derechos fundamentales.
    </p>

    <p class="beta-parrafo">
        Se considera acoso sexual laboral toda conducta persistente y ofensiva de connotación sexual que sea no deseada por la persona que la recibe, y que tenga como propósito o efecto interferir en su desempeño laboral o crear un ambiente intimidante, hostil o humillante. Esto incluye comentarios, insinuaciones, tocamientos, miradas lascivas, solicitudes de favores sexuales, entre otras manifestaciones, sin importar el nivel jerárquico del agresor o la víctima.
    </p>

    <p class="beta-parrafo">
        La falta a esta política se considera una falta grave. En consecuencia, la empresa podrá adoptar las medidas disciplinarias correspondientes, incluyendo la terminación del contrato de trabajo por justa causa, según lo estipulado en el Código Sustantivo del Trabajo y lo establecido en el Reglamento Interno de Trabajo.
    </p>

    <p class="beta-parrafo">
        <strong><?= strtoupper($client['nombre_cliente']) ?></strong> designa al Comité de Convivencia Laboral como el órgano responsable de recibir, tramitar y hacer seguimiento a las quejas y situaciones relacionadas con presunto acoso sexual laboral. Así mismo, promoverá permanentemente espacios de sensibilización, capacitación y comunicación, con el fin de fortalecer una cultura organizacional basada en el respeto, la equidad y la sana convivencia.
    </p>

    <p class="beta-parrafo">
        Esta política aplica a todos los trabajadores, contratistas, visitantes y demás personas que tengan relación directa o indirecta con la empresa, por lo tanto, no se permitirá ningún comportamiento que vulnere esta directriz dentro o fuera de las instalaciones, cuando medien vínculos laborales o contractuales con <strong><?= strtoupper($client['nombre_cliente']) ?></strong>.
    </p>

    <div class="signature-container">
        <div class="signature">
            <img src="<?= base_url('uploads/' . $client['firma_representante_legal']) ?>"
                 alt="Firma Representante Legal">
            <div class="name"><?= $client['nombre_rep_legal'] ?></div>
            <div class="title">Representante Legal</div>
        </div>
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
                    <td><?= strftime('%d de %B de %Y', strtotime($version['created_at'])); ?></td>
                    <td><?= $version['change_control'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </footer>

</body>

</html>
