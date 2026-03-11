<?php
/**
 * Fix: Corregir representación vacía en tbl_comite_miembros
 *
 * Causa: El formulario enviaba "trabajadores" (plural) pero el ENUM solo acepta "trabajador" (singular).
 * MySQL truncaba el valor inválido a '' (vacío).
 *
 * Este script muestra los miembros afectados para corrección manual.
 *
 * Ejecutar: php app/SQL/fix_representacion_miembros.php
 */

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

echo "Conectando a {$dbHost}:{$dbPort}/{$dbName}...\n";

$dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
$opciones = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
if ($dbHost !== 'localhost' && $dbHost !== '127.0.0.1') {
    $opciones[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    $opciones[PDO::MYSQL_ATTR_SSL_CA] = '';
}
$pdo = new PDO($dsn, $dbUser, $dbPass, $opciones);

echo "=== MIEMBROS CON REPRESENTACIÓN VACÍA ===\n\n";

$stmt = $pdo->query("
    SELECT m.id_miembro, m.nombre_completo, m.cargo, m.representacion, m.rol_comite,
           co.id_comite, tc.nombre as tipo_comite, c.nombre_cliente
    FROM tbl_comite_miembros m
    JOIN tbl_comites co ON co.id_comite = m.id_comite
    JOIN tbl_tipos_comite tc ON tc.id_tipo = co.id_tipo
    JOIN tbl_clientes c ON c.id_cliente = co.id_cliente
    WHERE m.representacion = '' OR m.representacion IS NULL
    ORDER BY c.nombre_cliente, co.id_comite, m.id_miembro
");
$afectados = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($afectados)) {
    echo "No hay miembros con representación vacía. Todo OK.\n";
    exit;
}

echo "Encontrados: " . count($afectados) . " miembros sin representación\n\n";

foreach ($afectados as $m) {
    echo "  M#{$m['id_miembro']} | {$m['nombre_completo']} | cargo={$m['cargo']} | rol={$m['rol_comite']}\n";
    echo "    Comité: {$m['tipo_comite']} (#{$m['id_comite']}) - {$m['nombre_cliente']}\n";
    echo "    representacion actual: '" . ($m['representacion'] ?? 'NULL') . "'\n\n";
}

// Corrección automática basada en rol_comite:
// - presidente, secretario → típicamente empleador
// - miembro sin más info → necesita confirmación manual
// Para COCOLAB de Ardurra sabemos que Milton Duarte y Daniel Sanabria son trabajadores

echo "=== CORRECCIÓN AUTOMÁTICA ===\n";
echo "Asignando 'trabajador' a miembros con representación vacía (causa: bug formulario 'trabajadores' plural)...\n\n";

$update = $pdo->prepare("UPDATE tbl_comite_miembros SET representacion = 'trabajador' WHERE id_miembro = ? AND (representacion = '' OR representacion IS NULL)");

$corregidos = 0;
foreach ($afectados as $m) {
    $update->execute([$m['id_miembro']]);
    if ($update->rowCount() > 0) {
        echo "  [CORREGIDO] M#{$m['id_miembro']} {$m['nombre_completo']} → representacion='trabajador'\n";
        $corregidos++;
    }
}

echo "\nCorregidos: {$corregidos} de " . count($afectados) . "\n";
echo "\nIMPORTANTE: Verifique que la representación sea correcta.\n";
echo "Si algún miembro debería ser 'empleador' en vez de 'trabajador', corríjalo desde la interfaz web.\n";
echo "\nDone.\n";
