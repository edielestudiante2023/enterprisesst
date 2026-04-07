<?php
/**
 * Migración: Agregar columna actividades_no_cerradas_pta a tbl_informe_avances
 *
 * Uso: php app/SQL/migrate_informe_no_cerradas.php [local|production]
 * Producción: DB_PROD_PASS=xxx php app/SQL/migrate_informe_no_cerradas.php production
 */

$env = $argv[1] ?? 'local';

$configs = [
    'local' => [
        'host' => 'localhost',
        'user' => 'root',
        'pass' => '',
        'db'   => 'empresas_sst',
        'ssl'  => false,
    ],
    'production' => [
        'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port' => 25060,
        'user' => 'cycloid_userdb',
        'pass' => getenv('DB_PROD_PASS') ?: '',
        'db'   => 'empresas_sst',
        'ssl'  => true,
    ],
];

if (!isset($configs[$env])) {
    echo "Uso: php app/SQL/migrate_informe_no_cerradas.php [local|production]\n";
    exit(1);
}

$cfg = $configs[$env];
echo "=== Migración: actividades_no_cerradas_pta - Entorno: {$env} ===\n\n";

if ($env === 'production' && empty($cfg['pass'])) {
    echo "ERROR: Variable DB_PROD_PASS no definida.\n";
    exit(1);
}

$conn = new mysqli();
if ($cfg['ssl'] ?? false) {
    $conn->ssl_set(null, null, null, null, null);
    $conn->real_connect($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['db'], $cfg['port'] ?? 3306, null, MYSQLI_CLIENT_SSL);
} else {
    $conn->real_connect($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['db'], $cfg['port'] ?? 3306);
}

if ($conn->connect_error) {
    echo "ERROR: " . $conn->connect_error . "\n";
    exit(1);
}

echo "Conectado a {$cfg['db']}@{$cfg['host']}\n\n";

$check = $conn->query("SHOW COLUMNS FROM tbl_informe_avances LIKE 'actividades_no_cerradas_pta'");
if ($check && $check->num_rows > 0) {
    echo "[SKIP] Columna 'actividades_no_cerradas_pta' ya existe.\n";
} else {
    $sql = "ALTER TABLE tbl_informe_avances ADD COLUMN actividades_no_cerradas_pta TEXT DEFAULT NULL AFTER actividades_cerradas_periodo";
    if ($conn->query($sql)) {
        echo "[OK] Columna 'actividades_no_cerradas_pta' agregada.\n";
    } else {
        echo "[ERROR] " . $conn->error . "\n";
        $conn->close();
        exit(1);
    }
}

echo "\n=== Migración completada ===\n";
$conn->close();
