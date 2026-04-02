<?php
/**
 * Migración: Eliminar FK del consultor en tbl_informe_avances
 * Permite que id_consultor sea NULL (para informes generados por API sin sesión).
 *
 * Uso: php app/SQL/migrate_drop_fk_informe_avances.php [local|production]
 * Producción: DB_PROD_PASS=xxx php app/SQL/migrate_drop_fk_informe_avances.php production
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
    echo "Uso: php app/SQL/migrate_drop_fk_informe_avances.php [local|production]\n";
    exit(1);
}

$cfg = $configs[$env];
echo "=== Migración: Drop FK consultor en tbl_informe_avances - Entorno: {$env} ===\n\n";

if ($env === 'production' && empty($cfg['pass'])) {
    echo "ERROR: Variable DB_PROD_PASS no definida.\n";
    echo "Uso: DB_PROD_PASS=xxx php app/SQL/migrate_drop_fk_informe_avances.php production\n";
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
    echo "ERROR de conexión: " . $conn->connect_error . "\n";
    exit(1);
}

echo "Conectado a {$cfg['db']}@{$cfg['host']}\n\n";

// 1. Buscar FK de consultor
$result = $conn->query("
    SELECT CONSTRAINT_NAME
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = '{$cfg['db']}'
      AND TABLE_NAME = 'tbl_informe_avances'
      AND CONSTRAINT_TYPE = 'FOREIGN KEY'
      AND CONSTRAINT_NAME LIKE '%consultor%'
");

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $fkName = $row['CONSTRAINT_NAME'];
        echo "Eliminando FK: {$fkName}... ";
        if ($conn->query("ALTER TABLE tbl_informe_avances DROP FOREIGN KEY `{$fkName}`")) {
            echo "OK\n";
        } else {
            echo "ERROR: " . $conn->error . "\n";
        }
    }
} else {
    echo "[SKIP] No se encontró FK de consultor.\n";
}

// 2. Asegurar que id_consultor permite NULL
echo "Asegurando que id_consultor permite NULL... ";
if ($conn->query("ALTER TABLE tbl_informe_avances MODIFY COLUMN id_consultor INT DEFAULT NULL")) {
    echo "OK\n";
} else {
    echo "ERROR: " . $conn->error . "\n";
}

echo "\n=== Migración completada ===\n";
$conn->close();
