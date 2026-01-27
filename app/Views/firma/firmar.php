<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firmar Documento - <?= esc($documento['codigo'] ?? '') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .firma-canvas {
            border: 2px dashed #D1D5DB;
            border-radius: 8px;
            cursor: crosshair;
            touch-action: none;
            background: #fff;
        }
        .firma-canvas.drawing {
            border-color: #3B82F6;
        }
        .upload-zone {
            border: 2px dashed #D1D5DB;
            border-radius: 8px;
            padding: 40px 20px;
            text-align: center;
            transition: all 0.3s;
            background: #fff;
        }
        .upload-zone:hover, .upload-zone.dragover {
            border-color: #3B82F6;
            background: #EFF6FF;
        }
        .nav-tabs .nav-link.active {
            font-weight: 600;
        }
        .acepto-terminos-box {
            border: 2px solid #0d6efd;
            border-radius: 8px;
            padding: 14px 18px;
            background: #f0f4ff;
            transition: all 0.3s;
        }
        .acepto-terminos-box:has(input:checked) {
            border-color: #198754;
            background: #d1e7dd;
        }
        .acepto-terminos-box .form-check-input {
            width: 1.3em;
            height: 1.3em;
            border: 2px solid #0d6efd;
            margin-top: 0.15em;
        }
        .acepto-terminos-box .form-check-input:checked {
            background-color: #198754;
            border-color: #198754;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark" style="background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);">
        <div class="container">
            <span class="navbar-brand">
                <i class="bi bi-pen me-2"></i>Firma Electronica
            </span>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <!-- Info del documento -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Documento a Firmar</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Codigo:</strong> <?= esc($documento['codigo'] ?? '') ?></p>
                                <p class="mb-1"><strong>Nombre:</strong> <?= esc($documento['titulo'] ?? $documento['nombre'] ?? '') ?></p>
                                <p class="mb-0"><strong>Version:</strong> <?= $documento['version'] ?? '1' ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Firmante:</strong> <?= esc($solicitud['firmante_nombre']) ?></p>
                                <p class="mb-1"><strong>Cargo:</strong> <?= esc($solicitud['firmante_cargo'] ?? '') ?></p>
                                <p class="mb-0"><strong>Tipo:</strong>
                                    <?php
                                    echo match($solicitud['firmante_tipo']) {
                                        'delegado_sst' => 'Delegado SST',
                                        'representante_legal' => 'Representante Legal',
                                        'elaboro' => 'Elaboro',
                                        'reviso' => 'Reviso',
                                        default => ucfirst($solicitud['firmante_tipo'])
                                    };
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulario de firma -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Registrar Firma</h5>
                    </div>
                    <div class="card-body">
                        <form id="formFirma" enctype="multipart/form-data">
                            <input type="hidden" name="token" value="<?= esc($token) ?>">
                            <input type="hidden" name="firma_imagen" id="firmaImagen">
                            <input type="hidden" name="geolocalizacion" id="geolocalizacion">
                            <input type="hidden" name="tipo_firma" id="tipoFirma" value="draw">

                            <!-- Tabs de tipo de firma -->
                            <ul class="nav nav-tabs mb-3" id="tipoFirmaTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="tab-dibujar" data-bs-toggle="tab" data-bs-target="#tabDibujar" type="button" role="tab">
                                        <i class="bi bi-brush me-1"></i>Dibujar firma
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="tab-subir" data-bs-toggle="tab" data-bs-target="#tabSubir" type="button" role="tab">
                                        <i class="bi bi-upload me-1"></i>Subir imagen
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content mb-4" id="tipoFirmaTabContent">
                                <!-- Tab: Dibujar firma -->
                                <div class="tab-pane fade show active" id="tabDibujar" role="tabpanel">
                                    <label class="form-label text-muted small">Dibuje su firma en el recuadro</label>
                                    <canvas id="firmaCanvas" class="firma-canvas w-100" height="200"></canvas>
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="limpiarFirma()">
                                            <i class="bi bi-eraser me-1"></i>Limpiar
                                        </button>
                                    </div>
                                </div>

                                <!-- Tab: Subir imagen -->
                                <div class="tab-pane fade" id="tabSubir" role="tabpanel">
                                    <div class="upload-zone" id="uploadZone">
                                        <input type="file" id="firmaFile" name="firma_file" accept="image/png,image/jpeg,image/gif" class="d-none">
                                        <i class="bi bi-cloud-upload text-primary" style="font-size: 2.5rem;"></i>
                                        <p class="mt-2 mb-1">
                                            <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('firmaFile').click()">
                                                <i class="bi bi-image me-1"></i>Seleccionar imagen de firma
                                            </button>
                                        </p>
                                        <p class="text-muted small mb-0">PNG, JPG o GIF. Maximo 2MB.</p>
                                        <p class="text-muted small">O arrastre la imagen aqui</p>
                                    </div>
                                    <div id="firmaPreviewContainer" class="text-center mt-3 d-none">
                                        <img id="firmaPreview" class="img-fluid border rounded" style="max-height: 120px;">
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarImagenFirma()">
                                                <i class="bi bi-trash me-1"></i>Eliminar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Declaración legal -->
                            <div class="alert alert-info">
                                <h6><i class="bi bi-shield-check me-2"></i>Declaracion de Firma Electronica</h6>
                                <p class="mb-2 small">
                                    De conformidad con la Ley 527 de 1999 y el Decreto 2364 de 2012 de Colombia,
                                    al registrar mi firma electronica declaro que:
                                </p>
                                <ul class="small mb-0">
                                    <li>He revisado el contenido del documento</li>
                                    <li>Acepto que esta firma tiene la misma validez que mi firma manuscrita</li>
                                    <li>Autorizo el registro de evidencia digital (IP, fecha, hora, ubicacion)</li>
                                </ul>
                            </div>

                            <div class="acepto-terminos-box mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="aceptoTerminos" name="acepto_terminos" value="1" required>
                                    <label class="form-check-label" for="aceptoTerminos">
                                        <strong>Acepto los terminos y condiciones de la firma electronica</strong>
                                    </label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100" id="btnFirmar" disabled>
                                <i class="bi bi-pen me-2"></i>Firmar Documento
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // =============================================
        // CANVAS DE FIRMA
        // =============================================
        const canvas = document.getElementById('firmaCanvas');
        const ctx = canvas.getContext('2d');
        let dibujando = false;
        let hayDibujo = false;

        function ajustarCanvas() {
            const rect = canvas.getBoundingClientRect();
            canvas.width = rect.width;
            canvas.height = 200;
            ctx.strokeStyle = '#000';
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
        }
        ajustarCanvas();
        window.addEventListener('resize', ajustarCanvas);

        // Mouse events
        canvas.addEventListener('mousedown', iniciarDibujo);
        canvas.addEventListener('mousemove', dibujar);
        canvas.addEventListener('mouseup', terminarDibujo);
        canvas.addEventListener('mouseout', terminarDibujo);

        // Touch events
        canvas.addEventListener('touchstart', (e) => { e.preventDefault(); iniciarDibujo(e.touches[0]); });
        canvas.addEventListener('touchmove', (e) => { e.preventDefault(); dibujar(e.touches[0]); });
        canvas.addEventListener('touchend', terminarDibujo);

        function getPos(e) {
            const rect = canvas.getBoundingClientRect();
            return { x: e.clientX - rect.left, y: e.clientY - rect.top };
        }

        function iniciarDibujo(e) {
            dibujando = true;
            hayDibujo = true;
            canvas.classList.add('drawing');
            const pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
        }

        function dibujar(e) {
            if (!dibujando) return;
            const pos = getPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
        }

        function terminarDibujo() {
            if (!dibujando) return;
            dibujando = false;
            canvas.classList.remove('drawing');
            document.getElementById('firmaImagen').value = canvas.toDataURL('image/png');
            verificarFormulario();
        }

        function limpiarFirma() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            document.getElementById('firmaImagen').value = '';
            hayDibujo = false;
            verificarFormulario();
        }

        // =============================================
        // UPLOAD DE IMAGEN
        // =============================================
        const firmaFile = document.getElementById('firmaFile');
        const uploadZone = document.getElementById('uploadZone');

        firmaFile.addEventListener('change', function(e) {
            procesarArchivo(e.target.files[0]);
        });

        // Drag & Drop
        uploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });
        uploadZone.addEventListener('dragleave', () => {
            uploadZone.classList.remove('dragover');
        });
        uploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
            if (e.dataTransfer.files.length) {
                procesarArchivo(e.dataTransfer.files[0]);
            }
        });

        function procesarArchivo(file) {
            if (!file) return;

            // Validar tipo
            if (!['image/png', 'image/jpeg', 'image/gif'].includes(file.type)) {
                alert('Solo se permiten imagenes PNG, JPG o GIF');
                return;
            }

            // Validar tamaño (2MB)
            if (file.size > 2 * 1024 * 1024) {
                alert('La imagen no debe superar 2MB');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('firmaImagen').value = e.target.result;
                document.getElementById('firmaPreview').src = e.target.result;
                document.getElementById('firmaPreviewContainer').classList.remove('d-none');
                document.getElementById('uploadZone').classList.add('d-none');
                verificarFormulario();
            };
            reader.readAsDataURL(file);
        }

        function eliminarImagenFirma() {
            document.getElementById('firmaImagen').value = '';
            document.getElementById('firmaFile').value = '';
            document.getElementById('firmaPreviewContainer').classList.add('d-none');
            document.getElementById('uploadZone').classList.remove('d-none');
            verificarFormulario();
        }

        // =============================================
        // TABS - Cambiar tipo de firma
        // =============================================
        document.getElementById('tab-dibujar').addEventListener('shown.bs.tab', () => {
            document.getElementById('tipoFirma').value = 'draw';
            verificarFormulario();
        });
        document.getElementById('tab-subir').addEventListener('shown.bs.tab', () => {
            document.getElementById('tipoFirma').value = 'upload';
            verificarFormulario();
        });

        // =============================================
        // GEOLOCALIZACIÓN
        // =============================================
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(pos) {
                document.getElementById('geolocalizacion').value =
                    pos.coords.latitude + ',' + pos.coords.longitude;
            });
        }

        // =============================================
        // VALIDACIÓN DEL FORMULARIO
        // =============================================
        function verificarFormulario() {
            const firma = document.getElementById('firmaImagen').value;
            const acepto = document.getElementById('aceptoTerminos').checked;
            document.getElementById('btnFirmar').disabled = !firma || !acepto;
        }

        document.getElementById('aceptoTerminos').addEventListener('change', verificarFormulario);

        // =============================================
        // ENVÍO DEL FORMULARIO
        // =============================================
        document.getElementById('formFirma').addEventListener('submit', function(e) {
            e.preventDefault();

            const btn = document.getElementById('btnFirmar');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';

            const formData = new FormData(this);

            fetch('<?= base_url('firma/procesar') ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '<?= base_url('firma/confirmacion/' . esc($token)) ?>';
                } else {
                    alert(data.error || 'Error al procesar la firma');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-pen me-2"></i>Firmar Documento';
                }
            })
            .catch(error => {
                alert('Error de conexion. Intente nuevamente.');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-pen me-2"></i>Firmar Documento';
            });
        });
    </script>
</body>
</html>
