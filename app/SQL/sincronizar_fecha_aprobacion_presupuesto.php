<?php
/**
 * Script para sincronizar fecha_aprobacion de presupuestos con tbl_documentos_sst
 * El problema: Los presupuestos guardan fecha_aprobacion en tbl_presupuesto_sst
 * pero no se sincronizaba con tbl_documentos_sst (donde se muestra en la maestra)
 *
 * Ejecutar una sola vez para corregir documentos existentes
 * @date 2026-01-31
 */

// Conexion directa a MySQL
$host = 'localhost';
$dbname = 'empresas_sst';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexion: " . $e->getMessage() . "\n");
}

echo "=== Sincronizar fecha_aprobacion de Presupuestos SST ===\n\n";

// Buscar documentos de presupuesto_sst sin fecha_aprobacion
$sql = "
    SELECT d.id_documento, d.id_cliente, d.anio, d.estado, d.fecha_aprobacion as doc_fecha,
           p.fecha_aprobacion as presupuesto_fecha, p.estado as presupuesto_estado
    FROM tbl_documentos_sst d
    LEFT JOIN tbl_presupuesto_sst p ON p.id_cliente = d.id_cliente AND p.anio = d.anio
    WHERE d.tipo_documento = 'presupuesto_sst'
    AND (d.fecha_aprobacion IS NULL OR d.fecha_aprobacion = '')
";

$stmt = $pdo->query($sql);
$documentosSinFecha = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Documentos de presupuesto sin fecha_aprobacion: " . count($documentosSinFecha) . "\n\n";

$actualizados = 0;
foreach ($documentosSinFecha as $doc) {
    echo "ID: {$doc['id_documento']} | Cliente: {$doc['id_cliente']} | Anio: {$doc['anio']}\n";
    echo "  Estado documento: {$doc['estado']}\n";
    echo "  Estado presupuesto: " . ($doc['presupuesto_estado'] ?? 'N/A') . "\n";
    echo "  Fecha en presupuesto: " . ($doc['presupuesto_fecha'] ?? 'NULL') . "\n";

    // Si el presupuesto tiene fecha, sincronizar
    if (!empty($doc['presupuesto_fecha'])) {
        $updateStmt = $pdo->prepare("UPDATE tbl_documentos_sst SET fecha_aprobacion = ? WHERE id_documento = ?");
        $updateStmt->execute([$doc['presupuesto_fecha'], $doc['id_documento']]);
        echo "  -> ACTUALIZADO con fecha: {$doc['presupuesto_fecha']}\n";
        $actualizados++;
    } else {
        // Si no tiene fecha pero el estado es aprobado/firmado, usar fecha actual
        if (in_array($doc['estado'], ['aprobado', 'firmado'])) {
            $fechaUsar = date('Y-m-d H:i:s');
            $updateStmt = $pdo->prepare("UPDATE tbl_documentos_sst SET fecha_aprobacion = ? WHERE id_documento = ?");
            $updateStmt->execute([$fechaUsar, $doc['id_documento']]);
            echo "  -> ACTUALIZADO con fecha actual: {$fechaUsar}\n";
            $actualizados++;
        } else {
            echo "  -> Sin cambios (estado no requiere fecha)\n";
        }
    }
    echo "\n";
}

echo "=== RESUMEN ===\n";
echo "Total documentos revisados: " . count($documentosSinFecha) . "\n";
echo "Documentos actualizados: {$actualizados}\n";
