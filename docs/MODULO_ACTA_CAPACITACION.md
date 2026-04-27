# Módulo Acta de Capacitación

Módulo nuevo, **transversal a todos los comités** (COPASST, Convivencia, Brigada, etc.) y disponible
también para el consultor. Permite levantar el acta de una capacitación (típicamente virtual,
dictada por la ARL), recolectar firmas de los asistentes vía **enlaces de WhatsApp** y generar el
PDF consolidado.

## Diferencias con módulos existentes

| Aspecto | Acta Visita (consultor) | Actas de Comité | **Acta Capacitación (este módulo)** |
|---|---|---|---|
| Quién crea | Solo consultor | Miembros del comité con permiso | **Cualquier miembro de cualquier comité, o el consultor** |
| Lista firmantes | Fija: 3 roles | Fija: miembros del comité | **Abierta: cualquier asistente ad-hoc** |
| Comité asociado | N/A | Obligatorio | **Opcional** (consultor puede dejarlo en blanco) |
| Canal de firma | WhatsApp (1 token global por rol) | Email con token (1 por miembro) | **WhatsApp (1 token por asistente)** |
| Restricción a COPASST | No (es de consultor) | No | **No — TRANSVERSAL** |

## Modelo de datos

### `tbl_acta_capacitacion`
Tabla principal. Una fila por capacitación.

| Columna | Tipo | Notas |
|---|---|---|
| `id` | INT PK AUTO_INCREMENT | |
| `id_cliente` | INT NOT NULL | FK `tbl_clientes(id_cliente)` |
| `id_comite` | INT NULL | FK `tbl_comites(id_comite)` — NULL si el consultor crea sin comité |
| `creado_por_tipo` | ENUM('miembro','consultor') NOT NULL | |
| `id_miembro` | INT NULL | FK `tbl_comite_miembros(id_miembro)` (si `creado_por_tipo='miembro'`) |
| `id_consultor` | INT NULL | FK `tbl_consultor(id_consultor)` (si `creado_por_tipo='consultor'`) |
| `tema` | VARCHAR(255) NOT NULL | Tema de la capacitación |
| `fecha_capacitacion` | DATE NOT NULL | |
| `hora_inicio` | TIME NULL | |
| `hora_fin` | TIME NULL | |
| `dictada_por` | ENUM('ARL','Consultor','Empresa','Otro') NOT NULL DEFAULT 'ARL' | |
| `nombre_capacitador` | VARCHAR(200) NULL | Nombre de quien dictó |
| `entidad_capacitadora` | VARCHAR(200) NULL | Ej: "ARL Sura" |
| `modalidad` | ENUM('virtual','presencial','mixta') NOT NULL DEFAULT 'virtual' | |
| `enlace_grabacion` | VARCHAR(500) NULL | Link Zoom/Meet/Teams a la grabación |
| `objetivos` | TEXT NULL | |
| `contenido` | TEXT NULL | Resumen de lo cubierto |
| `observaciones` | TEXT NULL | |
| `ruta_pdf` | VARCHAR(255) NULL | Path al PDF generado al finalizar |
| `estado` | ENUM('borrador','esperando_firmas','completo') NOT NULL DEFAULT 'borrador' | |
| `created_at` | DATETIME DEFAULT CURRENT_TIMESTAMP | |
| `updated_at` | DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | |

**Índices:** `idx_cliente`, `idx_comite`, `idx_fecha`, `idx_estado`.

### `tbl_acta_capacitacion_asistente`
Tabla hija. Una fila por asistente de la capacitación (lista abierta).

| Columna | Tipo | Notas |
|---|---|---|
| `id` | INT PK AUTO_INCREMENT | |
| `id_acta_capacitacion` | INT NOT NULL | FK ON DELETE CASCADE |
| `nombre_completo` | VARCHAR(200) NOT NULL | |
| `tipo_documento` | ENUM('CC','CE','PA','TI','NIT') DEFAULT 'CC' | |
| `numero_documento` | VARCHAR(20) NULL | |
| `cargo` | VARCHAR(150) NULL | |
| `area_dependencia` | VARCHAR(150) NULL | |
| `email` | VARCHAR(150) NULL | |
| `celular` | VARCHAR(30) NULL | |
| `token_firma` | VARCHAR(64) NULL UNIQUE | Token de firma remota (one-shot) |
| `token_expiracion` | DATETIME NULL | +7 días desde generación |
| `firma_path` | VARCHAR(255) NULL | Path PNG en `uploads/inspecciones/firmas_capacitacion/` |
| `firmado_at` | DATETIME NULL | Timestamp de cuando firmó |
| `orden` | INT NOT NULL DEFAULT 1 | |
| `created_at` | DATETIME DEFAULT CURRENT_TIMESTAMP | |

**Índices:** `idx_acta` (id_acta_capacitacion), `idx_token` (token_firma).

## Flujo

1. **Crear (creador autenticado: miembro o consultor)**
   - Form: tema, fecha, hora, modalidad, capacitador, entidad, link grabación, objetivos, contenido.
   - Lista de asistentes (mini-form repetible: nombre, doc, cargo, área, email, celular).
   - Guardar como `borrador`.

2. **Enviar enlaces por WhatsApp (uno por asistente)**
   - En la lista de asistentes, cada fila tiene botón **"Enviar enlace"** (verde, icono WhatsApp).
   - Click → `POST /acta-capacitacion/generar-token-firma/{id_asistente}` → genera `token_firma` (32
     bytes hex) + `token_expiracion = NOW() + 7 días`.
   - Frontend abre SweetAlert con el enlace copiable + botón "Abrir WhatsApp" → `wa.me/?text=...`.
   - El creador comparte manualmente el enlace al contacto (no hay API de WhatsApp).

3. **Firma pública (asistente, sin login)**
   - Asistente abre `GET /acta-capacitacion/firmar-remoto/{token}`.
   - Página pública muestra: datos de la capacitación, lista de asistentes (sin firmas previas), y
     un canvas para firmar.
   - Submit → `POST /acta-capacitacion/procesar-firma-remota` → guarda PNG en
     `uploads/inspecciones/firmas_capacitacion/`, persiste `firma_path` y `firmado_at`, anula
     `token_firma` y `token_expiracion` (one-shot).

4. **Finalizar**
   - El creador puede finalizar en cualquier momento (no requiere que TODOS hayan firmado — los que
     no firmaron quedan sin firma pero aparecen en la lista).
   - `POST /acta-capacitacion/finalizar/{id}` → genera PDF con Dompdf, lo sube a
     `uploads/{nit_cliente}/`, inserta/actualiza fila en `tbl_reporte` (`id_report_type=4`
     "Capacitación en SST", `id_detailreport=6` "Capacitaciones en SST"), notifica al consultor
     por SendGrid. Idempotente: busca por `acta_capacitacion_id:{id}` en `observaciones` y hace
     UPDATE si existe.
   - Estado pasa a `completo`.

## Rutas

### Para miembros autenticados (PWA `/miembro`)
**Sin filtro de COPASST** — accesible para cualquier miembro activo de cualquier comité.

```
GET  /miembro/acta-capacitacion                          → list
GET  /miembro/acta-capacitacion/create                   → form vacío
POST /miembro/acta-capacitacion/store                    → crea borrador
GET  /miembro/acta-capacitacion/edit/(:num)              → form con autosave
POST /miembro/acta-capacitacion/update/(:num)            → autosave / submit
GET  /miembro/acta-capacitacion/view/(:num)              → vista solo lectura
POST /miembro/acta-capacitacion/finalizar/(:num)         → genera PDF + sube + notifica
GET  /miembro/acta-capacitacion/pdf/(:num)               → preview/descarga PDF
POST /miembro/acta-capacitacion/asistente/store/(:num)   → AJAX agregar asistente
POST /miembro/acta-capacitacion/asistente/delete/(:num)  → AJAX eliminar asistente
POST /miembro/acta-capacitacion/generar-token-firma/(:num) → AJAX, devuelve URL para WhatsApp
```

### Para consultor (autenticado)
```
GET  /consultor/acta-capacitacion                        → list (todos los clientes asignados)
GET  /consultor/acta-capacitacion/create/(:num)          → form para cliente N
... (mismas acciones bajo prefijo /consultor)
```

### Públicas (sin login, token-based)
```
GET  /acta-capacitacion/firmar-remoto/(:any)             → canvas público
POST /acta-capacitacion/procesar-firma-remota            → AJAX guarda firma
```

## Reutilización

- **Layout PWA del miembro:** `app/Views/inspecciones/miembro/layout_pwa_miembro.php` (sin cambios).
- **Patrón token + wa.me:** copiado de `Inspecciones\ActaVisitaController::generarTokenFirma`,
  `firmarRemoto`, `procesarFirmaRemota` ([app/Controllers/Inspecciones/ActaVisitaController.php](../app/Controllers/Inspecciones/ActaVisitaController.php))
  y de la vista [app/Views/inspecciones/acta_visita/firma.php](../app/Views/inspecciones/acta_visita/firma.php).
- **Patrón finalizar/PDF/SendGrid:** copiado de `MiembroPausasActivasController::finalizar`,
  `generarPdfInterno`, `uploadToReportes`, `notificarConsultor`.
- **Autosave:** `App\Traits\AutosaveJsonTrait`.
- **Compresión imágenes (firmas):** `App\Traits\ImagenCompresionTrait` (no estrictamente necesaria
  para las firmas porque ya son PNGs pequeños, pero se reutiliza igual).

## Acceso desde el dashboard del miembro

En `app/Views/actas/miembro_auth/dashboard.php` se agrega una nueva tarjeta **"Acta de Capacitación"**
visible para todos los miembros (fuera del bloque `if ($esCopasst)`).

## Estándar del PDF

El PDF sigue el estándar de [3_AA_PDF_ENCABEZADO.md](MODULO_NUMERALES_SGSST/06_ESTILOS_PLANTILLAS/3_AA_PDF_ENCABEZADO.md) y [3_AA_PDF_CUERPO_DOCUMENTO.md](MODULO_NUMERALES_SGSST/06_ESTILOS_PLANTILLAS/3_AA_PDF_CUERPO_DOCUMENTO.md):

- **Código:** `FT-SST-232` (siguiente libre después de `FT-SST-231` = Investigación de Accidente). Hardcoded en la vista — no hay tabla central de códigos en BD.
- **Versión:** Fija en `001`. **No se conecta a `DocumentoVersionService`**. Razón: una acta de capacitación es un registro puntual de un evento — una vez firmada está cerrada, no hay v1.1. El servicio de versiones es solo para `tbl_documentos_sst` (políticas, manuales, programas IA).
- **Vigencia:** `fecha_capacitacion`.
- **Color títulos sección:** `#0d6efd` (estándar SGSST), no el dorado `#bd9751` del PWA.
- **Color `th` tablas:** `#0d6efd` con texto blanco.
- **Fuente:** DejaVu Sans 10pt, color `#333`, bordes `1px solid #333`.

Plantilla: [app/Views/inspecciones/acta_capacitacion/pdf.php](../app/Views/inspecciones/acta_capacitacion/pdf.php).

## Decisión: un token por asistente vs un token global

Se eligió **un token por asistente** porque:
- La lista es abierta y variable (puede ser de 5 o 50 personas).
- Permite paralelizar: el creador puede enviar todos los enlaces de una vez.
- Cada asistente solo ve la página para firmar él, no edita ni ve firmas de otros.
- Igual al modelo de Actas de Comité (que usa email pero la idea es la misma).
