<?php
$pdo = new PDO('mysql:host=localhost;dbname=empresas_sst;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== Actualizando estados vacios a 'historico' ===\n";

// Actualizar versiones con estado vacio (string vacio)
$affected = $pdo->exec("UPDATE tbl_doc_versiones_sst SET estado = 'historico' WHERE estado = '' OR estado IS NULL");
echo "Registros actualizados: {$affected}\n\n";

// Asegurar que la version mas reciente de cada documento sea 'vigente' (si no es pendiente_firma)
$stmt = $pdo->query("SELECT id_documento, MAX(id_version) as max_id FROM tbl_doc_versiones_sst GROUP BY id_documento");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $pdo->exec("UPDATE tbl_doc_versiones_sst SET estado = 'vigente' WHERE id_version = {$row['max_id']} AND estado NOT IN ('pendiente_firma')");
}

echo "=== Estado Final ===\n";
$stmt = $pdo->query('SELECT id_documento, id_version, version_texto, estado FROM tbl_doc_versiones_sst ORDER BY id_documento, id_version DESC');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Doc {$row['id_documento']} - v{$row['version_texto']} - Estado: {$row['estado']}\n";
}
