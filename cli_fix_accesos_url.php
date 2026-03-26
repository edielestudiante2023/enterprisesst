<?php
/**
 * Script CLI: Quitar el /1 hardcodeado de las URLs en tabla accesos
 * Deja solo la ruta base para que la vista inyecte id_cliente dinámicamente
 *
 * Uso: php cli_fix_accesos_url.php [local|prod]
 */

$env = $argv[1] ?? 'local';

$configs = [
    'local' => [
        'host'   => 'localhost',
        'user'   => 'root',
        'pass'   => '',
        'db'     => 'empresas_sst',
        'port'   => 3306,
        'ssl'    => false,
    ],
    'prod' => [
        'host'   => getenv('DB_PROD_HOST') ?: 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'user'   => getenv('DB_PROD_USER') ?: 'cycloid_userdb',
        'pass'   => getenv('DB_PROD_PASS') ?: '',
        'db'     => getenv('DB_PROD_NAME') ?: 'empresas_sst',
        'port'   => (int)(getenv('DB_PROD_PORT') ?: 25060),
        'ssl'    => true,
    ],
];

if (!isset($configs[$env])) {
    echo "Uso: php cli_fix_accesos_url.php [local|prod]\n";
    exit(1);
}

$c = $configs[$env];
echo "=== Ejecutando en: " . strtoupper($env) . " ===\n";

try {
    $dsn = "mysql:host={$c['host']};port={$c['port']};dbname={$c['db']};charset=utf8mb4";
    $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    if ($c['ssl']) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = true;
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
    $pdo = new PDO($dsn, $c['user'], $c['pass'], $options);
    echo "Conexion OK\n";

    // 1. Mostrar estado actual
    $rows = $pdo->query("SELECT id_acceso, nombre, url FROM accesos WHERE url REGEXP '/[0-9]+$' ORDER BY id_acceso")->fetchAll(PDO::FETCH_ASSOC);
    echo "Registros con ID numerico hardcodeado al final: " . count($rows) . "\n";

    if (empty($rows)) {
        echo "No hay URLs que corregir. Todo limpio.\n";
        exit(0);
    }

    foreach ($rows as $r) {
        echo "  [{$r['id_acceso']}] {$r['url']} => ";
        // Quitar el ultimo segmento numerico: /prgCapacitacion/1 => /prgCapacitacion
        $newUrl = preg_replace('#/\d+$#', '', $r['url']);
        echo "$newUrl\n";
    }

    // 2. Ejecutar UPDATE
    $stmt = $pdo->prepare("UPDATE accesos SET url = REGEXP_REPLACE(url, '/[0-9]+$', '') WHERE url REGEXP '/[0-9]+$'");
    $stmt->execute();
    $affected = $stmt->rowCount();
    echo "\nActualizados: $affected registros\n";

    // 3. Verificar
    $check = $pdo->query("SELECT id_acceso, url FROM accesos WHERE url REGEXP '/[0-9]+$'")->fetchAll();
    echo "Registros pendientes: " . count($check) . "\n";

    if (count($check) === 0) {
        echo "OK - Todas las URLs limpias\n";
    } else {
        echo "ADVERTENCIA - Aun quedan URLs con numeros:\n";
        foreach ($check as $r) echo "  [{$r['id_acceso']}] {$r['url']}\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
