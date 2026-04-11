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
        .datos-label { font-weight: bold; width: 20%; background-color: #f8f9fa; }

        .badge-accidente { background-color: #dc3545; color: white; padding: 2px 8px; border-radius: 4px; font-size: 9pt; font-weight: bold; }
        .badge-incidente { background-color: #ffc107; color: #333; padding: 2px 8px; border-radius: 4px; font-size: 9pt; font-weight: bold; }

        .seccion-contenido ul { margin: 3px 0 3px 15px; padding-left: 0; }
        .seccion-contenido li { margin-bottom: 2px; }

        .pie-documento { margin-top: 15px; padding-top: 8px; border-top: 1px solid #ccc; text-align: center; font-size: 8pt; color: #666; }
    </style>
</head>
<body>

<?php
$esAccidente = ($inv['tipo_evento'] ?? '') === 'accidente';
$tituloDoc = $esAccidente ? 'INVESTIGACION DE ACCIDENTE DE TRABAJO' : 'INVESTIGACION DE INCIDENTE DE TRABAJO';
$testigos = $testigos ?? [];
$evidencias = $evidencias ?? [];
$medidas = $medidas ?? [];
?>

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
                    <tr>
                        <td style="border-bottom:1px solid #333; padding:3px 6px; font-size:8pt;"><span style="font-weight:bold;">Codigo:</span> FT-SST-INV</td>
                    </tr>
                    <tr>
                        <td style="border-bottom:1px solid #333; padding:3px 6px; font-size:8pt;"><span style="font-weight:bold;">Version:</span> 001</td>
                    </tr>
                    <tr>
                        <td style="padding:3px 6px; font-size:8pt;"><span style="font-weight:bold;">Vigencia:</span> <?= date('d/m/Y', strtotime($inv['fecha_evento'])) ?></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="border:1px solid #333; text-align:center; padding:6px 10px; vertical-align:middle;">
                <div style="font-size:10pt; font-weight:bold; color:#333;">
                    <?= $tituloDoc ?>
                </div>
            </td>
        </tr>
    </table>

    <!-- DATOS DE LA EMPRESA -->
    <div class="seccion">
        <div class="seccion-titulo">1. DATOS DE LA EMPRESA</div>
        <table class="datos-general">
            <tr>
                <td class="datos-label">EMPRESA:</td>
                <td><?= esc($cliente['nombre_cliente'] ?? '') ?></td>
                <td class="datos-label">NIT:</td>
                <td><?= esc($cliente['nit_cliente'] ?? '') ?></td>
            </tr>
            <tr>
                <td class="datos-label">DIRECCION:</td>
                <td colspan="3"><?= esc($cliente['direccion_cliente'] ?? '') ?></td>
            </tr>
        </table>
    </div>

    <!-- DATOS DEL EVENTO -->
    <div class="seccion">
        <div class="seccion-titulo">2. DATOS DEL EVENTO</div>
        <table class="datos-general">
            <tr>
                <td class="datos-label">TIPO:</td>
                <td>
                    <?php if ($esAccidente): ?>
                        <span class="badge-accidente">ACCIDENTE</span>
                    <?php else: ?>
                        <span class="badge-incidente">INCIDENTE</span>
                    <?php endif; ?>
                </td>
                <?php if ($esAccidente && !empty($inv['gravedad'])): ?>
                <td class="datos-label">GRAVEDAD:</td>
                <td><?= strtoupper(esc($inv['gravedad'])) ?></td>
                <?php else: ?>
                <td class="datos-label">FECHA INV.:</td>
                <td><?= !empty($inv['fecha_investigacion']) ? date('d/m/Y', strtotime($inv['fecha_investigacion'])) : '' ?></td>
                <?php endif; ?>
            </tr>
            <tr>
                <td class="datos-label">FECHA:</td>
                <td><?= date('d/m/Y', strtotime($inv['fecha_evento'])) ?></td>
                <td class="datos-label">HORA:</td>
                <td><?= !empty($inv['hora_evento']) ? date('h:i A', strtotime($inv['hora_evento'])) : '' ?></td>
            </tr>
            <tr>
                <td class="datos-label">LUGAR:</td>
                <td colspan="3"><?= esc($inv['lugar_exacto'] ?? '') ?></td>
            </tr>
            <tr>
                <td class="datos-label">DESCRIPCION:</td>
                <td colspan="3"><?= nl2br(esc($inv['descripcion_detallada'] ?? '')) ?></td>
            </tr>
        </table>
    </div>

    <!-- DATOS DEL TRABAJADOR -->
    <div class="seccion">
        <div class="seccion-titulo">3. DATOS DEL TRABAJADOR <?= $esAccidente ? 'LESIONADO' : 'INVOLUCRADO' ?></div>
        <table class="datos-general">
            <tr>
                <td class="datos-label">NOMBRE:</td>
                <td><?= esc($inv['nombre_trabajador'] ?? '') ?></td>
                <td class="datos-label">DOCUMENTO:</td>
                <td><?= esc($inv['documento_trabajador'] ?? '') ?></td>
            </tr>
            <tr>
                <td class="datos-label">CARGO:</td>
                <td><?= esc($inv['cargo_trabajador'] ?? '') ?></td>
                <td class="datos-label">AREA:</td>
                <td><?= esc($inv['area_trabajador'] ?? '') ?></td>
            </tr>
            <tr>
                <td class="datos-label">ANTIGUEDAD:</td>
                <td><?= esc($inv['antiguedad_trabajador'] ?? '') ?></td>
                <td class="datos-label">VINCULACION:</td>
                <td><?= ucfirst(str_replace('_', ' ', esc($inv['tipo_vinculacion'] ?? ''))) ?></td>
            </tr>
            <tr>
                <td class="datos-label">JORNADA:</td>
                <td colspan="3"><?= esc($inv['jornada_habitual'] ?? '') ?></td>
            </tr>
        </table>
    </div>

    <!-- DATOS DE LESION (solo accidente) -->
    <?php if ($esAccidente): ?>
    <div class="seccion">
        <div class="seccion-titulo">4. DATOS DE LA LESION</div>
        <table class="datos-general">
            <tr>
                <td class="datos-label">PARTE CUERPO:</td>
                <td><?= esc($inv['parte_cuerpo_lesionada'] ?? '') ?></td>
                <td class="datos-label">TIPO LESION:</td>
                <td><?= esc($inv['tipo_lesion'] ?? '') ?></td>
            </tr>
            <tr>
                <td class="datos-label">AGENTE:</td>
                <td><?= esc($inv['agente_accidente'] ?? '') ?></td>
                <td class="datos-label">MECANISMO:</td>
                <td><?= esc($inv['mecanismo_accidente'] ?? '') ?></td>
            </tr>
            <tr>
                <td class="datos-label">DIAS INCAPACIDAD:</td>
                <td><?= esc($inv['dias_incapacidad'] ?? '') ?></td>
                <td class="datos-label">No. FURAT:</td>
                <td><?= esc($inv['numero_furat'] ?? '') ?></td>
            </tr>
        </table>
    </div>
    <?php endif; ?>

    <!-- POTENCIAL DE DANO (solo incidente) -->
    <?php if (!$esAccidente && !empty($inv['potencial_danio'])): ?>
    <div class="seccion">
        <div class="seccion-titulo">4. POTENCIAL DE DANO</div>
        <div class="seccion-contenido">
            <p><?= nl2br(esc($inv['potencial_danio'])) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- ANALISIS CAUSAL -->
    <div class="seccion">
        <div class="seccion-titulo"><?= $esAccidente ? '5' : (!empty($inv['potencial_danio']) ? '5' : '4') ?>. ANALISIS CAUSAL (Res. 1401 de 2007)</div>
        <table class="datos-general">
            <tr>
                <td colspan="4" style="background-color:#e9ecef; font-weight:bold; text-align:center;">CAUSAS INMEDIATAS</td>
            </tr>
            <tr>
                <td class="datos-label">ACTOS SUBESTANDAR:</td>
                <td colspan="3"><?= nl2br(esc($inv['actos_substandar'] ?? '')) ?></td>
            </tr>
            <tr>
                <td class="datos-label">CONDICIONES SUBESTANDAR:</td>
                <td colspan="3"><?= nl2br(esc($inv['condiciones_substandar'] ?? '')) ?></td>
            </tr>
            <tr>
                <td colspan="4" style="background-color:#e9ecef; font-weight:bold; text-align:center;">CAUSAS BASICAS</td>
            </tr>
            <tr>
                <td class="datos-label">FACTORES PERSONALES:</td>
                <td colspan="3"><?= nl2br(esc($inv['factores_personales'] ?? '')) ?></td>
            </tr>
            <tr>
                <td class="datos-label">FACTORES DEL TRABAJO:</td>
                <td colspan="3"><?= nl2br(esc($inv['factores_trabajo'] ?? '')) ?></td>
            </tr>
            <?php if (!empty($inv['metodologia_analisis'])): ?>
            <tr>
                <td class="datos-label">METODOLOGIA:</td>
                <td colspan="3">
                    <?php
                    $metodos = ['arbol_causas' => 'Arbol de causas', 'espina_pescado' => 'Espina de pescado (Ishikawa)', '5_porques' => '5 Porques', 'otra' => 'Otra'];
                    echo esc($metodos[$inv['metodologia_analisis']] ?? $inv['metodologia_analisis']);
                    ?>
                </td>
            </tr>
            <?php endif; ?>
            <?php if (!empty($inv['descripcion_analisis'])): ?>
            <tr>
                <td class="datos-label">DESCRIPCION:</td>
                <td colspan="3"><?= nl2br(esc($inv['descripcion_analisis'])) ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- TESTIGOS -->
    <?php if (!empty($testigos)): ?>
    <div class="seccion">
        <div class="seccion-titulo"><?= $esAccidente ? '6' : (!empty($inv['potencial_danio']) ? '6' : '5') ?>. TESTIGOS</div>
        <table class="tabla-contenido">
            <thead>
                <tr>
                    <th style="width:25%;">NOMBRE</th>
                    <th style="width:20%;">CARGO</th>
                    <th style="width:55%;">DECLARACION</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($testigos as $t): ?>
                <tr>
                    <td><?= esc($t['nombre'] ?? '') ?></td>
                    <td><?= esc($t['cargo'] ?? '') ?></td>
                    <td><?= esc($t['declaracion'] ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- EVIDENCIA FOTOGRAFICA -->
    <?php if (!empty($evidencias)): ?>
    <div class="seccion">
        <div class="seccion-titulo">EVIDENCIA FOTOGRAFICA</div>
        <?php foreach ($evidencias as $ev): ?>
        <div style="margin-bottom:10px; text-align:center;">
            <?php if (!empty($ev['imagen_base64'])): ?>
                <img src="<?= $ev['imagen_base64'] ?>" style="max-width:400px; max-height:250px; border:1px solid #ccc;">
            <?php elseif (!empty($ev['imagen'])): ?>
                <img src="<?= $ev['imagen'] ?>" style="max-width:400px; max-height:250px; border:1px solid #ccc;">
            <?php endif; ?>
            <?php if (!empty($ev['descripcion'])): ?>
                <div style="font-size:8pt; color:#666; margin-top:3px;"><?= esc($ev['descripcion']) ?></div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- MEDIDAS CORRECTIVAS -->
    <?php if (!empty($medidas)): ?>
    <div class="seccion">
        <div class="seccion-titulo">MEDIDAS CORRECTIVAS (Art. 12 Res. 1401 de 2007)</div>
        <table class="tabla-contenido">
            <thead>
                <tr>
                    <th style="width:12%;">TIPO</th>
                    <th style="width:30%;">DESCRIPCION</th>
                    <th style="width:18%;">RESPONSABLE</th>
                    <th style="width:15%;">FECHA CUMPLIMIENTO</th>
                    <th style="width:12%;">ESTADO</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $tipoLabels = ['fuente' => 'En la fuente', 'medio' => 'En el medio', 'trabajador' => 'En el trabajador'];
                foreach ($medidas as $m):
                ?>
                <tr>
                    <td><?= $tipoLabels[$m['tipo_medida'] ?? ''] ?? ucfirst($m['tipo_medida'] ?? '') ?></td>
                    <td><?= esc($m['descripcion'] ?? '') ?></td>
                    <td><?= esc($m['responsable'] ?? '') ?></td>
                    <td style="text-align:center;"><?= !empty($m['fecha_cumplimiento']) ? date('d/m/Y', strtotime($m['fecha_cumplimiento'])) : '' ?></td>
                    <td style="text-align:center;"><?= strtoupper(str_replace('_', ' ', $m['estado'] ?? '')) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- FIRMAS DEL EQUIPO INVESTIGADOR -->
    <div style="margin-top: 25px;">
        <div style="background-color: #198754; color: white; padding: 8px 12px; font-weight: bold; font-size: 10pt;">
            FIRMAS DEL EQUIPO INVESTIGADOR
        </div>
        <table class="tabla-contenido" style="width: 100%; margin-top: 0;">
            <tr>
                <th style="width: 33%; background-color: #e9ecef; color: #333;">JEFE INMEDIATO</th>
                <th style="width: 34%; background-color: #e9ecef; color: #333;">REPRESENTANTE COPASST</th>
                <th style="width: 33%; background-color: #e9ecef; color: #333;">RESPONSABLE SST</th>
            </tr>
            <tr>
                <td style="vertical-align: top; padding: 8px;">
                    <div style="margin-bottom: 3px;"><strong>Nombre:</strong> <?= esc($inv['investigador_jefe_nombre'] ?? '') ?></div>
                    <div><strong>Cargo:</strong> <?= esc($inv['investigador_jefe_cargo'] ?? '') ?></div>
                </td>
                <td style="vertical-align: top; padding: 8px;">
                    <div style="margin-bottom: 3px;"><strong>Nombre:</strong> <?= esc($inv['investigador_copasst_nombre'] ?? '') ?></div>
                    <div><strong>Cargo:</strong> <?= esc($inv['investigador_copasst_cargo'] ?? '') ?></div>
                </td>
                <td style="vertical-align: top; padding: 8px;">
                    <div style="margin-bottom: 3px;"><strong>Nombre:</strong> <?= esc($inv['investigador_sst_nombre'] ?? '') ?></div>
                    <div><strong>Cargo:</strong> <?= esc($inv['investigador_sst_cargo'] ?? '') ?></div>
                </td>
            </tr>
            <tr>
                <?php
                $firmaSlots = [
                    ['tipo' => 'jefe'],
                    ['tipo' => 'copasst'],
                    ['tipo' => 'sst'],
                ];
                foreach ($firmaSlots as $slot):
                ?>
                <td style="padding: 10px 12px; text-align: center; vertical-align: bottom; height: 80px;">
                    <?php if (!empty($firmas[$slot['tipo']])): ?>
                        <img src="<?= $firmas[$slot['tipo']] ?>" style="max-height: 56px; max-width: 140px;"><br>
                    <?php endif; ?>
                    <div style="border-top: 1px solid #333; width: 80%; margin: 5px auto 0; padding-top: 3px;">
                        <small style="color: #666;">Firma</small>
                    </div>
                </td>
                <?php endforeach; ?>
            </tr>
        </table>
    </div>

    <!-- PIE DE DOCUMENTO -->
    <div class="pie-documento">
        <p>Documento generado el <?= date('d/m/Y') ?> - Sistema de Gestion SST</p>
        <p><?= esc($cliente['nombre_cliente'] ?? '') ?> - NIT: <?= esc($cliente['nit_cliente'] ?? '') ?></p>
    </div>

</body>
</html>
