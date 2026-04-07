<?php
$totalRegistrados = count($asistentes);
$idInspeccion = $inspeccion['id'];
?>
<div class="container-fluid px-3 pb-5">

    <!-- Encabezado -->
    <div class="mt-2 mb-3 d-flex justify-content-between align-items-start">
        <div>
            <h6 class="mb-0" style="font-size:15px;">Registro de Asistentes</h6>
            <small class="text-muted"><?= esc($inspeccion['tema'] ?? '') ?> &middot; <?= date('d/m/Y', strtotime($inspeccion['fecha_sesion'])) ?></small>
        </div>
        <span class="badge bg-dark" id="contadorBadge" style="font-size:14px;"><?= $totalRegistrados ?> registrados</span>
    </div>

    <!-- Lista de registrados -->
    <div id="listaRegistrados" class="mb-3" style="<?= $totalRegistrados === 0 ? 'display:none' : '' ?>">
        <div style="font-size:12px; color:#999; margin-bottom:4px;">YA REGISTRADOS</div>
        <div id="listaBody">
            <?php foreach ($asistentes as $a): ?>
            <div class="d-flex justify-content-between align-items-center py-1 px-2 mb-1 rounded" style="background:#f8f9fa; font-size:13px;" id="asist-<?= $a['id'] ?>">
                <div>
                    <strong><?= esc($a['nombre']) ?></strong>
                    <span class="text-muted ms-1"><?= esc($a['cedula']) ?></span>
                    <?php if (!empty($a['firma'])): ?>
                    <i class="fas fa-pen-nib text-success ms-1" title="Firmado"></i>
                    <?php endif; ?>
                </div>
                <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="eliminarAsistente(<?= $a['id'] ?>, this)" title="Eliminar">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Formulario por persona -->
    <div class="card mb-3" id="formCard">
        <div class="card-body">
            <h6 class="card-title mb-3" style="font-size:14px; color:#1c2437; font-weight:700;">
                <i class="fas fa-user-plus me-1"></i>
                Asistente <span id="numAsistente"><?= $totalRegistrados + 1 ?></span>
            </h6>

            <div class="mb-3">
                <label class="form-label">Nombre completo *</label>
                <input type="text" id="asisNombre" class="form-control" placeholder="Nombre completo" autocomplete="off" autocorrect="off">
            </div>
            <div class="mb-3">
                <label class="form-label">Cedula *</label>
                <input type="number" id="asisCedula" class="form-control" placeholder="Numero de cedula" inputmode="numeric">
            </div>
            <div class="mb-3">
                <label class="form-label">Cargo *</label>
                <input type="text" id="asisCargo" class="form-control" placeholder="Cargo o funcion" autocomplete="off">
            </div>

            <!-- Canvas firma -->
            <div class="mb-3">
                <label class="form-label">Firma *</label>
                <div style="border:1px solid #ced4da; border-radius:6px; background:#fff; touch-action:none;">
                    <canvas id="firmaCanvas" style="width:100%; height:140px; display:block; cursor:crosshair;"></canvas>
                </div>
                <div class="d-flex justify-content-between mt-1">
                    <span id="firmaStatus" style="font-size:12px; color:#999;">Firme aqui arriba</span>
                    <button type="button" class="btn btn-sm btn-link text-secondary p-0" onclick="limpiarFirma()">
                        <i class="fas fa-eraser"></i> Limpiar
                    </button>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="button" class="btn btn-pwa py-3" id="btnGuardar" onclick="guardarAsistente()" style="font-size:16px; font-weight:600;">
                    <i class="fas fa-check me-1"></i> Guardar y pasar al siguiente
                </button>
            </div>
        </div>
    </div>

    <!-- Banner pendientes offline -->
    <div id="offlineBanner" class="alert alert-warning d-none mb-3" style="font-size:13px;">
        <i class="fas fa-wifi-slash me-1"></i>
        <span id="offlineBannerText">0 firma(s) pendientes de sincronizar</span>
        <button type="button" class="btn btn-sm btn-warning ms-2" id="btnSyncManual" onclick="syncManual()">
            <i class="fas fa-sync"></i> Sincronizar
        </button>
    </div>

    <!-- Boton finalizar -->
    <div class="mt-3">
        <button type="button" class="btn btn-outline-danger w-100 py-3" id="btnFinalizar" onclick="iniciarFinalizacion()" style="font-size:15px; font-weight:600; <?= $totalRegistrados === 0 ? 'display:none' : '' ?>">
            <i class="fas fa-flag-checkered me-1"></i> Finalizar lista (<span id="countFin"><?= $totalRegistrados ?></span> asistentes)
        </button>
    </div>
</div>

<script src="/js/offline_queue.js"></script>
<script>
const INSPECCION_ID = <?= $idInspeccion ?>;
const STORE_ASISTENTE_URL = '<?= base_url('/inspecciones/registro-asistencia/store-asistente/') ?>' + INSPECCION_ID;
const DELETE_ASISTENTE_URL = '<?= base_url('/inspecciones/registro-asistencia/delete-asistente/') ?>';
const FINALIZAR_URL = '<?= base_url('/inspecciones/registro-asistencia/finalizar/') ?>' + INSPECCION_ID;
const CSRFNAME = '<?= csrf_token() ?>';
let CSRFHASH = '<?= csrf_hash() ?>';
let pendingOfflineCount = 0;

// ── Canvas firma ──
const canvas = document.getElementById('firmaCanvas');
const ctx = canvas.getContext('2d');
let drawing = false;
let firmaVacia = true;

function resizeCanvas() {
    const ratio = window.devicePixelRatio || 1;
    const rect = canvas.getBoundingClientRect();
    canvas.width = rect.width * ratio;
    canvas.height = rect.height * ratio;
    ctx.scale(ratio, ratio);
    ctx.strokeStyle = '#000';
    ctx.lineWidth = 2;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
}
resizeCanvas();

function getPos(e) {
    const rect = canvas.getBoundingClientRect();
    if (e.touches) {
        return { x: e.touches[0].clientX - rect.left, y: e.touches[0].clientY - rect.top };
    }
    return { x: e.clientX - rect.left, y: e.clientY - rect.top };
}

canvas.addEventListener('mousedown', e => { drawing = true; firmaVacia = false; ctx.beginPath(); const p = getPos(e); ctx.moveTo(p.x, p.y); document.getElementById('firmaStatus').textContent = 'Firma capturada'; });
canvas.addEventListener('mousemove', e => { if (!drawing) return; const p = getPos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); });
canvas.addEventListener('mouseup', () => drawing = false);
canvas.addEventListener('mouseleave', () => drawing = false);

canvas.addEventListener('touchstart', e => { if (e.touches.length > 1) return; e.preventDefault(); drawing = true; firmaVacia = false; ctx.beginPath(); const p = getPos(e); ctx.moveTo(p.x, p.y); document.getElementById('firmaStatus').textContent = 'Firma capturada'; }, { passive: false });
canvas.addEventListener('touchmove', e => { if (e.touches.length > 1 || !drawing) return; e.preventDefault(); const p = getPos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); }, { passive: false });
canvas.addEventListener('touchend', () => drawing = false);

function limpiarFirma() {
    const ratio = window.devicePixelRatio || 1;
    const rect = canvas.getBoundingClientRect();
    ctx.clearRect(0, 0, rect.width * ratio, rect.height * ratio);
    firmaVacia = true;
    document.getElementById('firmaStatus').textContent = 'Firme aqui arriba';
}

// ── Guardar asistente ──
function guardarAsistente() {
    const nombre = document.getElementById('asisNombre').value.trim();
    const cedula = document.getElementById('asisCedula').value.trim();
    const cargo = document.getElementById('asisCargo').value.trim();
    const faltantes = [];
    if (!nombre) faltantes.push('Nombre completo');
    if (!cedula) faltantes.push('Cedula');
    if (!cargo) faltantes.push('Cargo');
    if (firmaVacia) faltantes.push('Firma');
    if (faltantes.length > 0) {
        Swal.fire({ icon: 'warning', title: 'Campos obligatorios', html: 'Faltan: <strong>' + faltantes.join(', ') + '</strong>', confirmButtonColor: '#bd9751' });
        return;
    }

    const firmaBase64 = canvas.toDataURL('image/png');

    const btn = document.getElementById('btnGuardar');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Guardando...';

    const formData = new FormData();
    formData.append('nombre', nombre);
    formData.append('cedula', cedula);
    formData.append('cargo', cargo);
    formData.append('firma', firmaBase64);
    formData.append(CSRFNAME, CSRFHASH);

    fetch(STORE_ASISTENTE_URL, { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            CSRFHASH = data.csrf_hash || CSRFHASH;
            if (!data.success) {
                Swal.fire({ icon: 'error', title: 'Error', text: data.error || 'No se pudo guardar', confirmButtonColor: '#bd9751' });
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check me-1"></i> Guardar y pasar al siguiente';
                return;
            }

            const lista = document.getElementById('listaBody');
            const div = document.createElement('div');
            div.className = 'd-flex justify-content-between align-items-center py-1 px-2 mb-1 rounded';
            div.id = 'asist-' + data.id_asistente;
            div.style = 'background:#f8f9fa; font-size:13px;';
            div.innerHTML = '<div><strong>' + escHtml(nombre) + '</strong><span class="text-muted ms-1">' + escHtml(cedula) + '</span><i class="fas fa-pen-nib text-success ms-1"></i></div>'
                + '<button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="eliminarAsistente(' + data.id_asistente + ', this)"><i class="fas fa-times"></i></button>';
            lista.appendChild(div);

            document.getElementById('listaRegistrados').style.display = '';
            const total = data.total;
            document.getElementById('contadorBadge').textContent = total + ' registrados';
            document.getElementById('numAsistente').textContent = total + 1;
            document.getElementById('countFin').textContent = total;
            document.getElementById('btnFinalizar').style.display = '';

            document.getElementById('asisNombre').value = '';
            document.getElementById('asisCedula').value = '';
            document.getElementById('asisCargo').value = '';
            limpiarFirma();
            document.getElementById('asisNombre').focus();

            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check me-1"></i> Guardar y pasar al siguiente';
        })
        .catch(async () => {
            try {
                await OfflineQueue.add({
                    type: 'asistente',
                    url: STORE_ASISTENTE_URL,
                    id_asistencia: INSPECCION_ID,
                    payload: { nombre, cedula, cargo, firma: firmaBase64, [CSRFNAME]: CSRFHASH },
                    meta: { nombre, cedula }
                });
                await OfflineQueue.requestSync();
                pendingOfflineCount++;
                updateOfflineBanner();

                const lista = document.getElementById('listaBody');
                const div = document.createElement('div');
                div.className = 'd-flex justify-content-between align-items-center py-1 px-2 mb-1 rounded offline-pending';
                div.style = 'background:#fff3cd; font-size:13px;';
                div.innerHTML = '<div><strong>' + escHtml(nombre) + '</strong><span class="text-muted ms-1">' + escHtml(cedula) + '</span><i class="fas fa-clock text-warning ms-1" title="Pendiente sync"></i></div>';
                lista.appendChild(div);
                document.getElementById('listaRegistrados').style.display = '';

                Swal.fire({ icon: 'info', title: 'Guardado offline', html: 'Sin conexion. La firma se guardo localmente y se enviara automaticamente cuando vuelva el internet.', confirmButtonColor: '#bd9751' });

                document.getElementById('asisNombre').value = '';
                document.getElementById('asisCedula').value = '';
                document.getElementById('asisCargo').value = '';
                limpiarFirma();
                document.getElementById('asisNombre').focus();
            } catch (dbErr) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo guardar la firma. Intente de nuevo.', confirmButtonColor: '#bd9751' });
            }
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check me-1"></i> Guardar y pasar al siguiente';
        });
}

// ── Eliminar asistente ──
function eliminarAsistente(idAsistente, btn) {
    Swal.fire({
        icon: 'question',
        title: 'Eliminar este asistente?',
        showCancelButton: true,
        confirmButtonText: 'Si, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
    }).then(r => {
        if (!r.isConfirmed) return;
        const formData = new FormData();
        formData.append(CSRFNAME, CSRFHASH);
        fetch(DELETE_ASISTENTE_URL + idAsistente, { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                CSRFHASH = data.csrf_hash || CSRFHASH;
                if (!data.success) return;
                document.getElementById('asist-' + idAsistente).remove();
                const total = data.total;
                document.getElementById('contadorBadge').textContent = total + ' registrados';
                document.getElementById('numAsistente').textContent = total + 1;
                document.getElementById('countFin').textContent = total;
                if (total === 0) {
                    document.getElementById('listaRegistrados').style.display = 'none';
                    document.getElementById('btnFinalizar').style.display = 'none';
                }
            });
    });
}

// ── Finalizar con doble validacion ──
function iniciarFinalizacion() {
    const total = parseInt(document.getElementById('countFin').textContent);
    if (total === 0) {
        Swal.fire({ icon: 'warning', title: 'Sin asistentes', text: 'Debe registrar al menos un asistente.', confirmButtonColor: '#bd9751' });
        return;
    }

    function randOp() {
        const ops = [
            () => { const a = Math.floor(Math.random()*20)+1, b = Math.floor(Math.random()*20)+1; return { q: a+' + '+b, r: a+b }; },
            () => { const a = Math.floor(Math.random()*20)+5, b = Math.floor(Math.random()*a)+1; return { q: a+' - '+b, r: a-b }; },
            () => { const a = Math.floor(Math.random()*9)+2, b = Math.floor(Math.random()*9)+2; return { q: a+' x '+b, r: a*b }; },
        ];
        return ops[Math.floor(Math.random()*ops.length)]();
    }

    const op1 = randOp();
    Swal.fire({
        title: 'Validacion 1 de 2',
        html: '<p>Esta seguro de finalizar con <strong>' + total + ' asistente(s)</strong>?</p>'
            + '<p style="font-size:22px; font-weight:bold; margin:12px 0;">Cuanto es ' + op1.q + '?</p>',
        input: 'number',
        inputPlaceholder: 'Resultado',
        showCancelButton: true,
        confirmButtonText: 'Continuar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#bd9751',
        inputValidator: val => {
            if (!val) return 'Ingrese un numero';
            if (parseInt(val) !== op1.r) return 'Respuesta incorrecta, intente de nuevo';
        }
    }).then(r1 => {
        if (!r1.isConfirmed) return;
        const op2 = randOp();
        Swal.fire({
            title: 'Validacion 2 de 2',
            html: '<p style="font-size:22px; font-weight:bold; margin:12px 0;">Cuanto es ' + op2.q + '?</p>',
            input: 'number',
            inputPlaceholder: 'Resultado',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-flag-checkered"></i> Finalizar ahora',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545',
            inputValidator: val => {
                if (!val) return 'Ingrese un numero';
                if (parseInt(val) !== op2.r) return 'Respuesta incorrecta, intente de nuevo';
            }
        }).then(r2 => {
            if (!r2.isConfirmed) return;
            const formData = new FormData();
            formData.append(CSRFNAME, CSRFHASH);
            fetch(FINALIZAR_URL, { method: 'POST', body: formData })
                .then(r => {
                    window.location.href = '<?= base_url('/inspecciones/registro-asistencia/view/') ?>' + INSPECCION_ID;
                });
        });
    });
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── Offline sync helpers ──
function updateOfflineBanner() {
    const banner = document.getElementById('offlineBanner');
    const text = document.getElementById('offlineBannerText');
    if (pendingOfflineCount > 0) {
        banner.classList.remove('d-none');
        text.textContent = pendingOfflineCount + ' firma(s) pendientes de sincronizar';
    } else {
        banner.classList.add('d-none');
    }
}

async function syncManual() {
    const btn = document.getElementById('btnSyncManual');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    try {
        const result = await OfflineQueue.syncAll();
        if (result.synced > 0) {
            Swal.fire({ icon: 'success', title: result.synced + ' firma(s) sincronizada(s)', text: 'Recargando...', timer: 2000, showConfirmButton: false });
            setTimeout(() => window.location.reload(), 2000);
        } else if (result.failed > 0) {
            Swal.fire({ icon: 'warning', title: 'Sin conexion', text: 'Aun no hay internet.', confirmButtonColor: '#bd9751' });
        }
    } catch (e) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo sincronizar.', confirmButtonColor: '#bd9751' });
    }
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-sync"></i> Sincronizar';
}

(async function checkOfflinePending() {
    try {
        const items = await OfflineQueue.getByAsistencia(INSPECCION_ID);
        pendingOfflineCount = items.length;
        updateOfflineBanner();
    } catch(e) {}
})();

OfflineQueue.startOnlineListener(function(result) {
    if (result.synced > 0) {
        Swal.fire({ icon: 'success', title: 'Conexion restaurada', html: result.synced + ' firma(s) sincronizada(s).<br>Recargando...', timer: 2500, showConfirmButton: false });
        setTimeout(() => window.location.reload(), 2500);
    }
});

if ('serviceWorker' in navigator) {
    navigator.serviceWorker.addEventListener('message', function(event) {
        if (event.data && event.data.type === 'sync-firmas-complete') {
            Swal.fire({ icon: 'success', title: 'Sincronizado', html: event.data.synced + ' firma(s) enviada(s).<br>Recargando...', timer: 2500, showConfirmButton: false });
            setTimeout(() => window.location.reload(), 2500);
        }
    });
}
</script>
