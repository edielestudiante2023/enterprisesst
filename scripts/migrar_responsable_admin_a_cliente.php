<?php
/**
 * Normaliza el valor del campo responsable en tbl_pendientes:
 *   'ADMINISTRADOR'  ->  'CLIENTE'
 *
 * Acompana al cambio del desplegable en edit_pendiente.php (la opcion paso de
 * "ADMINISTRADOR" a "CLIENTE"). Migra los registros viejos para uniformidad.
 *
 * Uso:
 *   php scripts/migrar_responsable_admin_a_cliente.php                     # LOCAL  dry-run
 *   php scripts/migrar_responsable_admin_a_cliente.php --apply             # LOCAL  aplica
 *   php scripts/migrar_responsable_admin_a_cliente.php --env=prod          # PROD   dry-run
 *   php scripts/migrar_responsable_admin_a_cliente.php --env=prod --apply  # PROD   aplica
 */

$argvList     = $argv ?? [];
$esProduccion = in_array('--env=prod', $argvList, true);
$aplicar      = in_array('--apply', $argvList, true);

$desde = 'ADMINISTRADOR';
$hacia = 'CLIENTE';

if ($esProduccion) {
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port = 25060; $dbname = 'empresas_sst';
    $username = 'cycloid_userdb'; $password = 'AVNS_MR2SLvzRh3i_7o9fEHN';
    $ssl = true; echo "=== PRODUCCION ===\n";
} else {
    $host = '127.0.0.1'; $port = 3306; $dbname = 'empresas_sst';
    $username = 'root'; $password = ''; $ssl = false; echo "=== LOCAL ===\n";
}
echo "Modo: " . ($aplicar ? "APLICAR (UPDATE)" : "DRY RUN (solo lectura)") . "\n";
echo "Cambio: responsable '{$desde}' -> '{$hacia}'\n\n";

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    if ($ssl) { $options[PDO::MYSQL_ATTR_SSL_CA] = true; $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false; }
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "Conexion OK\n\n";
} catch (Throwable $e) { echo "ERROR conexion: " . $e->getMessage() . "\n"; exit(1); }

// Distribucion actual de valores
echo "Distribucion actual de 'responsable' en tbl_pendientes:\n";
foreach ($pdo->query("SELECT responsable, COUNT(*) c FROM tbl_pendientes GROUP BY responsable ORDER BY c DESC")->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo "  " . str_pad((string) $row['c'], 5, ' ', STR_PAD_LEFT) . "  =>  " . ($row['responsable'] ?? '(NULL)') . "\n";
}
echo "\n";

$st = $pdo->prepare("SELECT COUNT(*) FROM tbl_pendientes WHERE responsable = ?");
$st->execute([$desde]);
$n = (int) $st->fetchColumn();
echo "Registros a migrar ('{$desde}'): {$n}\n\n";

if (!$aplicar) {
    echo "DRY RUN: no se modifico nada. Usa --apply para migrar.\n";
    exit(0);
}
if ($n === 0) {
    echo "Nada que migrar.\n";
    exit(0);
}

try {
    $pdo->beginTransaction();
    $u = $pdo->prepare("UPDATE tbl_pendientes SET responsable = ? WHERE responsable = ?");
    $u->execute([$hacia, $desde]);
    $filas = $u->rowCount();
    $pdo->commit();
    echo "MIGRADO OK (transaccion confirmada): {$filas} filas actualizadas a '{$hacia}'.\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    echo "ERROR durante la migracion (rollback): " . $e->getMessage() . "\n";
    exit(1);
}
