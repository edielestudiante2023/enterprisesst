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

        .item-img { max-width: 120px; max-height: 90px; border: 1px solid #ccc; }
        .empty-text { color: #888; font-style: italic; font-size: 9pt; }

        .estado-badge { padding: 2px 6px; font-size: 8pt; font-weight: bold; }
        .estado-na { background: #e2e3e5; color: #383d41; }
        .estado-nc { background: #f8d7da; color: #721c24; }
        .estado-cp { background: #fff3cd; color: #856404; }
        .estado-ct { background: #d4edda; color: #155724; }

        .grupo-header td { background-color: #e9ecef; color: #333; font-weight: bold; font-size: 9pt; padding: 4px 8px; border: 1px solid #999; }

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
                    <tr><td style="border-bottom:1px solid #333; padding:3px 6px; font-size:8pt;"><span style="font-weight:bold;">Codigo:</span> FT-SST-224</td></tr>
                    <tr><td style="border-bottom:1px solid #333; padding:3px 6px; font-size:8pt;"><span style="font-weight:bold;">Version:</span> 001</td></tr>
                    <tr><td style="padding:3px 6px; font-size:8pt;"><span style="font-weight:bold;">Vigencia:</span> <?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td></tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="border:1px solid #333; text-align:center; padding:6px 10px; vertical-align:middle;">
                <div style="font-size:10pt; font-weight:bold; color:#333;">
                    FORMATO DE INSPECCION DE SENALIZACION
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
            <p>En cumplimiento de los lineamientos establecidos por el Sistema de Gestion de Seguridad y Salud en el Trabajo (SG-SST) y con base en la normativa vigente en Colombia, se realizo una inspeccion tecnica de senalizacion en las instalaciones de <?= esc($cliente['nombre_cliente'] ?? '') ?>. Esta actividad hace parte de las acciones planificadas para la identificacion de condiciones inseguras y la verificacion del cumplimiento de los requisitos minimos de seguridad en infraestructura, enmarcados en el Decreto 1072 de 2015 y la Resolucion 0312 de 2019.</p>
            <p>El proposito de la inspeccion fue evaluar la adecuacion, visibilidad y estado de los elementos de senalizacion preventiva, informativa y de emergencia en las areas de trabajo y zonas comunes, considerando su impacto directo en la prevencion de accidentes, la orientacion de trabajadores y visitantes, y la adecuada respuesta ante situaciones de emergencia.</p>
            <p>La inspeccion se realizo siguiendo criterios tecnicos definidos en el Formato FT-SST-224 - Version 001, con el fin de garantizar la trazabilidad del proceso y la mejora continua del sistema de gestion.</p>
        </div>
    </div>

    <div class="seccion">
        <div class="seccion-titulo">JUSTIFICACION</div>
        <div class="seccion-contenido">
            <p>La senalizacion adecuada en los lugares de trabajo no es unicamente un requerimiento normativo, sino una herramienta esencial en la prevencion de riesgos y en la proteccion de la vida e integridad de las personas. Segun el Decreto 1072 de 2015 y sus normas complementarias, las organizaciones deben implementar medidas que garanticen ambientes de trabajo seguros, entre ellas, la correcta senalizacion de zonas de riesgo y equipos de emergencia.</p>
            <p>Este informe tecnico permite establecer un diagnostico claro del estado actual de la senalizacion, identificando fortalezas y oportunidades de mejora, para que <?= esc($cliente['nombre_cliente'] ?? '') ?> implemente planes de accion correctivos, preventivos o de mejora que aseguren el cumplimiento de los estandares minimos en SST.</p>
        </div>
    </div>

    <!-- HALLAZGOS -->
    <div class="seccion">
        <div class="seccion-titulo">HALLAZGOS DE LA INSPECCION</div>

        <?php if (!empty($itemsGrouped)): ?>
        <table class="tabla-contenido">
            <thead>
                <tr>
                    <th style="width:5%;">#</th>
                    <th style="width:30%;">ITEM</th>
                    <th style="width:25%;">ESTADO</th>
                    <th style="width:40%;">EVIDENCIA</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $counter = 0;
                foreach ($itemsGrouped as $grupo => $groupItems):
                ?>
                <tr class="grupo-header">
                    <td colspan="4"><?= esc($grupo) ?> (<?= count($groupItems) ?> items)</td>
                </tr>
                <?php foreach ($groupItems as $item):
                    $counter++;
                    $estadoClass = 'estado-nc';
                    if ($item['estado_cumplimiento'] === 'NO APLICA') $estadoClass = 'estado-na';
                    elseif ($item['estado_cumplimiento'] === 'CUMPLE PARCIALMENTE') $estadoClass = 'estado-cp';
                    elseif ($item['estado_cumplimiento'] === 'CUMPLE TOTALMENTE') $estadoClass = 'estado-ct';
                ?>
                <tr>
                    <td style="text-align:center;"><?= $counter ?></td>
                    <td><?= esc($item['nombre_item']) ?></td>
                    <td style="text-align:center;">
                        <span class="estado-badge <?= $estadoClass ?>"><?= esc($item['estado_cumplimiento']) ?></span>
                    </td>
                    <td style="text-align:center; padding:4px;">
                        <?php if (!empty($item['foto_base64'])): ?>
                            <img src="<?= $item['foto_base64'] ?>" class="item-img">
                        <?php else: ?>
                            <span class="empty-text">Sin foto</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p class="empty-text">No se registraron items en esta inspeccion.</p>
        <?php endif; ?>
    </div>

    <!-- RESUMEN -->
    <div class="seccion">
        <div class="seccion-titulo">RESUMEN DE CALIFICACION</div>
        <table class="datos-general">
            <tr>
                <td class="datos-label">Items que No Aplican</td>
                <td style="text-align:center;"><?= $inspeccion['conteo_no_aplica'] ?? 0 ?></td>
            </tr>
            <tr>
                <td class="datos-label">Items que No Cumplen</td>
                <td style="text-align:center;"><?= $inspeccion['conteo_no_cumple'] ?? 0 ?></td>
            </tr>
            <tr>
                <td class="datos-label">Items que Cumplen Parcialmente</td>
                <td style="text-align:center;"><?= $inspeccion['conteo_parcial'] ?? 0 ?></td>
            </tr>
            <tr>
                <td class="datos-label">Items que Cumplen Totalmente</td>
                <td style="text-align:center;"><?= $inspeccion['conteo_total'] ?? 0 ?></td>
            </tr>
            <tr>
                <td class="datos-label" style="font-size:11pt;">CALIFICACION</td>
                <td style="text-align:center; font-size:14pt; font-weight:bold;">
                    <?= number_format($inspeccion['calificacion'] ?? 0, 1) ?>%
                </td>
            </tr>
            <tr>
                <td class="datos-label">Descripcion de la Calificacion</td>
                <td><?= esc($inspeccion['descripcion_cualitativa'] ?? '') ?></td>
            </tr>
        </table>
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
