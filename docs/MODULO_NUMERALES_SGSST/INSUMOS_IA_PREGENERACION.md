# Insumos IA - Pregeneración

**Estado:** ✅ Implementado completamente (2026-02-15)
**Módulo:** Generación de documentos SST con IA

---

## Problema

La IA genera documentos SST citando normativa desactualizada porque el modelo económico (GPT-4o-mini) tiene conocimiento limitado. La normativa SST colombiana cambia constantemente y los consultores necesitan documentos con legislación vigente.

## Solución

Crear una etapa **anterior** a la generación IA donde se prepara y almacena el marco normativo vigente por tipo de documento. La IA recibe este marco como contexto antes de generar cada sección, así redacta con las normas correctas sin necesidad de usar un modelo costoso para cada sección.

### ✨ Características clave
- **Versionamiento completo:** Auditoría de todos los cambios del marco normativo
- **4 opciones de actualización:** Automática, manual, por demanda, con confirmación
- **Contexto personalizable:** El consultor puede dar instrucciones específicas a la IA
- **Sin duplicados de BD:** Un marco normativo global por tipo de documento (no por cliente)

## Relacion con el flujo existente

```
INSUMOS IA - PREGENERACION
(Preparar el marco normativo)
---------------------------------------------
- Existe marco normativo para este tipo de documento?
- Esta vigente o vencio los 90 dias?
- El consultor quiere revisarlo/editarlo?
- 4 opciones de actualizacion disponibles
- Resultado: marco normativo listo en BD
---------------------------------------------
                    |
                    v
GENERACION IA DEL DOCUMENTO
(Flujo existente - Tipo A y Tipo B)
---------------------------------------------
- Toma marco normativo de BD + contexto cliente
- Genera cada seccion con modelo economico
- Flujo actual sin cambios
---------------------------------------------
```

## 4 Opciones de Actualizacion del Marco Normativo

Las 4 opciones coexisten. El consultor usa la que necesite segun el momento.

### Opcion 1 - Actualizacion automatica por vigencia (90 dias)

Al cargar la pagina de generacion, si el marco normativo almacenado tiene mas de 90 dias (o no existe), el sistema automaticamente consulta GPT-4o con busqueda web, actualiza el registro en BD.

- **Cuando actua:** Al cargar la pagina, si el checkbox "Auto si >90 dias" esta activo
- **Quien lo dispara:** El sistema, automaticamente
- **Metodo en BD:** `automatico`
- **Costo:** Una consulta a GPT-4o con web_search_preview

### Opcion 2 - Actualizacion manual por demanda (boton)

El consultor presiona el boton "Consultar IA" en el sidebar o en el modal cuando sabe que hubo cambios legislativos.

- **Cuando actua:** Cuando el consultor lo decide
- **Quien lo dispara:** El consultor, con boton "Consultar IA"
- **Metodo en BD:** `boton`
- **Costo:** Una consulta a GPT-4o con web_search_preview

### Opcion 3 - Confirmacion al momento de generar

Al hacer clic en "Generar con IA" (individual o todo), si el checkbox "Preguntar al generar" esta activo y el marco no esta vigente, muestra SweetAlert: "Deseas actualizar el marco normativo con IA antes de generar?"

- **Cuando actua:** Primera vez que se genera en la sesion
- **Quien lo dispara:** El sistema pregunta, el usuario decide
- **Metodo en BD:** `confirmacion`
- **Costo:** Solo si el usuario elige actualizar

### Opcion 4 - Edicion directa por el consultor

Boton "Ver/Editar" abre modal con textarea. El consultor investiga por su cuenta usando ChatGPT, Gemini, Claude o fuentes oficiales, y edita directamente el texto.

- **Cuando actua:** Cuando el consultor quiere control total
- **Quien lo dispara:** El consultor entra al modal de edicion
- **Metodo en BD:** `manual`
- **Costo:** Cero en API

## Tabla BD

```sql
CREATE TABLE IF NOT EXISTS tbl_marco_normativo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_documento VARCHAR(100) NOT NULL,        -- snake_case: politica_sst_general
    marco_normativo_texto TEXT NOT NULL,          -- Contenido del marco normativo vigente
    fecha_actualizacion DATETIME NOT NULL,
    actualizado_por VARCHAR(100) DEFAULT 'sistema', -- sistema | consultor | nombre_usuario
    metodo_actualizacion VARCHAR(20) DEFAULT 'manual', -- automatico | boton | confirmacion | manual
    vigencia_dias INT DEFAULT 90,
    activo TINYINT(1) DEFAULT 1 COMMENT '1 = versión vigente, 0 = histórica',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tipo_activo (tipo_documento, activo, fecha_actualizacion DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### ⚠️ Cambio crítico: Versionamiento habilitado

**Antes (versión inicial):** Tenía constraint `UNIQUE KEY idx_tipo_documento` → solo 1 versión por tipo
**Ahora (versión actual):** Sin UNIQUE, con índice compuesto → **historial completo de versiones**

- **Campo `activo`:** `1` = versión vigente actual, `0` = versión histórica
- **Consultas:** Siempre filtrar por `WHERE activo = 1` para obtener la versión vigente
- **Guardar:** Método `guardar()` SIEMPRE hace INSERT (nunca UPDATE) para mantener historial
- **Auditoría:** Cada consulta IA o edición manual crea una nueva fila en BD

**Scripts:**
- Creación inicial: `app/SQL/crear_tbl_marco_normativo.sql`
- Migración a historial: `app/SQL/migrar_marco_normativo_historial.sql` ✅ **Ejecutado en LOCAL y PRODUCCIÓN**

## Archivos implementados

### Nuevos
| Archivo | Descripcion |
|---------|-------------|
| `app/SQL/crear_tbl_marco_normativo.sql` | Script CREATE TABLE |
| `app/Models/MarcoNormativoModel.php` | Modelo CRUD para tbl_marco_normativo |
| `app/Services/MarcoNormativoService.php` | Logica de negocio + consulta Responses API |

### Modificados
| Archivo | Cambio |
|---------|--------|
| `app/Controllers/DocumentosSSTController.php` | 3 endpoints AJAX + inyeccion en generarConIAReal() |
| `app/Config/Routes.php` | 3 rutas para marco normativo |
| `app/Services/IADocumentacionService.php` | Inyeccion del marco normativo en construirPrompt() |
| `app/Views/documentos_sst/generar_con_ia.php` | Panel sidebar + modal edicion + JS 4 opciones |

## Endpoints API

```
GET  /documentos/marco-normativo/{tipo}          -> getMarcoNormativo($tipo)
POST /documentos/marco-normativo/guardar          -> guardarMarcoNormativo()
POST /documentos/marco-normativo/consultar-ia     -> consultarMarcoNormativoIA()
```

### GET /documentos/marco-normativo/{tipo}
Retorna JSON:
```json
{
    "existe": true,
    "texto": "**Decreto 1072 de 2015**...",
    "fecha": "2026-02-14 10:30:00",
    "dias": 5,
    "vigente": true,
    "metodo": "boton",
    "actualizado_por": "sistema",
    "vigencia_dias": 90
}
```

### POST /documentos/marco-normativo/guardar
Body: `tipo_documento=politica_sst_general&marco_normativo_texto=...`
Retorna: `{ "success": true, "message": "..." }`

### POST /documentos/marco-normativo/consultar-ia
Body: `tipo_documento=politica_sst_general&metodo=boton`
Retorna: `{ "success": true, "texto": "..." }`

## Consulta IA: OpenAI Responses API

```php
// Endpoint: https://api.openai.com/v1/responses
$data = [
    'model' => 'gpt-4o',
    'input' => $prompt,  // Prompt pidiendo marco normativo vigente en Colombia
    'tools' => [
        ['type' => 'web_search_preview']  // Busqueda web en tiempo real
    ],
    'temperature' => 0.3
];
// Timeout: 90 segundos
// API Key: env('OPENAI_API_KEY')
```

La respuesta de Responses API tiene estructura: `output[] -> content[] -> text`
El metodo `extraerTextoRespuesta()` en MarcoNormativoService parsea esta estructura.

## Inyeccion en el prompt

En `IADocumentacionService::construirPrompt()`, despues del contexto base del documento y antes de "DOCUMENTO A GENERAR":

```php
$marcoNormativo = $datos['marco_normativo'] ?? '';
if (!empty($marcoNormativo)) {
    $userPrompt .= "\nMARCO NORMATIVO VIGENTE APLICABLE (fuente verificada, usar EXCLUSIVAMENTE):\n";
    $userPrompt .= $marcoNormativo . "\n";
    $userPrompt .= "IMPORTANTE: Usa SOLO las normas listadas arriba. NO inventes ni agregues normas adicionales.\n";
}
```

En `DocumentosSSTController::generarConIAReal()`:
```php
$marcoService = new MarcoNormativoService();
$marcoNormativo = $marcoService->obtenerMarcoNormativo($tipoDocumento);

$datosIA = [
    // ... datos existentes ...
    'marco_normativo' => $marcoNormativo ?? ''
];
```

## UI - Panel en sidebar

Panel colapsable en el sidebar de `generar_con_ia.php`, entre el card de progreso y el card de secciones:

- Badge de estado: Vigente (verde), Vencido (rojo), Sin datos (amarillo)
- Info: días desde actualización, método, quién actualizó
- **Botón "Consultar IA"** (primero): Consulta GPT-4o con búsqueda web (opción 2) con SweetAlert educativo
- **Botón "Ver/Editar"** (segundo): Abre modal con textarea (opción 4)
- Checkbox "Auto si >90 días": opción 1
- Checkbox "Preguntar al generar": opción 3

### Modal de edición (opción 4)

**Estructura:**
1. **Textarea principal:** Contenido del marco normativo actual (editable)
2. **Textarea de contexto IA:** Instrucciones adicionales para personalizar la consulta IA
   - Placeholder dinámico con año actual: `<?= date('Y') ?>`
   - Ejemplo: "Enfocarse en acoso laboral", "Incluir legislación reciente 2026"
3. **Botones de acción:**
   - **"Consultar con IA"** dentro del modal (opción 2 integrada)
   - **"Guardar Marco Normativo"** (guarda edición manual)

### Mejoras UX implementadas (2026-02-15)

✅ **Orden de botones corregido:**
- Antes: [Ver/Editar] [Consultar IA]
- Ahora: [Consultar IA] [Ver/Editar]
- Razón: El flujo más común es consultar primero, luego revisar/editar si es necesario

✅ **SweetAlert educativo antes de consultar IA:**
- Explica qué hace GPT-4o + búsqueda web
- Muestra tiempo estimado (30-90 segundos)
- Describe las 4 opciones disponibles
- Usuario puede cancelar o confirmar

✅ **Toast mejorado al guardar:**
- Antes: "Marco normativo guardado"
- Ahora: "Marco normativo actualizado correctamente. Este contenido se inyectará en todas las generaciones IA."
- Más informativo sobre el impacto del cambio

✅ **Contexto IA personalizable:**
- El consultor puede dar instrucciones específicas a GPT-4o
- Útil para enfocarse en temas particulares o priorizar cierta legislación
- El contexto se envía junto con el prompt base

## Valores Hardcodeados vs Dinámicos

Para un análisis completo de valores hardcodeados en el sistema, ver: [`VALORES_HARDCODEADOS_MARCO_NORMATIVO.md`](VALORES_HARDCODEADOS_MARCO_NORMATIVO.md)

### Resumen de decisiones

| Valor | Estado | Decisión |
|-------|--------|----------|
| **Año en placeholder** | ✅ Dinámico | `<?= date('Y') ?>` - muestra año actual |
| **Vigencia de 90 días** | 🔴 Hardcodeado en UI | Debería leer de `vigencia_dias` de BD |
| **Timeout 90 segundos** | 🟡 Hardcodeado | Constante técnica de API (aceptable) |
| **Modelo GPT-4o** | 🟢 Hardcodeado | Único modelo con `web_search_preview` |
| **Temperatura 0.3** | 🟢 Hardcodeado | Diseño intencional para precisión legal |

---

## Integración con Generación IA

### En `IADocumentacionService::construirPrompt()`

Después del contexto base del documento (~línea 226), se inyecta:

```php
// INSUMOS IA - PREGENERACIÓN: Marco normativo desde BD
$marcoNormativo = $datos['marco_normativo'] ?? '';
if (!empty($marcoNormativo)) {
    $userPrompt .= "\nMARCO NORMATIVO VIGENTE APLICABLE (fuente verificada, usar EXCLUSIVAMENTE este marco):\n";
    $userPrompt .= $marcoNormativo . "\n";
    $userPrompt .= "IMPORTANTE: Usa SOLO las normas listadas arriba. NO inventes ni agregues normas adicionales.\n";
}
```

### En `DocumentosSSTController::generarConIAReal()`

Antes de llamar al servicio IA (~línea 631):

```php
// Obtener marco normativo de BD
$marcoService = new MarcoNormativoService();
$marcoNormativo = $marcoService->obtenerMarcoNormativo($tipoDocumento);

$datosIA = [
    // ... datos existentes ...
    'marco_normativo' => $marcoNormativo ?? ''
];
```

---

## Historial de cambios del documento

| Fecha | Cambio |
|-------|--------|
| 2026-02-14 | Creación inicial. Problema identificado en reunión con especialista SST |
| 2026-02-14 | Implementación completa: tabla, modelo, servicio, endpoints, UI, inyección prompt |
| 2026-02-15 | **MIGRACIÓN CRÍTICA:** Habilitado versionamiento completo (quitar UNIQUE, agregar índice compuesto). Ejecutado en LOCAL y PRODUCCIÓN |
| 2026-02-15 | UX: Orden de botones, SweetAlert educativo, toast mejorado, contexto IA personalizable |
| 2026-02-15 | Corregido placeholder hardcodeado "2023-2024" → dinámico `<?= date('Y') ?>` |
| 2026-02-15 | Documentación completa de valores hardcodeados identificados |

---

## Scripts de Migración Ejecutados

### ✅ `migrar_marco_normativo_historial.sql`

**Ejecutado:** 2026-02-15 en LOCAL y PRODUCCIÓN
**Método:** Script PHP dual (`ejecutar_migracion_historial_v2.php`)

**Cambios aplicados:**
1. Eliminó constraint `UNIQUE KEY idx_tipo_documento`
2. Agregó índice compuesto `idx_tipo_activo (tipo_documento, activo, fecha_actualizacion DESC)`
3. Modificó comentario de columna `activo` para documentar su propósito

**Impacto:**
- Antes: Solo 1 versión por tipo de documento (sobreescritura)
- Después: Historial completo de versiones (auditoría)

**Validación:**
```sql
-- Verificar que el índice compuesto existe:
SHOW INDEX FROM tbl_marco_normativo WHERE Key_name = 'idx_tipo_activo';

-- Verificar que NO existe el UNIQUE constraint:
SHOW INDEX FROM tbl_marco_normativo WHERE Key_name = 'idx_tipo_documento';
```

---

## Pruebas Realizadas

### ✅ Flujo completo validado

1. **Consulta IA (opción 2):**
   - Documento: `politica_sst_general` (cliente 18)
   - Resultado: Marco normativo consultado con GPT-4o + web search
   - Verificado: Registro guardado en BD con `metodo_actualizacion = 'boton'`
   - Versionamiento: Nueva fila creada, versión anterior marcada `activo = 0`

2. **Edición manual (opción 4):**
   - Documento: Política de Prevención del Acoso Sexual
   - Modificación manual del texto en modal
   - Verificado: Nueva versión guardada con `metodo_actualizacion = 'manual'`
   - Contexto IA: Textarea funcional, placeholder dinámico con año 2026

3. **Inyección en prompt:**
   - Generación de sección con IA después de actualizar marco normativo
   - Verificado: Marco normativo presente en el prompt enviado a GPT-4o-mini
   - Resultado: Normativa citada coincide con el marco normativo de BD

---

## Próximos Pasos (Pendientes)

### 🔴 Prioridad Alta

1. **Hacer vigencia_dias dinámico en UI**
   - Eliminar referencias hardcodeadas a "90 días" en labels/tooltips
   - Pasar `vigencia_dias` como variable PHP desde controller
   - Actualizar JavaScript para usar valor dinámico

### 🟡 Prioridad Media

2. **Dashboard de marco normativo**
   - Vista de todos los tipos de documentos con marco normativo
   - Última actualización, método, vigencia
   - Acceso rápido a consultar/editar cada uno

3. **Historial visual por tipo**
   - Mostrar timeline de versiones en el modal
   - Ver diferencias entre versiones (diff)
   - Restaurar versión anterior si es necesario

### 🟢 Mejoras Futuras

4. **Notificaciones automáticas**
   - Email al consultor cuando un marco normativo vence
   - Sugerencia de actualización al entrar al sistema

5. **Estadísticas de uso**
   - Cuántas veces se consultó la IA por tipo
   - Frecuencia de actualizaciones manuales vs automáticas
   - Costos de API por tipo de documento
