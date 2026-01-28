<?php
$pdo = new PDO('mysql:host=localhost;dbname=empresas_sst;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== Actualizando estados uno por uno ===\n";

// Obtener IDs de versiones con estado vacio
$stmt = $pdo->query("SELECT id_version, version_texto FROM tbl_doc_versiones_sst WHERE id_documento = 4 AND (estado = '' OR estado IS NULL)");
$versiones = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Versiones con estado vacio encontradas: " . count($versiones) . "\n";

foreach ($versiones as $v) {
    echo "  Actualizando v{$v['version_texto']} (id={$v['id_version']})...\n";
    $updateStmt = $pdo->prepare("UPDATE tbl_doc_versiones_sst SET estado = ? WHERE id_version = ?");
    $result = $updateStmt->execute(['historico', $v['id_version']]);
    echo "    Resultado: " . ($result ? 'OK' : 'FAIL') . ", Rows: " . $updateStmt->rowCount() . "\n";
}

// Asegurar la version mas reciente como vigente
$pdo->exec("UPDATE tbl_doc_versiones_sst SET estado = 'vigente' WHERE id_version = (SELECT max_id FROM (SELECT MAX(id_version) as max_id FROM tbl_doc_versiones_sst WHERE id_documento = 4) t) AND estado != 'pendiente_firma'");

echo "\n=== Estado Final ===\n";
$stmt = $pdo->query('SELECT id_documento, id_version, version_texto, estado FROM tbl_doc_versiones_sst WHERE id_documento = 4 ORDER BY id_version DESC');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Doc {$row['id_documento']} - v{$row['version_texto']} - Estado: '{$row['estado']}'\n";
}
