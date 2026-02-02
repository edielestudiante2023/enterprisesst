<?php
$pdo = new PDO('mysql:host=localhost;dbname=empresas_sst;charset=utf8mb4', 'root', '');
$stmt = $pdo->query('DESCRIBE tbl_clientes');
echo "=== COLUMNAS DE tbl_clientes ===\n";
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
    echo $col['Field'] . " - " . $col['Type'] . "\n";
}

echo "\n=== DATOS DEL CLIENTE 11 ===\n";
$stmt = $pdo->query('SELECT * FROM tbl_clientes WHERE id_cliente = 11');
print_r($stmt->fetch(PDO::FETCH_ASSOC));
