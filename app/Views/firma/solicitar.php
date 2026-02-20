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

                        <?php if (in_array($documento['estado'], ['borrador', 'generado'])): ?>
                        <div class="alert alert-warning border-warning mb-4">
                            <h6 class="alert-heading mb-2">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                Documento en estado <?= ucfirst($documento['estado']) ?>
                            </h6>
                            <p class="mb-0 small">
                                Este documento aun no ha sido revisado ni aprobado. Si continua con el envio a firmas,
                                el firmante recibira el documento en su estado actual. Asegurese de que el contenido
                                sea el definitivo antes de solicitar firmas.
                            </p>
                        </div>
                        <?php endif; ?>

                        <p class="text-muted mb-4">
                            Se enviara una solicitud de firma electronica a los siguientes firmantes configurados en el contexto del cliente.
                        </p>

                        <!-- Formulario de envio (envuelve firmantes + boton) -->
                        <form id="formSolicitarFirmas" action="<?= base_url('firma/crear-solicitud') ?>" method="post">
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
                                        <?php $solDelegado = $estadoFirmas['delegado_sst'] ?? null; ?>
                                        <?php if (is_array($solDelegado) && in_array($solDelegado['estado'], ['pendiente', 'esperando'])): ?>
                                        <?php $urlFirmaDelegado = base_url('firma/firmar/' . $solDelegado['token']); ?>
                                        <div class="mt-2 pt-2 border-top d-flex gap-2 flex-wrap align-items-center">
                                            <button type="button" class="btn btn-sm btn-outline-info"
                                                    onclick="copiarEnlaceFirma('<?= $urlFirmaDelegado ?>', '<?= esc($contexto['delegado_sst_nombre']) ?>')">
                                                <i class="bi bi-clipboard me-1"></i>Copiar enlace
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                    onclick="reenviarSolicitud(<?= $solDelegado['id_solicitud'] ?>, '<?= esc($contexto['delegado_sst_nombre']) ?>')">
                                                <i class="bi bi-send me-1"></i>Reenviar
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-warning"
                                                    onclick="modalEmailAlternativo('<?= base_url('firma/reenviar/' . $solDelegado['id_solicitud']) ?>', '<?= esc($contexto['delegado_sst_nombre']) ?>', '<?= esc($solDelegado['firmante_email']) ?>')">
                                                <i class="bi bi-envelope-at me-1"></i>Email alt.
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                    onclick="cancelarSolicitud(<?= $solDelegado['id_solicitud'] ?>, '<?= esc($contexto['delegado_sst_nombre']) ?>')">
                                                <i class="bi bi-x-circle me-1"></i>Cancelar
                                            </button>
                                            <a href="<?= base_url('firma/audit-log/' . $solDelegado['id_solicitud']) ?>" class="btn btn-sm btn-outline-secondary" target="_blank">
                                                <i class="bi bi-clock-history me-1"></i>Audit Log
                                            </a>
                                        </div>
                                        <?php elseif (is_array($solDelegado) && $solDelegado['estado'] === 'firmado'): ?>
                                        <div class="mt-2 pt-2 border-top d-flex gap-2 flex-wrap align-items-center">
                                            <span class="badge bg-success"><i class="bi bi-check-lg me-1"></i>Firmado</span>
                                            <a href="<?= base_url('firma/audit-log/' . $solDelegado['id_solicitud']) ?>" class="btn btn-sm btn-outline-secondary" target="_blank">
                                                <i class="bi bi-clock-history me-1"></i>Audit Log
                                            </a>
                                        </div>
                                        <?php else: ?>
                                        <div class="mt-2 pt-2 border-top">
                                            <div class="d-flex gap-2 flex-wrap align-items-center mb-2">
                                                <button type="button" class="btn btn-sm btn-outline-info disabled" tabindex="-1" title="Disponible despues de enviar la solicitud">
                                                    <i class="bi bi-clipboard me-1"></i>Copiar enlace
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-primary disabled" tabindex="-1" title="Disponible despues de enviar la solicitud">
                                                    <i class="bi bi-send me-1"></i>Reenviar
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-warning"
                                                        onclick="document.getElementById('email-alt-delegado-wrap').classList.toggle('d-none');">
                                                    <i class="bi bi-envelope-at me-1"></i>Email alt.
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger disabled" tabindex="-1" title="Disponible despues de enviar la solicitud">
                                                    <i class="bi bi-x-circle me-1"></i>Cancelar
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary disabled" tabindex="-1" title="Disponible despues de enviar la solicitud">
                                                    <i class="bi bi-clock-history me-1"></i>Audit Log
                                                </button>
                                            </div>
                                            <div id="email-alt-delegado-wrap" class="d-none mb-2">
                                                <div class="input-group input-group-sm">
                                                    <input type="email" name="email_alt_delegado" class="form-control"
                                                           placeholder="correo.personal@gmail.com">
                                                </div>
                                                <small class="text-muted">La solicitud se enviara a este correo en vez del corporativo</small>
                                            </div>
                                            <div class="alert alert-light border py-2 px-3 mb-0 small">
                                                <i class="bi bi-lock-fill text-danger me-1"></i>
                                                <strong class="text-danger">Los botones deshabilitados se activaran al hacer clic en "Enviar Solicitud" al final de esta pagina.</strong>
                                            </div>
                                        </div>
                                        <?php endif; ?>
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
                                        <?php $solRepLegal = $estadoFirmas['representante_legal'] ?? null; ?>
                                        <?php if (is_array($solRepLegal) && in_array($solRepLegal['estado'], ['pendiente', 'esperando'])): ?>
                                        <?php $urlFirmaRepLegal = base_url('firma/firmar/' . $solRepLegal['token']); ?>
                                        <div class="mt-2 pt-2 border-top d-flex gap-2 flex-wrap align-items-center">
                                            <button type="button" class="btn btn-sm btn-outline-info"
                                                    onclick="copiarEnlaceFirma('<?= $urlFirmaRepLegal ?>', '<?= esc($contexto['representante_legal_nombre']) ?>')">
                                                <i class="bi bi-clipboard me-1"></i>Copiar enlace
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                    onclick="reenviarSolicitud(<?= $solRepLegal['id_solicitud'] ?>, '<?= esc($contexto['representante_legal_nombre']) ?>')">
                                                <i class="bi bi-send me-1"></i>Reenviar
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-warning"
                                                    onclick="modalEmailAlternativo('<?= base_url('firma/reenviar/' . $solRepLegal['id_solicitud']) ?>', '<?= esc($contexto['representante_legal_nombre']) ?>', '<?= esc($solRepLegal['firmante_email']) ?>')">
                                                <i class="bi bi-envelope-at me-1"></i>Email alt.
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                    onclick="cancelarSolicitud(<?= $solRepLegal['id_solicitud'] ?>, '<?= esc($contexto['representante_legal_nombre']) ?>')">
                                                <i class="bi bi-x-circle me-1"></i>Cancelar
                                            </button>
                                            <a href="<?= base_url('firma/audit-log/' . $solRepLegal['id_solicitud']) ?>" class="btn btn-sm btn-outline-secondary" target="_blank">
                                                <i class="bi bi-clock-history me-1"></i>Audit Log
                                            </a>
                                        </div>
                                        <?php elseif (is_array($solRepLegal) && $solRepLegal['estado'] === 'firmado'): ?>
                                        <div class="mt-2 pt-2 border-top d-flex gap-2 flex-wrap align-items-center">
                                            <span class="badge bg-success"><i class="bi bi-check-lg me-1"></i>Firmado</span>
                                            <a href="<?= base_url('firma/audit-log/' . $solRepLegal['id_solicitud']) ?>" class="btn btn-sm btn-outline-secondary" target="_blank">
                                                <i class="bi bi-clock-history me-1"></i>Audit Log
                                            </a>
                                        </div>
                                        <?php else: ?>
                                        <div class="mt-2 pt-2 border-top">
                                            <div class="d-flex gap-2 flex-wrap align-items-center mb-2">
                                                <button type="button" class="btn btn-sm btn-outline-info disabled" tabindex="-1" title="Disponible despues de enviar la solicitud">
                                                    <i class="bi bi-clipboard me-1"></i>Copiar enlace
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-primary disabled" tabindex="-1" title="Disponible despues de enviar la solicitud">
                                                    <i class="bi bi-send me-1"></i>Reenviar
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-warning"
                                                        onclick="document.getElementById('email-alt-replegal-wrap').classList.toggle('d-none');">
                                                    <i class="bi bi-envelope-at me-1"></i>Email alt.
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger disabled" tabindex="-1" title="Disponible despues de enviar la solicitud">
                                                    <i class="bi bi-x-circle me-1"></i>Cancelar
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary disabled" tabindex="-1" title="Disponible despues de enviar la solicitud">
                                                    <i class="bi bi-clock-history me-1"></i>Audit Log
                                                </button>
                                            </div>
                                            <div id="email-alt-replegal-wrap" class="d-none mb-2">
                                                <div class="input-group input-group-sm">
                                                    <input type="email" name="email_alt_representante" class="form-control"
                                                           placeholder="correo.personal@gmail.com">
                                                </div>
                                                <small class="text-muted">La solicitud se enviara a este correo en vez del corporativo</small>
                                            </div>
                                            <div class="alert alert-light border py-2 px-3 mb-0 small">
                                                <i class="bi bi-lock-fill text-danger me-1"></i>
                                                <strong class="text-danger">Los botones deshabilitados se activaran al hacer clic en "Enviar Solicitud" al final de esta pagina.</strong>
                                            </div>
                                        </div>
                                        <?php endif; ?>
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
                                <button type="button" class="btn btn-primary btn-lg" onclick="confirmarEnvioFirmas()">
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= base_url('js/firma-helpers.js') ?>"></script>
    <script>
    function reenviarSolicitud(idSolicitud, nombre) {
        Swal.fire({
            title: 'Reenviar Solicitud',
            html: '<p>Se reenviará la solicitud de firma a:</p><p><strong>' + nombre + '</strong></p><p class="text-muted small">Se generará un nuevo enlace. El anterior quedará invalidado.</p>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            confirmButtonText: '<i class="bi bi-send me-1"></i>Reenviar',
            cancelButtonText: 'Cancelar'
        }).then(function(result) {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Enviando...', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });
                fetch('<?= base_url("firma/reenviar/") ?>' + idSolicitud, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                }).then(function(r) { return r.json(); }).then(function(data) {
                    if (data.success) {
                        Swal.fire({ icon: 'success', title: 'Enviado', text: data.mensaje || 'Solicitud reenviada', timer: 3000 }).then(function() { location.reload(); });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: data.mensaje || 'No se pudo reenviar' });
                    }
                }).catch(function() {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión con el servidor.' });
                });
            }
        });
    }

    function confirmarEnvioFirmas() {
        var esBorrador = <?= in_array($documento['estado'], ['borrador', 'generado']) ? 'true' : 'false' ?>;

        function mostrarConfirmacionLegal() {
            Swal.fire({
                title: 'Confirmacion de Responsabilidad',
                html: '<div class="text-start">' +
                    '<p>Al enviar esta solicitud de firma electronica, tenga en cuenta:</p>' +
                    '<ul class="small">' +
                    '<li>El documento quedara <strong>bloqueado</strong> y no podra ser editado mientras las firmas esten vigentes.</li>' +
                    '<li>Editar un documento que ya fue firmado puede tener <strong>implicaciones legales</strong> y comprometer su <strong>licencia profesional</strong> como responsable del SG-SST.</li>' +
                    '<li>La firma electronica tiene plena validez legal segun la <strong>Ley 527 de 1999</strong>.</li>' +
                    '</ul>' +
                    '<p class="mb-0"><strong>¿Es consciente de los riesgos y desea continuar?</strong></p>' +
                    '</div>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                confirmButtonText: '<i class="bi bi-check-lg me-1"></i>Si, enviar a firmar',
                cancelButtonText: 'No, volver',
                focusCancel: true
            }).then(function(result) {
                if (result.isConfirmed) {
                    document.getElementById('formSolicitarFirmas').submit();
                }
            });
        }

        if (esBorrador) {
            Swal.fire({
                title: 'Documento en Borrador',
                html: '<p>Este documento se encuentra en estado <strong>Borrador</strong> y aun no ha sido revisado ni aprobado.</p>' +
                    '<p>¿Esta seguro de que desea enviarlo a firmar en su estado actual?</p>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                confirmButtonText: '<i class="bi bi-check me-1"></i>Si, continuar',
                cancelButtonText: 'No, revisar primero',
                focusCancel: true
            }).then(function(result) {
                if (result.isConfirmed) {
                    mostrarConfirmacionLegal();
                }
            });
        } else {
            mostrarConfirmacionLegal();
        }
    }

    function cancelarSolicitud(idSolicitud, nombre) {
        Swal.fire({
            title: 'Cancelar Solicitud',
            html: '<p>Se cancelará la solicitud de firma de:</p><p><strong>' + nombre + '</strong></p><p class="text-muted small">El enlace de firma quedará invalidado.</p>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: '<i class="bi bi-x-circle me-1"></i>Sí, cancelar',
            cancelButtonText: 'No, mantener'
        }).then(function(result) {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Cancelando...', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });
                fetch('<?= base_url("firma/cancelar/") ?>' + idSolicitud, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                }).then(function(r) { return r.json(); }).then(function(data) {
                    if (data.success) {
                        Swal.fire({ icon: 'success', title: 'Cancelada', text: data.mensaje || 'Solicitud cancelada', timer: 3000 }).then(function() { location.reload(); });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: data.mensaje || 'No se pudo cancelar' });
                    }
                }).catch(function() {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión con el servidor.' });
                });
            }
        });
    }
    </script>
</body>
</html>
