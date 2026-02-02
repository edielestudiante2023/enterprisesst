<?php
/**
 * Script para sincronizar emails de actas con los emails actuales de miembros
 */

$pdo = new PDO('mysql:host=localhost;dbname=empresas_sst;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== SINCRONIZACION DE EMAILS EN tbl_acta_asistentes ===\n\n";

// Actualizar email de Diana Cuestas
$stmt = $pdo->prepare("UPDATE tbl_acta_asistentes SET email = ? WHERE email = ?");
$stmt->execute(['head.consultant.cycloidtalent@gmail.com', 'diana.cuestas@cycloidtalent.com']);
$count1 = $stmt->rowCount();
echo "Diana Cuestas: $count1 registros actualizados (diana.cuestas@cycloidtalent.com -> head.consultant.cycloidtalent@gmail.com)\n";

// Actualizar email de SOLANGEL CUERVO PERDOMO
$stmt = $pdo->prepare("UPDATE tbl_acta_asistentes SET email = ? WHERE email = ?");
$stmt->execute(['edielestudiante2023@gmail.com', 'solangel.cuervo@cycloidtalent.com']);
$count2 = $stmt->rowCount();
echo "SOLANGEL CUERVO PERDOMO: $count2 registros actualizados (solangel.cuervo@cycloidtalent.com -> edielestudiante2023@gmail.com)\n";

echo "\n=== VERIFICACION ===\n\n";

$stmt = $pdo->query("
    SELECT aa.id_asistente, aa.id_acta, aa.nombre_completo, aa.email, aa.cargo,
           a.numero_acta
    FROM tbl_acta_asistentes aa
    JOIN tbl_actas a ON a.id_acta = aa.id_acta
    ORDER BY aa.id_acta, aa.orden_firma
");

$asistentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($asistentes as $a) {
    echo "ID: {$a['id_asistente']} | Acta: {$a['numero_acta']} | {$a['nombre_completo']} | {$a['email']}\n";
}

echo "\nTotal actualizado: " . ($count1 + $count2) . " registros\n";
echo "COMPLETADO.\n";
