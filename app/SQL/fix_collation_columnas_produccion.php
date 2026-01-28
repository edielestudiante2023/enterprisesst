<?php
/**
 * Script para corregir collation columna por columna en PRODUCCION
 */

$config = [
    'host'     => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
    'port'     => 25060,
    'database' => 'empresas_sst',
    'username' => 'cycloid_userdb',
    'password' => 'AVNS_iDypWizlpMRwHIORJGG',
];

echo "=== Corrigiendo Collation por Columnas (PRODUCCION) ===\n\n";

try {
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_SSL_CA => true,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ];

    $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
    echo "[OK] Conexion establecida\n\n";

    $targetCollation = 'utf8mb4_general_ci';

    // Buscar columnas con collation diferente
    echo "--- Buscando columnas con collation incorrecto ---\n";
    $sql = "
        SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, COLLATION_NAME
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = '{$config['database']}'
          AND COLLATION_NAME IS NOT NULL
          AND COLLATION_NAME != '{$targetCollation}'
        ORDER BY TABLE_NAME, COLUMN_NAME
    ";

    $stmt = $pdo->query($sql);
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Encontradas " . count($columnas) . " columnas con collation diferente\n\n";

    if (count($columnas) > 0) {
        echo "--- Corrigiendo columnas ---\n";

        foreach ($columnas as $col) {
            $tabla = $col['TABLE_NAME'];
            $columna = $col['COLUMN_NAME'];
            $tipo = $col['COLUMN_TYPE'];
            $collActual = $col['COLLATION_NAME'];

            echo "  {$tabla}.{$columna} ({$collActual})... ";

            try {
                // Construir ALTER para la columna
                $alterSql = "ALTER TABLE `{$tabla}` MODIFY `{$columna}` {$tipo} CHARACTER SET utf8mb4 COLLATE {$targetCollation}";
                $pdo->exec($alterSql);
                echo "OK\n";
            } catch (PDOException $e) {
                echo "ERROR: " . substr($e->getMessage(), 0, 80) . "\n";
            }
        }
    }

    // Verificar nuevamente
    echo "\n--- Verificando columnas restantes con collation diferente ---\n";
    $stmt = $pdo->query($sql);
    $restantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($restantes) == 0) {
        echo "  [OK] Todas las columnas tienen collation correcto\n";
    } else {
        echo "  Quedan " . count($restantes) . " columnas con collation diferente:\n";
        foreach ($restantes as $col) {
            echo "    - {$col['TABLE_NAME']}.{$col['COLUMN_NAME']}: {$col['COLLATION_NAME']}\n";
        }
    }

    echo "\n=== Completado ===\n";

} catch (PDOException $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
