<?php
/**
 * Script para agregar soporte de sincronizacion de PROGRAMAS
 *
 * Agrega columna sincronizar_bd a tbl_doc_secciones_config
 * para indicar que secciones deben enviarse a BD al aprobar
 *
 * Ejecutar: php app/SQL/agregar_sincronizacion_programas.php
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
        'host' => getenv('DB_PROD_HOST') ?: 'TU_HOST_PRODUCCION',
        'port' => getenv('DB_PROD_PORT') ?: 25060,
        'database' => getenv('DB_PROD_DATABASE') ?: 'empresas_sst',
        'username' => getenv('DB_PROD_USERNAME') ?: 'TU_USUARIO',
        'password' => getenv('DB_PROD_PASSWORD') ?: 'TU_PASSWORD',
        'ssl' => true
    ]
];

// SQL para agregar columna sincronizar_bd a secciones
$sqlAlterSecciones = <<<'SQL'
ALTER TABLE tbl_doc_secciones_config
ADD COLUMN IF NOT EXISTS sincronizar_bd VARCHAR(50) NULL
COMMENT 'Tabla destino al aprobar: pta_cliente, indicadores_sst, NULL=no sincroniza'
AFTER tabla_dinamica_tipo;
SQL;

// SQL para verificar/agregar columna categoria a tipo_configuracion
$sqlAlterTipos = <<<'SQL'
ALTER TABLE tbl_doc_tipo_configuracion
MODIFY COLUMN categoria VARCHAR(50) NULL
COMMENT 'Categoria: programas, procedimientos, politicas, matrices, planes, reglamentos';
SQL;

// SQL para agregar columna id_documento_origen a tbl_pta_cliente (para trazabilidad)
$sqlAlterPTA = <<<'SQL'
ALTER TABLE tbl_pta_cliente
ADD COLUMN IF NOT EXISTS id_documento_origen INT NULL
COMMENT 'ID del documento SST que genero esta actividad'
AFTER id_cliente;
SQL;

// SQL para agregar columna id_documento_origen a tbl_indicadores_sst (para trazabilidad)
$sqlAlterIndicadores = <<<'SQL'
ALTER TABLE tbl_indicadores_sst
ADD COLUMN IF NOT EXISTS id_documento_origen INT NULL
COMMENT 'ID del documento SST que genero este indicador'
AFTER id_cliente;
SQL;

// Los 9 numerales que son PROGRAMAS - actualizar categoria
$sqlActualizarProgramas = <<<'SQL'
UPDATE tbl_doc_tipo_configuracion
SET categoria = 'programas'
WHERE tipo_documento IN (
    'programa_capacitacion',
    'programa_induccion_reinduccion',
    'programa_promocion_prevencion_salud',
    'programa_estilos_vida_saludables',
    'programa_seguridad',
    'programa_inspecciones',
    'programa_mantenimiento',
    'plan_emergencias',
    'programa_brigada'
);
SQL;

// Marcar secciones que deben sincronizar con BD
$sqlMarcarSeccionesPTA = <<<'SQL'
UPDATE tbl_doc_secciones_config sc
JOIN tbl_doc_tipo_configuracion tc ON sc.id_tipo_config = tc.id_tipo_config
SET sc.sincronizar_bd = 'pta_cliente'
WHERE tc.categoria = 'programas'
AND sc.seccion_key IN ('cronograma', 'actividades', 'plan_trabajo');
SQL;

$sqlMarcarSeccionesIndicadores = <<<'SQL'
UPDATE tbl_doc_secciones_config sc
JOIN tbl_doc_tipo_configuracion tc ON sc.id_tipo_config = tc.id_tipo_config
SET sc.sincronizar_bd = 'indicadores_sst'
WHERE tc.categoria = 'programas'
AND sc.seccion_key = 'indicadores';
SQL;

function ejecutarEnEntorno($config, $sqls, $nombreEntorno) {
    echo "\n========================================\n";
    echo "Ejecutando en: {$nombreEntorno}\n";
    echo "========================================\n";

    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        if ($config['ssl']) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = true;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        echo "Conectado exitosamente a {$nombreEntorno}\n\n";

        foreach ($sqls as $nombre => $sql) {
            echo "Ejecutando: {$nombre}... ";
            try {
                $pdo->exec($sql);
                echo "OK\n";
            } catch (PDOException $e) {
                // Ignorar errores de columna ya existe
                if (strpos($e->getMessage(), 'Duplicate column') !== false ||
                    strpos($e->getMessage(), 'DUPLICATE') !== false) {
                    echo "Ya existe (OK)\n";
                } else {
                    echo "ERROR: " . $e->getMessage() . "\n";
                }
            }
        }

        // Verificar resultado
        echo "\n--- Verificacion ---\n";

        // Verificar programas
        $stmt = $pdo->query("
            SELECT tipo_documento, nombre, categoria
            FROM tbl_doc_tipo_configuracion
            WHERE categoria = 'programas'
            ORDER BY estandar
        ");
        $programas = $stmt->fetchAll();
        echo "\nProgramas configurados: " . count($programas) . "\n";
        foreach ($programas as $p) {
            echo "  - {$p['tipo_documento']}: {$p['nombre']}\n";
        }

        // Verificar secciones con sincronizacion
        $stmt = $pdo->query("
            SELECT tc.tipo_documento, sc.seccion_key, sc.sincronizar_bd
            FROM tbl_doc_secciones_config sc
            JOIN tbl_doc_tipo_configuracion tc ON sc.id_tipo_config = tc.id_tipo_config
            WHERE sc.sincronizar_bd IS NOT NULL
            ORDER BY tc.tipo_documento, sc.orden
        ");
        $secciones = $stmt->fetchAll();
        echo "\nSecciones con sincronizacion: " . count($secciones) . "\n";
        foreach ($secciones as $s) {
            echo "  - {$s['tipo_documento']}.{$s['seccion_key']} -> {$s['sincronizar_bd']}\n";
        }

        return true;

    } catch (PDOException $e) {
        echo "ERROR de conexion: " . $e->getMessage() . "\n";
        return false;
    }
}

// Preparar SQLs
$sqls = [
    '1. Agregar columna sincronizar_bd a secciones' => $sqlAlterSecciones,
    '2. Modificar columna categoria en tipos' => $sqlAlterTipos,
    '3. Agregar id_documento_origen a PTA' => $sqlAlterPTA,
    '4. Agregar id_documento_origen a Indicadores' => $sqlAlterIndicadores,
    '5. Marcar tipos como programas' => $sqlActualizarProgramas,
    '6. Marcar secciones PTA' => $sqlMarcarSeccionesPTA,
    '7. Marcar secciones Indicadores' => $sqlMarcarSeccionesIndicadores,
];

// Ejecutar automaticamente en ambos entornos
echo "===========================================\n";
echo "AGREGAR SOPORTE SINCRONIZACION PROGRAMAS\n";
echo "===========================================\n";

// Ejecutar en LOCAL
ejecutarEnEntorno($conexiones['local'], $sqls, 'LOCAL');

// Ejecutar en PRODUCCION
ejecutarEnEntorno($conexiones['produccion'], $sqls, 'PRODUCCION');

echo "\n========================================\n";
echo "Proceso completado en AMBOS entornos\n";
echo "========================================\n";
