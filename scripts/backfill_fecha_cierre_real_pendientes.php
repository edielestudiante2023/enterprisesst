<?php
/**
 * BACKFILL: Copiar fecha_cierre -> fecha_cierre_real para pendientes cerrados sin fecha real.
 *
 * Subconjunto (opcion A confirmada):
 *   - estado IN ('CERRADA','CERRADA POR FIN CONTRATO','SIN RESPUESTA DEL CLIENTE')
 *   - fecha_cierre_real IS NULL
 *   - fecha_cierre     IS NOT NULL
 *
 * Modo por defecto: DRY-RUN (solo cuenta y muestra muestra, no escribe).
 * Para aplicar realmente, pasar --apply.
 *
 * Uso LOCAL:
 *   php scripts/backfill_fecha_cierre_real_pendientes.php           # dry-run local
 *   php scripts/backfill_fecha_cierre_real_pendientes.php --apply   # aplica local
 *
 * Uso PRODUCCION (SOLO si el dry-run local + apply local salieron bien):
 *   php scripts/backfill_fecha_cierre_real_pendientes.php --prod              # dry-run prod
 *   php scripts/backfill_fecha_cierre_real_pendientes.php --prod --apply      # aplica prod
 */

$isProd  = in_array('--prod', $argv ?? [], true);
$apply   = in_array('--apply', $argv ?? [], true);

echo "=== " . ($isProd ? 'PRODUCCION' : 'LOCAL') . " | " . ($apply ? 'APPLY' : 'DRY-RUN') . " ===\n";

// Conexion
if ($isProd) {
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
    $ok = $conn->real_connect(
        $env['database.default.hostname'],
        $env['database.default.username'],
        $env['database.default.password'],
        'empresas_sst',
        (int)$env['database.default.port'],
        null,
        MYSQLI_CLIENT_SSL
    );
    if (!$ok) {
        echo "ERROR conexion prod: " . mysqli_connect_error() . "\n";
        exit(1);
    }
} else {
    $conn = new mysqli('localhost', 'root', '', 'empresas_sst');
    if ($conn->connect_error) {
        echo "ERROR conexion local: " . $conn->connect_error . "\n";
        exit(1);
    }
}
$conn->set_charset('utf8mb4');

// 1) Conteo previo: cuantas filas matchean el filtro
$where = "estado IN ('CERRADA','CERRADA POR FIN CONTRATO','SIN RESPUESTA DEL CLIENTE')
          AND fecha_cierre_real IS NULL
          AND fecha_cierre IS NOT NULL";

try {
    echo "\n--- Conteo previo (a actualizar) ---\n";
    $r = $conn->query("SELECT estado, COUNT(*) c FROM tbl_pendientes WHERE {$where} GROUP BY estado ORDER BY c DESC");
    $totalAUpdate = 0;
    while ($row = $r->fetch_assoc()) {
        printf("  [%-30s] %d\n", $row['estado'], (int)$row['c']);
        $totalAUpdate += (int)$row['c'];
    }
    echo "  TOTAL filas a actualizar: {$totalAUpdate}\n";
} catch (Exception $e) {
    echo "ERROR conteo: " . $e->getMessage() . "\n";
    exit(1);
}

// 2) Muestra de 5 filas afectadas (id, estado, fecha_cierre)
try {
    echo "\n--- Muestra 5 filas afectadas ---\n";
    $r = $conn->query("SELECT id_pendientes, id_cliente, estado, fecha_cierre, fecha_cierre_real FROM tbl_pendientes WHERE {$where} LIMIT 5");
    while ($row = $r->fetch_assoc()) {
        printf("  id=%s id_cliente=%s estado=[%s] fecha_cierre=[%s] -> fecha_cierre_real=[%s]\n",
            $row['id_pendientes'], $row['id_cliente'], $row['estado'],
            $row['fecha_cierre'], $row['fecha_cierre_real'] ?? 'NULL');
    }
} catch (Exception $e) {
    echo "ERROR muestra: " . $e->getMessage() . "\n";
}

if ($totalAUpdate === 0) {
    echo "\nNada que actualizar. Saliendo.\n";
    exit(0);
}

if (!$apply) {
    echo "\n[DRY-RUN] No se ejecuto el UPDATE. Para aplicar, vuelve a correr con --apply.\n";
    exit(0);
}

// 3) UPDATE real
try {
    echo "\n--- Ejecutando UPDATE ---\n";
    $sql = "UPDATE tbl_pendientes SET fecha_cierre_real = fecha_cierre WHERE {$where}";
    if (!$conn->query($sql)) {
        echo "ERROR UPDATE: " . $conn->error . "\n";
        exit(1);
    }
    $afectadas = $conn->affected_rows;
    echo "  Filas afectadas: {$afectadas}\n";
} catch (Exception $e) {
    echo "ERROR UPDATE: " . $e->getMessage() . "\n";
    exit(1);
}

// 4) Verificacion posterior
try {
    echo "\n--- Verificacion posterior ---\n";
    $r = $conn->query("SELECT COUNT(*) c FROM tbl_pendientes
                       WHERE estado IN ('CERRADA','CERRADA POR FIN CONTRATO','SIN RESPUESTA DEL CLIENTE')
                         AND fecha_cierre_real IS NULL");
    $row = $r->fetch_assoc();
    echo "  Cerrados con fecha_cierre_real NULL (post-update): {$row['c']}\n";
    echo "  (Si > 0, son filas que tampoco tenian fecha_cierre — quedaron como estaban.)\n";
} catch (Exception $e) {
    echo "ERROR verificacion: " . $e->getMessage() . "\n";
}

echo "\nOK.\n";
