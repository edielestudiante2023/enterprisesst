<?php
/**
 * Reserva en tbl_doc_plantillas el codigo FT-SST-018 para
 * acuerdo_confidencialidad_cocolab.
 *
 * Idempotente: SKIP si ya existe.
 *
 * Uso:
 *   php scripts/reservar_codigo_acuerdo_confidencialidad.php             # local dry-run
 *   php scripts/reservar_codigo_acuerdo_confidencialidad.php --apply     # local apply
 *   php scripts/reservar_codigo_acuerdo_confidencialidad.php --prod              # prod dry-run
 *   php scripts/reservar_codigo_acuerdo_confidencialidad.php --prod --apply      # prod apply
 */

$isProd = in_array('--prod', $argv ?? [], true);
$apply  = in_array('--apply', $argv ?? [], true);
echo "=== " . ($isProd ? 'PRODUCCION' : 'LOCAL') . " | " . ($apply ? 'APPLY' : 'DRY-RUN') . " ===\n\n";

if ($isProd) {
    $host = getenv('DB_PROD_HOST'); $user = getenv('DB_PROD_USER');
    $pass = getenv('DB_PROD_PASS'); $port = (int)(getenv('DB_PROD_PORT') ?: 25060);
    $db   = getenv('DB_PROD_NAME') ?: 'empresas_sst';
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

$reservas = [
    [
        'tipo'    => 'acuerdo_confidencialidad_cocolab',
        'codigo'  => 'FT-SST-018',
        'id_tipo' => 11, // 11 = Acta (en tbl_doc_tipos), porque es documento formal con firmantes
        'nombre'  => 'Acuerdo de Confidencialidad Comite de Convivencia Laboral',
        'descr'   => 'Acuerdo personal firmado por cada miembro del COCOLAB para garantizar la confidencialidad de los casos atendidos por el comite, segun Ley 1010 de 2006 y Resolucion 652 de 2012.',
    ],
];

$tieneNombre = $tieneDescripcion = $tieneActivo = true;

foreach ($reservas as $r) {
    $tipo = $conn->real_escape_string($r['tipo']);
    $chk = $conn->query("SELECT id_plantilla FROM tbl_doc_plantillas WHERE tipo_documento='{$tipo}'");
    if ($chk->num_rows > 0) {
        echo "  SKIP {$r['codigo']} ({$r['tipo']}) - ya existe.\n";
        continue;
    }
    if (!$apply) {
        echo "  WOULD INSERT {$r['codigo']} -> {$r['tipo']}\n";
        continue;
    }

    $sql = sprintf(
        "INSERT INTO tbl_doc_plantillas (tipo_documento, codigo_sugerido, id_tipo, nombre, descripcion, activo) VALUES ('%s','%s',%d,'%s','%s',1)",
        $conn->real_escape_string($r['tipo']),
        $conn->real_escape_string($r['codigo']),
        (int) $r['id_tipo'],
        $conn->real_escape_string($r['nombre']),
        $conn->real_escape_string($r['descr'])
    );
    if (!$conn->query($sql)) {
        echo "  ERROR insertando {$r['codigo']}: " . $conn->error . "\n";
        continue;
    }
    echo "  OK INSERT {$r['codigo']} -> {$r['tipo']}\n";
}

if (!$apply) echo "\n[DRY-RUN] Sin cambios.\n";

// Verificacion
$r2 = $conn->query("SELECT codigo_sugerido, tipo_documento FROM tbl_doc_plantillas WHERE tipo_documento='acuerdo_confidencialidad_cocolab'");
while ($row = $r2->fetch_assoc()) {
    echo "  Verificado: {$row['codigo_sugerido']} -> {$row['tipo_documento']}\n";
}

$conn->close();
echo "\nOK.\n";
