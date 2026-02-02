<?php
/**
 * Script para corregir el numero_acta de las actas existentes
 * El numero_acta debe ser solo el consecutivo (001, 002, 003)
 */
$pdo = new PDO('mysql:host=localhost;dbname=empresas_sst;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== CORRIGIENDO ACTAS EXISTENTES ===\n\n";

// Obtener todas las actas
$stmt = $pdo->query('SELECT id_acta, numero_acta, consecutivo_anual FROM tbl_actas ORDER BY id_acta');
$actas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$updateStmt = $pdo->prepare('UPDATE tbl_actas SET numero_acta = ? WHERE id_acta = ?');

foreach ($actas as $a) {
    // Extraer solo el consecutivo (ej: ACT-COCOLAB-2026-003 -> 003)
    $partes = explode('-', $a['numero_acta']);
    $consecutivo = end($partes);

    // Formato simple: solo el número con padding
    $nuevoNumero = str_pad($consecutivo, 3, '0', STR_PAD_LEFT);

    $updateStmt->execute([$nuevoNumero, $a['id_acta']]);
    echo "Acta {$a['id_acta']}: {$a['numero_acta']} -> {$nuevoNumero}\n";
}

echo "\n=== VERIFICACIÓN ===\n\n";
$stmt = $pdo->query('SELECT id_acta, numero_acta, codigo_documento, version_documento FROM tbl_actas ORDER BY id_acta');
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $a) {
    echo "ID: {$a['id_acta']} | Número: {$a['numero_acta']} | Código: {$a['codigo_documento']} | Versión: {$a['version_documento']}\n";
}

echo "\nCOMPLETADO.\n";
