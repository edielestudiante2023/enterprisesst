<?php
/**
 * Inspecciona tablas hijas (sin id_cliente) enlazadas al cliente 11 via JOIN con tablas padre.
 * SOLO LECTURA.
 */
$pdo = new PDO('mysql:host=localhost;port=3306;dbname=empresas_sst;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$id = 11;

echo "=== TABLAS HIJAS ENLAZADAS AL CLIENTE {$id} ===\n\n";

// Checks: [tabla_hija, columna_fk, tabla_padre, pk_padre]
$checks = [
    // Documentos
    ['tbl_doc_documentos',          'id_carpeta',    'tbl_doc_carpetas',    'id_carpeta'],
    ['tbl_doc_secciones',           'id_documento',  'tbl_documentos_sst',  'id_documento'],
    ['tbl_doc_firma_solicitudes',   'id_documento',  'tbl_documentos_sst',  'id_documento'],
    ['tbl_doc_firma_evidencias',    'id_documento',  'tbl_documentos_sst',  'id_documento'],
    ['tbl_doc_firma_audit_log',     'id_documento',  'tbl_documentos_sst',  'id_documento'],
    ['tbl_doc_exportaciones',       'id_documento',  'tbl_documentos_sst',  'id_documento'],
    ['tbl_doc_indicadores_mediciones','id_indicador','tbl_indicadores_sst', 'id_indicador'],
    // Indicadores
    ['tbl_indicadores_sst_mediciones','id_indicador','tbl_indicadores_sst', 'id_indicador'],
    // Presupuesto
    ['tbl_presupuesto_items',       'id_presupuesto','tbl_presupuesto_sst', 'id_presupuesto'],
    ['tbl_presupuesto_detalle',     'id_presupuesto','tbl_presupuesto_sst', 'id_presupuesto'],
    // Actas
    ['tbl_acta_anexos',             'id_acta',       'tbl_actas',           'id_acta'],
    ['tbl_acta_asistentes',         'id_acta',       'tbl_actas',           'id_acta'],
    ['tbl_acta_votaciones',         'id_acta',       'tbl_actas',           'id_acta'],
    // Comites / procesos electorales
    ['tbl_candidatos_comite',       'id_proceso',    'tbl_procesos_electorales','id_proceso'],
    // PTA
    ['tbl_acta_visita_pta',         'id_ptacliente', 'tbl_pta_cliente',     'id_ptacliente'],
];

foreach ($checks as $c) {
    [$hija, $fk, $padre, $pk] = $c;
    try {
        // Verificar que columnas existen
        $colsHija = $pdo->query("SHOW COLUMNS FROM `{$hija}`")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array($fk, $colsHija)) {
            // intentar variantes
            $variantes = [$fk, str_replace('id_','id_',$fk)];
            $encontrada = null;
            foreach ($colsHija as $cc) {
                if (strpos($cc, str_replace('id_','',$fk)) !== false) { $encontrada = $cc; break; }
            }
            echo sprintf("  %-40s SKIP (no col '{$fk}'; cols: %s)\n", $hija, implode(',', $colsHija));
            continue;
        }
        $sql = "SELECT COUNT(*) FROM `{$hija}` h INNER JOIN `{$padre}` p ON h.`{$fk}` = p.`{$pk}` WHERE p.id_cliente = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $n = (int)$stmt->fetchColumn();
        $marker = $n > 0 ? '*' : ' ';
        echo sprintf("%s %-40s %-25s -> %-25s : %d\n", $marker, $hija, $fk, $padre, $n);
    } catch (PDOException $e) {
        echo sprintf("  %-40s ERR: %s\n", $hija, $e->getMessage());
    }
}

// Busqueda amplia: cualquier tabla que tenga id_documento e id_cliente
echo "\n=== Tablas con id_documento (por prefijo tbl_doc_) + conteo cliente 11 ===\n";
$sql = "SELECT DISTINCT TABLE_NAME
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND COLUMN_NAME = 'id_documento'
          AND TABLE_NAME LIKE 'tbl_%'
        ORDER BY TABLE_NAME";
$tablas = $pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN);
foreach ($tablas as $t) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM `{$t}` h INNER JOIN tbl_documentos_sst d ON h.id_documento = d.id_documento WHERE d.id_cliente = ?");
        $stmt->execute([$id]);
        $n = (int)$stmt->fetchColumn();
        echo sprintf("  %-40s : %d\n", $t, $n);
    } catch (PDOException $e) {
        echo sprintf("  %-40s ERR\n", $t);
    }
}

echo "\n=== Tablas con id_indicador (por prefijo tbl_) ===\n";
$sql = "SELECT DISTINCT TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND COLUMN_NAME = 'id_indicador' AND TABLE_NAME LIKE 'tbl_%'
        ORDER BY TABLE_NAME";
foreach ($pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN) as $t) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM `{$t}` h INNER JOIN tbl_indicadores_sst i ON h.id_indicador = i.id_indicador WHERE i.id_cliente = ?");
        $stmt->execute([$id]);
        $n = (int)$stmt->fetchColumn();
        echo sprintf("  %-40s : %d\n", $t, $n);
    } catch (PDOException $e) {}
}

echo "\n=== Tablas con id_acta ===\n";
$sql = "SELECT DISTINCT TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND COLUMN_NAME = 'id_acta' AND TABLE_NAME LIKE 'tbl_%'
        ORDER BY TABLE_NAME";
foreach ($pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN) as $t) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM `{$t}` h INNER JOIN tbl_actas a ON h.id_acta = a.id_acta WHERE a.id_cliente = ?");
        $stmt->execute([$id]);
        $n = (int)$stmt->fetchColumn();
        echo sprintf("  %-40s : %d\n", $t, $n);
    } catch (PDOException $e) {}
}

echo "\n=== Tablas con id_proceso (procesos electorales) ===\n";
$sql = "SELECT DISTINCT TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND COLUMN_NAME = 'id_proceso' AND TABLE_NAME LIKE 'tbl_%'
        ORDER BY TABLE_NAME";
foreach ($pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN) as $t) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM `{$t}` h INNER JOIN tbl_procesos_electorales p ON h.id_proceso = p.id_proceso WHERE p.id_cliente = ?");
        $stmt->execute([$id]);
        $n = (int)$stmt->fetchColumn();
        echo sprintf("  %-40s : %d\n", $t, $n);
    } catch (PDOException $e) {}
}

echo "\n=== Tablas con id_comite ===\n";
$sql = "SELECT DISTINCT TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND COLUMN_NAME = 'id_comite' AND TABLE_NAME LIKE 'tbl_%'
        ORDER BY TABLE_NAME";
foreach ($pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN) as $t) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM `{$t}` h INNER JOIN tbl_comites c ON h.id_comite = c.id_comite WHERE c.id_cliente = ?");
        $stmt->execute([$id]);
        $n = (int)$stmt->fetchColumn();
        echo sprintf("  %-40s : %d\n", $t, $n);
    } catch (PDOException $e) {}
}

echo "\n=== Tablas con id_ptacliente ===\n";
$sql = "SELECT DISTINCT TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND COLUMN_NAME = 'id_ptacliente' AND TABLE_NAME LIKE 'tbl_%'
        ORDER BY TABLE_NAME";
foreach ($pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN) as $t) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM `{$t}` h INNER JOIN tbl_pta_cliente p ON h.id_ptacliente = p.id_ptacliente WHERE p.id_cliente = ?");
        $stmt->execute([$id]);
        $n = (int)$stmt->fetchColumn();
        echo sprintf("  %-40s : %d\n", $t, $n);
    } catch (PDOException $e) {}
}
