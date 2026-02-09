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
                    <?php if (in_array($documento['estado'] ?? '', ['generado', 'aprobado', 'en_revision', 'pendiente_firma'])): ?>
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
                            <span class="badge bg-success estado-badge">
                                <i class="bi bi-check-circle me-1"></i>
                                Aprobado
                            </span>
                        </div>
                        <small class="opacity-75">
                            Elaboró: Consultor SST | Aprobó: Representante Legal
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
                        <span class="fw-bold text-success">Elaboró + Aprobó</span>
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
                                <td><?= esc($documento['codigo'] ?? 'RES-SST-001') ?></td>
                            </tr>
                            <tr>
                                <td class="label">Version:</td>
                                <td><?= str_pad($documento['version'] ?? 1, 3, '0', STR_PAD_LEFT) ?></td>
                            </tr>
                            <tr>
                                <td class="label">Fecha:</td>
                                <td><?= date('j M Y') ?></td>
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
                <?php foreach ($contenido['secciones'] as $seccion): ?>
                    <div class="seccion">
                        <div class="seccion-titulo"><?= esc($seccion['titulo']) ?></div>
                        <div class="seccion-contenido">
                            <?= $seccion['contenido'] ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Control de Cambios -->
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
                                <td style="text-align: center;"><?= date('d/m/Y') ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Sección de Firmas -->
            <?php
            // Datos del Consultor SST (Elaboró)
            $consultorNombre = $consultor['nombre_consultor'] ?? '';
            $consultorLicencia = $consultor['numero_licencia'] ?? '';
            $consultorCedula = $consultor['cedula_consultor'] ?? '';
            $firmaConsultorImg = $consultor['firma_consultor'] ?? '';
            $firmaConsultorElectronica = ($firmasElectronicas ?? [])['consultor_sst'] ?? null;

            // Datos del Delegado SST (si aplica) - PRIORIDAD MÁXIMA
            $requiereDelegado = !empty($contexto['requiere_delegado_sst']);
            $delegadoNombre = $contexto['delegado_sst_nombre'] ?? '';
            $delegadoCargo = $contexto['delegado_sst_cargo'] ?? 'Delegado SST';
            $firmaDelegado = ($firmasElectronicas ?? [])['delegado_sst'] ?? null;

            // Datos del Representante Legal (Aprobó)
            $repLegalNombre = $contexto['representante_legal_nombre'] ?? $cliente['nombre_rep_legal'] ?? $cliente['representante_legal'] ?? '';
            $repLegalCedula = $contexto['representante_legal_documento'] ?? $cliente['cedula_rep_legal'] ?? '';
            $repLegalCargo = $contexto['representante_legal_cargo'] ?? 'Representante Legal';
            $firmaRepLegal = ($firmasElectronicas ?? [])['representante_legal'] ?? null;
            ?>

            <div class="firma-section" style="margin-top: 40px; page-break-inside: avoid;">
                <div class="seccion-titulo" style="background: linear-gradient(90deg, #198754, #20c997); color: white; padding: 10px 15px; border-radius: 5px; margin-bottom: 0; border: none;">
                    <i class="bi bi-pen me-2"></i>FIRMAS DE APROBACIÓN
                </div>

                <?php if ($requiereDelegado): ?>
                <!-- ========== 3 FIRMANTES: Cliente tiene Delegado SST (PRIORIDAD MÁXIMA) ========== -->
                <table class="table table-bordered mb-0" style="font-size: 0.85rem; border-top: none;">
                    <thead>
                        <tr style="background: linear-gradient(135deg, #f8f9fa, #e9ecef);">
                            <th style="width: 33.33%; text-align: center; padding: 10px;">ELABORÓ</th>
                            <th style="width: 33.33%; text-align: center; padding: 10px;">REVISÓ / Delegado SST</th>
                            <th style="width: 33.33%; text-align: center; padding: 10px;">APROBÓ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <!-- ELABORÓ: Consultor SST -->
                            <td style="vertical-align: top; padding: 15px; height: 180px;">
                                <div class="text-center mb-2">
                                    <?php if (!empty($firmaConsultorImg)): ?>
                                        <img src="<?= base_url('uploads/' . $firmaConsultorImg) ?>" alt="Firma Consultor" style="max-height: 50px; max-width: 120px;">
                                    <?php elseif ($firmaConsultorElectronica): ?>
                                        <span class="text-success"><i class="bi bi-patch-check-fill"></i> Firmado</span>
                                    <?php else: ?>
                                        <div style="height: 50px; border-bottom: 1px solid #333; width: 80%; margin: 0 auto;"></div>
                                    <?php endif; ?>
                                </div>
                                <div class="text-center" style="font-size: 0.75rem;">
                                    <strong><?= esc($consultorNombre) ?></strong><br>
                                    <span class="text-muted">Consultor SST</span><br>
                                    <?php if (!empty($consultorLicencia)): ?>
                                        <small>Lic. SST: <?= esc($consultorLicencia) ?></small><br>
                                    <?php endif; ?>
                                    <?php if (!empty($consultorCedula)): ?>
                                        <small>C.C. <?= esc($consultorCedula) ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <!-- REVISÓ: Delegado SST -->
                            <td style="vertical-align: top; padding: 15px; height: 180px;">
                                <div class="text-center mb-2">
                                    <?php if ($firmaDelegado && !empty($firmaDelegado['evidencia']['firma_imagen'])): ?>
                                        <img src="<?= $firmaDelegado['evidencia']['firma_imagen'] ?>" alt="Firma Delegado SST" style="max-height: 50px; max-width: 120px;">
                                    <?php elseif ($firmaDelegado): ?>
                                        <span class="text-success"><i class="bi bi-patch-check-fill"></i> Firmado</span>
                                    <?php else: ?>
                                        <div style="height: 50px; border-bottom: 1px solid #333; width: 80%; margin: 0 auto;"></div>
                                    <?php endif; ?>
                                </div>
                                <div class="text-center" style="font-size: 0.75rem;">
                                    <?php if ($firmaDelegado && !empty($firmaDelegado['solicitud']['firmante_nombre'])): ?>
                                        <strong><?= esc($firmaDelegado['solicitud']['firmante_nombre']) ?></strong><br>
                                    <?php else: ?>
                                        <strong><?= esc($delegadoNombre) ?></strong><br>
                                    <?php endif; ?>
                                    <span class="text-muted"><?= esc($delegadoCargo) ?></span>
                                </div>
                            </td>
                            <!-- APROBÓ: Representante Legal -->
                            <td style="vertical-align: top; padding: 15px; height: 180px;">
                                <div class="text-center mb-2">
                                    <?php if ($firmaRepLegal && !empty($firmaRepLegal['evidencia']['firma_imagen'])): ?>
                                        <img src="<?= $firmaRepLegal['evidencia']['firma_imagen'] ?>" alt="Firma Rep. Legal" style="max-height: 50px; max-width: 120px;">
                                    <?php elseif ($firmaRepLegal): ?>
                                        <span class="text-success"><i class="bi bi-patch-check-fill"></i> Firmado</span>
                                    <?php else: ?>
                                        <div style="height: 50px; border-bottom: 1px solid #333; width: 80%; margin: 0 auto;"></div>
                                    <?php endif; ?>
                                </div>
                                <div class="text-center" style="font-size: 0.75rem;">
                                    <?php if ($firmaRepLegal && !empty($firmaRepLegal['solicitud']['firmante_nombre'])): ?>
                                        <strong><?= esc($firmaRepLegal['solicitud']['firmante_nombre']) ?></strong><br>
                                    <?php else: ?>
                                        <strong><?= esc($repLegalNombre) ?></strong><br>
                                    <?php endif; ?>
                                    <span class="text-muted"><?= esc($repLegalCargo) ?></span><br>
                                    <?php if ($firmaRepLegal && !empty($firmaRepLegal['solicitud']['firmante_documento'])): ?>
                                        <small>C.C. <?= esc($firmaRepLegal['solicitud']['firmante_documento']) ?></small>
                                    <?php elseif (!empty($repLegalCedula)): ?>
                                        <small>C.C. <?= esc($repLegalCedula) ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php else: ?>
                <!-- ========== 2 FIRMANTES: Sin Delegado SST ========== -->
                <table class="table table-bordered mb-0" style="font-size: 0.85rem; border-top: none;">
                    <thead>
                        <tr style="background: linear-gradient(135deg, #f8f9fa, #e9ecef);">
                            <th style="width: 50%; text-align: center; padding: 10px;">ELABORÓ</th>
                            <th style="width: 50%; text-align: center; padding: 10px;">APROBÓ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <!-- ELABORÓ: Consultor SST -->
                            <td style="vertical-align: top; padding: 20px; height: 200px;">
                                <div class="text-center mb-2">
                                    <?php if (!empty($firmaConsultorImg)): ?>
                                        <img src="<?= base_url('uploads/' . $firmaConsultorImg) ?>" alt="Firma Consultor" style="max-height: 60px; max-width: 150px;">
                                    <?php elseif ($firmaConsultorElectronica): ?>
                                        <span class="text-success"><i class="bi bi-patch-check-fill"></i> Firmado electrónicamente</span>
                                    <?php else: ?>
                                        <div style="height: 60px; border-bottom: 1px solid #333; width: 80%; margin: 0 auto;"></div>
                                    <?php endif; ?>
                                </div>
                                <div class="text-center" style="font-size: 0.8rem;">
                                    <strong><?= esc($consultorNombre) ?></strong><br>
                                    <span class="text-muted">Consultor SST / Responsable SG-SST</span><br>
                                    <?php if (!empty($consultorLicencia)): ?>
                                        <small>Lic. SST: <?= esc($consultorLicencia) ?></small><br>
                                    <?php endif; ?>
                                    <?php if (!empty($consultorCedula)): ?>
                                        <small>C.C. <?= esc($consultorCedula) ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <!-- APROBÓ: Representante Legal -->
                            <td style="vertical-align: top; padding: 20px; height: 200px;">
                                <div class="text-center mb-2">
                                    <?php if ($firmaRepLegal && !empty($firmaRepLegal['evidencia']['firma_imagen'])): ?>
                                        <img src="<?= $firmaRepLegal['evidencia']['firma_imagen'] ?>" alt="Firma Rep. Legal" style="max-height: 60px; max-width: 150px;">
                                    <?php elseif ($firmaRepLegal): ?>
                                        <span class="text-success"><i class="bi bi-patch-check-fill"></i> Firmado electrónicamente</span>
                                    <?php else: ?>
                                        <div style="height: 60px; border-bottom: 1px solid #333; width: 80%; margin: 0 auto;"></div>
                                    <?php endif; ?>
                                </div>
                                <div class="text-center" style="font-size: 0.8rem;">
                                    <?php if ($firmaRepLegal && !empty($firmaRepLegal['solicitud']['firmante_nombre'])): ?>
                                        <strong><?= esc($firmaRepLegal['solicitud']['firmante_nombre']) ?></strong><br>
                                    <?php else: ?>
                                        <strong><?= esc($repLegalNombre) ?></strong><br>
                                    <?php endif; ?>
                                    <span class="text-muted"><?= esc($repLegalCargo) ?></span><br>
                                    <?php if ($firmaRepLegal && !empty($firmaRepLegal['solicitud']['firmante_documento'])): ?>
                                        <small>C.C. <?= esc($firmaRepLegal['solicitud']['firmante_documento']) ?></small>
                                    <?php elseif (!empty($repLegalCedula)): ?>
                                        <small>C.C. <?= esc($repLegalCedula) ?></small>
                                    <?php endif; ?>
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
    document.addEventListener('DOMContentLoaded', function() {
        const idDocumento = <?= $documento['id_documento'] ?>;

        // Cargar historial
        document.getElementById('modalHistorialVersiones')?.addEventListener('show.bs.modal', function() {
            const contenedor = document.getElementById('contenedorHistorial');
            fetch('<?= base_url('documentos-sst/historial-versiones/') ?>' + idDocumento)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.versiones.length > 0) {
                    let html = '<div class="table-responsive"><table class="table table-hover">';
                    html += '<thead><tr><th>Version</th><th>Descripcion</th><th>Fecha</th></tr></thead><tbody>';
                    data.versiones.forEach(v => {
                        const fecha = new Date(v.fecha_autorizacion).toLocaleDateString('es-CO');
                        html += `<tr><td><strong>v${v.version_texto}</strong></td><td>${v.descripcion_cambio}</td><td>${fecha}</td></tr>`;
                    });
                    html += '</tbody></table></div>';
                    contenedor.innerHTML = html;
                } else {
                    contenedor.innerHTML = '<p class="text-center text-muted">No hay versiones registradas.</p>';
                }
            });
        });

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
                alert('Seleccione un consultor');
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
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i>Actualizar';
                }
            });
        });
    });
    </script>
</body>
</html>
