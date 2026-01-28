<?php
/**
 * Script para unificar collation de todas las tablas a utf8mb4_general_ci
 * Ejecutar con: php app/SQL/ejecutar_unificar_collation.php
 */

// Configuracion de entornos
$environments = [
    'LOCAL' => [
        'host' => 'localhost',
        'port' => 3306,
        'dbname' => 'empresas_sst',
        'user' => 'root',
        'pass' => '',
        'ssl' => false
    ],
    'PRODUCTION' => [
        'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port' => 25060,
        'dbname' => 'empresas_sst',
        'user' => 'cycloid_userdb',
        'pass' => 'AVNS_iDypWizlpMRwHIORJGG',
        'ssl' => true
    ]
];

$targetCollation = 'utf8mb4_general_ci';
$targetCharset = 'utf8mb4';

foreach ($environments as $envName => $config) {
    echo "\n========== {$envName} ==========\n";

    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset=utf8mb4";
        $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

        if ($config['ssl']) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = true;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        $pdo = new PDO($dsn, $config['user'], $config['pass'], $options);
        echo "Conectado a {$envName}\n";

        // 1. Cambiar collation de la base de datos
        echo "\n[1] Cambiando collation de la base de datos...\n";
        $pdo->exec("ALTER DATABASE `{$config['dbname']}` CHARACTER SET {$targetCharset} COLLATE {$targetCollation}");
        echo "OK: Base de datos actualizada a {$targetCollation}\n";

        // 2. Obtener todas las tablas con collation diferente
        echo "\n[2] Buscando tablas con collation diferente...\n";
        $stmt = $pdo->query("
            SELECT TABLE_NAME, TABLE_COLLATION
            FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_SCHEMA = '{$config['dbname']}'
            AND TABLE_TYPE = 'BASE TABLE'
            AND TABLE_COLLATION != '{$targetCollation}'
        ");
        $tablesWithDifferentCollation = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($tablesWithDifferentCollation)) {
            echo "Todas las tablas ya tienen collation {$targetCollation}\n";
        } else {
            echo "Encontradas " . count($tablesWithDifferentCollation) . " tablas con collation diferente:\n";

            foreach ($tablesWithDifferentCollation as $table) {
                $tableName = $table['TABLE_NAME'];
                $currentCollation = $table['TABLE_COLLATION'];

                echo "  - {$tableName} ({$currentCollation})... ";

                try {
                    $pdo->exec("ALTER TABLE `{$tableName}` CONVERT TO CHARACTER SET {$targetCharset} COLLATE {$targetCollation}");
                    echo "OK\n";
                } catch (PDOException $e) {
                    echo "ERROR: " . $e->getMessage() . "\n";
                }
            }
        }

        // 3. Verificar columnas con collation diferente (en tablas ya convertidas)
        echo "\n[3] Verificando columnas con collation diferente...\n";
        $stmt = $pdo->query("
            SELECT TABLE_NAME, COLUMN_NAME, COLLATION_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = '{$config['dbname']}'
            AND COLLATION_NAME IS NOT NULL
            AND COLLATION_NAME != '{$targetCollation}'
            ORDER BY TABLE_NAME, COLUMN_NAME
        ");
        $columnsWithDifferentCollation = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($columnsWithDifferentCollation)) {
            echo "Todas las columnas ya tienen collation {$targetCollation}\n";
        } else {
            echo "Encontradas " . count($columnsWithDifferentCollation) . " columnas con collation diferente:\n";

            $currentTable = '';
            foreach ($columnsWithDifferentCollation as $col) {
                if ($currentTable !== $col['TABLE_NAME']) {
                    $currentTable = $col['TABLE_NAME'];
                    echo "\n  Tabla: {$currentTable}\n";
                }

                $columnName = $col['COLUMN_NAME'];
                $dataType = $col['DATA_TYPE'];
                $maxLength = $col['CHARACTER_MAXIMUM_LENGTH'];
                $currentCollation = $col['COLLATION_NAME'];

                // Determinar el tipo de dato para el ALTER
                if ($dataType === 'varchar') {
                    $columnType = "VARCHAR({$maxLength})";
                } elseif ($dataType === 'text') {
                    $columnType = "TEXT";
                } elseif ($dataType === 'longtext') {
                    $columnType = "LONGTEXT";
                } elseif ($dataType === 'mediumtext') {
                    $columnType = "MEDIUMTEXT";
                } elseif ($dataType === 'char') {
                    $columnType = "CHAR({$maxLength})";
                } elseif ($dataType === 'enum' || $dataType === 'set') {
                    // Skip enum and set - they need special handling
                    echo "    - {$columnName} ({$currentCollation}) - SKIP ({$dataType})\n";
                    continue;
                } else {
                    $columnType = strtoupper($dataType);
                }

                echo "    - {$columnName} ({$currentCollation})... ";

                try {
                    $pdo->exec("ALTER TABLE `{$currentTable}` MODIFY `{$columnName}` {$columnType} CHARACTER SET {$targetCharset} COLLATE {$targetCollation}");
                    echo "OK\n";
                } catch (PDOException $e) {
                    echo "ERROR: " . substr($e->getMessage(), 0, 60) . "\n";
                }
            }
        }

        // 4. Verificacion final
        echo "\n[4] Verificacion final...\n";
        $stmt = $pdo->query("
            SELECT COUNT(*) as count
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = '{$config['dbname']}'
            AND COLLATION_NAME IS NOT NULL
            AND COLLATION_NAME != '{$targetCollation}'
        ");
        $remaining = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($remaining['count'] == 0) {
            echo "EXITO: Todas las columnas tienen collation {$targetCollation}\n";
        } else {
            echo "ADVERTENCIA: Quedan {$remaining['count']} columnas con collation diferente\n";
        }

    } catch (PDOException $e) {
        echo "ERROR conexion {$envName}: " . $e->getMessage() . "\n";
    }
}

echo "\n========== COMPLETADO ==========\n";
