<?php
$pdo = new PDO(
    'mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=empresas_sst;charset=utf8mb4',
    'cycloid_userdb', 'AVNS_iDypWizlpMRwHIORJGG',
    [PDO::MYSQL_ATTR_SSL_CA => true, PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "=== COLUMNAS tbl_comites ===\n";
$cols = $pdo->query("SHOW COLUMNS FROM tbl_comites")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo "{$c['Field']} ({$c['Type']})\n";
}

echo "\n=== COMITES CLIENTE 15 ===\n";
$rows = $pdo->query("SELECT * FROM tbl_comites WHERE id_cliente = 15 ORDER BY id_comite")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo json_encode($r, JSON_UNESCAPED_UNICODE) . "\n";
}

echo "\n=== TIPOS DE COMITE ===\n";
$tipos = $pdo->query("SELECT * FROM tbl_tipos_comite ORDER BY id_tipo_comite")->fetchAll(PDO::FETCH_ASSOC);
foreach ($tipos as $t) {
    echo json_encode($t, JSON_UNESCAPED_UNICODE) . "\n";
}
