<?php
$pdo = new PDO(
    'mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=empresas_sst;charset=utf8mb4',
    'cycloid_userdb', 'AVNS_iDypWizlpMRwHIORJGG',
    [PDO::MYSQL_ATTR_SSL_CA => true, PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
$row = $pdo->query("SELECT * FROM tbl_cliente_contexto_sst WHERE id_cliente = 15")->fetch(PDO::FETCH_ASSOC);
echo "estandares_aplicables: " . ($row['estandares_aplicables'] ?? 'NULL') . "\n";
echo "total_trabajadores: " . ($row['total_trabajadores'] ?? 'NULL') . "\n";
echo "nivel_riesgo: " . ($row['nivel_riesgo'] ?? $row['nivel_riesgo_arl'] ?? 'NULL') . "\n";
