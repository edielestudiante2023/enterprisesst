<?php
$config = [
    'host'     => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
    'port'     => 25060,
    'database' => 'empresas_sst',
    'username' => 'cycloid_userdb',
    'password' => 'AVNS_iDypWizlpMRwHIORJGG',
];

echo "=== Verificando Stored Procedure en PRODUCCION ===\n\n";

try {
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_SSL_CA => true,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ];

    $pdo = new PDO($dsn, $config['username'], $config['password'], $options);

    // Set collation
    $pdo->exec("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci'");

    echo "[OK] Conexion establecida\n\n";

    // Ver definicion del SP
    echo "--- Definicion de sp_generar_codigo_documento ---\n";
    $stmt = $pdo->query("SHOW CREATE PROCEDURE sp_generar_codigo_documento");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo $result['Create Procedure'] ?? "No se pudo obtener";
    } else {
        echo "Stored Procedure no encontrado";
    }

    echo "\n\n";

    // Buscar todos los SP que tengan LIKE
    echo "--- Stored Procedures con LIKE ---\n";
    $stmt = $pdo->query("
        SELECT ROUTINE_NAME, ROUTINE_DEFINITION
        FROM information_schema.ROUTINES
        WHERE ROUTINE_SCHEMA = '{$config['database']}'
        AND ROUTINE_TYPE = 'PROCEDURE'
        AND ROUTINE_DEFINITION LIKE '%LIKE%'
    ");
    $sps = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($sps)) {
        echo "Ninguno encontrado\n";
    } else {
        foreach ($sps as $sp) {
            echo "  - {$sp['ROUTINE_NAME']}\n";
        }
    }

} catch (PDOException $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
