<?php
$pdo = new PDO('mysql:host=localhost;dbname=empresas_sst;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== Estructura de la tabla ===\n";
$stmt = $pdo->query("DESCRIBE tbl_doc_versiones_sst");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  {$row['Field']}: {$row['Type']} | Null={$row['Null']} | Key={$row['Key']} | Default={$row['Default']}\n";
}

echo "\n=== Todos los datos de Doc 4 ===\n";
$stmt = $pdo->query("SELECT * FROM tbl_doc_versiones_sst WHERE id_documento = 4");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}

echo "\n=== Intentando UPDATE directo con SQL ===\n";
$sql = "UPDATE tbl_doc_versiones_sst SET estado = 'historico' WHERE id_version = 9";
echo "SQL: {$sql}\n";
$result = $pdo->exec($sql);
echo "Resultado exec: {$result}\n";

// Verificar si cambio
$stmt = $pdo->query("SELECT id_version, estado FROM tbl_doc_versiones_sst WHERE id_version = 9");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Estado actual de id_version=9: '{$row['estado']}'\n";
