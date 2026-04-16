<?php
$totalPasos = count($firmantes);
?>

<div class="container-fluid px-3">
    <h5 class="mt-2 mb-3 text-center">Firmas del Acta</h5>

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
                <?php $labelTipo = ['administrador' => 'Cliente', 'vigia' => 'Vigia SST', 'consultor' => 'Consultor'][$f['tipo']] ?? ucfirst($f['tipo']); ?>
                <h6 class="mb-1">Firma del <?= $labelTipo ?></h6>
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

                    <div class="d-flex justify-content-between mt-2">
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="canvases['<?= $f['tipo'] ?>'].limpiar()">
                                <i class="fas fa-eraser"></i> Limpiar
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success btn-whatsapp-firma"
                                data-tipo="<?= esc($f['tipo']) ?>"
                                title="Enviar enlace para firma remota">
                                <i class="fab fa-whatsapp"></i> Enviar enlace
                            </button>
                        </div>
                        <button type="button" class="btn btn-sm btn-pwa-primary" onclick="guardarFirma('<?= $f['tipo'] ?>', <?= $i ?>)"
                                style="background:#bd9751; color:#fff; border:none; border-radius:6px; padding:6px 16px;">
                            <i class="fas fa-save"></i> Guardar firma
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Navegación entre pasos -->
        <div class="d-flex justify-content-between mt-3">
            <?php if ($i > 0): ?>
                <button type="button" class="btn btn-sm btn-outline-dark" onclick="irPaso(<?= $i - 1 ?>)">
                    <i class="fas fa-arrow-left"></i> Anterior
                </button>
            <?php else: ?>
                <a href="<?= site_url('inspecciones/acta-visita/edit/' . $acta['id']) ?>" class="btn btn-sm btn-outline-dark">
                    <i class="fas fa-arrow-left"></i> Volver al acta
                </a>
            <?php endif; ?>

            <?php if ($i < $totalPasos - 1): ?>
                <button type="button" class="btn btn-sm btn-outline-dark" onclick="irPaso(<?= $i + 1 ?>)">
                    Siguiente <i class="fas fa-arrow-right"></i>
                </button>
            <?php else: ?>
                <button type="button" class="btn btn-pwa btn-pwa-primary" id="btnFinalizar" onclick="finalizarActa()"
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
    const actaId = <?= $acta['id'] ?>;
    const csrfName = '<?= csrf_token() ?>';
    const csrfHash = '<?= csrf_hash() ?>';

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

            // Touch (Layer 1: filtro multi-touch)
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

        // Layer 2: mínimo 100 píxeles oscuros
        validarMinPixeles(minimo = 100) {
            const imgData = this.ctx.getImageData(0, 0, this.canvas.width, this.canvas.height).data;
            let oscuros = 0;
            for (let i = 3; i < imgData.length; i += 4) {
                if (imgData[i] > 0) oscuros++;
            }
            return oscuros >= minimo;
        }

        // Export optimizado: crop + resize a 150px height
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

    // Navegación entre pasos
    window.irPaso = function(step) {
        document.querySelectorAll('.firma-panel').forEach(p => p.style.display = 'none');
        document.getElementById('panel-' + step).style.display = 'block';

        document.querySelectorAll('.step-dot').forEach(d => d.classList.remove('active'));
        document.querySelector('.step-dot[data-step="' + step + '"]').classList.add('active');

        // Re-setup canvas si existe (necesario porque display:none rompe dimensiones)
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
            Swal.fire({ icon: 'warning', title: 'Firma vacía', text: 'Dibuja tu firma en el recuadro.', confirmButtonColor: '#bd9751' });
            return;
        }

        // Layer 2: validar píxeles
        if (!cv.validarMinPixeles(100)) {
            Swal.fire({ icon: 'warning', title: 'Firma insuficiente', text: 'La firma es demasiado pequeña. Intenta de nuevo.', confirmButtonColor: '#bd9751' });
            return;
        }

        const firmaBase64 = cv.exportar();

        // Layer 3: SweetAlert preview
        Swal.fire({
            title: 'Confirmar firma',
            html: '<p style="font-size:14px;">¿Esta firma es correcta?</p><img src="' + firmaBase64 + '" style="max-width:100%; max-height:120px; border:1px solid #ddd; border-radius:4px;">',
            showCancelButton: true,
            confirmButtonText: 'Confirmar',
            cancelButtonText: 'Repetir',
            confirmButtonColor: '#bd9751',
        }).then((result) => {
            if (!result.isConfirmed) return;

            // Enviar al servidor
            const formData = new FormData();
            formData.append(csrfName, csrfHash);
            formData.append('tipo', tipo);
            formData.append('firma_imagen', firmaBase64);

            fetch('<?= site_url('inspecciones/acta-visita/save-firma/') ?>' + actaId, {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    // Marcar paso como completado
                    const dot = document.querySelector('.step-dot[data-step="' + pasoActual + '"]');
                    dot.style.background = '#28a745';
                    dot.style.color = '#fff';
                    dot.innerHTML = '<i class="fas fa-check"></i>';

                    // Reemplazar canvas con mensaje de éxito
                    const panel = document.getElementById('panel-' + pasoActual);
                    const cardBody = panel.querySelector('.card-body');
                    const titulo = cardBody.querySelector('h6').textContent;
                    const nombre = cardBody.querySelectorAll('p')[1].textContent;
                    cardBody.innerHTML = '<p class="text-muted mb-1" style="font-size:13px;">PASO ' + (pasoActual+1) + ' de <?= $totalPasos ?></p>' +
                        '<h6 class="mb-1">' + titulo + '</h6>' +
                        '<p class="mb-3" style="font-size:14px; font-weight:600;">' + nombre + '</p>' +
                        '<div class="alert alert-success py-2" style="font-size:14px;"><i class="fas fa-check-circle"></i> Firma registrada</div>';

                    // Avanzar al siguiente paso automáticamente
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
            })
            .catch(() => {
                Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'Verifica tu conexión a internet.' });
            });
        });
    };

    // Finalizar acta
    window.finalizarActa = function() {
        Swal.fire({
            title: 'Finalizar acta',
            html: '<p>Se generará el PDF y el acta quedará <strong>bloqueada</strong>.</p><p>¿Confirmas que toda la información está correcta?</p>',
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

            fetch('<?= site_url('inspecciones/acta-visita/finalizar/') ?>' + actaId, {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                Swal.close();
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Acta finalizada',
                        html: '<p>El PDF ha sido generado exitosamente.</p>',
                        confirmButtonText: 'Ver PDF',
                        showCancelButton: true,
                        cancelButtonText: 'Volver al inicio',
                        confirmButtonColor: '#bd9751',
                    }).then((r) => {
                        if (r.isConfirmed) {
                            window.open('<?= site_url('inspecciones/acta-visita/pdf/') ?>' + actaId, '_blank');
                            window.location.href = '<?= site_url('inspecciones/acta-visita') ?>';
                        } else {
                            window.location.href = '<?= site_url('inspecciones') ?>';
                        }
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.error || 'No se pudo finalizar el acta.' });
                }
            })
            .catch(() => {
                Swal.close();
                Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'Verifica tu conexión a internet.' });
            });
        });
    };

    // Activar primer paso
    document.querySelector('.step-dot[data-step="0"]').classList.add('active');

    // ============ WhatsApp Firma Remota ============
    var pasoActual = 0;
    var totalPasos = <?= $totalPasos ?>;

    // Override irPaso para trackear paso actual
    var _irPasoOriginal = window.irPaso;
    window.irPaso = function(step) {
        pasoActual = step;
        _irPasoOriginal(step);
    };

    document.querySelectorAll('.btn-whatsapp-firma').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var tipo = this.dataset.tipo;
            var tipoLabel = {
                administrador: 'Cliente',
                vigia: 'Vigia SST',
                consultor: 'Consultor'
            }[tipo] || tipo;

            Swal.fire({
                title: 'Enviar enlace de firma',
                html: '<p style="font-size:14px;">Se generara un enlace para que <strong>'
                      + tipoLabel + '</strong> firme desde su celular.<br>'
                      + '<small class="text-muted">El enlace expira en 7 dias.</small></p>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="fab fa-whatsapp"></i> Generar enlace',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#25D366',
            }).then(function(result) {
                if (!result.isConfirmed) return;

                Swal.fire({
                    title: 'Generando enlace...',
                    allowOutsideClick: false,
                    didOpen: function() { Swal.showLoading(); }
                });

                var formData = new FormData();
                formData.append(csrfName, csrfHash);
                formData.append('tipo', tipo);

                fetch('<?= site_url('inspecciones/acta-visita/generar-token-firma/') ?>' + actaId, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (!data.success) {
                        Swal.fire('Error', data.error, 'error');
                        return;
                    }

                    var url   = data.url;
                    var texto = encodeURIComponent(
                        'Hola, por favor firma el acta de visita SST '
                        + 'haciendo clic en este enlace (valido 24 horas):\n'
                        + url
                    );
                    var waUrl = 'https://wa.me/?text=' + texto;

                    Swal.fire({
                        title: 'Enlace generado',
                        html: '<p style="font-size:13px;">Comparte este enlace con el <strong>' + tipoLabel + '</strong>:</p>'
                              + '<div id="swal-url-box" style="background:#f8f9fa;border-radius:8px;padding:10px;'
                              + 'font-size:11px;word-break:break-all;margin-bottom:12px;cursor:pointer;border:1px solid #dee2e6;" title="Click para copiar">'
                              + url + '</div>'
                              + '<div class="d-flex gap-2 justify-content-center">'
                              + '<button type="button" id="btnCopiarEnlace" class="btn btn-sm btn-outline-secondary">'
                              + '<i class="fas fa-copy me-1"></i>Copiar enlace</button>'
                              + '</div>',
                        showCancelButton: true,
                        confirmButtonText: '<i class="fab fa-whatsapp"></i> Abrir WhatsApp',
                        cancelButtonText: 'Cerrar',
                        confirmButtonColor: '#25D366',
                        didOpen: function() {
                            document.getElementById('btnCopiarEnlace').addEventListener('click', function() {
                                navigator.clipboard.writeText(url).then(function() {
                                    var btn = document.getElementById('btnCopiarEnlace');
                                    btn.innerHTML = '<i class="fas fa-check me-1"></i>Copiado!';
                                    btn.classList.remove('btn-outline-secondary');
                                    btn.classList.add('btn-success');
                                    setTimeout(function() {
                                        btn.innerHTML = '<i class="fas fa-copy me-1"></i>Copiar enlace';
                                        btn.classList.remove('btn-success');
                                        btn.classList.add('btn-outline-secondary');
                                    }, 2000);
                                });
                            });
                        }
                    }).then(function(r) {
                        if (r.isConfirmed) window.open(waUrl, '_blank');
                    });
                })
                .catch(function() {
                    Swal.fire('Error', 'Error de conexion', 'error');
                });
            });
        });
    });
});
</script>
