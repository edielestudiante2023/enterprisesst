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

        .seccion-contenido ul, .seccion-contenido ol {
            margin-left: 20px;
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

        @media print {
            .firma-section {
                page-break-inside: avoid;
            }
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
                    <span class="ms-3"><?= esc($cliente['nombre_cliente']) ?> - Procedimiento Matriz Legal <?= $anio ?></span>
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
                    <a href="<?= base_url('documentos/generar/procedimiento_matriz_legal/' . $cliente['id_cliente']) ?>" class="btn btn-warning btn-sm me-2">
                        <i class="bi bi-arrow-repeat me-1"></i>Actualizar
                    </a>
                    <?php if (in_array($documento['estado'] ?? '', ['borrador', 'generado', 'aprobado', 'en_revision', 'pendiente_firma'])): ?>
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
                                'firmado' => 'bg-primary',
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
                        <span class="fw-bold">Procedimiento</span>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Estandar:</small>
                        <span class="fw-bold">2.7.1</span>
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
                                <td><?= esc($documento['codigo'] ?? 'PRC-MRL-001') ?></td>
                            </tr>
                            <tr>
                                <td class="label">Version:</td>
                                <td><?= str_pad($documento['version'] ?? 1, 3, '0', STR_PAD_LEFT) ?></td>
                            </tr>
                            <tr>
                                <td class="label">Vigencia:</td>
                                <td><?= date('d/m/Y', strtotime($documento['created_at'] ?? 'now')) ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="encabezado-titulo-central">
                        <div class="nombre-doc"><?= esc(strtoupper($contenido['titulo'] ?? 'PROCEDIMIENTO PARA IDENTIFICACION DE REQUISITOS LEGALES')) ?></div>
                    </td>
                </tr>
            </table>

            <!-- Secciones del documento -->
            <?php
            // Normalizar tablas Markdown sin pipes al inicio/final (2_AA_WEB.md §20)
            if (!function_exists('normalizarTablasMarkdown')) {
                function normalizarTablasMarkdown($texto) {
                    if (empty($texto)) return '';
                    $lineas = explode("\n", $texto);
                    $resultado = [];
                    foreach ($lineas as $linea) {
                        $lineaTrim = trim($linea);
                        if (strpos($lineaTrim, '|') !== false && substr($lineaTrim, 0, 1) !== '|') {
                            $lineaTrim = '| ' . $lineaTrim . ' |';
                        }
                        $resultado[] = $lineaTrim;
                    }
                    return implode("\n", $resultado);
                }
            }
            $parsedown = new \Parsedown();
            ?>
            <?php if (!empty($contenido['secciones'])): ?>
                <?php foreach ($contenido['secciones'] as $seccion): ?>
                    <div class="seccion">
                        <div class="seccion-titulo"><?= esc($seccion['titulo'] ?? $seccion['nombre'] ?? 'Sin titulo') ?></div>
                        <div class="seccion-contenido">
                            <?php
                            $contenidoSeccion = $seccion['contenido'] ?? '';
                            if (!empty($contenidoSeccion)):
                                $contenidoSeccion = normalizarTablasMarkdown($contenidoSeccion);
                                echo $parsedown->text($contenidoSeccion);
                            else:
                            ?>
                                <p class="text-muted fst-italic">
                                    <i class="bi bi-exclamation-circle me-1"></i>
                                    Seccion pendiente de generar. Use el generador IA para completar esta seccion.
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- SECCION: CONTROL DE CAMBIOS -->
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

            <!-- SECCION: FIRMAS DE APROBACION -->
            <?php
            $estandares = $contexto['estandares_aplicables'] ?? 60;
            $requiereDelegado = !empty($contexto['requiere_delegado_sst']);

            $esSoloDosFirmantes = ($estandares <= 10) && !$requiereDelegado;

            $firmantesDefinidosArr = $firmantesDefinidos ?? null;
            $usaFirmantesDefinidos = !empty($firmantesDefinidosArr) && is_array($firmantesDefinidosArr);

            $esDosFirmantesPorDefinicion = $usaFirmantesDefinidos
                && in_array('representante_legal', $firmantesDefinidosArr)
                && in_array('responsable_sst', $firmantesDefinidosArr)
                && !in_array('delegado_sst', $firmantesDefinidosArr)
                && !in_array('vigia_sst', $firmantesDefinidosArr)
                && !in_array('copasst', $firmantesDefinidosArr);

            $consultorNombre = $consultor['nombre_consultor'] ?? '';
            $consultorCargo = $esDosFirmantesPorDefinicion ? 'Responsable del SG-SST' : 'Consultor SST';
            $consultorLicencia = $consultor['numero_licencia'] ?? '';

            $delegadoNombre = $contexto['delegado_sst_nombre'] ?? '';
            $delegadoCargo = $contexto['delegado_sst_cargo'] ?? 'Delegado SST';

            $repLegalNombre = $contexto['representante_legal_nombre'] ?? $cliente['nombre_rep_legal'] ?? $cliente['representante_legal'] ?? '';
            $repLegalCargo = 'Representante Legal';

            // Firmas del consultor: prioridad electrónica > física (según AA_ WEB.md sección 16)
            $firmaConsultorElectronica = ($firmasElectronicas ?? [])['consultor_sst'] ?? null;
            $firmaConsultorFisica = $consultor['firma_consultor'] ?? '';
            ?>

            <div class="firma-section" style="margin-top: 40px; page-break-inside: avoid;">
                <div class="seccion-titulo" style="background: linear-gradient(90deg, #198754, #20c997); color: white; padding: 10px 15px; border-radius: 5px; margin-bottom: 0; border: none;">
                    <i class="bi bi-pen me-2"></i>FIRMAS DE APROBACION
                </div>

                <?php if ($requiereDelegado): ?>
                <!-- 3 FIRMANTES - PRIORIDAD MÁXIMA: si cliente tiene delegado, SIEMPRE aparece (según SISTEMA_FIRMAS_DOCUMENTOS.md 11.6) -->
                <table class="table table-bordered mb-0" style="font-size: 0.85rem; border-top: none;">
                    <thead>
                        <tr style="background: linear-gradient(135deg, #f8f9fa, #e9ecef);">
                            <th style="width: 33.33%; text-align: center; font-weight: 600; color: #495057; border-top: none;">
                                <i class="bi bi-person-badge me-1"></i>Elaboro
                            </th>
                            <th style="width: 33.33%; text-align: center; font-weight: 600; color: #495057; border-top: none;">
                                <i class="bi bi-shield-check me-1"></i>Reviso / Delegado SST
                            </th>
                            <th style="width: 33.33%; text-align: center; font-weight: 600; color: #495057; border-top: none;">
                                <i class="bi bi-person-check me-1"></i>Aprobo
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <!-- CONSULTOR SST / ELABORÓ -->
                            <td style="vertical-align: top; padding: 15px; height: 160px; position: relative;">
                                <div style="margin-bottom: 6px;">
                                    <strong style="color: #495057; font-size: 0.8rem;">Nombre:</strong>
                                    <span style="border-bottom: 1px dotted #999; display: inline-block; min-width: 120px; padding-bottom: 2px; font-size: 0.85rem;">
                                        <?= !empty($consultorNombre) ? esc($consultorNombre) : '' ?>
                                    </span>
                                </div>
                                <div style="margin-bottom: 6px;">
                                    <strong style="color: #495057; font-size: 0.8rem;">Cargo:</strong>
                                    <span style="font-size: 0.85rem;">Consultor SST</span>
                                </div>
                                <?php if (!empty($consultorLicencia)): ?>
                                <div style="margin-bottom: 6px;">
                                    <strong style="color: #495057; font-size: 0.8rem;">Licencia:</strong>
                                    <span style="font-size: 0.85rem;"><?= esc($consultorLicencia) ?></span>
                                </div>
                                <?php endif; ?>
                                <div style="position: absolute; bottom: 12px; left: 15px; right: 15px; text-align: center;">
                                    <?php if ($firmaConsultorElectronica && !empty($firmaConsultorElectronica['evidencia']['firma_imagen'])): ?>
                                        <img src="<?= $firmaConsultorElectronica['evidencia']['firma_imagen'] ?>" alt="Firma Consultor" style="max-height: 50px; max-width: 120px; margin-bottom: 3px;">
                                    <?php elseif (!empty($firmaConsultorFisica)): ?>
                                        <img src="<?= base_url('uploads/' . $firmaConsultorFisica) ?>" alt="Firma Consultor" style="max-height: 50px; max-width: 120px; margin-bottom: 3px;">
                                    <?php endif; ?>
                                    <div style="border-top: 1px solid #333; width: 85%; margin: 0 auto; padding-top: 4px;">
                                        <small style="color: #666; font-size: 0.7rem;">Firma</small>
                                    </div>
                                </div>
                            </td>
                            <!-- DELEGADO SST / REVISÓ -->
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
                                    <?php
                                    $firmaDelegadoPrio = ($firmasElectronicas ?? [])['delegado_sst'] ?? null;
                                    if ($firmaDelegadoPrio && !empty($firmaDelegadoPrio['evidencia']['firma_imagen'])):
                                    ?>
                                        <img src="<?= $firmaDelegadoPrio['evidencia']['firma_imagen'] ?>" alt="Firma Delegado" style="max-height: 50px; max-width: 120px; margin-bottom: 3px;">
                                    <?php endif; ?>
                                    <div style="border-top: 1px solid #333; width: 85%; margin: 0 auto; padding-top: 4px;">
                                        <small style="color: #666; font-size: 0.7rem;">Firma</small>
                                    </div>
                                </div>
                            </td>
                            <!-- REPRESENTANTE LEGAL / APROBÓ -->
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
                                    $firmaRepLegalPrio = ($firmasElectronicas ?? [])['representante_legal'] ?? null;
                                    if ($firmaRepLegalPrio && !empty($firmaRepLegalPrio['evidencia']['firma_imagen'])):
                                    ?>
                                        <img src="<?= $firmaRepLegalPrio['evidencia']['firma_imagen'] ?>" alt="Firma Rep. Legal" style="max-height: 50px; max-width: 120px; margin-bottom: 3px;">
                                    <?php endif; ?>
                                    <div style="border-top: 1px solid #333; width: 85%; margin: 0 auto; padding-top: 4px;">
                                        <small style="color: #666; font-size: 0.7rem;">Firma</small>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php elseif ($esDosFirmantesPorDefinicion): ?>
                <!-- ========== TIPO D: 2 FIRMANTES POR DEFINICION: Responsable SST + Rep Legal ========== -->
                <table class="table table-bordered mb-0" style="font-size: 0.85rem; border-top: none;">
                    <thead>
                        <tr style="background: linear-gradient(135deg, #f8f9fa, #e9ecef);">
                            <th style="width: 50%; text-align: center; font-weight: 600; color: #495057; border-top: none;">
                                <i class="bi bi-person-badge me-1"></i>Elaboro / Responsable del SG-SST
                            </th>
                            <th style="width: 50%; text-align: center; font-weight: 600; color: #495057; border-top: none;">
                                <i class="bi bi-person-check me-1"></i>Aprobo / Representante Legal
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <!-- RESPONSABLE SST (Consultor) -->
                            <td style="vertical-align: top; padding: 20px; height: 180px; position: relative;">
                                <div style="margin-bottom: 8px;">
                                    <strong style="color: #495057;">Nombre:</strong>
                                    <span style="border-bottom: 1px dotted #999; display: inline-block; min-width: 200px; padding-bottom: 2px;">
                                        <?= !empty($consultorNombre) ? esc($consultorNombre) : '' ?>
                                    </span>
                                </div>
                                <div style="margin-bottom: 8px;">
                                    <strong style="color: #495057;">Cargo:</strong>
                                    <span>Responsable del SG-SST</span>
                                </div>
                                <?php if (!empty($consultorLicencia)): ?>
                                <div style="margin-bottom: 8px;">
                                    <strong style="color: #495057;">Licencia SST:</strong>
                                    <span><?= esc($consultorLicencia) ?></span>
                                </div>
                                <?php endif; ?>
                                <div style="position: absolute; bottom: 15px; left: 20px; right: 20px; text-align: center;">
                                    <?php if ($firmaConsultorElectronica && !empty($firmaConsultorElectronica['evidencia']['firma_imagen'])): ?>
                                        <img src="<?= $firmaConsultorElectronica['evidencia']['firma_imagen'] ?>" alt="Firma Consultor" style="max-height: 50px; max-width: 150px; margin-bottom: 5px;">
                                    <?php elseif (!empty($firmaConsultorFisica)): ?>
                                        <img src="<?= base_url('uploads/' . $firmaConsultorFisica) ?>" alt="Firma Consultor" style="max-height: 50px; max-width: 150px; margin-bottom: 5px;">
                                    <?php endif; ?>
                                    <div style="border-top: 1px solid #333; width: 80%; margin: 0 auto; padding-top: 5px;">
                                        <small style="color: #666;">Firma</small>
                                    </div>
                                </div>
                            </td>
                            <!-- REPRESENTANTE LEGAL -->
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
                                    $firmaRepLegalDef = ($firmasElectronicas ?? [])['representante_legal'] ?? null;
                                    if ($firmaRepLegalDef && !empty($firmaRepLegalDef['evidencia']['firma_imagen'])):
                                    ?>
                                        <img src="<?= $firmaRepLegalDef['evidencia']['firma_imagen'] ?>" alt="Firma Rep. Legal" style="max-height: 50px; max-width: 150px; margin-bottom: 5px;">
                                    <?php endif; ?>
                                    <div style="border-top: 1px solid #333; width: 80%; margin: 0 auto; padding-top: 5px;">
                                        <small style="color: #666;">Firma</small>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php elseif ($esSoloDosFirmantes): ?>
                <!-- ========== TIPO E: 7 ESTANDARES SIN DELEGADO: Solo 2 firmantes ========== -->
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
                            <!-- CONSULTOR SST -->
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
                                    <?php if ($firmaConsultorElectronica && !empty($firmaConsultorElectronica['evidencia']['firma_imagen'])): ?>
                                        <img src="<?= $firmaConsultorElectronica['evidencia']['firma_imagen'] ?>" alt="Firma Consultor" style="max-height: 50px; max-width: 150px; margin-bottom: 5px;">
                                    <?php elseif (!empty($firmaConsultorFisica)): ?>
                                        <img src="<?= base_url('uploads/' . $firmaConsultorFisica) ?>" alt="Firma Consultor" style="max-height: 50px; max-width: 150px; margin-bottom: 5px;">
                                    <?php endif; ?>
                                    <div style="border-top: 1px solid #333; width: 80%; margin: 0 auto; padding-top: 5px;">
                                        <small style="color: #666;">Firma</small>
                                    </div>
                                </div>
                            </td>
                            <!-- REPRESENTANTE LEGAL -->
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
                                    $firmaRepLegal = ($firmasElectronicas ?? [])['representante_legal'] ?? null;
                                    if ($firmaRepLegal && !empty($firmaRepLegal['evidencia']['firma_imagen'])):
                                    ?>
                                        <img src="<?= $firmaRepLegal['evidencia']['firma_imagen'] ?>" alt="Firma Rep. Legal" style="max-height: 50px; max-width: 150px; margin-bottom: 5px;">
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
                                        <img src="<?= $firmaDelegado['evidencia']['firma_imagen'] ?>" alt="Firma Delegado SST" style="max-height: 56px; max-width: 168px; margin-bottom: 3px;">
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
                                    $firmaRepLegal3 = ($firmasElectronicas ?? [])['representante_legal'] ?? null;
                                    if ($firmaRepLegal3 && !empty($firmaRepLegal3['evidencia']['firma_imagen'])):
                                    ?>
                                        <img src="<?= $firmaRepLegal3['evidencia']['firma_imagen'] ?>" alt="Firma Rep. Legal" style="max-height: 56px; max-width: 168px; margin-bottom: 3px;">
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
                <p class="mb-0"><?= esc($cliente['nombre_cliente']) ?> - NIT: <?= esc($cliente['nit_cliente']) ?></p>
            </div>
        </div>
    </div>

    <!-- Modal Historial de Versiones (componente estandar) -->
    <?= view('documentos_sst/_components/modal_historial_versiones', [
        'id_documento' => $documento['id_documento'],
        'versiones' => $versiones ?? []
    ]) ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
