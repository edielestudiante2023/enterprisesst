<?php
/**
 * Script para agregar tipo de documento: Plan de Objetivos y Metas del SG-SST
 * Estándar: 2.2.1 de la Resolución 0312/2019
 *
 * Ejecutar: php app/SQL/agregar_plan_objetivos_metas.php
 *
 * @author Enterprise SST
 * @version 1.0
 */

echo "=== Agregando Plan de Objetivos y Metas (2.2.1) ===\n\n";

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
(tipo_documento, nombre, descripcion, estandar, flujo, categoria, icono, orden, activo)
VALUES
('plan_objetivos_metas',
 'Plan de Objetivos y Metas del SG-SST',
 'Define los objetivos del Sistema de Gestión de SST con sus metas cuantificables e indicadores de medición',
 '2.2.1',
 'secciones_ia',
 'planificacion',
 'bi-bullseye',
 10,
 1)
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
(id_tipo_config, numero, nombre, seccion_key, tipo_contenido, tabla_dinamica_tipo, orden, prompt_ia, es_obligatoria, activo)
SELECT
    tc.id_tipo_config,
    s.numero,
    s.nombre,
    s.seccion_key,
    s.tipo_contenido,
    s.tabla_dinamica_tipo,
    s.orden,
    s.prompt_ia,
    s.es_obligatoria,
    1
FROM tbl_doc_tipo_configuracion tc
CROSS JOIN (
    SELECT 1 as numero,
           'Objetivo' as nombre,
           'objetivo' as seccion_key,
           'texto' as tipo_contenido,
           NULL as tabla_dinamica_tipo,
           1 as orden,
           'Genera el objetivo del Plan de Objetivos y Metas del SG-SST.
Debe establecer:
- El propósito de definir objetivos claros, medibles y cuantificables en SST
- La importancia de establecer metas e indicadores de cumplimiento
- Referencia al cumplimiento del estándar 2.2.1 de la Resolución 0312/2019
- La alineación con la política de SST y los peligros identificados
Máximo 2 párrafos concisos.' as prompt_ia,
           1 as es_obligatoria

    UNION SELECT 2,
           'Alcance',
           'alcance',
           'texto',
           NULL,
           2,
           'Define el alcance del Plan de Objetivos y Metas en SST.
Debe especificar:
- Que aplica a todos los trabajadores directos e indirectos
- Incluye contratistas y subcontratistas
- Cubre todos los procesos, actividades y sedes
- Vigencia anual con revisión periódica
Máximo 2 párrafos.',
           1

    UNION SELECT 3,
           'Definiciones',
           'definiciones',
           'texto',
           NULL,
           3,
           'Define los términos clave para el Plan de Objetivos y Metas.
INCLUIR OBLIGATORIAMENTE:
- Objetivo de SST
- Meta
- Indicador
- Indicador de estructura
- Indicador de proceso
- Indicador de resultado
- Plan de trabajo anual
- Ciclo PHVA
- Mejora continua
- Eficacia
Formato: término en negrita seguido de definición. Máximo 10 definiciones.',
           1

    UNION SELECT 4,
           'Responsabilidades',
           'responsabilidades',
           'texto',
           NULL,
           4,
           'Define las responsabilidades de cada actor en el cumplimiento de objetivos del SG-SST.
INCLUIR:
- Alta Dirección: aprobar objetivos, asignar recursos, revisar avance
- Responsable del SG-SST: formular objetivos, diseñar indicadores, seguimiento
- COPASST/Vigía: participar en formulación, verificar cumplimiento
- Trabajadores: conocer objetivos, contribuir al cumplimiento
Formato: Rol en negrita, seguido de lista de responsabilidades.',
           1

    UNION SELECT 5,
           'Marco Normativo',
           'marco_normativo',
           'texto',
           NULL,
           5,
           'Describe el marco normativo aplicable al Plan de Objetivos y Metas del SG-SST.
INCLUIR:
- Decreto 1072 de 2015: Artículo 2.2.4.6.18 sobre objetivos de SST
- Resolución 0312 de 2019: Estándar 2.2.1 - Objetivos definidos, claros, medibles
- ISO 45001:2018: Requisitos para objetivos y planificación del SST
Indicar requisitos específicos de cada norma sobre objetivos.
Extensión: 2-3 párrafos.',
           1

    UNION SELECT 6,
           'Objetivos del SG-SST',
           'objetivos_sgsst',
           'texto',
           NULL,
           6,
           'Genera la sección de Objetivos del SG-SST.
IMPORTANTE:
- Usa ÚNICAMENTE los objetivos listados en el CONTEXTO de la empresa
- NO inventes objetivos que no estén en el contexto
Para cada objetivo presenta:
1. Nombre del objetivo
2. Descripción breve
3. Meta cuantificable
4. Responsable
5. Plazo de cumplimiento
6. Ciclo PHVA
Si no hay datos en el contexto, indica que deben completarse las fases anteriores del módulo.',
           1

    UNION SELECT 7,
           'Indicadores de Medición',
           'indicadores_medicion',
           'texto',
           NULL,
           7,
           'Genera la sección de Indicadores de Medición de Objetivos.
IMPORTANTE:
- Usa ÚNICAMENTE los indicadores listados en el CONTEXTO de la empresa
- NO inventes indicadores que no estén en el contexto
Para cada indicador presenta:
1. Nombre del indicador
2. Tipo (estructura, proceso o resultado)
3. Fórmula de cálculo
4. Meta establecida
5. Periodicidad de medición
6. Responsable de medición
Agrupa los indicadores por tipo (estructura, proceso, resultado).
Si no hay datos en el contexto, indica que deben completarse las fases anteriores.',
           1

    UNION SELECT 8,
           'Seguimiento y Evaluación',
           'seguimiento_evaluacion',
           'texto',
           NULL,
           8,
           'Genera la sección de Seguimiento y Evaluación que incluya:
- Frecuencia de medición:
  * Indicadores de estructura: anual
  * Indicadores de proceso: trimestral
  * Indicadores de resultado: mensual
- Responsables del seguimiento
- Mecanismos de reporte (tableros de control, informes)
- Revisión en reuniones del COPASST/Vigía
- Acciones ante desviaciones de metas
- Comunicación de resultados a partes interesadas
Extensión: 2-3 párrafos.',
           1

    UNION SELECT 9,
           'Revisión y Actualización',
           'revision_actualizacion',
           'texto',
           NULL,
           9,
           'Genera la sección de Revisión y Actualización que defina:
- Periodicidad de revisión de objetivos (mínimo anual)
- Criterios para modificar objetivos o metas durante el año
- Eventos que disparan una revisión extraordinaria:
  * Accidentes graves o mortales
  * Cambios en la normatividad
  * Cambios significativos en procesos
  * Nuevos peligros identificados
- Registro de cambios en el control de versiones
- Aprobación por la Alta Dirección
- Comunicación de cambios a trabajadores
Extensión: 1-2 párrafos concisos.',
           1

) s
WHERE tc.tipo_documento = 'plan_objetivos_metas'
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    prompt_ia = VALUES(prompt_ia);
SQL;

// ============================================
// SQL 3: Insertar firmantes
// ============================================
$sqlFirmantes = <<<'SQL'
INSERT INTO tbl_doc_firmantes_config
(id_tipo_config, firmante_tipo, rol_display, columna_encabezado, orden, mostrar_licencia, activo)
SELECT
    tc.id_tipo_config,
    f.firmante_tipo,
    f.rol_display,
    f.columna_encabezado,
    f.orden,
    f.mostrar_licencia,
    1
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
WHERE tc.tipo_documento = 'plan_objetivos_metas'
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
        (SELECT id_tipo FROM tbl_doc_tipos WHERE codigo = 'PLN' LIMIT 1),
        (SELECT id_tipo FROM tbl_doc_tipos ORDER BY id_tipo LIMIT 1)
    ),
    'Plan de Objetivos y Metas del SG-SST',
    'PLN-OBJ',
    'plan_objetivos_metas',
    '001',
    1
FROM DUAL
WHERE EXISTS (SELECT 1 FROM tbl_doc_tipos LIMIT 1)
  AND NOT EXISTS (SELECT 1 FROM tbl_doc_plantillas WHERE codigo_sugerido = 'PLN-OBJ')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);
SQL;

// ============================================
// SQL 5: Mapear a carpeta del estándar
// ============================================
$sqlMapeoCarpeta = <<<'SQL'
INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
VALUES ('PLN-OBJ', '2.2.1')
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
        echo "  → Insertando secciones (9 secciones)...\n";
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
        } else {
            echo "    ⚠ Tabla tbl_doc_plantillas no existe, saltando...\n";
        }

        // 5. Mapear carpeta
        echo "  → Mapeando carpeta...\n";
        $tablaMapeoExists = $pdo->query("SHOW TABLES LIKE 'tbl_doc_plantilla_carpeta'")->rowCount() > 0;
        if ($tablaMapeoExists) {
            $pdo->exec($sqlMapeoCarpeta);
            echo "    ✓ Mapeo configurado\n";
        } else {
            echo "    ⚠ Tabla tbl_doc_plantilla_carpeta no existe, saltando...\n";
        }

        // Verificar inserción
        echo "  → Verificando configuración...\n";
        $verificar = $pdo->query("
            SELECT tc.tipo_documento, tc.nombre,
                   (SELECT COUNT(*) FROM tbl_doc_secciones_config sc WHERE sc.id_tipo_config = tc.id_tipo_config) as secciones,
                   (SELECT COUNT(*) FROM tbl_doc_firmantes_config fc WHERE fc.id_tipo_config = tc.id_tipo_config) as firmantes
            FROM tbl_doc_tipo_configuracion tc
            WHERE tc.tipo_documento = 'plan_objetivos_metas'
        ")->fetch();

        if ($verificar) {
            echo "    ✓ Tipo: {$verificar['tipo_documento']}\n";
            echo "    ✓ Nombre: {$verificar['nombre']}\n";
            echo "    ✓ Secciones: {$verificar['secciones']}\n";
            echo "    ✓ Firmantes: {$verificar['firmantes']}\n";
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
echo "1. Clase: app/Libraries/DocumentosSSTTypes/PlanObjetivosMetas.php\n";
echo "2. Factory: app/Libraries/DocumentosSSTTypes/DocumentoSSTFactory.php (línea 43)\n";
echo "3. URL: http://localhost/enterprisesst/public/documentos/generar/plan_objetivos_metas/{id_cliente}\n";
