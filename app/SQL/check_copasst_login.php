<?php
$pdo = new PDO(
    'mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=empresas_sst;charset=utf8mb4',
    'cycloid_userdb', 'AVNS_MR2SLvzRh3i_7o9fEHN',
    [PDO::MYSQL_ATTR_SSL_CA => true, PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false]
);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== MIEMBROS COPASST CON LOGIN ===\n";
$rows = $pdo->query("
    SELECT u.id_usuario, u.email, u.password, m.nombre_completo, m.rol_comite, tc.codigo, c.nombre_cliente
    FROM tbl_usuarios u
    JOIN tbl_comite_miembros m ON m.email = u.email AND m.id_cliente = u.id_entidad
    JOIN tbl_comites co ON co.id_comite = m.id_comite
    JOIN tbl_tipos_comite tc ON tc.id_tipo = co.id_tipo
    JOIN tbl_clientes c ON c.id_cliente = u.id_entidad
    WHERE u.tipo_usuario = 'miembro' AND tc.codigo = 'COPASST' AND m.estado = 'activo'
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

if (empty($rows)) {
    echo "Ningun miembro COPASST tiene login.\n";
} else {
    foreach ($rows as $r) {
        echo "ID:{$r['id_usuario']} | {$r['email']} | {$r['nombre_completo']} | {$r['rol_comite']} | {$r['nombre_cliente']}\n";
    }
}

// Verificar si la contraseña es conocida (probar con comunes)
if (!empty($rows)) {
    $testPasswords = ['123456', 'password', 'Copasst2026', 'miembro123'];
    $first = $rows[0];
    echo "\nProbando passwords comunes para {$first['email']}:\n";
    foreach ($testPasswords as $p) {
        $match = password_verify($p, $first['password']) ? 'SI' : 'no';
        echo "  {$p} => {$match}\n";
    }
}
