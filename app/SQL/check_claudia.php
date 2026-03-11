<?php
$pdo = new PDO(
    'mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=empresas_sst;charset=utf8mb4',
    'cycloid_userdb', 'AVNS_iDypWizlpMRwHIORJGG',
    [PDO::MYSQL_ATTR_SSL_CA => true, PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
$row = $pdo->query("SELECT id_responsable, tipo_rol, nombre_completo, updated_at FROM tbl_cliente_responsables_sst WHERE id_responsable IN (16,17)")->fetchAll(PDO::FETCH_ASSOC);
foreach ($row as $r) {
    echo "ID:{$r['id_responsable']} | tipo_rol='{$r['tipo_rol']}' | {$r['nombre_completo']} | updated: {$r['updated_at']}\n";
}

// Check column definition
$col = $pdo->query("SHOW COLUMNS FROM tbl_cliente_responsables_sst LIKE 'tipo_rol'")->fetch(PDO::FETCH_ASSOC);
echo "\nColumna tipo_rol: " . json_encode($col, JSON_UNESCAPED_UNICODE) . "\n";
