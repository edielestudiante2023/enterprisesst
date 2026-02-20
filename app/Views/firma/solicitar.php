<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Firma - <?= esc($documento['codigo']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .firma-step {
            position: relative;
            padding-left: 50px;
            padding-bottom: 30px;
        }
        .firma-step:last-child {
            padding-bottom: 0;
        }
        .firma-step::before {
            content: '';
            position: absolute;
            left: 18px;
            top: 35px;
            bottom: 0;
            width: 2px;
            background: #dee2e6;
        }
        .firma-step:last-child::before {
            display: none;
        }
        .firma-step-number {
            position: absolute;
            left: 0;
            top: 0;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #3B82F6;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        .firma-step.delegado .firma-step-number {
            background: #F59E0B;
        }
        .firma-step.rep-legal .firma-step-number {
            background: #10B981;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-pen me-2"></i>Solicitar Firmas
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="<?= base_url('documentos-sst/' . ($documento['id_cliente'] ?? '') . '/' . str_replace('_', '-', $documento['tipo_documento'] ?? 'programa-capacitacion') . '/' . ($documento['anio'] ?? date('Y'))) ?>">
                    <i class="bi bi-arrow-left me-1"></i>Volver al documento
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle me-2"></i><?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Información del documento -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Documento</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong>Codigo:</strong> <code><?= esc($documento['codigo']) ?></code></p>
                        <p class="mb-2"><strong>Nombre:</strong> <?= esc($documento['titulo'] ?? $documento['nombre'] ?? '') ?></p>
                        <p class="mb-2"><strong>Version:</strong> <?= $documento['version'] ?? $documento['version_actual'] ?? '1' ?></p>
                        <p class="mb-0"><strong>Estado:</strong>
                            <?php
                            $estadoColor = match($documento['estado']) {
                                'completado' => 'success',
                                'en_revision' => 'warning',
                                'pendiente_firma' => 'info',
                                default => 'secondary'
                            };
                            ?>
                            <span class="badge bg-<?= $estadoColor ?>"><?= ucfirst(str_replace('_', ' ', $documento['estado'])) ?></span>
                        </p>
                    </div>
                </div>

                <!-- Flujo de firmas configurado -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>Flujo de Firmas</h6>
                    </div>
                    <div class="card-body">
                        <?php if ($requiereDelegado): ?>
                            <div class="text-center mb-3">
                                <span class="badge bg-warning text-dark d-block p-2 mb-2">
                                    <i class="bi bi-1-circle me-1"></i>Delegado SST
                                </span>
                                <i class="bi bi-arrow-down"></i>
                                <span class="badge bg-success d-block p-2 mt-2">
                                    <i class="bi bi-2-circle me-1"></i>Representante Legal
                                </span>
                            </div>
                            <small class="text-muted d-block text-center">Firma secuencial</small>
                        <?php else: ?>
                            <div class="text-center">
                                <span class="badge bg-success d-block p-2">
                                    <i class="bi bi-check-circle me-1"></i>Representante Legal
                                </span>
                                <small class="text-muted d-block mt-2">Firma unica</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Estado actual de firmas -->
                <?php if (!empty($estadoFirmas) && is_array($estadoFirmas)): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-check2-all me-2"></i>Estado Actual</h6>
                    </div>
                    <div class="card-body">
                        <?php foreach ($estadoFirmas as $key => $firma): ?>
                            <?php if (is_array($firma)): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span><?= ucfirst(str_replace('_', ' ', $firma['firmante_tipo'])) ?></span>
                                    <?php
                                        $badgeClass = match($firma['estado']) {
                                            'firmado' => 'bg-success',
                                            'pendiente' => 'bg-warning',
                                            'esperando' => 'bg-secondary',
                                            'expirado' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= ucfirst($firma['estado']) ?></span>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Panel principal -->
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-send me-2"></i>Enviar Solicitud de Firmas
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Verificar que hay datos de firmantes -->
                        <?php
                        $datosFirmantesCompletos = !empty($contexto['representante_legal_email']) && !empty($contexto['representante_legal_nombre']);
                        if ($requiereDelegado) {
                            $datosFirmantesCompletos = $datosFirmantesCompletos && !empty($contexto['delegado_sst_email']) && !empty($contexto['delegado_sst_nombre']);
                        }
                        ?>

                        <?php if (!$datosFirmantesCompletos): ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Datos de firmantes incompletos</strong>
                                <p class="mb-2">Debe configurar los datos de los firmantes en el contexto del cliente antes de solicitar firmas.</p>
                                <a href="<?= base_url('contexto/' . $documento['id_cliente']) ?>" class="btn btn-warning btn-sm">
                                    <i class="bi bi-gear me-1"></i>Configurar Firmantes
                                </a>
                            </div>
                        <?php else: ?>

                        <p class="text-muted mb-4">
                            Se enviara una solicitud de firma electronica a los siguientes firmantes configurados en el contexto del cliente.
                        </p>

                        <!-- Formulario de envio (envuelve firmantes + boton) -->
                        <form action="<?= base_url('firma/crear-solicitud') ?>" method="post">
                            <input type="hidden" name="id_documento" value="<?= $documento['id_documento'] ?>">

                        <!-- Resumen de firmantes -->
                        <div class="mb-4">
                            <?php if ($requiereDelegado): ?>
                            <!-- Delegado SST -->
                            <div class="firma-step delegado">
                                <div class="firma-step-number">1</div>
                                <div class="card border-warning">
                                    <div class="card-body">
                                        <h6 class="mb-2">
                                            <i class="bi bi-person-badge text-warning me-2"></i>
                                            Delegado SST
                                            <span class="badge bg-warning text-dark float-end">Primera Firma</span>
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <small class="text-muted">Nombre:</small>
                                                <p class="mb-1 fw-bold"><?= esc($contexto['delegado_sst_nombre']) ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted">Cargo:</small>
                                                <p class="mb-1"><?= esc($contexto['delegado_sst_cargo']) ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted">Email:</small>
                                                <p class="mb-1"><code><?= esc($contexto['delegado_sst_email']) ?></code></p>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted">Cedula:</small>
                                                <p class="mb-0"><?= esc($contexto['delegado_sst_cedula']) ?></p>
                                            </div>
                                        </div>
                                        <div class="mt-2 pt-2 border-top">
                                            <a href="#" class="small text-decoration-none" onclick="event.preventDefault(); document.getElementById('email-alt-delegado-wrap').classList.toggle('d-none');">
                                                <i class="bi bi-envelope-at me-1"></i>Usar email alternativo
                                            </a>
                                            <div id="email-alt-delegado-wrap" class="d-none mt-2">
                                                <input type="email" name="email_alt_delegado" class="form-control form-control-sm"
                                                       placeholder="correo.personal@gmail.com">
                                                <small class="text-muted">Si el email corporativo no recibe correos, use uno alternativo</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Representante Legal -->
                            <div class="firma-step rep-legal">
                                <div class="firma-step-number"><?= $requiereDelegado ? '2' : '1' ?></div>
                                <div class="card border-success">
                                    <div class="card-body">
                                        <h6 class="mb-2">
                                            <i class="bi bi-person-check text-success me-2"></i>
                                            Representante Legal
                                            <span class="badge bg-success float-end"><?= $requiereDelegado ? 'Firma Final' : 'Firma Unica' ?></span>
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <small class="text-muted">Nombre:</small>
                                                <p class="mb-1 fw-bold"><?= esc($contexto['representante_legal_nombre']) ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted">Cargo:</small>
                                                <p class="mb-1"><?= esc($contexto['representante_legal_cargo'] ?? 'Representante Legal') ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted">Email:</small>
                                                <p class="mb-1"><code><?= esc($contexto['representante_legal_email']) ?></code></p>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted">Cedula:</small>
                                                <p class="mb-0"><?= esc($contexto['representante_legal_cedula']) ?></p>
                                            </div>
                                        </div>
                                        <div class="mt-2 pt-2 border-top">
                                            <a href="#" class="small text-decoration-none" onclick="event.preventDefault(); document.getElementById('email-alt-replegal-wrap').classList.toggle('d-none');">
                                                <i class="bi bi-envelope-at me-1"></i>Usar email alternativo
                                            </a>
                                            <div id="email-alt-replegal-wrap" class="d-none mt-2">
                                                <input type="email" name="email_alt_representante" class="form-control form-control-sm"
                                                       placeholder="correo.personal@gmail.com">
                                                <small class="text-muted">Si el email corporativo no recibe correos, use uno alternativo</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Informacion legal -->
                        <div class="alert alert-info mb-4">
                            <h6 class="alert-heading"><i class="bi bi-info-circle me-2"></i>Firma Electronica - Ley 527 de 1999</h6>
                            <p class="mb-2 small">
                                La solicitud de firma enviara un correo electronico con un enlace unico que permite al firmante:
                            </p>
                            <ul class="mb-0 small">
                                <li>Revisar el contenido completo del documento</li>
                                <li>Registrar su firma electronica (manuscrita digital o tipografica)</li>
                                <li>Verificar su identidad mediante numero de cedula</li>
                                <li>Aceptar los terminos de firma electronica</li>
                            </ul>
                            <hr>
                            <p class="mb-0 small">
                                <strong>Evidencia registrada:</strong> IP, fecha/hora UTC, geolocalizacion (opcional), user agent, hash del documento.
                            </p>
                        </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <a href="<?= base_url('documentos-sst/' . ($documento['id_cliente'] ?? '') . '/' . str_replace('_', '-', $documento['tipo_documento'] ?? 'programa-capacitacion') . '/' . ($documento['anio'] ?? date('Y'))) ?>" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-lg me-1"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-send me-2"></i>
                                    Enviar Solicitud<?= $requiereDelegado ? ' a Delegado SST' : '' ?>
                                </button>
                            </div>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Nota sobre cambios -->
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body">
                        <h6><i class="bi bi-gear me-2"></i>Cambiar configuracion de firmantes</h6>
                        <p class="text-muted small mb-2">
                            Si necesita modificar los datos de los firmantes o el flujo de firmas (activar/desactivar Delegado SST),
                            puede hacerlo desde el contexto del cliente.
                        </p>
                        <a href="<?= base_url('contexto/' . $documento['id_cliente']) ?>" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-pencil me-1"></i>Editar Contexto del Cliente
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
