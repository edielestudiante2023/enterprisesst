<?php
$isProd = in_array('--prod', $argv ?? [], true);
echo "=== " . ($isProd ? 'PROD' : 'LOCAL') . " | READ-ONLY ===\n";
if ($isProd) {
    $h = getenv('DB_PROD_HOST'); $u = getenv('DB_PROD_USER'); $p = getenv('DB_PROD_PASS');
    $po = (int)(getenv('DB_PROD_PORT') ?: 25060); $d = getenv('DB_PROD_NAME') ?: 'empresas_sst';
    $c = mysqli_init();
    mysqli_ssl_set($c, null, null, null, null, null);
    if (!@mysqli_real_connect($c, $h, $u, $p, $d, $po, null, MYSQLI_CLIENT_SSL)) { echo "ERROR\n"; exit(1); }
} else {
    $c = new mysqli('localhost', 'root', '', 'empresas_sst');
    if ($c->connect_error) { echo "ERROR\n"; exit(1); }
}
foreach (['tbl_lista_asistencia', 'tbl_lista_asistencia_asistente'] as $t) {
    $r = $c->query("SHOW TABLES LIKE '{$t}'");
    echo "  {$t}: " . ($r->num_rows > 0 ? "EXISTE" : "NO existe") . "\n";
}
$c->close();
