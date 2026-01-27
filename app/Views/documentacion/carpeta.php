<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($carpeta['nombre']) ?> - Documentacion SST</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .breadcrumb-item a { text-decoration: none; }
        .doc-card { transition: transform 0.2s; }
        .doc-card:hover { transform: translateY(-3px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .folder-card { background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%); }
        .estado-badge {
            font-size: 0.7rem;
            padding: 0.2rem 0.5rem;
        }
        /* Badges de estado IA */
        .estado-ia-pendiente { background-color: #6c757d; color: white; }
        .estado-ia-creado { background-color: #ffc107; color: #212529; }
        .estado-ia-aprobado { background-color: #198754; color: white; }

        /* Stats badges en carpetas */
        .folder-stats {
            display: flex;
            gap: 4px;
            justify-content: center;
            margin-top: 8px;
        }
        .folder-stats .badge {
            font-size: 0.65rem;
            padding: 2px 6px;
        }

        /* Indicador visual en tarjeta de documento */
        .doc-estado-indicator {
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            border-radius: 4px 0 0 4px;
        }
        .doc-estado-indicator.pendiente { background-color: #6c757d; }
        .doc-estado-indicator.creado { background-color: #ffc107; }
        .doc-estado-indicator.aprobado { background-color: #198754; }

        .doc-row { position: relative; }
        .doc-row td:first-child { padding-left: 12px; }

        /* Panel de Fases */
        .fases-panel {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .fases-titulo {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 16px;
        }
        .fases-timeline {
            display: flex;
            align-items: flex-start;
            gap: 0;
            position: relative;
        }
        .fase-item {
            flex: 1;
            text-align: center;
            position: relative;
        }
        .fase-item::after {
            content: '';
            position: absolute;
            top: 24px;
            left: 50%;
            width: 100%;
            height: 3px;
            background: #dee2e6;
            z-index: 0;
        }
        .fase-item:last-child::after {
            display: none;
        }
        .fase-item.completo::after {
            background: #198754;
        }
        .fase-item.en_proceso::after {
            background: linear-gradient(90deg, #ffc107 0%, #dee2e6 100%);
        }
        .fase-circulo {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            font-size: 1.2rem;
            position: relative;
            z-index: 1;
            transition: all 0.3s;
        }
        .fase-circulo.pendiente {
            background: #e9ecef;
            color: #6c757d;
            border: 2px solid #6c757d;
        }
        .fase-circulo.en_proceso {
            background: #fff3cd;
            color: #856404;
            border: 2px solid #ffc107;
            animation: pulse 2s infinite;
        }
        .fase-circulo.completo {
            background: #d1e7dd;
            color: #0f5132;
            border: 2px solid #198754;
        }
        .fase-circulo.bloqueado {
            background: #e9ecef;
            color: #6c757d;
            border: 2px dashed #adb5bd;
            opacity: 0.6;
        }
        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.5); }
            50% { box-shadow: 0 0 0 8px rgba(255, 193, 7, 0); }
        }
        .fase-nombre {
            font-weight: 500;
            font-size: 0.85rem;
            color: #333;
            margin-bottom: 4px;
        }
        .fase-mensaje {
            font-size: 0.75rem;
            color: #6c757d;
            max-width: 140px;
            margin: 0 auto;
        }
        .fase-cantidad {
            font-size: 0.7rem;
            color: #0d6efd;
            font-weight: 500;
        }
        .fase-acciones {
            margin-top: 8px;
        }
        .fase-acciones .btn {
            font-size: 0.7rem;
            padding: 4px 10px;
        }
        .fase-bloqueado-overlay {
            position: relative;
        }
        .fase-bloqueado-overlay::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.5);
            z-index: 2;
            border-radius: 8px;
        }

        /* Alerta de fases incompletas */
        .fases-alerta {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 12px 16px;
            margin-top: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .fases-alerta i {
            font-size: 1.5rem;
            color: #856404;
        }
        .fases-alerta-texto {
            flex: 1;
        }
        .fases-alerta-titulo {
            font-weight: 600;
            color: #856404;
        }
        .fases-alerta-desc {
            font-size: 0.85rem;
            color: #664d03;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= base_url('documentacion/' . $cliente['id_cliente']) ?>">
                <i class="bi bi-folder-fill me-2"></i>Documentacion SST
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text text-white me-3">
                    <i class="bi bi-building me-1"></i>
                    <?= esc($cliente['nombre_cliente']) ?>
                </span>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Breadcrumb mejorado -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb bg-white p-3 rounded shadow-sm">
                <li class="breadcrumb-item">
                    <a href="<?= base_url('documentacion/' . $cliente['id_cliente']) ?>">
                        <i class="bi bi-house me-1"></i>Inicio
                    </a>
                </li>
                <?php if (!empty($ruta)): ?>
                    <?php foreach ($ruta as $index => $item): ?>
                        <?php if ($item['id_carpeta'] == $carpeta['id_carpeta']): ?>
                            <li class="breadcrumb-item active">
                                <i class="bi bi-folder-fill text-warning me-1"></i>
                                <?= esc($item['nombre']) ?>
                            </li>
                        <?php else: ?>
                            <li class="breadcrumb-item">
                                <a href="<?= base_url('documentacion/' . $cliente['id_cliente']) ?>">
                                    <?= esc($item['nombre']) ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ol>
        </nav>

        <!-- Alertas -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Header de carpeta -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="mb-1">
                            <i class="bi bi-folder-fill text-warning me-2"></i>
                            <?= esc($carpeta['nombre']) ?>
                        </h4>
                        <?php if (!empty($carpeta['codigo'])): ?>
                            <span class="badge bg-light text-dark me-2"><?= esc($carpeta['codigo']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($carpeta['descripcion'])): ?>
                            <p class="text-muted mb-0 mt-1"><?= esc($carpeta['descripcion']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4 text-end">
                        <?php if (isset($fasesInfo) && $fasesInfo && !$fasesInfo['puede_generar_documento']): ?>
                            <button type="button" class="btn btn-secondary" disabled title="Complete las fases previas">
                                <i class="bi bi-lock me-1"></i>Crear con IA
                            </button>
                        <?php elseif (isset($tipoCarpetaFases) && $tipoCarpetaFases === 'capacitacion_sst'): ?>
                            <?php
                            // Solo mostrar "Crear con IA" si NO hay documento aprobado para el año actual
                            $hayAprobadoAnioActual = false;
                            if (!empty($documentosSSTAprobados)) {
                                foreach ($documentosSSTAprobados as $d) {
                                    if ($d['anio'] == date('Y')) {
                                        $hayAprobadoAnioActual = true;
                                        break;
                                    }
                                }
                            }
                            ?>
                            <?php if (!$hayAprobadoAnioActual): ?>
                                <a href="<?= base_url('documentos/generar/programa_capacitacion/' . $cliente['id_cliente']) ?>"
                                   class="btn btn-success">
                                    <i class="bi bi-magic me-1"></i>Crear con IA <?= date('Y') ?>
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="<?= base_url('documentacion/nuevo/' . $cliente['id_cliente'] . '?carpeta=' . $carpeta['id_carpeta']) ?>"
                               class="btn btn-primary">
                                <i class="bi bi-plus-lg me-1"></i>Nuevo Documento
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($fasesInfo) && $fasesInfo && $fasesInfo['tiene_fases']): ?>
        <!-- Panel de Fases de Dependencia -->
        <div class="fases-panel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fases-titulo mb-0">
                    <i class="bi bi-diagram-3 me-2"></i>Fases para Generacion del Documento
                </h5>
                <?php if ($fasesInfo['todas_completas']): ?>
                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Listo para generar</span>
                <?php else: ?>
                    <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Fases pendientes</span>
                <?php endif; ?>
            </div>

            <div class="fases-timeline">
                <?php foreach ($fasesInfo['fases'] as $fase): ?>
                    <div class="fase-item <?= $fase['estado'] ?>">
                        <div class="fase-circulo <?= $fase['estado'] ?>">
                            <?php
                            $icono = match($fase['estado']) {
                                'completo' => 'bi-check-lg',
                                'en_proceso' => 'bi-arrow-repeat',
                                'bloqueado' => 'bi-lock-fill',
                                default => 'bi-circle'
                            };
                            ?>
                            <i class="bi <?= $icono ?>"></i>
                        </div>
                        <div class="fase-nombre"><?= esc($fase['nombre']) ?></div>
                        <div class="fase-mensaje"><?= esc($fase['mensaje']) ?></div>
                        <?php if ($fase['cantidad'] > 0): ?>
                            <div class="fase-cantidad"><?= $fase['cantidad'] ?> registros</div>
                        <?php endif; ?>
                        <div class="fase-acciones">
                            <?php if ($fase['estado'] !== 'bloqueado'): ?>
                                <?php if ($fase['puede_generar'] && $fase['url_generar']): ?>
                                    <a href="<?= base_url(ltrim($fase['url_generar'], '/')) ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-magic me-1"></i>Generar
                                    </a>
                                <?php endif; ?>
                                <?php if ($fase['url_modulo']): ?>
                                    <a href="<?= base_url(ltrim($fase['url_modulo'], '/')) ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i>Ver
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <button class="btn btn-sm btn-outline-secondary" disabled>
                                    <i class="bi bi-lock me-1"></i>Bloqueado
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Fase Final: Documento IA -->
                <?php
                $tieneDocumento = isset($documentoExistente) && $documentoExistente;
                $estadoFaseDoc = $tieneDocumento ? 'completo' : ($fasesInfo['todas_completas'] ? 'en_proceso' : 'bloqueado');
                ?>
                <div class="fase-item <?= $estadoFaseDoc ?>">
                    <div class="fase-circulo <?= $estadoFaseDoc ?>">
                        <i class="bi <?= $tieneDocumento ? 'bi-check-lg' : ($fasesInfo['todas_completas'] ? 'bi-file-earmark-text-fill' : 'bi-lock-fill') ?>"></i>
                    </div>
                    <div class="fase-nombre">Documento IA</div>
                    <div class="fase-mensaje">
                        <?php if ($tieneDocumento): ?>
                            Documento creado
                            <?= isset($documentoExistente['estado']) && $documentoExistente['estado'] === 'aprobado' ? '(Aprobado)' : '(Borrador)' ?>
                        <?php elseif ($fasesInfo['todas_completas']): ?>
                            Listo para generar documento
                        <?php else: ?>
                            Complete las fases anteriores
                        <?php endif; ?>
                    </div>
                    <div class="fase-acciones">
                        <?php if ($tieneDocumento): ?>
                            <a href="<?= base_url('documentos/generar/programa_capacitacion/' . $cliente['id_cliente']) ?>"
                               class="btn btn-sm btn-primary">
                                <i class="bi bi-pencil-square me-1"></i>Ver/Editar
                            </a>
                        <?php elseif ($fasesInfo['todas_completas']): ?>
                            <?php
                            $urlCrearIA = base_url('documentacion/nuevo/' . $cliente['id_cliente'] . '?carpeta=' . $carpeta['id_carpeta'] . '&ia=1');
                            if (isset($tipoCarpetaFases) && $tipoCarpetaFases === 'capacitacion_sst') {
                                $urlCrearIA = base_url('documentos/generar/programa_capacitacion/' . $cliente['id_cliente']);
                            }
                            ?>
                            <a href="<?= $urlCrearIA ?>"
                               class="btn btn-sm btn-success">
                                <i class="bi bi-magic me-1"></i>Crear con IA
                            </a>
                        <?php else: ?>
                            <button class="btn btn-sm btn-outline-secondary" disabled>
                                <i class="bi bi-lock me-1"></i>Bloqueado
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if (!$fasesInfo['todas_completas'] && isset($fasesInfo['siguiente_fase'])): ?>
            <div class="fases-alerta">
                <i class="bi bi-info-circle-fill"></i>
                <div class="fases-alerta-texto">
                    <div class="fases-alerta-titulo">Siguiente paso: <?= esc($fasesInfo['siguiente_fase']['nombre']) ?></div>
                    <div class="fases-alerta-desc"><?= esc($fasesInfo['siguiente_fase']['descripcion']) ?></div>
                </div>
                <?php if ($fasesInfo['siguiente_fase']['url_modulo']): ?>
                <a href="<?= base_url(ltrim($fasesInfo['siguiente_fase']['url_modulo'], '/')) ?>" class="btn btn-warning btn-sm">
                    <i class="bi bi-arrow-right me-1"></i>Ir al modulo
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if (isset($tipoCarpetaFases) && $tipoCarpetaFases === 'capacitacion_sst'): ?>
        <!-- Tabla de Documentos SST -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0">
                    <i class="bi bi-file-earmark-check me-2"></i>Documentos SST
                </h6>
            </div>
            <div class="card-body">
                <?php if (!empty($documentosSSTAprobados)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 120px;">Código</th>
                                    <th>Nombre</th>
                                    <th style="width: 80px;">Año</th>
                                    <th style="width: 80px;">Versión</th>
                                    <th style="width: 110px;">Estado</th>
                                    <th style="width: 150px;">Fecha Aprobación</th>
                                    <th style="width: 110px;">Firmas</th>
                                    <th style="width: 180px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($documentosSSTAprobados as $docSST): ?>
                                    <?php
                                    $estadoDoc = $docSST['estado'] ?? 'aprobado';
                                    $estadoBadge = match($estadoDoc) {
                                        'firmado' => 'bg-success',
                                        'pendiente_firma' => 'bg-info',
                                        'aprobado' => 'bg-primary',
                                        'borrador' => 'bg-warning text-dark',
                                        'generado' => 'bg-secondary',
                                        default => 'bg-secondary'
                                    };
                                    $estadoTexto = match($estadoDoc) {
                                        'firmado' => 'Firmado',
                                        'pendiente_firma' => 'Pendiente firma',
                                        'aprobado' => 'Aprobado',
                                        'borrador' => 'Borrador',
                                        'generado' => 'Generado',
                                        default => ucfirst(str_replace('_', ' ', $estadoDoc))
                                    };
                                    $estadoIcono = match($estadoDoc) {
                                        'firmado' => 'bi-patch-check-fill',
                                        'pendiente_firma' => 'bi-pen',
                                        'aprobado' => 'bi-check-circle',
                                        'borrador' => 'bi-pencil-square',
                                        'generado' => 'bi-file-earmark-text',
                                        default => 'bi-circle'
                                    };
                                    ?>
                                    <?php $docIndex = $loop ?? $docSST['id_documento']; ?>
                                    <tr>
                                        <td><code><?= esc($docSST['codigo'] ?? 'N/A') ?></code></td>
                                        <td>
                                            <strong><?= esc($docSST['titulo']) ?></strong>
                                            <?php if (!empty($docSST['versiones']) && count($docSST['versiones']) > 0): ?>
                                                <button class="btn btn-sm btn-link p-0 ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#versiones-<?= $docSST['id_documento'] ?>">
                                                    <i class="bi bi-clock-history me-1"></i><?= count($docSST['versiones']) ?> versiones
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?= esc($docSST['anio']) ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">v<?= esc($docSST['version_texto'] ?? $docSST['version'] . '.0') ?></span>
                                        </td>
                                        <td>
                                            <span class="badge <?= $estadoBadge ?>">
                                                <i class="bi <?= $estadoIcono ?> me-1"></i><?= $estadoTexto ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($docSST['fecha_aprobacion'])): ?>
                                                <small><?= date('d/m/Y H:i', strtotime($docSST['fecha_aprobacion'])) ?></small>
                                            <?php else: ?>
                                                <span class="text-muted small">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($docSST['firmas_total'] > 0): ?>
                                                <span class="badge <?= $docSST['firmas_firmadas'] == $docSST['firmas_total'] ? 'bg-success' : 'bg-warning text-dark' ?>">
                                                    <?= $docSST['firmas_firmadas'] ?>/<?= $docSST['firmas_total'] ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted small">Sin firmas</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= base_url('documentos-sst/exportar-pdf/' . $docSST['id_documento']) ?>"
                                                   class="btn btn-danger" title="Descargar PDF" target="_blank">
                                                    <i class="bi bi-file-earmark-pdf"></i>
                                                </a>
                                                <?php if (!empty($docSST['archivo_pdf'])): ?>
                                                <a href="<?= esc($docSST['archivo_pdf']) ?>"
                                                   class="btn btn-outline-danger" title="PDF firmado publicado" target="_blank">
                                                    <i class="bi bi-patch-check-fill"></i>
                                                </a>
                                                <?php endif; ?>
                                                <a href="<?= base_url('documentos-sst/' . $cliente['id_cliente'] . '/programa-capacitacion/' . $docSST['anio']) ?>"
                                                   class="btn btn-outline-primary" title="Ver documento" target="_blank">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="<?= base_url('documentos/generar/programa_capacitacion/' . $cliente['id_cliente'] . '?anio=' . $docSST['anio']) ?>"
                                                   class="btn btn-outline-warning" title="Editar documento" target="_blank">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="<?= base_url('firma/estado/' . $docSST['id_documento']) ?>"
                                                   class="btn btn-outline-success" title="Firmas y Audit Log" target="_blank">
                                                    <i class="bi bi-pen"></i>
                                                </a>
                                                <a href="<?= base_url('documentos-sst/publicar-pdf/' . $docSST['id_documento']) ?>"
                                                   class="btn btn-outline-dark" title="Publicar en Reportes"
                                                   onclick="return confirm('¿Publicar este documento en Reportes? Será consultable desde reportList.')">
                                                    <i class="bi bi-cloud-upload"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php if (!empty($docSST['versiones'])): ?>
                                    <tr class="collapse" id="versiones-<?= $docSST['id_documento'] ?>">
                                        <td colspan="8" class="p-0">
                                            <div class="bg-light p-3">
                                                <h6 class="mb-2"><i class="bi bi-clock-history me-1"></i>Historial de Versiones</h6>
                                                <table class="table table-sm table-bordered mb-0 bg-white">
                                                    <thead>
                                                        <tr class="table-secondary">
                                                            <th style="width:80px;">Version</th>
                                                            <th style="width:90px;">Tipo</th>
                                                            <th>Descripcion del Cambio</th>
                                                            <th style="width:90px;">Estado</th>
                                                            <th style="width:150px;">Autorizado por</th>
                                                            <th style="width:150px;">Fecha</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($docSST['versiones'] as $ver): ?>
                                                        <tr>
                                                            <td><span class="badge bg-primary">v<?= esc($ver['version_texto']) ?></span></td>
                                                            <td>
                                                                <span class="badge <?= $ver['tipo_cambio'] === 'mayor' ? 'bg-danger' : 'bg-info' ?>">
                                                                    <?= $ver['tipo_cambio'] === 'mayor' ? 'Mayor' : 'Menor' ?>
                                                                </span>
                                                            </td>
                                                            <td><?= esc($ver['descripcion_cambio']) ?></td>
                                                            <td>
                                                                <span class="badge <?= $ver['estado'] === 'vigente' ? 'bg-success' : 'bg-secondary' ?>">
                                                                    <?= ucfirst($ver['estado']) ?>
                                                                </span>
                                                            </td>
                                                            <td><?= esc($ver['autorizado_por'] ?? '-') ?></td>
                                                            <td><?= $ver['fecha_autorizacion'] ? date('d/m/Y H:i', strtotime($ver['fecha_autorizacion'])) : '-' ?></td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-file-earmark-x text-muted" style="font-size: 2.5rem;"></i>
                        <p class="text-muted mt-2 mb-0">No hay documentos aprobados o firmados aún.</p>
                        <small class="text-muted">Complete las fases y apruebe el documento para verlo aquí.</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="row">
            <!-- Subcarpetas con estadisticas -->
            <?php if (!empty($subcarpetas)): ?>
                <div class="col-12 mb-4">
                    <h6 class="text-muted mb-3"><i class="bi bi-folder me-2"></i>Subcarpetas</h6>
                    <div class="row g-3">
                        <?php foreach ($subcarpetas as $sub): ?>
                            <?php
                                $stats = $sub['stats'] ?? ['total' => 0, 'pendiente' => 0, 'creado' => 0, 'aprobado' => 0];
                            ?>
                            <div class="col-md-3">
                                <a href="<?= base_url('documentacion/carpeta/' . $sub['id_carpeta']) ?>" class="text-decoration-none">
                                    <div class="card border-0 shadow-sm doc-card folder-card h-100">
                                        <div class="card-body text-center py-3">
                                            <i class="bi bi-folder-fill text-warning" style="font-size: 2rem;"></i>
                                            <h6 class="mt-2 mb-1 text-dark"><?= esc($sub['nombre']) ?></h6>
                                            <small class="text-muted d-block">
                                                <?= $stats['total'] ?> documento(s)
                                            </small>
                                            <?php if ($stats['total'] > 0): ?>
                                            <div class="folder-stats">
                                                <?php if ($stats['aprobado'] > 0): ?>
                                                    <span class="badge estado-ia-aprobado" title="Aprobados">
                                                        <i class="bi bi-check-circle"></i> <?= $stats['aprobado'] ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if ($stats['creado'] > 0): ?>
                                                    <span class="badge estado-ia-creado" title="Creados">
                                                        <i class="bi bi-pencil"></i> <?= $stats['creado'] ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if ($stats['pendiente'] > 0): ?>
                                                    <span class="badge estado-ia-pendiente" title="Pendientes">
                                                        <i class="bi bi-clock"></i> <?= $stats['pendiente'] ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!isset($tipoCarpetaFases)): ?>
            <!-- Documentos con estado IA -->
            <div class="col-12">
                <h6 class="text-muted mb-3"><i class="bi bi-file-earmark-text me-2"></i>Documentos</h6>
                <?php if (empty($documentos)): ?>
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-file-earmark-x text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">No hay documentos en esta carpeta</p>
                            <?php if (isset($tipoCarpetaFases) && $tipoCarpetaFases === 'capacitacion_sst'): ?>
                                <a href="<?= base_url('documentos/generar/programa_capacitacion/' . $cliente['id_cliente']) ?>"
                                   class="btn btn-success btn-sm">
                                    <i class="bi bi-magic me-1"></i>Crear con IA
                                </a>
                            <?php else: ?>
                                <a href="<?= base_url('documentacion/nuevo/' . $cliente['id_cliente'] . '?carpeta=' . $carpeta['id_carpeta']) ?>"
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-plus-lg me-1"></i>Crear documento
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card border-0 shadow-sm">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 120px;">Codigo</th>
                                        <th>Nombre</th>
                                        <th style="width: 100px;">Version</th>
                                        <th style="width: 130px;">Estado IA</th>
                                        <th style="width: 130px;">Estado Doc</th>
                                        <th style="width: 100px;">Actualizado</th>
                                        <th style="width: 120px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($documentos as $doc): ?>
                                        <?php
                                            $estadoIA = $doc['estado_ia'] ?? 'pendiente';
                                            $estadoIAClass = match($estadoIA) {
                                                'aprobado' => 'estado-ia-aprobado',
                                                'creado' => 'estado-ia-creado',
                                                default => 'estado-ia-pendiente'
                                            };
                                            $estadoIAIcon = match($estadoIA) {
                                                'aprobado' => 'bi-check-circle-fill',
                                                'creado' => 'bi-pencil-fill',
                                                default => 'bi-clock-fill'
                                            };
                                            $estadoIAText = match($estadoIA) {
                                                'aprobado' => 'Aprobado',
                                                'creado' => 'Creado',
                                                default => 'Pendiente'
                                            };

                                            $estadoDocClass = match($doc['estado'] ?? 'borrador') {
                                                'borrador' => 'bg-info',
                                                'en_revision' => 'bg-warning text-dark',
                                                'pendiente_firma' => 'bg-purple text-white',
                                                'aprobado' => 'bg-success',
                                                'obsoleto' => 'bg-secondary',
                                                default => 'bg-light text-dark'
                                            };
                                        ?>
                                        <tr class="doc-row">
                                            <td>
                                                <div class="doc-estado-indicator <?= $estadoIA ?>"></div>
                                                <code><?= esc($doc['codigo']) ?></code>
                                            </td>
                                            <td>
                                                <a href="/documentacion/ver/<?= $doc['id_documento'] ?>" class="text-decoration-none fw-medium">
                                                    <?= esc($doc['nombre']) ?>
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">v<?= $doc['version_actual'] ?></span>
                                            </td>
                                            <td>
                                                <span class="badge <?= $estadoIAClass ?>">
                                                    <i class="bi <?= $estadoIAIcon ?> me-1"></i><?= $estadoIAText ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge estado-badge <?= $estadoDocClass ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $doc['estado'] ?? 'borrador')) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= date('d/m/Y', strtotime($doc['updated_at'])) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="/documentacion/ver/<?= $doc['id_documento'] ?>"
                                                       class="btn btn-outline-primary" title="Ver">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="/documentacion/editar/<?= $doc['id_documento'] ?>"
                                                       class="btn btn-outline-secondary" title="Editar">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="/exportar/pdf/<?= $doc['id_documento'] ?>"
                                                       class="btn btn-outline-danger" title="PDF">
                                                        <i class="bi bi-file-pdf"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Leyenda de estados IA -->
                    <div class="mt-3 p-3 bg-white rounded shadow-sm">
                        <small class="text-muted me-3"><strong>Estados IA:</strong></small>
                        <span class="badge estado-ia-pendiente me-2"><i class="bi bi-clock-fill me-1"></i>Pendiente</span>
                        <small class="text-muted me-3">Sin contenido generado</small>
                        <span class="badge estado-ia-creado me-2"><i class="bi bi-pencil-fill me-1"></i>Creado</span>
                        <small class="text-muted me-3">Contenido generado, pendiente aprobacion</small>
                        <span class="badge estado-ia-aprobado me-2"><i class="bi bi-check-circle-fill me-1"></i>Aprobado</span>
                        <small class="text-muted">Todas las secciones aprobadas</small>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
