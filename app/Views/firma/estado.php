<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado de Firmas - <?= esc($documento['codigo'] ?? '') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .timeline { position: relative; padding-left: 40px; }
        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #dee2e6;
        }
        .timeline-item {
            position: relative;
            padding-bottom: 24px;
        }
        .timeline-item:last-child { padding-bottom: 0; }
        .timeline-dot {
            position: absolute;
            left: -33px;
            top: 4px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            border: 3px solid #fff;
            box-shadow: 0 0 0 2px #dee2e6;
        }
        .timeline-dot.firmado { background: #10B981; box-shadow: 0 0 0 2px #10B981; }
        .timeline-dot.pendiente { background: #F59E0B; box-shadow: 0 0 0 2px #F59E0B; }
        .timeline-dot.esperando { background: #9CA3AF; box-shadow: 0 0 0 2px #9CA3AF; }
        .timeline-dot.expirado { background: #EF4444; box-shadow: 0 0 0 2px #EF4444; }
        .timeline-dot.cancelado { background: #6B7280; box-shadow: 0 0 0 2px #6B7280; }
        .firma-thumb {
            max-height: 60px;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            background: #fff;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-pen me-2"></i>Estado de Firmas
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="<?= base_url('documentacion/' . ($documento['id_cliente'] ?? '')) ?>">
                    <i class="bi bi-arrow-left me-1"></i>Volver a Documentacion
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle me-2"></i><?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Panel izquierdo: Info documento -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Documento</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong>Codigo:</strong> <code><?= esc($documento['codigo'] ?? '') ?></code></p>
                        <p class="mb-2"><strong>Nombre:</strong> <?= esc($documento['titulo'] ?? $documento['nombre'] ?? '') ?></p>
                        <p class="mb-2"><strong>Version:</strong> <?= $documento['version'] ?? '1' ?></p>
                        <p class="mb-2"><strong>Cliente:</strong> <?= esc($cliente['nombre_empresa'] ?? '') ?></p>
                        <p class="mb-0"><strong>Estado:</strong>
                            <?php
                            $estadoColor = match($documento['estado'] ?? '') {
                                'firmado' => 'success',
                                'pendiente_firma' => 'info',
                                'aprobado' => 'primary',
                                default => 'secondary'
                            };
                            ?>
                            <span class="badge bg-<?= $estadoColor ?>"><?= ucfirst(str_replace('_', ' ', $documento['estado'] ?? '')) ?></span>
                        </p>
                    </div>
                </div>

                <!-- Resumen rapido -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Resumen</h6>
                    </div>
                    <div class="card-body">
                        <?php
                        $totalFirmas = count($solicitudes ?? []);
                        $firmadas = 0;
                        $pendientes = 0;
                        $canceladas = 0;
                        foreach ($solicitudes as $sol) {
                            if ($sol['estado'] === 'firmado') $firmadas++;
                            elseif (in_array($sol['estado'], ['pendiente', 'esperando'])) $pendientes++;
                            elseif (in_array($sol['estado'], ['cancelado', 'expirado'])) $canceladas++;
                        }
                        $progreso = $totalFirmas > 0 ? round(($firmadas / $totalFirmas) * 100) : 0;
                        ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted">Progreso</small>
                                <small class="fw-bold"><?= $firmadas ?>/<?= $totalFirmas ?></small>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: <?= $progreso ?>%"></div>
                            </div>
                        </div>
                        <div class="d-flex gap-3 text-center">
                            <div>
                                <span class="d-block fs-4 fw-bold text-success"><?= $firmadas ?></span>
                                <small class="text-muted">Firmadas</small>
                            </div>
                            <div>
                                <span class="d-block fs-4 fw-bold text-warning"><?= $pendientes ?></span>
                                <small class="text-muted">Pendientes</small>
                            </div>
                            <?php if ($canceladas > 0): ?>
                            <div>
                                <span class="d-block fs-4 fw-bold text-danger"><?= $canceladas ?></span>
                                <small class="text-muted">Canceladas</small>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Acciones -->
                <?php if (($documento['estado'] ?? '') === 'firmado'): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-patch-check text-success" style="font-size: 2rem;"></i>
                        <p class="fw-bold mt-2 mb-3">Documento completamente firmado</p>
                        <a href="<?= base_url('firma/certificado-pdf/' . $documento['id_documento']) ?>" class="btn btn-outline-success btn-sm w-100 mb-2">
                            <i class="bi bi-file-earmark-pdf me-1"></i>Descargar Certificado PDF
                        </a>
                        <?php
                        $codigoVerif = '';
                        if (!empty($solicitudes) && !empty($documento['id_documento'])) {
                            // Filtrar solo firmados y ordenar por id_solicitud para coincidir con DocFirmaModel
                            $firmados = array_filter($solicitudes, fn($s) => $s['estado'] === 'firmado');
                            usort($firmados, fn($a, $b) => $a['id_solicitud'] <=> $b['id_solicitud']);
                            $tokens = array_column($firmados, 'token');
                            if (!empty($tokens)) {
                                // Incluir id_documento para coincidir con DocFirmaModel::generarCodigoVerificacion()
                                $codigoVerif = strtoupper(substr(hash('sha256', implode('|', $tokens) . '|' . $documento['id_documento']), 0, 12));
                            }
                        }
                        ?>
                        <?php if ($codigoVerif): ?>
                        <a href="<?= base_url('firma/verificar/' . $codigoVerif) ?>" class="btn btn-outline-primary btn-sm w-100" target="_blank">
                            <i class="bi bi-shield-check me-1"></i>Ver Certificado Publico
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Panel principal: Timeline de firmas -->
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Linea de Tiempo de Firmas</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($solicitudes)): ?>
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2">No hay solicitudes de firma para este documento.</p>
                                <a href="<?= base_url('firma/solicitar/' . $documento['id_documento']) ?>" class="btn btn-primary">
                                    <i class="bi bi-send me-1"></i>Solicitar Firmas
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="timeline">
                                <?php foreach ($solicitudes as $sol): ?>
                                <div class="timeline-item">
                                    <div class="timeline-dot <?= $sol['estado'] ?>"></div>
                                    <div class="card border-0 bg-light">
                                        <div class="card-body py-3">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1">
                                                        <?= esc($sol['firmante_nombre']) ?>
                                                        <small class="text-muted ms-2">
                                                            <?= match($sol['firmante_tipo']) {
                                                                'delegado_sst' => 'Delegado SST',
                                                                'representante_legal' => 'Representante Legal',
                                                                'elaboro' => 'Elaboro',
                                                                'reviso' => 'Reviso',
                                                                default => ucfirst($sol['firmante_tipo'])
                                                            } ?>
                                                        </small>
                                                    </h6>
                                                    <small class="text-muted">
                                                        <?= esc($sol['firmante_cargo'] ?? '') ?>
                                                        <?php if (!empty($sol['firmante_email'])): ?>
                                                            &middot; <?= esc($sol['firmante_email']) ?>
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                                <div>
                                                    <?php
                                                    $badgeClass = match($sol['estado']) {
                                                        'firmado' => 'bg-success',
                                                        'pendiente' => 'bg-warning text-dark',
                                                        'esperando' => 'bg-secondary',
                                                        'expirado' => 'bg-danger',
                                                        'cancelado' => 'bg-dark',
                                                        'rechazado' => 'bg-danger',
                                                        default => 'bg-secondary'
                                                    };
                                                    ?>
                                                    <span class="badge <?= $badgeClass ?>"><?= ucfirst($sol['estado']) ?></span>
                                                </div>
                                            </div>

                                            <!-- Detalles segun estado -->
                                            <?php if ($sol['estado'] === 'firmado'): ?>
                                                <div class="mt-2 pt-2 border-top">
                                                    <small class="text-success">
                                                        <i class="bi bi-check-circle me-1"></i>
                                                        Firmado el <?= date('d/m/Y H:i', strtotime($sol['fecha_firma'])) ?>
                                                    </small>
                                                    <?php if (!empty($evidencias[$sol['id_solicitud']]) && !empty($evidencias[$sol['id_solicitud']]['firma_imagen'])): ?>
                                                        <div class="mt-2">
                                                            <img src="<?= $evidencias[$sol['id_solicitud']]['firma_imagen'] ?>" class="firma-thumb" alt="Firma">
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php elseif ($sol['estado'] === 'pendiente'): ?>
                                                <div class="mt-2 pt-2 border-top">
                                                    <small class="text-warning">
                                                        <i class="bi bi-clock me-1"></i>
                                                        Esperando firma. Expira el <?= date('d/m/Y', strtotime($sol['fecha_expiracion'])) ?>
                                                    </small>
                                                </div>
                                            <?php elseif ($sol['estado'] === 'esperando'): ?>
                                                <div class="mt-2 pt-2 border-top">
                                                    <small class="text-muted">
                                                        <i class="bi bi-hourglass me-1"></i>
                                                        Esperando turno de firma
                                                    </small>
                                                </div>
                                            <?php elseif ($sol['estado'] === 'expirado'): ?>
                                                <div class="mt-2 pt-2 border-top">
                                                    <small class="text-danger">
                                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                                        Link expirado el <?= date('d/m/Y', strtotime($sol['fecha_expiracion'])) ?>
                                                    </small>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Acciones -->
                                            <?php if (in_array($sol['estado'], ['pendiente', 'esperando', 'expirado'])): ?>
                                                <div class="mt-2 pt-2 border-top d-flex gap-2">
                                                    <?php if (in_array($sol['estado'], ['pendiente', 'expirado'])): ?>
                                                        <form action="<?= base_url('firma/reenviar/' . $sol['id_solicitud']) ?>" method="post" class="d-inline">
                                                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                                                <i class="bi bi-send me-1"></i>Reenviar
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                    <form action="<?= base_url('firma/cancelar/' . $sol['id_solicitud']) ?>" method="post" class="d-inline"
                                                          onsubmit="return confirm('Cancelar esta solicitud de firma?')">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="bi bi-x-circle me-1"></i>Cancelar
                                                        </button>
                                                    </form>
                                                    <a href="<?= base_url('firma/audit-log/' . $sol['id_solicitud']) ?>" class="btn btn-sm btn-outline-secondary">
                                                        <i class="bi bi-journal-text me-1"></i>Audit Log
                                                    </a>
                                                </div>
                                            <?php elseif ($sol['estado'] === 'firmado'): ?>
                                                <div class="mt-1">
                                                    <a href="<?= base_url('firma/audit-log/' . $sol['id_solicitud']) ?>" class="btn btn-sm btn-outline-secondary">
                                                        <i class="bi bi-journal-text me-1"></i>Ver Audit Log
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
