<?php
/**
 * Migración: Agregar columna metricas_desglose_json a tbl_informe_avances
 * Almacena desgloses por pilar (datos numéricos + imágenes base64 de Chart.js)
 *
 * Uso: DB_PROD_PASS=xxx php app/SQL/migrate_informe_metricas_v2.php production
 *      php app/SQL/migrate_informe_metricas_v2.php local
 */

$env = $argv[1] ?? 'local';

if ($env === 'production') {
    $pass = getenv('DB_PROD_PASS');
    if (!$pass) { echo "ERROR: Set DB_PROD_PASS env var\n"; exit(1); }
    $db = mysqli_init();
    $db->ssl_set(NULL, NULL, NULL, NULL, NULL);
    $db->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
    $db->real_connect(
        'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'cycloid_userdb', $pass, 'propiedad_horizontal', 25060, NULL, MYSQLI_CLIENT_SSL
    );
} else {
    $db = new mysqli('localhost', 'root', '', 'propiedad_horizontal');
}

if ($db->connect_error) { echo "ERROR conexión: {$db->connect_error}\n"; exit(1); }
$db->set_charset('utf8mb4');
echo "Conectado a: $env\n\n";

// Verificar si la columna ya existe
$check = $db->query("SHOW COLUMNS FROM tbl_informe_avances LIKE 'metricas_desglose_json'");
if ($check && $check->num_rows > 0) {
    echo "La columna metricas_desglose_json ya existe. Nada que hacer.\n";
    $db->close();
    exit(0);
}

// Agregar columna
$sql = "ALTER TABLE tbl_informe_avances ADD COLUMN metricas_desglose_json LONGTEXT NULL AFTER img_indicador_capacitacion";

if ($db->query($sql)) {
    echo "OK: Columna metricas_desglose_json agregada exitosamente.\n";
} else {
    echo "ERROR: {$db->error}\n";
    $db->close();
    exit(1);
}

// Verificar
$verify = $db->query("SHOW COLUMNS FROM tbl_informe_avances LIKE 'metricas_desglose_json'");
if ($verify && $verify->num_rows > 0) {
    $col = $verify->fetch_assoc();
    echo "Verificación: columna '{$col['Field']}' tipo '{$col['Type']}' nullable '{$col['Null']}'\n";
} else {
    echo "ADVERTENCIA: No se pudo verificar la columna.\n";
}

$db->close();
echo "\nMigración completada en $env.\n";
