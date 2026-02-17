#!/usr/bin/env php
<?php
/**
 * Script CLI para comparar estructura 'firmado' en PRODUCCION vs LOCAL
 */

// --- PRODUCCION ---
$prodConn = new mysqli(
    'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
    'cycloid_userdb',
    'AVNS_iDypWizlpMRwHIORJGG',
    'empresas_sst',
    25060
);
// SSL requerido
mysqli_ssl_set($prodConn, null, null, null, null, null);

if ($prodConn->connect_error) {
    die("Error conexion PRODUCCION: " . $prodConn->connect_error . "\n");
}
echo "Conectado a PRODUCCION OK\n\n";

// Tablas a verificar (las que en LOCAL tienen 'firmado' en ENUM)
$tablasCheck = [
    'tbl_documentos_sst' => 'estado',
    'tbl_doc_firma_solicitudes' => 'estado',
    'tbl_acta_asistentes' => 'estado_firma',
    'tbl_recomposiciones_comite' => 'estado',
];

echo "=== COMPARACION: ENUM con 'firmado' ===\n\n";

foreach ($tablasCheck as $tabla => $columna) {
    echo "--- $tabla.$columna ---\n";

    // Verificar si la tabla existe
    $exists = $prodConn->query("SHOW TABLES LIKE '$tabla'");
    if (!$exists || $exists->num_rows === 0) {
        echo "  [!] TABLA NO EXISTE en produccion\n\n";
        continue;
    }

    // Obtener tipo de columna
    $r = $prodConn->query("
        SELECT COLUMN_TYPE
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = 'empresas_sst'
          AND TABLE_NAME = '$tabla'
          AND COLUMN_NAME = '$columna'
    ");

    if (!$r || $r->num_rows === 0) {
        echo "  [!] COLUMNA '$columna' NO EXISTE en produccion\n\n";
        continue;
    }

    $row = $r->fetch_assoc();
    $tipo = $row['COLUMN_TYPE'];

    $tieneFirmado = strpos($tipo, 'firmado') !== false;
    $status = $tieneFirmado ? '[OK]' : '[FALTA firmado]';

    echo "  PROD: $tipo\n";
    echo "  $status\n\n";
}

// Tambien verificar TODAS las columnas ENUM que contengan 'firmado' en PROD
echo "=== TODAS las ENUM con 'firmado' en PROD ===\n";
$r = $prodConn->query("
    SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'empresas_sst'
      AND COLUMN_TYPE LIKE '%firmado%'
    ORDER BY TABLE_NAME
");
if ($r && $r->num_rows > 0) {
    while ($row = $r->fetch_assoc()) {
        echo "  {$row['TABLE_NAME']}.{$row['COLUMN_NAME']}: {$row['COLUMN_TYPE']}\n";
    }
} else {
    echo "  (ninguna columna ENUM tiene 'firmado')\n";
}

// Verificar columnas con nombre 'firmado'
echo "\n=== Columnas con NOMBRE '%firmado%' en PROD ===\n";
$r = $prodConn->query("
    SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'empresas_sst'
      AND COLUMN_NAME LIKE '%firmado%'
    ORDER BY TABLE_NAME
");
if ($r && $r->num_rows > 0) {
    while ($row = $r->fetch_assoc()) {
        echo "  {$row['TABLE_NAME']}.{$row['COLUMN_NAME']}: {$row['COLUMN_TYPE']}\n";
    }
} else {
    echo "  (ninguna)\n";
}

$prodConn->close();
echo "\n=== FIN COMPARACION ===\n";
