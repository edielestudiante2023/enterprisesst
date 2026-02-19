# Blueprint: Eliminar Hardcodeo y Usar Contexto Completo del Cliente

**Fecha:** 2026-02-19
**Caso resuelto:** `ObjetivosSgsstService.php` (Parte 1 del módulo Objetivos SG-SST)
**Estado:** Implementado y probado en producción local

---

## EL PROBLEMA (antes del fix)

### Qué estaba mal

```php
// ObjetivosSgsstService.php — ANTES (MAL)
public const OBJETIVOS_BASE = [
    ['objetivo' => 'Reducir la accidentalidad laboral', ...],
    ['objetivo' => 'Prevenir enfermedades laborales', ...],
    // 6 objetivos genéricos hardcodeados
];

public function previewObjetivos(...) {
    // Solo devolvía array_slice(OBJETIVOS_BASE, 0, $limite)
    // NUNCA llamaba a IA
    // Solo llamaba IA si había instrucciones adicionales del consultor
}
```

### Síntomas

1. Todos los clientes recibían los mismos 6 objetivos genéricos
2. El botón "Regenerar este objetivo" no cambiaba nada (mismo contexto pobre)
3. El campo `observaciones_contexto` de `tbl_cliente_contexto_sst` era completamente ignorado
4. El campo `peligros_identificados` era completamente ignorado
5. Responsables siempre "Responsable SST" — nunca adaptados al cliente

### Patrón roto identificado en 15 servicios más

Ver `docs/CONTINUACION_CHAT.md` para el reporte completo de auditoría:
- **7 servicios ROJOS** (sin IA, 100% hardcodeados)
- **8 servicios AMARILLOS** (con IA pero sin `observaciones_contexto` ni `peligros_identificados`)

---

## LA SOLUCIÓN (después del fix)

### Principio fundamental

> **CERO hardcodeo. La IA genera TODO usando el contexto completo del cliente desde `tbl_cliente_contexto_sst`.**

### Arquitectura del fix

```
┌─────────────────────────────────────────────────────────────────┐
│                    tbl_cliente_contexto_sst                      │
│  actividad_economica_principal, sector_economico, nivel_riesgo  │
│  total_trabajadores, peligros_identificados (JSON),             │
│  observaciones_contexto (TEXT), turnos_trabajo, sedes...        │
└─────────────────────┬───────────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────────┐
│            construirContextoCompleto($contexto, $idCliente)      │
│  Método PÚBLICO en ObjetivosSgsstService (reutilizable)         │
│  Genera un string con TODO el contexto en formato legible IA    │
└─────────────────────┬───────────────────────────────────────────┘
                      │
          ┌───────────┴───────────┐
          ▼                       ▼
┌──────────────────┐    ┌──────────────────────┐
│  previewObjetivos │    │  regenerarObjetivo    │
│  (generar nuevos) │    │  (mejorar/replantear) │
│  SIEMPRE usa IA   │    │  SIEMPRE usa IA       │
└──────────────────┘    └──────────────────────┘
```

---

## ARCHIVOS MODIFICADOS (referencia exacta)

### 1. `app/Services/ObjetivosSgsstService.php`

**Eliminado:**
- Constante `OBJETIVOS_BASE` (6 objetivos hardcodeados)
- Método `personalizarConIA()` (solo se invocaba con instrucciones)

**Conservado:**
- Constante `LIMITES_OBJETIVOS` (3/4/6 según Res. 0312/2019 — esto SÍ es fijo por ley)
- Método `getLimiteObjetivos()`
- Métodos de BD: `generarObjetivos()`, `getObjetivosCliente()`, `eliminarObjetivo()`

**Nuevo/Modificado:**

#### `construirContextoCompleto(?array $contexto, int $idCliente): string` — PÚBLICO

Este es el método central reutilizable. Construye un string de texto con TODO el contexto:

```php
public function construirContextoCompleto(?array $contexto, int $idCliente): string
```

**Campos que incluye (en orden):**

| Campo BD | Cómo lo presenta | Condición |
|----------|-------------------|-----------|
| `nombre_cliente` (de tbl_clientes) | `- Empresa: {nombre}` | Siempre |
| `actividad_economica_principal` | `- Actividad economica principal: X` | Si no vacío |
| `sector_economico` | `- Sector economico: X` | Si no vacío |
| `codigo_ciiu_principal` | `- Codigo CIIU principal: X` | Si no vacío |
| `nivel_riesgo_arl` | `- Nivel de riesgo ARL: X` | Si no vacío |
| `niveles_riesgo_arl` (JSON) | `- Niveles de riesgo multiples: I, III, V` | Si hay >1 nivel |
| `clase_riesgo_cotizacion` | `- Clase de riesgo cotizacion: X` | Si no vacío |
| `arl_actual` | `- ARL actual: X` | Si no vacío |
| `total_trabajadores` | `- Total trabajadores: X` | Siempre |
| `trabajadores_directos` | `  - Directos: X` | Si no vacío |
| `trabajadores_temporales` | `  - Temporales: X` | Si > 0 |
| `contratistas_permanentes` | `  - Contratistas permanentes: X` | Si > 0 |
| `numero_sedes` | `- Numero de sedes: X` | Si > 1 |
| `turnos_trabajo` (JSON) | `- Turnos de trabajo: diurno, nocturno` | Si array no vacío |
| `estandares_aplicables` | `- Estandares aplicables: Completo (60 estandares)` | Siempre (con descripción) |
| `tiene_copasst`, `tiene_vigia_sst`, etc. | `- Infraestructura SST activa: COPASST, Brigada...` | Los que tiene |
| (los que NO tiene) | `- Infraestructura SST faltante: Comite Convivencia...` | Los que le faltan |
| `responsable_sgsst_cargo` | `- Responsable SG-SST: {cargo}` | Si no vacío |
| `peligros_identificados` (JSON) | Sección `PELIGROS IDENTIFICADOS EN LA EMPRESA:` con bullets | Si array no vacío |
| `observaciones_contexto` (TEXT) | Sección `CONTEXTO Y OBSERVACIONES DEL CONSULTOR:` completa | Si no vacío |

**Ejemplo de salida:**

```
CONTEXTO COMPLETO DE LA EMPRESA:
- Empresa: Clinica San Rafael SAS
- Actividad economica principal: Prestacion de servicios de salud
- Sector economico: Salud
- Nivel de riesgo ARL: III
- Total trabajadores: 85
  - Directos: 60
  - Temporales: 15
  - Contratistas permanentes: 10
- Numero de sedes: 3
- Turnos de trabajo: diurno, nocturno, rotativo
- Estandares aplicables: Completo (60 estandares)
- Infraestructura SST activa: COPASST, Comite de Convivencia, Brigada de Emergencias
- Responsable SG-SST: Coordinador de Salud Ocupacional

PELIGROS IDENTIFICADOS EN LA EMPRESA:
- Riesgo biologico (virus, bacterias)
- Riesgo biomecanico (posturas prolongadas)
- Riesgo psicosocial (turnos nocturnos, carga laboral)

CONTEXTO Y OBSERVACIONES DEL CONSULTOR:
La clinica tiene alta rotacion de personal temporal, lo que dificulta la continuidad
de las capacitaciones SST. Se evidencia exposicion constante a fluidos corporales
en areas de atencion directa. El personal administrativo reporta dolor lumbar
frecuente por mobiliario inadecuado.
```

#### `previewObjetivos(int $idCliente, int $anio, ?array $contexto, string $instrucciones)` — MODIFICADO

```php
// ANTES: devolvía array_slice(OBJETIVOS_BASE)
// AHORA: SIEMPRE llama a OpenAI con contexto completo

$contextoTexto = $this->construirContextoCompleto($contexto, $idCliente);
$systemPrompt = $this->construirSystemPromptGenerar($limite);
$userPrompt = "AÑO: {$anio}\nCANTIDAD EXACTA: {$limite} objetivos\n\n" . $contextoTexto;

if (!empty($instrucciones)) {
    $userPrompt .= "\n\nINSTRUCCIONES ADICIONALES DEL CONSULTOR:\n\"{$instrucciones}\"";
}

$response = $this->llamarOpenAI($systemPrompt, $userPrompt, $apiKey, 0.7);
```

#### `construirSystemPromptGenerar(int $limite): string` — NUEVO

System prompt robusto con 10 reglas:
- Genera EXACTAMENTE N objetivos
- Específicos para la actividad económica y peligros
- SMART (Específico, Medible, Alcanzable, Relevante, Temporal)
- Distribución PHVA (mínimo 1 PLANEAR, 1 HACER, incluir VERIFICAR/ACTUAR si el límite lo permite)
- Si hay peligros, al menos 2 objetivos deben abordarlos
- Si hay observaciones, integrar esa información
- NO genéricos — reflejar realidad de la empresa
- JSON sin markdown

#### `llamarOpenAI()` — MODIFICADO

- Temperature: parámetro configurable (default 0.7)
- max_tokens: 3000 (antes 2000)
- Timeout: 45s (antes 30s)
- Logging en cada paso

---

### 2. `app/Controllers/GeneradorIAController.php`

#### `regenerarObjetivo(int $idCliente)` — MODIFICADO

Ahora recibe un campo `modo` del frontend:

```php
$modo = $json['modo'] ?? 'mejorar'; // 'mejorar' o 'replantear'
```

**Dos system prompts diferentes según modo:**

| Modo | System Prompt |
|------|---------------|
| `mejorar` | "MEJORAR un objetivo existente siguiendo las instrucciones... resultado DEBE ser notablemente diferente al original" |
| `replantear` | "CREAR un objetivo COMPLETAMENTE NUEVO... IGNORA el objetivo actual... genera uno totalmente diferente usando el contexto de la empresa" |

#### `construirPromptRegenerarObjetivo(...)` — REESCRITO

Ahora acepta 6 parámetros (antes 4):

```php
private function construirPromptRegenerarObjetivo(
    array $objetivoActual,
    string $instrucciones,
    string $contextoGeneral,
    ?array $contexto,
    int $idCliente,
    string $modo = 'mejorar'
): string
```

**Reutiliza `construirContextoCompleto()`:**

```php
$service = new \App\Services\ObjetivosSgsstService();
$contextoCompleto = $service->construirContextoCompleto($contexto, $idCliente);
```

**Lógica según modo:**

- `modo === 'replantear'`:
  - NO incluye objetivo actual en el prompt
  - Si el usuario escribió texto → lo usa como TÍTULO del nuevo objetivo
  - Si no escribió nada → IA genera uno nuevo basado en contexto/peligros/observaciones

- `modo === 'mejorar'`:
  - SÍ incluye objetivo actual completo
  - Las instrucciones del usuario guían la mejora
  - Obligatorio escribir instrucciones

#### `llamarOpenAIRegenerarObjetivo()` — MODIFICADO

- Temperature: 0.8 (antes 0.7 — más creativo para regenerar)
- max_tokens: 1500 (antes 800)
- Timeout: 45s (antes 30s)
- Logging completo

---

### 3. `app/Views/generador_ia/objetivos_sgsst.php`

#### Sección de contexto — REESCRITA

**Antes:** Solo mostraba 4 campos (actividad, riesgo, trabajadores, estándares) en 2 columnas.

**Ahora:** 3 columnas:

| Columna 1 | Columna 2 | Columna 3 |
|-----------|-----------|-----------|
| Datos empresa (actividad, sector, riesgo, trabajadores, estándares) | Infraestructura SST (badges verdes para activos), Responsable, Política SST | Peligros identificados (badges rojos, scroll) |

**Extras:**
- Botón "Editar Contexto" → abre `/contexto/{id}` en nueva pestaña
- Si hay `observaciones_contexto` → se muestra en sección colapsable
- Si NO hay peligros → alerta con link a registrarlos

#### Preview — MODIFICADO

- Spinner de carga mientras la IA genera ("Generando objetivos personalizados con IA...")
- Manejo de errores visible (si la API falla, muestra el error en el modal)
- Texto "Total: X objetivos generados por IA" (antes decía "sugeridos")

#### Botones Mejorar con IA — DOS BOTONES

```html
<div class="d-flex gap-2">
    <button onclick="regenerarObjetivoConIA(${idx}, this, 'mejorar')">
        Regenerar          <!-- morado, mejora el actual -->
    </button>
    <button onclick="regenerarObjetivoConIA(${idx}, this, 'replantear')">
        Replantear nuevo   <!-- rojo, lienzo en blanco -->
    </button>
</div>
```

| Botón | Color | Textarea | Qué hace |
|-------|-------|----------|----------|
| **Regenerar** | Morado (#9c27b0) | OBLIGATORIO | Mejora el objetivo actual según instrucciones |
| **Replantear nuevo** | Rojo (outline-danger) | OPCIONAL | Lienzo en blanco: genera uno nuevo. Si hay texto, lo usa como título |

#### JS `regenerarObjetivoConIA(idx, btnElement, modo)` — REESCRITO

Cambios clave:
- Recibe `btnElement` como parámetro (NO usa `event.target` — causaba bug silencioso)
- Recibe `modo` ('mejorar' o 'replantear')
- Deshabilita AMBOS botones durante la llamada
- Envía `modo` al backend en el body del POST
- Feedback visual diferenciado: borde verde (mejorar) vs borde rojo (replantear)
- Limpia textarea después de éxito
- `console.error()` en caso de fallo para debug en DevTools

---

## CÓMO REPLICAR PARA OTROS SERVICIOS

### Paso 1: Identificar el servicio a arreglar

Ejemplo: `ActividadesPvePsicosocialService.php`

### Paso 2: Eliminar constantes hardcodeadas

```php
// ELIMINAR:
public const ACTIVIDADES_PVE_PSICOSOCIAL = [...];

// CONSERVAR (si aplica por ley):
public const LIMITES_X = [...]; // Solo si es un límite legal (Res. 0312)
```

### Paso 3: Modificar el método preview/generar para SIEMPRE llamar IA

```php
// Reutilizar construirContextoCompleto() de ObjetivosSgsstService
$service = new \App\Services\ObjetivosSgsstService();
$contextoCompleto = $service->construirContextoCompleto($contexto, $idCliente);

// Construir system prompt específico para este tipo de contenido
$systemPrompt = "Eres un experto en SST de Colombia. Genera actividades de PVE Psicosocial personalizadas...";

// Incluir contexto completo en user prompt
$userPrompt = $contextoCompleto . "\n\nGenera exactamente {$limite} actividades...";

// Llamar IA
$response = $this->llamarOpenAI($systemPrompt, $userPrompt, $apiKey, 0.7);
```

### Paso 4: Actualizar getResumen() si referencia constantes eliminadas

```php
// ANTES (MAL):
'sugeridos' => count(self::ACTIVIDADES_X),

// DESPUÉS (BIEN):
'limite' => $limite,  // calculado desde estandares_aplicables
```

### Paso 5: Actualizar vista si muestra contexto

Replicar el patrón de 3 columnas con peligros y observaciones visibles.

### Paso 6: Si tiene "Regenerar individual", agregar modo replantear

Mismo patrón: dos botones, parámetro `modo`, prompts diferenciados.

---

## CONFIGURACIÓN DE IA

| Parámetro | Generar (preview) | Regenerar (mejorar) | Replantear (nuevo) |
|-----------|-------------------|--------------------|--------------------|
| Temperature | 0.7 | 0.8 | 0.8 |
| max_tokens | 3000 | 1500 | 1500 |
| Timeout | 45s | 45s | 45s |
| Modelo | env('OPENAI_MODEL') | env('OPENAI_MODEL') | env('OPENAI_MODEL') |

---

## CAMPOS DE tbl_cliente_contexto_sst — REFERENCIA RÁPIDA

Todos estos campos DEBEN llegar al prompt de IA via `construirContextoCompleto()`:

```
actividad_economica_principal  → Actividad económica
sector_economico               → Sector
codigo_ciiu_principal          → Código CIIU
nivel_riesgo_arl               → Riesgo ARL (I-V)
niveles_riesgo_arl             → JSON: múltiples niveles
clase_riesgo_cotizacion        → Clase cotización (1-5)
arl_actual                     → Nombre ARL
total_trabajadores             → Total
trabajadores_directos          → Directos
trabajadores_temporales        → Temporales
contratistas_permanentes       → Contratistas
numero_sedes                   → Sedes
turnos_trabajo                 → JSON: ["diurno","nocturno"]
estandares_aplicables          → 7, 21 o 60
tiene_copasst                  → 0/1
tiene_vigia_sst                → 0/1
tiene_comite_convivencia       → 0/1
tiene_brigada_emergencias      → 0/1
responsable_sgsst_cargo        → Cargo del responsable
peligros_identificados         → JSON array de peligros
observaciones_contexto         → TEXT libre del consultor ← CLAVE
```

---

## BUG JS CORREGIDO — IMPORTANTE PARA TODAS LAS VISTAS

**Bug:** Usar `event.target` en onclick handlers captura el `<i>` icon hijo en vez del `<button>`, causando fallo silencioso.

```js
// MAL (bug silencioso):
onclick="miFuncion(idx)"
function miFuncion(idx) {
    const btn = event.target; // ← puede ser <i>, no <button>
}

// BIEN:
onclick="miFuncion(idx, this)"
function miFuncion(idx, btnElement) {
    const btn = btnElement; // ← siempre es el <button>
}
```

Revisar TODAS las vistas de generador_ia que usen este patrón y corregirlo.
