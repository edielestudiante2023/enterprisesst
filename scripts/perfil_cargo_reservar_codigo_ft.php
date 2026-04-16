<?php
/**
 * Reserva el codigo FT-SST-100 en tbl_doc_plantillas para el modulo Perfiles de Cargo.
 *
 * Esto NO integra el modulo al motor Tipo A (el perfil sigue siendo un universo propio).
 * Solo registra el codigo como ocupado para que no colisione con otros modulos.
 *
 * El codigo aparecera en el encabezado estandar del PDF del perfil.
 *
 * Idempotente: por tipo_documento='perfil_cargo'.
 *
 * Uso:
 *   php scripts/perfil_cargo_reservar_codigo_ft.php             # LOCAL
 *   php scripts/perfil_cargo_reservar_codigo_ft.php --env=prod  # PROD
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
echo "Conexion OK\n\n";

// Guardrail: verificar que FT-SST-100 no este ocupado por otro tipo_documento
$stmt = $pdo->prepare("SELECT id_plantilla, tipo_documento FROM tbl_doc_plantillas WHERE codigo_sugerido = 'FT-SST-100' LIMIT 1");
$stmt->execute();
$exist = $stmt->fetch(PDO::FETCH_ASSOC);

if ($exist && $exist['tipo_documento'] !== 'perfil_cargo') {
    echo "  ABORT: FT-SST-100 ya esta reservado por otro tipo: {$exist['tipo_documento']} (id_plantilla={$exist['id_plantilla']})\n";
    echo "  Debe elegirse otro numero.\n";
    exit(1);
}

// Verificar si ya existe para perfil_cargo
$chk = $pdo->prepare("SELECT id_plantilla FROM tbl_doc_plantillas WHERE tipo_documento = 'perfil_cargo' LIMIT 1");
$chk->execute();
$row = $chk->fetch(PDO::FETCH_ASSOC);

if ($row) {
    $upd = $pdo->prepare("UPDATE tbl_doc_plantillas SET
        nombre='Perfil del Cargo',
        descripcion='Perfil del cargo con objetivo, funciones, competencias, indicadores y acuses firmados por cada trabajador asignado al cargo.',
        codigo_sugerido='FT-SST-100',
        version='001',
        activo=1,
        updated_at=NOW()
        WHERE id_plantilla = ?");
    $upd->execute([$row['id_plantilla']]);
    echo "  UPD  id_plantilla={$row['id_plantilla']}  FT-SST-100 reservado (actualizado) para perfil_cargo\n";
} else {
    // id_tipo=9 = Formato (el "F" de FT-SST)
    $ins = $pdo->prepare("INSERT INTO tbl_doc_plantillas
        (id_tipo, id_estandar, nombre, descripcion, codigo_sugerido, tipo_documento, version, activo, orden, aplica_7, aplica_21, aplica_60, created_at, updated_at)
        VALUES (9, NULL, ?, ?, ?, ?, '001', 1, 100, 1, 1, 1, NOW(), NOW())");
    $ins->execute([
        'Perfil del Cargo',
        'Perfil del cargo con objetivo, funciones, competencias, indicadores y acuses firmados por cada trabajador asignado al cargo.',
        'FT-SST-100',
        'perfil_cargo',
    ]);
    echo "  INS  id_plantilla=" . $pdo->lastInsertId() . "  FT-SST-100 reservado (nuevo) para perfil_cargo\n";
}

// Verificacion
$ver = $pdo->query("SELECT id_plantilla, codigo_sugerido, nombre, version, activo FROM tbl_doc_plantillas WHERE tipo_documento = 'perfil_cargo'")->fetch(PDO::FETCH_ASSOC);
echo "\n-- Verificacion --\n";
echo "  id_plantilla    = {$ver['id_plantilla']}\n";
echo "  codigo_sugerido = {$ver['codigo_sugerido']}\n";
echo "  nombre          = {$ver['nombre']}\n";
echo "  version         = {$ver['version']}\n";
echo "  activo          = {$ver['activo']}\n";

echo "\nLISTO\n";
