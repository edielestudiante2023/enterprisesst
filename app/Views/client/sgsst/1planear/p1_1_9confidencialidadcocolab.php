<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>1.1.9 Formato Acuerdo Confidencialidad COCOLAB</title>
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

        h1_alfa {
            text-align: center;
            color: #2c3e50;
        }

        p_alfa, p,  li {
            text-align: justify;
            margin: 15px 0;
        }

        .signature-section_beta {
            margin-top: 40px;
        }

        .signature-line_gamma {
            display: inline-flex;
            width: 400px;
            border-bottom: 1px solid black;
            margin: 10px 0;
        }

        ol_delta {
            margin: 20px;
            padding-left: 20px;
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

    <h1 style="text-align: center;">Comité de Convivencia Laboral - Acuerdo de Confidencialidad</h1>

    <p class="p_alfa">
        Los miembros del Comité de Convivencia Laboral de <strong><?= $client['nombre_cliente'] ?></strong>, elegidos por los trabajadores y los directivos de la entidad, en atención a las obligaciones que deben cumplir y todas las personas que participen en las reuniones, en forma libre y voluntaria convienen suscribir el presente Acuerdo de Confidencialidad en los siguientes términos:
    </p>

    <p class="p_alfa">
        Yo ______________________________________________  identificado(a) con documento de identidad número ____________________ expedida en _______________________, en mi calidad de miembro del Comité de Convivencia Laboral, comprendo y tengo claro que, para efectos de este acuerdo, la <strong>CONFIDENCIALIDAD</strong> es entendida por toda aquella información propia de cada uno de los casos, bien sea de carácter técnico, administrativo, laboral o legal, a la que normalmente no tengo acceso y, por tanto, debe permanecer en reserva.
    </p>

    <p class="p_alfa">
        La información dejará de ser confidencial cuando sea de dominio público por haber sido publicada por quien sea el titular o el dueño de la información, o cuando deba ser divulgada por disposición legal o por orden judicial.
    </p>

    <p class="p_alfa">
        En virtud de lo antes señalado, el(la) suscrito(a) se compromete a:
    </p>

    <ol class="ol_delta">
        <li>Manejar de manera confidencial la información que como tal sea conocida, prestada, entregada y toda aquella que se genere en torno a ella como resultado del funcionamiento del Comité de Convivencia Laboral.</li>
        <li>Guardar confidencialidad sobre esa información y no emplearla en beneficio propio o de terceros mientras conserve sus características de confidencialidad o mientras sea manejada como un caso de los que conoce el Comité de Convivencia Laboral.</li>
        <li>Mantener la reserva de la información de todos y cada uno de los documentos que le son entregados, o de aquellos que son socializados en el seno del Comité de Convivencia Laboral, así como mantener la reserva de todas las conversaciones que se susciten con los funcionarios de <strong><?= $client['nombre_cliente'] ?></strong> que son atendidos por el Comité en cualquiera de las diligencias que deban ser adelantadas.</li>
        <li>Guardar con recelo toda la información con la que cuente y tenga acceso a través de su participación dentro del Comité de Convivencia Laboral.</li>
    </ol>

    <p class="p_alfa">
        En ese orden, es claro que el desconocimiento de los compromisos descritos puede conllevar a inhabilitar o retirar al miembro de la toma de decisiones dentro del Comité de Convivencia Laboral.
    </p>

    <p class="p_alfa">
        Acepto voluntariamente, a los ____ días del mes de __________ del año __________.
    </p>

    <div class="signature-section_beta">
        <p><strong>FIRMA:</strong> <span class="signature-line_gamma"></span></p>
        <p><strong>C.C.:</strong> <span class="signature-line_gamma"></span></p>
    </div>

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

    <!-- <div>
        <a href="<?= base_url('/generatePdf_confidencialidadCocolab') ?>" target="_blank">
            <button type="button">PDF</button>
        </a>
    </div> -->

</body>

</html>