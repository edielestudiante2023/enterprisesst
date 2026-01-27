<?php
$pdo = new PDO('mysql:host=localhost;dbname=empresas_sst', 'root', '');

echo "Columnas en tbl_doc_secciones:\n";
echo str_repeat("-", 50) . "\n";
$stmt = $pdo->query('DESCRIBE tbl_doc_secciones');
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . " | " . $row['Type'] . "\n";
}
