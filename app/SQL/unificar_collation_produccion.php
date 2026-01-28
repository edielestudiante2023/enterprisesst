<?php
/**
 * Script para unificar collation en PRODUCCION
 * Convierte todas las tablas y columnas a utf8mb4_general_ci
 */

$config = [
    'host'     => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
    'port'     => 25060,
    'database' => 'empresas_sst',
    'username' => 'cycloid_userdb',
    'password' => 'AVNS_iDypWizlpMRwHIORJGG',
];

echo "=== Unificando Collation en PRODUCCION ===\n\n";

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
    $targetCharset = 'utf8mb4';

    // 1. Cambiar collation de la base de datos
    echo "--- Cambiando collation de la base de datos ---\n";
    $pdo->exec("ALTER DATABASE `{$config['database']}` CHARACTER SET {$targetCharset} COLLATE {$targetCollation}");
    echo "[OK] Base de datos actualizada\n\n";

    // 2. Obtener todas las tablas
    echo "--- Actualizando tablas ---\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        echo "  Procesando: {$table}... ";

        try {
            // Cambiar collation de la tabla
            $pdo->exec("ALTER TABLE `{$table}` CONVERT TO CHARACTER SET {$targetCharset} COLLATE {$targetCollation}");
            echo "OK\n";
        } catch (PDOException $e) {
            echo "ERROR: " . $e->getMessage() . "\n";
        }
    }

    echo "\n--- Verificando collation final ---\n";

    // Verificar collation de algunas tablas importantes
    $tablasVerificar = ['tbl_cliente', 'tbl_documentos_sst', 'tbl_doc_versiones_sst', 'tbl_cliente_contexto_sst'];

    foreach ($tablasVerificar as $tabla) {
        $stmt = $pdo->query("SELECT TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA = '{$config['database']}' AND TABLE_NAME = '{$tabla}'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            echo "  {$tabla}: {$result['TABLE_COLLATION']}\n";
        }
    }

    echo "\n=== Unificacion completada ===\n";

} catch (PDOException $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
