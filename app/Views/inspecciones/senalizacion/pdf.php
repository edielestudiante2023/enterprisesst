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

        .header-table { width: 100%; border-collapse: collapse; border: 1.5px solid #333; margin-bottom: 12px; }
        .header-table td { border: 1px solid #333; padding: 5px 8px; vertical-align: middle; }
        .header-logo { width: 110px; text-align: center; font-size: 9px; }
        .header-logo img { max-width: 95px; max-height: 55px; }
        .header-title { text-align: center; font-weight: bold; font-size: 10px; }
        .header-code { width: 130px; font-size: 9px; }

        .main-title { text-align: center; font-size: 12px; font-weight: bold; margin: 10px 0 8px; color: #1c2437; }

        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; border: 1px solid #ccc; }
        .info-table td { padding: 4px 8px; font-size: 10px; border: 1px solid #ccc; }
        .info-label { font-weight: bold; color: #444; width: 100px; background: #f7f7f7; }

        .section-title { background: #1c2437; color: white; padding: 4px 10px; font-weight: bold; font-size: 10px; margin: 10px 0 5px; }

        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .data-table th { background: #e8e8e8; border: 1px solid #aaa; padding: 4px 6px; font-size: 9px; text-align: left; }
        .data-table td { border: 1px solid #ccc; padding: 3px 6px; font-size: 9px; vertical-align: top; }

        .content-text { font-size: 10px; line-height: 1.5; margin-bottom: 6px; }
        .empty-text { color: #888; font-style: italic; font-size: 9px; margin-bottom: 6px; }

        .item-img { max-width: 120px; max-height: 90px; border: 1px solid #ccc; }

        .estado-badge { padding: 2px 6px; font-size: 8px; font-weight: bold; }
        .estado-na { background: #e2e3e5; color: #383d41; }
        .estado-nc { background: #f8d7da; color: #721c24; }
        .estado-cp { background: #fff3cd; color: #856404; }
        .estado-ct { background: #d4edda; color: #155724; }

        .resumen-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .resumen-table td { border: 1px solid #ccc; padding: 6px 10px; font-size: 10px; }
        .resumen-label { font-weight: bold; background: #f7f7f7; width: 200px; }
        .intro-text { font-size: 9px; line-height: 1.5; margin-bottom: 8px; text-align: justify; }

        .grupo-header td { background: #3a3f50; color: white; font-weight: bold; font-size: 9px; padding: 4px 8px; }
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
            <td class="header-code">Codigo: FT-SST-224<br>Version: 001</td>
        </tr>
        <tr>
            <td class="header-title" style="font-size:11px;">FORMATO DE INSPECCION DE SENALIZACION</td>
            <td class="header-code">Fecha: <?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td>
        </tr>
    </table>

    <div class="main-title">INSPECCION DE SENALIZACION</div>

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

    <div class="section-title">INTRODUCCION</div>
    <p class="intro-text">
        En cumplimiento de los lineamientos establecidos por el Sistema de Gestion de Seguridad y Salud en el Trabajo (SG-SST) y con base en la normativa vigente en Colombia, se realizo una inspeccion tecnica de senalizacion en las instalaciones de <?= esc($cliente['nombre_cliente'] ?? '') ?>. Esta actividad hace parte de las acciones planificadas para la identificacion de condiciones inseguras y la verificacion del cumplimiento de los requisitos minimos de seguridad en infraestructura, enmarcados en el Decreto 1072 de 2015 y la Resolucion 0312 de 2019.
    </p>
    <p class="intro-text">
        El proposito de la inspeccion fue evaluar la adecuacion, visibilidad y estado de los elementos de senalizacion preventiva, informativa y de emergencia en las zonas comunes, considerando su impacto directo en la prevencion de accidentes, la orientacion de residentes y visitantes, y la adecuada respuesta ante situaciones de emergencia.
    </p>
    <p class="intro-text">
        Esta labor fue liderada por Cycloid Talent SAS, siguiendo criterios tecnicos definidos en el Formato FT-SST-224 - Version 001, con el fin de garantizar la trazabilidad del proceso y la mejora continua del sistema de gestion.
    </p>

    <div class="section-title">JUSTIFICACION</div>
    <p class="intro-text">
        La senalizacion adecuada en espacios residenciales y empresariales no es unicamente un requerimiento normativo, sino una herramienta esencial en la prevencion de riesgos y en la proteccion de la vida e integridad de las personas. Segun el Decreto 1072 de 2015 y sus normas complementarias, las organizaciones deben implementar medidas que garanticen ambientes de trabajo y convivencia seguros, entre ellas, la correcta senalizacion de zonas de riesgo y equipos de emergencia.
    </p>
    <p class="intro-text">
        Este informe tecnico permite establecer un diagnostico claro del estado actual de la senalizacion, identificando fortalezas y oportunidades de mejora, para que la administracion de <?= esc($cliente['nombre_cliente'] ?? '') ?> implemente planes de accion correctivos, preventivos o de mejora que aseguren el cumplimiento de los estandares minimos en SST.
    </p>

    <div class="section-title">HALLAZGOS DE LA INSPECCION</div>

    <?php if (!empty($itemsGrouped)): ?>
    <table class="data-table">
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

    <div class="section-title">RESUMEN DE CALIFICACION</div>
    <table class="resumen-table">
        <tr>
            <td class="resumen-label">Items que No Aplican</td>
            <td style="text-align:center;"><?= $inspeccion['conteo_no_aplica'] ?? 0 ?></td>
        </tr>
        <tr>
            <td class="resumen-label">Items que No Cumplen</td>
            <td style="text-align:center;"><?= $inspeccion['conteo_no_cumple'] ?? 0 ?></td>
        </tr>
        <tr>
            <td class="resumen-label">Items que Cumplen Parcialmente</td>
            <td style="text-align:center;"><?= $inspeccion['conteo_parcial'] ?? 0 ?></td>
        </tr>
        <tr>
            <td class="resumen-label">Items que Cumplen Totalmente</td>
            <td style="text-align:center;"><?= $inspeccion['conteo_total'] ?? 0 ?></td>
        </tr>
        <tr>
            <td class="resumen-label" style="font-size:11px;">CALIFICACION</td>
            <td style="text-align:center; font-size:14px; font-weight:bold;">
                <?= number_format($inspeccion['calificacion'] ?? 0, 1) ?>%
            </td>
        </tr>
        <tr>
            <td class="resumen-label">Descripcion de la Calificacion</td>
            <td><?= esc($inspeccion['descripcion_cualitativa'] ?? '') ?></td>
        </tr>
    </table>

    <?php if (!empty($inspeccion['observaciones'])): ?>
    <div class="section-title">OBSERVACIONES GENERALES</div>
    <p class="content-text"><?= nl2br(esc($inspeccion['observaciones'])) ?></p>
    <?php endif; ?>

</body>
</html>
