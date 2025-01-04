<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>1.2.3 Evaluación de la Inducción y/o Reinducción</title>
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

        .alpha-title {
            font-size: 1.5em;
            font-weight: bold;
            text-align: center;
            margin-top: 20px;
        }

        .beta-parrafo {
            margin-bottom: 10px;
            font-size: 1em;
        }

        .gamma-section {
            margin: 20px 0;
        }

        .delta-label {
            font-weight: bold;
        }

        .epsilon-question {
            margin-top: 20px;
        }

        .epsilon-question li {
            margin-top: 5px;
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

    <div class="alpha-title">Evaluación de Seguridad y Salud en el Trabajo</div>

    <div class="gamma-section">
        <p class="delta-label">Nombre: ______________________________________________</p>
        <p class="delta-label">Área: ____________________________________________________</p>
        <p class="delta-label">Cargo: ________________________ Fecha: __________________</p>
    </div>

    <div class="epsilon-question">
        <p><strong>1. ¿Cuál de los siguientes objetivos se debe cumplir en la Política de Seguridad y Salud en trabajo?</strong></p>
        <ul>
            <li>a) Identificar los peligros, evaluar y valorar los riesgos y establecer los respectivos.</li>
            <li>b) Proteger la seguridad y salud de todos los trabajadores, mediante la mejora continua del SG-SST.</li>
            <li>c) Cumplir con la normatividad vigente que regula la Seguridad y Salud en el Trabajo.</li>
            <li>d) Todas las anteriores</li>
        </ul>
    </div>

    <div class="epsilon-question">
        <p><strong>2. ¿Algunos de los riesgos a los que estamos expuestos son?</strong></p>
        <ul>
            <li>a) Biomecánicos, Psicosocial, Físico, Locativo.</li>
            <li>b) Químico, eléctrico, espacios confinados.</li>
            <li>c) Trabajos en alturas, trabajos en caliente.</li>
            <li>d) Todas las anteriores.</li>
        </ul>
    </div>

    <div class="epsilon-question">
        <p><strong>3. ¿En caso de sufrir un accidente laboral debo reportarlo?</strong></p>
        <ul>
            <li>a) Inmediatamente.</li>
            <li>b) En el transcurso de la semana siguiente.</li>
            <li>c) A los quince días.</li>
        </ul>
    </div>

    <div class="epsilon-question">
        <p><strong>4. ¿Qué responsabilidades tengo yo al frente en Seguridad y Salud en el Trabajo?</strong></p>
        <ul>
            <li>a) Suministrar información personal y asistir a los eventos de la compañía.</li>
            <li>b) Suministrar información clara, veraz y completa sobre su estado de salud y participar en las actividades de capacitación en SST.</li>
            <li>c) Cumplir con horarios laborales y realizar mis funciones del cargo.</li>
        </ul>
    </div>

    <div class="epsilon-question">
        <p><strong>5. ¿Qué es COPASST y COCOLAB?</strong></p>
        <ul>
            <li>a) Comité para la Seguridad y Salud Ocupacional y Comité de control Laboral.</li>
            <li>b) Comités para la vigilancia del COVID-19.</li>
            <li>c) Comité Paritario de Seguridad y Salud en el Trabajo y Comité de Convivencia Laboral.</li>
            <li>d) Ninguna de las anteriores.</li>
        </ul>
    </div>

    <div class="epsilon-question">
        <p><strong>6. ¿Qué debo hacer en caso de emergencia?</strong></p>
        <ul>
            <li>a) Correr hacia el punto de encuentro, seguir ruta de evacuación y seguir instrucciones del brigadista.</li>
            <li>b) Mantener la calma, seguir la ruta de evacuación, seguir instrucciones del brigadista, dirigirse al punto de encuentro.</li>
            <li>c) Mantener la calma, seguir la ruta de evacuación, seguir instrucciones del brigadista, y devolverse.</li>
        </ul>
    </div>

    <div class="gamma-section">
        <p class="delta-label">Resultado de la Evaluación</p>
        <p class="delta-label">Puntaje: __________________________</p>
        <p class="delta-label">Firma evaluador(a): ________________________</p>
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
<!-- 
    <div>
        <a href="<?= base_url('/generatePdf_ftevaluacionInduccion') ?>" target="_blank">
            <button type="button">PDF</button>
        </a>
    </div> -->

</body>

</html>