<?php
/**
 * Migración: Agregar columna fecha_cierre_real a tbl_pendientes
 *
 * Uso:
 *   LOCAL:      php app/SQL/add_fecha_cierre_real.php
 *   PRODUCCIÓN: DB_PROD_PASS=xxx php app/SQL/add_fecha_cierre_real.php production
 */

$entorno = $argv[1] ?? 'local';

if ($entorno === 'production') {
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port = 25060;
    $user = 'cycloid_userdb';
    $pass = getenv('DB_PROD_PASS');
    $db   = 'empresas_sst';

    if (!$pass) {
        echo "ERROR: Variable DB_PROD_PASS no definida.\n";
        echo "Uso: DB_PROD_PASS=xxx php app/SQL/add_fecha_cierre_real.php production\n";
        exit(1);
    }

    $flags = MYSQLI_CLIENT_SSL;
    echo "=== PRODUCCIÓN (empresas_sst @ DigitalOcean) ===\n";
} else {
    $host = '127.0.0.1';
    $port = 3306;
    $user = 'root';
    $pass = '';
    $db   = 'empresas_sst';
    $flags = 0;
    echo "=== LOCAL (empresas_sst @ localhost) ===\n";
}

$mysqli = mysqli_init();
if ($entorno === 'production') {
    $mysqli->ssl_set(null, null, null, null, null);
}
if (!$mysqli->real_connect($host, $user, $pass, $db, $port, null, $flags)) {
    echo "ERROR conexión: " . $mysqli->connect_error . "\n";
    exit(1);
}
$mysqli->set_charset('utf8mb4');
echo "Conectado OK.\n\n";

// --- 1. Verificar si la columna ya existe ---
$check = $mysqli->query("SHOW COLUMNS FROM tbl_pendientes LIKE 'fecha_cierre_real'");
if ($check->num_rows > 0) {
    echo "La columna fecha_cierre_real ya existe. Saltando ALTER.\n\n";
} else {
    $sql = "ALTER TABLE tbl_pendientes ADD COLUMN fecha_cierre_real DATE NULL AFTER fecha_cierre";
    if ($mysqli->query($sql)) {
        echo "OK: Columna fecha_cierre_real agregada.\n\n";
    } else {
        echo "ERROR ALTER: " . $mysqli->error . "\n";
        exit(1);
    }
}

// --- 2. Backfill: pendientes ya cerradas → fecha_cierre_real = DATE(updated_at) ---
$sql = "UPDATE tbl_pendientes
        SET fecha_cierre_real = DATE(updated_at)
        WHERE estado IN ('CERRADA', 'CERRADA POR FIN CONTRATO', 'SIN RESPUESTA DEL CLIENTE')
          AND fecha_cierre_real IS NULL
          AND updated_at IS NOT NULL";
if ($mysqli->query($sql)) {
    echo "OK: Backfill completado. Filas afectadas: " . $mysqli->affected_rows . "\n\n";
} else {
    echo "ERROR backfill: " . $mysqli->error . "\n";
    exit(1);
}

// --- 3. Validación ---
echo "=== VALIDACIÓN ===\n";
$result = $mysqli->query("
    SELECT estado,
        COUNT(*) AS total,
        SUM(fecha_cierre_real IS NOT NULL) AS con_cierre_real,
        SUM(fecha_cierre_real IS NULL) AS sin_cierre_real
    FROM tbl_pendientes
    GROUP BY estado
    ORDER BY estado
");
printf("%-30s %6s %12s %12s\n", 'ESTADO', 'TOTAL', 'CON_REAL', 'SIN_REAL');
printf("%s\n", str_repeat('-', 62));
while ($row = $result->fetch_assoc()) {
    printf("%-30s %6d %12d %12d\n", $row['estado'], $row['total'], $row['con_cierre_real'], $row['sin_cierre_real']);
}

echo "\nMigración completada.\n";
$mysqli->close();
