# Arquitectura Escalable de Documentos SST v2.0

## Resumen

Esta arquitectura permite manejar **100+ tipos de documentos** sin modificar código PHP. La configuración se almacena en base de datos y se accede a través de servicios centralizados.

## Estructura de Archivos

```
app/
├── Services/
│   ├── DocumentoConfigService.php    # Lee configuración de BD
│   └── FirmanteService.php           # Maneja firmantes
├── Views/documentos_sst/
│   ├── _components/
│   │   ├── firmas_documento.php      # Componente de firmas
│   │   ├── seccion_documento.php     # Componente de sección
│   │   └── tabla_dinamica.php        # Componente de tabla
│   └── documento_generico.php        # Vista principal (futuro)
└── SQL/
    ├── crear_arquitectura_documentos_sst.sql
    └── ejecutar_arquitectura_documentos.php
```

## Tablas de Base de Datos

### 1. `tbl_doc_tipo_configuracion`
Almacena los tipos de documento (reemplaza `TIPOS_DOCUMENTO`).

| Campo | Descripción |
|-------|-------------|
| `tipo_documento` | Identificador único (ej: `procedimiento_control_documental`) |
| `nombre` | Nombre para mostrar |
| `estandar` | Estándar de Res. 0312 (ej: `2.5.1`) |
| `flujo` | Tipo de generación: `secciones_ia`, `formulario`, `carga_archivo` |
| `categoria` | Para agrupar: `procedimientos`, `programas`, `formatos` |

### 2. `tbl_doc_secciones_config`
Define las secciones de cada tipo de documento.

| Campo | Descripción |
|-------|-------------|
| `seccion_key` | Identificador (ej: `objetivo`, `alcance`) |
| `nombre` | Nombre de la sección |
| `prompt_ia` | Prompt para generación con IA |
| `tipo_contenido` | `texto`, `tabla_dinamica`, `mixto` |
| `tabla_dinamica_tipo` | Si aplica: `tipos_documento`, `plantillas`, etc. |

### 3. `tbl_doc_firmantes_config`
Define quiénes firman cada tipo de documento.

| Campo | Descripción |
|-------|-------------|
| `firmante_tipo` | `representante_legal`, `responsable_sst`, `delegado_sst`, etc. |
| `columna_encabezado` | Texto del encabezado (ej: `Elaboró / Responsable del SG-SST`) |
| `orden` | Posición en la tabla de firmas |
| `mostrar_licencia` | Si muestra número de licencia SST |

### 4. `tbl_doc_tablas_dinamicas`
Define las tablas dinámicas disponibles.

| Campo | Descripción |
|-------|-------------|
| `tabla_key` | Identificador (ej: `tipos_documento`) |
| `query_base` | SQL para obtener datos |
| `columnas` | JSON con definición de columnas |

## Uso de Servicios

### DocumentoConfigService

```php
use App\Services\DocumentoConfigService;

$configService = new DocumentoConfigService();

// Obtener configuración completa de un tipo
$config = $configService->obtenerTipoDocumento('procedimiento_control_documental');
// Retorna: nombre, secciones, firmantes, etc.

// Obtener solo los firmantes
$firmantes = $configService->obtenerFirmantes('procedimiento_control_documental');
// Retorna: ['representante_legal', 'responsable_sst']

// Obtener secciones
$secciones = $configService->obtenerSecciones('procedimiento_control_documental');

// Obtener prompt de IA para una sección
$prompt = $configService->obtenerPromptSeccion('procedimiento_control_documental', 'objetivo');

// Obtener datos de tabla dinámica
$tiposDoc = $configService->obtenerDatosTablaDinamica('tipos_documento');
$listado = $configService->obtenerDatosTablaDinamica('listado_maestro', $idCliente);
```

### FirmanteService

```php
use App\Services\FirmanteService;

$firmanteService = new FirmanteService();

// Obtener firmantes con datos completos para renderizar
$firmantes = $firmanteService->obtenerFirmantesDocumento(
    'procedimiento_control_documental',
    $contexto,      // Contexto SST del cliente
    $cliente,       // Datos del cliente
    $consultor,     // Datos del consultor
    $firmasElectronicas  // Firmas existentes
);

// Retorna array con:
// - tipo, nombre, cargo, cedula, licencia
// - firma_imagen (base64), firma_archivo (ruta)
// - columna_encabezado, mostrar_licencia, etc.
```

## Uso de Componentes de Vista

### Componente de Firmas

```php
<?= view('documentos_sst/_components/firmas_documento', [
    'firmantes' => $firmantes,  // Del FirmanteService
    'titulo' => 'FIRMAS DE APROBACIÓN',
    'formato' => 'web'  // o 'pdf'
]) ?>
```

### Componente de Tabla Dinámica

```php
<?= view('documentos_sst/_components/tabla_dinamica', [
    'datos' => $tiposDocumento,
    'tipo' => 'tipos_documento',
    'formato' => 'web'
]) ?>
```

## Agregar un Nuevo Tipo de Documento

### Paso 1: Insertar en BD

```sql
-- 1. Crear el tipo
INSERT INTO tbl_doc_tipo_configuracion
(tipo_documento, nombre, descripcion, estandar, flujo, categoria)
VALUES
('matriz_peligros', 'Matriz de Identificación de Peligros',
 'Identifica peligros, evalúa riesgos y establece controles',
 '4.1.2', 'secciones_ia', 'matrices');

-- 2. Definir secciones
SET @id = (SELECT id_tipo_config FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'matriz_peligros');

INSERT INTO tbl_doc_secciones_config
(id_tipo_config, numero, nombre, seccion_key, tipo_contenido, orden, prompt_ia)
VALUES
(@id, 1, 'Objetivo', 'objetivo', 'texto', 1, 'Genera el objetivo de la matriz...'),
(@id, 2, 'Metodología', 'metodologia', 'texto', 2, 'Describe la metodología GTC-45...');

-- 3. Definir firmantes
INSERT INTO tbl_doc_firmantes_config
(id_tipo_config, firmante_tipo, columna_encabezado, orden)
VALUES
(@id, 'responsable_sst', 'Elaboró / Responsable del SG-SST', 1),
(@id, 'copasst', 'Revisó / COPASST', 2),
(@id, 'representante_legal', 'Aprobó / Representante Legal', 3);
```

### Paso 2: Usar en el Código

```php
// El controlador ya funciona genéricamente
$configService = new DocumentoConfigService();
$config = $configService->obtenerTipoDocumento('matriz_peligros');
// ¡Listo! No hay que modificar código
```

## Migración Gradual

La arquitectura es **compatible hacia atrás**:

1. `DocumentoConfigService` tiene fallback a constantes legacy
2. Las vistas existentes siguen funcionando
3. Puedes migrar documento por documento

### Orden recomendado de migración:

1. ✅ Ejecutar SQL de creación de tablas
2. ⏳ Insertar configuración de documentos existentes en BD
3. ⏳ Modificar controlador para usar servicios
4. ⏳ Migrar vistas a componentes reutilizables
5. ⏳ Eliminar constantes y código legacy

## Beneficios

| Antes | Después |
|-------|---------|
| Constante PHP hardcodeada | Configuración en BD |
| Vista específica por documento | Componentes reutilizables |
| Lógica de firmantes en cada vista | Servicio centralizado |
| Modificar código para nuevo doc | INSERT en BD |
| ~2600 líneas en controlador | ~500 líneas + servicios |

## Comandos Útiles

```bash
# Ejecutar migración desde navegador
http://localhost/enterprisesst/public/index.php/sql-runner?file=ejecutar_arquitectura_documentos

# Ver configuración de un tipo
SELECT * FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'procedimiento_control_documental';

# Ver secciones
SELECT * FROM tbl_doc_secciones_config WHERE id_tipo_config = 1 ORDER BY orden;

# Ver firmantes
SELECT * FROM tbl_doc_firmantes_config WHERE id_tipo_config = 1 ORDER BY orden;
```
