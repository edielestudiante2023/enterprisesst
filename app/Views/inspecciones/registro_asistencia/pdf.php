<?php
$codigoPdf = 'FT-SST-006';
$tituloPdf = 'REGISTRO DE ASISTENCIA';
$tipoLabel = $tiposReunion[$inspeccion['tipo_reunion'] ?? ''] ?? $inspeccion['tipo_reunion'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 80px 50px 60px 50px; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 9px; color: #333; line-height: 1.3; padding: 10px 15px; }

        .header-table { width: 100%; border-collapse: collapse; border: 1.5px solid #333; margin-bottom: 10px; }
        .header-table td { border: 1px solid #333; padding: 4px 6px; vertical-align: middle; }
        .header-logo { width: 100px; text-align: center; font-size: 8px; }
        .header-logo img { max-width: 85px; max-height: 50px; }
        .header-title { text-align: center; font-weight: bold; font-size: 9px; }
        .header-code { width: 120px; font-size: 8px; }

        .main-title { text-align: center; font-size: 11px; font-weight: bold; margin: 8px 0 4px; color: #1c2437; }
        .main-subtitle { text-align: center; font-size: 9px; font-weight: bold; margin: 0 0 6px; color: #444; }

        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; border: 1px solid #ccc; }
        .info-table td { padding: 3px 6px; font-size: 9px; border: 1px solid #ccc; }
        .info-label { font-weight: bold; color: #444; width: 130px; background: #f7f7f7; }

        .section-title { background: #1c2437; color: white; padding: 3px 8px; font-weight: bold; font-size: 9px; margin: 8px 0 4px; }

        .asist-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; border: 1px solid #ccc; }
        .asist-table th { background: #e8e8e8; padding: 4px 6px; font-size: 8px; border: 1px solid #ccc; text-align: left; }
        .asist-table td { padding: 3px 6px; font-size: 8px; border: 1px solid #ccc; }

        .content-text { font-size: 9px; line-height: 1.4; margin-bottom: 5px; }

        .firma-img { max-width: 80px; max-height: 40px; }
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
                    <strong style="font-size:7px;"><?= esc($cliente['nombre_cliente'] ?? '') ?></strong>
                <?php endif; ?>
            </td>
            <td class="header-title">SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO</td>
            <td class="header-code">Codigo: <?= $codigoPdf ?><br>Version: V001</td>
        </tr>
        <tr>
            <td class="header-title" style="font-size:10px;"><?= $tituloPdf ?></td>
            <td class="header-code">Fecha: <?= date('d/m/Y', strtotime($inspeccion['fecha_sesion'])) ?></td>
        </tr>
    </table>

    <!-- TITULO -->
    <div class="main-title"><?= $tituloPdf ?></div>
    <div class="main-subtitle"><?= esc($cliente['nombre_cliente'] ?? '') ?></div>

    <!-- DATOS DE LA SESION -->
    <div class="section-title">DATOS DE LA SESION</div>
    <table class="info-table">
        <tr>
            <td class="info-label">TEMA:</td>
            <td colspan="3"><?= esc($inspeccion['tema'] ?? '') ?></td>
        </tr>
        <tr>
            <td class="info-label">LUGAR:</td>
            <td><?= esc($inspeccion['lugar'] ?? '') ?></td>
            <td class="info-label">FECHA:</td>
            <td><?= date('d/m/Y', strtotime($inspeccion['fecha_sesion'])) ?></td>
        </tr>
        <tr>
            <td class="info-label">OBJETIVO:</td>
            <td colspan="3"><?= nl2br(esc($inspeccion['objetivo'] ?? '')) ?></td>
        </tr>
        <tr>
            <td class="info-label">MATERIAL:</td>
            <td><?= esc($inspeccion['material'] ?? '') ?></td>
            <td class="info-label">TIPO:</td>
            <td><?= esc($tipoLabel) ?></td>
        </tr>
        <tr>
            <td class="info-label">TIEMPO (HORAS):</td>
            <td><?= esc($inspeccion['tiempo_horas'] ?? '') ?></td>
            <td class="info-label">CAPACITADOR:</td>
            <td><?= esc($inspeccion['capacitador'] ?? '') ?></td>
        </tr>
    </table>

    <!-- LISTADO DE ASISTENTES -->
    <div class="section-title">LISTADO DE ASISTENTES</div>
    <table class="asist-table">
        <tr>
            <th style="width:5%; text-align:center;">#</th>
            <th style="width:30%;">NOMBRE</th>
            <th style="width:18%;">CEDULA</th>
            <th style="width:22%;">CARGO</th>
            <th style="width:25%; text-align:center;">FIRMA</th>
        </tr>
        <?php $num = 1; foreach ($asistentes as $a): ?>
        <tr>
            <td style="text-align:center;"><?= $num++ ?></td>
            <td><?= esc($a['nombre']) ?></td>
            <td><?= esc($a['cedula']) ?></td>
            <td><?= esc($a['cargo']) ?></td>
            <td style="text-align:center;">
                <?php if (!empty($a['firma_base64'])): ?>
                <img src="<?= $a['firma_base64'] ?>" class="firma-img">
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <!-- OBSERVACIONES -->
    <?php if (!empty($inspeccion['observaciones'])): ?>
    <div class="section-title">OBSERVACIONES</div>
    <p class="content-text"><?= nl2br(esc($inspeccion['observaciones'])) ?></p>
    <?php endif; ?>

</body>
</html>
