<?php
/**
 * Script para agregar tipo de documento: Manual de Convivencia Laboral
 * Estandar: 1.1.8 - Conformacion Comite de Convivencia
 *
 * Basado en Resolucion 3461 de 2025, Ley 1010 de 2006, Ley 2406 de 2025
 *
 * Ejecutar: php app/SQL/agregar_manual_convivencia_laboral.php
 *
 * IMPORTANTE: Este documento NO usa IA - es contenido 100% estatico
 */

echo "=== Agregando Manual de Convivencia Laboral ===\n\n";

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

// SQL para tipo de documento
$sqlTipo = <<<'SQL'
INSERT INTO tbl_doc_tipo_configuracion
(tipo_documento, nombre, descripcion, estandar, flujo, categoria, icono, orden, activo)
VALUES
('manual_convivencia_laboral',
 'Manual de Convivencia Laboral',
 'Manual que establece las normas de comportamiento, conductas aceptables y no aceptables, y mecanismos de resolucion de conflictos en el entorno laboral. Basado en Resolucion 3461 de 2025, Ley 1010 de 2006 y Convenio C190 de la OIT.',
 '1.1.8',
 'formulario',
 'reglamentos',
 'bi-people',
 15,
 1)
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    descripcion = VALUES(descripcion),
    estandar = VALUES(estandar),
    flujo = VALUES(flujo),
    categoria = VALUES(categoria),
    updated_at = NOW();
SQL;

// SQL para secciones (contenido estatico, no requiere prompt_ia)
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
    SELECT 1 as numero, 'Introduccion y Proposito' as nombre, 'introduccion_proposito' as seccion_key, 'texto' as tipo_contenido, NULL as tabla_dinamica_tipo, 1 as orden, NULL as prompt_ia, 1 as es_obligatoria
    UNION SELECT 2, 'Fundamentacion Normativa', 'fundamentacion_normativa', 'texto', NULL, 2, NULL, 1
    UNION SELECT 3, 'Objetivo Principal', 'objetivo_principal', 'texto', NULL, 3, NULL, 1
    UNION SELECT 4, 'Objetivos Generales', 'objetivos_generales', 'lista', NULL, 4, NULL, 1
    UNION SELECT 5, 'Alcance', 'alcance', 'texto', NULL, 5, NULL, 1
    UNION SELECT 6, 'Valores Corporativos', 'valores_corporativos', 'lista', NULL, 6, NULL, 1
    UNION SELECT 7, 'Conductas Aceptables', 'conductas_aceptables', 'texto', NULL, 7, NULL, 1
    UNION SELECT 8, 'Conductas NO Aceptables', 'conductas_no_aceptables', 'texto', NULL, 8, NULL, 1
    UNION SELECT 9, 'Comportamientos Prohibidos', 'comportamientos_prohibidos', 'lista', NULL, 9, NULL, 1
    UNION SELECT 10, 'Seguridad Laboral', 'seguridad_laboral', 'lista', NULL, 10, NULL, 1
    UNION SELECT 11, 'Resolucion de Conflictos', 'resolucion_conflictos', 'lista', NULL, 11, NULL, 1
    UNION SELECT 12, 'Procedimiento para Reportar Conductas', 'procedimiento_reportes', 'texto', NULL, 12, NULL, 1
    UNION SELECT 13, 'Sanciones y Procedimiento', 'sanciones', 'texto', NULL, 13, NULL, 1
    UNION SELECT 14, 'Roles y Responsabilidades', 'roles_responsabilidades', 'lista', NULL, 14, NULL, 1
    UNION SELECT 15, 'Difusion del Manual', 'difusion_manual', 'lista', NULL, 15, NULL, 1
    UNION SELECT 16, 'Aceptacion y Compromiso', 'aceptacion_compromiso', 'texto', NULL, 16, NULL, 1
) s
WHERE tc.tipo_documento = 'manual_convivencia_laboral'
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    tipo_contenido = VALUES(tipo_contenido),
    orden = VALUES(orden);
SQL;

// SQL para firmantes (2 firmantes: Elaboro + Aprobo)
$sqlFirmantes = <<<'SQL'
INSERT INTO tbl_doc_firmantes_config
(id_tipo_config, firmante_tipo, rol_display, columna_encabezado, orden, es_obligatorio, mostrar_licencia, mostrar_cedula, activo)
SELECT
    tc.id_tipo_config,
    f.firmante_tipo,
    f.rol_display,
    f.columna_encabezado,
    f.orden,
    f.es_obligatorio,
    f.mostrar_licencia,
    f.mostrar_cedula,
    1
FROM tbl_doc_tipo_configuracion tc
CROSS JOIN (
    SELECT 'responsable_sst' as firmante_tipo, 'Elaboro' as rol_display, 'Elaboro / Responsable del SG-SST' as columna_encabezado, 1 as orden, 1 as es_obligatorio, 1 as mostrar_licencia, 0 as mostrar_cedula
    UNION SELECT 'representante_legal', 'Aprobo', 'Aprobo / Representante Legal', 2, 1, 0, 1
) f
WHERE tc.tipo_documento = 'manual_convivencia_laboral'
ON DUPLICATE KEY UPDATE
    rol_display = VALUES(rol_display),
    columna_encabezado = VALUES(columna_encabezado);
SQL;

// SQL para plantilla (usa codigo 'REG' = Reglamento, id_tipo = 14)
$sqlPlantilla = <<<'SQL'
INSERT INTO tbl_doc_plantillas (id_tipo, nombre, codigo_sugerido, tipo_documento, version, activo)
SELECT
    (SELECT id_tipo FROM tbl_doc_tipos WHERE codigo = 'REG' LIMIT 1),
    'Manual de Convivencia Laboral',
    'MAN-CVL',
    'manual_convivencia_laboral',
    '001',
    1
WHERE NOT EXISTS (
    SELECT 1 FROM tbl_doc_plantillas WHERE tipo_documento = 'manual_convivencia_laboral'
);
SQL;

// SQL para mapeo plantilla-carpeta
$sqlMapeo = <<<'SQL'
INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
SELECT 'MAN-CVL', '1.1.8'
WHERE NOT EXISTS (
    SELECT 1 FROM tbl_doc_plantilla_carpeta
    WHERE codigo_plantilla = 'MAN-CVL' AND codigo_carpeta = '1.1.8'
);
SQL;

function ejecutarSQL($conexion, $nombre, $sqls) {
    echo "Conectando a {$nombre}...\n";

    try {
        $dsn = "mysql:host={$conexion['host']};port={$conexion['port']};dbname={$conexion['database']};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        if ($conexion['ssl']) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = true;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        $pdo = new PDO($dsn, $conexion['username'], $conexion['password'], $options);
        echo "  Conectado a {$nombre}\n";

        foreach ($sqls as $descripcion => $sql) {
            echo "  Ejecutando: {$descripcion}... ";
            $pdo->exec($sql);
            echo "OK\n";
        }

        echo "  {$nombre} completado\n\n";
        return true;

    } catch (PDOException $e) {
        echo "  ERROR: " . $e->getMessage() . "\n\n";
        return false;
    }
}

// Ejecutar en local
$sqls = [
    'Tipo de documento' => $sqlTipo,
    'Secciones' => $sqlSecciones,
    'Firmantes' => $sqlFirmantes,
    'Plantilla' => $sqlPlantilla,
    'Mapeo carpeta' => $sqlMapeo
];

$localOK = ejecutarSQL($conexiones['local'], 'LOCAL', $sqls);

// Solo ejecutar en produccion si local fue exitoso
if ($localOK) {
    echo "LOCAL exitoso. Ejecutando en PRODUCCION...\n\n";
    ejecutarSQL($conexiones['produccion'], 'PRODUCCION', $sqls);
} else {
    echo "ERROR en LOCAL. No se ejecutara en PRODUCCION.\n";
}

echo "\n=== Proceso completado ===\n";
echo "Recuerda:\n";
echo "1. Crear clase PHP: app/Libraries/DocumentosSSTTypes/ManualConvivenciaLaboral.php\n";
echo "2. Registrar en: app/Libraries/DocumentosSSTTypes/DocumentoSSTFactory.php\n";
echo "3. Crear vista tipo: app/Views/documentacion/_tipos/manual_convivencia_1_1_8.php\n";
echo "4. Agregar deteccion en DocumentacionController::determinarTipo()\n";
