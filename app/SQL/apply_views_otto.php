<?php
/**
 * apply_views_otto.php — Aplica las vistas SQL de Otto en LOCAL o PRODUCCIÓN.
 *
 * Uso:
 *   php apply_views_otto.php local
 *   DB_PROD_PASS="AVNS_iDypWizlpMRwHIORJGG" php apply_views_otto.php production
 *
 * INSTRUCCIÓN TAXATIVA: ejecutar LOCAL primero. Solo si termina OK ejecutar PRODUCTION.
 */

$env = $argv[1] ?? 'local';
if (!in_array($env, ['local', 'production'])) {
    die("Uso: php apply_views_otto.php [local|production]\n");
}

// ─── Configuración de conexión ────────────────────────────────
if ($env === 'local') {
    $host = 'localhost';
    $port = 3306;
    $user = 'root';
    $pass = '';
    $db   = 'empresas_sst';
    $ssl  = false;
} else {
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port = 25060;
    $user = 'cycloid_userdb';
    $pass = getenv('DB_PROD_PASS');
    $db   = 'empresas_sst';
    $ssl  = true;

    if (!$pass) {
        die("ERROR: Define DB_PROD_PASS antes de ejecutar en producción.\n" .
            "  DB_PROD_PASS=\"...\" php apply_views_otto.php production\n");
    }
}

// ─── Conexión ────────────────────────────────────────────────
echo "─────────────────────────────────────────\n";
echo "Ambiente : " . strtoupper($env) . "\n";
echo "Host     : {$host}:{$port}\n";
echo "Base     : {$db}\n";
echo "─────────────────────────────────────────\n";

$conn = mysqli_init();
if ($ssl) {
    mysqli_ssl_set($conn, null, null, null, null, null);
}
$connected = @mysqli_real_connect(
    $conn, $host, $user, $pass, $db, $port,
    null, $ssl ? MYSQLI_CLIENT_SSL : 0
);

if (!$connected) {
    die("ERROR de conexión: " . mysqli_connect_error() . "\n");
}
echo "Conexión OK\n\n";

// ─── Leer SQL ────────────────────────────────────────────────
$sqlFile = __DIR__ . '/create_views_otto.sql';
if (!file_exists($sqlFile)) {
    die("ERROR: No se encuentra {$sqlFile}\n");
}

$content = file_get_contents($sqlFile);

// Dividir en sentencias individuales (por ;)
$statements = array_filter(
    array_map('trim', explode(';', $content)),
    fn($s) => strlen($s) > 10
);

// ─── Ejecutar ────────────────────────────────────────────────
$ok    = 0;
$error = 0;
$errors = [];

foreach ($statements as $sql) {
    // Extraer nombre de la vista del statement
    preg_match('/VIEW\s+`?(\w+)`?\s+AS/i', $sql, $m);
    $viewName = $m[1] ?? substr($sql, 0, 50);

    try {
        if (mysqli_query($conn, $sql)) {
            echo "  ✓  {$viewName}\n";
            $ok++;
        } else {
            throw new Exception(mysqli_error($conn));
        }
    } catch (Exception $e) {
        echo "  ✗  {$viewName} — " . $e->getMessage() . "\n";
        $errors[] = ['view' => $viewName, 'error' => $e->getMessage()];
        $error++;
    }
}

mysqli_close($conn);

// ─── Resumen ─────────────────────────────────────────────────
echo "\n─────────────────────────────────────────\n";
echo "Resultado [{$env}]: {$ok} OK, {$error} errores\n";

if ($error > 0) {
    echo "\nERRORES DETALLADOS:\n";
    foreach ($errors as $e) {
        echo "  - {$e['view']}: {$e['error']}\n";
    }
    echo "\n⚠  Corregir errores antes de ejecutar en producción.\n";
    exit(1);
} else {
    echo "\n✓  Todas las vistas aplicadas correctamente.\n";
    if ($env === 'local') {
        echo "→  Puedes ejecutar ahora en producción:\n";
        echo "   DB_PROD_PASS=\"...\" php apply_views_otto.php production\n";
    }
    exit(0);
}
