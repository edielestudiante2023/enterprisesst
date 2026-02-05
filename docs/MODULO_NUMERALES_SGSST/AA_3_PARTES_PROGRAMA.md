# Arquitectura de Módulo en 3 Partes con Generación IA Segura

## Concepto Fundamental

Un módulo de 3 partes implementa una **cadena de dependencias obligatorias** donde cada fase DEBE consumir datos de la fase anterior desde la base de datos. Sin esta consulta previa, la IA NO PUEDE generar contenido.

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        CADENA DE DEPENDENCIAS                               │
│                                                                             │
│   PARTE 1              PARTE 2              PARTE 3                        │
│   ────────             ────────             ────────                        │
│   Actividades    →     Indicadores    →     Documento                      │
│                                                                             │
│   ┌─────────┐         ┌─────────┐          ┌─────────┐                     │
│   │   IA    │         │   IA    │          │   IA    │                     │
│   │ genera  │         │ genera  │          │ genera  │                     │
│   └────┬────┘         └────┬────┘          └────┬────┘                     │
│        │                   │                    │                          │
│        ▼                   ▼                    ▼                          │
│   ┌─────────┐         ┌─────────┐          ┌─────────┐                     │
│   │  GUARDA │         │  GUARDA │          │ EXPORTA │                     │
│   │   BD    │────────→│   BD    │─────────→│ PDF/Word│                     │
│   └─────────┘  LEE    └─────────┘   LEE    └─────────┘                     │
│                                                                             │
│   tbl_pta_cliente      tbl_indicadores_sst    Vista preview               │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## Ejemplo Real: Programa de Promoción y Prevención en Salud (3.1.2)

### PARTE 1: Actividades en Plan de Trabajo

**Ubicación:** Módulo Plan de Trabajo Anual
**Tabla:** `tbl_pta_cliente`
**Categoría filtro:** `categoria = 'pyp_salud'`

```php
// Las actividades se generan con IA usando contexto del cliente
// y se GUARDAN en la base de datos
$actividadesGeneradas = $iaService->generarActividades($contextoCliente);
$ptaModel->insertBatch($actividadesGeneradas);
```

**Campos clave guardados:**
- `id_cliente` - Identificador del cliente
- `actividad` - Nombre de la actividad generada
- `objetivo` - Objetivo de la actividad
- `responsable` - Quién ejecuta
- `recursos` - Recursos necesarios
- `meta` - Meta cuantificable
- `categoria` - 'pyp_salud' para este módulo

---

### PARTE 2: Indicadores (Consume Actividades)

**Ubicación:** Módulo de Indicadores SST
**Tabla:** `tbl_indicadores_sst`
**Categoría filtro:** `categoria = 'pyp_salud'`

**REQUISITO CRÍTICO:** Antes de generar indicadores, DEBE consultar actividades:

```php
// OBLIGATORIO: Consultar actividades existentes
$actividades = $db->table('tbl_pta_cliente')
    ->where('id_cliente', $idCliente)
    ->where('categoria', 'pyp_salud')
    ->where('anio', $anio)
    ->get()
    ->getResultArray();

// VALIDACIÓN: Sin actividades, NO se puede generar
if (empty($actividades)) {
    throw new \Exception('No hay actividades de PyP Salud. Complete primero el Plan de Trabajo.');
}

// Solo entonces, generar indicadores con contexto de actividades
$contextoConActividades = $this->buildContextoConActividades($actividades);
$indicadoresGenerados = $iaService->generarIndicadores($contextoConActividades);
```

**Campos clave guardados:**
- `id_cliente` - Identificador del cliente
- `nombre_indicador` - Nombre del indicador
- `formula` - Fórmula de cálculo
- `meta` - Meta objetivo
- `periodicidad` - Frecuencia de medición
- `categoria` - 'pyp_salud' para este módulo

---

### PARTE 3: Documento (Consume Actividades + Indicadores)

**Ubicación:** `app/Libraries/DocumentosSSTTypes/ProgramaPromocionPrevencionSalud.php`
**Vista:** `app/Views/documentos_sst/programa_promocion_prevencion_salud.php`

**REQUISITO CRÍTICO:** El método `getContextoBase()` DEBE consultar AMBAS tablas:

```php
public function getContextoBase(int $idCliente, int $anio): array
{
    // ═══════════════════════════════════════════════════════════════════
    // VALIDACIÓN PARTE 1: Consultar actividades de Plan de Trabajo
    // ═══════════════════════════════════════════════════════════════════
    $actividadesPyP = $this->obtenerActividadesPyPSalud($idCliente, $anio);

    // ═══════════════════════════════════════════════════════════════════
    // VALIDACIÓN PARTE 2: Consultar indicadores
    // ═══════════════════════════════════════════════════════════════════
    $indicadoresPyP = $this->obtenerIndicadoresPyPSalud($idCliente, $anio);

    // ═══════════════════════════════════════════════════════════════════
    // AUTO-VALIDACIÓN: Si no hay datos, retornar contexto incompleto
    // ═══════════════════════════════════════════════════════════════════
    $datosCompletos = !empty($actividadesPyP) && !empty($indicadoresPyP);

    return [
        'cliente' => $cliente,
        'actividades_pyp_salud' => $actividadesPyP,
        'indicadores_pyp_salud' => $indicadoresPyP,
        'datos_completos' => $datosCompletos,
        'mensaje_validacion' => $datosCompletos
            ? 'Datos completos para generación'
            : 'ADVERTENCIA: Faltan datos. Complete Plan de Trabajo e Indicadores primero.'
    ];
}
```

---

## Métodos de Auto-Validación Obligatorios

### 1. Validación en getContextoBase()

```php
private function obtenerActividadesPyPSalud(int $idCliente, int $anio): string
{
    $db = \Config\Database::connect();
    $actividades = $db->table('tbl_pta_cliente')
        ->where('id_cliente', $idCliente)
        ->where('categoria', 'pyp_salud')
        ->where('anio', $anio)
        ->get()
        ->getResultArray();

    // AUTO-VALIDACIÓN: Retornar mensaje claro si no hay datos
    if (empty($actividades)) {
        return "⚠️ NO HAY ACTIVIDADES DE PyP SALUD EN EL PLAN DE TRABAJO PARA {$anio}. " .
               "El documento NO puede generarse sin esta información. " .
               "Complete primero el Plan de Trabajo Anual con actividades de categoría 'pyp_salud'.";
    }

    // Formatear actividades para el prompt
    $texto = "ACTIVIDADES DE PyP SALUD REGISTRADAS EN PLAN DE TRABAJO {$anio}:\n\n";
    foreach ($actividades as $idx => $act) {
        $num = $idx + 1;
        $texto .= "{$num}. {$act['actividad']}\n";
        $texto .= "   - Objetivo: {$act['objetivo']}\n";
        $texto .= "   - Responsable: {$act['responsable']}\n";
        $texto .= "   - Recursos: {$act['recursos']}\n";
        $texto .= "   - Meta: {$act['meta']}\n\n";
    }

    return $texto;
}

private function obtenerIndicadoresPyPSalud(int $idCliente, int $anio): string
{
    $db = \Config\Database::connect();
    $indicadores = $db->table('tbl_indicadores_sst')
        ->where('id_cliente', $idCliente)
        ->where('categoria', 'pyp_salud')
        ->where('activo', 1)
        ->get()
        ->getResultArray();

    // AUTO-VALIDACIÓN: Retornar mensaje claro si no hay datos
    if (empty($indicadores)) {
        return "⚠️ NO HAY INDICADORES DE PyP SALUD CONFIGURADOS. " .
               "El documento NO puede generarse sin indicadores de medición. " .
               "Complete primero el módulo de Indicadores con categoría 'pyp_salud'.";
    }

    // Formatear indicadores para el prompt
    $texto = "INDICADORES DE PyP SALUD CONFIGURADOS:\n\n";
    foreach ($indicadores as $idx => $ind) {
        $num = $idx + 1;
        $texto .= "{$num}. {$ind['nombre_indicador']}\n";
        $texto .= "   - Fórmula: {$ind['formula']}\n";
        $texto .= "   - Meta: {$ind['meta']}\n";
        $texto .= "   - Periodicidad: {$ind['periodicidad']}\n\n";
    }

    return $texto;
}
```

### 2. Validación en el Prompt de IA

Los prompts de las secciones DEBEN incluir instrucciones de validación:

```php
// En tbl_doc_secciones_config, campo 'prompt'
$prompt = <<<PROMPT
INSTRUCCIONES CRÍTICAS:
1. SOLO usa las actividades listadas en {actividades_pyp_salud}
2. SOLO usa los indicadores listados en {indicadores_pyp_salud}
3. Si ves un mensaje de advertencia (⚠️), NO generes contenido inventado
4. NO inventes actividades ni indicadores que no estén en el contexto
5. Cada actividad e indicador mencionado DEBE existir en los datos proporcionados

ACTIVIDADES DISPONIBLES:
{actividades_pyp_salud}

INDICADORES DISPONIBLES:
{indicadores_pyp_salud}

Genera la metodología basándote ÚNICAMENTE en los datos anteriores...
PROMPT;
```

### 3. Validación en el Controlador

```php
// En DocumentosSSTController o GeneradorIAController
public function generarSeccion($idDocumento, $idSeccion)
{
    $contexto = $handler->getContextoBase($idCliente, $anio);

    // VALIDACIÓN ANTES DE LLAMAR A LA IA
    if (!$contexto['datos_completos']) {
        return $this->response->setJSON([
            'success' => false,
            'error' => 'No se puede generar. ' . $contexto['mensaje_validacion'],
            'accion_requerida' => 'Complete primero las fases anteriores del módulo.'
        ]);
    }

    // Solo si hay datos completos, proceder con la generación
    $contenidoGenerado = $iaService->generar($prompt, $contexto);
}
```

### 4. Validación en la Vista

```php
<!-- En la vista del documento -->
<?php if (empty($contexto['actividades_pyp_salud']) || strpos($contexto['actividades_pyp_salud'], '⚠️') !== false): ?>
    <div class="alert alert-danger">
        <strong>Error:</strong> No hay actividades de PyP Salud en el Plan de Trabajo.
        <a href="<?= base_url('plan-trabajo/' . $idCliente) ?>">Completar Plan de Trabajo</a>
    </div>
<?php endif; ?>

<?php if (empty($contexto['indicadores_pyp_salud']) || strpos($contexto['indicadores_pyp_salud'], '⚠️') !== false): ?>
    <div class="alert alert-danger">
        <strong>Error:</strong> No hay indicadores de PyP Salud configurados.
        <a href="<?= base_url('indicadores/' . $idCliente) ?>">Configurar Indicadores</a>
    </div>
<?php endif; ?>
```

---

## Secciones que DEBEN Consumir Datos de BD

Para el Programa de Promoción y Prevención en Salud (3.1.2):

| Sección | Consume de BD | Validación Requerida |
|---------|---------------|----------------------|
| Objetivo | `tbl_pta_cliente` | Debe mencionar objetivos reales del plan |
| Metodología | `tbl_pta_cliente` | Debe describir actividades reales del plan |
| Actividades | `tbl_pta_cliente` | Lista SOLO actividades de la BD, no inventa |
| Responsabilidades | `tbl_pta_cliente` | Usa responsables reales asignados |
| Recursos | `tbl_pta_cliente` | Menciona recursos documentados |
| Evaluación y Seguimiento | `tbl_indicadores_sst` | Usa SOLO indicadores configurados |
| Indicadores | `tbl_indicadores_sst` | Lista exacta de indicadores de BD |

---

## Flujo Completo de Implementación

### Paso 1: Crear tablas con categoría

```sql
-- tbl_pta_cliente debe tener columna categoria
ALTER TABLE tbl_pta_cliente ADD COLUMN categoria VARCHAR(50) DEFAULT 'general';

-- tbl_indicadores_sst debe tener columna categoria
ALTER TABLE tbl_indicadores_sst ADD COLUMN categoria VARCHAR(50) DEFAULT 'general';
```

### Paso 2: Implementar Parte 1 (Plan de Trabajo)

1. Vista de captura de actividades por categoría
2. Generación IA con contexto del cliente
3. Guardado en `tbl_pta_cliente` con `categoria = 'pyp_salud'`

### Paso 3: Implementar Parte 2 (Indicadores)

1. Vista de configuración de indicadores
2. **OBLIGATORIO:** Consultar actividades antes de generar
3. Generación IA con contexto de actividades
4. Guardado en `tbl_indicadores_sst` con `categoria = 'pyp_salud'`

### Paso 4: Implementar Parte 3 (Documento)

1. Crear handler en `app/Libraries/DocumentosSSTTypes/`
2. Implementar `getContextoBase()` con consultas a AMBAS tablas
3. Implementar validaciones en controlador
4. Crear vista con alertas de datos faltantes
5. Configurar prompts que exijan uso de datos reales

---

## Troubleshooting

Ver archivo: `TROUBLESHOOTING_GENERACION_IA.md`

### Problema: La IA inventa actividades/indicadores

**Causa:** El contexto no se está pasando correctamente al prompt.

**Solución:**
1. Verificar que `getContextoBase()` consulta la BD
2. Verificar que el prompt incluye `{actividades_pyp_salud}` y `{indicadores_pyp_salud}`
3. Verificar que el servicio de IA reemplaza los placeholders

### Problema: El documento se genera vacío

**Causa:** No hay datos en las tablas de la Parte 1 y 2.

**Solución:**
1. Verificar datos: `SELECT * FROM tbl_pta_cliente WHERE categoria = 'pyp_salud'`
2. Verificar indicadores: `SELECT * FROM tbl_indicadores_sst WHERE categoria = 'pyp_salud'`
3. Completar las fases anteriores antes de generar el documento

### Problema: Error "Column not found"

**Causa:** La estructura de las tablas no coincide con las consultas.

**Solución:**
1. Verificar estructura: `DESCRIBE tbl_pta_cliente`
2. Ajustar las consultas en `obtenerActividadesPyPSalud()` y `obtenerIndicadoresPyPSalud()`

---

## Resumen de Seguridad

| Aspecto | Implementación |
|---------|----------------|
| Dependencia de datos | Cada parte DEBE leer de la BD antes de generar |
| Auto-validación | Métodos retornan advertencias si no hay datos |
| Bloqueo de generación | Controlador rechaza peticiones sin datos completos |
| Alertas visuales | Vista muestra errores claros con enlaces a completar |
| Prompts seguros | Instrucciones explícitas de usar SOLO datos del contexto |

**PRINCIPIO FUNDAMENTAL:** Sin datos previos en la base de datos, la IA NO puede generar contenido seguro ni confiable. La cadena de 3 partes garantiza que cada documento se basa en información real del cliente.

---

## Archivos de Referencia

- Handler ejemplo: `app/Libraries/DocumentosSSTTypes/ProgramaPromocionPrevencionSalud.php`
- Vista ejemplo: `app/Views/documentos_sst/programa_promocion_prevencion_salud.php`
- Servicio de actividades: `app/Services/ActividadesPyPSaludService.php`
- Troubleshooting: `docs/MODULO_NUMERALES_SGSST/TROUBLESHOOTING_GENERACION_IA.md`
