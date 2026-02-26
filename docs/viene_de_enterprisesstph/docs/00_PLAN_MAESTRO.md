# PLAN MAESTRO - Módulo de Inspecciones SST (PWA)

## Resumen Ejecutivo

Nuevo módulo dentro de `enterprisesstph` para gestionar inspecciones de seguridad desde el celular (PWA). Reemplaza AppSheet eliminando la generación manual de PDFs.

**Objetivo:** El consultor abre la PWA en su celular → selecciona cliente → llena la inspección → firma → se genera el PDF automáticamente.

---

## Arquitectura General

```
enterprisesstph/                          (proyecto existente CI4)
├── app/
│   ├── Controllers/
│   │   ├── Inspecciones/                    (módulos del consultor - PWA)
│   │   │   ├── InspeccionesController.php       (dashboard PWA, listados, API AJAX)
│   │   │   ├── ActaVisitaController.php         (CRUD acta de visita + firmas)
│   │   │   ├── InspeccionLocativaController.php  (CRUD inspección locativa)
│   │   │   ├── InspeccionSenalizacionController.php (37 ítems fijos, calificación)
│   │   │   ├── InspeccionExtintoresController.php   (N extintores dinámico, 12 criterios)
│   │   │   ├── InspeccionBotiquinController.php     (32 elementos fijos, cantidades)
│   │   │   ├── InspeccionGabineteController.php     (N gabinetes dinámico, 12 criterios)
│   │   │   ├── InspeccionComunicacionController.php (equipos comunicaciones, plano)
│   │   │   ├── InspeccionRecursosSeguridadController.php (recursos seguridad, plano)
│   │   │   ├── ProbabilidadPeligrosController.php   (probabilidad ocurrencia, 11 peligros)
│   │   │   ├── MatrizVulnerabilidadController.php   (25 criterios A/B/C, score 0-100)
│   │   │   ├── PlanEmergenciaController.php         (doc maestro, ~80 cols, 19 fotos, consolida todo)
│   │   │   ├── EvaluacionSimulacroController.php   (admin: list, view, PDF, finalizar)
│   │   │   └── HvBrigadistaController.php         (admin: list, view, PDF, finalizar)
│   │   ├── SimulacroPublicoController.php     (formulario público sin auth, wizard 9 pasos)
│   │   ├── HvBrigadistaPublicoController.php  (formulario público sin auth, single-page)
│   │   └── ClientInspeccionesController.php (portal cliente: vista read-only de inspecciones)
│   ├── Views/
│   │   ├── inspecciones/                    (vistas consultor - PWA)
│   │   │   ├── layout_pwa.php                  (layout mobile-first, NO sidebar admin)
│   │   │   ├── dashboard.php                   (menú principal inspecciones)
│   │   │   ├── acta_visita/                    (list, form, view, pdf)
│   │   │   ├── inspeccion_locativa/            (list, form, view, pdf)
│   │   │   ├── senalizacion/                   (list, form, view, pdf)
│   │   │   ├── extintores/                     (list, form, view, pdf)
│   │   │   ├── botiquin/                       (list, form, view, pdf)
│   │   │   ├── gabinetes/                      (list, form, view, pdf)
│   │   │   ├── comunicaciones/                 (list, form, view, pdf)
│   │   │   ├── recursos-seguridad/             (list, form, view, pdf)
│   │   │   ├── probabilidad-peligros/          (list, form, view, pdf)
│   │   │   ├── matriz-vulnerabilidad/          (list, form, view, pdf)
│   │   │   ├── plan-emergencia/                (list, form, view, pdf — doc maestro)
│   │   │   ├── simulacro/                    (list, view, pdf — admin read-only)
│   │   │   └── hv-brigadista/                (list, view, pdf — admin read-only)
│   │   ├── simulacro/                         (form_publico.php — wizard público sin auth)
│   │   ├── hv-brigadista/                     (form_publico.php — formulario público sin auth)
│   │   └── client/inspecciones/             (vistas cliente - portal read-only)
│   │       ├── dashboard.php                   (hub con cards por tipo)
│   │       ├── actas/, locativas/, senalizacion/, extintores/, botiquin/
│   │       └── (list + view por cada tipo)
│   ├── Models/
│   │   ├── ActaVisitaModel.php, ActaVisitaIntegranteModel.php, ActaVisitaTemaModel.php
│   │   ├── InspeccionLocativaModel.php, HallazgoLocativoModel.php
│   │   ├── InspeccionSenalizacionModel.php
│   │   ├── InspeccionExtintoresModel.php, ExtintorDetalleModel.php
│   │   ├── InspeccionBotiquinModel.php, ElementoBotiquinModel.php
│   │   ├── InspeccionGabineteModel.php, GabineteDetalleModel.php
│   │   ├── InspeccionComunicacionModel.php
│   │   ├── InspeccionRecursosSeguridadModel.php
│   │   ├── ProbabilidadPeligrosModel.php
│   │   ├── MatrizVulnerabilidadModel.php
│   │   ├── EvaluacionSimulacroModel.php
│   │   ├── HvBrigadistaModel.php
│   │   └── PlanEmergenciaModel.php
│   └── Config/
│       └── Routes.php                    (grupos /inspecciones/* y /client/inspecciones/*)
├── public/
│   ├── manifest_inspecciones.json        (PWA manifest)
│   ├── sw_inspecciones.js                (Service Worker, cache: inspecciones-v3)
│   └── uploads/
│       └── inspecciones/
│           ├── firmas/                       (firmas PNG de actas de visita)
│           ├── fotos/                        (fotos actas de visita)
│           ├── locativas/hallazgos/          (fotos hallazgos locativos)
│           ├── senalizacion/                 (PDFs señalización)
│           ├── extintores/                   (fotos + PDFs extintores)
│           ├── botiquin/                     (PDFs botiquín)
│           ├── gabinetes/                    (fotos + PDFs gabinetes)
│           ├── comunicaciones/               (fotos + PDFs comunicaciones)
│           ├── recursos-seguridad/           (fotos + PDFs recursos seguridad)
│           ├── probabilidad-peligros/        (PDFs probabilidad peligros)
│           ├── matriz-vulnerabilidad/        (PDFs matriz vulnerabilidad)
│           ├── plan-emergencia/              (19 fotos + PDFs plan emergencia)
│           └── pdfs/                         (PDFs actas de visita + locativas)
```

---

## Inspecciones a Implementar (Roadmap)

| # | Inspección | Patron | Estado | id_detailreport |
|---|------------|--------|--------|-----------------|
| 1 | **Acta de Visita** | Especial (firmas) | FUNCIONAL v2 | 9 |
| 2 | **Inspección Locativa** | N-Items (hallazgos) | FUNCIONAL | 10 |
| 3 | **Señalización** | N-Items fijos | FUNCIONAL | 11 |
| 4 | **Extintores** | N-Items dinámico | FUNCIONAL | 12 |
| 5 | **Botiquín** | Plana | FUNCIONAL | 13 |
| 6 | **Gabinetes** | N-Items dinámico | FUNCIONAL | 14 |
| 7 | **Comunicaciones** | Plana | FUNCIONAL | 15 |
| 8 | **Recursos de Seguridad** | Plana | FUNCIONAL | 16 |
| 9 | **Probabilidad Peligros** | Plana (ENUMs) | FUNCIONAL | 17 |
| 10 | **Matriz Vulnerabilidad** | Plana (ENUMs) | FUNCIONAL | 18 |
| 11 | **Plan de Emergencia** | Doc Maestro | FUNCIONAL | 19 |
| 12 | **Ev. Simulacro** | Plana (publico) | FUNCIONAL | 21 |
| 13 | **HV Brigadista** | Plana (publico) | FUNCIONAL | 22 |

**Portal cliente:** `ClientInspeccionesController` ofrece vista read-only de todas las inspecciones completadas por módulo, accesible desde `/client/inspecciones/`.

Cada inspección se documenta en su propio archivo (`01_ACTA_DE_VISITA.md`, `10_INSPECCION_LOCATIVA.md`, etc.)

---

## Decisiones Arquitectónicas

### 1. PWA (Progressive Web App) - IMPLEMENTADO
- `manifest_inspecciones.json` con `start_url: "/inspecciones"`, `scope: "/"`, `display: standalone`
- **Scope `/`** (no `/inspecciones/`) para que el SW intercepte login y otras rutas necesarias
- Service Worker `sw_inspecciones.js`: cache-first para CDN, network-first para paginas locales
- Iconos PWA en `public/icons/` (192x192 y 512x512) generados desde logo Cycloid
- Layout propio `layout_pwa.php` mobile-first con `<link rel="manifest">`, `apple-touch-icon` y registro del SW
- Meta tags iOS: `apple-mobile-web-app-capable`, `apple-mobile-web-app-status-bar-style`, `apple-mobile-web-app-title`

### 2. Autenticación
- Mismo login del sistema (`/login`)
- Sesión CI4 estándar con cookie de larga duración (30 días para rol inspector/consultor en PWA)
- El consultor se loguea una vez, la sesión persiste
- **NO se crean roles nuevos**: el consultor accede a `/inspecciones` desde su celular y al panel admin desde PC

### 3. Generación de PDF - IMPLEMENTADO

- **DOMPDF** con template dedicado `pdf.php` por tipo de inspección
- Header con logo del cliente (base64), datos del SGSST, contenido, firmas (solo Acta de Visita)
- Se guarda en `uploads/inspecciones/{tipo}/` y se auto-registra en `tbl_reporte` via `uploadToReportes()`
- Cada módulo usa su propio `id_detailreport` (ver tabla de roadmap)

### 4. Firmas Digitales
- Canvas HTML5 (mismo patrón de `contrato_firma.php`)
- Protección anti-firma accidental (multi-touch, preview SweetAlert2, validación píxeles)
- Se guardan como PNG en `uploads/inspecciones/firmas/`
- Se incrustan en el PDF generado

### 5. Integración con Datos Existentes

El Acta de Visita jala datos automáticamente de tablas existentes:
- `tbl_clientes` → nombre del cliente, logo
- `tbl_pendientes` → pendientes abiertos del cliente
- `tbl_vencimientos_mantenimientos` → mantenimientos por vencer
- `tbl_hallazgos` → hallazgos locativos abiertos (si existe)
- `tbl_pta_cliente` → actividades del plan de trabajo

Botiquín y Extintores sincronizan vencimientos a `tbl_vencimientos_mantenimientos` al finalizar.

### 6. Rutas CI4 - IMPLEMENTADO

```php
// Consultor PWA (protegido por AuthFilter)
$routes->group('inspecciones', ['filter' => 'auth', 'namespace' => 'App\Controllers\Inspecciones'], function($routes) {
    $routes->get('/', 'InspeccionesController::dashboard');

    // Cada módulo tiene: list, create, store, edit, update, view, generatePdf, finalizar, delete
    // Acta de Visita:         /inspecciones/acta-visita/*
    // Locativa:               /inspecciones/inspeccion-locativa/*
    // Señalización:           /inspecciones/senalizacion/*
    // Extintores:             /inspecciones/extintores/*
    // Botiquín:               /inspecciones/botiquin/*
    // Gabinetes:              /inspecciones/gabinetes/*
    // Comunicaciones:         /inspecciones/comunicaciones/*
    // Recursos Seguridad:     /inspecciones/recursos-seguridad/*
    // Probabilidad Peligros:  /inspecciones/probabilidad-peligros/*
    // Matriz Vulnerabilidad:  /inspecciones/matriz-vulnerabilidad/*
    // Plan de Emergencia:     /inspecciones/plan-emergencia/*
    // Ev. Simulacro (admin):  /inspecciones/simulacro/* (list, view, pdf, finalizar, delete)
    // HV Brigadista (admin):  /inspecciones/hv-brigadista/* (list, view, pdf, finalizar, delete)

    // API endpoints AJAX
    $routes->get('api/clientes', 'InspeccionesController::getClientes');
    $routes->get('api/pendientes/(:num)', 'InspeccionesController::getPendientes/$1');
    $routes->get('api/mantenimientos/(:num)', 'InspeccionesController::getMantenimientos/$1');
});

// Ev. Simulacro (público, sin auth)
$routes->get('simulacro', 'SimulacroPublicoController::form');
$routes->get('simulacro/api/clientes', 'SimulacroPublicoController::getClientesActivos');
$routes->post('simulacro/save-step', 'SimulacroPublicoController::saveStep');
$routes->post('simulacro/upload-foto', 'SimulacroPublicoController::uploadFoto');
$routes->post('simulacro/store', 'SimulacroPublicoController::store');

// HV Brigadista (público, sin auth)
$routes->get('hv-brigadista', 'HvBrigadistaPublicoController::form');
$routes->get('hv-brigadista/api/clientes', 'HvBrigadistaPublicoController::getClientesActivos');
$routes->post('hv-brigadista/store', 'HvBrigadistaPublicoController::store');

// Portal cliente (read-only)
$routes->group('client/inspecciones', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'ClientInspeccionesController::dashboard');
    // Cada tipo: list + view (actas-visita, locativas, senalizacion, extintores, botiquin)
});
```

---

## Stack Tecnológico

| Componente      | Tecnología                              |
|-----------------|-----------------------------------------|
| Backend         | CodeIgniter 4 (PHP 8.2)                |
| Frontend        | Bootstrap 5.3 (mobile-first)           |
| PDF             | DOMPDF                                  |
| Firma           | Canvas HTML5 + SweetAlert2             |
| BD              | MySQL `propiedad_horizontal` (misma BD)|
| PWA             | manifest.json + Service Worker          |
| Cámara/Fotos    | HTML5 `<input type="file" capture>`    |
| Mapas/Ubicación | Geolocation API (coordenadas GPS)      |

---

## Documentos Relacionados

### Inspecciones y Modulos

- [01_ACTA_DE_VISITA.md](./01_ACTA_DE_VISITA.md) - Especificacion completa del Acta de Visita
- [02_DB_ACTA_VISITA.md](./02_DB_ACTA_VISITA.md) - Diseno de base de datos
- [10_INSPECCION_LOCATIVA.md](./10_INSPECCION_LOCATIVA.md) - Inspeccion locativa (hallazgos con fotos)
- [14_PLAN_EMERGENCIA.md](./14_PLAN_EMERGENCIA.md) - Plan de Emergencia (doc maestro, ~80 cols, 19 fotos, 8 ENUMs)

### Patrones Reutilizables

- [12_PATRON_INSPECCION_PLANA.md](./12_PATRON_INSPECCION_PLANA.md) - Patron: 1 tabla, campos fijos (Botiquin, Comunicaciones, Recursos, Prob. Peligros, Matriz Vuln.)
- [13_PATRON_INSPECCION_NITEMS.md](./13_PATRON_INSPECCION_NITEMS.md) - Patron: master+detalle, filas dinamicas (Extintores, Gabinetes)
- [15_PATRON_DOCUMENTO_MAESTRO.md](./15_PATRON_DOCUMENTO_MAESTRO.md) - Patron: doc consolidado que jala datos de TODAS las inspecciones (Plan Emergencia)

### Estrategias Tecnicas

- [03_PWA_LAYOUT.md](./03_PWA_LAYOUT.md) - Diseno del layout PWA y flujo mobile
- [04_ESTRATEGIA_FIRMAS.md](./04_ESTRATEGIA_FIRMAS.md) - Canvas, almacenamiento y flujo presencial de firmas
- [07_ESTRATEGIA_PDF_UPLOAD.md](./07_ESTRATEGIA_PDF_UPLOAD.md) - Auto-cargue de PDF a tbl_reporte, reemplazo del pipeline n8n
- [08_ESTRATEGIA_AUTOGUARDADO.md](./08_ESTRATEGIA_AUTOGUARDADO.md) - localStorage para recuperar formularios ante perdida de sesion
- [09_DISENO_PDF_ACTA.md](./09_DISENO_PDF_ACTA.md) - Diseno del PDF, restricciones DOMPDF, problemas conocidos
- [11_INPUT_FILE_CAMARA_GALERIA.md](./11_INPUT_FILE_CAMARA_GALERIA.md) - Patron dos botones Camara/Galeria para inputs de foto

### Pendientes de implementacion (diseno futuro)

- [05_ESTRATEGIA_OFFLINE.md](./05_ESTRATEGIA_OFFLINE.md) - IndexedDB, Background Sync, pre-carga y sincronizacion
- [06_ESTRATEGIA_NOTIFICACIONES.md](./06_ESTRATEGIA_NOTIFICACIONES.md) - Web Push, SendGrid, recordatorios por cron
