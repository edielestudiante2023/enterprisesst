<?php
/**
 * Script CLI: Igualar estándar 7E (id=5) a 7A (id=1)
 * Inserta en estandares_accesos los accesos que tiene 7A y no tiene 7E
 *
 * Uso: php cli_igualar_7e_a_7a.php [local|prod]
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

if (!isset($configs[$env])) { echo "Uso: php cli_igualar_7e_a_7a.php [local|prod]\n"; exit(1); }

$c = $configs[$env];
echo "=== " . strtoupper($env) . " ===\n";

try {
    $dsn = "mysql:host={$c['host']};port={$c['port']};dbname={$c['db']};charset=utf8mb4";
    $opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    if ($c['ssl']) { $opts[PDO::MYSQL_ATTR_SSL_CA] = true; $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false; }
    $pdo = new PDO($dsn, $c['user'], $c['pass'], $opts);
    echo "Conexion OK\n";

    $id7A = 1;
    $id7E = 5;

    // Accesos en 7A que no están en 7E
    $sql = "SELECT id_acceso FROM estandares_accesos WHERE id_estandar = $id7A
            AND id_acceso NOT IN (SELECT id_acceso FROM estandares_accesos WHERE id_estandar = $id7E)";
    $faltan = $pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN);

    echo "Accesos faltantes en 7E: " . count($faltan) . "\n";

    if (empty($faltan)) {
        echo "7E ya tiene todos los accesos de 7A. Nada que hacer.\n";
        exit(0);
    }

    $inserted = 0;
    $stmt = $pdo->prepare("INSERT INTO estandares_accesos (id_estandar, id_acceso) VALUES (?, ?)");
    foreach ($faltan as $idAcceso) {
        $stmt->execute([$id7E, $idAcceso]);
        $inserted++;
        echo "  Insertado acceso $idAcceso en 7E\n";
    }

    // Verificar
    $total7E = $pdo->query("SELECT COUNT(*) FROM estandares_accesos WHERE id_estandar = $id7E")->fetchColumn();
    $total7A = $pdo->query("SELECT COUNT(*) FROM estandares_accesos WHERE id_estandar = $id7A")->fetchColumn();
    echo "\n7A: $total7A accesos | 7E: $total7E accesos\n";
    echo ($total7E == $total7A) ? "OK - Iguales\n" : "ADVERTENCIA - Aun difieren\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
