<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>1.1.11 Manual de Convivencia Laboral </title>
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
        .alpha-titulo {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }

        .beta-subtitulo {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .gamma-parrafo {
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 15px;
            text-align: justify;
        }

        .delta-lista {
            margin-left: 20px;
            list-style-type: disc;
        }

        .epsilon-tabla {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .epsilon-tabla th,
        .epsilon-tabla td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        /* .zeta-resaltado {
            font-weight: bold;
            color: #2a7ae2;
        } */
    </style>


</head>

<body>
    <div class="centered-content">
        <table>
            <tr>
                <td rowspan="2" class="logo">
                    <img src="<?= base_url('uploads/' . $client['logo']) ?>" alt="Logo de <strong><?= $client['nombre_cliente'] ?></strong>" width="100%">
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


    <body>

        <div class="alpha-titulo">Manual de Convivencia Laboral</div>



        <div class="beta-subtitulo">Introducción</div>
        <p class="gamma-parrafo">
            Este manual de convivencia es una invitación a promover y cumplir con nuestras obligaciones, responsabilidades y deberes en nuestro lugar de trabajo, para mantener una buena conducta, actitud positiva, realizar nuestras actividades correctamente y con responsabilidad, para lograr un ambiente laboral armónico que contribuya a nuestro desarrollo personal y profesional.
        </p>
        <p class="gamma-parrafo">
            <strong><?= $client['nombre_cliente'] ?></strong>, se compromete a cumplir con todas las leyes, decretos, resoluciones y demás normas que sean expedidas para reglamentar el abordaje del Acoso Laboral, en materia de prevención y atención de este factor de riesgo psicosocial Intralaboral. La Ley 1010 de 2006, emitida por el Congreso de la República, tiene como objetivo prevenir, corregir y sancionar las diversas formas de agresión, maltrato, vejámenes, trato desconsiderado u ofensivo, y en general, cualquier ultraje a la dignidad humana que se ejerza sobre quienes desarrollan actividades económicas en el marco de una relación laboral, ya sea pública o privada. Esta ley establece que los reglamentos de trabajo de las empresas deben incluir mecanismos para la prevención del acoso laboral, así como un procedimiento interno, confidencial, conciliatorio y efectivo para abordar cualquier situación que se presente en el lugar de trabajo. <strong><?= $client['nombre_cliente'] ?></strong>, en cumplimiento de lo dispuesto por la Resolución 652 del 30 de abril de 2012, modificada por la Resolución 1356 de 2012, ambas emitidas por el Ministerio de Trabajo, ha conformado el Comité de Convivencia Laboral. Este comité tiene la responsabilidad de implementar acciones administrativas orientadas a mejorar el clima laboral y proponer medidas que promuevan la convivencia armónica. Además, debe enfocar sus esfuerzos en generar una conciencia colectiva sobre la importancia de mantener un ambiente laboral digno y justo, que favorezca la armonía entre los empleados y fomente un buen ambiente dentro de la empresa.
        </p>

        <div class="beta-subtitulo">1. Objetivo</div>
        <p class="gamma-parrafo">
            El presente manual tiene por objeto generar las pautas de sana convivencia en todos sus procesos con el propósito de brindar un ambiente laboral adecuado favoreciendo las relaciones interpersonales de todos nuestros colaboradores.
        </p>

        <div class="beta-subtitulo">1.1 Alcance</div>
        <p class="gamma-parrafo">Aplica para todos los trabajadores de <strong><?= $client['nombre_cliente'] ?></strong></p>

        <div class="beta-subtitulo">2. Marco Teórico</div>
        <p class="gamma-parrafo">
            <span class="zeta-resaltado">Definición y modalidades de acoso laboral.</span> El acoso laboral se define como cualquier conducta persistente y comprobable, ejercida sobre un empleado o trabajador por parte de un empleador, jefe, superior jerárquico (inmediato o mediato), compañero de trabajo o subalterno. Esta conducta tiene como finalidad generar miedo, intimidación, terror o angustia; causar perjuicio en el entorno laboral; desmotivar al empleado, o inducir su renuncia.
        </p>
        <ul class="delta-lista">
            <li class="gamma-parrafo"><strong>Maltrato laboral:</strong> Se refiere a cualquier acto de violencia que atente contra la integridad física o moral, la libertad personal o sexual, y los bienes de un empleado o trabajador. Incluye expresiones verbales injuriosas o ultrajantes que afecten la integridad moral, el derecho a la intimidad o el buen nombre de los involucrados en una relación laboral. También abarca comportamientos que buscan menoscabar la autoestima y dignidad de quienes participan en una relación de trabajo.</li>
            <li class="gamma-parrafo"><strong>Persecución laboral:</strong> Se trata de una conducta reiterada o arbitraria cuyo objetivo es inducir la renuncia del empleado o trabajador. Esto se puede lograr mediante la descalificación constante, la asignación de una carga excesiva de trabajo o la modificación continua de horarios, lo que genera desmotivación en el trabajador.</li>
            <li class="gamma-parrafo"><strong>Discriminación laboral:</strong> Consiste en un trato diferenciado e injustificado basado en razones de raza, género, edad, origen familiar o nacional, credo religioso, preferencia política o situación social, que carezca de justificación razonable en el contexto laboral.</li>
            <li class="gamma-parrafo"><strong>Entorpecimiento laboral:</strong> Se refiere a cualquier acción destinada a obstaculizar el desempeño laboral, haciéndolo más difícil o lento, en perjuicio del trabajador o empleado. Esto incluye, entre otras acciones, la privación, ocultación o inutilización de insumos, documentos o herramientas de trabajo, la destrucción o pérdida de información, y el ocultamiento de correspondencia o mensajes electrónicos.</li>
            <li class="gamma-parrafo"><strong>Inequidad laboral:</strong> Consiste en la asignación de funciones que menosprecian al trabajador o lo colocan en una posición desfavorable sin justificación.</li>
            <li class="gamma-parrafo"><strong>Desprotección laboral:</strong> Hace referencia a cualquier conducta que ponga en riesgo la integridad y seguridad del trabajador, como la emisión de órdenes o la asignación de funciones sin garantizar los requisitos mínimos de protección y seguridad para el empleado.</li>
        </ul>

        <div class="beta-subtitulo">3. Medidas Preventivas y Correctivas del Acoso Laboral</div>
        <p class="gamma-parrafo">
            Los reglamentos de trabajo de las empresas deben prever mecanismos para prevenir las conductas de acoso laboral y establecer un procedimiento interno, confidencial, conciliatorio y efectivo para resolver las situaciones que se presenten en el lugar de trabajo. Los comités bipartitos de empresa, donde existan, podrán asumir funciones relacionadas con el acoso laboral, conforme lo estipulen los reglamentos de trabajo.
            <br>
            El Comité de Convivencia Laboral, en conjunto con la empresa, deberá:
        </p>

        <ul class="delta-lista">
            <li class="gamma-parrafo"><strong>Desarrollar campañas de divulgación preventiva:</strong> Conversatorios y capacitaciones sobre las conductas que constituyen o no acoso laboral, así como sobre las circunstancias agravantes, las conductas atenuantes y el tratamiento sancionatorio correspondiente.</li>
            <li class="gamma-parrafo"><strong>Crear espacios de diálogo:</strong> Como círculos de participación o grupos similares, para evaluar periódicamente la vida laboral. Estos espacios deben promover la coherencia operativa y la armonía funcional, fomentando un ambiente de respeto y buen trato dentro de la organización.</li>
            <li class="gamma-parrafo"><strong>Capacitar al personal:</strong> Sobre la Ley 1010 de 2006, en lo que respecta a las conductas que constituyen o no acoso laboral.</li>
            <li class="gamma-parrafo"><strong>Establecer, en conjunto con los colaboradores:</strong> Valores y hábitos que promuevan un clima laboral saludable, alineado con los principios y normas de la organización.</li>
            <li class="gamma-parrafo"><strong>Formular recomendaciones constructivas:</strong> Para enfrentar situaciones empresariales que puedan afectar el cumplimiento de dichos valores y hábitos.</li>
            <li class="gamma-parrafo"><strong>Examinar conductas específicas:</strong> Que puedan constituir acoso laboral u otros tipos de hostigamiento que afecten la dignidad de las personas, y ofrecer las recomendaciones pertinentes.</li>
        </ul>

        <div class="beta-subtitulo">4. Marco Normativo</div>
        <table class="epsilon-tabla">
            <thead>
                <tr>
                    <th>Normativa</th>
                    <th>Descripción</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Código Sustantivo del Trabajo - Art. 57</td>
                    <td>Obligaciones especiales del Empleador.</td>
                </tr>
                <tr>
                    <td>Código Sustantivo del Trabajo - Art. 58</td>
                    <td>Obligaciones especiales del Trabajador.</td>
                </tr>
                <tr>
                    <td>Código Sustantivo del Trabajo - Art. 59 y 60</td>
                    <td>Prohibiciones del Trabajador y del Empleador.</td>
                </tr>
                <tr>
                    <td>Ley 1010 de 2006</td>
                    <td>Por medio de la cual se adoptan medidas para prevenir, corregir y sancionar el acoso laboral y otros hostigamientos en el marco de las relaciones de trabajo.</td>
                </tr>
                <tr>
                    <td>Resolución 2646 de 2008</td>
                    <td>Por la cual se establecen disposiciones y se definen responsabilidades para la identificación, evaluación, prevención, intervención y monitoreo permanente de la exposición a factor de riesgo psicosocial en el trabajo y para la determinación del origen de las patologías causadas por el estrés laboral.</td>
                </tr>
                <tr>
                    <td>Resolución 652 de 2012</td>
                    <td>Por la cual se establece la conformación y funcionamiento del Comité de Convivencia Laboral en entidades públicas y empresas privadas y se dictan otras disposiciones.</td>
                </tr>
                <tr>
                    <td>Resolución 1356 de 2012</td>
                    <td>Ministerio del Trabajo que modifica parcialmente la Resolución 652 de 2012.</td>
                </tr>
                <tr>
                    <td>Ley 1616 de 2013</td>
                    <td>Por medio de la cual se expide la ley de salud mental y se dictan otras disposiciones.</td>
                </tr>
                <tr>
                    <td>Resolución 2764 de 2022</td>
                    <td>Por la cual se adopta la Batería de instrumentos para la evaluación de factores de Riesgo Psicosocial, la Guía Técnica General para la promoción, prevención e intervención de los factores psicosociales y sus efectos en la población trabajadora y sus protocolos específicos y se dictan otras disposiciones.</td>
                </tr>
                <tr>
                    <td>Circular 026 de 2023</td>
                    <td>Prevención y atención del acoso laboral y sexual, violencia basada en género contra las mujeres y personas de los sectores sociales LGBTIQ+ en el ámbito laboral.</td>
                </tr>

            </tbody>
        </table>

        <div class="beta-subtitulo">5. Generalidades de la Empresa</div>
        <p class="gamma-parrafo">
            <span class="zeta-resaltado"><strong>Misión:</strong> <br> </span> <?= $clientPolicy2['policy_content'] ?>
        </p>
        <p class="gamma-parrafo">
            <span class="zeta-resaltado"><strong>Visión:</strong> <br> </span> <?= $clientPolicy3['policy_content'] ?>
        </p>

        <div class="beta-subtitulo">6. Principios Rectores de <strong><?= $client['nombre_cliente'] ?></strong></div>
        <ul class="delta-lista">
            <li class="gamma-parrafo"><strong>6.1 Ética:</strong> Estamos comprometidos con la transparencia y profesionalismo en todas nuestras actividades. Esta es la manera como generamos confianza entre nosotros y con nuestros socios, inversionistas y clientes.</li>

            <li class="gamma-parrafo"><strong>6.2 Desempeño y Responsabilidad:</strong> Todos prosperamos con base en nuestra responsabilidad y compromiso, entendemos como nuestro trabajo lleva a la compañía hacia adelante y cumple de manera consistente. No ofrecemos, ni aceptamos, disculpas por un pobre desempeño.</li>

            <li class="gamma-parrafo"><strong>6.3 Trabajo en equipo:</strong> Estamos comprometidos con construir el mejor equipo de la industria y a trabajar de manera colaborativa para lograr cosas que de manera individual no podríamos.</li>

            <li class="gamma-parrafo"><strong>6.4 Mentalidad de participación:</strong> Todos hacemos parte de manera profunda de la estrategia, operatividad e iniciativas clave. No existen "Gerentes" solitarios, todos realizamos labores significativas. El Pensamiento claro hábilmente ejecutado es nuestra ventaja competitiva.</li>

            <li class="gamma-parrafo"><strong>6.5 Ser locales, rápidos y descentralizados:</strong> Creemos que las mejores decisiones se toman estando lo más cercano posible a nuestros clientes. No permitimos que la burocracia se atraviese en la toma de decisiones rápidas, ejecutando con urgencia y haciendo las cosas correctas.</li>

            <li class="gamma-parrafo"><strong>6.6 Recursividad:</strong> Constantemente buscamos formas de hacer mejor las cosas y crear un valor diferencial. Constantemente ponemos nuestros recursos (Únicamente) donde tiene sentido ponerlos.</li>

        </ul>

        <div class="beta-subtitulo">7. Normas de Convivencia Laboral</div>
        <ul class="delta-lista">
            <strong>7.1. Trabajar juntos:</strong> Entregando lo mejor de cada uno y fomentando relaciones de trabajo armónicas y productivas, valorando las necesidades del otro y demostrando solidaridad por los compañeros. Conductas asociadas:
            <ul>
                <li class="gamma-parrafo">Cooperar e interactuar con los compañeros de trabajo y demás áreas para facilitar el trabajo en equipo.</li>
                <li class="gamma-parrafo">Propiciar espacios de comunicación donde se escuche de forma respetuosa, empática y se propongan ideas.</li>
                <li class="gamma-parrafo">Hacer caer en cuenta a los integrantes del equipo de trabajo cuando sus conductas van en contra de los principios y valores organizacionales.</li>
                <li class="gamma-parrafo">Atender oportunamente las tareas que afectan el trabajo de otros compañeros.</li>
                <li class="gamma-parrafo">Fomentar el trabajo en equipo fundamentado en criterios de colaboración, solidaridad y compañerismo para facilitar el alcance de los objetivos empresariales.</li>
            </ul>

            <strong>7.2. Mostrar con el ejemplo:</strong> Siendo coherentes con lo que se dice y se hace, asumiendo con responsabilidad las consecuencias de nuestros actos con honestidad y transparencia. Conductas asociadas:
            <ul>
                <li class="gamma-parrafo">Cumplir los compromisos y ser acordes con las palabras.</li>
                <li class="gamma-parrafo">Responsabilizarse de las consecuencias propias de nuestras acciones.</li>
                <li class="gamma-parrafo">Manifestar de forma coherente y respetuosa las órdenes o tareas.</li>
                <li class="gamma-parrafo">Velar por que las acciones estén acordes con los valores personales y organizacionales.</li>
                <li class="gamma-parrafo">Acoger las políticas internas de la empresa y las disposiciones normativas.</li>
            </ul>

            <strong>7.3. Respetar a los demás:</strong> Por su condición de ser humano, valorando sus conocimientos, potencial y experiencia como miembros del equipo de trabajo. Conductas asociadas:
            <ul>
                <li class="gamma-parrafo">Tratar a todo el personal con respeto, asertividad y dignidad.</li>
                <li class="gamma-parrafo">Valorar las diferencias individuales y sociales.</li>
                <li class="gamma-parrafo">Dirigirse amablemente a las demás personas, presentes y ausentes.</li>
                <li class="gamma-parrafo">Realizar peticiones con amabilidad.</li>
                <li class="gamma-parrafo">Adoptar modales de educación como saludar y despedirse de los compañeros de trabajo, gracias y por favor.</li>
                <li class="gamma-parrafo">Compartir y gestionar el conocimiento como la mejor manera de aprender y crecer profesionalmente dentro de <strong><?= $client['nombre_cliente'] ?></strong>.</li>
            </ul>

            <strong>7.4. Mantener una comunicación abierta:</strong> Respetuosa y asertiva, aportando al crecimiento personal y profesional de los miembros del equipo de trabajo. Conductas asociadas:
            <ul>
                <li class="gamma-parrafo">Informar con veracidad, oportunidad y claridad aspectos relevantes y decisiones que adopte <strong><?= $client['nombre_cliente'] ?></strong> que incidan e involucren a los empleados.</li>
                <li class="gamma-parrafo">Manejar de forma responsable y confidencial la información de <strong><?= $client['nombre_cliente'] ?></strong>.</li>
                <li class="gamma-parrafo">Expresar las ideas y opiniones de forma clara y precisa y asertiva.</li>
                <li class="gamma-parrafo">Escuchar a las personas antes de emitir juicios sobre ellos.</li>
            </ul>

            <strong>7.5. Ser abiertos a la crítica y autocrítica:</strong> Constructiva para convertirla en un aporte satisfactorio y agradable en el desarrollo de nuestro quehacer. Conductas asociadas:
            <ul>
                <li class="gamma-parrafo">Valorar y respetar la diferencia y la oposición o contradicción.</li>
                <li class="gamma-parrafo">Evitar la crítica destructiva, descalificar o desprestigiar las actividades realizadas por otros con mala intención.</li>
                <li class="gamma-parrafo">Prepararse para escuchar y atender la crítica y convertirla en un insumo importante para el mejoramiento continuo.</li>
                <li class="gamma-parrafo">Buscar alcanzar acuerdos satisfactorios.</li>
                <li class="gamma-parrafo">Evitar juicios de valor.</li>
                <li class="gamma-parrafo">No juzgar la información recibida sin antes investigar y analizar su veracidad.</li>
                <li class="gamma-parrafo">Respetar los turnos de opinión, sin interrupciones.</li>
                <li class="gamma-parrafo">Fomentar la participación en las reuniones, donde los participantes se sientan cómodos de realizar sus aportes.</li>
                <li class="gamma-parrafo">No patrocinar los enfrentamientos o situaciones violentas.</li>
                <li class="gamma-parrafo">Privilegiar el diálogo respetuoso como la herramienta esencial para construir acuerdos.</li>
            </ul>

            <strong>7.6. Respetar nuestra privacidad Y la de los compañeros de trabajo:</strong> Conductas asociadas:
            <ul>
                <li class="gamma-parrafo">No patrocinar el chisme y el rumor.</li>
                <li class="gamma-parrafo">Evitar que los comentarios afecten la integridad de las personas, el clima laboral y el logro de los objetivos empresariales.</li>
                <li class="gamma-parrafo">Respetar la vida privada de los compañeros de trabajo (evitar contar la vida personal a todo el mundo y ventilar la de otros).</li>
                <li class="gamma-parrafo">No juzgar por la primera impresión y basado en comentarios o percepciones subjetivas.</li>
                <li class="gamma-parrafo">Mantener los problemas bajo control dentro del proceso que se gestó, en la medida que sea posible.</li>
            </ul>

            <strong>7.7. Construir un ambiente de trabajo agradable:</strong> Fomentando el respeto y las buenas relaciones en el desempeño productivo de los trabajadores y el logro de los objetivos de <strong><?= $client['nombre_cliente'] ?></strong>. Conductas asociadas:
            <ul>
                <li class="gamma-parrafo">Evitar aislar o excluir a un compañero de trabajo.</li>
                <li class="gamma-parrafo">Estimular la construcción de relaciones sanas y efectivas con compañeros de trabajo, basadas en la sinceridad y confianza.</li>
                <li class="gamma-parrafo">Participar activamente de todas las actividades de bienestar laboral programadas en la empresa.</li>
                <li class="gamma-parrafo">Convocar a la convivencia desde una actitud positiva.</li>
                <li class="gamma-parrafo">Buscar siempre el beneficio mutuo en las relaciones de trabajo.</li>
                <li class="gamma-parrafo">Promover el diálogo como forma de construir acuerdos.</li>
                <li class="gamma-parrafo">No tolerar actos de violencia como amenazas, ni represalias por parte de ningún empleado contra otros compañeros.</li>
                <li class="gamma-parrafo">Fomentar las buenas acciones entre jefes y colaboradores, las cuales deben ser cordiales y respetuosas.</li>
            </ul>

            <strong>7.8. Valorar el buen trabajo:</strong> Fomentar el reconocimiento de logros sincero y oportuno entre compañeros. Conductas asociadas:
            <ul>
                <li class="gamma-parrafo">Reconocer los logros y buenos resultados de los servidores, no apropiarse de los logros que no correspondan.</li>
                <li class="gamma-parrafo">Delegar en los colaboradores funciones, como forma de facilitar el desarrollo y permitir la apropiación de responsabilidades.</li>
                <li class="gamma-parrafo">Propiciar espacios de generación de ideas que permitan una mejor gestión empresarial.</li>
                <li class="gamma-parrafo">Crear espacios para expresar a compañeros y colaboradores el aprecio, interés y reconocimiento por el valor agregado a las funciones de los servidores.</li>
                <li class="gamma-parrafo">Destacar más las fortalezas que las debilidades, como forma de mantener en los servidores una actitud motivadora y positiva.</li>
            </ul>

            <strong>7.9. Hacer uso adecuado Y respetuoso de las zonas comunes y puestos de trabajo, garantizando que permanezcan limpios y ordenados.:</strong> Conductas asociadas:
            <ul>
                <li class="gamma-parrafo">Respetar el espacio de trabajo compartido.</li>
                <li class="gamma-parrafo">Evitar interrumpir las labores de los compañeros.</li>
                <li class="gamma-parrafo">Manejar un tono de voz adecuado.</li>
                <li class="gamma-parrafo">Depositar los residuos en los lugares establecidos.</li>
                <li class="gamma-parrafo">Dejar los baños como nos gustaría encontrarlos, limpios.</li>
                <li class="gamma-parrafo">Organizar las sillas y mesas al terminar de consumir los alimentos.</li>
                <li class="gamma-parrafo">Hacer uso adecuado de la cantidad de agua, jabón de manos y papel higiénico.</li>
                <li class="gamma-parrafo">Todos los espacios de las instalaciones deben estar libres de humo.</li>
            </ul>

        </ul>

        <div class="beta-subtitulo">8. Deberes de Convivencia de los Trabajadores</div>
        <ul class="delta-lista">
            <ul>
                <li class="gamma-parrafo"><strong>Cumplir con todas las normas internas:</strong> Adoptadas por el trabajador para el buen funcionamiento de <strong><?= $client['nombre_cliente'] ?></strong></li>
                <li class="gamma-parrafo"><strong>Respetar a los compañeros:</strong> En todo momento y lugar (incluyendo a todo el personal que labore en la empresa, incluso trabajadores temporales y practicantes).</li>
                <li class="gamma-parrafo"><strong>Dar trato respetuoso:</strong> A los demás frente a raza, creencia religiosa o preferencia sexual.</li>
                <li class="gamma-parrafo"><strong>Comunicarse de manera asertiva y tranquila.</strong></li>
                <li class="gamma-parrafo"><strong>Cumplimiento de las obligaciones:</strong> Derivadas de su vinculación.</li>
                <li class="gamma-parrafo"><strong>Comunicar oportunamente:</strong> Las observaciones que estime puedan provocar daños o perjuicios en cuanto al acoso laboral se refiere.</li>
            </ul>

        </ul>

        <div class="beta-subtitulo">9. Derechos de los Trabajadores</div>
        <ul class="delta-lista">
            <ul>
                <li class="gamma-parrafo"><strong>Expresar las sugerencias necesarias:</strong> Para el mantenimiento del buen clima laboral.</li>
                <li class="gamma-parrafo"><strong>Participar de las actividades de integración.</strong></li>
                <li class="gamma-parrafo"><strong>Ser reconocidos por el buen desempeño laboral.</strong></li>
                <li class="gamma-parrafo"><strong>Gozar de una sana convivencia:</strong> En el entorno organizacional.</li>
                <li class="gamma-parrafo"><strong>Recibir un buen trato:</strong> Por parte de sus superiores, subalternos y compañeros de trabajo.</li>
                <li class="gamma-parrafo"><strong>Presentar sugerencias:</strong> Sobre actividades para desarrollar en las integraciones al área de salud ocupacional, donde se analizará su viabilidad.</li>
                <li class="gamma-parrafo"><strong>Realizar sugerencias sobre el mejoramiento:</strong> De la seguridad e higiene industrial, las cuales serán revisadas y mantenidas en discreción.</li>
                <li class="gamma-parrafo"><strong>Contar con un trato equitativo:</strong> Sin ningún tipo de discriminación.</li>
                <li class="gamma-parrafo"><strong>Recibir atención de manera prudente y confidencial:</strong> Al momento de presentar quejas por acoso laboral, garantizando las determinaciones suficientes para solucionarlas.</li>
            </ul>

        </ul>

        <div class="beta-subtitulo">10. Derechos de Convivencia Laboral de <strong><?= $client['nombre_cliente'] ?></strong></div>
        <ul class="delta-lista">
            <ul>
                <li class="gamma-parrafo"><strong>Derecho a ser respetado.</strong></li>
                <li class="gamma-parrafo"><strong>Derecho a recibir un trato digno:</strong> Frente a creencias religiosas o identidad sexual.</li>
                <li class="gamma-parrafo"><strong>Derecho a manifestar su opinión o emociones.</strong></li>
                <li class="gamma-parrafo"><strong>Derecho a ser escuchado:</strong> Cuando expone un argumento o punto de vista.</li>
                <li class="gamma-parrafo"><strong>Derecho a realizar las labores:</strong> Que se encuentren dentro del manual de sus funciones.</li>
                <li class="gamma-parrafo"><strong>Derecho a ser escuchado:</strong> Por el comité de convivencia cuando identifique conductas que atenten contra la convivencia laboral de <strong><?= $client['nombre_cliente'] ?></strong>. (Incluyendo maltrato laboral o conductas de acoso).</li>
            </ul>

        </ul>

        <div class="beta-subtitulo">11. Conductos Regulares y Solución de Conflictos</div>

        <p class="gamma-parrafo">Procedimientos para solucionar conflictos entre los trabajadores:

        </p>

        <p class="gamma-parrafo">
            En el entorno laboral los conflictos son prácticamente inevitables y estos contribuyen a la maduración y crecimiento de las personas, pero lo complicado es saber encauzarlos para no pasar a consecuencias indeseadas. Los conflictos no deben buscarse ni crear ocasiones para que se den y deben ser vistos de forma positiva, para tomar conciencia y contribuir al mejoramiento continuo.

            En caso de presentarse diferencias o situaciones que alteren la convivencia entre los trabajadores y que no necesariamente constituyan conductas de acoso laboral, se recomienda tener en cuenta los siguientes pasos para que el evento pueda ser solucionado de manera pacífica y no pase a instancias más graves.
        </p>
        <ul class="delta-lista">
            <ul>
                <li class="gamma-parrafo"><strong>Adoptar una actitud pacífica.</strong></li>
                <li class="gamma-parrafo"><strong>Escuchar el punto de vista del otro.</strong></li>
                <li class="gamma-parrafo"><strong>Mantener la situación en privado:</strong> Y no hacerlo enfrente de otras personas.</li>
                <li class="gamma-parrafo"><strong>Tratar al otro con respeto:</strong> Sin insultos.</li>
                <li class="gamma-parrafo"><strong>Manifestar las situaciones que están causando incomodidad.</strong></li>
                <li class="gamma-parrafo"><strong>Evitar la provocación que lleve a la violencia.</strong></li>
                <li class="gamma-parrafo"><strong>Mantener la calma.</strong></li>
                <li class="gamma-parrafo"><strong>Declarar su disposición para llegar a un acuerdo.</strong></li>
                <li class="gamma-parrafo"><strong>Ser sincero en el intento de reconciliación.</strong></li>
            </ul>

        </ul>

        <div class="beta-subtitulo">12. Procedimiento para Atender las Quejas por Acoso Laboral</div>
        <p class="gamma-parrafo">Cuando se presente una situación conflictiva que no pueda ser solucionada entre los involucrados y conductas que constituyan acoso laboral tales como maltrato, persecución, discriminación, entorpecimiento de la labor, inequidad o desprotección, tal como lo manifiesta la Ley 1010 de 2006, se deberá seguir el siguiente conducto regular:</p>
        <ul class="delta-lista">
            <ul>
                <li class="gamma-parrafo"><strong>Informar de la situación agravante:</strong> Al secretario(a) del Comité.</li>
                <li class="gamma-parrafo"><strong>Convocar reunión:</strong> Del Comité de Convivencia Laboral.</li>
                <li class="gamma-parrafo"><strong>Análisis de las pruebas.</strong></li>
                <li class="gamma-parrafo"><strong>Socialización con el sujeto pasivo y activo:</strong> Del conflicto o acoso laboral.</li>
                <li class="gamma-parrafo"><strong>Tomar medidas pertinentes al caso:</strong> Plan de acción.</li>
                <li class="gamma-parrafo"><strong>Realizar seguimiento:</strong> A los planes de acción establecidos.</li>
                <li class="gamma-parrafo"><strong>Remitir a la Gerencia:</strong> Si no se llegan a acuerdos, la conducta persiste o no se cumplen las recomendaciones.</li>
            </ul>

        </ul>

        <div class="beta-subtitulo">13. Quejas sobre Conductas que no Constituyen Acoso Laboral</div>
        <p class="gamma-parrafo">Es importante tener en cuenta que no toda conducta que genere una diferencia o desacuerdo entre los trabajadores en su relacionamiento laboral, constituye acoso, sino que debe ser una conducta tendiente a causar perjuicio o la renuncia del trabajador, de manera persistente y demostrable, por lo tanto, la queja de acoso laboral carezca de todo fundamento fáctico o razonable es decir, aquellas que se consideran como temerarias, es sancionada por la legislación con multa entre medio y tres salario mínimos legales mensuales. <br> Para contextualizar lo anterior a continuación se relaciona algunas de las conductas que NO se consideran de acoso laboral:
        </p>
        <ul class="delta-lista">
            <ul>
                <li class="gamma-parrafo"><strong>La formulación de exigencias razonables:</strong> De fidelidad laboral o lealtad empresarial e institucional.</li>
                <li class="gamma-parrafo"><strong>La solicitud de cumplir deberes extras:</strong> De colaboración con la empresa o la institución, cuando sean necesarios para la continuidad del servicio o para solucionar situaciones difíciles en la operación de la empresa o la institución.</li>
                <li class="gamma-parrafo"><strong>Los actos destinados a ejercer la potestad disciplinaria:</strong> Que legalmente corresponde a los superiores jerárquicos sobre sus subalternos.</li>
                <li class="gamma-parrafo"><strong>Las exigencias de cumplir con las estipulaciones:</strong> Contenidas en los reglamentos y cláusulas de los contratos de trabajo.</li>
                <li class="gamma-parrafo"><strong>Las exigencias técnicas, los requerimientos de eficiencia y las peticiones de colaboración.</strong></li>
            </ul>

        </ul>

        <p class="gamma-parrafo">Se les informa que las normas que se encuentran contenidas en este manual son de obligatorio cumplimiento para todo el personal, por lo que su incumplimiento constituye como una violación al manual que nos podría llevar a sanciones.</p>

        <p class="gamma-parrafo">
            <span class="zeta-resaltado">Nota:</span> Este Manual no cubre las actividades desarrolladas por personal propio de <strong><?= $client['nombre_cliente'] ?></strong>
        </p>

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

   <!--  <div>
        <a href="<?= base_url('/generatePdf_manconvivenciaLaboral') ?>" target="_blank">
            <button type="button">PDF</button>
        </a>
    </div> -->

</body>

</html>