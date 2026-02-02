<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firmar Acta - <?= esc($acta['numero_acta']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .firma-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .card-header {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }
        .firma-canvas-container {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            background: #f8f9fa;
            position: relative;
        }
        #firmaCanvas {
            width: 100%;
            height: 200px;
            cursor: crosshair;
            touch-action: none;
        }
        .btn-limpiar {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .acta-resumen {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .seccion-acta {
            border-left: 3px solid #667eea;
            padding-left: 15px;
            margin-bottom: 15px;
        }
        .progreso-firmas {
            background: #e9ecef;
            border-radius: 20px;
            padding: 3px;
        }
        .progreso-firmas .progress-bar {
            border-radius: 20px;
        }
        .asistente-badge {
            display: inline-flex;
            align-items: center;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            margin: 2px;
        }
        .asistente-firmado {
            background: #d4edda;
            color: #155724;
        }
        .asistente-pendiente {
            background: #fff3cd;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="firma-container py-4">
        <!-- Header -->
        <div class="text-center text-white mb-4">
            <h2><i class="bi bi-pen-fill me-2"></i>Firma de Acta</h2>
            <p class="mb-0"><?= esc($cliente['nombre_cliente']) ?></p>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1"><?= esc($acta['numero_acta']) ?></h4>
                        <p class="mb-0 opacity-75"><?= esc($comite['tipo_nombre']) ?></p>
                    </div>
                    <div class="text-end">
                        <div class="h5 mb-0"><?= date('d/m/Y', strtotime($acta['fecha_reunion'])) ?></div>
                        <small class="opacity-75"><?= esc($acta['lugar']) ?></small>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <!-- Progreso de firmas -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Progreso de firmas</span>
                        <strong><?= $firmados ?> de <?= $totalFirmantes ?></strong>
                    </div>
                    <div class="progreso-firmas">
                        <?php $porcentaje = $totalFirmantes > 0 ? ($firmados / $totalFirmantes) * 100 : 0; ?>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-success" style="width: <?= $porcentaje ?>%">
                                <?= round($porcentaje) ?>%
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información del firmante -->
                <div class="alert alert-info">
                    <i class="bi bi-person-circle me-2"></i>
                    Firmando como: <strong><?= esc($asistente['nombre_completo']) ?></strong>
                    <?php if (!empty($asistente['cargo'])): ?>
                        <br><small><?= esc($asistente['cargo']) ?></small>
                    <?php endif; ?>
                </div>

                <!-- Resumen del acta -->
                <div class="acta-resumen">
                    <h5 class="mb-3"><i class="bi bi-file-text me-2"></i>Resumen del Acta</h5>

                    <!-- Orden del día -->
                    <?php if (!empty($acta['orden_del_dia'])): ?>
                    <div class="seccion-acta">
                        <h6>Orden del Dia</h6>
                        <ol class="mb-0">
                            <?php foreach ($acta['orden_del_dia'] as $punto): ?>
                                <li><?= esc($punto['tema']) ?></li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                    <?php endif; ?>

                    <!-- Desarrollo -->
                    <?php if (!empty($acta['desarrollo'])): ?>
                    <div class="seccion-acta">
                        <h6>Desarrollo de la Reunion</h6>
                        <?php foreach ($acta['desarrollo'] as $punto): ?>
                            <p class="mb-1"><strong>Punto <?= $punto['punto'] ?>:</strong> <?= esc(substr($punto['descripcion'], 0, 200)) ?>...</p>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Compromisos -->
                    <?php if (!empty($acta['compromisos'])): ?>
                    <div class="seccion-acta">
                        <h6>Compromisos</h6>
                        <ul class="mb-0">
                            <?php foreach ($acta['compromisos'] as $comp): ?>
                                <li>
                                    <?= esc(substr($comp['descripcion'], 0, 100)) ?>
                                    <small class="text-muted">(<?= esc($comp['responsable_nombre']) ?> - <?= date('d/m/Y', strtotime($comp['fecha_vencimiento'])) ?>)</small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <!-- Asistentes -->
                    <div class="seccion-acta">
                        <h6>Asistentes</h6>
                        <div>
                            <?php foreach ($acta['asistentes'] as $a): ?>
                                <?php if ($a['asistio']): ?>
                                    <span class="asistente-badge <?= $a['estado_firma'] === 'firmado' ? 'asistente-firmado' : 'asistente-pendiente' ?>">
                                        <?php if ($a['estado_firma'] === 'firmado'): ?>
                                            <i class="bi bi-check-circle me-1"></i>
                                        <?php else: ?>
                                            <i class="bi bi-clock me-1"></i>
                                        <?php endif; ?>
                                        <?= esc($a['nombre_completo']) ?>
                                    </span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Formulario de firma -->
                <form id="formFirma">
                    <!-- Observación opcional -->
                    <div class="mb-3">
                        <label class="form-label">Observacion (opcional)</label>
                        <textarea class="form-control" id="observacion" rows="2"
                                  placeholder="Agregue alguna observacion si lo desea..."></textarea>
                    </div>

                    <!-- Canvas de firma -->
                    <div class="mb-3">
                        <label class="form-label">Su firma <span class="text-danger">*</span></label>
                        <div class="firma-canvas-container">
                            <canvas id="firmaCanvas"></canvas>
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-limpiar" onclick="limpiarFirma()">
                                <i class="bi bi-eraser me-1"></i>Limpiar
                            </button>
                        </div>
                        <small class="text-muted">Dibuje su firma con el mouse o con el dedo en dispositivos tactiles</small>
                    </div>

                    <!-- Checkbox de confirmación -->
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" id="confirmacion" required>
                        <label class="form-check-label" for="confirmacion">
                            Confirmo que asisti a la reunion y estoy de acuerdo con el contenido del acta.
                        </label>
                    </div>

                    <!-- Botón de firma -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg" id="btnFirmar">
                            <i class="bi bi-pen-fill me-2"></i>Firmar Acta
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center text-white-50 mt-4">
            <small>
                <i class="bi bi-shield-check me-1"></i>
                Su firma queda registrada con marca de tiempo y direccion IP para garantizar la autenticidad.
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Canvas de firma
        const canvas = document.getElementById('firmaCanvas');
        const ctx = canvas.getContext('2d');
        let dibujando = false;
        let hayFirma = false;

        // Configurar canvas
        function configurarCanvas() {
            const rect = canvas.getBoundingClientRect();
            const dpr = window.devicePixelRatio || 1;

            canvas.width = rect.width * dpr;
            canvas.height = 200 * dpr;

            ctx.scale(dpr, dpr);
            canvas.style.width = rect.width + 'px';
            canvas.style.height = '200px';

            ctx.strokeStyle = '#000';
            ctx.lineWidth = 2.5;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
        }

        // Obtener posición del mouse/touch
        function obtenerPosicion(e) {
            const rect = canvas.getBoundingClientRect();
            if (e.touches) {
                return {
                    x: e.touches[0].clientX - rect.left,
                    y: e.touches[0].clientY - rect.top
                };
            }
            return {
                x: e.clientX - rect.left,
                y: e.clientY - rect.top
            };
        }

        // Eventos del canvas
        canvas.addEventListener('mousedown', iniciarDibujo);
        canvas.addEventListener('mousemove', dibujar);
        canvas.addEventListener('mouseup', terminarDibujo);
        canvas.addEventListener('mouseout', terminarDibujo);

        canvas.addEventListener('touchstart', iniciarDibujo);
        canvas.addEventListener('touchmove', dibujar);
        canvas.addEventListener('touchend', terminarDibujo);

        function iniciarDibujo(e) {
            e.preventDefault();
            dibujando = true;
            const pos = obtenerPosicion(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
        }

        function dibujar(e) {
            if (!dibujando) return;
            e.preventDefault();
            const pos = obtenerPosicion(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
            hayFirma = true;
        }

        function terminarDibujo() {
            dibujando = false;
        }

        function limpiarFirma() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            hayFirma = false;
        }

        // Obtener firma como imagen base64
        function obtenerFirmaBase64() {
            if (!hayFirma) return null;

            // Crear canvas temporal para recortar
            const tempCanvas = document.createElement('canvas');
            const tempCtx = tempCanvas.getContext('2d');

            // Encontrar bounds de la firma
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const data = imageData.data;
            let minX = canvas.width, minY = canvas.height, maxX = 0, maxY = 0;

            for (let y = 0; y < canvas.height; y++) {
                for (let x = 0; x < canvas.width; x++) {
                    const alpha = data[(y * canvas.width + x) * 4 + 3];
                    if (alpha > 0) {
                        minX = Math.min(minX, x);
                        minY = Math.min(minY, y);
                        maxX = Math.max(maxX, x);
                        maxY = Math.max(maxY, y);
                    }
                }
            }

            // Agregar padding
            const padding = 10;
            minX = Math.max(0, minX - padding);
            minY = Math.max(0, minY - padding);
            maxX = Math.min(canvas.width, maxX + padding);
            maxY = Math.min(canvas.height, maxY + padding);

            const width = maxX - minX;
            const height = maxY - minY;

            if (width <= 0 || height <= 0) return null;

            tempCanvas.width = width;
            tempCanvas.height = height;
            tempCtx.drawImage(canvas, minX, minY, width, height, 0, 0, width, height);

            return tempCanvas.toDataURL('image/png');
        }

        // Enviar firma
        document.getElementById('formFirma').addEventListener('submit', async function(e) {
            e.preventDefault();

            if (!document.getElementById('confirmacion').checked) {
                alert('Debe confirmar que asistio a la reunion');
                return;
            }

            const firmaBase64 = obtenerFirmaBase64();
            if (!firmaBase64) {
                alert('Debe dibujar su firma');
                return;
            }

            const btnFirmar = document.getElementById('btnFirmar');
            btnFirmar.disabled = true;
            btnFirmar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';

            try {
                const response = await fetch('<?= base_url('acta/firmar/' . $token) ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `firma_imagen=${encodeURIComponent(firmaBase64)}&observacion=${encodeURIComponent(document.getElementById('observacion').value)}`
                });

                const data = await response.json();

                if (data.success) {
                    window.location.href = '<?= base_url('acta/firma-exitosa/' . $token) ?>';
                } else {
                    alert(data.message || 'Error al procesar la firma');
                    btnFirmar.disabled = false;
                    btnFirmar.innerHTML = '<i class="bi bi-pen-fill me-2"></i>Firmar Acta';
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error de conexion. Intente nuevamente.');
                btnFirmar.disabled = false;
                btnFirmar.innerHTML = '<i class="bi bi-pen-fill me-2"></i>Firmar Acta';
            }
        });

        // Inicializar
        window.addEventListener('load', configurarCanvas);
        window.addEventListener('resize', configurarCanvas);
    </script>
</body>
</html>
