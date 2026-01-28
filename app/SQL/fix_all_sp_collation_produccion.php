<?php
$config = [
    'host'     => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
    'port'     => 25060,
    'database' => 'empresas_sst',
    'username' => 'cycloid_userdb',
    'password' => 'AVNS_iDypWizlpMRwHIORJGG',
];

echo "=== Corrigiendo TODOS los Stored Procedures (PRODUCCION) ===\n\n";

try {
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_SSL_CA => true,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ];

    $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
    $pdo->exec("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci'");
    echo "[OK] Conexion establecida\n\n";

    // Obtener todos los SPs
    $stmt = $pdo->query("
        SELECT ROUTINE_NAME
        FROM information_schema.ROUTINES
        WHERE ROUTINE_SCHEMA = '{$config['database']}'
        AND ROUTINE_TYPE = 'PROCEDURE'
    ");
    $allSPs = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "SPs encontrados: " . count($allSPs) . "\n\n";

    foreach ($allSPs as $spName) {
        echo "Procesando {$spName}... ";

        // Obtener definicion
        $stmt = $pdo->query("SHOW CREATE PROCEDURE `{$spName}`");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result || !isset($result['Create Procedure'])) {
            echo "NO SE PUDO LEER\n";
            continue;
        }

        $originalDef = $result['Create Procedure'];

        // Verificar si tiene LIKE
        if (stripos($originalDef, ' LIKE ') === false) {
            echo "sin LIKE\n";
            continue;
        }

        // Verificar si ya tiene COLLATE junto al LIKE
        if (preg_match('/LIKE.*COLLATE\s+utf8mb4_general_ci/i', $originalDef)) {
            echo "ya corregido\n";
            continue;
        }

        echo "CORRIGIENDO... ";

        // Extraer la definicion del cuerpo del SP
        // Remover el DEFINER y extraer solo el CREATE PROCEDURE
        $newDef = preg_replace('/DEFINER=`[^`]+`@`[^`]+`\s*/i', '', $originalDef);

        // Reemplazar LIKE pattern con LIKE pattern COLLATE utf8mb4_general_ci
        // Buscar: LIKE CONCAT(...) o LIKE 'string'
        $newDef = preg_replace(
            "/LIKE\s+(CONCAT\([^)]+\)|'[^']+')(?!\s+COLLATE)/i",
            "LIKE $1 COLLATE utf8mb4_general_ci",
            $newDef
        );

        // También agregar COLLATE al campo de la izquierda del LIKE si no lo tiene
        // Buscar patrones como: campo LIKE
        $newDef = preg_replace(
            "/(\w+)\s+LIKE\s+(CONCAT\([^)]+\)|'[^']+')\s+COLLATE/i",
            "$1 COLLATE utf8mb4_general_ci LIKE $2 COLLATE",
            $newDef
        );

        try {
            // Eliminar SP actual
            $pdo->exec("DROP PROCEDURE IF EXISTS `{$spName}`");

            // Recrear con la nueva definicion
            $pdo->exec($newDef);
            echo "OK\n";
        } catch (PDOException $e) {
            echo "ERROR: " . $e->getMessage() . "\n";
        }
    }

    echo "\n=== Completado ===\n";

} catch (PDOException $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
