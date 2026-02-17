#!/usr/bin/env php
<?php
/**
 * Comparar tbl_doc_documentos LOCAL vs PRODUCCION
 * Y buscar CUALQUIER tabla que NO tenga alguna columna esperada
 */

// LOCAL
$local = new mysqli('localhost', 'root', '', 'empresas_sst');
if ($local->connect_error) die("Error LOCAL: " . $local->connect_error . "\n");

// PRODUCCION
$prod = new mysqli(
    'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
    'cycloid_userdb',
    'AVNS_iDypWizlpMRwHIORJGG',
    'empresas_sst',
    25060
);
if ($prod->connect_error) die("Error PROD: " . $prod->connect_error . "\n");

echo "=== Conectado a LOCAL y PRODUCCION ===\n\n";

// 1. Comparar tbl_doc_documentos
echo "--- tbl_doc_documentos: LOCAL ---\n";
$r = $local->query("SHOW COLUMNS FROM tbl_doc_documentos");
$localCols = [];
if ($r) {
    while ($row = $r->fetch_assoc()) {
        $localCols[$row['Field']] = $row['Type'];
        echo "  {$row['Field']} | {$row['Type']}\n";
    }
}

echo "\n--- tbl_doc_documentos: PRODUCCION ---\n";
$exists = $prod->query("SHOW TABLES LIKE 'tbl_doc_documentos'");
if (!$exists || $exists->num_rows === 0) {
    echo "  [!] TABLA NO EXISTE en produccion!\n";
} else {
    $r = $prod->query("SHOW COLUMNS FROM tbl_doc_documentos");
    $prodCols = [];
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            $prodCols[$row['Field']] = $row['Type'];
            echo "  {$row['Field']} | {$row['Type']}\n";
        }
    }

    // Diferencias
    echo "\n--- Columnas en LOCAL pero NO en PROD ---\n";
    foreach ($localCols as $col => $type) {
        if (!isset($prodCols[$col])) {
            echo "  [FALTA] $col ($type)\n";
        } elseif ($prodCols[$col] !== $type) {
            echo "  [DIFF] $col: LOCAL=$type vs PROD={$prodCols[$col]}\n";
        }
    }
    echo "\n--- Columnas en PROD pero NO en LOCAL ---\n";
    foreach ($prodCols as $col => $type) {
        if (!isset($localCols[$col])) {
            echo "  [EXTRA] $col ($type)\n";
        }
    }
}

// 2. Comparar TODAS las tablas tbl_doc_* entre local y prod
echo "\n\n=== COMPARACION COMPLETA: tablas tbl_doc_* ===\n";
$r = $local->query("SHOW TABLES LIKE 'tbl_doc_%'");
$tablasLocal = [];
while ($row = $r->fetch_row()) {
    $tablasLocal[] = $row[0];
}

foreach ($tablasLocal as $tabla) {
    $existsProd = $prod->query("SHOW TABLES LIKE '$tabla'");
    if (!$existsProd || $existsProd->num_rows === 0) {
        echo "\n[!] $tabla: NO EXISTE en PRODUCCION\n";
        continue;
    }

    // Obtener columnas de ambos
    $lCols = [];
    $r = $local->query("SHOW COLUMNS FROM $tabla");
    while ($row = $r->fetch_assoc()) $lCols[$row['Field']] = $row['Type'];

    $pCols = [];
    $r = $prod->query("SHOW COLUMNS FROM $tabla");
    while ($row = $r->fetch_assoc()) $pCols[$row['Field']] = $row['Type'];

    $diffs = [];
    foreach ($lCols as $col => $type) {
        if (!isset($pCols[$col])) {
            $diffs[] = "  [FALTA en PROD] $col ($type)";
        } elseif ($pCols[$col] !== $type) {
            $diffs[] = "  [DIFF] $col: L=$type vs P={$pCols[$col]}";
        }
    }
    foreach ($pCols as $col => $type) {
        if (!isset($lCols[$col])) {
            $diffs[] = "  [EXTRA en PROD] $col ($type)";
        }
    }

    if (!empty($diffs)) {
        echo "\n$tabla:\n" . implode("\n", $diffs) . "\n";
    }
}

// 3. Buscar si hay algun query que referencia 'firmado' como columna
// Simulamos el query del dashboard para ver si falla
echo "\n\n=== TEST: Queries del dashboard en PROD ===\n";

// Test 1: getEstadisticas
echo "Test getEstadisticas (tbl_doc_documentos)... ";
$r = $prod->query("SELECT estado, COUNT(*) as cantidad FROM tbl_doc_documentos WHERE id_cliente = 18 GROUP BY estado");
if ($r) { echo "OK\n"; } else { echo "ERROR: " . $prod->error . "\n"; }

// Test 2: getByCliente
echo "Test getByCliente (tbl_doc_documentos)... ";
$r = $prod->query("SELECT * FROM tbl_doc_documentos WHERE id_cliente = 18 ORDER BY updated_at DESC LIMIT 1");
if ($r) { echo "OK\n"; } else { echo "ERROR: " . $prod->error . "\n"; }

// Test 3: getEstadoIA (tbl_doc_secciones)
echo "Test getEstadoIA (tbl_doc_secciones)... ";
$r = $prod->query("SELECT COUNT(*) as total, SUM(CASE WHEN contenido IS NOT NULL AND contenido != '' THEN 1 ELSE 0 END) as con_contenido, SUM(CASE WHEN aprobado = 1 THEN 1 ELSE 0 END) as aprobadas FROM tbl_doc_secciones WHERE id_documento = 1");
if ($r) { echo "OK\n"; } else { echo "ERROR: " . $prod->error . "\n"; }

// Test 4: getResumenCumplimiento
echo "Test getResumenCumplimiento (tbl_cliente_estandares)... ";
$r = $prod->query("SELECT estado, COUNT(*) as cantidad FROM tbl_cliente_estandares WHERE id_cliente = 18 GROUP BY estado");
if ($r) { echo "OK\n"; } else { echo "ERROR: " . $prod->error . "\n"; }

// Test 5: getDocumentosPorEstado -> fieldExists
echo "Test fieldExists aplica_60 en tbl_doc_plantillas... ";
$r = $prod->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='empresas_sst' AND TABLE_NAME='tbl_doc_plantillas' AND COLUMN_NAME='aplica_60'");
if ($r && $r->num_rows > 0) { echo "OK (existe)\n"; } else { echo "NO EXISTE\n"; }

// Test 6: Verificar si 'firmado' aparece en allowedFields de algún modelo CI4
// Buscar en tbl_doc_documentos si el estado 'firmado' es válido
echo "\nTest: estados ENUM de tbl_doc_documentos en PROD... ";
$r = $prod->query("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='empresas_sst' AND TABLE_NAME='tbl_doc_documentos' AND COLUMN_NAME='estado'");
if ($r) {
    $row = $r->fetch_assoc();
    echo $row['COLUMN_TYPE'] . "\n";
}

$local->close();
$prod->close();
echo "\n=== FIN ===\n";
