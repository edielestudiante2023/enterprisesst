<?php
/**
 * Script CLI: Migrar sub-carpeta 2.5.1.1 Documentos Externos a PRODUCCION
 * Ejecutar: php ejecutar_sql_documentos_externos.php
 * Fecha: 2026-02-17
 */

// Credenciales produccion DigitalOcean
$host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
$port = 25060;
$db   = 'empresas_sst';
$user = 'cycloid_userdb';
$pass = 'AVNS_iDypWizlpMRwHIORJGG';

echo "=== Migracion: Sub-carpeta 2.5.1.1 Documentos Externos ===\n";
echo "Conectando a PRODUCCION ({$host}:{$port})...\n";

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_SSL_CA => true,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ];
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "Conexion exitosa!\n\n";
} catch (Exception $e) {
    echo "ERROR de conexion: " . $e->getMessage() . "\n";
    exit(1);
}

// Paso 1: Verificar estado actual en produccion
echo "--- Paso 1: Verificar estado actual ---\n";
$stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_doc_carpetas WHERE codigo = '2.5.1'");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Carpetas 2.5.1 encontradas: {$row['total']}\n";

$stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_doc_carpetas WHERE codigo = '2.5.1.1'");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Sub-carpetas 2.5.1.1 existentes: {$row['total']}\n\n";

if ($row['total'] > 0) {
    echo "Ya existen sub-carpetas 2.5.1.1 en produccion.\n";
    echo "Mostrando detalle:\n";
    $stmt = $pdo->query("
        SELECT sub.id_carpeta, sub.id_cliente, sub.nombre, sub.codigo
        FROM tbl_doc_carpetas sub
        WHERE sub.codigo = '2.5.1.1'
        ORDER BY sub.id_cliente
    ");
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  Cliente {$r['id_cliente']}: id_carpeta={$r['id_carpeta']} - {$r['nombre']}\n";
    }
    echo "\nNo se requiere migracion adicional.\n";
    exit(0);
}

// Paso 2: Insertar sub-carpetas 2.5.1.1
echo "--- Paso 2: Insertar sub-carpetas 2.5.1.1 ---\n";
$sql = "
    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    SELECT
        c.id_cliente,
        c.id_carpeta,
        '2.5.1.1. Listado Maestro de Documentos Externos',
        '2.5.1.1',
        1,
        'estandar',
        'file-earmark-arrow-up'
    FROM tbl_doc_carpetas c
    WHERE c.codigo = '2.5.1'
    AND NOT EXISTS (
        SELECT 1 FROM tbl_doc_carpetas sub
        WHERE sub.id_carpeta_padre = c.id_carpeta
        AND sub.codigo = '2.5.1.1'
    )
";

$affected = $pdo->exec($sql);
echo "Sub-carpetas insertadas: {$affected}\n\n";

// Paso 3: Verificar resultado
echo "--- Paso 3: Verificar resultado ---\n";
$stmt = $pdo->query("
    SELECT
        c.id_carpeta AS id_padre,
        c.id_cliente,
        c.nombre AS carpeta_padre,
        sub.id_carpeta AS id_sub,
        sub.nombre AS subcarpeta
    FROM tbl_doc_carpetas c
    LEFT JOIN tbl_doc_carpetas sub ON sub.id_carpeta_padre = c.id_carpeta AND sub.codigo = '2.5.1.1'
    WHERE c.codigo = '2.5.1'
    ORDER BY c.id_cliente
");

while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $status = $r['id_sub'] ? 'OK' : 'FALTA';
    echo "  [{$status}] Cliente {$r['id_cliente']}: padre={$r['id_padre']}, sub={$r['id_sub']}\n";
}

echo "\n=== Migracion completada exitosamente ===\n";
