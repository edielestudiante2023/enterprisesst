<?php
/**
 * LIMPIAR COMPROMISOS DUPLICADOS en tbl_acta_compromisos.
 *
 * Identifica grupos con mismo (id_acta, descripcion, responsable_email) y elimina
 * los duplicados, conservando UNA fila por grupo segun orden de prioridad:
 *   1) estado mas avanzado (cumplido > en_proceso > pendiente > vencido > cancelado)
 *   2) tiene evidencia (archivo o descripcion)
 *   3) tiene fecha_cierre_efectiva
 *   4) mayor porcentaje_avance
 *   5) menor id_compromiso (el original)
 *
 * Antes de cualquier DELETE, escribe backup en scripts/backups/.
 *
 * Uso:
 *   php scripts/limpiar_compromisos_duplicados.php             # local dry-run
 *   php scripts/limpiar_compromisos_duplicados.php --apply     # local apply
 *   php scripts/limpiar_compromisos_duplicados.php --prod      # prod dry-run
 *   php scripts/limpiar_compromisos_duplicados.php --prod --apply  # prod apply
 *
 * Variables de entorno (solo si --prod):
 *   DB_PROD_HOST, DB_PROD_USER, DB_PROD_PASS, DB_PROD_PORT, DB_PROD_NAME
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

// 1) Encontrar grupos duplicados
$sql = "SELECT id_acta, descripcion, IFNULL(responsable_email,'') AS resp_email,
               COUNT(*) AS c,
               GROUP_CONCAT(id_compromiso ORDER BY id_compromiso) AS ids
        FROM tbl_acta_compromisos
        GROUP BY id_acta, descripcion, IFNULL(responsable_email,'')
        HAVING c > 1
        ORDER BY id_acta";
try {
    $r = $conn->query($sql);
} catch (\Throwable $e) {
    echo "ERROR consulta grupos: " . $e->getMessage() . "\n";
    exit(1);
}

$grupos = [];
while ($row = $r->fetch_assoc()) {
    $grupos[] = $row;
}

if (count($grupos) === 0) {
    echo "Sin duplicados. Nada que hacer.\n";
    $conn->close();
    exit(0);
}

echo "Grupos duplicados encontrados: " . count($grupos) . "\n\n";

// Prioridad estado
$prioridadEstado = [
    'cumplido'   => 5,
    'en_proceso' => 4,
    'pendiente'  => 3,
    'vencido'    => 2,
    'cancelado'  => 1,
];

$idsAEliminar = [];
$resumen = [];

foreach ($grupos as $g) {
    $ids = explode(',', $g['ids']);
    $idsCsv = implode(',', array_map('intval', $ids));

    $rDet = $conn->query("SELECT * FROM tbl_acta_compromisos WHERE id_compromiso IN ({$idsCsv})");
    $filas = [];
    while ($f = $rDet->fetch_assoc()) {
        $filas[] = $f;
    }

    // Ordenar por prioridad: la primera del array es la que se CONSERVA
    usort($filas, function($a, $b) use ($prioridadEstado) {
        $pa = $prioridadEstado[$a['estado']] ?? 0;
        $pb = $prioridadEstado[$b['estado']] ?? 0;
        if ($pa !== $pb) return $pb - $pa; // estado mas avanzado primero

        $ea = (!empty($a['evidencia_archivo']) || !empty($a['evidencia_descripcion'])) ? 1 : 0;
        $eb = (!empty($b['evidencia_archivo']) || !empty($b['evidencia_descripcion'])) ? 1 : 0;
        if ($ea !== $eb) return $eb - $ea;

        $fa = !empty($a['fecha_cierre_efectiva']) ? 1 : 0;
        $fb = !empty($b['fecha_cierre_efectiva']) ? 1 : 0;
        if ($fa !== $fb) return $fb - $fa;

        $pca = (int)$a['porcentaje_avance'];
        $pcb = (int)$b['porcentaje_avance'];
        if ($pca !== $pcb) return $pcb - $pca;

        return (int)$a['id_compromiso'] - (int)$b['id_compromiso']; // id mas bajo = original
    });

    $conservar = $filas[0];
    $eliminar  = array_slice($filas, 1);

    foreach ($eliminar as $e) {
        $idsAEliminar[] = (int)$e['id_compromiso'];
    }

    $resumen[] = [
        'id_acta'    => $g['id_acta'],
        'desc_corta' => mb_substr($g['descripcion'], 0, 60),
        'resp'       => $g['resp_email'],
        'total'      => count($filas),
        'conservar'  => (int)$conservar['id_compromiso'] . " ({$conservar['estado']}, av={$conservar['porcentaje_avance']}%)",
        'eliminar'   => array_map(fn($e) => (int)$e['id_compromiso'] . " ({$e['estado']})", $eliminar),
    ];
}

// 2) Mostrar reporte
echo "--- REPORTE ---\n";
foreach ($resumen as $r) {
    echo "  acta={$r['id_acta']} | desc=[{$r['desc_corta']}] | resp={$r['resp']}\n";
    echo "    total={$r['total']} | CONSERVAR id={$r['conservar']}\n";
    echo "    ELIMINAR ids=[" . implode(', ', $r['eliminar']) . "]\n";
}
echo "\nTotal filas a eliminar: " . count($idsAEliminar) . "\n";

if (count($idsAEliminar) === 0) {
    echo "Nada que eliminar.\n";
    $conn->close();
    exit(0);
}

// 3) BACKUP de las filas que se van a eliminar
$backupDir = __DIR__ . '/backups';
if (!is_dir($backupDir)) mkdir($backupDir, 0755, true);
$ts = date('Ymd_His');
$envTag = $isProd ? 'prod' : 'local';
$backupFile = "{$backupDir}/compromisos_eliminados_{$envTag}_{$ts}.sql";

$idsCsv = implode(',', $idsAEliminar);
$rBk = $conn->query("SELECT * FROM tbl_acta_compromisos WHERE id_compromiso IN ({$idsCsv})");

$lines = [];
$lines[] = "-- Backup compromisos a eliminar - {$envTag} - {$ts}";
$lines[] = "-- Total filas: " . count($idsAEliminar);
$lines[] = "-- Para restaurar: ejecutar estos INSERTs en la misma BD.";
$lines[] = "";

while ($f = $rBk->fetch_assoc()) {
    $cols = array_keys($f);
    $vals = array_map(function($v) use ($conn) {
        if ($v === null) return 'NULL';
        return "'" . $conn->real_escape_string((string)$v) . "'";
    }, array_values($f));
    $lines[] = "INSERT INTO tbl_acta_compromisos (`" . implode('`,`', $cols) . "`) VALUES (" . implode(',', $vals) . ");";
}
file_put_contents($backupFile, implode("\n", $lines));
echo "\nBackup escrito en: {$backupFile}\n";

// 4) DELETE (solo si --apply)
if (!$apply) {
    echo "\n[DRY-RUN] Sin cambios. Para aplicar, vuelve a correr con --apply.\n";
    $conn->close();
    exit(0);
}

try {
    $sqlDel = "DELETE FROM tbl_acta_compromisos WHERE id_compromiso IN ({$idsCsv})";
    if (!$conn->query($sqlDel)) {
        echo "\nERROR DELETE: " . $conn->error . "\n";
        exit(1);
    }
    $afectadas = $conn->affected_rows;
    echo "\nDELETE OK. Filas eliminadas: {$afectadas}\n";
} catch (\Throwable $e) {
    echo "\nERROR DELETE: " . $e->getMessage() . "\n";
    exit(1);
}

// 5) Verificacion posterior
$rChk = $conn->query("SELECT id_acta, descripcion, IFNULL(responsable_email,'') AS resp,
                             COUNT(*) AS c
                      FROM tbl_acta_compromisos
                      GROUP BY id_acta, descripcion, IFNULL(responsable_email,'')
                      HAVING c > 1");
$rest = $rChk->num_rows;
echo "Grupos duplicados restantes (post-clean): {$rest}\n";

$conn->close();
echo "\nOK.\n";
