# PWA & Layout Movil - Modulo de Inspecciones

## Estado: IMPLEMENTADO (2026-02-23)

---

## 1. Progressive Web App (PWA) - IMPLEMENTADO

### manifest_inspecciones.json (en `public/`)

```json
{
    "name": "Inspecciones SST - Cycloid",
    "short_name": "Inspecciones",
    "start_url": "/inspecciones",
    "scope": "/",
    "display": "standalone",
    "orientation": "portrait",
    "background_color": "#1c2437",
    "theme_color": "#1c2437",
    "icons": [
        { "src": "/icons/icon-192.png?v=3", "sizes": "192x192", "type": "image/png", "purpose": "any" },
        { "src": "/icons/icon-192.png?v=3", "sizes": "192x192", "type": "image/png", "purpose": "maskable" },
        { "src": "/icons/icon-512.png?v=3", "sizes": "512x512", "type": "image/png", "purpose": "any" },
        { "src": "/icons/icon-512.png?v=3", "sizes": "512x512", "type": "image/png", "purpose": "maskable" }
    ]
}
```

**Decisiones de implementacion:**

- **`scope: "/"`** en vez de `"/inspecciones/"` — necesario para que el SW intercepte `/login` y otras rutas del sistema. Si el scope se limita a `/inspecciones/`, el login redirige fuera del scope y la PWA se abre en el navegador.
- **Iconos en `/icons/`** (no `/assets/icons/`) — carpeta dedicada en `public/icons/`
- **Purposes separados:** Usar entradas separadas para `"any"` y `"maskable"` — combinar `"any maskable"` en una sola entrada puede causar problemas en algunos launchers.
- **Cache busting `?v=N`:** Los iconos PWA se cachean agresivamente. Bumpar `?v=N` en manifest, login.php y layout_pwa.php al cambiar iconos.
- Iconos generados con PHP GD desde `uploads/logoenterprisesstdorado.jpg` (script `generate_icons.php`), logo full-bleed sin fondo blanco.

### Login como PWA (CRITICO)

Chrome detecta la PWA en la pagina visible al momento de "Agregar a pantalla de inicio", NO en la `start_url`. Como el usuario aterriza en `/login` primero, **login.php DEBE tener**:

```html
<meta name="theme-color" content="#1c2437">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Inspecciones">
<link rel="manifest" href="/manifest_inspecciones.json?v=3">
<link rel="apple-touch-icon" href="/icons/icon-192.png?v=3">
```

Y registro del SW antes de `</body>`:

```html
<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw_inspecciones.js', { scope: '/' });
}
</script>
```

Sin esto, la app se abre como pestaña web en vez de standalone.

### Service Worker (sw_inspecciones.js) - IMPLEMENTADO

Estrategia: **Cache First** para CDN assets, **Network First** para paginas locales.

```javascript
const CACHE_NAME = 'inspecciones-v3';
const ASSETS_TO_CACHE = [
    '/inspecciones',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
    'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
    'https://cdn.jsdelivr.net/npm/sweetalert2@11',
    'https://code.jquery.com/jquery-3.7.0.min.js'
];

// Cache estaticos al instalar
self.addEventListener('install', event => { ... });

// Network First para API calls, Cache First para assets
self.addEventListener('fetch', event => { ... });
```

**NOTA:** NO cachear datos de formulario ni respuestas de API. Solo assets estáticos (CSS, JS, iconos, fuentes).

### Instalación

El `layout_pwa.php` incluye registro del SW y meta tags de iOS:

```html
<!-- En <head> -->
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Inspecciones">
<link rel="manifest" href="/manifest_inspecciones.json?v=3">
<link rel="apple-touch-icon" href="/icons/icon-192.png?v=3">
```

```javascript
// Antes de </body>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/sw_inspecciones.js', { scope: '/' })
            .then(function(reg) {
                console.log('SW registrado, scope:', reg.scope);
            })
            .catch(function(err) {
                console.log('SW error:', err);
            });
    });
}
```

**Banner de instalacion (pendiente implementar):**

```javascript
let deferredPrompt;
window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    // Mostrar boton "Instalar app"
});
```

---

## 2. Layout PWA (`layout_pwa.php`) - IMPLEMENTADO

### Estructura HTML

```html
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#1c2437">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Inspecciones">
    <link rel="manifest" href="/manifest_inspecciones.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">

    <!-- Bootstrap 5.3 -->
    <!-- Font Awesome 6 -->
    <!-- SweetAlert2 -->

    <title>Inspecciones SST</title>
</head>
<body>
    <!-- Top Bar (fija) -->
    <nav class="navbar navbar-dark fixed-top" style="background: #1c2437;">
        <div class="container-fluid">
            <button class="btn" id="btnBack">←</button>
            <span class="navbar-brand">Inspecciones SST</span>
            <button class="btn" id="btnMenu">☰</button>
        </div>
    </nav>

    <!-- Contenido principal -->
    <main class="container-fluid" style="padding-top: 60px; padding-bottom: 70px;">
        <?= $content ?>
    </main>

    <!-- Bottom Navigation (fija) -->
    <nav class="navbar navbar-dark fixed-bottom" style="background: #1c2437;">
        <div class="container-fluid d-flex justify-content-around">
            <a href="/inspecciones" class="nav-link text-center">
                <i class="fas fa-home"></i><br><small>Inicio</small>
            </a>
            <a href="/inspecciones/acta-visita" class="nav-link text-center">
                <i class="fas fa-file-alt"></i><br><small>Actas</small>
            </a>
            <a href="/inspecciones/acta-visita/create" class="nav-link text-center">
                <i class="fas fa-plus-circle fa-2x" style="color: #bd9751;"></i>
            </a>
            <a href="#" class="nav-link text-center">
                <i class="fas fa-list"></i><br><small>Otras</small>
            </a>
            <a href="#" class="nav-link text-center">
                <i class="fas fa-user"></i><br><small>Perfil</small>
            </a>
        </div>
    </nav>

    <!-- Service Worker -->
    <script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw_inspecciones.js');
    }
    </script>
</body>
</html>
```

### Diferencia con Layout Admin

| Aspecto           | Layout Admin (PC)                | Layout PWA (Celular)            |
|-------------------|----------------------------------|----------------------------------|
| Sidebar           | Sidebar completo con todas las secciones | NO hay sidebar                 |
| Navegación        | Sidebar + breadcrumbs            | Bottom navigation (5 iconos)    |
| Ancho             | Full desktop (container-xl)      | 100% width, sin padding lateral |
| Tipografía        | Normal (14-16px)                 | Grande (16-18px) para touch     |
| Botones           | Tamaño normal                    | Mínimo 48px alto (zona touch)   |
| DataTables        | Completas con filtros            | Simplificadas, scroll horizontal|
| Header            | Logo + menú usuario              | Barra simple con botón atrás    |

---

## 3. Pantallas

### 3.1 Dashboard Inspecciones (`dashboard.php`)

```
┌─────────────────────────────┐
│  ← Inspecciones SST      ☰ │  ← Top bar
├─────────────────────────────┤
│                             │
│  Hola, Edison               │
│  22 de febrero, 2026        │
│                             │
│  ── PENDIENTES ──────────── │
│                             │
│  ┌─────────────────────────┐│
│  │ 📝 Acta - Los Tucanes   ││
│  │ 22/02/2026 · Borrador   ││
│  │ [Continuar editando →]  ││
│  └─────────────────────────┘│
│                             │
│  ┌─────────────────────────┐│
│  │ ✍️ Acta - El Zorzal     ││
│  │ 21/02/2026 · Pend.Firma ││
│  │ [Ir a firmas →]         ││
│  └─────────────────────────┘│
│                             │
│  ┌─────────────────────────┐│
│  │ 📝 Extint. - Jacaranda  ││
│  │ 20/02/2026 · Borrador   ││
│  │ [Continuar editando →]  ││
│  └─────────────────────────┘│
│                             │
│  ── INSPECCIONES ────────── │
│                             │
│  ┌─────────┐ ┌─────────┐   │
│  │   📋    │ │   🔍    │   │
│  │  Actas  │ │  Señal. │   │
│  │  de     │ │  ización│   │
│  │  Visita │ │         │   │
│  │   (12)  │ │  (---)  │   │
│  └─────────┘ └─────────┘   │
│                             │
│  ┌─────────┐ ┌─────────┐   │
│  │   🏗️    │ │   🧯    │   │
│  │  Locat. │ │  Extin. │   │
│  │         │ │         │   │
│  │  (---)  │ │  (---)  │   │
│  └─────────┘ └─────────┘   │
│                             │
│  ┌─────────┐ ┌─────────┐   │
│  │   🩹    │ │   🚿    │   │
│  │ Botiq.  │ │ Gabin.  │   │
│  │         │ │         │   │
│  │  (---)  │ │  (---)  │   │
│  └─────────┘ └─────────┘   │
│                             │
│  (---) = Próximamente       │
│                             │
├─────────────────────────────┤
│  🏠    📄    ➕    📋   👤 │  ← Bottom nav
└─────────────────────────────┘
```

**Seccion "Pendientes":**
- Solo aparece si hay documentos en estado `borrador` o `pendiente_firma`
- Si no hay pendientes, la seccion no se muestra
- Cada tarjeta tiene un CTA directo: "Continuar editando" (borrador) o "Ir a firmas" (pendiente_firma)
- Borde izquierdo amarillo para borradores, naranja para pendiente_firma
- Ordenadas por fecha descendente (mas reciente primero)
- Query: `WHERE id_consultor = ? AND estado IN ('borrador', 'pendiente_firma') ORDER BY updated_at DESC`

### 3.2 Listado Actas (`acta_visita/list.php`)

```
┌─────────────────────────────┐
│  ← Actas de Visita        ☰ │
├─────────────────────────────┤
│  🔍 Buscar cliente...       │
│  [Enero ▼] [2026 ▼]        │
│                             │
│  ┌─────────────────────────┐│
│  │ 📄 Los Tucanes          ││
│  │ 19/02/2026 - 5:54 PM    ││
│  │ Inducción en SST        ││
│  │ ✅ Completo      📥 PDF ││
│  └─────────────────────────┘│
│                             │
│  ┌─────────────────────────┐│
│  │ 📄 El Zorzal            ││
│  │ 27/01/2026 - 12:05 PM   ││
│  │ Visita mes de enero      ││
│  │ ✅ Completo      📥 PDF ││
│  └─────────────────────────┘│
│                             │
│  ┌─────────────────────────┐│
│  │ 📝 Jacaranda            ││
│  │ 22/01/2026 - 10:57 AM   ││
│  │ Visita mes de enero      ││
│  │ 📝 Borrador    ✏️ Editar││
│  └─────────────────────────┘│
│                             │
│  [➕ Nueva Acta de Visita]  │
│                             │
├─────────────────────────────┤
│  🏠    📄    ➕    📋   👤 │
└─────────────────────────────┘
```

### 3.3 Formulario Crear/Editar (`create.php` / `edit.php`)

Formulario de una sola página con secciones colapsables (accordion Bootstrap):

```
┌─────────────────────────────┐
│  ← Nueva Acta de Visita   ☰ │
├─────────────────────────────┤
│                             │
│  ▼ DATOS GENERALES          │
│  ┌─────────────────────────┐│
│  │ Cliente: [Select2    ▼] ││
│  │ Fecha: [21/02/2026]     ││
│  │ Hora:  [14:30]          ││
│  │ Motivo: [____________]  ││
│  │ 📍 Ubicación: capturada ││
│  └─────────────────────────┘│
│                             │
│  ▶ INTEGRANTES (2)          │
│  ▶ TEMAS (3)                │
│  ▶ TEMAS ABIERTOS (auto)   │
│  ▶ OBSERVACIONES            │
│  ▶ CARTERA                  │
│  ▶ COMPROMISOS (1)          │
│  ▶ PRÓXIMA REUNIÓN          │
│  ▶ FOTOS Y SOPORTES        │
│                             │
│  ┌─────────────────────────┐│
│  │  💾 Guardar borrador    ││
│  │  ✍️  Ir a firmas        ││
│  └─────────────────────────┘│
│                             │
├─────────────────────────────┤
│  🏠    📄    ➕    📋   👤 │
└─────────────────────────────┘
```

**Secciones dinámicas (agregar/quitar):**
- Integrantes: Botón "+ Agregar integrante" agrega fila [Nombre] [Rol ▼] [X]
- Temas: Botón "+ Agregar tema" agrega campo de texto
- Compromisos: Botón "+ Agregar compromiso" agrega fila [Actividad] [Fecha] [Responsable] [X]
- Fotos: Botón "📷 Tomar foto" abre cámara (HTML5 capture) o galería

**Sección "Temas Abiertos" (automática):**
- Se carga vía AJAX al seleccionar cliente
- Muestra pendientes, mantenimientos y hallazgos del cliente
- Solo lectura, informativa para el acta y para el PDF

---

## 4. Consideraciones Mobile-First

### Touch-friendly
- Todos los botones: mínimo `48px` de alto
- Inputs: `font-size: 16px` (evita zoom automático en iOS)
- Spacing entre elementos: mínimo `8px`
- Select2: configurado para mobile (`minimumResultsForSearch: 0`)

### Cámara y GPS
```html
<!-- Cámara -->
<input type="file" accept="image/*" capture="environment">

<!-- GPS (JavaScript) -->
navigator.geolocation.getCurrentPosition(pos => {
    document.getElementById('ubicacion').value =
        pos.coords.latitude + ', ' + pos.coords.longitude;
});
```

### Offline básico
- Service Worker cachea assets estáticos
- Si no hay conexión: muestra mensaje "Sin conexión, vuelve a intentar"
- **NO** hay modo offline completo (no se guardan formularios offline) — esto sería fase 2

### Performance
- No cargar DataTables en listados mobile — usar cards en su lugar
- Lazy loading de imágenes
- Comprimir fotos antes de subir (canvas resize a max 1200px)

---

## 5. Flujo de Login PWA

```
[Consultor instala PWA]
         │
         ▼
[Abre app → /inspecciones]
         │
    ¿Sesión activa?
    ┌────┴────┐
   SÍ        NO
    │         │
    ▼         ▼
[Dashboard]  [Login]
             │
             ▼
         [Login OK]
             │
             ▼
         [Redirect → /inspecciones]
             │
             ▼
         [Dashboard]
```

### Sesión larga para PWA
En `AuthController::loginPost()`, si el usuario accede desde mobile (user-agent check o parámetro), se configura cookie con expiración 30 días:

```php
// Si viene de PWA, sesión larga
if ($this->request->getGet('pwa') === '1' || $this->isPwaRequest()) {
    session()->set('session_timeout', 30 * 24 * 60 * 60); // 30 días
}
```

---

## 6. Colores y Estilos

Mismos colores del sistema principal para consistencia de marca:

```css
:root {
    --primary-dark: #1c2437;    /* Top/bottom bars, headers */
    --gold-primary: #bd9751;    /* Botones principales, acentos */
    --gold-hover: #a8843f;      /* Hover en botones */
    --bg-light: #f5f5f5;        /* Fondo de la app */
    --text-primary: #333;       /* Texto principal */
    --success: #28a745;         /* Estado completo */
    --warning: #ffc107;         /* Estado borrador */
    --danger: #dc3545;          /* Alertas, eliminar */
}

/* Cards de inspección */
.card-inspeccion {
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 12px;
    border-left: 4px solid var(--gold-primary);
}

/* Botones grandes para touch */
.btn-pwa {
    min-height: 48px;
    font-size: 16px;
    border-radius: 8px;
    width: 100%;
    margin-bottom: 8px;
}

.btn-pwa-primary {
    background: var(--gold-primary);
    color: white;
    border: none;
}

.btn-pwa-outline {
    background: white;
    color: var(--primary-dark);
    border: 2px solid var(--primary-dark);
}
```

---

## 7. Compatibilidad

| Dispositivo     | Navegador      | Soporte PWA |
|-----------------|----------------|-------------|
| Android 8+      | Chrome 80+     | Completo    |
| Android 8+      | Samsung Internet| Completo    |
| iOS 14+         | Safari         | Parcial (no push notifications, pero instala OK) |
| Desktop         | Chrome/Edge    | Completo (pero no es el caso de uso) |

**Target principal:** Android + Chrome (mayoría de consultores en Colombia).
