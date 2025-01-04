<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>1.2.2 Programa de Inducción y Reinducción</title>
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
        .beta-subtitulo {
            font-size: 1.5em;
            font-weight: bold;
            margin-top: 20px;
        }

        .gamma-parrafo {
            font-size: 1em;
            margin-bottom: 10px;
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


    <div class="beta-subtitulo">1. OBJETIVO</div>
    <p class="gamma-parrafo">
        Facilitar un conocimiento global de <strong><?= $client['nombre_cliente'] ?></strong> y el SGSST al trabajador, mediante información de los objetivos generales, metas actuales, reglamentaciones y procedimientos, valores y características especiales de la misma.
    </p>

    <div class="beta-subtitulo">2. ALCANCE</div>
    <p class="gamma-parrafo">
        Se aplica antes de dar inicio a sus actividades laborales en <strong><?= $client['nombre_cliente'] ?></strong> inmediatamente después de su vinculación legal. Adicionalmente, aplica a todo el personal que realice cambio de cargo, que requiera reinducción o que regrese de una incapacidad por accidente de trabajo.
    </p>

    <div class="beta-subtitulo">3. REQUISITOS GENERALES</div>
    <p class="gamma-parrafo">
        El proceso de inducción en <strong><?= $client['nombre_cliente'] ?></strong> es fundamental para la formación y desarrollo de los empleados. Este proceso de aprendizaje permite incorporar, ubicar y orientar al nuevo trabajador en la empresa y en temas de seguridad y salud tanto dentro como fuera de esta. Es el complemento del proceso de selección y el inicio de la etapa de socialización, crucial para dar paso al entrenamiento directo en el cargo.
    </p>
    <p class="gamma-parrafo">
        Por lo anterior, se establecen claramente los procedimientos a seguir, mediante el desarrollo de diferentes etapas que contienen la información necesaria, el diligenciamiento de la evaluación de la inducción y el formato de control de la misma.
    </p>

    <div class="beta-subtitulo">4. CONTENIDO: ESQUEMA GENERAL DEL PROCESO DE INDUCCIÓN</div>

    <div class="beta-subtitulo">ETAPA 1: INTRODUCCIÓN</div>
    <ul class="delta-lista">
        <li class="gamma-parrafo">Historia</li>
        <li class="gamma-parrafo">Principios y Valores</li>
        <li class="gamma-parrafo">Ubicación de la empresa y objetivos</li>
        <li class="gamma-parrafo">Organigrama</li>
    </ul>

    <div class="beta-subtitulo">ETAPA 2: SALUD OCUPACIONAL Y SEGURIDAD INDUSTRIAL</div>
    <ul class="delta-lista">
        <li class="gamma-parrafo">Aspectos generales y legales en Seguridad y Salud en el Trabajo</li>
        <li class="gamma-parrafo">Política de SST</li>
        <li class="gamma-parrafo">Política de no alcohol ni drogas</li>
        <li class="gamma-parrafo">Reglamento de Higiene y Seguridad en el Trabajo</li>
        <li class="gamma-parrafo">Funcionamiento del comité paritario de seguridad y salud en el trabajo VIGIA</li>
        <li class="gamma-parrafo">Funcionamiento del comité de convivencia laboral</li>
        <li class="gamma-parrafo">Plan de emergencia</li>
        <li class="gamma-parrafo">Peligros y riesgos asociados a la labor a desempeñar y sus controles</li>
        <li class="gamma-parrafo">Procedimientos seguros para el desarrollo de la tarea</li>
        <li class="gamma-parrafo">Responsabilidades generales en SST</li>
        <li class="gamma-parrafo">Derechos y deberes del Sistema de Gestión de Seguridad y Salud en el Trabajo</li>
        <li class="gamma-parrafo">Verificar recomendaciones en exámenes de ingreso</li>
    </ul>

    <div class="beta-subtitulo">ETAPA 3: RELACIONES LABORALES</div>
    <ul class="delta-lista">
        <li class="gamma-parrafo">Reglamento Interno de Trabajo y entrega del Documento (digital)</li>
        <li class="gamma-parrafo">Explicación pago salarial (método, concepto, cuenta, etc.)</li>
        <li class="gamma-parrafo">Horario laboral</li>
        <li class="gamma-parrafo">Prestaciones legales y extralegales</li>
    </ul>

    <div class="beta-subtitulo">ETAPA 4: CONOCIMIENTO Y RECORRIDO INSTALACIONES</div>
    <ul class="delta-lista">
        <li class="gamma-parrafo">Equipo de trabajo</li>
        <li class="gamma-parrafo">Áreas Administrativas</li>
        <li class="gamma-parrafo">Áreas Productivas</li>
    </ul>

    <div class="beta-subtitulo">ETAPA 5: ENTRENAMIENTO AL CARGO</div>
    <ul class="delta-lista">
        <li class="gamma-parrafo">Entrenamiento en el puesto de trabajo y área específica</li>
    </ul>

    <div class="beta-subtitulo">5. ENTREGA DE MEMORIAS</div>
    <p class="gamma-parrafo">
        Al culminar el proceso de inducción descrito anteriormente, cada trabajador vinculado debe recibir la siguiente documentación por correo electrónico:
    </p>
    <ul class="delta-lista">
        <li class="gamma-parrafo">Política de SST</li>
        <li class="gamma-parrafo">Política de no alcohol ni drogas</li>
        <li class="gamma-parrafo">Reglamento de Higiene y Seguridad en el Trabajo</li>
        <li class="gamma-parrafo">Responsabilidades generales en SST</li>
        <li class="gamma-parrafo">Derechos y deberes del Sistema de Gestión de Seguridad y Salud en el Trabajo</li>
        <li class="gamma-parrafo">Reglamento Interno de Trabajo</li>
    </ul>

    <p class="gamma-parrafo">
        En físico, se entregará:
    </p>
    <ul class="delta-lista">
        <li class="gamma-parrafo">Copia del Contrato de vinculación</li>
        <li class="gamma-parrafo">Copia de afiliación a EPS</li>
        <li class="gamma-parrafo">Carné de ARL</li>
    </ul>

    <div class="beta-subtitulo">6. EVALUACIÓN Y CONTROL</div>
    <p class="gamma-parrafo">
        Una vez finalizado el programa de inducción, este debe ser evaluado por el responsable mediante el Formato de Control y Evaluación del Proceso de Inducción. Dicho formato debe ser entregado al área de Seguridad y Salud en el Trabajo para su revisión, y la información será utilizada para el manejo del indicador correspondiente. Además, debe archivarse en cada hoja de vida como evidencia del desarrollo del programa. Así mismo, a cada empleado se le entregará una copia del formato.
    </p>


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
        <a href="<?= base_url('/generatePdf_prgInduccion') ?>" target="_blank">
            <button type="button">PDF</button>
        </a>
    </div> -->

</body>

</html>