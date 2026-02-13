<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firma de Contrato SST - <?= esc($contrato['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .firma-container {
            max-width: 900px;
            margin: 0 auto;
        }
        .card-contrato {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .header-contrato {
            background: linear-gradient(135deg, #1a5f7a 0%, #2c3e50 100%);
            color: white;
            border-radius: 12px 12px 0 0;
            padding: 25px;
        }
        .firma-canvas {
            border: 2px dashed #ccc;
            border-radius: 8px;
            background: #fafafa;
            cursor: crosshair;
        }
        .firma-canvas:hover {
            border-color: #1a5f7a;
        }
        .btn-firmar {
            background: linear-gradient(135deg, #28a745 0%, #218838 100%);
            border: none;
            padding: 12px 40px;
            font-size: 1.1rem;
        }
        .info-legal {
            font-size: 0.75rem;
            color: #666;
        }
        .parte-card {
            border-left: 4px solid #667eea;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 0 8px 8px 0;
            margin-bottom: 15px;
        }
        .parte-card.contratista {
            border-left-color: #764ba2;
        }
        .detalle-label {
            font-size: 0.8rem;
            color: #888;
            margin-bottom: 2px;
        }
        .detalle-valor {
            font-weight: 600;
            color: #333;
        }
    </style>
</head>
<body class="py-4">
    <div class="container firma-container">
        <!-- Logo y titulo -->
        <div class="text-center text-white mb-4">
            <h2><i class="bi bi-pen me-2"></i>Firma de Contrato de Prestacion de Servicios SST</h2>
            <p class="opacity-75"><?= esc($contrato['nombre_cliente']) ?></p>
        </div>

        <div class="card card-contrato">
            <!-- Header -->
            <div class="header-contrato">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="mb-1"><i class="bi bi-file-earmark-text me-2"></i>Contrato <?= esc($contrato['numero_contrato']) ?></h4>
                        <p class="mb-0 opacity-75">Contrato de Prestacion de Servicios de Seguridad y Salud en el Trabajo</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <span class="badge bg-warning text-dark fs-6">Pendiente de Firma</span>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <!-- Partes del contrato -->
                <h5 class="mb-3"><i class="bi bi-people me-2"></i>Partes del Contrato</h5>

                <div class="parte-card">
                    <div class="detalle-label">EL CONTRATANTE</div>
                    <div class="detalle-valor"><?= esc($contrato['nombre_cliente']) ?></div>
                    <div class="text-muted small">NIT: <?= esc($contrato['nit_cliente']) ?></div>
                    <div class="text-muted small">Representante Legal: <?= esc($contrato['nombre_rep_legal_cliente']) ?></div>
                    <div class="text-muted small">C.C. <?= esc($contrato['cedula_rep_legal_cliente']) ?></div>
                </div>

                <div class="parte-card contratista">
                    <div class="detalle-label">EL CONTRATISTA</div>
                    <div class="detalle-valor">CYCLOID TALENT S.A.S.</div>
                    <div class="text-muted small">Representante Legal: <?= esc($contrato['nombre_rep_legal_contratista']) ?></div>
                    <div class="text-muted small">C.C. <?= esc($contrato['cedula_rep_legal_contratista']) ?></div>
                </div>

                <hr>

                <!-- Detalles del contrato -->
                <h5 class="mb-3"><i class="bi bi-info-circle me-2"></i>Detalles del Contrato</h5>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="detalle-label">Fecha de Inicio</div>
                        <div class="detalle-valor"><?= date('d/m/Y', strtotime($contrato['fecha_inicio'])) ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="detalle-label">Fecha de Finalizacion</div>
                        <div class="detalle-valor"><?= date('d/m/Y', strtotime($contrato['fecha_fin'])) ?></div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="detalle-label">Valor del Contrato</div>
                        <div class="detalle-valor">$<?= number_format($contrato['valor_contrato'], 0, ',', '.') ?> COP</div>
                    </div>
                    <div class="col-md-6">
                        <div class="detalle-label">Frecuencia de Visitas</div>
                        <div class="detalle-valor"><?= esc($contrato['frecuencia_visitas'] ?? 'No definida') ?></div>
                    </div>
                </div>

                <?php if (!empty($contrato['clausula_primera_objeto'])): ?>
                <div class="mb-3">
                    <div class="detalle-label">Objeto del Contrato (Clausula Primera)</div>
                    <div class="bg-light rounded p-3 small" style="max-height: 200px; overflow-y: auto;">
                        <?= nl2br(esc(mb_substr($contrato['clausula_primera_objeto'], 0, 1000))) ?>
                        <?php if (mb_strlen($contrato['clausula_primera_objeto']) > 1000): ?>
                            <span class="text-muted">... (ver documento completo)</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($contrato['nombre_responsable_sgsst'])): ?>
                <div class="mb-3">
                    <div class="detalle-label">Responsable SG-SST Asignado</div>
                    <div class="detalle-valor"><?= esc($contrato['nombre_responsable_sgsst']) ?></div>
                    <?php if (!empty($contrato['licencia_responsable_sgsst'])): ?>
                        <div class="text-muted small">Licencia SST: <?= esc($contrato['licencia_responsable_sgsst']) ?></div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <hr>

                <!-- Seccion de firma -->
                <h5 class="mb-3"><i class="bi bi-pen me-2"></i>Firma del Representante Legal</h5>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Nombre Completo *</label>
                            <input type="text" class="form-control" id="firmaNombre"
                                   value="<?= esc($contrato['nombre_rep_legal_cliente'] ?? '') ?>"
                                   placeholder="Nombre del firmante" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Numero de Cedula *</label>
                            <input type="text" class="form-control" id="firmaCedula"
                                   value="<?= esc($contrato['cedula_rep_legal_cliente'] ?? '') ?>"
                                   placeholder="Cedula de ciudadania" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Firma Digital * <small class="text-muted">(dibuje su firma)</small></label>
                            <canvas id="canvasFirma" class="firma-canvas w-100" height="150"></canvas>
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnLimpiarFirma">
                                    <i class="bi bi-eraser me-1"></i>Limpiar
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="bg-light rounded p-4 h-100">
                            <h6 class="text-muted mb-3">Al firmar este contrato, usted declara:</h6>
                            <ul class="info-legal">
                                <li class="mb-2">He revisado el contrato de prestacion de servicios de Seguridad y Salud en el Trabajo N° <?= esc($contrato['numero_contrato']) ?>.</li>
                                <li class="mb-2">Acepto los terminos y condiciones establecidos en el contrato, incluyendo el valor de <strong>$<?= number_format($contrato['valor_contrato'], 0, ',', '.') ?> COP</strong>.</li>
                                <li class="mb-2">Autorizo la ejecucion del contrato desde el <?= date('d/m/Y', strtotime($contrato['fecha_inicio'])) ?> hasta el <?= date('d/m/Y', strtotime($contrato['fecha_fin'])) ?>.</li>
                                <li>Esta firma digital tiene la misma validez que una firma manuscrita segun la Ley 527 de 1999.</li>
                            </ul>

                            <div class="alert alert-info mt-3 mb-0">
                                <small>
                                    <i class="bi bi-shield-check me-1"></i>
                                    Su firma sera registrada junto con la fecha, hora e IP de origen para garantizar la autenticidad del documento.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Boton de firma -->
                <div class="text-center">
                    <button type="button" class="btn btn-firmar btn-lg text-white" id="btnFirmarContrato">
                        <i class="bi bi-pen me-2"></i>Aprobar y Firmar Contrato
                    </button>
                </div>
            </div>

            <!-- Footer -->
            <div class="card-footer text-center text-muted">
                <small>Documento generado por Enterprise SST - Sistema de Gestion de Seguridad y Salud en el Trabajo</small>
            </div>
        </div>
    </div>

    <!-- Modal de exito -->
    <div class="modal fade" id="modalExito" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-5">
                    <div class="text-success mb-3">
                        <i class="bi bi-check-circle" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="text-success">Contrato Firmado Exitosamente</h4>
                    <p class="text-muted">El contrato ha sido firmado y aprobado correctamente.</p>
                    <p class="text-muted small">Puede cerrar esta ventana. El equipo de Cycloid Talent sera notificado de su firma.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Canvas de firma
        const canvas = document.getElementById('canvasFirma');
        const ctx = canvas.getContext('2d');
        let dibujando = false;
        let tieneContenido = false;

        // Ajustar tamano real del canvas
        canvas.width = canvas.offsetWidth;
        canvas.height = 150;

        // Configurar estilo de dibujo
        ctx.strokeStyle = '#000';
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';

        // Funciones de dibujo
        function getPos(e) {
            const rect = canvas.getBoundingClientRect();
            const x = (e.clientX || e.touches[0].clientX) - rect.left;
            const y = (e.clientY || e.touches[0].clientY) - rect.top;
            return { x, y };
        }

        function iniciarDibujo(e) {
            dibujando = true;
            const pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
            e.preventDefault();
        }

        function dibujar(e) {
            if (!dibujando) return;
            const pos = getPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
            tieneContenido = true;
            e.preventDefault();
        }

        function terminarDibujo() {
            dibujando = false;
        }

        // Eventos mouse
        canvas.addEventListener('mousedown', iniciarDibujo);
        canvas.addEventListener('mousemove', dibujar);
        canvas.addEventListener('mouseup', terminarDibujo);
        canvas.addEventListener('mouseleave', terminarDibujo);

        // Eventos touch
        canvas.addEventListener('touchstart', iniciarDibujo);
        canvas.addEventListener('touchmove', dibujar);
        canvas.addEventListener('touchend', terminarDibujo);

        // Limpiar firma
        document.getElementById('btnLimpiarFirma').addEventListener('click', function() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            tieneContenido = false;
        });

        // Firmar contrato
        document.getElementById('btnFirmarContrato').addEventListener('click', function() {
            const nombre = document.getElementById('firmaNombre').value.trim();
            const cedula = document.getElementById('firmaCedula').value.trim();

            // Validaciones
            if (!nombre) {
                alert('Debe ingresar su nombre completo');
                document.getElementById('firmaNombre').focus();
                return;
            }

            if (!cedula) {
                alert('Debe ingresar su numero de cedula');
                document.getElementById('firmaCedula').focus();
                return;
            }

            if (!tieneContenido) {
                alert('Debe dibujar su firma en el recuadro');
                return;
            }

            // Confirmar
            if (!confirm('¿Esta seguro de firmar este contrato?\n\nEsta accion no se puede deshacer.')) {
                return;
            }

            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';

            // Obtener imagen del canvas
            const firmaImagen = canvas.toDataURL('image/png');

            const data = new FormData();
            data.append('token', '<?= $token ?>');
            data.append('firma_nombre', nombre);
            data.append('firma_cedula', cedula);
            data.append('firma_imagen', firmaImagen);

            fetch('<?= base_url("contrato/procesar-firma") ?>', {
                method: 'POST',
                body: data
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    // Mostrar modal de exito
                    const modal = new bootstrap.Modal(document.getElementById('modalExito'));
                    modal.show();
                } else {
                    alert('Error: ' + (result.message || 'No se pudo procesar la firma'));
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-pen me-2"></i>Aprobar y Firmar Contrato';
                }
            })
            .catch(error => {
                alert('Error de conexion. Intente nuevamente.');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-pen me-2"></i>Aprobar y Firmar Contrato';
            });
        });
    });
    </script>
</body>
</html>
