<?php
/**
 * Script CLI para agregar columna id_documento_sst a las 4 tablas de inspecciones.
 * Integra inspecciones con el sistema de versionamiento (tbl_documentos_sst).
 *
 * Uso: php migrate_inspecciones_versionamiento.php [local|production]
 *
 * Modifica:
 *   - tbl_acta_visita           → ADD id_documento_sst INT NULL
 *   - tbl_inspeccion_extintores → ADD id_documento_sst INT NULL
 *   - tbl_inspeccion_botiquin   → ADD id_documento_sst INT NULL
 *   - tbl_inspeccion_locativa   → ADD id_documento_sst INT NULL
 */

if (php_sapi_name() !== 'cli') {
    die('Este script solo puede ejecutarse desde la línea de comandos.');
}

$env = $argv[1] ?? 'local';

if ($env === 'local') {
    $config = [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'root',
        'password' => '',
        'database' => 'empresas_sst',
        'ssl'      => false,
    ];
} elseif ($env === 'production') {
    $config = [
        'host'     => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port'     => 25060,
        'user'     => 'cycloid_userdb',
        'password' => 'AVNS_iDypWizlpMRwHIORJGG',
        'database' => 'empresas_sst',
        'ssl'      => true,
    ];
} else {
    die("Uso: php migrate_inspecciones_versionamiento.php [local|production]\n");
}

echo "=== Migración SQL - Integración Inspecciones con Versionamiento ===\n";
echo "Entorno: " . strtoupper($env) . "\n";
echo "Host: {$config['host']}:{$config['port']}\n";
echo "Database: {$config['database']}\n";
echo "---\n";

$mysqli = mysqli_init();

if ($config['ssl']) {
    $mysqli->ssl_set(null, null, null, null, null);
    $mysqli->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
}

$connected = @$mysqli->real_connect(
    $config['host'],
    $config['user'],
    $config['password'],
    $config['database'],
    $config['port'],
    null,
    $config['ssl'] ? MYSQLI_CLIENT_SSL : 0
);

if (!$connected) {
    die("ERROR de conexión: " . $mysqli->connect_error . "\n");
}

echo "Conexión exitosa.\n\n";

$success = 0;
$errors  = 0;
$total   = 0;

/**
 * Helper: agrega columna solo si no existe ya.
 */
function addColumnIfNotExists(mysqli $db, string $table, string $column, string $definition): array
{
    $check = $db->query(
        "SELECT COUNT(*) AS cnt FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = '{$table}'
           AND COLUMN_NAME = '{$column}'"
    );
    $row = $check->fetch_assoc();
    if ((int)$row['cnt'] > 0) {
        return ['skipped' => true, 'msg' => "columna '{$column}' ya existe"];
    }
    $sql = "ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}";
    if ($db->query($sql)) {
        return ['skipped' => false, 'msg' => 'OK'];
    }
    return ['skipped' => false, 'error' => $db->error];
}

$alteraciones = [
    [
        'desc'   => "ADD id_documento_sst a tbl_acta_visita",
        'table'  => 'tbl_acta_visita',
        'column' => 'id_documento_sst',
        'def'    => 'INT NULL COMMENT \'FK a tbl_documentos_sst para versionamiento SST\'',
    ],
    [
        'desc'   => "ADD id_documento_sst a tbl_inspeccion_extintores",
        'table'  => 'tbl_inspeccion_extintores',
        'column' => 'id_documento_sst',
        'def'    => 'INT NULL COMMENT \'FK a tbl_documentos_sst para versionamiento SST\'',
    ],
    [
        'desc'   => "ADD id_documento_sst a tbl_inspeccion_botiquin",
        'table'  => 'tbl_inspeccion_botiquin',
        'column' => 'id_documento_sst',
        'def'    => 'INT NULL COMMENT \'FK a tbl_documentos_sst para versionamiento SST\'',
    ],
    [
        'desc'   => "ADD id_documento_sst a tbl_inspeccion_locativa",
        'table'  => 'tbl_inspeccion_locativa',
        'column' => 'id_documento_sst',
        'def'    => 'INT NULL COMMENT \'FK a tbl_documentos_sst para versionamiento SST\'',
    ],
];

foreach ($alteraciones as $alt) {
    $total++;
    echo "[{$total}] {$alt['desc']}... ";

    $result = addColumnIfNotExists($mysqli, $alt['table'], $alt['column'], $alt['def']);

    if (isset($result['error'])) {
        echo "ERROR: {$result['error']}\n";
        $errors++;
    } elseif ($result['skipped']) {
        echo "SKIP ({$result['msg']})\n";
        $success++;
    } else {
        echo "OK\n";
        $success++;
    }
}

echo "\n=== RESULTADO ===\n";
echo "Exitosas: {$success}\n";
echo "Errores: {$errors}\n";
echo "Total: {$total}\n";

if ($errors === 0) {
    echo "MIGRACIÓN COMPLETADA SIN ERRORES.\n";
} else {
    echo "HAY ERRORES - REVISAR ANTES DE CONTINUAR.\n";
}

$mysqli->close();
