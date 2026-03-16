<?php
/**
 * apply_views_production.php — Ejecuta los 3 batches de vistas en producción
 * Uso: DB_PROD_PASS="xxx" php apply_views_production.php
 */
$host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
$port = 25060;
$user = 'cycloid_userdb';
$pass = getenv('DB_PROD_PASS');
$db   = 'empresas_sst';

if (!$pass) die("ERROR: define DB_PROD_PASS\n");

$m = mysqli_init();
mysqli_ssl_set($m, null, null, null, null, null);
$ok_conn = @mysqli_real_connect($m, $host, $user, $pass, $db, $port, null, MYSQLI_CLIENT_SSL);
if (!$ok_conn) die("ERROR conexión: " . mysqli_connect_error() . "\n");
echo "Conexión OK\n\n";

$batches = [
    __DIR__ . '/views_otto_batch1.sql',
    __DIR__ . '/views_otto_batch2.sql',
    __DIR__ . '/views_otto_batch3.sql',
];

$ok = 0; $err = 0;

foreach ($batches as $file) {
    echo "--- " . basename($file) . " ---\n";
    $sql   = file_get_contents($file);
    $stmts = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($stmts as $s) {
        if (str_starts_with($s, '--') || strlen($s) < 15) continue;
        if (@mysqli_query($m, $s)) {
            $ok++;
        } else {
            $e = mysqli_error($m);
            echo "  ERR: $e\n  SQL: " . substr($s, 0, 80) . "...\n";
            $err++;
        }
    }
    echo "  done\n\n";
}

mysqli_close($m);
echo "─────────────────────────────────\n";
echo "Resultado: $ok OK, $err errores\n";
exit($err > 0 ? 1 : 0);
