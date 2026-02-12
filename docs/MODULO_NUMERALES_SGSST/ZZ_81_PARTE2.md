# INSTRUCTIVO PARTE 2: Generador de Indicadores para el Plan de Trabajo

## Resumen Ejecutivo

La **Parte 2** genera **INDICADORES** que miden el cumplimiento de las **ACTIVIDADES** creadas en la Parte 1. Esta es la segunda fase del módulo de 3 partes para generación de documentos con IA.

---

## IMPORTANTE: Consistencia de UX entre Parte 1 y Parte 2

**REGLA FUNDAMENTAL**: El UX de la Parte 2 debe ser **IDÉNTICO** al de la Parte 1.

### Comparación Visual

```
┌─────────────────────────────────────┐    ┌─────────────────────────────────────┐
│  PARTE 1: Actividades               │    │  PARTE 2: Indicadores               │
├─────────────────────────────────────┤    ├─────────────────────────────────────┤
│                                     │    │                                     │
│  Estado actual (2026):              │    │  Estado actual:                     │
│  Actividades definidas:     6       │    │  Indicadores configurados:   0      │
│  Límite según estándares:   6       │    │  Límite según estándares:    6      │
│                                     │    │                                     │
│  ┌─────────────────────────────┐    │    │  ┌─────────────────────────────┐    │
│  │ ✓ Fase completa            │    │    │  │ ✓ Fase completa            │    │
│  └─────────────────────────────┘    │    │  └─────────────────────────────┘    │
│                                     │    │                                     │
│  Actividades típicas:               │    │  Indicadores recomendados:          │
│  • Reducir accidentalidad           │    │  • Índice de frecuencia accidentes  │
│  • Prevenir enfermedades            │    │  • Tasa de enfermedad laboral       │
│  • Cumplir requisitos legales       │    │  • Cumplimiento estándares mínimos  │
│  • Fortalecer cultura autocuidado   │    │  • Cobertura de capacitación SST    │
│                                     │    │                                     │
│  ┌─────────────────────────────┐    │    │  ┌─────────────────────────────┐    │
│  │  👁 Ver Preview             │    │    │  │  👁 Ver Preview             │    │
│  └─────────────────────────────┘    │    │  └─────────────────────────────┘    │
│  ┌─────────────────────────────┐    │    │  ┌─────────────────────────────┐    │
│  │  ✨ Generar Actividades     │    │    │  │  ✨ Generar Indicadores     │    │
│  └─────────────────────────────┘    │    │  └─────────────────────────────┘    │
│                                     │    │                                     │
└─────────────────────────────────────┘    └─────────────────────────────────────┘
```

### ANTI-PATRÓN: Lo que NO debe hacer la Parte 2

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        ❌ INCORRECTO (Anti-patrón)                         │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  Parte 2 con botón "Ir a Indicadores" que REDIRIGE a otra vista           │
│                                                                             │
│  ┌─────────────────────────────────────┐                                   │
│  │  Indicadores de Objetivos           │                                   │
│  │                                     │                                   │
│  │  Indicadores configurados: 0        │                                   │
│  │  Límite según estándares: 10        │                                   │
│  │                                     │                                   │
│  │  ┌─────────────────────────────┐    │                                   │
│  │  │  ↗ Ir a Indicadores        │────┼──▶ REDIRIGE A OTRA PÁGINA ❌      │
│  │  └─────────────────────────────┘    │                                   │
│  │                                     │                                   │
│  └─────────────────────────────────────┘                                   │
│                                                                             │
│  PROBLEMA: Rompe la consistencia del UX con Parte 1                        │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

### PATRÓN CORRECTO: Lo que SÍ debe hacer la Parte 2

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        ✓ CORRECTO (Igual que Parte 1)                      │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  Parte 2 con botones "Ver Preview" + "Generar Indicadores"                 │
│  que abren un MODAL en la MISMA vista                                      │
│                                                                             │
│  ┌─────────────────────────────────────┐                                   │
│  │  Indicadores de Objetivos           │                                   │
│  │                                     │                                   │
│  │  Indicadores configurados: 0        │                                   │
│  │  Límite según estándares: 6         │                                   │
│  │                                     │                                   │
│  │  ┌─────────────────────────────┐    │                                   │
│  │  │  👁 Ver Preview             │────┼──▶ ABRE MODAL EN MISMA VISTA ✓   │
│  │  └─────────────────────────────┘    │                                   │
│  │  ┌─────────────────────────────┐    │                                   │
│  │  │  ✨ Generar Indicadores     │────┼──▶ ABRE MODAL EN MISMA VISTA ✓   │
│  │  └─────────────────────────────┘    │                                   │
│  │                                     │                                   │
│  └─────────────────────────────────────┘                                   │
│                                                                             │
│  BENEFICIO: UX consistente con Parte 1, usuario no pierde contexto         │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Elementos que DEBEN ser idénticos

| Elemento | Parte 1 | Parte 2 |
|----------|---------|---------|
| Botón Preview | "Ver Preview" | "Ver Preview" |
| Botón Generar | "Generar Actividades" | "Generar Indicadores" |
| **Comportamiento** | **Modal en MISMA vista** | **Modal en MISMA vista** |
| Modal Preview | Modal XL con checkbox | Modal XL con checkbox |
| Selección | Checkbox + Seleccionar Todos | Checkbox + Seleccionar Todos |
| Edición | Inline en cada card | Inline en cada card |
| Mejorar con IA | Panel colapsable | Panel colapsable |
| Contador | "X actividades seleccionadas" | "X indicadores seleccionados" |
| Validación límite | Bloquea si excede | Bloquea si excede |
| Enviar | "Enviar al Plan de Trabajo" | "Confirmar Indicadores" |

### Regla de Oro

> **NUNCA redirigir a otra vista para el preview/generación.**
>
> Ambas partes deben usar un **Modal Bootstrap XL** que se abre en la misma página.
> Esto permite al usuario:
> - Ver el contexto del cliente mientras selecciona
> - No perder el estado de la página
> - Experiencia consistente entre Parte 1 y Parte 2

### Flujo de Usuario (IDÉNTICO en ambas partes)

```
┌──────────────────────────────────────────────────────────────────────────┐
│                    FLUJO DE USUARIO (IGUAL EN PARTE 1 Y 2)               │
├──────────────────────────────────────────────────────────────────────────┤
│                                                                          │
│  1. Usuario ve el ESTADO ACTUAL                                          │
│     - Cuántos elementos existen                                          │
│     - Cuál es el límite según estándares                                │
│     - Si la fase está completa o faltan elementos                       │
│                                                                          │
│  2. Usuario hace clic en "Ver Preview" o "Generar [Elementos]"          │
│     - Ambos botones abren el MISMO modal                                │
│     - El modal carga elementos desde el servidor                        │
│                                                                          │
│  3. Modal PREVIEW se abre con:                                          │
│     ┌────────────────────────────────────────────────────────────────┐  │
│     │ Total: X elementos sugeridos (límite: Y)                       │  │
│     │                                                                │  │
│     │ [Seleccionar Todos] [Deseleccionar]                           │  │
│     │                                                                │  │
│     │ ┌────────────────────────────────────────────────────────────┐│  │
│     │ │ ☑ Elemento 1                                    [TIPO]    ││  │
│     │ │   ┌─────────────────────────────────────────────────────┐ ││  │
│     │ │   │ Campo editable                                      │ ││  │
│     │ │   └─────────────────────────────────────────────────────┘ ││  │
│     │ │   ▼ Mejorar con IA                                        ││  │
│     │ └────────────────────────────────────────────────────────────┘│  │
│     │                                                                │  │
│     │ ┌────────────────────────────────────────────────────────────┐│  │
│     │ │ ☑ Elemento 2                                    [TIPO]    ││  │
│     │ │   ...                                                      ││  │
│     │ └────────────────────────────────────────────────────────────┘│  │
│     │                                                                │  │
│     │ [X elementos seleccionados]        [Cancelar] [Confirmar X]   │  │
│     └────────────────────────────────────────────────────────────────┘  │
│                                                                          │
│  4. Usuario SELECCIONA con checkbox cuáles enviar                       │
│                                                                          │
│  5. Usuario EDITA inline los campos que necesite                        │
│                                                                          │
│  6. Usuario puede "Mejorar con IA" cualquier elemento individual        │
│                                                                          │
│  7. Usuario hace clic en "Confirmar X Elementos"                        │
│     - Sistema valida que no exceda el límite                            │
│     - Sistema guarda SOLO los seleccionados                             │
│     - Sistema muestra toast de éxito                                    │
│     - Página se recarga                                                 │
│                                                                          │
└──────────────────────────────────────────────────────────────────────────┘
```

---

## Arquitectura de 3 Partes

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        MÓDULO DE GENERACIÓN DE DOCUMENTOS                   │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐         │
│  │     PARTE 1     │───▶│     PARTE 2     │───▶│     PARTE 3     │         │
│  │   Actividades   │    │   Indicadores   │    │   Documento     │         │
│  │  (Plan Trabajo) │    │   (Medición)    │    │   (Formal)      │         │
│  └────────┬────────┘    └────────┬────────┘    └────────┬────────┘         │
│           │                      │                      │                   │
│           ▼                      ▼                      ▼                   │
│    tbl_pta_cliente       tbl_indicadores_sst    tbl_documentos_sst         │
│    tipo_servicio='X'     categoria='X'          tipo_documento='X'         │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## Mecanismo de Vinculación Parte 1 → Parte 2

### Campo Clave: `tipo_servicio`

La Parte 2 **CONSUME** las actividades de la Parte 1 filtrando por el campo `tipo_servicio`:

```php
// En el Servicio de Parte 1: guarda actividades
protected const TIPO_SERVICIO = '[NOMBRE_DOCUMENTO]';

$db->table('tbl_pta_cliente')->insert([
    'id_cliente' => $idCliente,
    'tipo_servicio' => self::TIPO_SERVICIO,  // ← Clave de vinculación
    // ... otros campos
]);

// En el Servicio de Parte 2: consulta actividades de Parte 1
$actividades = $db->table('tbl_pta_cliente')
    ->where('id_cliente', $idCliente)
    ->where('tipo_servicio', self::TIPO_SERVICIO)  // ← Mismo valor
    ->where('YEAR(fecha_propuesta)', $anio)
    ->get()
    ->getResultArray();
```

### Campo para Indicadores: `categoria`

Los indicadores generados en Parte 2 se guardan con una `categoria` que los vincula al tipo de documento:

```php
protected const CATEGORIA = '[nombre_documento]';

$this->indicadorModel->insert([
    'id_cliente' => $idCliente,
    'categoria' => self::CATEGORIA,  // ← Vincula indicadores al documento
    // ... otros campos
]);
```

---

## Validación Obligatoria: Parte 1 Completa

**REGLA CRÍTICA**: La Parte 2 **NO PERMITE ACCESO** si no existen actividades de la Parte 1.

```php
/**
 * VALIDACIÓN OBLIGATORIA: Verificar que existan actividades de Parte 1
 */
public function verificarActividadesPrevias(int $idCliente, int $anio): array
{
    $actividades = $this->actividadesService->getActividadesCliente($idCliente, $anio);

    return [
        'tiene_actividades' => count($actividades) > 0,
        'total_actividades' => count($actividades),
        'actividades' => $actividades,
        'mensaje' => count($actividades) > 0
            ? 'Actividades encontradas para asociar indicadores'
            : 'Debe completar la Parte 1 (Actividades) antes de generar indicadores'
    ];
}
```

### En la Vista: Bloqueo Visual

```php
<?php if (!$verificacionActividades['tiene_actividades']): ?>
<div class="alert alert-danger mb-4">
    <i class="bi bi-exclamation-octagon me-2"></i>
    <strong>Parte 1 incompleta:</strong> <?= esc($verificacionActividades['mensaje']) ?>
    <a href="<?= base_url('generador-ia/' . $cliente['id_cliente'] . '/[ruta-parte-1]') ?>"
       class="btn btn-sm btn-danger ms-3">
        <i class="bi bi-arrow-left me-1"></i>Ir a Actividades
    </a>
</div>
<?php else: ?>
    <!-- Contenido de Parte 2 -->
<?php endif; ?>
```

---

## Límites de Indicadores según Estándares

| Estándares Aplicables | Límite de Indicadores |
|-----------------------|----------------------|
| 7 (Básico)            | **2** indicadores    |
| 21 (Intermedio)       | **4** indicadores    |
| 60 (Avanzado)         | **6** indicadores    |

```php
public const LIMITES_INDICADORES = [
    7 => 2,   // Básico: 2 indicadores
    21 => 4,  // Intermedio: 4 indicadores
    60 => 6   // Avanzado: 6 indicadores
];

public function getLimiteIndicadores(int $estandares): int
{
    if ($estandares <= 7) return self::LIMITES_INDICADORES[7];
    if ($estandares <= 21) return self::LIMITES_INDICADORES[21];
    return self::LIMITES_INDICADORES[60];
}
```

---

## Estructura de un Indicador

Cada indicador tiene los siguientes campos:

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `nombre` | string | Nombre del indicador |
| `tipo` | enum | `estructura`, `proceso`, `resultado` |
| `formula` | string | Fórmula de cálculo |
| `meta` | number | Valor objetivo a alcanzar |
| `unidad` | string | Unidad de medida (%, IF, IS, etc.) |
| `periodicidad` | enum | `mensual`, `trimestral`, `semestral`, `anual` |
| `phva` | enum | Ciclo PHVA (generalmente `verificar`) |
| `numeral` | string | Numeral de la Resolución 0312 |
| `descripcion` | string | Descripción del indicador |
| `menor_es_mejor` | bool | Para indicadores donde el objetivo es reducir |
| `definicion` | string | Definición formal del indicador para Ficha Técnica |
| `interpretacion` | string | Cómo interpretar los resultados y umbrales |
| `origen_datos` | string | Fuentes de datos para alimentar el indicador |
| `cargo_responsable` | string | Cargo encargado de medir el indicador |
| `cargos_conocer_resultado` | string | Cargos que deben conocer los resultados |

### Tipos de Indicadores

```
┌─────────────────────────────────────────────────────────────────┐
│                    TIPOS DE INDICADORES                         │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌───────────────┐  ┌───────────────┐  ┌───────────────┐       │
│  │  ESTRUCTURA   │  │   PROCESO     │  │  RESULTADO    │       │
│  │   (gris)      │  │   (azul)      │  │   (rojo)      │       │
│  ├───────────────┤  ├───────────────┤  ├───────────────┤       │
│  │ Miden         │  │ Miden         │  │ Miden         │       │
│  │ RECURSOS      │  │ EJECUCIÓN     │  │ IMPACTO       │       │
│  │ disponibles   │  │ de acciones   │  │ final         │       │
│  └───────────────┘  └───────────────┘  └───────────────┘       │
│                                                                 │
│  Ej: Presupuesto   Ej: Cumplimiento   Ej: Índice de           │
│      asignado          del PTA            Frecuencia AT        │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## Servicio de Parte 2

### Archivo a Crear
```
app/Services/[NombreDocumento]IndicadoresService.php
```

### Estructura del Servicio

```php
<?php

namespace App\Services;

use App\Models\IndicadorSSTModel;

/**
 * Servicio para generar Indicadores de [Nombre del Documento]
 * Estándar X.X.X - Resolución 0312/2019
 *
 * PARTE 2 del módulo de 3 partes:
 * - CONSUME las actividades de Parte 1 (tbl_pta_cliente tipo_servicio='[TIPO]')
 * - Genera indicadores para medir cumplimiento de actividades
 * - Se guardan en tbl_indicadores_sst con categoria = '[categoria]'
 */
class [NombreDocumento]IndicadoresService
{
    protected IndicadorSSTModel $indicadorModel;
    protected [NombreDocumento]ActividadesService $actividadesService;

    protected const CATEGORIA = '[nombre_documento]';

    /**
     * Límites fijos de indicadores según estándares
     */
    public const LIMITES_INDICADORES = [
        7 => 2,   // Básico: 2 indicadores
        21 => 4,  // Intermedio: 4 indicadores
        60 => 6   // Avanzado: 6 indicadores
    ];

    /**
     * Indicadores base para este tipo de documento
     * Definir indicadores específicos según el documento
     */
    public const INDICADORES_BASE = [
        [
            'nombre' => '[Nombre del Indicador 1]',
            'tipo' => 'resultado',  // estructura | proceso | resultado
            'formula' => '[Fórmula de cálculo]',
            'meta' => 0,
            'unidad' => '%',
            'periodicidad' => 'mensual',
            'phva' => 'verificar',
            'numeral' => 'X.X.X',
            'descripcion' => '[Descripción del indicador]',
            'menor_es_mejor' => true,  // Solo si aplica
            'actividad_relacionada' => '[Texto para buscar actividad asociada]',
            // ── Campos Ficha Técnica (OBLIGATORIOS) ──
            'definicion' => '[Definición formal del indicador]',
            'interpretacion' => '[Cómo interpretar resultados y umbrales]',
            'origen_datos' => '[Fuentes: registros, formatos, bases de datos]',
            'cargo_responsable' => 'Responsable del SG-SST',
            'cargos_conocer_resultado' => 'Gerencia, Responsable SG-SST, COPASST/Vigía'
        ],
        // ... más indicadores
    ];

    public function __construct()
    {
        $this->indicadorModel = new IndicadorSSTModel();
        $this->actividadesService = new [NombreDocumento]ActividadesService();
    }

    // ═══════════════════════════════════════════════════════════════════════
    // MÉTODO 1: Obtener límite según estándares
    // ═══════════════════════════════════════════════════════════════════════
    public function getLimiteIndicadores(int $estandares): int
    {
        if ($estandares <= 7) return self::LIMITES_INDICADORES[7];
        if ($estandares <= 21) return self::LIMITES_INDICADORES[21];
        return self::LIMITES_INDICADORES[60];
    }

    // ═══════════════════════════════════════════════════════════════════════
    // MÉTODO 2: Obtener resumen de indicadores
    // ═══════════════════════════════════════════════════════════════════════
    public function getResumenIndicadores(int $idCliente): array
    {
        $indicadores = $this->indicadorModel
            ->where('id_cliente', $idCliente)
            ->where('activo', 1)
            ->where('categoria', self::CATEGORIA)
            ->findAll();

        $total = count($indicadores);
        $medidos = 0;
        $cumplen = 0;

        foreach ($indicadores as $ind) {
            if ($ind['cumple_meta'] !== null) {
                $medidos++;
                if ($ind['cumple_meta'] == 1) {
                    $cumplen++;
                }
            }
        }

        return [
            'existentes' => $total,
            'sugeridos' => count(self::INDICADORES_BASE),
            'medidos' => $medidos,
            'cumplen' => $cumplen,
            'completo' => $total >= 2,  // Mínimo 2 indicadores
            'minimo' => 2
        ];
    }

    // ═══════════════════════════════════════════════════════════════════════
    // MÉTODO 3: VALIDACIÓN OBLIGATORIA - Verificar actividades de Parte 1
    // ═══════════════════════════════════════════════════════════════════════
    public function verificarActividadesPrevias(int $idCliente, int $anio): array
    {
        $actividades = $this->actividadesService->getActividadesCliente($idCliente, $anio);

        return [
            'tiene_actividades' => count($actividades) > 0,
            'total_actividades' => count($actividades),
            'actividades' => $actividades,
            'mensaje' => count($actividades) > 0
                ? 'Actividades encontradas para asociar indicadores'
                : 'Debe completar la Parte 1 (Actividades) antes de generar indicadores'
        ];
    }

    // ═══════════════════════════════════════════════════════════════════════
    // MÉTODO 4: Preview de indicadores
    // CONSUME las actividades de Parte 1 para sugerir indicadores relevantes
    // ═══════════════════════════════════════════════════════════════════════
    public function previewIndicadores(int $idCliente, int $anio, ?array $contexto = null): array
    {
        // VALIDACIÓN: Verificar actividades previas
        $verificacion = $this->verificarActividadesPrevias($idCliente, $anio);
        if (!$verificacion['tiene_actividades']) {
            return [
                'indicadores' => [],
                'total' => 0,
                'error' => true,
                'mensaje' => $verificacion['mensaje']
            ];
        }

        $estandares = $contexto['estandares_aplicables'] ?? 60;
        $limite = $this->getLimiteIndicadores($estandares);

        // Obtener actividades existentes para mapear indicadores
        $actividadesCliente = $verificacion['actividades'];
        $actividadesTexto = array_map(function($act) {
            return $act['actividad_plandetrabajo'];
        }, $actividadesCliente);

        // Tomar indicadores base hasta el límite
        $indicadoresBase = array_slice(self::INDICADORES_BASE, 0, $limite);

        $indicadores = [];
        foreach ($indicadoresBase as $idx => $ind) {
            // Buscar si hay una actividad relacionada
            $actividadAsociada = '';
            if (!empty($ind['actividad_relacionada'])) {
                foreach ($actividadesTexto as $actTexto) {
                    if (stripos($actTexto, substr($ind['actividad_relacionada'], 0, 20)) !== false) {
                        $actividadAsociada = $actTexto;
                        break;
                    }
                }
            }

            $indicadores[] = [
                'indice' => $idx,
                'nombre' => $ind['nombre'],
                'tipo' => $ind['tipo'],
                'formula' => $ind['formula'],
                'meta' => $ind['meta'],
                'unidad' => $ind['unidad'],
                'periodicidad' => $ind['periodicidad'],
                'phva' => $ind['phva'],
                'numeral' => $ind['numeral'],
                'descripcion' => $ind['descripcion'] ?? '',
                'menor_es_mejor' => $ind['menor_es_mejor'] ?? false,
                'actividad_relacionada' => $ind['actividad_relacionada'] ?? '',
                'actividad_asociada' => $actividadAsociada,
                'origen' => 'base',
                'seleccionado' => true
            ];
        }

        // Marcar los que ya existen
        $existentes = $this->getIndicadoresCliente($idCliente);
        $nombresExistentes = array_map('strtolower', array_column($existentes, 'nombre_indicador'));

        foreach ($indicadores as &$ind) {
            $nombreLower = strtolower($ind['nombre']);
            foreach ($nombresExistentes as $existente) {
                if (similar_text($nombreLower, $existente) > strlen($nombreLower) * 0.6) {
                    $ind['ya_existe'] = true;
                    $ind['seleccionado'] = false;
                    break;
                }
            }
        }

        return [
            'indicadores' => $indicadores,
            'total' => count($indicadores),
            'limite' => $limite,
            'estandares' => $estandares,
            'actividades_base' => count($actividadesCliente),
            'contexto_aplicado' => $contexto ? true : false
        ];
    }

    // ═══════════════════════════════════════════════════════════════════════
    // MÉTODO 5: Generar indicadores en la BD
    // ═══════════════════════════════════════════════════════════════════════
    public function generarIndicadores(int $idCliente, int $anio, ?array $indicadoresSeleccionados = null): array
    {
        // VALIDACIÓN: Verificar actividades previas
        $verificacion = $this->verificarActividadesPrevias($idCliente, $anio);
        if (!$verificacion['tiene_actividades']) {
            return [
                'creados' => 0,
                'existentes' => 0,
                'errores' => [$verificacion['mensaje']],
                'total' => 0
            ];
        }

        $creados = 0;
        $existentes = 0;
        $errores = [];

        $indicadores = $indicadoresSeleccionados ?? self::INDICADORES_BASE;

        foreach ($indicadores as $ind) {
            // Verificar si ya existe un indicador similar
            $existe = $this->indicadorModel
                ->where('id_cliente', $idCliente)
                ->where('activo', 1)
                ->like('nombre_indicador', substr($ind['nombre'], 0, 30), 'both')
                ->countAllResults();

            if ($existe > 0) {
                $existentes++;
                continue;
            }

            try {
                $this->indicadorModel->insert([
                    'id_cliente' => $idCliente,
                    'nombre_indicador' => $ind['nombre'],
                    'tipo_indicador' => $ind['tipo'],
                    'categoria' => self::CATEGORIA,
                    'formula' => $ind['formula'],
                    'meta' => $ind['meta'],
                    'unidad_medida' => $ind['unidad'],
                    'periodicidad' => $ind['periodicidad'],
                    'phva' => $ind['phva'],
                    'numeral_resolucion' => $ind['numeral'] ?? 'X.X.X',
                    // ── Campos Ficha Técnica ──
                    'definicion' => $ind['definicion'] ?? null,
                    'interpretacion' => $ind['interpretacion'] ?? null,
                    'origen_datos' => $ind['origen_datos'] ?? null,
                    'cargo_responsable' => $ind['cargo_responsable'] ?? null,
                    'cargos_conocer_resultado' => $ind['cargos_conocer_resultado'] ?? null,
                    'activo' => 1
                ]);
                $creados++;
            } catch (\Exception $e) {
                $errores[] = "Error en '{$ind['nombre']}': " . $e->getMessage();
            }
        }

        return [
            'creados' => $creados,
            'existentes' => $existentes,
            'errores' => $errores,
            'total' => count($indicadores)
        ];
    }

    // ═══════════════════════════════════════════════════════════════════════
    // MÉTODO 6: Obtener indicadores del cliente
    // ═══════════════════════════════════════════════════════════════════════
    public function getIndicadoresCliente(int $idCliente): array
    {
        return $this->indicadorModel
            ->where('id_cliente', $idCliente)
            ->where('activo', 1)
            ->where('categoria', self::CATEGORIA)
            ->orderBy('tipo_indicador', 'ASC')
            ->orderBy('nombre_indicador', 'ASC')
            ->findAll();
    }

    // ═══════════════════════════════════════════════════════════════════════
    // MÉTODO 7: Obtener indicadores formateados para Parte 3 (Documento)
    // ═══════════════════════════════════════════════════════════════════════
    public function getIndicadoresParaContexto(int $idCliente): string
    {
        $indicadores = $this->getIndicadoresCliente($idCliente);

        if (empty($indicadores)) {
            return "No hay indicadores configurados para este documento.";
        }

        $texto = "Total: " . count($indicadores) . " indicadores\n\n";

        $porTipo = ['resultado' => [], 'proceso' => [], 'estructura' => []];
        foreach ($indicadores as $ind) {
            $tipo = $ind['tipo_indicador'] ?? 'proceso';
            $porTipo[$tipo][] = $ind;
        }

        foreach ($porTipo as $tipo => $inds) {
            if (!empty($inds)) {
                $texto .= strtoupper("INDICADORES DE " . $tipo) . ":\n";
                foreach ($inds as $i => $ind) {
                    $texto .= ($i + 1) . ". {$ind['nombre_indicador']}\n";
                    $texto .= "   - Formula: {$ind['formula']}\n";
                    $texto .= "   - Meta: {$ind['meta']} {$ind['unidad_medida']}\n";
                    $texto .= "   - Periodicidad: {$ind['periodicidad']}\n\n";
                }
            }
        }

        return $texto;
    }

    // ═══════════════════════════════════════════════════════════════════════
    // MÉTODO 8: Regenerar indicador con IA
    // ═══════════════════════════════════════════════════════════════════════
    public function regenerarIndicadorConIA(array $indicadorActual, string $instrucciones, ?array $contexto = null): array
    {
        $apiKey = env('OPENAI_API_KEY', '');
        if (empty($apiKey)) {
            return ['success' => false, 'error' => 'API Key no configurada'];
        }

        $systemPrompt = "Eres un experto en indicadores de Seguridad y Salud en el Trabajo (SST) de Colombia.
Tu tarea es mejorar un indicador según las instrucciones del consultor.

REGLAS:
1. Mantén la estructura del indicador
2. Las fórmulas deben ser calculables y claras
3. Las metas deben ser MEDIBLES y ALCANZABLES
4. Responde SOLO en formato JSON válido

FORMATO DE RESPUESTA (JSON):
{
  \"nombre\": \"Nombre del indicador\",
  \"tipo\": \"estructura|proceso|resultado\",
  \"formula\": \"Fórmula de cálculo\",
  \"meta\": 90,
  \"unidad\": \"%\",
  \"periodicidad\": \"mensual|trimestral|semestral|anual\",
  \"descripcion\": \"Descripción del indicador\"
}";

        $userPrompt = "INDICADOR ACTUAL:\n";
        $userPrompt .= json_encode($indicadorActual, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $userPrompt .= "\n\nINSTRUCCIONES DEL CONSULTOR:\n\"{$instrucciones}\"\n\n";
        $userPrompt .= "Mejora el indicador según las instrucciones.";

        $response = $this->llamarOpenAI($systemPrompt, $userPrompt, $apiKey);

        if (!$response['success']) {
            return ['success' => false, 'error' => $response['error'] ?? 'Error desconocido'];
        }

        return $this->procesarRespuestaIA($response['contenido'], $indicadorActual);
    }

    // Métodos auxiliares para OpenAI (implementación idéntica a Parte 1)
    protected function llamarOpenAI(string $systemPrompt, string $userPrompt, string $apiKey): array
    {
        $data = [
            'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt]
            ],
            'temperature' => 0.3,
            'max_tokens' => 2000
        ];

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ],
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => "Error de conexión: {$error}"];
        }

        $result = json_decode($response, true);

        if ($httpCode !== 200) {
            return ['success' => false, 'error' => $result['error']['message'] ?? 'Error HTTP ' . $httpCode];
        }

        if (isset($result['choices'][0]['message']['content'])) {
            return [
                'success' => true,
                'contenido' => trim($result['choices'][0]['message']['content'])
            ];
        }

        return ['success' => false, 'error' => 'Respuesta inesperada'];
    }

    protected function procesarRespuestaIA(string $contenidoIA, array $indicadorBase): array
    {
        $contenidoIA = preg_replace('/```json\s*/', '', $contenidoIA);
        $contenidoIA = preg_replace('/```\s*/', '', $contenidoIA);

        $respuesta = json_decode($contenidoIA, true);
        if (!$respuesta) {
            return ['success' => false, 'error' => 'Respuesta JSON inválida'];
        }

        return [
            'success' => true,
            'data' => array_merge($indicadorBase, $respuesta)
        ];
    }
}
```

---

## Controlador: Métodos de Parte 2

### En GeneradorIAController.php

```php
// ═══════════════════════════════════════════════════════════════════════════
// MÉTODO 1: Vista principal de indicadores
// ═══════════════════════════════════════════════════════════════════════════
public function indicadores[NombreDocumento](int $idCliente): string
{
    $anio = date('Y');
    $cliente = $this->clienteModel->find($idCliente);

    if (!$cliente) {
        return redirect()->back()->with('error', 'Cliente no encontrado');
    }

    $service = new \App\Services\[NombreDocumento]IndicadoresService();
    $contexto = $this->obtenerContextoCliente($idCliente);

    return view('generador_ia/indicadores_[nombre_documento]', [
        'cliente' => $cliente,
        'anio' => $anio,
        'contexto' => $contexto,
        'verificacionActividades' => $service->verificarActividadesPrevias($idCliente, $anio),
        'resumenIndicadores' => $service->getResumenIndicadores($idCliente),
        'limiteIndicadores' => $service->getLimiteIndicadores($contexto['estandares_aplicables'] ?? 60),
        'indicadoresExistentes' => $service->getIndicadoresCliente($idCliente)
    ]);
}

// ═══════════════════════════════════════════════════════════════════════════
// MÉTODO 2: Preview de indicadores (AJAX)
// ═══════════════════════════════════════════════════════════════════════════
public function previewIndicadores[NombreDocumento](int $idCliente): \CodeIgniter\HTTP\ResponseInterface
{
    $anio = $this->request->getGet('anio') ?? date('Y');
    $contexto = $this->obtenerContextoCliente($idCliente);

    $service = new \App\Services\[NombreDocumento]IndicadoresService();
    $resultado = $service->previewIndicadores($idCliente, (int)$anio, $contexto);

    return $this->response->setJSON([
        'success' => !($resultado['error'] ?? false),
        'data' => $resultado,
        'message' => $resultado['mensaje'] ?? ''
    ]);
}

// ═══════════════════════════════════════════════════════════════════════════
// MÉTODO 3: Generar indicadores seleccionados (AJAX POST)
// ═══════════════════════════════════════════════════════════════════════════
public function generarIndicadores[NombreDocumento](int $idCliente): \CodeIgniter\HTTP\ResponseInterface
{
    $json = $this->request->getJSON(true);
    $anio = $json['anio'] ?? date('Y');
    $indicadoresSeleccionados = $json['indicadores'] ?? [];

    if (empty($indicadoresSeleccionados)) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'No se seleccionaron indicadores'
        ]);
    }

    $service = new \App\Services\[NombreDocumento]IndicadoresService();
    $resultado = $service->generarIndicadores($idCliente, (int)$anio, $indicadoresSeleccionados);

    return $this->response->setJSON([
        'success' => empty($resultado['errores']),
        'data' => $resultado,
        'message' => empty($resultado['errores'])
            ? "Se crearon {$resultado['creados']} indicadores"
            : implode(', ', $resultado['errores'])
    ]);
}

// ═══════════════════════════════════════════════════════════════════════════
// MÉTODO 4: Regenerar indicador con IA (AJAX POST)
// ═══════════════════════════════════════════════════════════════════════════
public function regenerarIndicador(int $idCliente): \CodeIgniter\HTTP\ResponseInterface
{
    $json = $this->request->getJSON(true);
    $indicadorActual = $json['indicador_actual'] ?? [];
    $instrucciones = $json['instrucciones'] ?? '';
    $tipoIndicador = $json['tipo_indicador'] ?? '';

    if (empty($instrucciones)) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Debe proporcionar instrucciones'
        ]);
    }

    $contexto = $this->obtenerContextoCliente($idCliente);

    // Determinar qué servicio usar según el tipo
    $service = $this->obtenerServicioIndicadores($tipoIndicador);
    $resultado = $service->regenerarIndicadorConIA($indicadorActual, $instrucciones, $contexto);

    return $this->response->setJSON([
        'success' => $resultado['success'] ?? false,
        'data' => $resultado['data'] ?? null,
        'message' => $resultado['error'] ?? ''
    ]);
}
```

---

## Rutas de Parte 2

### En app/Config/Routes.php

```php
// ═══════════════════════════════════════════════════════════════════════════
// RUTAS PARTE 2: Generador de Indicadores
// ═══════════════════════════════════════════════════════════════════════════

// Vista principal
$routes->get(
    'generador-ia/(:num)/indicadores-[nombre-documento]',
    'GeneradorIAController::indicadores[NombreDocumento]/$1'
);

// Preview de indicadores (AJAX GET)
$routes->get(
    'generador-ia/(:num)/preview-indicadores-[nombre-documento]',
    'GeneradorIAController::previewIndicadores[NombreDocumento]/$1'
);

// Generar indicadores seleccionados (AJAX POST)
$routes->post(
    'generador-ia/(:num)/generar-indicadores-[nombre-documento]',
    'GeneradorIAController::generarIndicadores[NombreDocumento]/$1'
);

// Regenerar indicador individual con IA (AJAX POST)
$routes->post(
    'generador-ia/(:num)/regenerar-indicador',
    'GeneradorIAController::regenerarIndicador/$1'
);
```

---

## Tabla de Base de Datos: tbl_indicadores_sst

### Estructura

```sql
CREATE TABLE tbl_indicadores_sst (
    id_indicador INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    nombre_indicador VARCHAR(255) NOT NULL,
    tipo_indicador ENUM('estructura', 'proceso', 'resultado') DEFAULT 'proceso',
    categoria VARCHAR(100) NOT NULL,  -- ← Vincula al tipo de documento
    formula TEXT,
    meta DECIMAL(10,2),
    unidad_medida VARCHAR(50),
    periodicidad ENUM('mensual', 'trimestral', 'semestral', 'anual') DEFAULT 'mensual',
    phva ENUM('planear', 'hacer', 'verificar', 'actuar') DEFAULT 'verificar',
    numeral_resolucion VARCHAR(20),
    valor_actual DECIMAL(10,2) NULL,
    cumple_meta TINYINT(1) NULL,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_cliente (id_cliente),
    INDEX idx_categoria (categoria),
    INDEX idx_tipo (tipo_indicador),
    FOREIGN KEY (id_cliente) REFERENCES tbl_clientes(id_cliente) ON DELETE CASCADE
);
```

---

## IMPORTANTE: Registro de Categorías en el Modelo

### El Problema

El campo `categoria` que se usa en el servicio **DEBE estar registrado** en `IndicadorSSTModel::CATEGORIAS`. Si no está registrado, los indicadores se agruparán en "Otros" cuando se muestren en las vistas.

### Categorías Registradas (IndicadorSSTModel.php)

```php
// Archivo: app/Models/IndicadorSSTModel.php (líneas 60-121)

public const CATEGORIAS = [
    'capacitacion' => [
        'nombre' => 'Capacitación',
        'icono' => 'bi-mortarboard',
        'color' => 'primary',
        'descripcion' => 'Indicadores del programa de capacitación y formación'
    ],
    'accidentalidad' => [...],
    'ausentismo' => [...],
    'pta' => [...],
    'inspecciones' => [...],
    'emergencias' => [...],
    'vigilancia' => [...],
    'riesgos' => [...],
    'pyp_salud' => [...],
    'objetivos_sgsst' => [
        'nombre' => 'Objetivos del SG-SST',
        'icono' => 'bi-bullseye',
        'color' => 'success',
        'descripcion' => 'Indicadores de medición de objetivos (Estándar 2.2.1)'
    ],
    'induccion' => [
        'nombre' => 'Inducción y Reinducción',
        'icono' => 'bi-person-badge',
        'color' => 'info',
        'descripcion' => 'Indicadores del programa de inducción'
    ],
    'otro' => [...]  // ← Catch-all, siempre al final
];
```

### Al Crear un Nuevo Módulo de Indicadores

**OBLIGATORIO**: Antes de crear el servicio de indicadores, verificar si la categoría existe:

1. Abrir `app/Models/IndicadorSSTModel.php`
2. Buscar el array `CATEGORIAS`
3. Si la categoría NO existe, **agregarla ANTES de 'otro'**:

```php
'[mi_categoria]' => [
    'nombre' => '[Nombre Visible]',
    'icono' => 'bi-[icono]',
    'color' => '[bootstrap-color]',
    'descripcion' => '[Descripción del tipo de indicadores]'
],
```

### Consecuencia de NO Registrar

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                    ❌ SI NO REGISTRAS LA CATEGORÍA                          │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  Servicio guarda:                                                           │
│  categoria = 'mi_nueva_categoria'                                           │
│                                                                             │
│  Modelo busca en CATEGORIAS:                                                │
│  if (!isset(self::CATEGORIAS['mi_nueva_categoria'])) {                      │
│      $cat = 'otro';  // ← SE VA A "OTROS"                                   │
│  }                                                                          │
│                                                                             │
│  En la vista de indicadores:                                                │
│  ┌─────────────────────────────────────┐                                   │
│  │ Otros (5)                           │  ← Todos tus indicadores aquí     │
│  │ • Indicador 1                       │                                   │
│  │ • Indicador 2                       │                                   │
│  │ • ...                               │                                   │
│  └─────────────────────────────────────┘                                   │
│                                                                             │
│  PROBLEMA: Pierden identidad y no se filtran correctamente                 │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Colores Bootstrap Disponibles

| Color | Uso Sugerido |
|-------|--------------|
| `primary` | Capacitación, formación |
| `success` | Objetivos, cumplimiento |
| `danger` | Accidentalidad, salud |
| `warning` | Ausentismo, alertas |
| `info` | Inducción, información |
| `secondary` | Riesgos, gestión |
| `dark` | Otros |

---

## Checklist de Implementación

### Archivos a Crear

- [ ] `app/Services/[NombreDocumento]IndicadoresService.php`
- [ ] `app/Views/generador_ia/indicadores_[nombre_documento].php`

### Modificaciones en Archivos Existentes

- [ ] `app/Models/IndicadorSSTModel.php` - **Agregar categoría en CATEGORIAS**
- [ ] `app/Controllers/GeneradorIAController.php` - Agregar 4 métodos
- [ ] `app/Config/Routes.php` - Agregar 4 rutas

### Constantes a Definir

```php
// En el Servicio de Indicadores
protected const CATEGORIA = '[nombre_documento]';

public const LIMITES_INDICADORES = [
    7 => 2,   // Básico: 2 indicadores
    21 => 4,  // Intermedio: 4 indicadores
    60 => 6   // Avanzado: 6 indicadores
];

public const INDICADORES_BASE = [
    // Definir indicadores específicos para el documento
];
```

---

## Resumen del Flujo Completo

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                           FLUJO PARTE 2                                     │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  1. USUARIO ACCEDE A /generador-ia/{id}/indicadores-[documento]            │
│                           │                                                 │
│                           ▼                                                 │
│  2. SISTEMA VALIDA: ¿Existen actividades de Parte 1?                       │
│                           │                                                 │
│              ┌────────────┴────────────┐                                   │
│              │                         │                                   │
│           NO ▼                      SÍ ▼                                   │
│     ┌─────────────────┐      ┌─────────────────┐                           │
│     │ BLOQUEA ACCESO  │      │ MUESTRA VISTA   │                           │
│     │ Enlace a Parte 1│      │ CON INDICADORES │                           │
│     └─────────────────┘      └────────┬────────┘                           │
│                                       │                                     │
│                                       ▼                                     │
│  3. USUARIO HACE CLIC EN "Generar Indicadores"                             │
│                           │                                                 │
│                           ▼                                                 │
│  4. MODAL PREVIEW: Muestra indicadores sugeridos                           │
│     - Checkbox para seleccionar                                            │
│     - Edición inline de campos                                             │
│     - Panel "Mejorar con IA" colapsable                                    │
│                           │                                                 │
│                           ▼                                                 │
│  5. USUARIO SELECCIONA, EDITA, CONFIRMA                                    │
│                           │                                                 │
│                           ▼                                                 │
│  6. SISTEMA GUARDA EN tbl_indicadores_sst                                  │
│     categoria = '[nombre_documento]'                                       │
│                           │                                                 │
│                           ▼                                                 │
│  7. ENLACE DISPONIBLE A PARTE 3 (Documento)                                │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## Notas Importantes

1. **Validación Obligatoria**: Siempre verificar que existan actividades de Parte 1 antes de permitir acceso a Parte 2.

2. **Campo `categoria`**: Este campo en `tbl_indicadores_sst` vincula los indicadores al tipo de documento específico.

3. **Campo `tipo_servicio`**: Este campo en `tbl_pta_cliente` vincula las actividades de Parte 1.

4. **Límites**: Los límites de indicadores (2/4/6) son diferentes a los de actividades (3/5/8).

5. **UX Consistente**: El patrón de selección con checkbox, edición inline y panel IA colapsable es idéntico a Parte 1.

6. **Método para Parte 3**: `getIndicadoresParaContexto()` formatea los indicadores para ser consumidos por la generación del documento formal.

7. **Campos Ficha Técnica (OBLIGATORIOS)**: Todo indicador en `INDICADORES_BASE` debe incluir los 5 campos de ficha técnica: `definicion`, `interpretacion`, `origen_datos`, `cargo_responsable`, `cargos_conocer_resultado`. Estos campos alimentan la **Ficha Técnica** formal (reporte de auditoría Res. 0312/2019). Valores específicos por indicador, NO genéricos. El INSERT en `generarIndicadores()` debe incluirlos con fallback `?? null`.
