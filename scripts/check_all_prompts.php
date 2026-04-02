<?php
/**
 * Compara prompt_ia de TODOS los tipos de documento entre LOCAL y PRODUCCIÓN.
 * Detecta secciones que existen en local con prompt pero en producción sin prompt.
 *
 * Uso: php scripts/check_all_prompts.php
 */

// ── Conexión LOCAL ──
$local = new mysqli('localhost', 'root', '', 'empresas_sst');
if ($local->connect_error) { echo "ERROR LOCAL: " . $local->connect_error . "\n"; exit(1); }
$local->set_charset('utf8mb4');

// ── Conexión PRODUCCIÓN ──
$lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$env = [];
foreach ($lines as $l) {
    $l = trim($l);
    if ($l === '' || $l[0] === '#') continue;
    $parts = explode(' = ', $l, 2);
    if (count($parts) === 2) $env[trim($parts[0])] = trim($parts[1]);
}

// Usar credenciales de producción del usuario
$prod = mysqli_init();
$prod->ssl_set(null, null, '/www/ca/ca-certificate_cycloid.crt', null, null);
// Nota: este script se ejecuta en el SERVIDOR, no local
// Para comparar, primero obtenemos local, luego ejecutamos en servidor

// ── Recopilar LOCAL ──
echo "=== ESTADO LOCAL ===\n\n";
$r = $local->query("SELECT t.id_tipo_config, t.tipo_documento, t.nombre FROM tbl_doc_tipo_configuracion t WHERE t.activo = 1 ORDER BY t.tipo_documento");
$localData = [];
while ($row = $r->fetch_assoc()) {
    $tipo = $row['tipo_documento'];
    $id = $row['id_tipo_config'];

    $r2 = $local->query("SELECT seccion_key, IF(prompt_ia IS NULL OR prompt_ia = '', 'VACIO', 'TIENE') as estado FROM tbl_doc_secciones_config WHERE id_tipo_config = {$id} AND activo = 1 ORDER BY orden");

    $secciones = [];
    $vacias = 0;
    $total = 0;
    while ($s = $r2->fetch_assoc()) {
        $secciones[$s['seccion_key']] = $s['estado'];
        if ($s['estado'] === 'VACIO') $vacias++;
        $total++;
    }

    $status = $vacias > 0 ? "⚠ {$vacias}/{$total} SIN PROMPT" : "✓ {$total}/{$total} OK";
    echo "  {$tipo}: {$status}\n";
    if ($vacias > 0) {
        foreach ($secciones as $key => $estado) {
            if ($estado === 'VACIO') echo "    - {$key}: VACIO\n";
        }
    }

    $localData[$tipo] = [
        'nombre' => $row['nombre'],
        'secciones' => $secciones,
    ];
}

echo "\nTotal tipos en local: " . count($localData) . "\n";
$local->close();

// ── Generar JSON para comparar en servidor ──
$jsonFile = __DIR__ . '/local_prompts_check.json';
file_put_contents($jsonFile, json_encode($localData, JSON_UNESCAPED_UNICODE));
echo "\nDatos locales exportados a: {$jsonFile}\n";
echo "Ahora ejecuta en producción para comparar.\n";
