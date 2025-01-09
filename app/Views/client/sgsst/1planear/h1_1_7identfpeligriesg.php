<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>4.1.0 Procedimiento para la Identificación de Peligros y Valoración de Riesgos</title>
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

        /* ***************************************************************************** */
        .delta-container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
            box-sizing: border-box;
        }

        .epsilon-title {
            font-size: 24px;
            text-align: center;
            margin-bottom: 20px;
        }

        .epsilon-paragraph {
            font-size: 16px;
            margin-bottom: 15px;
            text-align: justify;
        }

        .epsilon-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .epsilon-table th,
        .epsilon-table td {
            border: 1px solid #000;
            padding: 10px;
            text-align: left;
        }

        .epsilon-table th {
            background-color: #f0f0f0;
        }

        .theta-container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
            box-sizing: border-box;
        }

        .zeta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .zeta-table th,
        .zeta-table td {
            border: 1px solid #000;
            padding: 10px;
            text-align: left;
            vertical-align: top;
        }

        .zeta-table th {
            background-color: #f0f0f0;
        }

        .iota-container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
            box-sizing: border-box;
        }

        .zeta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .zeta-table th,
        .zeta-table td {
            border: 1px solid #000;
            padding: 10px;
            text-align: left;
            vertical-align: top;
        }

        .zeta-table th {
            background-color: #f0f0f0;
        }

        .zeta-table ul {
            margin: 0;
            padding-left: 20px;
        }

        .kappa-container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
            box-sizing: border-box;
        }

        .zeta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .zeta-table th,
        .zeta-table td {
            border: 1px solid #000;
            padding: 10px;
            text-align: left;
            vertical-align: top;
        }

        .zeta-table th {
            background-color: #f0f0f0;
        }

        .zeta-table ul {
            margin: 0;
            padding-left: 20px;
        }

        .lambda-container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
            box-sizing: border-box;
        }

        .risk-matrix {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .risk-matrix th,
        .risk-matrix td {
            border: 1px solid #000;
            padding: 10px;
            text-align: center;
        }

        .risk-matrix th {
            background-color: #d3d3d3;
        }

        .blue-background {
            background-color: #0055a4;
            color: white;
        }

        .yellow-background {
            background-color: #ffff00;
        }

        .orange-background {
            background-color: #ffa500;
        }

        .red-background {
            background-color: #ff0000;
            color: white;
        }

        .green-background {
            background-color: #00ff00;
        }

        .gray-background {
            background-color: #b0b0b0;
        }

        .sigma-container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
            box-sizing: border-box;
        }

        .recommendation-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .recommendation-table th,
        .recommendation-table td {
            border: 1px solid #000;
            padding: 10px;
            text-align: left;
        }

        .recommendation-table th {
            background-color: #d3d3d3;
        }

        .green-background {
            background-color: #00ff00;
        }

        .yellow-background {
            background-color: #ffff00;
        }

        .orange-background {
            background-color: #ffa500;
        }

        .red-background {
            background-color: #ff0000;
            color: white;
        }

        .xi-container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
            box-sizing: border-box;
        }

        .acceptability-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .acceptability-table th,
        .acceptability-table td {
            border: 1px solid #000;
            padding: 10px;
            text-align: left;
        }

        .acceptability-table th {
            background-color: #d3d3d3;
        }

        .xi-container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
            box-sizing: border-box;
        }

        .xi-paragraph {
            font-size: 16px;
            margin-bottom: 15px;
            text-align: justify;
        }

        .xi-list {
            margin-bottom: 15px;
            padding-left: 20px;
        }

        .omicron-container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
            box-sizing: border-box;
        }

        .omicron-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;

        }

        .omicron-table th,
        .omicron-table td {
            border: 1px solid #000;
            padding: 10px;
            text-align: left;
            vertical-align: top;
        }

        .omicron-table th {
            background-color: #f0f0f0;
        }

        .roman-container {
            width: 100%;
        }

        .roman-table {
            width: 100%;
            border-collapse: collapse;
        }

        .roman-table th,
        .roman-table td {
            border: 1px solid #000;
            padding: 10px;
            text-align: left;
            vertical-align: top;
        }

        .roman-table th {
            background-color: #f0f0f0;
        }

        .roman-table td {
            width: 25%;
        }

        .roman-title {
            font-size: 1.5em;
            font-weight: bold;
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

    <div class="delta-container">
        <h3 class="epsilon-title">PRESENTACIÓN</h3>
        <p class="epsilon-paragraph">La herramienta de identificación de los peligros, evolución y valoración de riesgos permite conocer y entender los peligros de la organización, además debe orientarnos en la definición de los objetivos de control y acciones propias para su gestión; en esto radica su importancia, porque sobre la coherencia y validez de los resultados obtenidos se debe construir el Sistema de Gestión de Seguridad y Salud en el Trabajo (SG-SST), que garantice un acertado tratamiento de los riesgos y la mejora continua de la organización.</p>

        <p class="epsilon-paragraph">Conociendo la importancia de la temática <strong><?= $client['nombre_cliente'] ?></strong> decidió adoptar una nueva metodología que permitiera realizar un proceso sistemático para la identificación de los peligros, evaluación y valoración de riesgos, adoptando diferentes parámetros para calcular la probabilidad y consecuencia.</p>

        <p class="epsilon-paragraph">En la nueva metodología se determinan los niveles de riesgo a través de un ejercicio matricial de calificación directa, en donde la estimación de la probabilidad es orientada por la calidad y suficiencia de los controles y defensas existentes y la calificación de las consecuencias por la afectación en la salud, pérdidas económicas, de imagen e información; haciendo más amplías las posibilidades de calificación.</p>

        <p class="epsilon-paragraph">Además, para hacer más precisa la visualización y estimación de los riesgos, se ampliaron los cuadrantes de la matriz de riesgos, pasando de una de tres por tres (3x3) a una de cuatro por cuatro (4x4); lo que permitirá encontrar valoraciones más ajustadas al comportamiento de los riesgos en las organizaciones.</p>

        <p class="epsilon-paragraph">Para construir esta metodología se revisaron varias fuentes bibliográficas, entre ellas guías y normas, como la Guía Técnica Colombia GTC 45 (segunda actualización), los principios de la norma NTC- OHSAS 18001, la norma BS 8800 (British Standard) y la NTP 330 del Instituto Nacional de Seguridad e Higiene en el Trabajo de España (INSHT), además se realizó una consulta de expertos (método Delphi), por último con la primera versión se realizó una prueba piloto donde sus sugerencias sirvieron para ajustar la actual metodología.</p>

        <p class="epsilon-paragraph">Esperamos que este documento suministre información suficiente para el desarrollo de acciones orientadas al control de las de pérdidas, al mejoramiento de la calidad de vida de los trabajadores y de la productividad de la empresa, al igual que los elementos de entrada para la documentación e implementación del SG-SST.</p>

        <h3 class="epsilon-title">14.1 METODOLOGÍA</h3>

        <p class="epsilon-paragraph">La metodología establecida por la <strong><?= $client['nombre_cliente'] ?></strong> permite realizar un proceso sistemático de identificación de peligros, su estimación y valoración de los riesgos propios de la organización, además de proponer controles generales y específicos al riesgo, de acuerdo con su aceptabilidad.</p>

        <h3 class="epsilon-title">14.1.1 Contexto de la organización</h3>

        <p class="epsilon-paragraph">Implica recolectar la información necesaria para poder adelantar una amplia y completa identificación de los peligros para la seguridad y salud en el trabajo (SST), entre ellas: actividad económica de la empresa, procesos y servicios con que cuenta, objetivos estratégicos, planeación estratégica, organigrama de la empresa, información sociodemográfica de la empresa, tipos de contratación de trabajadores, ubicación geográfica, definición de responsabilidades, políticas de gestión del riesgo.</p>

        <h3 class="epsilon-title">14.1.2 Identificación de Peligros para la Seguridad y Salud en el Trabajo</h3>

        <p class="epsilon-paragraph">Para hacer la identificación de los Peligros se debe realiza la matriz de peligros</p>

        <p class="epsilon-paragraph"><strong>Anexo:</strong> Matriz de Peligros.</p>

        <p class="epsilon-paragraph">Para el ejercicio de identificación de los peligros se realizó la verificación de los siguientes puntos como:</p>

        <ul class="epsilon-paragraph">
            <li>Plano del sitio a evaluar.</li>
            <li>El inventario de las materias primas o insumos utilizados.</li>
            <li>Subproductos.</li>
            <li>El inventario de las áreas y lugares.</li>
            <li>Equipos principales y auxiliares.</li>
            <li>Procesos.</li>
            <li>Actividades rutinarias y no rutinarias.</li>
            <li>Análisis histórico de accidentes, incidentes y enfermedades laborales.</li>
            <li>Análisis histórico de comportamiento del personal.</li>
            <li>Efectos posibles y daño potencial.</li>
            <li>Requisitos legales y de otro tipo aplicables y su grado de cumplimiento.</li>
            <li>El inventario de cambios realizados en almacenes y plantas.</li>
        </ul>

        <h3 class="epsilon-title">14.1.3 Análisis y evaluación del Riesgo</h3>

        <p class="epsilon-paragraph">Para el análisis y evaluación del riesgo se realiza un ejercicio matricial de estimación de la probabilidad por consecuencia de los peligros identificados, en los cuales se contempla:</p>

        <ul class="epsilon-paragraph">
            <li>Los equipos y las actividades que son realizadas en cada proceso o servicio.</li>
            <li>Los peligros asociados y los riesgos que para la seguridad y salud en el trabajo se pueden generar.</li>
            <li>Controles y defensas actuales existentes.</li>
            <li>Evaluar la calidad y suficiencia de los controles y defensas.</li>
            <li>Su evaluación se puede realizar de manera cualitativa o cuantitativa y para hacer más exacta su estimación se pueden utilizar las metodologías más precisas o avanzadas en el estado del arte en la evaluación del peligro, que cumplan con legislación vigente en el país o con los estándares nacionales o internacionales, si no tiene legislación para su evaluación.</li>
            <li>Anteriores evaluaciones de riesgos.</li>
        </ul>

        <p class="epsilon-paragraph"><strong>La matriz de 4x4.</strong></p>
    </div>

    <div class="theta-container">
        <h3>Calificación del Riesgo</h3>
        <table class="zeta-table">
            <thead>
                <tr>
                    <th>Calificación</th>
                    <th>Criterio</th>
                    <th>Detalle del Criterio</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>BAJA</td>
                    <td>Los controles y defensas establecidos hacen improbable la materialización del riesgo, nunca se ha expresado</td>
                    <td>
                        <ul>
                            <li>Hay un gran número de controles de ingeniería que no son basados en el comportamiento y que están diseñados "a prueba de fallos”, como:
                                <ul>
                                    <li>barreras o guardas fijas;</li>
                                    <li>mecanismos sensibles a la presión o al contacto tales como bordes, barras y perfiles de posición que se accionan al contacto o la presión;</li>
                                    <li>controles a dos manos que requieren contacto constante durante todo el movimiento peligroso, con un circuito de control apropiado.</li>
                                </ul>
                            </li>
                            <li>Hay un pequeño número de controles administrativos y barreras, como:
                                <ul>
                                    <li>barreras perimetrales como barandillas;</li>
                                    <li>barreras móviles no aseguradas o con bloqueo mecánico;</li>
                                    <li>barreras que eviten que se introduzcan las manos en el peligro;</li>
                                    <li>sistemas de advertencia visual o sonora como bocinas, alarmas, luces, voz sintetizada para indicar el arranque de equipos o el movimiento de personal.</li>
                                </ul>
                            </li>
                            <li>La mayoría de los trabajadores asumen comportamientos seguros (entre el 95% y el 100%).</li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <td>MEDIA</td>
                    <td>Los controles y defensas establecidos hacen posible la materialización del riesgo, ya se ha expresado alguna vez</td>
                    <td>
                        <ul>
                            <li>Hay un gran número de controles administrativos y barreras y un bajo número de controles de ingeniería.</li>
                            <li>Se refuerza el comportamiento basado en controles administrativos como:
                                <ul>
                                    <li>política disciplinaria específica;</li>
                                    <li>procesos formales de certificación de los trabajadores;</li>
                                    <li>programas formales de verificación del comportamiento;</li>
                                    <li>implementación de métodos de seguimiento y verificación para asegurar el cumplimiento de los procedimientos.</li>
                                </ul>
                            </li>
                            <li>Entre el 70% y el 85% de los trabajadores asumen comportamientos seguros.</li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <td>ALTA</td>
                    <td>Los controles y defensas establecidos hacen completamente probable la materialización del riesgo</td>
                    <td>
                        <ul>
                            <li>Aún hay alta dependencia de controles administrativos que dependen del comportamiento de las personas, como:
                                <ul>
                                    <li>procedimientos o políticas documentadas;</li>
                                    <li>programas de capacitación;</li>
                                    <li>elementos de protección personal;</li>
                                    <li>control visual de distancias permitidas;</li>
                                    <li>señalización perimetral (por ejemplo, líneas en el piso);</li>
                                    <li>avisos de advertencia.</li>
                                </ul>
                            </li>
                            <li>Se están introduciendo mecanismos para reforzar el comportamiento como:
                                <ul>
                                    <li>política disciplinaria específica;</li>
                                    <li>procesos formales de certificación de los trabajadores.</li>
                                </ul>
                            </li>
                            <li>Entre el 50% y el 70% de los trabajadores asumen comportamientos seguros.</li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <td>MUY ALTA</td>
                    <td>Los controles y defensas establecidos hacen inminente la materialización del riesgo</td>
                    <td>
                        <ul>
                            <li>Los controles administrativos dependen del comportamiento de las personas; estos controles corresponden a:
                                <ul>
                                    <li>procedimientos o políticas documentadas;</li>
                                    <li>programas de capacitación;</li>
                                    <li>elementos de protección personal;</li>
                                    <li>control visual de distancias permitidas;</li>
                                    <li>señalización perimetral (por ejemplo, líneas en el piso);</li>
                                    <li>avisos de advertencia.</li>
                                </ul>
                            </li>
                            <li>Menos del 50% de los trabajadores asumen comportamientos seguros.</li>
                        </ul>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>


    <div class="iota-container">
        <h3>Probabilidad en riesgos de Higiene</h3>
        <table class="zeta-table">
            <thead>
                <tr>
                    <th>FACTOR</th>
                    <th>CALIFICACIÓN</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Químico</td>
                    <td>
                        <ul>
                            <li><strong>Muy Alta:</strong> Si los niveles de Riesgo Relativo (RR) es mayor a 1 (Superior al 100% del TLV)</li>
                            <li><strong>Alta:</strong> Si Riesgo Relativo (RR) entre 0.5 y 0.99 (entre el 50% y el 99% del TLV)</li>
                            <li><strong>Media:</strong> Si Riesgo Relativo (RR) menor de 0.5 (Menor al 50% y mayor al 10% del TLV)</li>
                            <li><strong>Baja:</strong> Si Riesgo Relativo (RR) menor de 0.1 (Menor al 10% del TLV)</li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <td>Ruido</td>
                    <td>
                        <ul>
                            <li><strong>Muy Alta:</strong> Si los niveles de ruido o la dosis es superior a 95 dB(A)</li>
                            <li><strong>Alta:</strong> Si los niveles de ruido o la dosis se encuentran entre 85 y 95 dB(A)</li>
                            <li><strong>Media:</strong> Si los niveles de ruido o la dosis se encuentra entre 80 y 84.9 dB(A)</li>
                            <li><strong>Baja:</strong> Si los niveles de ruido o la dosis son inferiores a 80 dB(A)</li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <td>Temperaturas Extremas</td>
                    <td>
                        <ul>
                            <li><strong>Muy Alta:</strong> Si el WBGT encontrado es superior al establecido por la ACGIH (TLV)</li>
                            <li><strong>Alta:</strong> Si el WBGT encontrado es inferior hasta en dos grados Celsius al establecido por la ACGIH (TLV)</li>
                            <li><strong>Media:</strong> Si el WBGT encontrado es inferior en más de dos grados Celsius al establecido por la ACGIH, pero el ambiente no es confortable según los valores de temperatura LEST</li>
                            <li><strong>Baja:</strong> Si el ambiente es confortable según los valores de temperatura LEST</li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <td>Iluminación</td>
                    <td>
                        <ul>
                            <li><strong>Muy Alta:</strong> Los niveles se encuentran por debajo en más de un 50% con respecto a los recomendados por el RETILAP</li>
                            <li><strong>Alta:</strong> Si los niveles se encuentran por debajo entre un 49% - 20% con respecto a los recomendados por el RETILAP</li>
                            <li><strong>Media:</strong> Si los niveles se encuentran por debajo en menos de un 20% con respecto a los recomendados por el RETILAP</li>
                            <li><strong>Baja:</strong> Si los niveles se encuentran dentro del rango recomendado por el RETILAP</li>
                        </ul>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="kappa-container">
        <h3>Consecuencia</h3>
        <p>Se evalúa la consecuencia potencial, independiente de los controles y defensas implementados; se selecciona la calificación que corresponde al criterio más exigente o crítico.</p>

        <table class="zeta-table">
            <thead>
                <tr>
                    <th>Calificación</th>
                    <th>Criterio</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>INSIGNIFICANTE</td>
                    <td>
                        <ul>
                            <li>Sin lesión o lesiones sin incapacidad.</li>
                            <li>Pérdidas menores a 15 SMMLV.</li>
                            <li>Afectación a la imagen de la empresa solo de conocimiento interno.</li>
                            <li>Suspensión de actividad máximo 3 días.</li>
                            <li>No hay pérdida de la información.</li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <td>MODERADO</td>
                    <td>
                        <ul>
                            <li>Lesión o enfermedad con incapacidad temporal, NO permanente.</li>
                            <li>Pérdidas entre 16 y 50 SMMLV.</li>
                            <li>Afectación a la imagen de la empresa solo de conocimiento local.</li>
                            <li>Suspensión de actividad entre 4 - 6 días.</li>
                            <li>Pérdida de la información, pero con respaldo.</li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <td>DAÑINO</td>
                    <td>
                        <ul>
                            <li>Lesión o enfermedad con posibilidad de generar incapacidad permanente parcial.</li>
                            <li>Pérdidas entre 51 y 100 SMMLV.</li>
                            <li>Afectación a la imagen de la empresa solo de conocimiento nacional.</li>
                            <li>Suspensión de actividad entre 7 - 15 días.</li>
                            <li>Pérdida de la información, sin respaldo.</li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <td>EXTREMO</td>
                    <td>
                        <ul>
                            <li>Lesión o enfermedad que pueda generar invalidez o muerte.</li>
                            <li>Pérdidas mayores a 100 SMMLV.</li>
                            <li>Afectación a la imagen de la empresa a nivel internacional, suspensión de actividad más de 16 días.</li>
                            <li>Pérdida de la información crítica, sin respaldo.</li>
                        </ul>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="lambda-container">
        <h3>Matriz de Valoración de Riesgos</h3>
        <table class="risk-matrix">
            <thead>
                <tr>
                    <th class="blue-background" rowspan="2">MATRIZ DE VALORACIÓN DE RIESGOS</th>
                    <th class="gray-background" colspan="4">CONSECUENCIAS</th>
                </tr>
                <tr>
                    <th>INSIGNIFICANTE</th>
                    <th>MODERADO</th>
                    <th>DAÑINO</th>
                    <th>EXTREMO</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="gray-background">MUY ALTA</td>
                    <td class="yellow-background">MEDIO</td>
                    <td class="orange-background">ALTO</td>
                    <td class="red-background">CRÍTICO</td>
                    <td class="red-background">CRÍTICO</td>
                </tr>
                <tr>
                    <td class="gray-background">ALTA</td>
                    <td class="yellow-background">MEDIO</td>
                    <td class="orange-background">ALTO</td>
                    <td class="orange-background">ALTO</td>
                    <td class="red-background">CRÍTICO</td>
                </tr>
                <tr>
                    <td class="gray-background">MEDIA</td>
                    <td class="green-background">BAJO</td>
                    <td class="yellow-background">MEDIO</td>
                    <td class="orange-background">ALTO</td>
                    <td class="orange-background">ALTO</td>
                </tr>
                <tr>
                    <td class="gray-background">BAJA</td>
                    <td class="green-background">BAJO</td>
                    <td class="green-background">BAJO</td>
                    <td class="yellow-background">MEDIO</td>
                    <td class="yellow-background">MEDIO</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="sigma-container">
        <h3>Recomendaciones según Riesgo</h3>
        <table class="recommendation-table">
            <thead>
                <tr>
                    <th>RIESGO</th>
                    <th>RECOMENDACIONES</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="green-background"><strong>BAJO</strong></td>
                    <td>
                        Mantener las medidas de control existentes. Se deben hacer evaluaciones periódicas para verificar que el riesgo sigue siendo bajo. <br>
                        Es importante que en el plan de trabajo se definan los periodos para valorar este riesgo.
                    </td>
                </tr>
                <tr>
                    <td class="yellow-background"><strong>MEDIO</strong></td>
                    <td>
                        Se deben hacer esfuerzos por reducir el riesgo. Implementar estándares de seguridad, permisos de trabajo o listas de verificación para realizar control operativo del riesgo. Es importante justificar la intervención y su rentabilidad (Costo - beneficio). <br>
                        Se deben hacer verificaciones periódicas dentro del plan de trabajo, para evaluar si el riesgo aún es medio, comprobando que no hay tendencia a subir de nivel.
                    </td>
                </tr>
                <tr>
                    <td class="orange-background"><strong>ALTO</strong></td>
                    <td>
                        Se debe reducir el riesgo a través del diseño y ejecución de un programa de gestión. Como está asociado a lesiones muy graves, se debe garantizar la reducción de su probabilidad. <br>
                        Verificar que el riesgo está bajo control antes de realizar cualquier tarea.
                    </td>
                </tr>
                <tr>
                    <td class="red-background"><strong>CRÍTICO</strong></td>
                    <td>
                        La intervención es urgente. En presencia de un riesgo así, se sugiere no realizar ningún trabajo hasta contar con las medidas de control que impacten la probabilidad de su ocurrencia. <br>
                        De ser indispensable la realización de la labor, se deben adoptar todas las medidas necesarias para evitar la materialización del riesgo; las medidas deben garantizar que el riesgo está bajo control antes de iniciar cualquier tarea. <br>
                        Una actividad operacional no debe estar en este rango, desde el diseño de la misma se deben adaptar sus respectivos controles.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>


    <div class="xi-container">
        <h3>Aceptabilidad del Riesgo</h3>
        <table class="acceptability-table">
            <thead>
                <tr>
                    <th>Nivel del Riesgo</th>
                    <th>Aceptabilidad (teniendo en cuenta la definición de nivel de riesgo)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Bajo</strong></td>
                    <td>Aceptable</td>
                </tr>
                <tr>
                    <td><strong>Medio</strong></td>
                    <td>Mejorable</td>
                </tr>
                <tr>
                    <td><strong>Alto</strong></td>
                    <td>No aceptable o aceptable con medidas de control específicas</td>
                </tr>
                <tr>
                    <td><strong>Crítico</strong></td>
                    <td>No aceptable</td>
                </tr>
            </tbody>
        </table>
    </div>


    <div class="xi-container">
        <p class="xi-paragraph">
            Cada empresa debe establecer o acogerse a una determinación de nivel de aceptación de sus riesgos, de acuerdo con los objetivos, metas, visión, misión, tolerancia al riesgo y la política de seguridad y salud en el trabajo que tenga establecida.
        </p>

        <h3>14.1.4 DEFINICIÓN DE LAS MEDIDAS PARA EL TRATAMIENTO DEL RIESGO SEGÚN LA JERARQUIZACIÓN DE CONTROLES</h3>
        <p class="xi-paragraph">
            Una vez culminada la evaluación de riesgos, se definieron las medidas requeridas para el tratamiento del riesgo. Para ello se consideraron medidas adicionales, teniendo en cuenta el siguiente orden de prioridades:
        </p>
        <ul class="xi-list">
            <li>Eliminación</li>
            <li>Sustitución</li>
            <li>Controles de ingeniería</li>
            <li>Señalización/advertencias y/o controles administrativos</li>
            <li>Equipos de protección personal</li>
        </ul>
        <p class="xi-paragraph">
            Después de definir estos controles adicionales, se debe recalcular la estimación del riesgo para definir si las medidas propuestas son adecuadas y se reflejan en la disminución de la probabilidad.
        </p>

        <h3>14.1.5 IMPLEMENTACIÓN DE LAS MEDIDAS DE CONTROL</h3>
        <p class="xi-paragraph">
            Para garantizar la implementación de las medidas de control se debe definir un cronograma de actividades que contemple:
        </p>
        <ul class="xi-list">
            <li>Qué se espera hacer</li>
            <li>Cómo se espera hacer</li>
            <li>Donde se va a hacer</li>
            <li>Cuando se va a hacer</li>
            <li>Quién lo va a hacer</li>
            <li>Cuánto cuesta hacerlo</li>
        </ul>
        <p class="xi-paragraph">
            Para desarrollar este proceso es necesario cruzar la valoración de riesgos con la determinación de objetivos y programas. Luego de implementados los controles adicionales, debe adelantarse su evaluación:
        </p>
        <ul class="xi-list">
            <li>Definir si cumplen con la intención de diseño y determinar la necesidad de controles adicionales que aseguren el objetivo esperado.</li>
            <li>Determinar si con su implementación aparecen riesgos nuevos que deban ser tratados para eliminarlos o minimizar su impacto.</li>
        </ul>

        <h3>14.1.6 SEGUIMIENTO DE LAS MEDIDAS DE CONTROL PARA GARANTIZAR QUE CONTINÚEN SIENDO ADECUADAS</h3>
        <p class="xi-paragraph">
            Luego de implementadas las medidas para el tratamiento de los riesgos, es necesario realizar el seguimiento a su implementación, efectividad y permanencia en el tiempo. El proceso incluye:
        </p>
        <ul class="xi-list">
            <li>Revisión de la conveniencia del tratamiento</li>
            <li>Verificación del uso correcto de los controles y defensas</li>
            <li>Revisión de los indicadores de seguridad y salud</li>
            <li>Revisión del cumplimiento de la legislación</li>
        </ul>
        <p class="xi-paragraph">
            Este seguimiento debe programarse y realizarse a través de inspecciones o auditorías del sistema de gestión.
        </p>

        <h3>Revisión de la valoración de riesgos</h3>
        <p class="xi-paragraph">
            En forma periódica y cuando las condiciones cambien, se debe realizar una revisión de la valoración de riesgos a fin de garantizar que:
        </p>
        <ul class="xi-list">
            <li>Se incluyan los peligros nuevos provenientes de cambios o modificaciones.</li>
            <li>Se modifique la evaluación del riesgo luego de implementadas las medidas para el tratamiento del riesgo.</li>
            <li>Cambio en la naturaleza del trabajo o actividad.</li>
            <li>Fallas o debilidades en los controles reveladas por las inspecciones de seguridad, auditorías, investigaciones de accidentes e incidentes (análisis de causalidad de los mismos).</li>
            <li>Desarrollo de análisis de seguridad más profundos a riesgos específicos.</li>
            <li>Nueva legislación.</li>
            <li>Cambios en los procesos o servicios.</li>
            <li>Cambio o mejora de equipos.</li>
        </ul>

        <h3>Comunicación de los Riesgos</h3>
        <p class="xi-paragraph">
            La matriz de riesgos y su información se debe considerar como documento controlado, y debe estar disponible para la consulta y análisis en los procesos de formación e inducción, tanto de personal vinculado como tercerizado.
        </p>

        <h3>14.2 FACTORES DE RIESGO Y PELIGROS RELACIONADOS</h3>
    </div>

    <div class="roman-container">
        <h3 class="roman-title">Glosario de Factores de Riesgo</h3>
        <table class="roman-table">
            <thead>
                <tr>
                    <th>Agente de Riesgo</th>
                    <th>Descripción</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Factores de Riesgo Biológicos</td>
                    <td>Todos aquellos seres vivos ya sean de origen animal o vegetal y todas aquellas sustancias derivadas de los mismos, presentes en el puesto de trabajo y que pueden ser susceptibles de provocar efectos negativos en la salud de los trabajadores. Los efectos negativos pueden manifestarse en procesos infecciosos, tóxicos o alérgicos.</td>
                </tr>
                <tr>
                    <td>Agente de Riesgo por Carga Física</td>
                    <td>Se refiere a todos aquellos aspectos de la organización del trabajo, de la estación o puesto de trabajo y de su diseño que pueden alterar la relación del individuo con el objeto técnico, produciendo problemas en la secuencia de uso o la producción.</td>
                </tr>
                <tr>
                    <td>Agente de Riesgo Eléctricos</td>
                    <td>Se refiere a los sistemas eléctricos de las máquinas y equipos que, al entrar en contacto con las personas o las instalaciones, pueden provocar lesiones a las personas y daños a la propiedad. Los riesgos se clasifican en muy baja, baja, media, alta y extra alta tensión.</td>
                </tr>
                <tr>
                    <td>Agente de Riesgo Físico</td>
                    <td>Son todos aquellos factores ambientales de naturaleza física que pueden provocar efectos adversos a la salud, dependiendo de la intensidad, exposición y concentración de los mismos.</td>
                </tr>
                <tr>
                    <td>Agente de Riesgo Físico-Químico</td>
                    <td>Riesgo generado por la combinación de las condiciones físicas y químicas de los procesos, que pueden poner en serio riesgo de incendio y explosión a la organización.</td>
                </tr>
                <tr>
                    <td>Agente de Riesgos Locativos</td>
                    <td>Condiciones de las instalaciones o áreas de trabajo que pueden ocasionar accidentes de trabajo o pérdidas para la empresa.</td>
                </tr>
                <tr>
                    <td>Agente de Riesgo Mecánico</td>
                    <td>Objetos, máquinas, equipos y herramientas que, por sus condiciones de funcionamiento, diseño o ubicación, tienen la capacidad potencial de entrar en contacto con las personas o materiales, provocando lesiones o daños.</td>
                </tr>
                <tr>
                    <td>Agente de Riesgo Psicosocial</td>
                    <td>Se refiere a aquellos aspectos intrínsecos y organizativos del trabajo y a las interrelaciones humanas que, al interactuar con factores humanos endógenos y exógenos, tienen la capacidad de generar cambios en el comportamiento o trastornos físicos y psicosomáticos.</td>
                </tr>
                <tr>
                    <td>Agente de Riesgo Público</td>
                    <td>Situaciones externas, como violencia o delincuencia, que pueden poner en riesgo la seguridad física de las personas en el entorno laboral.</td>
                </tr>
                <tr>
                    <td>Agente de Riesgo Químico</td>
                    <td>Toda sustancia orgánica o inorgánica, natural o sintética, que durante su fabricación, manejo, transporte, almacenamiento o uso, puede ingresar al ambiente de trabajo en forma de aerosoles, gases o vapores, y provocar efectos adversos en la salud.</td>
                </tr>
                <tr>
                    <td>Tareas de Alto Riesgo</td>
                    <td>Son aquellas actividades laborales que, por su naturaleza, conllevan un alto riesgo de accidentes o lesiones graves, requiriendo medidas especiales de prevención y control.</td>
                </tr>
                <tr>
                    <td>Procesos Peligrosos</td>
                    <td>Operaciones o procedimientos industriales que implican un riesgo significativo para la salud y seguridad de los trabajadores, debido a la manipulación de sustancias peligrosas o el uso de maquinaria pesada.</td>
                </tr>
                <tr>
                    <td>Actividades Deportivas</td>
                    <td>Actividades lúdicas o deportivas realizadas por los trabajadores en representación del empleador, que pueden conllevar riesgos físicos.</td>
                </tr>
                <tr>
                    <td>Salud Pública</td>
                    <td>Incluye enfermedades transmitidas por agua, alimentos o por vía inmunoprevenible, que pueden afectar la salud de los trabajadores.</td>
                </tr>
                <tr>
                    <td>Agente de Riesgo Naturales</td>
                    <td>Riesgos relacionados con eventos naturales, como deslizamientos, terremotos, inundaciones, huracanes, maremotos, incendios forestales y erupciones volcánicas, que pueden poner en peligro la vida y seguridad de las personas.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="omicron-container">
        <h3>Factores de Riesgo y Peligros Relacionados</h3>
        <table class="omicron-table">
            <thead>
                <tr>
                    <th>Agente de Riesgo</th>
                    <th>Peligros</th>
                    <th>Descripción</th>
                    <th>Posibles Efectos</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Factores de riesgo Biológicos</td>
                    <td></td>
                    <td>Todos aquellos seres vivos ya sean de origen animal o vegetal y todas aquellas sustancias derivadas de los mismos, presentes en el puesto de trabajo y que pueden ser susceptibles de provocar efectos negativos en la salud de los trabajadores. Efectos negativos se pueden concertar en procesos infecciosos, tóxicos o alérgicos.</td>
                    <td></td>
                </tr>
                <tr>
                    <td>Biológico</td>
                    <td>Derivados de origen animal (Pieles, pelo, estiércol, desechos, etc.)</td>
                    <td>Inhalación, contacto y consumo de pelos, plumas, excrementos, sustancias antigénicas (enzima, proteínas), larvas de invertebrados</td>
                    <td>Enfermedades infecciosas, gastrointestinales y tóxicas y reacciones alérgicas</td>
                </tr>
                <tr>
                    <td>Biológico</td>
                    <td>Microrganismos (Mordeduras, golpes, pisadas de animales, picadura de insectos, etc.)</td>
                    <td>Contacto con seres vivos (roedores, serpientes, caballos, perros, gatos, etc.) por medio de mordedura, picadura, rasgadura y en general ataque de animales</td>
                    <td>Golpes, traumas, heridas, infecciones, intoxicación, muerte</td>
                </tr>
                <tr>
                    <td>Biológico</td>
                    <td>Derivados de origen vegetal</td>
                    <td>Inhalación, contacto y consumo de Polvo vegetal, Polen, Madera, Esporas fúngicas, Micotoxinas, Sustancias antigénicas (antibióticos, polisacáridos), incluye además Musgos, Helechos, Semillas, Derivados de Vegetales</td>
                    <td>Enfermedades gastrointestinales, Intoxicaciones, reacciones alérgicas, muerte</td>
                </tr>
                <tr>
                    <td>Biológico</td>
                    <td>Microorganismos tipo hongos, bacterias y/o virus</td>
                    <td>Fungal producida por Hongos, Mónera por bacterias</td>
                    <td>Dermatosis, reacciones alérgicas, enfermedades infectocontagiosas, alteraciones en los diferentes sistemas, muerte</td>
                </tr>
                <tr>
                    <td>Biológico</td>
                    <td>Parásitos</td>
                    <td>Protista producida por Ameba, Plasmodium</td>
                    <td>Enfermedades gastrointestinales, Intoxicaciones, reacciones alérgicas, muerte</td>
                </tr>

                <tr>
                    <td>Carga Física</td>
                    <td>Carga dinámica por esfuerzos</td>
                    <td>Producido por desplazamientos con carga y sin carga, al dejar o levantar cargas, cargas visuales y afección de otros grupos musculares</td>
                    <td>Desórdenes de trauma acumulativo, lesiones del sistema músculo esquelético, fatiga, alteraciones del sistema vascular</td>
                </tr>
                <tr>
                    <td>Carga Física</td>
                    <td>Carga dinámica por movimientos repetitivos</td>
                    <td>Se refiere a la realización de la labor con repeticiones frecuentes de Cuello, extremidades superiores, extremidades inferiores y tronco</td>
                    <td>Desórdenes de trauma acumulativo, lesiones del sistema músculo esquelético, fatiga, alteraciones del sistema vascular</td>
                </tr>
                <tr>
                    <td>Carga Física</td>
                    <td>Carga dinámica por sobreesfuerzos de la voz</td>
                    <td>Riesgo presente en la alta exposición de uso de la voz en tiempo e intensidad</td>
                    <td>Disfonías y afecciones en garganta</td>
                </tr>
                <tr>
                    <td>Carga Física</td>
                    <td>Carga estática de pie</td>
                    <td>Jornadas de alta duración estático de pie en la operación</td>
                    <td>Desórdenes de trauma acumulativo, lesiones del sistema músculo esquelético, fatiga, alteraciones del sistema vascular, alteraciones lumbares, dorsales, cervicales y sacras</td>
                </tr>
                <tr>
                    <td>Carga Física</td>
                    <td>Carga estática sentado</td>
                    <td>Jornadas de alta duración estático sentado en la operación</td>
                    <td>Desórdenes de trauma acumulativo, lesiones del sistema músculo esquelético, fatiga, alteraciones del sistema vascular</td>
                </tr>
                <tr>
                    <td>Carga Física</td>
                    <td>Otras posturas (hiperextensión, cuclillas, posiciones incómodas, etc.)</td>
                    <td>Otras posturas subestándar en la realización de la tarea que generan extensión muscular, posiciones incómodas que comprometan forzar de forma excesiva y prolongada de articulaciones y posiciones no convencionales del cuerpo</td>
                    <td>Desórdenes de trauma acumulativo, lesiones del sistema músculo esquelético, fatiga, alteraciones del sistema vascular</td>
                </tr>
                <tr>
                    <td>Eléctrico</td>
                    <td>Energía Eléctrica muy baja tensión (MBT)</td>
                    <td>Riesgos de contacto o Arco eléctrico menores de 25V</td>
                    <td></td>
                </tr>
                <tr>
                    <td>Eléctrico</td>
                    <td>Energía Eléctrica baja tensión (BT)</td>
                    <td>Riesgos de contacto o Arco eléctrico mayor o igual a 25V y menor o igual a 1000V</td>
                    <td>Fibrilación ventricular, quemaduras, shock</td>
                </tr>
                <tr>
                    <td>Eléctrico</td>
                    <td>Energía Eléctrica Media Tensión (MT)</td>
                    <td>Riesgos de contacto o Arco eléctrico mayores de 1000V e inferior a 57KV</td>
                    <td>Paro cardiaco, paro respiratorio, fibrilación ventricular, quemaduras severas, muerte</td>
                </tr>
                <tr>
                    <td>Eléctrico</td>
                    <td>Energía Eléctrica Alta Tensión (AT)</td>
                    <td>Riesgos de contacto o Arco eléctrico mayores o iguales a 57.5 KV y menores o iguales a 230 KV</td>
                    <td>Paro cardiaco, paro respiratorio, fibrilación ventricular, quemaduras severas, muerte</td>
                </tr>
                <tr>
                    <td>Eléctrico</td>
                    <td>Energía Eléctrica Extra alta Tensión (EAT)</td>
                    <td>Riesgos de contacto o Arco eléctrico mayores de 230 KV</td>
                    <td>Paro cardiaco, paro respiratorio, fibrilación ventricular, quemaduras severas, muerte</td>
                </tr>
                <tr>
                    <td>Eléctrico</td>
                    <td>Energía Estática</td>
                    <td>Fenómeno asociado con la aparición de una carga eléctrica en la superficie de un cuerpo aislante o en cuerpo conductor aislado. Se genera por el contacto o fricción y la separación entre dos materiales generalmente diferentes y no necesariamente aislantes, siendo uno de ellos mal conductor de la electricidad.</td>
                    <td>Fibrilación ventricular, quemaduras, shock, golpes, heridas, contusiones</td>
                </tr>
                <tr>
                    <td>Físico</td>
                    <td>Iluminación Deficiente y/o en Exceso</td>
                    <td>Define el exceso o defecto de luz por tipo de actividad y regulado en Colombia por el RETILAP</td>
                    <td>Fatiga visual, cefalea, disminución de la destreza y precisión, deslumbramiento</td>
                </tr>
                <tr>
                    <td>Físico</td>
                    <td>Radiaciones Ionizantes (rayos X, alfa, beta y gama)</td>
                    <td>Radiaciones electromagnéticas o corpusculares capaces de producir iones emitidas por los aceleradores de partículas, las substancias radiactivas (alfa y beta), los rayos gamma, rayos X, así como los neutrinos. Entre más alto sea este nivel de energía, mayor probabilidad tendrá de penetrar en los tejidos y ocasionar daños para la salud, agudos o crónicos.</td>
                    <td>Alteraciones en tejidos blandos, quemaduras, cáncer, malformaciones congénitas y alteración de células madres</td>
                </tr>
                <tr>
                    <td>Físico</td>
                    <td>Radiaciones no ionizantes (microondas, infrarroja, radiofrecuencias, etc.)</td>
                    <td>Radiación de microondas, infrarroja, radiofrecuencias, de luz visible, ultravioleta y láser</td>
                    <td>Alteraciones de la piel, deshidratación, alteración en algunos tejidos blandos (ojos)</td>
                </tr>
                <tr>
                    <td>Físico</td>
                    <td>Ruido</td>
                    <td>Presencia de ruido mayor o igual a 80 db(A) definido por los límites permisibles dependiendo de la exposición al riesgo en tiempo y al tipo de ruido</td>
                    <td>Pérdida auditiva inducida por ruido</td>
                </tr>
                <tr>
                    <td>Físico</td>
                    <td>Temperaturas extremas por calor</td>
                    <td>Exposición a altos niveles de calor radiante o dirigido considerados altos si la temperatura profunda del cuerpo se incrementa a más de 42 grados centígrados, es decir, se aumenta más o menos en 5 grados. Principalmente producidas por generación de calor por hornos, equipos y ambiente externo</td>
                    <td>Fatiga que puede producir disminución de la vigilancia, la destreza manual y la rapidez, mareos, desmayos por deshidratación, agravamiento de trastornos cardiovasculares</td>
                </tr>
                <tr>
                    <td>Físico</td>
                    <td>Temperaturas extremas por frío</td>
                    <td>Pérdida de calor corporal por exposición a ambientes por debajo de los límites, generadores de estrés por frío, generalmente presentes en refrigeradores, congeladores y ambiente externo</td>
                    <td>Fatiga, problemas cardiovasculares, alteraciones vasculares y nerviosas</td>
                </tr>
                <tr>
                    <td>Físico</td>
                    <td>Disconfort térmico por calor</td>
                    <td>Exposición a condiciones termohigrométricas (Temperatura ambiental, Humedad relativa y Velocidad del aire). Generado por situaciones naturales o antrópicas</td>
                    <td>Fatiga, estrés</td>
                </tr>
                <tr>
                    <td>Físico</td>
                    <td>Disconfort térmico por frío</td>
                    <td>Exposición a condiciones termohigrométricas (Temperatura ambiental, Humedad relativa y Velocidad del aire). Generado por situaciones naturales o antrópicas</td>
                    <td>Fatiga, estrés</td>
                </tr>
                <tr>
                    <td>Físico</td>
                    <td>Vibraciones</td>
                    <td>Clasificadas por Alta frecuencia (20–1000 Hz): Herramientas manuales rotativas o alternativas, eléctricas y neumáticas, o percutoras. Baja frecuencia (1-20 Hz): puente-grúas, trenes, tractores y maquinaria agrícola, muy baja frecuencia (menos de 1 Hz): aviones, trenes, barcos, automóviles</td>
                    <td>Trastornos articulares, daños vasculares (venosos y arteriales), alteración del sistema nervioso central, pérdida de la capacidad auditiva, dolor de espalda, debilitación de la capacidad de agarre, disminución de la sensación y habilidad de las manos, blanqueo de los dedos o "dedos blancos", síndrome del túnel carpiano, trastornos de visión por resonancia, síndrome de Raynaud</td>
                </tr>
                <tr>
                    <td>Físico</td>
                    <td>Presiones barométricas altas</td>
                    <td>Cuando se desciende del nivel del mar la presión aumenta y se produce una expansión o concentración de los espacios huecos del cuerpo, lo cual puede producirse durante la descompresión en el descenso o la compresión en el descenso</td>
                    <td>Barotrauma, embolia cerebral y síndrome de descompresión, muerte</td>
                </tr>
                <tr>
                    <td>Físico</td>
                    <td>Presiones barométricas bajas</td>
                    <td>Cuando se asciende sobre el nivel del mar la presión barométrica baja en mm de Hg. Esta disminución es la causa básica de todos los problemas de falta de oxígeno en las grandes alturas, pues cada vez que baja la presión lo hace proporcionalmente al oxígeno</td>
                    <td>Fatiga, edema pulmonar, edema cerebral, muerte</td>
                </tr>
                <tr>
                    <td>Físico - Químico</td>
                    <td>Materiales y sustancias combustibles</td>
                    <td>Sólidos o sustancias susceptibles a combinarse con el oxígeno de forma rápida y exotérmica.</td>
                    <td>Quemaduras, amputaciones, alteraciones de órganos y sentidos, muerte</td>
                </tr>
                <tr>
                    <td>Físico - Químico</td>
                    <td>Sustancias inflamables</td>
                    <td>
                        Sustancias con bajo punto de ignición menor de 18°C (las de mayor riesgo), sustancias con punto de ignición intermedio (18-21°C), y sustancias con un elevado punto de ignición (23-61°C).
                    </td>
                    <td>Quemaduras, amputaciones, alteraciones de órganos y sentidos, muerte</td>
                </tr>
                <tr>
                    <td>Físico - Químico</td>
                    <td>Materiales y sustancias explosivas</td>
                    <td>Son sustancias sólidas o líquidas, o mezclas de ellas, que por sí mismas son capaces de reaccionar químicamente produciendo gases a tales temperaturas, presiones y velocidades que pueden ocasionar daños graves en los alrededores.</td>
                    <td>Quemaduras, amputaciones, alteraciones de órganos y sentidos, muerte</td>
                </tr>
                <tr>
                    <td>Locativo</td>
                    <td>Pisos</td>
                    <td>Tiene que ver con las instalaciones físicas de la edificación o entorno que generan riesgos en las personas, tanto en la construcción, demolición o en su condición actual. Tiene alcance a la estructura, mampostería, acabados o entorno natural.</td>
                    <td>Golpes, heridas, contusiones, fracturas, esguinces, luxaciones, muerte</td>
                </tr>
                <tr>
                    <td>Locativo</td>
                    <td>Plataformas</td>
                    <td>Tiene que ver con las instalaciones físicas de la edificación o entorno que generan riesgos en las personas, tanto en la construcción, demolición o en su condición actual. Tiene alcance a la estructura, mampostería, acabados o entorno natural.</td>
                    <td>Golpes, heridas, contusiones, fracturas, esguinces, luxaciones, muerte</td>
                </tr>
                <tr>
                    <td>Locativo</td>
                    <td>Techos</td>
                    <td>Tiene que ver con las instalaciones físicas de la edificación o entorno que generan riesgos en las personas, tanto en la construcción, demolición o en su condición actual. Tiene alcance a la estructura, mampostería, acabados o entorno natural.</td>
                    <td>Golpes, heridas, contusiones, fracturas, esguinces, luxaciones, muerte</td>
                </tr>
                <tr>
                    <td>Locativo</td>
                    <td>Paredes, muros, divisiones</td>
                    <td>Tiene que ver con las instalaciones físicas de la edificación o entorno que generan riesgos en las personas, tanto en la construcción, demolición o en su condición actual. Tiene alcance a la estructura, mampostería, acabados o entorno natural.</td>
                    <td>Golpes, heridas, contusiones, fracturas, esguinces, luxaciones, muerte</td>
                </tr>
                <tr>
                    <td>Locativo</td>
                    <td>Estructura (vigas, columnas, etc.)</td>
                    <td>Tiene que ver con las instalaciones físicas de la edificación o entorno que generan riesgos en las personas, tanto en la construcción, demolición o en su condición actual.</td>
                    <td>Golpes, heridas, contusiones, fracturas, esguinces, luxaciones, muerte</td>
                </tr>
                <tr>
                    <td>Locativo</td>
                    <td>Cielorrasos, cielos falsos</td>
                    <td>Tiene que ver con las instalaciones físicas de la edificación o entorno que generan riesgos en las personas, tanto en la construcción, demolición o en su condición actual.</td>
                    <td>Golpes, heridas, contusiones, fracturas, esguinces, luxaciones, muerte</td>
                </tr>
                <tr>
                    <td>Locativo</td>
                    <td>Ventanas, claraboyas</td>
                    <td>Tiene que ver con las instalaciones físicas de la edificación o entorno que generan riesgos en las personas, tanto en la construcción, demolición o en su condición actual.</td>
                    <td>Golpes, heridas, contusiones, fracturas, esguinces, luxaciones, muerte</td>
                </tr>
                <tr>
                    <td>Locativo</td>
                    <td>Puertas</td>
                    <td>Tiene que ver con las instalaciones físicas de la edificación o entorno que generan riesgos en las personas, tanto en la construcción, demolición o en su condición actual.</td>
                    <td>Golpes, heridas, contusiones, fracturas, esguinces, luxaciones, muerte</td>
                </tr>
                <tr>
                    <td>Locativo</td>
                    <td>Rampas</td>
                    <td>Tiene que ver con las instalaciones físicas de la edificación o entorno que generan riesgos en las personas, tanto en la construcción, demolición o en su condición actual.</td>
                    <td>Golpes, heridas, contusiones, fracturas, esguinces, luxaciones, muerte</td>
                </tr>
                <tr>
                    <td>Locativo</td>
                    <td>Escalas, escaleras</td>
                    <td>Tiene que ver con las instalaciones físicas de la edificación o entorno que generan riesgos en las personas, tanto en la construcción, demolición o en su condición actual.</td>
                    <td>Golpes, heridas, contusiones, fracturas, esguinces, luxaciones, muerte</td>
                </tr>
                <tr>
                    <td>Locativo</td>
                    <td>Pasamanos, barandas</td>
                    <td>Tiene que ver con las instalaciones físicas de la edificación o entorno que generan riesgos en las personas, tanto en la construcción, demolición o en su condición actual.</td>
                    <td>Golpes, heridas, contusiones, fracturas, esguinces, luxaciones, muerte</td>
                </tr>
                <tr>
                    <td>Locativo</td>
                    <td>Túneles</td>
                    <td>Tiene que ver con las instalaciones físicas de la edificación o entorno que generan riesgos en las personas, tanto en la construcción, demolición o en su condición actual.</td>
                    <td>Golpes, heridas, contusiones, fracturas, esguinces, luxaciones, muerte</td>
                </tr>
                <tr>
                    <td>Locativo</td>
                    <td>Vías, caminos, senderos</td>
                    <td>Tiene que ver con las instalaciones físicas de la edificación o entorno que generan riesgos en las personas, tanto en la construcción, demolición o en su condición actual.</td>
                    <td>Golpes, heridas, contusiones, fracturas, esguinces, luxaciones, muerte</td>
                </tr>
                <tr>
                    <td>Locativo</td>
                    <td>Almacenamiento</td>
                    <td>Se refiere a las condiciones generales de seguridad en el almacenamiento y bodegaje, instalaciones y disposición de estanterías y acceso.</td>
                    <td>Golpes, heridas, contusiones, fracturas, esguinces, luxaciones, muerte</td>
                </tr>
                <tr>
                    <td>Locativo</td>
                    <td>Orden y aseo</td>
                    <td>Se refiere a las deficiencias de las condiciones generales o específicas del orden y el aseo de lugares, áreas o procesos.</td>
                    <td>Golpes, heridas, contusiones, fracturas, esguinces, luxaciones, muerte</td>
                </tr>
                <tr>
                    <td>Mecánico</td>
                    <td>Izaje y cargas suspendidas</td>
                    <td>Trabajos con grúas, plumas, puente grúa, torre grúa diferenciales, polipastos y demás, y que genere riesgo de caída de los objetos suspendidos en el aire o del equipo mismo.</td>
                    <td>Golpes, contusiones, heridas, fracturas, muerte</td>
                </tr>
                <tr>
                    <td>Mecánico</td>
                    <td>Superficies calientes</td>
                    <td>Manipulación o contacto con equipos, herramientas, instalaciones y producto luego de adquirir por medios externos o internos temperaturas elevadas.</td>
                    <td>Quemaduras, laceraciones</td>
                </tr>
                <tr>
                    <td>Mecánico</td>
                    <td>Partes en movimiento, sistemas de transmisión y puntos de operación</td>
                    <td>Riesgo generado por el potencial de atrapamiento de segmentos corporales por medio de sistemas en movimiento.</td>
                    <td>Heridas, amputaciones, trastornos de tejidos blandos, golpes, fracturas, muerte</td>
                </tr>
                <tr>
                    <td>Mecánico</td>
                    <td>Proyección de partículas</td>
                    <td>Exposición a partículas de polvo, proyección de objetos, partículas y fluidos.</td>
                    <td>Golpes, fracturas, heridas</td>
                </tr>
                <tr>
                    <td>Mecánico</td>
                    <td>Objetos que caen, ruedan, se deslizan, se movilizan</td>
                    <td>Contacto con objetos que: caen, se derrumban, deslizan, se transportan, etc.</td>
                    <td>Heridas, amputaciones, laceraciones, muerte</td>
                </tr>
                <tr>
                    <td>Mecánico</td>
                    <td>Superficies o herramientas cortantes</td>
                    <td>Contacto con superficies o herramientas cortantes.</td>
                    <td>Heridas, amputaciones, laceraciones</td>
                </tr>
                <tr>
                    <td>Mecánico</td>
                    <td>Manejo de equipos, máquinas y herramientas manuales</td>
                    <td>Contacto con equipos, máquinas y herramientas manuales.</td>
                    <td>Heridas, amputaciones, laceraciones</td>
                </tr>
                <tr>
                    <td>Psicosocial</td>
                    <td>Factores intralaborales, Factores extralaborales, Factores individuales</td>
                    <td>Para Identificar el Riesgo Psicosocial, evaluar con la "Guía de autoanálisis" que EXIGE LA LEGISLACIÓN COLOMBIANA ACTUAL.</td>
                    <td>Fatiga, estrés, disminución de la destreza y precisión. Estados de ansiedad y/o depresión y trastornos del aparato digestivo.</td>
                </tr>
                <tr>
                    <td>Públicos</td>
                    <td>Situación de atraco, robo u otras situaciones de violencia</td>
                    <td>Situaciones que atentan contra la seguridad física de las personas por violencia generada desde terceros para efectos de robo, estafa, secuestro, etc.</td>
                    <td>Fatiga, estrés, disminución de la destreza y precisión. Estados de ansiedad y/o depresión y trastornos del aparato digestivo.</td>
                </tr>
                <tr>
                    <td>Públicos</td>
                    <td>Movilización peatonal</td>
                    <td>Incluye los riesgos generados por contacto en condición de peatón con vehículos de transporte de personas o mercancías.</td>
                    <td>Muerte, fracturas, contusiones, laceraciones.</td>
                </tr>
                <tr>
                    <td>Públicos</td>
                    <td>Transporte de personas</td>
                    <td>Incluye los riesgos generados por la operación de vehículos destinados para el transporte de personas, donde se hace fundamental programas de mantenimiento preventivo y correctivo y formación y entrenamiento en conducción segura y manejo defensivo.</td>
                    <td>Muerte, fracturas, contusiones, laceraciones.</td>
                </tr>
                <tr>
                    <td>Públicos</td>
                    <td>Transporte de mercancías</td>
                    <td>Incluye los riesgos generados por la operación de vehículos de carga en su magnitud, donde se hace fundamental programas de mantenimiento preventivo y correctivo y formación y entrenamiento en conducción segura y manejo defensivo.</td>
                    <td>Muerte, fracturas, contusiones, laceraciones.</td>
                </tr>
                <tr>
                    <td>Químicos</td>
                    <td>Líquidos (nieblas y rocíos)</td>
                    <td>Principales fuentes generadoras: Ebullición, Limpieza con Vapor de agua, Pinturas, solventes, etc.</td>
                    <td>Quemaduras, Trastornos inespecíficos del sistema nervioso, daño auditivo, daño respiratorio, daño hepático, daño renal, daño dermatológico, cáncer y muerte.</td>
                </tr>
                <tr>
                    <td>Químicos</td>
                    <td>Sólidos (polvos orgánicos, polvos inorgánicos, fibras, humos metálicos y no metálicos)</td>
                    <td>Principales fuentes generadoras: Minería, Cerámica, Cemento, Madera, Harinas, Soldadura</td>
                    <td>Neumoconiosis, bisinosis, neumonitis, asma profesional, EPOC, cáncer y muerte.</td>
                </tr>
                <tr>
                    <td>Químicos</td>
                    <td>Gases y Vapores</td>
                    <td>Principales fuentes generadoras: Monóxido de carbono, Dióxido de azufre, Óxidos de nitrógeno, Cloro y sus derivados, Amoníaco, Cianuros Plomo, Mercurio</td>
                    <td>Cefaleas, temblores, falta de coordinación, náuseas, vómitos, somnolencia, acufenos, parálisis, edema cutáneo, neuritis periférica, déficit cognitivos, alteraciones psiquiátricas, diabetes, hipertiroidismo, edema pulmonar, queratitis, dificultad respiratoria, irritación de vías respiratorias, ojos, piel y tracto gastrointestinal, quemaduras, anemia, hipertensión arterial, daño renal, disminución de la fertilidad, disminución de la libido, depresión, teratogenicidad, trastornos del sueño, trastornos de la memoria, convulsiones, coma, paro respiratorio y muerte.</td>
                </tr>
                <tr>
                    <td>Tareas de alto riesgo</td>
                    <td>Trabajo en alturas por encima de 1.50 metros. Sin sistemas de protección intrínseca</td>
                    <td>Tarea que, por su potencial alto de pérdida en vidas humanas por caída de altura, necesita la implementación de un sistema de permisos y listas de verificación y el diseño y puesta en marcha de un estándar de seguridad específico.</td>
                    <td>Politraumatismos y muerte.</td>
                </tr>
                <tr>
                    <td>Tareas de alto riesgo</td>
                    <td>Trabajo en espacios confinados</td>
                    <td>Tarea que por su potencial alto de pérdida en vidas humanas por el ingreso a espacios con atmósferas peligrosas o sistemas de atrapamiento. Necesita la implementación de un sistema de permisos y listas de verificación y el diseño y puesta en marcha de un estándar de seguridad específico.</td>
                    <td>Asfixia, alteraciones del sistema nervioso central, paros cardiorrespiratorios, muerte.</td>
                </tr>
                <tr>
                    <td>Tareas de alto riesgo</td>
                    <td>Trabajo con energías peligrosas</td>
                    <td>Son actividades en donde se utilizan diferentes tipos de energía: hidráulica, eólica, química y térmica. Estas son casi siempre la fuente principal para los procesos, pero en dichos procesos se pueden transformar en: energía potencial, eléctrica, cinética, mecánica, neumática, calórica, luminosa, térmica, etc. El principal riesgo con la energía es que no la vemos excepto cuando se transforma o cuando hacemos parte de ella. Al liberarse esa energía y de forma no controlada, es cuando se producen los accidentes y sus consecuencias varían según la capacidad de esta en ese preciso momento.</td>
                    <td>Golpes, heridas, laceraciones, amputaciones, asfixia, intoxicación, electrocución, politraumatismos, muerte.</td>
                </tr>
                <tr>
                    <td>Tareas de alto riesgo</td>
                    <td>Trabajo en Excavaciones o brechas</td>
                    <td>Actividades de las cuales se extrae tierra u otros materiales estratificados en el suelo mediante cualquier sistema, pueden desarrollarse con maquinaria pesada o a mano con herramientas livianas.</td>
                    <td>Golpes, heridas, laceraciones, asfixia, intoxicación, electrocución, politraumatismos, muerte.</td>
                </tr>
                <tr>
                    <td>Tareas de alto riesgo</td>
                    <td>Trabajos en caliente, corte y soldadura</td>
                    <td>Actividades que generan chispa o esquirla y que a su vez son potenciales de generación de incendios y/o explosiones. Necesita la implementación de un sistema de permisos y listas de verificación y el diseño y puesta en marcha de un estándar de seguridad específico.</td>
                    <td>Quemaduras, intoxicaciones, muerte.</td>
                </tr>
                <tr>
                    <td>Procesos peligrosos</td>
                    <td>Recipientes y sistemas a presión</td>
                    <td>Riesgo generado por aquellos dispositivos cargados con presión y/o alimentados por combustibles varios y que genera riesgo de explosión.</td>
                    <td>Politraumatismos y muerte.</td>
                </tr>
                <tr>
                    <td>Procesos peligrosos</td>
                    <td>Actividades en agua</td>
                    <td>Se refiere a aquellas tareas realizadas en ríos, quebradas, lagunas, mares, sobre la superficie o bajo el agua. Actividades submarinas que hace necesario la utilización de equipos especializados respiratorios para su ejecución.</td>
                    <td>Golpes, heridas, laceraciones, amputaciones, asfixia, barotrauma, embolia cerebral, síndrome de descompresión, muerte.</td>
                </tr>
                <tr>
                    <td>Deportes y otras actividades</td>
                    <td>Actividades deportivas</td>
                    <td>Actividades deportivas y lúdicas en general que se realizan con autorización y en representación del empleador.</td>
                    <td>Contusiones, laceraciones, luxaciones, fracturas.</td>
                </tr>
                <tr>
                    <td>Salud Pública</td>
                    <td>Enfermedades transmitidas por el agua y los alimentos</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>Salud Pública</td>
                    <td>Enfermedades inmunoprevenibles</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>Naturales</td>
                    <td>Derrumbe - Deslizamientos</td>
                    <td>Movimiento de masa de tierra, provocado por la inestabilidad de un talud, una gran masa de terreno se convierte en zona inestable y desliza con respecto a una zona estable.</td>
                    <td>Quemaduras, golpes, heridas, laceraciones, amputaciones, asfixia, intoxicación, politraumatismos, muerte</td>
                </tr>
                <tr>
                    <td>Naturales</td>
                    <td>Inundación - desbordamiento de ríos</td>
                    <td>Ocupación por parte del agua de zonas que habitualmente están libres de esta, bien por desbordamiento de ríos, lagunas, embalses generalmente producido por lluvias torrenciales.</td>
                    <td>Quemaduras, golpes, heridas, laceraciones, amputaciones, asfixia, intoxicación, politraumatismos, muerte</td>
                </tr>
                <tr>
                    <td>Naturales</td>
                    <td>Sismo - Terremoto</td>
                    <td>Fenómeno de movimiento brusco y temporal de la corteza terrestre producido por la liberación de energía acumulada en forma de ondas sísmicas. Los más comunes se producen por la ruptura de fallas geológicas. También pueden ocurrir por otras causas como fricción en el borde de placas tectónicas, procesos volcánicos.</td>
                    <td>Quemaduras, golpes, heridas, laceraciones, amputaciones, asfixia, intoxicación, politraumatismos, muerte</td>
                </tr>
                <tr>
                    <td>Naturales</td>
                    <td>Precipitaciones - Tormentas</td>
                    <td>Fenómeno caracterizado por la coexistencia próxima de dos o más masas de aire de diferentes temperaturas que provocan inestabilidad caracterizada por lluvias, vientos, relámpagos, truenos y ocasionalmente granizos entre otros fenómenos meteorológicos.</td>
                    <td>Golpes, heridas, laceraciones, amputaciones, asfixia, intoxicación, electrocución, politraumatismos, muerte</td>
                </tr>
                <tr>
                    <td>Naturales</td>
                    <td>Huracanes - Vendaval</td>
                    <td>Término meteorológico usado para referirse a un sistema de tormentas caracterizado por una circulación cerrada alrededor de un centro de baja presión y que produce fuertes vientos y abundante lluvia. Cuando solo está asociado al aumento de la velocidad del viento se denomina vendaval.</td>
                    <td>Golpes, heridas, laceraciones, amputaciones, asfixia, intoxicación, electrocución, politraumatismos, muerte</td>
                </tr>
                <tr>
                    <td>Naturales</td>
                    <td>Tsunami - Maremoto</td>
                    <td>Maremoto es un evento que involucra un grupo de olas de gran energía y de tamaño variable que se producen cuando algún fenómeno extraordinario desplaza verticalmente una gran masa de agua. Este tipo de olas remueven una cantidad de agua muy superior a las olas superficiales producidas por el viento. Se calcula que la mayoría de estos fenómenos son provocados por terremotos.</td>
                    <td>Golpes, heridas, laceraciones, amputaciones, asfixia, intoxicación, electrocución, politraumatismos, muerte</td>
                </tr>
                <tr>
                    <td>Naturales</td>
                    <td>Incendio Forestal</td>
                    <td>Incendios de zonas boscosas de origen natural o antrópico.</td>
                    <td>Quemaduras, golpes, heridas, laceraciones, amputaciones, asfixia, intoxicación, politraumatismos, muerte</td>
                </tr>
                <tr>
                    <td>Naturales</td>
                    <td>Erupción volcánica</td>
                    <td>Ascenso de magma (roca fundida) en forma de lava, ceniza volcánica y gases del interior del planeta. Ocurre generalmente en episodios de actividad violenta (erupciones) las cuales pueden variar en intensidad, duración y frecuencia; siendo desde conductos de corrientes de lava hasta explosiones extremadamente destructivas.</td>
                    <td>Quemaduras, golpes, heridas, laceraciones, amputaciones, asfixia, intoxicación, politraumatismos, muerte</td>
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
<!-- 
    <div>
        <a href="<?= base_url('/generatePdf_indentPeligros') ?>" target="_blank">
            <button type="button">PDF</button>
        </a>
    </div> -->

</body>

</html>