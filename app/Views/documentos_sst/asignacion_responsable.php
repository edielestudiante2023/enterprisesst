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
            .firma-section { page-break-inside: avoid; }
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

        .documento-fecha {
            text-align: left;
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 25px;
            color: #333;
        }

        .documento-cuerpo {
            text-align: justify;
            line-height: 1.8;
            font-size: 0.95rem;
            margin-bottom: 30px;
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

        .info-documento {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
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
                    <span class="ms-3"><?= esc($cliente['nombre_cliente']) ?> - Asignacion Responsable SG-SST <?= $anio ?></span>
                </div>
                <div>
                    <button type="button" class="btn btn-outline-light btn-sm me-2" data-bs-toggle="modal" data-bs-target="#modalHistorialVersiones">
                        <i class="bi bi-clock-history me-1"></i>Historial
                    </button>
                    <a href="<?= base_url('documentos-sst/exportar-pdf/' . $documento['id_documento']) ?>" class="btn btn-danger btn-sm me-2">
                        <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                    </a>
                    <a href="<?= base_url('documentos-sst/exportar-word/' . $documento['id_documento']) ?>" class="btn btn-primary btn-sm me-2">
                        <i class="bi bi-file-earmark-word me-1"></i>Word
                    </a>
                    <button type="button" class="btn btn-warning btn-sm me-2" data-bs-toggle="modal" data-bs-target="#modalRegenerarDocumento">
                        <i class="bi bi-arrow-repeat me-1"></i>Actualizar Datos
                    </button>
                    <?php if (in_array($documento['estado'] ?? '', ['aprobado', 'en_revision', 'pendiente_firma'])): ?>
                        <a href="<?= base_url('firma/solicitar/' . $documento['id_documento']) ?>" class="btn btn-success btn-sm me-2">
                            <i class="bi bi-pen me-1"></i>Solicitar Firmas
                        </a>
                    <?php endif; ?>
                    <?php if (($documento['estado'] ?? '') === 'firmado'): ?>
                        <a href="<?= base_url('firma/estado/' . $documento['id_documento']) ?>" class="btn btn-outline-success btn-sm me-2">
                            <i class="bi bi-patch-check me-1"></i>Ver Firmas
                        </a>
                    <?php endif; ?>
                    <?php if (($documento['estado'] ?? '') === 'pendiente_firma'): ?>
                        <a href="<?= base_url('firma/estado/' . $documento['id_documento']) ?>" class="btn btn-outline-warning btn-sm me-2">
                            <i class="bi bi-clock-history me-1"></i>Estado Firmas
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Documento -->
    <div class="container my-4">
        <div class="bg-white shadow documento-contenido" style="padding: 40px; max-width: 900px; margin: 0 auto;">

            <!-- Panel de Informacion del Documento (no imprimible) -->
            <div class="panel-aprobacion no-print">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <span class="badge bg-dark estado-badge"><?= esc($documento['codigo'] ?? 'Sin codigo') ?></span>
                            <span class="badge bg-light text-dark estado-badge">v<?= $documento['version'] ?>.0</span>
                            <?php
                            $estadoClass = match($documento['estado']) {
                                'aprobado' => 'bg-success',
                                'generado' => 'bg-info',
                                'borrador' => 'bg-warning text-dark',
                                default => 'bg-secondary'
                            };
                            ?>
                            <span class="badge <?= $estadoClass ?> estado-badge">
                                <i class="bi bi-<?= $documento['estado'] === 'aprobado' ? 'check-circle' : 'pencil' ?> me-1"></i>
                                <?= ucfirst($documento['estado']) ?>
                            </span>
                        </div>
                        <small class="opacity-75">
                            <?php if ($documento['estado'] === 'aprobado' && !empty($documento['fecha_aprobacion'])): ?>
                                Aprobado el <?= date('d/m/Y H:i', strtotime($documento['fecha_aprobacion'])) ?>
                            <?php else: ?>
                                Ultima modificacion: <?= date('d/m/Y H:i', strtotime($documento['updated_at'])) ?>
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
            </div>

            <!-- Info del documento simplificada (no imprimible) -->
            <div class="info-documento no-print">
                <div class="row">
                    <div class="col-md-4">
                        <small class="text-muted">Tipo de documento:</small>
                        <span class="fw-bold">Acta de Asignacion</span>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Periodo:</small>
                        <span class="fw-bold"><?= $anio ?></span>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Cliente:</small>
                        <span class="fw-bold"><?= esc($cliente['nombre_cliente']) ?></span>
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
                                <td><?= esc($documento['codigo'] ?? 'ASG-RES-001') ?></td>
                            </tr>
                            <tr>
                                <td class="label">Version:</td>
                                <td><?= str_pad($documento['version'] ?? 1, 3, '0', STR_PAD_LEFT) ?></td>
                            </tr>
                            <tr>
                                <td class="label">Entra en Vigor:</td>
                                <td><?= date('j M Y', strtotime($documento['created_at'] ?? 'now')) ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="encabezado-titulo-central">
                        <div class="nombre-doc">ASIGNACION PARA EL DISENO E IMPLEMENTACION DE SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO</div>
                    </td>
                </tr>
            </table>

            <!-- Fecha -->
            <div class="documento-fecha">
                FECHA: <?= date('d \d\e F \d\e Y', strtotime($documento['created_at'] ?? 'now')) ?>
            </div>

            <!-- Contenido del documento -->
            <?php if (!empty($contenido['secciones'])): ?>
                <?php foreach ($contenido['secciones'] as $seccion): ?>
                    <div class="documento-cuerpo">
                        <?= $seccion['contenido'] ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- ============================================== -->
            <!-- SECCION: CONTROL DE CAMBIOS (Imprimible) -->
            <!-- ============================================== -->
            <div class="seccion" style="page-break-inside: avoid; margin-top: 40px;">
                <div class="seccion-titulo" style="background: linear-gradient(90deg, #0d6efd, #6610f2); color: white; padding: 10px 15px; border-radius: 5px; margin-bottom: 0; border: none;">
                    <i class="bi bi-journal-text me-2"></i>CONTROL DE CAMBIOS
                </div>
                <table class="table table-bordered mb-0" style="font-size: 0.85rem; border-top: none;">
                    <thead>
                        <tr style="background: linear-gradient(135deg, #f8f9fa, #e9ecef);">
                            <th style="width: 100px; text-align: center; font-weight: 600; color: #495057; border-top: none;">Version</th>
                            <th style="font-weight: 600; color: #495057; border-top: none;">Descripcion del Cambio</th>
                            <th style="width: 130px; text-align: center; font-weight: 600; color: #495057; border-top: none;">Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($versiones)): ?>
                            <?php foreach ($versiones as $idx => $ver): ?>
                            <tr style="<?= $idx % 2 === 0 ? 'background-color: #fff;' : 'background-color: #f8f9fa;' ?>">
                                <td style="text-align: center; vertical-align: middle;">
                                    <span style="display: inline-block; background: #0d6efd; color: white; padding: 3px 12px; border-radius: 20px; font-weight: 600; font-size: 0.8rem;">
                                        <?= esc($ver['version_texto']) ?>
                                    </span>
                                </td>
                                <td style="vertical-align: middle;"><?= esc($ver['descripcion_cambio']) ?></td>
                                <td style="text-align: center; vertical-align: middle; font-weight: 500;"><?= date('d/m/Y', strtotime($ver['fecha_autorizacion'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td style="text-align: center; vertical-align: middle;">
                                    <span style="display: inline-block; background: #0d6efd; color: white; padding: 3px 12px; border-radius: 20px; font-weight: 600; font-size: 0.8rem;">
                                        1.0
                                    </span>
                                </td>
                                <td style="vertical-align: middle;">Elaboracion inicial del documento</td>
                                <td style="text-align: center; vertical-align: middle; font-weight: 500;"><?= date('d/m/Y', strtotime($documento['created_at'] ?? 'now')) ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- ============================================== -->
            <!-- SECCION: FIRMAS -->
            <!-- ============================================== -->
            <?php
            $estandares = $contexto['estandares_aplicables'] ?? 60;
            $requiereDelegado = !empty($contexto['requiere_delegado_sst']);
            $esSoloDosFirmantes = ($estandares <= 10) && !$requiereDelegado;

            $consultorNombre = $consultor['nombre_consultor'] ?? '';
            $consultorCargo = 'Consultor SST';
            $consultorLicencia = $consultor['numero_licencia'] ?? '';
            $firmaConsultor = $consultor['firma_consultor'] ?? '';

            $delegadoNombre = $contexto['delegado_sst_nombre'] ?? '';
            $delegadoCargo = $contexto['delegado_sst_cargo'] ?? 'Delegado SST';

            $repLegalNombre = $contexto['representante_legal_nombre'] ?? $cliente['nombre_rep_legal'] ?? $cliente['representante_legal'] ?? '';
            $repLegalCargo = 'Representante Legal';
            ?>

            <div class="firma-section" style="margin-top: 40px; page-break-inside: avoid;">
                <div class="seccion-titulo" style="background: linear-gradient(90deg, #198754, #20c997); color: white; padding: 10px 15px; border-radius: 5px; margin-bottom: 0; border: none;">
                    <i class="bi bi-pen me-2"></i>FIRMAS DE APROBACION
                </div>

                <?php if ($esSoloDosFirmantes): ?>
                <!-- 7 ESTANDARES SIN DELEGADO: Solo 2 firmantes -->
                <table class="table table-bordered mb-0" style="font-size: 0.85rem; border-top: none;">
                    <thead>
                        <tr style="background: linear-gradient(135deg, #f8f9fa, #e9ecef);">
                            <th style="width: 50%; text-align: center; font-weight: 600; color: #495057; border-top: none;">
                                <i class="bi bi-person-badge me-1"></i>Elaboro / Consultor SST
                            </th>
                            <th style="width: 50%; text-align: center; font-weight: 600; color: #495057; border-top: none;">
                                <i class="bi bi-person-check me-1"></i>Aprobo / Representante Legal
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="vertical-align: top; padding: 20px; height: 180px; position: relative;">
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
                                <div style="position: absolute; bottom: 15px; left: 20px; right: 20px; text-align: center;">
                                    <?php if (!empty($firmaConsultor)): ?>
                                        <img src="<?= base_url('uploads/' . $firmaConsultor) ?>" alt="Firma Consultor" style="max-height: 50px; max-width: 150px; margin-bottom: 5px;">
                                    <?php endif; ?>
                                    <div style="border-top: 1px solid #333; width: 80%; margin: 0 auto; padding-top: 5px;">
                                        <small style="color: #666;">Firma</small>
                                    </div>
                                </div>
                            </td>
                            <td style="vertical-align: top; padding: 20px; height: 180px; position: relative;">
                                <div style="margin-bottom: 8px;">
                                    <strong style="color: #495057;">Nombre:</strong>
                                    <span style="border-bottom: 1px dotted #999; display: inline-block; min-width: 200px; padding-bottom: 2px;">
                                        <?= !empty($repLegalNombre) ? esc($repLegalNombre) : '' ?>
                                    </span>
                                </div>
                                <div style="margin-bottom: 8px;">
                                    <strong style="color: #495057;">Cargo:</strong>
                                    <span><?= esc($repLegalCargo) ?></span>
                                </div>
                                <div style="position: absolute; bottom: 15px; left: 20px; right: 20px; text-align: center;">
                                    <?php
                                    $firmaRepLegal2col = ($firmasElectronicas ?? [])['representante_legal'] ?? null;
                                    if ($firmaRepLegal2col && !empty($firmaRepLegal2col['evidencia']['firma_imagen'])):
                                    ?>
                                        <img src="<?= $firmaRepLegal2col['evidencia']['firma_imagen'] ?>" alt="Firma Rep. Legal" style="max-height: 50px; max-width: 150px; margin-bottom: 5px;">
                                    <?php endif; ?>
                                    <div style="border-top: 1px solid #333; width: 80%; margin: 0 auto; padding-top: 5px;">
                                        <small style="color: #666;">Firma</small>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php else: ?>
                <!-- 3 FIRMANTES -->
                <table class="table table-bordered mb-0" style="font-size: 0.85rem; border-top: none;">
                    <thead>
                        <tr style="background: linear-gradient(135deg, #f8f9fa, #e9ecef);">
                            <th style="width: 33.33%; text-align: center; font-weight: 600; color: #495057; border-top: none;">
                                <i class="bi bi-person-badge me-1"></i>Elaboro
                            </th>
                            <th style="width: 33.33%; text-align: center; font-weight: 600; color: #495057; border-top: none;">
                                <?php if ($requiereDelegado): ?>
                                <i class="bi bi-shield-check me-1"></i>Reviso / Delegado SST
                                <?php else: ?>
                                <i class="bi bi-people me-1"></i>Reviso / <?= $estandares <= 21 ? 'Vigia SST' : 'COPASST' ?>
                                <?php endif; ?>
                            </th>
                            <th style="width: 33.33%; text-align: center; font-weight: 600; color: #495057; border-top: none;">
                                <i class="bi bi-person-check me-1"></i>Aprobo
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
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
                                <div style="position: absolute; bottom: 12px; left: 15px; right: 15px; text-align: center;">
                                    <?php if (!empty($firmaConsultor)): ?>
                                        <img src="<?= base_url('uploads/' . $firmaConsultor) ?>" alt="Firma Consultor" style="max-height: 40px; max-width: 120px; margin-bottom: 3px;">
                                    <?php endif; ?>
                                    <div style="border-top: 1px solid #333; width: 85%; margin: 0 auto; padding-top: 4px;">
                                        <small style="color: #666; font-size: 0.7rem;">Firma</small>
                                    </div>
                                </div>
                            </td>
                            <td style="vertical-align: top; padding: 15px; height: 160px; position: relative;">
                                <div style="margin-bottom: 6px;">
                                    <strong style="color: #495057; font-size: 0.8rem;">Nombre:</strong>
                                    <span style="border-bottom: 1px dotted #999; display: inline-block; min-width: 120px; padding-bottom: 2px; font-size: 0.85rem;">
                                        <?php if ($requiereDelegado): ?>
                                            <?= !empty($delegadoNombre) ? esc($delegadoNombre) : '' ?>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div style="margin-bottom: 6px;">
                                    <strong style="color: #495057; font-size: 0.8rem;">Cargo:</strong>
                                    <span style="font-size: 0.85rem;">
                                        <?php if ($requiereDelegado): ?>
                                            <?= esc($delegadoCargo) ?>
                                        <?php else: ?>
                                            <?= $estandares <= 21 ? 'Vigia de SST' : 'COPASST' ?>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div style="position: absolute; bottom: 12px; left: 15px; right: 15px; text-align: center;">
                                    <?php
                                    $firmaDelegado = ($firmasElectronicas ?? [])['delegado_sst'] ?? null;
                                    if ($firmaDelegado && !empty($firmaDelegado['evidencia']['firma_imagen'])):
                                    ?>
                                        <img src="<?= $firmaDelegado['evidencia']['firma_imagen'] ?>" alt="Firma Delegado SST" style="max-height: 40px; max-width: 120px; margin-bottom: 3px;">
                                    <?php endif; ?>
                                    <div style="border-top: 1px solid #333; width: 85%; margin: 0 auto; padding-top: 4px;">
                                        <small style="color: #666; font-size: 0.7rem;">Firma</small>
                                    </div>
                                </div>
                            </td>
                            <td style="vertical-align: top; padding: 15px; height: 160px; position: relative;">
                                <div style="margin-bottom: 6px;">
                                    <strong style="color: #495057; font-size: 0.8rem;">Nombre:</strong>
                                    <span style="border-bottom: 1px dotted #999; display: inline-block; min-width: 120px; padding-bottom: 2px; font-size: 0.85rem;">
                                        <?= !empty($repLegalNombre) ? esc($repLegalNombre) : '' ?>
                                    </span>
                                </div>
                                <div style="margin-bottom: 6px;">
                                    <strong style="color: #495057; font-size: 0.8rem;">Cargo:</strong>
                                    <span style="font-size: 0.85rem;"><?= esc($repLegalCargo) ?></span>
                                </div>
                                <div style="position: absolute; bottom: 12px; left: 15px; right: 15px; text-align: center;">
                                    <?php
                                    $firmaRepLegal = ($firmasElectronicas ?? [])['representante_legal'] ?? null;
                                    if ($firmaRepLegal && !empty($firmaRepLegal['evidencia']['firma_imagen'])):
                                    ?>
                                        <img src="<?= $firmaRepLegal['evidencia']['firma_imagen'] ?>" alt="Firma Rep. Legal" style="max-height: 40px; max-width: 120px; margin-bottom: 3px;">
                                    <?php endif; ?>
                                    <div style="border-top: 1px solid #333; width: 85%; margin: 0 auto; padding-top: 4px;">
                                        <small style="color: #666; font-size: 0.7rem;">Firma</small>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <!-- Pie de documento -->
            <div class="text-center text-muted mt-4 pt-3 border-top" style="font-size: 0.75rem;">
                <p class="mb-1">Documento generado el <?= date('d/m/Y') ?> - Sistema de Gestion SST</p>
                <p class="mb-0"><?= esc($cliente['nombre_cliente']) ?> - NIT: <?= esc($cliente['nit_cliente'] ?? '') ?></p>
            </div>
        </div>
    </div>

    <!-- Modal Historial de Versiones -->
    <div class="modal fade" id="modalHistorialVersiones" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-clock-history me-2"></i>Historial de Versiones</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="contenedorHistorial">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2 text-muted">Cargando historial...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Regenerar/Actualizar Documento -->
    <div class="modal fade" id="modalRegenerarDocumento" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-arrow-repeat me-2"></i>Actualizar Datos del Documento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Edite los datos que desea cambiar y haga clic en <strong>Actualizar</strong>.
                        Se creara una nueva version del documento con los datos actualizados.
                    </div>

                    <!-- Datos del Representante Legal -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <i class="bi bi-person-check me-2"></i><strong>Representante Legal</strong>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label for="regenerarRepLegalNombre" class="form-label">Nombre Completo</label>
                                    <input type="text" class="form-control" id="regenerarRepLegalNombre"
                                           value="<?= esc($contexto['representante_legal_nombre'] ?? $cliente['nombre_rep_legal'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="regenerarRepLegalCedula" class="form-label">Cedula</label>
                                    <input type="text" class="form-control" id="regenerarRepLegalCedula"
                                           value="<?= esc($contexto['representante_legal_cedula'] ?? $cliente['cedula_rep_legal'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Datos del Consultor/Responsable SST -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <i class="bi bi-person-badge me-2"></i><strong>Consultor/Responsable SST</strong>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="regenerarConsultor" class="form-label">Seleccione Consultor</label>
                                <select class="form-select" id="regenerarConsultor">
                                    <option value="">-- Seleccione --</option>
                                    <?php
                                    $consultorModel = new \App\Models\ConsultantModel();
                                    $listaConsultores = $consultorModel->orderBy('nombre_consultor', 'ASC')->findAll();
                                    $idConsultorActual = $contexto['id_consultor_responsable'] ?? null;
                                    foreach ($listaConsultores as $c):
                                    ?>
                                        <option value="<?= $c['id_consultor'] ?>"
                                                data-nombre="<?= esc($c['nombre_consultor']) ?>"
                                                data-cedula="<?= esc($c['cedula_consultor'] ?? '') ?>"
                                                data-licencia="<?= esc($c['numero_licencia'] ?? '') ?>"
                                                <?= ($idConsultorActual == $c['id_consultor']) ? 'selected' : '' ?>>
                                            <?= esc($c['nombre_consultor']) ?>
                                            <?php if (!empty($c['numero_licencia'])): ?> - Lic: <?= esc($c['numero_licencia']) ?><?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div id="datosConsultorPreview" class="alert alert-light border">
                                <?php if ($consultor): ?>
                                    <div><strong>Nombre:</strong> <?= esc($consultor['nombre_consultor'] ?? '') ?></div>
                                    <div><strong>Cedula:</strong> <?= esc($consultor['cedula_consultor'] ?? 'No registrada') ?></div>
                                    <div><strong>Licencia SST:</strong> <?= esc($consultor['numero_licencia'] ?? 'No registrada') ?></div>
                                <?php else: ?>
                                    <span class="text-muted">Seleccione un consultor para ver sus datos</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="descripcionCambio" class="form-label">Descripcion del cambio (opcional):</label>
                        <textarea class="form-control" id="descripcionCambio" rows="2"
                                  placeholder="Ej: Cambio de representante legal por nueva administracion"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning" id="btnConfirmarRegenerar">
                        <i class="bi bi-arrow-repeat me-1"></i>Actualizar y Crear Nueva Version
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const idDocumento = <?= $documento['id_documento'] ?>;

        document.getElementById('modalHistorialVersiones')?.addEventListener('show.bs.modal', function() {
            const contenedor = document.getElementById('contenedorHistorial');

            fetch('<?= base_url('documentos-sst/historial-versiones/') ?>' + idDocumento)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.versiones.length > 0) {
                    let html = '<div class="table-responsive"><table class="table table-hover">';
                    html += '<thead><tr><th>Version</th><th>Tipo</th><th>Descripcion</th><th>Fecha</th><th>Autorizado por</th><th>Acciones</th></tr></thead><tbody>';

                    data.versiones.forEach(v => {
                        const fecha = new Date(v.fecha_autorizacion).toLocaleDateString('es-CO', {
                            day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'
                        });
                        const estadoBadge = v.estado === 'vigente'
                            ? '<span class="badge bg-success">Vigente</span>'
                            : '<span class="badge bg-secondary">Obsoleto</span>';
                        const tipoBadge = v.tipo_cambio === 'mayor'
                            ? '<span class="badge bg-danger">Mayor</span>'
                            : '<span class="badge bg-info">Menor</span>';

                        html += `<tr class="${v.estado === 'obsoleto' ? 'table-secondary' : ''}">
                            <td><strong>v${v.version_texto}</strong> ${estadoBadge}</td>
                            <td>${tipoBadge}</td>
                            <td>${v.descripcion_cambio}</td>
                            <td><small>${fecha}</small></td>
                            <td>${v.autorizado_por || 'N/A'}</td>
                            <td>
                                <a href="<?= base_url('documentos-sst/descargar-version-pdf/') ?>${v.id_version}"
                                   class="btn btn-sm btn-outline-danger" title="Descargar PDF de esta version">
                                    <i class="bi bi-file-pdf"></i>
                                </a>
                                ${v.estado === 'obsoleto' ? `
                                <button type="button" class="btn btn-sm btn-outline-warning btn-restaurar"
                                        data-id="${v.id_version}" title="Restaurar esta version">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </button>` : ''}
                            </td>
                        </tr>`;
                    });

                    html += '</tbody></table></div>';
                    contenedor.innerHTML = html;

                    contenedor.querySelectorAll('.btn-restaurar').forEach(btn => {
                        btn.addEventListener('click', function() {
                            if (confirm('Restaurar esta version? El documento actual pasara a estado borrador.')) {
                                restaurarVersion(this.dataset.id);
                            }
                        });
                    });
                } else {
                    contenedor.innerHTML = `
                        <div class="text-center py-4">
                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                            <p class="mt-2 text-muted">No hay versiones registradas aun.<br>
                            Apruebe el documento para crear la primera version.</p>
                        </div>`;
                }
            })
            .catch(error => {
                contenedor.innerHTML = '<div class="alert alert-danger">Error al cargar historial</div>';
            });
        });

        function restaurarVersion(idVersion) {
            const formData = new FormData();
            formData.append('id_version', idVersion);

            fetch('<?= base_url('documentos-sst/restaurar-version') ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error de conexion');
            });
        }

        // Actualizar preview del consultor al cambiar el selector
        document.getElementById('regenerarConsultor')?.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const preview = document.getElementById('datosConsultorPreview');

            if (this.value) {
                const nombre = selected.dataset.nombre || 'No disponible';
                const cedula = selected.dataset.cedula || 'No registrada';
                const licencia = selected.dataset.licencia || 'No registrada';
                preview.innerHTML = `
                    <div><strong>Nombre:</strong> ${nombre}</div>
                    <div><strong>Cedula:</strong> ${cedula}</div>
                    <div><strong>Licencia SST:</strong> ${licencia}</div>
                `;
            } else {
                preview.innerHTML = '<span class="text-muted">Seleccione un consultor para ver sus datos</span>';
            }
        });

        // Regenerar/Actualizar documento con datos editados
        document.getElementById('btnConfirmarRegenerar')?.addEventListener('click', function() {
            const btn = this;
            const descripcion = document.getElementById('descripcionCambio').value.trim()
                || 'Actualizacion de datos de representante legal y/o consultor SST';

            // Obtener valores editados
            const repLegalNombre = document.getElementById('regenerarRepLegalNombre').value.trim();
            const repLegalCedula = document.getElementById('regenerarRepLegalCedula').value.trim();
            const idConsultor = document.getElementById('regenerarConsultor').value;

            if (!repLegalNombre) {
                alert('El nombre del representante legal es obligatorio');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Actualizando...';

            const formData = new FormData();
            formData.append('descripcion_cambio', descripcion);
            formData.append('representante_legal_nombre', repLegalNombre);
            formData.append('representante_legal_cedula', repLegalCedula);
            formData.append('id_consultor_responsable', idConsultor);

            fetch('<?= base_url("documentos-sst/{$cliente['id_cliente']}/regenerar-asignacion-responsable-sst/{$anio}") ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i>Actualizar y Crear Nueva Version';
                }
            })
            .catch(error => {
                alert('Error de conexion');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i>Actualizar y Crear Nueva Version';
            });
        });
    });
    </script>
</body>
</html>
