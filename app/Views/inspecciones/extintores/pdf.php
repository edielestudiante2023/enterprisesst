<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 100px 70px 80px 90px; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10pt; line-height: 1.15; color: #333; }
        br { line-height: 0.5; }

        .seccion { margin-bottom: 8px; }
        .seccion-titulo {
            font-size: 11pt; font-weight: bold; color: #0d6efd;
            border-bottom: 1px solid #e9ecef; padding-bottom: 3px;
            margin-bottom: 5px; margin-top: 8px;
        }
        .seccion-contenido { text-align: justify; line-height: 1.2; }
        .seccion-contenido p { margin: 3px 0; }
        .seccion-contenido ul { margin: 3px 0 3px 15px; padding-left: 0; }
        .seccion-contenido li { margin-bottom: 2px; }

        table.tabla-contenido { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 9pt; }
        table.tabla-contenido th, table.tabla-contenido td { border: 1px solid #999; padding: 5px 8px; }
        table.tabla-contenido th { background-color: #0d6efd; color: white; font-weight: bold; text-align: center; }

        /* Tabla detalle extintores: font mas pequeño por la cantidad de columnas */
        table.tabla-ext { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 7pt; }
        table.tabla-ext th, table.tabla-ext td { border: 1px solid #999; padding: 3px 4px; text-align: center; }
        table.tabla-ext th { background-color: #0d6efd; color: white; font-weight: bold; }

        table.datos-general { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 9pt; }
        table.datos-general td { border: 1px solid #999; padding: 5px 8px; }
        .datos-label { font-weight: bold; width: 20%; }

        .ext-img { max-width: 80px; max-height: 60px; border: 1px solid #ccc; }

        .val-bueno { color: #155724; }
        .val-regular { color: #856404; }
        .val-malo { color: #721c24; }
        .val-na { color: #6c757d; }

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
                    <tr><td style="border-bottom:1px solid #333; padding:3px 6px; font-size:8pt;"><span style="font-weight:bold;">Codigo:</span> FT-SST-201</td></tr>
                    <tr><td style="border-bottom:1px solid #333; padding:3px 6px; font-size:8pt;"><span style="font-weight:bold;">Version:</span> 001</td></tr>
                    <tr><td style="padding:3px 6px; font-size:8pt;"><span style="font-weight:bold;">Vigencia:</span> <?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td></tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="border:1px solid #333; text-align:center; padding:6px 10px; vertical-align:middle;">
                <div style="font-size:10pt; font-weight:bold; color:#333;">
                    FORMATO DE INSPECCION EXTINTORES
                </div>
            </td>
        </tr>
    </table>

    <!-- INTRODUCCION -->
    <div class="seccion">
        <div class="seccion-titulo">INTRODUCCION</div>
        <div class="seccion-contenido">
            <p>Los extintores portatiles contra incendios son un equipo esencial para la seguridad contra incendios. En caso de incendio, un extintor portatil puede ayudar a controlar o extinguir el fuego, lo que puede salvar vidas y proteger la propiedad. Sin embargo, para que un extintor funcione correctamente, debe inspeccionarse y mantenerse regularmente.</p>
            <p><strong>Que se debe revisar en una inspeccion de extintor?</strong></p>
            <p>La norma tecnica colombiana NTC 2885 establece los requisitos minimos que deben cumplirse en la inspeccion de extintores portatiles contra incendios. A continuacion, se presenta un resumen de los aspectos que se deben revisar en una inspeccion:</p>
            <p><strong>1. Condiciones generales del extintor:</strong></p>
            <ul>
                <li>Verifique que el extintor no este danado, corroido o presente fugas.</li>
                <li>Revise que la manguera, la boquilla y el manometro esten en buen estado.</li>
                <li>Asegurese de que el extintor este montado correctamente en su soporte.</li>
            </ul>
            <p><strong>2. Presion del agente extintor:</strong></p>
            <ul>
                <li>Verifique que la presion del agente extintor este dentro del rango adecuado.</li>
                <li>Si el indicador de presion muestra que la presion es demasiado baja, el extintor debe ser recargado.</li>
            </ul>
            <p><strong>3. Etiquetado y funcionamiento:</strong></p>
            <ul>
                <li>Verifique que el extintor este correctamente etiquetado con la informacion del fabricante.</li>
                <li>Las inspecciones deben realizarse como minimo una vez al ano (NTC 2885).</li>
            </ul>
        </div>
    </div>

    <!-- DATOS GENERALES -->
    <div class="seccion">
        <div class="seccion-titulo">DATOS DE LA INSPECCION</div>
        <table class="datos-general">
            <tr>
                <td class="datos-label">CLIENTE:</td>
                <td><?= esc($cliente['nombre_cliente'] ?? '') ?></td>
                <td class="datos-label">FECHA INSPECCION:</td>
                <td><?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td>
            </tr>
            <tr>
                <td class="datos-label">CONSULTOR:</td>
                <td><?= esc($consultor['nombre_consultor'] ?? '') ?></td>
                <td class="datos-label">VENCIMIENTO GLOBAL:</td>
                <td><?= !empty($inspeccion['fecha_vencimiento_global']) ? date('d/m/Y', strtotime($inspeccion['fecha_vencimiento_global'])) : '-' ?></td>
            </tr>
        </table>
    </div>

    <!-- INVENTARIO -->
    <div class="seccion">
        <div class="seccion-titulo">INVENTARIO GENERAL</div>
        <table class="datos-general">
            <tr>
                <td class="datos-label">Numero de extintores totales</td>
                <td style="text-align:center; font-weight:bold;"><?= $inspeccion['numero_extintores_totales'] ?? 0 ?></td>
                <td class="datos-label">Capacidad (libras)</td>
                <td style="text-align:center;"><?= esc($inspeccion['capacidad_libras'] ?? '-') ?></td>
            </tr>
            <tr>
                <td class="datos-label">ABC (Multiproposito)</td>
                <td style="text-align:center;"><?= $inspeccion['cantidad_abc'] ?? 0 ?></td>
                <td class="datos-label">CO2 (Dioxido de carbono)</td>
                <td style="text-align:center;"><?= $inspeccion['cantidad_co2'] ?? 0 ?></td>
            </tr>
            <tr>
                <td class="datos-label">Solkaflam 123</td>
                <td style="text-align:center;"><?= $inspeccion['cantidad_solkaflam'] ?? 0 ?></td>
                <td class="datos-label">Extintores de agua</td>
                <td style="text-align:center;"><?= $inspeccion['cantidad_agua'] ?? 0 ?></td>
            </tr>
        </table>
        <table class="datos-general" style="margin-top:0;">
            <tr>
                <td class="datos-label">Unidades residenciales</td>
                <td style="text-align:center;"><?= $inspeccion['cantidad_unidades_residenciales'] ?? 0 ?></td>
                <td class="datos-label">Porteria</td>
                <td style="text-align:center;"><?= $inspeccion['cantidad_porteria'] ?? 0 ?></td>
            </tr>
            <tr>
                <td class="datos-label">Oficina administracion</td>
                <td style="text-align:center;"><?= $inspeccion['cantidad_oficina_admin'] ?? 0 ?></td>
                <td class="datos-label">Shut de basuras</td>
                <td style="text-align:center;"><?= $inspeccion['cantidad_shut_basuras'] ?? 0 ?></td>
            </tr>
            <tr>
                <td class="datos-label">Salones comunales</td>
                <td style="text-align:center;"><?= $inspeccion['cantidad_salones_comunales'] ?? 0 ?></td>
                <td class="datos-label">Cuarto de bombas</td>
                <td style="text-align:center;"><?= $inspeccion['cantidad_cuarto_bombas'] ?? 0 ?></td>
            </tr>
            <tr>
                <td class="datos-label">Planta electrica</td>
                <td style="text-align:center;"><?= $inspeccion['cantidad_planta_electrica'] ?? 0 ?></td>
                <td class="datos-label">&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
        </table>
    </div>

    <!-- EXTINTORES INSPECCIONADOS -->
    <div class="seccion">
        <div class="seccion-titulo">DETALLE DE EXTINTORES INSPECCIONADOS (<?= count($extintores) ?>)</div>

        <?php if (!empty($extintores)): ?>
        <table class="tabla-ext">
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
                            <span style="color:#999; font-size:7pt;">Sin foto</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:left; font-size:7pt;"><?= esc($ext['observaciones'] ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p style="color:#888; font-style:italic;">No se inspeccionaron extintores.</p>
        <?php endif; ?>
    </div>

    <!-- RECOMENDACIONES -->
    <?php if (!empty($inspeccion['recomendaciones_generales'])): ?>
    <div class="seccion">
        <div class="seccion-titulo">RECOMENDACIONES GENERALES</div>
        <div class="seccion-contenido"><?= nl2br(esc($inspeccion['recomendaciones_generales'])) ?></div>
    </div>
    <?php endif; ?>

    <!-- PIE DE DOCUMENTO -->
    <div class="pie-documento">
        <p>Documento generado el <?= date('d/m/Y') ?> - Sistema de Gestion SST</p>
        <p><?= esc($cliente['nombre_cliente'] ?? '') ?> - NIT: <?= esc($cliente['nit_cliente'] ?? '') ?></p>
    </div>

</body>
</html>
