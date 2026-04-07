/**
 * offline_queue.js
 * Cola offline para firmas de asistencia-induccion.
 * Usa IndexedDB para persistir datos y sincroniza cuando vuelve la conexion.
 */
const OfflineQueue = (function() {
    const DB_NAME = 'inspecciones_offline';
    const DB_VERSION = 1;
    const STORE_NAME = 'pending_signatures';

    function openDB() {
        return new Promise((resolve, reject) => {
            const req = indexedDB.open(DB_NAME, DB_VERSION);
            req.onupgradeneeded = function(e) {
                const db = e.target.result;
                if (!db.objectStoreNames.contains(STORE_NAME)) {
                    const store = db.createObjectStore(STORE_NAME, { keyPath: 'id', autoIncrement: true });
                    store.createIndex('type', 'type', { unique: false });
                    store.createIndex('id_asistencia', 'id_asistencia', { unique: false });
                }
            };
            req.onsuccess = () => resolve(req.result);
            req.onerror = () => reject(req.error);
        });
    }

    /**
     * Guardar un registro pendiente en IndexedDB.
     * @param {Object} data - { type: 'asistente'|'firma', url, payload, id_asistencia, meta }
     */
    async function add(data) {
        const db = await openDB();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE_NAME, 'readwrite');
            const store = tx.objectStore(STORE_NAME);
            data.created_at = new Date().toISOString();
            const req = store.add(data);
            req.onsuccess = () => resolve(req.result);
            req.onerror = () => reject(req.error);
            tx.oncomplete = () => db.close();
        });
    }

    /**
     * Obtener todos los registros pendientes.
     */
    async function getAll() {
        const db = await openDB();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE_NAME, 'readonly');
            const store = tx.objectStore(STORE_NAME);
            const req = store.getAll();
            req.onsuccess = () => resolve(req.result);
            req.onerror = () => reject(req.error);
            tx.oncomplete = () => db.close();
        });
    }

    /**
     * Obtener pendientes por id_asistencia (para mostrar en UI).
     */
    async function getByAsistencia(idAsistencia) {
        const db = await openDB();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE_NAME, 'readonly');
            const store = tx.objectStore(STORE_NAME);
            const idx = store.index('id_asistencia');
            const req = idx.getAll(idAsistencia);
            req.onsuccess = () => resolve(req.result);
            req.onerror = () => reject(req.error);
            tx.oncomplete = () => db.close();
        });
    }

    /**
     * Eliminar un registro por ID (despues de sync exitoso).
     */
    async function remove(id) {
        const db = await openDB();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE_NAME, 'readwrite');
            const store = tx.objectStore(STORE_NAME);
            const req = store.delete(id);
            req.onsuccess = () => resolve();
            req.onerror = () => reject(req.error);
            tx.oncomplete = () => db.close();
        });
    }

    /**
     * Contar pendientes.
     */
    async function count() {
        const db = await openDB();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE_NAME, 'readonly');
            const store = tx.objectStore(STORE_NAME);
            const req = store.count();
            req.onsuccess = () => resolve(req.result);
            req.onerror = () => reject(req.error);
            tx.oncomplete = () => db.close();
        });
    }

    /**
     * Sincronizar todos los pendientes con el servidor.
     * Retorna { synced: N, failed: N, errors: [] }
     */
    async function syncAll() {
        const items = await getAll();
        if (items.length === 0) return { synced: 0, failed: 0, errors: [] };

        let synced = 0;
        let failed = 0;
        const errors = [];

        for (const item of items) {
            try {
                const formData = new FormData();
                // Reconstruir el payload
                for (const [key, value] of Object.entries(item.payload)) {
                    // Caso especial: foto almacenada como base64 → convertir a File
                    if (key === 'foto_brigadista_base64' && value) {
                        const res = await fetch(value);
                        const blob = await res.blob();
                        formData.append('foto_brigadista', blob, 'foto_brigadista.jpg');
                        continue;
                    }
                    if (key === 'foto_brigadista_base64') continue;
                    formData.append(key, value);
                }

                const response = await fetch(item.url, {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) throw new Error('HTTP ' + response.status);

                const data = await response.json();
                if (data.success) {
                    await remove(item.id);
                    synced++;
                } else {
                    errors.push({ item, error: data.error || 'Server rejected' });
                    failed++;
                }
            } catch (err) {
                errors.push({ item, error: err.message });
                failed++;
            }
        }

        return { synced, failed, errors };
    }

    /**
     * Registrar Background Sync si el navegador lo soporta.
     */
    async function requestSync() {
        if ('serviceWorker' in navigator && 'SyncManager' in window) {
            try {
                const reg = await navigator.serviceWorker.ready;
                await reg.sync.register('sync-firmas');
            } catch (e) {
                // SyncManager no disponible, fallback manual
            }
        }
    }

    /**
     * Iniciar listener de reconexion para sync automatico.
     * @param {Function} onSyncComplete - callback({synced, failed}) cuando sync termina
     */
    function startOnlineListener(onSyncComplete) {
        window.addEventListener('online', async function() {
            const pending = await count();
            if (pending === 0) return;

            const result = await syncAll();
            if (typeof onSyncComplete === 'function') {
                onSyncComplete(result);
            }
        });
    }

    return {
        add,
        getAll,
        getByAsistencia,
        remove,
        count,
        syncAll,
        requestSync,
        startOnlineListener
    };
})();
