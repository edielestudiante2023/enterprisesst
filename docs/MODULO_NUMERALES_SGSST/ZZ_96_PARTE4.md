# INSTRUCTIVO: Crear un Nuevo Tipo de Documento SST

## ⚠️ IMPORTANTE: Módulo de 3 Partes

Si el documento que vas a crear es parte de un **módulo de 3 partes** (Actividades → Indicadores → Documento), **LEE PRIMERO**:

📄 **[ZZ_90_PARTE3.md](ZZ_90_PARTE3.md)** - Arquitectura del módulo de 3 partes

En ese caso, tu clase deberá:
1. Definir constantes `TIPO_SERVICIO` y `CATEGORIA`
2. Sobrescribir `getContextoBase()` para consumir datos de Parte 1 y Parte 2
3. Usar los métodos `obtenerActividades()` y `obtenerIndicadores()` documentados allí

```
┌─────────────────────────────────────────────────────────────────┐
│  ¿Tu documento necesita actividades del PTA o indicadores?     │
│                                                                 │
│  SÍ → Leer ZZ_90_PARTE3.md primero (arquitectura 3 partes)     │
│  NO → Continuar con este instructivo (documento simple)        │
└─────────────────────────────────────────────────────────────────┘
```

---

## Terminología Correcta

| Término Técnico | Descripción | Ejemplo en URL |
|-----------------|-------------|----------------|
| **Tipo de Documento SST** | El identificador único del documento | `programa_capacitacion` |
| **Segment** (en URL) | El parámetro dinámico en la ruta | `/documentos/generar/{segment}/{id_cliente}` |
| **Clase de Documento** | La clase PHP que implementa la lógica | `ProgramaCapacitacion.php` |

La ruta `/documentos/generar/(:segment)/(:num)` acepta cualquier `tipo_documento` registrado en el sistema como el primer parámetro (segment).

---

## ARQUITECTURA DEL SISTEMA

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                         FLUJO DE GENERACIÓN DE DOCUMENTO                     │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│   URL: /documentos/generar/{tipo_documento}/{id_cliente}                    │
│                           │                                                  │
│                           ▼                                                  │
│   ┌─────────────────────────────────────────┐                               │
│   │     DocumentosSSTController.php         │                               │
│   │     método: generarConIA()              │                               │
│   └─────────────────────────────────────────┘                               │
│                           │                                                  │
│            ┌──────────────┼──────────────┐                                  │
│            ▼              ▼              ▼                                   │
│   ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐                │
│   │DocumentoSST │  │DocumentoSST │  │ DocumentoConfigSvc  │                │
│   │  Factory    │  │  Interface  │  │ (BD: tbl_doc_*)     │                │
│   └─────────────┘  └─────────────┘  └─────────────────────┘                │
│         │                                                                    │
│         ▼                                                                    │
│   ┌─────────────────────────────────────────┐                               │
│   │     TU_NUEVA_CLASE.php                  │                               │
│   │     extends AbstractDocumentoSST        │                               │
│   │     (Secciones, Prompts, Firmantes)     │                               │
│   └─────────────────────────────────────────┘                               │
│                           │                                                  │
│                           ▼                                                  │
│   ┌─────────────────────────────────────────┐                               │
│   │     Vista: generar_con_ia.php           │                               │
│   │     (Interfaz de edición por secciones) │                               │
│   └─────────────────────────────────────────┘                               │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## ARCHIVOS QUE DEBES CREAR/MODIFICAR

Para crear un nuevo tipo de documento necesitas:

| # | Archivo | Acción | Obligatorio |
|---|---------|--------|-------------|
| 1 | `app/Libraries/DocumentosSSTTypes/TuNuevaClase.php` | CREAR | ✅ SÍ |
| 2 | `app/Libraries/DocumentosSSTTypes/DocumentoSSTFactory.php` | MODIFICAR | ✅ SÍ |
| 3 | `app/SQL/agregar_tu_nuevo_tipo.php` | CREAR | ✅ SÍ |
| 4 | `app/Views/documentacion/_tipos/tu_nuevo_tipo.php` | CREAR | ⚠️ Opcional |

---

## PASO 1: Crear la Clase del Tipo de Documento

### Ubicación
```
app/Libraries/DocumentosSSTTypes/TuNuevaClase.php
```

### Convención de Nombres

| tipo_documento (snake_case) | Clase PHP (PascalCase) |
|-----------------------------|------------------------|
| `programa_capacitacion` | `ProgramaCapacitacion` |
| `politica_sst_general` | `PoliticaSstGeneral` |
| `manual_convivencia_laboral` | `ManualConvivenciaLaboral` |
| `procedimiento_matriz_legal` | `ProcedimientoMatrizLegal` |

### Plantilla Completa de la Clase

```php
<?php

namespace App\Libraries\DocumentosSSTTypes;

/**
 * Clase [NOMBRE_CLASE_PASCALCASE]
 *
 * Implementa la generación de [NOMBRE LEGIBLE DEL DOCUMENTO]
 * Numeral [X.X.X] de la Resolución 0312/2019
 *
 * @package App\Libraries\DocumentosSSTTypes
 * @author Enterprise SST
 * @version 1.0
 */
class [NOMBRE_CLASE_PASCALCASE] extends AbstractDocumentoSST
{
    /**
     * MÉTODO 1: getTipoDocumento()
     *
     * Retorna el identificador único del tipo de documento.
     * DEBE coincidir con:
     * - El valor en tbl_documentos_sst.tipo_documento
     * - El key en DocumentoSSTFactory::$tiposRegistrados
     * - El segment en la URL /documentos/generar/{segment}/
     *
     * FORMATO: snake_case, sin espacios, sin caracteres especiales
     */
    public function getTipoDocumento(): string
    {
        return '[tipo_documento_snake_case]';
    }

    /**
     * MÉTODO 2: getNombre()
     *
     * Retorna el nombre legible/amigable del documento.
     * Se muestra en:
     * - Encabezado de la vista de generación
     * - Listados de documentos
     * - PDFs generados
     */
    public function getNombre(): string
    {
        return '[Nombre Legible del Documento]';
    }

    /**
     * MÉTODO 3: getDescripcion()
     *
     * Retorna una descripción breve del propósito del documento.
     * Se usa en tooltips y listados.
     */
    public function getDescripcion(): string
    {
        return '[Descripción breve del documento y su propósito]';
    }

    /**
     * MÉTODO 4: getEstandar()
     *
     * Retorna el código del estándar de la Resolución 0312/2019.
     * Ejemplos: '1.1.1', '2.1.1', '3.1.2', null si no aplica
     */
    public function getEstandar(): ?string
    {
        return '[X.X.X]';
    }

    /**
     * MÉTODO 5: getSecciones()
     *
     * Define las secciones que componen el documento.
     * CADA SECCIÓN TIENE:
     * - numero: Orden numérico (1, 2, 3...)
     * - nombre: Título visible de la sección
     * - key: Identificador único (snake_case, sin acentos)
     *
     * IMPORTANTE: El 'key' se usa para:
     * - Identificar la sección en BD
     * - Generar el prompt de IA correspondiente
     * - Guardar/cargar contenido
     */
    public function getSecciones(): array
    {
        return [
            ['numero' => 1, 'nombre' => 'Objetivo', 'key' => 'objetivo'],
            ['numero' => 2, 'nombre' => 'Alcance', 'key' => 'alcance'],
            ['numero' => 3, 'nombre' => 'Definiciones', 'key' => 'definiciones'],
            ['numero' => 4, 'nombre' => 'Marco Legal', 'key' => 'marco_legal'],
            ['numero' => 5, 'nombre' => 'Responsabilidades', 'key' => 'responsabilidades'],
            // Agregar más secciones según necesidad del documento
            // ...
        ];
    }

    /**
     * MÉTODO 6: getFirmantesRequeridos()
     *
     * Define quiénes deben firmar el documento.
     * TIPOS DE FIRMANTES DISPONIBLES:
     * - 'representante_legal': El gerente/dueño de la empresa
     * - 'responsable_sst': Encargado del SG-SST
     * - 'consultor_sst': Consultor externo (si aplica)
     * - 'copasst': Representante del COPASST (si aplica, 21+ estándares)
     * - 'vigia_sst': Vigía SST (si aplica, 7 estándares)
     *
     * @param int $estandares Nivel de estándares del cliente (7, 21, 60)
     */
    public function getFirmantesRequeridos(int $estandares): array
    {
        // Ejemplo: Para 7 estándares usa Vigía, para 21+ usa COPASST
        if ($estandares <= 10) {
            return ['responsable_sst', 'representante_legal'];
        }

        return ['responsable_sst', 'representante_legal', 'copasst'];
    }

    /**
     * MÉTODO 7: getPromptParaSeccion()
     *
     * EL MÉTODO MÁS IMPORTANTE PARA LA GENERACIÓN CON IA.
     *
     * Retorna el prompt específico para que la IA genere el contenido
     * de cada sección del documento.
     *
     * GUÍA PARA ESCRIBIR BUENOS PROMPTS:
     *
     * 1. SER ESPECÍFICO: Indicar exactamente qué debe incluir
     * 2. DAR CONTEXTO: Mencionar normativa aplicable (Res. 0312, Decreto 1072)
     * 3. AJUSTAR POR ESTÁNDARES: Variar complejidad según 7/21/60 estándares
     * 4. INDICAR FORMATO: Lista, párrafos, tabla (si aplica)
     * 5. LIMITAR EXTENSIÓN: Indicar máximo de elementos/párrafos
     * 6. PROHIBIR TABLAS MD: A menos que sea necesario para la sección
     *
     * @param string $seccionKey El 'key' de la sección (de getSecciones())
     * @param int $estandares Nivel de estándares (7, 21, 60)
     */
    public function getPromptParaSeccion(string $seccionKey, int $estandares): string
    {
        // Determinar texto de comité según estándares
        $comite = $estandares <= 10 ? 'Vigía de SST' : 'COPASST';

        // Determinar nivel de complejidad
        $nivelTexto = match(true) {
            $estandares <= 7 => 'básico (7 estándares)',
            $estandares <= 21 => 'intermedio (21 estándares)',
            default => 'avanzado (60 estándares)'
        };

        $prompts = [
            // ═══════════════════════════════════════════════════════════════
            // SECCIÓN: OBJETIVO
            // ═══════════════════════════════════════════════════════════════
            'objetivo' => "Genera el objetivo del [NOMBRE DEL DOCUMENTO].
Debe expresar el propósito principal del documento según la normativa colombiana.
INCLUIR referencia a:
- Resolución 0312 de 2019 (estándar [X.X.X])
- Decreto 1072 de 2015
FORMATO: Máximo 2 párrafos concisos.
TONO: Formal, técnico, en tercera persona.",

            // ═══════════════════════════════════════════════════════════════
            // SECCIÓN: ALCANCE
            // ═══════════════════════════════════════════════════════════════
            'alcance' => "Define el alcance del documento.
DEBE especificar a quién aplica:
- Trabajadores directos
- Contratistas (si aplica)
- Visitantes (si aplica)
- Áreas o procesos cubiertos
AJUSTAR según nivel de empresa ({$nivelTexto}):
- 7 estándares: alcance simple, 3-4 ítems
- 21 estándares: alcance moderado, 5-6 ítems
- 60 estándares: alcance completo
FORMATO: Lista con viñetas.",

            // ═══════════════════════════════════════════════════════════════
            // SECCIÓN: DEFINICIONES
            // ═══════════════════════════════════════════════════════════════
            'definiciones' => "Genera un glosario de términos técnicos relevantes.
CANTIDAD según estándares:
- 7 estándares: MÁXIMO 6-8 términos esenciales
- 21 estándares: MÁXIMO 10-12 términos
- 60 estándares: 12-15 términos completos
FORMATO: Término en **negrita** seguido de dos puntos y definición.
BASARSE en normativa colombiana (Decreto 1072, Resolución 0312).
NO usar tablas Markdown.",

            // ═══════════════════════════════════════════════════════════════
            // SECCIÓN: MARCO LEGAL
            // ═══════════════════════════════════════════════════════════════
            'marco_legal' => "Lista el marco normativo aplicable.
NORMATIVA ESENCIAL (siempre incluir):
- Ley 1562 de 2012
- Decreto 1072 de 2015
- Resolución 0312 de 2019
CANTIDAD según estándares:
- 7 estándares: MÁXIMO 4-5 normas principales
- 21 estándares: MÁXIMO 6-8 normas
- 60 estándares: Hasta 10 normas
FORMATO: Lista con viñetas, nombre de la norma en negrita.
PROHIBIDO: NO usar tablas Markdown.",

            // ═══════════════════════════════════════════════════════════════
            // SECCIÓN: RESPONSABILIDADES
            // ═══════════════════════════════════════════════════════════════
            'responsabilidades' => "Define los roles y responsabilidades.
ROLES según estándares:
- 7 estándares: SOLO 3-4 roles (Representante Legal, Responsable SST, {$comite}, Trabajadores)
- 21 estándares: 5-6 roles (agregar supervisores, coordinadores)
- 60 estándares: Todos los roles necesarios
IMPORTANTE para {$estandares} estándares:
- Si son 7 estándares: usar 'Vigía de SST', NUNCA mencionar COPASST
- Si son 21+ estándares: usar 'COPASST'
FORMATO: Rol en **negrita**, seguido de lista de responsabilidades.",

            // ═══════════════════════════════════════════════════════════════
            // AGREGA MÁS SECCIONES SEGÚN TU DOCUMENTO
            // ═══════════════════════════════════════════════════════════════
            // 'mi_seccion_personalizada' => "Prompt para esta sección...",
        ];

        // Retornar el prompt correspondiente o uno genérico
        return $prompts[$seccionKey]
            ?? "Genera el contenido para la sección '{$seccionKey}' del documento [NOMBRE], siguiendo la normativa colombiana de SST.";
    }

    /**
     * MÉTODO 8: getContenidoEstatico() - OPCIONAL
     *
     * Proporciona contenido de respaldo (fallback) cuando:
     * - La IA no está disponible
     * - La generación falla
     * - Se necesita contenido predeterminado
     *
     * HEREDADO de AbstractDocumentoSST, sobrescribir solo si necesitas
     * contenido específico más elaborado que el genérico.
     */
    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'la empresa';
        $comite = $this->getTextoComite($estandares);

        $contenidos = [
            'objetivo' => "{$nombreEmpresa}, en cumplimiento de la normatividad legal vigente en materia de Seguridad y Salud en el Trabajo, específicamente la Resolución 0312 de 2019 que establece los Estándares Mínimos del Sistema de Gestión de Seguridad y Salud en el Trabajo (SG-SST), ha desarrollado el presente documento.\n\nEl objetivo es [DESCRIBIR OBJETIVO ESPECÍFICO DEL DOCUMENTO].",

            'alcance' => "El presente documento aplica a:\n\n- Todos los trabajadores de {$nombreEmpresa}\n- Contratistas y subcontratistas\n- Visitantes\n- Todas las actividades desarrolladas en las instalaciones",

            'definiciones' => "**[Término 1]:** Definición del término según normativa.\n\n**[Término 2]:** Definición del término según normativa.\n\n**[Término 3]:** Definición del término según normativa.",

            'marco_legal' => "**Normativa aplicable:**\n\n- **Ley 1562 de 2012:** Por la cual se modifica el Sistema de Riesgos Laborales.\n- **Decreto 1072 de 2015:** Decreto Único Reglamentario del Sector Trabajo.\n- **Resolución 0312 de 2019:** Estándares Mínimos del SG-SST.",

            'responsabilidades' => "**Representante Legal:**\n- Asignar recursos para el cumplimiento del documento\n- Aprobar el documento\n\n**Responsable del SG-SST:**\n- Elaborar y actualizar el documento\n- Verificar el cumplimiento\n\n**{$comite}:**\n- Participar en la revisión del documento\n\n**Trabajadores:**\n- Cumplir con lo establecido en el documento",
        ];

        return $contenidos[$seccionKey] ?? parent::getContenidoEstatico($seccionKey, $cliente, $contexto, $estandares, $anio);
    }
}
```

---

## PASO 2: Registrar en el Factory

### Archivo a Modificar
```
app/Libraries/DocumentosSSTTypes/DocumentoSSTFactory.php
```

### Qué Modificar

Localiza el array `$tiposRegistrados` y agrega tu nuevo tipo:

```php
private static array $tiposRegistrados = [
    // ... tipos existentes ...

    // ══════════════════════════════════════════════════════════════
    // AGREGAR TU NUEVO TIPO AQUÍ
    // ══════════════════════════════════════════════════════════════
    'tu_tipo_documento' => TuNuevaClase::class,
];
```

### Ejemplo Real

```php
private static array $tiposRegistrados = [
    'programa_capacitacion' => ProgramaCapacitacion::class,
    'procedimiento_control_documental' => ProcedimientoControlDocumental::class,
    'programa_promocion_prevencion_salud' => ProgramaPromocionPrevencionSalud::class,
    // ... más tipos ...

    // NUEVO TIPO AGREGADO:
    'mecanismos_comunicacion_sgsst' => MecanismosComunicacionSgsst::class,
];
```

---

## PASO 3: Crear Script SQL para Base de Datos

### Archivo a Crear
```
app/SQL/agregar_[tu_tipo_documento].php
```

### Plantilla Completa del Script SQL

```php
<?php
/**
 * Script para agregar tipo de documento: [NOMBRE DEL DOCUMENTO]
 * Estándar: [X.X.X] de la Resolución 0312/2019
 *
 * Ejecutar: php app/SQL/agregar_[tu_tipo_documento].php
 *
 * @author Enterprise SST
 * @version 1.0
 */

echo "=== Agregando [NOMBRE DEL DOCUMENTO] ([X.X.X]) ===\n\n";

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
        'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port' => 25060,
        'database' => 'empresas_sst',
        'username' => 'cycloid_userdb',
        'password' => 'AVNS_iDypWizlpMRwHIORJGG',
        'ssl' => true
    ]
];

// ============================================
// SQL 1: Insertar tipo de documento
// ============================================
$sqlTipo = <<<'SQL'
INSERT INTO tbl_doc_tipo_configuracion
(tipo_documento, nombre, descripcion, estandar, flujo, categoria, icono, orden)
VALUES
('[tu_tipo_documento]',
 '[Nombre Legible del Documento]',
 '[Descripción del documento]',
 '[X.X.X]',
 'secciones_ia',
 '[categoria]',
 '[bi-icono]',
 [numero_orden])
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    descripcion = VALUES(descripcion),
    updated_at = NOW();
SQL;

// ============================================
// SQL 2: Insertar secciones del documento
// ============================================
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
    SELECT 1 as numero,
           'Objetivo' as nombre,
           'objetivo' as seccion_key,
           'texto' as tipo_contenido,
           NULL as tabla_dinamica_tipo,
           1 as orden,
           '[PROMPT PARA SECCIÓN OBJETIVO]' as prompt_ia

    UNION SELECT 2,
           'Alcance',
           'alcance',
           'texto',
           NULL,
           2,
           '[PROMPT PARA SECCIÓN ALCANCE]'

    UNION SELECT 3,
           'Definiciones',
           'definiciones',
           'texto',
           NULL,
           3,
           '[PROMPT PARA SECCIÓN DEFINICIONES]'

    -- AGREGAR MÁS SECCIONES SEGÚN NECESIDAD
    -- UNION SELECT N, ...

) s
WHERE tc.tipo_documento = '[tu_tipo_documento]'
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    prompt_ia = VALUES(prompt_ia);
SQL;

// ============================================
// SQL 3: Insertar firmantes
// ============================================
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
    SELECT 'responsable_sst' as firmante_tipo,
           'Elaboró' as rol_display,
           'Elaboró / Responsable del SG-SST' as columna_encabezado,
           1 as orden,
           1 as mostrar_licencia
    UNION SELECT 'representante_legal',
           'Aprobó',
           'Aprobó / Representante Legal',
           2,
           0
) f
WHERE tc.tipo_documento = '[tu_tipo_documento]'
ON DUPLICATE KEY UPDATE
    rol_display = VALUES(rol_display),
    columna_encabezado = VALUES(columna_encabezado);
SQL;

// ============================================
// SQL 4: Insertar plantilla (código del documento)
// ============================================
$sqlPlantilla = <<<'SQL'
INSERT INTO tbl_doc_plantillas (
    id_tipo, nombre, codigo_sugerido, tipo_documento, version, activo
)
SELECT
    COALESCE(
        (SELECT id_tipo FROM tbl_doc_tipos WHERE codigo = '[categoria]' LIMIT 1),
        (SELECT id_tipo FROM tbl_doc_tipos ORDER BY id_tipo LIMIT 1)
    ),
    '[Nombre del Documento]',
    '[COD-DOC]',
    '[tu_tipo_documento]',
    '001',
    1
FROM DUAL
WHERE EXISTS (SELECT 1 FROM tbl_doc_tipos LIMIT 1)
  AND NOT EXISTS (SELECT 1 FROM tbl_doc_plantillas WHERE codigo_sugerido = '[COD-DOC]')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);
SQL;

// ============================================
// SQL 5: Mapear a carpeta del estándar
// ============================================
$sqlMapeoCarpeta = <<<'SQL'
INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
VALUES ('[COD-DOC]', '[X.X.X]')
ON DUPLICATE KEY UPDATE codigo_carpeta = VALUES(codigo_carpeta);
SQL;

// ============================================
// Función de ejecución
// ============================================
function ejecutarEnConexion($config, $nombre, $sqlTipo, $sqlSecciones, $sqlFirmantes, $sqlPlantilla, $sqlMapeoCarpeta) {
    echo "Conectando a {$nombre}...\n";

    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];

        if ($config['ssl']) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = true;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        echo "  ✓ Conexión exitosa\n";

        // 1. Insertar tipo de documento
        echo "  → Insertando tipo de documento...\n";
        $pdo->exec($sqlTipo);
        echo "    ✓ Tipo de documento insertado/actualizado\n";

        // 2. Insertar secciones
        echo "  → Insertando secciones...\n";
        $pdo->exec($sqlSecciones);
        echo "    ✓ Secciones configuradas\n";

        // 3. Insertar firmantes
        echo "  → Insertando firmantes...\n";
        $pdo->exec($sqlFirmantes);
        echo "    ✓ Firmantes configurados\n";

        // 4. Insertar plantilla
        echo "  → Insertando plantilla...\n";
        $tablaExists = $pdo->query("SHOW TABLES LIKE 'tbl_doc_plantillas'")->rowCount() > 0;
        if ($tablaExists) {
            $pdo->exec($sqlPlantilla);
            echo "    ✓ Plantilla insertada\n";
        }

        // 5. Mapear carpeta
        echo "  → Mapeando carpeta...\n";
        $tablaMapeoExists = $pdo->query("SHOW TABLES LIKE 'tbl_doc_plantilla_carpeta'")->rowCount() > 0;
        if ($tablaMapeoExists) {
            $pdo->exec($sqlMapeoCarpeta);
            echo "    ✓ Mapeo configurado\n";
        }

        echo "  ✓ {$nombre} completado\n\n";
        return true;

    } catch (PDOException $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n\n";
        return false;
    }
}

// Ejecutar en LOCAL
$resultadoLocal = ejecutarEnConexion(
    $conexiones['local'], 'LOCAL',
    $sqlTipo, $sqlSecciones, $sqlFirmantes, $sqlPlantilla, $sqlMapeoCarpeta
);

// Si local exitoso, ejecutar en producción
if ($resultadoLocal) {
    echo "LOCAL exitoso. Ejecutando en PRODUCCIÓN...\n\n";
    ejecutarEnConexion(
        $conexiones['produccion'], 'PRODUCCIÓN',
        $sqlTipo, $sqlSecciones, $sqlFirmantes, $sqlPlantilla, $sqlMapeoCarpeta
    );
}

echo "=== Proceso completado ===\n";
echo "\nVerificar:\n";
echo "1. Clase: app/Libraries/DocumentosSSTTypes/[TuClase].php\n";
echo "2. Factory: app/Libraries/DocumentosSSTTypes/DocumentoSSTFactory.php\n";
echo "3. URL: /documentos/generar/[tu_tipo_documento]/{id_cliente}\n";
```

---

## PASO 4 (OPCIONAL): Crear Vista Personalizada

### Cuándo es Necesario

La vista personalizada en `app/Views/documentacion/_tipos/` es necesaria SOLO si:
- El documento aparece en el panel de documentación del cliente
- Necesitas mostrar información adicional o botones especiales
- El documento requiere una interfaz diferente

### Si NO Necesitas Vista Personalizada

El sistema usará automáticamente `generar_con_ia.php` que es la vista genérica.

### Si SÍ Necesitas Vista Personalizada

Crear archivo en:
```
app/Views/documentacion/_tipos/[tu_tipo_documento].php
```

Ejemplo básico:
```php
<?php
/**
 * Vista del documento [Nombre] en el panel de documentación
 */
$hayDocumento = !empty($documento);
$estado = $documento['estado'] ?? 'pendiente';
?>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div>
            <h6 class="mb-0">
                <i class="bi bi-[icono] text-primary me-2"></i>
                [Nombre del Documento]
            </h6>
            <small class="text-muted">Estándar [X.X.X]</small>
        </div>
        <div>
            <?php if ($hayDocumento): ?>
                <span class="badge bg-success">Generado</span>
            <?php else: ?>
                <span class="badge bg-warning">Pendiente</span>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">
            [Descripción del documento]
        </p>

        <div class="d-flex gap-2">
            <a href="<?= base_url('documentos/generar/[tu_tipo_documento]/' . $idCliente) ?>"
               class="btn btn-primary btn-sm">
                <i class="bi bi-magic me-1"></i>
                <?= $hayDocumento ? 'Editar' : 'Generar' ?>
            </a>

            <?php if ($hayDocumento): ?>
            <a href="<?= base_url('documentos-sst/' . $idCliente . '/[tu-tipo-documento]/' . date('Y')) ?>"
               class="btn btn-outline-secondary btn-sm" target="_blank">
                <i class="bi bi-eye me-1"></i>Vista Previa
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>
```

---

## RESUMEN DE CHECKLIST

### Antes de Empezar
- [ ] Definir el nombre del tipo de documento (snake_case)
- [ ] Identificar el estándar de la Resolución 0312/2019
- [ ] Listar las secciones del documento
- [ ] Definir quiénes firman el documento

### Archivos a Crear
- [ ] `app/Libraries/DocumentosSSTTypes/[TuClase].php`
- [ ] `app/SQL/agregar_[tu_tipo].php`
- [ ] (Opcional) `app/Views/documentacion/_tipos/[tu_tipo].php`

### Archivo a Modificar
- [ ] `app/Libraries/DocumentosSSTTypes/DocumentoSSTFactory.php`

### Después de Crear
- [ ] Ejecutar el script SQL: `php app/SQL/agregar_[tu_tipo].php`
- [ ] Probar la URL: `/documentos/generar/[tu_tipo]/18`
- [ ] Verificar generación de cada sección
- [ ] Verificar guardado y aprobación
- [ ] Verificar vista previa

---

## EJEMPLO COMPLETO: MecanismosComunicacionSgsst

### 1. La Clase (ya existe)
```
app/Libraries/DocumentosSSTTypes/MecanismosComunicacionSgsst.php
```

### 2. Registro en Factory (ya existe)
```php
'mecanismos_comunicacion_sgsst' => MecanismosComunicacionSgsst::class,
```

### 3. Script SQL (ya existe)
```
app/SQL/agregar_mecanismos_comunicacion_sgsst.php
```

### 4. URL de Prueba
```
http://localhost/enterprisesst/public/documentos/generar/mecanismos_comunicacion_sgsst/18
```

---

## TABLAS DE BASE DE DATOS INVOLUCRADAS

| Tabla | Propósito |
|-------|-----------|
| `tbl_doc_tipo_configuracion` | Registro del tipo de documento |
| `tbl_doc_secciones_config` | Configuración de secciones |
| `tbl_doc_firmantes_config` | Configuración de firmantes |
| `tbl_doc_plantillas` | Código del documento (ej: PRG-CAP) |
| `tbl_doc_plantilla_carpeta` | Mapeo a carpeta del estándar |
| `tbl_documentos_sst` | Documentos generados por cliente |

---

## CONTENIDO INICIAL DINÁMICO (OBLIGATORIO)

**⚠️ NUNCA hardcodear contenido inicial en controladores.**

Si el controlador necesita crear un documento con secciones iniciales, usar `DocumentoConfigService`:

### Patrón Correcto

```php
// En cualquier método del controlador que cree un documento nuevo:
$contenidoInicial = $this->configService->crearContenidoInicial('tipo_documento');

$this->db->table('tbl_documentos_sst')->insert([
    'id_cliente' => $idCliente,
    'tipo_documento' => 'tipo_documento',
    'contenido' => json_encode($contenidoInicial),
    // ...
]);
```

### Patrón Incorrecto (NUNCA USAR)

```php
// ❌ ESTO CAUSA INCONSISTENCIAS entre Vista Web y Vista Edición
$contenidoInicial = [
    'secciones' => [
        ['titulo' => '1. OBJETIVO', 'contenido' => '...', 'orden' => 1],
        ['titulo' => '2. ALCANCE', 'contenido' => '...', 'orden' => 2],
    ]
];
```

### Por qué es importante

1. **Consistencia**: Las secciones vienen de `tbl_doc_secciones_config`
2. **Keys correctos**: El contenido usa keys (`objetivo`, `alcance`) que coinciden con BD
3. **normalizarSecciones()**: Puede hacer match correcto entre Vista Web y Vista Edición
4. **Mantenibilidad**: Cambiar secciones solo requiere modificar BD, no código PHP

---

## ERRORES COMUNES Y SOLUCIONES

### Error: "Tipo de documento no válido"
**Causa:** El tipo no está registrado en `tbl_doc_tipo_configuracion`
**Solución:** Ejecutar el script SQL

### Error: "Clase no encontrada"
**Causa:** La clase no existe o no está en el Factory
**Solución:** Verificar nombre de clase y registro en Factory

### Error: Secciones vacías
**Causa:** Los prompts no están configurados en BD
**Solución:** Verificar `tbl_doc_secciones_config`

### Error: "Vigía SST" cuando debería ser "COPASST"
**Causa:** No se está usando el parámetro `$estandares` correctamente
**Solución:** Usar `$this->getTextoComite($estandares)` en los prompts

### Error: Contenido diferente entre Vista Web y Vista Edición
**Causa:** El controlador usa contenido inicial hardcodeado en lugar de `crearContenidoInicial()`
**Solución:**
1. Buscar hardcodeo: `grep -n "contenidoInicial.*\[" app/Controllers/`
2. Reemplazar por: `$this->configService->crearContenidoInicial('tipo_documento')`
3. Verificar que el script SQL fue ejecutado

---

## CONTACTO Y SOPORTE

Este sistema fue desarrollado por Enterprise SST.
Documentación actualizada: Febrero 2026
