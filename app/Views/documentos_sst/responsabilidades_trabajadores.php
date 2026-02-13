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
            .page-break { page-break-before: always; }
            .tabla-firmas { font-size: 9pt; }
            .tabla-firmas td, .tabla-firmas th { padding: 8px 5px !important; }
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

        .tabla-firmas {
            width: 100%;
            border-collapse: collapse;
        }
        .tabla-firmas th, .tabla-firmas td {
            border: 1px solid #333;
            padding: 10px 8px;
            text-align: center;
            vertical-align: middle;
        }
        .tabla-firmas th {
            background-color: #f8f9fa;
            font-weight: 600;
            font-size: 0.85rem;
        }
        .tabla-firmas td {
            height: 35px;
        }
        .tabla-firmas .col-num { width: 40px; }
        .tabla-firmas .col-fecha { width: 90px; }
        .tabla-firmas .col-nombre { width: 25%; }
        .tabla-firmas .col-cedula { width: 15%; }
        .tabla-firmas .col-cargo { width: 20%; }
        .tabla-firmas .col-firma { width: 20%; }
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
                    <span class="ms-3"><?= esc($cliente['nombre_cliente']) ?> - Responsabilidades Trabajadores <?= $anio ?></span>
                </div>
                <div>
                    <a href="<?= base_url('documentos-sst/historial/' . $documento['id_documento']) ?>" class="btn btn-outline-light btn-sm me-2">
                        <i class="bi bi-clock-history me-1"></i>Historial
                    </a>
                    <a href="<?= base_url('documentos-sst/exportar-pdf/' . $documento['id_documento']) ?>" class="btn btn-danger btn-sm me-2">
                        <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                    </a>
                    <a href="<?= base_url('documentos-sst/exportar-word/' . $documento['id_documento']) ?>" class="btn btn-primary btn-sm me-2">
                        <i class="bi bi-file-earmark-word me-1"></i>Word
                    </a>
                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalRegenerarDocumento">
                        <i class="bi bi-arrow-repeat me-1"></i>Actualizar
                    </button>
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
                            <span class="badge bg-dark"><?= esc($documento['codigo'] ?? 'Sin codigo') ?></span>
                            <span class="badge bg-light text-dark">v<?= $documento['version'] ?>.0</span>
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle me-1"></i>Aprobado
                            </span>
                        </div>
                        <small class="opacity-75">Documento firmado por el Consultor SST</small>
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
                        <small class="text-muted">Version:</small>
                        <span class="fw-bold"><?= $documento['version'] ?>.0</span>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Fecha:</small>
                        <span class="fw-bold"><?= date('d/m/Y') ?></span>
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
                                <td><?= esc($documento['codigo'] ?? 'RES-TRA-001') ?></td>
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
                        <div class="nombre-doc">RESPONSABILIDADES DE TRABAJADORES Y CONTRATISTAS EN EL SG-SST</div>
                    </td>
                </tr>
            </table>

            <!-- Contenido del documento -->
            <?php if (!empty($contenido['secciones'])): ?>
                <?php $parsedown = new \Parsedown(); ?>
                <?php foreach ($contenido['secciones'] as $seccion): ?>
                    <div class="seccion">
                        <div class="seccion-titulo"><?= esc($seccion['titulo']) ?></div>
                        <div class="seccion-contenido">
                            <?= $parsedown->text($seccion['contenido'] ?? '') ?>
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

            <!-- PAGINA DE FIRMAS (Separada para impresion) -->
            <div class="page-break"></div>

            <!-- Titulo seccion firmas -->
            <div class="firma-section" style="margin-top: 40px; page-break-inside: avoid;">
                <div class="seccion-titulo" style="background: linear-gradient(90deg, #198754, #20c997); color: white; padding: 10px 15px; border-radius: 5px; margin-bottom: 0; border: none;">
                    <i class="bi bi-pen me-2"></i>FIRMA DE ACEPTACION
                </div>
            </div>

            <!-- Encabezado repetido para pagina de firmas -->
            <table class="encabezado-formal" style="margin-top: 20px;">
                <tr>
                    <td class="encabezado-logo" rowspan="2">
                        <?php if (!empty($cliente['logo'])): ?>
                            <img src="<?= base_url('uploads/' . $cliente['logo']) ?>" alt="Logo">
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
                                <td><?= esc($documento['codigo'] ?? 'RES-TRA-001') ?></td>
                            </tr>
                            <tr>
                                <td class="label">Version:</td>
                                <td><?= str_pad($documento['version'] ?? 1, 3, '0', STR_PAD_LEFT) ?></td>
                            </tr>
                            <tr>
                                <td class="label">Pagina:</td>
                                <td>Hoja de Firmas</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="encabezado-titulo-central">
                        <div class="nombre-doc">REGISTRO DE FIRMAS - RESPONSABILIDADES DE TRABAJADORES</div>
                    </td>
                </tr>
            </table>

            <!-- Instrucciones -->
            <div style="background: #e7f3ff; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #0d6efd;">
                <p style="margin: 0; font-size: 0.9rem;">
                    <strong><i class="bi bi-info-circle me-1"></i>Instrucciones:</strong>
                    Con mi firma certifico haber leido, entendido y aceptado las responsabilidades establecidas en este documento.
                    Este registro se diligencia durante el proceso de induccion en Seguridad y Salud en el Trabajo.
                </p>
            </div>

            <!-- Tabla de Firmas -->
            <table class="tabla-firmas">
                <thead>
                    <tr>
                        <th class="col-num">No.</th>
                        <th class="col-fecha">Fecha</th>
                        <th class="col-nombre">Nombre Completo</th>
                        <th class="col-cedula">Cedula</th>
                        <th class="col-cargo">Cargo / Area</th>
                        <th class="col-firma">Firma</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $filas = $contenido['filas_firma'] ?? 15;
                    for ($i = 1; $i <= $filas; $i++):
                    ?>
                    <tr>
                        <td><?= $i ?></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <?php endfor; ?>
                </tbody>
            </table>

            <!-- Pie de documento -->
            <div class="text-center text-muted mt-4 pt-3 border-top" style="font-size: 0.75rem;">
                <p class="mb-1">Documento generado el <?= date('d/m/Y') ?> - Sistema de Gestion SST</p>
                <p class="mb-0"><?= esc($cliente['nombre_cliente']) ?> - NIT: <?= esc($cliente['nit_cliente'] ?? '') ?></p>
            </div>
        </div>
    </div>

    <!-- Modal Regenerar Documento -->
    <div class="modal fade" id="modalRegenerarDocumento" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-arrow-repeat me-2"></i>Actualizar Documento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Se actualizara el documento con los datos mas recientes del contexto SST.
                    </div>
                    <div class="mb-3">
                        <label for="descripcionCambio" class="form-label">Descripcion del cambio</label>
                        <textarea class="form-control" id="descripcionCambio" rows="2"
                                  placeholder="Ej: Actualizacion de responsabilidades"></textarea>
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
        // Regenerar documento
        document.getElementById('btnConfirmarRegenerar')?.addEventListener('click', function() {
            const btn = this;
            const descripcion = document.getElementById('descripcionCambio').value.trim() || 'Actualizacion del documento';

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Actualizando...';

            const formData = new FormData();
            formData.append('descripcion_cambio', descripcion);

            fetch('<?= base_url("documentos-sst/{$cliente['id_cliente']}/regenerar-responsabilidades-trabajadores/{$anio}") ?>', {
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
