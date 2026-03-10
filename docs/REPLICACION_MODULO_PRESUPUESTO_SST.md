# DOCUMENTO DE REPLICACION: Modulo Presupuesto SST (1.1.3)

> **Objetivo:** Permitir que otro chat de Claude AI duplique la funcionalidad completa del modulo de Presupuesto SST en un proyecto casi gemelo.
>
> **URL de referencia:** `https://dashboard.cycloidtalent.com/documentos-sst/presupuesto/15`
>
> **Framework:** CodeIgniter 4 (PHP 8.x) | **BD:** MySQL 8 | **Frontend:** Bootstrap 5 + JS vanilla
>
> **Fecha de documentacion:** 2026-03-06

---

## 1. INVENTARIO DE ARCHIVOS (21 archivos)

### 1.1 Controlador Principal

| # | Ruta Absoluta | Descripcion |
|---|---|---|
| 1 | `c:\xampp\htdocs\enterprisesst\app\Controllers\PzpresupuestoSstController.php` | Controlador completo (~1300 lineas). Contiene TODA la logica: CRUD items, exportacion PDF/Word/Excel, firmas, versionamiento, copiar presupuesto entre anios |

### 1.2 Vistas PHP (CodeIgniter views, NO Blade)

| # | Ruta Absoluta | Descripcion |
|---|---|---|
| 2 | `app\Views\documentos_sst\presupuesto_sst.php` | **Vista principal editable** - Tabla interactiva con 12 meses, inputs editables por celda, agregar/eliminar items, AJAX autosave. Es la vista mas compleja (~1500 lineas con JS inline) |
| 3 | `app\Views\documentos_sst\presupuesto_preview.php` | **Vista preview vertical** - Documento formal con encabezado SST, tabla resumen, control de cambios, firmas, toolbar con botones PDF/Word/Excel/Firmas + modal nueva version (~565 lineas) |
| 4 | `app\Views\documentos_sst\presupuesto_pdf.php` | **Vista PDF** - HTML optimizado para DOMPDF, formato carta portrait. Encabezado formal, tabla items, resumen, firmas con imagenes base64, control de cambios (~545 lineas) |
| 5 | `app\Views\documentos_sst\presupuesto_word.php` | **Vista Word** - HTML con namespaces `xmlns:w` para MS Word. Estructura similar al PDF pero con estilos inline compatibles Word (~370 lineas) |
| 6 | `app\Views\documentos_sst\presupuesto_firma.php` | **Pagina publica de firma** - Canvas para dibujar firma digital, validaciones, envio AJAX con token. Accesible sin login (~408 lineas) |
| 7 | `app\Views\documentos_sst\presupuesto_cliente.php` | **Vista consulta cliente** - Solo lectura via token publico, muestra presupuesto completo con estado de firma (~326 lineas) |
| 8 | `app\Views\documentos_sst\presupuesto_estado_firmas.php` | **Panel estado firmas** - Timeline visual de firmas con acciones: copiar enlace, WhatsApp, reenviar email, email alternativo, cancelar (~358 lineas) |
| 9 | `app\Views\documentos_sst\email_presupuesto.php` | **Template email** - HTML para email de solicitud de firma, incluye resumen del presupuesto por categorias y boton CTA (~168 lineas) |

### 1.3 Componentes Compartidos (parciales)

| # | Ruta Absoluta | Lineas Relevantes | Descripcion |
|---|---|---|---|
| 10 | `app\Views\documentacion\_tipos\presupuesto_sst.php` | Todo el archivo | Card de carpeta 1.1.3 con boton "Abrir Presupuesto SST" + tabla documentos |
| 11 | `app\Views\documentacion\_components\acciones_documento.php` | L15, L73-74, L86-87 | Mapa de rutas para presupuesto_sst (preview y edicion) |
| 12 | `app\Views\documentacion\_components\card_carpeta.php` | L110-114 | Enlace directo al modulo presupuesto |
| 13 | `app\Views\documentacion\_components\tabla_documentos_sst.php` | (referenciado) | Tabla de documentos SST aprobados |

### 1.4 JavaScript Compartido

| # | Ruta Absoluta | Descripcion |
|---|---|---|
| 14 | `public\js\firma-helpers.js` | Funciones compartidas de firma: `copiarEnlaceFirma()`, `modalEmailAlternativo()`, `enviarEmailAlternativo()`, `mostrarToastFirma()` (~183 lineas) |
| 14b | `public\js\autosave_server.js` | Auto-guardado de items del presupuesto. Usado por la vista principal `presupuesto_sst.php` (~250 lineas) |

### 1.5 Scripts de Migracion BD

| # | Ruta Absoluta | Descripcion |
|---|---|---|
| 15 | `app\SQL\ejecutar_presupuesto_sst.php` | Crea las 4 tablas principales + categorias maestras + plantilla documento |
| 16 | `app\SQL\ejecutar_columnas_firma.php` | ALTER TABLE: agrega columnas de firma digital (token, imagen, IP, etc.) |
| 17 | `app\SQL\migrar_categorias_presupuesto_7.php` | Migracion de 6 a 7 categorias (Decreto 1072/2015) |
| 18 | `app\SQL\sincronizar_fecha_aprobacion_presupuesto.php` | Correccion de fechas de aprobacion |
| 19 | `app\SQL\corregir_codigo_presupuesto.php` | Correccion de codigos de documento |

### 1.6 Rutas (Routes.php)

| # | Ruta Absoluta | Lineas |
|---|---|---|
| 20 | `app\Config\Routes.php` | L1169-1216 | Bloque completo de rutas presupuesto |

### 1.7 Servicios Relacionados (no exclusivos)

| # | Ruta Absoluta | Relacion |
|---|---|---|
| 21 | `app\Services\DocumentoVersionService.php` | L662: mapeo ruta presupuesto_sst en versionamiento |

---

## 2. RUTAS DEL APLICATIVO (18 rutas)

### 2.1 Rutas de Vista (GET)

```
GET  /documentos-sst/presupuesto/{id_cliente}                    → index($idCliente)           # Vista principal editable
GET  /documentos-sst/presupuesto/{id_cliente}/{anio}              → index($idCliente, $anio)    # Vista principal con anio
GET  /documentos-sst/presupuesto/preview/{id_cliente}/{anio}      → preview($idCliente, $anio)  # Vista preview formal
GET  /documentos-sst/{id_cliente}/presupuesto/preview/{anio}      → preview($idCliente, $anio)  # Ruta alternativa preview
```

### 2.2 Rutas AJAX (POST) - Edicion de Items

```
POST /documentos-sst/presupuesto/agregar-item        → agregarItem()       # Agregar item con meses seleccionados
POST /documentos-sst/presupuesto/actualizar-monto     → actualizarMonto()   # Actualizar celda presupuestado/ejecutado
POST /documentos-sst/presupuesto/actualizar-item       → actualizarItem()    # Actualizar texto actividad/descripcion
POST /documentos-sst/presupuesto/eliminar-item         → eliminarItem()      # Soft delete item (activo=0)
```

### 2.3 Rutas de Consulta AJAX (GET)

```
GET  /documentos-sst/presupuesto/totales/{id_presupuesto}                     → getTotales()
GET  /documentos-sst/presupuesto/estado/{id_presupuesto}/{nuevo_estado}       → cambiarEstado()
GET  /documentos-sst/presupuesto/estado-firmas/{id_cliente}/{anio}            → estadoFirmas()
```

### 2.4 Rutas de Exportacion (GET)

```
GET  /documentos-sst/presupuesto/pdf/{id_cliente}/{anio}    → exportarPdf()    # Genera PDF con DOMPDF
GET  /documentos-sst/presupuesto/word/{id_cliente}/{anio}   → exportarWord()   # Genera .doc (HTML con xmlns)
GET  /documentos-sst/presupuesto/excel/{id_cliente}/{anio}  → exportarExcel()  # Genera .xlsx con PhpSpreadsheet
```

### 2.5 Rutas de Firmas

```
POST /documentos-sst/presupuesto/enviar-firmas              → enviarAprobacion()       # Envia email con token de 7 dias
GET  /presupuesto/aprobar/{token}                            → paginaFirma($token)      # Pagina publica de firma (sin login)
POST /presupuesto/procesar-firma                             → procesarFirma()          # Procesa firma digital
POST /documentos-sst/presupuesto/reenviar-firma              → reenviarFirmaPresupuesto() # Regenera token y reenvia
POST /documentos-sst/presupuesto/cancelar-firma              → cancelarFirmaPresupuesto() # Cancela solicitud, vuelve a borrador
```

### 2.6 Rutas Especiales

```
GET  /presupuesto/consulta/{token}                                            → vistaCliente($token)      # Vista solo-lectura por token
POST /documentos-sst/presupuesto/generar-token-consulta                       → generarTokenConsulta()     # Genera token de consulta
GET  /documentos-sst/presupuesto/copiar/{id_cliente}/{anio_origen}/{anio_dest} → copiarDeAnio()            # Copia presupuesto entre anios
POST /documentos-sst/presupuesto/nueva-version/{id_cliente}/{anio}            → crearNuevaVersion()       # Crea nueva version del documento
```

---

## 3. ESTRUCTURA DE BASE DE DATOS

### 3.1 Tablas Propias del Modulo (4 tablas)

#### `tbl_presupuesto_categorias` (catalogo maestro)
```sql
CREATE TABLE tbl_presupuesto_categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(10) NOT NULL,           -- '1', '2', ..., '7'
    nombre VARCHAR(100) NOT NULL,          -- 'Talento Humano SST', etc.
    orden INT DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_codigo (codigo)
);

-- 7 categorias (Decreto 1072/2015):
-- 1. Talento Humano SST
-- 2. Capacitacion y Formacion
-- 3. Medicina Preventiva y del Trabajo
-- 4. Promocion y Prevencion
-- 5. Seguridad Industrial e Higiene
-- 6. Gestion de Emergencias
-- 7. Otros Gastos SST
```

#### `tbl_presupuesto_sst` (presupuesto principal, 1 por cliente/anio)
```sql
CREATE TABLE tbl_presupuesto_sst (
    id_presupuesto INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    anio INT NOT NULL,
    mes_inicio INT DEFAULT 1,                    -- Mes de inicio (1=Enero)
    estado ENUM('borrador','pendiente_firma','aprobado','cerrado') DEFAULT 'borrador',
    firmado_por VARCHAR(200) NULL,
    cedula_firmante VARCHAR(20) NULL,
    fecha_aprobacion DATETIME NULL,
    firma_imagen VARCHAR(255) NULL,              -- Ruta a imagen de firma rep. legal
    ip_firma VARCHAR(45) NULL,
    token_firma VARCHAR(64) NULL,                -- Token unico para pagina publica de firma
    token_expiracion DATETIME NULL,              -- Expira en 7 dias
    token_consulta VARCHAR(32) NULL,             -- Token para vista solo-lectura cliente
    firma_delegado_imagen VARCHAR(255) NULL,     -- Firma delegado SST
    firmado_delegado_por VARCHAR(200) NULL,
    fecha_aprobacion_delegado DATETIME NULL,
    observaciones TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_cliente_anio (id_cliente, anio)
);
```

#### `tbl_presupuesto_items` (items/actividades del presupuesto)
```sql
CREATE TABLE tbl_presupuesto_items (
    id_item INT AUTO_INCREMENT PRIMARY KEY,
    id_presupuesto INT NOT NULL,
    id_categoria INT NOT NULL,
    codigo_item VARCHAR(10) NOT NULL,             -- '1.1', '3.2', '4.1'
    actividad VARCHAR(200) NOT NULL,              -- Nombre de la actividad
    descripcion TEXT NULL,
    orden INT DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,                  -- Soft delete
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_presupuesto) REFERENCES tbl_presupuesto_sst(id_presupuesto) ON DELETE CASCADE,
    FOREIGN KEY (id_categoria) REFERENCES tbl_presupuesto_categorias(id_categoria)
);
```

#### `tbl_presupuesto_detalle` (montos mensuales por item)
```sql
CREATE TABLE tbl_presupuesto_detalle (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_item INT NOT NULL,
    mes INT NOT NULL,                             -- 1-12
    anio INT NOT NULL,
    presupuestado DECIMAL(15,2) DEFAULT 0.00,
    ejecutado DECIMAL(15,2) DEFAULT 0.00,
    notas VARCHAR(255) NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_item) REFERENCES tbl_presupuesto_items(id_item) ON DELETE CASCADE,
    UNIQUE KEY uk_item_mes_anio (id_item, mes, anio)
);
```

### 3.2 Tablas del Sistema Usadas (NO crear, ya existen)

| Tabla | Uso en este modulo |
|---|---|
| `tbl_clientes` | Datos del cliente: `id_cliente`, `nombre_cliente`, `nit_cliente`, `logo`, `representante_legal`, `email` |
| `tbl_cliente_contexto_sst` | Contexto SST: `representante_legal_nombre`, `representante_legal_email`, `representante_legal_cedula`, `representante_legal_cargo`, `responsable_sst_nombre`, `responsable_sst_cargo`, `delegado_sst_nombre` |
| `tbl_consultor` | Datos consultor: `nombre_consultor`, `numero_licencia`, `cargo_consultor`, `firma_consultor` |
| `tbl_doc_plantillas` | Control documental: `id_estandar=3`, `tipo_documento='presupuesto_sst'`, `codigo_sugerido`, `nombre`, `version` |
| `tbl_documentos_sst` | Tabla unificada de documentos: sincroniza estado, version, contenido JSON del presupuesto |
| `tbl_doc_versiones_sst` | Historial de versiones para Control de Cambios |
| `tbl_doc_firma_solicitudes` | Solicitudes de firma electronica del sistema unificado |
| `tbl_doc_firma_evidencias` | Evidencias de firma (imagen, fecha, IP) |

---

## 4. FLUJO FUNCIONAL COMPLETO

### 4.1 Ciclo de Vida del Presupuesto

```
[Crear] → borrador → [Editar items/montos] → [Vista Preview] → [Enviar a Firmas]
    → pendiente_firma → [Pagina Publica Firma] → aprobado → [Cerrar] → cerrado
                                                           ↓
                                                   [Nueva Version] → borrador (v2.0)
```

### 4.2 Flujo: Vista Principal (index)

1. Usuario accede a `/documentos-sst/presupuesto/15` (id_cliente=15)
2. `getOrCreatePresupuesto()`: busca en `tbl_presupuesto_sst` por (id_cliente, anio); si no existe, lo crea con estado `borrador`
3. `sincronizarConDocumentosSST()`: crea/actualiza registro en `tbl_documentos_sst` (sistema unificado de control documental)
4. Carga categorias maestras de `tbl_presupuesto_categorias`
5. `getItemsConDetalles()`: items JOIN categorias + detalle mensual indexado por mes
6. `calcularTotales()`: totales por categoria, por mes, y general
7. Renderiza tabla editable con 12 columnas de meses

### 4.3 Flujo: Edicion AJAX (autosave)

- **Agregar item:** POST con `id_presupuesto`, `id_categoria`, `actividad`, `meses[]` (JSON), `valor_inicial`
  - Crea registro en `tbl_presupuesto_items` + registros en `tbl_presupuesto_detalle` por cada mes
- **Actualizar monto:** POST con `id_item`, `mes`, `anio`, `tipo` (presupuestado|ejecutado), `valor`
  - Busca o crea `tbl_presupuesto_detalle`, recalcula totales del item
- **Eliminar item:** Soft delete (`activo=0`)

### 4.4 Flujo: Firma Digital

1. Consultor hace clic en "Enviar a Firmas" desde preview
2. `enviarAprobacion()`: genera `token_firma` (64 chars), `token_expiracion` (+7 dias), cambia estado a `pendiente_firma`
3. Envia email con template `email_presupuesto.php` al representante legal
4. Rep. legal accede a `/presupuesto/aprobar/{token}` (pagina publica sin login)
5. Dibuja firma en canvas, ingresa nombre y cedula
6. `procesarFirma()`: valida token, guarda firma como imagen, cambia estado a `aprobado`
7. Sistema tambien soporta firma via sistema unificado (`/firma/solicitar/{id_documento}`)

### 4.5 Flujo: Exportacion

- **PDF:** DOMPDF, formato carta portrait. Logo convertido a base64. Vista `presupuesto_pdf.php`
- **Word:** HTML con namespaces `xmlns:w`. Vista `presupuesto_word.php`. Headers: `Content-Type: application/msword`
- **Excel:** PhpSpreadsheet con estilos (colores SST #1a5f7a, #2c3e50), formula de totales. Genera .xlsx

### 4.6 Flujo: Versionamiento

1. Documento firmado → boton "Nueva Version" en preview
2. POST `/documentos-sst/presupuesto/nueva-version/{id_cliente}/{anio}` con `descripcion_cambio`
3. Crea snapshot en `tbl_doc_versiones_sst`, incrementa `version` en `tbl_documentos_sst`
4. Documento vuelve a estado `aprobado` (listo para firmas) con nueva version

---

## 5. PATRON DE SINCRONIZACION CON SISTEMA DOCUMENTAL

El presupuesto tiene su propia tabla (`tbl_presupuesto_sst`) pero se sincroniza con `tbl_documentos_sst` via `sincronizarConDocumentosSST()`:

```
tbl_presupuesto_sst.estado → tbl_documentos_sst.estado
    borrador               → aprobado (= listo para firmas)
    pendiente_firma         → pendiente_firma
    aprobado                → firmado
    cerrado                 → firmado
```

**Contenido JSON** almacenado en `tbl_documentos_sst.contenido`:
```json
{
    "id_presupuesto": 5,
    "total_presupuestado": 15000000,
    "total_ejecutado": 3500000,
    "categorias": ["1", "2", "3", "4", "5", "6", "7"]
}
```

---

## 6. DATOS DEL DOCUMENTO (Control Documental)

| Campo | Valor |
|---|---|
| `id_estandar` | 3 (Res. 0312/2019, estandar 1.1.3) |
| `tipo_documento` | `presupuesto_sst` |
| `codigo_sugerido` | `FT-SST` |
| `codigo_completo` | `FT-SST-001` (con consecutivo) |
| `nombre` | "Asignacion de recursos para el SG-SST" |

Estos datos se obtienen de `tbl_doc_plantillas` via `getDatosDocumento()` con 3 niveles de fallback:
1. Buscar por `id_estandar = 3`
2. Buscar por `tipo_documento = 'presupuesto_sst'`
3. Buscar por `codigo_sugerido = 'FT-SST'`
4. Hardcoded como ultimo recurso

---

## 7. DEPENDENCIAS EXTERNAS

### 7.1 Librerias PHP (Composer)
- `dompdf/dompdf` - Generacion PDF
- `phpoffice/phpspreadsheet` - Generacion Excel

### 7.2 CDN Frontend
- Bootstrap 5.3.0 (CSS + JS)
- Bootstrap Icons 1.11.0
- SweetAlert2 v11 (solo en vistas de firma y estado firmas)

### 7.3 Assets Locales
- `public/js/firma-helpers.js` - Utilidades compartidas de firma digital
- `public/uploads/` - Directorio de logos e imagenes de firma

---

## 8. ENCABEZADO FORMAL DEL DOCUMENTO

El encabezado tiene la misma estructura en preview, PDF y Word:

```
+------------------+------------------------------------------+------------------+
|                  | SISTEMA DE GESTION DE SEGURIDAD Y        | Codigo: FT-SST-001|
|    [LOGO]        | SALUD EN EL TRABAJO                      | Version: 001      |
|                  |------------------------------------------|  Fecha: dd/mm/yyyy|
|                  | ASIGNACION DE RECURSOS PARA EL SG-SST    |                    |
+------------------+------------------------------------------+------------------+
```

---

## 9. ESTRUCTURA DE FIRMAS

3 firmantes en el documento:

| Posicion | Rol | Fuente de Datos |
|---|---|---|
| Izquierda | **Elaboro** - Consultor SST | `tbl_consultor`: nombre, numero_licencia, firma_consultor |
| Centro | **Aprobo** - Representante Legal | `tbl_cliente_contexto_sst`: representante_legal_nombre/cargo/cedula |
| Derecha | **Reviso** - Responsable SG-SST | `tbl_cliente_contexto_sst`: responsable_sst_nombre/cargo O delegado_sst_nombre/cargo |

Cada firma puede provenir de:
1. **Firma electronica** (sistema unificado `tbl_doc_firma_solicitudes` + `tbl_doc_firma_evidencias`)
2. **Firma fisica** del perfil del consultor (`tbl_consultor.firma_consultor`)
3. **Firma del presupuesto** (sistema legacy `tbl_presupuesto_sst.firma_imagen`)

---

## 10. NOTAS PARA REPLICACION

### 10.1 Que crear primero
1. **BD**: Ejecutar `ejecutar_presupuesto_sst.php` (4 tablas + categorias)
2. **BD**: Ejecutar `ejecutar_columnas_firma.php` (columnas adicionales)
3. **BD**: Ejecutar `migrar_categorias_presupuesto_7.php` (corrige a 7 categorias)
4. **BD**: INSERT en `tbl_doc_plantillas` el registro del tipo presupuesto_sst
5. **Rutas**: Agregar bloque de rutas en Routes.php
6. **Controller**: Crear PzpresupuestoSstController
7. **Vistas**: Crear las 8 vistas en `Views/documentos_sst/`
8. **JS**: Copiar `firma-helpers.js` si no existe
9. **Componentes**: Agregar condicionales en `acciones_documento.php`, `card_carpeta.php`

### 10.2 Particularidades del modulo
- Este modulo NO usa generacion IA. Es edicion manual pura (CRUD)
- La tabla principal tiene su propio sistema de estados (borrador → aprobado), diferente del flujo IA de otros documentos
- Soporta DOS sistemas de firma: legacy (token+canvas propio) y unificado (FirmaElectronicaController)
- La vista principal (`presupuesto_sst.php`) es la mas compleja del proyecto (~1500 lineas) por el JS inline de la tabla editable
- Excel usa PhpSpreadsheet (no HTML), los otros dos formatos son HTML renderizado

### 10.3 Archivos que NO necesitas replicar
- Los scripts SQL en `app/SQL/` son one-time migrations, solo para referencia de estructura
- Los archivos en `writable/debugbar/` son logs temporales
- `SqlRunnerController.php` tiene funciones de diagnostico, no de funcionalidad
