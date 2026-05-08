<?php
/**
 * Elimina la(s) socializacion(es) de un proceso + tipo especifico, para permitir
 * el reenvio. Generaliza el script eliminar_socializacion_proceso7_cronograma.php.
 *
 * Borra:
 *  - filas en tbl_socializaciones
 *  - filas relacionadas en tbl_documentos_sst
 *
 * Uso:
 *   php scripts/eliminar_socializacion_proceso_tipo.php --proceso=7 --tipo=miembros
 *   php scripts/eliminar_socializacion_proceso_tipo.php --proceso=7 --tipo=miembros --apply
 *   php scripts/eliminar_socializacion_proceso_tipo.php --proceso=7 --tipo=cronograma --prod --apply
 */

$idProceso = null;
$tipo = null;
$isProd = in_array('--prod', $argv ?? [], true);
$apply  = in_array('--apply', $argv ?? [], true);

foreach ($argv as $arg) {
    if (preg_match('/^--proceso=(\d+)$/', $arg, $m)) $idProceso = (int) $m[1];
    if (preg_match('/^--tipo=(miembros|cronograma)$/', $arg, $m)) $tipo = $m[1];
}

if ($idProceso === null || $tipo === null) {
    echo "Uso: php scripts/eliminar_socializacion_proceso_tipo.php --proceso=N --tipo=miembros|cronograma [--prod] [--apply]\n";
    exit(1);
}

echo "=== " . ($isProd ? 'PRODUCCION' : 'LOCAL') . " | " . ($apply ? 'APPLY' : 'DRY-RUN')
   . " | proceso={$idProceso} | tipo={$tipo} ===\n\n";

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

$sql = "SELECT id_socializacion, id_cliente, tipo_comite, estado, total_destinatarios,
               enviados_ok, fallidos, id_documento_sst, id_documento_evidencia, created_at
        FROM tbl_socializaciones
        WHERE id_proceso = {$idProceso} AND tipo_socializacion = '{$tipo}'
        ORDER BY id_socializacion";
$r = $conn->query($sql);
$rows = [];
while ($row = $r->fetch_assoc()) $rows[] = $row;

if (count($rows) === 0) {
    echo "No hay socializaciones tipo '{$tipo}' para proceso {$idProceso}. Nada que hacer.\n";
    $conn->close();
    exit(0);
}

echo "Filas en tbl_socializaciones a eliminar:\n";
$idsSoc = [];
$idsDoc = [];
foreach ($rows as $row) {
    $idsSoc[] = (int) $row['id_socializacion'];
    if (!empty($row['id_documento_sst']))       $idsDoc[] = (int) $row['id_documento_sst'];
    if (!empty($row['id_documento_evidencia'])) $idsDoc[] = (int) $row['id_documento_evidencia'];
    echo "  id_soc={$row['id_socializacion']} | cliente={$row['id_cliente']} | comite={$row['tipo_comite']} | "
       . "estado={$row['estado']} | dest={$row['total_destinatarios']} (ok={$row['enviados_ok']}/fall={$row['fallidos']}) | "
       . "doc={$row['id_documento_sst']} evd={$row['id_documento_evidencia']} | created={$row['created_at']}\n";
}
echo "\nFilas en tbl_documentos_sst a eliminar (id_documento): " . implode(', ', $idsDoc) . "\n";

if (!$apply) {
    echo "\n[DRY-RUN] Sin cambios. Para aplicar usar --apply.\n";
    $conn->close();
    exit(0);
}

try {
    $conn->begin_transaction();

    if (!empty($idsSoc)) {
        $idsSocCsv = implode(',', $idsSoc);
        if (!$conn->query("DELETE FROM tbl_socializaciones WHERE id_socializacion IN ({$idsSocCsv})")) {
            throw new \Exception('Error eliminando tbl_socializaciones: ' . $conn->error);
        }
        echo "OK delete tbl_socializaciones: " . $conn->affected_rows . " filas\n";
    }

    if (!empty($idsDoc)) {
        $idsDocCsv = implode(',', $idsDoc);
        if (!$conn->query("DELETE FROM tbl_documentos_sst WHERE id_documento IN ({$idsDocCsv})")) {
            throw new \Exception('Error eliminando tbl_documentos_sst: ' . $conn->error);
        }
        echo "OK delete tbl_documentos_sst: " . $conn->affected_rows . " filas\n";
    }

    $conn->commit();
    echo "\nCommit OK.\n";
} catch (\Throwable $e) {
    $conn->rollback();
    echo "\nROLLBACK: " . $e->getMessage() . "\n";
    exit(1);
}

$r2 = $conn->query("SELECT COUNT(*) c FROM tbl_socializaciones WHERE id_proceso={$idProceso} AND tipo_socializacion='{$tipo}'");
$row = $r2->fetch_assoc();
echo "Quedan " . $row['c'] . " filas de socializacion '{$tipo}' para proceso {$idProceso}.\n";

$conn->close();
