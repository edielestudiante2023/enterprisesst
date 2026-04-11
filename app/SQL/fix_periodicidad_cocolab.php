<?php
/**
 * Actualizar periodicidad COCOLAB de 90 a 30 días
 * Uso: php app/SQL/fix_periodicidad_cocolab.php [--prod]
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

$pdo = new PDO($dsn, $user, $pass, $opts);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
echo "=== {$label} ===\n";

$pdo->exec("UPDATE tbl_tipos_comite SET periodicidad_dias = 30 WHERE codigo = 'COCOLAB'");
echo "OK: COCOLAB actualizado a 30 dias\n";

$rows = $pdo->query("SELECT codigo, periodicidad_dias FROM tbl_tipos_comite")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "  {$r['codigo']} | {$r['periodicidad_dias']} dias\n";
}
