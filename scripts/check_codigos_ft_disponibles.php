<?php
/**
 * Lista los codigos FT-SST ya usados en tbl_doc_plantillas para ayudar a elegir
 * codigos disponibles cuando se reserva uno nuevo.
 *
 * Uso:
 *   php scripts/check_codigos_ft_disponibles.php          # local
 *   php scripts/check_codigos_ft_disponibles.php --prod   # prod
 */

$isProd = in_array('--prod', $argv ?? [], true);
echo "=== " . ($isProd ? 'PRODUCCION' : 'LOCAL') . " | READ-ONLY ===\n\n";

if ($isProd) {
    $host = getenv('DB_PROD_HOST') ?: '';
    $user = getenv('DB_PROD_USER') ?: '';
    $pass = getenv('DB_PROD_PASS') ?: '';
    $port = (int)(getenv('DB_PROD_PORT') ?: 25060);
    $db   = getenv('DB_PROD_NAME') ?: 'empresas_sst';
    if ($host === '' || $user === '' || $pass === '') { echo "ERROR env vars\n"; exit(1); }
    $conn = mysqli_init();
    mysqli_ssl_set($conn, null, null, null, null, null);
    if (!@mysqli_real_connect($conn, $host, $user, $pass, $db, $port, null, MYSQLI_CLIENT_SSL)) {
        echo "ERROR conn: " . mysqli_connect_error() . "\n"; exit(1);
    }
} else {
    $conn = new mysqli('localhost', 'root', '', 'empresas_sst');
    if ($conn->connect_error) { echo "ERROR conn local\n"; exit(1); }
}
$conn->set_charset('utf8mb4');

// Inspeccionar tbl_doc_tipos (catalogo de tipos)
echo "--- tbl_doc_tipos ---\n";
$r0 = $conn->query("SELECT * FROM tbl_doc_tipos");
$cols0 = [];
if ($r0->num_rows > 0) {
    $first = $r0->fetch_assoc();
    $cols0 = array_keys($first);
    echo "Columnas: " . implode(', ', $cols0) . "\n";
    // Imprimir todas las filas
    do {
        echo "  " . json_encode($first, JSON_UNESCAPED_UNICODE) . "\n";
    } while ($first = $r0->fetch_assoc());
}

echo "\n--- Sample tbl_doc_plantillas (id_tipo usados) ---\n";
$r0b = $conn->query("SELECT id_tipo, COUNT(*) c FROM tbl_doc_plantillas GROUP BY id_tipo");
while ($row = $r0b->fetch_assoc()) echo "  id_tipo={$row['id_tipo']} | usado en {$row['c']} plantillas\n";

echo "\n--- Lista FT-SST ---\n";
$r = $conn->query("SELECT codigo_sugerido, tipo_documento FROM tbl_doc_plantillas
                   ORDER BY codigo_sugerido");
$usados = [];
while ($row = $r->fetch_assoc()) {
    echo sprintf("  %-15s -> %s\n", $row['codigo_sugerido'], $row['tipo_documento']);
    $usados[] = $row['codigo_sugerido'];
}
echo "\nTotal codigos registrados: " . count($usados) . "\n";

// Sugerir codigos disponibles en rango 200-220
echo "\n--- Disponibilidad rango FT-SST-200..220 ---\n";
for ($i = 200; $i <= 220; $i++) {
    $code = 'FT-SST-' . $i;
    $libre = !in_array($code, $usados, true);
    echo sprintf("  %-15s %s\n", $code, $libre ? 'LIBRE' : 'OCUPADO');
}

$conn->close();
