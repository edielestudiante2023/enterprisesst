# Dashboard de Gestión de Documentos SST por Cliente

## Fecha: 2026-02-15

## Objetivo

Crear un módulo centralizado para gestionar la generación de los 36 documentos del Sistema de Gestión de Seguridad y Salud en el Trabajo (SG-SST) por cliente, con visualización detallada del estado de cada documento, filtros avanzados, métricas de completitud y acceso directo a generación/edición.

---

## Problema Inicial

El sistema tenía 36 tipos de documentos SST distribuidos en diferentes categorías, pero:
1. No existía una vista consolidada que mostrara el estado de todos los documentos por cliente
2. No era posible identificar rápidamente cuáles documentos estaban generados y cuáles pendientes
3. No había acceso centralizado desde los dashboards del consultor y admin
4. No se podía filtrar o buscar documentos por categoría, numeral, tipo de flujo o estado
5. No existían métricas visuales del porcentaje de completitud del SG-SST

---

## Solución Implementada

### Arquitectura de la Solución

La implementación sigue el patrón **MVC de CodeIgniter 4** con integración de **DataTables** para búsqueda en tiempo real y **Select2** para selectores avanzados.

**Flujo de trabajo:**
```
Dashboard (Consultor/Admin)
    ↓ (Selector de cliente)
Vista Lista Documentos
    ↓ (Consulta BD)
tbl_versiones_documento
    ↓ (Verificación de existencia)
Tabla con 36 documentos + Estado + Acciones
```

**Tipos de flujo de documentos:**
- **Tipo A (secciones_ia)**: 28 documentos - Solo contexto del cliente
- **Tipo B (programa_con_pta)**: 1 documento - PTA + Indicadores + Contexto
- **Electoral**: 7 documentos - Actas de comités y brigadas

---

## 1. Metadata de los 36 Documentos

**Archivo:** `app/Controllers/DocumentosSSTController.php`

Se creó el método privado `getDocumentosMetadata()` con información hardcodeada de todos los documentos:

```php
private function getDocumentosMetadata(): array
{
    return [
        // 1.1 - Requisitos Legales y Básicos (1 doc)
        [
            'tipo' => 'identificacion_alto_riesgo',
            'numeral' => '1.1.5',
            'categoria' => 'Requisitos Legales y Básicos',
            'nombre' => 'Identificación de Trabajadores de Alto Riesgo',
            'flujo' => 'Tipo A',
            'orden' => 1
        ],

        // 2.1 - Políticas de SST (6 docs)
        [
            'tipo' => 'politica_sst_general',
            'numeral' => '2.1.1',
            'categoria' => 'Políticas de SST',
            'nombre' => 'Política de Seguridad y Salud en el Trabajo',
            'flujo' => 'Tipo A',
            'orden' => 2
        ],
        // ... +5 políticas más

        // 2.2 - Planificación (2 docs)
        [
            'tipo' => 'programa_capacitacion',
            'numeral' => '2.2.2',
            'categoria' => 'Planificación',
            'nombre' => 'Programa de Capacitación en SST',
            'flujo' => 'Tipo B',  // ← Único Tipo B
            'orden' => 9
        ],

        // ... +27 documentos más

        // Actas de Constitución (4 docs)
        [
            'tipo' => 'acta_constitucion_copasst',
            'numeral' => '1.1.1',
            'categoria' => 'Actas de Constitución',
            'nombre' => 'Acta de Constitución COPASST',
            'flujo' => 'Electoral',
            'orden' => 29
        ],
        // ... +3 actas más

        // Actas de Recomposición (4 docs)
        [
            'tipo' => 'acta_recomposicion_copasst',
            'numeral' => '1.1.1',
            'categoria' => 'Actas de Recomposición',
            'nombre' => 'Acta de Recomposición COPASST',
            'flujo' => 'Electoral',
            'orden' => 33
        ],
        // ... +3 actas más
    ];
}
```

**Campos de metadata:**
- `tipo`: Identificador snake_case del documento (usado en Factory y BD)
- `numeral`: Numeral de la Resolución 0312 de 2019
- `categoria`: Agrupación temática para filtros
- `nombre`: Nombre descriptivo del documento
- `flujo`: Tipo de flujo (Tipo A, Tipo B, Electoral)
- `orden`: Orden de presentación en la tabla

**Distribución por categoría:**
| Categoría | Cantidad |
|-----------|----------|
| Requisitos Legales y Básicos | 1 |
| Políticas de SST | 6 |
| Planificación | 2 |
| Comunicación | 1 |
| Adquisiciones | 2 |
| Gestión del Cambio | 1 |
| Control Documental | 2 |
| Promoción y Prevención | 6 |
| Investigación de Incidentes | 2 |
| Identificación de Peligros | 2 |
| Programas de Vigilancia | 3 |
| Comités y Brigadas | 1 |
| Actas de Constitución | 4 |
| Actas de Recomposición | 4 |
| **TOTAL** | **36** |

---

## 2. Método listaDocumentos() en Controlador

**Archivo:** `app/Controllers/DocumentosSSTController.php` (línea 5937)

Este método es el corazón de la funcionalidad:

```php
public function listaDocumentos(int $idCliente)
{
    // 1. Verificar que el cliente existe
    $cliente = $this->clienteModel->find($idCliente);
    if (!$cliente) {
        return redirect()->back()->with('error', 'Cliente no encontrado');
    }

    // 2. Obtener metadata de los 36 documentos
    $documentosMetadata = $this->getDocumentosMetadata();

    // 3. Inicializar contadores
    $documentos = [];
    $generados = 0;
    $noGenerados = 0;

    // 4. Verificar cada documento en la BD
    foreach ($documentosMetadata as $metadata) {
        $tipo = $metadata['tipo'];

        // Consultar tbl_versiones_documento
        $version = $this->db->table('tbl_versiones_documento')
            ->select('version_numero, fecha_creacion, estado')
            ->where('tipo_documento', $tipo)
            ->where('id_cliente', $idCliente)
            ->whereIn('estado', ['borrador', 'revision', 'aprobado'])
            ->orderBy('fecha_creacion', 'DESC')
            ->get()
            ->getRow();

        $existe = ($version !== null);

        // 5. Generar URLs usando DocumentoSSTFactory
        $factory = new DocumentoSSTFactory();
        $tipoKebab = str_replace('_', '-', $tipo);
        $anio = date('Y');

        // URL de generación (snake_case)
        $urlGenerar = base_url("documentos/generar/{$tipo}/{$idCliente}");

        // URL de vista previa (kebab-case)
        $urlVer = base_url("documentos-sst/{$idCliente}/{$tipoKebab}/{$anio}");

        // 6. Preparar datos del documento
        $documentos[] = [
            'tipo' => $tipo,
            'numeral' => $metadata['numeral'],
            'categoria' => $metadata['categoria'],
            'nombre' => $metadata['nombre'],
            'flujo' => $metadata['flujo'],
            'orden' => $metadata['orden'],
            'existe' => $existe,
            'estado' => $existe ? ucfirst($version->estado) : 'No generado',
            'version' => $existe ? $version->version_numero : '-',
            'fecha_modificacion' => $existe ? $version->fecha_creacion : null,
            'url_generar' => $urlGenerar,
            'url_ver' => $urlVer,
        ];

        // 7. Actualizar contadores
        if ($existe) {
            $generados++;
        } else {
            $noGenerados++;
        }
    }

    // 8. Calcular métricas
    $total = count($documentos);
    $porcentaje = $total > 0 ? round(($generados / $total) * 100, 1) : 0;

    // 9. Renderizar vista
    return view('documentos_sst/lista_documentos_cliente', [
        'cliente' => $cliente,
        'documentos' => $documentos,
        'total' => $total,
        'generados' => $generados,
        'no_generados' => $noGenerados,
        'porcentaje' => $porcentaje,
    ]);
}
```

**Lógica de verificación:**
- Se consulta `tbl_versiones_documento` por `tipo_documento` e `id_cliente`
- Solo se consideran estados: `borrador`, `revision`, `aprobado`
- Se ordena por `fecha_creacion DESC` para obtener la versión más reciente
- Si existe registro → documento generado
- Si no existe → documento pendiente

**Reglas de URLs:**
```php
// URL de generación: SIEMPRE snake_case
base_url("documentos/generar/programa_capacitacion/18")

// URL de vista previa: SIEMPRE kebab-case
base_url("documentos-sst/18/programa-capacitacion/2026")
```

---

## 3. Vista lista_documentos_cliente.php

**Archivo:** `app/Views/documentos_sst/lista_documentos_cliente.php` (nuevo)

### Estructura de la Vista

```html
<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Bootstrap 5 -->
    <!-- DataTables CSS -->
    <!-- Select2 CSS -->
    <!-- Font Awesome -->
</head>
<body>
    <!-- 1. Header con info del cliente -->
    <div class="container-fluid">
        <h1>Gestión de Documentos SST</h1>
        <p>Cliente: <?= esc($cliente['nombre_cliente']) ?></p>
    </div>

    <!-- 2. Métricas visuales -->
    <div class="row">
        <!-- Cards de métricas -->
    </div>

    <!-- 3. Filtros -->
    <div class="card">
        <select id="filtroCategorias">...</select>
        <select id="filtroFlujo">...</select>
        <select id="filtroEstado">...</select>
        <select id="filtroNumeral">...</select>
    </div>

    <!-- 4. Tabla DataTables -->
    <table id="tablaDocumentos">
        <thead>
            <tr>
                <th>Numeral</th>
                <th>Categoría</th>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th>Versión</th>
                <th>Última Modificación</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($documentos as $doc): ?>
                <tr>
                    <td><?= $doc['numeral'] ?></td>
                    <td><?= $doc['categoria'] ?></td>
                    <td><?= $doc['nombre'] ?></td>
                    <td><?= $doc['flujo'] ?></td>
                    <td>
                        <?php if ($doc['existe']): ?>
                            <span class="badge bg-success">Generado</span>
                        <?php else: ?>
                            <span class="badge bg-warning">Pendiente</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $doc['version'] ?></td>
                    <td><?= $doc['fecha_modificacion'] ?></td>
                    <td>
                        <?php if ($doc['existe']): ?>
                            <a href="<?= $doc['url_ver'] ?>" target="_blank" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> Ver/Editar
                            </a>
                            <a href="<?= $doc['url_generar'] ?>" target="_blank" class="btn btn-sm btn-secondary">
                                <i class="fas fa-plus"></i> Nueva Versión
                            </a>
                        <?php else: ?>
                            <a href="<?= $doc['url_generar'] ?>" target="_blank" class="btn btn-sm btn-success">
                                <i class="fas fa-file-alt"></i> Generar Documento
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Scripts -->
</body>
</html>
```

### Sección de Métricas

```html
<div class="row mb-4">
    <!-- Card 1: Documentos Generados -->
    <div class="col-md-3">
        <div class="card text-center shadow-sm border-success">
            <div class="card-body">
                <h5 class="card-title text-success">
                    <i class="fas fa-check-circle fa-2x"></i>
                </h5>
                <h2 class="display-4 text-success"><?= $generados ?></h2>
                <p class="card-text">Documentos Generados</p>
            </div>
        </div>
    </div>

    <!-- Card 2: Documentos Pendientes -->
    <div class="col-md-3">
        <div class="card text-center shadow-sm border-warning">
            <div class="card-body">
                <h5 class="card-title text-warning">
                    <i class="fas fa-exclamation-circle fa-2x"></i>
                </h5>
                <h2 class="display-4 text-warning"><?= $no_generados ?></h2>
                <p class="card-text">Documentos Pendientes</p>
            </div>
        </div>
    </div>

    <!-- Card 3: Total Documentos -->
    <div class="col-md-3">
        <div class="card text-center shadow-sm border-info">
            <div class="card-body">
                <h5 class="card-title text-info">
                    <i class="fas fa-file-alt fa-2x"></i>
                </h5>
                <h2 class="display-4 text-info"><?= $total ?></h2>
                <p class="card-text">Total de Documentos</p>
            </div>
        </div>
    </div>

    <!-- Card 4: Porcentaje de Completitud -->
    <div class="col-md-3">
        <div class="card text-center shadow-sm border-primary">
            <div class="card-body">
                <h5 class="card-title text-primary">
                    <i class="fas fa-chart-pie fa-2x"></i>
                </h5>
                <h2 class="display-4 text-primary"><?= $porcentaje ?>%</h2>
                <p class="card-text">Completitud SG-SST</p>
            </div>
        </div>
    </div>
</div>

<!-- Barra de Progreso -->
<div class="progress mb-4" style="height: 30px;">
    <div class="progress-bar bg-success progress-bar-striped progress-bar-animated"
         role="progressbar"
         style="width: <?= $porcentaje ?>%;">
        <?= $porcentaje ?>% completado
    </div>
</div>
```

### JavaScript: DataTables + Filtros

```javascript
$(document).ready(function() {
    // 1. Inicializar DataTables
    var table = $('#tablaDocumentos').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        pageLength: 25,
        order: [[0, 'asc']], // Ordenar por numeral
        columnDefs: [
            { targets: [7], orderable: false } // Columna Acciones no ordenable
        ],
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip'
    });

    // 2. Inicializar Select2 en filtros
    $('#filtroCategorias, #filtroFlujo, #filtroEstado, #filtroNumeral').select2({
        theme: 'bootstrap-5',
        placeholder: 'Todos',
        allowClear: true,
        width: '100%'
    });

    // 3. Filtro por Categoría
    $('#filtroCategorias').on('change', function() {
        var categoria = $(this).val();
        if (categoria) {
            table.column(1).search('^' + categoria + '$', true, false).draw();
        } else {
            table.column(1).search('').draw();
        }
    });

    // 4. Filtro por Tipo de Flujo
    $('#filtroFlujo').on('change', function() {
        var flujo = $(this).val();
        if (flujo) {
            table.column(3).search('^' + flujo + '$', true, false).draw();
        } else {
            table.column(3).search('').draw();
        }
    });

    // 5. Filtro por Estado
    $('#filtroEstado').on('change', function() {
        var estado = $(this).val();
        if (estado) {
            table.column(4).search(estado).draw();
        } else {
            table.column(4).search('').draw();
        }
    });

    // 6. Filtro por Numeral
    $('#filtroNumeral').on('change', function() {
        var numeral = $(this).val();
        if (numeral) {
            table.column(0).search('^' + numeral + '$', true, false).draw();
        } else {
            table.column(0).search('').draw();
        }
    });
});
```

**Características de DataTables:**
- **Búsqueda global**: Input en la esquina superior derecha filtra en todas las columnas
- **Ordenamiento**: Click en headers para ordenar ascendente/descendente
- **Paginación**: 25 registros por página por defecto
- **Idioma**: Español (es-ES.json)
- **Filtros de columna**: Regex para búsquedas exactas (`^valor$`)

---

## 4. Ruta en Routes.php

**Archivo:** `app/Config/Routes.php` (línea 1017)

```php
// Dashboard de gestión de documentos SST por cliente
$routes->get('/documentos-sst/lista/(:num)', 'DocumentosSSTController::listaDocumentos/$1');
```

**Patrón de URL:**
```
https://enterprisesst.local/documentos-sst/lista/18
                                                   ↑
                                             id_cliente
```

**Ubicación en Routes.php:**
- Se agregó después de las rutas de versionamiento (línea ~1015)
- Antes de las rutas de comités y actas

---

## 5. Modificación de Dashboards

### 5.1 Dashboard del Consultor

**Archivo:** `app/Views/consultant/dashboard.php`

**HTML agregado (antes de "Modulo de Firmas Electronicas"):**

```html
<!-- Modulo de Generacion de Documentos SST -->
<div class="mb-5">
    <h4 class="text-center mb-4" style="color: var(--primary-dark); font-weight: 700;">
        <i class="fas fa-file-alt me-2"></i>Generación de Documentos SST
    </h4>
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-6 mb-3">
            <div class="card shadow-sm border-0" style="border-radius: 15px; overflow: hidden;">
                <div class="card-body p-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="text-white text-center mb-3">
                        <i class="fas fa-clipboard-check me-2"></i>Documentos SST por Cliente
                    </h5>
                    <div class="row align-items-center">
                        <div class="col-12 mb-3">
                            <label class="text-white fw-bold mb-2">
                                <i class="fas fa-building me-2"></i>Seleccione un Cliente
                            </label>
                            <select id="selectClienteDocumentos" class="form-select" style="width: 100%;">
                                <option value="">-- Buscar cliente --</option>
                                <?php foreach ($clientes ?? [] as $cliente): ?>
                                    <option value="<?= esc($cliente['id_cliente']) ?>">
                                        <?= esc($cliente['nombre_cliente']) ?> - NIT: <?= esc($cliente['nit_cliente']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="button" id="btnIrDocumentos" class="btn btn-light w-100 py-2 fw-bold" disabled style="border-radius: 10px;">
                                <i class="fas fa-file-alt me-2"></i>Ir a Gestión de Documentos
                            </button>
                        </div>
                    </div>
                    <small class="text-white-50 d-block text-center mt-2">
                        36 documentos del SG-SST - Políticas, Programas, Procedimientos
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
```

**JavaScript agregado (antes de "=== SELECTOR DE FIRMAS ELECTRONICAS ==="):**

```javascript
// === SELECTOR DE DOCUMENTOS SST ===
$('#selectClienteDocumentos').select2({
    theme: 'bootstrap-5',
    placeholder: '-- Buscar cliente por nombre o NIT --',
    allowClear: true,
    width: '100%'
});

$('#selectClienteDocumentos').on('change', function() {
    var clienteId = $(this).val();
    $('#btnIrDocumentos').prop('disabled', !clienteId);
});

$('#btnIrDocumentos').on('click', function() {
    var clienteId = $('#selectClienteDocumentos').val();
    if (clienteId) {
        window.open('<?= base_url('documentos-sst/lista/') ?>' + clienteId, '_blank');
    }
});
```

### 5.2 Dashboard del Admin

**Archivo:** `app/Views/consultant/admindashboard.php`

Se agregó exactamente el mismo código HTML y JavaScript que en el dashboard del consultor, insertado antes de la sección "Modulo de Firmas Electronicas".

**Diferencias:**
- Ninguna. El código es idéntico en ambos dashboards.
- Ambos usan la misma variable `$clientes` que se pasa desde el controlador.

---

## 6. Integración con DocumentoSSTFactory

### Generación Dinámica de URLs

El método `listaDocumentos()` utiliza el **DocumentoSSTFactory** para generar URLs correctas según el tipo de documento:

```php
$factory = new DocumentoSSTFactory();
$tipoKebab = str_replace('_', '-', $tipo);
$anio = date('Y');

// URL de generación (snake_case)
$urlGenerar = base_url("documentos/generar/{$tipo}/{$idCliente}");

// URL de vista previa (kebab-case)
$urlVer = base_url("documentos-sst/{$idCliente}/{$tipoKebab}/{$anio}");
```

**Ejemplo práctico:**

| tipo_documento (BD) | URL Generar | URL Ver |
|---------------------|-------------|---------|
| `programa_capacitacion` | `/documentos/generar/programa_capacitacion/18` | `/documentos-sst/18/programa-capacitacion/2026` |
| `politica_sst_general` | `/documentos/generar/politica_sst_general/18` | `/documentos-sst/18/politica-sst-general/2026` |
| `acta_constitucion_copasst` | `/documentos/generar/acta_constitucion_copasst/18` | `/documentos-sst/18/acta-constitucion-copasst/2026` |

**Regla crítica:**
- **Backend/BD/Factory**: Siempre snake_case (`tipo_documento`)
- **Frontend/URLs web**: Siempre kebab-case para mejor SEO y legibilidad

---

## 7. Búsqueda en Tiempo Real (Enfoque COMBO)

Se implementó la **Opción 4 (COMBO)** que combina:

### A. Búsqueda Global de DataTables
```javascript
$('#tablaDocumentos').DataTable({
    // ... configuración ...
});
```
- Input automático en esquina superior derecha
- Busca en TODAS las columnas simultáneamente
- Resalta coincidencias
- Actualización instantánea mientras se escribe

### B. Filtros por Columna con Select2
```javascript
$('#filtroCategorias').on('change', function() {
    var categoria = $(this).val();
    if (categoria) {
        table.column(1).search('^' + categoria + '$', true, false).draw();
    } else {
        table.column(1).search('').draw();
    }
});
```
- 4 selectores: Categoría, Tipo de Flujo, Estado, Numeral
- Búsqueda exacta usando regex `^valor$`
- Combinables entre sí (filtros acumulativos)

### C. Ordenamiento por Columna
- Click en header de columna para ordenar
- Toggle ascendente/descendente
- Visual indicator (flechas)

**Ventajas del enfoque COMBO:**
- ✅ Búsqueda rápida por texto libre (DataTables)
- ✅ Filtrado preciso por categorías (Select2)
- ✅ Sin latencia (todo client-side)
- ✅ Combinación flexible de filtros
- ✅ UX intuitiva para consultores

---

## Archivos Modificados/Creados (Resumen)

| Archivo | Tipo de Cambio | Líneas |
|---------|----------------|--------|
| `app/Controllers/DocumentosSSTController.php` | Agregar método `listaDocumentos()` | 5937-6015 |
| `app/Controllers/DocumentosSSTController.php` | Agregar método `getDocumentosMetadata()` | 6016-6335 |
| `app/Views/documentos_sst/lista_documentos_cliente.php` | **Archivo nuevo** | 1-450 |
| `app/Config/Routes.php` | Agregar ruta `/documentos-sst/lista/(:num)` | 1017 |
| `app/Views/consultant/dashboard.php` | Agregar selector de documentos (HTML) | 753-790 |
| `app/Views/consultant/dashboard.php` | Agregar JavaScript Select2 | 1066-1080 |
| `app/Views/consultant/admindashboard.php` | Agregar selector de documentos (HTML) | 831-867 |
| `app/Views/consultant/admindashboard.php` | Agregar JavaScript Select2 | 1192-1209 |

---

## Lecciones Aprendidas

### 1. Convención de Nomenclatura (snake_case vs kebab-case)

**Problema:**
Confusión entre cuándo usar snake_case y cuándo kebab-case en URLs.

**Solución:**
Establecer regla clara:
```php
// Backend (BD, Factory, métodos PHP): snake_case
$tipo = 'programa_capacitacion';

// Frontend (URLs web, rutas): kebab-case
$tipoKebab = str_replace('_', '-', $tipo);
// Resultado: 'programa-capacitacion'
```

**Leccion:** Mantener consistencia en cada capa de la aplicación.

---

### 2. Verificación de Documentos Generados

**Problema inicial:**
¿Cómo determinar si un documento ya fue generado para un cliente?

**Solución implementada:**
```php
$version = $this->db->table('tbl_versiones_documento')
    ->select('version_numero, fecha_creacion, estado')
    ->where('tipo_documento', $tipo)
    ->where('id_cliente', $idCliente)
    ->whereIn('estado', ['borrador', 'revision', 'aprobado'])
    ->orderBy('fecha_creacion', 'DESC')
    ->get()
    ->getRow();

$existe = ($version !== null);
```

**Leccion:** Usar `tbl_versiones_documento` como fuente única de verdad para el estado de documentos.

---

### 3. Hardcodear Metadata vs Consultar BD

**Decisión:** Hardcodear los 36 documentos en `getDocumentosMetadata()`

**Justificación:**
- ✅ Los 36 documentos son **estáticos** (definidos por Resolución 0312/2019)
- ✅ No cambian frecuentemente
- ✅ Evita JOIN complejo con múltiples tablas
- ✅ Performance superior (sin query a BD)
- ✅ Metadata enriquecida (categoria, orden, flujo) que no está en BD

**Alternativa descartada:**
```sql
-- Query complejo que sería necesario
SELECT dt.tipo_documento, dt.numeral, c.nombre_categoria, ...
FROM tipos_documentos dt
LEFT JOIN categorias c ON ...
-- Requeriría tablas adicionales que no existen
```

**Leccion:** Para datos estáticos pequeños (< 100 registros), hardcodear es aceptable y más eficiente.

---

### 4. DataTables: Configuración en Español

**Problema:**
DataTables por defecto muestra textos en inglés ("Search", "Showing 1 to 10 of 50 entries").

**Solución:**
```javascript
$('#tablaDocumentos').DataTable({
    language: {
        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
    },
    // ... resto de configuración
});
```

**Resultado:**
- "Buscar" en lugar de "Search"
- "Mostrando 1 a 25 de 36 registros" en lugar de "Showing..."
- "Siguiente", "Anterior" en lugar de "Next", "Previous"

**Leccion:** Siempre configurar idioma en bibliotecas de terceros para mejorar UX.

---

### 5. Filtros Acumulativos con Regex

**Problema:**
¿Cómo filtrar por categoría exacta sin falsos positivos?

**Ejemplo del problema:**
- Categoría: "Políticas de SST"
- Búsqueda simple: `search('Políticas')` también encontraría "Políticas de Emergencia"

**Solución con regex:**
```javascript
if (categoria) {
    // ^ = inicio de línea, $ = fin de línea
    table.column(1).search('^' + categoria + '$', true, false).draw();
} else {
    table.column(1).search('').draw();
}
```

**Parámetros de `search()`:**
1. `'^valor$'`: Patrón de búsqueda
2. `true`: Habilitar regex
3. `false`: No case-sensitive (aunque luego se puede cambiar)

**Leccion:** Usar regex con `^$` para búsquedas exactas en filtros de columna.

---

### 6. Select2 con Bootstrap 5 Theme

**Problema:**
Select2 por defecto no se integra visualmente con Bootstrap 5.

**Solución:**
```html
<!-- CDN del tema Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
```

```javascript
$('#selectClienteDocumentos').select2({
    theme: 'bootstrap-5',
    placeholder: '-- Buscar cliente --',
    allowClear: true,
    width: '100%'
});
```

**Resultado:**
- Selectores con estilo consistente con Bootstrap 5
- Botón "X" para limpiar selección (`allowClear: true`)
- Placeholder visible cuando no hay selección

**Leccion:** Usar temas de terceros para mantener consistencia visual en la UI.

---

### 7. Deshabilitar Botón hasta Seleccionar Cliente

**UX Pattern:**
```javascript
$('#selectClienteDocumentos').on('change', function() {
    var clienteId = $(this).val();
    $('#btnIrDocumentos').prop('disabled', !clienteId);
});
```

**HTML inicial:**
```html
<button type="button" id="btnIrDocumentos" class="btn btn-light w-100 py-2 fw-bold" disabled>
    <i class="fas fa-file-alt me-2"></i>Ir a Gestión de Documentos
</button>
```

**Flujo:**
1. Botón inicialmente deshabilitado (`disabled`)
2. Seleccionar cliente → habilita botón
3. Limpiar selección → deshabilita botón

**Leccion:** Validar acciones del usuario antes de permitir navegación.

---

### 8. Abrir en Nueva Pestaña (`target="_blank"`)

**Implementación:**
```javascript
$('#btnIrDocumentos').on('click', function() {
    var clienteId = $('#selectClienteDocumentos').val();
    if (clienteId) {
        window.open('<?= base_url('documentos-sst/lista/') ?>' + clienteId, '_blank');
    }
});
```

**Ventajas:**
- ✅ No pierde el dashboard actual
- ✅ Permite comparar múltiples clientes abriendo varias pestañas
- ✅ Flujo de trabajo más eficiente para consultores

**Alternativa descartada:**
```javascript
window.location.href = url; // Navega en la misma pestaña
```

**Leccion:** Para dashboards administrativos, preferir `window.open()` para preservar contexto.

---

### 9. Métricas Visuales para Engagement

**Decisión de diseño:**
Agregar 4 tarjetas de métricas + barra de progreso en la parte superior de la vista.

**Impacto en UX:**
- ✅ Responde la pregunta clave del usuario: "¿Qué % de documentos tengo completos?"
- ✅ Motivación visual (barra de progreso animada)
- ✅ Identificación rápida de pendientes
- ✅ Sentido de logro cuando se acerca a 100%

**HTML de barra de progreso:**
```html
<div class="progress mb-4" style="height: 30px;">
    <div class="progress-bar bg-success progress-bar-striped progress-bar-animated"
         role="progressbar"
         style="width: <?= $porcentaje ?>%;">
        <?= $porcentaje ?>% completado
    </div>
</div>
```

**Clases clave de Bootstrap:**
- `progress-bar-striped`: Efecto de rayas diagonales
- `progress-bar-animated`: Animación de movimiento
- `bg-success`: Color verde (transmite progreso positivo)

**Leccion:** Las métricas visuales aumentan el engagement y claridad del dashboard.

---

### 10. Botones Condicionales según Estado

**Lógica implementada:**

```php
<?php if ($doc['existe']): ?>
    <!-- Documento YA generado -->
    <a href="<?= $doc['url_ver'] ?>" target="_blank" class="btn btn-sm btn-primary">
        <i class="fas fa-eye"></i> Ver/Editar
    </a>
    <a href="<?= $doc['url_generar'] ?>" target="_blank" class="btn btn-sm btn-secondary">
        <i class="fas fa-plus"></i> Nueva Versión
    </a>
<?php else: ?>
    <!-- Documento NO generado -->
    <a href="<?= $doc['url_generar'] ?>" target="_blank" class="btn btn-sm btn-success">
        <i class="fas fa-file-alt"></i> Generar Documento
    </a>
<?php endif; ?>
```

**Estados visuales:**
| Estado | Botones | Color | Acción |
|--------|---------|-------|--------|
| No generado | "Generar Documento" | Verde (`btn-success`) | Crear primer borrador |
| Generado | "Ver/Editar" + "Nueva Versión" | Azul + Gris (`btn-primary` + `btn-secondary`) | Ver existente o crear nueva versión |

**Leccion:** Los botones deben reflejar claramente el estado y las acciones disponibles.

---

## Resultado Final

### Características Implementadas

✅ **Vista consolidada** de 36 documentos SST por cliente
✅ **Métricas visuales**: Generados, Pendientes, Total, % Completitud
✅ **Barra de progreso animada** mostrando completitud del SG-SST
✅ **Filtros avanzados**: Por Categoría, Tipo de Flujo, Estado, Numeral
✅ **Búsqueda en tiempo real**: Global (DataTables) + Columnas (Select2)
✅ **Ordenamiento dinámico** por cualquier columna
✅ **Botones condicionales**: "Generar" vs "Ver/Editar" + "Nueva Versión"
✅ **Acceso desde dashboards**: Consultor y Admin con selector Select2
✅ **Internacionalización**: DataTables en español
✅ **Responsive design**: Bootstrap 5 para mobile-first
✅ **Integración con Factory**: URLs correctas automáticamente
✅ **Verificación en BD**: Consulta `tbl_versiones_documento`

### Flujo de Usuario Completo

1. **Consultor/Admin** abre su dashboard
2. Selecciona cliente en el selector "Generación de Documentos SST"
3. Click en "Ir a Gestión de Documentos" (abre en nueva pestaña)
4. Ve dashboard con:
   - 4 tarjetas de métricas
   - Barra de progreso visual
   - 4 filtros desplegables
   - Tabla con 36 documentos
5. Usa búsqueda global o filtros para encontrar documento específico
6. Click en:
   - **"Generar Documento"** → Abre formulario de generación con IA
   - **"Ver/Editar"** → Abre vista previa del documento existente
   - **"Nueva Versión"** → Crea nueva versión del documento

### Impacto en la Productividad

**Antes:**
- No había forma de saber qué documentos faltaban
- Había que navegar manualmente a cada documento
- No existían métricas de completitud

**Ahora:**
- **Vista única** de todos los 36 documentos
- **Identificación visual** de pendientes (badge amarillo)
- **Acceso directo** con un click desde dashboard
- **Filtros rápidos** para encontrar documentos
- **Métricas claras** de progreso del SG-SST

---

## Consultas SQL Útiles para Debugging

```sql
-- Ver todos los documentos generados para un cliente específico
SELECT
    tipo_documento,
    version_numero,
    estado,
    fecha_creacion
FROM tbl_versiones_documento
WHERE id_cliente = 18
ORDER BY fecha_creacion DESC;

-- Contar documentos generados por estado
SELECT
    estado,
    COUNT(*) as total
FROM tbl_versiones_documento
WHERE id_cliente = 18
GROUP BY estado;

-- Ver documentos pendientes (comparando con los 36)
-- (Esto requeriría crear una tabla temporal con los 36 tipos)
SELECT 'identificacion_alto_riesgo' AS tipo_documento
UNION SELECT 'politica_sst_general'
-- ... (36 veces)
EXCEPT
SELECT tipo_documento FROM tbl_versiones_documento WHERE id_cliente = 18;

-- Ver última versión de cada tipo de documento
SELECT
    tipo_documento,
    MAX(version_numero) as ultima_version,
    MAX(fecha_creacion) as ultima_modificacion
FROM tbl_versiones_documento
WHERE id_cliente = 18
GROUP BY tipo_documento;
```

---

## Mejoras Futuras (Opcionales)

### 1. Exportación a Excel/PDF
Agregar botón para exportar el listado de documentos:
```javascript
buttons: [
    'copy', 'csv', 'excel', 'pdf', 'print'
]
```

### 2. Notificaciones de Pendientes
Enviar email al consultor cuando un cliente tenga < 50% de documentos generados.

### 3. Dashboard Global de Todos los Clientes
Vista que muestre % de completitud de todos los clientes en una tabla.

### 4. Filtro por Rango de Fechas
Agregar DateRangePicker para filtrar por "Última Modificación".

### 5. Acciones Masivas
Checkbox para seleccionar múltiples documentos y "Generar Todos".

### 6. Historial de Versiones
Modal que muestre todas las versiones de un documento al hacer click en "Versión X".

---

## Documentación Relacionada

- [ACCESO_MODULO_ACTAS.md](../10_DOCUMENTOS_ESPECIFICOS/ACCESO_MODULO_ACTAS.md) - Patrón similar para módulo de actas
- [vista-web-estandar.md](../../.claude/projects/c--xampp-htdocs-enterprisesst/memory/vista-web-estandar.md) - Estilos estándar de vistas web
- [versionamiento.md](../../.claude/projects/c--xampp-htdocs-enterprisesst/memory/versionamiento.md) - Sistema de versiones de documentos
- [toolbar-documentos.md](../../.claude/projects/c--xampp-htdocs-enterprisesst/memory/toolbar-documentos.md) - Estándares de botones y toolbars

---

## Créditos

**Desarrollado:** 2026-02-15
**Sistema:** Enterprise SST
**Módulo:** Generación de Documentos del SG-SST
**Patrón:** MVC + Factory + DataTables + Select2
**Total de Documentos:** 36 (28 Tipo A + 1 Tipo B + 7 Electoral)
**Framework:** CodeIgniter 4 + Bootstrap 5
