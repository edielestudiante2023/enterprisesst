<?php
/**
 * Script CLI: Normalizar PHVA de letra sola a palabra completa
 * P→PLANEAR, H→HACER, V→VERIFICAR, A→ACTUAR
 *
 * Uso: php cli_fix_phva.php [local|prod]
 */

$env = $argv[1] ?? 'local';

$configs = [
    'local' => [
        'host' => 'localhost', 'user' => 'root', 'pass' => '',
        'db' => 'empresas_sst', 'port' => 3306, 'ssl' => false,
    ],
    'prod' => [
        'host' => getenv('DB_PROD_HOST') ?: 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'user' => getenv('DB_PROD_USER') ?: 'cycloid_userdb',
        'pass' => getenv('DB_PROD_PASS') ?: '',
        'db'   => getenv('DB_PROD_NAME') ?: 'empresas_sst',
        'port' => (int)(getenv('DB_PROD_PORT') ?: 25060),
        'ssl'  => true,
    ],
];

if (!isset($configs[$env])) { echo "Uso: php cli_fix_phva.php [local|prod]\n"; exit(1); }

$c = $configs[$env];
echo "=== " . strtoupper($env) . " ===\n";

try {
    $dsn = "mysql:host={$c['host']};port={$c['port']};dbname={$c['db']};charset=utf8mb4";
    $opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    if ($c['ssl']) { $opts[PDO::MYSQL_ATTR_SSL_CA] = true; $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false; }
    $pdo = new PDO($dsn, $c['user'], $c['pass'], $opts);
    echo "Conexion OK\n";

    $map = ['P' => 'PLANEAR', 'H' => 'HACER', 'V' => 'VERIFICAR', 'A' => 'ACTUAR'];

    foreach ($map as $letter => $word) {
        $stmt = $pdo->prepare("UPDATE tbl_pta_cliente SET phva_plandetrabajo = ? WHERE UPPER(TRIM(phva_plandetrabajo)) = ?");
        $stmt->execute([$word, $letter]);
        $count = $stmt->rowCount();
        echo "  $letter => $word: $count registros\n";
    }

    // Verificar
    $check = $pdo->query("SELECT phva_plandetrabajo, COUNT(*) as total FROM tbl_pta_cliente GROUP BY phva_plandetrabajo ORDER BY total DESC")->fetchAll(PDO::FETCH_ASSOC);
    echo "\nDistribucion actual:\n";
    foreach ($check as $r) echo "  {$r['phva_plandetrabajo']}: {$r['total']}\n";

    echo "OK\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
