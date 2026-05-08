<?php
/**
 * READ-ONLY: localiza ocurrencias de "JEDE" en tbl_candidatos_comite.cargo
 * (typo de "JEFE"). Muestra cuántos registros y los detalles para confirmar
 * antes de cualquier UPDATE.
 */
$isProd = in_array('--prod', $argv ?? [], true);
echo "=== " . ($isProd ? 'PROD' : 'LOCAL') . " | READ-ONLY ===\n";
if ($isProd) {
    $h = getenv('DB_PROD_HOST'); $u = getenv('DB_PROD_USER'); $p = getenv('DB_PROD_PASS');
    $po = (int)(getenv('DB_PROD_PORT') ?: 25060); $d = getenv('DB_PROD_NAME') ?: 'empresas_sst';
    if (!$h || !$u || !$p) { echo "ERROR env vars\n"; exit(1); }
    $c = mysqli_init();
    mysqli_ssl_set($c, null, null, null, null, null);
    if (!@mysqli_real_connect($c, $h, $u, $p, $d, $po, null, MYSQLI_CLIENT_SSL)) { echo "ERROR conn\n"; exit(1); }
} else {
    $c = new mysqli('localhost', 'root', '', 'empresas_sst');
    if ($c->connect_error) { echo "ERROR\n"; exit(1); }
}
$c->set_charset('utf8mb4');

echo "\n--- tbl_candidatos_comite con 'JEDE' en campo cargo ---\n";
$sql = "SELECT id_candidato, id_proceso, nombres, apellidos, cargo, representacion, tipo_plaza
        FROM tbl_candidatos_comite
        WHERE cargo LIKE '%JEDE%' OR cargo LIKE '%jede%'
        ORDER BY id_candidato";
$r = $c->query($sql);
$total = 0;
while ($row = $r->fetch_assoc()) {
    $total++;
    printf("  id=%s proceso=%s | %s %s | cargo=[%s] | rep=%s | %s\n",
        $row['id_candidato'], $row['id_proceso'],
        $row['nombres'], $row['apellidos'],
        $row['cargo'], $row['representacion'], $row['tipo_plaza']);
}
echo "\nTotal en tbl_candidatos_comite: {$total}\n";

// Tambien buscar en tbl_comite_miembros por si replico el typo
echo "\n--- tbl_comite_miembros con 'JEDE' (posible replicacion) ---\n";
$sql2 = "SELECT id_miembro, id_comite, nombre_completo, cargo
         FROM tbl_comite_miembros
         WHERE cargo LIKE '%JEDE%' OR cargo LIKE '%jede%'";
$r2 = $c->query($sql2);
$total2 = 0;
while ($row = $r2->fetch_assoc()) {
    $total2++;
    printf("  id=%s comite=%s | %s | cargo=[%s]\n",
        $row['id_miembro'], $row['id_comite'], $row['nombre_completo'], $row['cargo']);
}
echo "Total en tbl_comite_miembros: {$total2}\n";

$c->close();
