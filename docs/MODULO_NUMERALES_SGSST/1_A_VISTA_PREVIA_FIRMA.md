# Vista Previa del Documento en Pagina de Firma Electronica

## Problema

Cuando un firmante (Rep. Legal, Delegado SST) recibe el email de solicitud de firma
y hace clic en "Firmar Documento", llega a una pagina que solo muestra:
- Codigo del documento
- Nombre del documento
- Version
- Datos del firmante

**NO ve el contenido del documento que va a firmar.** Esto es un problema grave porque:
1. La persona firma "a ciegas" sin poder revisar que esta firmando
2. Para ver el documento tendria que loguearse en la plataforma, buscar el documento, etc.
3. Adjuntar PDF al email NO es viable (seguridad, peso, no revocable, sin audit trail)

## Solucion: Opcion A - Vista Previa en la Misma Pagina de Firma

Modificar la ruta publica `/firma/firmar/{token}` para que **muestre el documento completo
renderizado en HTML** antes del formulario de firma.

### Por que esta opcion y no otras

| Opcion | Pros | Contras | Decision |
|--------|------|---------|----------|
| **A) Vista previa en firmar.php** | No requiere nueva ruta, token, ni BD. Usa infraestructura existente | Pagina mas larga | **ELEGIDA** |
| B) Ruta separada /firma/ver/{token} | Separacion de concerns | Requiere nueva ruta, logica duplicada | Descartada |
| C) PDF adjunto al email | Familiar para usuarios | Inseguro, no revocable, sin audit trail, spam | Descartada |
| D) PDF embebido con iframe | Visual profesional | Requiere generar PDF al solicitar firma, +storage | Futura mejora |

### Referencia: Como lo hacen las plataformas lider

- **DocuSign**: NUNCA adjunta PDFs al email. Un link lleva a ver el documento + firmar en la misma pagina.
- **HelloSign/Dropbox Sign**: Link con token unico. Vista del documento + firma integrada.
- **PandaDoc**: Link personalizado por destinatario. Documento visible antes de firmar.
- **SignRequest**: Link sin requerir cuenta. Documento renderizado en navegador.

**Patron universal**: Un solo enlace token-based → muestra documento → seccion de firma abajo.

---

## Arquitectura del Cambio

### Archivos a Modificar

| Archivo | Cambio |
|---------|--------|
| `app/Controllers/FirmaElectronicaController.php` | En `firmar()`: json_decode contenido, pasar `$contenido` y `$cliente` a la vista |
| `app/Views/firma/firmar.php` | Agregar seccion de vista previa del documento entre info y formulario de firma |

### Archivos que NO se modifican

- Rutas (`Routes.php`) - la ruta `/firma/firmar/{token}` ya existe
- Modelos - no se requieren queries nuevas
- Base de datos - no se tocan tablas
- Email - el email sigue igual, mismo link

---

## Flujo Actual vs Nuevo

### ANTES
```
Email → clic "Firmar Documento" → /firma/firmar/{token}
  → Ve: Codigo, Nombre, Version, Firmante
  → Firma sin ver el documento
```

### DESPUES
```
Email → clic "Firmar Documento" → /firma/firmar/{token}
  → Ve: Codigo, Nombre, Version, Firmante
  → Ve: DOCUMENTO COMPLETO (todas las secciones renderizadas)
  → Firma habiendo revisado el contenido
```

---

## Cambios en el Controller

### FirmaElectronicaController::firmar($token)

**Antes (linea ~627):**
```php
public function firmar($token)
{
    $validacion = $this->firmaModel->validarToken($token);
    if (!$validacion['valido']) {
        return view('firma/error', ['error' => $validacion['error']]);
    }
    $solicitud = $validacion['solicitud'];
    $this->firmaModel->registrarAudit(...);
    $documento = $this->getDocumentoSST($solicitud['id_documento']);

    return view('firma/firmar', [
        'solicitud' => $solicitud,
        'documento' => $documento,
        'token' => $token
    ]);
}
```

**Despues:**
```php
public function firmar($token)
{
    $validacion = $this->firmaModel->validarToken($token);
    if (!$validacion['valido']) {
        return view('firma/error', ['error' => $validacion['error']]);
    }
    $solicitud = $validacion['solicitud'];
    $this->firmaModel->registrarAudit(...);
    $documento = $this->getDocumentoSST($solicitud['id_documento']);

    // NUEVO: Decodificar contenido para vista previa
    $contenido = json_decode($documento['contenido'] ?? '{}', true);

    // NUEVO: Obtener datos del cliente para encabezado
    $cliente = $this->clienteModel->find($documento['id_cliente']);

    return view('firma/firmar', [
        'solicitud' => $solicitud,
        'documento' => $documento,
        'contenido' => $contenido,   // NUEVO
        'cliente'   => $cliente,     // NUEVO
        'token' => $token
    ]);
}
```

---

## Cambios en la Vista

### firma/firmar.php - Nueva seccion entre info y formulario

Se agrega un bloque `<div class="card">` colapsable entre el card de "Documento a Firmar"
y el card de "Registrar Firma":

```
[Card: Info del documento]       ← Ya existe (codigo, nombre, version, firmante)
[Card: VISTA PREVIA DOCUMENTO]   ← NUEVO (contenido completo, colapsable)
[Card: Registrar Firma]          ← Ya existe (canvas, upload, terminos, boton)
```

### Estructura del nuevo card:

1. **Encabezado**: Logo cliente (si existe) + nombre empresa + codigo documento
2. **Secciones del documento**: Iteracion sobre `$contenido['secciones']`
   - Cada seccion con titulo y contenido renderizado via Parsedown (Markdown → HTML)
3. **Indicador visual**: Badge que dice "Documento completo - X secciones"
4. **Responsive**: Se ve bien en movil (los firmantes pueden estar en el celular)

### Consideraciones de seguridad:
- El contenido ya esta sanitizado al momento de guardarse en BD
- Se usa Parsedown (igual que las vistas normales) para renderizar Markdown
- No se expone ninguna ruta nueva ni informacion adicional
- El token sigue siendo la unica forma de acceder

---

## Notas Tecnicas

### Parsedown
Las vistas existentes usan `$parsedown = new \Parsedown();` para convertir Markdown a HTML.
La vista de firma debe hacer lo mismo para mantener consistencia visual.

### Contenido JSON
El campo `tbl_documentos_sst.contenido` almacena JSON con esta estructura:
```json
{
  "secciones": [
    {
      "key": "objeto",
      "titulo": "1. OBJETO",
      "contenido": "Texto en markdown...",
      "aprobado": true
    }
  ],
  "empresa": { "nombre": "...", "nit": "..." },
  "representante_legal": { "nombre": "...", "cedula": "..." }
}
```

### Documentos sin secciones
Algunos documentos pueden no tener `secciones` (ej: presupuesto_sst que usa estructura diferente).
En ese caso se muestra un mensaje "El contenido de este documento estara disponible tras la firma."
