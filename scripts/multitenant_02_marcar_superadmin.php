<?php
/**
 * Multi-tenant Fase 2: marcar superadmin
 *
 * Cambia tbl_usuarios.tipo_usuario = 'superadmin' para el usuario dueño (Cycloid).
 *
 * Uso:
 *   php scripts/multitenant_02_marcar_superadmin.php             # LOCAL
 *   php scripts/multitenant_02_marcar_superadmin.php --env=prod  # PRODUCCION
 */

$esProduccion = in_array('--env=prod', $argv ?? []);
$emailTarget  = 'head.consultant.cycloidtalent@gmail.com';

if ($esProduccion) {
    $host     = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port     = 25060;
    $dbname   = 'empresas_sst';
    $username = 'cycloid_userdb';
    $password = getenv('DB_PROD_PASS') ?: 'AVNS_MR2SLvzRh3i_7o9fEHN';
    $ssl      = true;
    echo "=== PRODUCCION ===\n";
} else {
    $host     = '127.0.0.1';
    $port     = 3306;
    $dbname   = 'empresas_sst';
    $username = 'root';
    $password = '';
    $ssl      = false;
    echo "=== LOCAL ===\n";
}

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    if ($ssl) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = true;
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "Conexion OK\n\n";
} catch (Throwable $e) {
    echo "ERROR conexion: " . $e->getMessage() . "\n";
    exit(1);
}

echo "-- Buscar usuario {$emailTarget} --\n";
$stmt = $pdo->prepare("SELECT id_usuario, email, tipo_usuario, id_entidad, estado FROM tbl_usuarios WHERE email = ?");
$stmt->execute([$emailTarget]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "  ERR usuario NO encontrado\n";
    exit(1);
}

echo "  OK encontrado: id_usuario={$user['id_usuario']}, tipo={$user['tipo_usuario']}, id_entidad={$user['id_entidad']}, estado={$user['estado']}\n\n";

if ($user['tipo_usuario'] === 'superadmin') {
    echo "  OK ya es superadmin (skip)\n";
    exit(0);
}

echo "-- UPDATE -> superadmin --\n";
try {
    $pdo->prepare("UPDATE tbl_usuarios SET tipo_usuario = 'superadmin' WHERE email = ?")
        ->execute([$emailTarget]);
    echo "  OK UPDATE ejecutado\n";
} catch (Throwable $e) {
    echo "  ERR UPDATE: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n-- Verificacion --\n";
$stmt = $pdo->prepare("SELECT tipo_usuario FROM tbl_usuarios WHERE email = ?");
$stmt->execute([$emailTarget]);
$nuevoTipo = $stmt->fetchColumn();
echo "  tipo_usuario ahora = {$nuevoTipo}\n";

exit($nuevoTipo === 'superadmin' ? 0 : 1);
