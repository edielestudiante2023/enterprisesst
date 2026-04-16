<?php
/**
 * Inserta 2 accesos en dashboard_items para el modulo Perfiles de Cargo.
 * Estos aparecen en /consultor/dashboard (vista consultant/dashboard.php).
 *
 * Idempotente: usa accion_url unica para detectar si ya existen.
 *
 * Uso:
 *   php scripts/perfil_cargo_dashboard_items.php             # LOCAL
 *   php scripts/perfil_cargo_dashboard_items.php --env=prod  # PROD
 */

$esProduccion = in_array('--env=prod', $argv ?? []);

if ($esProduccion) {
    $host='db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';$port=25060;
    $username='cycloid_userdb';$password=getenv('DB_PROD_PASS')?:'AVNS_MR2SLvzRh3i_7o9fEHN';$ssl=true;
    echo "=== PRODUCCION ===\n";
} else {
    $host='127.0.0.1';$port=3306;$username='root';$password='';$ssl=false;
    echo "=== LOCAL ===\n";
}

try {
    $opts=[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION];
    if ($ssl){$opts[PDO::MYSQL_ATTR_SSL_CA]=true;$opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT]=false;}
    $pdo = new PDO("mysql:host={$host};port={$port};dbname=empresas_sst;charset=utf8mb4", $username, $password, $opts);
    echo "Conexion OK\n\n";
} catch (Throwable $e) { echo "ERROR: ".$e->getMessage()."\n"; exit(1); }

$items = [
    [
        'rol'             => 'Administrador',
        'tipo_proceso'    => 'talento_humano',
        'detalle'         => 'Perfiles de Cargo',
        'descripcion'     => 'Crear perfiles con objetivo IA, funciones, indicadores IA, competencias y acuses firmados por trabajador',
        'accion_url'      => 'perfiles-cargo/{id_cliente}',
        'orden'           => 100,
        'categoria'       => 'Operación por Cliente',
        'icono'           => 'fas fa-user-tie',
        'color_gradiente' => '#1e4c9a,#2d6cdf',
        'target_blank'    => 0,
        'activo'          => 1,
    ],
    [
        'rol'             => 'Administrador',
        'tipo_proceso'    => 'talento_humano',
        'detalle'         => 'Trabajadores del Cliente',
        'descripcion'     => 'Censo de trabajadores del cliente con importador masivo CSV y CRUD',
        'accion_url'      => 'perfiles-cargo/{id_cliente}/trabajadores',
        'orden'           => 101,
        'categoria'       => 'Operación por Cliente',
        'icono'           => 'fas fa-users',
        'color_gradiente' => '#2d8f5a,#4ec07d',
        'target_blank'    => 0,
        'activo'          => 1,
    ],
];

$chk = $pdo->prepare("SELECT id FROM dashboard_items WHERE accion_url = ? LIMIT 1");
$ins = $pdo->prepare("INSERT INTO dashboard_items
    (rol, tipo_proceso, detalle, descripcion, accion_url, orden, categoria, icono, color_gradiente, target_blank, activo, creado_en, actualizado_en)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
$upd = $pdo->prepare("UPDATE dashboard_items SET
    rol=?, tipo_proceso=?, detalle=?, descripcion=?, orden=?, categoria=?, icono=?, color_gradiente=?, target_blank=?, activo=?, actualizado_en=NOW()
    WHERE id=?");

foreach ($items as $it) {
    $chk->execute([$it['accion_url']]);
    $ex = $chk->fetch(PDO::FETCH_ASSOC);
    if ($ex) {
        $upd->execute([$it['rol'],$it['tipo_proceso'],$it['detalle'],$it['descripcion'],$it['orden'],$it['categoria'],$it['icono'],$it['color_gradiente'],$it['target_blank'],$it['activo'],(int)$ex['id']]);
        echo "  UPD  id={$ex['id']} {$it['detalle']}\n";
    } else {
        $ins->execute([$it['rol'],$it['tipo_proceso'],$it['detalle'],$it['descripcion'],$it['accion_url'],$it['orden'],$it['categoria'],$it['icono'],$it['color_gradiente'],$it['target_blank'],$it['activo']]);
        echo "  INS  id=".$pdo->lastInsertId()." {$it['detalle']}\n";
    }
}

echo "\n-- Verificacion --\n";
foreach ($pdo->query("SELECT id, detalle, categoria, accion_url FROM dashboard_items WHERE accion_url LIKE 'perfiles-cargo%'") as $r) {
    echo "  {$r['id']} | {$r['categoria']} | {$r['detalle']} | {$r['accion_url']}\n";
}
echo "\nLISTO\n";
