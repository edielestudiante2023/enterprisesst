<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($titulo) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            .documento-contenido { padding: 20px !important; }
            body { font-size: 11pt; }
            .encabezado-formal { page-break-inside: avoid; }
            .firma-section { page-break-inside: avoid; }
            .seccion { page-break-inside: avoid; }
        }

        table.tabla-contenido {
            width: 100%;
            border-collapse: collapse;
            margin: 4px 0;
            font-size: 9pt;
        }
        table.tabla-contenido th,
        table.tabla-contenido td {
            border: 1px solid #999;
            padding: 2px 4px;
        }
        table.tabla-contenido th {
            background-color: #0d6efd;
            color: white;
            font-weight: bold;
        }

        .encabezado-formal {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
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

        .panel-aprobacion {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            color: white;
        }

        .info-documento {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .toast-retry-btn {
            background: none;
            border: 1px solid #dc3545;
            color: #dc3545;
            border-radius: 4px;
            padding: 2px 10px;
            font-size: 0.8rem;
            cursor: pointer;
        }
        .toast-retry-btn:hover {
            background: #dc3545;
            color: white;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Toast Stack -->
    <div class="toast-container position-fixed top-0 end-0 p-3" id="toastStack" style="z-index: 9999;"></div>

    <!-- Barra de herramientas -->
    <div class="no-print bg-dark text-white py-2 sticky-top">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <a href="<?= base_url('documentacion/' . $cliente['id_cliente']) ?>" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>Volver
                    </a>
                    <span class="ms-3"><?= esc($cliente['nombre_cliente']) ?> - Responsabilidades Responsable SG-SST <?= $anio ?></span>
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
                    <?php if (in_array($documento['estado'] ?? '', ['borrador', 'generado', 'aprobado', 'en_revision', 'pendiente_firma'])): ?>
                        <a href="<?= base_url('firma/solicitar/' . $documento['id_documento']) ?>" class="btn btn-success btn-sm me-2" target="_blank">
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
                            <span class="badge bg-light text-dark estado-badge">v<?= $documento['version_texto'] ?? ($documento['version'] . '.0') ?></span>
                            <?php
                            $estadoClass = match($documento['estado']) {
                                'aprobado' => 'bg-success',
                                'generado' => 'bg-info',
                                'borrador' => 'bg-warning text-dark',
                                'firmado' => 'bg-success',
                                'pendiente_firma' => 'bg-warning text-dark',
                                default => 'bg-secondary'
                            };
                            ?>
                            <span class="badge <?= $estadoClass ?> estado-badge">
                                <i class="bi bi-<?= $documento['estado'] === 'aprobado' || $documento['estado'] === 'firmado' ? 'check-circle' : 'pencil' ?> me-1"></i>
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

            <!-- Info del documento -->
            <div class="info-documento no-print">
                <div class="row">
                    <div class="col-md-4">
                        <small class="text-muted">Tipo de documento:</small>
                        <span class="fw-bold">Responsabilidades</span>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Periodo:</small>
                        <span class="fw-bold"><?= $anio ?></span>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Firmas:</small>
                        <?php if (!empty($contexto['requiere_delegado_sst'])): ?>
                            <span class="fw-bold text-success">Elaboro + Reviso + Aprobo</span>
                        <?php else: ?>
                            <span class="fw-bold text-success">Responsable SG-SST</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Encabezado formal del documento -->
            <table class="encabezado-formal">
                <tr>
                    <td class="encabezado-logo" rowspan="2" bgcolor="#FFFFFF" style="background-color:#ffffff;">
                        <?php if (!empty($cliente['logo'])): ?>
                            <img src="<?= base_url('uploads/' . $cliente['logo']) ?>" alt="Logo <?= esc($cliente['nombre_cliente']) ?>" style="background-color:#ffffff;">
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
                                <td><?= esc($documento['codigo'] ?? 'RES-SST-001') ?></td>
                            </tr>
                            <tr>
                                <td class="label">Version:</td>
                                <td><?= str_pad($documento['version'] ?? 1, 3, '0', STR_PAD_LEFT) ?></td>
                            </tr>
                            <tr>
                                <td class="label">Fecha:</td>
                                <td><?= date('d/m/Y', strtotime($documento['created_at'] ?? 'now')) ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="encabezado-titulo-central">
                        <div class="nombre-doc">RESPONSABILIDADES DEL RESPONSABLE DEL SG-SST</div>
                    </td>
                </tr>
            </table>

            <!-- Contenido del documento -->
            <?php if (!empty($contenido['secciones'])): ?>
                <?php $parsedown = new \Parsedown(); ?>
                <?php foreach ($contenido['secciones'] as $seccion): ?>
                    <div class="seccion">
                        <?php if (!empty($seccion['titulo'])): ?>
                            <div class="seccion-titulo"><?= esc($seccion['titulo']) ?></div>
                        <?php endif; ?>
                        <div class="seccion-contenido">
                            <?= $parsedown->text($seccion['contenido'] ?? '') ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Control de Cambios -->
            <div class="seccion" style="page-break-inside: avoid; margin-top: 20px;">
                <div class="seccion-titulo" style="background-color: #0d6efd; color: white; padding: 5px 8px; margin-bottom: 0; border: none;">
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
                            <td>Elaboracion inicial del documento</td>
                            <td style="text-align: center;"><?= date('d/m/Y', strtotime($documento['created_at'] ?? 'now')) ?></td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>

            <!-- Seccion de Firmas -->
            <?php
            // Datos del Consultor SST (Elaboro)
            $consultorNombre = $consultor['nombre_consultor'] ?? '';
            $consultorLicencia = $consultor['numero_licencia'] ?? '';
            $consultorCedula = $consultor['cedula_consultor'] ?? '';
            $firmaConsultorImg = $consultor['firma_consultor'] ?? '';
            $firmaConsultorElectronica = ($firmasElectronicas ?? [])['consultor_sst'] ?? null;

            // Datos del Delegado SST (si aplica) - PRIORIDAD MAXIMA
            $requiereDelegado = !empty($contexto['requiere_delegado_sst']);
            $delegadoNombre = $contexto['delegado_sst_nombre'] ?? '';
            $delegadoCargo = $contexto['delegado_sst_cargo'] ?? 'Delegado SST';
            $firmaDelegado = ($firmasElectronicas ?? [])['delegado_sst'] ?? null;

            // Datos del Representante Legal (Aprobo)
            $repLegalNombre = $contexto['representante_legal_nombre'] ?? $cliente['nombre_rep_legal'] ?? $cliente['representante_legal'] ?? '';
            $repLegalCedula = $contexto['representante_legal_documento'] ?? $cliente['cedula_rep_legal'] ?? '';
            $repLegalCargo = $contexto['representante_legal_cargo'] ?? 'Representante Legal';
            $firmaRepLegal = ($firmasElectronicas ?? [])['representante_legal'] ?? null;
            ?>

            <div class="firma-section" style="margin-top: 20px; page-break-inside: avoid;">
                <?php if ($requiereDelegado): ?>
                <!-- ========== 3 FIRMANTES: Delegado SST activo (TIPO E) ========== -->
                <div class="seccion-titulo" style="background-color: #198754; color: white; padding: 5px 8px; margin-bottom: 0; border: none;">
                    FIRMAS DE APROBACION
                </div>
                <table border="1" cellpadding="0" cellspacing="0" style="width: 100%; table-layout: fixed; border-collapse: collapse; border: 1px solid #999; margin-top: 0;">
                    <tr>
                        <td width="33%" style="background-color: #e9ecef; color: #333; font-weight: bold; text-align: center; padding: 4px; border: 1px solid #999; font-size: 8pt;">
                            Elaboro
                        </td>
                        <td width="34%" style="background-color: #e9ecef; color: #333; font-weight: bold; text-align: center; padding: 4px; border: 1px solid #999; font-size: 8pt;">
                            Reviso / Delegado SST
                        </td>
                        <td width="33%" style="background-color: #e9ecef; color: #333; font-weight: bold; text-align: center; padding: 4px; border: 1px solid #999; font-size: 8pt;">
                            Aprobo
                        </td>
                    </tr>
                    <tr>
                        <!-- ELABORO: Consultor SST -->
                        <td style="vertical-align: top; padding: 5px; border: 1px solid #999; font-size: 8pt;">
                            <p style="margin: 2px 0;"><b>Nombre:</b> <?= !empty($consultorNombre) ? esc($consultorNombre) : '_______________' ?></p>
                            <p style="margin: 2px 0;"><b>Cargo:</b> Consultor SST</p>
                            <?php if (!empty($consultorLicencia)): ?>
                            <p style="margin: 2px 0;"><b>Licencia:</b> <?= esc($consultorLicencia) ?></p>
                            <?php endif; ?>
                        </td>
                        <!-- REVISO: Delegado SST -->
                        <td style="vertical-align: top; padding: 5px; border: 1px solid #999; font-size: 8pt;">
                            <p style="margin: 2px 0;"><b>Nombre:</b> <?= !empty($delegadoNombre) ? esc($delegadoNombre) : '_______________' ?></p>
                            <p style="margin: 2px 0;"><b>Cargo:</b> <?= esc($delegadoCargo) ?></p>
                        </td>
                        <!-- APROBO: Representante Legal -->
                        <td style="vertical-align: top; padding: 5px; border: 1px solid #999; font-size: 8pt;">
                            <p style="margin: 2px 0;"><b>Nombre:</b> <?= !empty($repLegalNombre) ? esc($repLegalNombre) : '_______________' ?></p>
                            <p style="margin: 2px 0;"><b>Cargo:</b> <?= esc($repLegalCargo) ?></p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 5px; text-align: center; border: 1px solid #999; height: 45px; vertical-align: bottom;">
                            <?php if ($firmaConsultorElectronica && !empty($firmaConsultorElectronica['evidencia']['firma_imagen'])): ?>
                                <img src="<?= $firmaConsultorElectronica['evidencia']['firma_imagen'] ?>" alt="Firma Consultor" style="max-height: 35px; max-width: 100px; margin-bottom: 3px;">
                            <?php elseif (!empty($firmaConsultorImg)): ?>
                                <img src="<?= base_url('uploads/' . $firmaConsultorImg) ?>" alt="Firma Consultor" style="max-height: 35px; max-width: 100px; margin-bottom: 3px;">
                            <?php endif; ?>
                            <div style="border-top: 1px solid #333; width: 65%; margin: 3px auto 0;">
                                <span style="color: #666; font-size: 6pt;">Firma</span>
                            </div>
                        </td>
                        <td style="padding: 5px; text-align: center; border: 1px solid #999; height: 45px; vertical-align: bottom;">
                            <?php if ($firmaDelegado && !empty($firmaDelegado['evidencia']['firma_imagen'])): ?>
                                <img src="<?= $firmaDelegado['evidencia']['firma_imagen'] ?>" alt="Firma Delegado SST" style="max-height: 35px; max-width: 100px; margin-bottom: 3px;">
                            <?php endif; ?>
                            <div style="border-top: 1px solid #333; width: 65%; margin: 3px auto 0;">
                                <span style="color: #666; font-size: 6pt;">Firma</span>
                            </div>
                        </td>
                        <td style="padding: 5px; text-align: center; border: 1px solid #999; height: 45px; vertical-align: bottom;">
                            <?php if ($firmaRepLegal && !empty($firmaRepLegal['evidencia']['firma_imagen'])): ?>
                                <img src="<?= $firmaRepLegal['evidencia']['firma_imagen'] ?>" alt="Firma Rep. Legal" style="max-height: 35px; max-width: 100px; margin-bottom: 3px;">
                            <?php endif; ?>
                            <div style="border-top: 1px solid #333; width: 65%; margin: 3px auto 0;">
                                <span style="color: #666; font-size: 6pt;">Firma</span>
                            </div>
                        </td>
                    </tr>
                </table>

                <?php else: ?>
                <!-- ========== 1 FIRMANTE: solo_firma_consultor (TIPO A) ========== -->
                <div class="seccion-titulo" style="background-color: #198754; color: white; padding: 5px 8px; margin-bottom: 0; border: none;">
                    FIRMA DE ACEPTACION
                </div>
                <table border="1" cellpadding="0" cellspacing="0" style="width: 100%; border-collapse: collapse; border: 1px solid #999; margin-top: 0;">
                    <tr>
                        <td width="100%" style="background-color: #e9ecef; color: #333; font-weight: bold; text-align: center; padding: 4px; border: 1px solid #999; font-size: 9pt;">
                            RESPONSABLE DEL SG-SST
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top; padding: 10px; border: 1px solid #999; font-size: 8pt; text-align: center;">
                            <p style="margin: 2px 0;"><b>Nombre:</b> <?= !empty($consultorNombre) ? esc($consultorNombre) : '_______________' ?></p>
                            <p style="margin: 2px 0;"><b>Documento:</b> <?= !empty($consultorCedula) ? esc($consultorCedula) : '_______________' ?></p>
                            <?php if (!empty($consultorLicencia)): ?>
                            <p style="margin: 2px 0;"><b>Licencia SST:</b> <?= esc($consultorLicencia) ?></p>
                            <?php endif; ?>
                            <p style="margin: 2px 0;"><b>Cargo:</b> Responsable del SG-SST</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; text-align: center; border: 1px solid #999; height: 60px; vertical-align: bottom;">
                            <?php if ($firmaConsultorElectronica && !empty($firmaConsultorElectronica['evidencia']['firma_imagen'])): ?>
                                <img src="<?= $firmaConsultorElectronica['evidencia']['firma_imagen'] ?>" alt="Firma Responsable SST" style="max-height: 45px; max-width: 150px; margin-bottom: 3px;">
                            <?php elseif (!empty($firmaConsultorImg)): ?>
                                <img src="<?= base_url('uploads/' . $firmaConsultorImg) ?>" alt="Firma Responsable SST" style="max-height: 45px; max-width: 150px; margin-bottom: 3px;">
                            <?php endif; ?>
                            <div style="border-top: 1px solid #333; width: 40%; margin: 3px auto 0;">
                                <span style="color: #666; font-size: 7pt;">Firma</span>
                            </div>
                        </td>
                    </tr>
                </table>
                <?php endif; ?>
            </div>

            <!-- Pie de documento -->
            <div style="margin-top: 20px; padding-top: 10px; border-top: 1px solid #ccc; text-align: center; font-size: 8pt; color: #666;">
                <p style="margin: 2px 0;"><?= esc($cliente['nombre_cliente']) ?> - NIT: <?= esc($cliente['nit_cliente'] ?? '') ?></p>
                <p style="margin: 2px 0;">Documento generado el <?= date('d/m/Y') ?></p>
            </div>
        </div>
    </div>

    <!-- Modal Historial de Versiones (componente estandar) -->
    <?= view('documentos_sst/_components/modal_historial_versiones', [
        'id_documento' => $documento['id_documento'],
        'versiones' => $versiones ?? []
    ]) ?>

    <!-- Modal Nueva Version (componente estandar) -->
    <?= view('documentos_sst/_components/modal_nueva_version', [
        'id_documento' => $documento['id_documento'],
        'version_actual' => ($documento['version'] ?? 1) . '.0',
        'tipo_documento' => $documento['tipo_documento'] ?? 'responsabilidades_responsable_sgsst'
    ]) ?>

    <!-- Modal Regenerar Documento -->
    <div class="modal fade" id="modalRegenerarDocumento" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-arrow-repeat me-2"></i>Actualizar Datos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Seleccione el consultor responsable del SG-SST.
                    </div>

                    <div class="mb-3">
                        <label for="regenerarConsultor" class="form-label">Consultor/Responsable SST</label>
                        <select class="form-select" id="regenerarConsultor">
                            <option value="">-- Seleccione --</option>
                            <?php
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
                            <span class="text-muted">Seleccione un consultor</span>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="descripcionCambio" class="form-label">Descripcion del cambio</label>
                        <textarea class="form-control" id="descripcionCambio" rows="2"
                                  placeholder="Ej: Cambio de responsable SST"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning" id="btnConfirmarRegenerar">
                        <i class="bi bi-arrow-repeat me-1"></i>Actualizar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function mostrarToast(tipo, titulo, mensaje, reintentarCallback) {
        const container = document.getElementById('toastStack');
        const toastId = 'toast-' + Date.now() + '-' + Math.random().toString(36).substr(2, 5);
        const hora = new Date().toLocaleTimeString('es-CO', {hour:'2-digit', minute:'2-digit', second:'2-digit'});
        const configs = {
            'success':  { bg: 'bg-success', text: 'text-white', icon: 'bi-check-circle-fill' },
            'error':    { bg: 'bg-danger',  text: 'text-white', icon: 'bi-x-circle-fill' },
            'warning':  { bg: 'bg-warning', text: 'text-dark',  icon: 'bi-exclamation-triangle-fill' },
            'info':     { bg: 'bg-info',    text: 'text-white', icon: 'bi-info-circle-fill' },
            'ia':       { bg: 'bg-primary', text: 'text-white', icon: 'bi-robot' },
            'save':     { bg: 'bg-success', text: 'text-white', icon: 'bi-save-fill' },
            'database': { bg: 'bg-info',    text: 'text-white', icon: 'bi-database-check' },
            'progress': { bg: 'bg-primary', text: 'text-white', icon: 'spinner' }
        };
        const cfg = configs[tipo] || configs['info'];
        const duraciones = { 'database': 15000, 'error': 8000, 'success': 6000, 'warning': 6000, 'save': 5000, 'progress': 60000 };
        const duracion = duraciones[tipo] || 5000;
        const autoHide = tipo !== 'progress';
        const closeWhite = cfg.text === 'text-white' ? ' btn-close-white' : '';
        const iconHtml = cfg.icon === 'spinner'
            ? '<span class="spinner-border spinner-border-sm me-2"></span>'
            : `<i class="bi ${cfg.icon} me-2"></i>`;

        let bodyHtml = mensaje;
        if (reintentarCallback && tipo === 'error') {
            const cbId = 'retry-' + toastId;
            bodyHtml += `<div class="mt-2"><button class="toast-retry-btn" id="${cbId}">Reintentar</button></div>`;
            window['__toastRetry_' + cbId] = reintentarCallback;
        }

        const toastHtml = `<div id="${toastId}" class="toast" role="alert" style="min-width:300px;box-shadow:0 4px 12px rgba(0,0,0,.15);margin-bottom:8px;">
            <div class="toast-header ${cfg.bg} ${cfg.text}">
                ${iconHtml}
                <strong class="me-auto">${titulo}</strong>
                <small>${hora}</small>
                <button type="button" class="btn-close${closeWhite}" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">${bodyHtml}</div>
        </div>`;

        container.insertAdjacentHTML('beforeend', toastHtml);
        const toastEl = document.getElementById(toastId);
        const instance = new bootstrap.Toast(toastEl, { autohide: autoHide, delay: duracion });
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());

        if (reintentarCallback && tipo === 'error') {
            const retryBtn = document.getElementById('retry-' + toastId);
            if (retryBtn) {
                retryBtn.addEventListener('click', function() {
                    instance.hide();
                    reintentarCallback();
                });
            }
        }

        instance.show();
        return { id: toastId, element: toastEl, instance };
    }

    function cerrarToast(ref) {
        if (ref && ref.instance) ref.instance.hide();
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Preview del consultor
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
                preview.innerHTML = '<span class="text-muted">Seleccione un consultor</span>';
            }
        });

        // Regenerar documento
        document.getElementById('btnConfirmarRegenerar')?.addEventListener('click', function() {
            const btn = this;
            const descripcion = document.getElementById('descripcionCambio').value.trim() || 'Actualizacion de datos';
            const idConsultor = document.getElementById('regenerarConsultor').value;

            if (!idConsultor) {
                mostrarToast('warning', 'Dato Requerido', 'Seleccione un consultor');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Actualizando...';

            const formData = new FormData();
            formData.append('descripcion_cambio', descripcion);
            formData.append('id_consultor_responsable', idConsultor);

            fetch('<?= base_url("documentos-sst/{$cliente['id_cliente']}/regenerar-responsabilidades-responsable-sst/{$anio}") ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarToast('success', 'Documento Actualizado', data.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    mostrarToast('error', 'Error al Actualizar', data.message);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i>Actualizar';
                }
            })
            .catch(error => {
                mostrarToast('error', 'Error de Conexion', 'No se pudo conectar con el servidor');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i>Actualizar';
            });
        });
    });
    </script>
</body>
</html>
