<?php
/**
 * Script ONE-TIME: Publicar actas ya firmadas en reportList
 * Las actas se firmaron antes de implementar la auto-publicación.
 *
 * Ejecutar: php app/SQL/publicar_actas_firmadas_en_reportlist.php
 */

// Leer credenciales del .env de CodeIgniter
$envFile = __DIR__ . '/../../.env';
$dbHost = 'localhost';
$dbName = 'empresas_sst';
$dbUser = 'root';
$dbPass = '';
$dbPort = '3306';

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

echo "Conectando a {$dbHost}:{$dbPort}/{$dbName} como {$dbUser}...\n";

$dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";

// SSL requerido para servidores remotos (DigitalOcean, etc.)
$opciones = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
if ($dbHost !== 'localhost' && $dbHost !== '127.0.0.1') {
    $opciones[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    $opciones[PDO::MYSQL_ATTR_SSL_CA] = '';
}

$pdo = new PDO($dsn, $dbUser, $dbPass, $opciones);

echo "=== PUBLICAR ACTAS FIRMADAS EN REPORTLIST ===\n\n";

// 1. Obtener actas en estado 'firmada'
$stmt = $pdo->query("
    SELECT a.id_acta, a.numero_acta, a.consecutivo_anual, a.fecha_reunion,
           a.codigo_verificacion, a.id_cliente, a.id_comite,
           c.nombre_cliente, c.nit_cliente,
           tc.codigo as tipo_comite, tc.nombre as tipo_nombre
    FROM tbl_actas a
    JOIN tbl_clientes c ON c.id_cliente = a.id_cliente
    JOIN tbl_comites co ON co.id_comite = a.id_comite
    JOIN tbl_tipos_comite tc ON tc.id_tipo = co.id_tipo
    WHERE a.estado = 'firmada'
    ORDER BY a.id_acta
");
$actasFirmadas = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($actasFirmadas)) {
    echo "No hay actas en estado 'firmada'\n";
    exit;
}

echo "Actas firmadas encontradas: " . count($actasFirmadas) . "\n\n";

$publicadas = 0;
$yaExistentes = 0;

foreach ($actasFirmadas as $acta) {
    // Verificar si ya está en tbl_reporte
    $check = $pdo->prepare("SELECT id_reporte FROM tbl_reporte WHERE titulo_reporte LIKE ? AND id_cliente = ?");
    $patron = "%Acta de Reunión #{$acta['consecutivo_anual']}%{$acta['tipo_nombre']}%";
    $check->execute([$patron, $acta['id_cliente']]);

    if ($check->fetch()) {
        echo "  [YA EXISTE] Acta #{$acta['id_acta']} - {$acta['numero_acta']} - {$acta['nombre_cliente']}\n";
        $yaExistentes++;
        continue;
    }

    // Generar código documento
    $codigosComite = ['COPASST' => 'COP', 'COCOLAB' => 'COL', 'BRIGADA' => 'BRI', 'GENERAL' => 'GEN'];
    $codigoDoc = 'ACT-' . ($codigosComite[$acta['tipo_comite']] ?? substr($acta['tipo_comite'], 0, 3));

    $fechaFormateada = date('d/m/Y', strtotime($acta['fecha_reunion']));
    $tituloReporte = "{$codigoDoc} - Acta de Reunión #{$acta['consecutivo_anual']} - {$acta['tipo_nombre']} {$fechaFormateada} (Firmado)";

    // URL al acta (vista web, no PDF estático)
    $enlace = "https://dashboard.cycloidtalent.com/actas/comite/{$acta['id_comite']}/acta/{$acta['id_acta']}/ver";

    $insert = $pdo->prepare("
        INSERT INTO tbl_reporte (titulo_reporte, id_detailreport, id_report_type, id_cliente, estado, observaciones, enlace, created_at, updated_at)
        VALUES (?, 2, 1, ?, 'CERRADO', ?, ?, NOW(), NOW())
    ");

    $observaciones = "Auto-publicado desde actas firmadas. Código verificación: " . ($acta['codigo_verificacion'] ?? 'N/A');

    $insert->execute([
        $tituloReporte,
        $acta['id_cliente'],
        $observaciones,
        $enlace
    ]);

    $publicadas++;
    echo "  [PUBLICADA] Acta #{$acta['id_acta']} - {$acta['numero_acta']} - {$acta['nombre_cliente']} -> {$tituloReporte}\n";
}

echo "\n=== RESULTADO ===\n";
echo "Publicadas: {$publicadas}\n";
echo "Ya existían: {$yaExistentes}\n";
echo "Total firmadas: " . count($actasFirmadas) . "\n";
echo "\nDone.\n";
