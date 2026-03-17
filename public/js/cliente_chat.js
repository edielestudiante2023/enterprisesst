/**
 * Chat Otto - Modo Cliente (solo lectura, sin confirmaciones)
 */

const BASE_URL = window.location.origin + '/';
const API_URL  = BASE_URL + 'cliente/chat/api';
const OTTO_IMG = BASE_URL + 'img/otto/otto.png';

let sesionChat = crypto.randomUUID ? crypto.randomUUID() : Date.now().toString(36) + Math.random().toString(36).substr(2);
let historial  = [];
let enviando   = false;

const chatMessages    = document.getElementById('chatMessages');
const inputMsg        = document.getElementById('inputMsg');
const btnSend         = document.getElementById('btnSend');
const typingIndicator = document.getElementById('typingIndicator');
const welcomeMessage  = document.getElementById('welcomeMessage');

if (typeof marked !== 'undefined') {
    marked.setOptions({ breaks: true, gfm: true });
}

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

        if (data.tipo === 'resultado' && data.datos?.length > 0) {
            agregarMensajeConTabla(data.mensaje, data.datos);
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

// ─── Render ──────────────────────────────────────────────────────
function crearEnvolturaAgente() {
    const div = document.createElement('div');
    div.className = 'message agent';
    div.innerHTML = `
        <div class="msg-avatar"><img src="${OTTO_IMG}" alt="Otto"></div>
        <div class="msg-bubble"></div>`;
    return div;
}

function agregarMensajeAgente(texto) {
    const wrap   = crearEnvolturaAgente();
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

function agregarMensajeConTabla(texto, datos) {
    const wrap   = crearEnvolturaAgente();
    const bubble = wrap.querySelector('.msg-bubble');

    let html = (typeof marked !== 'undefined' ? marked.parse(texto || '') : escHtml(texto));

    if (datos.length > 0) {
        const cols = Object.keys(datos[0]);
        html += `<div class="data-table-wrapper"><table class="data-table">`;
        html += `<thead><tr>${cols.map(c => `<th>${escHtml(c)}</th>`).join('')}</tr></thead><tbody>`;
        datos.forEach(row => {
            html += `<tr>${cols.map(c => `<td title="${escHtml(String(row[c] ?? ''))}">${escHtml(String(row[c] ?? 'NULL'))}</td>`).join('')}</tr>`;
        });
        html += `</tbody></table></div>`;
        if (datos.length >= 50) html += `<div style="font-size:0.72rem;color:#888;margin-top:4px;">Máx. 50 filas</div>`;
    }

    html += `<div class="msg-time">${horaActual()}</div>`;
    bubble.innerHTML = html;
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

function handleKeyDown(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); enviarMensaje(); }
}

function autoResize(el) {
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 120) + 'px';
}

// ─── Finalizar sesión / Email ─────────────────────────────────────
const INACTIVITY_MS  = 10 * 60 * 1000;
let inactivityTimer  = null;
let sessionEmailSent = false;

function sendSessionEmail() {
    if (sessionEmailSent || historial.length === 0) return;
    sessionEmailSent = true;
    const payload = JSON.stringify({ history: historial });
    if (navigator.sendBeacon) {
        const blob = new Blob([payload], { type: 'application/json' });
        navigator.sendBeacon(BASE_URL + 'cliente/chat/end-session', blob);
    } else {
        fetch(BASE_URL + 'cliente/chat/end-session', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: payload,
            keepalive: true
        });
    }
}

function resetInactivityTimer() {
    sessionEmailSent = false;
    clearTimeout(inactivityTimer);
    inactivityTimer = setTimeout(sendSessionEmail, INACTIVITY_MS);
}

['keydown', 'mousedown', 'touchstart', 'click'].forEach(function(evt) {
    document.addEventListener(evt, resetInactivityTimer, { passive: true });
});

window.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'hidden') sendSessionEmail();
});
window.addEventListener('beforeunload', sendSessionEmail);

resetInactivityTimer();

async function finalizarConversacion() {
    if (historial.length === 0) {
        window.location.href = BASE_URL + 'client/dashboard';
        return;
    }
    if (!confirm('¿Finalizar la conversación con Otto?')) return;
    sendSessionEmail();
    window.location.href = BASE_URL + 'client/dashboard';
}
