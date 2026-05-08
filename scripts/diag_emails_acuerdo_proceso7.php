<?php
/**
 * READ-ONLY: diagnostico de envios de email del Acuerdo de Confidencialidad
 * para el proceso 7 (TECHNOLINER COCOLAB).
 *
 * Cruza tbl_doc_firma_solicitudes + tbl_doc_firma_audit_log para mostrar
 * exactamente que ocurrio con cada solicitud (correo_enviado / correo_fallo
 * con su error real).
 */
$isProd = in_array('--prod', $argv ?? [], true);
echo "=== " . ($isProd ? 'PROD' : 'LOCAL') . " | READ-ONLY ===\n\n";
if ($isProd) {
    $h = getenv('DB_PROD_HOST'); $u = getenv('DB_PROD_USER'); $p = getenv('DB_PROD_PASS');
    $po = (int)(getenv('DB_PROD_PORT') ?: 25060); $d = getenv('DB_PROD_NAME') ?: 'empresas_sst';
    $c = mysqli_init();
    mysqli_ssl_set($c, null, null, null, null, null);
    if (!@mysqli_real_connect($c, $h, $u, $p, $d, $po, null, MYSQLI_CLIENT_SSL)) { echo "ERROR\n"; exit(1); }
} else {
    $c = new mysqli('localhost', 'root', '', 'empresas_sst');
    if ($c->connect_error) { echo "ERROR\n"; exit(1); }
}
$c->set_charset('utf8mb4');

// 1) Buscar el documento del Acuerdo del proceso 7
$r = $c->query("SELECT id_documento, titulo, estado, created_at FROM tbl_documentos_sst
                WHERE tipo_documento='acuerdo_confidencialidad_cocolab'
                ORDER BY created_at DESC LIMIT 5");
echo "--- Documentos acuerdo_confidencialidad_cocolab ---\n";
$idsDoc = [];
while ($row = $r->fetch_assoc()) {
    $idsDoc[] = (int) $row['id_documento'];
    echo "  id_documento={$row['id_documento']} | {$row['titulo']} | estado={$row['estado']} | {$row['created_at']}\n";
}
if (empty($idsDoc)) { echo "  (no hay)\n"; $c->close(); exit(0); }

// 2) Solicitudes de cada documento
echo "\n--- Solicitudes por documento ---\n";
$idsSol = [];
foreach ($idsDoc as $idDoc) {
    $r2 = $c->query("SELECT id_solicitud, firmante_tipo, firmante_email, firmante_nombre, estado, created_at
                     FROM tbl_doc_firma_solicitudes WHERE id_documento={$idDoc}
                     ORDER BY id_solicitud");
    echo "  documento={$idDoc}:\n";
    while ($row = $r2->fetch_assoc()) {
        $idsSol[] = (int) $row['id_solicitud'];
        echo "    sol={$row['id_solicitud']} | {$row['firmante_tipo']} | {$row['firmante_email']} | "
           . "estado={$row['estado']} | {$row['created_at']}\n";
    }
}
if (empty($idsSol)) { echo "  (no hay solicitudes)\n"; $c->close(); exit(0); }

// 3) Audit log para cada solicitud (lo importante - aqui se ve si el email salio o fallo)
echo "\n--- Audit log por solicitud (eventos relevantes) ---\n";
$idsCsv = implode(',', $idsSol);
// Inspeccionar columnas de la tabla
$colInfo = $c->query("SHOW COLUMNS FROM tbl_doc_firma_audit_log");
$cols = []; while ($cc = $colInfo->fetch_assoc()) $cols[] = $cc['Field'];
echo "  (columnas audit_log: " . implode(', ', $cols) . ")\n";

$r3 = $c->query("SELECT * FROM tbl_doc_firma_audit_log
                 WHERE id_solicitud IN ({$idsCsv})
                 ORDER BY id_solicitud, fecha_hora ASC");
$porSol = [];
while ($row = $r3->fetch_assoc()) {
    $porSol[(int) $row['id_solicitud']][] = $row;
}
foreach ($idsSol as $idSol) {
    echo "\n  Solicitud {$idSol}:\n";
    if (empty($porSol[$idSol])) { echo "    (sin eventos)\n"; continue; }
    foreach ($porSol[$idSol] as $ev) {
        $det = $ev['detalles'];
        if ($det) {
            $detArr = @json_decode($det, true);
            if (is_array($detArr)) $det = json_encode($detArr, JSON_UNESCAPED_UNICODE);
        }
        echo "    [{$ev['fecha_hora']}] {$ev['evento']}";
        if ($det) echo "  -> " . substr($det, 0, 350);
        echo "\n";
    }
}

$c->close();
