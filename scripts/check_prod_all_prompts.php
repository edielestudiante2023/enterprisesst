<?php
/**
 * Revisa TODOS los tipos de documento en PRODUCCIÓN y reporta secciones sin prompt_ia.
 * Uso: se ejecuta en el servidor de producción.
 */

$lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$env = [];
foreach ($lines as $l) {
    $l = trim($l);
    if ($l === '' || $l[0] === '#') continue;
    $parts = explode(' = ', $l, 2);
    if (count($parts) === 2) $env[trim($parts[0])] = trim($parts[1]);
}

$conn = mysqli_init();
$conn->ssl_set(null, null, '/www/ca/ca-certificate_cycloid.crt', null, null);
$conn->real_connect(
    $env['database.default.hostname'],
    $env['database.default.username'],
    $env['database.default.password'],
    'empresas_sst',
    (int)$env['database.default.port'],
    null,
    MYSQLI_CLIENT_SSL
);
if ($conn->connect_error) { echo "ERROR: " . $conn->connect_error . "\n"; exit(1); }
$conn->set_charset('utf8mb4');

echo "=== PRODUCCIÓN - TODOS LOS TIPOS DE DOCUMENTO ===\n\n";

$r = $conn->query("SELECT id_tipo_config, tipo_documento, nombre FROM tbl_doc_tipo_configuracion WHERE activo = 1 ORDER BY tipo_documento");

$problemas = [];
while ($row = $r->fetch_assoc()) {
    $tipo = $row['tipo_documento'];
    $id = $row['id_tipo_config'];

    $r2 = $conn->query("SELECT seccion_key, nombre, IF(prompt_ia IS NULL OR prompt_ia = '', 'VACIO', 'TIENE') as estado FROM tbl_doc_secciones_config WHERE id_tipo_config = {$id} AND activo = 1 ORDER BY orden");

    $vacias = 0;
    $total = 0;
    $seccionesVacias = [];
    while ($s = $r2->fetch_assoc()) {
        $total++;
        if ($s['estado'] === 'VACIO') {
            $vacias++;
            $seccionesVacias[] = $s['seccion_key'] . ' (' . $s['nombre'] . ')';
        }
    }

    if ($vacias > 0) {
        echo "⚠ {$tipo} — {$vacias}/{$total} SIN PROMPT\n";
        foreach ($seccionesVacias as $sv) {
            echo "    - {$sv}\n";
        }
        $problemas[$tipo] = $seccionesVacias;
    } else {
        echo "✓ {$tipo} — {$total}/{$total} OK\n";
    }
}

echo "\n";
if (empty($problemas)) {
    echo "RESULTADO: Todos los tipos tienen sus prompts completos.\n";
} else {
    echo "RESULTADO: " . count($problemas) . " tipo(s) con secciones sin prompt.\n";
}

$conn->close();
