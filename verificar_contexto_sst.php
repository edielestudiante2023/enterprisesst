<?php
$pdo = new PDO('mysql:host=localhost;dbname=empresas_sst', 'root', '');

echo "Columnas en tbl_cliente_contexto_sst:\n";
echo str_repeat("-", 60) . "\n";
$stmt = $pdo->query('DESCRIBE tbl_cliente_contexto_sst');
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo sprintf("%-35s | %s\n", $row['Field'], $row['Type']);
}

echo "\n\nDatos del contexto SST para cliente 11 (CYCLOID):\n";
echo str_repeat("-", 60) . "\n";
$stmt = $pdo->query('SELECT * FROM tbl_cliente_contexto_sst WHERE id_cliente = 11');
$contexto = $stmt->fetch(PDO::FETCH_ASSOC);
if ($contexto) {
    foreach ($contexto as $key => $value) {
        echo sprintf("%-35s : %s\n", $key, substr($value ?? 'NULL', 0, 50));
    }
} else {
    echo "No hay contexto SST para este cliente\n";
}

echo "\n\nDatos del cliente 11:\n";
echo str_repeat("-", 60) . "\n";
$stmt = $pdo->query('SELECT * FROM tbl_clientes WHERE id_cliente = 11');
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);
if ($cliente) {
    foreach ($cliente as $key => $value) {
        echo sprintf("%-35s : %s\n", $key, substr($value ?? 'NULL', 0, 50));
    }
}
