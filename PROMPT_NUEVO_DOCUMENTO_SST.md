# Prompt para Crear Nuevo Documento SST

---

## ARQUITECTURA ESCALABLE v2.0 - Basada en Base de Datos

**Fecha de actualización:** Febrero 2026

A partir de esta versión, **TODOS los tipos de documento se configuran en la base de datos**, no en código PHP. Esto permite agregar nuevos documentos sin modificar el controlador.

### Tablas de Configuración

| Tabla | Propósito |
|-------|-----------|
| `tbl_doc_tipo_configuracion` | Define tipos de documento (nombre, flujo, categoría, estándar) |
| `tbl_doc_secciones_config` | Define secciones por tipo (número, nombre, key, prompts IA) |
| `tbl_doc_firmantes_config` | Define firmantes por tipo (quién firma, orden, roles) |
| `tbl_doc_tablas_dinamicas` | Define tablas que se insertan automáticamente en secciones |

### Servicios PHP

| Servicio | Propósito |
|----------|-----------|
| `DocumentoConfigService` | Lee configuración de tipos desde BD |
| `FirmanteService` | Maneja lógica de firmantes centralizada |

---

## PROHIBICIONES ABSOLUTAS

### REGLA #1: NUNCA CREAR CONTROLADORES Pz* o Hz*

```
❌ PROHIBIDO: Crear app/Controllers/PznuevoDocumentoController.php
❌ PROHIBIDO: Crear app/Controllers/HznuevoDocumentoController.php
```

**Si necesitas crear un nuevo documento, SIEMPRE usa la BD + DocumentosSSTController.php**

### REGLA #2: NO HARDCODEAR TIPOS DE DOCUMENTO

```
❌ PROHIBIDO: Agregar tipos en la constante TIPOS_DOCUMENTO del controlador
❌ PROHIBIDO: Hardcodear códigos de documento en PHP
✅ CORRECTO: Insertar configuración en tbl_doc_tipo_configuracion
✅ CORRECTO: Insertar secciones en tbl_doc_secciones_config
✅ CORRECTO: Insertar firmantes en tbl_doc_firmantes_config
```

### REGLA #3: LA CONSTANTE TIPOS_DOCUMENTO ES LEGACY

```php
// Esta constante está OBSOLETA - NO AGREGAR NADA AQUÍ
public const TIPOS_DOCUMENTO = [
    // @deprecated - Solo para compatibilidad temporal
    // Los nuevos documentos se configuran en tbl_doc_tipo_configuracion
];
```

### REGLA #4: RESPETAR ESTILOS ESTANDARIZADOS

```
❌ PROHIBIDO: Inventar nuevos estilos CSS para PDF/Word/Web
❌ PROHIBIDO: Cambiar colores, fuentes o dimensiones sin consultar la documentación
✅ CORRECTO: Usar los estilos documentados en docs/MODULO_NUMERALES_SGSST/
✅ CORRECTO: Consultar snippets reutilizables de cada sección
```

---

## DOCUMENTACIÓN DE ESTILOS ESTANDARIZADOS

**IMPORTANTE:** Antes de crear cualquier vista de documento, consulta la documentación de estilos correspondiente.

### Archivos de Referencia de Estilos

| Archivo | Descripción |
|---------|-------------|
| **VISTAS WEB** | |
| `docs/MODULO_NUMERALES_SGSST/AA_ WEB.md` | Estilos para vistas web (Bootstrap, encabezado, secciones, firmas) |
| **EXPORTACIÓN PDF** | |
| `docs/MODULO_NUMERALES_SGSST/AA_PDF_ENCABEZADO.md` | Encabezado PDF (logo, código, versión, fecha) |
| `docs/MODULO_NUMERALES_SGSST/AA_PDF_CUERPO_DOCUMENTO.md` | Cuerpo PDF (secciones, párrafos, listas, tablas) |
| `docs/MODULO_NUMERALES_SGSST/AA_PDF_CONTROL_CAMBIOS.md` | Tabla Control de Cambios PDF |
| `docs/MODULO_NUMERALES_SGSST/AA_PDF_FIRMAS.md` | Sección Firmas PDF (1, 2, 3 firmantes, física) |
| **EXPORTACIÓN WORD** | |
| `docs/MODULO_NUMERALES_SGSST/AA_WORD_ENCABEZADO.md` | Encabezado Word (MSO, dimensiones, colores) |
| `docs/MODULO_NUMERALES_SGSST/AA_WORD_CUERPO_DOCUMENTO.md` | Cuerpo Word (directivas MSO, line-height 1.0) |
| `docs/MODULO_NUMERALES_SGSST/AA_WORD_CONTROL_CAMBIOS.md` | Tabla Control de Cambios Word |
| `docs/MODULO_NUMERALES_SGSST/AA_WORD_FIRMAS.md` | Sección Firmas Word |
| **LÓGICA DE NEGOCIO** | |
| `docs/MODULO_NUMERALES_SGSST/SISTEMA_FIRMAS_DOCUMENTOS.md` | Sistema completo de firmas electrónicas |
| `docs/MODULO_NUMERALES_SGSST/NUMERALES_CON_PROGRAMAS.md` | Qué numerales requieren Programas con PTA e Indicadores |
| `docs/MODULO_NUMERALES_SGSST/TROUBLESHOOTING_GENERACION_IA.md` | Solución de problemas comunes |

### Comparación Rápida de Estilos

| Aspecto | WEB | PDF | WORD |
|---------|-----|-----|------|
| **Framework CSS** | Bootstrap 5.3 | CSS puro | CSS + MSO |
| **Fuente** | System/Bootstrap | DejaVu Sans | Arial |
| **Unidades** | rem | pt | pt/px |
| **Line-height** | 1.7 | 1.2 | 1.0 |
| **Gradientes** | Sí | No | No |
| **Border-radius** | Sí | No | No |
| **Imágenes firma** | URL directa | Base64 | No |
| **Título sección** | 1.1rem #0d6efd | 11pt #0d6efd | 11pt #0d6efd |
| **Bordes tabla** | #dee2e6 | #999 | #999 |
| **TH Control Cambios** | #e9ecef | #e9ecef | #e9ecef |
| **Barra Firmas** | Gradiente verde | #198754 sólido | #198754 sólido |

### Dimensiones de Encabezado

| Elemento | WEB | PDF | WORD |
|----------|-----|-----|------|
| Ancho Logo (celda) | 150px | 120px | 80px |
| Max img width | 130px | 100px | 70px |
| Max img height | 70px | 60px | 45px |
| Ancho Info | 170px | 140px | 120px |
| Font títulos | 0.85rem | 10pt | 9pt |
| Font info | 0.75rem | 8pt | 8pt |

---

## ARQUITECTURA DE PERMISOS: CONSULTOR vs CLIENTE

| Aspecto | CONSULTOR | CLIENTE |
|---------|-----------|---------|
| **URL Base** | `/documentacion/carpeta/{id}` | `/client/mis-documentos-sst/carpeta/{id}` |
| **Controlador** | `DocumentacionController` | `ClienteDocumentosSstController` |
| **Crear documentos** | SI | NO |
| **Editar documentos** | SI | NO |
| **Ver documentos** | SI (todos los estados) | SI (solo aprobados/firmados) |
| **Descargar PDF** | SI | SI |

---

## INSTRUCCIONES PARA CLAUDE - CREAR NUEVO DOCUMENTO

```
Necesito crear un nuevo tipo de documento SST para el aplicativo EnterpriseSST.

ANTES DE ESCRIBIR CUALQUIER CÓDIGO, haz lo siguiente:

### FASE 1: Recopilar Información del Documento

Pregúntame UNA POR UNA:

**Pregunta 1 - Identidad:**
- ¿Cómo se llama el documento? (ej: "Programa de Vigilancia Epidemiológica")
- ¿Cuál es el identificador interno? (ej: "programa_vigilancia_epidemiologica")
- ¿Cuál es el código de plantilla? (ej: "PRG-VEP")

**Pregunta 2 - Clasificación:**
- ¿En qué estándar de la Resolución 0312 se ubica? (ej: "3.1.4")
- ¿Qué categoría tiene? (procedimientos, programas, políticas, planes, matrices, reglamentos)
- ¿Qué flujo usa? (secciones_ia, formulario, carga_archivo)

**Pregunta 3 - Secciones (si flujo=secciones_ia):**
- Propón una lista de secciones basándote en normativa colombiana
- Cada sección necesita: número, nombre, key, tipo_contenido
- Pregúntame cuáles se alimentan de tablas dinámicas vs texto generado por IA

**Pregunta 4 - Firmantes:**
- ¿Quiénes firman el documento?
- Opciones: representante_legal, responsable_sst, consultor_sst, delegado_sst, vigia_sst, copasst
- Ver docs/MODULO_NUMERALES_SGSST/SISTEMA_FIRMAS_DOCUMENTOS.md para tipos de firma

**Pregunta 5 - Dependencias:**
- ¿Requiere fases previas? (cronograma, PTA, indicadores, responsables)
- ¿Es un PROGRAMA que sincroniza actividades e indicadores con BD?
  (Ver docs/MODULO_NUMERALES_SGSST/NUMERALES_CON_PROGRAMAS.md)
- ¿O es independiente?

### FASE 2: Implementar EN BLOQUE

---

#### BLOQUE 1: BASE DE DATOS (Ejecutar en LOCAL y PRODUCCIÓN)

Crear script `app/SQL/agregar_[nombre_documento].php`:

```php
<?php
/**
 * Script para agregar tipo de documento: [NOMBRE]
 * Ejecutar: php app/SQL/agregar_[nombre_documento].php
 */

$conexiones = [
    'local' => [
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'empresas_sst',
        'username' => 'root',
        'password' => '',
        'ssl' => false
    ],
    'produccion' => [
        'host' => getenv('DB_PROD_HOST') ?: 'TU_HOST',
        'port' => getenv('DB_PROD_PORT') ?: 25060,
        'database' => getenv('DB_PROD_DATABASE') ?: 'empresas_sst',
        'username' => getenv('DB_PROD_USERNAME') ?: 'TU_USUARIO',
        'password' => getenv('DB_PROD_PASSWORD') ?: 'TU_PASSWORD',
        'ssl' => true
    ]
];

// SQL para tipo de documento
$sqlTipo = <<<'SQL'
INSERT INTO tbl_doc_tipo_configuracion
(tipo_documento, nombre, descripcion, estandar, flujo, categoria, icono, orden)
VALUES
('mi_nuevo_documento',
 'Mi Nuevo Documento',
 'Descripción del documento',
 '1.2.3',
 'secciones_ia',
 'procedimientos',
 'bi-file-text',
 10)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), updated_at = NOW();
SQL;

// SQL para secciones
$sqlSecciones = <<<'SQL'
INSERT INTO tbl_doc_secciones_config
(id_tipo_config, numero, nombre, seccion_key, tipo_contenido, tabla_dinamica_tipo, orden, prompt_ia)
SELECT
    tc.id_tipo_config,
    s.numero,
    s.nombre,
    s.seccion_key,
    s.tipo_contenido,
    s.tabla_dinamica_tipo,
    s.orden,
    s.prompt_ia
FROM tbl_doc_tipo_configuracion tc
CROSS JOIN (
    SELECT 1 as numero, 'Objetivo' as nombre, 'objetivo' as seccion_key, 'texto' as tipo_contenido, NULL as tabla_dinamica_tipo, 1 as orden, 'Genera el objetivo del documento.' as prompt_ia
    UNION SELECT 2, 'Alcance', 'alcance', 'texto', NULL, 2, 'Define el alcance del documento.'
    -- Agregar más secciones aquí...
) s
WHERE tc.tipo_documento = 'mi_nuevo_documento'
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), prompt_ia = VALUES(prompt_ia);
SQL;

// SQL para firmantes
$sqlFirmantes = <<<'SQL'
INSERT INTO tbl_doc_firmantes_config
(id_tipo_config, firmante_tipo, rol_display, columna_encabezado, orden, mostrar_licencia)
SELECT
    tc.id_tipo_config,
    f.firmante_tipo,
    f.rol_display,
    f.columna_encabezado,
    f.orden,
    f.mostrar_licencia
FROM tbl_doc_tipo_configuracion tc
CROSS JOIN (
    SELECT 'responsable_sst' as firmante_tipo, 'Elaboró' as rol_display, 'Elaboró / Responsable del SG-SST' as columna_encabezado, 1 as orden, 1 as mostrar_licencia
    UNION SELECT 'representante_legal', 'Aprobó', 'Aprobó / Representante Legal', 2, 0
) f
WHERE tc.tipo_documento = 'mi_nuevo_documento'
ON DUPLICATE KEY UPDATE rol_display = VALUES(rol_display);
SQL;

// ... código para ejecutar en ambos entornos ...
```

---

#### BLOQUE 2: PLANTILLA EN BD

```sql
-- Agregar plantilla con código
INSERT INTO tbl_doc_plantillas (
    id_tipo, nombre, codigo_sugerido, tipo_documento, version, activo
) VALUES (
    3, 'Mi Nuevo Documento', 'MND-DOC', 'mi_nuevo_documento', '001', 1
)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- Mapear a carpeta del estándar
INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
VALUES ('MND-DOC', '1.2.3')
ON DUPLICATE KEY UPDATE codigo_carpeta = VALUES(codigo_carpeta);
```

---

#### BLOQUE 3: VISTA DE TIPO EN CARPETA

**Crear archivo:** `app/Views/documentacion/_tipos/mi_nuevo_documento.php`

```php
<?php
/**
 * Vista de Tipo: Mi Nuevo Documento
 * Código: 1.2.3
 */
?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h5><i class="bi bi-file-text me-2"></i><?= esc($carpeta['nombre']) ?></h5>
        <p class="text-muted">Descripción del documento y su propósito.</p>

        <?php if (empty($documentosSSTAprobados)): ?>
            <a href="<?= base_url('documentos/generar/mi_nuevo_documento/' . $cliente['id_cliente']) ?>"
               class="btn btn-success">
                <i class="bi bi-plus-circle me-1"></i>Generar Documento
            </a>
        <?php endif; ?>
    </div>
</div>

<?= view('documentacion/_components/panel_fases', ['fasesInfo' => $fasesInfo ?? null]) ?>
<?= view('documentacion/_components/tabla_documentos', ['documentosSSTAprobados' => $documentosSSTAprobados ?? []]) ?>
```

---

#### BLOQUE 4: DETECCIÓN EN CONTROLADOR

**Agregar en `DocumentacionController::determinarTipo()`:**

```php
protected function determinarTipo(array $carpeta): ?string
{
    $codigo = strtolower($carpeta['codigo'] ?? '');

    // Agregar detección del nuevo tipo
    if ($codigo === '1.2.3') return 'mi_nuevo_documento';

    // ... otros tipos ...
    return null;
}
```

---

#### BLOQUE 5: RUTAS (si se necesita vista previa específica)

**Agregar en `app/Config/Routes.php`:**

```php
// Rutas del nuevo documento
$routes->get('documentos-sst/(:num)/mi-nuevo-documento/(:num)',
    'DocumentosSSTController::miNuevoDocumento/$1/$2');
```

---

#### BLOQUE 6: MAPEO CLIENTE

**Agregar en `ClienteDocumentosSstController::mapearPlantillaATipoDocumento()`:**

```php
$mapa = [
    'PRG-CAP' => 'programa_capacitacion',
    'MND-DOC' => 'mi_nuevo_documento',  // <-- Agregar aquí
];
```

---

#### BLOQUE 7: CLASE PHP EN DOCUMENTOSSTTYPES (⚠️ OBLIGATORIO)

**Crear archivo:** `app/Libraries/DocumentosSSTTypes/MiNuevoDocumento.php`

```php
<?php
namespace App\Libraries\DocumentosSSTTypes;

use App\Services\DocumentoConfigService;

/**
 * Clase MiNuevoDocumento
 * Documento simple que lee configuración desde BD
 */
class MiNuevoDocumento extends AbstractDocumentoSST
{
    protected ?DocumentoConfigService $configService = null;

    protected function getConfigService(): DocumentoConfigService
    {
        if ($this->configService === null) {
            $this->configService = new DocumentoConfigService();
        }
        return $this->configService;
    }

    public function getTipoDocumento(): string
    {
        return 'mi_nuevo_documento'; // Debe coincidir con BD
    }

    public function getNombre(): string
    {
        return 'Mi Nuevo Documento';
    }

    public function getDescripcion(): string
    {
        return 'Descripcion del documento';
    }

    public function getEstandar(): ?string
    {
        return '1.2.3';
    }

    public function getSecciones(): array
    {
        $seccionesBD = $this->getConfigService()->obtenerSecciones($this->getTipoDocumento());
        if (!empty($seccionesBD)) {
            $secciones = [];
            foreach ($seccionesBD as $s) {
                $secciones[] = [
                    'numero' => (int)($s['numero'] ?? 0),
                    'nombre' => $s['nombre'] ?? '',
                    'key' => $s['key'] ?? $s['seccion_key'] ?? ''
                ];
            }
            return $secciones;
        }
        // Fallback si BD no tiene secciones
        return [
            ['numero' => 1, 'nombre' => 'Objetivo', 'key' => 'objetivo'],
            ['numero' => 2, 'nombre' => 'Alcance', 'key' => 'alcance'],
        ];
    }

    public function getFirmantesRequeridos(int $estandares): array
    {
        return ['responsable_sst', 'representante_legal'];
    }

    public function getPromptParaSeccion(string $seccionKey, int $estandares): string
    {
        $promptBD = $this->getConfigService()->obtenerPromptSeccion($this->getTipoDocumento(), $seccionKey);
        if (!empty($promptBD)) {
            return $promptBD;
        }
        return "Genera el contenido para la seccion '{$seccionKey}'.";
    }

    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        return parent::getContenidoEstatico($seccionKey, $cliente, $contexto, $estandares, $anio);
    }
}
```

**IMPORTANTE:** Esta clase es OBLIGATORIA. Sin ella, la generación con IA mostrará "[Seccion no definida]".

---

#### BLOQUE 8: REGISTRO EN FACTORY (⚠️ OBLIGATORIO)

**Agregar en `app/Libraries/DocumentosSSTTypes/DocumentoSSTFactory.php`:**

```php
private static array $tiposRegistrados = [
    'programa_capacitacion' => ProgramaCapacitacion::class,
    'procedimiento_control_documental' => ProcedimientoControlDocumental::class,
    // ... otros tipos ...
    'mi_nuevo_documento' => MiNuevoDocumento::class,  // <-- AGREGAR AQUÍ
];
```

**IMPORTANTE:** Sin este registro, el Factory no encontrará la clase y la generación fallará.

---

### FASE 3: Crear Vistas con Estilos Estándar

**CRÍTICO:** Las vistas de documento DEBEN usar los estilos documentados.

#### Vista Web (`documento_generico.php` o específica)

Consultar: `docs/MODULO_NUMERALES_SGSST/AA_ WEB.md`

- Bootstrap 5.3
- Encabezado tabla 3 columnas (logo 150px, info 170px)
- Títulos 1.1rem bold #0d6efd border-bottom 2px #e9ecef
- Line-height 1.7
- Contenedor max-width 900px padding 40px shadow

#### Vista PDF (`pdf_template.php` o específica)

Consultar:
- `docs/MODULO_NUMERALES_SGSST/AA_PDF_ENCABEZADO.md`
- `docs/MODULO_NUMERALES_SGSST/AA_PDF_CUERPO_DOCUMENTO.md`
- `docs/MODULO_NUMERALES_SGSST/AA_PDF_CONTROL_CAMBIOS.md`
- `docs/MODULO_NUMERALES_SGSST/AA_PDF_FIRMAS.md`

Parámetros clave:
- Fuente: DejaVu Sans, 10pt, #333
- Títulos: 11pt bold #0d6efd con border-bottom #e9ecef
- Line-height: 1.2, justify
- Tablas: 9pt, TH #0d6efd white, bordes #999
- Control Cambios: Barra #0d6efd, columnas 80px|flex|90px
- Firmas: Barra #198754, firmantes según estándares cliente

#### Vista Word (`word_template.php` o específica)

Consultar:
- `docs/MODULO_NUMERALES_SGSST/AA_WORD_ENCABEZADO.md`
- `docs/MODULO_NUMERALES_SGSST/AA_WORD_CUERPO_DOCUMENTO.md`
- `docs/MODULO_NUMERALES_SGSST/AA_WORD_CONTROL_CAMBIOS.md`
- `docs/MODULO_NUMERALES_SGSST/AA_WORD_FIRMAS.md`

Parámetros clave:
- Fuente: Arial, 10pt, #333
- Line-height: 1.0 con mso-line-height-rule: exactly
- Títulos: 11pt bold #0d6efd con border-bottom #ccc
- Logo: 70x45px en celda 80px con bgcolor=#FFFFFF
- Tablas: 9pt, padding 3px 5px, sin filas alternas

---

### FASE 4: Verificación

**Verificar entorno CONSULTOR:**
1. Ir a la carpeta del estándar y ver que aparezca el botón para generar
2. Generar el documento
3. Ver vista previa
4. Exportar PDF/Word
5. Solicitar firmas

**Verificar entorno CLIENTE:**
1. Acceder a /client/mis-documentos-sst
2. Navegar a la carpeta del estándar
3. Ver el documento (solo si está aprobado/firmado)
4. Descargar PDF

### DOCUMENTO QUE QUIERO CREAR:

[ESCRIBIR AQUÍ EL NOMBRE DEL DOCUMENTO]
```

---

## EJEMPLO COMPLETO: Agregar "Procedimiento de Auditorías Internas"

### Paso 1: Script de BD

```php
<?php
// app/SQL/agregar_procedimiento_auditorias.php

$sqlTipo = <<<'SQL'
INSERT INTO tbl_doc_tipo_configuracion
(tipo_documento, nombre, descripcion, estandar, flujo, categoria, icono, orden)
VALUES
('procedimiento_auditorias',
 'Procedimiento de Auditorías Internas del SG-SST',
 'Establece la metodología para planificar y ejecutar auditorías internas',
 '6.1.1',
 'secciones_ia',
 'procedimientos',
 'bi-clipboard-check',
 8)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), updated_at = NOW();
SQL;

$sqlSecciones = <<<'SQL'
INSERT INTO tbl_doc_secciones_config
(id_tipo_config, numero, nombre, seccion_key, tipo_contenido, orden, prompt_ia)
SELECT tc.id_tipo_config, s.numero, s.nombre, s.seccion_key, s.tipo_contenido, s.orden, s.prompt_ia
FROM tbl_doc_tipo_configuracion tc
CROSS JOIN (
    SELECT 1 as numero, 'Objetivo' as nombre, 'objetivo' as seccion_key, 'texto' as tipo_contenido, 1 as orden, 'Genera el objetivo del procedimiento de auditorías internas del SG-SST.' as prompt_ia
    UNION SELECT 2, 'Alcance', 'alcance', 'texto', 2, 'Define el alcance del procedimiento de auditorías.'
    UNION SELECT 3, 'Definiciones', 'definiciones', 'texto', 3, 'Define términos clave: auditoría, hallazgo, no conformidad, etc.'
    UNION SELECT 4, 'Responsabilidades', 'responsabilidades', 'texto', 4, 'Define roles del auditor líder, auditores, auditados.'
    UNION SELECT 5, 'Planificación de Auditorías', 'planificacion', 'texto', 5, 'Describe cómo se planifican las auditorías anuales.'
    UNION SELECT 6, 'Ejecución de Auditorías', 'ejecucion', 'texto', 6, 'Describe el proceso de ejecución de auditorías.'
    UNION SELECT 7, 'Informe de Auditoría', 'informe', 'texto', 7, 'Describe el contenido del informe de auditoría.'
    UNION SELECT 8, 'Seguimiento de Hallazgos', 'seguimiento', 'texto', 8, 'Describe el proceso de seguimiento a hallazgos.'
) s
WHERE tc.tipo_documento = 'procedimiento_auditorias'
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), prompt_ia = VALUES(prompt_ia);
SQL;

$sqlFirmantes = <<<'SQL'
INSERT INTO tbl_doc_firmantes_config
(id_tipo_config, firmante_tipo, rol_display, columna_encabezado, orden, mostrar_licencia)
SELECT tc.id_tipo_config, f.firmante_tipo, f.rol_display, f.columna_encabezado, f.orden, f.mostrar_licencia
FROM tbl_doc_tipo_configuracion tc
CROSS JOIN (
    SELECT 'responsable_sst' as firmante_tipo, 'Elaboró' as rol_display, 'Elaboró / Responsable del SG-SST' as columna_encabezado, 1 as orden, 1 as mostrar_licencia
    UNION SELECT 'representante_legal', 'Aprobó', 'Aprobó / Representante Legal', 2, 0
) f
WHERE tc.tipo_documento = 'procedimiento_auditorias'
ON DUPLICATE KEY UPDATE rol_display = VALUES(rol_display);
SQL;
```

### Paso 2: Ejecutar

```bash
php app/SQL/agregar_procedimiento_auditorias.php
```

### Paso 3: ¡Listo!

El documento ya está disponible. El sistema lo leerá automáticamente desde la BD usando `DocumentoConfigService`.

---

## ARCHIVOS DE REFERENCIA

### Servicios (Arquitectura v2.0):

| Archivo | Función |
|---------|---------|
| `app/Services/DocumentoConfigService.php` | Lee configuración de tipos desde BD |
| `app/Services/FirmanteService.php` | Maneja lógica de firmantes centralizada |

### Controladores:

| Archivo | Función |
|---------|---------|
| `app/Controllers/DocumentosSSTController.php` | Controlador principal (usa servicios) |
| `app/Controllers/DocumentacionController.php` | Lógica de carpetas del consultor |
| `app/Controllers/ClienteDocumentosSstController.php` | Vista cliente (solo lectura) |
| `app/Controllers/FirmaElectronicaController.php` | Sistema de firmas electrónicas |

### Vistas Genéricas:

| Archivo | Función |
|---------|---------|
| `app/Views/documentos_sst/documento_generico.php` | Vista web genérica |
| `app/Views/documentos_sst/pdf_template.php` | Template PDF |
| `app/Views/documentos_sst/word_template.php` | Template Word |
| `app/Views/documentos_sst/generar_con_ia.php` | Editor de secciones con IA |

### Componentes Reutilizables:

| Archivo | Función |
|---------|---------|
| `app/Views/documentos_sst/_components/seccion_documento.php` | Renderiza una sección |
| `app/Views/documentos_sst/_components/tabla_dinamica.php` | Renderiza tablas dinámicas |
| `app/Views/documentos_sst/_components/firmas_documento.php` | Renderiza bloque de firmas |
| `app/Views/documentacion/_components/tabla_documentos.php` | Lista documentos aprobados |
| `app/Views/documentacion/_components/panel_fases.php` | Muestra fases del documento |

### Scripts de BD:

| Archivo | Función |
|---------|---------|
| `app/SQL/ejecutar_migracion_completa.php` | Migración de arquitectura v2.0 |
| `app/SQL/agregar_tipos_documento_bd.php` | Agrega tipos adicionales |

### Documentación de Estilos:

| Archivo | Función |
|---------|---------|
| `docs/MODULO_NUMERALES_SGSST/AA_ WEB.md` | Estilos vista web |
| `docs/MODULO_NUMERALES_SGSST/AA_PDF_*.md` | Estilos exportación PDF |
| `docs/MODULO_NUMERALES_SGSST/AA_WORD_*.md` | Estilos exportación Word |
| `docs/MODULO_NUMERALES_SGSST/SISTEMA_FIRMAS_DOCUMENTOS.md` | Lógica de firmas |
| `docs/MODULO_NUMERALES_SGSST/NUMERALES_CON_PROGRAMAS.md` | Numerales que requieren PTA |
| `docs/MODULO_NUMERALES_SGSST/TROUBLESHOOTING_GENERACION_IA.md` | Solución de problemas |

---

## ESTRUCTURA DE TABLAS

### tbl_doc_tipo_configuracion

```sql
CREATE TABLE tbl_doc_tipo_configuracion (
    id_tipo_config INT AUTO_INCREMENT PRIMARY KEY,
    tipo_documento VARCHAR(100) NOT NULL UNIQUE,  -- Identificador interno
    nombre VARCHAR(255) NOT NULL,                  -- Nombre para mostrar
    descripcion TEXT,
    estandar VARCHAR(20),                          -- Código estándar 0312
    flujo ENUM('secciones_ia', 'formulario', 'carga_archivo', 'mixto'),
    categoria VARCHAR(50),                         -- procedimientos, programas, etc.
    icono VARCHAR(50) DEFAULT 'bi-file-text',
    orden INT DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### tbl_doc_secciones_config

```sql
CREATE TABLE tbl_doc_secciones_config (
    id_seccion_config INT AUTO_INCREMENT PRIMARY KEY,
    id_tipo_config INT NOT NULL,
    numero INT NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    seccion_key VARCHAR(100) NOT NULL,
    prompt_ia TEXT,                                -- Prompt para generar con IA
    tipo_contenido ENUM('texto', 'tabla_dinamica', 'lista', 'mixto'),
    tabla_dinamica_tipo VARCHAR(50),               -- Key de tbl_doc_tablas_dinamicas
    sincronizar_bd VARCHAR(50),                    -- 'pta_cliente' o 'indicadores_sst'
    es_obligatoria TINYINT(1) DEFAULT 1,
    orden INT NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    UNIQUE KEY uk_tipo_seccion (id_tipo_config, seccion_key)
);
```

### tbl_doc_firmantes_config

```sql
CREATE TABLE tbl_doc_firmantes_config (
    id_firmante_config INT AUTO_INCREMENT PRIMARY KEY,
    id_tipo_config INT NOT NULL,
    firmante_tipo ENUM('representante_legal', 'responsable_sst', 'consultor_sst',
                       'delegado_sst', 'vigia_sst', 'copasst', 'trabajador'),
    rol_display VARCHAR(100) NOT NULL,             -- "Elaboró", "Aprobó", "Revisó"
    columna_encabezado VARCHAR(100) NOT NULL,      -- Texto del encabezado
    orden INT NOT NULL,
    es_obligatorio TINYINT(1) DEFAULT 1,
    mostrar_licencia TINYINT(1) DEFAULT 0,
    mostrar_cedula TINYINT(1) DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    UNIQUE KEY uk_tipo_firmante (id_tipo_config, firmante_tipo)
);
```

### tbl_doc_tablas_dinamicas

```sql
CREATE TABLE tbl_doc_tablas_dinamicas (
    id_tabla_dinamica INT AUTO_INCREMENT PRIMARY KEY,
    tabla_key VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    query_base TEXT NOT NULL,                      -- Query SQL con :id_cliente
    columnas JSON NOT NULL,                        -- Configuración de columnas
    filtro_cliente TINYINT(1) DEFAULT 1,           -- Si requiere id_cliente
    estilo_encabezado VARCHAR(50) DEFAULT 'primary',
    activo TINYINT(1) DEFAULT 1
);
```

---

## USO DE LOS SERVICIOS EN PHP

### DocumentoConfigService

```php
use App\Services\DocumentoConfigService;

$configService = new DocumentoConfigService();

// Obtener configuración completa de un tipo
$config = $configService->obtenerTipoDocumento('programa_capacitacion');
// Retorna: ['nombre' => '...', 'secciones' => [...], 'firmantes' => [...], ...]

// Obtener solo firmantes
$firmantes = $configService->obtenerFirmantes('procedimiento_control_documental');
// Retorna: ['responsable_sst', 'representante_legal']

// Obtener secciones
$secciones = $configService->obtenerSecciones('programa_capacitacion');
// Retorna: [['numero' => 1, 'nombre' => 'Introducción', 'key' => 'introduccion', ...], ...]

// Obtener prompt de IA para una sección
$prompt = $configService->obtenerPromptSeccion('programa_capacitacion', 'introduccion');

// Obtener datos de tabla dinámica
$datos = $configService->obtenerDatosTablaDinamica('listado_maestro', $idCliente);
```

### FirmanteService

```php
use App\Services\FirmanteService;

$firmanteService = new FirmanteService();

// Obtener firmantes con datos completos para renderizar
$firmantes = $firmanteService->obtenerFirmantesDocumento(
    'procedimiento_control_documental',
    $contexto,
    $cliente,
    $consultor,
    $firmasElectronicas
);
// Retorna array con nombre, cargo, firma_imagen, etc. listo para la vista
```

---

## SNIPPETS REUTILIZABLES

### Control de Cambios PDF

```html
<div class="seccion" style="margin-top: 25px;">
    <div style="background-color: #0d6efd; color: white; padding: 8px 12px; font-weight: bold; font-size: 10pt;">
        CONTROL DE CAMBIOS
    </div>
    <table class="tabla-contenido" style="width: 100%; margin-top: 0;">
        <tr>
            <th style="width: 80px; background-color: #e9ecef; color: #333;">Version</th>
            <th style="background-color: #e9ecef; color: #333;">Descripcion del Cambio</th>
            <th style="width: 90px; background-color: #e9ecef; color: #333;">Fecha</th>
        </tr>
        <!-- filas de versiones -->
    </table>
</div>
```

### Firmas 3 Columnas PDF

```html
<div style="margin-top: 25px;">
    <div style="background-color: #198754; color: white; padding: 8px 12px; font-weight: bold; font-size: 10pt;">
        FIRMAS DE APROBACIÓN
    </div>
    <table class="tabla-contenido" style="width: 100%; margin-top: 0;">
        <tr>
            <th style="width: 33.33%; background-color: #e9ecef; color: #333;">Elaboró</th>
            <th style="width: 33.33%; background-color: #e9ecef; color: #333;">Revisó / Vigía SST</th>
            <th style="width: 33.33%; background-color: #e9ecef; color: #333;">Aprobó</th>
        </tr>
        <!-- filas de datos y firmas -->
    </table>
</div>
```

### Encabezado Word

```html
<table width="100%" border="1" cellpadding="0" cellspacing="0"
       style="border-collapse:collapse; border:1px solid #333; margin-bottom:15px;">
    <tr>
        <td width="80" rowspan="2" align="center" valign="middle"
            bgcolor="#FFFFFF" style="border:1px solid #333; padding:5px; background-color:#ffffff;">
            <img src="<?= $logoBase64 ?>" width="70" height="45" alt="Logo">
        </td>
        <td align="center" valign="middle"
            style="border:1px solid #333; padding:5px; font-size:9pt; font-weight:bold;">
            SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO
        </td>
        <td width="120" rowspan="2" valign="middle"
            style="border:1px solid #333; padding:0; font-size:8pt;">
            <!-- Tabla anidada de info -->
        </td>
    </tr>
    <tr>
        <td align="center" valign="middle"
            style="border:1px solid #333; padding:5px; font-size:9pt; font-weight:bold;">
            <?= esc(strtoupper($titulo)) ?>
        </td>
    </tr>
</table>
```

---

## RESUMEN DE REGLAS

| Acción | Permitido |
|--------|-----------|
| Agregar tipo en BD (tbl_doc_tipo_configuracion) | ✅ **CORRECTO** |
| Agregar secciones en BD (tbl_doc_secciones_config) | ✅ **CORRECTO** |
| Agregar firmantes en BD (tbl_doc_firmantes_config) | ✅ **CORRECTO** |
| Crear clase PHP en DocumentosSSTTypes/ | ✅ **OBLIGATORIO** |
| Registrar clase en DocumentoSSTFactory | ✅ **OBLIGATORIO** |
| Usar DocumentoConfigService para leer configuración | ✅ **CORRECTO** |
| Usar FirmanteService para obtener firmantes | ✅ **CORRECTO** |
| Consultar docs/MODULO_NUMERALES_SGSST/ para estilos | ✅ **CORRECTO** |
| Agregar tipo en constante TIPOS_DOCUMENTO | ❌ **OBSOLETO** |
| Crear controlador Pz* o Hz* nuevo | ❌ **PROHIBIDO** |
| Hardcodear códigos de documento en PHP | ❌ **PROHIBIDO** |
| Inventar estilos CSS sin consultar documentación | ❌ **PROHIBIDO** |
| Omitir clase PHP pensando que BD es suficiente | ❌ **ERROR COMÚN** |

⚠️ **IMPORTANTE:** Aunque la configuración está en BD, SIEMPRE se requiere:
1. Clase PHP en `app/Libraries/DocumentosSSTTypes/`
2. Registro en `DocumentoSSTFactory.php`

Sin estos, la generación con IA mostrará "[Seccion no definida]".

---

**Última actualización:** Febrero 2026 - Arquitectura Escalable v2.0 con Clase PHP Obligatoria
