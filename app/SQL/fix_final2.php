<?php
$pdo = new PDO('mysql:host=localhost;dbname=empresas_sst;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== Debug del campo estado ===\n";

// Ver los bytes exactos
$stmt = $pdo->query("SELECT id_version, version_texto, estado, HEX(estado) as hex_val, CHAR_LENGTH(estado) as char_len, LENGTH(estado) as byte_len FROM tbl_doc_versiones_sst WHERE id_documento = 4");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "v{$row['version_texto']}: estado='{$row['estado']}' | hex='{$row['hex_val']}' | chars={$row['char_len']} | bytes={$row['byte_len']}\n";
}

echo "\n=== Forzando UPDATE con TRIM ===\n";
$affected = $pdo->exec("UPDATE tbl_doc_versiones_sst SET estado = 'historico' WHERE TRIM(COALESCE(estado, '')) = '' AND id_documento = 4");
echo "Registros actualizados: {$affected}\n\n";

echo "=== Estado Final ===\n";
$stmt = $pdo->query('SELECT id_documento, id_version, version_texto, estado FROM tbl_doc_versiones_sst ORDER BY id_documento, id_version DESC');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Doc {$row['id_documento']} - v{$row['version_texto']} - Estado: {$row['estado']}\n";
}
