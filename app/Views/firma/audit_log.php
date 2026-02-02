<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Log - <?= esc($solicitud['firmante_nombre'] ?? '') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .event-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
        }
        .json-details {
            background: #1e293b;
            color: #e2e8f0;
            border-radius: 6px;
            padding: 12px;
            font-size: 0.8rem;
            font-family: 'Consolas', 'Monaco', monospace;
            max-height: 200px;
            overflow-y: auto;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark" style="background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-journal-text me-2"></i>Audit Log
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="<?= base_url('firma/estado/' . ($documento['id_documento'] ?? '')) ?>">
                    <i class="bi bi-arrow-left me-1"></i>Volver al estado
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row">
            <!-- Info de la solicitud -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-person me-2"></i>Solicitud de Firma</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong>Firmante:</strong> <?= esc($solicitud['firmante_nombre'] ?? '') ?></p>
                        <p class="mb-2"><strong>Tipo:</strong>
                            <?= match($solicitud['firmante_tipo'] ?? '') {
                                'delegado_sst' => 'Delegado SST',
                                'representante_legal' => 'Representante Legal',
                                'elaboro' => 'Elaboro',
                                'reviso' => 'Reviso',
                                default => ucfirst($solicitud['firmante_tipo'] ?? '')
                            } ?>
                        </p>
                        <p class="mb-2"><strong>Cargo:</strong> <?= esc($solicitud['firmante_cargo'] ?? '') ?></p>
                        <p class="mb-2"><strong>Email:</strong> <code><?= esc($solicitud['firmante_email'] ?? '') ?></code></p>
                        <p class="mb-2"><strong>Estado:</strong>
                            <?php
                            $badgeClass = match($solicitud['estado'] ?? '') {
                                'firmado' => 'bg-success',
                                'pendiente' => 'bg-warning text-dark',
                                'esperando' => 'bg-secondary',
                                'expirado' => 'bg-danger',
                                'cancelado' => 'bg-dark',
                                default => 'bg-secondary'
                            };
                            ?>
                            <span class="badge <?= $badgeClass ?>"><?= ucfirst($solicitud['estado'] ?? '') ?></span>
                        </p>
                        <p class="mb-2"><strong>Creada:</strong> <?= date('d/m/Y H:i', strtotime($solicitud['fecha_creacion'] ?? 'now')) ?></p>
                        <p class="mb-0"><strong>Expira:</strong> <?= date('d/m/Y H:i', strtotime($solicitud['fecha_expiracion'] ?? 'now')) ?></p>
                    </div>
                </div>

                <!-- Evidencia de firma -->
                <?php if (!empty($evidencia)): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-fingerprint me-2"></i>Evidencia Digital</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong>IP:</strong> <code><?= esc($evidencia['ip_address'] ?? '') ?></code></p>
                        <p class="mb-2"><strong>Fecha UTC:</strong> <?= esc($evidencia['fecha_hora_utc'] ?? '') ?></p>
                        <p class="mb-2"><strong>Tipo firma:</strong>
                            <?= match($evidencia['tipo_firma'] ?? '') {
                                'draw' => 'Manuscrita digital',
                                'upload' => 'Imagen subida',
                                'internal' => 'Firma interna',
                                default => $evidencia['tipo_firma'] ?? ''
                            } ?>
                        </p>
                        <?php if (!empty($evidencia['geolocalizacion'])): ?>
                            <p class="mb-2"><strong>Geolocalizacion:</strong> <code><?= esc($evidencia['geolocalizacion']) ?></code></p>
                        <?php endif; ?>
                        <p class="mb-2"><strong>Hash documento:</strong></p>
                        <code class="small d-block bg-light p-2 rounded"><?= esc($evidencia['hash_documento'] ?? '') ?></code>
                        <?php if (!empty($evidencia['user_agent'])): ?>
                            <p class="mb-1 mt-2"><strong>User Agent:</strong></p>
                            <small class="text-muted d-block"><?= esc($evidencia['user_agent']) ?></small>
                        <?php endif; ?>
                        <?php if (!empty($evidencia['firma_imagen'])): ?>
                            <div class="mt-3 pt-3 border-top text-center">
                                <small class="text-muted d-block mb-2">Imagen de firma</small>
                                <img src="<?= $evidencia['firma_imagen'] ?>" class="img-fluid border rounded" style="max-height: 80px; background: #fff;">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Documento -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Documento</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong>Codigo:</strong> <code><?= esc($documento['codigo'] ?? '') ?></code></p>
                        <p class="mb-0"><strong>Nombre:</strong> <?= esc($documento['titulo'] ?? $documento['nombre'] ?? '') ?></p>
                    </div>
                </div>
            </div>

            <!-- Log de eventos -->
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Historial de Eventos</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($auditLog)): ?>
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-journal" style="font-size: 3rem;"></i>
                                <p class="mt-2">No hay eventos registrados.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 50px;"></th>
                                            <th>Evento</th>
                                            <th>Fecha/Hora</th>
                                            <th>IP</th>
                                            <th style="width: 50px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($auditLog as $idx => $log): ?>
                                        <tr>
                                            <td class="text-center">
                                                <?php
                                                $iconConfig = match($log['evento'] ?? '') {
                                                    'solicitud_creada' => ['bg-primary', 'bi-plus-circle'],
                                                    'email_enviado', 'email_reenviado' => ['bg-info', 'bi-envelope'],
                                                    'link_accedido', 'link_abierto' => ['bg-warning', 'bi-box-arrow-in-right'],
                                                    'firma_registrada', 'firma_completada' => ['bg-success', 'bi-pen'],
                                                    'documento_firmado_completo' => ['bg-success', 'bi-patch-check'],
                                                    'solicitud_cancelada' => ['bg-danger', 'bi-x-circle'],
                                                    'token_renovado', 'token_reenviado' => ['bg-secondary', 'bi-arrow-repeat'],
                                                    default => ['bg-secondary', 'bi-circle']
                                                };
                                                ?>
                                                <span class="event-icon <?= $iconConfig[0] ?> text-white">
                                                    <i class="bi <?= $iconConfig[1] ?>"></i>
                                                </span>
                                            </td>
                                            <td>
                                                <strong><?= esc(ucfirst(str_replace('_', ' ', $log['evento'] ?? ''))) ?></strong>
                                            </td>
                                            <td>
                                                <small><?= date('d/m/Y H:i:s', strtotime($log['fecha_hora'] ?? 'now')) ?></small>
                                            </td>
                                            <td>
                                                <small><code><?= esc($log['ip_address'] ?? '-') ?></code></small>
                                            </td>
                                            <td>
                                                <?php if (!empty($log['detalles'])): ?>
                                                    <button class="btn btn-sm btn-outline-secondary" type="button"
                                                            data-bs-toggle="collapse" data-bs-target="#detalles<?= $idx ?>">
                                                        <i class="bi bi-code-slash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php if (!empty($log['detalles'])): ?>
                                        <tr class="collapse" id="detalles<?= $idx ?>">
                                            <td colspan="5" class="p-2">
                                                <div class="json-details">
                                                    <pre class="mb-0"><?php
                                                        $detalles = is_string($log['detalles']) ? json_decode($log['detalles'], true) : $log['detalles'];
                                                        echo esc(json_encode($detalles, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                                                    ?></pre>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
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
