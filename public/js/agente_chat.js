/**
 * Agente Virtual de Chat - EnterpriseSST
 * Frontend PWA para consulta/edición de BD via lenguaje natural
 */

const BASE_URL = document.querySelector('link[rel="manifest"]')?.href?.replace('/agente-chat/manifest.json', '') || '';
const API_URL = BASE_URL + '/agente-chat/api';

// ─── Estado ────────────────────────────────────────────────────
let sesionChat = crypto.randomUUID ? crypto.randomUUID() : Date.now().toString(36) + Math.random().toString(36).substr(2);
let historial = [];
let enviando = false;

// ─── Refs DOM ──────────────────────────────────────────────────
const chatMessages = document.getElementById('chatMessages');
const inputMsg = document.getElementById('inputMsg');
const btnSend = document.getElementById('btnSend');
const typingIndicator = document.getElementById('typingIndicator');
const statusText = document.getElementById('statusText');

// ─── Enviar Mensaje ────────────────────────────────────────────
async function enviarMensaje() {
    const texto = inputMsg.value.trim();
    if (!texto || enviando) return;

    enviando = true;
    btnSend.disabled = true;
    inputMsg.value = '';
    inputMsg.style.height = 'auto';

    // Mostrar mensaje del usuario
    agregarMensaje(texto, 'user');
    historial.push({ role: 'user', content: texto });

    // Mostrar typing
    mostrarTyping(true);
    statusText.textContent = 'Pensando...';

    try {
        const res = await fetch(API_URL + '/mensaje', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                mensaje: texto,
                historial: historial.slice(-10),
                sesion_chat: sesionChat
            })
        });

        const data = await res.json();
        mostrarTyping(false);
        statusText.textContent = 'En línea';

        if (data.tipo === 'confirmacion') {
            mostrarConfirmacion(data);
        } else if (data.tipo === 'resultado' && data.datos && data.datos.length > 0) {
            agregarMensajeConTabla(data.mensaje, data.datos, data.sql);
            historial.push({ role: 'assistant', content: data.mensaje });
        } else if (data.tipo === 'modificacion') {
            agregarMensaje(data.mensaje, 'agent');
            if (data.sql) agregarSQL(data.sql);
            historial.push({ role: 'assistant', content: data.mensaje });
        } else if (data.tipo === 'error' || data.tipo === 'rechazado') {
            agregarMensaje(data.mensaje, 'error');
            historial.push({ role: 'assistant', content: data.mensaje });
        } else {
            // Texto normal
            agregarMensaje(data.mensaje || 'Sin respuesta', data.success ? 'agent' : 'error');
            historial.push({ role: 'assistant', content: data.mensaje || '' });
        }
    } catch (err) {
        mostrarTyping(false);
        statusText.textContent = 'En línea';
        agregarMensaje('Error de conexión: ' + err.message, 'error');
    }

    enviando = false;
    btnSend.disabled = false;
    inputMsg.focus();
}

// ─── Confirmar operación (INSERT/UPDATE/DELETE) ────────────────
function mostrarConfirmacion(data) {
    const isDelete = data.operacion === 'DELETE';

    let html = `<b>⚠ Operación: ${data.operacion}</b><br>`;
    html += `<div class="sql-block">${escapeHtml(data.sql)}</div>`;
    if (data.explicacion) html += `<p>${formatearTexto(data.explicacion)}</p>`;
    html += `<p>Tablas: <b>${(data.tablas || []).join(', ')}</b></p>`;

    html += `<div class="confirm-box">`;

    if (isDelete) {
        const v = data.verificacion_aritmetica;
        html += `<p class="mb-2"><b>Doble verificación:</b> ${v.pregunta}</p>`;
        html += `<div class="d-flex align-items-center gap-2 mb-2">`;
        html += `<input type="number" class="form-control" id="respAritmetica" placeholder="?">`;
        html += `</div>`;
        html += `<button class="btn btn-danger btn-sm me-2" onclick="confirmarOp(this, '${btoa(data.sql)}', '${data.operacion}', ${v.respuesta_correcta})">`;
        html += `<i class="bi bi-trash"></i> Eliminar</button>`;
    } else {
        html += `<button class="btn btn-warning btn-sm me-2" onclick="confirmarOp(this, '${btoa(data.sql)}', '${data.operacion}')">`;
        html += `<i class="bi bi-check-lg"></i> Confirmar ${data.operacion}</button>`;
    }

    html += `<button class="btn btn-secondary btn-sm" onclick="cancelarOp(this)">`;
    html += `<i class="bi bi-x-lg"></i> Cancelar</button>`;
    html += `</div>`;

    const div = document.createElement('div');
    div.className = 'msg msg-confirm';
    div.innerHTML = html + `<div class="timestamp">${horaActual()}</div>`;
    chatMessages.insertBefore(div, typingIndicator);
    scrollAbajo();
}

async function confirmarOp(btn, sqlB64, tipoOp, respCorrecta) {
    const sql = atob(sqlB64);
    const box = btn.closest('.confirm-box');

    let payload = {
        sql,
        tipo_operacion: tipoOp,
        sesion_chat: sesionChat,
        mensaje_original: ''
    };

    if (tipoOp === 'DELETE') {
        const input = box.querySelector('#respAritmetica');
        const respUsuario = parseInt(input?.value || '0');
        payload.respuesta_aritmetica = respUsuario;
        payload.respuesta_correcta = respCorrecta;
    }

    // Deshabilitar botones
    box.querySelectorAll('button').forEach(b => b.disabled = true);
    box.innerHTML = '<div class="text-center"><i class="bi bi-hourglass-split"></i> Ejecutando...</div>';

    try {
        const res = await fetch(API_URL + '/confirmar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        });

        const data = await res.json();

        if (data.success) {
            box.innerHTML = `<div class="text-success"><i class="bi bi-check-circle"></i> ${data.mensaje}</div>`;
            historial.push({ role: 'assistant', content: data.mensaje });
        } else {
            box.innerHTML = `<div class="text-danger"><i class="bi bi-x-circle"></i> ${data.mensaje}</div>`;
        }
    } catch (err) {
        box.innerHTML = `<div class="text-danger">Error: ${err.message}</div>`;
    }
    scrollAbajo();
}

function cancelarOp(btn) {
    const box = btn.closest('.confirm-box');
    box.innerHTML = '<div class="text-muted"><i class="bi bi-slash-circle"></i> Operación cancelada</div>';
}

// ─── Render helpers ────────────────────────────────────────────
function agregarMensaje(texto, tipo) {
    const div = document.createElement('div');
    const clases = {
        user: 'msg-user',
        agent: 'msg-agent',
        error: 'msg-error',
        system: 'msg-system'
    };
    div.className = `msg ${clases[tipo] || 'msg-agent'}`;
    div.innerHTML = formatearTexto(texto) + `<div class="timestamp">${horaActual()}</div>`;
    chatMessages.insertBefore(div, typingIndicator);
    scrollAbajo();
}

function agregarMensajeConTabla(texto, datos, sql) {
    const div = document.createElement('div');
    div.className = 'msg msg-agent';

    let html = formatearTexto(texto);

    if (sql) {
        html += `<div class="sql-block">${escapeHtml(sql)}</div>`;
    }

    if (datos.length > 0) {
        const cols = Object.keys(datos[0]);
        html += `<div class="msg-table-wrapper"><table class="msg-table">`;
        html += `<thead><tr>${cols.map(c => `<th>${escapeHtml(c)}</th>`).join('')}</tr></thead>`;
        html += `<tbody>`;
        datos.forEach(row => {
            html += `<tr>${cols.map(c => `<td title="${escapeHtml(String(row[c] ?? ''))}">${escapeHtml(String(row[c] ?? 'NULL'))}</td>`).join('')}</tr>`;
        });
        html += `</tbody></table></div>`;

        if (datos.length >= 100) {
            html += `<div class="text-muted" style="font-size:0.75rem;margin-top:4px;">Mostrando máx. 100 filas</div>`;
        }
    }

    html += `<div class="timestamp">${horaActual()}</div>`;
    div.innerHTML = html;
    chatMessages.insertBefore(div, typingIndicator);
    scrollAbajo();
}

function agregarSQL(sql) {
    const div = document.createElement('div');
    div.className = 'msg msg-agent';
    div.innerHTML = `<div class="sql-block">${escapeHtml(sql)}</div><div class="timestamp">${horaActual()}</div>`;
    chatMessages.insertBefore(div, typingIndicator);
    scrollAbajo();
}

// ─── Utilidades ────────────────────────────────────────────────
function formatearTexto(text) {
    if (!text) return '';
    // Bold **text**
    text = text.replace(/\*\*(.*?)\*\*/g, '<b>$1</b>');
    // Italic *text*
    text = text.replace(/(?<!\*)\*(?!\*)(.*?)(?<!\*)\*(?!\*)/g, '<i>$1</i>');
    // Line breaks
    text = text.replace(/\n/g, '<br>');
    return text;
}

function escapeHtml(text) {
    const d = document.createElement('div');
    d.textContent = text;
    return d.innerHTML;
}

function horaActual() {
    return new Date().toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit', hour12: true });
}

function scrollAbajo() {
    requestAnimationFrame(() => {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    });
}

function mostrarTyping(show) {
    typingIndicator.style.display = show ? 'block' : 'none';
    if (show) scrollAbajo();
}

// ─── Input handling ────────────────────────────────────────────
function handleKeyDown(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        enviarMensaje();
    }
}

function autoResize(el) {
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 120) + 'px';
}

// ─── Tables panel ──────────────────────────────────────────────
function toggleTablesPanel() {
    document.getElementById('tablesPanel').classList.toggle('open');
    document.getElementById('overlay').classList.toggle('active');
}

function filtrarTablas() {
    const q = document.getElementById('searchTabla').value.toLowerCase();
    document.querySelectorAll('#tablesList .table-item').forEach(el => {
        el.style.display = el.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}

function insertarTabla(nombre) {
    inputMsg.value += nombre + ' ';
    inputMsg.focus();
    toggleTablesPanel();
}

// ─── Nueva sesión ──────────────────────────────────────────────
function nuevaSesion() {
    if (!confirm('¿Iniciar nueva sesión? Se limpiará el historial actual.')) return;
    sesionChat = crypto.randomUUID ? crypto.randomUUID() : Date.now().toString(36) + Math.random().toString(36).substr(2);
    historial = [];

    // Limpiar mensajes (dejar solo el welcome)
    const msgs = chatMessages.querySelectorAll('.msg');
    msgs.forEach(m => {
        if (m !== typingIndicator) m.remove();
    });

    agregarMensaje('¡Hola! Soy **Otto**, tu asistente virtual de SST. ¿Cómo puedo ayudarte?', 'agent');
}
