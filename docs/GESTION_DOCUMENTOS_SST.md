# Gestión de Documentos SST

## Resumen

Este documento explica cómo gestionar los documentos del Sistema de Gestión de Seguridad y Salud en el Trabajo (SG-SST) en la aplicación.

---

## 1. Estructura de Documentos

### 1.1 Tablas Principales

| Tabla | Propósito |
|-------|-----------|
| `tbl_documentos_sst` | Documento principal (metadata, estado, código) |
| `tbl_doc_versiones_sst` | Historial de versiones del documento |
| `tbl_doc_plantillas` | Plantillas y códigos base de cada tipo |
| `tbl_doc_firma_solicitudes` | Solicitudes de firma electrónica |

### 1.2 Estados de un Documento

```
borrador → generado → pendiente_firma → firmado → aprobado
```

| Estado | Descripción |
|--------|-------------|
| `borrador` | Documento creado pero sin contenido completo |
| `generado` | Contenido generado (por IA o manual) |
| `pendiente_firma` | Enviado para firma electrónica |
| `firmado` | Todas las firmas completadas |
| `aprobado` | Documento oficialmente aprobado |

---

## 2. Códigos de Documentos

### 2.1 Estructura del Código

```
PREFIJO-CONSECUTIVO
```

**Ejemplos:**
- `FT-SST-001` - Formato SST #1 (Presupuesto)
- `PRG-CAP-001` - Programa de Capacitación #1
- `PRO-DOC-001` - Procedimiento de Control Documental #1

### 2.2 Fuente del Código Base

El código base viene de `tbl_doc_plantillas.codigo_sugerido`:

```sql
SELECT codigo_sugerido FROM tbl_doc_plantillas
WHERE tipo_documento = 'presupuesto_sst' AND activo = 1;
-- Resultado: 'FT-SST'
```

El sistema agrega automáticamente el consecutivo: `FT-SST` → `FT-SST-001`

### 2.3 Auto-Corrección de Códigos Legacy

Si un documento tiene código incorrecto (ej: hardcodeado), el sistema lo corrige automáticamente:

**Ubicación:** `DocumentacionController.php` líneas 337-346

```php
// Auto-corrección de código para presupuesto_sst
if ($docSST['tipo_documento'] === 'presupuesto_sst' && $docSST['codigo'] !== 'FT-SST-001') {
    $db->table('tbl_documentos_sst')
        ->where('id_documento', $docSST['id_documento'])
        ->update(['codigo' => 'FT-SST-001']);
    $docSST['codigo'] = 'FT-SST-001';
}
```

---

## 3. Conversión de Markdown a HTML

### 3.1 Sintaxis Soportada

Las vistas de documentos SST soportan la siguiente sintaxis Markdown:

| Sintaxis | Resultado | Ejemplo |
|----------|-----------|---------|
| `**texto**` | **Negrita** | `**importante**` → **importante** |
| `*texto*` | *Cursiva* | `*nota*` → *nota* |
| `## Título` | Encabezado H2 | `## Objetivo` |
| `- item` | Lista | `- Primer punto` |
| `1. item` | Lista numerada | `1. Paso uno` |
| `\|col1\|col2\|` | Tabla | Ver ejemplo abajo |

### 3.2 Ejemplo de Tabla Markdown

```markdown
| Tipo | Prefijo | Descripción |
|------|---------|-------------|
| Formato | FT | Documentos de registro |
| Programa | PRG | Planes de acción |
| Procedimiento | PRO | Instrucciones paso a paso |
```

### 3.3 Función de Conversión

**Ubicación:** Definida en cada vista que la necesite (ej: `procedimiento_control_documental.php`)

```php
if (!function_exists('convertirMarkdownAHtml')) {
    function convertirMarkdownAHtml($texto) {
        // 1. Convierte tablas Markdown
        $texto = convertirTablasMarkdown($texto);
        // 2. Convierte texto (negritas, cursivas, listas)
        $resultado = convertirTextoMarkdown($texto);
        return $resultado;
    }
}
```

### 3.4 Uso Correcto en Vistas

**CORRECTO** - Usar la función de conversión:
```php
<?= convertirMarkdownAHtml($seccion['contenido']) ?>
```

**INCORRECTO** - Esto NO convierte markdown:
```php
<?= nl2br(esc($seccion['contenido'])) ?>
```

### 3.5 Secciones Especiales

Algunas secciones pueden mostrar tablas dinámicas del sistema O contenido guardado:

| Key de Sección | Comportamiento |
|----------------|----------------|
| `tipos_documentos` | Si hay contenido guardado → mostrar solo ese. Si no → tabla dinámica |
| `codificacion` | Si hay contenido guardado → mostrar solo ese. Si no → tabla dinámica |
| `listado_maestro` | Siempre muestra tabla dinámica (son documentos reales del cliente) |

**Lógica para secciones 6 y 7:**
```php
if ($keySeccion === 'tipos_documentos'):
    if (!empty($seccion['contenido'])):
        // Mostrar SOLO el contenido guardado (incluye tabla markdown)
        echo convertirMarkdownAHtml($seccion['contenido']);
    elseif (!empty($tiposDocumento)):
        // Fallback: mostrar tabla dinámica del sistema
        echo '<table>...</table>';
    endif;
endif;
```

**Lógica para sección 13 (Listado Maestro):**
```php
// El listado maestro SIEMPRE muestra la tabla dinámica
// porque representa los documentos reales del cliente
if ($keySeccion === 'listado_maestro'):
    echo convertirMarkdownAHtml($seccion['contenido']); // Texto intro
    echo '<table>...</table>'; // Tabla siempre dinámica
endif;
```

---

## 4. Vistas de Documentos

### 4.1 Ubicación de Vistas

```
app/Views/documentos_sst/
├── presupuesto_sst.php
├── presupuesto_preview.php
├── presupuesto_pdf.php
├── presupuesto_word.php
├── programa_capacitacion.php
├── procedimiento_control_documental.php
└── ... otros documentos
```

### 4.2 Estructura Típica de una Vista

```php
<!-- Encabezado con logo y datos -->
<table class="encabezado-table">
    <tr>
        <td>Logo</td>
        <td>Título</td>
        <td>Código, Versión, Fecha</td>
    </tr>
</table>

<!-- Secciones del documento -->
<?php foreach ($contenido['secciones'] as $seccion): ?>
    <div class="seccion">
        <div class="seccion-titulo"><?= esc($seccion['titulo']) ?></div>
        <div class="seccion-contenido">
            <?= convertirMarkdownAHtml($seccion['contenido']) ?>
        </div>
    </div>
<?php endforeach; ?>

<!-- Control de Cambios -->
<table><!-- Versiones --></table>

<!-- Firmas -->
<table><!-- Firmantes --></table>
```

---

## 5. Generación de Contenido con IA

### 5.1 Flujo de Generación

```
1. Usuario abre documento → contenido vacío
2. Clic en "Generar con IA"
3. Sistema envía contexto a Claude API
4. Claude genera contenido en Markdown
5. Sistema guarda en tbl_documentos_sst.contenido (JSON)
6. Vista convierte Markdown a HTML para mostrar
```

### 5.2 Estructura del Contenido JSON

```json
{
    "titulo": "Procedimiento de Control Documental",
    "secciones": [
        {
            "key": "objetivo",
            "titulo": "1. Objetivo",
            "contenido": "Establecer los lineamientos para..."
        },
        {
            "key": "tipos_documentos",
            "titulo": "6. Tipos de Documentos",
            "contenido": "**Clasificación:**\n\n| Tipo | Descripción |..."
        }
    ]
}
```

---

## 6. Firmas Electrónicas

### 6.1 Proceso de Firma

```
1. Documento en estado "generado"
2. Enviar solicitud de firma (genera token único)
3. Firmantes reciben email con enlace
4. Firmante accede, ve documento, firma con contraseña
5. Sistema registra firma con IP, timestamp, hash
6. Al completar todas las firmas → estado "firmado"
```

### 6.2 Firmantes Típicos

| Rol | Cuándo Aplica |
|-----|---------------|
| Consultor SST | Siempre |
| Delegado SST | Si aplica (>10 trabajadores o actividad especial) |
| Representante Legal | Siempre |

---

## 7. Control de Versiones

### 7.1 Crear Nueva Versión

Solo se puede crear nueva versión si el documento está `firmado` o `aprobado`:

```php
// PzpresupuestoSstController::crearNuevaVersion()
if (!in_array($documento['estado'], ['firmado', 'aprobado'])) {
    return error('Solo documentos firmados pueden tener nueva versión');
}
```

### 7.2 Numeración de Versiones

- **Cambio Mayor:** 1.0 → 2.0 → 3.0
- **Cambio Menor:** 1.0 → 1.1 → 1.2

---

## 8. Exportación

### 8.1 Formatos Disponibles

| Formato | Uso |
|---------|-----|
| **Vista Web** | Revisión en pantalla |
| **PDF** | Archivo oficial, impresión |
| **Word** | Edición externa si es necesario |

### 8.2 Consideraciones para PDF/Word

- Las imágenes deben estar en base64
- Los QR codes se generan con la librería `chillerlan/php-qrcode`
- Los estilos CSS deben estar inline para DOMPDF

---

## 9. Troubleshooting

### 9.1 Markdown No Se Renderiza

**Síntoma:** Se ven `**asteriscos**` o `|tablas|` sin formato

**Causa:** La sección usa `nl2br(esc())` en lugar de `convertirMarkdownAHtml()`

**Solución:** Cambiar en la vista:
```php
// Antes (incorrecto)
<?= nl2br(esc($seccion['contenido'])) ?>

// Después (correcto)
<?= convertirMarkdownAHtml($seccion['contenido']) ?>
```

### 9.2 Código de Documento Incorrecto

**Síntoma:** Documento muestra `FT-SST-004` en lugar de `FT-SST-001`

**Causa:** Código hardcodeado en creación anterior

**Solución:** El sistema auto-corrige al visualizar, o ejecutar:
```sql
UPDATE tbl_documentos_sst SET codigo = 'FT-SST-001'
WHERE tipo_documento = 'presupuesto_sst';
```

### 9.3 Tabla Dinámica No Aparece

**Síntoma:** Sección de "Tipos de Documentos" sin tabla

**Causa:** Variable `$tiposDocumento` no pasada desde controlador

**Solución:** Verificar que el controlador pase los datos:
```php
return view('documentos_sst/procedimiento_control_documental', [
    'tiposDocumento' => $tiposDocumento,
    'plantillas' => $plantillas,
    // ...
]);
```

---

## 10. Agregar Nuevo Tipo de Documento

### 10.1 Pasos

1. **Crear clase** en `app/Libraries/DocumentosSSTTypes/`
   ```php
   class NuevoDocumento extends AbstractDocumentoSST {
       public function getTipoDocumento(): string {
           return 'nuevo_documento';
       }
       // ... implementar métodos requeridos
   }
   ```

2. **Registrar en Factory** (`DocumentoSSTFactory.php`)

3. **Crear vista** en `app/Views/documentos_sst/nuevo_documento.php`

4. **Agregar ruta** en `app/Config/Routes.php`

5. **Insertar plantilla** en BD:
   ```sql
   INSERT INTO tbl_doc_plantillas (tipo_documento, nombre, codigo_sugerido, activo)
   VALUES ('nuevo_documento', 'Nuevo Documento', 'ND-SST', 1);
   ```

---

## Autor

- **Creado:** 2026-01-31
- **Framework:** CodeIgniter 4
- **Patrón:** Strategy + Factory para tipos de documentos
