<?php
/**
 * Script para corregir tbl_roles y recrear la vista
 */

$config = [
    'host'     => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
    'port'     => 25060,
    'database' => 'empresas_sst',
    'username' => 'cycloid_userdb',
    'password' => 'AVNS_iDypWizlpMRwHIORJGG',
];

echo "=== Corrigiendo tbl_roles y vistas (PRODUCCION) ===\n\n";

try {
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_SSL_CA => true,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ];

    $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
    echo "[OK] Conexion establecida\n\n";

    // Verificar collation de tbl_roles
    echo "--- Verificando tbl_roles ---\n";
    $stmt = $pdo->query("
        SELECT COLUMN_NAME, COLUMN_TYPE, COLLATION_NAME
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = '{$config['database']}' AND TABLE_NAME = 'tbl_roles'
        AND COLLATION_NAME IS NOT NULL
    ");
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($columnas as $col) {
        echo "  {$col['COLUMN_NAME']}: {$col['COLLATION_NAME']}\n";

        if ($col['COLLATION_NAME'] !== 'utf8mb4_general_ci') {
            echo "    -> Corrigiendo...\n";
            $pdo->exec("ALTER TABLE tbl_roles MODIFY `{$col['COLUMN_NAME']}` {$col['COLUMN_TYPE']} CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
        }
    }

    // Obtener definicion de la vista y recrearla
    echo "\n--- Recreando vista vw_responsables_sst_activos ---\n";

    // Obtener la definición de la vista
    $stmt = $pdo->query("SHOW CREATE VIEW vw_responsables_sst_activos");
    $viewDef = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($viewDef) {
        $createView = $viewDef['Create View'];
        echo "  Definicion actual obtenida\n";

        // Eliminar y recrear la vista
        $pdo->exec("DROP VIEW IF EXISTS vw_responsables_sst_activos");
        echo "  Vista eliminada\n";

        // Limpiar la definicion (quitar el DEFINER)
        $createView = preg_replace('/DEFINER=`[^`]+`@`[^`]+`\s*/', '', $createView);
        $pdo->exec($createView);
        echo "  Vista recreada\n";
    } else {
        echo "  Vista no existe o no se puede leer\n";
    }

    // Verificar collation de la columna nombre_rol ahora
    echo "\n--- Verificando resultado ---\n";
    $stmt = $pdo->query("
        SELECT COLLATION_NAME
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = '{$config['database']}'
        AND TABLE_NAME = 'vw_responsables_sst_activos'
        AND COLUMN_NAME = 'nombre_rol'
    ");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo "  vw_responsables_sst_activos.nombre_rol: {$result['COLLATION_NAME']}\n";
    }

    // Verificar si quedan columnas con collation diferente
    echo "\n--- Verificando todas las columnas ---\n";
    $stmt = $pdo->query("
        SELECT COUNT(*) as total
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = '{$config['database']}'
          AND COLLATION_NAME IS NOT NULL
          AND COLLATION_NAME NOT IN ('utf8mb4_general_ci')
          AND TABLE_NAME NOT LIKE 'prueba%'
    ");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "  Columnas con collation diferente (excluyendo prueba): {$count}\n";

    echo "\n=== Completado ===\n";

} catch (PDOException $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
