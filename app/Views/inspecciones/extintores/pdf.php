<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 80px 50px 60px 50px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9px;
            color: #333;
            line-height: 1.3;
            padding: 10px 15px;
        }

        .header-table { width: 100%; border-collapse: collapse; border: 1.5px solid #333; margin-bottom: 10px; }
        .header-table td { border: 1px solid #333; padding: 4px 6px; vertical-align: middle; }
        .header-logo { width: 100px; text-align: center; font-size: 8px; }
        .header-logo img { max-width: 85px; max-height: 50px; }
        .header-title { text-align: center; font-weight: bold; font-size: 9px; }
        .header-code { width: 120px; font-size: 8px; }

        .main-title { text-align: center; font-size: 11px; font-weight: bold; margin: 8px 0 6px; color: #1c2437; }

        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; border: 1px solid #ccc; }
        .info-table td { padding: 3px 6px; font-size: 9px; border: 1px solid #ccc; }
        .info-label { font-weight: bold; color: #444; width: 140px; background: #f7f7f7; }

        .section-title { background: #1c2437; color: white; padding: 3px 8px; font-weight: bold; font-size: 9px; margin: 8px 0 4px; }

        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .data-table th { background: #e8e8e8; border: 1px solid #aaa; padding: 3px 4px; font-size: 7px; text-align: center; }
        .data-table td { border: 1px solid #ccc; padding: 2px 3px; font-size: 7px; text-align: center; vertical-align: middle; }

        .inv-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .inv-table td { border: 1px solid #ccc; padding: 3px 6px; font-size: 9px; }
        .inv-label { font-weight: bold; background: #f7f7f7; width: 200px; }

        .content-text { font-size: 9px; line-height: 1.4; margin-bottom: 5px; }
        .intro-text { font-size: 8px; line-height: 1.4; margin-bottom: 6px; text-align: justify; }
        .intro-subtitle { font-weight: bold; font-size: 8px; margin: 4px 0 2px; }
        .intro-list { font-size: 8px; margin: 0 0 4px 15px; }

        .ext-img { max-width: 80px; max-height: 60px; border: 1px solid #ccc; }
        .val-bueno { color: #155724; }
        .val-regular { color: #856404; }
        .val-malo { color: #721c24; }
        .val-na { color: #6c757d; }
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
            <td class="header-code">Codigo: FT-SST-201<br>Version: 001</td>
        </tr>
        <tr>
            <td class="header-title" style="font-size:10px;">FORMATO DE INSPECCION EXTINTORES</td>
            <td class="header-code">Fecha: <?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td>
        </tr>
    </table>

    <!-- TITULO -->
    <div class="main-title">INVENTARIO DE EQUIPOS DE EXTINCION<br><?= esc($cliente['nombre_cliente'] ?? '') ?></div>

    <!-- INTRODUCCION -->
    <div class="section-title">INTRODUCCION</div>
    <p class="intro-text">
        Los extintores portatiles contra incendios son un equipo esencial para la seguridad contra incendios. En caso de incendio, un extintor portatil puede ayudar a controlar o extinguir el fuego, lo que puede salvar vidas y proteger la propiedad. Sin embargo, para que un extintor funcione correctamente, debe inspeccionarse y mantenerse regularmente.
    </p>
    <p class="intro-subtitle">Que se debe revisar en una inspeccion de extintor?</p>
    <p class="intro-text">
        La norma tecnica colombiana NTC 2885 establece los requisitos minimos que deben cumplirse en la inspeccion de extintores portatiles contra incendios. A continuacion, se presenta un resumen de los aspectos que se deben revisar en una inspeccion:
    </p>
    <p class="intro-subtitle">1. Condiciones generales del extintor:</p>
    <ul class="intro-list">
        <li>Verifique que el extintor no este danado, corroido o presente fugas.</li>
        <li>Revise que la manguera, la boquilla y el manometro esten en buen estado.</li>
        <li>Asegurese de que el extintor este montado correctamente en su soporte.</li>
    </ul>
    <p class="intro-subtitle">2. Presion del agente extintor:</p>
    <ul class="intro-list">
        <li>Verifique que la presion del agente extintor este dentro del rango adecuado.</li>
        <li>Si el indicador de presion muestra que la presion es demasiado baja, el extintor debe ser recargado.</li>
    </ul>
    <p class="intro-subtitle">3. Etiquetado y funcionamiento:</p>
    <ul class="intro-list">
        <li>Verifique que el extintor este correctamente etiquetado con la informacion del fabricante.</li>
        <li>Las inspecciones deben realizarse como minimo una vez al ano (NTC 2885).</li>
    </ul>

    <!-- DATOS GENERALES -->
    <div class="section-title">DATOS DE LA INSPECCION</div>
    <table class="info-table">
        <tr>
            <td class="info-label">CLIENTE:</td>
            <td><?= esc($cliente['nombre_cliente'] ?? '') ?></td>
            <td class="info-label">FECHA INSPECCION:</td>
            <td><?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td>
        </tr>
        <tr>
            <td class="info-label">CONSULTOR:</td>
            <td><?= esc($consultor['nombre_consultor'] ?? '') ?></td>
            <td class="info-label">VENCIMIENTO GLOBAL:</td>
            <td><?= !empty($inspeccion['fecha_vencimiento_global']) ? date('d/m/Y', strtotime($inspeccion['fecha_vencimiento_global'])) : '-' ?></td>
        </tr>
    </table>

    <!-- INVENTARIO -->
    <div class="section-title">INVENTARIO GENERAL</div>
    <table class="inv-table">
        <tr>
            <td class="inv-label">Numero de extintores totales</td>
            <td style="text-align:center; font-weight:bold;"><?= $inspeccion['numero_extintores_totales'] ?? 0 ?></td>
            <td class="inv-label">Capacidad (libras)</td>
            <td style="text-align:center;"><?= esc($inspeccion['capacidad_libras'] ?? '-') ?></td>
        </tr>
        <tr>
            <td class="inv-label">ABC (Multiproposito)</td>
            <td style="text-align:center;"><?= $inspeccion['cantidad_abc'] ?? 0 ?></td>
            <td class="inv-label">CO2 (Dioxido de carbono)</td>
            <td style="text-align:center;"><?= $inspeccion['cantidad_co2'] ?? 0 ?></td>
        </tr>
        <tr>
            <td class="inv-label">Solkaflam 123</td>
            <td style="text-align:center;"><?= $inspeccion['cantidad_solkaflam'] ?? 0 ?></td>
            <td class="inv-label">Extintores de agua</td>
            <td style="text-align:center;"><?= $inspeccion['cantidad_agua'] ?? 0 ?></td>
        </tr>
    </table>
    <table class="inv-table">
        <tr>
            <td class="inv-label">Unidades residenciales</td>
            <td style="text-align:center;"><?= $inspeccion['cantidad_unidades_residenciales'] ?? 0 ?></td>
            <td class="inv-label">Porteria</td>
            <td style="text-align:center;"><?= $inspeccion['cantidad_porteria'] ?? 0 ?></td>
        </tr>
        <tr>
            <td class="inv-label">Oficina administracion</td>
            <td style="text-align:center;"><?= $inspeccion['cantidad_oficina_admin'] ?? 0 ?></td>
            <td class="inv-label">Shut de basuras</td>
            <td style="text-align:center;"><?= $inspeccion['cantidad_shut_basuras'] ?? 0 ?></td>
        </tr>
        <tr>
            <td class="inv-label">Salones comunales</td>
            <td style="text-align:center;"><?= $inspeccion['cantidad_salones_comunales'] ?? 0 ?></td>
            <td class="inv-label">Cuarto de bombas</td>
            <td style="text-align:center;"><?= $inspeccion['cantidad_cuarto_bombas'] ?? 0 ?></td>
        </tr>
        <tr>
            <td class="inv-label">Planta electrica</td>
            <td style="text-align:center;"><?= $inspeccion['cantidad_planta_electrica'] ?? 0 ?></td>
            <td class="inv-label">&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
    </table>

    <!-- EXTINTORES INSPECCIONADOS -->
<div class="section-title">DETALLE DE EXTINTORES INSPECCIONADOS (<?= count($extintores) ?>)</div>

    <?php if (!empty($extintores)): ?>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:3%;">#</th>
                <th>Pintura</th>
                <th>Golpes</th>
                <th>Adhesivo</th>
                <th>Manija</th>
                <th>Palanca</th>
                <th>Presion</th>
                <th>Manom.</th>
                <th>Boquilla</th>
                <th>Manguera</th>
                <th>Ring</th>
                <th>Senal.</th>
                <th>Soporte</th>
                <th>Venc.</th>
                <th style="width:8%;">Foto</th>
                <th style="width:12%;">Obs.</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($extintores as $i => $ext):
                // Función inline para color
                $colorClass = function($val) {
                    if (in_array($val, ['BUENO', 'CARGADO', 'NO'])) return 'val-bueno';
                    if ($val === 'REGULAR') return 'val-regular';
                    if (in_array($val, ['MALO', 'SI', 'DESCARGADO'])) return 'val-malo';
                    return 'val-na';
                };
            ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td class="<?= $colorClass($ext['pintura_cilindro']) ?>"><?= esc($ext['pintura_cilindro']) ?></td>
                <td class="<?= $colorClass($ext['golpes_extintor']) ?>"><?= esc($ext['golpes_extintor']) ?></td>
                <td class="<?= $colorClass($ext['autoadhesivo']) ?>"><?= esc($ext['autoadhesivo']) ?></td>
                <td class="<?= $colorClass($ext['manija_transporte']) ?>"><?= esc($ext['manija_transporte']) ?></td>
                <td class="<?= $colorClass($ext['palanca_accionamiento']) ?>"><?= esc($ext['palanca_accionamiento']) ?></td>
                <td class="<?= $colorClass($ext['presion']) ?>"><?= esc($ext['presion']) ?></td>
                <td class="<?= $colorClass($ext['manometro']) ?>"><?= esc($ext['manometro']) ?></td>
                <td class="<?= $colorClass($ext['boquilla']) ?>"><?= esc($ext['boquilla']) ?></td>
                <td class="<?= $colorClass($ext['manguera']) ?>"><?= esc($ext['manguera']) ?></td>
                <td class="<?= $colorClass($ext['ring_seguridad']) ?>"><?= esc($ext['ring_seguridad']) ?></td>
                <td class="<?= $colorClass($ext['senalizacion']) ?>"><?= esc($ext['senalizacion']) ?></td>
                <td class="<?= $colorClass($ext['soporte']) ?>"><?= esc($ext['soporte']) ?></td>
                <td><?= !empty($ext['fecha_vencimiento']) ? date('d/m/Y', strtotime($ext['fecha_vencimiento'])) : '-' ?></td>
                <td>
                    <?php if (!empty($ext['foto_base64'])): ?>
                        <img src="<?= $ext['foto_base64'] ?>" class="ext-img">
                    <?php else: ?>
                        <span style="color:#999; font-size:7px;">Sin foto</span>
                    <?php endif; ?>
                </td>
                <td style="text-align:left; font-size:7px;"><?= esc($ext['observaciones'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p style="color:#888; font-style:italic; font-size:8px;">No se inspeccionaron extintores.</p>
    <?php endif; ?>

    <!-- RECOMENDACIONES -->
    <?php if (!empty($inspeccion['recomendaciones_generales'])): ?>
    <div class="section-title">RECOMENDACIONES GENERALES</div>
    <p class="content-text"><?= nl2br(esc($inspeccion['recomendaciones_generales'])) ?></p>
    <?php endif; ?>

</body>
</html>
