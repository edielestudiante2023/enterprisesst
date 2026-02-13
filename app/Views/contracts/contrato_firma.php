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
                            <label class="form-label">Firma Digital *</label>

                            <!-- Tabs: Dibujar / Subir imagen -->
                            <ul class="nav nav-tabs nav-fill mb-2" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="tab-dibujar" data-bs-toggle="tab" data-bs-target="#panelDibujar" type="button" role="tab">
                                        <i class="bi bi-pencil me-1"></i>Dibujar firma
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="tab-subir" data-bs-toggle="tab" data-bs-target="#panelSubir" type="button" role="tab">
                                        <i class="bi bi-upload me-1"></i>Subir imagen
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content">
                                <!-- Panel Dibujar -->
                                <div class="tab-pane fade show active" id="panelDibujar" role="tabpanel">
                                    <canvas id="canvasFirma" class="firma-canvas w-100" height="150"></canvas>
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnLimpiarFirma">
                                            <i class="bi bi-eraser me-1"></i>Limpiar
                                        </button>
                                    </div>
                                </div>

                                <!-- Panel Subir -->
                                <div class="tab-pane fade" id="panelSubir" role="tabpanel">
                                    <div class="border rounded p-3 text-center" style="background: #fafafa; min-height: 150px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                        <div id="previewSubida" style="display:none;" class="mb-2">
                                            <img id="imgPreview" src="" alt="Vista previa" style="max-height: 120px; max-width: 100%;">
                                        </div>
                                        <div id="placeholderSubida">
                                            <i class="bi bi-image text-muted" style="font-size: 2rem;"></i>
                                            <p class="text-muted small mb-2">Suba una imagen de su firma (PNG, JPG)</p>
                                        </div>
                                        <input type="file" id="inputFirmaArchivo" accept="image/png,image/jpeg,image/jpg" class="form-control form-control-sm" style="max-width: 300px;">
                                        <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="btnLimpiarSubida" style="display:none;">
                                            <i class="bi bi-x-circle me-1"></i>Quitar imagen
                                        </button>
                                    </div>
                                </div>
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
        // === CANVAS DE FIRMA ===
        const canvas = document.getElementById('canvasFirma');
        const ctx = canvas.getContext('2d');
        let dibujando = false;
        let tieneContenidoCanvas = false;

        canvas.width = canvas.offsetWidth;
        canvas.height = 150;

        ctx.strokeStyle = '#000';
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';

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
            tieneContenidoCanvas = true;
            e.preventDefault();
        }

        function terminarDibujo() {
            dibujando = false;
        }

        canvas.addEventListener('mousedown', iniciarDibujo);
        canvas.addEventListener('mousemove', dibujar);
        canvas.addEventListener('mouseup', terminarDibujo);
        canvas.addEventListener('mouseleave', terminarDibujo);
        canvas.addEventListener('touchstart', iniciarDibujo);
        canvas.addEventListener('touchmove', dibujar);
        canvas.addEventListener('touchend', terminarDibujo);

        document.getElementById('btnLimpiarFirma').addEventListener('click', function() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            tieneContenidoCanvas = false;
        });

        // === SUBIR IMAGEN DE FIRMA ===
        let imagenSubidaBase64 = null;
        const inputArchivo = document.getElementById('inputFirmaArchivo');
        const imgPreview = document.getElementById('imgPreview');
        const previewSubida = document.getElementById('previewSubida');
        const placeholderSubida = document.getElementById('placeholderSubida');
        const btnLimpiarSubida = document.getElementById('btnLimpiarSubida');

        inputArchivo.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            if (!file.type.match('image/(png|jpeg|jpg)')) {
                alert('Solo se permiten imagenes PNG o JPG');
                this.value = '';
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                alert('La imagen no debe superar 2MB');
                this.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(ev) {
                imagenSubidaBase64 = ev.target.result;
                imgPreview.src = imagenSubidaBase64;
                previewSubida.style.display = 'block';
                placeholderSubida.style.display = 'none';
                btnLimpiarSubida.style.display = 'inline-block';
            };
            reader.readAsDataURL(file);
        });

        btnLimpiarSubida.addEventListener('click', function() {
            imagenSubidaBase64 = null;
            inputArchivo.value = '';
            imgPreview.src = '';
            previewSubida.style.display = 'none';
            placeholderSubida.style.display = 'block';
            btnLimpiarSubida.style.display = 'none';
        });

        // === DETECTAR TAB ACTIVO ===
        function getMetodoActivo() {
            return document.getElementById('tab-subir').classList.contains('active') ? 'subir' : 'dibujar';
        }

        function obtenerFirmaImagen() {
            if (getMetodoActivo() === 'subir') {
                return imagenSubidaBase64;
            } else {
                return tieneContenidoCanvas ? canvas.toDataURL('image/png') : null;
            }
        }

        // === FIRMAR CONTRATO ===
        document.getElementById('btnFirmarContrato').addEventListener('click', function() {
            const nombre = document.getElementById('firmaNombre').value.trim();
            const cedula = document.getElementById('firmaCedula').value.trim();

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

            const firmaImagen = obtenerFirmaImagen();
            if (!firmaImagen) {
                const metodo = getMetodoActivo();
                alert(metodo === 'subir' ? 'Debe subir una imagen de su firma' : 'Debe dibujar su firma en el recuadro');
                return;
            }

            if (!confirm('¿Esta seguro de firmar este contrato?\n\nEsta accion no se puede deshacer.')) {
                return;
            }

            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';

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
