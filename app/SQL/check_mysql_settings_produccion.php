<?php
/**
 * Script para verificar y corregir configuración MySQL en PRODUCCION
 */

$config = [
    'host'     => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
    'port'     => 25060,
    'database' => 'empresas_sst',
    'username' => 'cycloid_userdb',
    'password' => 'AVNS_iDypWizlpMRwHIORJGG',
];

echo "=== Verificando Configuración MySQL (PRODUCCION) ===\n\n";

try {
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_SSL_CA => true,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ];

    $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
    echo "[OK] Conexion establecida\n\n";

    // Verificar variables de collation del servidor
    echo "--- Variables de Collation del Servidor ---\n";
    $stmt = $pdo->query("SHOW VARIABLES LIKE '%collation%'");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  {$row['Variable_name']}: {$row['Value']}\n";
    }

    echo "\n--- Variables de Charset del Servidor ---\n";
    $stmt = $pdo->query("SHOW VARIABLES LIKE '%character_set%'");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  {$row['Variable_name']}: {$row['Value']}\n";
    }

    // Verificar collation de la conexión actual
    echo "\n--- Collation de la Conexión Actual ---\n";
    $stmt = $pdo->query("SELECT @@collation_connection as conn_coll, @@character_set_connection as conn_charset");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "  Connection Collation: {$row['conn_coll']}\n";
    echo "  Connection Charset: {$row['conn_charset']}\n";

    // Verificar collation de la base de datos
    echo "\n--- Collation de la Base de Datos ---\n";
    $stmt = $pdo->query("SELECT DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = '{$config['database']}'");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "  Database Charset: {$row['DEFAULT_CHARACTER_SET_NAME']}\n";
    echo "  Database Collation: {$row['DEFAULT_COLLATION_NAME']}\n";

    // Forzar el collation en la sesión
    echo "\n--- Forzando Collation en la Sesión ---\n";
    $pdo->exec("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci'");
    $pdo->exec("SET collation_connection = 'utf8mb4_general_ci'");
    echo "  SET NAMES ejecutado\n";

    // Verificar de nuevo
    $stmt = $pdo->query("SELECT @@collation_connection as conn_coll");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "  Nuevo Connection Collation: {$row['conn_coll']}\n";

    echo "\n=== Completado ===\n";

} catch (PDOException $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
