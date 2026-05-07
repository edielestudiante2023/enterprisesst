# Replicación del módulo `inspecciones/acta-capacitacion`

> **Origen:** `enterprisesst` (CodeIgniter 4 + MySQL)
> **Destino:** `enterprisesstph` (CodeIgniter 4 + MySQL)
> **Fecha snapshot:** 2026-05-06
> **Código del documento PDF:** `FT-SST-232` (libre en origen — VERIFICAR disponibilidad en destino, ver §10)

Este documento contiene todo lo necesario para replicar el módulo en otro proyecto gemelo. Snippets son **literales** (copy-paste), no descripciones.

---

## 0. Resumen ejecutivo

Módulo **transversal** que permite a un consultor o a un miembro de comité (cualquiera, no solo COPASST) levantar el **acta de una capacitación**, recolectar firmas de los asistentes a través de **enlaces individuales** (WhatsApp, email o copiar-pegar) o vía **QR de auto-inscripción**, y generar un **PDF consolidado** que se sube al sistema de reportes y se notifica por SendGrid al consultor del cliente.

**Estados:** `borrador` → (firmas remotas asíncronas) → `completo` (al finalizar). Una vez completo no se puede volver a editar.

---

## 1. Inventario de archivos

### 1.1 Archivos PROPIOS del módulo (en origen)

| # | Ruta | Líneas | Propósito |
|---|------|-------:|-----------|
| 1 | `app/Controllers/Inspecciones/ActaCapacitacionController.php` | 854 | Controlador del consultor + endpoints públicos (firma remota e inscripción QR) |
| 2 | `app/Controllers/MiembroActaCapacitacionController.php` | 771 | Controlador del flujo PWA del miembro (transversal a todo comité) |
| 3 | `app/Models/ActaCapacitacionModel.php` | 50 | Modelo `tbl_acta_capacitacion` — usa trait `TenantScopedModel` |
| 4 | `app/Models/ActaCapacitacionAsistenteModel.php` | 37 | Modelo `tbl_acta_capacitacion_asistente` |
| 5 | `app/Views/inspecciones/acta_capacitacion/form.php` | 966 | Formulario crear/editar (acordeón datos + asistentes + JS de firma/email/WhatsApp/QR) |
| 6 | `app/Views/inspecciones/acta_capacitacion/view.php` | 121 | Vista solo lectura (consultor) |
| 7 | `app/Views/inspecciones/acta_capacitacion/list.php` | 60 | Listado del consultor |
| 8 | `app/Views/inspecciones/acta_capacitacion/pdf.php` | 190 | Plantilla Dompdf — encabezado FT-SST-232 + tabla asistentes/firmas |
| 9 | `app/Views/inspecciones/acta_capacitacion/firma_remota.php` | 208 | Vista pública: canvas de firma del asistente |
| 10 | `app/Views/inspecciones/acta_capacitacion/firma_remota_error.php` | 24 | Vista pública: error de firma (link inválido/expirado/usado) |
| 11 | `app/Views/inspecciones/acta_capacitacion/inscripcion_publica.php` | 157 | Vista pública: form auto-inscripción al escanear QR |
| 12 | `app/Views/inspecciones/acta_capacitacion/inscripcion_error.php` | 22 | Vista pública: error de inscripción |
| 13 | `app/Views/inspecciones/miembro/acta_capacitacion_list.php` | 63 | Listado del miembro (PWA) |
| 14 | `app/Views/inspecciones/miembro/acta_capacitacion_view.php` | 121 | Vista solo lectura del miembro (PWA) |
| 15 | `app/SQL/migrate_acta_capacitacion.php` | 171 | CLI: crea las 2 tablas (con `--prod` para DigitalOcean SSL) |
| 16 | `app/SQL/agregar_token_inscripcion_acta_capacitacion.php` | 57 | CLI idempotente: agrega columna `token_inscripcion` (segunda iteración) |
| 17 | `sql/migrate_acta_capacitacion_qr.sql` | 10 | SQL crudo equivalente al anterior |
| 18 | `cli_recover_firmas_capacitacion.php` | 180 | Recovery: PNGs huérfanos de asistentes borrados (no se necesita en destino limpio) |

### 1.2 Archivos COMPARTIDOS que el módulo TOCA / referencia

| Ruta | Qué hace | Lo modifica el módulo? |
|------|----------|---|
| `app/Config/Routes.php` | Definición de rutas | **SÍ** — agrega ~30 líneas (ver §2) |
| `app/Views/inspecciones/dashboard.php` | Dashboard del consultor (Inspecciones) | **SÍ** — agrega tarjeta "Actas Capacitacion" (ver §1.3) |
| `app/Views/actas/miembro_auth/dashboard.php` | Dashboard del miembro (PWA `/miembro`) | **SÍ** — agrega tarjeta transversal "Acta de Capacitacion" |
| `app/Views/inspecciones/layout_pwa.php` | Layout PWA del consultor | NO — solo lo consume |
| `app/Views/inspecciones/miembro/layout_pwa_miembro.php` | Layout PWA del miembro | NO — solo lo consume |
| `app/Traits/AutosaveJsonTrait.php` | Trait reutilizado | NO — solo lo consume |
| `app/Models/Traits/TenantScopedModel.php` | Filtro multi-tenant en queries | NO — solo lo consume el `ActaCapacitacionModel` |
| `app/Libraries/TenantFilter.php` | Indirecto: usado por `TenantScopedModel` | NO |
| `composer.json` | Dependencias | **SÍ** — requiere `dompdf/dompdf`, `sendgrid/sendgrid`, `chillerlan/php-qrcode` |
| `app/Models/ClientModel.php` | Lookups de cliente | NO — solo lectura (`find($id_cliente)`) |
| `app/Models/ConsultantModel.php` | Lookups de consultor (notificación email) | NO — solo lectura |
| `app/Models/MiembroComiteModel.php` | Resuelve miembro autenticado en PWA | NO — solo lectura |
| `app/Models/ReporteModel.php` | Inserta/actualiza fila en `tbl_reporte` al finalizar | NO — solo `save/update` |

### 1.3 Snippet exacto de la tarjeta del dashboard del consultor

`app/Views/inspecciones/dashboard.php` líneas 393-397 (existe enlace + contador):

```php
<a href="<?= site_url('inspecciones/acta-capacitacion') ?>" class="card-tipo">
    <i class="fas fa-graduation-cap"></i>
    <div><strong>Actas Capacitacion</strong></div>
    <div class="count">(<?= $totalCapacitaciones ?? 0 ?>)</div>
</a>
```

Y bloque de continuación-de-borrador (líneas 256-266) — botones **Continuar editando** y **Eliminar**.

### 1.4 Snippet exacto de la tarjeta del dashboard del miembro

`app/Views/actas/miembro_auth/dashboard.php` líneas 38-50:

```php
<!-- Acta de Capacitacion: TRANSVERSAL — visible para todos los comites -->
<div class="col-md-6 mb-3">
    <a href="<?= base_url('miembro/acta-capacitacion') ?>" class="card border-0 shadow-sm text-decoration-none h-100">
        <div class="card-body d-flex align-items-center">
            <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                <i class="bi bi-mortarboard text-primary fs-4"></i>
            </div>
            <div>
                <h6 class="mb-1">Acta de Capacitación</h6>
                <small class="text-muted">Levantar acta + firmas asistentes</small>
            </div>
        </div>
    </a>
</div>
```

---

## 2. Rutas del aplicativo

Todas viven en `app/Config/Routes.php`. Líneas exactas (origen):

### 2.1 Rutas PÚBLICAS (sin autenticación — token = autenticación)

| Línea | Método | URL | Controlador::método |
|---:|---|---|---|
| 1375 | GET | `acta-capacitacion/firmar-remoto/(:any)` | `Inspecciones\ActaCapacitacionController::firmarRemoto/$1` |
| 1376 | POST | `acta-capacitacion/procesar-firma-remota` | `Inspecciones\ActaCapacitacionController::procesarFirmaRemota` |
| 1379 | GET | `acta-capacitacion/inscripcion/(:any)` | `Inspecciones\ActaCapacitacionController::inscripcion/$1` |
| 1380 | POST | `acta-capacitacion/procesar-inscripcion` | `Inspecciones\ActaCapacitacionController::procesarInscripcion` |

```php
$routes->get('acta-capacitacion/firmar-remoto/(:any)', 'Inspecciones\ActaCapacitacionController::firmarRemoto/$1');
$routes->post('acta-capacitacion/procesar-firma-remota', 'Inspecciones\ActaCapacitacionController::procesarFirmaRemota');
$routes->get('acta-capacitacion/inscripcion/(:any)', 'Inspecciones\ActaCapacitacionController::inscripcion/$1');
$routes->post('acta-capacitacion/procesar-inscripcion', 'Inspecciones\ActaCapacitacionController::procesarInscripcion');
```

> ⚠️ Estas rutas viven en **el nivel raíz** del archivo (fuera de cualquier `$routes->group()`). Es crítico — si quedan dentro de un grupo con filtros de auth, el QR público no funciona.

### 2.2 Rutas del MIEMBRO (PWA, prefijo `/miembro`)

Bloque dentro de `$routes->group('miembro', ['filter' => '...'], function() { ... })` — líneas 1622-1635:

```php
$routes->get('acta-capacitacion', 'MiembroActaCapacitacionController::list');
$routes->get('acta-capacitacion/create', 'MiembroActaCapacitacionController::create');
$routes->post('acta-capacitacion/store', 'MiembroActaCapacitacionController::store');
$routes->get('acta-capacitacion/edit/(:num)', 'MiembroActaCapacitacionController::edit/$1');
$routes->post('acta-capacitacion/update/(:num)', 'MiembroActaCapacitacionController::update/$1');
$routes->get('acta-capacitacion/view/(:num)', 'MiembroActaCapacitacionController::view/$1');
$routes->get('acta-capacitacion/pdf/(:num)', 'MiembroActaCapacitacionController::generatePdf/$1');
$routes->post('acta-capacitacion/finalizar/(:num)', 'MiembroActaCapacitacionController::finalizar/$1');
$routes->post('acta-capacitacion/generar-token-firma/(:num)', 'MiembroActaCapacitacionController::generarTokenFirma/$1');
$routes->post('acta-capacitacion/asistente/save/(:num)', 'MiembroActaCapacitacionController::saveAsistente/$1');
$routes->post('acta-capacitacion/asistente/delete/(:num)/(:num)', 'MiembroActaCapacitacionController::deleteAsistente/$1/$2');
$routes->post('acta-capacitacion/asistente/enviar-email/(:num)', 'MiembroActaCapacitacionController::enviarEmailFirma/$1');
$routes->get('acta-capacitacion/asistentes-status/(:num)', 'MiembroActaCapacitacionController::getAsistentesStatus/$1');
$routes->post('acta-capacitacion/generar-token-inscripcion/(:num)', 'MiembroActaCapacitacionController::generarTokenInscripcion/$1');
```

### 2.3 Rutas del CONSULTOR (prefijo `/inspecciones`)

Bloque dentro de `$routes->group('inspecciones', ['filter' => '...'], function() { ... })` — líneas 1712-1727:

```php
$routes->get('acta-capacitacion', 'ActaCapacitacionController::list');
$routes->get('acta-capacitacion/create', 'ActaCapacitacionController::create');
$routes->get('acta-capacitacion/create/(:num)', 'ActaCapacitacionController::create/$1');
$routes->post('acta-capacitacion/store', 'ActaCapacitacionController::store');
$routes->get('acta-capacitacion/edit/(:num)', 'ActaCapacitacionController::edit/$1');
$routes->post('acta-capacitacion/update/(:num)', 'ActaCapacitacionController::update/$1');
$routes->get('acta-capacitacion/view/(:num)', 'ActaCapacitacionController::view/$1');
$routes->get('acta-capacitacion/pdf/(:num)', 'ActaCapacitacionController::generatePdf/$1');
$routes->post('acta-capacitacion/finalizar/(:num)', 'ActaCapacitacionController::finalizar/$1');
$routes->post('acta-capacitacion/generar-token-firma/(:num)', 'ActaCapacitacionController::generarTokenFirma/$1');
$routes->post('acta-capacitacion/asistente/save/(:num)', 'ActaCapacitacionController::saveAsistente/$1');
$routes->post('acta-capacitacion/asistente/delete/(:num)/(:num)', 'ActaCapacitacionController::deleteAsistente/$1/$2');
$routes->post('acta-capacitacion/asistente/enviar-email/(:num)', 'ActaCapacitacionController::enviarEmailFirma/$1');
$routes->get('acta-capacitacion/asistentes-status/(:num)', 'ActaCapacitacionController::getAsistentesStatus/$1');
$routes->post('acta-capacitacion/generar-token-inscripcion/(:num)', 'ActaCapacitacionController::generarTokenInscripcion/$1');
$routes->get('acta-capacitacion/delete/(:num)', 'ActaCapacitacionController::delete/$1');
```

> ⚠️ El controlador del consultor NO usa el namespace `App\Controllers\Inspecciones\` para todas las rutas — solo los endpoints públicos (líneas 1375-1380) lo invocan con namespace explícito. Las rutas del consultor lo invocan **sin** namespace porque están dentro del grupo `'inspecciones'` que **ya** tiene namespace base. Para los endpoints públicos, sí se usa el namespace completo. Es sutil pero importante.

### 2.4 Tabla de endpoints AJAX por contrato

| URL | Método | Body | Response JSON |
|---|---|---|---|
| `inspecciones/api/clientes` | GET | — | `[{id_cliente, nombre_cliente}, ...]` (consume el `<select>` del form) |
| `*/asistente/save/{idActa}` | POST | `id_asistente?, nombre_completo, tipo_documento, numero_documento, cargo, area_dependencia, email, celular, orden` | `{success, id, asistente: {...}}` |
| `*/asistente/delete/{idActa}/{idAsistente}` | POST | csrf | `{success, error?}` |
| `*/asistente/enviar-email/{idAsistente}` | POST | csrf | `{success, email, error?}` |
| `*/asistentes-status/{idActa}` | GET | — | `{success, total, firmados, pct, asistentes:[{id,nombre_completo,firmado,firmado_at,enlace_enviado}]}` |
| `*/generar-token-firma/{idAsistente}` | POST | csrf | `{success, url, nombre}` |
| `*/generar-token-inscripcion/{idActa}` | POST | csrf, `regenerar?=1` | `{success, token, url, qr_svg}` |
| `*/finalizar/{id}` | POST | csrf, `finalizar=1` | redirect HTML (genera PDF + sube + notifica) |
| `acta-capacitacion/procesar-firma-remota` | POST | `token, firma_imagen` (base64) | `{success, error?}` |
| `acta-capacitacion/procesar-inscripcion` | POST | `token, nombre_completo, tipo_documento, numero_documento, cargo, area_dependencia, email, celular` | `{success, id_asistente, url_firmar}` o `{success:false, duplicado?:true, error}` |

---

## 3. Estructura de Base de Datos

### 3.1 Tablas PROPIAS del módulo

#### `tbl_acta_capacitacion`

```sql
CREATE TABLE IF NOT EXISTS `tbl_acta_capacitacion` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_cliente` INT NOT NULL,
    `id_comite` INT NULL DEFAULT NULL COMMENT 'Opcional: NULL si consultor crea sin comité',
    `creado_por_tipo` ENUM('miembro','consultor') NOT NULL,
    `id_miembro` INT NULL DEFAULT NULL,
    `id_consultor` INT NULL DEFAULT NULL,

    -- Datos de la capacitación
    `tema` VARCHAR(255) NOT NULL,
    `fecha_capacitacion` DATE NOT NULL,
    `hora_inicio` TIME NULL,
    `hora_fin` TIME NULL,
    `dictada_por` ENUM('ARL','Consultor','Empresa','Otro') NOT NULL DEFAULT 'ARL',
    `nombre_capacitador` VARCHAR(200) NULL,
    `entidad_capacitadora` VARCHAR(200) NULL,
    `modalidad` ENUM('virtual','presencial','mixta') NOT NULL DEFAULT 'virtual',
    `enlace_grabacion` VARCHAR(500) NULL,
    `objetivos` TEXT NULL,
    `contenido` TEXT NULL,
    `observaciones` TEXT NULL,

    -- PDF
    `ruta_pdf` VARCHAR(255) NULL,

    -- QR auto-inscripcion (segunda iteracion)
    `token_inscripcion` VARCHAR(64) NULL DEFAULT NULL,

    -- Estado y tracking
    `estado` ENUM('borrador','esperando_firmas','completo') NOT NULL DEFAULT 'borrador',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX `idx_acta_cap_cliente` (`id_cliente`),
    INDEX `idx_acta_cap_comite` (`id_comite`),
    INDEX `idx_acta_cap_fecha` (`fecha_capacitacion`),
    INDEX `idx_acta_cap_estado` (`estado`),
    INDEX `idx_acta_cap_creador` (`creado_por_tipo`, `id_miembro`, `id_consultor`),
    INDEX `idx_token_inscripcion` (`token_inscripcion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### `tbl_acta_capacitacion_asistente`

```sql
CREATE TABLE IF NOT EXISTS `tbl_acta_capacitacion_asistente` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_acta_capacitacion` INT NOT NULL,

    `nombre_completo` VARCHAR(200) NOT NULL,
    `tipo_documento` ENUM('CC','CE','PA','TI','NIT') DEFAULT 'CC',
    `numero_documento` VARCHAR(20) NULL,
    `cargo` VARCHAR(150) NULL,
    `area_dependencia` VARCHAR(150) NULL,
    `email` VARCHAR(150) NULL,
    `celular` VARCHAR(30) NULL,

    -- Token y firma remota
    `token_firma` VARCHAR(64) NULL DEFAULT NULL,
    `token_expiracion` DATETIME NULL DEFAULT NULL,
    `firma_path` VARCHAR(255) NULL DEFAULT NULL,
    `firmado_at` DATETIME NULL DEFAULT NULL,

    `orden` INT NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT `fk_asistente_acta_cap`
        FOREIGN KEY (`id_acta_capacitacion`) REFERENCES `tbl_acta_capacitacion`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,

    INDEX `idx_asistente_acta` (`id_acta_capacitacion`),
    UNIQUE KEY `uniq_token_firma` (`token_firma`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

> **NOTA:** No hay datos semilla. Las tablas nacen vacías. La columna `token_inscripcion` se agregó después en una segunda migración — ya está incluida arriba. Si replicas con un solo `CREATE TABLE` esto está OK; si replicas con dos migraciones separadas (como en origen), usa el archivo `agregar_token_inscripcion_acta_capacitacion.php`.

### 3.2 Tablas del SISTEMA que el módulo CONSULTA

| Tabla | Campos usados | Tipo de uso |
|---|---|---|
| `tbl_clientes` | `id_cliente, nombre_cliente, nit_cliente, logo, id_consultor` | Lookup en list/view/PDF/email; `nit_cliente` define carpeta `public/uploads/{nit}/` para el PDF subido al sistema de reportes |
| `tbl_consultor` | `id_consultor, nombre_consultor, correo_consultor` | Lookup para mostrar autor; `correo_consultor` es destino del email cuando un miembro finaliza |
| `tbl_comites` | `id_comite` | Solo FK opcional (NULL permitido) |
| `tbl_comite_miembros` | `id_miembro, nombre_completo, email, ...` | Resuelve miembro autenticado vía `MiembroComiteModel::getByEmailYCliente` y `getComitesPorEmail` |
| `tbl_reporte` | `id_reporte, titulo_reporte, id_detailreport, id_report_type, id_cliente, estado, observaciones, enlace, created_at, updated_at` | INSERT/UPDATE al finalizar, con `id_report_type=4`, `id_detailreport=6` y marca `acta_capacitacion_id:{id}` en `observaciones` para idempotencia |

---

## 4. Flujo funcional

### 4.1 Estados y transiciones

```
[no existe]
    ↓ POST store
[borrador] ←————— UPDATE update
    ↓ POST finalizar (genera PDF + sube + notifica)
[completo]   (no editable, solo ver/PDF)
```

> El estado `esperando_firmas` está declarado en el ENUM pero **nunca se usa** en el código actual. Se mantiene por extensibilidad.

### 4.2 Métodos del controlador (ambos comparten la misma estructura)

| Método | Qué hace |
|---|---|
| `list()` | Lista actas con conteo `total_asistentes` y `total_firmados`; ordena por `fecha_capacitacion DESC` |
| `create($idCliente?)` | Render del form vacío |
| `store()` | INSERT en `tbl_acta_capacitacion` (estado `borrador`) + `saveAsistentes()` desde el form bulk |
| `edit($id)` | Render del form con datos; bloquea si `estado=completo` |
| `update($id)` | UPDATE + `saveAsistentes()`; si POST trae `finalizar=1` redirige a `finalizar()` |
| `view($id)` | Render solo lectura |
| `saveAsistente($idActa)` | **AJAX**: insert/update de UN asistente (usado por botón "Guardar este asistente") |
| `deleteAsistente($idActa, $idAsistente)` | **AJAX**: elimina UN asistente; bloquea si ya firmó |
| `getAsistentesStatus($idActa)` | **AJAX**: snapshot del estado de firmas para refrescar UI sin recargar |
| `generarTokenFirma($idAsistente)` | **AJAX**: genera token (32 hex) + expiración +7d; retorna URL |
| `enviarEmailFirma($idAsistente)` | **AJAX**: reutiliza/genera token y envía email vía SendGrid |
| `generarTokenInscripcion($idActa)` | **AJAX**: genera/reutiliza token (24 hex) del acta + QR SVG |
| `finalizar($id)` | Genera PDF, marca `estado=completo`, sube a `tbl_reporte`, notifica al consultor (solo en miembro) |
| `generatePdf($id)` | Streamea el PDF inline (preview/descarga) |
| `delete($id)` | Solo consultor; bloquea si `estado=completo` |
| `firmarRemoto($token)` | **PÚBLICO**: render del canvas; valida token vivo y no usado |
| `procesarFirmaRemota()` | **PÚBLICO**: guarda PNG en `uploads/inspecciones/firmas_capacitacion/`, anula token (one-shot) |
| `inscripcion($token)` | **PÚBLICO**: render del form de auto-inscripción (QR) |
| `procesarInscripcion()` | **PÚBLICO**: anti-duplicado por `numero_documento` dentro del acta; INSERT asistente + token de firma → redirige al canvas |
| `saveAsistentes($idActa)` (private) | **NO-DESTRUCTIVO**: bulk insert/update por arrays `asistente_*[]`; nunca borra (la eliminación es solo vía `deleteAsistente`) |
| `generarPdfInterno($id)` (private) | Renderiza vista `pdf` con Dompdf (Letter portrait), guarda en `uploads/inspecciones/actas_capacitacion/pdfs/`; reemplaza el PDF anterior si existe |
| `uploadToReportes($acta, $pdfPath)` (private) | Idempotente vía `like('observaciones', 'acta_capacitacion_id:'.$id)` |
| `notificarConsultor($cliente, $miembro, $acta)` (private, **solo Miembro**) | Email SendGrid al consultor del cliente |

### 4.3 Diagrama de secuencia — flujo completo

```
[Creador]                  [Backend]                  [Asistente celular]
   |                           |                              |
   | GET create               |                              |
   |--------------------------→|                              |
   |←--- form vacío -----------|                              |
   |                           |                              |
   | POST store               |                              |
   |--------------------------→| INSERT acta (borrador)       |
   |                           | INSERT asistentes[]          |
   |←--- redirect edit/{id} ---|                              |
   |                           |                              |
   | (3 vías para enviar enlace)                              |
   |                           |                              |
   |  --vía A: WhatsApp--      |                              |
   | POST generar-token-firma  |                              |
   |--------------------------→| UPDATE asistente token+exp   |
   |←--- {url} ----------------|                              |
   | abre wa.me?text=...       |                              |
   | manda al contacto -------------------------------→ recibe |
   |                           |                              |
   |  --vía B: Email--         |                              |
   | POST asistente/enviar-email                              |
   |--------------------------→| (genera/reusa token)         |
   |                           | SendGrid.send(...)  -------→ recibe email
   |←--- {success:true} -------|                              |
   |                           |                              |
   |  --vía C: QR--            |                              |
   | POST generar-token-inscripcion                           |
   |--------------------------→| UPDATE acta token_inscripcion|
   |←--- {url, qr_svg} --------|                              |
   | imprime / muestra QR ----------------- escanea --------→ |
   |                           |                              |
   |                           | GET inscripcion/{token}     ←|
   |                           |←--- form datos asistente ---→|
   |                           |     POST procesar-inscripcion|
   |                           | INSERT asistente +token      |
   |                           |←--- {url_firmar} ------------|
   |                           |                              |
   |                           | GET firmar-remoto/{token}   ←|
   |                           |←--- canvas firma -----------→|
   |                           |     POST procesar-firma-remota|
   |                           | guarda PNG, UPDATE asistente |
   |                           |←--- {success:true} ----------|
   |                           |                              |
   | POST finalizar           |                              |
   |--------------------------→| Dompdf → PDF                 |
   |                           | UPDATE acta estado=completo  |
   |                           | INSERT/UPDATE tbl_reporte    |
   |                           | SendGrid → consultor (solo si creador=miembro)
   |←--- redirect view/{id} ---|                              |
```

---

## 5. Snippets críticos

### 5.1 Modelo `ActaCapacitacionModel`

`app/Models/ActaCapacitacionModel.php`:

```php
<?php
namespace App\Models;

use CodeIgniter\Model;
use App\Models\Traits\TenantScopedModel;

class ActaCapacitacionModel extends Model
{
    use TenantScopedModel;  // ← omitir en destino si no existe (ver §10)

    protected $table = 'tbl_acta_capacitacion';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_comite', 'creado_por_tipo', 'id_miembro', 'id_consultor',
        'tema', 'fecha_capacitacion', 'hora_inicio', 'hora_fin',
        'dictada_por', 'nombre_capacitador', 'entidad_capacitadora',
        'modalidad', 'enlace_grabacion', 'objetivos', 'contenido', 'observaciones',
        'ruta_pdf', 'estado',
        'token_inscripcion',
        'created_at', 'updated_at',
    ];
    protected $useTimestamps = true;

    public function findByTokenInscripcion(string $token): ?array
    {
        if (empty($token)) return null;
        return $this->where('token_inscripcion', $token)->first();
    }

    public function getByCliente(int $idCliente): array
    {
        return $this->where('id_cliente', $idCliente)
            ->orderBy('fecha_capacitacion', 'DESC')
            ->findAll();
    }

    public function getAllPendientes(): array
    {
        return $this->select('tbl_acta_capacitacion.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_acta_capacitacion.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_acta_capacitacion.id_consultor', 'left')
            ->whereIn('tbl_acta_capacitacion.estado', ['borrador', 'pendiente_firma'])
            ->orderBy('tbl_acta_capacitacion.fecha_capacitacion', 'DESC')
            ->findAll();
    }
}
```

### 5.2 Modelo `ActaCapacitacionAsistenteModel`

`app/Models/ActaCapacitacionAsistenteModel.php` — completo, 37 líneas:

```php
<?php
namespace App\Models;

use CodeIgniter\Model;

class ActaCapacitacionAsistenteModel extends Model
{
    protected $table = 'tbl_acta_capacitacion_asistente';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_acta_capacitacion',
        'nombre_completo', 'tipo_documento', 'numero_documento',
        'cargo', 'area_dependencia', 'email', 'celular',
        'token_firma', 'token_expiracion', 'firma_path', 'firmado_at',
        'orden', 'created_at',
    ];
    protected $useTimestamps = false;

    public function getByActa(int $idActa): array
    {
        return $this->where('id_acta_capacitacion', $idActa)
            ->orderBy('orden', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    public function getByToken(string $token): ?array
    {
        return $this->where('token_firma', $token)->first();
    }

    public function deleteByActa(int $idActa): bool
    {
        return $this->where('id_acta_capacitacion', $idActa)->delete();
    }
}
```

### 5.3 Trait `AutosaveJsonTrait` (ya existe en destino — verificado)

`app/Traits/AutosaveJsonTrait.php` — completo, 29 líneas:

```php
<?php
namespace App\Traits;

trait AutosaveJsonTrait
{
    protected function isAutosaveRequest(): bool
    {
        return $this->request->isAJAX()
            || $this->request->getHeaderLine('X-Autosave') === '1';
    }

    protected function autosaveJsonSuccess(int $id, array $extra = [])
    {
        return $this->response->setJSON(array_merge([
            'success'  => true,
            'id'       => $id,
            'saved_at' => date('H:i:s'),
        ], $extra));
    }

    protected function autosaveJsonError(string $message, int $statusCode = 400)
    {
        return $this->response->setJSON([
            'success' => false,
            'message' => $message,
        ])->setStatusCode($statusCode);
    }
}
```

### 5.4 Controlador del consultor — esqueleto + métodos clave

> **Archivo completo:** `app/Controllers/Inspecciones/ActaCapacitacionController.php` (854 líneas) — copiar **literal** al destino. Aquí los snippets críticos para entender el ruteo de lógica.

#### 5.4.1 Constructor + `list`

```php
namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\ActaCapacitacionModel;
use App\Models\ActaCapacitacionAsistenteModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use App\Models\MiembroComiteModel;
use App\Traits\AutosaveJsonTrait;
use Dompdf\Dompdf;

class ActaCapacitacionController extends BaseController
{
    use AutosaveJsonTrait;

    protected ActaCapacitacionModel $actaModel;
    protected ActaCapacitacionAsistenteModel $asistenteModel;

    public function __construct()
    {
        $this->actaModel = new ActaCapacitacionModel();
        $this->asistenteModel = new ActaCapacitacionAsistenteModel();
    }

    public function list()
    {
        $actas = $this->actaModel
            ->select('tbl_acta_capacitacion.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_acta_capacitacion.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_acta_capacitacion.id_consultor', 'left')
            ->orderBy('tbl_acta_capacitacion.fecha_capacitacion', 'DESC')
            ->findAll();

        foreach ($actas as &$a) {
            $a['total_asistentes'] = $this->asistenteModel
                ->where('id_acta_capacitacion', $a['id'])->countAllResults(false);
            $a['total_firmados'] = $this->asistenteModel
                ->where('id_acta_capacitacion', $a['id'])
                ->where('firma_path IS NOT NULL', null, false)->countAllResults(false);
        }

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/acta_capacitacion/list', ['actas' => $actas]),
            'title'   => 'Actas de Capacitación',
        ]);
    }
}
```

#### 5.4.2 Procesar firma remota (público — endpoint clave)

```php
public function procesarFirmaRemota()
{
    $token       = $this->request->getPost('token');
    $firmaBase64 = $this->request->getPost('firma_imagen');

    if (!$token || !$firmaBase64) {
        return $this->response->setJSON(['success' => false, 'error' => 'Datos incompletos']);
    }

    $asistente = $this->asistenteModel->getByToken($token);
    if (!$asistente) {
        return $this->response->setJSON(['success' => false, 'error' => 'Enlace inválido']);
    }
    if ($asistente['token_expiracion'] && strtotime($asistente['token_expiracion']) < time()) {
        return $this->response->setJSON(['success' => false, 'error' => 'Enlace expirado']);
    }
    if (!empty($asistente['firma_path'])) {
        return $this->response->setJSON(['success' => false, 'error' => 'Ya firmado']);
    }

    $firmaData    = explode(',', $firmaBase64);
    $firmaDecoded = base64_decode(end($firmaData));
    if ($firmaDecoded === false) {
        return $this->response->setJSON(['success' => false, 'error' => 'Firma inválida']);
    }

    $dir = FCPATH . 'uploads/inspecciones/firmas_capacitacion/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $nombreArchivo = 'firma_cap_' . $asistente['id'] . '_' . time() . '.png';
    file_put_contents($dir . $nombreArchivo, $firmaDecoded);

    $this->asistenteModel->update($asistente['id'], [
        'firma_path'       => 'uploads/inspecciones/firmas_capacitacion/' . $nombreArchivo,
        'firmado_at'       => date('Y-m-d H:i:s'),
        'token_firma'      => null,
        'token_expiracion' => null,
    ]);

    return $this->response->setJSON(['success' => true]);
}
```

#### 5.4.3 Generar PDF (Dompdf)

```php
private function generarPdfInterno(int $id): ?string
{
    $acta = $this->actaModel->find($id);
    $clientModel = new ClientModel();
    $consultantModel = new ConsultantModel();
    $miembroModel = new MiembroComiteModel();
    $cliente = $clientModel->find($acta['id_cliente']);
    $consultor = $acta['id_consultor'] ? $consultantModel->find($acta['id_consultor']) : null;
    $asistentes = $this->asistenteModel->getByActa($id);

    $realizadoPor = null;
    if ($acta['creado_por_tipo'] === 'miembro' && $acta['id_miembro']) {
        $m = $miembroModel->find($acta['id_miembro']);
        $realizadoPor = $m['nombre_completo'] ?? 'Miembro';
    }

    $logoBase64 = '';
    if (!empty($cliente['logo'])) {
        $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:' . mime_content_type($logoPath) . ';base64,' . base64_encode(file_get_contents($logoPath));
        }
    }

    foreach ($asistentes as &$a) {
        $a['firma_base64'] = '';
        if (!empty($a['firma_path']) && file_exists(FCPATH . $a['firma_path'])) {
            $a['firma_base64'] = 'data:image/png;base64,' . base64_encode(file_get_contents(FCPATH . $a['firma_path']));
        }
    }
    unset($a);

    $html = view('inspecciones/acta_capacitacion/pdf', [
        'acta'         => $acta,
        'cliente'      => $cliente,
        'consultor'    => $consultor,
        'realizadoPor' => $realizadoPor,
        'asistentes'   => $asistentes,
        'logoBase64'   => $logoBase64,
    ]);

    $options = new \Dompdf\Options();
    $options->set('isRemoteEnabled', true);
    $options->set('isHtml5ParserEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('letter', 'portrait');
    $dompdf->render();

    $pdfDir = 'uploads/inspecciones/actas_capacitacion/pdfs/';
    if (!is_dir(FCPATH . $pdfDir)) mkdir(FCPATH . $pdfDir, 0755, true);

    $pdfFileName = 'acta_capacitacion_' . $id . '_' . date('Ymd_His') . '.pdf';
    $pdfPath = $pdfDir . $pdfFileName;

    if (!empty($acta['ruta_pdf']) && file_exists(FCPATH . $acta['ruta_pdf'])) {
        unlink(FCPATH . $acta['ruta_pdf']);
    }

    file_put_contents(FCPATH . $pdfPath, $dompdf->output());
    return $pdfPath;
}
```

#### 5.4.4 Subida a `tbl_reporte` (idempotente)

```php
private function uploadToReportes(array $acta, string $pdfPath): bool
{
    $reporteModel = new ReporteModel();
    $clientModel = new ClientModel();
    $cliente = $clientModel->find($acta['id_cliente']);
    if (!$cliente) return false;

    $nitCliente = $cliente['nit_cliente'] ?? '';
    $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
    if (!is_dir($destDir)) mkdir($destDir, 0755, true);

    $fileName = 'acta_capacitacion_' . $acta['id'] . '_' . date('Ymd_His') . '.pdf';
    copy(FCPATH . $pdfPath, $destDir . '/' . $fileName);

    $data = [
        'titulo_reporte'  => 'ACTA DE CAPACITACION - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $acta['fecha_capacitacion'],
        'id_detailreport' => 6,
        'id_report_type'  => 4,
        'id_cliente'      => $acta['id_cliente'],
        'estado'          => 'CERRADO',
        'observaciones'   => 'Generado por consultor. acta_capacitacion_id:' . $acta['id'],
        'enlace'          => base_url('uploads/' . $nitCliente . '/' . $fileName),
        'updated_at'      => date('Y-m-d H:i:s'),
    ];

    $existente = $reporteModel->where('id_cliente', $acta['id_cliente'])
        ->where('id_report_type', 4)
        ->where('id_detailreport', 6)
        ->like('observaciones', 'acta_capacitacion_id:' . $acta['id'])
        ->first();

    if ($existente) return $reporteModel->update($existente['id_reporte'], $data);
    $data['created_at'] = date('Y-m-d H:i:s');
    return $reporteModel->save($data);
}
```

#### 5.4.5 Generación de QR (con fallback a API externa)

```php
private function generarQrSvg(string $url): string
{
    if (class_exists('\\chillerlan\\QRCode\\QRCode')) {
        try {
            $opts = new \chillerlan\QRCode\QROptions([
                'outputType' => \chillerlan\QRCode\QRCode::OUTPUT_MARKUP_SVG,
                'eccLevel'   => \chillerlan\QRCode\QRCode::ECC_M,
                'scale'      => 8,
                'imageBase64'=> false,
            ]);
            return (new \chillerlan\QRCode\QRCode($opts))->render($url);
        } catch (\Throwable $e) {
            log_message('error', 'QR local fallo, fallback a api externa: ' . $e->getMessage());
        }
    }
    // Fallback: imagen via API externa
    $apiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=' . urlencode($url);
    return '<img src="' . esc($apiUrl) . '" alt="QR" style="width:100%;height:auto;">';
}
```

### 5.5 `MiembroActaCapacitacionController` — resolución del miembro autenticado

> **Archivo completo:** 771 líneas, copiar literal. Snippet clave: cómo identifica al miembro logueado SIN filtrar por COPASST (es transversal).

```php
private function getMiembroAny(): ?array
{
    $session   = session();
    $email     = $session->get('email_miembro');
    $idCliente = $session->get('user_id');
    if (!$email || !$idCliente) return null;

    $miembro = $this->miembroModel->getByEmailYCliente($email, $idCliente);
    if (!$miembro) return null;

    $comites = $this->miembroModel->getComitesPorEmail($email, $idCliente);
    if (empty($comites)) return null;

    $miembro['id_cliente'] = $idCliente;
    return $miembro;
}
```

### 5.6 Form view — primera mitad (datos generales)

`app/Views/inspecciones/acta_capacitacion/form.php` — usado por **ambos contextos** (consultor + miembro). Diferenciación por variable `$contexto = 'consultor'|'miembro'`. **Copiar literal**, son 966 líneas.

Bloque crítico de mapeo de URLs según contexto (líneas 1-26):

```php
<?php
$isEdit = !empty($acta);
$ctx = $contexto ?? 'miembro';
$baseUrl = $ctx === 'consultor' ? 'inspecciones/acta-capacitacion' : 'miembro/acta-capacitacion';
$action = $isEdit
    ? site_url($baseUrl . '/update/' . $acta['id'])
    : site_url($baseUrl . '/store');
$tokenUrlBase = $ctx === 'consultor'
    ? 'inspecciones/acta-capacitacion/generar-token-firma/'
    : 'miembro/acta-capacitacion/generar-token-firma/';
$saveAsistUrlBase = $ctx === 'consultor'
    ? 'inspecciones/acta-capacitacion/asistente/save/'
    : 'miembro/acta-capacitacion/asistente/save/';
$emailUrlBase = $ctx === 'consultor'
    ? 'inspecciones/acta-capacitacion/asistente/enviar-email/'
    : 'miembro/acta-capacitacion/asistente/enviar-email/';
$deleteAsistUrlBase = $ctx === 'consultor'
    ? 'inspecciones/acta-capacitacion/asistente/delete/'
    : 'miembro/acta-capacitacion/asistente/delete/';
$statusUrlBase = $ctx === 'consultor'
    ? 'inspecciones/acta-capacitacion/asistentes-status/'
    : 'miembro/acta-capacitacion/asistentes-status/';
$tokenInscripcionUrlBase = $ctx === 'consultor'
    ? 'inspecciones/acta-capacitacion/generar-token-inscripcion/'
    : 'miembro/acta-capacitacion/generar-token-inscripcion/';
?>
```

### 5.7 Vista pública de firma — canvas

`app/Views/inspecciones/acta_capacitacion/firma_remota.php` — 208 líneas, **copiar literal**. La parte JS es independiente del backend (no usa CSRF — es endpoint público).

Snippet del envío de la firma (líneas 174-202):

```javascript
btnFirmar.addEventListener('click', function() {
    if (!hayFirma) return;

    Swal.fire({
        title: 'Confirmar firma',
        text: 'Tu firma será registrada en el acta. ¿Continuar?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, firmar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#28a745',
    }).then(function(r) {
        if (!r.isConfirmed) return;

        Swal.fire({ title: 'Enviando firma...', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });

        var firmaBase64 = canvas.toDataURL('image/png');
        var fd = new FormData();
        fd.append('token', '<?= esc($token) ?>');
        fd.append('firma_imagen', firmaBase64);

        fetch('<?= site_url('acta-capacitacion/procesar-firma-remota') ?>', {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                Swal.fire({ icon: 'success', title: '¡Firma registrada!', text: 'Gracias por confirmar tu asistencia.' })
                    .then(function() {
                        document.body.innerHTML = '<div style="padding:40px; text-align:center;"><i class="fas fa-check-circle" style="font-size:64px; color:#28a745;"></i><h3>Firma registrada</h3><p>Ya puedes cerrar esta ventana.</p></div>';
                    });
            } else {
                Swal.fire('Error', data.error || 'No se pudo registrar la firma', 'error');
            }
        });
    });
});
```

### 5.8 Vista PDF — encabezado FT-SST-232

`app/Views/inspecciones/acta_capacitacion/pdf.php` — 190 líneas, **copiar literal**. Es la fuente del código `FT-SST-232`. Para cambiar el código del documento, editar SOLO esta línea (50):

```php
<tr><td style="border-bottom:1px solid #333; padding:3px 6px; font-size:8pt;"><span style="font-weight:bold;">Código:</span> FT-SST-232</td></tr>
```

---

## 6. Contratos AJAX (request/response por endpoint)

### 6.1 `POST /inspecciones/acta-capacitacion/asistente/save/{idActa}`

**Request body** (form-urlencoded):
```
csrf_token=...&id_asistente=42&nombre_completo=Juan Perez&tipo_documento=CC&numero_documento=1234567890&cargo=Operario&area_dependencia=Producción&email=jp@x.com&celular=3001234567&orden=3
```
**Response (éxito):**
```json
{ "success": true, "id": 42, "asistente": { "id": 42, "nombre_completo": "Juan Perez", ... } }
```
**Response (error):**
```json
{ "success": false, "error": "Acta ya finalizada" }
```

### 6.2 `POST /inspecciones/acta-capacitacion/generar-token-firma/{idAsistente}`

**Request:** csrf
**Response:**
```json
{ "success": true, "url": "https://app.tld/acta-capacitacion/firmar-remoto/abc123...", "nombre": "Juan Perez" }
```

### 6.3 `POST /inspecciones/acta-capacitacion/asistente/enviar-email/{idAsistente}`

**Request:** csrf
**Response:**
```json
{ "success": true, "email": "jp@x.com", "error": null }
```

### 6.4 `POST /inspecciones/acta-capacitacion/generar-token-inscripcion/{idActa}`

**Request:** csrf, `regenerar=1` (opcional)
**Response:**
```json
{
  "success": true,
  "token": "abc...",
  "url": "https://app.tld/acta-capacitacion/inscripcion/abc...",
  "qr_svg": "<svg ...>...</svg>"
}
```

### 6.5 `GET /inspecciones/acta-capacitacion/asistentes-status/{idActa}`

**Response:**
```json
{
  "success": true,
  "total": 12,
  "firmados": 7,
  "pct": 58,
  "asistentes": [
    { "id": 1, "nombre_completo": "Juan", "firmado": true, "firmado_at": "2026-05-06 10:23:00", "enlace_enviado": false },
    { "id": 2, "nombre_completo": "Ana",  "firmado": false, "firmado_at": null, "enlace_enviado": true }
  ]
}
```

### 6.6 `POST /acta-capacitacion/procesar-firma-remota` (público)

**Request body:**
```
token=abc...&firma_imagen=data:image/png;base64,iVBORw0KGgoAAAANS...
```
**Response:** `{ "success": true }` o `{ "success": false, "error": "Enlace expirado" }`

### 6.7 `POST /acta-capacitacion/procesar-inscripcion` (público, QR)

**Request body:**
```
token=abc...&nombre_completo=...&tipo_documento=CC&numero_documento=12345&cargo=...&area_dependencia=...&email=...&celular=...
```
**Response (éxito):**
```json
{ "success": true, "id_asistente": 99, "url_firmar": "https://app.tld/acta-capacitacion/firmar-remoto/xyz..." }
```
**Response (duplicado):**
```json
{ "success": false, "duplicado": true, "error": "Ya hay un asistente registrado con este numero de documento." }
```

---

## 7. Dependencias

### 7.1 Composer (`composer.json`)

| Paquete | Versión origen | Versión PH | Para qué |
|---|---|---|---|
| `dompdf/dompdf` | `^3.1` | `^3.0` ✅ presente | Generación del PDF Letter portrait |
| `sendgrid/sendgrid` | `^8.1` | `^8.1` ✅ presente | Email transaccional (firma + notificación al consultor) |
| `chillerlan/php-qrcode` | `^5.0` | `^5.0` ✅ presente | QR SVG inline para auto-inscripción |

> **Las 3 librerías ya están en `enterprisesstph/composer.json`. Sin acción.**

### 7.2 Variables de entorno

| Variable | Uso |
|---|---|
| `SENDGRID_API_KEY` | Clave de la cuenta SendGrid (obligatoria — `getenv('SENDGRID_API_KEY')`) |

> El remitente está hardcoded: `notificacion.cycloidtalent@cycloidtalent.com` con nombre `EnterpriseSST`. **Si en destino el remitente debe ser otro, hay 2 lugares** para editar (uno por controlador). Buscar `notificacion.cycloidtalent@cycloidtalent.com`.

### 7.3 CDN (frontend)

Usados en las vistas públicas (`firma_remota`, `inscripcion_publica`, errores):

```html
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
```

(En las vistas internas — `form.php`, `view.php`, `list.php` — ya hereda Bootstrap+FA+SweetAlert del `layout_pwa.php`).

### 7.4 Sistema de archivos

Carpetas que se autogeneran (con `mkdir 0755 recursivo`):

| Ruta | Qué guarda |
|---|---|
| `public/uploads/inspecciones/firmas_capacitacion/` | PNGs de firmas individuales (`firma_cap_{idAsistente}_{timestamp}.png`) |
| `public/uploads/inspecciones/actas_capacitacion/pdfs/` | PDFs intermedios generados por Dompdf |
| `public/uploads/{nit_cliente}/` | PDF final copiado para el sistema de reportes |

**Los `.gitignore` deben permitir esos directorios y excluir su contenido.**

---

## 8. Patrones especiales

### 8.1 Token one-shot (firma remota)

- Se genera con `bin2hex(random_bytes(32))` → 64 chars hex.
- Expira a los **7 días** (`strtotime('+7 days')`).
- Al firmar, se setean `token_firma=NULL` y `token_expiracion=NULL` → no reutilizable.
- UNIQUE KEY en BD impide colisiones.

### 8.2 Token de inscripción (QR — many-shot)

- Se genera con `bin2hex(random_bytes(24))` → 48 chars hex.
- **No expira** mientras el acta esté en estado `borrador`.
- Si el acta pasa a `completo`, deja de aceptar inscripciones (validación en `inscripcion()` y `procesarInscripcion()`).
- Se puede regenerar con `regenerar=1` — el QR anterior queda inválido.
- Anti-duplicado: clave es `(id_acta_capacitacion, numero_documento)` — verificación a nivel aplicación, no de BD.

### 8.3 Save no-destructivo de asistentes

`saveAsistentes()` (privado) **NUNCA borra**. Solo INSERT (filas sin `asistente_id[]`) o UPDATE (filas con `asistente_id[]`). La eliminación se hace solo vía `deleteAsistente` con confirmación SweetAlert + bloqueo si ya firmó. Este patrón evita el bug histórico donde "Guardar borrador" borraba asistentes que ya habían firmado pero faltaban sus datos en el form.

### 8.4 Idempotencia de subida a `tbl_reporte`

El finalizar puede ejecutarse N veces (regenera el PDF cada vez). En vez de duplicar filas en `tbl_reporte`:

```php
$existente = $reporteModel
    ->where('id_cliente', $acta['id_cliente'])
    ->where('id_report_type', 4)
    ->where('id_detailreport', 6)
    ->like('observaciones', 'acta_capacitacion_id:' . $acta['id'])
    ->first();
if ($existente) return $reporteModel->update($existente['id_reporte'], $data);
return $reporteModel->save($data);
```

La marca `acta_capacitacion_id:{id}` en `observaciones` es el ancla.

### 8.5 Email SendGrid — dos templates

| Template | Destinatario | Cuándo |
|---|---|---|
| **Firma requerida** | Asistente | Cuando se hace click en botón "Email" en el form |
| **Notificación de finalización** | Consultor del cliente | Solo cuando el creador es **miembro** y el acta se finaliza |

Ambos templates están **hardcoded en HTML inline** dentro de `enviarEmailFirmaCapacitacion()` y `notificarConsultor()`. Color marca dorado `#bd9751`, encabezado azul gradient `#1e3a5f → #2d5a87`.

### 8.6 PDF: Dompdf con paper Letter portrait

- Logo del cliente embebido en base64 (`mime_content_type` + `base64_encode`).
- Cada firma embebida en base64 PNG.
- `isRemoteEnabled=true` (por si el logo viene como URL — defensivo).
- Colores: títulos sección `#0d6efd`, `<th>` background `#0d6efd` con texto blanco, body `#333`.
- Fuente: **DejaVu Sans 10pt** (no Arial, soporta tildes).
- Código del documento: `FT-SST-232` (hardcoded en línea 50 de `pdf.php`).
- Versión: `001` fija (no se conecta a `DocumentoVersionService`). Razón: una acta es un evento puntual, no un documento que evoluciona.

### 8.7 QR con fallback dual

Si `chillerlan\\QRCode\\QRCode` está disponible en el autoload → genera SVG inline localmente. Si falla por cualquier razón → fallback a `https://api.qrserver.com/v1/create-qr-code/`. Este patrón asegura que el módulo funciona incluso si Composer falla parcialmente.

### 8.8 Versión "Recovery" para firmas huérfanas

`cli_recover_firmas_capacitacion.php` (180 líneas) escanea `public/uploads/inspecciones/firmas_capacitacion/`, cruza con BD y detecta PNGs cuyo `id_asistente` ya no existe (asistente borrado pero archivo en disco). Modos:

- Sin args → diagnóstico (lista huérfanas vs válidas)
- `--acta=ID --apply` → re-inserta asistentes placeholder con la firma recuperada

**No portar al destino** salvo que se pretenda heredar firmas existentes (poco probable en migración limpia).

---

## 9. Trampas / gotchas conocidas

| # | Gotcha | Por qué importa |
|---|---|---|
| 1 | `tbl_reporte.observaciones` debe contener literal `acta_capacitacion_id:{id}` | La idempotencia depende de esa marca exacta. No la cambies "para que se vea bonita". |
| 2 | `firma_path` se guarda como **ruta relativa desde `public/`** (sin `FCPATH`) | Para servirla con `base_url($a['firma_path'])`. Si la guardas absoluta, el HTML no la encuentra. |
| 3 | Los endpoints públicos (`firmar-remoto`, `procesar-firma-remota`, `inscripcion`, `procesar-inscripcion`) viven en el **nivel raíz de `Routes.php`** | Si quedan dentro de `$routes->group('inspecciones', ['filter' => '...'])`, los filtros de auth bloquearán al asistente sin sesión y romperán el QR. |
| 4 | `procesarInscripcion` setea `token_firma` y devuelve `url_firmar` | El asistente que se auto-inscribe DEBE poder firmar inmediatamente. Si saltas ese paso, no firma nunca. |
| 5 | El form `form.php` se usa para **ambos contextos** (consultor + miembro) — distinguir por `$contexto` | La variable `$ctx = $contexto ?? 'miembro';` es la fuente de verdad. Cambiar URLs de un endpoint requiere editar las 7 líneas de `*UrlBase`. |
| 6 | `dictada_por` y `modalidad` son ENUMs estrictos | Si el form envía un valor distinto a los del ENUM (`ARL`/`Consultor`/`Empresa`/`Otro`, `virtual`/`presencial`/`mixta`), el INSERT falla con un strict-mode error. |
| 7 | El asistente NO tiene `id_cliente` directo | Para validar pertenencia se navega: `asistente.id_acta_capacitacion → acta.id_cliente`. |
| 8 | `id_comite` puede ser NULL (consultor sin comité) | NO declarar como NOT NULL ni con FK estricta en destino. |
| 9 | Eliminar un asistente con firma está **prohibido** | El controlador valida `firma_path !== null` antes de delete. Mantén esa guarda. |
| 10 | `bin2hex(random_bytes(N))` genera 2N chars hex | El campo `token_firma VARCHAR(64)` ajusta exactamente a `random_bytes(32)`. Si subes a 64 bytes, ajusta el VARCHAR. |
| 11 | Sin `SENDGRID_API_KEY` el `enviarEmailFirma` y `notificarConsultor` retornan false silenciosamente | El log dice el error pero el usuario ve "No se pudo enviar el email". Validar variable en deploy. |
| 12 | `id_report_type=4` (Capacitación en SST) e `id_detailreport=6` (Capacitaciones en SST) | Estos IDs deben existir en las tablas de catálogo `tbl_reporte_tipo` y `tbl_reporte_detalle` (o equivalentes) en destino. **VERIFICAR en `enterprisesstph` antes de finalizar.** |
| 13 | Código `FT-SST-232` es hardcoded en `pdf.php` línea 50 | En `enterprisesstph` ya hay codigos `FT-SST-249`. Verificar que `232` no esté ya tomado en destino. Si lo está, reasignar. |
| 14 | Dashboard del consultor referencia `$totalCapacitaciones` | Si replicas la tarjeta del dashboard sin proveer la variable, sale `0`. Debe inicializarse en el método del controller que renderiza el dashboard. |
| 15 | El `MiembroActaCapacitacionController` lee `session()->get('email_miembro')` y `session()->get('user_id')` | Esas claves de sesión deben existir en destino. Si el sistema de auth de miembros usa otras claves, ajustar `getMiembroAny()`. |

---

## 10. Mapa de paridad `enterprisesst` → `enterprisesstph`

### 10.1 Estado actual del destino (verificado 2026-05-06)

| Recurso | Estado en `enterprisesstph` |
|---|---|
| Tabla `tbl_acta_capacitacion` | ❌ NO existe — crear |
| Tabla `tbl_acta_capacitacion_asistente` | ❌ NO existe — crear |
| `app/Controllers/Inspecciones/` | ✅ Existe |
| `app/Controllers/Inspecciones/ActaCapacitacionController.php` | ❌ NO existe |
| `app/Controllers/MiembroActaCapacitacionController.php` | ❌ NO existe |
| `app/Models/ActaCapacitacionModel.php` | ❌ NO existe |
| `app/Models/ActaCapacitacionAsistenteModel.php` | ❌ NO existe |
| `app/Models/Traits/TenantScopedModel.php` | ❌ **NO existe** (no hay carpeta `Models/Traits/`) |
| `app/Libraries/TenantFilter.php` | ❌ **NO existe** (asumir mismo origen) |
| `app/Traits/AutosaveJsonTrait.php` | ✅ **Existe** |
| `app/Traits/ImagenCompresionTrait.php` | ✅ Existe (no usado por este módulo de todos modos) |
| `app/Views/inspecciones/layout_pwa.php` | ✅ Existe |
| `app/Views/inspecciones/miembro/` | ❌ **NO existe** (carpeta entera) |
| `app/Views/inspecciones/miembro/layout_pwa_miembro.php` | ❌ NO existe |
| `app/Views/actas/miembro_auth/dashboard.php` | ❌ NO existe (no hay carpeta `actas/miembro_auth/`) |
| `composer.json` → `dompdf/dompdf` | ✅ `^3.0` |
| `composer.json` → `sendgrid/sendgrid` | ✅ `^8.1` |
| `composer.json` → `chillerlan/php-qrcode` | ✅ `^5.0` |
| `tbl_clientes` | ✅ Existe (verificar campos `id_cliente, nombre_cliente, nit_cliente, logo, id_consultor`) |
| `tbl_consultor` | ✅ Existe (verificar campos `id_consultor, nombre_consultor, correo_consultor`) |
| `tbl_comites` | ⚠️ Verificar — el modelo de comités en PH es diferente (`PzactacopasstController`, `PzinscripcioncopasstController`) |
| `tbl_comite_miembros` | ⚠️ Verificar nombre exacto en PH |
| `tbl_reporte` con `id_report_type=4, id_detailreport=6` | ✅ Catálogo en uso por otros controladores (verificar IDs) |

### 10.2 Decisiones de adaptación

| Decisión | Acción en `enterprisesstph` |
|---|---|
| **No portar `TenantScopedModel`** | Quitar `use TenantScopedModel;` y `use App\Models\Traits\TenantScopedModel;` del `ActaCapacitacionModel`. El módulo funciona sin el trait — solo pierde el filtro multi-tenant automático que probablemente NO aplica en PH |
| **Flujo PWA del miembro** | ⚠️ Como **NO existe** el sistema de auth de miembros (`actas/miembro_auth/dashboard.php`, `inspecciones/miembro/layout_pwa_miembro.php`, sesiones `email_miembro`/`user_id` específicas), el `MiembroActaCapacitacionController` **no funcionará out-of-the-box** en PH. **OPCIONES:** (a) replicar primero ese sistema de auth (gran esfuerzo, fuera de alcance), (b) **omitir el flujo miembro** y dejar solo el del consultor, (c) preguntar al usuario si en PH existe un equivalente con otro nombre |
| **Layout del miembro** | Si se decide por (b), borrar referencias a `inspecciones/miembro/layout_pwa_miembro` en form.php (línea ~80) y borrar las dos vistas `miembro/acta_capacitacion_*.php` |
| **Código `FT-SST-232`** | Verificar que no choque con códigos existentes en PH. Si hay conflicto, asignar el siguiente libre y editar `pdf.php` línea 50 |
| **Dashboard del consultor** | Editar el dashboard de inspecciones de PH para agregar la tarjeta `Actas Capacitacion`. Inicializar `$totalCapacitaciones` en el método que lo renderiza con `(new ActaCapacitacionModel())->countAllResults()` |

### 10.3 Sin cambios de nombres BD esperados

A diferencia de otros módulos portados, en `acta-capacitacion` **NO hay discrepancias de nombres** entre `enterprisesst` y `enterprisesstph` para las tablas que consulta (`tbl_clientes`, `tbl_consultor`, `tbl_reporte`). Las únicas tablas nuevas (`tbl_acta_capacitacion*`) llevan los mismos nombres en ambos lados.

---

## 11. Orden de implementación con checkpoints

> Ejecutar los pasos en este orden. Cada checkpoint debe pasar antes de avanzar al siguiente.

### Paso 1 — Base de datos

```bash
# 1.1 Copiar el archivo de migración
cp c:/xampp/htdocs/enterprisesst/app/SQL/migrate_acta_capacitacion.php \
   c:/xampp/htdocs/enterprisesstph/app/SQL/

# 1.2 Editar credenciales LOCAL si difieren (líneas 31-39 del script)

# 1.3 LOCAL primero
php c:/xampp/htdocs/enterprisesstph/app/SQL/migrate_acta_capacitacion.php

# 1.4 Si LOCAL OK → PRODUCCIÓN
php c:/xampp/htdocs/enterprisesstph/app/SQL/migrate_acta_capacitacion.php --prod
```

**El SQL ya incluye `token_inscripcion` directamente** (es el `CREATE TABLE` consolidado de §3.1). No hace falta correr la segunda migración.

✅ **Checkpoint 1:** `SHOW TABLES LIKE 'tbl_acta_capacitacion%'` debe retornar 2 filas. `DESCRIBE tbl_acta_capacitacion` debe mostrar la columna `token_inscripcion`.

### Paso 2 — Modelos

```
app/Models/ActaCapacitacionModel.php          ← copiar literal, QUITAR el `use TenantScopedModel`
app/Models/ActaCapacitacionAsistenteModel.php ← copiar literal
```

✅ **Checkpoint 2:** Probar en tinker / un controlador test:
```php
$m = new \App\Models\ActaCapacitacionModel();
var_dump($m->countAllResults()); // debe imprimir int(0)
```

### Paso 3 — Rutas (consultor solamente)

Copiar al `Routes.php` de PH **solo** los bloques §2.1 (públicos) y §2.3 (consultor). El bloque §2.2 (miembro) se omite si no existe el sistema de auth de miembros en PH.

✅ **Checkpoint 3:** `php spark routes | grep acta-capacitacion` debe listar al menos 19 rutas (4 públicas + 15 del consultor).

### Paso 4 — Controlador del consultor

```
app/Controllers/Inspecciones/ActaCapacitacionController.php  ← copiar literal
```

✅ **Checkpoint 4:** `GET /inspecciones/acta-capacitacion` (con sesión de consultor) debe responder HTML con `Actas de Capacitación` en el `<title>` y un mensaje "No hay actas de capacitación registradas".

### Paso 5 — Vistas

Carpeta completa:
```
app/Views/inspecciones/acta_capacitacion/
    form.php              (966 líneas, literal)
    view.php              (literal)
    list.php              (literal)
    pdf.php               (literal — verificar código FT-SST-232)
    firma_remota.php      (literal)
    firma_remota_error.php(literal)
    inscripcion_publica.php(literal)
    inscripcion_error.php (literal)
```

✅ **Checkpoint 5:** Click en "Nueva" desde la lista renderiza el form. Acordeón "Datos Generales" se abre. El `<select>` de cliente se carga vía AJAX (`inspecciones/api/clientes` debe existir y responder JSON — esa ruta es preexistente del proyecto, NO la crea este módulo).

### Paso 6 — Dashboard de inspecciones (cosmético)

Editar el dashboard del consultor de PH para agregar la tarjeta y el conteo:

```php
// En el método del controller que renderiza el dashboard:
$totalCapacitaciones = (new \App\Models\ActaCapacitacionModel())->countAllResults();

// En la vista:
<a href="<?= site_url('inspecciones/acta-capacitacion') ?>" class="card-tipo">
    <i class="fas fa-graduation-cap"></i>
    <div><strong>Actas Capacitacion</strong></div>
    <div class="count">(<?= $totalCapacitaciones ?? 0 ?>)</div>
</a>
```

✅ **Checkpoint 6:** El dashboard de inspecciones muestra la tarjeta nueva con `(0)`.

### Paso 7 — Smoke test funcional

1. Crear acta como consultor → guardar → debe redirigir a `/edit/{id}`.
2. Agregar 1 asistente, click "Guardar este asistente" → AJAX debe responder `{success:true}` y la badge cambia a "Guardado".
3. Click "Copiar" → genera token, copia URL al portapapeles.
4. Abrir URL copiada en ventana incógnita → debe ver el canvas.
5. Firmar → guarda PNG en `public/uploads/inspecciones/firmas_capacitacion/firma_cap_{id}_{ts}.png` y redirige a vista "Firma registrada".
6. Refrescar el form del consultor → la badge debe estar "Firmado" verde.
7. Click "Mostrar QR" → SweetAlert con QR SVG visible.
8. Escanear QR (o abrir URL del modal) → form de inscripción.
9. Llenar y enviar → debe redirigir directo al canvas con un nuevo asistente creado.
10. Click "Finalizar y generar PDF" → estado pasa a `completo`. Verificar PDF en `public/uploads/inspecciones/actas_capacitacion/pdfs/` y copia en `public/uploads/{nit_cliente}/`. Verificar fila en `tbl_reporte`.

✅ **Checkpoint 7:** Los 10 pasos pasan sin errores en consola/log.

### Paso 8 — Email (opcional pero recomendado)

```bash
# Verificar variable de entorno
grep SENDGRID_API_KEY c:/xampp/htdocs/enterprisesstph/.env
```

Si no existe, agregarla. Probar: en un acta con asistente que tenga email, click botón "Email" → debe llegar el email con header dorado y link de firma.

✅ **Checkpoint 8:** Email recibido con preview "Solicitud de Firma — Acta de Capacitación".

### Paso 9 — Flujo del miembro (OPCIONAL — solo si existe auth de miembros en PH)

Solo si el sistema de auth de miembros existe en PH:
- Copiar `app/Controllers/MiembroActaCapacitacionController.php`
- Copiar `app/Views/inspecciones/miembro/acta_capacitacion_list.php` y `acta_capacitacion_view.php`
- Agregar el bloque §2.2 al `Routes.php`
- Agregar tarjeta en dashboard de miembros

✅ **Checkpoint 9:** Miembro logueado puede ver `/miembro/acta-capacitacion` y crear actas.

---

## 12. Apéndice — queries SQL útiles para verificar

```sql
-- ¿Cuántas actas hay y en qué estado?
SELECT estado, COUNT(*) FROM tbl_acta_capacitacion GROUP BY estado;

-- Actas con su % de firmas
SELECT
    a.id, a.tema, a.estado,
    (SELECT COUNT(*) FROM tbl_acta_capacitacion_asistente WHERE id_acta_capacitacion = a.id) AS total,
    (SELECT COUNT(*) FROM tbl_acta_capacitacion_asistente WHERE id_acta_capacitacion = a.id AND firma_path IS NOT NULL) AS firmados
FROM tbl_acta_capacitacion a
ORDER BY a.fecha_capacitacion DESC;

-- Asistentes pendientes de firmar (con enlace activo)
SELECT
    a.tema, asi.nombre_completo, asi.email, asi.token_firma, asi.token_expiracion
FROM tbl_acta_capacitacion_asistente asi
JOIN tbl_acta_capacitacion a ON a.id = asi.id_acta_capacitacion
WHERE asi.firma_path IS NULL
  AND asi.token_firma IS NOT NULL
  AND asi.token_expiracion > NOW();

-- Verificar que se subió correctamente al sistema de reportes
SELECT * FROM tbl_reporte
WHERE id_report_type = 4
  AND id_detailreport = 6
  AND observaciones LIKE 'acta_capacitacion_id:%'
ORDER BY id_reporte DESC LIMIT 10;

-- Detectar firmas huérfanas (PNG en disco sin asistente en BD) — solo conceptual,
-- el chequeo real está en cli_recover_firmas_capacitacion.php
SELECT id, firma_path FROM tbl_acta_capacitacion_asistente WHERE firma_path IS NOT NULL;

-- Verificar tokens de inscripción activos (QR vigentes)
SELECT id, tema, fecha_capacitacion, token_inscripcion, estado
FROM tbl_acta_capacitacion
WHERE token_inscripcion IS NOT NULL AND estado != 'completo';

-- Verificar que tbl_reporte_tipo / detalle tengan los IDs esperados
-- (el nombre exacto de las tablas catálogo puede variar — ajustar)
SELECT * FROM tbl_reporte_tipo WHERE id_report_type = 4;
SELECT * FROM tbl_reporte_detalle WHERE id_detailreport = 6;
```

---

## Notas finales para la IA receptora

1. **Lee este documento de arriba abajo antes de tocar nada.** Las secciones §10 (paridad) y §9 (gotchas) son las que más errores evitan.
2. **Copia los archivos PHP/HTML literalmente** — la única edición obligatoria es quitar `use TenantScopedModel;` del modelo. No "mejores" ni "refactorices" mientras portas. Hazlo idéntico, valida que funcione, después si quieres mejorar lo haces como tarea separada.
3. **Las rutas son sensibles al orden y al grupo.** Las públicas deben quedar fuera de cualquier `$routes->group()` con filtros de auth. El consultor va dentro de `inspecciones`.
4. **No portes el `MiembroActaCapacitacionController` sin antes verificar** que existe en PH el sistema de auth de miembros con las claves de sesión `email_miembro` y `user_id`. Si no existe, omítelo (deja solo flujo consultor).
5. **Verifica `id_report_type=4` y `id_detailreport=6` en las tablas catálogo de PH.** Si los IDs son distintos, ajusta los dos `uploadToReportes()`.
6. **Verifica `FT-SST-232`** no esté tomado en PH. Si lo está, asigna el siguiente libre y edita `pdf.php` línea 50.
7. **El usuario validará el smoke test del paso 7.** No declares "completo" hasta que esos 10 puntos pasen.
