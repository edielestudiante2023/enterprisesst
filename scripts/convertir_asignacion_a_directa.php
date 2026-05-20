<?php
/**
 * Convierte el documento "Asignacion de Responsable del SG-SST" de la modalidad
 * 'con_empresa' (con empresa consultora intermediaria) a 'directa' (el rep. legal
 * nombra directamente al profesional), modificando el contenido EN SITIO.
 *
 * NO crea version nueva, NO borra firmas. Solo reescribe:
 *   - tbl_documentos_sst.contenido           (lo que muestra la vista del documento)
 *   - tbl_doc_versiones_sst.contenido_snapshot  (solo la version MAS reciente, para que coincida)
 *
 * El nuevo texto se arma con los datos ya presentes en el JSON del documento
 * (representante_legal, responsable_sst, empresa) -> identico a construirContenido('directa').
 *
 * Uso:
 *   php scripts/convertir_asignacion_a_directa.php                    # LOCAL  dry-run
 *   php scripts/convertir_asignacion_a_directa.php --apply            # LOCAL  aplica
 *   php scripts/convertir_asignacion_a_directa.php --env=prod         # PROD   dry-run
 *   php scripts/convertir_asignacion_a_directa.php --env=prod --apply # PROD   aplica
 *
 * Opcionales: --doc=58   (id_documento exacto; si no, usa --cliente/--anio)
 *             --cliente=23  --anio=2026
 */

$argvList     = $argv ?? [];
$esProduccion = in_array('--env=prod', $argvList, true);
$aplicar      = in_array('--apply', $argvList, true);

$idDocumento = null;
$idCliente   = 23;
$anio        = 2026;
foreach ($argvList as $a) {
    if (str_starts_with($a, '--doc='))     $idDocumento = (int) substr($a, 6);
    if (str_starts_with($a, '--cliente=')) $idCliente   = (int) substr($a, 10);
    if (str_starts_with($a, '--anio='))    $anio        = (int) substr($a, 7);
}
$tipoDocumento = 'asignacion_responsable_sgsst';

if ($esProduccion) {
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port = 25060; $dbname = 'empresas_sst';
    $username = 'cycloid_userdb'; $password = 'AVNS_MR2SLvzRh3i_7o9fEHN';
    $ssl = true; echo "=== PRODUCCION ===\n";
} else {
    $host = '127.0.0.1'; $port = 3306; $dbname = 'empresas_sst';
    $username = 'root'; $password = ''; $ssl = false; echo "=== LOCAL ===\n";
}
echo "Modo: " . ($aplicar ? "APLICAR (UPDATE)" : "DRY RUN (solo lectura)") . "\n\n";

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    if ($ssl) { $options[PDO::MYSQL_ATTR_SSL_CA] = true; $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false; }
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "Conexion OK\n\n";
} catch (Throwable $e) { echo "ERROR conexion: " . $e->getMessage() . "\n"; exit(1); }

// Localizar documento
if ($idDocumento) {
    $stmt = $pdo->prepare("SELECT * FROM tbl_documentos_sst WHERE id_documento = ?");
    $stmt->execute([$idDocumento]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM tbl_documentos_sst WHERE id_cliente = ? AND tipo_documento = ? AND anio = ?");
    $stmt->execute([$idCliente, $tipoDocumento, $anio]);
}
$doc = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$doc) { echo "No se encontro el documento. Nada que hacer.\n"; exit(0); }

$idDocumento = (int) $doc['id_documento'];
$contenido = json_decode($doc['contenido'] ?? '{}', true);
if (!is_array($contenido)) { echo "ERROR: contenido JSON invalido.\n"; exit(1); }

echo "Documento: id={$idDocumento} | {$doc['codigo']} | anio={$doc['anio']} | version={$doc['version']} | estado={$doc['estado']}\n";
echo "Modalidad actual: " . ($contenido['modalidad'] ?? '(no definida -> con_empresa)') . "\n\n";

// Datos desde el JSON (identico a construirContenido)
$repNombre = $contenido['representante_legal']['nombre'] ?? '';
$repCedula = $contenido['representante_legal']['cedula'] ?? '';
$empNombre = $contenido['empresa']['nombre'] ?? '';
$proNombre = $contenido['responsable_sst']['nombre'] ?? '';
$proCedula = $contenido['responsable_sst']['cedula'] ?? '';
$proLic    = $contenido['responsable_sst']['licencia'] ?? '';

// Texto DIRECTA (igual a construirContenido modalidad='directa')
$textoDirecta = "<strong>{$repNombre}</strong> con documento de identidad <strong>{$repCedula}</strong> como representante legal de <strong>{$empNombre}</strong>, "
    . "nombro como responsable al profesional en Seguridad y Salud en el Trabajo a "
    . "<strong>{$proNombre}</strong> con documento de identidad <strong>{$proCedula}</strong>, con numero de licencia <strong>{$proLic}</strong>"
    . ", quien directamente diseña, administra y asesora el Sistema de Gestion "
    . "de Seguridad y Salud en el Trabajo (SG-SST) en la organizacion.";

// Mostrar antes/despues de la seccion asignacion_designacion
$idxSeccion = null;
foreach (($contenido['secciones'] ?? []) as $i => $sec) {
    if (($sec['key'] ?? '') === 'asignacion_designacion') { $idxSeccion = $i; break; }
}
if ($idxSeccion === null) { echo "ERROR: no se encontro la seccion 'asignacion_designacion'.\n"; exit(1); }

echo "----- ANTES -----\n" . strip_tags($contenido['secciones'][$idxSeccion]['contenido'] ?? '') . "\n\n";
echo "----- DESPUES ---\n" . strip_tags($textoDirecta) . "\n\n";

// Construir nuevo contenido
$nuevoContenido = $contenido;
$nuevoContenido['secciones'][$idxSeccion]['contenido'] = $textoDirecta;
$nuevoContenido['modalidad'] = 'directa';
$nuevoJson = json_encode($nuevoContenido, JSON_UNESCAPED_UNICODE);

// Version mas reciente (para sincronizar snapshot)
$verRow = $pdo->prepare("SELECT id_version, version FROM tbl_doc_versiones_sst WHERE id_documento = ? ORDER BY version DESC LIMIT 1");
$verRow->execute([$idDocumento]);
$ver = $verRow->fetch(PDO::FETCH_ASSOC);

if (!$aplicar) {
    echo "DRY RUN: no se modifico nada.\n";
    echo "Al aplicar se actualizaria:\n";
    echo "  - tbl_documentos_sst.contenido (id_documento={$idDocumento})\n";
    if ($ver) echo "  - tbl_doc_versiones_sst.contenido_snapshot (id_version={$ver['id_version']}, version={$ver['version']})\n";
    exit(0);
}

try {
    $pdo->beginTransaction();

    $u1 = $pdo->prepare("UPDATE tbl_documentos_sst SET contenido = ?, updated_at = NOW() WHERE id_documento = ?");
    $u1->execute([$nuevoJson, $idDocumento]);
    $n1 = $u1->rowCount();

    $n2 = 0;
    if ($ver) {
        $u2 = $pdo->prepare("UPDATE tbl_doc_versiones_sst SET contenido_snapshot = ? WHERE id_version = ?");
        $u2->execute([$nuevoJson, (int) $ver['id_version']]);
        $n2 = $u2->rowCount();
    }

    $pdo->commit();
    echo "ACTUALIZADO OK (transaccion confirmada):\n";
    echo "  tbl_documentos_sst        filas={$n1}\n";
    echo "  tbl_doc_versiones_sst     filas={$n2}\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    echo "ERROR durante el update (rollback): " . $e->getMessage() . "\n";
    exit(1);
}
