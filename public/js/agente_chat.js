/**
 * Agente Virtual de Chat - EnterpriseSST (consultor/admin)
 * marked.js para Markdown, avatares por mensaje, suggestion chips
 */

const BASE_URL = document.querySelector('link[rel="manifest"]')?.href?.replace('/agente-chat/manifest.json', '') || '';
const API_URL  = BASE_URL + '/agente-chat/api';
const OTTO_IMG = BASE_URL + '/img/otto/otto.png';

// ─── Estado ──────────────────────────────────────────────────────
let sesionChat = crypto.randomUUID ? crypto.randomUUID() : Date.now().toString(36) + Math.random().toString(36).substr(2);
let historial  = [];
let enviando   = false;

// ─── DOM refs ────────────────────────────────────────────────────
const chatMessages    = document.getElementById('chatMessages');
const inputMsg        = document.getElementById('inputMsg');
const btnSend         = document.getElementById('btnSend');
const typingIndicator = document.getElementById('typingIndicator');
const welcomeMessage  = document.getElementById('welcomeMessage');

// ─── marked config ───────────────────────────────────────────────
if (typeof marked !== 'undefined') {
    marked.setOptions({ breaks: true, gfm: true });
}

// ─── Enviar mensaje ──────────────────────────────────────────────
async function enviarMensaje() {
    const texto = inputMsg.value.trim();
    if (!texto || enviando) return;

    ocultarWelcome();
    enviando = true;
    btnSend.disabled = true;
    inputMsg.value = '';
    inputMsg.style.height = 'auto';

    agregarMensaje(texto, 'user');
    historial.push({ role: 'user', content: texto });
    mostrarTyping(true);

    try {
        const res = await fetch(API_URL + '/mensaje', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ mensaje: texto, historial: historial.slice(-10), sesion_chat: sesionChat })
        });

        const data = await res.json();
        mostrarTyping(false);

        if (data.tipo === 'confirmacion') {
            mostrarConfirmacion(data);
        } else if (data.tipo === 'resultado' && data.datos?.length > 0) {
            agregarMensajeConTabla(data.mensaje, data.datos, data.sql);
            historial.push({ role: 'assistant', content: data.mensaje });
        } else if (data.tipo === 'modificacion') {
            agregarMensajeAgente(data.mensaje);
            if (data.sql) agregarSQL(data.sql);
            historial.push({ role: 'assistant', content: data.mensaje });
        } else if (data.tipo === 'error' || data.tipo === 'rechazado') {
            agregarMensaje(data.mensaje, 'error');
            historial.push({ role: 'assistant', content: data.mensaje });
        } else {
            agregarMensajeAgente(data.mensaje || 'Sin respuesta');
            historial.push({ role: 'assistant', content: data.mensaje || '' });
        }
    } catch (err) {
        mostrarTyping(false);
        agregarMensaje('Error de conexión: ' + err.message, 'error');
    }

    enviando = false;
    btnSend.disabled = false;
    inputMsg.focus();
}

function usarSugerencia(el) {
    inputMsg.value = el.textContent.trim();
    enviarMensaje();
}

// ─── Confirmación INSERT/UPDATE/DELETE ───────────────────────────
function mostrarConfirmacion(data) {
    const isDelete = data.operacion === 'DELETE';
    const wrap = crearEnvolturaAgente();
    const bubble = wrap.querySelector('.msg-bubble');

    let html = `<b>⚠ Operación: ${data.operacion}</b>`;
    if (data.explicacion) html += `<br><span style="font-size:0.85rem">${escHtml(data.explicacion)}</span>`;
    html += `<div class="sql-block">${escHtml(data.sql)}</div>`;
    html += `<p style="font-size:0.82rem">Tablas: <b>${(data.tablas || []).join(', ')}</b></p>`;
    html += `<div class="confirm-area">`;

    if (isDelete) {
        const v = data.verificacion_aritmetica;
        html += `<span style="font-size:0.82rem"><b>Verificación:</b> ${escHtml(v.pregunta)}</span>`;
        html += `<input type="number" class="aritm-input" id="respAritmetica" placeholder="?">`;
        html += `<button class="btn-delete-op" onclick="confirmarOp(this,'${btoa(data.sql)}','DELETE',${v.respuesta_correcta})">`;
        html += `<i class="fas fa-trash me-1"></i>Eliminar</button>`;
    } else {
        html += `<button class="btn-confirm" onclick="confirmarOp(this,'${btoa(data.sql)}','${data.operacion}')">`;
        html += `<i class="fas fa-check me-1"></i>Confirmar ${data.operacion}</button>`;
    }

    html += `<button class="btn-cancel-op" onclick="cancelarOp(this)"><i class="fas fa-times me-1"></i>Cancelar</button>`;
    html += `</div>`;
    html += `<div class="msg-time">${horaActual()}</div>`;

    bubble.innerHTML = html;
    bubble.closest('.message').classList.add('confirm');
    chatMessages.insertBefore(wrap, typingIndicator);
    scrollAbajo();
}

async function confirmarOp(btn, sqlB64, tipoOp, respCorrecta) {
    const sql = atob(sqlB64);
    const area = btn.closest('.confirm-area');

    let payload = { sql, tipo_operacion: tipoOp, sesion_chat: sesionChat, mensaje_original: '' };

    if (tipoOp === 'DELETE') {
        const input = area.parentElement.querySelector('#respAritmetica');
        const respUsuario = parseInt(input?.value || '0');
        payload.respuesta_aritmetica = respUsuario;
        payload.respuesta_correcta = respCorrecta;
    }

    area.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ejecutando...';

    try {
        const res = await fetch(API_URL + '/confirmar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        area.innerHTML = data.success
            ? `<span style="color:#198754"><i class="fas fa-check-circle me-1"></i>${escHtml(data.mensaje)}</span>`
            : `<span style="color:#dc3545"><i class="fas fa-times-circle me-1"></i>${escHtml(data.mensaje)}</span>`;
        if (data.success) historial.push({ role: 'assistant', content: data.mensaje });
    } catch (err) {
        area.innerHTML = `<span style="color:#dc3545">Error: ${escHtml(err.message)}</span>`;
    }
    scrollAbajo();
}

function cancelarOp(btn) {
    const area = btn.closest('.confirm-area');
    area.innerHTML = '<span style="color:#6c757d"><i class="fas fa-ban me-1"></i>Operación cancelada</span>';
}

// ─── Helpers de renderizado ───────────────────────────────────────
function crearEnvolturaAgente() {
    const div = document.createElement('div');
    div.className = 'message agent';
    div.innerHTML = `
        <div class="msg-avatar">
            <img src="${OTTO_IMG}" alt="Otto">
        </div>
        <div class="msg-bubble"></div>`;
    return div;
}

function agregarMensajeAgente(texto) {
    const wrap = crearEnvolturaAgente();
    const bubble = wrap.querySelector('.msg-bubble');
    bubble.innerHTML = (typeof marked !== 'undefined' ? marked.parse(texto || '') : escHtml(texto))
        + `<div class="msg-time">${horaActual()}</div>`;
    chatMessages.insertBefore(wrap, typingIndicator);
    scrollAbajo();
}

function agregarMensaje(texto, tipo) {
    if (tipo === 'agent') { agregarMensajeAgente(texto); return; }

    const div = document.createElement('div');

    if (tipo === 'user') {
        div.className = 'message user';
        div.innerHTML = `
            <div class="msg-bubble">
                ${escHtml(texto)}
                <div class="msg-time">${horaActual()}</div>
            </div>
            <div class="msg-avatar"><i class="fas fa-user"></i></div>`;
    } else {
        // error
        div.className = 'message error';
        div.innerHTML = `
            <div class="msg-avatar"><img src="${OTTO_IMG}" alt="Otto"></div>
            <div class="msg-bubble">
                <i class="fas fa-exclamation-circle me-1"></i>${escHtml(texto)}
                <div class="msg-time">${horaActual()}</div>
            </div>`;
    }

    chatMessages.insertBefore(div, typingIndicator);
    scrollAbajo();
}

function agregarMensajeConTabla(texto, datos, sql) {
    const wrap = crearEnvolturaAgente();
    const bubble = wrap.querySelector('.msg-bubble');

    let html = (typeof marked !== 'undefined' ? marked.parse(texto || '') : escHtml(texto));

    if (sql) html += `<div class="sql-block">${escHtml(sql)}</div>`;

    if (datos.length > 0) {
        const cols = Object.keys(datos[0]);
        html += `<div class="data-table-wrapper"><table class="data-table">`;
        html += `<thead><tr>${cols.map(c => `<th>${escHtml(c)}</th>`).join('')}</tr></thead><tbody>`;
        datos.forEach(row => {
            html += `<tr>${cols.map(c => `<td title="${escHtml(String(row[c] ?? ''))}">${escHtml(String(row[c] ?? 'NULL'))}</td>`).join('')}</tr>`;
        });
        html += `</tbody></table></div>`;
        if (datos.length >= 100) html += `<div style="font-size:0.72rem;color:#888;margin-top:4px;">Máx. 100 filas</div>`;
    }

    html += `<div class="msg-time">${horaActual()}</div>`;
    bubble.innerHTML = html;
    chatMessages.insertBefore(wrap, typingIndicator);
    scrollAbajo();
}

function agregarSQL(sql) {
    const wrap = crearEnvolturaAgente();
    wrap.querySelector('.msg-bubble').innerHTML = `<div class="sql-block">${escHtml(sql)}</div><div class="msg-time">${horaActual()}</div>`;
    chatMessages.insertBefore(wrap, typingIndicator);
    scrollAbajo();
}

// ─── Utilidades ──────────────────────────────────────────────────
function escHtml(text) {
    const d = document.createElement('div');
    d.textContent = String(text ?? '');
    return d.innerHTML;
}

function horaActual() {
    return new Date().toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit', hour12: true });
}

function scrollAbajo() {
    requestAnimationFrame(() => { chatMessages.scrollTop = chatMessages.scrollHeight; });
}

function mostrarTyping(show) {
    typingIndicator.style.display = show ? 'flex' : 'none';
    if (show) scrollAbajo();
}

function ocultarWelcome() {
    if (welcomeMessage) welcomeMessage.style.display = 'none';
}

// ─── Input ───────────────────────────────────────────────────────
function handleKeyDown(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); enviarMensaje(); }
}

function autoResize(el) {
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 120) + 'px';
}

// ─── Schema panel ────────────────────────────────────────────────
function toggleSchema() {
    document.getElementById('schemaPanel').classList.toggle('open');
    document.getElementById('schemaOverlay').classList.toggle('open');
}

function filtrarTablas() {
    const q = document.getElementById('searchTabla').value.toLowerCase();
    document.querySelectorAll('#tablesList .schema-table-item').forEach(el => {
        el.style.display = el.querySelector('.tbl-name').textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}

function insertarTabla(nombre) {
    inputMsg.value += nombre + ' ';
    inputMsg.focus();
    toggleSchema();
}

// ─── Nueva sesión ────────────────────────────────────────────────
function nuevaSesion() {
    if (!confirm('¿Iniciar nueva sesión? Se limpiará el historial.')) return;
    sesionChat = crypto.randomUUID ? crypto.randomUUID() : Date.now().toString(36) + Math.random().toString(36).substr(2);
    historial  = [];
    document.querySelectorAll('#chatMessages .message').forEach(m => m.remove());
    if (welcomeMessage) welcomeMessage.style.display = '';
}
