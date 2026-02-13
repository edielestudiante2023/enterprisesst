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

// Firma consultor: prioridad electronica > fisica (Instructivo 2_AA_WEB seccion 16)
$firmaConsultorElectronica = ($firmasElectronicas ?? [])['consultor_sst'] ?? null;
$firmaConsultorFisica = $consultor['firma_consultor'] ?? '';

// Firma delegado/vigia: electronica delegado > electronica vigia > fisica (Instructivo 3_AA_PDF_FIRMAS seccion 17)
$firmaDelegadoElectronica = ($firmasElectronicas ?? [])['delegado_sst'] ?? ($firmasElectronicas ?? [])['vigia_sst'] ?? null;

// Firma representante legal: electronica (Instructivo 3_AA_PDF_FIRMAS seccion 18)
$firmaRepLegalElectronica = ($firmasElectronicas ?? [])['representante_legal'] ?? null;
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
        .seccion-contenido {
            text-align: justify;
            line-height: 1.7;
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

        /* Panel aprobacion - Instructivo 2_AA_WEB seccion 5 */
        .panel-aprobacion {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            color: white;
        }

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
                <?php
                $idDocumento = $documento['id_documento'] ?? null;
                $estadoDoc = $documento['estado'] ?? 'generado';
                ?>
                <?php if ($idDocumento && in_array($estadoDoc, ['borrador', 'generado', 'aprobado', 'en_revision'])): ?>
                    <a href="<?= base_url('firma/solicitar/' . $idDocumento) ?>" class="btn btn-success btn-sm me-2" target="_blank">
                        <i class="bi bi-pen me-1"></i>Solicitar Firmas
                    </a>
                <?php endif; ?>
                <?php if ($idDocumento && $estadoDoc === 'firmado'): ?>
                    <a href="<?= base_url('firma/estado/' . $idDocumento) ?>" class="btn btn-outline-success btn-sm me-2">
                        <i class="bi bi-patch-check me-1"></i>Ver Firmas
                    </a>
                <?php endif; ?>
                <?php if ($idDocumento && $estadoDoc === 'pendiente_firma'): ?>
                    <a href="<?= base_url('firma/estado/' . $idDocumento) ?>" class="btn btn-outline-warning btn-sm me-2">
                        <i class="bi bi-clock-history me-1"></i>Estado Firmas
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MAIN CONTENT - Instructivo 2_AA_WEB seccion 12: max-width 900px, padding 40px -->
<!-- ========================================== -->
<div class="container my-4">
    <div class="bg-white shadow documento-contenido" style="padding: 40px; max-width: 900px; margin: 0 auto;">

        <!-- Panel Aprobacion - Instructivo 2_AA_WEB seccion 5 -->
        <?php if ($idDocumento): ?>
        <div class="panel-aprobacion no-print">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <span class="badge bg-dark"><?= esc($codigo) ?></span>
                        <span class="badge bg-light text-dark">v<?= esc(($versionVigente['version_texto'] ?? null) ?: (($documento['version'] ?? 1) . '.0')) ?></span>
                        <?php
                        $badgeEstado = match($estadoDoc) {
                            'firmado' => 'bg-success',
                            'pendiente_firma' => 'bg-warning text-dark',
                            'aprobado' => 'bg-info',
                            'en_revision' => 'bg-secondary',
                            default => 'bg-light text-dark',
                        };
                        $textoEstado = match($estadoDoc) {
                            'firmado' => 'Firmado',
                            'pendiente_firma' => 'Pendiente Firma',
                            'aprobado' => 'Aprobado',
                            'en_revision' => 'En Revision',
                            'generado' => 'Generado',
                            default => ucfirst($estadoDoc),
                        };
                        $iconoEstado = match($estadoDoc) {
                            'firmado' => 'bi-patch-check-fill',
                            'pendiente_firma' => 'bi-pen',
                            'aprobado' => 'bi-check-circle',
                            default => 'bi-file-earmark-text',
                        };
                        ?>
                        <span class="badge <?= $badgeEstado ?>">
                            <i class="bi <?= $iconoEstado ?> me-1"></i><?= $textoEstado ?>
                        </span>
                    </div>
                    <small class="opacity-75">Ficha Tecnica - <?= esc($indicador['nombre_indicador'] ?? '') ?></small>
                </div>
                <div class="col-md-4 text-end">
                    <?php if ($estadoDoc === 'borrador'): ?>
                        <button type="button" class="btn btn-sm btn-warning me-1" id="btnCancelarEdicion" title="Cancelar edicion y volver al estado anterior">
                            <i class="bi bi-x-circle me-1"></i>Cancelar Edicion
                        </button>
                    <?php endif; ?>
                    <?php if ($estadoDoc === 'aprobado' || $estadoDoc === 'firmado'): ?>
                        <button type="button" class="btn btn-sm btn-outline-light me-1" data-bs-toggle="modal" data-bs-target="#modalNuevaVersion" title="Crear nueva version">
                            <i class="bi bi-plus-circle me-1"></i>Nueva Version
                        </button>
                    <?php endif; ?>
                    <?php if (!empty($versiones)): ?>
                        <button type="button" class="btn btn-sm btn-outline-light" data-bs-toggle="modal" data-bs-target="#modalHistorialVersiones" title="Ver historial">
                            <i class="bi bi-clock-history me-1"></i>Historial
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

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
            <?php
            // Auto-generar resumen de tendencia desde periodos
            $totalMedidos = 0;
            $totalDesviados = 0;
            $resultadosArr = [];
            foreach ($periodos as $p) {
                if ($p['resultado'] !== null) {
                    $totalMedidos++;
                    $resultadosArr[] = (float)$p['resultado'];
                    if ($p['cumple_meta'] !== null && (int)$p['cumple_meta'] === 0) {
                        $totalDesviados++;
                    }
                }
            }
            if ($totalMedidos > 0):
                $tendencia = 'estable';
                if (count($resultadosArr) >= 2) {
                    $ultimo = end($resultadosArr);
                    $penultimo = prev($resultadosArr);
                    if ($ultimo > $penultimo) $tendencia = 'ascendente';
                    elseif ($ultimo < $penultimo) $tendencia = 'descendente';
                }
                $iconoTendencia = match($tendencia) {
                    'ascendente' => '<i class="bi bi-arrow-up-right text-success"></i>',
                    'descendente' => '<i class="bi bi-arrow-down-right text-danger"></i>',
                    default => '<i class="bi bi-arrow-right text-secondary"></i>',
                };
            ?>
            <div class="mt-2 p-2 border rounded" style="background: #f8f9fa; font-size: 0.85rem;">
                <?= $iconoTendencia ?>
                <strong>Resumen:</strong>
                <?= $totalMedidos ?> de <?= count($periodos) ?> periodos medidos.
                <?php if ($totalDesviados > 0): ?>
                    <span class="text-danger"><?= $totalDesviados ?> con desviacion de la meta.</span>
                <?php else: ?>
                    <span class="text-success">Todos cumplen la meta.</span>
                <?php endif; ?>
                Tendencia: <strong><?= $tendencia ?></strong>.
                Promedio: <strong><?= round(array_sum($resultadosArr) / count($resultadosArr), 2) ?></strong><?= esc($indicador['unidad_medida'] ?? '') ?>.
            </div>
            <?php endif; ?>
        </div>

        <!-- ============================== -->
        <!-- SECCION 4: ANALISIS DE DATOS (editable inline) -->
        <!-- ============================== -->
        <div class="seccion">
            <div class="seccion-titulo d-flex justify-content-between align-items-center">
                <span>4. ANALISIS DE DATOS</span>
                <button class="btn btn-sm btn-outline-primary d-print-none" onclick="activarEdicion('analisis_datos')" title="Editar">
                    <i class="bi bi-pencil-square"></i>
                </button>
            </div>
            <div style="min-height: 60px;" id="display-analisis_datos" onclick="activarEdicion('analisis_datos')" style="cursor:pointer;">
                <?php if (!empty($indicador['analisis_datos'])): ?>
                    <p class="mb-0"><?= nl2br(esc($indicador['analisis_datos'])) ?></p>
                <?php else: ?>
                    <p class="mb-0 text-muted fst-italic">Haz clic para agregar analisis de datos...</p>
                <?php endif; ?>
            </div>
            <div id="edit-analisis_datos" class="d-none p-2">
                <textarea class="form-control" id="input-analisis_datos" rows="4" placeholder="Escriba el analisis de datos del indicador..."><?= esc($indicador['analisis_datos'] ?? '') ?></textarea>
                <div class="mt-2 d-flex gap-2">
                    <button class="btn btn-sm btn-success" onclick="guardarCampo('analisis_datos')"><i class="bi bi-check-lg me-1"></i>Guardar</button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="cancelarEdicion('analisis_datos')">Cancelar</button>
                    <span id="status-analisis_datos" class="ms-2 align-self-center"></span>
                </div>
            </div>
        </div>

        <!-- ============================== -->
        <!-- SECCION 5: SEGUIMIENTO Y ALERTAS DE DESVIACION -->
        <!-- ============================== -->
        <?php
        // Computar datos de desviacion desde periodos
        $alerta = $indicador['requiere_plan_accion'] ?? null;
        $periodosDesviados = [];
        $metaValorNum = (float)($indicador['meta'] ?? 0);
        foreach ($periodos as $p) {
            if ($p['cumple_meta'] !== null && (int)$p['cumple_meta'] === 0) {
                $periodosDesviados[] = [
                    'label' => $p['label'] ?? '',
                    'resultado' => $p['resultado'],
                    'desviacion' => $metaValorNum > 0 ? round(abs((float)$p['resultado'] - $metaValorNum), 2) : null,
                ];
            }
        }
        ?>
        <div class="seccion">
            <div class="seccion-titulo">5. SEGUIMIENTO Y ALERTAS DE DESVIACION</div>
            <table class="ficha-table">
                <!-- Estado de Alerta (AUTO-COMPUTADO) -->
                <tr>
                    <td class="label-cell">Estado de Alerta</td>
                    <td>
                        <?php if ($alerta !== null && (int)$alerta === 1): ?>
                            <span class="badge bg-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i>DESVIACION DETECTADA</span>
                        <?php elseif ($alerta !== null && (int)$alerta === 0): ?>
                            <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>SIN DESVIACION</span>
                        <?php else: ?>
                            <span class="badge bg-secondary"><i class="bi bi-dash-circle me-1"></i>PENDIENTE DE MEDICION</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <!-- Codigo de Alerta (AUTO, visible solo si hay desviacion) -->
                <?php if ($alerta !== null && (int)$alerta === 1): ?>
                <tr>
                    <td class="label-cell">Codigo de Alerta</td>
                    <td>
                        <span class="badge bg-warning text-dark" style="font-size:0.85rem;"><?= esc($indicador['numero_accion'] ?? '') ?></span>
                    </td>
                </tr>
                <!-- Periodos con desviacion (AUTO-COMPUTADO) -->
                <?php if (!empty($periodosDesviados)): ?>
                <tr>
                    <td class="label-cell">Periodos Desviados</td>
                    <td>
                        <?php foreach ($periodosDesviados as $pd): ?>
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger me-1 mb-1" style="font-size:0.8rem;">
                                <?= esc($pd['label']) ?>: <?= $pd['resultado'] ?><?= esc($indicador['unidad_medida'] ?? '') ?>
                                <?php if ($pd['desviacion'] !== null): ?>
                                    <small>(desvio: <?= $pd['desviacion'] ?>)</small>
                                <?php endif; ?>
                            </span>
                        <?php endforeach; ?>
                        <div class="mt-1">
                            <small class="text-muted">Meta: <?= $metaValorNum ?><?= esc($indicador['unidad_medida'] ?? '') ?></small>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
                <?php endif; ?>
                <!-- Acciones de Ajuste (editable inline) -->
                <tr>
                    <td class="label-cell">
                        Acciones de Ajuste
                        <button class="btn btn-sm btn-outline-primary d-print-none ms-1" onclick="activarEdicion('acciones_mejora')" title="Editar" style="padding:0 4px;"><i class="bi bi-pencil-square" style="font-size:0.7rem;"></i></button>
                    </td>
                    <td>
                        <div id="display-acciones_mejora" onclick="activarEdicion('acciones_mejora')" style="cursor:pointer;">
                            <?php if (!empty($indicador['acciones_mejora'])): ?>
                                <?= nl2br(esc($indicador['acciones_mejora'])) ?>
                            <?php else: ?>
                                <span class="text-muted fst-italic">Clic para registrar acciones de ajuste...</span>
                            <?php endif; ?>
                        </div>
                        <div id="edit-acciones_mejora" class="d-none mt-1">
                            <textarea class="form-control form-control-sm" id="input-acciones_mejora" rows="3" placeholder="Describa las acciones de ajuste operativo..."><?= esc($indicador['acciones_mejora'] ?? '') ?></textarea>
                            <div class="mt-1 d-flex gap-1">
                                <button class="btn btn-sm btn-success" onclick="guardarCampo('acciones_mejora')"><i class="bi bi-check-lg me-1"></i>Guardar</button>
                                <button class="btn btn-sm btn-outline-secondary" onclick="cancelarEdicion('acciones_mejora')">Cancelar</button>
                                <span id="status-acciones_mejora" class="ms-2 align-self-center"></span>
                            </div>
                        </div>
                    </td>
                </tr>
                <!-- Observaciones (editable inline) -->
                <tr>
                    <td class="label-cell">
                        Observaciones
                        <button class="btn btn-sm btn-outline-primary d-print-none ms-1" onclick="activarEdicion('observaciones')" title="Editar" style="padding:0 4px;"><i class="bi bi-pencil-square" style="font-size:0.7rem;"></i></button>
                    </td>
                    <td>
                        <div id="display-observaciones" onclick="activarEdicion('observaciones')" style="cursor:pointer;">
                            <?php if (!empty($indicador['observaciones'])): ?>
                                <?= nl2br(esc($indicador['observaciones'])) ?>
                            <?php else: ?>
                                <span class="text-muted fst-italic">Clic para agregar observaciones...</span>
                            <?php endif; ?>
                        </div>
                        <div id="edit-observaciones" class="d-none mt-1">
                            <textarea class="form-control form-control-sm" id="input-observaciones" rows="3" placeholder="Observaciones adicionales..."><?= esc($indicador['observaciones'] ?? '') ?></textarea>
                            <div class="mt-1 d-flex gap-1">
                                <button class="btn btn-sm btn-success" onclick="guardarCampo('observaciones')"><i class="bi bi-check-lg me-1"></i>Guardar</button>
                                <button class="btn btn-sm btn-outline-secondary" onclick="cancelarEdicion('observaciones')">Cancelar</button>
                                <span id="status-observaciones" class="ms-2 align-self-center"></span>
                            </div>
                        </div>
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

            <?php if (!$esSoloDosFirmantes): ?>
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
                                <?php if ($firmaConsultorElectronica && !empty($firmaConsultorElectronica['evidencia']['firma_imagen'])): ?>
                                    <img src="<?= $firmaConsultorElectronica['evidencia']['firma_imagen'] ?>" alt="Firma Consultor" style="max-height: 56px; max-width: 168px; margin-bottom: 3px;">
                                <?php elseif (!empty($firmaConsultorFisica)): ?>
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
                                <?php if ($firmaDelegadoElectronica && !empty($firmaDelegadoElectronica['evidencia']['firma_imagen'])): ?>
                                    <img src="<?= $firmaDelegadoElectronica['evidencia']['firma_imagen'] ?>" alt="Firma Delegado" style="max-height: 56px; max-width: 168px; margin-bottom: 3px;">
                                <?php endif; ?>
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
                                <?php if ($firmaRepLegalElectronica && !empty($firmaRepLegalElectronica['evidencia']['firma_imagen'])): ?>
                                    <img src="<?= $firmaRepLegalElectronica['evidencia']['firma_imagen'] ?>" alt="Firma Rep. Legal" style="max-height: 56px; max-width: 168px; margin-bottom: 3px;">
                                <?php endif; ?>
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
                                <?php if ($firmaConsultorElectronica && !empty($firmaConsultorElectronica['evidencia']['firma_imagen'])): ?>
                                    <img src="<?= $firmaConsultorElectronica['evidencia']['firma_imagen'] ?>" alt="Firma Consultor" style="max-height: 70px; max-width: 200px; margin-bottom: 3px;">
                                <?php elseif (!empty($firmaConsultorFisica)): ?>
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
                                <?php if ($firmaRepLegalElectronica && !empty($firmaRepLegalElectronica['evidencia']['firma_imagen'])): ?>
                                    <img src="<?= $firmaRepLegalElectronica['evidencia']['firma_imagen'] ?>" alt="Firma Rep. Legal" style="max-height: 70px; max-width: 200px; margin-bottom: 3px;">
                                <?php endif; ?>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const labels = <?= json_encode(array_map(function($p) { return $p['label'] ?? ''; }, $periodos)) ?>;
    const resultados = <?= json_encode(array_map(function($p) { return $p['resultado'] !== null ? (float)$p['resultado'] : null; }, $periodos)) ?>;
    const cumpleMeta = <?= json_encode(array_map(function($p) { return $p['cumple_meta'] !== null ? (int)$p['cumple_meta'] : null; }, $periodos)) ?>;
    const metaValor = <?= json_encode((float)($indicador['meta'] ?? 0)) ?>;
    const metaLine = labels.map(() => metaValor);

    // Semaforo: color de cada punto segun cumplimiento de meta
    const puntosColor = cumpleMeta.map((c, i) => {
        if (resultados[i] === null) return '#adb5bd'; // gris - sin datos
        if (c === 1) return '#198754'; // verde - cumple
        if (c === 0) return '#dc3545'; // rojo - no cumple
        return '#0d6efd'; // azul - sin evaluar
    });
    const puntosBorde = puntosColor.map(c => c);

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
                    pointBackgroundColor: puntosColor,
                    pointBorderColor: puntosBorde,
                    pointRadius: 7,
                    pointHoverRadius: 9,
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

// ============================
// INLINE EDITING - Ficha Tecnica
// ============================
const urlActualizarCampo = '<?= base_url("indicadores-sst/{$idCliente}/ficha-tecnica/{$idIndicador}/actualizar-campo") ?>';

function activarEdicion(campo) {
    document.getElementById('display-' + campo)?.classList.add('d-none');
    document.getElementById('edit-' + campo)?.classList.remove('d-none');
    const input = document.getElementById('input-' + campo);
    if (input) input.focus();
}

function cancelarEdicion(campo) {
    document.getElementById('edit-' + campo)?.classList.add('d-none');
    document.getElementById('display-' + campo)?.classList.remove('d-none');
    const statusEl = document.getElementById('status-' + campo);
    if (statusEl) statusEl.innerHTML = '';
}

function guardarCampo(campo) {
    const input = document.getElementById('input-' + campo);
    if (!input) return;

    const valor = input.value;
    const statusEl = document.getElementById('status-' + campo);
    if (statusEl) statusEl.innerHTML = '<span class="text-info"><i class="bi bi-arrow-repeat spin-icon"></i> Guardando...</span>';

    fetch(urlActualizarCampo, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ campo: campo, valor: valor })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            if (statusEl) statusEl.innerHTML = '<span class="text-success"><i class="bi bi-check-circle-fill"></i> Guardado</span>';
            actualizarDisplay(campo, valor);
            setTimeout(() => cancelarEdicion(campo), 1200);
        } else {
            if (statusEl) statusEl.innerHTML = '<span class="text-danger"><i class="bi bi-exclamation-circle"></i> ' + (data.message || 'Error') + '</span>';
        }
    })
    .catch(err => {
        if (statusEl) statusEl.innerHTML = '<span class="text-danger"><i class="bi bi-exclamation-circle"></i> Error de conexion</span>';
    });
}

function actualizarDisplay(campo, valor) {
    const displayEl = document.getElementById('display-' + campo);
    if (!displayEl) return;

    if (campo === 'requiere_plan_accion') {
        let badge = '';
        if (valor === '1') badge = '<span class="badge bg-danger">SI</span>';
        else if (valor === '0') badge = '<span class="badge bg-success">NO</span>';
        else badge = '<span class="badge bg-secondary">N/A</span>';
        displayEl.innerHTML = badge + ' <i class="bi bi-pencil text-muted ms-1 d-print-none" style="font-size:0.7rem;"></i>';
    } else if (campo === 'numero_accion') {
        displayEl.innerHTML = (valor ? escHtml(valor) : '<span class="text-muted fst-italic">Clic para agregar...</span>')
            + ' <i class="bi bi-pencil text-muted ms-1 d-print-none" style="font-size:0.7rem;"></i>';
    } else {
        // textarea fields: analisis_datos, acciones_mejora, observaciones
        if (valor && valor.trim()) {
            displayEl.innerHTML = '<p class="mb-0">' + escHtml(valor).replace(/\n/g, '<br>') + '</p>';
        } else {
            const placeholders = {
                'analisis_datos': 'Haz clic para agregar analisis de datos...',
                'acciones_mejora': 'Clic para agregar acciones de mejora...',
                'observaciones': 'Clic para agregar observaciones...'
            };
            displayEl.innerHTML = '<span class="text-muted fst-italic">' + (placeholders[campo] || 'Clic para editar...') + '</span>';
        }
    }
}

function escHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
<style>
    .spin-icon { animation: spin 1s linear infinite; display: inline-block; }
    @keyframes spin { 100% { transform: rotate(360deg); } }
    @media print { .d-print-none { display: none !important; } }
</style>

<?php if ($estadoDoc === 'borrador' && $idDocumento): ?>
<script>
document.getElementById('btnCancelarEdicion')?.addEventListener('click', async function() {
    const confirmacion = await Swal.fire({
        icon: 'question',
        title: 'Cancelar edicion?',
        text: 'El documento volvera al estado anterior (aprobado). Los cambios no guardados se perderan.',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Si, cancelar edicion',
        cancelButtonText: 'No, continuar editando'
    });
    if (!confirmacion.isConfirmed) return;

    this.disabled = true;
    this.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Cancelando...';

    try {
        const formData = new FormData();
        formData.append('id_documento', '<?= (int)$idDocumento ?>');
        const response = await fetch('<?= base_url('documentos-sst/cancelar-nueva-version') ?>', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            await Swal.fire({ icon: 'success', title: 'Edicion cancelada', text: result.message, confirmButtonText: 'Continuar' });
            window.location.reload();
        } else {
            throw new Error(result.message || 'Error al cancelar');
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({ icon: 'error', title: 'Error', text: error.message });
        this.disabled = false;
        this.innerHTML = '<i class="bi bi-x-circle me-1"></i>Cancelar Edicion';
    }
});
</script>
<?php endif; ?>

<?php
// Modales estandar de versionamiento (Instructivo 1_AA_VERSIONAMIENTO)
if ($idDocumento):
?>
<?= view('documentos_sst/_components/modal_nueva_version', [
    'id_documento' => $idDocumento,
    'version_actual' => ($versionVigente['version_texto'] ?? null) ?: (($documento['version'] ?? 1) . '.0'),
    'tipo_documento' => $documento['tipo_documento'] ?? 'ficha_tecnica_indicador'
]) ?>
<?= view('documentos_sst/_components/modal_historial_versiones', [
    'id_documento' => $idDocumento,
    'versiones' => $versiones ?? []
]) ?>
<?php endif; ?>

</body>
</html>
