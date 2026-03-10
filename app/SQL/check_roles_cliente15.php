<?php
$pdo = new PDO(
    'mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=empresas_sst;charset=utf8mb4',
    'cycloid_userdb', 'AVNS_iDypWizlpMRwHIORJGG',
    [PDO::MYSQL_ATTR_SSL_CA => true, PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "=== RESPONSABLES SST CLIENTE 15 - DETALLE ROLES ===\n\n";

$rows = $pdo->query("
    SELECT r.id_responsable, r.tipo_rol, r.nombre_completo, r.cargo, r.activo, r.email,
           v.nombre_rol
    FROM tbl_cliente_responsables_sst r
    LEFT JOIN vw_responsables_sst_activos v ON r.id_responsable = v.id_responsable
    WHERE r.id_cliente = 15
    ORDER BY r.tipo_rol, r.nombre_completo
")->fetchAll(PDO::FETCH_ASSOC);

echo "Total: " . count($rows) . " registros\n\n";

foreach ($rows as $r) {
    echo "ID:{$r['id_responsable']} | Rol: {$r['tipo_rol']} ({$r['nombre_rol']}) | {$r['nombre_completo']} | Cargo: {$r['cargo']} | Activo: {$r['activo']} | Email: {$r['email']}\n";
}

// Ver catalogo de roles
echo "\n=== CATALOGO DE ROLES SST ===\n";
$stmt = $pdo->query("SHOW TABLES LIKE '%rol%'");
$tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "Tablas roles: " . implode(', ', $tablas) . "\n";

foreach ($tablas as $t) {
    $rows2 = $pdo->query("SELECT * FROM `$t` LIMIT 30")->fetchAll(PDO::FETCH_ASSOC);
    echo "\n--- $t (" . count($rows2) . " registros) ---\n";
    foreach ($rows2 as $r2) {
        echo json_encode($r2, JSON_UNESCAPED_UNICODE) . "\n";
    }
}

// Ver que roles tiene el controlador esperados
echo "\n=== ROLES ASIGNADOS POR tipo_rol (cliente 15) ===\n";
$stmt = $pdo->query("
    SELECT tipo_rol, COUNT(*) as cantidad, GROUP_CONCAT(nombre_completo SEPARATOR ', ') as personas
    FROM tbl_cliente_responsables_sst
    WHERE id_cliente = 15
    GROUP BY tipo_rol
    ORDER BY tipo_rol
");
foreach ($stmt as $r) {
    echo "{$r['tipo_rol']}: {$r['cantidad']} -> {$r['personas']}\n";
}

// Ver que roles existen para comite convivencia
echo "\n=== ROLES CONVIVENCIA EN CATALOGO ===\n";
$stmt = $pdo->query("SELECT * FROM tbl_roles_sst WHERE codigo LIKE '%convivencia%' ORDER BY codigo");
$convivencia = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (empty($convivencia)) {
    echo "Buscando con LIKE nombre...\n";
    $stmt = $pdo->query("SELECT * FROM tbl_roles_sst WHERE nombre LIKE '%convivencia%' ORDER BY codigo");
    $convivencia = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
foreach ($convivencia as $r) {
    echo json_encode($r, JSON_UNESCAPED_UNICODE) . "\n";
}
