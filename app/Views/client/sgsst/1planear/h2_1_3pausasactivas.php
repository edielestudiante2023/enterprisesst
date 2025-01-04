<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>4.1.3 Programa de Pausas Activas</title>
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
        .primus-container {
            width: 100%;
            /*  max-width: 1000px; */
            margin: 0 auto;
            /*  padding: 20px; */
            /*  border: 1px solid #000; */
        }

        .primus-title {
            font-size: 24px;
            font-weight: bold;
            /* text-align: center; */
            margin-bottom: 20px;
        }

        .secundus-paragraph {
            font-size: 16px;
            text-align: justify;
            margin-bottom: 20px;
        }

        .tertia-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .tertia-table td,
        .tertia-table th {
            border: 1px solid #000;
            padding: 10px;
            text-align: left;
        }

        .tertia-table th {
            background-color: #f2f2f2;
        }

        .image-container {
            width: 100%;
            margin: 20px 0;
            text-align: center;
        }

        .image-caption {
            font-size: 14px;
            text-align: center;
            margin-top: 10px;
        }

        .exercise-container {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }

        .exercise-container img {
            max-width: 200px;
            /* Ajusta el tamaño de la imagen */
            margin-right: 20px;
        }

        .exercise-text {
            font-size: 16px;
            text-align: justify;
        }

        .image-center-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 50vh;
            /* Ajusta la altura si lo deseas */
        }

        .image-center-container img {
            max-width: 100%;
            /* La imagen se ajusta a su contenedor */
            height: auto;
            /* Mantener la proporción de la imagen */
        }

        .breathing-container {
            display: flex;
            justify-content: space-around;
            align-items: center;
            margin: 20px 0;
        }

        .breathing-item {
            text-align: center;
        }

        .breathing-item img {
            max-width: 200px;
            height: auto;
        }

        .breathing-label {
            border: 1px solid #000;
            padding: 10px;
            margin-bottom: 10px;
            font-size: 16px;
            font-weight: bold;
        }

        .secundus-paragraph {
            font-size: 16px;
            text-align: justify;
            margin-bottom: 20px;
        }

        .bullet-points {
            margin-left: 20px;
        }

        .tertia-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .tertia-table td,
        .tertia-table th {
            border: 1px solid #000;
            padding: 10px;
            text-align: left;
        }

        .tertia-table th {
            background-color: #f2f2f2;
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

    <div class="primus-container">

        <h3 class="primus-title">1. Objetivo</h3>
        <p class="secundus-paragraph">
            Establecer un programa de Pausas Activas en <strong><?= $client['nombre_cliente'] ?></strong>, con el fin de crear conciencia sobre la importancia de adquirir y promover hábitos saludables dentro y fuera de la jornada laboral, buscando así la prevención de enfermedades laborales.
        </p>

        <h3 class="primus-title">2. Alcance</h3>
        <p class="secundus-paragraph">
            Este programa aplica a todos los empleados de <strong><?= $client['nombre_cliente'] ?></strong>.
        </p>



        <h3 class="primus-title">3. Definiciones</h3>
        <p class="secundus-paragraph">
            <strong>Actividad física:</strong> entendiéndose este, como el conjunto de ejercicios físicos y mentales ejecutados en los sitios de trabajo, en las pausas correspondientes.
        </p>
        <p class="secundus-paragraph">
            <strong>Pausas activas:</strong> Consiste en la ejecución de diferentes ejercicios durante la jornada laboral con el fin de reactivar nuestro cuerpo y mente, para prevenir desórdenes osteomusculares y/o psicológicos causados por la fatiga física y/o mental y de esta manera mejorar nuestro bienestar e incrementar nuestra productividad y el rendimiento laboral.
        </p>
        <p class="secundus-paragraph">
            <strong>Sedentarismo:</strong> es la falta de movilidad o inactividad mínima necesaria que requiere el organismo para mantenerse saludable, es asumido como parte de la vida diaria y con él sus múltiples consecuencias como las enfermedades cardiovasculares y el aumento de la morbilidad por enfermedades asociadas como la obesidad, el estrés y el consumo de alcohol.
        </p>

        <!-- Sección de Desarrollo del Programa -->
        <h3 class="primus-title">4. Desarrollo del Programa</h3>
        <p class="secundus-paragraph">
            La PAUSA ACTIVA es una actividad destinada a compensar el esfuerzo diario realizado por las personas entregando un espacio destinado a la educación, recuperación y formación de hábitos hacia una vida saludable.
        </p>
        <p class="secundus-paragraph">
            Los ejercicios a realizar tienen una duración de 5 a 7 minutos y se realizan una o dos veces al día, estos momentos en los cuales se realizan las pausas activas se deben volver parte de la vida diaria y necesariamente con ellos se buscan, en primer lugar mejorar la calidad de vida de los colaboradores, cambiar por unos minutos los movimientos repetitivos y generar conciencia de cuidado por la salud en cada uno de ellos.
        </p>
        <p class="secundus-paragraph">
            Dentro de este programa se darán a conocer algunos ejercicios de la gran cantidad que existen para orientar a los trabajadores para implementar las pausas activas en su trabajo.
        </p>
        <p class="secundus-paragraph">
            Este programa tiene como base mejorar dolores por cansancio muscular y funciones repetitivas, además de concientizar a los trabajadores de la importancia de tener una vida saludable tanto física, como emocional.
        </p>

        <!-- Beneficios -->
        <h3 class="primus-title">Beneficios</h3>
        <table class="tertia-table">
            <tr>
                <th>AUMENTAN</th>
                <th>DISMINUYEN</th>
            </tr>
            <tr>
                <td>La armonía laboral a través del ejercicio físico y la relajación.</td>
                <td>El estrés laboral.</td>
            </tr>
            <tr>
                <td>Alivian las tensiones laborales producidas por malas posturas y rutina generada por el trabajo.</td>
                <td>Los factores generadores de trastornos músculo-esqueléticos de origen laboral que repercuten principalmente en cuello y extremidades superiores.</td>
            </tr>
            <tr>
                <td>El rendimiento en la ejecución de las labores.</td>
                <td>Las ausencias al trabajo.</td>
            </tr>
        </table>

        <h3 class="primus-title">4.1. Antes de Iniciar la Pausa Activa</h3>
        <p class="secundus-paragraph">
            Tenga en cuenta lo siguiente para realizar una buena pausa activa:
        </p>
        <ul class="bullet-points">
            <li>Preguntar a cada trabajador sobre su estado de salud, si tiene alguna lesión o molestia muscular, lumbar que le impida realizar la pausa.</li>
            <li>La respiración debe ser lo más profunda y rítmica posible, esto contribuye a oxigenar de manera adecuada el cuerpo y mente.</li>
        </ul>

        <div class="image-center-container">
            <img src="<?= base_url('uploads/respiracionabdominal.jpg') ?>" alt="Imagen Respiración Abdominal">
        </div>

        <div class="breathing-container">
            <div class="breathing-item">
                <div class="breathing-label">Inhala</div>
                <img src="<?= base_url('uploads/inhala.jpg') ?>" alt="Inhala">
            </div>
            <div class="breathing-item">
                <div class="breathing-label">Exhala</div>
                <img src="<?= base_url('uploads/exhala.jpg') ?>" alt="Exhala">
            </div>
        </div>

        <ul class="bullet-points">
            <li>Relájese.</li>
            <li>Concéntrese en los músculos y articulaciones que va a estirar.</li>
            <li>Sienta el estiramiento.</li>
            <li>No debe existir dolor.</li>
            <li>Realice ejercicios de calentamiento, antes del estiramiento.</li>
            <li>Póngase de pie, con los pies ligeramente separados y rodillas ligeramente dobladas para proteger la espalda, espalda y cuello rectos, brazos y hombros relajados a cada lado del cuerpo.</li>
        </ul>
        | <div class="image-center-container">
            <img src="<?= base_url('uploads/posicioninicial.jpg') ?>" alt="Imagen Respiración Abdominal">
        </div>

        <h3 class="primus-title">4.2. Ejercicios Recomendados</h3>
        <p class="secundus-paragraph">
            4.2.1 Ejercicios de estiramiento corporal
        </p>
        <p class="secundus-paragraph">
            4.2.1.1 Cuello
        </p>

        <div class="exercise-container">
            <img src="<?= base_url('uploads/cuello1.jpg') ?>" alt="">
            <div class="exercise-text">
                <p>Con la ayuda de la mano lleve la cabeza hacían un lado como si tocara el hombro con la oreja hasta sentir una leve tensión. Sostenga durante 15 segundos y realícelo hacia otro lado.</p>
            </div>
        </div>

        <div class="exercise-container">
            <img src="<?= base_url('uploads/cuello2.jpg') ?>" alt="">
            <div class="exercise-text">
                <p>Entrelace las manos y llévelas atrás de la cabeza de tal manera tal que lleve el mentón hacia el pecho. Sostenga esta posición durante 15 segundos.</p>
            </div>
        </div>
        <div class="exercise-container">
            <img src="<?= base_url('uploads/cuello3.jpg') ?>" alt="">
            <div class="exercise-text">
                <p>Eleve los hombros los mas que pueda y sostenga esta posición durante 15 segundos, luego descanse</p>
            </div>
        </div>
        <div class="exercise-container">
            <img src="<?= base_url('uploads/cuello4.jpg') ?>" alt="">
            <div class="exercise-text">
                <p>Lleve los brazos hacia atrás, por la espalda y entrelace los dedos e intente subir las manos sin soltar los dedos.
                    Sostenga esta posición durante 15 segundos y hágalo con el otro brazo.
                </p>
            </div>
        </div>
        <p class="secundus-paragraph">
            4.2.1.2 Brazos
        </p>

        <div class="exercise-container">
            <img src="<?= base_url('uploads/brazos1.jpg') ?>" alt="">
            <div class="exercise-text">
                <p>Con la espalada recta cruce los brazos por detrás de la cabeza e intente llevarlos hacia arriba. Sostenga esta posición durante 15 segundos.</p>
            </div>
        </div>

        <div class="exercise-container">
            <img src="<?= base_url('uploads/brazos2.jpg') ?>" alt="">
            <div class="exercise-text">
                <p>Lleve el brazo hasta el lado contrario y con la otra mano empújelo hacia el hombro. Realice este ejercicio durante 15 segundos y luego hágalo con el otro brazo.</p>
            </div>
        </div>
        <div class="exercise-container">
            <img src="<?= base_url('uploads/brazos3.jpg') ?>" alt="">
            <div class="exercise-text">
                <p>Extienda completamente el brazo hacia el frente, voltee la mano hacia abajo y con la mano contraía ejerza un poco de presión sobre el pulgar, hasta que sienta algo de tensión. Luego se debe hacer con el otro brazo.
                </p>
            </div>
        </div>
        <div class="exercise-container">
            <img src="<?= base_url('uploads/brazos4.jpg') ?>" alt="">
            <div class="exercise-text">
                <p>Lleve los brazos hacia atrás por encima del nivel de los hombros, tome un codo con la mano contraria, empujándolo hacia el cuello. Sostenga durante 15 segundos y cambie de lado.


                </p>
            </div>
        </div>
        <p class="secundus-paragraph">
            4.2.1.3 Manos
        </p>

        <div class="exercise-container">
            <img src="<?= base_url('uploads/manos1.jpg') ?>" alt="">
            <div class="exercise-text">
                <p>Estire el brazo hacia el frente y abra la mano como si estuviera haciendo la señal de pare, y con ayuda de la otra mano lleve hacia atrás todos los dedos durante 15 segundas y repita con la otra mano.</p>
            </div>
        </div>

        <div class="exercise-container">
            <img src="<?= base_url('uploads/manos2.jpg') ?>" alt="">
            <div class="exercise-text">
                <p>Lleve adelante la mano y voltee hacia abajo todos los dedos, con la ayuda de la otra mano ejerza un poco de presión hacia atrás durante 15 segundos.</p>
            </div>
        </div>
        <div class="exercise-container">
            <img src="<?= base_url('uploads/manos3.jpg') ?>" alt="">
            <div class="exercise-text">
                <p> Con una mano estire uno a uno cada dedo de la mano contraria (como si estuviera contando) y sosténgalo durante tres segundos.
                </p>
            </div>
        </div>
        <div class="exercise-container">
            <img src="<?= base_url('uploads/manos4.jpg') ?>" alt="">
            <div class="exercise-text">
                <p>Con las palmas de las manos hacia arriba, abra y cierre los dedos. Esto se debe repetir 10 veces.

                </p>
            </div>
        </div>
        <p class="secundus-paragraph">
            4.2.1.4 Piernas
        </p>

        <div class="exercise-container">
            <img src="<?= base_url('uploads/piernas1.jpg') ?>" alt="">
            <div class="exercise-text">
                <p>Levante la rodilla hasta donde le sea posible y sostenga esta posición durante 15 segundas. Mantenga recta la espalda y la pierna de apoyo (Se Recomienda sostenerse)</p>
            </div>
        </div>

        <div class="exercise-container">
            <img src="<?= base_url('uploads/piernas2.jpg') ?>" alt="">
            <div class="exercise-text">
                <p>Conservando la pierna recta, extiéndala al máximo posible. Mantenga esta posición durante 15 segundos.</p>
            </div>
        </div>
        <div class="exercise-container">
            <img src="<?= base_url('uploads/piernas3.jpg') ?>" alt="">
            <div class="exercise-text">
                <p> De un paso al frente, apoyando el talón en el piso Con una mano estire uno a uno lleve la punta del pie hacia su cuerpo. Mantenga esta posición durante 15 segundos.
                </p>
            </div>
        </div>
        <div class="exercise-container">
            <img src="<?= base_url('uploads/Captura.PNG') ?>" alt="">
            <div class="exercise-text">
                <p> De un paso al frente, apoyando el talón en el piso Con una mano estire uno a uno lleve la punta del pie hacia su cuerpo. Mantenga esta posición durante 15 segundos.
                </p>
            </div>
        </div>
        
        <p class="secundus-paragraph">
            4.2.1.5 Ejercicios para los pies
        </p>

        <div class="exercise-container">
            <div class="exercise-container">
                <img src="<?= base_url('uploads/pies1.jpg') ?>" alt="">
                <div class="exercise-text">
                    <p>Levante una pierna y mueva su pie hacia arriba y hacia abajo. </p>
                </div>
            </div>

            <div class="exercise-container">
                <img src="<?= base_url('uploads/pies1.jpg') ?>" alt="">
                <div class="exercise-text">
                    <p>Luego hacia un lado y hacia el otro. Repita y cambie de pierna</p>
                </div>
            </div>
        </div>

        <p class="secundus-paragraph">
            4.2.2 Ejercicios de conservación visual
        </p>

        <div class="exercise-container">
            <img src="<?= base_url('uploads/visual1.jpg') ?>" alt="">
            <div class="exercise-text">
                <p>Siéntese con la espalda y cuello rectos, apoyando la columna contra el espaldar de la silla, cierre los ojos y coloque las palmas de las manos sobre cada ojo, sin hacer presión, respire profundamente e imagine un escenario agradable por unos minutos.</p>
            </div>
        </div>
        <div class="exercise-container">
            <img src="<?= base_url('uploads/ojos1.jpg') ?>" alt="">
            <div class="exercise-text">
                <p>Parpadee repetidamente hasta que sienta sus ojos húmedos.</p>
            </div>
        </div>
        <div class="exercise-container">
            <img src="<?= base_url('uploads/ojos2.jpg') ?>" alt="">
            <div class="exercise-text">
                <p>Mantenga cabeza recta y alineada con la espalda, inhale y luego mientras exhala mueva sus ojos: Con el cuello recto mire hacia arriba, hacia la derecha, hacia la izquierda y hacia abajo repetidamente durante 10 segundos. </p>
            </div>
        </div>
        <div class="exercise-container">
            <img src="<?= base_url('uploads/ojos3.jpg') ?>" alt="">
            <div class="exercise-text">
                <p>Con el cuello recto haga movimientos circulares con los ojos hacia el lado derecho y luego hacia el lado izquierdo durante 10 segundos. </p>
            </div>
        </div>
        <p class="secundus-paragraph">
            4.2.3 Gimnasia Cerebral
        </p>

        <div class="exercise-container">
            <img src="<?= base_url('uploads/gimnasia1.jpg') ?>" alt="">
            <div class="exercise-text">
                <p>No importa si no logra realizar el ejercicio como se indica, inténtelo haciéndolo de la mejor manera posible. Con el codo de su brazo derecho, toque la rodilla de su pierna izquierda levantando esta y luego, al contrario, con el codo del brazo izquierdo toque la rodilla de la pierna derecha levantando esta. Repita. </p>
            </div>
        </div>
        <div class="exercise-container">
            <img src="<?= base_url('uploads/gimnasia2.jpg') ?>" alt="">
            <div class="exercise-text">
                <p>Ubique sus brazos extendidos al frente, con uno realice círculos hacia afuera y con el otro círculo hacia adentro, haciéndolo al tiempo con los dos brazos.</p>
            </div>
        </div>
        <div class="exercise-container">
            <img src="<?= base_url('uploads/gimnasia3.jpg') ?>" alt="">
            <div class="exercise-text">
                <p>Ubique sus brazos, uno en la nariz y otro en la oreja y viceversa</p>
            </div>
        </div>

        <h3 class="primus-title">5. Diseño Ideal Del Puesto De Trabajo</h3>
        <p class="secundus-paragraph">
            A continuación, observaremos el esquema de un puesto de trabajo ideal con el diseño y postura adecuada para la prevención de enfermedades osteo- musculares. Nivel de los ojos y a una distancia entre 45 a 70 cms.
        </p>

        <div class="image-center-container">
            <img src="<?= base_url('uploads/ptrabajo1.jpg') ?>" alt="">
        </div>

        <div class="image-center-container">
            <img src="<?= base_url('uploads/ptrabajo2.png') ?>" alt="">
        </div>

        <h3 class="primus-title">5.1 Mala Postura</h3>
        <p class="secundus-paragraph">
            En las siguientes imágenes se reflejan cuáles son las malas posturas más comunes, y los errores que cometemos en la posición del computador y sus componentes:
        </p>
        <ul class="bullet-points">
            <li>Pantalla de Computador muy cerca, muy alta o muy baja.</li>
            <li>Silla muy separada del escritorio.</li>
            <li>Espalda doblada.</li>
            <li>Posición de las piernas cruzadas y hacia atrás.</li>
        </ul>

        <div class="image-center-container">
            <img src="<?= base_url('uploads/malapostura.jpg') ?>" alt="">
        </div>

        <!-- Efectos de la Mala Postura -->
        <h3 class="primus-title">Efectos de la Mala Postura</h3>
        <ul class="bullet-points">
            <li>Dolor en el cuello.</li>
            <li>Dolor en la espalda.</li>
            <li>Dolor en el brazo o antebrazo.</li>
            <li>Dolor en las manos.</li>
            <li>Sensación de cansancio.</li>
            <li>Disconformidad.</li>
        </ul>
        <p class="secundus-paragraph">
            Una buena postura permite permanecer más tiempo cómodo en nuestro escritorio. Lo primero que se debe hacer es organizar y adecuar el espacio de trabajo para un ambiente ideal, luego aprender a tomar las posiciones ergonómicas para sentarse frente al computador.
        </p>

        <!-- Zonas de Trabajo -->
        <h3 class="primus-title">5.2 Zonas de Trabajo</h3>
        <p class="secundus-paragraph">
            Lo primero que se debe hacer es identificar las distintas zonas en el escritorio, se habla de tres zonas:
        </p>
        <ul class="bullet-points">
            <li>Zona poco común,</li>
            <li>Zona ocasional,</li>
            <li>Zona usual.</li>
        </ul>
        <div class="image-center-container">
            <img src="<?= base_url('uploads/zonatrabajo.jpg') ?>" alt="">
        </div>
        <!-- Posición de los Componentes del Computador -->
        <h3 class="primus-title">5.3 Posición de los Componentes del Computador</h3>
        <p class="secundus-paragraph">
            La posición correcta de los periféricos del computador también influye en la postura que se debe tomar, la siguiente gráfica muestra cómo ubicarlos en el escritorio:
        </p>
        <p class="secundus-paragraph">
            Mantener el ratón, teclado y otros accesorios de uso frecuente al mismo nivel y en la zona usual.
        </p>

        <div class="image-center-container">
            <img src="<?= base_url('uploads/computador.jpg') ?>" alt="">
        </div>

        <!-- Posición de la Silla -->
        <h3 class="primus-title">5.3.4 Posición de la Silla</h3>
        <p class="secundus-paragraph">
            Otra pieza importante de la zona de trabajo es la silla y su ubicación, a continuación en el gráfico se muestra cómo se debe acondicionar para una buena postura:
        </p>
        <ul class="bullet-points">
            <li>Ajuste el asiento a la altura de las rodillas.</li>
            <li>Siéntese de forma que siempre el tronco esté apoyado en el espaldar de la silla.</li>
            <li>Dejar un espacio libre entre el pliegue de la rodilla y el borde del asiento.</li>
            <li>Mantener siempre los pies apoyados, preferiblemente utilizar un apoyapiés.</li>
        </ul>

        <div class="image-center-container">
            <img src="<?= base_url('uploads/silla.jpg') ?>" alt="">
        </div>

        <!-- Uso Correcto del Teclado y el Ratón -->
        <h3 class="primus-title">5.3.5 Uso Correcto del Teclado y el Ratón</h3>
        <p class="secundus-paragraph">
            Una vez se tenga acondicionado el espacio de trabajo, y se inicien las actividades, se debe saber cómo trabajar con los periféricos más usados, a continuación se explicará con gráficos cada uno de ellos.
        </p>
        <ul class="bullet-points">
            <li>Teclado: Mantener los antebrazos, puños y manos alineados en posición recta, con relación al teclado.</li>

            <div class="image-center-container">
            <img src="<?= base_url('uploads/mouse.jpg') ?>" alt="">
        </div>
            <li>Ratón: Tomar una posición similar que con el teclado, considerando que el secreto está en la posición neutral.</li>

            <div class="image-center-container">
            <img src="<?= base_url('uploads/mouse2.jpg') ?>" alt="">
        </div>
        </ul>

        <!-- Estrategias de Implementación -->
        <h3 class="primus-title">5.4 Estrategias de Implementación</h3>
        <p class="secundus-paragraph">
            El programa debe ser orientado por las personas a cargo del área de salud ocupacional quienes se encargaran de llevar los controles necesarios para el desarrollo y continuidad del programa.
        </p>
        <p class="secundus-paragraph">
            Cabe aclarar que el querer o no querer realizar los ejercicios que se presentan depende primero de la concientización de cada colaborador del beneficio que ellos le brindan a su salud y a su entorno laboral aumentando los niveles de clima organizacional al interior de las áreas.
        </p>
        <p class="secundus-paragraph">
            Con este programa se busca la interiorización en cada trabajador, además de la necesidad propia por buscar mejorar los estándares de satisfacción al interior de la empresa y los cuales se logran si cada trabajador siente apoyo por parte de la empresa tanto a nivel personal como familiar y laboral. Sin embargo, con la implementación del presente programa se pretende incluir el manejo del estrés en el mismo momento de la realización de los ejercicios para que se pueda dinamizar la jornada laboral y de esta manera reducir los niveles de estrés causados por la actividad diaria y que necesariamente conllevan dolor de cabeza y agotamiento mental.
        </p>
        <p class="secundus-paragraph">
            Las actividades a desarrollar para la implementación del programa son las siguientes:
        </p>
        <ul class="bullet-points">
            <li>Se elaborará un video con diferentes ejercicios a realizar por parte de los empleados del área administrativa, el video se reproducirá en los computadores de tal manera que interrumpa las actividades laborales a las 10:00 am y a las 3:30 pm.</li>
            <li>Se realizará la presentación de artículos y videos en los cuales se evidencia los RIESGOS DE UNA VIDA SEDENTARIA, tales como:
                <ul class="bullet-points">
                    <li>Aumento de depresión por estrés acumulado.</li>
                    <li>Dolencias propias del cargo por posturas no adecuadas.</li>
                    <li>Deficiencia en la respuesta de las articulaciones para el desplazamiento.</li>
                    <li>Trastornos cardiovasculares.</li>
                    <li>Trastornos musculares.</li>
                </ul>
            </li>
            <li>Se presentarán artículos y videos en los cuales se referencien DOLENCIAS POR MALAS POSTURAS, tales como:
                <ul class="bullet-points">
                    <li>Dolor lumbar.</li>
                    <li>Escoliosis.</li>
                    <li>Lordosis.</li>
                </ul>
            </li>
            <li>Se contemplará el MANEJO DEL STRESS en el mismo momento de la realización de los ejercicios para que de una forma integral se pueda dar una continuidad a la jornada laboral dinamizando las actividades.</li>
            <li>Se presentarán artículos que permitan identificar las situaciones que los producen, cómo se manifiestan en su cuerpo, en su vida familiar y personal y tratar de identificar el estrés bueno o positivo. Las actividades a realizar pueden enmarcarse en reuniones grupales en las cuales se utilice música ambiental o situaciones que generen risa.</li>
            <li>Se presentarán artículos y capacitaciones en las cuales se evidenciarán los riesgos expuestos al no tener una posición ergonómica adecuada para la actividad que se está realizando. Se hace necesario la revisión de los puestos de trabajo que no cumplan con las especificaciones necesarias para la actividad laboral y las frecuentes charlas con el fin de minimizar los riesgos.</li>
            <li>Se le entregará a todo el personal un folleto sobre pausas activas que pueden desarrollar durante su jornada laboral, se identificarán líderes que ayuden a promover el programa de pausas activas en cada una de las oficinas y áreas de trabajo.</li>
        </ul>
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

   <!--  <div>
        <a href="<?= base_url('/generatePdf_pausasActivas') ?>" target="_blank">
            <button type="button">PDF</button>
        </a>
    </div> -->

</body>

</html>