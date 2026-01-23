# Proyecto de Documentación SST - Parte 6

## Resumen

Esta parte documenta las mejoras implementadas en el módulo de **Cumplimiento PHVA** (Estándares Mínimos Res. 0312/2019), incluyendo:
- Campo `criterio` (pregunta de auditoría) en estándares
- Vista de detalle mejorada con criterio de verificación
- Calificación automática según la Resolución 0312/2019
- Actualización en tiempo real de cards de resumen
- Instructivo actualizado v1.3

---

## 1. Campo Criterio de Verificación

### 1.1 Descripción
Cada uno de los 60 estándares mínimos ahora tiene un campo `criterio` que contiene la **pregunta de auditoría** exacta según el formato de autoevaluación de la Res. 0312/2019.

### 1.2 Cambio en Base de Datos
```sql
ALTER TABLE tbl_estandares_minimos
ADD COLUMN criterio TEXT NULL AFTER nombre;
```

### 1.3 Modelo Actualizado
**Archivo:** `app/Models/EstandarMinimoModel.php`
```php
protected $allowedFields = [
    'ciclo_phva', 'categoria', 'categoria_nombre', 'item', 'nombre',
    'criterio',  // <-- Campo agregado
    'peso_porcentual', 'aplica_7', 'aplica_21', 'aplica_60',
    'documentos_sugeridos', 'activo'
];
```

### 1.4 Script de Carga de Criterios
**Archivo:** `execute_criterio.php`

Carga los 60 criterios de verificación desde el CSV de auditoría oficial:

```php
// Ejemplo de criterio para estándar 1.1.1
UPDATE tbl_estandares_minimos
SET criterio = 'Hay un responsable del Sistema de Gestión de Seguridad...'
WHERE item = '1.1.1';
```

### 1.5 SQL para Producción
**Archivo:** `sql_ajustes_detalle_estandar.sql`

Contiene:
- ALTER TABLE para agregar columna `criterio`
- 60 UPDATEs con los criterios de verificación

---

## 2. Vista de Detalle del Estándar

### 2.1 Archivo
**Ubicación:** `app/Views/estandares/detalle.php`

### 2.2 Secciones de la Vista

| Sección | Descripción |
|---------|-------------|
| Encabezado | Número, ciclo PHVA, peso porcentual, nombre |
| Criterio de Verificación | Pregunta de auditoría según Res. 0312/2019 |
| Evaluación del Estándar | Estado, calificación (automática), fecha, observaciones |
| Documentos Sugeridos | Lista de documentos recomendados como evidencia |
| Documentos Vinculados | Documentos del cliente relacionados al estándar |
| Sidebar | Calificación actual, información del estándar, acciones |

### 2.3 Ruta de Acceso
```
/estandares/detalle/{id_cliente}/{id_estandar}
```

Ejemplo: `/estandares/detalle/11/1` para ver el estándar 1.1.1 del cliente 11.

---

## 3. Calificación Automática (Res. 0312/2019)

### 3.1 Regla de Negocio
Según la Resolución 0312/2019, la calificación de cada estándar es **automática** y depende del estado:

| Estado | Calificación |
|--------|-------------|
| **Cumple** | 100% del peso del estándar |
| **No Aplica** | 100% del peso (cuando está debidamente justificado) |
| **No Cumple** | 0% |
| **En Proceso** | 0% |
| **Pendiente** | 0% |

### 3.2 Implementación en Vista (detalle.php)
```php
<?php
$estadoActual = $clienteEstandar['estado'] ?? 'pendiente';
$pesoEstandar = (float)($estandar['peso_porcentual'] ?? 0);
// Calificación automática según estado (Res. 0312/2019)
$calificacionAuto = ($estadoActual === 'cumple' || $estadoActual === 'no_aplica')
                    ? $pesoEstandar
                    : 0;
?>
<input type="text" id="calificacionDisplay"
       class="form-control text-center fw-bold"
       value="<?= number_format($calificacionAuto, 2) ?>"
       readonly style="background-color: #e9ecef;">
```

### 3.3 Implementación en Controlador
**Archivo:** `app/Controllers/EstandaresClienteController.php`

```php
public function actualizarEstado()
{
    // Si no se envía calificación, calcularla automáticamente
    if ($calificacionPost === null || $calificacionPost === '') {
        $estandar = $this->estandarModel->find($idEstandar);
        $pesoEstandar = (float) ($estandar['peso_porcentual'] ?? 0);
        // Cumple o No Aplica = 100% del peso, otros = 0
        $calificacion = ($estado === 'cumple' || $estado === 'no_aplica')
                        ? $pesoEstandar : 0;
    }
    // ...
}
```

### 3.4 Actualización en Tiempo Real (JavaScript)
```javascript
function calcularCalificacion(estado) {
    // Según Res. 0312/2019:
    // Cumple = 100% del peso del estándar
    // No Aplica = 100% del peso (cuando está debidamente justificado)
    // No Cumple / Pendiente / En Proceso = 0%
    return (estado === 'cumple' || estado === 'no_aplica') ? pesoMax : 0;
}

document.getElementById('estadoSelect').addEventListener('change', function() {
    const calificacion = calcularCalificacion(this.value);
    document.getElementById('calificacionDisplay').value = calificacion.toFixed(2);
    document.getElementById('calificacionInput').value = calificacion;
});
```

---

## 4. Dashboard con Actualización en Tiempo Real

### 4.1 Archivo
**Ubicación:** `app/Views/estandares/dashboard.php`

### 4.2 Cards de Resumen con IDs
Los cards ahora tienen IDs para actualización dinámica:

```html
<h4 id="contadorCumple" class="mt-1 mb-0"><?= $resumen['cumple'] ?? 0 ?></h4>
<h4 id="contadorEnProceso" class="mt-1 mb-0"><?= $resumen['en_proceso'] ?? 0 ?></h4>
<h4 id="contadorNoCumple" class="mt-1 mb-0"><?= $resumen['no_cumple'] ?? 0 ?></h4>
<h4 id="contadorPendiente" class="mt-1 mb-0"><?= $resumen['pendiente'] ?? 0 ?></h4>
<h4 id="contadorNoAplica" class="mt-1 mb-0"><?= $resumen['no_aplica'] ?? 0 ?></h4>
<h2 id="porcentajeCumplimiento" class="mt-3 mb-0"><?= $cumplimientoPonderado ?>%</h2>
<circle id="progressCircle" ... />
```

### 4.3 Función de Actualización AJAX
```javascript
function actualizarResumen(resumen) {
    if (!resumen) return;

    // Actualizar contadores
    document.getElementById('contadorCumple').textContent = resumen.cumple || 0;
    document.getElementById('contadorEnProceso').textContent = resumen.en_proceso || 0;
    document.getElementById('contadorNoCumple').textContent = resumen.no_cumple || 0;
    document.getElementById('contadorPendiente').textContent = resumen.pendiente || 0;
    document.getElementById('contadorNoAplica').textContent = resumen.no_aplica || 0;

    // Actualizar porcentaje de cumplimiento
    const porcentaje = resumen.porcentaje_cumplimiento || 0;
    document.getElementById('porcentajeCumplimiento').textContent =
        porcentaje.toFixed(1) + '%';

    // Actualizar el círculo de progreso
    const circle = document.getElementById('progressCircle');
    if (circle) {
        const offset = 440 - (440 * porcentaje / 100);
        circle.style.strokeDashoffset = offset;
    }
}
```

### 4.4 Flujo de Actualización
1. Usuario cambia estado de un estándar en el select
2. Se muestra indicador de carga (select deshabilitado)
3. AJAX envía `id_cliente`, `id_estandar`, `estado`
4. Controlador calcula calificación automática y guarda
5. Controlador retorna `resumen` con contadores actualizados
6. JavaScript actualiza todos los cards sin recargar página
7. Select muestra feedback verde de éxito

---

## 5. Proceso de Inicialización Manual

### 5.1 Cambio de Comportamiento
Anteriormente los estándares se inicializaban automáticamente. Ahora requiere:
1. Clic en botón "Inicializar Estándares del Cliente"
2. Primera confirmación (muestra nivel de estándares)
3. Segunda confirmación (confirmación final)

### 5.2 Código del Dashboard
```javascript
function confirmarInicializacion() {
    const nivelEstandares = <?= $contexto['estandares_aplicables'] ?? 60 ?>;
    const primerConfirm = confirm(
        '¿Está seguro de inicializar los estándares?\n\n' +
        'Nivel configurado: ' + nivelEstandares + ' estándares aplicables'
    );

    if (primerConfirm) {
        const segundoConfirm = confirm('CONFIRMACIÓN FINAL\n\n¿Confirma?');
        if (segundoConfirm) {
            window.location.href = '<?= base_url("estandares/inicializar/" . $cliente['id_cliente']) ?>';
        }
    }
}
```

### 5.3 Cuándo Usar la Inicialización
- Una vez al año para la autoevaluación del SG-SST
- Cuando ingresa un cliente nuevo al sistema

---

## 6. Instructivo Actualizado (v1.3)

### 6.1 Archivo
**Ubicación:** `app/Views/documentacion/instructivo.php`

### 6.2 Cambios en la Sección 6 (Cumplimiento PHVA)
- Eliminada referencia a "auto-inicialización"
- Explicación del proceso de inicialización manual
- Tabla de calificación automática según estado
- Advertencia sobre cuándo usar la inicialización

### 6.3 Cambios en la Sección 8 (Flujo de Trabajo)
```
2. DIAGNÓSTICO (Inicialización Manual)
   ├── Acceder a "Cumplimiento PHVA" del cliente
   ├── Hacer clic en "Inicializar Estándares del Cliente"
   │   └── Confirmar dos veces (doble confirmación)
   ├── Revisar todos los estándares aplicables
   ├── Ver detalle de cada estándar (criterio de verificación)
   └── Marcar estado actual de cada uno (Cumple/No Cumple/En Proceso)
```

---

## 7. Script de Limpieza de Estándares

### 7.1 Archivo
**Ubicación:** `limpiar_estandares_cliente.php`

### 7.2 Uso
```
http://localhost/enterprisesst/limpiar_estandares_cliente.php?cliente=11
```

### 7.3 Función
Elimina los estándares de un cliente para poder regenerarlos con los criterios actualizados.

---

## 8. Resumen de Archivos Modificados

### 8.1 Controladores
| Archivo | Cambios |
|---------|---------|
| `EstandaresClienteController.php` | Calificación automática, acepta AJAX y POST |

### 8.2 Modelos
| Archivo | Cambios |
|---------|---------|
| `EstandarMinimoModel.php` | Campo `criterio` en allowedFields |
| `ClienteEstandaresModel.php` | Método `actualizarEvaluacion()` |

### 8.3 Vistas
| Archivo | Cambios |
|---------|---------|
| `estandares/dashboard.php` | IDs en cards, AJAX con actualización en tiempo real |
| `estandares/detalle.php` | Criterio de verificación, calificación automática |
| `documentacion/instructivo.php` | Versión 1.3, flujo actualizado |

### 8.4 Scripts Auxiliares
| Archivo | Descripción |
|---------|-------------|
| `execute_criterio.php` | Carga los 60 criterios en la BD |
| `limpiar_estandares_cliente.php` | Limpia estándares de un cliente |
| `sql_ajustes_detalle_estandar.sql` | SQL para producción |

---

## 9. Estructura de Datos del Estándar

### 9.1 Tabla tbl_estandares_minimos
```sql
id_estandar INT PRIMARY KEY
ciclo_phva ENUM('PLANEAR','HACER','VERIFICAR','ACTUAR')
categoria VARCHAR(10)
categoria_nombre VARCHAR(100)
item VARCHAR(10)           -- Ej: '1.1.1', '2.1.1'
nombre TEXT                -- Nombre del estándar
criterio TEXT              -- Pregunta de auditoría (NUEVO)
peso_porcentual DECIMAL(5,2)
aplica_7 TINYINT(1)
aplica_21 TINYINT(1)
aplica_60 TINYINT(1)
documentos_sugeridos TEXT
activo TINYINT(1)
```

### 9.2 Tabla tbl_cliente_estandares
```sql
id_cliente_estandar INT PRIMARY KEY
id_cliente INT
id_estandar INT
estado ENUM('pendiente','en_proceso','cumple','no_cumple','no_aplica')
calificacion DECIMAL(5,2)  -- Calculada automáticamente
fecha_cumplimiento DATE
evidencia_path VARCHAR(255)
observaciones TEXT
created_at TIMESTAMP
updated_at TIMESTAMP
```

---

## 10. Conclusión

### 10.1 Mejoras Implementadas
1. **Criterio de verificación** - Cada estándar ahora muestra la pregunta de auditoría oficial
2. **Calificación automática** - Se calcula según el estado (Res. 0312/2019)
3. **Actualización en tiempo real** - Los cards del dashboard se actualizan sin recargar
4. **Inicialización controlada** - Requiere doble confirmación
5. **Instructivo actualizado** - Refleja el flujo real del sistema

### 10.2 Beneficios
- El consultor puede ver exactamente qué debe verificar para cada estándar
- No hay errores manuales en la calificación
- Mejor experiencia de usuario con feedback inmediato
- Proceso de inicialización más seguro

---

## 11. Archivos del Proyecto

```
proyecto_documentacion_sst_parte1.md  -- Conceptos, alcance, estructura
proyecto_documentacion_sst_parte2.md  -- Prompts IA, wireframes, flujo firmas
proyecto_documentacion_sst_parte3.md  -- BD implementada, stored procedures
proyecto_documentacion_sst_parte4.md  -- Mejoras contexto SST
proyecto_documentacion_sst_parte5.md  -- Estado real verificado
proyecto_documentacion_sst_parte6.md  -- (Este archivo) Mejoras cumplimiento PHVA
```

---

*Documento generado: Enero 2026*
*Proyecto: EnterpriseSST - Módulo de Documentación*
*Parte 6 de 6*
