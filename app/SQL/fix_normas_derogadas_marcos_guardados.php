<?php
/**
 * fix_normas_derogadas_marcos_guardados.php
 *
 * Invalida registros en tbl_marco_normativo que contengan normas derogadas
 * (Decreto 1443/2014, Res 652/2012, Res 1356/2012, Res 3641/2026).
 * Al poner vigencia_dias = 0, el dashboard los muestra como "Vencido"
 * y obliga al consultor a regenerarlos con la IA corregida.
 *
 * Uso: php app/SQL/fix_normas_derogadas_marcos_guardados.php [local|prod]
 */

$entorno = $argv[1] ?? 'local';

if ($entorno === 'prod') {
    $host     = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port     = 25060;
    $user     = 'cycloid_userdb';
    $pass     = 'AVNS_iDypWizlpMRwHIORJGG';
    $dbname   = 'empresas_sst';
    $ssl      = true;
} else {
    $host     = 'localhost';
    $port     = 3306;
    $user     = 'root';
    $pass     = '';
    $dbname   = 'empresas_sst';
    $ssl      = false;
}

echo "=== Entorno: $entorno ===\n";

$db = mysqli_init();
if ($ssl) {
    mysqli_ssl_set($db, null, null, null, null, null);
    mysqli_real_connect($db, $host, $user, $pass, $dbname, $port, null, MYSQLI_CLIENT_SSL);
} else {
    mysqli_real_connect($db, $host, $user, $pass, $dbname, $port);
}

if (mysqli_connect_errno()) {
    die("Error conexión: " . mysqli_connect_error() . "\n");
}

// Mostrar cuántos se van a invalidar
$r = mysqli_query($db, "
    SELECT tipo_documento, LEFT(marco_normativo_texto, 100) as preview
    FROM tbl_marco_normativo
    WHERE marco_normativo_texto LIKE '%1443 de 2014%'
       OR marco_normativo_texto LIKE '%652 de 2012%'
       OR marco_normativo_texto LIKE '%1356 de 2012%'
       OR marco_normativo_texto LIKE '%3641 de 2026%'
");
$afectados = [];
while ($row = mysqli_fetch_assoc($r)) {
    $afectados[] = $row['tipo_documento'];
    echo "  Encontrado: {$row['tipo_documento']}\n";
}

if (empty($afectados)) {
    echo "  Nada que invalidar.\n";
    mysqli_close($db);
    exit(0);
}

// Invalidar
try {
    mysqli_query($db, "
        UPDATE tbl_marco_normativo
        SET vigencia_dias = 0
        WHERE marco_normativo_texto LIKE '%1443 de 2014%'
           OR marco_normativo_texto LIKE '%652 de 2012%'
           OR marco_normativo_texto LIKE '%1356 de 2012%'
           OR marco_normativo_texto LIKE '%3641 de 2026%'
    ");
    $rows = mysqli_affected_rows($db);
    echo "  [OK] $rows registros marcados como vencidos (vigencia_dias = 0)\n";
} catch (Exception $e) {
    echo "  [ERROR]: " . $e->getMessage() . "\n";
}

mysqli_close($db);
echo "=== Listo ===\n";
