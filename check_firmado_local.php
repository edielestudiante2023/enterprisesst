#!/usr/bin/env php
<?php
/**
 * Script CLI para investigar la columna/valor 'firmado' en la BD LOCAL
 */

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'empresas_sst';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Error conexion LOCAL: " . $conn->connect_error . "\n");
}

echo "=== INVESTIGACION LOCAL: 'firmado' ===\n\n";

// 1. Buscar tablas que tengan una COLUMNA llamada 'firmado'
echo "--- 1. Tablas con COLUMNA 'firmado' ---\n";
$r = $conn->query("
    SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = '$db'
      AND COLUMN_NAME LIKE '%firmado%'
    ORDER BY TABLE_NAME
");
if ($r && $r->num_rows > 0) {
    while ($row = $r->fetch_assoc()) {
        echo "  Tabla: {$row['TABLE_NAME']}, Columna: {$row['COLUMN_NAME']}, Tipo: {$row['COLUMN_TYPE']}\n";
    }
} else {
    echo "  (ninguna columna con 'firmado' en el nombre)\n";
}

// 2. Buscar columnas ENUM que contengan 'firmado' como valor
echo "\n--- 2. Columnas ENUM que incluyen 'firmado' como valor ---\n";
$r = $conn->query("
    SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = '$db'
      AND COLUMN_TYPE LIKE '%firmado%'
    ORDER BY TABLE_NAME
");
if ($r && $r->num_rows > 0) {
    while ($row = $r->fetch_assoc()) {
        echo "  Tabla: {$row['TABLE_NAME']}, Columna: {$row['COLUMN_NAME']}\n    Tipo: {$row['COLUMN_TYPE']}\n";
    }
} else {
    echo "  (ninguna)\n";
}

// 3. Detalle de tbl_documentos_sst.estado (principal sospechoso)
echo "\n--- 3. Estructura de tbl_documentos_sst ---\n";
$r = $conn->query("SHOW COLUMNS FROM tbl_documentos_sst");
if ($r) {
    while ($row = $r->fetch_assoc()) {
        echo "  {$row['Field']} | {$row['Type']} | Null:{$row['Null']} | Default:{$row['Default']}\n";
    }
}

// 4. Detalle de tbl_doc_firma_solicitudes.estado
echo "\n--- 4. Estructura de tbl_doc_firma_solicitudes ---\n";
$r = $conn->query("SHOW COLUMNS FROM tbl_doc_firma_solicitudes");
if ($r) {
    while ($row = $r->fetch_assoc()) {
        echo "  {$row['Field']} | {$row['Type']} | Null:{$row['Null']} | Default:{$row['Default']}\n";
    }
}

// 5. Detalle de tbl_doc_versiones_sst (por si tiene columna firmado)
echo "\n--- 5. Estructura de tbl_doc_versiones_sst ---\n";
$r = $conn->query("SHOW COLUMNS FROM tbl_doc_versiones_sst");
if ($r) {
    while ($row = $r->fetch_assoc()) {
        echo "  {$row['Field']} | {$row['Type']} | Null:{$row['Null']} | Default:{$row['Default']}\n";
    }
}

$conn->close();
echo "\n=== FIN INVESTIGACION LOCAL ===\n";
