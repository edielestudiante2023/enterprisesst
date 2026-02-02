# Arquitectura de carpeta.php - Guía para Cambios Seguros

## IMPORTANTE: Una Vista para Todas las Carpetas

El archivo `app/Views/documentacion/carpeta.php` es **UNA SOLA VISTA** que renderiza **TODAS** las carpetas del sistema de documentación SST. Cualquier cambio global afectará a todas las carpetas.

---

## Flujo de Datos

```
┌─────────────────────────────────────────────────────────────────────────────┐
│  URL: /documentacion/carpeta/{id}                                           │
│  Ejemplos:                                                                  │
│    /documentacion/carpeta/164  → 1.1.1 Responsable del SG-SST              │
│    /documentacion/carpeta/166  → 1.1.2 Responsabilidades en el SG-SST      │
│    /documentacion/carpeta/167  → 1.1.3 Presupuesto SST                     │
│    /documentacion/carpeta/168  → 1.2.1 Programa de Capacitación            │
│    /documentacion/carpeta/177  → 2.5.1 Archivo Documental                  │
└─────────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│  CONTROLADOR: DocumentacionController.php                                   │
│                                                                             │
│  public function carpeta($idCarpeta) {                                     │
│      $carpeta = $this->carpetaModel->find($idCarpeta);                     │
│      $tipoCarpetaFases = $this->determinarTipoCarpetaFases($carpeta);      │
│      // ...                                                                 │
│      return view('documentacion/carpeta', [                                │
│          'carpeta' => $carpeta,                                            │
│          'tipoCarpetaFases' => $tipoCarpetaFases,  // ← CLAVE              │
│          'documentosSSTAprobados' => $docs,                                │
│          // ...                                                             │
│      ]);                                                                    │
│  }                                                                          │
└─────────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│  FUNCIÓN: determinarTipoCarpetaFases($carpeta)                             │
│  Ubicación: DocumentacionController.php línea ~402                          │
│                                                                             │
│  Analiza el CÓDIGO de la carpeta y retorna el tipo:                        │
│                                                                             │
│  CÓDIGO    │ RETORNA                    │ DESCRIPCIÓN                       │
│  ──────────┼────────────────────────────┼─────────────────────────────────  │
│  1.1.1     │ 'responsables_sst'         │ Asignación de Responsable SST    │
│  1.1.2     │ 'responsabilidades_sgsst'  │ 3 docs de Responsabilidades      │
│  1.1.3     │ 'presupuesto_sst'          │ Presupuesto del SG-SST           │
│  1.2.1     │ 'capacitacion_sst'         │ Programa de Capacitación IA      │
│  2.5.1     │ 'archivo_documental'       │ Maestra de todos los documentos  │
│  otros     │ null                       │ Carpeta genérica                 │
└─────────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│  VISTA: carpeta.php                                                         │
│                                                                             │
│  Usa $tipoCarpetaFases para mostrar UI diferente según el tipo             │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## Tipos de Carpeta y sus Funcionalidades

| tipoCarpetaFases | Código | Botón Header | Tabla Documentos | Fases |
|------------------|--------|--------------|------------------|-------|
| `responsables_sst` | 1.1.1 | Generar Asignación | Sí | Sí |
| `responsabilidades_sgsst` | 1.1.2 | Dropdown 3 docs | Sí | Sí |
| `presupuesto_sst` | 1.1.3 | Abrir Presupuesto | Sí | No |
| `capacitacion_sst` | 1.2.1 | Crear con IA | Sí | Sí |
| `archivo_documental` | 2.5.1 | Proc. Control Doc | Sí (todos) | No |
| `null` (genérica) | otros | Nuevo Documento | No | No |

---

## REGLAS PARA CAMBIOS SEGUROS

### CAMBIO GLOBAL (afecta TODAS las carpetas)

Agregar código **FUERA** de cualquier condición `if ($tipoCarpetaFases ...)`:

```php
<!-- PELIGRO: Esto aparece en TODAS las carpetas -->
<div class="mi-componente-global">
    Este contenido se ve en carpeta 164, 166, 167, 168, 177, etc.
</div>
```

### CAMBIO ESPECÍFICO (solo una carpeta)

Agregar código **DENTRO** de la condición correspondiente:

```php
<?php if ($tipoCarpetaFases === 'archivo_documental'): ?>
    <!-- SEGURO: Solo aparece en carpeta 2.5.1 (id 177) -->
    <button class="btn btn-success">Exportar a Excel</button>
<?php endif; ?>
```

---

## Zonas de la Vista y Cómo Modificarlas

### 1. BOTONES DEL HEADER (col-md-4 text-end)
**Líneas aproximadas: 383-488**

```php
<div class="col-md-4 text-end">
    <?php if (isset($fasesInfo) && $fasesInfo && !$fasesInfo['puede_generar_documento']): ?>
        <!-- Botón bloqueado cuando hay fases incompletas -->

    <?php elseif ($tipoCarpetaFases === 'responsabilidades_sgsst'): ?>
        <!-- SOLO para 1.1.2: Dropdown con 3 documentos -->

    <?php elseif (in_array($tipoCarpetaFases, ['capacitacion_sst', 'responsables_sst'])): ?>
        <!-- SOLO para 1.1.1 y 1.2.1: Botón crear -->

    <?php elseif ($tipoCarpetaFases === 'presupuesto_sst'): ?>
        <!-- SOLO para 1.1.3: Botón abrir presupuesto -->

    <?php elseif ($tipoCarpetaFases === 'archivo_documental'): ?>
        <!-- SOLO para 2.5.1: Botón procedimiento control documental -->

    <?php elseif (!isset($tipoCarpetaFases)): ?>
        <!-- SOLO para carpetas genéricas: Botón nuevo documento -->

    <?php endif; ?>
</div>
```

**Para agregar un botón a UNA carpeta específica:**
```php
<?php elseif ($tipoCarpetaFases === 'mi_nuevo_tipo'): ?>
    <a href="..." class="btn btn-primary">Mi Nuevo Botón</a>
```

### 2. TABLA DE DOCUMENTOS SST
**Líneas aproximadas: 551-795**

```php
<?php if (isset($tipoCarpetaFases) && in_array($tipoCarpetaFases, [
    'capacitacion_sst',
    'responsables_sst',
    'responsabilidades_sgsst',
    'archivo_documental',    // ← Agregar aquí nuevos tipos
    'presupuesto_sst'
])): ?>
    <!-- Tabla de documentos SST -->
<?php endif; ?>
```

**Para que una carpeta muestre la tabla de documentos SST:**
1. Agregar el tipo al array `in_array()`
2. Asegurarse que el controlador envíe `$documentosSSTAprobados`

### 3. PANEL DE FASES
**Líneas aproximadas: 494-549**

```php
<?php if (isset($fasesInfo) && $fasesInfo && $fasesInfo['tiene_fases']): ?>
    <!-- Panel de fases - se muestra automáticamente si el servicio retorna fases -->
<?php endif; ?>
```

**Las fases se configuran en:** `app/Services/FasesDocumentoService.php`

### 4. ACCIONES POR TIPO DE DOCUMENTO
**Líneas aproximadas: 636-765**

```php
// Mapa de rutas para cada tipo de documento
$mapaRutas = [
    'asignacion_responsable_sgsst' => 'asignacion-responsable-sst/' . $docSST['anio'],
    'responsabilidades_rep_legal_sgsst' => 'responsabilidades-rep-legal/' . $docSST['anio'],
    // ... agregar nuevas rutas aquí
];

// Documentos que tienen editor
if ($tipoDoc === 'programa_capacitacion') {
    $urlEditar = base_url('documentos/generar/programa_capacitacion/...');
} elseif ($tipoDoc === 'procedimiento_control_documental') {
    $urlEditar = base_url('documentos/generar/procedimiento_control_documental/...');
}
// ... agregar nuevos editores aquí
```

---

## Checklist para Nuevos Cambios

### Antes de modificar carpeta.php:

- [ ] ¿El cambio es para TODAS las carpetas o solo para UNA?
- [ ] Si es para una carpeta específica, ¿está dentro del `if ($tipoCarpetaFases === '...')`?
- [ ] ¿Probé el cambio en múltiples carpetas (166, 177, etc.)?

### Si agrego una nueva carpeta especial:

- [ ] Agregar caso en `determinarTipoCarpetaFases()` del controlador
- [ ] Agregar `elseif` para el botón del header
- [ ] Agregar al array de la tabla de documentos SST si aplica
- [ ] Configurar fases en `FasesDocumentoService.php` si aplica
- [ ] Agregar ruta en `$mapaRutas` para las acciones

### Si agrego funcionalidad global:

- [ ] ¿Realmente debe aparecer en TODAS las carpetas?
- [ ] ¿Impacta el rendimiento? (cargar librerías JS/CSS para todas)
- [ ] ¿Hay conflictos con CSS/JS existente?

---

## Errores Comunes

### ERROR: Agregar código fuera de condiciones
```php
<!-- MAL: Aparece en TODAS las carpetas -->
<button id="exportExcel">Exportar</button>

<!-- BIEN: Solo en archivo_documental -->
<?php if ($tipoCarpetaFases === 'archivo_documental'): ?>
    <button id="exportExcel">Exportar</button>
<?php endif; ?>
```

### ERROR: Olvidar el else/elseif
```php
<!-- MAL: Si no hay else, carpetas genéricas no tienen botón -->
<?php if ($tipoCarpetaFases === 'capacitacion_sst'): ?>
    <button>Crear con IA</button>
<?php endif; ?>
<!-- Falta el else para carpetas genéricas -->

<!-- BIEN: Siempre tener un caso por defecto -->
<?php if ($tipoCarpetaFases === 'capacitacion_sst'): ?>
    <button>Crear con IA</button>
<?php elseif (!isset($tipoCarpetaFases)): ?>
    <button>Nuevo Documento</button>
<?php endif; ?>
```

### ERROR: Modificar el orden de los elseif
```php
<!-- MAL: El orden importa, condiciones más específicas primero -->
<?php if (!isset($tipoCarpetaFases)): ?>  <!-- Esto atrapa todo -->
<?php elseif ($tipoCarpetaFases === 'archivo_documental'): ?>  <!-- Nunca llega -->

<!-- BIEN: Específicos primero, genérico al final -->
<?php if ($tipoCarpetaFases === 'archivo_documental'): ?>
<?php elseif (!isset($tipoCarpetaFases)): ?>  <!-- Al final -->
```

---

## Archivos Relacionados

| Archivo | Propósito |
|---------|-----------|
| `app/Views/documentacion/carpeta.php` | Vista única para todas las carpetas |
| `app/Controllers/DocumentacionController.php` | Determina tipoCarpetaFases |
| `app/Services/FasesDocumentoService.php` | Configura fases por tipo |
| `app/Models/DocumentoSSTModel.php` | Consulta documentos SST |

---

## Contacto

Si tienes dudas sobre cómo hacer un cambio seguro, revisa este documento o consulta antes de modificar.

**Última actualización:** 2026-02-01
