<?php
/**
 * Script para alterar el ENUM del campo estado en tbl_doc_versiones_sst
 * VERSION PRODUCCION
 */

$config = [
    'host'     => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
    'port'     => 25060,
    'database' => 'empresas_sst',
    'username' => 'cycloid_userdb',
    'password' => 'AVNS_iDypWizlpMRwHIORJGG',
];

echo "=== Alterando ENUM del campo estado (PRODUCCION) ===\n\n";

try {
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_SSL_CA => true,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ];

    $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
    echo "[OK] Conexion establecida a PRODUCCION\n\n";

    // Verificar estructura actual
    echo "--- Estructura ACTUAL del campo estado ---\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM tbl_doc_versiones_sst WHERE Field = 'estado'");
    $col = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($col) {
        echo "  Type: {$col['Type']}\n";
        echo "  Default: {$col['Default']}\n\n";
    } else {
        echo "  Campo no encontrado\n\n";
    }

    // Alterar el ENUM para incluir los nuevos valores
    echo "--- Alterando tabla tbl_doc_versiones_sst ---\n";
    $sql = "ALTER TABLE tbl_doc_versiones_sst MODIFY COLUMN estado ENUM('vigente', 'obsoleto', 'historico', 'pendiente_firma') NOT NULL DEFAULT 'vigente'";
    $pdo->exec($sql);
    echo "[OK] ENUM modificado\n\n";

    // Verificar la nueva estructura
    echo "--- Nueva estructura del campo estado ---\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM tbl_doc_versiones_sst WHERE Field = 'estado'");
    $col = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "  Type: {$col['Type']}\n";
    echo "  Default: {$col['Default']}\n\n";

    // Verificar si hay datos y actualizarlos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_doc_versiones_sst");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "--- Registros en la tabla: {$count} ---\n";

    if ($count > 0) {
        // Actualizar versiones con estado vacio a 'historico'
        $affected = $pdo->exec("UPDATE tbl_doc_versiones_sst SET estado = 'historico' WHERE estado = '' OR estado IS NULL");
        echo "  Versiones actualizadas a 'historico': {$affected}\n";

        // Asegurar la version mas reciente como vigente
        $stmt = $pdo->query("SELECT id_documento, MAX(id_version) as max_id FROM tbl_doc_versiones_sst GROUP BY id_documento");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $pdo->exec("UPDATE tbl_doc_versiones_sst SET estado = 'vigente' WHERE id_version = {$row['max_id']} AND estado NOT IN ('pendiente_firma')");
        }
        echo "  Versiones mas recientes marcadas como 'vigente'\n";
    }

    echo "\n=== PRODUCCION actualizada exitosamente ===\n";

} catch (PDOException $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
