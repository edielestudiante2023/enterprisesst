# Guia Completa: Firma Digital de Contratos y Documentos SST

## Para otra IA que quiera replicar esta funcionalidad

Este documento describe **DOS sistemas de firma digital independientes** que coexisten en EnterpriseSST (CodeIgniter 4 + MySQL + SendGrid + Dompdf). Ambos siguen el mismo patron pero con diferencias clave.

---

## SISTEMA 1: Firma Digital de CONTRATOS (mas simple)

### Resumen del Flujo
```
Consultor genera PDF → Click "Enviar a Firmar" → Email con token al cliente
→ Cliente abre link publico → Dibuja/sube firma → Se guarda en BD
→ Se notifica al consultor → PDF se puede regenerar con la firma incluida
```

### Archivos Involucrados (copiar estos)

| Archivo | Funcion |
|---------|---------|
| `app/Controllers/ContractController.php` | Metodos `enviarFirma()`, `paginaFirmaContrato()`, `procesarFirmaContrato()`, `estadoFirma()`, `enviarEmailFirmaContrato()` (lineas 916-1203) |
| `app/Models/ContractModel.php` | Tabla `tbl_contratos` con campos de firma (lineas 50-58) |
| `app/Libraries/ContractPDFGenerator.php` | Genera PDF con firmas incluidas. Metodo `buildSignaturesHTML()` y `getSignatureImage()` |
| `app/Views/contracts/contrato_firma.php` | Pagina publica donde el cliente firma (canvas + upload) |
| `app/Views/contracts/email_contrato_firma.php` | Template HTML del email con boton CTA |
| `app/Views/contracts/firma_error_contrato.php` | Pagina de error (token invalido/expirado) |
| `app/Views/contracts/view.php` | Vista del contrato con botones "Enviar a Firmar" / "Reenviar" / "Copiar Link" |

### Columnas en tabla `tbl_contratos` (agregar con ALTER TABLE)

```sql
ALTER TABLE tbl_contratos ADD COLUMN token_firma VARCHAR(64) NULL;
ALTER TABLE tbl_contratos ADD COLUMN token_firma_expiracion DATETIME NULL;
ALTER TABLE tbl_contratos ADD COLUMN estado_firma ENUM('sin_enviar','pendiente_firma','firmado') DEFAULT 'sin_enviar';
ALTER TABLE tbl_contratos ADD COLUMN firma_cliente_nombre VARCHAR(255) NULL;
ALTER TABLE tbl_contratos ADD COLUMN firma_cliente_cedula VARCHAR(20) NULL;
ALTER TABLE tbl_contratos ADD COLUMN firma_cliente_imagen VARCHAR(500) NULL COMMENT 'Ruta al PNG de la firma';
ALTER TABLE tbl_contratos ADD COLUMN firma_cliente_ip VARCHAR(45) NULL;
ALTER TABLE tbl_contratos ADD COLUMN firma_cliente_fecha DATETIME NULL;
```

### Rutas (agregar en Routes.php)

```php
// Firma digital de contratos (autenticado)
$routes->post('/contracts/enviar-firma', 'ContractController::enviarFirma');
$routes->get('/contracts/estado-firma/(:num)', 'ContractController::estadoFirma/$1');

// Firma digital de contratos (publico, sin auth)
$routes->get('/contrato/firmar/(:segment)', 'ContractController::paginaFirmaContrato/$1');
$routes->post('/contrato/procesar-firma', 'ContractController::procesarFirmaContrato');
```

### Flujo Detallado Paso a Paso

#### 1. Enviar a Firmar (`enviarFirma()`)
```
POST /contracts/enviar-firma  {id_contrato: 9}
```
- Valida que el PDF ya fue generado (`contrato_generado = 1`)
- Valida que no este ya firmado
- Valida que tenga email del representante legal
- Genera token: `bin2hex(random_bytes(32))` → 64 chars hex
- Expiracion: 7 dias
- Guarda token + estado `pendiente_firma` en `tbl_contratos`
- Construye URL: `base_url("contrato/firmar/{$token}")`
- Envia email via SendGrid API (curl directo, no SDK)
- Si tiene email del responsable SST diferente, envia copia informativa
- Si falla el envio, revierte el token a NULL

#### 2. Pagina Publica de Firma (`paginaFirmaContrato($token)`)
```
GET /contrato/firmar/abc123...
```
- Busca contrato por `token_firma` en BD
- Valida: token existe, estado = `pendiente_firma`, no expirado
- Muestra vista `contrato_firma.php` con:
  - Datos del contrato (partes, fechas, valor)
  - Canvas HTML5 para dibujar firma (mouse + touch)
  - Opcion de subir imagen PNG/JPG (max 2MB)
  - Campos nombre + cedula (pre-llenados)
  - Declaracion legal (Ley 527 de 1999)
  - Boton "Aprobar y Firmar Contrato"

#### 3. Procesar Firma (`procesarFirmaContrato()`)
```
POST /contrato/procesar-firma  {token, firma_nombre, firma_cedula, firma_imagen}
```
- `firma_imagen` es un data URI base64 (ej: `data:image/png;base64,...`)
- Decodifica base64 y guarda como archivo PNG en `uploads/firmas/`
- Actualiza `tbl_contratos`:
  - `estado_firma` → `firmado`
  - `firma_cliente_nombre`, `firma_cliente_cedula`
  - `firma_cliente_imagen` → ruta relativa del PNG
  - `firma_cliente_ip` → IP del firmante
  - `firma_cliente_fecha` → timestamp
  - `token_firma` → NULL (invalida el token)
- Retorna JSON `{success: true}`

#### 4. Frontend post-firma
- SweetAlert2 confirm antes de firmar
- Modal de exito despues de firma exitosa
- Botones en vista del contrato:
  - Sin enviar: "Enviar a Firmar Digitalmente" (morado gradient)
  - Pendiente: "Copiar Link" + "WhatsApp" + "Reenviar por Email"
  - Firmado: Alert verde con nombre y fecha

### Envio de Email (SendGrid)
```php
// Usa cURL directo a la API de SendGrid v3
$ch = curl_init('https://api.sendgrid.com/v3/mail/send');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiKey,
    'Content-Type: application/json'
]);
// JSON body con personalizations, from, content
```
- From: `notificacion.cycloidtalent@cycloidtalent.com`
- Template: vista PHP `email_contrato_firma.php` renderizada con `view()`
- **Variable de entorno necesaria:** `SENDGRID_API_KEY` en `.env`

---

## SISTEMA 2: Firma Electronica de DOCUMENTOS SST (mas completo)

### Resumen del Flujo
```
Consultor solicita firmas → Se crean 1-2 solicitudes en cadena
→ Email al primer firmante (Delegado SST o Rep Legal)
→ Firmante abre link publico → Ve contenido del documento → Firma
→ Si hay siguiente firmante, se activa automaticamente
→ Cuando todas las firmas estan completas:
   → Se notifica al consultor por email
   → Se genera PDF firmado automaticamente
   → Se publica en reportList (tbl_reporte)
   → Se crea version del documento (aprobacion automatica)
```

### Archivos Involucrados (copiar estos)

| Archivo | Funcion |
|---------|---------|
| `app/Controllers/FirmaElectronicaController.php` | TODO el flujo: solicitar, crear, firmar, verificar, dashboard, reenviar, cancelar, audit log, certificado PDF, firma interna |
| `app/Models/DocFirmaModel.php` | Modelo completo: crearSolicitud, validarToken, registrarFirma, getEstadoFirmas, firmasCompletas, getSiguienteFirmante, reenviar, cancelar, getDashboardFirmas, generarCodigoVerificacion |
| `app/Views/firma/firmar.php` | Pagina publica para firmar (canvas HD + upload + drag&drop + geolocalizacion) |
| `app/Views/firma/confirmacion.php` | Pagina post-firma exitosa |
| `app/Views/firma/estado.php` | Estado de firmas de un documento (para consultor) |
| `app/Views/firma/dashboard.php` | Dashboard centralizado de todas las firmas |
| `app/Views/firma/verificacion.php` | Verificacion publica con QR |
| `app/Views/firma/certificado_pdf.php` | Template PDF del certificado de verificacion |
| `app/Views/firma/audit_log.php` | Log de auditoria de una solicitud |
| `app/SQL/crear_tablas_firma_electronica.sql` | DDL de las 3 tablas |

### Tablas en BD (3 tablas)

#### `tbl_doc_firma_solicitudes`
```sql
id_solicitud INT PK AUTO_INCREMENT
id_documento INT FK → tbl_documentos_sst.id_documento
id_version INT NULL FK → tbl_doc_versiones_sst.id_version
token VARCHAR(64) UNIQUE -- Token de acceso al link
estado ENUM('pendiente','esperando','firmado','expirado','rechazado','cancelado')
fecha_creacion DATETIME
fecha_expiracion DATETIME -- +7 dias
fecha_firma DATETIME NULL
firmante_tipo ENUM('elaboro','reviso','delegado_sst','representante_legal')
orden_firma TINYINT -- 1=primero, 2=segundo
firmante_interno_id INT NULL -- Si es firma interna (usuario del sistema)
firmante_email VARCHAR(255)
firmante_nombre VARCHAR(255)
firmante_cargo VARCHAR(100)
firmante_documento VARCHAR(20) -- Cedula
recordatorios_enviados INT DEFAULT 0
ultimo_recordatorio DATETIME NULL
```

#### `tbl_doc_firma_evidencias`
```sql
id_evidencia INT PK AUTO_INCREMENT
id_solicitud INT FK → tbl_doc_firma_solicitudes CASCADE
ip_address VARCHAR(45)
user_agent TEXT
fecha_hora_utc DATETIME
geolocalizacion VARCHAR(255) -- "lat,lng"
tipo_firma ENUM('draw','upload','internal')
firma_imagen LONGTEXT -- Base64 completo de la imagen
hash_documento VARCHAR(64) -- SHA-256 del contenido al firmar
aceptacion_terminos TINYINT(1) DEFAULT 1
```

#### `tbl_doc_firma_audit_log`
```sql
id_log INT PK AUTO_INCREMENT
id_solicitud INT FK → tbl_doc_firma_solicitudes CASCADE
evento VARCHAR(50) -- solicitud_creada, email_enviado, link_abierto, firma_completada, etc.
fecha_hora DATETIME
ip_address VARCHAR(45)
detalles JSON
```

### Rutas (agregar en Routes.php)

```php
// Dashboard y gestion (autenticado)
$routes->get('/firma/dashboard', 'FirmaElectronicaController::dashboard');
$routes->get('/firma/dashboard/(:num)', 'FirmaElectronicaController::dashboard/$1');
$routes->get('/firma/solicitar/(:num)', 'FirmaElectronicaController::solicitar/$1');
$routes->post('/firma/crear-solicitud', 'FirmaElectronicaController::crearSolicitud');
$routes->get('/firma/estado/(:num)', 'FirmaElectronicaController::estado/$1');
$routes->post('/firma/reenviar/(:num)', 'FirmaElectronicaController::reenviar/$1');
$routes->post('/firma/cancelar/(:num)', 'FirmaElectronicaController::cancelar/$1');
$routes->get('/firma/audit-log/(:num)', 'FirmaElectronicaController::auditLog/$1');
$routes->post('/firma/firmar-interno/(:num)', 'FirmaElectronicaController::firmarInterno/$1');
$routes->get('/firma/certificado-pdf/(:num)', 'FirmaElectronicaController::certificadoPDF/$1');

// Publico (sin auth)
$routes->get('/firma/firmar/(:any)', 'FirmaElectronicaController::firmar/$1');
$routes->post('/firma/procesar', 'FirmaElectronicaController::procesarFirma');
$routes->get('/firma/confirmacion/(:any)', 'FirmaElectronicaController::confirmacion/$1');
$routes->get('/firma/verificar/(:any)', 'FirmaElectronicaController::verificar/$1');
```

### Flujo Detallado: Cadena de Firmas

#### 1. Crear Solicitud (`crearSolicitud()`)
- Lee contexto del cliente (`tbl_contexto_sst_cliente`) para obtener datos de firmantes
- Verifica si tiene Delegado SST configurado (nombre + email)
- **Con delegado:** Crea 2 solicitudes:
  1. Delegado SST → estado `pendiente`, orden 1
  2. Representante Legal → estado `esperando`, orden 2
- **Sin delegado:** Crea 1 solicitud:
  1. Representante Legal → estado `pendiente`, orden 1
- Cambia estado del documento a `pendiente_firma`
- Envia email al PRIMER firmante solamente

#### 2. Firma del Primer Firmante
- Cuando el primer firmante firma, `procesarFirma()`:
  - Registra la firma (evidencia + hash SHA-256)
  - Busca siguiente firmante con `getSiguienteFirmante()` (estado = `esperando`)
  - Si hay siguiente: cambia su estado a `pendiente` y le envia email
  - Si no hay siguiente: verifica `firmasCompletas()`

#### 3. Todas las Firmas Completas
Cuando `firmasCompletas()` = true, se ejecutan **3 acciones automaticas**:

1. **`notificarConsultorFirmasCompletas()`**: Email al consultor asignado con tabla de firmantes
2. **`publicarDocumentoFirmado()`**: Genera PDF con Dompdf, lo guarda en `uploads/{nit}/`, lo inserta en `tbl_reporte`
3. **`aprobarDocumentoAutomatico()`**: Crea version en `tbl_doc_versiones_sst`, marca versiones anteriores como obsoletas

### Canvas de Firma (Frontend Avanzado)
La vista `firma/firmar.php` tiene features avanzadas:
- **Canvas HD**: Usa `devicePixelRatio` para pantallas retina
- **Recorte inteligente**: `exportarFirmaOptimizada()` recorta el bounding box de la firma, agrega padding, y escala a 150px de alto
- **Drag & drop**: Zona de upload con drag&drop + preview
- **Geolocalizacion**: Captura lat/lng si el navegador lo permite
- **Checkbox obligatorio**: "Acepto los terminos" habilita el boton
- **Validacion**: El boton se habilita solo cuando hay firma + checkbox

### Verificacion con QR
- Se genera un codigo de verificacion SHA-256 de 12 chars basado en los tokens de firma
- Se genera QR con libreria `chillerlan/qrcode`
- Pagina publica `/firma/verificar/{codigo}` muestra: documento valido, firmantes, fechas, evidencia
- Se puede descargar certificado PDF

---

## DEPENDENCIAS (composer)

```json
{
    "require": {
        "dompdf/dompdf": "^2.0",
        "sendgrid/sendgrid": "^7.0 || ^8.0",
        "chillerlan/php-qrcode": "^5.0",
        "erusev/parsedown": "^1.7"
    }
}
```

## VARIABLES DE ENTORNO (.env)

```
SENDGRID_API_KEY=SG.xxxxx
SENDGRID_FROM_EMAIL=notificacion@tudominio.com
SENDGRID_FROM_NAME=Tu App
```

---

## PATRON GENERAL PARA REPLICAR EN OTRO PROYECTO

### Paso 1: Crear tablas
Copiar `crear_tablas_firma_electronica.sql` y ejecutar. Para contratos, agregar columnas con ALTER TABLE.

### Paso 2: Crear modelo
Copiar `DocFirmaModel.php` completo. Ajustar nombres de tablas si difieren.

### Paso 3: Crear controlador
- Metodo para **crear solicitud** (generar token, guardar en BD, enviar email)
- Metodo para **pagina publica** (validar token, mostrar documento, mostrar canvas)
- Metodo para **procesar firma** (guardar evidencia, activar siguiente firmante, verificar completas)
- Metodo para **reenviar** (generar nuevo token, nuevo email)

### Paso 4: Crear vistas
- **Email**: HTML inline styles (no CSS externo, para compatibilidad de email clients)
- **Pagina de firma**: Bootstrap 5 + Canvas HTML5 + JS vanilla
- **Error**: Para token invalido/expirado

### Paso 5: Registrar rutas
- Rutas autenticadas para gestion (solicitar, estado, reenviar)
- Rutas publicas para firma (firmar, procesar, confirmacion)

### Paso 6: Email con SendGrid
Dos formas usadas en el proyecto:
1. **SDK SendGrid** (en FirmaElectronicaController): `new \SendGrid\Mail\Mail()` → `$sendgrid->send()`
2. **cURL directo** (en ContractController): curl a `https://api.sendgrid.com/v3/mail/send`

Ambas funcionan. La SDK es mas limpia, cURL es mas portable.

---

## SEGURIDAD

- Tokens: 64 chars hex (`bin2hex(random_bytes(32))`) - criptograficamente seguros
- Expiracion: 7 dias por defecto
- Token se invalida despues de uso (NULL en contratos, estado cambia en documentos)
- Se registra IP, User-Agent, geolocalizacion, hash SHA-256 del documento
- Hash del documento permite detectar si el contenido cambio despues de la firma
- Audit log completo de cada evento (creacion, apertura de link, firma, reenvio)
- Referencias legales: Ley 527 de 1999, Decreto 2364 de 2012 (Colombia)

---

## DIAGRAMA DE ARCHIVOS PARA COPIAR

```
app/
├── Controllers/
│   ├── ContractController.php          ← Firma de contratos (lineas 916-1203)
│   └── FirmaElectronicaController.php  ← Firma de documentos SST (completo)
├── Models/
│   ├── ContractModel.php               ← Campos firma en allowedFields
│   └── DocFirmaModel.php               ← Modelo completo de firma electronica
├── Libraries/
│   └── ContractPDFGenerator.php        ← PDF con firmas embebidas
├── Views/
│   ├── contracts/
│   │   ├── contrato_firma.php          ← Pagina publica firma contrato
│   │   ├── email_contrato_firma.php    ← Email template
│   │   ├── firma_error_contrato.php    ← Error de token
│   │   └── view.php                    ← Botones firma en vista contrato
│   └── firma/
│       ├── firmar.php                  ← Pagina publica firma documento SST
│       ├── confirmacion.php            ← Post-firma exitosa
│       ├── estado.php                  ← Estado firmas (consultor)
│       ├── dashboard.php               ← Dashboard centralizado
│       ├── verificacion.php            ← Verificacion publica con QR
│       ├── certificado_pdf.php         ← PDF certificado
│       └── audit_log.php               ← Log auditoria
├── SQL/
│   └── crear_tablas_firma_electronica.sql ← DDL 3 tablas
└── Config/
    └── Routes.php                      ← Rutas (ver seccion de rutas arriba)
```
