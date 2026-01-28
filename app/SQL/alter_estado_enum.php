<?php
/**
 * Script para alterar el ENUM del campo estado en tbl_doc_versiones_sst
 * Agrega los valores 'historico' y 'pendiente_firma'
 */

$config = [
    'host'     => 'localhost',
    'port'     => 3306,
    'database' => 'empresas_sst',
    'username' => 'root',
    'password' => '',
];

echo "=== Alterando ENUM del campo estado (LOCAL) ===\n\n";

try {
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "[OK] Conexion establecida\n\n";

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

    // Ahora actualizar las versiones vacias a 'historico'
    echo "--- Actualizando versiones con estado vacio a 'historico' ---\n";
    $affected = $pdo->exec("UPDATE tbl_doc_versiones_sst SET estado = 'historico' WHERE estado = '' OR estado IS NULL");
    echo "  Registros actualizados: {$affected}\n\n";

    // Asegurar la version mas reciente como vigente (si no es pendiente_firma)
    echo "--- Asegurando version mas reciente como 'vigente' ---\n";
    $stmt = $pdo->query("SELECT id_documento, MAX(id_version) as max_id FROM tbl_doc_versiones_sst GROUP BY id_documento");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $pdo->exec("UPDATE tbl_doc_versiones_sst SET estado = 'vigente' WHERE id_version = {$row['max_id']} AND estado NOT IN ('pendiente_firma')");
    }
    echo "  Completado\n\n";

    // Estado final
    echo "=== Estado Final ===\n";
    $stmt = $pdo->query('SELECT id_documento, id_version, version_texto, estado FROM tbl_doc_versiones_sst ORDER BY id_documento, id_version DESC');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  Doc {$row['id_documento']} - v{$row['version_texto']} - Estado: {$row['estado']}\n";
    }

    echo "\n=== Completado exitosamente ===\n";

} catch (PDOException $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
