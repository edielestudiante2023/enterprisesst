<?php
/**
 * Script para agregar columna codigo_documento a tbl_actas y actualizar valores
 */
$pdo = new PDO('mysql:host=localhost;dbname=empresas_sst;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== AGREGAR COLUMNA codigo_documento A tbl_actas ===\n\n";

// 1. Verificar estructura actual
$stmt = $pdo->query("DESCRIBE tbl_actas");
$columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
$columnasExistentes = array_column($columnas, 'Field');

echo "Columnas actuales: " . implode(', ', $columnasExistentes) . "\n\n";

// 2. Agregar columna codigo_documento si no existe
if (!in_array('codigo_documento', $columnasExistentes)) {
    echo "Agregando columna codigo_documento...\n";
    $pdo->exec("ALTER TABLE tbl_actas ADD COLUMN codigo_documento VARCHAR(20) NULL AFTER numero_acta");
    echo "✓ Columna agregada\n\n";
} else {
    echo "✓ Columna codigo_documento ya existe\n\n";
}

// 3. Agregar columna version_documento si no existe
if (!in_array('version_documento', $columnasExistentes)) {
    echo "Agregando columna version_documento...\n";
    $pdo->exec("ALTER TABLE tbl_actas ADD COLUMN version_documento VARCHAR(10) DEFAULT '001' AFTER codigo_documento");
    echo "✓ Columna version_documento agregada\n\n";
} else {
    echo "✓ Columna version_documento ya existe\n\n";
}

// 4. Calcular y actualizar códigos de documento
echo "=== ACTUALIZANDO CODIGOS DE DOCUMENTO ===\n\n";

$codigosComite = [
    'COPASST' => 'COP',
    'COCOLAB' => 'CCL',
    'BRIGADA' => 'BRI',
    'GENERAL' => 'GEN'
];

// Obtener todas las actas ordenadas por cliente, tipo y fecha
$stmt = $pdo->query("
    SELECT
        a.id_acta,
        a.numero_acta,
        c.id_cliente,
        tc.codigo as tipo_comite
    FROM tbl_actas a
    JOIN tbl_comites c ON c.id_comite = a.id_comite
    JOIN tbl_tipos_comite tc ON tc.id_tipo = c.id_tipo
    ORDER BY c.id_cliente, tc.codigo, a.created_at, a.id_acta
");

$actas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$consecutivos = [];
$updateStmt = $pdo->prepare("UPDATE tbl_actas SET codigo_documento = ? WHERE id_acta = ?");

foreach ($actas as $a) {
    $key = $a['id_cliente'] . '_' . $a['tipo_comite'];
    if (!isset($consecutivos[$key])) {
        $consecutivos[$key] = 0;
    }
    $consecutivos[$key]++;

    $codigoTipo = $codigosComite[$a['tipo_comite']] ?? substr($a['tipo_comite'], 0, 3);
    $codigoDocumento = 'ACT-' . $codigoTipo . '-' . str_pad($consecutivos[$key], 3, '0', STR_PAD_LEFT);

    $updateStmt->execute([$codigoDocumento, $a['id_acta']]);

    echo "Acta ID {$a['id_acta']}: {$a['numero_acta']} → {$codigoDocumento}\n";
}

echo "\n=== VERIFICACION FINAL ===\n\n";

$stmt = $pdo->query("
    SELECT
        a.id_acta,
        a.numero_acta,
        a.codigo_documento,
        a.version_documento,
        tc.codigo as tipo_comite
    FROM tbl_actas a
    JOIN tbl_comites c ON c.id_comite = a.id_comite
    JOIN tbl_tipos_comite tc ON tc.id_tipo = c.id_tipo
    ORDER BY a.id_acta
");

$actas = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "ID | Tipo | Numero Acta | Código Documento | Version\n";
echo str_repeat("-", 80) . "\n";

foreach ($actas as $a) {
    echo "{$a['id_acta']} | {$a['tipo_comite']} | {$a['numero_acta']} | {$a['codigo_documento']} | {$a['version_documento']}\n";
}

echo "\nCOMPLETADO.\n";
