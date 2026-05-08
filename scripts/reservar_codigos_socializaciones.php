<?php
/**
 * Reserva en tbl_doc_plantillas los codigos FT-SST para los 6 nuevos tipos
 * de documento de socializacion (miembros + cronograma) x (COPASST + COCOLAB + BRIGADA).
 *
 * Idempotente: usa INSERT ... ON DUPLICATE KEY UPDATE.
 *
 * Uso:
 *   php scripts/reservar_codigos_socializaciones.php             # local dry-run
 *   php scripts/reservar_codigos_socializaciones.php --apply     # local apply
 *   php scripts/reservar_codigos_socializaciones.php --prod              # prod dry-run
 *   php scripts/reservar_codigos_socializaciones.php --prod --apply      # prod apply
 */

$isProd = in_array('--prod', $argv ?? [], true);
$apply  = in_array('--apply', $argv ?? [], true);
echo "=== " . ($isProd ? 'PRODUCCION' : 'LOCAL') . " | " . ($apply ? 'APPLY' : 'DRY-RUN') . " ===\n\n";

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

// Mapeo tipo_documento -> codigo_sugerido + nombre + descripcion
$reservas = [
    [
        'tipo'   => 'socializacion_miembros_copasst',
        'codigo' => 'FT-SST-201',
        'nombre' => 'Socializacion de Miembros COPASST',
        'descr'  => 'Documento PDF distribuido a colaboradores presentando los miembros del Comite Paritario SST electos.',
    ],
    [
        'tipo'   => 'socializacion_miembros_cocolab',
        'codigo' => 'FT-SST-202',
        'nombre' => 'Socializacion de Miembros Comite de Convivencia',
        'descr'  => 'Documento PDF distribuido a colaboradores presentando los miembros del Comite de Convivencia Laboral.',
    ],
    [
        'tipo'   => 'socializacion_miembros_brigada',
        'codigo' => 'FT-SST-203',
        'nombre' => 'Socializacion de Miembros Brigada de Emergencias',
        'descr'  => 'Documento PDF distribuido a colaboradores presentando los integrantes de la Brigada de Emergencias.',
    ],
    [
        'tipo'   => 'socializacion_cronograma_copasst',
        'codigo' => 'FT-SST-211',
        'nombre' => 'Socializacion de Cronograma COPASST',
        'descr'  => 'Documento PDF con el cronograma de reuniones del Comite Paritario SST.',
    ],
    [
        'tipo'   => 'socializacion_cronograma_cocolab',
        'codigo' => 'FT-SST-212',
        'nombre' => 'Socializacion de Cronograma Comite de Convivencia',
        'descr'  => 'Documento PDF con el cronograma de reuniones del Comite de Convivencia Laboral.',
    ],
    [
        'tipo'   => 'socializacion_cronograma_brigada',
        'codigo' => 'FT-SST-213',
        'nombre' => 'Socializacion de Cronograma Brigada',
        'descr'  => 'Documento PDF con el cronograma de reuniones de la Brigada de Emergencias.',
    ],
];

// Inspeccionar columnas reales de tbl_doc_plantillas para construir un INSERT correcto
$cols = [];
$r = $conn->query("SHOW COLUMNS FROM tbl_doc_plantillas");
while ($c = $r->fetch_assoc()) $cols[] = $c['Field'];
echo "Columnas en tbl_doc_plantillas: " . implode(', ', $cols) . "\n\n";

$tieneNombre = in_array('nombre', $cols, true);
$tieneDescripcion = in_array('descripcion', $cols, true);
$tieneActivo = in_array('activo', $cols, true);

foreach ($reservas as $r) {
    $codigo = $conn->real_escape_string($r['codigo']);
    $tipo   = $conn->real_escape_string($r['tipo']);

    // Verificar si ya existe
    $chk = $conn->query("SELECT id_plantilla FROM tbl_doc_plantillas WHERE tipo_documento='{$tipo}'");
    $existe = $chk->num_rows > 0;

    if ($existe) {
        echo "  SKIP {$r['codigo']} ({$r['tipo']}) - ya existe.\n";
        continue;
    }

    if (!$apply) {
        echo "  WOULD INSERT {$r['codigo']} -> {$r['tipo']}\n";
        continue;
    }

    // Construir INSERT segun columnas reales
    // id_tipo=9 (FOR - Formato): sin secciones, no requiere firma cliente.
    // Encaja para socializaciones (PDFs generados desde datos del modulo).
    $fields = ['tipo_documento', 'codigo_sugerido', 'id_tipo'];
    $values = ["'{$tipo}'", "'{$codigo}'", '9'];
    if ($tieneNombre) {
        $fields[] = 'nombre';
        $values[] = "'" . $conn->real_escape_string($r['nombre']) . "'";
    }
    if ($tieneDescripcion) {
        $fields[] = 'descripcion';
        $values[] = "'" . $conn->real_escape_string($r['descr']) . "'";
    }
    if ($tieneActivo) {
        $fields[] = 'activo';
        $values[] = "1";
    }

    $sql = "INSERT INTO tbl_doc_plantillas (" . implode(',', $fields) . ") VALUES (" . implode(',', $values) . ")";
    if (!$conn->query($sql)) {
        echo "  ERROR insertando {$r['codigo']}: " . $conn->error . "\n";
        continue;
    }
    echo "  OK INSERT {$r['codigo']} -> {$r['tipo']}\n";
}

if (!$apply) {
    echo "\n[DRY-RUN] Sin cambios. Para aplicar usar --apply.\n";
} else {
    echo "\nVerificacion:\n";
    $r2 = $conn->query("SELECT codigo_sugerido, tipo_documento FROM tbl_doc_plantillas
                        WHERE tipo_documento LIKE 'socializacion_%'
                        ORDER BY codigo_sugerido");
    while ($row = $r2->fetch_assoc()) {
        echo "  {$row['codigo_sugerido']} -> {$row['tipo_documento']}\n";
    }
}

$conn->close();
echo "\nOK.\n";
