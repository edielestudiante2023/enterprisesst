<?php
/**
 * Ficha Tecnica de Indicador SST
 * Vista web Bootstrap 5 - Cumple instructivos 2_AA_WEB.md
 *
 * Variables recibidas:
 * - $indicador      : array con datos del indicador
 * - $periodos       : array de periodos con numerador, denominador, resultado, cumple_meta
 * - $acumulado      : array con numerador, denominador, resultado
 * - $anio           : int anno actual
 * - $cliente        : array con datos del cliente
 * - $contexto       : array contexto SST del cliente (puede ser null)
 * - $consultor      : array datos del consultor
 * - $consecutivo    : int numero consecutivo del indicador
 * - $repLegalNombre : string nombre del representante legal
 */

// Helpers
$idCliente   = $cliente['id_cliente'] ?? 0;
$idIndicador = $indicador['id_indicador'] ?? 0;
$codigo      = 'FT-IND-' . str_pad($consecutivo ?? 1, 3, '0', STR_PAD_LEFT);
$fechaActual = date('d/m/Y');

// Mapeo PHVA
$phvaMap = [
    'P' => 'Planear', 'H' => 'Hacer', 'V' => 'Verificar', 'A' => 'Actuar',
    'planear' => 'Planear', 'hacer' => 'Hacer', 'verificar' => 'Verificar', 'actuar' => 'Actuar',
];
$phvaValor = $indicador['phva'] ?? '';
$phvaTexto = $phvaMap[$phvaValor] ?? $phvaValor;

// Tipo indicador legible
$tipoIndicadorMap = [
    'estructura' => 'Estructura',
    'proceso'    => 'Proceso',
    'resultado'  => 'Resultado',
];
$tipoIndicadorTexto = $tipoIndicadorMap[$indicador['tipo_indicador'] ?? ''] ?? ($indicador['tipo_indicador'] ?? 'N/A');

// Representante legal (usa variable del controller, fallback local)
$repLegalNombre = $repLegalNombre
    ?? $contexto['representante_legal_nombre']
    ?? $cliente['nombre_rep_legal']
    ?? $cliente['representante_legal']
    ?? '';

// Firmantes dinamicos segun contexto
$estandares = $contexto['estandares_aplicables'] ?? 60;
$requiereDelegado = !empty($contexto['requiere_delegado_sst']);
$esSoloDosFirmantes = ($estandares <= 10) && !$requiereDelegado;

// Datos consultor
$consultorNombre  = $consultor['nombre_consultor'] ?? trim(($consultor['primer_nombre'] ?? '') . ' ' . ($consultor['primer_apellido'] ?? ''));
$consultorCargo   = 'Consultor SST';
$consultorLicencia = $consultor['numero_licencia'] ?? $consultor['licencia_sst'] ?? '';

// Datos delegado SST
$delegadoNombre = $contexto['delegado_sst_nombre'] ?? '';
$delegadoCargo  = $contexto['delegado_sst_cargo'] ?? 'Delegado SST';

// Firma consultor: prioridad electronica > fisica
$firmaConsultorFisica = $consultor['firma_consultor'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha Tecnica - <?= esc($indicador['nombre_indicador'] ?? 'Indicador') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            .documento-contenido { padding: 20px !important; }
            body { font-size: 11pt; }
            .encabezado-formal { page-break-inside: avoid; }
            .firma-section { page-break-inside: avoid; }
        }

        /* Encabezado formal - Instructivo 2_AA_WEB seccion 7 */
        .encabezado-formal {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .encabezado-formal td {
            border: 1px solid #333;
            vertical-align: middle;
        }
        .encabezado-logo {
            width: 150px;
            padding: 10px;
            text-align: center;
        }
        .encabezado-logo img {
            max-width: 130px;
            max-height: 70px;
            object-fit: contain;
        }
        .encabezado-titulo-central {
            text-align: center;
            padding: 0;
        }
        .encabezado-titulo-central .sistema {
            font-size: 0.85rem;
            font-weight: bold;
            color: #333;
            padding: 8px 15px;
            border-bottom: 1px solid #333;
        }
        .encabezado-titulo-central .nombre-doc {
            font-size: 0.85rem;
            font-weight: bold;
            color: #333;
            padding: 8px 15px;
        }
        .encabezado-info {
            width: 170px;
            padding: 0;
        }
        .encabezado-info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .encabezado-info-table td {
            border: none;
            border-bottom: 1px solid #333;
            padding: 3px 8px;
            font-size: 0.75rem;
        }
        .encabezado-info-table tr:last-child td {
            border-bottom: none;
        }
        .encabezado-info-table .label {
            font-weight: bold;
        }

        /* Secciones - Instructivo 2_AA_WEB seccion 8 */
        .seccion {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .seccion-titulo {
            font-size: 1.1rem;
            font-weight: bold;
            color: #0d6efd;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }

        /* Data table */
        .ficha-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }
        .ficha-table th,
        .ficha-table td {
            border: 1px solid #dee2e6;
            padding: 8px 10px;
            vertical-align: top;
        }
        .ficha-table .label-cell {
            background-color: #f8f9fa;
            font-weight: 600;
            width: 200px;
            white-space: nowrap;
        }

        /* Medicion table */
        .medicion-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8rem;
            text-align: center;
        }
        .medicion-table th,
        .medicion-table td {
            border: 1px solid #dee2e6;
            padding: 6px 4px;
        }
        .medicion-table th {
            background-color: #e9ecef;
            font-weight: 600;
            font-size: 0.75rem;
        }
        .medicion-table .componente-cell {
            background-color: #f8f9fa;
            font-weight: 600;
            text-align: left;
            padding-left: 10px;
            white-space: nowrap;
        }

        /* Semaforo */
        .celda-verde { background-color: #198754 !important; color: #fff; font-weight: bold; }
        .celda-roja  { background-color: #dc3545 !important; color: #fff; font-weight: bold; }
        .celda-gris  { background-color: #e9ecef; color: #6c757d; }

        /* Info documento */
        .info-documento {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="bg-light">

<!-- ========================================== -->
<!-- TOOLBAR - Instructivo 2_AA_WEB seccion 4: bg-dark -->
<!-- ========================================== -->
<div class="no-print bg-dark text-white py-2 sticky-top">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <a href="<?= base_url('indicadores-sst/' . esc($idCliente)) ?>" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Volver
                </a>
                <span class="ms-3 d-none d-md-inline"><?= esc($cliente['nombre_cliente'] ?? '') ?> - Ficha Tecnica <?= esc($anio) ?></span>
            </div>
            <div>
                <!-- Year selector -->
                <div class="dropdown d-inline-block me-2">
                    <button class="btn btn-outline-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-calendar3 me-1"></i><?= esc($anio) ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <?php for ($y = 2035; $y >= 2025; $y--): ?>
                        <li>
                            <a class="dropdown-item <?= ($y == $anio) ? 'active' : '' ?>"
                               href="<?= base_url('indicadores-sst/' . esc($idCliente) . '/ficha-tecnica/' . esc($idIndicador) . '?anio=' . $y) ?>">
                                <?= $y ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </div>
                <a href="<?= base_url('indicadores-sst/' . esc($idCliente) . '/ficha-tecnica/' . esc($idIndicador) . '/pdf?anio=' . esc($anio)) ?>"
                   class="btn btn-danger btn-sm me-2">
                    <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                </a>
                <a href="<?= base_url('indicadores-sst/' . esc($idCliente) . '/ficha-tecnica/' . esc($idIndicador) . '/word?anio=' . esc($anio)) ?>"
                   class="btn btn-primary btn-sm me-2">
                    <i class="bi bi-file-earmark-word me-1"></i>Word
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MAIN CONTENT - Instructivo 2_AA_WEB seccion 12: max-width 900px, padding 40px -->
<!-- ========================================== -->
<div class="container my-4">
    <div class="bg-white shadow documento-contenido" style="padding: 40px; max-width: 900px; margin: 0 auto;">

        <!-- Info documento (no imprimible) -->
        <div class="info-documento no-print">
            <div class="row">
                <div class="col-md-4">
                    <small class="text-muted">Tipo de documento:</small>
                    <span class="fw-bold">Ficha Tecnica</span>
                </div>
                <div class="col-md-4">
                    <small class="text-muted">Periodo:</small>
                    <span class="fw-bold"><?= esc($anio) ?></span>
                </div>
                <div class="col-md-4">
                    <small class="text-muted">Codigo:</small>
                    <span class="fw-bold"><?= esc($codigo) ?></span>
                </div>
            </div>
        </div>

        <!-- ============================== -->
        <!-- ENCABEZADO FORMAL -->
        <!-- ============================== -->
        <table class="encabezado-formal" cellpadding="0" cellspacing="0">
            <tr>
                <td class="encabezado-logo" rowspan="2">
                    <?php if (!empty($cliente['logo'])): ?>
                        <img src="<?= base_url('uploads/' . esc($cliente['logo'])) ?>" alt="Logo">
                    <?php else: ?>
                        <div style="font-size: 0.7rem; color: #666;">
                            <strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong>
                        </div>
                    <?php endif; ?>
                </td>
                <td class="encabezado-titulo-central">
                    <div class="sistema">SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO</div>
                </td>
                <td class="encabezado-info" rowspan="2">
                    <table class="encabezado-info-table" cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="label">Codigo:</td>
                            <td><?= esc($codigo) ?></td>
                        </tr>
                        <tr>
                            <td class="label">Version:</td>
                            <td>001</td>
                        </tr>
                        <tr>
                            <td class="label">Fecha:</td>
                            <td><?= $fechaActual ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="encabezado-titulo-central">
                    <div class="nombre-doc">FICHA TECNICA DE INDICADOR</div>
                </td>
            </tr>
        </table>

        <!-- ============================== -->
        <!-- SECCION 1: INFORMACION DEL INDICADOR -->
        <!-- ============================== -->
        <div class="seccion">
            <div class="seccion-titulo">1. INFORMACION DEL INDICADOR</div>
            <?php
            $sinDato = '<span class="text-muted fst-italic" style="font-size:0.8rem;">Sin diligenciar - Edite el indicador para completar</span>';
            $val = function(string $campo) use ($indicador, $sinDato) {
                $v = $indicador[$campo] ?? '';
                return ($v !== '') ? esc($v) : $sinDato;
            };
            ?>
            <table class="ficha-table">
                <tr>
                    <td class="label-cell">Nombre del Indicador</td>
                    <td><?= esc($indicador['nombre_indicador'] ?? '') ?></td>
                </tr>
                <tr>
                    <td class="label-cell">Definicion</td>
                    <td><?= $val('definicion') ?></td>
                </tr>
                <tr>
                    <td class="label-cell">Interpretacion</td>
                    <td><?= $val('interpretacion') ?></td>
                </tr>
                <tr>
                    <td class="label-cell">Meta</td>
                    <td>
                        <?php
                        $meta = $indicador['meta'] ?? '';
                        $unidad = $indicador['unidad_medida'] ?? '';
                        echo esc($meta);
                        if ($unidad) echo ' ' . esc($unidad);
                        ?>
                    </td>
                </tr>
                <tr>
                    <td class="label-cell">Formula</td>
                    <td><code><?= esc($indicador['formula'] ?? '') ?></code></td>
                </tr>
                <tr>
                    <td class="label-cell">Frecuencia de Medicion</td>
                    <td><?= esc(ucfirst($indicador['periodicidad'] ?? '')) ?></td>
                </tr>
                <tr>
                    <td class="label-cell">Origen de los Datos</td>
                    <td><?= $val('origen_datos') ?></td>
                </tr>
                <tr>
                    <td class="label-cell">Cargo Responsable</td>
                    <td><?= $val('cargo_responsable') ?></td>
                </tr>
                <tr>
                    <td class="label-cell">Cargos que Conocen el Resultado</td>
                    <td><?= $val('cargos_conocer_resultado') ?></td>
                </tr>
                <tr>
                    <td class="label-cell">Tipo de Indicador</td>
                    <td>
                        <?php
                        $badgeClass = match($indicador['tipo_indicador'] ?? '') {
                            'estructura' => 'bg-info',
                            'proceso'    => 'bg-warning text-dark',
                            'resultado'  => 'bg-success',
                            default      => 'bg-secondary',
                        };
                        ?>
                        <span class="badge <?= $badgeClass ?>"><?= esc($tipoIndicadorTexto) ?></span>
                    </td>
                </tr>
                <tr>
                    <td class="label-cell">Base Legal (Numeral Resolucion)</td>
                    <td><?= esc($indicador['numeral_resolucion'] ?? '') ?></td>
                </tr>
                <tr>
                    <td class="label-cell">Ciclo PHVA</td>
                    <td>
                        <?php if ($phvaValor): ?>
                            <span class="badge bg-primary"><?= esc(strtoupper($phvaValor)) ?></span>
                            <span class="ms-1"><?= esc($phvaTexto) ?></span>
                        <?php else: ?>
                            <?= $sinDato ?>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>

        <!-- ============================== -->
        <!-- SECCION 2: MEDICION -->
        <!-- ============================== -->
        <?php
        $periodicidad = strtolower($indicador['periodicidad'] ?? 'mensual');
        $metaValor = $indicador['meta'] ?? 0;
        ?>
        <div class="seccion">
            <div class="seccion-titulo">2. MEDICION - <?= esc(strtoupper($periodicidad)) ?> (<?= esc($anio) ?>)</div>
            <div class="table-responsive">
                <table class="medicion-table">
                    <thead>
                        <tr>
                            <th style="width: 120px;">Componente</th>
                            <?php foreach ($periodos as $p): ?>
                                <th><?= esc($p['label'] ?? '') ?></th>
                            <?php endforeach; ?>
                            <th class="bg-primary text-white">ACUM</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Fila Numerador -->
                        <tr>
                            <td class="componente-cell">Numerador</td>
                            <?php foreach ($periodos as $p): ?>
                                <td>
                                    <?php if (isset($p['numerador']) && $p['numerador'] !== null): ?>
                                        <?= number_format((float)$p['numerador'], 2) ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                            <td class="fw-bold">
                                <?php if (isset($acumulado['numerador']) && $acumulado['numerador'] !== null): ?>
                                    <?= number_format((float)$acumulado['numerador'], 2) ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <!-- Fila Denominador -->
                        <tr>
                            <td class="componente-cell">Denominador</td>
                            <?php foreach ($periodos as $p): ?>
                                <td>
                                    <?php if (isset($p['denominador']) && $p['denominador'] !== null): ?>
                                        <?= number_format((float)$p['denominador'], 2) ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                            <td class="fw-bold">
                                <?php if (isset($acumulado['denominador']) && $acumulado['denominador'] !== null): ?>
                                    <?= number_format((float)$acumulado['denominador'], 2) ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <!-- Fila Resultado (con semaforo) -->
                        <tr>
                            <td class="componente-cell">Resultado</td>
                            <?php foreach ($periodos as $p): ?>
                                <?php
                                $resultado = $p['resultado'] ?? null;
                                $cumple    = $p['cumple_meta'] ?? null;
                                $claseCell = 'celda-gris';
                                if ($resultado !== null && $cumple !== null) {
                                    $claseCell = ((int)$cumple === 1) ? 'celda-verde' : 'celda-roja';
                                }
                                ?>
                                <td class="<?= $claseCell ?>">
                                    <?php if ($resultado !== null): ?>
                                        <?= number_format((float)$resultado, 2) ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                            <?php
                            $acumResultado = $acumulado['resultado'] ?? null;
                            $acumClase = 'celda-gris';
                            if ($acumResultado !== null && $metaValor > 0) {
                                $acumClase = ((float)$acumResultado >= (float)$metaValor) ? 'celda-verde' : 'celda-roja';
                            }
                            ?>
                            <td class="fw-bold <?= $acumClase ?>">
                                <?php if ($acumResultado !== null): ?>
                                    <?= number_format((float)$acumResultado, 2) ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                        <!-- Fila Meta -->
                        <tr>
                            <td class="componente-cell">Meta</td>
                            <?php foreach ($periodos as $p): ?>
                                <td class="text-primary fw-semibold"><?= esc($metaValor) ?></td>
                            <?php endforeach; ?>
                            <td class="text-primary fw-bold"><?= esc($metaValor) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Leyenda semaforo -->
            <div class="d-flex gap-3 mt-2 small">
                <span><span class="badge" style="background-color: #198754;">&nbsp;&nbsp;&nbsp;</span> Cumple meta</span>
                <span><span class="badge" style="background-color: #dc3545;">&nbsp;&nbsp;&nbsp;</span> No cumple meta</span>
                <span><span class="badge bg-secondary">&nbsp;&nbsp;&nbsp;</span> Sin datos</span>
            </div>
        </div>

        <!-- ============================== -->
        <!-- SECCION 3: GRAFICA -->
        <!-- ============================== -->
        <div class="seccion">
            <div class="seccion-titulo">3. GRAFICA DE TENDENCIA</div>
            <div class="p-3 border" style="background: #fff;">
                <canvas id="chartIndicador" height="100"></canvas>
            </div>
        </div>

        <!-- ============================== -->
        <!-- SECCION 4: ANALISIS DE DATOS -->
        <!-- ============================== -->
        <div class="seccion">
            <div class="seccion-titulo">4. ANALISIS DE DATOS</div>
            <div style="min-height: 60px;">
                <?php if (!empty($indicador['analisis_datos'])): ?>
                    <p class="mb-0"><?= nl2br(esc($indicador['analisis_datos'])) ?></p>
                <?php else: ?>
                    <p class="mb-0 text-muted fst-italic">Pendiente de analisis</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- ============================== -->
        <!-- SECCION 5: SEGUIMIENTO / PLAN DE ACCION -->
        <!-- ============================== -->
        <div class="seccion">
            <div class="seccion-titulo">5. SEGUIMIENTO / PLAN DE ACCION</div>
            <table class="ficha-table">
                <tr>
                    <td class="label-cell">Requiere Plan de Accion</td>
                    <td>
                        <?php
                        $requiere = $indicador['requiere_plan_accion'] ?? null;
                        if ($requiere !== null && (int)$requiere === 1): ?>
                            <span class="badge bg-danger">SI</span>
                        <?php elseif ($requiere !== null && (int)$requiere === 0): ?>
                            <span class="badge bg-success">NO</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">N/A</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php if (!empty($indicador['numero_accion'])): ?>
                <tr>
                    <td class="label-cell">Numero de Accion</td>
                    <td><?= esc($indicador['numero_accion']) ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td class="label-cell">Acciones de Mejora</td>
                    <td>
                        <?php if (!empty($indicador['acciones_mejora'])): ?>
                            <?= nl2br(esc($indicador['acciones_mejora'])) ?>
                        <?php else: ?>
                            <span class="text-muted fst-italic">Sin acciones registradas</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td class="label-cell">Observaciones</td>
                    <td>
                        <?php if (!empty($indicador['observaciones'])): ?>
                            <?= nl2br(esc($indicador['observaciones'])) ?>
                        <?php else: ?>
                            <span class="text-muted fst-italic">Sin observaciones</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>

        <!-- ============================== -->
        <!-- CONTROL DE CAMBIOS - Instructivo 2_AA_WEB seccion 9: gradiente azul-morado -->
        <!-- ============================== -->
        <div class="seccion" style="page-break-inside: avoid; margin-top: 40px;">
            <div class="seccion-titulo" style="background: linear-gradient(90deg, #0d6efd, #6610f2); color: white; padding: 10px 15px; border-radius: 5px; margin-bottom: 0; border: none;">
                <i class="bi bi-journal-text me-2"></i>CONTROL DE CAMBIOS
            </div>
            <table class="table table-bordered mb-0" style="font-size: 0.85rem; border-top: none;">
                <thead>
                    <tr style="background: linear-gradient(135deg, #f8f9fa, #e9ecef);">
                        <th style="width: 100px; text-align: center;">Version</th>
                        <th>Descripcion del Cambio</th>
                        <th style="width: 130px; text-align: center;">Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($versiones)): ?>
                        <?php foreach ($versiones as $ver): ?>
                        <tr>
                            <td style="text-align: center;">
                                <span style="display: inline-block; background: #0d6efd; color: white; padding: 3px 12px; border-radius: 20px;">
                                    <?= esc($ver['version_texto']) ?>
                                </span>
                            </td>
                            <td><?= esc($ver['descripcion_cambio']) ?></td>
                            <td style="text-align: center;"><?= date('d/m/Y', strtotime($ver['fecha_autorizacion'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td style="text-align: center;">
                                <span style="display: inline-block; background: #0d6efd; color: white; padding: 3px 12px; border-radius: 20px;">1.0</span>
                            </td>
                            <td>Elaboracion inicial del documento</td>
                            <td style="text-align: center;"><?= $fechaActual ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- ============================== -->
        <!-- FIRMAS - Instructivo 2_AA_WEB seccion 10: gradiente verde, dinamico -->
        <!-- ============================== -->
        <div class="firma-section" style="margin-top: 40px; page-break-inside: avoid;">
            <div class="seccion-titulo" style="background: linear-gradient(90deg, #198754, #20c997); color: white; padding: 10px 15px; border-radius: 5px; margin-bottom: 0; border: none;">
                <i class="bi bi-pen me-2"></i>FIRMAS DE APROBACION
            </div>

            <?php if ($requiereDelegado): ?>
            <!-- 3 firmantes: Consultor + Delegado SST + Rep. Legal -->
            <table class="table table-bordered mb-0" style="font-size: 0.85rem; border-top: none;">
                <thead>
                    <tr style="background-color: #e9ecef;">
                        <th style="width: 33.33%; text-align: center; font-weight: bold; color: #333; border-top: none;">Elaboro / Consultor SST</th>
                        <th style="width: 33.33%; text-align: center; font-weight: bold; color: #333; border-top: none;">Reviso / Delegado SST</th>
                        <th style="width: 33.33%; text-align: center; font-weight: bold; color: #333; border-top: none;">Aprobo / Representante Legal</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <!-- Consultor SST -->
                        <td style="vertical-align: top; padding: 15px; height: 160px; position: relative;">
                            <div style="margin-bottom: 6px;">
                                <strong style="color: #495057; font-size: 0.8rem;">Nombre:</strong>
                                <span style="border-bottom: 1px dotted #999; display: inline-block; min-width: 120px; padding-bottom: 2px; font-size: 0.85rem;">
                                    <?= !empty($consultorNombre) ? esc($consultorNombre) : '' ?>
                                </span>
                            </div>
                            <div style="margin-bottom: 6px;">
                                <strong style="color: #495057; font-size: 0.8rem;">Cargo:</strong>
                                <span style="font-size: 0.85rem;"><?= esc($consultorCargo) ?></span>
                            </div>
                            <?php if (!empty($consultorLicencia)): ?>
                            <div style="margin-bottom: 6px;">
                                <strong style="color: #495057; font-size: 0.8rem;">Licencia SST:</strong>
                                <span style="font-size: 0.85rem;"><?= esc($consultorLicencia) ?></span>
                            </div>
                            <?php endif; ?>
                            <div style="position: absolute; bottom: 12px; left: 15px; right: 15px; text-align: center;">
                                <?php if (!empty($firmaConsultorFisica)): ?>
                                    <img src="<?= base_url('uploads/' . $firmaConsultorFisica) ?>" alt="Firma Consultor" style="max-height: 56px; max-width: 168px; margin-bottom: 3px;">
                                <?php endif; ?>
                                <div style="border-top: 1px solid #333; width: 85%; margin: 0 auto; padding-top: 4px;">
                                    <small style="color: #666; font-size: 0.7rem;">Firma</small>
                                </div>
                            </div>
                        </td>
                        <!-- Delegado SST -->
                        <td style="vertical-align: top; padding: 15px; height: 160px; position: relative;">
                            <div style="margin-bottom: 6px;">
                                <strong style="color: #495057; font-size: 0.8rem;">Nombre:</strong>
                                <span style="border-bottom: 1px dotted #999; display: inline-block; min-width: 120px; padding-bottom: 2px; font-size: 0.85rem;">
                                    <?= !empty($delegadoNombre) ? esc($delegadoNombre) : '' ?>
                                </span>
                            </div>
                            <div style="margin-bottom: 6px;">
                                <strong style="color: #495057; font-size: 0.8rem;">Cargo:</strong>
                                <span style="font-size: 0.85rem;"><?= esc($delegadoCargo) ?></span>
                            </div>
                            <div style="position: absolute; bottom: 12px; left: 15px; right: 15px; text-align: center;">
                                <div style="border-top: 1px solid #333; width: 85%; margin: 0 auto; padding-top: 4px;">
                                    <small style="color: #666; font-size: 0.7rem;">Firma</small>
                                </div>
                            </div>
                        </td>
                        <!-- Representante Legal -->
                        <td style="vertical-align: top; padding: 15px; height: 160px; position: relative;">
                            <div style="margin-bottom: 6px;">
                                <strong style="color: #495057; font-size: 0.8rem;">Nombre:</strong>
                                <span style="border-bottom: 1px dotted #999; display: inline-block; min-width: 120px; padding-bottom: 2px; font-size: 0.85rem;">
                                    <?= !empty($repLegalNombre) ? esc($repLegalNombre) : '' ?>
                                </span>
                            </div>
                            <div style="margin-bottom: 6px;">
                                <strong style="color: #495057; font-size: 0.8rem;">Cargo:</strong>
                                <span style="font-size: 0.85rem;">Representante Legal</span>
                            </div>
                            <div style="position: absolute; bottom: 12px; left: 15px; right: 15px; text-align: center;">
                                <div style="border-top: 1px solid #333; width: 85%; margin: 0 auto; padding-top: 4px;">
                                    <small style="color: #666; font-size: 0.7rem;">Firma</small>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php else: ?>
            <!-- 2 firmantes: Consultor SST + Representante Legal -->
            <table class="table table-bordered mb-0" style="font-size: 0.85rem; border-top: none;">
                <thead>
                    <tr style="background-color: #e9ecef;">
                        <th style="width: 50%; text-align: center; font-weight: bold; color: #333; border-top: none;">Elaboro / Consultor SST</th>
                        <th style="width: 50%; text-align: center; font-weight: bold; color: #333; border-top: none;">Aprobo / Representante Legal</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <!-- Consultor SST -->
                        <td style="vertical-align: top; padding: 25px; height: 200px; position: relative;">
                            <div style="margin-bottom: 8px;">
                                <strong style="color: #495057;">Nombre:</strong>
                                <span style="border-bottom: 1px dotted #999; display: inline-block; min-width: 200px; padding-bottom: 2px;">
                                    <?= !empty($consultorNombre) ? esc($consultorNombre) : '' ?>
                                </span>
                            </div>
                            <div style="margin-bottom: 8px;">
                                <strong style="color: #495057;">Cargo:</strong>
                                <span><?= esc($consultorCargo) ?></span>
                            </div>
                            <?php if (!empty($consultorLicencia)): ?>
                            <div style="margin-bottom: 8px;">
                                <strong style="color: #495057;">Licencia SST:</strong>
                                <span><?= esc($consultorLicencia) ?></span>
                            </div>
                            <?php endif; ?>
                            <div style="position: absolute; bottom: 15px; left: 25px; right: 25px; text-align: center;">
                                <?php if (!empty($firmaConsultorFisica)): ?>
                                    <img src="<?= base_url('uploads/' . $firmaConsultorFisica) ?>" alt="Firma Consultor" style="max-height: 70px; max-width: 200px; margin-bottom: 3px;">
                                <?php endif; ?>
                                <div style="border-top: 1px solid #333; width: 60%; margin: 0 auto; padding-top: 5px;">
                                    <small style="color: #666;">Firma</small>
                                </div>
                            </div>
                        </td>
                        <!-- Representante Legal -->
                        <td style="vertical-align: top; padding: 25px; height: 200px; position: relative;">
                            <div style="margin-bottom: 8px;">
                                <strong style="color: #495057;">Nombre:</strong>
                                <span style="border-bottom: 1px dotted #999; display: inline-block; min-width: 200px; padding-bottom: 2px;">
                                    <?= !empty($repLegalNombre) ? esc($repLegalNombre) : '' ?>
                                </span>
                            </div>
                            <div style="margin-bottom: 8px;">
                                <strong style="color: #495057;">Cargo:</strong>
                                <span>Representante Legal</span>
                            </div>
                            <div style="position: absolute; bottom: 15px; left: 25px; right: 25px; text-align: center;">
                                <div style="border-top: 1px solid #333; width: 60%; margin: 0 auto; padding-top: 5px;">
                                    <small style="color: #666;">Firma</small>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <!-- Pie de documento - Instructivo 2_AA_WEB seccion 11 -->
        <div class="text-center text-muted mt-4 pt-3 border-top" style="font-size: 0.75rem;">
            <p class="mb-1">Documento generado el <?= $fechaActual ?> - Sistema de Gestion SST</p>
            <p class="mb-0"><?= esc($cliente['nombre_cliente'] ?? '') ?> - NIT: <?= esc($cliente['nit_cliente'] ?? '') ?></p>
        </div>

    </div><!-- documento-contenido -->
</div><!-- container -->

<!-- ========================================== -->
<!-- SCRIPTS -->
<!-- ========================================== -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const labels = <?= json_encode(array_map(function($p) { return $p['label'] ?? ''; }, $periodos)) ?>;
    const resultados = <?= json_encode(array_map(function($p) { return $p['resultado'] !== null ? (float)$p['resultado'] : null; }, $periodos)) ?>;
    const metaValor = <?= json_encode((float)($indicador['meta'] ?? 0)) ?>;
    const metaLine = labels.map(() => metaValor);

    const ctx = document.getElementById('chartIndicador').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Resultado',
                    data: resultados,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: '#0d6efd',
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    fill: true,
                    tension: 0.3,
                    spanGaps: false
                },
                {
                    label: 'Meta (<?= esc($metaValor) ?>)',
                    data: metaLine,
                    borderColor: '#dc3545',
                    borderWidth: 2,
                    borderDash: [8, 4],
                    pointRadius: 0,
                    fill: false,
                    tension: 0
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { usePointStyle: true, padding: 20 }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let val = context.parsed.y;
                            if (val === null || val === undefined) return context.dataset.label + ': Sin datos';
                            return context.dataset.label + ': ' + val.toFixed(2);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: '<?= esc($indicador['unidad_medida'] ?? 'Valor') ?>' },
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                x: {
                    title: { display: true, text: 'Periodo (<?= esc($anio) ?>)' },
                    grid: { display: false }
                }
            }
        }
    });
});
</script>
</body>
</html>
