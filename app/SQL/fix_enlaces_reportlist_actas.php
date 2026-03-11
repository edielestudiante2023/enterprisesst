<?php
/**
 * Fix: Corregir enlaces de actas en tbl_reporte (quitar /ver del final)
 * Y también eliminar los registros malos para que se re-creen con el enlace correcto
 *
 * Ejecutar: php app/SQL/fix_enlaces_reportlist_actas.php
 */

// Leer credenciales del .env
$envFile = __DIR__ . '/../../.env';
$dbHost = 'localhost'; $dbName = 'empresas_sst'; $dbUser = 'root'; $dbPass = ''; $dbPort = '3306';

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (str_contains($line, 'database.default.hostname')) $dbHost = trim(explode('=', $line, 2)[1] ?? $dbHost);
        if (str_contains($line, 'database.default.database')) $dbName = trim(explode('=', $line, 2)[1] ?? $dbName);
        if (str_contains($line, 'database.default.username')) $dbUser = trim(explode('=', $line, 2)[1] ?? $dbUser);
        if (str_contains($line, 'database.default.password')) $dbPass = trim(explode('=', $line, 2)[1] ?? $dbPass);
        if (str_contains($line, 'database.default.port')) $dbPort = trim(explode('=', $line, 2)[1] ?? $dbPort);
    }
}

$dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
$opciones = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
if ($dbHost !== 'localhost' && $dbHost !== '127.0.0.1') {
    $opciones[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    $opciones[PDO::MYSQL_ATTR_SSL_CA] = '';
}
$pdo = new PDO($dsn, $dbUser, $dbPass, $opciones);

echo "=== FIX ENLACES ACTAS EN REPORTLIST ===\n\n";

// Buscar registros auto-publicados con enlace /ver que da 404
$stmt = $pdo->query("SELECT id_reporte, titulo_reporte, enlace FROM tbl_reporte WHERE observaciones LIKE '%Auto-publicado%' AND enlace LIKE '%/ver'");
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($registros)) {
    echo "No hay registros con enlace /ver para corregir\n";
} else {
    echo "Encontrados: " . count($registros) . " registros para corregir\n\n";
    foreach ($registros as $r) {
        // Cambiar /actas/comite/X/acta/Y/ver → /actas/pdf/Y (descarga directa del PDF)
        $enlaceViejo = $r['enlace'];
        if (preg_match('/\/acta\/(\d+)\/ver$/', $enlaceViejo, $m)) {
            $idActa = $m[1];
            $enlaceNuevo = preg_replace('/\/actas\/comite\/\d+\/acta\/\d+\/ver$/', "/actas/pdf/{$idActa}", $enlaceViejo);

            $update = $pdo->prepare("UPDATE tbl_reporte SET enlace = ? WHERE id_reporte = ?");
            $update->execute([$enlaceNuevo, $r['id_reporte']]);

            echo "  [FIXED] #{$r['id_reporte']} {$r['titulo_reporte']}\n";
            echo "    Antes:   {$enlaceViejo}\n";
            echo "    Después: {$enlaceNuevo}\n\n";
        }
    }
}

echo "Done.\n";
