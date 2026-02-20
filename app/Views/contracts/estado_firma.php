<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado de Firma - Contrato <?= esc($contract['numero_contrato'] ?? '') ?></title>
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
        .timeline-dot.sin_enviar { background: #9CA3AF; box-shadow: 0 0 0 2px #9CA3AF; }
        .timeline-dot.expirado { background: #EF4444; box-shadow: 0 0 0 2px #EF4444; }
        .firma-thumb {
            max-height: 60px;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            background: #fff;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-pen me-2"></i>Estado de Firma - Contrato
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="<?= base_url('contracts/view/' . $contract['id_contrato']) ?>">
                    <i class="bi bi-arrow-left me-1"></i>Volver al Contrato
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

        <?php
        $estadoFirma = $contract['estado_firma'] ?? 'sin_enviar';
        $nombreFirmante = $contract['nombre_rep_legal_cliente'] ?? 'Representante Legal';
        $emailFirmante = $contract['email_cliente'] ?? '';
        $tokenExpirado = false;
        if ($estadoFirma === 'pendiente_firma' && !empty($contract['token_firma_expiracion'])) {
            $tokenExpirado = strtotime($contract['token_firma_expiracion']) < time();
        }
        ?>

        <div class="row">
            <!-- Panel izquierdo: Info contrato -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Contrato</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong>N&deg;:</strong> <code><?= esc($contract['numero_contrato'] ?? '') ?></code></p>
                        <p class="mb-2"><strong>Cliente:</strong> <?= esc($contract['nombre_cliente'] ?? '') ?></p>
                        <p class="mb-2"><strong>NIT:</strong> <?= esc($contract['nit_cliente'] ?? '') ?></p>
                        <p class="mb-0"><strong>Estado firma:</strong>
                            <?php
                            $badgeClass = match($estadoFirma) {
                                'firmado' => 'bg-success',
                                'pendiente_firma' => $tokenExpirado ? 'bg-danger' : 'bg-warning text-dark',
                                default => 'bg-secondary'
                            };
                            $estadoTexto = match($estadoFirma) {
                                'firmado' => 'Firmado',
                                'pendiente_firma' => $tokenExpirado ? 'Expirado' : 'Pendiente de firma',
                                default => 'Sin enviar'
                            };
                            ?>
                            <span class="badge <?= $badgeClass ?>"><?= $estadoTexto ?></span>
                        </p>
                    </div>
                </div>

                <!-- Resumen -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Resumen</h6>
                    </div>
                    <div class="card-body">
                        <?php $progreso = $estadoFirma === 'firmado' ? 100 : 0; ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted">Progreso</small>
                                <small class="fw-bold"><?= $estadoFirma === 'firmado' ? '1/1' : '0/1' ?></small>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: <?= $progreso ?>%"></div>
                            </div>
                        </div>
                        <div class="d-flex gap-3 text-center">
                            <?php if ($estadoFirma === 'firmado'): ?>
                            <div>
                                <span class="d-block fs-4 fw-bold text-success">1</span>
                                <small class="text-muted">Firmada</small>
                            </div>
                            <?php elseif ($estadoFirma === 'pendiente_firma'): ?>
                            <div>
                                <span class="d-block fs-4 fw-bold text-warning">1</span>
                                <small class="text-muted"><?= $tokenExpirado ? 'Expirada' : 'Pendiente' ?></small>
                            </div>
                            <?php else: ?>
                            <div>
                                <span class="d-block fs-4 fw-bold text-secondary">1</span>
                                <small class="text-muted">Sin enviar</small>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if ($estadoFirma === 'firmado'): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-patch-check text-success" style="font-size: 2rem;"></i>
                        <p class="fw-bold mt-2 mb-0">Contrato firmado</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Panel principal: Timeline -->
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Estado de Firma</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item">
                                <?php
                                $dotClass = match(true) {
                                    $estadoFirma === 'firmado' => 'firmado',
                                    $tokenExpirado => 'expirado',
                                    $estadoFirma === 'pendiente_firma' => 'pendiente',
                                    default => 'sin_enviar'
                                };
                                ?>
                                <div class="timeline-dot <?= $dotClass ?>"></div>
                                <div class="card border-0 bg-light">
                                    <div class="card-body py-3">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">
                                                    <?= esc($nombreFirmante) ?>
                                                    <small class="text-muted ms-2">Representante Legal</small>
                                                </h6>
                                                <small class="text-muted">
                                                    <?= esc($contract['cedula_rep_legal_cliente'] ?? '') ?>
                                                    <?php if (!empty($emailFirmante)): ?>
                                                        &middot; <?= esc($emailFirmante) ?>
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                            <div>
                                                <span class="badge <?= $badgeClass ?>"><?= $estadoTexto ?></span>
                                            </div>
                                        </div>

                                        <!-- Detalles segun estado -->
                                        <?php if ($estadoFirma === 'firmado'): ?>
                                            <div class="mt-2 pt-2 border-top">
                                                <small class="text-success">
                                                    <i class="bi bi-check-circle me-1"></i>
                                                    Firmado el <?= date('d/m/Y H:i', strtotime($contract['firma_cliente_fecha'])) ?>
                                                    por <?= esc($contract['firma_cliente_nombre']) ?>
                                                    (C.C. <?= esc($contract['firma_cliente_cedula']) ?>)
                                                </small>
                                                <?php if (!empty($contract['firma_cliente_ip'])): ?>
                                                    <br><small class="text-muted">IP: <?= esc($contract['firma_cliente_ip']) ?></small>
                                                <?php endif; ?>
                                            </div>
                                        <?php elseif ($estadoFirma === 'pendiente_firma' && !$tokenExpirado): ?>
                                            <div class="mt-2 pt-2 border-top">
                                                <small class="text-warning">
                                                    <i class="bi bi-clock me-1"></i>
                                                    Esperando firma. Expira el <?= date('d/m/Y H:i', strtotime($contract['token_firma_expiracion'])) ?>
                                                </small>
                                            </div>
                                        <?php elseif ($tokenExpirado): ?>
                                            <div class="mt-2 pt-2 border-top">
                                                <small class="text-danger">
                                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                                    Enlace expirado el <?= date('d/m/Y H:i', strtotime($contract['token_firma_expiracion'])) ?>. Reenv&iacute;e para generar un nuevo enlace.
                                                </small>
                                            </div>
                                        <?php else: ?>
                                            <div class="mt-2 pt-2 border-top">
                                                <small class="text-muted">
                                                    <i class="bi bi-hourglass me-1"></i>
                                                    No se ha enviado solicitud de firma
                                                </small>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Acciones -->
                                        <?php if ($estadoFirma === 'pendiente_firma'): ?>
                                            <?php $urlFirma = base_url('contrato/firmar/' . ($contract['token_firma'] ?? '')); ?>
                                            <div class="mt-2 pt-2 border-top d-flex gap-2 flex-wrap">
                                                <button type="button" class="btn btn-sm btn-outline-info"
                                                        onclick="copiarEnlaceFirma('<?= $urlFirma ?>', '<?= esc($nombreFirmante) ?>')">
                                                    <i class="bi bi-clipboard me-1"></i>Copiar enlace
                                                </button>
                                                <a href="https://wa.me/?text=<?= urlencode('Firme el contrato SST: ' . $urlFirma) ?>"
                                                   target="_blank" class="btn btn-sm btn-success">
                                                    <i class="bi bi-whatsapp me-1"></i>WhatsApp
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                        onclick="reenviarFirmaContrato()">
                                                    <i class="bi bi-send me-1"></i>Reenviar
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-warning"
                                                        onclick="modalEmailAlternativo('<?= base_url('contracts/reenviar-firma-contrato') ?>', '<?= esc($nombreFirmante) ?>', '<?= esc($emailFirmante) ?>')">
                                                    <i class="bi bi-envelope-at me-1"></i>Email alt.
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                        onclick="cancelarFirmaContrato()">
                                                    <i class="bi bi-x-circle me-1"></i>Cancelar
                                                </button>
                                            </div>
                                        <?php elseif ($estadoFirma === 'sin_enviar'): ?>
                                            <div class="mt-2 pt-2 border-top">
                                                <a href="<?= base_url('contracts/view/' . $contract['id_contrato']) ?>" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-send me-1"></i>Ir al contrato para enviar a firmar
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= base_url('js/firma-helpers.js') ?>"></script>
    <script>
    function reenviarFirmaContrato() {
        Swal.fire({
            title: 'Reenviar Solicitud de Firma',
            html: '<p>Se generará un nuevo enlace y se enviará a:</p><p><strong><?= esc($emailFirmante) ?></strong></p><p class="text-muted small">El enlace anterior quedará invalidado.</p>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            confirmButtonText: '<i class="bi bi-send me-1"></i>Reenviar',
            cancelButtonText: 'Cancelar'
        }).then(function(result) {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Enviando...', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });
                var formData = new FormData();
                formData.append('id_contrato', '<?= $contract['id_contrato'] ?>');
                fetch('<?= base_url("contracts/reenviar-firma-contrato") ?>', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                }).then(function(r) { return r.json(); }).then(function(data) {
                    if (data.success) {
                        Swal.fire({ icon: 'success', title: 'Enviado', text: data.mensaje, timer: 3000 }).then(function() { location.reload(); });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: data.mensaje });
                    }
                }).catch(function() {
                    Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar con el servidor.' });
                });
            }
        });
    }

    function cancelarFirmaContrato() {
        Swal.fire({
            title: 'Cancelar Solicitud de Firma',
            html: '<p>Se cancelará la solicitud de firma para:</p><p><strong><?= esc($nombreFirmante) ?></strong></p><p class="text-muted small">El enlace de firma quedará invalidado y el contrato volverá a estado "sin enviar".</p>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: '<i class="bi bi-x-circle me-1"></i>Sí, cancelar',
            cancelButtonText: 'No, mantener'
        }).then(function(result) {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Cancelando...', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });
                var formData = new FormData();
                formData.append('id_contrato', '<?= $contract['id_contrato'] ?>');
                fetch('<?= base_url("contracts/cancelar-firma-contrato") ?>', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                }).then(function(r) { return r.json(); }).then(function(data) {
                    if (data.success) {
                        Swal.fire({ icon: 'success', title: 'Cancelada', text: data.mensaje, timer: 3000 }).then(function() { location.reload(); });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: data.mensaje });
                    }
                }).catch(function() {
                    Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar con el servidor.' });
                });
            }
        });
    }
    </script>
</body>
</html>
