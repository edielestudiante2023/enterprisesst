<?php
$pdo = new PDO('mysql:host=localhost;dbname=empresas_sst;charset=utf8mb4', 'root', '');
$stmt = $pdo->query('SELECT * FROM tbl_comite_miembros ORDER BY id_comite, nombre_completo');
$miembros = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "=== EMAILS EN tbl_comite_miembros ===\n\n";
foreach ($miembros as $m) {
    echo "ID: " . ($m['id_miembro'] ?? $m['id'] ?? '?') . " | Comite " . ($m['id_comite'] ?? '?') . " | " . ($m['nombre_completo'] ?? $m['nombre'] ?? '?') . " | " . ($m['email'] ?? '?') . " | " . ($m['cargo'] ?? '?') . "\n";
}
echo "\nColumnas disponibles: " . implode(', ', array_keys($miembros[0] ?? [])) . "\n";
