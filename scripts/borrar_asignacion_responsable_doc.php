<?php
/**
 * Borra TODOS los registros del documento "Asignacion de Responsable del SG-SST"
 * (tipo_documento = 'asignacion_responsable_sgsst') de un cliente/anio dados,
 * para que pueda volver a generarse desde cero como version 1.
 *
 * Borra en orden de dependencias:
 *   1) tbl_doc_firma_evidencias   (por id_solicitud)
 *   2) tbl_doc_firma_audit_log    (por id_solicitud)
 *   3) tbl_doc_firma_solicitudes  (por id_documento)
 *   4) tbl_doc_versiones_sst      (por id_documento)
 *   5) tbl_documentos_sst         (por id_documento)
 *
 * Uso:
 *   php scripts/borrar_asignacion_responsable_doc.php                  # LOCAL  - dry run (solo muestra)
 *   php scripts/borrar_asignacion_responsable_doc.php --apply          # LOCAL  - borra
 *   php scripts/borrar_asignacion_responsable_doc.php --env=prod       # PROD   - dry run (solo muestra)
 *   php scripts/borrar_asignacion_responsable_doc.php --env=prod --apply  # PROD - borra
 *
 * Opcionales: --cliente=23  --anio=2026   (defaults abajo)
 */

$argvList     = $argv ?? [];
$esProduccion = in_array('--env=prod', $argvList, true);
$aplicar      = in_array('--apply', $argvList, true);

// Parametros del documento a borrar
$idCliente = 23;
$anio      = 2026;
foreach ($argvList as $a) {
    if (str_starts_with($a, '--cliente=')) $idCliente = (int) substr($a, 10);
    if (str_starts_with($a, '--anio='))    $anio      = (int) substr($a, 7);
}
$tipoDocumento = 'asignacion_responsable_sgsst';

if ($esProduccion) {
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port = 25060;
    $dbname = 'empresas_sst';
    $username = 'cycloid_userdb';
    $password = 'AVNS_MR2SLvzRh3i_7o9fEHN';
    $ssl = true;
    echo "=== PRODUCCION ===\n";
} else {
    $host = '127.0.0.1';
    $port = 3306;
    $dbname = 'empresas_sst';
    $username = 'root';
    $password = '';
    $ssl = false;
    echo "=== LOCAL ===\n";
}

echo "Modo: " . ($aplicar ? "APLICAR (DELETE)" : "DRY RUN (solo lectura)") . "\n";
echo "Objetivo: cliente={$idCliente}, tipo={$tipoDocumento}, anio={$anio}\n\n";

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    if ($ssl) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = true;
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "Conexion OK\n\n";
} catch (Throwable $e) {
    echo "ERROR conexion: " . $e->getMessage() . "\n";
    exit(1);
}

// 1) Localizar el/los documento(s)
$stmt = $pdo->prepare(
    "SELECT d.id_documento, d.codigo, d.titulo, d.anio, d.version, d.estado, c.nombre_cliente
       FROM tbl_documentos_sst d
       LEFT JOIN tbl_clientes c ON c.id_cliente = d.id_cliente
      WHERE d.id_cliente = ? AND d.tipo_documento = ? AND d.anio = ?"
);
$stmt->execute([$idCliente, $tipoDocumento, $anio]);
$docs = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$docs) {
    echo "No se encontro ningun documento con esos criterios. Nada que borrar.\n";
    exit(0);
}

$ids = array_map(fn($d) => (int) $d['id_documento'], $docs);
$inIds = implode(',', $ids);

echo "Documento(s) encontrado(s):\n";
foreach ($docs as $d) {
    echo "  - id_documento={$d['id_documento']} | {$d['codigo']} | \"{$d['titulo']}\" | cliente=\"{$d['nombre_cliente']}\" | anio={$d['anio']} | version={$d['version']} | estado={$d['estado']}\n";
}
echo "\n";

// 2) Solicitudes de firma vinculadas
$solicitudes = $pdo->query(
    "SELECT id_solicitud FROM tbl_doc_firma_solicitudes WHERE id_documento IN ({$inIds})"
)->fetchAll(PDO::FETCH_COLUMN);
$inSol = $solicitudes ? implode(',', array_map('intval', $solicitudes)) : '0';

// 3) Conteos de lo que se borraria
$count = function (string $sql) use ($pdo): int {
    return (int) $pdo->query($sql)->fetchColumn();
};
$nVersiones  = $count("SELECT COUNT(*) FROM tbl_doc_versiones_sst   WHERE id_documento IN ({$inIds})");
$nSolic      = count($solicitudes);
$nEvidencias = $count("SELECT COUNT(*) FROM tbl_doc_firma_evidencias WHERE id_solicitud IN ({$inSol})");
$nAudit      = $count("SELECT COUNT(*) FROM tbl_doc_firma_audit_log  WHERE id_solicitud IN ({$inSol})");
$nDocs       = count($docs);

echo "Registros vinculados que se borrarian:\n";
echo "  tbl_doc_firma_evidencias  : {$nEvidencias}\n";
echo "  tbl_doc_firma_audit_log   : {$nAudit}\n";
echo "  tbl_doc_firma_solicitudes : {$nSolic}\n";
echo "  tbl_doc_versiones_sst     : {$nVersiones}\n";
echo "  tbl_documentos_sst        : {$nDocs}\n\n";

if (!$aplicar) {
    echo "DRY RUN: no se borro nada. Ejecuta con --apply para borrar.\n";
    exit(0);
}

// 4) Borrado transaccional en orden de dependencias
try {
    $pdo->beginTransaction();

    $del = function (string $sql) use ($pdo): int {
        $st = $pdo->query($sql);
        return $st->rowCount();
    };

    $dEvi  = $inSol !== '0' ? $del("DELETE FROM tbl_doc_firma_evidencias WHERE id_solicitud IN ({$inSol})") : 0;
    $dAud  = $inSol !== '0' ? $del("DELETE FROM tbl_doc_firma_audit_log  WHERE id_solicitud IN ({$inSol})") : 0;
    $dSol  = $del("DELETE FROM tbl_doc_firma_solicitudes WHERE id_documento IN ({$inIds})");
    $dVer  = $del("DELETE FROM tbl_doc_versiones_sst     WHERE id_documento IN ({$inIds})");
    $dDoc  = $del("DELETE FROM tbl_documentos_sst        WHERE id_documento IN ({$inIds})");

    $pdo->commit();

    echo "BORRADO OK (transaccion confirmada):\n";
    echo "  tbl_doc_firma_evidencias  : {$dEvi}\n";
    echo "  tbl_doc_firma_audit_log   : {$dAud}\n";
    echo "  tbl_doc_firma_solicitudes : {$dSol}\n";
    echo "  tbl_doc_versiones_sst     : {$dVer}\n";
    echo "  tbl_documentos_sst        : {$dDoc}\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    echo "ERROR durante el borrado (rollback aplicado): " . $e->getMessage() . "\n";
    exit(1);
}
