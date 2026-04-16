<?php
/**
 * Inserta el acceso "Funciones SST y TH del Cliente" en dashboard_items.
 * Idempotente por accion_url.
 *
 * Uso:
 *   php scripts/perfil_cargo_dashboard_item_funciones_transversales.php             # LOCAL
 *   php scripts/perfil_cargo_dashboard_item_funciones_transversales.php --env=prod  # PROD
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

$opts=[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION];
if ($ssl){$opts[PDO::MYSQL_ATTR_SSL_CA]=true;$opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT]=false;}
$pdo = new PDO("mysql:host={$host};port={$port};dbname=empresas_sst;charset=utf8mb4", $username, $password, $opts);
echo "Conexion OK\n";

$item = [
    'rol'             => 'Administrador',
    'tipo_proceso'    => 'talento_humano',
    'detalle'         => 'Funciones SST y TH del Cliente',
    'descripcion'     => 'Administrar las funciones transversales SST y Talento Humano que aplican a todos los perfiles de cargo del cliente',
    'accion_url'      => 'perfiles-cargo/{id_cliente}/funciones-transversales',
    'orden'           => 102,
    'categoria'       => 'Operación por Cliente',
    'icono'           => 'fas fa-list-check',
    'color_gradiente' => '#8e44ad,#b265d3',
    'target_blank'    => 0,
    'activo'          => 1,
];

$chk = $pdo->prepare("SELECT id FROM dashboard_items WHERE accion_url = ? LIMIT 1");
$chk->execute([$item['accion_url']]);
$ex = $chk->fetch(PDO::FETCH_ASSOC);

if ($ex) {
    $pdo->prepare("UPDATE dashboard_items SET detalle=?, descripcion=?, orden=?, categoria=?, icono=?, color_gradiente=?, activo=?, actualizado_en=NOW() WHERE id=?")
        ->execute([$item['detalle'],$item['descripcion'],$item['orden'],$item['categoria'],$item['icono'],$item['color_gradiente'],$item['activo'],(int)$ex['id']]);
    echo "  UPD  id={$ex['id']} {$item['detalle']}\n";
} else {
    $pdo->prepare("INSERT INTO dashboard_items (rol, tipo_proceso, detalle, descripcion, accion_url, orden, categoria, icono, color_gradiente, target_blank, activo, creado_en, actualizado_en) VALUES (?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())")
        ->execute([$item['rol'],$item['tipo_proceso'],$item['detalle'],$item['descripcion'],$item['accion_url'],$item['orden'],$item['categoria'],$item['icono'],$item['color_gradiente'],$item['target_blank'],$item['activo']]);
    echo "  INS  id=".$pdo->lastInsertId()." {$item['detalle']}\n";
}
echo "LISTO\n";
