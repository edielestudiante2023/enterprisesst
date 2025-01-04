<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>4.1.4 Procedimiento de Identificación, Evaluación y Actualización de Requisitos Legales</title>
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
        .gamma-title {
            font-size: 1.5em;
            font-weight: bold;
            /* text-align: center; */
            margin-bottom: 10px;
        }

        .epsilon-paragraph {
            text-align: justify;
            margin-bottom: 10px;
        }

        .zeta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .zeta-table td,
        .zeta-table th {
            border: 1px solid black;
            padding: 8px;
        }

        .zeta-table th {
            background-color: #f2f2f2;
            text-align: left;
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
    <div>
        <h3 class="gamma-title">1. OBJETIVO</h3>
        <p class="epsilon-paragraph">
            Establecer el procedimiento para identificar, registrar, acceder, actualizar, evaluar y comunicar a los colaboradores y demás partes interesadas, los requisitos legales que en materia de Seguridad y Salud en el trabajo le aplican a la compañía.
        </p>

        <h3 class="gamma-title">2. ALCANCE</h3>
        <p class="epsilon-paragraph">
            Este procedimiento aplica a todas las áreas y procesos de la empresa y contratistas que estén sujetos al cumplimiento de los requisitos legales en seguridad y salud en el trabajo y otros que la empresa suscriba.
        </p>

        <div>
            <h3 class="gamma-title">3. DEFINICIONES</h3>
            <p class="epsilon-paragraph">
                <strong>Acto administrativo:</strong> es el medio a través del cual la Administración Pública cumple su objetivo de satisfacer los intereses colectivos o interés público. Es la formalización de la voluntad administrativa, y debe ser dictado de conformidad con el principio de legalidad.
            </p>
            <p class="epsilon-paragraph">
                <strong>Ley:</strong> La ley (del latín lex, legis) es una norma jurídica dictada por el legislador. Es decir, un precepto establecido por la autoridad competente, en que se manda o prohíbe algo en consonancia con la justicia. Su incumplimiento trae aparejada una sanción.
            </p>
            <p class="epsilon-paragraph">
                <strong>Decreto ley:</strong> Acto administrativo expedido por el presidente de la república que tiene la misma fuerza de una ley, pero que, por mandato de la constitución en algunos casos particulares, se asimilan a leyes expedidas por el congreso.
            </p>
            <p class="epsilon-paragraph">
                <strong>Decreto:</strong> Un decreto es un tipo de acto administrativo emanado habitualmente del poder ejecutivo y que, generalmente, posee un contenido normativo reglamentario, por lo que su rango es jerárquicamente inferior a las leyes.
            </p>
            <p class="epsilon-paragraph">
                <strong>Resolución:</strong> Acto administrativo por el cual las diferentes entidades de la administración pública adoptan decisiones en el ejercicio de sus funciones.
            </p>
            <p class="epsilon-paragraph">
                Las resoluciones se dictan para cumplir las funciones que la ley encomienda a cada servicio público.
            </p>
            <p class="epsilon-paragraph">
                En cuanto a su ámbito material, la resolución alcanza a todo aquello que complemente, desarrolle o detalle a la ley en la esfera de competencia del servicio público.
            </p>
            <p class="epsilon-paragraph">
                En cuanto al territorio, las resoluciones pueden tener alcance nacional o local, tratándose de servicios descentralizados.
            </p>
            <p class="epsilon-paragraph">
                <strong>Circular:</strong> Para los efectos de este procedimiento, se entiende por tal, la comunicación emanada de una autoridad administrativa o de las autoridades internas de la organización, mediante la cual se establece una obligación, se modifica otra y/o se aclara el contenido de una orden.
            </p>
            <p class="epsilon-paragraph">
                <strong>Requisito Legal:</strong> Disposición de carácter obligatorio por tener relación directa con la actividad que se desarrolla cada empresa cliente, los peligros y evaluación de riesgo identificados y que se hacen obligaciones por estar contenida en una Ley, Decreto, Resolución, etc.
            </p>
            <p class="epsilon-paragraph">
                <strong>Matriz legal:</strong> Es la compilación de los requisitos legales exigibles por parte la empresa acorde con las actividades propias e inherentes de su actividad productiva, los cuales dan los lineamientos normativos y técnicos para desarrollar el Sistema de Gestión en Seguridad y Salud en el Trabajo -SG-SST, el cual deberá actualizarse en la medida que sean emitidas nuevas disposiciones legales aplicables.
            </p>
            <p class="epsilon-paragraph">
                <strong>Cumplimiento:</strong> Condición de aprobación o desaprobación por medio de valoraciones cualitativas y cuantitativas a partir de parámetros establecidos y que son condiciones dadas en un requisito legal.
            </p>
            <p class="epsilon-paragraph">
                <strong>Fuentes de Información:</strong> Lugar de consulta oficial y confiable establecido para determinar los requisitos legales que aplican.
            </p>
            <p class="epsilon-paragraph">
                <strong>No conformidad:</strong> No cumplimiento de un requisito. Puede ser una desviación de estándares, prácticas, procedimientos de trabajo, requisitos normativos aplicables entre otros.
            </p>
            <p class="epsilon-paragraph">
                <strong>Partes interesadas:</strong> Es cualquier organización, grupo o individuo que pueda afectar o ser afectado por las actividades de una empresa u organización de referencia.
            </p>
            <p class="epsilon-paragraph">
                <strong>Peligro:</strong> Es una fuente o situación con potencial de daño, en términos de muerte, enfermedad, lesión, daño a la propiedad, ambiente de trabajo o una combinación de estos.
            </p>
            <p class="epsilon-paragraph">
                <strong>Riesgo:</strong> Riesgo es la posibilidad de que un objeto, sustancia, material o fenómeno pueda desencadenar alguna perturbación en la salud o integridad física del trabajador.
            </p>
        </div>

        <div>
            <h3 class="gamma-title">4. RESPONSABLES</h3>
            <p class="epsilon-paragraph">
                • <strong>Alta Gerencia:</strong> Responsable de la asignación de los recursos necesarios para la implementación de la legislación aplicable a la gestión de la organización y otros requisitos legales.
            </p>
            <p class="epsilon-paragraph">
                • <strong>Líder del sistema de gestión:</strong> Encargado de asegurar la actualización de los requisitos legales, evaluar su cumplimiento y realizar la divulgación a todos los niveles de la organización.
            </p>
            <p class="epsilon-paragraph">
                • <strong>COPASST:</strong> Apoyarán el proceso de verificación del cumplimiento legal.
            </p>
            <p class="epsilon-paragraph">
                • <strong>Líder de proceso:</strong> Cumplir y fomentar al interior de sus equipos de trabajo el cumplimiento de los requerimientos legales en riesgos laborales.
            </p>
            <p class="epsilon-paragraph">
                • <strong>Colaborador:</strong> Dar cumplimiento a las normas en materia de seguridad y salud en el trabajo.
            </p>
            <p class="epsilon-paragraph">
                • <strong>Área Legal:</strong> Brindar asesoría jurídica a petición del responsable del presente procedimiento.
            </p>

            <h3 class="gamma-title">5. GENERALIDADES</h3>
            <p class="epsilon-paragraph">N/A.</p>
        </div>

        <div>
            <h3 class="gamma-title">6. PROCEDIMIENTO</h3>
            <table class="zeta-table">
                <tr>
                    <th>Procedimiento</th>
                    <th>Responsable</th>
                </tr>
                <tr>
                    <td>
                        <strong>6.1. Identificación de los requisitos legales en SST</strong><br>
                        6.1.1. Identificar los requisitos legales y otros requisitos en materia de seguridad y salud en el trabajo, seguridad social, riesgos laborales, entre otros, aplicables a la actividad económica que se desarrolla en la compañía y que tiene relación directa con los peligros y riesgos de los procesos.<br>
                        Las principales fuentes de información establecidas por los diferentes entes reguladores son:<br>
                        • Ministerio de trabajo: <a href="https://www.mintrabajo.gov.co/normatividad/leyes-y-decretos-ley/leyes">https://www.mintrabajo.gov.co/normatividad/leyes-y-decretos-ley/leyes</a><br>
                        • Ministerio de Salud y Protección Social<br>
                        • Ministerio de Ambiente y Desarrollo Sostenible<br>
                        • Ministerio de Transporte<br>
                        • Ministerio de Justicia y del Derecho: <a href="http://www.suin-juriscol.gov.co/">http://www.suin-juriscol.gov.co/</a><br>
                        • Superintendencia de Puertos y Transporte<br>
                        • Diario Oficial<br>
                        • Página de la Organización Internacional del Trabajo<br>
                        • Página de la Alcaldía Mayor de Bogotá<br>
                        • Administradoras de riesgos laborales ARL<br>
                        • Publicaciones Legis<br>
                        Los requisitos legales identificados se plasmarán en la matriz legal.
                    </td>
                    <td>Gestión Humana / Responsable del Sistema de Gestión SST</td>
                </tr>
                <tr>
                    <td>
                        <strong>6.2. Análisis de la aplicabilidad de los requisitos legales</strong><br>
                        6.2.1. Revisar la información para determinar los requisitos aplicables a los procesos y actividades que se desarrollan en la empresa, en materia de riesgos laborales, seguridad social, seguridad y salud en el trabajo, identificando los artículos, parágrafos, numerales, etc.<br>
                        Los requisitos analizados y que aplican a la gestión de la organización, se consolidan en la matriz de requisitos legales, determinando lo siguiente:<br>
                        • Tema / agente<br>
                        • Subtema / peligro<br>
                        • Norma<br>
                        • URL<br>
                        • Descripción<br>
                        • Autoridad que lo emite<br>
                        • Notas de vigencia<br>
                        • Artículo<br>
                        • Requerimientos específicos (exigencias)
                    </td>
                    <td>Responsable Sistema de Gestión SST</td>
                </tr>
                <tr>
                    <td>
                        <strong>6.3. Comunicación y/o divulgación de los requisitos legales</strong><br>
                        6.3.1. Identificar los requisitos legales, incluyendo los cambios que se presenten en las normas.<br>
                        Los requisitos legales u otros requisitos de seguridad y salud en el trabajo que sean aplicables a las actividades o productos suministrados por proveedores y/o subcontratistas serán comunicados a estos, de manera que se pueda garantizar que tales proveedores o subcontratistas conocen las exigencias de la empresa, así como a las partes interesadas que pudieran ser afectadas.
                    </td>
                    <td>Gestión Humana / Responsable del Sistema de Gestión SST</td>
                </tr>
                <tr>
                    <td>
                        <strong>6.4. Seguimiento y evaluación de cumplimiento de los requisitos legales</strong><br>
                        6.4.1. Revisar periódicamente a intervalos planificados, el estado de la matriz legal y la conformidad en el cumplimiento de los requerimientos legales, verificando que la empresa cumpla con dichos requisitos exigibles, al igual que con otros requisitos que se generen en las nuevas normas emitidas por los entes reguladores.<br>
                        Realizar anualmente un proceso de auditoría interna, en donde se evaluará el cumplimiento de los requisitos legales identificados en la matriz legal.<br>
                        Se tendrá en cuenta los siguientes criterios en la evaluación:<br>
                        • Cumple totalmente: Cuando se cumple con el criterio enunciado del requisito legal, se implementa y se mantiene.<br>
                        • Cumple parcialmente: Cuando se cumple parcialmente con el criterio enunciado del requisito legal.<br>
                        • No cumple: Cuando no se implementa y no se mantiene el requisito legal enunciado en la matriz legal, o cuando no se tiene identificado un requisito legal aplicable.
                    </td>
                    <td>Gestión Humana / Responsable del Sistema de Gestión SST</td>
                </tr>
                <tr>
                    <td>
                        <strong>6.5. Definición de planes de acción</strong><br>
                        6.5.1. Cuando se identifique una desviación frente al cumplimiento de los requerimientos legales, se establecerán las acciones correctivas y/o preventivas, de acuerdo con los lineamientos definidos en el procedimiento de acciones correctivas, preventivas y de mejora.
                    </td>
                    <td>Gestión Humana / Responsable del Sistema de Gestión SST</td>
                </tr>
                <tr>
                    <td>
                        <strong>6.6. Actualización de los requisitos legales</strong><br>
                        6.6.1. Permanentemente se consultará las fuentes de información legal, para identificar la emisión de nueva normatividad, cambios en la normatividad vigente o derogación de normas. En la medida que existan cambios en las normas que afecten directamente a los procesos de la organización, se divulgarán los cambios presentados a las partes interesadas, a través de los mecanismos establecidos en el presente procedimiento.<br>
                        Anualmente se revisará y actualizará la matriz legal, para incluir la nueva normatividad y retirar aquella normatividad que haya sido derogada, para tal efecto se contará con la asesoría y soporte de la ARL con la cual se encuentra afiliada la empresa.
                    </td>
                    <td>Gestión Humana / Responsable del Sistema de Gestión SST / Área Legal</td>
                </tr>
                <tr>
                    <td>
                        <strong>6.6.2.</strong> Realizar las respectivas modificaciones y/o actualizaciones documentales que solicite el responsable del Sistema de Gestión SST, para la selección, evaluación y contratación de proveedores y colaboradores cuando los cambios en la legislación, afecta la gestión de estos y/o sus operaciones.
                    </td>
                    <td>Responsable del Sistema de Gestión SST / Área Legal</td>
                </tr>

            </table>
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

        <!-- <div>
            <a href="<?= base_url('/generatePdf_requisitosLegales') ?>" target="_blank">
                <button type="button">PDF</button>
            </a>
        </div> -->

</body>

</html>