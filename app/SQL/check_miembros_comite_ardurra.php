<?php
$pdo = new PDO(
    'mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=empresas_sst;charset=utf8mb4',
    'cycloid_userdb', 'AVNS_iDypWizlpMRwHIORJGG',
    [PDO::MYSQL_ATTR_SSL_CA => true, PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// 1. Ver comites del cliente 15
echo "=== COMITES CLIENTE 15 ===\n";
$rows = $pdo->query("SELECT * FROM tbl_comites WHERE id_cliente = 15")->fetchAll(PDO::FETCH_ASSOC);
if (empty($rows)) {
    echo "NO HAY COMITES CREADOS para cliente 15\n";
} else {
    foreach ($rows as $r) {
        echo "ID:{$r['id_comite']} | Tipo: {$r['id_tipo_comite']} | Estado: {$r['estado']}\n";
    }
}

// 2. Ver miembros de comite del cliente 15
echo "\n=== MIEMBROS COMITE CLIENTE 15 ===\n";
$rows2 = $pdo->query("
    SELECT mc.*, c.id_cliente
    FROM tbl_miembros_comite mc
    JOIN tbl_comites c ON mc.id_comite = c.id_comite
    WHERE c.id_cliente = 15
")->fetchAll(PDO::FETCH_ASSOC);
if (empty($rows2)) {
    echo "NO HAY MIEMBROS registrados en tbl_miembros_comite para cliente 15\n";
} else {
    foreach ($rows2 as $r) {
        echo "ID:{$r['id_miembro']} | Comite:{$r['id_comite']} | {$r['nombre_completo']} | {$r['email']} | {$r['rol_comite']} | Estado: {$r['estado']}\n";
    }
}

// 3. Verificar session setup del miembro
echo "\n=== USUARIOS TIPO MIEMBRO CLIENTE 15 ===\n";
$rows3 = $pdo->query("SELECT id_usuario, email, nombre_completo, tipo_usuario, id_entidad, estado FROM tbl_usuarios WHERE id_entidad = 15 AND tipo_usuario = 'miembro'")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows3 as $r) {
    echo "ID:{$r['id_usuario']} | {$r['email']} | {$r['nombre_completo']} | entidad:{$r['id_entidad']} | estado:{$r['estado']}\n";
}

// 4. Ver como el login setea la sesion del miembro
echo "\n=== TABLA tbl_miembros_comite COLUMNS ===\n";
$cols = $pdo->query("SHOW COLUMNS FROM tbl_miembros_comite")->fetchAll(PDO::FETCH_COLUMN);
echo implode(', ', $cols) . "\n";
