<?php
/**
 * Script para actualizar emails en tablas de actas
 * Ejecutar desde la raíz del proyecto: php app/SQL/actualizar_emails_actas.php
 */

// Configuración de la base de datos
$host = 'localhost';
$dbname = 'empresas_sst';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== EMAILS ACTUALES EN tbl_acta_asistentes ===\n\n";

    $stmt = $pdo->query("
        SELECT aa.id_asistente, aa.id_acta, aa.nombre_completo, aa.email, aa.cargo,
               a.numero_acta, a.id_comite
        FROM tbl_acta_asistentes aa
        JOIN tbl_actas a ON a.id_acta = aa.id_acta
        ORDER BY aa.id_acta, aa.orden_firma
    ");

    $asistentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($asistentes as $a) {
        echo "ID: {$a['id_asistente']} | Acta: {$a['numero_acta']} (Comite {$a['id_comite']}) | {$a['nombre_completo']} | {$a['email']} | {$a['cargo']}\n";
    }

    echo "\n\n=== EMAILS EN tbl_miembros_comite ===\n\n";

    $stmt = $pdo->query("
        SELECT mc.id_miembro, mc.id_comite, mc.nombre_completo, mc.email, mc.cargo
        FROM tbl_miembros_comite mc
        WHERE mc.activo = 1
        ORDER BY mc.id_comite, mc.nombre_completo
    ");

    $miembros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($miembros as $m) {
        echo "ID: {$m['id_miembro']} | Comité: {$m['id_comite']} | {$m['nombre_completo']} | {$m['email']} | {$m['cargo']}\n";
    }

    echo "\n\nConsulta completada.\n";

} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage() . "\n";
    exit(1);
}
