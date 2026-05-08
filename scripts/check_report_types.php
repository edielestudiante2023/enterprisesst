<?php
/**
 * Lista los report_types y detailreport disponibles para encontrar/crear los
 * apropiados para socializaciones de comites.
 */
$isProd = in_array('--prod', $argv ?? [], true);
echo "=== " . ($isProd ? 'PRODUCCION' : 'LOCAL') . " | READ-ONLY ===\n\n";

if ($isProd) {
    $host = getenv('DB_PROD_HOST'); $user = getenv('DB_PROD_USER');
    $pass = getenv('DB_PROD_PASS'); $port = (int)(getenv('DB_PROD_PORT') ?: 25060);
    $db = getenv('DB_PROD_NAME') ?: 'empresas_sst';
    if (!$host || !$user || !$pass) { echo "ERROR env vars\n"; exit(1); }
    $conn = mysqli_init();
    mysqli_ssl_set($conn, null, null, null, null, null);
    if (!@mysqli_real_connect($conn, $host, $user, $pass, $db, $port, null, MYSQLI_CLIENT_SSL)) {
        echo "ERROR conn: " . mysqli_connect_error() . "\n"; exit(1);
    }
} else {
    $conn = new mysqli('localhost', 'root', '', 'empresas_sst');
    if ($conn->connect_error) { echo "ERROR\n"; exit(1); }
}
$conn->set_charset('utf8mb4');

echo "--- Tablas relacionadas con reporte ---\n";
$r = $conn->query("SHOW TABLES LIKE '%report%'");
while ($x = $r->fetch_array()) echo "  " . $x[0] . "\n";
$r = $conn->query("SHOW TABLES LIKE '%reporte%'");
while ($x = $r->fetch_array()) echo "  " . $x[0] . "\n";
echo "\n";

// Encontrar tabla real de tipos
foreach (['tbl_reporttypes', 'tbl_report_types', 'tbl_report_type', 'tbl_tipos_reporte', 'tbl_tipo_reporte'] as $t) {
    $r = $conn->query("SHOW TABLES LIKE '{$t}'");
    if ($r->num_rows > 0) { echo "--- {$t} ---\n";
        $r2 = $conn->query("SELECT * FROM {$t} ORDER BY 1");
        while ($row = $r2->fetch_assoc()) echo "  " . json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
        echo "\n";
    }
}

foreach (['report_type_table', 'detail_report'] as $t) {
    echo "\n--- {$t} ---\n";
    $r = $conn->query("SELECT * FROM {$t} ORDER BY 1");
    while ($row = $r->fetch_assoc()) echo "  " . json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
}

$conn->close();
