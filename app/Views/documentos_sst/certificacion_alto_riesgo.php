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

        .documento-cuerpo {
            text-align: justify;
            line-height: 1.8;
            font-size: 0.95rem;
            margin-bottom: 30px;
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
                    <span class="ms-3"><?= esc($cliente['nombre_cliente']) ?> - Certificacion No Alto Riesgo <?= $anio ?></span>
                </div>
                <div>
                    <button type="button" class="btn btn-outline-light btn-sm me-2" data-bs-toggle="modal" data-bs-target="#modalHistorialVersiones">
                        <i class="bi bi-clock-history me-1"></i>Historial
                    </button>
                    <a href="<?= base_url('documentos-sst/exportar-pdf/' . $documento['id_documento']) ?>" class="btn btn-danger btn-sm me-2" target="_blank">
                        <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                    </a>
                    <a href="<?= base_url('documentos-sst/exportar-word/' . $documento['id_documento']) ?>" class="btn btn-primary btn-sm me-2" target="_blank">
                        <i class="bi bi-file-earmark-word me-1"></i>Word
                    </a>
                    <button type="button" class="btn btn-warning btn-sm me-2" data-bs-toggle="modal" data-bs-target="#modalRegenerarDocumento">
                        <i class="bi bi-arrow-repeat me-1"></i>Actualizar Datos
                    </button>
                    <?php if (in_array($documento['estado'] ?? '', ['generado', 'aprobado', 'en_revision', 'pendiente_firma'])): ?>
                        <a href="<?= base_url('firma/solicitar/' . $documento['id_documento']) ?>" class="btn btn-success btn-sm me-2" target="_blank">
                            <i class="bi bi-pen me-1"></i>Solicitar Firmas
                        </a>
                    <?php endif; ?>
                    <?php if (($documento['estado'] ?? '') === 'firmado'): ?>
                        <a href="<?= base_url('firma/estado/' . $documento['id_documento']) ?>" class="btn btn-outline-success btn-sm me-2" target="_blank">
                            <i class="bi bi-patch-check me-1"></i>Ver Firmas
                        </a>
                    <?php endif; ?>
                    <?php if (in_array($documento['estado'] ?? '', ['generado', 'aprobado', 'en_revision', 'pendiente_firma'])): ?>
                        <a href="<?= base_url('firma/estado/' . $documento['id_documento']) ?>" class="btn btn-outline-warning btn-sm me-2" target="_blank">
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

            <!-- Info del documento (no imprimible) -->
            <div class="info-documento no-print">
                <div class="row">
                    <div class="col-md-4">
                        <small class="text-muted">Tipo de documento:</small>
                        <span class="fw-bold">Certificacion</span>
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
                                <td><?= esc($documento['codigo'] ?? 'CRT-AR-001') ?></td>
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
                        <div class="nombre-doc">CERTIFICACION DE NO ALTO RIESGO</div>
                    </td>
                </tr>
            </table>

            <?php
            $repLegalNombre = $contenido['representante_legal']['nombre'] ?? $contexto['representante_legal_nombre'] ?? '';
            $repLegalCedula = $contenido['representante_legal']['cedula'] ?? $contexto['representante_legal_cedula'] ?? '';
            $nombreEmpresa = $contenido['empresa']['nombre'] ?? $cliente['nombre_cliente'] ?? '';
            $nitEmpresa = $contenido['empresa']['nit'] ?? $cliente['nit_cliente'] ?? '';
            $direccionEmpresa = $contenido['empresa']['direccion'] ?? $cliente['direccion_cliente'] ?? '';
            ?>

            <!-- Datos de la empresa -->
            <div style="margin-bottom: 25px; line-height: 1.6; font-size: 0.95rem;">
                <div><strong><?= esc($nombreEmpresa) ?></strong></div>
                <div>NIT: <?= esc($nitEmpresa) ?></div>
                <?php if (!empty($direccionEmpresa)): ?>
                <div>Direccion: <?= esc($direccionEmpresa) ?></div>
                <?php endif; ?>
            </div>

            <!-- Fecha y destinatario -->
            <div style="margin-bottom: 25px; font-size: 0.95rem;">
                <div style="margin-bottom: 15px;"><?= esc($contenido['fecha_expedicion'] ?? '') ?></div>
                <div style="font-weight: 600;">A quien corresponda:</div>
            </div>

            <!-- Cuerpo de la certificacion -->
            <div class="documento-cuerpo">
                <?php if (!empty($contenido['secciones'])): ?>
                    <?php foreach ($contenido['secciones'] as $seccion): ?>
                        <?php if ($seccion['key'] !== 'encabezado'): ?>
                        <p><?= $seccion['contenido'] ?? '' ?></p>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- ============================================== -->
            <!-- SECCION: CONTROL DE CAMBIOS (Imprimible) -->
            <!-- ============================================== -->
            <div style="page-break-inside: avoid; margin-top: 40px;">
                <div style="background: linear-gradient(90deg, #0d6efd, #6610f2); color: white; padding: 10px 15px; border-radius: 5px; margin-bottom: 0; font-weight: bold;">
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
                                <span style="display: inline-block; background: #0d6efd; color: white; padding: 3px 12px; border-radius: 20px; font-weight: bold;">
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
                                <span style="display: inline-block; background: #0d6efd; color: white; padding: 3px 12px; border-radius: 20px; font-weight: bold;">1.0</span>
                            </td>
                            <td>Elaboracion inicial del documento</td>
                            <td style="text-align: center;"><?= date('d/m/Y', strtotime($documento['created_at'] ?? 'now')) ?></td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- ============================================== -->
            <!-- SECCION: FIRMA (Solo Representante Legal) -->
            <!-- ============================================== -->
            <div class="firma-section" style="margin-top: 40px; page-break-inside: avoid;">
                <div style="background: linear-gradient(90deg, #198754, #20c997); color: white; padding: 10px 15px; border-radius: 5px; margin-bottom: 0; font-weight: bold;">
                    <i class="bi bi-pen me-2"></i>FIRMA
                </div>
                <table class="table table-bordered mb-0" style="font-size: 0.85rem; border-top: none;">
                    <tbody>
                        <tr>
                            <td style="vertical-align: top; padding: 25px; height: 200px; text-align: center;">
                                <div style="margin-top: 60px;">
                                    <?php
                                    $firmaRepLegal = ($firmasElectronicas ?? [])['representante_legal'] ?? null;
                                    if ($firmaRepLegal && !empty($firmaRepLegal['evidencia']['firma_imagen'])):
                                    ?>
                                        <img src="<?= $firmaRepLegal['evidencia']['firma_imagen'] ?>" alt="Firma Rep. Legal" style="max-height: 70px; max-width: 200px;">
                                    <?php endif; ?>
                                    <div style="border-top: 1px solid #333; width: 40%; margin: 10px auto 5px auto; padding-top: 5px;">
                                        <small style="color: #666;">Firma</small>
                                    </div>
                                </div>
                                <div style="margin-top: 10px;">
                                    <strong>Representante Legal</strong><br>
                                    <?= esc($repLegalNombre) ?><br>
                                    <?php if (!empty($repLegalCedula)): ?>
                                    C.C. <?= esc($repLegalCedula) ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pie de documento -->
            <div class="text-center text-muted mt-4 pt-3 border-top" style="font-size: 0.75rem;">
                <p class="mb-1">Documento generado el <?= date('d/m/Y') ?> - Sistema de Gestion SST</p>
                <p class="mb-0"><?= esc($cliente['nombre_cliente']) ?> - NIT: <?= esc($cliente['nit_cliente'] ?? '') ?></p>
            </div>
        </div>
    </div>

    <!-- Modal Historial de Versiones -->
    <?= view('documentos_sst/_components/modal_historial_versiones', [
        'id_documento' => $documento['id_documento'],
        'versiones' => $versiones ?? []
    ]) ?>

    <!-- Modal Nueva Version -->
    <?= view('documentos_sst/_components/modal_nueva_version', [
        'id_documento' => $documento['id_documento'],
        'version_actual' => ($documento['version'] ?? 1) . '.0',
        'tipo_documento' => $documento['tipo_documento'] ?? 'certificacion_no_alto_riesgo',
        'es_primera_aprobacion' => empty($versiones ?? [])
    ]) ?>

    <!-- Modal Regenerar/Actualizar Documento -->
    <div class="modal fade" id="modalRegenerarDocumento" tabindex="-1">
        <div class="modal-dialog">
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
                                           value="<?= esc($contexto['representante_legal_nombre'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="regenerarRepLegalCedula" class="form-label">Cedula</label>
                                    <input type="text" class="form-control" id="regenerarRepLegalCedula"
                                           value="<?= esc($contexto['representante_legal_cedula'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="descripcionCambio" class="form-label">Descripcion del cambio (opcional):</label>
                        <textarea class="form-control" id="descripcionCambio" rows="2"
                                  placeholder="Ej: Cambio de representante legal"></textarea>
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
    function mostrarToast(tipo, titulo, mensaje) {
        const container = document.getElementById('toastStack');
        const toastId = 'toast-' + Date.now() + '-' + Math.random().toString(36).substr(2, 5);
        const hora = new Date().toLocaleTimeString('es-CO', {hour:'2-digit', minute:'2-digit', second:'2-digit'});
        const configs = {
            'success':  { bg: 'bg-success', text: 'text-white', icon: 'bi-check-circle-fill' },
            'error':    { bg: 'bg-danger',  text: 'text-white', icon: 'bi-x-circle-fill' },
            'warning':  { bg: 'bg-warning', text: 'text-dark',  icon: 'bi-exclamation-triangle-fill' },
            'info':     { bg: 'bg-info',    text: 'text-white', icon: 'bi-info-circle-fill' }
        };
        const cfg = configs[tipo] || configs['info'];
        const duracion = tipo === 'error' ? 8000 : 5000;
        const closeWhite = cfg.text === 'text-white' ? ' btn-close-white' : '';

        const toastHtml = `<div id="${toastId}" class="toast" role="alert" style="min-width:300px;box-shadow:0 4px 12px rgba(0,0,0,.15);margin-bottom:8px;">
            <div class="toast-header ${cfg.bg} ${cfg.text}">
                <i class="bi ${cfg.icon} me-2"></i>
                <strong class="me-auto">${titulo}</strong>
                <small>${hora}</small>
                <button type="button" class="btn-close${closeWhite}" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">${mensaje}</div>
        </div>`;

        container.insertAdjacentHTML('beforeend', toastHtml);
        const toastEl = document.getElementById(toastId);
        const instance = new bootstrap.Toast(toastEl, { delay: duracion });
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
        instance.show();
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('btnConfirmarRegenerar')?.addEventListener('click', function() {
            const btn = this;
            const descripcion = document.getElementById('descripcionCambio').value.trim()
                || 'Actualizacion de datos de representante legal';

            const repLegalNombre = document.getElementById('regenerarRepLegalNombre').value.trim();
            const repLegalCedula = document.getElementById('regenerarRepLegalCedula').value.trim();

            if (!repLegalNombre) {
                mostrarToast('warning', 'Dato Requerido', 'El nombre del representante legal es obligatorio');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Actualizando...';

            const formData = new FormData();
            formData.append('descripcion_cambio', descripcion);
            formData.append('representante_legal_nombre', repLegalNombre);
            formData.append('representante_legal_cedula', repLegalCedula);

            fetch('<?= base_url("documentos-sst/{$cliente['id_cliente']}/regenerar-certificacion-alto-riesgo/{$anio}") ?>', {
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
                    btn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i>Actualizar y Crear Nueva Version';
                }
            })
            .catch(error => {
                mostrarToast('error', 'Error de Conexion', 'No se pudo conectar con el servidor');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i>Actualizar y Crear Nueva Version';
            });
        });
    });
    </script>
</body>
</html>
