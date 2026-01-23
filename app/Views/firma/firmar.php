<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firmar Documento - <?= esc($documento['codigo']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .firma-canvas {
            border: 2px dashed #D1D5DB;
            border-radius: 8px;
            cursor: crosshair;
            touch-action: none;
        }
        .firma-canvas.drawing {
            border-color: #3B82F6;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary">
        <div class="container">
            <span class="navbar-brand">
                <i class="bi bi-pen me-2"></i>Firma Electrónica
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
                                <p class="mb-1"><strong>Código:</strong> <?= esc($documento['codigo']) ?></p>
                                <p class="mb-1"><strong>Nombre:</strong> <?= esc($documento['nombre']) ?></p>
                                <p class="mb-0"><strong>Versión:</strong> <?= $documento['version_actual'] ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Firmante:</strong> <?= esc($solicitud['firmante_nombre']) ?></p>
                                <p class="mb-1"><strong>Cargo:</strong> <?= esc($solicitud['firmante_cargo']) ?></p>
                                <p class="mb-0"><strong>Tipo:</strong> <?= ucfirst($solicitud['firmante_tipo']) ?></p>
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
                        <form id="formFirma">
                            <input type="hidden" name="token" value="<?= esc($token) ?>">
                            <input type="hidden" name="firma_imagen" id="firmaImagen">
                            <input type="hidden" name="geolocalizacion" id="geolocalizacion">
                            <input type="hidden" name="tipo_firma" value="canvas">

                            <div class="mb-4">
                                <label class="form-label">Dibuje su firma</label>
                                <canvas id="firmaCanvas" class="firma-canvas w-100" height="200"></canvas>
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="limpiarFirma()">
                                        <i class="bi bi-eraser me-1"></i>Limpiar
                                    </button>
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <h6><i class="bi bi-shield-check me-2"></i>Declaración de Firma Electrónica</h6>
                                <p class="mb-2 small">
                                    De conformidad con la Ley 527 de 1999 de Colombia, al registrar mi firma electrónica
                                    declaro que:
                                </p>
                                <ul class="small mb-0">
                                    <li>He revisado el contenido del documento</li>
                                    <li>Acepto que esta firma tiene la misma validez que mi firma manuscrita</li>
                                    <li>Autorizo el registro de evidencia digital (IP, fecha, hora, ubicación)</li>
                                </ul>
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="aceptoTerminos" name="acepto_terminos" value="1" required>
                                <label class="form-check-label" for="aceptoTerminos">
                                    <strong>Acepto los términos y condiciones de la firma electrónica</strong>
                                </label>
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
        // Canvas de firma
        const canvas = document.getElementById('firmaCanvas');
        const ctx = canvas.getContext('2d');
        let dibujando = false;

        // Ajustar tamaño del canvas
        function ajustarCanvas() {
            const rect = canvas.getBoundingClientRect();
            canvas.width = rect.width;
            canvas.height = 200;
            ctx.strokeStyle = '#000';
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
        }
        ajustarCanvas();
        window.addEventListener('resize', ajustarCanvas);

        // Eventos de dibujo
        canvas.addEventListener('mousedown', iniciarDibujo);
        canvas.addEventListener('mousemove', dibujar);
        canvas.addEventListener('mouseup', terminarDibujo);
        canvas.addEventListener('mouseout', terminarDibujo);

        // Touch events
        canvas.addEventListener('touchstart', (e) => {
            e.preventDefault();
            iniciarDibujo(e.touches[0]);
        });
        canvas.addEventListener('touchmove', (e) => {
            e.preventDefault();
            dibujar(e.touches[0]);
        });
        canvas.addEventListener('touchend', terminarDibujo);

        function getPos(e) {
            const rect = canvas.getBoundingClientRect();
            return {
                x: e.clientX - rect.left,
                y: e.clientY - rect.top
            };
        }

        function iniciarDibujo(e) {
            dibujando = true;
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
            dibujando = false;
            canvas.classList.remove('drawing');
            document.getElementById('firmaImagen').value = canvas.toDataURL('image/png');
            verificarFormulario();
        }

        function limpiarFirma() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            document.getElementById('firmaImagen').value = '';
            verificarFormulario();
        }

        // Geolocalización
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(pos) {
                document.getElementById('geolocalizacion').value =
                    pos.coords.latitude + ',' + pos.coords.longitude;
            });
        }

        // Verificar formulario
        function verificarFormulario() {
            const firma = document.getElementById('firmaImagen').value;
            const acepto = document.getElementById('aceptoTerminos').checked;
            document.getElementById('btnFirmar').disabled = !firma || !acepto;
        }

        document.getElementById('aceptoTerminos').addEventListener('change', verificarFormulario);

        // Enviar firma
        document.getElementById('formFirma').addEventListener('submit', function(e) {
            e.preventDefault();

            const btn = document.getElementById('btnFirmar');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';

            const formData = new FormData(this);

            fetch('/firma/procesar', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '/firma/confirmacion/<?= esc($token) ?>';
                } else {
                    alert(data.error || 'Error al procesar la firma');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-pen me-2"></i>Firmar Documento';
                }
            })
            .catch(error => {
                alert('Error de conexión');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-pen me-2"></i>Firmar Documento';
            });
        });
    </script>
</body>
</html>
