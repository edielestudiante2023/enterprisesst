<?php
/**
 * Ficha Tecnica de Indicador SST - Exportacion Word
 *
 * Variables recibidas:
 *   $indicador   - array con todos los campos del indicador (tbl_indicadores_sst)
 *   $periodos    - array de ['periodo', 'label', 'numerador', 'denominador', 'resultado', 'cumple_meta']
 *   $acumulado   - array con numerador, denominador, resultado
 *   $anio        - integer year
 *   $cliente     - array con nombre_cliente, nit_cliente
 *   $consultor   - datos del consultor
 *   $consecutivo - integer
 *   $logoBase64  - base64 logo con fondo blanco
 */

$codigo = 'FT-IND-' . str_pad($consecutivo, 3, '0', STR_PAD_LEFT);

// Mapeo de periodicidades
$periodicidadLabels = [
    'mensual'    => 'Mensual',
    'trimestral' => 'Trimestral',
    'semestral'  => 'Semestral',
    'anual'      => 'Anual',
];

// Mapeo de tipos de indicador
$tipoLabels = [
    'estructura' => 'Estructura',
    'proceso'    => 'Proceso',
    'resultado'  => 'Resultado',
];

// Mapeo de fases PHVA
$phvaLabels = [
    'planear'   => 'PLANEAR',
    'hacer'     => 'HACER',
    'verificar' => 'VERIFICAR',
    'actuar'    => 'ACTUAR',
];

// Categorias
$categoriaLabels = [
    'capacitacion'                    => 'Capacitacion',
    'accidentalidad'                  => 'Accidentalidad',
    'ausentismo'                      => 'Ausentismo',
    'pta'                             => 'Plan de Trabajo Anual',
    'inspecciones'                    => 'Inspecciones',
    'emergencias'                     => 'Emergencias',
    'vigilancia'                      => 'Vigilancia Epidemiologica',
    'riesgos'                         => 'Gestion de Riesgos',
    'pyp_salud'                       => 'Promocion y Prevencion en Salud',
    'objetivos_sgsst'                 => 'Objetivos del SG-SST',
    'induccion'                       => 'Induccion y Reinduccion',
    'estilos_vida_saludable'          => 'Estilos de Vida Saludable',
    'evaluaciones_medicas_ocupacionales' => 'Evaluaciones Medicas Ocupacionales',
    'pve_biomecanico'                 => 'PVE Riesgo Biomecanico',
    'pve_psicosocial'                 => 'PVE Riesgo Psicosocial',
    'mantenimiento_periodico'         => 'Mantenimiento Periodico',
    'otro'                            => 'Otros',
];

$periodicidadTexto = $periodicidadLabels[$indicador['periodicidad'] ?? ''] ?? ($indicador['periodicidad'] ?? 'N/A');
$tipoTexto         = $tipoLabels[$indicador['tipo_indicador'] ?? ''] ?? ($indicador['tipo_indicador'] ?? 'N/A');
$phvaTexto         = $phvaLabels[$indicador['phva'] ?? ''] ?? ($indicador['phva'] ?? 'N/A');
$categoriaTexto    = $categoriaLabels[$indicador['categoria'] ?? ''] ?? ($indicador['categoria'] ?? 'N/A');
$esMensual         = ($indicador['periodicidad'] ?? '') === 'mensual';

// Representante legal (usa variable del controller)
$repLegalNombre = $repLegalNombre
    ?? $contexto['representante_legal_nombre']
    ?? $cliente['nombre_rep_legal']
    ?? $cliente['representante_legal']
    ?? '';

// Consultor datos
$consultorNombre  = $consultor['nombre_consultor'] ?? trim(($consultor['primer_nombre'] ?? '') . ' ' . ($consultor['primer_apellido'] ?? ''));
$consultorLicencia = $consultor['numero_licencia'] ?? $consultor['licencia_sst'] ?? '';

// Firmantes dinamicos segun contexto
$estandares = $contexto['estandares_aplicables'] ?? 60;
$requiereDelegado = !empty($contexto['requiere_delegado_sst']);
$esSoloDosFirmantes = ($estandares <= 10) && !$requiereDelegado;
$delegadoNombre = $contexto['delegado_sst_nombre'] ?? '';
$delegadoCargo = $contexto['delegado_sst_cargo'] ?? 'Delegado SST';
?>
<html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<!--[if gte mso 9]>
<xml>
    <w:WordDocument>
        <w:View>Print</w:View>
        <w:Zoom>100</w:Zoom>
    </w:WordDocument>
</xml>
<![endif]-->
<style>
    @page {
        size: letter;
        margin: 2cm 1.5cm;
    }
    body {
        font-family: Arial, sans-serif;
        font-size: 10pt;
        line-height: 1.0;
        color: #333;
        mso-line-height-rule: exactly;
    }
    p { margin: 2px 0; line-height: 1.0; mso-line-height-rule: exactly; }
    br { mso-data-placement: same-cell; }
    table { border-collapse: collapse; }
    .seccion { margin-bottom: 6px; }
    .seccion-titulo {
        font-size: 11pt;
        font-weight: bold;
        color: #0d6efd;
        border-bottom: 1px solid #ccc;
        padding-bottom: 2px;
        margin-bottom: 4px;
        margin-top: 8px;
        line-height: 1.0;
    }
    .seccion-contenido { text-align: justify; line-height: 1.0; }
    table.tabla-contenido {
        width: 100%;
        border-collapse: collapse;
        margin: 4px 0;
        font-size: 9pt;
    }
    table.tabla-contenido th, table.tabla-contenido td {
        border: 1px solid #999;
        padding: 2px 4px;
    }
    table.tabla-contenido th {
        background-color: #0d6efd;
        color: white;
        font-weight: bold;
    }
</style>
</head>
<body>

    <!-- ============================================== -->
    <!-- ENCABEZADO DEL DOCUMENTO -->
    <!-- ============================================== -->
    <table width="100%" border="1" cellpadding="0" cellspacing="0" style="border-collapse:collapse; border:1px solid #333; margin-bottom:15px;">
        <tr>
            <td width="80" rowspan="2" align="center" valign="middle" bgcolor="#FFFFFF" style="border:1px solid #333; padding:5px; background-color:#ffffff;">
                <?php if (!empty($logoBase64)): ?>
                <img src="<?= $logoBase64 ?>" width="70" height="45" alt="Logo" style="background-color:#ffffff;">
                <?php else: ?>
                <b style="font-size:8pt;"><?= esc($cliente['nombre_cliente']) ?></b>
                <?php endif; ?>
            </td>
            <td align="center" valign="middle" style="border:1px solid #333; padding:5px; font-size:9pt; font-weight:bold;">
                SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO
            </td>
            <td width="120" rowspan="2" valign="middle" style="border:1px solid #333; padding:0; font-size:8pt;">
                <table width="100%" cellpadding="2" cellspacing="0" style="border-collapse:collapse;">
                    <tr><td style="border-bottom:1px solid #333;"><b>Codigo:</b></td><td style="border-bottom:1px solid #333;"><?= esc($codigo) ?></td></tr>
                    <tr><td style="border-bottom:1px solid #333;"><b>Version:</b></td><td style="border-bottom:1px solid #333;">001</td></tr>
                    <tr><td><b>Fecha:</b></td><td><?= date('d/m/Y') ?></td></tr>
                </table>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle" style="border:1px solid #333; padding:5px; font-size:9pt; font-weight:bold;">
                FICHA TECNICA DE INDICADOR
            </td>
        </tr>
    </table>

    <!-- ============================================== -->
    <!-- SECCION 1: INFORMACION DEL INDICADOR -->
    <!-- ============================================== -->
    <div class="seccion">
        <div class="seccion-titulo" style="background-color: #0d6efd; color: white; padding: 5px 8px; border: none;">
            1. INFORMACION DEL INDICADOR
        </div>
        <table class="tabla-contenido" style="width: 100%; margin-top: 0;">
            <tr>
                <td style="width: 30%; background-color: #e9ecef; font-weight: bold;">Nombre del Indicador</td>
                <td colspan="3"><?= esc($indicador['nombre_indicador'] ?? '') ?></td>
            </tr>
            <tr>
                <td style="width: 30%; background-color: #e9ecef; font-weight: bold;">Tipo de Indicador</td>
                <td style="width: 20%;"><?= esc($tipoTexto) ?></td>
                <td style="width: 20%; background-color: #e9ecef; font-weight: bold;">Ciclo PHVA</td>
                <td style="width: 30%;"><?= esc($phvaTexto) ?></td>
            </tr>
            <tr>
                <td style="background-color: #e9ecef; font-weight: bold;">Categoria</td>
                <td><?= esc($categoriaTexto) ?></td>
                <td style="background-color: #e9ecef; font-weight: bold;">Periodicidad</td>
                <td><?= esc($periodicidadTexto) ?></td>
            </tr>
            <tr>
                <td style="background-color: #e9ecef; font-weight: bold;">Definicion</td>
                <td colspan="3"><?= esc($indicador['definicion'] ?? 'No definido') ?></td>
            </tr>
            <tr>
                <td style="background-color: #e9ecef; font-weight: bold;">Formula</td>
                <td colspan="3"><b><?= esc($indicador['formula'] ?? 'No definida') ?></b></td>
            </tr>
            <tr>
                <td style="background-color: #e9ecef; font-weight: bold;">Meta</td>
                <td><?= esc(($indicador['meta'] ?? 'N/A') . ' ' . ($indicador['unidad_medida'] ?? '')) ?></td>
                <td style="background-color: #e9ecef; font-weight: bold;">Unidad de Medida</td>
                <td><?= esc($indicador['unidad_medida'] ?? 'N/A') ?></td>
            </tr>
            <tr>
                <td style="background-color: #e9ecef; font-weight: bold;">Interpretacion</td>
                <td colspan="3"><?= esc($indicador['interpretacion'] ?? 'No definida') ?></td>
            </tr>
            <tr>
                <td style="background-color: #e9ecef; font-weight: bold;">Origen de Datos</td>
                <td colspan="3"><?= esc($indicador['origen_datos'] ?? 'No definido') ?></td>
            </tr>
            <tr>
                <td style="background-color: #e9ecef; font-weight: bold;">Responsable</td>
                <td><?= esc($indicador['cargo_responsable'] ?? 'No asignado') ?></td>
                <td style="background-color: #e9ecef; font-weight: bold;">Numeral / Norma</td>
                <td><?= esc($indicador['numeral_resolucion'] ?? 'N/A') ?></td>
            </tr>
            <?php if (!empty($indicador['cargos_conocer_resultado'])): ?>
            <tr>
                <td style="background-color: #e9ecef; font-weight: bold;">Cargos que Deben Conocer el Resultado</td>
                <td colspan="3"><?= esc($indicador['cargos_conocer_resultado']) ?></td>
            </tr>
            <?php endif; ?>
            <?php if (!empty($indicador['es_minimo_obligatorio'])): ?>
            <tr>
                <td style="background-color: #e9ecef; font-weight: bold;">Indicador Minimo Obligatorio</td>
                <td colspan="3"><b>Si</b> - Resolucion 0312 de 2019, Articulo 30</td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- ============================================== -->
    <!-- SECCION 2: TABLA DE MEDICION -->
    <!-- ============================================== -->
    <div class="seccion">
        <div class="seccion-titulo" style="background-color: #0d6efd; color: white; padding: 5px 8px; border: none;">
            2. MEDICION - ANO <?= esc($anio) ?>
        </div>
        <table class="tabla-contenido" style="width: 100%; margin-top: 0; font-size: <?= $esMensual ? '8pt' : '9pt' ?>;">
            <tr>
                <th style="background-color: #0d6efd; color: white; font-weight: bold; text-align: center; <?= $esMensual ? 'padding: 2px 2px;' : '' ?>">Componente</th>
                <?php foreach ($periodos as $p): ?>
                <th style="background-color: #0d6efd; color: white; font-weight: bold; text-align: center; <?= $esMensual ? 'padding: 2px 2px;' : '' ?>"><?= esc($p['label']) ?></th>
                <?php endforeach; ?>
                <th style="background-color: #0d6efd; color: white; font-weight: bold; text-align: center; <?= $esMensual ? 'padding: 2px 2px;' : '' ?>">Acum.</th>
            </tr>
            <!-- Fila Numerador -->
            <tr>
                <td style="background-color: #e9ecef; font-weight: bold; <?= $esMensual ? 'padding: 2px 2px;' : '' ?>">Numerador</td>
                <?php foreach ($periodos as $p): ?>
                <td style="text-align: center; <?= $esMensual ? 'padding: 2px 2px;' : '' ?>"><?= ($p['numerador'] !== null) ? esc(number_format((float)$p['numerador'], 2)) : '-' ?></td>
                <?php endforeach; ?>
                <td style="text-align: center; font-weight: bold; background-color: #f8f9fa; <?= $esMensual ? 'padding: 2px 2px;' : '' ?>"><?= ($acumulado['numerador'] !== null) ? esc(number_format((float)$acumulado['numerador'], 2)) : '-' ?></td>
            </tr>
            <!-- Fila Denominador -->
            <tr>
                <td style="background-color: #e9ecef; font-weight: bold; <?= $esMensual ? 'padding: 2px 2px;' : '' ?>">Denominador</td>
                <?php foreach ($periodos as $p): ?>
                <td style="text-align: center; <?= $esMensual ? 'padding: 2px 2px;' : '' ?>"><?= ($p['denominador'] !== null) ? esc(number_format((float)$p['denominador'], 2)) : '-' ?></td>
                <?php endforeach; ?>
                <td style="text-align: center; font-weight: bold; background-color: #f8f9fa; <?= $esMensual ? 'padding: 2px 2px;' : '' ?>"><?= ($acumulado['denominador'] !== null) ? esc(number_format((float)$acumulado['denominador'], 2)) : '-' ?></td>
            </tr>
            <!-- Fila Resultado -->
            <tr>
                <td style="background-color: #e9ecef; font-weight: bold; <?= $esMensual ? 'padding: 2px 2px;' : '' ?>">Resultado</td>
                <?php foreach ($periodos as $p): ?>
                <td style="text-align: center; font-weight: bold; <?= $esMensual ? 'padding: 2px 2px;' : '' ?>"><?= ($p['resultado'] !== null) ? esc(number_format((float)$p['resultado'], 2)) : '-' ?></td>
                <?php endforeach; ?>
                <td style="text-align: center; font-weight: bold; background-color: #f8f9fa; <?= $esMensual ? 'padding: 2px 2px;' : '' ?>"><?= ($acumulado['resultado'] !== null) ? esc(number_format((float)$acumulado['resultado'], 2)) : '-' ?></td>
            </tr>
            <!-- Fila Meta -->
            <tr>
                <td style="background-color: #e9ecef; font-weight: bold; <?= $esMensual ? 'padding: 2px 2px;' : '' ?>">Meta</td>
                <?php foreach ($periodos as $p): ?>
                <td style="text-align: center; color: #0d6efd; <?= $esMensual ? 'padding: 2px 2px;' : '' ?>"><?= ($indicador['meta'] !== null) ? esc($indicador['meta'] . ' ' . ($indicador['unidad_medida'] ?? '')) : 'N/A' ?></td>
                <?php endforeach; ?>
                <td style="text-align: center; color: #0d6efd; background-color: #f8f9fa; <?= $esMensual ? 'padding: 2px 2px;' : '' ?>"><?= ($indicador['meta'] !== null) ? esc($indicador['meta'] . ' ' . ($indicador['unidad_medida'] ?? '')) : 'N/A' ?></td>
            </tr>
            <!-- Fila Cumple Meta -->
            <tr>
                <td style="background-color: #e9ecef; font-weight: bold; <?= $esMensual ? 'padding: 2px 2px;' : '' ?>">Cumple Meta</td>
                <?php foreach ($periodos as $p): ?>
                <?php
                    $bgCumple = '';
                    $textoCumple = '-';
                    if ($p['cumple_meta'] !== null) {
                        if ($p['cumple_meta'] == 1) {
                            $bgCumple = 'background-color: #d4edda; color: #155724;';
                            $textoCumple = 'SI';
                        } else {
                            $bgCumple = 'background-color: #f8d7da; color: #721c24;';
                            $textoCumple = 'NO';
                        }
                    }
                ?>
                <td style="text-align: center; font-weight: bold; <?= $bgCumple ?> <?= $esMensual ? 'padding: 2px 2px;' : '' ?>"><?= $textoCumple ?></td>
                <?php endforeach; ?>
                <td style="text-align: center; font-weight: bold; background-color: #f8f9fa; <?= $esMensual ? 'padding: 2px 2px;' : '' ?>">-</td>
            </tr>
        </table>
    </div>

    <!-- ============================================== -->
    <!-- SECCION 3: GRAFICA -->
    <!-- ============================================== -->
    <div class="seccion">
        <div class="seccion-titulo" style="background-color: #0d6efd; color: white; padding: 5px 8px; border: none;">
            3. GRAFICA DE TENDENCIA
        </div>
        <table class="tabla-contenido" style="width: 100%; margin-top: 0;">
            <tr>
                <td style="text-align: center; padding: 20px 10px; color: #666; font-style: italic;">
                    Grafica disponible en vista web. Para visualizar la grafica de tendencia del indicador,
                    acceda al modulo de Indicadores SST en la plataforma Enterprise SST.
                </td>
            </tr>
        </table>
    </div>

    <!-- ============================================== -->
    <!-- SECCION 4: ANALISIS DE DATOS -->
    <!-- ============================================== -->
    <div class="seccion">
        <div class="seccion-titulo" style="background-color: #0d6efd; color: white; padding: 5px 8px; border: none;">
            4. ANALISIS DE DATOS
        </div>
        <table class="tabla-contenido" style="width: 100%; margin-top: 0;">
            <tr>
                <td style="background-color: #e9ecef; font-weight: bold; width: 25%;">Analisis de Datos</td>
                <td style="text-align: justify;">
                    <?php if (!empty($indicador['analisis_datos'])): ?>
                        <?= esc($indicador['analisis_datos']) ?>
                    <?php else: ?>
                        <i style="color: #999;">Sin analisis registrado para este periodo.</i>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td style="background-color: #e9ecef; font-weight: bold;">Observaciones</td>
                <td style="text-align: justify;">
                    <?php if (!empty($indicador['observaciones'])): ?>
                        <?= esc($indicador['observaciones']) ?>
                    <?php else: ?>
                        <i style="color: #999;">Sin observaciones.</i>
                    <?php endif; ?>
                </td>
            </tr>
            <?php if (!empty($indicador['requiere_plan_accion']) && $indicador['requiere_plan_accion'] == 1): ?>
            <tr>
                <td style="background-color: #f8d7da; font-weight: bold; color: #721c24;">Requiere Plan de Accion</td>
                <td>
                    <b>Si</b>
                    <?php if (!empty($indicador['numero_accion'])): ?>
                        - Accion No. <?= esc($indicador['numero_accion']) ?>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endif; ?>
            <tr>
                <td style="background-color: #e9ecef; font-weight: bold;">Acciones de Mejora</td>
                <td style="text-align: justify;">
                    <?php if (!empty($indicador['acciones_mejora'])): ?>
                        <?= esc($indicador['acciones_mejora']) ?>
                    <?php else: ?>
                        <i style="color: #999;">Sin acciones de mejora registradas.</i>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>

    <!-- ============================================== -->
    <!-- SECCION 5: SEGUIMIENTO -->
    <!-- ============================================== -->
    <div class="seccion">
        <div class="seccion-titulo" style="background-color: #0d6efd; color: white; padding: 5px 8px; border: none;">
            5. SEGUIMIENTO
        </div>
        <table class="tabla-contenido" style="width: 100%; margin-top: 0;">
            <tr>
                <th style="background-color: #e9ecef; color: #333; width: 25%;">Aspecto</th>
                <th style="background-color: #e9ecef; color: #333;">Detalle</th>
            </tr>
            <tr>
                <td style="font-weight: bold;">Ultima Fecha de Medicion</td>
                <td><?= !empty($indicador['fecha_medicion']) ? esc(date('d/m/Y', strtotime($indicador['fecha_medicion']))) : 'Sin medicion' ?></td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Ultimo Resultado</td>
                <td>
                    <?php if ($indicador['valor_resultado'] !== null): ?>
                        <?= esc(number_format((float)$indicador['valor_resultado'], 2)) ?> <?= esc($indicador['unidad_medida'] ?? '') ?>
                        <?php if ($indicador['cumple_meta'] !== null): ?>
                            - <b><?= $indicador['cumple_meta'] == 1 ? 'CUMPLE' : 'NO CUMPLE' ?></b>
                        <?php endif; ?>
                    <?php else: ?>
                        Sin resultado
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Responsable de Medicion</td>
                <td><?= esc($indicador['cargo_responsable'] ?? 'No asignado') ?></td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Ano Evaluado</td>
                <td><?= esc($anio) ?></td>
            </tr>
        </table>
    </div>

    <!-- ============================================== -->
    <!-- FIRMAS DE APROBACION - Dinamico                -->
    <!-- ============================================== -->
    <div style="margin-top: 20px;">
        <div class="seccion-titulo" style="background-color: #198754; color: white; padding: 5px 8px; border: none;">
            FIRMAS DE APROBACION
        </div>
        <table border="1" cellpadding="0" cellspacing="0" style="width: 100%; table-layout: fixed; border-collapse: collapse; border: 1px solid #999; margin-top: 0;">
            <?php if (!$esSoloDosFirmantes): ?>
            <!-- 3 firmantes -->
            <tr>
                <th width="33%" style="background-color: #e9ecef; color: #333; font-weight: bold; text-align: center; padding: 4px; border: 1px solid #999; font-size: 8pt;">Elaboro / Consultor SST</th>
                <th width="34%" style="background-color: #e9ecef; color: #333; font-weight: bold; text-align: center; padding: 4px; border: 1px solid #999; font-size: 8pt;">Reviso / Delegado SST</th>
                <th width="33%" style="background-color: #e9ecef; color: #333; font-weight: bold; text-align: center; padding: 4px; border: 1px solid #999; font-size: 8pt;">Aprobo / Representante Legal</th>
            </tr>
            <tr>
                <td style="vertical-align: top; padding: 5px; border: 1px solid #999; font-size: 8pt;">
                    <p style="margin: 2px 0;"><b>Nombre:</b> <?= !empty($consultorNombre) ? esc($consultorNombre) : '_________________' ?></p>
                    <p style="margin: 2px 0;"><b>Cargo:</b> Consultor SST</p>
                    <?php if (!empty($consultorLicencia)): ?>
                    <p style="margin: 2px 0;"><b>Licencia SST:</b> <?= esc($consultorLicencia) ?></p>
                    <?php endif; ?>
                </td>
                <td style="vertical-align: top; padding: 5px; border: 1px solid #999; font-size: 8pt;">
                    <p style="margin: 2px 0;"><b>Nombre:</b> <?= !empty($delegadoNombre) ? esc($delegadoNombre) : '_________________' ?></p>
                    <p style="margin: 2px 0;"><b>Cargo:</b> <?= esc($delegadoCargo) ?></p>
                </td>
                <td style="vertical-align: top; padding: 5px; border: 1px solid #999; font-size: 8pt;">
                    <p style="margin: 2px 0;"><b>Nombre:</b> <?= !empty($repLegalNombre) ? esc($repLegalNombre) : '_________________' ?></p>
                    <p style="margin: 2px 0;"><b>Cargo:</b> Representante Legal</p>
                </td>
            </tr>
            <tr>
                <td style="padding: 5px; text-align: center; border: 1px solid #999; height: 45px; vertical-align: bottom;">
                    <div style="border-top: 1px solid #333; width: 65%; margin: 3px auto 0;">
                        <span style="color: #666; font-size: 6pt;">Firma</span>
                    </div>
                </td>
                <td style="padding: 5px; text-align: center; border: 1px solid #999; height: 45px; vertical-align: bottom;">
                    <div style="border-top: 1px solid #333; width: 65%; margin: 3px auto 0;">
                        <span style="color: #666; font-size: 6pt;">Firma</span>
                    </div>
                </td>
                <td style="padding: 5px; text-align: center; border: 1px solid #999; height: 45px; vertical-align: bottom;">
                    <div style="border-top: 1px solid #333; width: 65%; margin: 3px auto 0;">
                        <span style="color: #666; font-size: 6pt;">Firma</span>
                    </div>
                </td>
            </tr>
            <?php else: ?>
            <!-- 2 firmantes -->
            <tr>
                <th width="50%" style="background-color: #e9ecef; color: #333; font-weight: bold; text-align: center; padding: 4px; border: 1px solid #999; font-size: 8pt;">Elaboro / Consultor SST</th>
                <th width="50%" style="background-color: #e9ecef; color: #333; font-weight: bold; text-align: center; padding: 4px; border: 1px solid #999; font-size: 8pt;">Aprobo / Representante Legal</th>
            </tr>
            <tr>
                <td style="vertical-align: top; padding: 5px; border: 1px solid #999; font-size: 8pt;">
                    <p style="margin: 2px 0;"><b>Nombre:</b> <?= !empty($consultorNombre) ? esc($consultorNombre) : '_________________' ?></p>
                    <p style="margin: 2px 0;"><b>Cargo:</b> Consultor SST</p>
                    <?php if (!empty($consultorLicencia)): ?>
                    <p style="margin: 2px 0;"><b>Licencia SST:</b> <?= esc($consultorLicencia) ?></p>
                    <?php endif; ?>
                </td>
                <td style="vertical-align: top; padding: 5px; border: 1px solid #999; font-size: 8pt;">
                    <p style="margin: 2px 0;"><b>Nombre:</b> <?= !empty($repLegalNombre) ? esc($repLegalNombre) : '_________________' ?></p>
                    <p style="margin: 2px 0;"><b>Cargo:</b> Representante Legal</p>
                </td>
            </tr>
            <tr>
                <td style="padding: 5px; text-align: center; border: 1px solid #999; height: 50px; vertical-align: bottom;">
                    <div style="border-top: 1px solid #333; width: 70%; margin: 3px auto 0;">
                        <span style="color: #666; font-size: 7pt;">Firma</span>
                    </div>
                </td>
                <td style="padding: 5px; text-align: center; border: 1px solid #999; height: 50px; vertical-align: bottom;">
                    <div style="border-top: 1px solid #333; width: 70%; margin: 3px auto 0;">
                        <span style="color: #666; font-size: 7pt;">Firma</span>
                    </div>
                </td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- ============================================== -->
    <!-- CONTROL DE CAMBIOS - con $versiones            -->
    <!-- ============================================== -->
    <div class="seccion" style="margin-top: 20px;">
        <div class="seccion-titulo" style="background-color: #0d6efd; color: white; padding: 5px 8px; border: none;">
            CONTROL DE CAMBIOS
        </div>
        <table class="tabla-contenido" style="width: 100%; margin-top: 0;">
            <tr>
                <th style="width: 80px; background-color: #e9ecef; color: #333;">Version</th>
                <th style="background-color: #e9ecef; color: #333;">Descripcion del Cambio</th>
                <th style="width: 90px; background-color: #e9ecef; color: #333;">Fecha</th>
            </tr>
            <?php if (!empty($versiones)): ?>
                <?php foreach ($versiones as $ver): ?>
                <tr>
                    <td style="text-align: center; font-weight: bold;"><?= esc($ver['version_texto']) ?></td>
                    <td><?= esc($ver['descripcion_cambio']) ?></td>
                    <td style="text-align: center;"><?= date('d/m/Y', strtotime($ver['fecha_autorizacion'])) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
            <tr>
                <td style="text-align: center; font-weight: bold;">1.0</td>
                <td>Elaboracion inicial de la ficha tecnica del indicador</td>
                <td style="text-align: center;"><?= date('d/m/Y') ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- ============================================== -->
    <!-- PIE DE PAGINA -->
    <!-- ============================================== -->
    <div style="margin-top:20px; padding-top:10px; border-top:1px solid #ccc; text-align:center; font-size:8pt; color:#666;">
        <p><?= esc($cliente['nombre_cliente']) ?> - NIT: <?= esc($cliente['nit_cliente']) ?></p>
        <p>Ficha Tecnica de Indicador - <?= esc($codigo) ?> - Generado el <?= date('d/m/Y') ?></p>
    </div>

</body>
</html>
