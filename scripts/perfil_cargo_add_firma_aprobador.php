<?php
/**
 * Migration aditiva: agrega columna firma_aprobador_base64 a tbl_perfil_cargo.
 * Mueve la firma del aprobador del path en condiciones_laborales._firma_aprobador_path
 * a una columna dedicada, resolviendo el riesgo #2 de la deuda tecnica.
 *
 * Operacion aditiva pura, no destructiva.
 * Idempotente: detecta si la columna ya existe.
 *
 * Uso:
 *   php scripts/perfil_cargo_add_firma_aprobador.php             # LOCAL
 *   php scripts/perfil_cargo_add_firma_aprobador.php --env=prod  # PROD
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

// Verificar si columna ya existe
$exists = false;
foreach ($pdo->query("SHOW COLUMNS FROM tbl_perfil_cargo") as $col) {
    if ($col['Field'] === 'firma_aprobador_base64') { $exists = true; break; }
}

if ($exists) {
    echo "  SKIP  Columna firma_aprobador_base64 ya existe\n";
} else {
    $pdo->exec("ALTER TABLE tbl_perfil_cargo ADD COLUMN firma_aprobador_base64 LONGTEXT NULL AFTER aprobador_cedula");
    echo "  OK    Columna firma_aprobador_base64 creada\n";
}

echo "\nLISTO\n";
