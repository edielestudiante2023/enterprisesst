<?php
/**
 * Hacer id_consultor nullable en tbl_inspeccion_locativa
 * Para que miembros COPASST puedan crear inspecciones sin consultor
 * Uso: php app/SQL/fix_id_consultor_nullable.php [--prod]
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

try {
    $pdo->exec("ALTER TABLE tbl_inspeccion_locativa MODIFY COLUMN id_consultor INT NULL DEFAULT NULL");
    echo "OK: id_consultor ahora es nullable.\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

$col = $pdo->query("SHOW COLUMNS FROM tbl_inspeccion_locativa WHERE Field = 'id_consultor'")->fetch(PDO::FETCH_ASSOC);
echo "Verificacion: Type={$col['Type']} | Null={$col['Null']} | Default={$col['Default']}\n";
