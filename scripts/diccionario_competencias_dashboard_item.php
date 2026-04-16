<?php
/**
 * Diccionario de Competencias - Agrega el item al dashboard del consultor.
 *
 * Inserta un registro en `dashboard_items` para exponer el acceso al
 * modulo desde el dashboard del consultor. La URL usa el placeholder
 * {id_cliente} para que el modal selector de cliente lo reemplace.
 *
 * Idempotente: si ya existe un item con el mismo accion_url, no duplica.
 *
 * Uso:
 *   php scripts/diccionario_competencias_dashboard_item.php             # LOCAL
 *   php scripts/diccionario_competencias_dashboard_item.php --env=prod  # PRODUCCION
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
    $opt = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    if ($ssl) {
        $opt[PDO::MYSQL_ATTR_SSL_CA] = true;
        $opt[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
    $pdo = new PDO($dsn, $username, $password, $opt);
    echo "Conexion OK\n\n";
} catch (Throwable $e) {
    echo "ERROR conexion: " . $e->getMessage() . "\n"; exit(1);
}

$item = [
    'rol'             => 'consultor',
    'tipo_proceso'    => 'talento_humano',
    'categoria'       => 'Operación por Cliente',
    'detalle'         => 'Diccionario de Competencias',
    'descripcion'     => 'Catalogo de competencias del cliente, escala y matriz por cargo',
    'accion_url'      => '/diccionario-competencias/{id_cliente}',
    'icono'           => 'fas fa-user-check',
    'color_gradiente' => '#bd9751,#8c6d38',
    'target_blank'    => 0,
    'orden'           => 99,
    'activo'          => 1,
];

$exist = $pdo->prepare("SELECT id FROM dashboard_items WHERE accion_url = ?");
$exist->execute([$item['accion_url']]);
$id = $exist->fetchColumn();

if ($id) {
    echo "  SKIP ya existe item id={$id} con accion_url={$item['accion_url']}\n";
} else {
    $sql = "INSERT INTO dashboard_items (rol, tipo_proceso, detalle, descripcion, accion_url, orden, categoria, icono, color_gradiente, target_blank, activo)
            VALUES (:rol, :tipo_proceso, :detalle, :descripcion, :accion_url, :orden, :categoria, :icono, :color_gradiente, :target_blank, :activo)";
    $st = $pdo->prepare($sql);
    $st->execute($item);
    $id = $pdo->lastInsertId();
    echo "  OK  item insertado id={$id}\n";
}

// Verificacion
$q = $pdo->prepare("SELECT id,categoria,detalle,accion_url,activo FROM dashboard_items WHERE id = ?");
$q->execute([$id]);
$r = $q->fetch(PDO::FETCH_ASSOC);
echo "\n  id={$r['id']} | {$r['categoria']} | {$r['detalle']} | {$r['accion_url']} | activo={$r['activo']}\n";
echo "\nOK\n";
