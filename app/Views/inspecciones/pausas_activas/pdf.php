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

        .registro-img { max-width: 200px; max-height: 150px; border: 1px solid #ccc; }
        .empty-text { color: #888; font-style: italic; font-size: 9pt; }

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
                    SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO
                </div>
            </td>
            <td rowspan="2" style="width:130px; border:1px solid #333; padding:0; vertical-align:middle;">
                <table style="width:100%; border-collapse:collapse;" cellpadding="0" cellspacing="0">
                    <tr><td style="border-bottom:1px solid #333; padding:3px 6px; font-size:8pt;"><span style="font-weight:bold;">Codigo:</span> FT-SST-230</td></tr>
                    <tr><td style="border-bottom:1px solid #333; padding:3px 6px; font-size:8pt;"><span style="font-weight:bold;">Version:</span> 001</td></tr>
                    <tr><td style="padding:3px 6px; font-size:8pt;"><span style="font-weight:bold;">Vigencia:</span> <?= date('d/m/Y', strtotime($inspeccion['fecha_actividad'])) ?></td></tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="border:1px solid #333; text-align:center; padding:6px 10px; vertical-align:middle;">
                <div style="font-size:10pt; font-weight:bold; color:#333;">
                    REGISTRO DE PAUSAS ACTIVAS
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
            <td><?= date('d/m/Y', strtotime($inspeccion['fecha_actividad'])) ?></td>
        </tr>
        <tr>
            <td class="datos-label"><?= !empty($realizadoPor) ? 'REALIZADO POR:' : 'CONSULTOR:' ?></td>
            <td colspan="3"><?= !empty($realizadoPor) ? esc($realizadoPor) . ' (COPASST)' : esc($consultor['nombre_consultor'] ?? '') ?></td>
        </tr>
    </table>

    <!-- INTRODUCCION -->
    <div class="seccion">
        <div class="seccion-titulo">INTRODUCCION</div>
        <div class="seccion-contenido">
            <p>Las pausas activas son una estrategia de promocion y prevencion en salud en el trabajo, orientada a reducir el riesgo de enfermedades laborales asociadas a posturas prolongadas, movimientos repetitivos y fatiga fisica o mental. Su implementacion es parte de las obligaciones del empleador conforme al Decreto 1072 de 2015 y la Resolucion 0312 de 2019.</p>
            <p>Este registro documenta la realizacion de pausas activas con evidencia fotografica, contribuyendo al cumplimiento del programa de promocion y prevencion en salud del SG-SST.</p>
        </div>
    </div>

    <!-- REGISTROS -->
    <div class="seccion">
        <div class="seccion-titulo">REGISTROS DE PAUSAS ACTIVAS</div>
        <?php if (!empty($registros)): ?>
        <table class="tabla-contenido">
            <thead>
                <tr>
                    <th style="width:5%;">#</th>
                    <th style="width:35%;">TIPO DE PAUSA</th>
                    <th style="width:60%;">EVIDENCIA FOTOGRAFICA</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registros as $i => $r): ?>
                <tr>
                    <td style="text-align:center; font-weight:bold;"><?= $i + 1 ?></td>
                    <td><?= esc($r['tipo_pausa']) ?></td>
                    <td style="text-align:center;">
                        <?php if (!empty($r['imagen_base64'])): ?>
                            <img src="<?= $r['imagen_base64'] ?>" class="registro-img">
                        <?php else: ?>
                            <span class="empty-text">Sin foto</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p class="empty-text">No se registraron pausas activas.</p>
        <?php endif; ?>
    </div>

    <!-- OBSERVACIONES -->
    <?php if (!empty($inspeccion['observaciones'])): ?>
    <div class="seccion">
        <div class="seccion-titulo">OBSERVACIONES</div>
        <div class="seccion-contenido">
            <p><?= nl2br(esc($inspeccion['observaciones'])) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <div class="pie-documento">
        <p>Documento generado por EnterpriseSST - <?= date('d/m/Y H:i') ?></p>
    </div>

</body>
</html>
