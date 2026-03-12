<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 100px 70px 80px 90px; }
        body { margin: 0; padding: 0; font-family: DejaVu Sans, Arial, sans-serif; font-size: 10pt; line-height: 1.15; color: #333; }
        p, h1, h2, h3, h4, h5, h6, table, div { margin: 0; padding: 0; }
        *, *::before, *::after { box-sizing: border-box; }
        br { line-height: 0.5; }

        .seccion { margin-bottom: 8px; }
        .seccion-titulo {
            font-size: 11pt; font-weight: bold; color: #0d6efd;
            border-bottom: 1px solid #e9ecef; padding-bottom: 3px;
            margin-bottom: 5px; margin-top: 8px;
        }
        .seccion-contenido { text-align: justify; line-height: 1.2; }
        .seccion-contenido p { margin: 3px 0; }

        table.tabla-contenido { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 9pt; }
        table.tabla-contenido th, table.tabla-contenido td { border: 1px solid #999; padding: 5px 8px; }
        table.tabla-contenido th { background-color: #0d6efd; color: white; font-weight: bold; text-align: center; }

        table.datos-general { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 9pt; }
        table.datos-general td { border: 1px solid #999; padding: 5px 8px; }
        .datos-label { font-weight: bold; width: 15%; }

        .hallazgo-img { max-width: 160px; max-height: 120px; border: 1px solid #ccc; }
        .empty-text { color: #888; font-style: italic; font-size: 9pt; }

        .hallazgo-estado { padding: 2px 6px; font-size: 8pt; font-weight: bold; }
        .estado-abierto { background: #fff3cd; color: #856404; }
        .estado-cerrado { background: #d4edda; color: #155724; }
        .estado-excedido { background: #f8d7da; color: #721c24; }

        .pie-documento { margin-top: 15px; padding-top: 8px; border-top: 1px solid #ccc; text-align: center; font-size: 8pt; color: #666; }
    </style>
</head>
<body>

    <!-- ENCABEZADO ESTANDAR -->
    <table style="width:100%; border-collapse:collapse; margin-bottom:20px;" cellpadding="0" cellspacing="0">
        <tr>
            <td rowspan="2" style="width:100px; border:1px solid #333; padding:8px; text-align:center; vertical-align:middle; background:#fff;">
                <?php if (!empty($logoBase64)): ?>
                    <img src="<?= $logoBase64 ?>" style="max-width:80px; max-height:50px;">
                <?php else: ?>
                    <div style="font-size:8pt; font-weight:bold;"><?= esc($cliente['nombre_cliente'] ?? '') ?></div>
                <?php endif; ?>
            </td>
            <td style="border:1px solid #333; text-align:center; padding:6px 10px; vertical-align:middle;">
                <div style="font-size:10pt; font-weight:bold; color:#333;">
                    SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO
                </div>
            </td>
            <td rowspan="2" style="width:130px; border:1px solid #333; padding:0; vertical-align:middle;">
                <table style="width:100%; border-collapse:collapse;" cellpadding="0" cellspacing="0">
                    <tr><td style="border-bottom:1px solid #333; padding:3px 6px; font-size:8pt;"><span style="font-weight:bold;">Codigo:</span> FT-SST-216</td></tr>
                    <tr><td style="border-bottom:1px solid #333; padding:3px 6px; font-size:8pt;"><span style="font-weight:bold;">Version:</span> 001</td></tr>
                    <tr><td style="padding:3px 6px; font-size:8pt;"><span style="font-weight:bold;">Vigencia:</span> <?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td></tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="border:1px solid #333; text-align:center; padding:6px 10px; vertical-align:middle;">
                <div style="font-size:10pt; font-weight:bold; color:#333;">
                    FORMATO DE INSPECCION LOCATIVA
                </div>
            </td>
        </tr>
    </table>

    <!-- DATOS GENERALES -->
    <table class="datos-general">
        <tr>
            <td class="datos-label">CLIENTE:</td>
            <td><?= esc($cliente['nombre_cliente'] ?? '') ?></td>
            <td class="datos-label">FECHA:</td>
            <td><?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td>
        </tr>
        <tr>
            <td class="datos-label">CONSULTOR:</td>
            <td colspan="3"><?= esc($consultor['nombre_consultor'] ?? '') ?></td>
        </tr>
    </table>

    <!-- INTRODUCCION -->
    <div class="seccion">
        <div class="seccion-titulo">INTRODUCCION</div>
        <div class="seccion-contenido">
            <p>Las inspecciones locativas en el ambito de la propiedad horizontal desempenan un papel critico en la gestion integral de la seguridad y la salud en el trabajo (SST), asi como en la proteccion del bienestar de todos los residentes y proveedores que interactuan con estas instalaciones. Cycloid Talent SAS, como experto en la gestion de talento y SST, destaca la importancia de estas inspecciones como una herramienta fundamental para mantener la seguridad de todos los actores involucrados. Estas inspecciones permiten una identificacion proactiva y una mitigacion oportuna de riesgos que, si no son gestionados adecuadamente, podrian derivar en incidentes que comprometan tanto la integridad fisica de las personas como la infraestructura misma.</p>
            <p>El analisis sistematico de los elementos estructurales y operativos de las propiedades horizontales facilita la adopcion de estrategias preventivas que minimicen el impacto de posibles peligros, convirtiendo a las inspecciones en un componente esencial de la gestion de riesgos y en un requisito indispensable para la sostenibilidad del entorno construido. Ademas, este proceso contribuye a la mejora continua del ambiente laboral, impulsando la implementacion de practicas seguras y garantizando el cumplimiento de las normativas vigentes en materia de SST, lo cual es crucial para la reduccion de accidentes y enfermedades laborales.</p>
        </div>
    </div>

    <div class="seccion">
        <div class="seccion-titulo">IDENTIFICACION DE RIESGOS COMUNES</div>
        <div class="seccion-contenido">
            <p>La identificacion de riesgos en la propiedad horizontal revela una amplia variedad de peligros que requieren una intervencion rigurosa y tecnica. Entre los hallazgos mas frecuentes se encuentran pisos rotos, cables electricos sueltos, vidrios peligrosos, y otros elementos que se encuentran deteriorados o mal instalados. Estos elementos constituyen amenazas significativas para la seguridad de residentes y trabajadores. Cycloid Talent SAS hace enfasis en la importancia de abordar estos riesgos de manera estructurada y tecnica, con el fin de garantizar la seguridad integral de todas las personas que habitan o trabajan en la propiedad.</p>
        </div>
    </div>

    <div class="seccion">
        <div class="seccion-titulo">ENFOQUE PREVENTIVO EN SST</div>
        <div class="seccion-contenido">
            <p>Desde la perspectiva de la gestion en SST, resulta fundamental adoptar un enfoque preventivo, con inspecciones regulares y sistematicas orientadas a la identificacion, evaluacion y control de riesgos antes de que estos se materialicen en danos. Dichas inspecciones deben llevarse a cabo mediante una metodologia rigurosa que permita no solo el levantamiento detallado de los riesgos presentes, sino tambien la priorizacion de estos en funcion de su severidad y probabilidad de ocurrencia. Ademas, es esencial involucrar a personal capacitado en la realizacion de estas inspecciones, lo cual garantiza un analisis tecnico adecuado y recomendaciones precisas para la mitigacion de riesgos.</p>
            <p>La implementacion de medidas correctivas debe incluir la reparacion inmediata de elementos deteriorados, la senalizacion adecuada de areas peligrosas, y la ejecucion de acciones de mantenimiento preventivo que garanticen la funcionalidad y seguridad de las instalaciones. Estas acciones preventivas no solo aseguran el bienestar de los residentes y proveedores, sino que tambien contribuyen a la optimizacion de los recursos al reducir costos asociados a indemnizaciones, responsabilidades legales, y deterioro de la reputacion de la administracion.</p>
            <p>El enfoque preventivo en SST tambien implica la capacitacion continua de los trabajadores y la sensibilizacion de los residentes en temas de seguridad. La formacion en el reconocimiento de riesgos y en el reporte oportuno de condiciones peligrosas es una herramienta poderosa para la prevencion de accidentes. La educacion de los residentes y el personal de servicio respecto a practicas seguras no solo mejora la capacidad de respuesta ante emergencias, sino que tambien fomenta una actitud proactiva hacia la seguridad, haciendo de cada individuo un agente activo en la prevencion de riesgos.</p>
        </div>
    </div>

    <!-- HALLAZGOS -->
    <div class="seccion">
        <div class="seccion-titulo">HALLAZGOS DE LA INSPECCION</div>
        <?php if (!empty($hallazgos)): ?>
        <table class="tabla-contenido">
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
                            <br><em style="font-size:8pt; color:#666;">Obs: <?= esc($h['observaciones']) ?></em>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center; padding:4px;">
                        <?php if (!empty($h['imagen_base64'])): ?>
                            <img src="<?= $h['imagen_base64'] ?>" class="hallazgo-img">
                            <?php if (!empty($h['fecha_hallazgo'])): ?>
                                <br><small style="font-size:7pt;"><?= date('d/m/Y', strtotime($h['fecha_hallazgo'])) ?></small>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="empty-text">Sin foto</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center; padding:4px;">
                        <?php if (!empty($h['correccion_base64'])): ?>
                            <img src="<?= $h['correccion_base64'] ?>" class="hallazgo-img">
                            <?php if (!empty($h['fecha_correccion'])): ?>
                                <br><small style="font-size:7pt;"><?= date('d/m/Y', strtotime($h['fecha_correccion'])) ?></small>
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
    </div>

    <!-- OBSERVACIONES -->
    <?php if (!empty($inspeccion['observaciones'])): ?>
    <div class="seccion">
        <div class="seccion-titulo">OBSERVACIONES GENERALES</div>
        <div class="seccion-contenido"><?= nl2br(esc($inspeccion['observaciones'])) ?></div>
    </div>
    <?php endif; ?>

    <!-- PIE DE DOCUMENTO -->
    <div class="pie-documento">
        <p>Documento generado el <?= date('d/m/Y') ?> - Sistema de Gestion SST</p>
        <p><?= esc($cliente['nombre_cliente'] ?? '') ?> - NIT: <?= esc($cliente['nit_cliente'] ?? '') ?></p>
    </div>

</body>
</html>
