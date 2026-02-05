# Instructivo: Duplicacion de Modulos de Numerales SG-SST

## Introduccion

Este documento describe paso a paso como duplicar la funcionalidad de tabla de documentos SST para nuevos numerales del Sistema de Gestion de Seguridad y Salud en el Trabajo (SG-SST).

El patron de referencia es el modulo **1.1.2 Responsabilidades en el SG-SST**, que fue replicado exitosamente para **1.1.5 Identificacion de Trabajadores de Alto Riesgo**.

---

## Arquitectura del Sistema

### Flujo de Datos

```
URL: /documentacion/carpeta/{id}
         |
         v
DocumentacionController::carpeta()
         |
         v
determinarTipoCarpetaFases() --> Retorna tipo: 'identificacion_alto_riesgo'
         |
         v
Query a tbl_documentos_sst filtrado por tipo_documento
         |
         v
Vista: documentacion/carpeta.php
         |
         v
Vista tipo: documentacion/_tipos/{tipo}.php
         |
         v
Componente: _components/tabla_documentos_sst.php
         |
         v
Componente: _components/acciones_documento.php
```

### Archivos Involucrados

| Archivo | Proposito |
|---------|-----------|
| `app/Controllers/DocumentacionController.php` | Controlador principal, determina tipo de carpeta y carga documentos |
| `app/Views/documentacion/_tipos/{tipo}.php` | Vista especifica para cada tipo de carpeta |
| `app/Views/documentacion/_components/tabla_documentos_sst.php` | Tabla reutilizable de documentos |
| `app/Views/documentacion/_components/acciones_documento.php` | Botones de acciones (PDF, ver, editar, firmas) |
| `app/Config/Routes.php` | Rutas para ver/crear documentos |
| `app/SQL/agregar_{tipo}.php` | Script para configurar tipo en BD |

---

## Paso 1: Crear Vista de Tipo de Carpeta

Crear archivo en `app/Views/documentacion/_tipos/{nuevo_tipo}.php`

### Plantilla Base

```php
<?php
/**
 * Vista de Tipo: X.X.X Nombre del Numeral
 * Carpeta con dropdown para documentos de este tipo
 * Variables: $carpeta, $cliente, $fasesInfo, $documentosSSTAprobados, $contextoCliente
 */

// Verificar que documentos ya existen para el anio actual
$docsExistentesTipos = [];
if (!empty($documentosSSTAprobados)) {
    foreach ($documentosSSTAprobados as $d) {
        if ($d['anio'] == date('Y')) {
            $docsExistentesTipos[$d['tipo_documento']] = true;
        }
    }
}
$totalEsperado = 1; // Numero de documentos esperados para esta carpeta
?>

<!-- Card de Carpeta con Dropdown de Documentos -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="mb-1">
                    <i class="bi bi-folder-fill text-warning me-2"></i>
                    <?= esc($carpeta['nombre']) ?>
                </h4>
                <?php if (!empty($carpeta['codigo'])): ?>
                    <span class="badge bg-light text-dark me-2"><?= esc($carpeta['codigo']) ?></span>
                <?php endif; ?>
                <?php if (!empty($carpeta['descripcion'])): ?>
                    <p class="text-muted mb-0 mt-1"><?= esc($carpeta['descripcion']) ?></p>
                <?php endif; ?>
            </div>
            <div class="col-md-4 text-end">
                <?php if (isset($fasesInfo) && $fasesInfo && !$fasesInfo['puede_generar_documento']): ?>
                    <button type="button" class="btn btn-secondary" disabled title="Complete las fases previas">
                        <i class="bi bi-lock me-1"></i>Nuevo Documento
                    </button>
                <?php else: ?>
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-plus-lg me-1"></i>Nuevo Documento
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if (!isset($docsExistentesTipos['TIPO_DOCUMENTO'])): ?>
                            <li>
                                <!-- OPCION A: Enlace directo al generador IA -->
                                <a href="<?= base_url('documentos/generar/TIPO_DOCUMENTO/' . $cliente['id_cliente']) ?>" class="dropdown-item">
                                    <i class="bi bi-ICONO me-2 text-COLOR"></i>Nombre Documento
                                </a>

                                <!-- OPCION B: Formulario POST para crear -->
                                <form action="<?= base_url('documentos-sst/' . $cliente['id_cliente'] . '/crear-TIPO') ?>" method="post">
                                    <button type="submit" class="dropdown-item">
                                        <i class="bi bi-ICONO me-2 text-COLOR"></i>Nombre Documento
                                    </button>
                                </form>
                            </li>
                            <?php endif; ?>
                            <?php if (count($docsExistentesTipos) >= $totalEsperado): ?>
                            <li><span class="dropdown-item text-muted"><i class="bi bi-check-circle me-2"></i>Documento creado <?= date('Y') ?></span></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Panel de Fases (opcional) -->
<?= view('documentacion/_components/panel_fases', [
    'fasesInfo' => $fasesInfo ?? null,
    'tipoCarpetaFases' => 'TIPO_CARPETA',
    'cliente' => $cliente,
    'carpeta' => $carpeta,
    'documentoExistente' => $documentoExistente ?? null
]) ?>

<!-- Tabla de Documentos SST -->
<?= view('documentacion/_components/tabla_documentos_sst', [
    'tipoCarpetaFases' => 'TIPO_CARPETA',
    'documentosSSTAprobados' => $documentosSSTAprobados ?? [],
    'cliente' => $cliente
]) ?>

<!-- Subcarpetas -->
<div class="row">
    <?= view('documentacion/_components/lista_subcarpetas', [
        'subcarpetas' => $subcarpetas ?? []
    ]) ?>
</div>
```

### Variables a Reemplazar

| Variable | Descripcion | Ejemplo |
|----------|-------------|---------|
| `TIPO_DOCUMENTO` | Identificador del tipo de documento | `identificacion_alto_riesgo` |
| `TIPO_CARPETA` | Identificador del tipo de carpeta (mismo que tipo documento) | `identificacion_alto_riesgo` |
| `ICONO` | Icono de Bootstrap Icons | `exclamation-diamond` |
| `COLOR` | Color del icono | `warning`, `primary`, `success` |

---

## Paso 2: Modificar DocumentacionController

### 2.1 Agregar a la Lista de Tipos con Documentos SST

En `app/Controllers/DocumentacionController.php`, buscar el array en el metodo `carpeta()`:

```php
if (in_array($tipoCarpetaFases, ['capacitacion_sst', 'responsables_sst', 'responsabilidades_sgsst', 'archivo_documental', 'presupuesto_sst', 'afiliacion_srl', 'identificacion_alto_riesgo'])) {
```

**Agregar el nuevo tipo al array.**

### 2.2 Agregar Filtro de Tipo de Documento

En el mismo metodo, buscar los filtros `elseif` y agregar:

```php
} elseif ($tipoCarpetaFases === 'NUEVO_TIPO') {
    // X.X.X: Descripcion del numeral
    $queryDocs->where('tipo_documento', 'NUEVO_TIPO');
}
```

### 2.3 Agregar Deteccion de Tipo de Carpeta

En el metodo `determinarTipoCarpetaFases()`, agregar la condicion:

```php
// X.X.X. Nombre del Numeral
if (strpos($codigo, 'X.X.X') !== false ||
    strpos($nombre, 'palabra_clave_1') !== false ||
    strpos($nombre, 'palabra_clave_2') !== false) {
    return 'NUEVO_TIPO';
}
```

**IMPORTANTE:** El orden de las condiciones importa. Colocar condiciones mas especificas primero.

---

## Paso 3: Modificar Componente tabla_documentos_sst.php

En `app/Views/documentacion/_components/tabla_documentos_sst.php`, agregar al array:

```php
$tiposConTabla = ['capacitacion_sst', 'responsables_sst', 'responsabilidades_sgsst', 'archivo_documental', 'presupuesto_sst', 'identificacion_alto_riesgo', 'NUEVO_TIPO'];
```

---

## Paso 4: Modificar Componente acciones_documento.php

En `app/Views/documentacion/_components/acciones_documento.php`:

### 4.1 Agregar Ruta al Mapa

```php
$mapaRutas = [
    // ... rutas existentes ...
    'NUEVO_TIPO' => 'ruta-url/' . $docSST['anio'],
];
```

### 4.2 Agregar URL de Edicion (si aplica)

```php
} elseif ($tipoDoc === 'NUEVO_TIPO') {
    $urlEditar = base_url('documentos/generar/NUEVO_TIPO/' . $cliente['id_cliente'] . '?anio=' . $docSST['anio']);
}
```

---

## Paso 5: Agregar Rutas

En `app/Config/Routes.php`, agregar las rutas necesarias:

### 5.1 Ruta para Ver Documento

```php
// X.X.X Nombre del Numeral
$routes->get('/documentos-sst/(:num)/ruta-url/(:num)', 'DocumentosSSTController::verDocumentoGenerico/$1/NUEVO_TIPO/$2');
```

### 5.2 Ruta para Crear Documento (si usa formulario POST)

```php
$routes->post('/documentos-sst/(:num)/crear-TIPO', 'NuevoTipoController::crear/$1');
```

### 5.3 Ruta para Generador IA (si usa enlace GET)

Ya existe ruta generica:
```php
$routes->get('/documentos/generar/(:segment)/(:num)', 'DocumentosSSTController::generarConIA/$1/$2');
```

---

## Paso 6: Crear Script SQL de Configuracion

Crear archivo `app/SQL/agregar_{tipo}.php`:

```php
<?php
/**
 * Script para agregar tipo de documento: Nombre del Documento
 * Estandar: X.X.X
 *
 * Ejecutar: php app/SQL/agregar_{tipo}.php
 */

$conexiones = [
    'local' => [
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'empresas_sst',
        'username' => 'root',
        'password' => '',
        'ssl' => false
    ]
];

function ejecutar($nombre, $config) {
    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
        $pdo = new PDO($dsn, $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

        // 1. Insertar tipo de documento
        $pdo->exec("
            INSERT INTO tbl_doc_tipo_configuracion
            (tipo_documento, nombre, descripcion, estandar, flujo, categoria, icono, orden)
            VALUES
            ('NUEVO_TIPO',
             'Nombre Completo del Documento',
             'Descripcion del proposito del documento',
             'X.X.X',
             'secciones_ia',
             'categoria',
             'bi-icono',
             10)
            ON DUPLICATE KEY UPDATE nombre = VALUES(nombre)
        ");

        // 2. Obtener ID del tipo
        $idTipo = $pdo->query("SELECT id_tipo_config FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'NUEVO_TIPO'")->fetchColumn();

        // 3. Insertar secciones
        $secciones = [
            [1, 'Objetivo', 'objetivo', 'texto', null, 'Prompt IA para generar esta seccion...'],
            [2, 'Alcance', 'alcance', 'texto', null, 'Prompt IA...'],
            // ... mas secciones
        ];

        $stmt = $pdo->prepare("
            INSERT INTO tbl_doc_secciones_config
            (id_tipo_config, numero, nombre, seccion_key, tipo_contenido, tabla_dinamica_tipo, orden, prompt_ia)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), prompt_ia = VALUES(prompt_ia)
        ");

        foreach ($secciones as $s) {
            $stmt->execute([$idTipo, $s[0], $s[1], $s[2], $s[3], $s[4], $s[0], $s[5]]);
        }

        // 4. Insertar firmantes
        $firmantes = [
            ['responsable_sst', 'Elaboro', 'Elaboro / Responsable del SG-SST', 1, 1],
            ['representante_legal', 'Aprobo', 'Aprobo / Representante Legal', 2, 0],
        ];

        $stmtF = $pdo->prepare("
            INSERT INTO tbl_doc_firmantes_config
            (id_tipo_config, firmante_tipo, rol_display, columna_encabezado, orden, mostrar_licencia)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE rol_display = VALUES(rol_display)
        ");

        foreach ($firmantes as $f) {
            $stmtF->execute([$idTipo, $f[0], $f[1], $f[2], $f[3], $f[4]]);
        }

        // 5. Insertar plantilla
        $pdo->exec("
            INSERT INTO tbl_doc_plantillas (id_tipo, nombre, codigo_sugerido, tipo_documento, version, activo)
            VALUES (1, 'Nombre Documento', 'COD-SST-XX', 'NUEVO_TIPO', '001', 1)
            ON DUPLICATE KEY UPDATE nombre = VALUES(nombre)
        ");

        // 6. Mapear a carpeta
        $pdo->exec("
            INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
            VALUES ('COD-SST-XX', 'X.X.X')
            ON DUPLICATE KEY UPDATE codigo_carpeta = VALUES(codigo_carpeta)
        ");

        echo "OK - Documento 'NUEVO_TIPO' configurado\n";
        return true;

    } catch (PDOException $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
        return false;
    }
}

ejecutar('LOCAL', $conexiones['local']);
```

---

## Paso 7: Verificacion Final

### Checklist de Verificacion

- [ ] Vista tipo creada en `_tipos/{tipo}.php`
- [ ] Tipo agregado al array en `carpeta()` del controlador
- [ ] Filtro de tipo_documento agregado en controlador
- [ ] Condicion de deteccion agregada en `determinarTipoCarpetaFases()`
- [ ] Tipo agregado a `$tiposConTabla` en `tabla_documentos_sst.php`
- [ ] Ruta agregada a `$mapaRutas` en `acciones_documento.php`
- [ ] URL de edicion agregada (si aplica)
- [ ] Rutas agregadas en `Routes.php`
- [ ] Script SQL ejecutado en BD local y produccion

### Pruebas a Realizar

1. Navegar a la carpeta del numeral
2. Verificar que se muestra la vista correcta
3. Verificar que el dropdown muestra las opciones correctas
4. Crear un documento de prueba
5. Verificar que aparece en la tabla
6. Probar cada boton de acciones (PDF, ver, editar, firmas, publicar)
7. Verificar que se publica correctamente en reportList

---

## Ejemplo Completo: 1.1.5 Identificacion Alto Riesgo

### Cambios Realizados

1. **Vista creada:** `app/Views/documentacion/_tipos/identificacion_alto_riesgo.php`

2. **DocumentacionController.php:**
   - Linea ~303: Agregado `'identificacion_alto_riesgo'` al array
   - Linea ~327-329: Agregado filtro `whereIn` para tipo_documento
   - Linea ~423-428: Agregada deteccion de tipo de carpeta (PRIORIDAD al inicio)

3. **tabla_documentos_sst.php:**
   - Linea 6: Agregado al array `$tiposConTabla`

4. **acciones_documento.php:**
   - Linea 18: Agregado a `$mapaRutas`
   - Linea 36-37: Agregado `$urlEditar`

5. **Routes.php:**
   - Linea 750: Ruta para ver documento generico

6. **Script SQL:** `app/SQL/agregar_identificacion_alto_riesgo.php`
   - Tipo de documento con 10 secciones
   - 2 firmantes (Responsable SST + Rep. Legal)
   - Plantilla con codigo PR-SST-AR

---

## Tablas de Base de Datos Involucradas

| Tabla | Proposito |
|-------|-----------|
| `tbl_documentos_sst` | Almacena los documentos generados |
| `tbl_doc_tipo_configuracion` | Configuracion de tipos de documento |
| `tbl_doc_secciones_config` | Secciones de cada tipo (para IA) |
| `tbl_doc_firmantes_config` | Firmantes requeridos por tipo |
| `tbl_doc_versiones_sst` | Historial de versiones |
| `tbl_doc_firma_solicitudes` | Solicitudes de firma electronica |
| `tbl_doc_firma_evidencias` | Evidencias de firmas |
| `tbl_doc_plantillas` | Plantillas de documentos |
| `tbl_doc_plantilla_carpeta` | Mapeo plantilla-carpeta |
| `tbl_doc_carpetas` | Estructura de carpetas del cliente |
| `tbl_reporte` | Documentos publicados para cliente |

---

## Paso 8: Templates PDF Especializados (IMPORTANTE)

### El Problema

La funcion `publicarPDF()` en `DocumentosSSTController.php` usa por defecto el template generico `pdf_template.php`. Sin embargo, algunos tipos de documento requieren templates especializados con datos adicionales.

**Ejemplo real:** El documento `presupuesto_sst` se publicaba incompleto porque usaba `pdf_template.php` en lugar de `presupuesto_pdf.php`, perdiendo toda la informacion de items, categorias y totales.

### Documentos con Templates Especializados

| Tipo Documento | Template PDF | Datos Especiales |
|----------------|--------------|------------------|
| `presupuesto_sst` | `presupuesto_pdf.php` | items, categorias, totales, meses |
| Otros genericos | `pdf_template.php` | secciones, firmantes |

### Solucion Implementada

En `DocumentosSSTController::publicarPDF()`, se agrego deteccion de tipo:

```php
// Renderizar HTML según tipo de documento
if ($documento['tipo_documento'] === 'presupuesto_sst') {
    // Presupuesto SST requiere template y datos especializados
    $html = $this->generarHtmlPresupuesto($documento, $cliente, $contexto, $consultor, $logoBase64, $firmasElectronicas, $versiones);
} else {
    // Documentos genéricos
    $html = view('documentos_sst/pdf_template', $data);
}
```

### Cuando Crear un Template Especializado

Crear template especializado si el documento:
- Tiene tablas con calculos (presupuestos, matrices)
- Requiere datos de tablas diferentes a `tbl_documentos_sst`
- Tiene estructura visual muy diferente al template generico
- Necesita graficos o visualizaciones especiales

### Como Agregar un Nuevo Template Especializado

1. **Crear template:** `app/Views/documentos_sst/{tipo}_pdf.php`

2. **Crear metodo helper en DocumentosSSTController:**
```php
protected function generarHtml{Tipo}(
    array $documento,
    array $cliente,
    ?array $contexto,
    ?array $consultor,
    string $logoBase64,
    array $firmasElectronicas,
    array $versiones
): string {
    // Cargar datos especificos del tipo
    $datosEspecificos = $this->db->table('tbl_xxx')
        ->where('id_cliente', $documento['id_cliente'])
        ->get()->getResultArray();

    return view('documentos_sst/{tipo}_pdf', [
        'cliente' => $cliente,
        'documento' => $documento,
        'datosEspecificos' => $datosEspecificos,
        // ... otros datos
    ]);
}
```

3. **Agregar condicion en publicarPDF():**
```php
if ($documento['tipo_documento'] === 'nuevo_tipo') {
    $html = $this->generarHtmlNuevoTipo(...);
} else {
    $html = view('documentos_sst/pdf_template', $data);
}
```

---

## Paso 9: Sistema de Publicacion con Historial

### Funcionamiento

Cada vez que se publica un documento en `tbl_reporte`, se crea un **nuevo registro** manteniendo el historial completo de publicaciones. Esto permite evidenciar la gestion documental.

### Formato del Titulo en reportList

```
{CODIGO} - {TITULO} (v{VERSION}) - Pub. #{NUMERO} {FECHA_HORA}
```

**Ejemplo:**
```
PR-SST-AR-001 - Identificación Alto Riesgo (v1) - Pub. #1 03/02/2026 14:30
PR-SST-AR-001 - Identificación Alto Riesgo (v1) - Pub. #2 03/02/2026 16:45
PR-SST-AR-001 - Identificación Alto Riesgo (v2) - Pub. #3 04/02/2026 09:15
```

### Lugares que Publican a tbl_reporte

| Archivo | Metodo | Descripcion |
|---------|--------|-------------|
| `DocumentosSSTController.php` | `publicarPDF()` | Publicacion manual (boton nube) |
| `DocumentosSSTController.php` | `adjuntarDocumentoFirmado()` | Documento escaneado firmado |
| `DocumentosSSTController.php` | `adjuntarPlanillaAfiliacion()` | Planillas SRL |
| `FirmaElectronicaController.php` | Auto-publicar | Al completar firmas electronicas |

### Codigo de Publicacion con Historial

```php
// Contar publicaciones anteriores de este documento para numerar
$publicacionesAnteriores = $this->db->table('tbl_reporte')
    ->where("titulo_reporte COLLATE utf8mb4_general_ci LIKE '%" . $this->db->escapeLikeString($codigoBusqueda) . "%'", null, false)
    ->where('id_cliente', $documento['id_cliente'])
    ->where('id_detailreport', $idDetailReport)
    ->countAllResults();

$numPublicacion = $publicacionesAnteriores + 1;
$fechaPublicacion = date('d/m/Y H:i');

$tituloReporte = ($documento['codigo'] ?? '') . ' - ' . $documento['titulo']
    . ' (v' . ($documento['version'] ?? '1') . ')'
    . ' - Pub. #' . $numPublicacion . ' ' . $fechaPublicacion;

// Siempre insertar nuevo registro para mantener historial
$this->db->table('tbl_reporte')->insert([
    'titulo_reporte' => $tituloReporte,
    'id_detailreport' => $idDetailReport,
    'id_report_type' => 12, // Reportes SST
    'id_cliente' => $documento['id_cliente'],
    'enlace' => $enlace,
    'estado' => 'CERRADO',
    'observaciones' => 'Publicación #' . $numPublicacion . '. Estado: ' . $estadoDoc,
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
]);
```

---

## Notas Importantes

1. **Orden de condiciones:** En `determinarTipoCarpetaFases()`, las condiciones mas especificas deben ir primero para evitar coincidencias incorrectas.

2. **Nombres consistentes:** Usar el mismo identificador (`tipo_documento`) en todos los archivos.

3. **Tipos de flujo:**
   - `secciones_ia`: Genera secciones con IA
   - `formulario`: Formulario de datos
   - `archivo`: Solo adjuntar archivo

4. **Firmantes disponibles:**
   - `representante_legal`
   - `responsable_sst`
   - `delegado_sst`
   - `vigia_sst`
   - `copasst`

5. **Colores de iconos:**
   - `text-primary`: Azul
   - `text-success`: Verde
   - `text-warning`: Amarillo/Naranja
   - `text-danger`: Rojo
   - `text-info`: Celeste

6. **Templates PDF:** Verificar si el nuevo tipo de documento necesita un template especializado. Si tiene datos de tablas diferentes o estructura visual distinta, crear template propio.

7. **Publicacion con historial:** Todas las publicaciones a `tbl_reporte` deben usar el patron de "siempre insertar" para mantener el historial de evidencias.

---

## Paso 10: Duplicación Carga de Archivos y Enlaces

### Descripción del Patrón

Algunos módulos requieren **doble funcionalidad**:
1. **Generación de procedimientos** (documentos generados con IA)
2. **Carga de archivos/enlaces** (adjuntos como listados, soportes, evidencias)

Este patrón se implementó en el módulo **1.1.5 Identificación de Trabajadores de Alto Riesgo**, tomando como referencia el módulo **1.1.4 Afiliación al Sistema General de Riesgos Laborales**.

### Estructura Visual del Módulo

```
┌─────────────────────────────────────────────────────────────┐
│ 1.1.5 Identificación Trabajadores de Alto Riesgo           │
├─────────────────────────────────────────────────────────────┤
│  [Adjuntar Listado]  [▼ Nuevo Procedimiento]               │
├─────────────────────────────────────────────────────────────┤
│  📋 Tabla de Procedimientos (documentos IA)                │
│     - tipo_documento: identificacion_alto_riesgo           │
├─────────────────────────────────────────────────────────────┤
│  👥 Tabla de Listados de Trabajadores (archivos/enlaces)   │
│     - tipo_documento: listado_trabajadores_alto_riesgo     │
│     - Código: LST-AR-001, LST-AR-002...                    │
└─────────────────────────────────────────────────────────────┘
```

### Archivos Involucrados

| Archivo | Cambio |
|---------|--------|
| `_tipos/identificacion_alto_riesgo.php` | Vista con doble tabla + modal de carga |
| `DocumentacionController.php` | Filtro `whereIn` para múltiples tipos |
| `DocumentosSSTController.php` | Método `adjuntarListadoAltoRiesgo()` |
| `Routes.php` | Ruta POST para adjuntar |

### Implementación Paso a Paso

#### 10.1 Modificar Vista de Tipo

En la vista `_tipos/{modulo}.php`, separar documentos al inicio:

```php
<?php
// Separar documentos por tipo
$procedimientos = [];
$listadosAdjuntos = [];
$docsExistentesTipos = [];

if (!empty($documentosSSTAprobados)) {
    foreach ($documentosSSTAprobados as $d) {
        if ($d['tipo_documento'] === 'listado_trabajadores_alto_riesgo') {
            $listadosAdjuntos[] = $d;
        } else {
            $procedimientos[] = $d;
        }
        if ($d['anio'] == date('Y')) {
            $docsExistentesTipos[$d['tipo_documento']] = true;
        }
    }
}
?>
```

#### 10.2 Agregar Botones en Card Principal

```php
<div class="col-md-6 text-end">
    <!-- Botón Adjuntar -->
    <button type="button" class="btn btn-success me-2"
            data-bs-toggle="modal" data-bs-target="#modalAdjuntar">
        <i class="bi bi-cloud-upload me-1"></i>Adjuntar Listado
    </button>

    <!-- Dropdown Nuevo Procedimiento -->
    <div class="dropdown d-inline-block">
        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <i class="bi bi-plus-lg me-1"></i>Nuevo Procedimiento
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <!-- Opciones de documentos IA -->
        </ul>
    </div>
</div>
```

#### 10.3 Crear Tabla para Archivos Adjuntos

```php
<!-- Tabla de Listados Adjuntados -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-warning text-dark">
        <h6 class="mb-0">
            <i class="bi bi-people-fill me-2"></i>Listados de Trabajadores
        </h6>
    </div>
    <div class="card-body">
        <?php if (!empty($listadosAdjuntos)): ?>
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th>Año</th>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($listadosAdjuntos as $listado): ?>
                        <?php
                        $esEnlace = !empty($listado['url_externa']);
                        $urlArchivo = $esEnlace ? $listado['url_externa'] : ($listado['archivo_pdf'] ?? '#');
                        ?>
                        <tr>
                            <td><code><?= esc($listado['codigo'] ?? 'LST-XX') ?></code></td>
                            <td><strong><?= esc($listado['titulo']) ?></strong></td>
                            <td><span class="badge bg-secondary"><?= esc($listado['anio']) ?></span></td>
                            <td><small><?= date('d/m/Y', strtotime($listado['created_at'])) ?></small></td>
                            <td>
                                <?php if ($esEnlace): ?>
                                    <span class="badge bg-primary"><i class="bi bi-link-45deg me-1"></i>Enlace</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><i class="bi bi-file-earmark me-1"></i>Archivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= esc($urlArchivo) ?>" class="btn btn-outline-primary" target="_blank">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if (!$esEnlace): ?>
                                    <a href="<?= esc($urlArchivo) ?>" class="btn btn-danger" download>
                                        <i class="bi bi-download"></i>
                                    </a>
                                    <?php endif; ?>
                                    <a href="<?= base_url('documentos-sst/publicar-pdf/' . $listado['id_documento']) ?>"
                                       class="btn btn-outline-dark" onclick="return confirm('¿Publicar?')">
                                        <i class="bi bi-cloud-upload"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="text-center py-4">
                <i class="bi bi-people text-muted" style="font-size: 2.5rem;"></i>
                <p class="text-muted mt-2 mb-0">No hay listados adjuntados aún.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
```

#### 10.4 Crear Modal de Carga con Toggle Archivo/Enlace

```php
<!-- Modal para Adjuntar -->
<div class="modal fade" id="modalAdjuntar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="bi bi-cloud-upload me-2"></i>Adjuntar Listado
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('documentos-sst/adjuntar-listado-alto-riesgo') ?>"
                  method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id_cliente" value="<?= $cliente['id_cliente'] ?>">
                    <input type="hidden" name="id_carpeta" value="<?= $carpeta['id_carpeta'] ?>">

                    <!-- Toggle Archivo/Enlace -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de carga</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="tipo_carga"
                                   id="tipoCargaArchivo" value="archivo" checked>
                            <label class="btn btn-outline-warning" for="tipoCargaArchivo">
                                <i class="bi bi-file-earmark-arrow-up me-1"></i>Subir Archivo
                            </label>
                            <input type="radio" class="btn-check" name="tipo_carga"
                                   id="tipoCargaEnlace" value="enlace">
                            <label class="btn btn-outline-warning" for="tipoCargaEnlace">
                                <i class="bi bi-link-45deg me-1"></i>Pegar Enlace
                            </label>
                        </div>
                    </div>

                    <!-- Campo Archivo -->
                    <div class="mb-3" id="campoArchivo">
                        <label class="form-label">Archivo (Excel, PDF, Imagen)</label>
                        <input type="file" class="form-control" name="archivo_listado"
                               accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx">
                        <div class="form-text">Máximo: 10MB</div>
                    </div>

                    <!-- Campo Enlace (oculto por defecto) -->
                    <div class="mb-3 d-none" id="campoEnlace">
                        <label class="form-label">Enlace (Google Drive, OneDrive)</label>
                        <input type="url" class="form-control" name="url_externa"
                               placeholder="https://drive.google.com/...">
                    </div>

                    <!-- Descripción -->
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <input type="text" class="form-control" name="descripcion" required>
                    </div>

                    <!-- Año -->
                    <div class="mb-3">
                        <label class="form-label">Año</label>
                        <select class="form-select" name="anio" required>
                            <?php for ($y = 2026; $y <= 2030; $y++): ?>
                                <option value="<?= $y ?>" <?= $y == date('Y') ? 'selected' : '' ?>>
                                    <?= $y ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-cloud-upload me-1"></i>Adjuntar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Toggle entre archivo y enlace
document.querySelectorAll('input[name="tipo_carga"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const isArchivo = this.value === 'archivo';
        document.getElementById('campoArchivo').classList.toggle('d-none', !isArchivo);
        document.getElementById('campoEnlace').classList.toggle('d-none', isArchivo);
    });
});
</script>
```

#### 10.5 Modificar DocumentacionController - Filtro whereIn

```php
} elseif ($tipoCarpetaFases === 'identificacion_alto_riesgo') {
    // 1.1.5: Identificación de trabajadores de alto riesgo + listados adjuntos
    $queryDocs->whereIn('tipo_documento', [
        'identificacion_alto_riesgo',           // Procedimiento IA
        'listado_trabajadores_alto_riesgo'      // Archivos adjuntos
    ]);
}
```

#### 10.6 Crear Método en DocumentosSSTController

```php
/**
 * Adjuntar listado de trabajadores de alto riesgo (archivo o enlace)
 */
public function adjuntarListadoAltoRiesgo()
{
    $idCliente = $this->request->getPost('id_cliente');
    $idCarpeta = $this->request->getPost('id_carpeta');
    $descripcion = $this->request->getPost('descripcion');
    $anio = $this->request->getPost('anio') ?? date('Y');
    $observaciones = $this->request->getPost('observaciones') ?? '';
    $tipoCarga = $this->request->getPost('tipo_carga') ?? 'archivo';

    // Generar código consecutivo
    $ultimoListado = $this->db->table('tbl_documentos_sst')
        ->where('id_cliente', $idCliente)
        ->where('tipo_documento', 'listado_trabajadores_alto_riesgo')
        ->orderBy('id_documento', 'DESC')
        ->get()->getRowArray();

    $consecutivo = 1;
    if ($ultimoListado && preg_match('/LST-AR-(\d+)/', $ultimoListado['codigo'], $m)) {
        $consecutivo = intval($m[1]) + 1;
    }
    $codigo = 'LST-AR-' . str_pad($consecutivo, 3, '0', STR_PAD_LEFT);

    $urlExterna = null;
    $archivoPdf = null;

    if ($tipoCarga === 'enlace') {
        $urlExterna = $this->request->getPost('url_externa');
    } else {
        $archivo = $this->request->getFile('archivo_listado');
        if ($archivo && $archivo->isValid()) {
            $nombreArchivo = $archivo->getRandomName();
            $archivo->move(FCPATH . 'uploads/listados_alto_riesgo/', $nombreArchivo);
            $archivoPdf = base_url('uploads/listados_alto_riesgo/' . $nombreArchivo);
        }
    }

    // Insertar en tbl_documentos_sst
    $this->db->table('tbl_documentos_sst')->insert([
        'id_cliente' => $idCliente,
        'id_carpeta' => $idCarpeta,
        'tipo_documento' => 'listado_trabajadores_alto_riesgo',
        'titulo' => $descripcion,
        'codigo' => $codigo,
        'anio' => $anio,
        'version' => '1',
        'estado' => 'vigente',
        'archivo_pdf' => $archivoPdf,
        'url_externa' => $urlExterna,
        'observaciones' => $observaciones,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);

    return redirect()->back()->with('success', 'Listado adjuntado correctamente: ' . $codigo);
}
```

#### 10.7 Agregar Ruta en Routes.php

```php
$routes->post('/documentos-sst/adjuntar-listado-alto-riesgo', 'DocumentosSSTController::adjuntarListadoAltoRiesgo');
```

### Ejemplo Implementado: 1.1.5

| Componente | Valor |
|------------|-------|
| Procedimiento IA | `identificacion_alto_riesgo` |
| Listados adjuntos | `listado_trabajadores_alto_riesgo` |
| Código listados | `LST-AR-001`, `LST-AR-002`... |
| Carpeta uploads | `uploads/listados_alto_riesgo/` |
| Colores | Header warning (amarillo) |

### Referencia: 1.1.4 Afiliación SRL

El módulo 1.1.4 usa el mismo patrón pero solo para **carga de archivos** (sin procedimientos IA):

| Componente | Valor |
|------------|-------|
| Solo archivos | `planilla_afiliacion_srl` |
| Código planillas | `PLA-SRL-001`... |
| Carpeta uploads | `uploads/planillas_srl/` |
| Colores | Header success (verde) |

### Checklist de Implementación

- [ ] Vista separar documentos por tipo al inicio
- [ ] Botón "Adjuntar" + Dropdown "Nuevo Procedimiento"
- [ ] Tabla de procedimientos (componente existente)
- [ ] Tabla de archivos adjuntos (nueva)
- [ ] Modal con toggle archivo/enlace
- [ ] Script JS para toggle
- [ ] Filtro `whereIn` en DocumentacionController
- [ ] Método `adjuntar{Tipo}()` en DocumentosSSTController
- [ ] Ruta POST en Routes.php
- [ ] Crear carpeta uploads si no existe

---

## Soporte

Para dudas sobre este proceso, revisar los archivos de referencia:
- `app/Views/documentacion/_tipos/responsabilidades_sgsst.php`
- `app/Views/documentacion/_tipos/identificacion_alto_riesgo.php`
- `app/Views/documentacion/_tipos/afiliacion_srl.php`
- `app/SQL/agregar_identificacion_alto_riesgo.php`

Fecha de creación: 2026-02-02
Última actualización: 2026-02-03
Autor: Claude Code
