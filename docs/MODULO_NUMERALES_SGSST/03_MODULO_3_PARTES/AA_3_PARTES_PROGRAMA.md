# Arquitectura de Módulo en 3 Partes con Generación IA

## Concepto Fundamental

Un módulo de 3 partes implementa una **cadena de dependencias obligatorias** donde cada fase DEBE consumir datos de la fase anterior desde la base de datos.

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        CADENA DE DEPENDENCIAS                               │
│                                                                             │
│   PARTE 1              PARTE 2              PARTE 3                        │
│   ────────             ────────             ────────                        │
│   Items/Datos    →     Indicadores    →     Documento                      │
│                        (opcional)                                           │
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
│   tipo_servicio=X      categoria=X                                         │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## Discriminador: tipo_servicio

La columna `tipo_servicio` en `tbl_pta_cliente` actúa como **discriminador** para identificar a qué módulo pertenecen los registros.

```sql
-- Insertar datos de un módulo específico
INSERT INTO tbl_pta_cliente (id_cliente, tipo_servicio, actividad_plandetrabajo, ...)
VALUES (?, '{NOMBRE_MODULO}', ?, ...);

-- Consultar datos de un módulo específico
SELECT * FROM tbl_pta_cliente
WHERE id_cliente = ? AND tipo_servicio = '{NOMBRE_MODULO}';
```

---

## PARTE 1: Vista Generador IA

### Ubicación
`app/Views/generador_ia/{nombre_modulo}.php`

### Estructura

```php
<!-- 1. Contexto del Cliente (datos para la IA) -->
<div class="card">
    <div class="card-header">Contexto para la IA</div>
    <div class="card-body">
        <!-- Datos de la empresa -->
        <table class="table">
            <tr><td>Actividad económica:</td><td><?= $contexto['actividad_economica'] ?></td></tr>
            <tr><td>Nivel de riesgo:</td><td><?= $contexto['nivel_riesgo_arl'] ?></td></tr>
            <tr><td>Trabajadores:</td><td><?= $contexto['total_trabajadores'] ?></td></tr>
        </table>

        <!-- Instrucciones adicionales del consultor -->
        <textarea id="instruccionesIA" placeholder="Instrucciones adicionales..."></textarea>
    </div>
</div>

<!-- 2. Botones de acción -->
<button onclick="previewItems()">Ver Preview</button>
<button onclick="generarItems()">Generar Items</button>

<!-- 3. Modal de Preview/Selección -->
<div class="modal" id="modalPreview">
    <!-- Tabla con checkboxes para seleccionar -->
    <!-- Dropdowns para ajustar valores -->
    <!-- Botón: Guardar Seleccionados -->
</div>
```

### Flujo JavaScript

```javascript
// 1. Preview: IA genera sugerencias
function previewItems() {
    fetch(`/generador-ia/${idCliente}/preview-{modulo}?instrucciones=${instrucciones}`)
        .then(r => r.json())
        .then(data => renderPreviewTable(data.items));
}

// 2. Generar: Guardar seleccionados en BD
function generarItemsSeleccionados() {
    const seleccionados = getItemsSeleccionados();
    fetch(`/generador-ia/${idCliente}/generar-{modulo}`, {
        method: 'POST',
        body: JSON.stringify({ items: seleccionados })
    });
}
```

---

## PARTE 2: Indicadores (Opcional)

Si el módulo requiere indicadores, estos se guardan en `tbl_indicadores_sst` con `categoria = '{NOMBRE_MODULO}'`.

### Validación Obligatoria

```php
// ANTES de generar indicadores, verificar que existan items de Parte 1
$items = $db->table('tbl_pta_cliente')
    ->where('id_cliente', $idCliente)
    ->where('tipo_servicio', '{NOMBRE_MODULO}')
    ->countAllResults();

if ($items === 0) {
    throw new \Exception('Complete primero la Parte 1 del módulo.');
}
```

---

## PARTE 3: Documento (Consume Datos de BD)

### Ubicación
`app/Libraries/DocumentosSSTTypes/{NombreModulo}.php`

### Método getContextoBase()

El documento DEBE sobrescribir `getContextoBase()` para consultar datos de BD:

```php
public function getContextoBase(array $cliente, ?array $contexto): string
{
    $idCliente = $cliente['id_cliente'] ?? 0;
    $anio = (int) date('Y');

    // ═══════════════════════════════════════════════════════════════════
    // CONSULTAR DATOS DE PARTE 1 (tbl_pta_cliente)
    // ═══════════════════════════════════════════════════════════════════
    $itemsTexto = $this->obtenerItemsDelModulo($idCliente, $anio);

    // ═══════════════════════════════════════════════════════════════════
    // CONSULTAR INDICADORES DE PARTE 2 (si aplica)
    // ═══════════════════════════════════════════════════════════════════
    $indicadoresTexto = $this->obtenerIndicadoresDelModulo($idCliente);

    // Construir contexto para la IA
    return "CONTEXTO DE LA EMPRESA:
- Nombre: {$cliente['nombre_cliente']}
- Actividad: {$contexto['actividad_economica_principal']}
...

============================================================
DATOS DEL MÓDULO (Parte 1)
============================================================
{$itemsTexto}

============================================================
INDICADORES (Parte 2)
============================================================
{$indicadoresTexto}

============================================================
INSTRUCCIONES
============================================================
- Usa SOLO los datos listados arriba
- NO inventes información que no esté en el contexto
";
}
```

### Método de Consulta a BD

```php
private function obtenerItemsDelModulo(int $idCliente, int $anio): string
{
    $db = \Config\Database::connect();

    $items = $db->table('tbl_pta_cliente')
        ->where('id_cliente', $idCliente)
        ->where('tipo_servicio', '{NOMBRE_MODULO}')
        ->where('YEAR(fecha_propuesta)', $anio)
        ->where('estado_actividad !=', 'CERRADA')  // Solo activos
        ->orderBy('fecha_propuesta', 'ASC')
        ->get()
        ->getResultArray();

    // AUTO-VALIDACIÓN
    if (empty($items)) {
        return "⚠️ NO HAY DATOS DEL MÓDULO. Complete primero la Parte 1.";
    }

    // Formatear para el prompt
    $texto = "Total: " . count($items) . " registros\n\n";
    foreach ($items as $i => $item) {
        $texto .= ($i + 1) . ". {$item['actividad_plandetrabajo']}\n";
        $texto .= "   - Responsable: {$item['responsable_sugerido_plandetrabajo']}\n";
        $texto .= "   - Estado: {$item['estado_actividad']}\n\n";
    }

    return $texto;
}
```

---

## Service de Generación

### Ubicación
`app/Services/{NombreModulo}Service.php`

### Estructura Base

```php
class {NombreModulo}Service
{
    // Discriminador para este módulo
    protected const TIPO_SERVICIO = '{NOMBRE_MODULO}';

    // Items base predefinidos (opcional)
    public const ITEMS_BASE = [
        ['actividad' => '...', 'responsable' => '...', 'phva' => 'PLANEAR'],
        // ...
    ];

    /**
     * Preview: IA genera sugerencias
     */
    public function previewItems(int $idCliente, int $anio, ?array $contexto, string $instrucciones): array
    {
        $items = self::ITEMS_BASE;

        // Personalizar con IA si hay instrucciones
        if (!empty($instrucciones)) {
            $items = $this->personalizarConIA($items, $instrucciones, $contexto);
        }

        return ['items' => $items, 'total' => count($items)];
    }

    /**
     * Generar: Guardar en BD
     */
    public function generarItems(int $idCliente, int $anio, array $itemsSeleccionados): array
    {
        $creados = 0;
        $existentes = 0;
        $db = \Config\Database::connect();

        foreach ($itemsSeleccionados as $item) {
            // Verificar duplicados
            $existe = $db->table('tbl_pta_cliente')
                ->where('id_cliente', $idCliente)
                ->where('tipo_servicio', self::TIPO_SERVICIO)
                ->like('actividad_plandetrabajo', substr($item['actividad'], 0, 30))
                ->countAllResults();

            if ($existe > 0) {
                $existentes++;
                continue;
            }

            // Insertar
            $db->table('tbl_pta_cliente')->insert([
                'id_cliente' => $idCliente,
                'tipo_servicio' => self::TIPO_SERVICIO,
                'actividad_plandetrabajo' => $item['actividad'],
                'responsable_sugerido_plandetrabajo' => $item['responsable'],
                'phva_plandetrabajo' => $item['phva'] ?? 'HACER',
                'fecha_propuesta' => $item['fecha'],
                'estado_actividad' => 'ABIERTA'
            ]);
            $creados++;
        }

        return ['creadas' => $creados, 'existentes' => $existentes];
    }

    /**
     * Obtener items existentes del cliente
     */
    public function getItemsCliente(int $idCliente, int $anio): array
    {
        $db = \Config\Database::connect();

        return $db->table('tbl_pta_cliente')
            ->where('id_cliente', $idCliente)
            ->where('tipo_servicio', self::TIPO_SERVICIO)
            ->where('YEAR(fecha_propuesta)', $anio)
            ->orderBy('fecha_propuesta', 'ASC')
            ->get()
            ->getResultArray();
    }
}
```

---

## Validaciones Obligatorias

### 1. En el Service (Parte 1 → Parte 2)

```php
// Antes de generar indicadores
$itemsExistentes = $this->getItemsCliente($idCliente, $anio);
if (empty($itemsExistentes)) {
    throw new \Exception('No hay datos de Parte 1. Complete primero.');
}
```

### 2. En el Handler del Documento (Parte 3)

```php
// En getContextoBase()
if (empty($items)) {
    return "⚠️ NO HAY DATOS. Complete las partes anteriores.";
}
```

### 3. En el Controlador

```php
public function generarSeccion($idDocumento, $idSeccion)
{
    $contexto = $handler->getContextoBase($cliente, $contextoCliente);

    // Detectar advertencia
    if (strpos($contexto, '⚠️') !== false) {
        return $this->response->setJSON([
            'success' => false,
            'error' => 'Complete las fases anteriores del módulo.'
        ]);
    }

    // Proceder con generación IA
}
```

### 4. En la Vista

```php
<?php if (strpos($contextoItems ?? '', '⚠️') !== false): ?>
    <div class="alert alert-danger">
        <strong>Error:</strong> No hay datos del módulo.
        <a href="<?= base_url('generador-ia/' . $idCliente . '/{modulo}') ?>">
            Completar Parte 1
        </a>
    </div>
<?php endif; ?>
```

---

## Resumen de Archivos por Módulo

| Parte | Archivo | Función |
|-------|---------|---------|
| 1 | `Views/generador_ia/{modulo}.php` | Vista de generación IA |
| 1 | `Services/{Modulo}Service.php` | Lógica de generación y guardado |
| 1 | `Controllers/GeneradorIAController.php` | Endpoints preview/generar |
| 2 | `Views/generador_ia/indicadores_{modulo}.php` | Vista indicadores (opcional) |
| 2 | `Services/Indicadores{Modulo}Service.php` | Lógica indicadores (opcional) |
| 3 | `Libraries/DocumentosSSTTypes/{Modulo}.php` | Handler del documento |
| 3 | `Views/documentos_sst/{modulo}.php` | Vista preview documento |

---

## Principio Fundamental

**Sin datos previos en la base de datos, la IA NO puede generar contenido confiable.**

La cadena de 3 partes garantiza que cada documento se basa en información REAL del cliente, no en contenido inventado por la IA.

```
PARTE 1 genera → BD almacena → PARTE 2 consume/genera → BD almacena → PARTE 3 consume
```
