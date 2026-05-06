<?php
/**
 * Agrega columna motivo_sin_firma a tbl_acta_visita.
 *
 * Permite finalizar acta de visita sin firma del cliente, registrando el motivo
 * (mismo patron que enterprisesstph).
 *
 * Uso:
 *   php scripts/acta_visita_add_motivo_sin_firma.php             # LOCAL
 *   php scripts/acta_visita_add_motivo_sin_firma.php --env=prod  # PRODUCCION
 */

$esProduccion = in_array('--env=prod', $argv ?? []);

if ($esProduccion) {
    $host     = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port     = 25060;
    $dbname   = 'empresas_sst';
    $username = 'cycloid_userdb';
    $password = getenv('DB_PROD_PASS') ?: 'AVNS_MR2SLvzRh3i_7o9fEHN';
    $ssl      = true;
    echo "=== PRODUCCION ===\n";
} else {
    $host     = '127.0.0.1';
    $port     = 3306;
    $dbname   = 'empresas_sst';
    $username = 'root';
    $password = '';
    $ssl      = false;
    echo "=== LOCAL ===\n";
}

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    if ($ssl) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = true;
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "Conexion OK\n\n";
} catch (Throwable $e) {
    echo "ERROR conexion: " . $e->getMessage() . "\n";
    exit(1);
}

// Precheck: tabla existe
$existe = $pdo->query("SHOW TABLES LIKE 'tbl_acta_visita'")->fetchAll();
if (!$existe) {
    echo "ERR: tabla tbl_acta_visita no existe\n";
    exit(1);
}
echo "OK tabla tbl_acta_visita existe\n";

// Idempotencia: si la columna ya existe, no hacer nada
$colExiste = $pdo->query("SHOW COLUMNS FROM tbl_acta_visita LIKE 'motivo_sin_firma'")->fetchAll();
if ($colExiste) {
    echo "OK columna motivo_sin_firma YA existe (nada que hacer)\n";
    exit(0);
}

try {
    $pdo->exec("ALTER TABLE tbl_acta_visita ADD COLUMN motivo_sin_firma VARCHAR(255) NULL AFTER firma_consultor");
    echo "OK columna motivo_sin_firma agregada\n";
} catch (Throwable $e) {
    echo "ERR ALTER TABLE: " . $e->getMessage() . "\n";
    exit(1);
}

// Verificacion post
$verif = $pdo->query("SHOW COLUMNS FROM tbl_acta_visita LIKE 'motivo_sin_firma'")->fetch(PDO::FETCH_ASSOC);
if ($verif) {
    echo "VERIF: " . json_encode($verif) . "\n";
}

echo "\n=== Listo ===\n";
