<?php
/**
 * Script para forzar actualización de estados vacios
 */

$config = [
    'host'     => 'localhost',
    'port'     => 3306,
    'database' => 'empresas_sst',
    'username' => 'root',
    'password' => '',
];

echo "=== Fix Estados Vacios LOCAL ===\n\n";

try {
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "[OK] Conexion establecida\n\n";

    // Ver el valor hexadecimal del campo estado
    echo "--- Analizando valores de estado ---\n";
    $stmt = $pdo->query("SELECT id_version, version_texto, estado, HEX(estado) as estado_hex, LENGTH(estado) as estado_len FROM tbl_doc_versiones_sst ORDER BY id_documento, id_version");
    $versiones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($versiones as $v) {
        echo "  v{$v['version_texto']}: estado='{$v['estado']}' | hex={$v['estado_hex']} | len={$v['estado_len']}\n";
    }
    echo "\n";

    // Actualizar TODAS las versiones que no sean la mas reciente por documento
    echo "--- Actualizando versiones antiguas a 'historico' ---\n";

    // Primero, identificar la version mas reciente de cada documento
    $stmt = $pdo->query("
        SELECT id_documento, MAX(id_version) as max_version
        FROM tbl_doc_versiones_sst
        GROUP BY id_documento
    ");
    $maxVersiones = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Actualizar las que NO son la mas reciente a 'historico'
    $updateStmt = $pdo->prepare("UPDATE tbl_doc_versiones_sst SET estado = 'historico' WHERE id_version = ?");

    $stmt = $pdo->query("SELECT id_version, id_documento, version_texto, estado FROM tbl_doc_versiones_sst");
    $todas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $actualizadas = 0;
    foreach ($todas as $v) {
        $esMaxima = ($v['id_version'] == $maxVersiones[$v['id_documento']]);
        if (!$esMaxima && ($v['estado'] !== 'historico' && $v['estado'] !== 'obsoleto')) {
            $updateStmt->execute([$v['id_version']]);
            echo "  v{$v['version_texto']} (id={$v['id_version']}) -> historico\n";
            $actualizadas++;
        }
    }
    echo "  Total actualizadas: {$actualizadas}\n\n";

    // Verificar resultado final
    echo "--- Estado FINAL ---\n";
    $stmt = $pdo->query("SELECT id_documento, id_version, version_texto, estado FROM tbl_doc_versiones_sst ORDER BY id_documento, id_version DESC");
    $versionesFinal = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($versionesFinal as $v) {
        $estado = $v['estado'] ?: '(vacio)';
        echo "  Doc {$v['id_documento']} - v{$v['version_texto']} - Estado: {$estado}\n";
    }

    echo "\n=== Completado ===\n";

} catch (PDOException $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
