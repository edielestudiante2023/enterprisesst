<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2.11.0 Procedimiento de Entrega y Reposición de EPP y Dotación</title>
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
            max-width: 100px;
            /* Adjust the size of the images as needed */
            height: auto;
        }

        .signature .name {
            font-weight: bold;
        }

        .signature .title {
            font-style: italic;
        }

        /* ***************************************************************************** */
        .alfa-contenedor {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .gamma-titulo {
            font-size: 1.5em;
            font-weight: bold;
            
            margin-bottom: 20px;
        }
        .zeta-tabla {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .zeta-tabla th, .zeta-tabla td {
            border: 1px solid #000;
            padding: 10px;
            text-align: left;
        }
        .zeta-tabla th {
            background-color: #f2f2f2;
        }
        .delta-lista {
            list-style-type: none;
            padding-left: 0;
        }
        .delta-lista li::before {
            content: "• ";
            font-weight: bold;
        }
        .beta-subtitulo {
            font-size: 1.2em;
            font-weight: bold;
            margin-top: 20px;
        }

        p, li {
            text-align: justify;
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

    <div class="alfa-contenedor">
        <h3 class="gamma-titulo">1. OBJETIVO</h3>
        <p>
            Garantizar la protección de los colaboradores asegurando la entrega de los Elementos de Protección Personal (EPP) establecidos de acuerdo con los factores de riesgo identificados y a la Dotación de acuerdo con el desarrollo de sus actividades teniendo en cuenta la normatividad vigente.
        </p>

        <h3 class="gamma-titulo">2. ALCANCE</h3>
        <p>
            Este procedimiento incluye la identificación, selección, entrega, reposición, capacitación y seguimiento del uso adecuado de los Elementos de Protección Personal (EPP) y Dotación que se asignan a los colaboradores de la empresa.
        </p>

        <h3 class="gamma-titulo">3. DEFINICIONES</h3>
        <ul class="delta-lista">
            <li><strong>DOTACIÓN:</strong> Uniformes definidos para el buen desarrollo de las actividades dentro de la empresa.</li>
            <li><strong>ELEMENTO DE PROTECCIÓN PERSONAL (EPP):</strong> Son todos los elementos de protección física adecuados, según la naturaleza del riesgo, que reúnan condiciones de seguridad y eficiencia para el usuario.</li>
            <li><strong>ELEMENTOS DE PROTECCIÓN PARA LOS OJOS Y LA CARA:</strong> Son elementos como gafas de seguridad, monogafas de seguridad o caretas, que los colaboradores usan cuando están expuestos a riesgos tales como la proyección de partículas, químicos ácidos o cáusticos, gases o vapores, así como a rayos luminosos nocivos como los de soldadura y otros.</li>
            <li><strong>ELEMENTOS DE PROTECCIÓN PARA LA CABEZA:</strong> Generalmente se refiere a cascos de seguridad que deben utilizar los colaboradores en áreas en las cuales existe la posibilidad de lesionarse por caída de objetos, impacto o caída de altura. Hay cascos diseñados para reducir la posibilidad de choque eléctrico para aquellas personas que están expuestas a energía eléctrica (cascos dieléctricos).</li>
            <li><strong>ELEMENTOS DE PROTECCIÓN PARA LOS PIES:</strong> Son elementos como botas o zapatos de seguridad con puntera de acero cuando existe el riesgo de caída de objetos pesados, atrapamiento por vehículos o equipos, o ante la presencia de objetos que puedan perforar la suela. Aquellos colaboradores que están expuestos a riesgos eléctricos deben utilizar calzado especial dieléctrico para disminuir la probabilidad de choque, en caso de entrar en contacto con alguna fuente generadora de energía.</li>
            <li><strong>ELEMENTOS DE PROTECCIÓN PARA LAS MANOS:</strong> Se refiere a los guantes que deben utilizar los colaboradores cuando están expuestos a riesgos como productos químicos, cortadas por elementos cortopunzantes, quemaduras, abrasiones o temperaturas extremas. Éstos deben seleccionarse cuidadosamente de acuerdo con la actividad que realizar la persona, de manera que cumplan con el objetivo de protegerla contra el riesgo.</li>
            <li><strong>ELEMENTOS DE PROTECCIÓN RESPIRATORIA:</strong> Son aquellos que protegen de las sustancias químicas presentes en el lugar de trabajo, y que no han podido ser controladas en la fuente de origen. Es muy importante la adecuada selección del tipo de respiradores de acuerdo con las sustancias y determinar si la persona está expuesta a un químico líquido o sólido; también deben reconocerse las propiedades tóxicas del mismo.</li>
        </ul>

        <h3 class="gamma-titulo">4. ELEMENTOS DE PROTECCIÓN PERSONAL Y SEGURIDAD INDUSTRIAL</h3>
        <p>
            Se definen de acuerdo con el trabajo o actividad que se va a realizar.
        </p>

        <h3 class="gamma-titulo">5. RESPONSABLES</h3>
        <p><strong>QUIEN DEBE CONOCERLO:</strong> Personal Operativo.</p>
        <p><strong>QUIEN DEBE EJECUTARLO:</strong> Analista de Gestión Humana, jefe inmediato, COPASST.</p>
        <p><strong>QUIEN DEBE HACERLO CUMPLIR:</strong> Analista de Gestión Humana, jefe inmediato, COPASST.</p>

        <h3 class="gamma-titulo">PROCEDIMIENTO Y RESPONSABLES</h3>
        <table class="zeta-tabla">
            <thead>
                <tr>
                    <th>Procedimiento</th>
                    <th>Responsable</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>La Dotación y los EPP son indispensables para los trabajos y su uso es obligatorio y permanente.</td>
                    <td>Colaborador / Analista de Gestión Humana / Jefe inmediato</td>
                </tr>
                <tr>
                    <td>Es responsabilidad dar el uso correcto a la Dotación y los EPP entregados por la compañía.</td>
                    <td>Colaborador / Analista de Gestión Humana / Jefe inmediato</td>
                </tr>
                <tr>
                    <td>Inspección mensual de los EPP y asegurarse de que estén en condiciones adecuadas de uso.</td>
                    <td>Colaborador / Analista de Gestión Humana / Jefe inmediato</td>
                </tr>
                <tr>
                    <td>Si el EPP no provee un nivel adecuado de protección, debe repararse o sustituirse inmediatamente.</td>
                    <td>Colaborador / Analista de Gestión Humana / Jefe inmediato</td>
                </tr>
                <tr>
                    <td>Capacitación sobre el uso, cuidado y mantenimiento de los EPP.</td>
                    <td>Colaborador / Analista de Gestión Humana / Jefe inmediato</td>
                </tr>
                <tr>
                    <td>La compañía entregará los uniformes de trabajo e implementos necesarios para el buen desarrollo de las labores.</td>
                    <td>Colaborador / Analista de Gestión Humana / Jefe inmediato</td>
                </tr>
                <tr>
                    <td>El trabajador será responsable de sus uniformes y no debe utilizarlos fuera de las áreas de trabajo.</td>
                    <td>Colaborador / Analista de Gestión Humana / Jefe inmediato</td>
                </tr>
                <tr>
                    <td>Llevar el control de inventario de Dotación y EPP en el Formato correspondiente.</td>
                    <td>Analista de Gestión Humana</td>
                </tr>
                <tr>
                    <td>Definir los elementos de protección personal que se deben entregar según el riesgo identificado.</td>
                    <td>Analista de Gestión Humana</td>
                </tr>
                <tr>
                    <td>Solicitar los EPP y Dotación necesarios al Analista de Gestión Humana con la descripción de referencias y cantidades.</td>
                    <td>Analista de Gestión Humana</td>
                </tr>
                <tr>
                    <td>Recibir y verificar los EPP y Dotación solicitados. Devolver los que no cumplan los requisitos.</td>
                    <td>Analista de Gestión Humana</td>
                </tr>
                <tr>
                    <td>Entregar los EPP y Dotación a los colaboradores utilizando el Formato correspondiente.</td>
                    <td>Analista de Gestión Humana</td>
                </tr>
                <tr>
                    <td>Realizar 6 inspecciones al año del uso adecuado de los EPP y Dotación.</td>
                    <td>Analista de Gestión Humana / Jefe de Área / COPASST</td>
                </tr>
                <tr>
                    <td>Solicitar un EPP o Dotación por reposición y registrar la entrega en la carpeta laboral de cada empleado.</td>
                    <td>Colaborador / Analista de Gestión Humana</td>
                </tr>
            </tbody>
        </table>
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
        <a href="<?= base_url('/generatePdf_entregaDotacion') ?>" target="_blank">
            <button type="button">PDF</button>
        </a>
    </div> -->

</body>

</html>