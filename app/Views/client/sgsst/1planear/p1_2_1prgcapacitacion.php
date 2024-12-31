<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>1.2.1 Programa de Capacitación y Entrenamiento</title>
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


    <div class="beta-subtitulo">1. OBJETO</div>
    <p class="gamma-parrafo">
        Desarrollar actividades de capacitación acordes con los programas del Sistema de Gestión de la Salud y Seguridad en el Trabajo de la copropiedad <strong><?= $client['nombre_cliente'] ?></strong>, que fortalezcan las capacidades, conocimientos y aptitudes del consejo de administración, vigía, proveedores y contratistas en materia de Salud y Seguridad en el trabajo.
    </p>

    <div class="beta-subtitulo">2. ALCANCE</div>
    <p class="gamma-parrafo">
        Aplica a todas las partes interesadas de <strong><?= $client['nombre_cliente'] ?></strong>, incluyendo proveedores y contratistas, cuyas actividades o servicios estén relacionados directamente con riesgos a la salud.
    </p>

    <div class="beta-subtitulo">3. METODOLOGÍA</div>
    <p class="gamma-parrafo"><strong>CAPACITACIÓN CONTINUA:</strong> Con el programa de capacitación, se busca mantener actualizados a las partes interesadas y desarrollar un plan de entrenamiento que incluya temas de seguridad industrial y ambiental. Este plan contará con la participación del administrador de la copropiedad, el vigía de la seguridad y salud en el trabajo, y los miembros del consejo de administración interesados. Tomando como referencia la Matriz de riesgos, se capacitará a todas las partes interesadas de acuerdo con los riesgos críticos detectados. El entrenamiento estará enfocado en la prevención de incidentes, accidentes y enfermedades laborales; se analizarán indicadores como cumplimiento, cobertura y eficacia para la elaboración del cronograma.</p>

    <p class="gamma-parrafo"><strong>CAPACITACIÓN ESPECÍFICA:</strong> Según la evaluación de riesgos de la copropiedad, se procederá al fortalecimiento de las competencias de todas las partes interesadas, promoviendo el "saber-hacer" y el "saber-saber" para mejorar la gestión y aplicar acciones concretas para la comunidad, proveedores y contratistas.</p>

    <ul class="delta-lista">
        <li class="gamma-parrafo">
            <strong>Descripción de la Actividad:</strong> El responsable del SG-SST, en común acuerdo con el administrador de la copropiedad, definirá la ruta de capacitación para las partes interesadas (consejo de administración, vigía SST, proveedores y contratistas) según criterios de criticidad o nivelación de conocimientos.
        </li>
        <li class="gamma-parrafo">
            <strong>Capacitación Programada:</strong> Tras el proceso de Identificación de Peligros y Evaluación de Riesgos, el responsable del SG-SST detectará las capacitaciones necesarias para el personal cuyas actividades representen riesgos significativos y procederá a establecer el cronograma.
        </li>
        <li class="gamma-parrafo">
            <strong>Capacitación No Programada:</strong> Ante nuevas necesidades no contempladas en el cronograma, el encargado del SG-SST realizará las capacitaciones o entrenamientos necesarios para mitigar riesgos significativos.
        </li>
    </ul>

    <div class="beta-subtitulo">4. DEFINICIONES</div>
    <ul class="delta-lista">
        <li class="gamma-parrafo"><strong>Capacitación:</strong> Actividades de formación en temas específicos que complementan la educación académica del trabajador.</li>
        <li class="gamma-parrafo"><strong>Líder del programa:</strong> Persona encargada del desarrollo y cumplimiento de las actividades definidas en el SG-SST.</li>
        <li class="gamma-parrafo"><strong>Competencias técnicas, cognitivas e interpersonales:</strong> Incluyen habilidades específicas (técnicas), pensamiento crítico y resolución de problemas (cognitivas), y capacidad para interactuar eficazmente con otros (interpersonales).</li>
    </ul>

    <div class="beta-subtitulo">5. RESPONSABLE</div>
    <p class="gamma-parrafo">
        La responsabilidad de implementar este programa recae en el responsable del SG-SST, quien debe contar con licencia y resolución vigente.
    </p>

    <div class="beta-subtitulo">6. DOCUMENTOS RELACIONADOS</div>
    <ul class="delta-lista">
        <li class="gamma-parrafo">Decreto 1072 de 2015.</li>
    </ul>

    <div class="beta-subtitulo">7. DESCRIPCIÓN</div>
    <p class="gamma-parrafo">
        El programa de capacitaciones de <strong><?= $client['nombre_cliente'] ?></strong> se divide en 5 etapas:
    </p>

    <div class="beta-subtitulo">7.1. Programación</div>
    <p class="gamma-parrafo">
        El responsable del SG-SST y el administrador de la copropiedad definirán y programarán las actividades de capacitación, incluyendo:
    </p>
    <ul class="delta-lista">
        <li class="gamma-parrafo">Tema</li>
        <li class="gamma-parrafo">Responsable</li>
        <li class="gamma-parrafo">Fecha de ejecución</li>
        <li class="gamma-parrafo">Horas dictadas</li>
        <li class="gamma-parrafo">N. de personas invitadas</li>
        <li class="gamma-parrafo">Población objetivo</li>
    </ul>
    <p class="gamma-parrafo">
        Nota: Las capacitaciones no planeadas deberán registrarse de la misma forma, cumpliendo con estos parámetros.
    </p>

    <div class="beta-subtitulo">7.2. Ejecución</div>
    <p class="gamma-parrafo">
        El responsable de la capacitación presentará la propuesta al administrador de la copropiedad, vigía SST y/o consejo de administración para su verificación. Si se requiere un ente externo, se solicitará su revisión previa. Todos los asistentes deberán ser registrados en el formato FT-SST-006, junto con una evaluación del conocimiento.
    </p>

    <div class="beta-subtitulo">7.3. Registro de asistencia y resultado de evaluaciones</div>
    <p class="gamma-parrafo">
        El líder de la actividad recopilará los formatos y calificará las evaluaciones, que serán archivadas en el sistema del SG-SST. Se analizarán:
    </p>
    <ul class="delta-lista">
        <li class="gamma-parrafo">N. de personas asistentes</li>
        <li class="gamma-parrafo">Promedio de calificaciones</li>
    </ul>

    <div class="beta-subtitulo">7.4. Etapa de seguimiento</div>
    <p class="gamma-parrafo">
        Se identificarán oportunidades de mejora y se asegurarán altos niveles de comprensión. Rangos de calificación: &lt; 70 (Reprobó), &gt;= 70 (Aprobó). Cobertura mínima aceptable: 60% de asistencia.
    </p>

    <div class="beta-subtitulo">7.5. Indicadores</div>
    <ul class="delta-lista">
        <li class="gamma-parrafo"><strong>Cumplimiento de capacitaciones:</strong> N° capacitaciones realizadas / N° programadas x 100.</li>
        <li class="gamma-parrafo"><strong>Cobertura de capacitaciones:</strong> N° asistentes / N° invitados x 100.</li>
    </ul>


    <footer>
        <h2>Historial de Versiones</h2>
        <style>
            footer table {
                width: 100%;
                border-collapse: collapse;
                table-layout: fixed;
            }

            footer table th,
            footer table td {
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
                width: 35%;
                /* Más ancho para la columna Observaciones */
            }

            footer table th:nth-child(1),
            footer table td:nth-child(1) {
                width: 10%;
                /* Más estrecho para la columna Versión */
            }

            footer table th:nth-child(2),
            footer table td:nth-child(2),
            footer table th:nth-child(3),
            footer table td:nth-child(3),
            footer table th:nth-child(4),
            footer table td:nth-child(4) {
                width: 15%;
                /* Ancho uniforme para las demás columnas */
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
        <a href="<?= base_url('/generatePdf_prgCapacitacion') ?>" target="_blank">
            <button type="button">PDF</button>
        </a>
    </div> -->

</body>

</html>