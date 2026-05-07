<?php
/**
 * DIAGNOSTICO (READ-ONLY): Detectar compromisos duplicados en acta 19 (produccion).
 *
 * Uso:
 *   php scripts/diag_compromisos_acta_19.php          # local
 *   php scripts/diag_compromisos_acta_19.php --prod   # produccion
 */

$isProd = in_array('--prod', $argv ?? [], true);
echo "=== " . ($isProd ? 'PRODUCCION' : 'LOCAL') . " | READ-ONLY ===\n\n";

if ($isProd) {
    $host = getenv('DB_PROD_HOST') ?: '';
    $user = getenv('DB_PROD_USER') ?: '';
    $pass = getenv('DB_PROD_PASS') ?: '';
    $port = (int)(getenv('DB_PROD_PORT') ?: 25060);
    $db   = getenv('DB_PROD_NAME') ?: 'empresas_sst';
    if ($host === '' || $user === '' || $pass === '') {
        echo "ERROR: faltan variables de entorno DB_PROD_HOST / DB_PROD_USER / DB_PROD_PASS\n";
        exit(1);
    }
    $conn = mysqli_init();
    mysqli_ssl_set($conn, null, null, null, null, null);
    $ok = @mysqli_real_connect($conn, $host, $user, $pass, $db, $port, null, MYSQLI_CLIENT_SSL);
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

$idActa = 19;

// 1) Total de compromisos en acta 19
try {
    $r = $conn->query("SELECT COUNT(*) c FROM tbl_acta_compromisos WHERE id_acta = {$idActa}");
    $row = $r->fetch_assoc();
    echo "Total compromisos en acta {$idActa}: {$row['c']}\n\n";
} catch (\Throwable $e) {
    echo "ERROR conteo: " . $e->getMessage() . "\n";
}

// 2) Listado completo
try {
    echo "--- Listado completo ---\n";
    $r = $conn->query("SELECT id_compromiso, numero_compromiso, LEFT(descripcion, 80) AS desc_corta,
                              responsable_email, fecha_compromiso, fecha_vencimiento,
                              estado, created_at
                       FROM tbl_acta_compromisos
                       WHERE id_acta = {$idActa}
                       ORDER BY created_at ASC, id_compromiso ASC");
    while ($row = $r->fetch_assoc()) {
        printf("  id=%s num=%s [%s] %s | resp=%s | venc=%s | created=%s\n",
            $row['id_compromiso'],
            $row['numero_compromiso'],
            $row['estado'],
            $row['desc_corta'],
            $row['responsable_email'] ?? '-',
            $row['fecha_vencimiento'] ?? '-',
            $row['created_at']);
    }
} catch (\Throwable $e) {
    echo "ERROR listado: " . $e->getMessage() . "\n";
}

// 3) Detectar duplicados exactos por (descripcion + responsable + fecha_vencimiento)
try {
    echo "\n--- Duplicados exactos (descripcion + responsable + fecha_vencimiento) ---\n";
    $sql = "SELECT MD5(CONCAT(IFNULL(descripcion,''), '|', IFNULL(responsable_email,''), '|', IFNULL(fecha_vencimiento,''))) AS firma,
                   COUNT(*) AS c,
                   GROUP_CONCAT(id_compromiso ORDER BY id_compromiso) AS ids,
                   GROUP_CONCAT(numero_compromiso ORDER BY id_compromiso) AS nums,
                   GROUP_CONCAT(created_at ORDER BY id_compromiso) AS createds,
                   LEFT(MAX(descripcion), 80) AS muestra_desc
            FROM tbl_acta_compromisos
            WHERE id_acta = {$idActa}
            GROUP BY firma
            HAVING c > 1
            ORDER BY c DESC";
    $r = $conn->query($sql);
    if ($r && $r->num_rows > 0) {
        while ($row = $r->fetch_assoc()) {
            echo "  GRUPO duplicado x{$row['c']}\n";
            echo "    desc: {$row['muestra_desc']}\n";
            echo "    ids: {$row['ids']}\n";
            echo "    nums: {$row['nums']}\n";
            echo "    createds: {$row['createds']}\n\n";
        }
    } else {
        echo "  Sin duplicados exactos.\n";
    }
} catch (\Throwable $e) {
    echo "ERROR duplicados: " . $e->getMessage() . "\n";
}

// 4) Detectar inserts cercanos en tiempo (mismo segundo o pocos segundos -> indicio doble-click)
try {
    echo "\n--- Inserts en ventana <=5 segundos (indicio doble-click/submit) ---\n";
    $sql = "SELECT a.id_compromiso AS id_a, b.id_compromiso AS id_b,
                   a.created_at AS ca, b.created_at AS cb,
                   TIMESTAMPDIFF(SECOND, a.created_at, b.created_at) AS diff_s,
                   LEFT(a.descripcion, 60) AS desc_a,
                   LEFT(b.descripcion, 60) AS desc_b
            FROM tbl_acta_compromisos a
            JOIN tbl_acta_compromisos b
              ON a.id_acta = b.id_acta
             AND a.id_compromiso < b.id_compromiso
             AND TIMESTAMPDIFF(SECOND, a.created_at, b.created_at) BETWEEN 0 AND 5
            WHERE a.id_acta = {$idActa}
            ORDER BY a.created_at";
    $r = $conn->query($sql);
    if ($r && $r->num_rows > 0) {
        while ($row = $r->fetch_assoc()) {
            echo "  ids {$row['id_a']} & {$row['id_b']} | diff={$row['diff_s']}s | a=[{$row['desc_a']}] b=[{$row['desc_b']}]\n";
        }
    } else {
        echo "  Sin pares cercanos en tiempo.\n";
    }
} catch (\Throwable $e) {
    echo "ERROR ventana: " . $e->getMessage() . "\n";
}

$conn->close();
echo "\nOK.\n";
