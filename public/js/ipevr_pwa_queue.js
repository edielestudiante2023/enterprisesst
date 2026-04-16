/**
 * IPEVR PWA - Cola offline para autosave de filas.
 *
 * Usa localStorage como cola sencilla (patron similar a offline_queue.js).
 * Al volver online, reintenta enviar las filas encoladas.
 */
(function () {
  const KEY = 'ipevr_pwa_queue_v1';

  function read() {
    try { return JSON.parse(localStorage.getItem(KEY) || '[]'); }
    catch (e) { return []; }
  }
  function write(arr) { localStorage.setItem(KEY, JSON.stringify(arr)); }

  function enqueue(payload) {
    const q = read();
    q.push({ at: Date.now(), payload });
    write(q);
  }
  function size() { return read().length; }
  function clear() { write([]); }

  async function flush(endpointUrl) {
    const q = read();
    if (!q.length) return { sent: 0, fail: 0 };
    let sent = 0, fail = 0;
    const keep = [];
    for (const item of q) {
      try {
        const fd = new FormData();
        Object.entries(item.payload).forEach(([k, v]) => fd.append(k, v));
        const r = await fetch(endpointUrl, { method: 'POST', body: fd });
        const j = await r.json();
        if (j.ok) sent++; else { fail++; keep.push(item); }
      } catch (e) {
        fail++;
        keep.push(item);
      }
    }
    write(keep);
    return { sent, fail };
  }

  window.IPEVR_QUEUE = { enqueue, size, clear, flush, read };

  window.addEventListener('online', () => {
    if (window.IPEVR_PWA_ENDPOINT) {
      flush(window.IPEVR_PWA_ENDPOINT).then(r => {
        if (r.sent > 0) console.log('[IPEVR PWA] Sincronizadas', r.sent, 'filas');
      });
    }
  });
})();
