<?php
$firmantes = [
    ['tipo' => 'jefe', 'nombre' => $inv['investigador_jefe_nombre'] ?? 'Jefe Inmediato', 'firmado' => !empty($inv['firma_jefe'])],
    ['tipo' => 'copasst', 'nombre' => $inv['investigador_copasst_nombre'] ?? 'Representante COPASST', 'firmado' => !empty($inv['firma_copasst'])],
    ['tipo' => 'sst', 'nombre' => $inv['investigador_sst_nombre'] ?? 'Responsable SST', 'firmado' => !empty($inv['firma_sst'])],
];
$totalPasos = count($firmantes);
?>

<div class="container-fluid px-3">
    <h5 class="mt-2 mb-3 text-center">Firmas de la Investigacion</h5>

    <!-- Indicador de progreso -->
    <div class="d-flex justify-content-center gap-2 mb-3" id="stepsIndicator">
        <?php foreach ($firmantes as $i => $f): ?>
        <div class="step-dot <?= $f['firmado'] ? 'completed' : '' ?>" data-step="<?= $i ?>"
             style="width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center;
                    font-size:13px; font-weight:700;
                    background:<?= $f['firmado'] ? '#28a745' : '#e0e0e0' ?>;
                    color:<?= $f['firmado'] ? '#fff' : '#666' ?>;">
            <?= $f['firmado'] ? '<i class="fas fa-check"></i>' : ($i + 1) ?>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Panels de firma (uno por firmante) -->
    <?php foreach ($firmantes as $i => $f): ?>
    <div class="firma-panel" id="panel-<?= $i ?>" style="display:<?= $i === 0 ? 'block' : 'none' ?>;">
        <div class="card">
            <div class="card-body text-center">
                <p class="text-muted mb-1" style="font-size:13px;">PASO <?= $i + 1 ?> de <?= $totalPasos ?></p>
                <h6 class="mb-1">Firma del <?= ucfirst($f['tipo']) ?></h6>
                <p class="mb-3" style="font-size:14px; font-weight:600;"><?= esc($f['nombre']) ?></p>

                <?php if ($f['firmado']): ?>
                    <!-- Ya firmado -->
                    <div class="alert alert-success py-2" style="font-size:14px;">
                        <i class="fas fa-check-circle"></i> Firma registrada
                    </div>
                <?php else: ?>
                    <!-- Canvas para firmar -->
                    <div style="border:2px dashed #ccc; border-radius:8px; background:#fafafa; position:relative;">
                        <canvas id="canvas-<?= $f['tipo'] ?>" class="firma-canvas" width="100" height="200"
                                style="width:100%; height:200px; touch-action:none; cursor:crosshair;"></canvas>
                    </div>

                    <div class="d-flex justify-content-between mt-2 flex-wrap gap-1">
                        <div class="d-flex gap-1 flex-wrap">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="canvases['<?= $f['tipo'] ?>'].limpiar()">
                                <i class="fas fa-eraser"></i> Limpiar
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success btn-whatsapp-firma"
                                data-tipo="<?= esc($f['tipo']) ?>"
                                title="Enviar enlace por WhatsApp">
                                <i class="fab fa-whatsapp"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-email-firma"
                                data-tipo="<?= esc($f['tipo']) ?>"
                                title="Enviar enlace por email">
                                <i class="fas fa-envelope"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-copiar-firma"
                                data-tipo="<?= esc($f['tipo']) ?>"
                                title="Copiar enlace de firma">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <button type="button" class="btn btn-sm btn-pwa-primary" onclick="guardarFirma('<?= $f['tipo'] ?>', <?= $i ?>)"
                                style="background:#bd9751; color:#fff; border:none; border-radius:6px; padding:6px 16px;">
                            <i class="fas fa-save"></i> Guardar firma
                        </button>
                    </div>

                    <!-- Email inline (oculto hasta click) -->
                    <div class="email-inline-section mt-2" id="emailInline-<?= $f['tipo'] ?>" style="display:none;">
                        <div class="input-group input-group-sm">
                            <input type="email" class="form-control input-email-firma" data-tipo="<?= $f['tipo'] ?>" placeholder="correo@ejemplo.com">
                            <button type="button" class="btn btn-primary btn-enviar-email-inline" data-tipo="<?= $f['tipo'] ?>">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                        <div class="email-status" id="emailStatus-<?= $f['tipo'] ?>" style="font-size:12px; margin-top:4px;"></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Navegacion entre pasos -->
        <div class="d-flex justify-content-between mt-3">
            <?php if ($i > 0): ?>
                <button type="button" class="btn btn-sm btn-outline-dark" onclick="irPaso(<?= $i - 1 ?>)">
                    <i class="fas fa-arrow-left"></i> Anterior
                </button>
            <?php else: ?>
                <a href="/inspecciones/investigacion-accidente/edit/<?= $inv['id'] ?>" class="btn btn-sm btn-outline-dark">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            <?php endif; ?>

            <?php if ($i < $totalPasos - 1): ?>
                <button type="button" class="btn btn-sm btn-outline-dark" onclick="irPaso(<?= $i + 1 ?>)">
                    Siguiente <i class="fas fa-arrow-right"></i>
                </button>
            <?php else: ?>
                <button type="button" class="btn btn-pwa btn-pwa-primary" id="btnFinalizar" onclick="finalizarInvestigacion()"
                        style="min-height:40px; font-size:15px;">
                    <i class="fas fa-file-pdf"></i> Finalizar y generar PDF
                </button>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>

</div>

<style>
.firma-canvas {
    display: block;
}
.step-dot.active {
    background: #bd9751 !important;
    color: #fff !important;
    transform: scale(1.2);
    transition: transform 0.2s;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const invId = <?= $inv['id'] ?>;
    const csrfName = '<?= csrf_token() ?>';
    var csrfHash = '<?= csrf_hash() ?>';

    // Actualizar CSRF hash desde cookie después de cada POST
    function getCSRFFromCookie() {
        var match = document.cookie.match(/csrf_cookie_name=([^;]+)/);
        if (match) csrfHash = match[1];
    }

    // ============ Clase SignatureCanvas ============
    class SignatureCanvas {
        constructor(canvasId) {
            this.canvas = document.getElementById(canvasId);
            if (!this.canvas) return;
            this.ctx = this.canvas.getContext('2d');
            this.dibujando = false;
            this.hayDibujo = false;
            this.dpr = window.devicePixelRatio || 1;
            this.setup();
        }

        setup() {
            const rect = this.canvas.getBoundingClientRect();
            this.canvas.width = rect.width * this.dpr;
            this.canvas.height = 200 * this.dpr;
            this.ctx.scale(this.dpr, this.dpr);

            this.ctx.strokeStyle = '#000';
            this.ctx.lineWidth = 3;
            this.ctx.lineCap = 'round';
            this.ctx.lineJoin = 'round';

            // Mouse
            this.canvas.addEventListener('mousedown', (e) => this.iniciar(e));
            this.canvas.addEventListener('mousemove', (e) => this.dibujar(e));
            this.canvas.addEventListener('mouseup', () => this.terminar());
            this.canvas.addEventListener('mouseout', () => this.terminar());

            // Touch
            this.canvas.addEventListener('touchstart', (e) => {
                e.preventDefault();
                if (e.touches.length > 1) return;
                this.iniciar(e.touches[0]);
            });
            this.canvas.addEventListener('touchmove', (e) => {
                e.preventDefault();
                if (e.touches.length > 1) { this.terminar(); return; }
                this.dibujar(e.touches[0]);
            });
            this.canvas.addEventListener('touchend', () => this.terminar());
        }

        getPos(e) {
            const rect = this.canvas.getBoundingClientRect();
            return { x: e.clientX - rect.left, y: e.clientY - rect.top };
        }

        iniciar(e) {
            this.dibujando = true;
            this.hayDibujo = true;
            const pos = this.getPos(e);
            this.ctx.beginPath();
            this.ctx.moveTo(pos.x, pos.y);
        }

        dibujar(e) {
            if (!this.dibujando) return;
            const pos = this.getPos(e);
            this.ctx.lineTo(pos.x, pos.y);
            this.ctx.stroke();
        }

        terminar() { this.dibujando = false; }

        limpiar() {
            this.ctx.setTransform(1, 0, 0, 1, 0, 0);
            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
            this.hayDibujo = false;
            this.setup();
        }

        validarMinPixeles(minimo = 100) {
            const imgData = this.ctx.getImageData(0, 0, this.canvas.width, this.canvas.height).data;
            let oscuros = 0;
            for (let i = 3; i < imgData.length; i += 4) {
                if (imgData[i] > 0) oscuros++;
            }
            return oscuros >= minimo;
        }

        exportar() {
            const imageData = this.ctx.getImageData(0, 0, this.canvas.width, this.canvas.height);
            const data = imageData.data;
            let minX = this.canvas.width, minY = this.canvas.height, maxX = 0, maxY = 0;

            for (let y = 0; y < this.canvas.height; y++) {
                for (let x = 0; x < this.canvas.width; x++) {
                    if (data[(y * this.canvas.width + x) * 4 + 3] > 0) {
                        if (x < minX) minX = x;
                        if (x > maxX) maxX = x;
                        if (y < minY) minY = y;
                        if (y > maxY) maxY = y;
                    }
                }
            }

            if (maxX <= minX || maxY <= minY) return this.canvas.toDataURL('image/png');

            const pad = 20;
            minX = Math.max(0, minX - pad);
            minY = Math.max(0, minY - pad);
            maxX = Math.min(this.canvas.width, maxX + pad);
            maxY = Math.min(this.canvas.height, maxY + pad);

            const cropW = maxX - minX, cropH = maxY - minY;
            const finalH = 150, finalW = Math.round(finalH * (cropW / cropH));

            const tmp = document.createElement('canvas');
            tmp.width = finalW;
            tmp.height = finalH;
            tmp.getContext('2d').drawImage(this.canvas, minX, minY, cropW, cropH, 0, 0, finalW, finalH);
            return tmp.toDataURL('image/png');
        }
    }

    // Inicializar canvas por cada firmante no firmado
    window.canvases = {};
    <?php foreach ($firmantes as $f): ?>
    <?php if (!$f['firmado']): ?>
    canvases['<?= $f['tipo'] ?>'] = new SignatureCanvas('canvas-<?= $f['tipo'] ?>');
    <?php endif; ?>
    <?php endforeach; ?>

    // Navegacion entre pasos
    window.irPaso = function(step) {
        document.querySelectorAll('.firma-panel').forEach(p => p.style.display = 'none');
        document.getElementById('panel-' + step).style.display = 'block';

        document.querySelectorAll('.step-dot').forEach(d => d.classList.remove('active'));
        document.querySelector('.step-dot[data-step="' + step + '"]').classList.add('active');

        <?php foreach ($firmantes as $i => $f): ?>
        <?php if (!$f['firmado']): ?>
        if (step === <?= $i ?> && canvases['<?= $f['tipo'] ?>']) {
            setTimeout(() => canvases['<?= $f['tipo'] ?>'].setup(), 50);
        }
        <?php endif; ?>
        <?php endforeach; ?>
    };

    // Guardar firma individual via AJAX
    window.guardarFirma = function(tipo, pasoActual) {
        const cv = canvases[tipo];
        if (!cv || !cv.hayDibujo) {
            Swal.fire({ icon: 'warning', title: 'Firma vacia', text: 'Dibuja tu firma en el recuadro.', confirmButtonColor: '#bd9751' });
            return;
        }

        if (!cv.validarMinPixeles(100)) {
            Swal.fire({ icon: 'warning', title: 'Firma insuficiente', text: 'La firma es demasiado pequena. Intenta de nuevo.', confirmButtonColor: '#bd9751' });
            return;
        }

        const firmaBase64 = cv.exportar();

        Swal.fire({
            title: 'Confirmar firma',
            html: '<p style="font-size:14px;">Esta firma es correcta?</p><img src="' + firmaBase64 + '" style="max-width:100%; max-height:120px; border:1px solid #ddd; border-radius:4px;">',
            showCancelButton: true,
            confirmButtonText: 'Confirmar',
            cancelButtonText: 'Repetir',
            confirmButtonColor: '#bd9751',
        }).then((result) => {
            if (!result.isConfirmed) return;

            const formData = new FormData();
            formData.append(csrfName, csrfHash);
            formData.append('tipo', tipo);
            formData.append('firma_imagen', firmaBase64);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/inspecciones/investigacion-accidente/save-firma/' + invId, true);
            xhr.withCredentials = true;
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.onload = function() {
                getCSRFFromCookie();
                try { var data = JSON.parse(xhr.responseText); } catch(e) { Swal.fire({icon:'error',title:'Error',text:'Respuesta invalida'}); return; }
                if (data.success) {
                    const dot = document.querySelector('.step-dot[data-step="' + pasoActual + '"]');
                    dot.style.background = '#28a745';
                    dot.style.color = '#fff';
                    dot.innerHTML = '<i class="fas fa-check"></i>';

                    const panel = document.getElementById('panel-' + pasoActual);
                    const cardBody = panel.querySelector('.card-body');
                    const titulo = cardBody.querySelector('h6').textContent;
                    const nombre = cardBody.querySelectorAll('p')[1].textContent;
                    cardBody.innerHTML = '<p class="text-muted mb-1" style="font-size:13px;">PASO ' + (pasoActual+1) + ' de <?= $totalPasos ?></p>' +
                        '<h6 class="mb-1">' + titulo + '</h6>' +
                        '<p class="mb-3" style="font-size:14px; font-weight:600;">' + nombre + '</p>' +
                        '<div class="alert alert-success py-2" style="font-size:14px;"><i class="fas fa-check-circle"></i> Firma registrada</div>';

                    const siguiente = pasoActual + 1;
                    if (siguiente < <?= $totalPasos ?>) {
                        setTimeout(() => irPaso(siguiente), 500);
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Firma guardada',
                        timer: 1200,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.error || 'No se pudo guardar la firma.' });
                }
            };
            xhr.onerror = function() {
                Swal.fire({ icon: 'error', title: 'Error de conexion', text: 'Verifica tu conexion a internet.' });
            };
            xhr.send(formData);
        });
    };

    // Finalizar investigacion
    window.finalizarInvestigacion = function() {
        Swal.fire({
            title: 'Finalizar investigacion',
            html: '<p>Se generara el PDF y la investigacion quedara <strong>bloqueada</strong>.</p><p>Confirmas que toda la informacion esta correcta?</p>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Finalizar y generar PDF',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#bd9751',
        }).then((result) => {
            if (!result.isConfirmed) return;

            Swal.fire({ title: 'Generando PDF...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            const formData = new FormData();
            formData.append(csrfName, csrfHash);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/inspecciones/investigacion-accidente/finalizar/' + invId, true);
            xhr.withCredentials = true;
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.onload = function() {
                Swal.close();
                getCSRFFromCookie();
                try { var data = JSON.parse(xhr.responseText); } catch(e) {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Respuesta invalida del servidor.' });
                    return;
                }
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Investigacion finalizada',
                        html: '<p>El PDF ha sido generado exitosamente.</p>',
                        confirmButtonText: 'Ver PDF',
                        showCancelButton: true,
                        cancelButtonText: 'Volver al inicio',
                        confirmButtonColor: '#bd9751',
                    }).then((r) => {
                        if (r.isConfirmed) {
                            window.open('/inspecciones/investigacion-accidente/pdf/' + invId, '_blank');
                            window.location.href = '/inspecciones/investigacion-accidente';
                        } else {
                            window.location.href = '/inspecciones';
                        }
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.error || 'No se pudo finalizar la investigacion.' });
                }
            };
            xhr.onerror = function() {
                Swal.close();
                Swal.fire({ icon: 'error', title: 'Error de conexion', text: 'Verifica tu conexion a internet.' });
            };
            xhr.send(formData);
        });
    };

    // Activar primer paso
    document.querySelector('.step-dot[data-step="0"]').classList.add('active');

    // ============ Firma Remota: WhatsApp, Email, Copiar (URLs pre-generadas, sin fetch) ============
    var tokenUrls = <?= json_encode($tokensRemoto ?? []) ?>;

    // Boton WhatsApp
    document.querySelectorAll('.btn-whatsapp-firma').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var tipo = this.dataset.tipo;
            var url = tokenUrls[tipo];
            if (!url) { Swal.fire('Info', 'Esta firma ya fue registrada.', 'info'); return; }
            var texto = encodeURIComponent(
                'Hola, por favor firma la investigacion de accidente/incidente de trabajo '
                + 'haciendo clic en este enlace (valido 7 dias):\n' + url
            );
            window.open('https://wa.me/?text=' + texto, '_blank');
        });
    });

    // Boton Copiar enlace
    document.querySelectorAll('.btn-copiar-firma').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var tipo = this.dataset.tipo;
            var url = tokenUrls[tipo];
            if (!url) { Swal.fire('Info', 'Esta firma ya fue registrada.', 'info'); return; }
            var thisBtn = this;
            navigator.clipboard.writeText(url).then(function() {
                thisBtn.innerHTML = '<i class="fas fa-check"></i>';
                thisBtn.classList.remove('btn-outline-secondary');
                thisBtn.classList.add('btn-success');
                setTimeout(function() {
                    thisBtn.innerHTML = '<i class="fas fa-copy"></i>';
                    thisBtn.classList.remove('btn-success');
                    thisBtn.classList.add('btn-outline-secondary');
                }, 2000);
                Swal.fire({ icon: 'success', title: 'Copiado', text: 'Enlace copiado al portapapeles.', timer: 1500, showConfirmButton: false, toast: true, position: 'top-end' });
            });
        });
    });

    // Boton Email - mostrar/ocultar input inline
    document.querySelectorAll('.btn-email-firma').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var tipo = this.dataset.tipo;
            var url = tokenUrls[tipo];
            if (!url) { Swal.fire('Info', 'Esta firma ya fue registrada.', 'info'); return; }
            var section = document.getElementById('emailInline-' + tipo);
            section.style.display = section.style.display === 'none' ? 'block' : 'none';
            if (section.style.display === 'block') {
                section.querySelector('.input-email-firma').focus();
            }
        });
    });

    // Enviar email inline - usa form submit normal (no fetch)
    document.querySelectorAll('.btn-enviar-email-inline').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var tipo = this.dataset.tipo;
            var input = document.querySelector('.input-email-firma[data-tipo="' + tipo + '"]');
            var statusEl = document.getElementById('emailStatus-' + tipo);
            var email = input.value.trim();
            var url = tokenUrls[tipo];

            if (!email || !email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                statusEl.innerHTML = '<span class="text-danger">Ingresa un correo valido</span>';
                return;
            }
            if (!url) { statusEl.innerHTML = '<span class="text-danger">No hay enlace disponible</span>'; return; }

            btn.disabled = true;
            statusEl.innerHTML = '<span class="text-muted"><i class="fas fa-spinner fa-spin"></i> Enviando...</span>';

            // Usar XMLHttpRequest en vez de fetch (el SW no lo intercepta)
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/inspecciones/investigacion-accidente/enviar-enlace-firma/' + invId, true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.withCredentials = true;
            xhr.onload = function() {
                getCSRFFromCookie();
                try {
                    var resp = JSON.parse(xhr.responseText);
                    if (resp.success) {
                        statusEl.innerHTML = '<span class="text-success"><i class="fas fa-check-circle"></i> Enviado a ' + email + '</span>';
                        input.value = '';
                    } else {
                        statusEl.innerHTML = '<span class="text-danger">' + (resp.error || 'Error al enviar') + '</span>';
                        btn.disabled = false;
                    }
                } catch(e) {
                    statusEl.innerHTML = '<span class="text-danger">Error en respuesta del servidor</span>';
                    btn.disabled = false;
                }
            };
            xhr.onerror = function() {
                statusEl.innerHTML = '<span class="text-danger">Error de conexion</span>';
                btn.disabled = false;
            };
            var fd = new FormData();
            fd.append(csrfName, csrfHash);
            fd.append('email', email);
            fd.append('url', url);
            fd.append('tipo', tipo);
            xhr.send(fd);
        });
    });

    // Enter en input email
    document.querySelectorAll('.input-email-firma').forEach(function(input) {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.querySelector('.btn-enviar-email-inline[data-tipo="' + this.dataset.tipo + '"]').click();
            }
        });
    });
});
</script>
