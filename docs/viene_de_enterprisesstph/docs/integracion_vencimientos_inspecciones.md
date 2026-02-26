# Integración: Inspecciones → Vencimientos de Mantenimientos

## Contexto

El sistema tiene dos tablas de mantenimientos:

| Tabla | Propósito | PK |
|-------|-----------|-----|
| `tbl_mantenimientos` | Catálogo de tipos de mantenimiento | `id_mantenimiento` |
| `tbl_vencimientos_mantenimientos` | Registros de vencimiento por cliente | `id_vencimientos_mmttos` |

### Estructura `tbl_vencimientos_mantenimientos`

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id_vencimientos_mmttos` | INT PK | Auto-increment |
| `id_mantenimiento` | INT FK | Tipo de mantenimiento (catálogo) |
| `id_cliente` | INT FK | Cliente asignado |
| `id_consultor` | INT FK | Consultor responsable |
| `fecha_vencimiento` | DATE | Fecha límite |
| `estado_actividad` | ENUM | `sin ejecutar` / `ejecutado` / `CERRADA` / `CERRADA POR FIN CONTRATO` |
| `fecha_realizacion` | DATE | Fecha en que se ejecutó (null si pendiente) |
| `observaciones` | TEXT | Notas |

Actualmente estos registros se crean **manualmente** desde `/vencimientos/add`.

---

## Problema

Algunas inspecciones capturan fechas de vencimiento que deberían alimentar automáticamente la tabla de vencimientos. Hoy el consultor debe:
1. Hacer la inspección (ej: extintores) y registrar `fecha_vencimiento_global`
2. Ir por separado a `/vencimientos/add` y crear manualmente el registro

Esto genera doble trabajo y riesgo de olvido.

---

## Solución: Auto-poblado desde inspecciones

Cuando una inspección que maneja vencimientos se **finaliza**, el sistema debe automáticamente crear o actualizar el registro correspondiente en `tbl_vencimientos_mantenimientos`.

### Inspecciones que alimentan vencimientos

| Módulo | Campo fuente | id_mantenimiento | Fase |
|--------|-------------|------------------|------|
| Extintores | `fecha_vencimiento_global` | (buscar por `detalle_mantenimiento` LIKE '%extintor%') | 4 |
| Botiquín | `fecha_vencimiento_elementos` (por definir) | (buscar por `detalle_mantenimiento` LIKE '%botiqu%') | 5 |

> **Nota:** Los `id_mantenimiento` exactos dependen de los registros existentes en `tbl_mantenimientos`. Se debe hacer lookup por nombre, no hardcodear IDs.

### Lógica de negocio (UPSERT)

```
Al finalizar inspección:
  1. Obtener fecha_vencimiento del formulario
  2. Si fecha_vencimiento está vacía → no hacer nada
  3. Buscar id_mantenimiento en tbl_mantenimientos por detalle_mantenimiento
  4. Buscar registro existente en tbl_vencimientos_mantenimientos WHERE:
     - id_cliente = cliente de la inspección
     - id_mantenimiento = tipo correspondiente
     - estado_actividad = 'sin ejecutar'
  5. Si existe → UPDATE fecha_vencimiento
  6. Si no existe → INSERT nuevo registro con estado_actividad='sin ejecutar'
```

### Datos del registro

| Campo | Valor |
|-------|-------|
| `id_mantenimiento` | ID del tipo de mantenimiento correspondiente |
| `id_cliente` | `$inspeccion['id_cliente']` |
| `id_consultor` | `$inspeccion['id_consultor']` |
| `fecha_vencimiento` | `$inspeccion['fecha_vencimiento_global']` |
| `estado_actividad` | `'sin ejecutar'` |
| `fecha_realizacion` | `null` |
| `observaciones` | `'Auto-generado desde inspección de extintores #' . $id` |

---

## Implementación técnica

### Dónde hookear

En el método `finalizar($id)` de cada controlador de inspección, **después** de generar el PDF y **antes** del redirect.

**Ejemplo en `InspeccionExtintoresController::finalizar()`:**

```php
public function finalizar($id)
{
    $inspeccion = $this->inspeccionModel->find($id);
    if (!$inspeccion) {
        return redirect()->to('/inspecciones/extintores')->with('error', 'No encontrada');
    }

    $pdfPath = $this->generarPdfInterno($id);
    if (!$pdfPath) {
        return redirect()->back()->with('error', 'Error al generar PDF');
    }

    $this->inspeccionModel->update($id, [
        'estado'   => 'completo',
        'ruta_pdf' => $pdfPath,
    ]);

    $inspeccion = $this->inspeccionModel->find($id);
    $this->uploadToReportes($inspeccion, $pdfPath);

    // >>> NUEVO: Auto-poblar vencimiento de mantenimiento
    $this->syncVencimiento($inspeccion);

    return redirect()->to('/inspecciones/extintores/view/' . $id)
        ->with('msg', 'Inspeccion finalizada y PDF generado');
}
```

### Método privado reutilizable

```php
/**
 * Sincroniza fecha_vencimiento_global con tbl_vencimientos_mantenimientos.
 * Patrón UPSERT: actualiza si existe, crea si no.
 *
 * @param array  $inspeccion     Datos de la inspección finalizada
 * @param string $keyword        Palabra clave para buscar en tbl_mantenimientos
 * @param string $campoFecha     Nombre del campo de fecha en la inspección
 */
private function syncVencimiento(array $inspeccion, string $keyword = 'extintor', string $campoFecha = 'fecha_vencimiento_global'): void
{
    $fechaVencimiento = $inspeccion[$campoFecha] ?? null;
    if (empty($fechaVencimiento)) {
        return; // Sin fecha, no se crea vencimiento
    }

    $mantenimientoModel = new \App\Models\MantenimientoModel();
    $vencimientoModel = new \App\Models\VencimientosMantenimientoModel();

    // Buscar tipo de mantenimiento por keyword
    $mantenimiento = $mantenimientoModel
        ->like('detalle_mantenimiento', $keyword, 'both')
        ->first();

    if (!$mantenimiento) {
        log_message('warning', "syncVencimiento: No se encontró mantenimiento con keyword '{$keyword}'");
        return;
    }

    $idMantenimiento = $mantenimiento['id_mantenimiento'];

    // Buscar vencimiento existente (sin ejecutar) para este cliente + tipo
    $existente = $vencimientoModel
        ->where('id_cliente', $inspeccion['id_cliente'])
        ->where('id_mantenimiento', $idMantenimiento)
        ->where('estado_actividad', 'sin ejecutar')
        ->first();

    if ($existente) {
        // UPDATE: actualizar fecha
        $vencimientoModel->update($existente['id_vencimientos_mmttos'], [
            'fecha_vencimiento' => $fechaVencimiento,
            'id_consultor'      => $inspeccion['id_consultor'],
            'observaciones'     => 'Actualizado desde inspección extintores #' . $inspeccion['id'],
        ]);
    } else {
        // INSERT: crear nuevo
        $vencimientoModel->insert([
            'id_mantenimiento'  => $idMantenimiento,
            'id_cliente'        => $inspeccion['id_cliente'],
            'id_consultor'      => $inspeccion['id_consultor'],
            'fecha_vencimiento' => $fechaVencimiento,
            'estado_actividad'  => 'sin ejecutar',
            'observaciones'     => 'Auto-generado desde inspección extintores #' . $inspeccion['id'],
        ]);
    }
}
```

### Para Botiquín (futuro)

El mismo patrón, cambiando el keyword y el campo fuente:

```php
// En InspeccionBotiquinController::finalizar()
$this->syncVencimiento($inspeccion, 'botiqu', 'fecha_vencimiento_elementos');
```

---

## Consideraciones

1. **Prerequisito:** Debe existir un registro en `tbl_mantenimientos` con `detalle_mantenimiento` que contenga la keyword (ej: "Revisión Extintores", "Inspección Botiquín"). Si no existe, el sync falla silenciosamente con log de warning.

2. **Idempotencia:** Si se finaliza la misma inspección dos veces (regenerar PDF), el UPSERT actualiza el registro existente en vez de crear duplicados.

3. **Solo al finalizar:** El sync ocurre SOLO en `finalizar()`, no en `store()` ni `update()`. Los borradores no generan vencimientos.

4. **No bloquea:** Si falla el sync (ej: tabla mantenimiento no tiene el tipo), la inspección se finaliza igual. El vencimiento es un side-effect, no un requisito.

5. **Vencimientos ya ejecutados:** Si existe un vencimiento con `estado_actividad = 'ejecutado'`, NO se modifica. Se crea uno nuevo con `'sin ejecutar'` para el próximo ciclo.

---

## Verificación

1. Crear inspección de extintores con `fecha_vencimiento_global = 2026-09-15`
2. Finalizar la inspección
3. Ir a `/vencimientos` → verificar que aparece registro nuevo con fecha 15/09/2026, estado "sin ejecutar"
4. Editar la inspección (si se permite) y cambiar fecha → re-finalizar → verificar que se actualizó (no duplicó)
5. Verificar que si NO se pone fecha_vencimiento_global, NO se crea vencimiento
