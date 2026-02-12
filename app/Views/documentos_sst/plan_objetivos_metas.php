<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($titulo) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            .documento-contenido { padding: 20px !important; }
            body { font-size: 11pt; }
            .encabezado-formal { page-break-inside: avoid; }
        }

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

        .tabla-objetivos {
            font-size: 0.85rem;
        }

        .tabla-objetivos th {
            background-color: #198754;
            color: white;
            font-weight: 600;
        }

        .tabla-indicadores th {
            background-color: #0d6efd;
            color: white;
            font-weight: 600;
        }

        .firma-section {
            margin-top: 40px;
            padding-top: 0;
        }

        .firma-box {
            text-align: center;
            padding: 20px;
        }

        .firma-linea {
            border-top: 1px solid #333;
            width: 250px;
            margin: 40px auto 10px auto;
        }

        .tabla-control-cambios th,
        .tabla-firmas th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
        }

        .info-documento {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .panel-aprobacion {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            color: white;
        }

        .panel-aprobacion .estado-badge {
            font-size: 1rem;
            padding: 8px 16px;
        }

        @media print {
            .firma-section { page-break-inside: avoid; }
            .seccion:last-of-type { page-break-inside: avoid; }
        }
    </style>
</head>
<body class="bg-light">
    <!-- Barra de herramientas -->
    <div class="no-print bg-dark text-white py-2 sticky-top">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <a href="<?= base_url('documentacion/' . $cliente['id_cliente']) ?>" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>Volver
                    </a>
                    <span class="ms-3"><?= esc($cliente['nombre_cliente']) ?> - Plan Objetivos y Metas <?= $anio ?></span>
                </div>
                <div>
                    <!-- Editar con IA (Generador) -->
                    <a href="<?= base_url('documentos/generar/plan_objetivos_metas/' . $cliente['id_cliente']) ?>" class="btn btn-sm me-2" style="background: linear-gradient(90deg, #667eea, #764ba2); color: white;">
                        <i class="bi bi-stars me-1"></i>Editar con IA
                    </a>
                    <!-- Boton Historial -->
                    <button type="button" class="btn btn-outline-light btn-sm me-2" data-bs-toggle="modal" data-bs-target="#modalHistorialVersiones">
                        <i class="bi bi-clock-history me-1"></i>Historial
                    </button>
                    <!-- Exportar PDF -->
                    <a href="<?= base_url('documentos-sst/exportar-pdf/' . $documento['id_documento']) ?>" class="btn btn-danger btn-sm me-2">
                        <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                    </a>
                    <!-- Exportar Word -->
                    <a href="<?= base_url('documentos-sst/exportar-word/' . $documento['id_documento']) ?>" class="btn btn-primary btn-sm me-2">
                        <i class="bi bi-file-earmark-word me-1"></i>Word
                    </a>
                    <!-- Aprobar (primera vez - estado borrador) -->
                    <?php if (($documento['estado'] ?? '') === 'borrador'): ?>
                        <button type="button" class="btn btn-success btn-sm me-2" data-bs-toggle="modal" data-bs-target="#modalAprobarDocumento">
                            <i class="bi bi-check-circle me-1"></i>Aprobar Documento
                        </button>
                    <?php endif; ?>
                    <!-- Nueva Version (documento ya aprobado) -->
                    <?php if (in_array($documento['estado'] ?? '', ['aprobado', 'firmado'])): ?>
                        <button type="button" class="btn btn-warning btn-sm me-2" data-bs-toggle="modal" data-bs-target="#modalAprobarDocumento">
                            <i class="bi bi-plus-circle me-1"></i>Nueva Version
                        </button>
                    <?php endif; ?>
                    <!-- Solicitar Firmas (incluye borrador según 1_A_TROUBLESHOOTING_GENERACION_IA.md) -->
                    <?php if (in_array($documento['estado'] ?? '', ['borrador', 'generado', 'aprobado', 'en_revision', 'pendiente_firma'])): ?>
                        <a href="<?= base_url('firma/solicitar/' . $documento['id_documento']) ?>" class="btn btn-success btn-sm me-2">
                            <i class="bi bi-pen me-1"></i>Solicitar Firmas
                        </a>
                    <?php endif; ?>
                    <!-- Estado Firmas (pendiente_firma) -->
                    <?php if (($documento['estado'] ?? '') === 'pendiente_firma'): ?>
                        <a href="<?= base_url('firma/estado/' . $documento['id_documento']) ?>" class="btn btn-outline-warning btn-sm me-2">
                            <i class="bi bi-clock-history me-1"></i>Estado Firmas
                        </a>
                    <?php endif; ?>
                    <!-- Ver Firmas (documento firmado) -->
                    <?php if (($documento['estado'] ?? '') === 'firmado'): ?>
                        <a href="<?= base_url('firma/estado/' . $documento['id_documento']) ?>" class="btn btn-outline-success btn-sm me-2">
                            <i class="bi bi-patch-check me-1"></i>Ver Firmas
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Documento -->
    <div class="container my-4">
        <div class="bg-white shadow documento-contenido" style="padding: 40px; max-width: 900px; margin: 0 auto;">

            <!-- Panel de Informacion del Documento -->
            <div class="panel-aprobacion no-print">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <span class="badge bg-dark estado-badge"><?= esc($documento['codigo'] ?? 'POM-SST') ?></span>
                            <span class="badge bg-light text-dark estado-badge">v<?= $documento['version'] ?>.0</span>
                            <?php
                            $estadoClass = match($documento['estado']) {
                                'aprobado' => 'bg-success',
                                'generado' => 'bg-info',
                                'borrador' => 'bg-warning text-dark',
                                'firmado' => 'bg-primary',
                                'pendiente_firma' => 'bg-warning text-dark',
                                default => 'bg-secondary'
                            };
                            ?>
                            <span class="badge <?= $estadoClass ?> estado-badge">
                                <?= ucfirst(str_replace('_', ' ', $documento['estado'])) ?>
                            </span>
                        </div>
                        <small class="opacity-75">
                            Ultima modificacion: <?= date('d/m/Y H:i', strtotime($documento['updated_at'])) ?>
                        </small>
                    </div>
                </div>
            </div>

            <!-- Info del documento -->
            <div class="info-documento no-print">
                <div class="row">
                    <div class="col-md-4">
                        <small class="text-muted">Tipo de documento:</small>
                        <span class="fw-bold">Plan</span>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Periodo:</small>
                        <span class="fw-bold"><?= $anio ?></span>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Estandar:</small>
                        <span class="fw-bold">2.2.1</span>
                    </div>
                </div>
            </div>

            <!-- Encabezado formal del documento -->
            <table class="encabezado-formal">
                <tr>
                    <td class="encabezado-logo" rowspan="2">
                        <?php if (!empty($cliente['logo'])): ?>
                            <img src="<?= base_url('uploads/' . $cliente['logo']) ?>" alt="Logo <?= esc($cliente['nombre_cliente']) ?>">
                        <?php else: ?>
                            <div style="font-size: 0.7rem; color: #666;">
                                <strong><?= esc($cliente['nombre_cliente']) ?></strong>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="encabezado-titulo-central">
                        <div class="sistema">SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO</div>
                    </td>
                    <td class="encabezado-info" rowspan="2">
                        <table class="encabezado-info-table">
                            <tr>
                                <td class="label">Codigo:</td>
                                <td><?= esc($documento['codigo'] ?? 'POM-SST') ?></td>
                            </tr>
                            <tr>
                                <td class="label">Version:</td>
                                <td><?= $documento['version'] ?>.0</td>
                            </tr>
                            <tr>
                                <td class="label">Fecha:</td>
                                <td><?= date('d/m/Y', strtotime($documento['fecha_aprobacion'] ?? $documento['created_at'])) ?></td>
                            </tr>
                            <tr>
                                <td class="label">Pagina:</td>
                                <td>1 de 1</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="encabezado-titulo-central">
                        <div class="nombre-doc">PLAN DE OBJETIVOS Y METAS DEL SG-SST</div>
                    </td>
                </tr>
            </table>

            <!-- Secciones del documento -->
            <?php if (!empty($contenido['secciones'])): ?>
                <?php $parsedown = new \Parsedown(); ?>
                <?php foreach ($contenido['secciones'] as $seccion): ?>
                    <div class="seccion">
                        <h3 class="seccion-titulo">
                            <?= esc($seccion['numero'] ?? '') ?>. <?= esc($seccion['titulo']) ?>
                        </h3>
                        <div class="seccion-contenido">
                            <?= $parsedown->text($seccion['contenido'] ?? '') ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Tabla de Objetivos del SG-SST -->
            <?php if (!empty($objetivos)): ?>
            <div class="seccion">
                <h3 class="seccion-titulo">Objetivos del SG-SST <?= $anio ?></h3>
                <div class="table-responsive">
                    <table class="table table-bordered tabla-objetivos">
                        <thead>
                            <tr>
                                <th style="width:5%">#</th>
                                <th style="width:40%">Objetivo</th>
                                <th style="width:25%">Meta</th>
                                <th style="width:15%">Responsable</th>
                                <th style="width:15%">PHVA</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($objetivos as $idx => $obj): ?>
                            <?php
                                $partes = explode(' | Meta: ', $obj['actividad_plandetrabajo']);
                                $tituloObj = $partes[0];
                                $metaObj = $partes[1] ?? '';
                            ?>
                            <tr>
                                <td class="text-center"><?= $idx + 1 ?></td>
                                <td><?= esc($tituloObj) ?></td>
                                <td><?= esc($metaObj) ?></td>
                                <td><?= esc($obj['responsable_sugerido_plandetrabajo'] ?? 'Responsable SST') ?></td>
                                <td class="text-center">
                                    <span class="badge bg-secondary"><?= esc($obj['phva_plandetrabajo'] ?? 'PLANEAR') ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Tabla de Indicadores -->
            <?php if (!empty($indicadores)): ?>
            <div class="seccion">
                <h3 class="seccion-titulo">Indicadores de Medicion</h3>
                <div class="table-responsive">
                    <table class="table table-bordered tabla-indicadores">
                        <thead>
                            <tr>
                                <th style="width:25%">Indicador</th>
                                <th style="width:30%">Formula</th>
                                <th style="width:10%">Meta</th>
                                <th style="width:10%">Tipo</th>
                                <th style="width:15%">Periodicidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($indicadores as $ind): ?>
                            <tr>
                                <td>
                                    <strong><?= esc($ind['nombre_indicador']) ?></strong>
                                </td>
                                <td><small><?= esc($ind['formula']) ?></small></td>
                                <td class="text-center"><?= $ind['meta'] ?><?= esc($ind['unidad_medida']) ?></td>
                                <td class="text-center">
                                    <span class="badge bg-<?= $ind['tipo_indicador'] == 'resultado' ? 'danger' : ($ind['tipo_indicador'] == 'proceso' ? 'primary' : 'secondary') ?>">
                                        <?= ucfirst(esc($ind['tipo_indicador'])) ?>
                                    </span>
                                </td>
                                <td class="text-center"><?= ucfirst(esc($ind['periodicidad'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Tabla de Control de Cambios -->
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
                                        <?= $ver['version_texto'] ?>
                                    </span>
                                </td>
                                <td><?= esc($ver['descripcion_cambio'] ?? 'Creacion inicial del documento') ?></td>
                                <td style="text-align: center;"><?= date('d/m/Y', strtotime($ver['fecha_autorizacion'] ?? $ver['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td style="text-align: center;">
                                    <span style="display: inline-block; background: #0d6efd; color: white; padding: 3px 12px; border-radius: 20px;">
                                        1.0
                                    </span>
                                </td>
                                <td>Elaboracion inicial del documento</td>
                                <td style="text-align: center;"><?= date('d/m/Y', strtotime($documento['created_at'] ?? 'now')) ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Seccion de Firmas -->
            <?php
            // === LOGICA DE DETERMINACION DE FIRMANTES (segun 1_A_SISTEMA_FIRMAS_DOCUMENTOS.md) ===
            $estandares = $contexto['estandares_aplicables'] ?? 60;
            $requiereDelegado = !empty($contexto['requiere_delegado_sst']);

            // Firmantes definidos por el tipo de documento
            $firmantesDefinidosArr = $firmantesDefinidos ?? null;
            $usaFirmantesDefinidos = !empty($firmantesDefinidosArr) && is_array($firmantesDefinidosArr);

            // Caso: Solo 2 firmantes por definicion del documento
            $esDosFirmantesPorDefinicion = $usaFirmantesDefinidos
                && in_array('representante_legal', $firmantesDefinidosArr)
                && in_array('responsable_sst', $firmantesDefinidosArr)
                && !in_array('delegado_sst', $firmantesDefinidosArr)
                && !in_array('vigia_sst', $firmantesDefinidosArr)
                && !in_array('copasst', $firmantesDefinidosArr);

            // Caso: Solo 2 firmantes por estandares (7 estandares sin delegado)
            $esSoloDosFirmantes = ($estandares <= 10) && !$requiereDelegado;

            // Datos del Consultor/Responsable SST
            $consultorNombre = $consultor['nombre_consultor'] ?? '';
            $consultorCargo = $esDosFirmantesPorDefinicion ? 'Responsable del SG-SST' : 'Consultor SST';
            $consultorLicencia = $consultor['numero_licencia'] ?? '';
            $firmaConsultor = $consultor['firma_consultor'] ?? '';

            // Firma consultor: prioridad electronica > fisica (2_AA_WEB.md §16)
            $firmaConsultorElectronica = ($firmasElectronicas ?? [])['consultor_sst'] ?? null;

            // Datos del Delegado SST
            $delegadoNombre = $contexto['delegado_sst_nombre'] ?? '';
            $delegadoCargo = $contexto['delegado_sst_cargo'] ?? 'Delegado SST';
            $delegadoCedula = $contexto['delegado_sst_cedula'] ?? '';

            // Datos del Representante Legal
            $repLegalNombre = $contexto['representante_legal_nombre'] ?? $cliente['nombre_rep_legal'] ?? '';
            $repLegalCargo = 'Representante Legal';
            $repLegalCedula = $contexto['representante_legal_cedula'] ?? '';
            ?>

            <?php if ($requiereDelegado): ?>
            <!-- TIPO F: 3 FIRMANTES (Cliente tiene Delegado SST - PRIORIDAD MAXIMA) -->
            <div class="firma-section" style="margin-top: 40px; page-break-inside: avoid;">
                <div class="seccion-titulo" style="background: linear-gradient(90deg, #198754, #20c997); color: white; padding: 10px 15px; border-radius: 5px; margin-bottom: 0; border: none;">
                    <i class="bi bi-pen me-2"></i>FIRMAS DE APROBACION
                </div>
                <table class="table table-bordered mb-0" style="font-size: 0.85rem; border-top: none;">
                    <thead>
                        <tr style="background: #e9ecef;">
                            <th style="width: 33.33%; text-align: center;">
                                <i class="bi bi-person-badge me-1"></i>Elaboro
                            </th>
                            <th style="width: 33.33%; text-align: center;">
                                <i class="bi bi-shield-check me-1"></i>Reviso / Delegado SST
                            </th>
                            <th style="width: 33.33%; text-align: center;">
                                <i class="bi bi-person-check me-1"></i>Aprobo
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <!-- CONSULTOR SST -->
                            <td style="padding: 15px; height: 160px; position: relative;">
                                <div><strong>Nombre:</strong> <?= esc($consultorNombre) ?></div>
                                <div><strong>Cargo:</strong> Consultor SST</div>
                                <?php if (!empty($consultorLicencia)): ?>
                                <div><strong>Licencia SST:</strong> <?= esc($consultorLicencia) ?></div>
                                <?php endif; ?>
                                <div style="position: absolute; bottom: 12px; left: 15px; right: 15px; text-align: center;">
                                    <?php if ($firmaConsultorElectronica && !empty($firmaConsultorElectronica['evidencia']['firma_imagen'])): ?>
                                        <img src="<?= $firmaConsultorElectronica['evidencia']['firma_imagen'] ?>" style="max-height: 50px;">
                                    <?php elseif (!empty($firmaConsultor)): ?>
                                        <img src="<?= base_url('uploads/' . $firmaConsultor) ?>" style="max-height: 50px;">
                                    <?php endif; ?>
                                    <div style="border-top: 1px solid #333; width: 85%; margin: 0 auto;">
                                        <small>Firma</small>
                                    </div>
                                </div>
                            </td>
                            <!-- DELEGADO SST -->
                            <td style="padding: 15px; height: 160px; position: relative;">
                                <div><strong>Nombre:</strong> <?= esc($delegadoNombre) ?></div>
                                <div><strong>Cargo:</strong> <?= esc($delegadoCargo) ?></div>
                                <?php if (!empty($delegadoCedula)): ?>
                                <div><strong>Cedula:</strong> <?= esc($delegadoCedula) ?></div>
                                <?php endif; ?>
                                <div style="position: absolute; bottom: 12px; left: 15px; right: 15px; text-align: center;">
                                    <?php
                                    $firmaDelegado = ($firmasElectronicas ?? [])['delegado_sst'] ?? null;
                                    if ($firmaDelegado && !empty($firmaDelegado['evidencia']['firma_imagen'])):
                                    ?>
                                        <img src="<?= $firmaDelegado['evidencia']['firma_imagen'] ?>" style="max-height: 50px;">
                                    <?php endif; ?>
                                    <div style="border-top: 1px solid #333; width: 85%; margin: 0 auto;">
                                        <small>Firma</small>
                                    </div>
                                </div>
                            </td>
                            <!-- REPRESENTANTE LEGAL -->
                            <td style="padding: 15px; height: 160px; position: relative;">
                                <div><strong>Nombre:</strong> <?= esc($repLegalNombre) ?></div>
                                <div><strong>Cargo:</strong> Representante Legal</div>
                                <?php if (!empty($repLegalCedula)): ?>
                                <div><strong>Cedula:</strong> <?= esc($repLegalCedula) ?></div>
                                <?php endif; ?>
                                <div style="position: absolute; bottom: 12px; left: 15px; right: 15px; text-align: center;">
                                    <?php
                                    $firmaRepLegal = ($firmasElectronicas ?? [])['representante_legal'] ?? null;
                                    if ($firmaRepLegal && !empty($firmaRepLegal['evidencia']['firma_imagen'])):
                                    ?>
                                        <img src="<?= $firmaRepLegal['evidencia']['firma_imagen'] ?>" style="max-height: 50px;">
                                    <?php endif; ?>
                                    <div style="border-top: 1px solid #333; width: 85%; margin: 0 auto;">
                                        <small>Firma</small>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <?php elseif ($esDosFirmantesPorDefinicion): ?>
            <!-- TIPO D: 2 FIRMANTES (Responsable SST + Rep. Legal) -->
            <div class="firma-section" style="margin-top: 40px; page-break-inside: avoid;">
                <div class="seccion-titulo" style="background: linear-gradient(90deg, #198754, #20c997); color: white; padding: 10px 15px; border-radius: 5px; margin-bottom: 0; border: none;">
                    <i class="bi bi-pen me-2"></i>FIRMAS DE APROBACION
                </div>
                <table class="table table-bordered mb-0" style="font-size: 0.85rem; border-top: none;">
                    <thead>
                        <tr style="background: #e9ecef;">
                            <th style="width: 50%; text-align: center;">
                                <i class="bi bi-person-badge me-1"></i>Elaboro / Responsable del SG-SST
                            </th>
                            <th style="width: 50%; text-align: center;">
                                <i class="bi bi-person-check me-1"></i>Aprobo / Representante Legal
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding: 20px; height: 180px; position: relative;">
                                <div><strong>Nombre:</strong> <?= esc($consultorNombre) ?></div>
                                <div><strong>Cargo:</strong> Responsable del SG-SST</div>
                                <?php if (!empty($consultorLicencia)): ?>
                                <div><strong>Licencia SST:</strong> <?= esc($consultorLicencia) ?></div>
                                <?php endif; ?>
                                <div style="position: absolute; bottom: 15px; left: 20px; right: 20px; text-align: center;">
                                    <?php if ($firmaConsultorElectronica && !empty($firmaConsultorElectronica['evidencia']['firma_imagen'])): ?>
                                        <img src="<?= $firmaConsultorElectronica['evidencia']['firma_imagen'] ?>" style="max-height: 50px;">
                                    <?php elseif (!empty($firmaConsultor)): ?>
                                        <img src="<?= base_url('uploads/' . $firmaConsultor) ?>" style="max-height: 50px;">
                                    <?php endif; ?>
                                    <div style="border-top: 1px solid #333; width: 80%; margin: 5px auto 0;">
                                        <small>Firma</small>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 20px; height: 180px; position: relative;">
                                <div><strong>Nombre:</strong> <?= esc($repLegalNombre) ?></div>
                                <div><strong>Cargo:</strong> Representante Legal</div>
                                <?php if (!empty($repLegalCedula)): ?>
                                <div><strong>Cedula:</strong> <?= esc($repLegalCedula) ?></div>
                                <?php endif; ?>
                                <div style="position: absolute; bottom: 15px; left: 20px; right: 20px; text-align: center;">
                                    <?php
                                    $firmaRepLegal = ($firmasElectronicas ?? [])['representante_legal'] ?? null;
                                    if ($firmaRepLegal && !empty($firmaRepLegal['evidencia']['firma_imagen'])):
                                    ?>
                                        <img src="<?= $firmaRepLegal['evidencia']['firma_imagen'] ?>" style="max-height: 50px;">
                                    <?php endif; ?>
                                    <div style="border-top: 1px solid #333; width: 80%; margin: 5px auto 0;">
                                        <small>Firma</small>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <?php elseif ($esSoloDosFirmantes): ?>
            <!-- TIPO E: 2 FIRMANTES (Consultor + Rep. Legal - 7 Estandares) -->
            <div class="firma-section" style="margin-top: 40px; page-break-inside: avoid;">
                <div class="seccion-titulo" style="background: linear-gradient(90deg, #198754, #20c997); color: white; padding: 10px 15px; border-radius: 5px; margin-bottom: 0; border: none;">
                    <i class="bi bi-pen me-2"></i>FIRMAS DE APROBACION
                </div>
                <table class="table table-bordered mb-0" style="font-size: 0.85rem; border-top: none;">
                    <thead>
                        <tr style="background: #e9ecef;">
                            <th style="width: 50%; text-align: center;">
                                <i class="bi bi-person-badge me-1"></i>Elaboro / Consultor SST
                            </th>
                            <th style="width: 50%; text-align: center;">
                                <i class="bi bi-person-check me-1"></i>Aprobo / Representante Legal
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding: 20px; height: 180px; position: relative;">
                                <div><strong>Nombre:</strong> <?= esc($consultorNombre) ?></div>
                                <div><strong>Cargo:</strong> Consultor SST</div>
                                <?php if (!empty($consultorLicencia)): ?>
                                <div><strong>Licencia SST:</strong> <?= esc($consultorLicencia) ?></div>
                                <?php endif; ?>
                                <div style="position: absolute; bottom: 15px; left: 20px; right: 20px; text-align: center;">
                                    <?php if ($firmaConsultorElectronica && !empty($firmaConsultorElectronica['evidencia']['firma_imagen'])): ?>
                                        <img src="<?= $firmaConsultorElectronica['evidencia']['firma_imagen'] ?>" style="max-height: 50px;">
                                    <?php elseif (!empty($firmaConsultor)): ?>
                                        <img src="<?= base_url('uploads/' . $firmaConsultor) ?>" style="max-height: 50px;">
                                    <?php endif; ?>
                                    <div style="border-top: 1px solid #333; width: 80%; margin: 5px auto 0;">
                                        <small>Firma</small>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 20px; height: 180px; position: relative;">
                                <div><strong>Nombre:</strong> <?= esc($repLegalNombre) ?></div>
                                <div><strong>Cargo:</strong> Representante Legal</div>
                                <?php if (!empty($repLegalCedula)): ?>
                                <div><strong>Cedula:</strong> <?= esc($repLegalCedula) ?></div>
                                <?php endif; ?>
                                <div style="position: absolute; bottom: 15px; left: 20px; right: 20px; text-align: center;">
                                    <?php
                                    $firmaRepLegal = ($firmasElectronicas ?? [])['representante_legal'] ?? null;
                                    if ($firmaRepLegal && !empty($firmaRepLegal['evidencia']['firma_imagen'])):
                                    ?>
                                        <img src="<?= $firmaRepLegal['evidencia']['firma_imagen'] ?>" style="max-height: 50px;">
                                    <?php endif; ?>
                                    <div style="border-top: 1px solid #333; width: 80%; margin: 5px auto 0;">
                                        <small>Firma</small>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <?php else: ?>
            <!-- TIPO F: 3 FIRMANTES (Estandares 21+ o con Vigia/COPASST) -->
            <div class="firma-section" style="margin-top: 40px; page-break-inside: avoid;">
                <div class="seccion-titulo" style="background: linear-gradient(90deg, #198754, #20c997); color: white; padding: 10px 15px; border-radius: 5px; margin-bottom: 0; border: none;">
                    <i class="bi bi-pen me-2"></i>FIRMAS DE APROBACION
                </div>
                <table class="table table-bordered mb-0" style="font-size: 0.85rem; border-top: none;">
                    <thead>
                        <tr style="background: #e9ecef;">
                            <th style="width: 33.33%; text-align: center;">
                                <i class="bi bi-person-badge me-1"></i>Elaboro
                            </th>
                            <th style="width: 33.33%; text-align: center;">
                                <i class="bi bi-people me-1"></i>Reviso / <?= $estandares <= 21 ? 'Vigia SST' : 'COPASST' ?>
                            </th>
                            <th style="width: 33.33%; text-align: center;">
                                <i class="bi bi-person-check me-1"></i>Aprobo
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <!-- CONSULTOR SST -->
                            <td style="padding: 15px; height: 160px; position: relative;">
                                <div><strong>Nombre:</strong> <?= esc($consultorNombre) ?></div>
                                <div><strong>Cargo:</strong> Consultor SST</div>
                                <?php if (!empty($consultorLicencia)): ?>
                                <div><strong>Licencia SST:</strong> <?= esc($consultorLicencia) ?></div>
                                <?php endif; ?>
                                <div style="position: absolute; bottom: 12px; left: 15px; right: 15px; text-align: center;">
                                    <?php if ($firmaConsultorElectronica && !empty($firmaConsultorElectronica['evidencia']['firma_imagen'])): ?>
                                        <img src="<?= $firmaConsultorElectronica['evidencia']['firma_imagen'] ?>" style="max-height: 50px;">
                                    <?php elseif (!empty($firmaConsultor)): ?>
                                        <img src="<?= base_url('uploads/' . $firmaConsultor) ?>" style="max-height: 50px;">
                                    <?php endif; ?>
                                    <div style="border-top: 1px solid #333; width: 85%; margin: 0 auto;">
                                        <small>Firma</small>
                                    </div>
                                </div>
                            </td>
                            <!-- VIGIA/COPASST -->
                            <td style="padding: 15px; height: 160px; position: relative;">
                                <div><strong>Nombre:</strong> </div>
                                <div><strong>Cargo:</strong> <?= $estandares <= 21 ? 'Vigia de SST' : 'COPASST' ?></div>
                                <div style="position: absolute; bottom: 12px; left: 15px; right: 15px; text-align: center;">
                                    <?php
                                    $firmaVigia = ($firmasElectronicas ?? [])['vigia_sst'] ?? ($firmasElectronicas ?? [])['copasst'] ?? null;
                                    if ($firmaVigia && !empty($firmaVigia['evidencia']['firma_imagen'])):
                                    ?>
                                        <img src="<?= $firmaVigia['evidencia']['firma_imagen'] ?>" style="max-height: 50px;">
                                    <?php elseif (!empty($vigia['firma_vigia'] ?? '')): ?>
                                        <img src="<?= base_url('uploads/' . $vigia['firma_vigia']) ?>" style="max-height: 50px;">
                                    <?php endif; ?>
                                    <div style="border-top: 1px solid #333; width: 85%; margin: 0 auto;">
                                        <small>Firma</small>
                                    </div>
                                </div>
                            </td>
                            <!-- REPRESENTANTE LEGAL -->
                            <td style="padding: 15px; height: 160px; position: relative;">
                                <div><strong>Nombre:</strong> <?= esc($repLegalNombre) ?></div>
                                <div><strong>Cargo:</strong> Representante Legal</div>
                                <?php if (!empty($repLegalCedula)): ?>
                                <div><strong>Cedula:</strong> <?= esc($repLegalCedula) ?></div>
                                <?php endif; ?>
                                <div style="position: absolute; bottom: 12px; left: 15px; right: 15px; text-align: center;">
                                    <?php
                                    $firmaRepLegal = ($firmasElectronicas ?? [])['representante_legal'] ?? null;
                                    if ($firmaRepLegal && !empty($firmaRepLegal['evidencia']['firma_imagen'])):
                                    ?>
                                        <img src="<?= $firmaRepLegal['evidencia']['firma_imagen'] ?>" style="max-height: 50px;">
                                    <?php endif; ?>
                                    <div style="border-top: 1px solid #333; width: 85%; margin: 0 auto;">
                                        <small>Firma</small>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <!-- Pie de Documento -->
            <div class="text-center text-muted mt-4 pt-3 border-top" style="font-size: 0.75rem;">
                <p class="mb-1">Documento generado el <?= date('d/m/Y') ?> - Sistema de Gestion SST</p>
                <p class="mb-0"><?= esc($cliente['nombre_cliente']) ?> - NIT: <?= esc($cliente['nit_cliente'] ?? '') ?></p>
            </div>

        </div>
    </div>

    <!-- SweetAlert2 para notificaciones -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Modal Nueva Version - COMPONENTE ESTANDAR -->
    <?php
    // Obtener version vigente del historial
    $versionVigente = null;
    if (!empty($versiones)) {
        foreach ($versiones as $v) {
            if (($v['estado'] ?? '') === 'vigente') {
                $versionVigente = $v;
                break;
            }
        }
    }
    ?>
    <?= view('documentos_sst/_components/modal_nueva_version', [
        'id_documento' => $documento['id_documento'],
        'version_actual' => $versionVigente['version_texto'] ?? '1.0',
        'tipo_documento' => $documento['tipo_documento'] ?? 'plan_objetivos_metas',
        'modal_id' => 'modalAprobarDocumento',
        'tipo_cambio_default' => empty($versionVigente) ? 'mayor' : 'menor'
    ]) ?>

    <!-- Modal Historial de Versiones - COMPONENTE ESTANDAR -->
    <?= view('documentos_sst/_components/modal_historial_versiones', [
        'id_documento' => $documento['id_documento'],
        'versiones' => $versiones ?? []
    ]) ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
