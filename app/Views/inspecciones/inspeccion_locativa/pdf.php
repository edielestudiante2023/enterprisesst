<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 100px 70px 80px 90px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.4;
            padding: 15px 20px;
        }

        /* Header corporativo */
        .header-table { width: 100%; border-collapse: collapse; border: 1.5px solid #333; margin-bottom: 12px; }
        .header-table td { border: 1px solid #333; padding: 5px 8px; vertical-align: middle; }
        .header-logo { width: 110px; text-align: center; font-size: 9px; }
        .header-logo img { max-width: 95px; max-height: 55px; }
        .header-title { text-align: center; font-weight: bold; font-size: 10px; }
        .header-code { width: 130px; font-size: 9px; }

        /* Titulo principal */
        .main-title { text-align: center; font-size: 12px; font-weight: bold; margin: 10px 0 8px; color: #1c2437; }

        /* Tabla datos generales */
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; border: 1px solid #ccc; }
        .info-table td { padding: 4px 8px; font-size: 10px; border: 1px solid #ccc; }
        .info-label { font-weight: bold; color: #444; width: 100px; background: #f7f7f7; }

        /* Titulos de seccion */
        .section-title { background: #1c2437; color: white; padding: 4px 10px; font-weight: bold; font-size: 10px; margin: 10px 0 5px; }

        /* Tablas de datos */
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .data-table th { background: #e8e8e8; border: 1px solid #aaa; padding: 4px 6px; font-size: 9px; text-align: left; }
        .data-table td { border: 1px solid #ccc; padding: 3px 6px; font-size: 9px; vertical-align: top; }

        /* Texto contenido */
        .content-text { font-size: 10px; line-height: 1.5; margin-bottom: 6px; }
        .empty-text { color: #888; font-style: italic; font-size: 9px; margin-bottom: 6px; }

        /* Fotos hallazgos */
        .hallazgo-img { max-width: 160px; max-height: 120px; border: 1px solid #ccc; }
        .hallazgo-estado { padding: 2px 6px; font-size: 8px; font-weight: bold; }
        .estado-abierto { background: #fff3cd; color: #856404; }
        .estado-cerrado { background: #d4edda; color: #155724; }
        .estado-excedido { background: #f8d7da; color: #721c24; }

        .page-break { page-break-before: always; }
        .intro-text { font-size: 9px; line-height: 1.5; margin-bottom: 8px; text-align: justify; }
    </style>
</head>
<body>

    <!-- HEADER CORPORATIVO -->
    <table class="header-table">
        <tr>
            <td class="header-logo" rowspan="2">
                <?php if (!empty($logoBase64)): ?>
                    <img src="<?= $logoBase64 ?>">
                <?php else: ?>
                    <strong style="font-size:8px;"><?= esc($cliente['nombre_cliente'] ?? '') ?></strong>
                <?php endif; ?>
            </td>
            <td class="header-title">SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO</td>
            <td class="header-code">Codigo: FT-SST-216<br>Version: 001</td>
        </tr>
        <tr>
            <td class="header-title" style="font-size:11px;">FORMATO DE INSPECCION LOCATIVA</td>
            <td class="header-code">Fecha: <?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td>
        </tr>
    </table>

    <!-- TITULO -->
    <div class="main-title">INSPECCION DE CONDICIONES LOCATIVAS</div>

    <!-- DATOS GENERALES -->
    <table class="info-table">
        <tr>
            <td class="info-label">CLIENTE:</td>
            <td><?= esc($cliente['nombre_cliente'] ?? '') ?></td>
            <td class="info-label">FECHA:</td>
            <td><?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td>
        </tr>
        <tr>
            <td class="info-label">CONSULTOR:</td>
            <td colspan="3"><?= esc($consultor['nombre_consultor'] ?? '') ?></td>
        </tr>
    </table>

    <!-- TEXTO INTRODUCTORIO -->
    <div class="section-title">INTRODUCCION</div>
    <p class="intro-text">
        Las inspecciones locativas en el ambito de la propiedad horizontal desempenan un papel critico en la gestion integral de la seguridad y la salud en el trabajo (SST), asi como en la proteccion del bienestar de todos los residentes y proveedores que interactuan con estas instalaciones. Cycloid Talent SAS, como experto en la gestion de talento y SST, destaca la importancia de estas inspecciones como una herramienta fundamental para mantener la seguridad de todos los actores involucrados. Estas inspecciones permiten una identificacion proactiva y una mitigacion oportuna de riesgos que, si no son gestionados adecuadamente, podrian derivar en incidentes que comprometan tanto la integridad fisica de las personas como la infraestructura misma.
    </p>
    <p class="intro-text">
        El analisis sistematico de los elementos estructurales y operativos de las propiedades horizontales facilita la adopcion de estrategias preventivas que minimicen el impacto de posibles peligros, convirtiendo a las inspecciones en un componente esencial de la gestion de riesgos y en un requisito indispensable para la sostenibilidad del entorno construido. Ademas, este proceso contribuye a la mejora continua del ambiente laboral, impulsando la implementacion de practicas seguras y garantizando el cumplimiento de las normativas vigentes en materia de SST, lo cual es crucial para la reduccion de accidentes y enfermedades laborales.
    </p>

    <div class="section-title">IDENTIFICACION DE RIESGOS COMUNES</div>
    <p class="intro-text">
        La identificacion de riesgos en la propiedad horizontal revela una amplia variedad de peligros que requieren una intervencion rigurosa y tecnica. Entre los hallazgos mas frecuentes se encuentran pisos rotos, cables electricos sueltos, vidrios peligrosos, y otros elementos que se encuentran deteriorados o mal instalados. Estos elementos constituyen amenazas significativas para la seguridad de residentes y trabajadores. Cycloid Talent SAS hace enfasis en la importancia de abordar estos riesgos de manera estructurada y tecnica, con el fin de garantizar la seguridad integral de todas las personas que habitan o trabajan en la propiedad.
    </p>

    <div class="section-title">ENFOQUE PREVENTIVO EN SST</div>
    <p class="intro-text">
        Desde la perspectiva de la gestion en SST, resulta fundamental adoptar un enfoque preventivo, con inspecciones regulares y sistematicas orientadas a la identificacion, evaluacion y control de riesgos antes de que estos se materialicen en danos. Dichas inspecciones deben llevarse a cabo mediante una metodologia rigurosa que permita no solo el levantamiento detallado de los riesgos presentes, sino tambien la priorizacion de estos en funcion de su severidad y probabilidad de ocurrencia. Ademas, es esencial involucrar a personal capacitado en la realizacion de estas inspecciones, lo cual garantiza un analisis tecnico adecuado y recomendaciones precisas para la mitigacion de riesgos.
    </p>
    <p class="intro-text">
        La implementacion de medidas correctivas debe incluir la reparacion inmediata de elementos deteriorados, la senalizacion adecuada de areas peligrosas, y la ejecucion de acciones de mantenimiento preventivo que garanticen la funcionalidad y seguridad de las instalaciones. Estas acciones preventivas no solo aseguran el bienestar de los residentes y proveedores, sino que tambien contribuyen a la optimizacion de los recursos al reducir costos asociados a indemnizaciones, responsabilidades legales, y deterioro de la reputacion de la administracion.
    </p>
    <p class="intro-text">
        El enfoque preventivo en SST tambien implica la capacitacion continua de los trabajadores y la sensibilizacion de los residentes en temas de seguridad. La formacion en el reconocimiento de riesgos y en el reporte oportuno de condiciones peligrosas es una herramienta poderosa para la prevencion de accidentes. La educacion de los residentes y el personal de servicio respecto a practicas seguras no solo mejora la capacidad de respuesta ante emergencias, sino que tambien fomenta una actitud proactiva hacia la seguridad, haciendo de cada individuo un agente activo en la prevencion de riesgos.
    </p>

    <!-- HALLAZGOS -->
    <div class="section-title">HALLAZGOS DE LA INSPECCION</div>

    <?php if (!empty($hallazgos)): ?>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:5%;">#</th>
                <th style="width:30%;">DESCRIPCION</th>
                <th style="width:25%;">IMAGEN HALLAZGO</th>
                <th style="width:25%;">IMAGEN CORRECCION</th>
                <th style="width:15%;">ESTADO</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($hallazgos as $i => $h): ?>
            <tr>
                <td style="text-align:center;"><?= $i + 1 ?></td>
                <td>
                    <?= esc($h['descripcion']) ?>
                    <?php if (!empty($h['observaciones'])): ?>
                        <br><em style="font-size:8px; color:#666;">Obs: <?= esc($h['observaciones']) ?></em>
                    <?php endif; ?>
                </td>
                <td style="text-align:center; padding:4px;">
                    <?php if (!empty($h['imagen_base64'])): ?>
                        <img src="<?= $h['imagen_base64'] ?>" class="hallazgo-img">
                        <?php if (!empty($h['fecha_hallazgo'])): ?>
                            <br><small style="font-size:7px;"><?= date('d/m/Y', strtotime($h['fecha_hallazgo'])) ?></small>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="empty-text">Sin foto</span>
                    <?php endif; ?>
                </td>
                <td style="text-align:center; padding:4px;">
                    <?php if (!empty($h['correccion_base64'])): ?>
                        <img src="<?= $h['correccion_base64'] ?>" class="hallazgo-img">
                        <?php if (!empty($h['fecha_correccion'])): ?>
                            <br><small style="font-size:7px;"><?= date('d/m/Y', strtotime($h['fecha_correccion'])) ?></small>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="empty-text">-</span>
                    <?php endif; ?>
                </td>
                <td style="text-align:center;">
                    <?php
                    $estadoClass = 'estado-abierto';
                    if ($h['estado'] === 'CERRADO') $estadoClass = 'estado-cerrado';
                    elseif (strpos($h['estado'], 'EXCEDIDO') !== false) $estadoClass = 'estado-excedido';
                    ?>
                    <span class="hallazgo-estado <?= $estadoClass ?>"><?= esc($h['estado']) ?></span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p class="empty-text">No se registraron hallazgos en esta inspeccion.</p>
    <?php endif; ?>

    <!-- OBSERVACIONES -->
    <?php if (!empty($inspeccion['observaciones'])): ?>
    <div class="section-title">OBSERVACIONES GENERALES</div>
    <p class="content-text"><?= nl2br(esc($inspeccion['observaciones'])) ?></p>
    <?php endif; ?>

</body>
</html>
