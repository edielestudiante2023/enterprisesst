<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>1.1.13 Conformación y Funcionamiento del comité de Convivencia Laboral</title>
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


    <div class="beta-subtitulo">1. OBJETIVO DEL COMITÉ</div>
    <p class="gamma-parrafo">
        Velar por el cumplimiento de la Ley 1010 del 2006 (Prevenir acoso laboral es prevenir ultraje a la dignidad humana en el contexto de una relación laboral privada o pública) y la Resolución 652 del 2012 (Definir la conformación del comité de convivencia laboral, así como establecer la responsabilidad que le asiste a los empleadores y a las administradoras de riesgo laborales frente al desarrollo de las medidas preventivas y correctivas del acoso laboral).
    </p>

    <div class="beta-subtitulo">2. ALCANCE</div>
    <p class="gamma-parrafo">
        Fomentar entre todos los colaboradores de la Organización un apropiado ambiente de convivencia laboral, garantizando equidad y cumplimiento del correcto procedimiento.
    </p>

    <div class="beta-subtitulo">3. REFERENCIAS NORMATIVAS</div>
    <ul class="delta-lista">
        <li class="gamma-parrafo"><strong>Ley 1010 de 2006:</strong> Por medio de la cual se adoptan medidas para prevenir, corregir y sancionar el acoso laboral y otros hostigamientos en el marco de las relaciones de trabajo.</li>
        <li class="gamma-parrafo"><strong>Resolución 2646 de 2008:</strong> Por la cual se establecen disposiciones y se definen responsabilidades para la identificación, evaluación, prevención, intervención y monitoreo permanente de la exposición a factor de riesgo psicosocial en el trabajo y para la determinación del origen de las patologías causadas por el estrés laboral.</li>
        <li class="gamma-parrafo"><strong>Resolución 652 de 2012:</strong> Por la cual se establece la conformación y funcionamiento del Comité de Convivencia Laboral en entidades públicas y empresas privadas y se dictan otras disposiciones.</li>
        <li class="gamma-parrafo"><strong>Resolución 1356 de 2012:</strong> Ministerio del Trabajo que modifica parcialmente la Resolución 652 de 2012.</li>
    </ul>

    <div class="beta-subtitulo">4. DESIGNACIÓN</div>
    <p class="gamma-parrafo">
        Este Comité estará integrado en forma bipartita por dos (2) representantes de los trabajadores y dos (2) representantes del empleador con sus respectivos suplentes. Los cuáles serán designados de la siguiente forma:
    </p>
    <ul class="delta-lista">
        <li class="gamma-parrafo"><strong>Representantes de los trabajadores:</strong> Por votación abierta, mediante elección de los trabajadores de la empresa.</li>
        <li class="gamma-parrafo"><strong>Representante del empleador:</strong> Designados por la Gerencia General.</li>
    </ul>

    <div class="beta-subtitulo">5. PERIODO</div>
    <p class="gamma-parrafo">
        El período del Comité será de 2 años, contados a partir de la fecha de su instalación. Los representantes de los trabajadores pueden ser reelegidos por una sola vez de manera consecutiva.
    </p>

    <div class="beta-subtitulo">6. INHABILIDADES</div>
    <ul class="delta-lista">
        <li class="gamma-parrafo">Siempre que un miembro del Comité considere que existe algún factor que impide su actuación con la autonomía e imparcialidad necesarias, deberá manifestarlo a los demás miembros del Comité, para que estos se pronuncien al respecto.</li>
        <li class="gamma-parrafo">En caso de que la inhabilidad sea aceptada por el resto de los miembros del Comité, se designará un suplente.</li>
        <li class="gamma-parrafo">Si la persona que presenta la queja o cualquiera de los miembros del Comité manifiesta recusa, con razones válidas a juicio de los demás miembros de este, motivos que afecten la autonomía o la imparcialidad de alguno de sus miembros para decidir en un caso concreto de actuación del Comité, se procederá de la misma forma que en el punto anterior.</li>
    </ul>

    <div class="beta-subtitulo">7. RETIRO</div>
    <p class="gamma-parrafo">
        Son causales de retiro de los miembros del Comité de Convivencia Laboral, las siguientes:
    </p>
    <ul class="delta-lista">
        <li class="gamma-parrafo">La terminación del contrato de trabajo.</li>
        <li class="gamma-parrafo">Haber violado el deber de confidencialidad como miembro del Comité.</li>
        <li class="gamma-parrafo">Faltar a más de tres (3) reuniones consecutivas.</li>
        <li class="gamma-parrafo">Incumplir en forma reiterada las otras obligaciones que le corresponden como miembro del Comité.</li>
        <li class="gamma-parrafo">La renuncia presentada por el miembro del Comité.</li>
    </ul>
    <p class="gamma-parrafo"><strong>Parágrafo:</strong> La decisión de retiro en los casos 2, 3, 4 y 5 debe ser adoptada por el resto de miembros del Comité, e informada a quienes lo eligieron.</p>

    <div class="beta-subtitulo">8. RESPONSABILIDADES DE LOS PARTICIPANTES DEL COMITÉ</div>
    <ul class="delta-lista">
        <li class="gamma-parrafo">Recibir y dar trámite a la queja en 30 días.</li>
        <li class="gamma-parrafo">Examinar de manera confidencial los casos específicos o puntuales en los que se formule queja o reclamo.</li>
        <li class="gamma-parrafo">Escuchar a las partes involucradas de forma individual.</li>
        <li class="gamma-parrafo">Adelantar reuniones de diálogo con las partes involucradas.</li>
        <li class="gamma-parrafo">Formular planes de mejora concertado entre las partes.</li>
        <li class="gamma-parrafo">Realizar seguimiento de los compromisos adquiridos por las partes involucradas.</li>
        <li class="gamma-parrafo">Informar a Gerencia General en caso de que no se lleguen a ningún acuerdo.</li>
        <li class="gamma-parrafo">Apoyar en la elaboración del informe trimestral sobre la gestión del comité que incluya estadísticas de las quejas y presentar a la alta dirección.</li>
        <li class="gamma-parrafo">Realizar Seguimiento a los planes de acción establecidos.</li>
        <li class="gamma-parrafo">Realizar informe anual.</li>
    </ul>

    <div class="beta-subtitulo">9. RESPONSABILIDADES PRESIDENTE DEL COMITÉ</div>
    <ul class="delta-lista">
        <li class="gamma-parrafo">Convocar a los miembros del comité a las sesiones ordinarias y extraordinarias.</li>
        <li class="gamma-parrafo">Presidir y orientar las reuniones en forma dinámica y eficaz.</li>
        <li class="gamma-parrafo">Tramitar ante la administración de la Organización, las recomendaciones aprobadas en el comité.</li>
        <li class="gamma-parrafo">Gestionar ante la alta dirección de la Organización, los recursos requeridos para el funcionamiento del comité.</li>
    </ul>

    <div class="beta-subtitulo">10. RESPONSABILIDADES DEL SECRETARIO DEL COMITÉ</div>
    <ul class="delta-lista">
        <li class="gamma-parrafo">Recibir las quejas de acoso laboral por escrito, así como las pruebas que la soportan.</li>
        <li class="gamma-parrafo">Enviar por medio físico o electrónico, la convocatoria realizada por el presidente a los miembros del comité.</li>
        <li class="gamma-parrafo">Citar individualmente a cada una de las partes involucradas en las quejas, con el fin de escuchar los hechos que dieron lugar a las mismas.</li>
        <li class="gamma-parrafo">Citar conjuntamente a los colaboradores involucrados en las quejas con el fin de establecer compromisos de convivencia.</li>
        <li class="gamma-parrafo">Llevar el archivo de las quejas presentadas, la documentación soporte y velar por la reserva, custodia y confidencialidad de la información.</li>
        <li class="gamma-parrafo">Elaborar el orden del día y las actas de cada una de las sesiones del Comité.</li>
        <li class="gamma-parrafo">Enviar las comunicaciones con las recomendaciones dadas a las diferentes áreas de la Organización.</li>
        <li class="gamma-parrafo">Citar a reuniones y solicitar los soportes requeridos para hacer seguimiento al cumplimiento de los compromisos adquiridos por cada una de las partes involucradas.</li>
        <li class="gamma-parrafo">Elaborar informes trimestrales sobre la gestión del comité que incluya estadísticas de las quejas, seguimiento de los casos y recomendaciones, los cuales serán presentados a la alta dirección de la organización.</li>
    </ul>
    <p class="gamma-parrafo"><strong>Nota:</strong> En caso de que algún participante no pueda asistir debe enviar un delegado o enviar respuesta u opinión del tema a tratar.</p>

    <div class="beta-subtitulo">11. FUNCIONAMIENTO DEL COMITÉ DE CONVIVENCIA LABORAL</div>
    <div class="beta-subtitulo">11.1 Instalación</div>
    <p class="gamma-parrafo">Una vez elegidos los miembros del Comité, se procederá a la instalación formal del Comité y de dicha instalación se dejará constancia en un acta. De igual manera, en este acto se desarrollarán los siguientes puntos:</p>
    <ul class="delta-lista">
        <li class="gamma-parrafo">Nombramiento del coordinador del Comité (presidente).</li>
        <li class="gamma-parrafo">Firma de acta de compromisos de confidencialidad.</li>
        <li class="gamma-parrafo">Definición de fechas de reuniones según la periodicidad estipulada por el comité (trimestral).</li>
        <li class="gamma-parrafo">Los demás temas que los miembros quieran tratar.</li>
    </ul>
    <p class="gamma-parrafo"><strong>Parágrafo:</strong> De la instalación del comité debe informarse a toda la comunidad laboral.</p>

    <div class="beta-subtitulo">12. SESIONES DEL COMITÉ</div>
    <p class="gamma-parrafo">Clasificación de las reuniones:</p>
    <ul class="delta-lista">
        <li class="gamma-parrafo"><strong>Reuniones ordinarias:</strong> Las celebradas mediante convocatoria previa.</li>
        <li class="gamma-parrafo"><strong>Reuniones extraordinarias:</strong> Cuando los miembros del Comité consideren que deben reunirse en fechas adicionales a las ordinarias. Estas podrán ser convocadas por el presidente del Comité o por cualquiera de los integrantes.</li>
    </ul>
    <p class="gamma-parrafo">En la medida que se presente una queja de acoso laboral, el comité deberá programar una reunión extraordinaria y no esperar a la siguiente reunión ordinaria, a menos que la misma esté próxima a realizarse.</p>

    <div class="beta-subtitulo">De la validez de las reuniones</div>
    <p class="gamma-parrafo">El Comité solo podrá sesionar con la asistencia de por lo menos el 50% + 1 de los miembros principales o suplentes, y extraordinariamente cuando se presenten casos que requieran de su inmediata intervención.</p>

    <div class="beta-subtitulo">De las actas</div>
    <p class="gamma-parrafo">De cada reunión se elevará un acta en la cual se señalará la naturaleza de la reunión, la fecha y lugar de reunión y los asuntos tratados, refrendada con la firma del presidente y el secretario.</p>
    <p class="gamma-parrafo">Para la conservación de las actas se implementará un archivo confidencial especial que será de libre consulta para los miembros del Comité. Este archivo se encontrará bajo la custodia del secretario del comité.</p>
    <p class="gamma-parrafo">La información contenida en este archivo es de propiedad del Comité de Convivencia Laboral y no de sus miembros, quienes simplemente son custodios de este en cumplimiento de su deber de confidencialidad.</p>
    <p class="gamma-parrafo">Cuando se actualiza el comité de convivencia laboral, debe entregar su labor con todos los casos cerrados, y la documentación o expedientes deben ser entregados al Analista de Seguridad y Salud en el Trabajo, quien se encarga de salvaguardar esta información por 20 años.</p>

    <div class="beta-subtitulo">Decisiones del comité</div>
    <p class="gamma-parrafo">El Comité adoptará en principio decisiones por la vía del consenso; sin embargo, en caso de no llegarse a él, las decisiones se tomarán a través del sistema de mayoría simple. En caso de existir empate, se llamará a un miembro suplente del Comité, de conformidad con el orden de votación, para que con su voto se dirima el empate.</p>
    <p class="gamma-parrafo"><strong>Parágrafo:</strong> Para estos efectos, el consenso es un acuerdo producido por el consentimiento de todos los miembros.</p>

    <div class="beta-subtitulo">13. PROCEDIMIENTO PARA MANEJAR CASOS POR CONVIVENCIA Y PRESUNTO ACOSO LABORAL</div>

    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>No.</th>
                <th>Actividad</th>
                <th>Responsable</th>
                <th>Producto</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td></td>
                <td>Radicar la queja por convivencia o Acoso laboral, mediante comunicación escrita y remitir a través del correo electrónico o medio físico (carta-formato)</td>
                <td>Sujeto pasivo (víctima del Acoso Laboral)</td>
                <td>
                    <ul>
                        <li>Formato para presentar queja de convivencia o Acoso Laboral - Código SGSST</li>
                        <li>Correo electrónico (notificar)</li>
                        <li>Documento en físico (carta)</li>
                    </ul>
                </td>
            </tr>
            <tr>
                <td>1</td>
                <td>Examinar e identificar de manera confidencial los casos o quejas para programar sesión del Comité de Convivencia Laboral – Reunión extraordinaria</td>
                <td>Secretaria(o) de Convivencia Laboral</td>
                <td>
                    <ul>
                        <li>Listado de quejas</li>
                        <li>Acta de reunión extraordinaria</li>
                    </ul>
                </td>
            </tr>
            <tr>
                <td>2</td>
                <td>Programar sesión con los integrantes del Comité de Convivencia Laboral y las partes involucradas de forma individual, para escuchar la versión de los hechos.</td>
                <td>Secretaria(o) de Convivencia Laboral, Sujeto Pasivo, Sujeto Activo</td>
                <td>
                    <ul>
                        <li>Correo electrónico</li>
                        <li>Llamada telefónica</li>
                        <li>Carta de notificación</li>
                        <li>Acta de reunión individual</li>
                    </ul>
                </td>
            </tr>
            <tr>
                <td>3</td>
                <td>Crear un espacio de diálogo entre las partes involucradas – Reunión de Conciliación (empresa), con el fin de promover compromisos mutuos para llegar a una solución efectiva de las controversias.</td>
                <td>Comité de Convivencia Laboral</td>
                <td>
                    <ul>
                        <li>Acta de conciliación</li>
                    </ul>
                </td>
            </tr>
            <tr>
                <td>4</td>
                <td>Formular un plan de mejora concertado entre las partes, para construir, renovar y promover la convivencia laboral, garantizando en todos los casos el principio de la confidencialidad.</td>
                <td>Comité de Convivencia Laboral, Sujeto Pasivo, Sujeto Activo</td>
                <td>
                    <ul>
                        <li>Acta de conciliación</li>
                        <li>Acta de reunión trimestral / seguimiento de compromisos</li>
                    </ul>
                </td>
            </tr>
            <tr>
                <td>5</td>
                <td>Hacer seguimiento a los compromisos adquiridos por las partes involucradas en la queja, verificando su cumplimiento de acuerdo con lo pactado.</td>
                <td>Comité de Convivencia Laboral</td>
                <td>
                    <ul>
                        <li>Acta de conciliación</li>
                        <li>Informe trimestral</li>
                    </ul>
                </td>
            </tr>
            <tr>
                <td>6</td>
                <td>En aquellos casos en que no se llegue a un acuerdo entre las partes, no se cumplan las recomendaciones formuladas o la conducta persista, el Comité de Convivencia Laboral informará a la alta dirección de la empresa, cerrará el caso y el trabajador puede presentar la queja ante el inspector de trabajo o demandar ante el juez competente.</td>
                <td>Comité de Convivencia Laboral</td>
                <td>
                    <ul>
                        <li>Acta de conciliación</li>
                        <li>Informe trimestral</li>
                    </ul>
                </td>
            </tr>
            <tr>
                <td>7</td>
                <td>Elaborar el informe trimestral sobre la gestión del Comité que incluya estadísticas de las quejas, seguimiento de los casos y recomendaciones, los cuales serán presentados a la alta dirección. En caso de no registrar quejas en un periodo, se sugiere informar sobre la gestión preventiva y de psicoeducación en promoción de la convivencia y prevención del Acoso Laboral.</td>
                <td>Comité de Convivencia Laboral</td>
                <td>
                    <ul>
                        <li>Informe trimestral</li>
                    </ul>
                </td>
            </tr>
        </tbody>
    </table>




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
        <a href="<?= base_url('/generatePdf_prcCocolab') ?>" target="_blank">
            <button type="button">PDF</button>
        </a>
    </div> -->

</body>

</html>