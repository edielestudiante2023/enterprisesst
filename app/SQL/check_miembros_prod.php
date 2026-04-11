<?php
/**
 * Consultar miembros con login en producción
 * Uso: php app/SQL/check_miembros_prod.php
 */
$pdo = new PDO(
    'mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=empresas_sst;charset=utf8mb4',
    'cycloid_userdb', 'AVNS_MR2SLvzRh3i_7o9fEHN',
    [PDO::MYSQL_ATTR_SSL_CA => true, PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false]
);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== USUARIOS TIPO MIEMBRO ===\n";
$rows = $pdo->query("
    SELECT u.id_usuario, u.email, u.tipo_usuario, u.estado, u.id_entidad,
           c.nombre_cliente
    FROM tbl_usuarios u
    LEFT JOIN tbl_clientes c ON c.id_cliente = u.id_entidad
    WHERE u.tipo_usuario = 'miembro'
    ORDER BY u.id_usuario
")->fetchAll(PDO::FETCH_ASSOC);

if (empty($rows)) {
    echo "No hay usuarios tipo miembro registrados.\n";

    echo "\n=== MIEMBROS COPASST ACTIVOS (sin usuario) ===\n";
    $miembros = $pdo->query("
        SELECT m.id_miembro, m.nombre_completo, m.email, m.cargo, m.rol_comite,
               c.nombre_cliente, tc.codigo
        FROM tbl_comite_miembros m
        JOIN tbl_comites co ON co.id_comite = m.id_comite
        JOIN tbl_tipos_comite tc ON tc.id_tipo = co.id_tipo
        JOIN tbl_clientes c ON c.id_cliente = m.id_cliente
        WHERE tc.codigo = 'COPASST' AND m.estado = 'activo'
        ORDER BY c.nombre_cliente, m.nombre_completo
        LIMIT 10
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($miembros as $m) {
        echo "  ID:{$m['id_miembro']} | {$m['nombre_completo']} | {$m['email']} | {$m['cargo']} | {$m['rol_comite']} | {$m['nombre_cliente']}\n";
    }
} else {
    foreach ($rows as $r) {
        echo "  ID:{$r['id_usuario']} | {$r['email']} | estado:{$r['estado']} | cliente:{$r['nombre_cliente']}\n";
    }
}
