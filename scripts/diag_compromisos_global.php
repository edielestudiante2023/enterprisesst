<?php
/**
 * DIAGNOSTICO GLOBAL (READ-ONLY): Detectar patrones de duplicacion de compromisos
 * en todas las actas (no solo la 19).
 */

$isProd = in_array('--prod', $argv ?? [], true);
echo "=== " . ($isProd ? 'PRODUCCION' : 'LOCAL') . " | READ-ONLY GLOBAL ===\n\n";

if ($isProd) {
    $host = getenv('DB_PROD_HOST') ?: '';
    $user = getenv('DB_PROD_USER') ?: '';
    $pass = getenv('DB_PROD_PASS') ?: '';
    $port = (int)(getenv('DB_PROD_PORT') ?: 25060);
    $db   = getenv('DB_PROD_NAME') ?: 'empresas_sst';
    if ($host === '' || $user === '' || $pass === '') {
        echo "ERROR: faltan variables de entorno DB_PROD_*\n"; exit(1);
    }
    $conn = mysqli_init();
    mysqli_ssl_set($conn, null, null, null, null, null);
    $ok = @mysqli_real_connect($conn, $host, $user, $pass, $db, $port, null, MYSQLI_CLIENT_SSL);
    if (!$ok) { echo "ERROR conexion prod: " . mysqli_connect_error() . "\n"; exit(1); }
} else {
    $conn = new mysqli('localhost', 'root', '', 'empresas_sst');
    if ($conn->connect_error) { echo "ERROR conexion local: " . $conn->connect_error . "\n"; exit(1); }
}
$conn->set_charset('utf8mb4');

// 1) Duplicados exactos por (id_acta + descripcion + responsable_email + fecha_vencimiento)
echo "--- DUPLICADOS EXACTOS por (acta + descripcion + responsable + fecha_venc) ---\n";
$sql = "SELECT id_acta,
               LEFT(descripcion, 60) AS desc_corta,
               COUNT(*) AS c,
               GROUP_CONCAT(id_compromiso ORDER BY id_compromiso) AS ids,
               GROUP_CONCAT(numero_compromiso ORDER BY id_compromiso) AS nums,
               MIN(created_at) AS primer_create,
               MAX(created_at) AS ultimo_create,
               TIMESTAMPDIFF(SECOND, MIN(created_at), MAX(created_at)) AS rango_s
        FROM tbl_acta_compromisos
        GROUP BY id_acta, descripcion, responsable_email, fecha_vencimiento
        HAVING c > 1
        ORDER BY c DESC, ultimo_create DESC
        LIMIT 30";
$r = $conn->query($sql);
if ($r && $r->num_rows > 0) {
    while ($row = $r->fetch_assoc()) {
        echo "  acta={$row['id_acta']} x{$row['c']} | nums=[{$row['nums']}] ids=[{$row['ids']}] | rango={$row['rango_s']}s\n";
        echo "    desc: {$row['desc_corta']}\n";
        echo "    primer={$row['primer_create']}  ultimo={$row['ultimo_create']}\n";
    }
} else {
    echo "  Sin duplicados exactos en toda la BD.\n";
}

// 2) Pares de compromisos con misma descripcion creados muy cercanos en tiempo (sin importar otros campos)
echo "\n--- PARES con misma descripcion creados <=10s (indicio doble-submit) ---\n";
$sql = "SELECT a.id_acta, a.id_compromiso AS id_a, b.id_compromiso AS id_b,
               LEFT(a.descripcion, 60) AS desc_corta,
               TIMESTAMPDIFF(SECOND, a.created_at, b.created_at) AS diff_s,
               a.created_at AS ca, b.created_at AS cb
        FROM tbl_acta_compromisos a
        JOIN tbl_acta_compromisos b
          ON a.id_acta = b.id_acta
         AND a.id_compromiso < b.id_compromiso
         AND a.descripcion = b.descripcion
         AND TIMESTAMPDIFF(SECOND, a.created_at, b.created_at) BETWEEN 0 AND 10
        ORDER BY a.created_at DESC
        LIMIT 30";
$r = $conn->query($sql);
if ($r && $r->num_rows > 0) {
    while ($row = $r->fetch_assoc()) {
        echo "  acta={$row['id_acta']} ids=[{$row['id_a']},{$row['id_b']}] diff={$row['diff_s']}s\n";
        echo "    desc: {$row['desc_corta']}\n";
        echo "    ca={$row['ca']} cb={$row['cb']}\n";
    }
} else {
    echo "  Sin pares con misma descripcion en ventana corta.\n";
}

// 3) Top 10 actas con mayor cantidad de compromisos (para ver si alguna esta inflada)
echo "\n--- TOP 10 actas con mayor cantidad de compromisos ---\n";
$sql = "SELECT id_acta, COUNT(*) c, COUNT(DISTINCT descripcion) distintos,
               (COUNT(*) - COUNT(DISTINCT descripcion)) AS posibles_dup
        FROM tbl_acta_compromisos
        GROUP BY id_acta
        ORDER BY c DESC
        LIMIT 10";
$r = $conn->query($sql);
while ($row = $r->fetch_assoc()) {
    printf("  acta=%-5s total=%-3s distintos=%-3s posibles_dup=%s\n",
        $row['id_acta'], $row['c'], $row['distintos'], $row['posibles_dup']);
}

// 4) Total general
echo "\n--- TOTAL GENERAL ---\n";
$r = $conn->query("SELECT COUNT(*) c, COUNT(DISTINCT id_acta) actas FROM tbl_acta_compromisos");
$row = $r->fetch_assoc();
echo "  Total compromisos: {$row['c']} en {$row['actas']} actas\n";

$conn->close();
echo "\nOK.\n";
