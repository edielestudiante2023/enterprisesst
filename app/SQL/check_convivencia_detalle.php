<?php
$pdo = new PDO(
    'mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=empresas_sst;charset=utf8mb4',
    'cycloid_userdb', 'AVNS_iDypWizlpMRwHIORJGG',
    [PDO::MYSQL_ATTR_SSL_CA => true, PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "=== DETALLE RESPONSABLES CON ROL VACIO - CLIENTE 15 ===\n\n";

// Los 2 sin rol
$rows = $pdo->query("
    SELECT * FROM tbl_cliente_responsables_sst
    WHERE id_cliente = 15 AND (tipo_rol IS NULL OR tipo_rol = '')
    ORDER BY id_responsable
")->fetchAll(PDO::FETCH_ASSOC);

echo "Personas sin rol asignado: " . count($rows) . "\n\n";
foreach ($rows as $r) {
    echo "ID: {$r['id_responsable']}\n";
    echo "  Nombre: {$r['nombre_completo']}\n";
    echo "  Cargo: {$r['cargo']}\n";
    echo "  Email: {$r['email']}\n";
    echo "  tipo_rol: '{$r['tipo_rol']}'\n";
    echo "  Creado: {$r['created_at']}\n";
    echo "  Actualizado: {$r['updated_at']}\n\n";
}

// Todos los de convivencia
echo "=== TODOS LOS DE CONVIVENCIA ===\n";
$rows2 = $pdo->query("
    SELECT id_responsable, tipo_rol, nombre_completo, cargo, email, created_at
    FROM tbl_cliente_responsables_sst
    WHERE id_cliente = 15 AND tipo_rol LIKE '%convivencia%'
    ORDER BY tipo_rol
")->fetchAll(PDO::FETCH_ASSOC);

echo "Con rol convivencia: " . count($rows2) . "\n";
foreach ($rows2 as $r) {
    echo "  [{$r['tipo_rol']}] {$r['nombre_completo']} - {$r['cargo']} (creado: {$r['created_at']})\n";
}

// Ver la vista que usa el controller
echo "\n=== VISTA vw_responsables_sst_activos - CONVIVENCIA ===\n";
$rows3 = $pdo->query("
    SELECT id_responsable, tipo_rol, nombre_rol, nombre_completo
    FROM vw_responsables_sst_activos
    WHERE id_cliente = 15 AND (tipo_rol LIKE '%convivencia%' OR tipo_rol IS NULL OR tipo_rol = '')
")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows3 as $r) {
    echo "  [{$r['tipo_rol']}] ({$r['nombre_rol']}) {$r['nombre_completo']}\n";
}

// Ver definicion de la vista
echo "\n=== DEFINICION DE LA VISTA ===\n";
$def = $pdo->query("SHOW CREATE VIEW vw_responsables_sst_activos")->fetch(PDO::FETCH_ASSOC);
echo $def['Create View'] . "\n";
