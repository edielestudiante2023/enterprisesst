<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 100px 70px 80px 90px; }
        body { margin: 0; padding: 0; font-family: DejaVu Sans, Arial, sans-serif; font-size: 10pt; line-height: 1.15; color: #333; }
        p, h1, h2, h3, h4, h5, h6, table, div { margin: 0; padding: 0; }
        *, *::before, *::after { box-sizing: border-box; }

        .seccion { margin-bottom: 8px; }
        .seccion-titulo { font-size: 11pt; font-weight: bold; color: #0d6efd; border-bottom: 1px solid #e9ecef; padding-bottom: 3px; margin-bottom: 5px; margin-top: 8px; }
        .seccion-contenido { text-align: justify; line-height: 1.2; }
        .seccion-contenido p { margin: 3px 0; }

        table.tabla-contenido { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 9pt; }
        table.tabla-contenido th, table.tabla-contenido td { border: 1px solid #999; padding: 5px 8px; vertical-align: top; }
        table.tabla-contenido th { background-color: #0d6efd; color: white; font-weight: bold; text-align: center; }

        table.datos-general { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 9pt; }
        table.datos-general td { border: 1px solid #999; padding: 5px 8px; }
        .datos-label { font-weight: bold; width: 22%; background:#f8f9fa; }

        .firma-img { max-width: 110px; max-height: 50px; border-bottom: 1px solid #999; }
        .empty-text { color: #888; font-style: italic; font-size: 9pt; }
        .pendiente-text { color:#d97706; font-style:italic; font-size:9pt; }

        .pie-documento { margin-top: 15px; padding-top: 8px; border-top: 1px solid #ccc; text-align: center; font-size: 8pt; color: #666; }
    </style>
</head>
<body>

    <!-- ENCABEZADO -->
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
                    SISTEMA DE GESTIÓN DE SEGURIDAD Y SALUD EN EL TRABAJO
                </div>
            </td>
            <td rowspan="2" style="width:130px; border:1px solid #333; padding:0; vertical-align:middle;">
                <table style="width:100%; border-collapse:collapse;" cellpadding="0" cellspacing="0">
                    <tr><td style="border-bottom:1px solid #333; padding:3px 6px; font-size:8pt;"><span style="font-weight:bold;">Código:</span> FT-SST-233</td></tr>
                    <tr><td style="border-bottom:1px solid #333; padding:3px 6px; font-size:8pt;"><span style="font-weight:bold;">Versión:</span> 001</td></tr>
                    <tr><td style="padding:3px 6px; font-size:8pt;"><span style="font-weight:bold;">Vigencia:</span> <?= date('d/m/Y', strtotime($lista['fecha_actividad'])) ?></td></tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="border:1px solid #333; text-align:center; padding:6px 10px; vertical-align:middle;">
                <div style="font-size:10pt; font-weight:bold; color:#333;">
                    LISTA DE ASISTENCIA
                </div>
            </td>
        </tr>
    </table>

    <!-- DATOS GENERALES -->
    <table class="datos-general">
        <tr>
            <td class="datos-label">CLIENTE:</td>
            <td colspan="3"><?= esc($cliente['nombre_cliente'] ?? '') ?></td>
        </tr>
        <tr>
            <td class="datos-label">MOTIVO:</td>
            <td colspan="3"><strong><?= esc($lista['motivo']) ?></strong></td>
        </tr>
        <tr>
            <td class="datos-label">FECHA:</td>
            <td><?= date('d/m/Y', strtotime($lista['fecha_actividad'])) ?></td>
            <td class="datos-label">MODALIDAD:</td>
            <td><?= ucfirst($lista['modalidad']) ?></td>
        </tr>
        <?php if (!empty($lista['hora_inicio']) || !empty($lista['hora_fin'])): ?>
        <tr>
            <td class="datos-label">HORA INICIO:</td>
            <td><?= !empty($lista['hora_inicio']) ? date('g:i A', strtotime($lista['hora_inicio'])) : '-' ?></td>
            <td class="datos-label">HORA FIN:</td>
            <td><?= !empty($lista['hora_fin']) ? date('g:i A', strtotime($lista['hora_fin'])) : '-' ?></td>
        </tr>
        <?php endif; ?>
        <?php if (!empty($lista['convocada_por']) || !empty($lista['lugar'])): ?>
        <tr>
            <td class="datos-label">CONVOCADA POR:</td>
            <td><?= esc($lista['convocada_por'] ?? '-') ?></td>
            <td class="datos-label">LUGAR:</td>
            <td><?= esc($lista['lugar'] ?? '-') ?></td>
        </tr>
        <?php endif; ?>
        <tr>
            <td class="datos-label"><?= !empty($realizadoPor) ? 'REGISTRADA POR:' : 'CONSULTOR:' ?></td>
            <td colspan="3">
                <?php if (!empty($realizadoPor)): ?>
                    <?= esc($realizadoPor) ?> (Comité)
                <?php elseif (!empty($consultor)): ?>
                    <?= esc($consultor['nombre_consultor'] ?? '') ?>
                <?php else: ?>-<?php endif; ?>
            </td>
        </tr>
        <?php if (!empty($lista['enlace_grabacion'])): ?>
        <tr>
            <td class="datos-label">GRABACIÓN:</td>
            <td colspan="3" style="word-break:break-all; font-size:8pt;"><?= esc($lista['enlace_grabacion']) ?></td>
        </tr>
        <?php endif; ?>
    </table>

    <!-- AGENDA -->
    <?php if (!empty($lista['agenda'])): ?>
    <div class="seccion">
        <div class="seccion-titulo">AGENDA / ORDEN DEL DÍA</div>
        <div class="seccion-contenido"><p><?= nl2br(esc($lista['agenda'])) ?></p></div>
    </div>
    <?php endif; ?>

    <!-- ASISTENTES Y FIRMAS -->
    <div class="seccion">
        <div class="seccion-titulo">REGISTRO DE ASISTENCIA Y FIRMAS</div>
        <?php if (!empty($asistentes)): ?>
        <table class="tabla-contenido">
            <thead>
                <tr>
                    <th style="width:4%;">#</th>
                    <th style="width:28%;">NOMBRE COMPLETO</th>
                    <th style="width:18%;">DOCUMENTO</th>
                    <th style="width:18%;">CARGO</th>
                    <th style="width:32%;">FIRMA</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($asistentes as $i => $a): ?>
                <tr>
                    <td style="text-align:center; font-weight:bold;"><?= $i + 1 ?></td>
                    <td><?= esc($a['nombre_completo']) ?></td>
                    <td><?= esc($a['tipo_documento'] ?? '') ?> <?= esc($a['numero_documento'] ?? '') ?></td>
                    <td>
                        <?= esc($a['cargo'] ?? '-') ?>
                        <?php if (!empty($a['area_dependencia'])): ?>
                            <br><small style="font-size:8pt; color:#666;"><?= esc($a['area_dependencia']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center; vertical-align:middle;">
                        <?php if (!empty($a['firma_base64'])): ?>
                            <img src="<?= $a['firma_base64'] ?>" class="firma-img">
                            <div style="font-size:7pt; color:#666;"><?= !empty($a['firmado_at']) ? date('d/m/Y H:i', strtotime($a['firmado_at'])) : '' ?></div>
                        <?php else: ?>
                            <span class="pendiente-text">Sin firma</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p class="empty-text">Sin asistentes registrados.</p>
        <?php endif; ?>
    </div>

    <!-- OBSERVACIONES -->
    <?php if (!empty($lista['observaciones'])): ?>
    <div class="seccion">
        <div class="seccion-titulo">OBSERVACIONES</div>
        <div class="seccion-contenido"><p><?= nl2br(esc($lista['observaciones'])) ?></p></div>
    </div>
    <?php endif; ?>

    <div class="pie-documento">
        <p>Documento generado por EnterpriseSST &middot; <?= date('d/m/Y H:i') ?></p>
    </div>
</body>
</html>
