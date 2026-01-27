<?php
$pdo = new PDO('mysql:host=localhost;dbname=empresas_sst', 'root', '');

echo "Columnas en tbl_doc_tipos:\n";
echo str_repeat("-", 50) . "\n";
$stmt = $pdo->query('DESCRIBE tbl_doc_tipos');
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . " | " . $row['Type'] . "\n";
}

echo "\n\nColumnas en tbl_doc_plantillas:\n";
echo str_repeat("-", 50) . "\n";
$stmt = $pdo->query('DESCRIBE tbl_doc_plantillas');
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . " | " . $row['Type'] . "\n";
}
