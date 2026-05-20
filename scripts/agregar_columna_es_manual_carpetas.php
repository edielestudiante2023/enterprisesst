<?php
/**
 * Agrega la columna tbl_doc_carpetas.es_manual TINYINT(1) NOT NULL DEFAULT 0
 * Marca las carpetas agregadas manualmente (estandares de otro nivel) para:
 *   - mostrarlas/gestionarlas en la UI
 *   - re-crearlas tras "Regenerar estructura" (protegerlas del borrado del SP)
 *
 * Uso:
 *   php scripts/agregar_columna_es_manual_carpetas.php                    # LOCAL  dry-run
 *   php scripts/agregar_columna_es_manual_carpetas.php --apply            # LOCAL  aplica
 *   php scripts/agregar_columna_es_manual_carpetas.php --env=prod         # PROD   dry-run
 *   php scripts/agregar_columna_es_manual_carpetas.php --env=prod --apply # PROD   aplica
 */

$argvList     = $argv ?? [];
$esProduccion = in_array('--env=prod', $argvList, true);
$aplicar      = in_array('--apply', $argvList, true);

if ($esProduccion) {
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port = 25060; $dbname = 'empresas_sst';
    $username = 'cycloid_userdb'; $password = 'AVNS_MR2SLvzRh3i_7o9fEHN';
    $ssl = true; echo "=== PRODUCCION ===\n";
} else {
    $host = '127.0.0.1'; $port = 3306; $dbname = 'empresas_sst';
    $username = 'root'; $password = ''; $ssl = false; echo "=== LOCAL ===\n";
}
echo "Modo: " . ($aplicar ? "APLICAR (ALTER)" : "DRY RUN (solo lectura)") . "\n\n";

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    if ($ssl) { $options[PDO::MYSQL_ATTR_SSL_CA] = true; $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false; }
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "Conexion OK\n\n";
} catch (Throwable $e) { echo "ERROR conexion: " . $e->getMessage() . "\n"; exit(1); }

// Verificar si la columna ya existe
$st = $pdo->prepare(
    "SELECT COUNT(*) FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'tbl_doc_carpetas' AND COLUMN_NAME = 'es_manual'"
);
$st->execute([$dbname]);
$existe = (int) $st->fetchColumn() > 0;

echo "Columna es_manual en tbl_doc_carpetas: " . ($existe ? "YA EXISTE" : "NO existe") . "\n\n";

if ($existe) {
    echo "Nada que hacer.\n";
    exit(0);
}

if (!$aplicar) {
    echo "DRY RUN: se agregaria la columna. Usa --apply para ejecutar.\n";
    echo "  ALTER TABLE tbl_doc_carpetas ADD COLUMN es_manual TINYINT(1) NOT NULL DEFAULT 0 AFTER id_estandar;\n";
    exit(0);
}

try {
    $pdo->exec("ALTER TABLE tbl_doc_carpetas ADD COLUMN es_manual TINYINT(1) NOT NULL DEFAULT 0 AFTER id_estandar");
    echo "OK: columna es_manual agregada.\n";
} catch (Throwable $e) {
    echo "ERROR al agregar columna: " . $e->getMessage() . "\n";
    exit(1);
}
