<?php
/**
 * Verificar reportes de actas en tbl_reporte
 * Ejecutar: php app/SQL/verificar_reportes_actas.php [local|produccion]
 */
$entorno = $argv[1] ?? 'local';

if ($entorno === 'produccion') {
    $dsn = 'mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=empresas_sst;charset=utf8mb4';
    $user = 'cycloid_userdb';
    $pass = 'AVNS_MR2SLvzRh3i_7o9fEHN';
    $opciones = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false];
    echo "=== PRODUCCION ===\n";
} else {
    $dsn = 'mysql:host=localhost;port=3306;dbname=empresas_sst;charset=utf8mb4';
    $user = 'root';
    $pass = '';
    $opciones = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    echo "=== LOCAL ===\n";
}

$pdo = new PDO($dsn, $user, $pass, $opciones);

// Buscar todos los reportes de actas de constitucion para cliente 12
$stmt = $pdo->query("
    SELECT id_reporte, titulo_reporte, enlace, created_at, updated_at
    FROM tbl_reporte
    WHERE id_cliente = 12
    AND (titulo_reporte LIKE '%Constitucion%' OR titulo_reporte LIKE '%constitucion%')
    ORDER BY created_at DESC
");

echo "\nReportes de Actas de Constitucion (cliente 12):\n";
echo str_repeat('-', 120) . "\n";
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo "ID: {$r['id_reporte']} | Created: {$r['created_at']} | Updated: {$r['updated_at']}\n";
    echo "  Titulo: {$r['titulo_reporte']}\n";
    echo "  Enlace: {$r['enlace']}\n\n";
}
