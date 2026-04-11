<?php
/**
 * Limpiar registros de prueba de pausas activas e inspecciones locativas
 * Uso: php app/SQL/limpiar_pruebas_pausas_locativas.php [--prod]
 */
$isProd = in_array('--prod', $argv ?? []);

if ($isProd) {
    $dsn = 'mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=empresas_sst;charset=utf8mb4';
    $user = 'cycloid_userdb';
    $pass = 'AVNS_MR2SLvzRh3i_7o9fEHN';
    $opts = [PDO::MYSQL_ATTR_SSL_CA => true, PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false];
    $label = 'PRODUCCION';
} else {
    $dsn = 'mysql:host=localhost;dbname=empresas_sst;charset=utf8mb4';
    $user = 'root';
    $pass = '';
    $opts = [];
    $label = 'LOCAL';
}

echo "=== {$label} ===\n";
$pdo = new PDO($dsn, $user, $pass, $opts);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Verificar qué hay antes de borrar
$pausas = $pdo->query("SELECT COUNT(*) FROM tbl_pausas_activas")->fetchColumn();
$pausaReg = $pdo->query("SELECT COUNT(*) FROM tbl_pausa_activa_registros")->fetchColumn();
$locativas = $pdo->query("SELECT COUNT(*) FROM tbl_inspeccion_locativa WHERE creado_por_tipo = 'miembro'")->fetchColumn();

echo "Pausas activas: {$pausas} registros\n";
echo "Registros de pausas: {$pausaReg}\n";
echo "Inspecciones locativas (miembro): {$locativas}\n\n";

// Limpiar pausas activas (registros se borran por CASCADE)
$del1 = $pdo->exec("DELETE FROM tbl_pausas_activas");
echo "OK: {$del1} pausas activas eliminadas (registros por CASCADE).\n";

// Limpiar inspecciones locativas creadas por miembro
$del2 = $pdo->exec("DELETE FROM tbl_inspeccion_locativa WHERE creado_por_tipo = 'miembro'");
echo "OK: {$del2} inspecciones locativas de miembro eliminadas.\n";

// Verificar
echo "\nVerificacion:\n";
echo "  Pausas activas: " . $pdo->query("SELECT COUNT(*) FROM tbl_pausas_activas")->fetchColumn() . "\n";
echo "  Registros pausas: " . $pdo->query("SELECT COUNT(*) FROM tbl_pausa_activa_registros")->fetchColumn() . "\n";
echo "  Insp. locativas miembro: " . $pdo->query("SELECT COUNT(*) FROM tbl_inspeccion_locativa WHERE creado_por_tipo = 'miembro'")->fetchColumn() . "\n";
