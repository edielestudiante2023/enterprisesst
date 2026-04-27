<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud Marcar Ausente - Acta <?= esc($acta['numero_acta']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            min-height: 100vh;
        }
        .container-aprobacion {
            max-width: 700px;
            margin: 0 auto;
            padding: 20px;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .card-header {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }
        .info-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .asistente-box {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .justificacion-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container-aprobacion py-4">
        <div class="text-center mb-4">
            <h4 class="text-white"><i class="bi bi-person-x me-2"></i>Marcar Asistente como Ausente</h4>
        </div>

        <div class="card">
            <div class="card-header text-center">
                <h5 class="mb-1">Solicitud de Marcar Ausente</h5>
                <p class="mb-0 opacity-75">Revise la solicitud y decida si aprobar o rechazar</p>
            </div>
            <div class="card-body p-4">
                <!-- Info del acta -->
                <div class="info-box">
                    <h6 class="mb-3"><i class="bi bi-file-text me-2"></i>Datos del Acta</h6>
                    <div class="row">
                        <div class="col-sm-6">
                            <p class="mb-1"><strong>Acta N°:</strong> <?= esc($acta['numero_acta']) ?></p>
                            <p class="mb-1"><strong>Comite:</strong> <?= esc($comite['tipo_nombre'] ?? 'Comite') ?></p>
                        </div>
                        <div class="col-sm-6">
                            <p class="mb-1"><strong>Fecha reunion:</strong> <?= date('d/m/Y', strtotime($acta['fecha_reunion'])) ?></p>
                            <p class="mb-1"><strong>Estado actual:</strong>
                                <span class="badge bg-secondary"><?= ucfirst(str_replace('_', ' ', $acta['estado'])) ?></span>
                            </p>
                        </div>
                    </div>
                    <p class="mb-0 mt-1"><strong>Cliente:</strong> <?= esc($cliente['nombre_cliente']) ?></p>
                </div>

                <!-- Persona a marcar ausente -->
                <div class="asistente-box">
                    <h6 class="mb-2"><i class="bi bi-person-x me-2"></i>Persona a marcar como ausente</h6>
                    <p class="mb-1" style="font-size:18px;"><strong><?= esc($asistente['nombre_completo']) ?></strong></p>
                    <?php if (!empty($asistente['cargo'])): ?>
                    <p class="mb-1 text-muted"><?= esc($asistente['cargo']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($asistente['email'])): ?>
                    <p class="mb-0 text-muted"><i class="bi bi-envelope me-1"></i><?= esc($asistente['email']) ?></p>
                    <?php endif; ?>
                </div>

                <!-- Info del solicitante -->
                <div class="info-box">
                    <h6 class="mb-3"><i class="bi bi-person me-2"></i>Solicitado por</h6>
                    <p class="mb-1"><strong>Nombre:</strong> <?= esc($solicitud['solicitante_nombre']) ?></p>
                    <p class="mb-1"><strong>Email:</strong> <?= esc($solicitud['solicitante_email']) ?></p>
                    <?php if (!empty($solicitud['solicitante_cargo'])): ?>
                    <p class="mb-1"><strong>Cargo:</strong> <?= esc($solicitud['solicitante_cargo']) ?></p>
                    <?php endif; ?>
                    <p class="mb-0"><strong>Fecha solicitud:</strong> <?= date('d/m/Y H:i', strtotime($solicitud['created_at'])) ?></p>
                </div>

                <!-- Justificación -->
                <div class="justificacion-box">
                    <h6 class="mb-2"><i class="bi bi-chat-quote me-2"></i>Justificacion</h6>
                    <p class="mb-0 fst-italic">"<?= esc($solicitud['justificacion']) ?>"</p>
                </div>

                <!-- Info -->
                <div class="alert alert-info small">
                    <i class="bi bi-info-circle-fill me-1"></i>
                    Si aprueba, esta persona quedara como ausente y dejara de contar en el indicador de firmas pendientes. Las firmas existentes de otros asistentes <strong>NO se afectan</strong>.
                </div>

                <!-- Formulario de acción -->
                <form action="<?= base_url('acta/aprobar-marcar-ausente/' . $token) ?>" method="post" id="formMarcarAusente">
                    <div id="divMotivoRechazo" class="mb-3" style="display: none;">
                        <label class="form-label fw-bold">Motivo del rechazo</label>
                        <textarea name="motivo_rechazo" class="form-control" rows="3" placeholder="Indique el motivo por el cual rechaza la solicitud..."></textarea>
                    </div>

                    <input type="hidden" name="accion" id="inputAccion" value="">

                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <button type="button" class="btn btn-success btn-lg px-4" onclick="confirmarAccion('aprobar')">
                            <i class="bi bi-check-circle me-1"></i> Aprobar
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-lg px-4" onclick="mostrarRechazo()">
                            <i class="bi bi-x-circle me-1"></i> Rechazar
                        </button>
                    </div>

                    <div id="divConfirmarRechazo" class="text-center mt-3" style="display: none;">
                        <button type="button" class="btn btn-danger" onclick="confirmarAccion('rechazar')">
                            Confirmar Rechazo
                        </button>
                        <button type="button" class="btn btn-secondary ms-2" onclick="cancelarRechazo()">
                            Cancelar
                        </button>
                    </div>
                </form>

                <div class="text-center mt-3">
                    <small class="text-muted">
                        <i class="bi bi-clock me-1"></i>
                        Este enlace expira el <?= date('d/m/Y H:i', strtotime($solicitud['token_expira'])) ?>
                    </small>
                </div>
            </div>
        </div>

        <div class="text-center mt-3">
            <small class="text-white-50">EnterpriseSST - Sistema de Gestion de Seguridad y Salud en el Trabajo</small>
        </div>
    </div>

    <script>
    function mostrarRechazo() {
        document.getElementById('divMotivoRechazo').style.display = 'block';
        document.getElementById('divConfirmarRechazo').style.display = 'block';
    }

    function cancelarRechazo() {
        document.getElementById('divMotivoRechazo').style.display = 'none';
        document.getElementById('divConfirmarRechazo').style.display = 'none';
    }

    function confirmarAccion(accion) {
        const mensaje = accion === 'aprobar'
            ? 'Esta seguro de APROBAR? La persona quedara como ausente.'
            : 'Esta seguro de RECHAZAR la solicitud?';

        if (confirm(mensaje)) {
            document.getElementById('inputAccion').value = accion;
            document.getElementById('formMarcarAusente').submit();
        }
    }
    </script>
</body>
</html>
