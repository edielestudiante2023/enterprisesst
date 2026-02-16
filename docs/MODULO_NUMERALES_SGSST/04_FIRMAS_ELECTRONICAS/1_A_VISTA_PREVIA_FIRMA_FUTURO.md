# Vista Previa en Firma Electronica - Mejoras Futuras

> Documento de referencia con patrones de la industria y rutas de evolucion.
> Basado en investigacion de DocuSign, HelloSign, PandaDoc, SignRequest y Documenso (open source).

---

## Estado Actual (Implementado)

La Opcion A ya esta en produccion:
- `/firma/firmar/{token}` muestra el documento completo renderizado en HTML
- El contenido se obtiene de `tbl_documentos_sst.contenido` (JSON → Parsedown)
- No requirio nuevas rutas, tokens ni tablas
- Archivos: `FirmaElectronicaController::firmar()` + `Views/firma/firmar.php`
- Doc: `1_A_VISTA_PREVIA_FIRMA.md`

---

## Mejora 1: PDF Embebido en la Pagina de Firma

### Que es
En lugar de renderizar el HTML con Parsedown, mostrar el PDF real del documento
dentro de un `<iframe>` o visor PDF en el navegador. El firmante ve exactamente
lo que quedara archivado.

### Como implementarlo

**Paso 1: Generar PDF al momento de solicitar firma**

En `FirmaElectronicaController::crearSolicitud()`, despues de crear las solicitudes,
generar el PDF y guardarlo:

```php
// Generar PDF temporal para preview
$pdfPath = $this->generarPdfPreview($idDocumento);

// Guardar ruta en la solicitud o en el documento
$this->db->table('tbl_documentos_sst')
    ->where('id_documento', $idDocumento)
    ->update(['pdf_preview' => $pdfPath]);
```

**Paso 2: Agregar columna a BD (si se decide ruta separada)**

```sql
ALTER TABLE tbl_documentos_sst
ADD COLUMN pdf_preview VARCHAR(500) NULL AFTER contenido;
```

**Paso 3: Ruta publica para servir el PDF**

```php
// Routes.php
$routes->get('firma/preview-pdf/(:alphanum)', 'FirmaElectronicaController::previewPdf/$1');
```

```php
// Controller
public function previewPdf($token)
{
    $validacion = $this->firmaModel->validarToken($token);
    if (!$validacion['valido']) {
        return $this->response->setStatusCode(403);
    }

    $documento = $this->getDocumentoSST($validacion['solicitud']['id_documento']);
    $pdfPath = FCPATH . $documento['pdf_preview'];

    if (!file_exists($pdfPath)) {
        return $this->response->setStatusCode(404);
    }

    return $this->response
        ->setHeader('Content-Type', 'application/pdf')
        ->setHeader('Content-Disposition', 'inline')
        ->setBody(file_get_contents($pdfPath));
}
```

**Paso 4: Iframe en firmar.php**

```html
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-file-pdf me-2"></i>Documento a Firmar</h5>
    </div>
    <div class="card-body p-0">
        <iframe
            src="/firma/preview-pdf/<?= esc($token) ?>"
            style="width: 100%; height: 700px; border: none;">
        </iframe>
    </div>
</div>
```

### Costo
- Storage: ~200KB-1MB por PDF
- Procesamiento: Dompdf ya existe, solo se llama antes
- BD: 1 columna nueva opcional

---

## Mejora 2: Ruta Separada de Solo Lectura

### Que es
Dos links en el email en vez de uno:
- "Ver Documento" → `/firma/ver/{token}` (solo lectura, sin formulario de firma)
- "Firmar Documento" → `/firma/firmar/{token}` (con formulario)

### Como implementarlo

```php
// Routes.php
$routes->get('firma/ver/(:alphanum)', 'FirmaElectronicaController::verDocumento/$1');
```

```php
// Controller
public function verDocumento($token)
{
    $validacion = $this->firmaModel->validarToken($token);
    if (!$validacion['valido']) {
        return view('firma/error', ['error' => $validacion['error']]);
    }

    $solicitud = $validacion['solicitud'];
    $documento = $this->getDocumentoSST($solicitud['id_documento']);
    $contenido = json_decode($documento['contenido'] ?? '{}', true);
    $cliente = $this->clienteModel->find($documento['id_cliente']);

    // Registrar en audit log que vio el documento
    $this->firmaModel->registrarAudit($solicitud['id_solicitud'], 'documento_visualizado', [
        'ip' => $this->request->getIPAddress(),
        'user_agent' => $this->request->getUserAgent()->getAgentString()
    ]);

    return view('firma/ver_documento', [
        'solicitud' => $solicitud,
        'documento' => $documento,
        'contenido' => $contenido,
        'cliente'   => $cliente,
        'token' => $token
    ]);
}
```

### Cuando conviene
- Si el documento es muy largo y el scroll hasta el formulario es molesto
- Si se quiere separar la accion de "revisar" vs "firmar" en el audit trail

---

## Mejora 3: Mejorar el Email con Resumen del Documento

### Que es
El email actualmente dice "Firmar Documento" con codigo, nombre y version.
Se puede agregar un resumen (primeras 2-3 secciones o un extracto) para que
el firmante tenga contexto antes de hacer clic.

### Como implementarlo

En `FirmaElectronicaController::enviarCorreoFirma()`, agregar al HTML del email:

```php
// Obtener resumen del contenido
$contenido = json_decode($documento['contenido'] ?? '{}', true);
$resumen = '';
if (!empty($contenido['secciones'])) {
    $primerasSecciones = array_slice($contenido['secciones'], 0, 2);
    foreach ($primerasSecciones as $sec) {
        $texto = strip_tags($sec['contenido'] ?? '');
        $resumen .= '<p><strong>' . htmlspecialchars($sec['titulo']) . '</strong><br>'
                  . htmlspecialchars(mb_substr($texto, 0, 150)) . '...</p>';
    }
}
```

Luego en el HTML del email, despues de los datos del documento:

```html
<div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;">
    <p style="font-weight: bold; color: #1e3a5f;">Vista previa:</p>
    {$resumen}
    <p style="color: #666; font-size: 12px;">
        Haga clic en "Firmar Documento" para ver el contenido completo.
    </p>
</div>
```

### Costo
- Cero. Solo es agregar texto al email existente.

---

## Mejora 4: Link Publico Compartible (tipo PandaDoc)

### Que es
Un link que se pueda compartir por WhatsApp, SMS u otro medio,
ademas del email. Util cuando el firmante no revisa su correo.

### Como implementarlo

Ya esta resuelto: el token actual sirve para esto.
El link `/firma/firmar/{token}` es publico, no requiere login.

Solo faltaria:
1. Un boton en la UI de "Estado de Firmas" que copie el link al clipboard
2. Opcionalmente, un boton de "Compartir por WhatsApp"

```html
<!-- En firma/estado.php, junto al boton "Reenviar" -->
<button class="btn btn-outline-success btn-sm"
        onclick="copiarLink('<?= base_url('firma/firmar/' . $solicitud['token']) ?>')">
    <i class="bi bi-clipboard me-1"></i>Copiar Link
</button>

<a href="https://wa.me/?text=<?= urlencode('Firme el documento: ' . base_url('firma/firmar/' . $solicitud['token'])) ?>"
   target="_blank" class="btn btn-success btn-sm">
    <i class="bi bi-whatsapp me-1"></i>WhatsApp
</a>
```

### Costo
- Cero. Solo agregar botones a la UI existente.

---

## Referencia: Patrones de la Industria

### Por que NUNCA adjuntar PDF al email

| Razon | Detalle |
|-------|---------|
| Seguridad | Un PDF adjunto se puede reenviar, no se puede revocar |
| Phishing | DocuSign dice: "si nuestro email trae adjunto, es scam" |
| Deliverability | Emails con adjuntos pesados caen en spam/promotions |
| Audit trail | No se puede rastrear quien abrio, cuando, desde donde |
| Revocacion | Una vez enviado, no se puede anular el acceso |
| Tamaño | PDFs de 2-5MB = emails rechazados por servidores |

### Como manejan la autenticacion

| Plataforma | Metodo | Expiracion | Notas |
|------------|--------|------------|-------|
| DocuSign | Token opaco en URL | 48h o 5 clics | El mas corto |
| DocuSign (embedded) | URL generada on-demand | 5 minutos | Para iframes |
| HelloSign | signature_id permanente | sign_url temporal | Dos niveles |
| Adobe Sign | Link configurable | 7-90 dias | Default 7 dias |
| **Nuestro sistema** | `bin2hex(random_bytes(32))` | **7 dias** | Alineado con Adobe |

### Patron de seguridad recomendado (tokens opacos)

Nuestro sistema ya sigue las mejores practicas:
- Token: `bin2hex(random_bytes(32))` → 64 chars, 256 bits de entropia
- Almacenado en BD (revocable inmediatamente)
- Expiracion: 7 dias
- Audit trail completo: IP, user agent, timestamp, geolocalizacion
- Cumplimiento: Ley 527 de 1999, Decreto 2364 de 2012

### Mejoras de seguridad opcionales (nivel enterprise)

1. **Limitar usos del token**: Agregar columna `max_usos` y `contador_usos`
2. **Verificacion por SMS/OTP**: Pedir codigo SMS antes de mostrar documento
3. **Re-verificacion de email**: Si el token expira, enviar nuevo link al mismo email
4. **Rate limiting**: Limitar intentos de acceso por IP (ya cubierto por CloudFlare si se usa)

---

## Prioridad Sugerida de Implementacion

| # | Mejora | Esfuerzo | Impacto | Prioridad |
|---|--------|----------|---------|-----------|
| 1 | Resumen en email (Mejora 3) | 30 min | Alto | Inmediata |
| 2 | Boton copiar link + WhatsApp (Mejora 4) | 30 min | Alto | Inmediata |
| 3 | PDF embebido (Mejora 1) | 3-4 horas | Medio | Proxima iteracion |
| 4 | Ruta separada ver/firmar (Mejora 2) | 1-2 horas | Bajo | Solo si se necesita |

---

## Archivos Clave del Sistema de Firma

| Archivo | Descripcion |
|---------|-------------|
| `app/Controllers/FirmaElectronicaController.php` | Controller principal (1078 lineas) |
| `app/Models/DocFirmaModel.php` | Modelo de solicitudes de firma (363 lineas) |
| `app/Views/firma/firmar.php` | Pagina publica de firma (ahora con vista previa) |
| `app/Views/firma/solicitar.php` | Formulario interno para crear solicitudes |
| `app/Views/firma/estado.php` | Dashboard de estado de firmas |
| `app/Views/firma/verificacion.php` | Verificacion publica de documento firmado |
| `app/Views/firma/confirmacion.php` | Pagina post-firma exitosa |
| `app/Views/firma/error.php` | Pagina de error (token invalido/expirado) |

### Tablas de BD

| Tabla | Uso |
|-------|-----|
| `tbl_doc_firma_solicitudes` | Solicitudes de firma (token, estado, firmante) |
| `tbl_doc_firma_evidencias` | Evidencia legal (IP, firma_imagen, hash) |
| `tbl_doc_firma_audit_log` | Auditoria completa de eventos |
| `tbl_documentos_sst` | Documentos (contenido JSON) |
