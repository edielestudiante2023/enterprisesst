# Estrategia Offline - Modulo de Inspecciones

> **Estado: PENDIENTE DE IMPLEMENTACION** — Este documento es un diseno a futuro. Actualmente solo esta implementado el cache basico de assets via Service Worker (`sw_inspecciones.js`, cache `inspecciones-v3`). La funcionalidad completa de IndexedDB, Background Sync y modo offline NO esta implementada aun.

---

## 1. El Problema Real

El consultor visita conjuntos residenciales donde **no hay senal de datos moviles**.
Necesita poder:

1. Abrir la app (ya instalada como PWA)
2. Seleccionar el cliente (de una lista pre-cargada)
3. Llenar el acta completa
4. Capturar firmas
5. Tomar fotos
6. Guardar todo localmente
7. Cuando vuelva a tener senal, **todo se sincroniza automaticamente**

El PDF se genera en el servidor (DOMPDF), NO en el celular. Entonces el flujo offline es:

```
SIN CONEXION:
  Formulario → Firmas → Fotos → Todo guardado en IndexedDB del celular

CON CONEXION (automatico):
  IndexedDB → POST al servidor → Servidor genera PDF → Acta completa
```

---

## 2. Arquitectura Offline

### Tecnologias

| Componente | Tecnologia | Por que |
|------------|-----------|---------|
| Almacenamiento local | **IndexedDB** | Soporta blobs (fotos), sin limite de 5MB como localStorage |
| Cache de assets | **Cache API** (Service Worker) | CSS, JS, iconos, fuentes cargadas offline |
| Cache de datos | **IndexedDB** | Lista de clientes, pendientes, mantenimientos pre-cargados |
| Sincronizacion | **Background Sync API** + fallback manual | Envia datos cuando hay conexion |
| Deteccion de red | `navigator.onLine` + `fetch` probe | Doble verificacion de conectividad |

### Flujo Completo

```
[Consultor abre PWA con WiFi en oficina]
        |
        v
[Pre-carga automatica] ← Service Worker + IndexedDB
  - Lista de clientes activos con logo
  - Pendientes abiertos por cliente
  - Mantenimientos proximos por cliente
  - Assets (CSS, JS, fuentes, iconos)
        |
        v
[Sale a campo - SIN CONEXION]
        |
        v
[Abre la app → funciona todo offline]
  - Selecciona cliente (de IndexedDB)
  - Ve pendientes/mantenimientos (de IndexedDB)
  - Llena el acta
  - Captura firmas (canvas → IndexedDB como base64)
  - Toma fotos (camara → comprimidas → IndexedDB como blob)
  - Guarda → entra a cola de sincronizacion
        |
        v
[Indicador visual: "1 acta pendiente de sincronizar"]
        |
        v
[Vuelve a zona con senal]
        |
        v
[Sincronizacion automatica en background]
  - Background Sync API (Android Chrome)
  - O al detectar `online` event (iOS fallback)
  - POST datos + firmas + fotos al servidor
  - Servidor genera PDF con DOMPDF
  - Respuesta: {ok, url_pdf}
  - Se borra de IndexedDB
        |
        v
[Notificacion: "Acta de Los Tucanes sincronizada ✓"]
```

---

## 3. IndexedDB - Estructura

### Database: `inspecciones_sst`

```javascript
const DB_NAME = 'inspecciones_sst';
const DB_VERSION = 1;

// Stores (tablas locales)
const STORES = {
    // Datos pre-cargados (cache de servidor)
    clientes: 'clientes',           // Lista de clientes activos
    pendientes: 'pendientes',       // Pendientes abiertos por cliente
    mantenimientos: 'mantenimientos', // Mantenimientos proximos

    // Datos creados offline (cola de sincronizacion)
    actas_offline: 'actas_offline', // Actas completas pendientes de sync
    sync_queue: 'sync_queue'        // Cola de operaciones pendientes
};
```

### Store: `clientes` (pre-cargado)

```javascript
// Se llena al abrir la app con conexion
// Estructura por registro:
{
    id_cliente: 44,
    nombre_cliente: 'CONJUNTO RESIDENCIAL LOS TUCANES',
    nit_cliente: '900123456',
    logo: 'data:image/png;base64,...', // Logo convertido a base64 para offline
    id_consultor: 1,
    ciudad_cliente: 'Bogota',
    // Pre-calculados:
    pendientes_count: 3,
    mantenimientos_count: 2,
    last_sync: '2026-02-21T10:00:00Z'
}
```

### Store: `pendientes` (pre-cargado)

```javascript
{
    id_pendientes: 123,
    id_cliente: 44,
    tarea_actividad: 'Remitir pieza grafica para la induccion',
    fecha_asignacion: '2026-02-12',
    responsable: 'Edison Cuervo',
    estado: 'ABIERTA',
    conteo_dias: 9
}
```

### Store: `mantenimientos` (pre-cargado)

```javascript
{
    id_vencimientos_mmttos: 456,
    id_cliente: 44,
    detalle_mantenimiento: 'Mantenimiento ascensor', // OJO: columna es detalle_mantenimiento, NO descripcion_mantenimiento
    fecha_vencimiento: '2026-03-15',
    estado_actividad: 'sin ejecutar'
}
```

### Store: `actas_offline` (creado offline)

```javascript
{
    id_local: 'offline_1708524800_44', // timestamp + id_cliente
    id_cliente: 44,
    fecha_visita: '2026-02-21',
    hora_visita: '14:30',
    ubicacion_gps: '4.601875, -74.218899',
    motivo: 'Visita mes de febrero',
    modalidad: 'Presencial',
    cartera: 'Ok',
    observaciones: 'Todo en orden',
    proxima_reunion_fecha: '2026-03-21',
    proxima_reunion_hora: '14:00',

    // Integrantes (array)
    integrantes: [
        { nombre: 'EDITA SANABRIA', rol: 'ADMINISTRADOR', orden: 1 },
        { nombre: 'EDISON CUERVO', rol: 'CONSULTOR CYCLOID', orden: 2 },
        { nombre: 'PEDRO PEREZ', rol: 'VIGIA SST', orden: 3 }
    ],

    // Temas (array)
    temas: [
        { descripcion: 'Socializacion plan de trabajo', orden: 1 },
        { descripcion: 'Revision pendientes', orden: 2 }
    ],

    // Compromisos/Pendientes nuevos (array)
    compromisos: [
        { tarea_actividad: 'Enviar cotizacion', fecha_cierre: '2026-03-01', responsable: 'Admin' },
        { tarea_actividad: 'Actualizar matriz', fecha_cierre: '2026-03-15', responsable: 'Consultor' }
    ],

    // Firmas (base64 PNG - tipicamente 5-20KB cada una)
    firma_administrador: 'data:image/png;base64,iVBOR...',
    firma_vigia: 'data:image/png;base64,iVBOR...',
    firma_consultor: 'data:image/png;base64,iVBOR...',

    // Fotos (blobs comprimidos - max 200KB cada una)
    fotos: [
        { blob: Blob, tipo: 'foto', descripcion: 'Entrada principal' },
        { blob: Blob, tipo: 'seg_social', descripcion: 'Afiliacion ARL' }
    ],

    // Soportes
    soporte_lavado_tanques: Blob || null,
    soporte_plagas: Blob || null,

    // Metadata de sync
    estado_sync: 'pendiente', // 'pendiente', 'sincronizando', 'sincronizado', 'error'
    created_at: '2026-02-21T14:30:00Z',
    sync_attempts: 0,
    last_error: null
}
```

### Store: `sync_queue` (cola de operaciones)

```javascript
{
    id: 'sync_1708524800',
    tipo: 'acta_visita',
    id_local: 'offline_1708524800_44',
    estado: 'pendiente',  // 'pendiente', 'en_proceso', 'completado', 'error'
    intentos: 0,
    max_intentos: 5,
    created_at: '2026-02-21T14:30:00Z',
    next_retry: null
}
```

---

## 4. Pre-carga de Datos (Sync Down)

Cuando la app se abre con conexion, se descargan los datos necesarios para trabajar offline.

### Endpoint del servidor

```php
// GET /inspecciones/api/sync-data
// Retorna todo lo necesario para trabajar offline
public function getSyncData()
{
    $idConsultor = session()->get('user_id');

    // Clientes activos del consultor con logo en base64
    $clientes = $this->clientModel
        ->select('tbl_clientes.id_cliente, nombre_cliente, nit_cliente, logo, ciudad_cliente')
        ->join('tbl_contratos', 'tbl_contratos.id_cliente = tbl_clientes.id_cliente')
        ->where('tbl_clientes.id_consultor', $idConsultor)
        ->where('tbl_contratos.estado', 'activo')
        ->findAll();

    // Convertir logos a base64 para offline
    foreach ($clientes as &$c) {
        if (!empty($c['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $c['logo'];
            if (file_exists($logoPath)) {
                $c['logo_base64'] = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
            }
        }
    }

    // Pendientes abiertos de todos los clientes del consultor
    $clienteIds = array_column($clientes, 'id_cliente');
    $pendientes = $this->pendientesModel
        ->whereIn('id_cliente', $clienteIds)
        ->where('estado', 'ABIERTA')
        ->findAll();

    // Mantenimientos proximos (30 dias)
    $mantenimientos = $this->vencimientosModel
        ->getUpcomingVencimientosByClientes($clienteIds);

    return $this->response->setJSON([
        'clientes' => $clientes,
        'pendientes' => $pendientes,
        'mantenimientos' => $mantenimientos,
        'sync_timestamp' => date('c')
    ]);
}
```

### JavaScript: Pre-carga

```javascript
class SyncManager {

    async precargarDatos() {
        if (!navigator.onLine) return;

        try {
            const response = await fetch('/inspecciones/api/sync-data');
            if (!response.ok) return;

            const data = await response.json();

            // Guardar en IndexedDB
            const db = await this.openDB();

            const tx = db.transaction(['clientes', 'pendientes', 'mantenimientos'], 'readwrite');

            // Limpiar y recargar clientes
            await tx.objectStore('clientes').clear();
            for (const cliente of data.clientes) {
                await tx.objectStore('clientes').put(cliente);
            }

            // Limpiar y recargar pendientes
            await tx.objectStore('pendientes').clear();
            for (const pend of data.pendientes) {
                await tx.objectStore('pendientes').put(pend);
            }

            // Limpiar y recargar mantenimientos
            await tx.objectStore('mantenimientos').clear();
            for (const mant of data.mantenimientos) {
                await tx.objectStore('mantenimientos').put(mant);
            }

            await tx.done;

            localStorage.setItem('last_sync', data.sync_timestamp);
            this.updateSyncIndicator('Datos actualizados');

        } catch (err) {
            console.warn('Pre-carga fallida, usando cache:', err);
        }
    }
}
```

---

## 5. Sincronizacion (Sync Up)

### 5.1 Background Sync API (Android Chrome)

```javascript
// En el formulario, al guardar offline:
async function guardarActaOffline(actaData) {
    const db = await openDB();

    // Guardar acta en IndexedDB
    await db.put('actas_offline', actaData);

    // Agregar a cola de sync
    await db.put('sync_queue', {
        id: 'sync_' + Date.now(),
        tipo: 'acta_visita',
        id_local: actaData.id_local,
        estado: 'pendiente',
        intentos: 0,
        max_intentos: 5,
        created_at: new Date().toISOString()
    });

    // Registrar Background Sync
    if ('serviceWorker' in navigator && 'SyncManager' in window) {
        const reg = await navigator.serviceWorker.ready;
        await reg.sync.register('sync-actas');
        console.log('Background Sync registrado');
    }

    // Feedback al usuario
    Swal.fire({
        icon: 'success',
        title: 'Acta guardada',
        text: navigator.onLine
            ? 'Sincronizando...'
            : 'Se sincronizara automaticamente cuando tengas conexion.',
        timer: 3000
    });
}
```

### 5.2 Service Worker: Sync Handler

```javascript
// sw_inspecciones.js

self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-actas') {
        event.waitUntil(sincronizarActasPendientes());
    }
});

async function sincronizarActasPendientes() {
    const db = await openDB();
    const queue = await db.getAll('sync_queue');
    const pendientes = queue.filter(q => q.estado === 'pendiente' || q.estado === 'error');

    for (const item of pendientes) {
        try {
            // Marcar como en proceso
            item.estado = 'en_proceso';
            await db.put('sync_queue', item);

            // Obtener acta de IndexedDB
            const acta = await db.get('actas_offline', item.id_local);
            if (!acta) continue;

            // Preparar FormData con fotos como blobs
            const formData = new FormData();
            formData.append('acta_json', JSON.stringify({
                ...acta,
                fotos: undefined,          // Se envian aparte
                soporte_lavado_tanques: undefined,
                soporte_plagas: undefined
            }));

            // Adjuntar firmas como archivos
            if (acta.firma_administrador) {
                formData.append('firma_administrador', dataURLtoBlob(acta.firma_administrador), 'firma_admin.png');
            }
            if (acta.firma_vigia) {
                formData.append('firma_vigia', dataURLtoBlob(acta.firma_vigia), 'firma_vigia.png');
            }
            if (acta.firma_consultor) {
                formData.append('firma_consultor', dataURLtoBlob(acta.firma_consultor), 'firma_consultor.png');
            }

            // Adjuntar fotos
            if (acta.fotos) {
                acta.fotos.forEach((foto, i) => {
                    formData.append(`fotos[${i}]`, foto.blob, `foto_${i}.jpg`);
                    formData.append(`fotos_meta[${i}]`, JSON.stringify({
                        tipo: foto.tipo,
                        descripcion: foto.descripcion
                    }));
                });
            }

            // Adjuntar soportes
            if (acta.soporte_lavado_tanques) {
                formData.append('soporte_lavado_tanques', acta.soporte_lavado_tanques, 'soporte_tanques.jpg');
            }
            if (acta.soporte_plagas) {
                formData.append('soporte_plagas', acta.soporte_plagas, 'soporte_plagas.jpg');
            }

            // POST al servidor
            const response = await fetch('/inspecciones/acta-visita/sync', {
                method: 'POST',
                body: formData
                // NO poner Content-Type, el browser lo pone con boundary
            });

            if (response.ok) {
                const result = await response.json();

                // Limpiar de IndexedDB
                item.estado = 'completado';
                await db.put('sync_queue', item);
                await db.delete('actas_offline', item.id_local);

                // Notificar al usuario (si la app esta abierta)
                self.clients.matchAll().then(clients => {
                    clients.forEach(client => {
                        client.postMessage({
                            type: 'SYNC_COMPLETE',
                            id_local: item.id_local,
                            id_server: result.id,
                            url_pdf: result.url_pdf,
                            nombre_cliente: result.nombre_cliente
                        });
                    });
                });

            } else {
                throw new Error(`HTTP ${response.status}`);
            }

        } catch (err) {
            item.intentos++;
            item.estado = item.intentos >= item.max_intentos ? 'error_permanente' : 'error';
            item.last_error = err.message;
            item.next_retry = new Date(Date.now() + Math.pow(2, item.intentos) * 60000).toISOString();
            await db.put('sync_queue', item);
        }
    }
}

// Utilidad: convertir data URL a Blob
function dataURLtoBlob(dataURL) {
    const parts = dataURL.split(',');
    const mime = parts[0].match(/:(.*?);/)[1];
    const binary = atob(parts[1]);
    const array = new Uint8Array(binary.length);
    for (let i = 0; i < binary.length; i++) {
        array[i] = binary.charCodeAt(i);
    }
    return new Blob([array], { type: mime });
}
```

### 5.3 Fallback para iOS (no soporta Background Sync)

```javascript
// En layout_pwa.php - se ejecuta siempre

// Detectar cuando vuelve la conexion
window.addEventListener('online', () => {
    console.log('Conexion recuperada, sincronizando...');
    syncManager.sincronizarPendientes();
});

// Tambien intentar al abrir la app
document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible' && navigator.onLine) {
        syncManager.sincronizarPendientes();
    }
});

// Intento periodico cada 30 segundos si hay pendientes
setInterval(() => {
    if (navigator.onLine) {
        syncManager.sincronizarPendientes();
    }
}, 30000);
```

---

## 6. Endpoint de Sincronizacion (Server-Side)

```php
// POST /inspecciones/acta-visita/sync
// Recibe acta completa con firmas y fotos desde offline
public function sync()
{
    $actaJson = json_decode($this->request->getPost('acta_json'), true);

    $db = \Config\Database::connect();
    $db->transStart();

    try {
        // 1. Crear acta principal
        $actaData = [
            'id_cliente'             => $actaJson['id_cliente'],
            'id_consultor'           => session()->get('user_id'),
            'fecha_visita'           => $actaJson['fecha_visita'],
            'hora_visita'            => $actaJson['hora_visita'],
            'ubicacion_gps'          => $actaJson['ubicacion_gps'],
            'motivo'                 => $actaJson['motivo'],
            'modalidad'              => $actaJson['modalidad'],
            'cartera'                => $actaJson['cartera'],
            'observaciones'          => $actaJson['observaciones'],
            'proxima_reunion_fecha'  => $actaJson['proxima_reunion_fecha'],
            'proxima_reunion_hora'   => $actaJson['proxima_reunion_hora'],
            'estado'                 => 'completo'
        ];

        $this->actaModel->insert($actaData);
        $idActa = $this->actaModel->getInsertID();

        // 2. Guardar firmas (archivos)
        $firmaDir = FCPATH . 'uploads/inspecciones/firmas/';
        if (!is_dir($firmaDir)) mkdir($firmaDir, 0755, true);

        foreach (['administrador', 'vigia', 'consultor'] as $tipo) {
            $firmaFile = $this->request->getFile("firma_{$tipo}");
            if ($firmaFile && $firmaFile->isValid()) {
                $nombre = "firma_{$tipo}_{$idActa}_" . time() . '.png';
                $firmaFile->move($firmaDir, $nombre);
                $this->actaModel->update($idActa, [
                    "firma_{$tipo}" => "uploads/inspecciones/firmas/{$nombre}"
                ]);
            }
        }

        // 3. Guardar integrantes
        foreach ($actaJson['integrantes'] as $integrante) {
            $this->integranteModel->insert([
                'id_acta_visita' => $idActa,
                'nombre'         => $integrante['nombre'],
                'rol'            => $integrante['rol'],
                'orden'          => $integrante['orden']
            ]);
        }

        // 4. Guardar temas
        foreach ($actaJson['temas'] as $tema) {
            $this->temaModel->insert([
                'id_acta_visita' => $idActa,
                'descripcion'    => $tema['descripcion'],
                'orden'          => $tema['orden']
            ]);
        }

        // 5. Guardar compromisos como pendientes
        foreach ($actaJson['compromisos'] as $comp) {
            $this->pendientesModel->insert([
                'id_cliente'       => $actaJson['id_cliente'],
                'id_acta_visita'   => $idActa,
                'tarea_actividad'  => $comp['tarea_actividad'],
                'fecha_cierre'     => $comp['fecha_cierre'],
                'responsable'      => $comp['responsable'],
                'fecha_asignacion' => $actaJson['fecha_visita'],
                'estado'           => 'ABIERTA'
            ]);
        }

        // 6. Guardar fotos
        $fotoDir = FCPATH . 'uploads/inspecciones/fotos/';
        if (!is_dir($fotoDir)) mkdir($fotoDir, 0755, true);

        $fotos = $this->request->getFiles();
        foreach ($fotos['fotos'] ?? [] as $i => $foto) {
            if ($foto->isValid()) {
                $nombre = "foto_{$idActa}_{$i}_" . time() . '.jpg';
                $foto->move($fotoDir, $nombre);

                $meta = json_decode($this->request->getPost("fotos_meta[{$i}]"), true);
                $this->fotoModel->insert([
                    'id_acta_visita' => $idActa,
                    'ruta_archivo'   => "uploads/inspecciones/fotos/{$nombre}",
                    'tipo'           => $meta['tipo'] ?? 'foto',
                    'descripcion'    => $meta['descripcion'] ?? null
                ]);
            }
        }

        // 7. Generar PDF
        $urlPdf = $this->generatePdf($idActa);

        $db->transComplete();

        if ($db->transStatus() === false) {
            throw new \Exception('Error en transaccion');
        }

        // Respuesta al Service Worker
        $cliente = $this->clientModel->find($actaJson['id_cliente']);
        return $this->response->setJSON([
            'success'        => true,
            'id'             => $idActa,
            'url_pdf'        => $urlPdf,
            'nombre_cliente' => $cliente['nombre_cliente']
        ]);

    } catch (\Exception $e) {
        $db->transRollback();
        return $this->response->setStatusCode(500)->setJSON([
            'success' => false,
            'error'   => $e->getMessage()
        ]);
    }
}
```

---

## 7. Compresion de Fotos (Antes de guardar en IndexedDB)

Las fotos del celular pueden pesar 3-8MB cada una. Se comprimen antes de guardar:

```javascript
async function comprimirFoto(file, maxWidth = 1200, quality = 0.7) {
    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const img = new Image();
            img.onload = () => {
                const canvas = document.createElement('canvas');

                // Calcular dimensiones manteniendo proporcion
                let width = img.width;
                let height = img.height;
                if (width > maxWidth) {
                    height = Math.round(height * maxWidth / width);
                    width = maxWidth;
                }

                canvas.width = width;
                canvas.height = height;

                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                // Convertir a blob JPEG comprimido
                canvas.toBlob((blob) => {
                    resolve(blob); // Tipicamente 100-200KB
                }, 'image/jpeg', quality);
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
}

// Uso al tomar/seleccionar foto:
document.getElementById('inputFoto').addEventListener('change', async (e) => {
    const file = e.target.files[0];
    if (!file) return;

    const blobComprimido = await comprimirFoto(file);
    console.log(`Original: ${(file.size/1024).toFixed(0)}KB → Comprimido: ${(blobComprimido.size/1024).toFixed(0)}KB`);

    // Guardar blob en el array de fotos del acta
    actaData.fotos.push({
        blob: blobComprimido,
        tipo: 'foto',
        descripcion: ''
    });
});
```

---

## 8. UX Offline - Indicadores Visuales

### Barra de estado de conexion

```html
<!-- En layout_pwa.php, debajo del top bar -->
<div id="offlineBar" class="offline-bar" style="display:none;">
    <i class="fas fa-wifi-slash"></i> Sin conexion — Los datos se guardaran localmente
</div>

<div id="syncBar" class="sync-bar" style="display:none;">
    <i class="fas fa-sync fa-spin"></i> Sincronizando <span id="syncCount">1</span> acta(s)...
</div>
```

```css
.offline-bar {
    background: #dc3545;
    color: white;
    text-align: center;
    padding: 6px;
    font-size: 13px;
    position: fixed;
    top: 56px; /* debajo del navbar */
    width: 100%;
    z-index: 1000;
}

.sync-bar {
    background: #ffc107;
    color: #333;
    text-align: center;
    padding: 6px;
    font-size: 13px;
    position: fixed;
    top: 56px;
    width: 100%;
    z-index: 1000;
}
```

### Badge de pendientes en dashboard

```
+------------------------------------------+
|  Inspecciones SST       [1 pendiente ⏳] |  ← Badge naranja
|  Hola, Edison                            |
|  Sin conexion                            |
|                                          |
|  +------------------+ +---------------+  |
|  |  Actas de Visita | |  Senalizacion |  |
|  |      (12)        | |    (---)      |  |
|  |  ⏳ 1 sin sync   | |               |  |
|  +------------------+ +---------------+  |
+------------------------------------------+
```

### Estados del acta en el listado

| Icono | Estado | Significado |
|-------|--------|-------------|
| ✅ | `completo` | Acta firmada, PDF generado, en servidor |
| 📝 | `borrador` | Acta guardada sin firmar (en servidor) |
| ⏳ | `pendiente_sync` | Acta completa, esperando conexion (en IndexedDB) |
| 🔄 | `sincronizando` | Sincronizacion en progreso |
| ❌ | `error_sync` | Fallo la sincronizacion (con boton "Reintentar") |

---

## 9. Capacidad de Almacenamiento

### Tamano estimado por acta offline

| Componente | Tamano estimado |
|------------|----------------|
| Datos JSON del acta | ~2-5 KB |
| Firmas (3x base64 PNG) | ~15-60 KB |
| Fotos comprimidas (3x JPEG) | ~300-600 KB |
| Soportes (2x JPEG) | ~200-400 KB |
| **Total por acta** | **~500 KB - 1 MB** |

### Limites de IndexedDB

| Navegador | Limite |
|-----------|--------|
| Chrome Android | ~80% del espacio libre (tipicamente 1-5 GB) |
| Safari iOS | ~1 GB (pero puede ser purgado si no se usa) |
| Samsung Internet | Similar a Chrome |

**Con 1MB por acta, puedes almacenar ~1000 actas offline.** Mas que suficiente.

---

## 10. Casos Borde y Manejo de Errores

### Conflicto de ID
- El `id_local` es `offline_{timestamp}_{id_cliente}` — unico por definicion
- El `id` real del servidor se asigna al sincronizar
- No hay conflicto posible (no se editan actas remotamente)

### Sesion expirada al sincronizar
- Si el servidor responde 401, el Service Worker guarda el error
- Al abrir la app, se muestra: "Tu sesion expiro. Inicia sesion para sincronizar"
- Los datos siguen seguros en IndexedDB hasta que se reloguee

### Sincronizacion parcial
- Cada acta se sincroniza como transaccion atomica
- Si falla una, las demas se siguen intentando
- No hay estados intermedios en el servidor

### Espacio lleno
- Antes de guardar, verificar espacio disponible:
```javascript
if (navigator.storage && navigator.storage.estimate) {
    const { quota, usage } = await navigator.storage.estimate();
    const disponible = quota - usage;
    if (disponible < 5 * 1024 * 1024) { // menos de 5MB libres
        alert('Poco espacio disponible. Sincroniza tus actas pendientes.');
    }
}
```

### Datos pre-cargados desactualizados
- Se muestra fecha de ultima sincronizacion: "Datos actualizados: hace 2 horas"
- Si los datos tienen mas de 24h, se muestra advertencia
- Pero se permite seguir trabajando (mejor datos viejos que no poder trabajar)

---

## 11. Service Worker Completo

```javascript
// sw_inspecciones.js

const CACHE_NAME = 'inspecciones-v3'; // Debe coincidir con sw_inspecciones.js actual
const STATIC_ASSETS = [
    '/inspecciones',
    '/inspecciones/acta-visita/create',
    // CDN assets
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
    'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js',
    // Fuentes e iconos (se agregan dinamicamente)
];

// INSTALL: Cachear assets estaticos
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS);
        })
    );
    self.skipWaiting();
});

// ACTIVATE: Limpiar caches viejos
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((names) => {
            return Promise.all(
                names.filter(n => n !== CACHE_NAME).map(n => caches.delete(n))
            );
        })
    );
    self.clients.claim();
});

// FETCH: Cache First para assets, Network First para API
self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    // API calls: Network First
    if (url.pathname.startsWith('/inspecciones/api/')) {
        event.respondWith(
            fetch(event.request)
                .catch(() => caches.match(event.request))
        );
        return;
    }

    // Pages de inspecciones: Network First con fallback a cache
    if (url.pathname.startsWith('/inspecciones')) {
        event.respondWith(
            fetch(event.request)
                .then((response) => {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                    return response;
                })
                .catch(() => caches.match(event.request))
        );
        return;
    }

    // Assets estaticos: Cache First
    event.respondWith(
        caches.match(event.request).then((cached) => {
            return cached || fetch(event.request).then((response) => {
                const clone = response.clone();
                caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                return response;
            });
        })
    );
});

// BACKGROUND SYNC: Sincronizar actas cuando hay conexion
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-actas') {
        event.waitUntil(sincronizarActasPendientes());
    }
});

// Listener para mensajes desde la app
self.addEventListener('message', (event) => {
    if (event.data.type === 'FORCE_SYNC') {
        sincronizarActasPendientes();
    }
});
```

---

## 12. Resumen de Compatibilidad

| Feature | Chrome Android | Safari iOS | Samsung Internet |
|---------|---------------|------------|------------------|
| IndexedDB | Si | Si | Si |
| Service Worker | Si | Si (13.4+) | Si |
| Cache API | Si | Si | Si |
| Background Sync | **Si** | **NO** | Si |
| Geolocation | Si | Si | Si |
| Camera capture | Si | Si | Si |
| navigator.onLine | Si | Si | Si |

**Para iOS sin Background Sync:** Se usa el fallback con `online` event + `visibilitychange` + intervalo de 30s.
Funciona igual de bien, solo que no se ejecuta con la app cerrada (en Android si).

---

## 13. Archivos Nuevos para Offline

| Archivo | Descripcion |
|---------|-------------|
| `public/sw_inspecciones.js` | Service Worker completo |
| `public/js/inspecciones/db.js` | Modulo IndexedDB (open, read, write) |
| `public/js/inspecciones/sync-manager.js` | Logica de sincronizacion |
| `public/js/inspecciones/offline-ui.js` | Indicadores visuales offline/online |
| `public/js/inspecciones/foto-compressor.js` | Compresion de fotos |
