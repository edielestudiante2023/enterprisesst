<?php
/**
 * Agrega item al dashboard del consultor para Maestros del Cliente
 * (procesos, cargos, tareas, zonas — usado por IPEVR, Diccionario de
 * Competencias, etc.)
 *
 * Idempotente: si ya existe item con el mismo accion_url, no duplica.
 *
 * Uso:
 *   php scripts/maestros_cliente_dashboard_item.php             # LOCAL
 *   php scripts/maestros_cliente_dashboard_item.php --env=prod  # PRODUCCION
 */

$esProduccion = in_array('--env=prod', $argv ?? []);

if ($esProduccion) {
    $host     = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port     = 25060;
    $dbname   = 'empresas_sst';
    $username = 'cycloid_userdb';
    $password = getenv('DB_PROD_PASS') ?: 'AVNS_MR2SLvzRh3i_7o9fEHN';
    $ssl      = true;
    echo "=== PRODUCCION ===\n";
} else {
    $host = '127.0.0.1'; $port = 3306; $dbname = 'empresas_sst';
    $username = 'root'; $password = ''; $ssl = false;
    echo "=== LOCAL ===\n";
}

try {
    $opt = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    if ($ssl) {
        $opt[PDO::MYSQL_ATTR_SSL_CA] = true;
        $opt[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4", $username, $password, $opt);
    echo "Conexion OK\n\n";
} catch (Throwable $e) {
    echo "ERROR conexion: " . $e->getMessage() . "\n"; exit(1);
}

$item = [
    'rol'             => 'consultor',
    'tipo_proceso'    => 'maestros',
    'categoria'       => 'Operación por Cliente',
    'detalle'         => 'Maestros del Cliente',
    'descripcion'     => 'Procesos, cargos, tareas y zonas del cliente',
    'accion_url'      => '/maestros-cliente/{id_cliente}',
    'icono'           => 'fas fa-sitemap',
    'color_gradiente' => '#1c2437,#2c3e50',
    'target_blank'    => 0,
    'orden'           => 98,
    'activo'          => 1,
];

$exist = $pdo->prepare("SELECT id FROM dashboard_items WHERE accion_url = ?");
$exist->execute([$item['accion_url']]);
$id = $exist->fetchColumn();

if ($id) {
    echo "  SKIP ya existe item id={$id}\n";
} else {
    $sql = "INSERT INTO dashboard_items (rol, tipo_proceso, detalle, descripcion, accion_url, orden, categoria, icono, color_gradiente, target_blank, activo)
            VALUES (:rol, :tipo_proceso, :detalle, :descripcion, :accion_url, :orden, :categoria, :icono, :color_gradiente, :target_blank, :activo)";
    $st = $pdo->prepare($sql);
    $st->execute($item);
    $id = $pdo->lastInsertId();
    echo "  OK  item insertado id={$id}\n";
}

$q = $pdo->prepare("SELECT id,categoria,detalle,accion_url,activo FROM dashboard_items WHERE id = ?");
$q->execute([$id]);
$r = $q->fetch(PDO::FETCH_ASSOC);
echo "\n  id={$r['id']} | {$r['categoria']} | {$r['detalle']} | {$r['accion_url']} | activo={$r['activo']}\n";
echo "\nOK\n";
