<?php
/**
 * Script para verificar actas y sus códigos de documento
 */
$pdo = new PDO('mysql:host=localhost;dbname=empresas_sst;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== ACTAS EN EL SISTEMA ===\n\n";

$stmt = $pdo->query("
    SELECT
        a.id_acta,
        a.numero_acta,
        a.fecha_reunion,
        a.estado,
        c.id_cliente,
        tc.codigo as tipo_comite,
        tc.nombre as nombre_comite
    FROM tbl_actas a
    JOIN tbl_comites c ON c.id_comite = a.id_comite
    JOIN tbl_tipos_comite tc ON tc.id_tipo = c.id_tipo
    ORDER BY c.id_cliente, tc.codigo, a.id_acta
");

$actas = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "ID | Cliente | Tipo Comité | Numero Acta | Fecha | Estado\n";
echo str_repeat("-", 100) . "\n";

$consecutivos = [];
foreach ($actas as $a) {
    $key = $a['id_cliente'] . '_' . $a['tipo_comite'];
    if (!isset($consecutivos[$key])) {
        $consecutivos[$key] = 0;
    }
    $consecutivos[$key]++;

    // Calcular código sugerido
    $codigosComite = [
        'COPASST' => 'COP',
        'COCOLAB' => 'CCL',
        'BRIGADA' => 'BRI',
        'GENERAL' => 'GEN'
    ];
    $codigoTipo = $codigosComite[$a['tipo_comite']] ?? substr($a['tipo_comite'], 0, 3);
    $codigoSugerido = 'ACT-' . $codigoTipo . '-' . str_pad($consecutivos[$key], 3, '0', STR_PAD_LEFT);

    echo "{$a['id_acta']} | {$a['id_cliente']} | {$a['tipo_comite']} | {$a['numero_acta']} | {$a['fecha_reunion']} | {$a['estado']} | Código: {$codigoSugerido}\n";
}

echo "\n\n=== PLANTILLAS DE ACTAS EN tbl_doc_plantillas ===\n\n";

$stmt = $pdo->query("
    SELECT id_plantilla, codigo_sugerido, nombre
    FROM tbl_doc_plantillas
    WHERE codigo_sugerido LIKE 'ACT-%'
    ORDER BY codigo_sugerido
");

$plantillas = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($plantillas)) {
    echo "No hay plantillas de actas registradas.\n";
    echo "\nSe necesita crear plantillas para actas con códigos:\n";
    echo "- ACT-COP: Acta COPASST\n";
    echo "- ACT-CCL: Acta Comité Convivencia Laboral\n";
    echo "- ACT-BRI: Acta Brigada de Emergencias\n";
    echo "- ACT-GEN: Acta General SST\n";
} else {
    foreach ($plantillas as $p) {
        echo "ID: {$p['id_plantilla']} | Código: {$p['codigo_sugerido']} | {$p['nombre']}\n";
    }
}

echo "\n\n=== VERIFICAR tbl_plantillas_documentos_sst ===\n\n";

$stmt = $pdo->query("
    SELECT id, codigo, version, nombre_documento, id_estandar
    FROM tbl_plantillas_documentos_sst
    WHERE codigo LIKE 'ACT-%'
    ORDER BY codigo
");

$plantillasDocs = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($plantillasDocs)) {
    echo "No hay plantillas de actas en tbl_plantillas_documentos_sst.\n";
} else {
    foreach ($plantillasDocs as $p) {
        echo "ID: {$p['id']} | Código: {$p['codigo']} | Version: {$p['version']} | {$p['nombre_documento']}\n";
    }
}

echo "\nCOMPLETADO.\n";
