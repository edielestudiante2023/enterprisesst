<?php
/**
 * Corregir titulos de reportes de actas que tienen codigo viejo FT-SST-013
 * Ejecutar: php app/SQL/corregir_reportes_actas.php [local|produccion]
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

// Corregir titulos: reemplazar FT-SST-013 por codigo correcto segun tipo
$correcciones = [
    'COCOLAB' => 'FT-SST-015',
    'BRIGADA' => 'FT-SST-016',
    'VIGIA'   => 'FT-SST-017',
];

echo "\n--- Corregir titulos en tbl_reporte ---\n";
foreach ($correcciones as $tipo => $nuevoCodigo) {
    $stmt = $pdo->prepare("
        UPDATE tbl_reporte
        SET titulo_reporte = REPLACE(titulo_reporte, 'FT-SST-013', ?)
        WHERE titulo_reporte LIKE '%Constitucion " . $tipo . "%'
        AND titulo_reporte LIKE '%FT-SST-013%'
    ");
    $stmt->execute([$nuevoCodigo]);
    echo "  {$tipo}: {$stmt->rowCount()} titulos corregidos a {$nuevoCodigo}\n";
}

// Verificar resultado
echo "\n--- Verificacion ---\n";
$stmt = $pdo->query("
    SELECT id_reporte, titulo_reporte, enlace, updated_at
    FROM tbl_reporte
    WHERE titulo_reporte LIKE '%Constitucion%'
    ORDER BY id_reporte DESC
    LIMIT 10
");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo "ID {$r['id_reporte']}: {$r['titulo_reporte']}\n";
    echo "  Enlace: {$r['enlace']}\n\n";
}

echo "Listo.\n";
