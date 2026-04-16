<?php
/**
 * IPEVR GTC 45 - Registrar accesos en dashboard_items (Consultor).
 *
 * Agrega 2 cards al dashboard del consultor siguiendo el patron existente:
 *   1. Matriz IPEVR del Cliente  -> /ipevr/cliente/{id_cliente}
 *   2. Maestros del Cliente      -> /maestros-cliente/{id_cliente}
 *
 * Idempotente: usa INSERT ... ON DUPLICATE KEY UPDATE via verificacion previa
 * por accion_url (unico campo discriminador natural).
 *
 * Uso:
 *   php scripts/ipevr_gtc45_fase3b_dashboard.php             # LOCAL
 *   php scripts/ipevr_gtc45_fase3b_dashboard.php --env=prod  # PRODUCCION
 */

$esProduccion = in_array('--env=prod', $argv ?? []);

if ($esProduccion) {
    $host     = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port     = 25060;
    $dbname   = 'empresas_sst';
    $username = 'cycloid_userdb';
    $password = getenv('DB_PROD_PASS') ?: 'AVNS_MR2SLvzRh3i_7o9fEHN';
    $ssl      = true;
    echo "=== PRODUCCION ===\n";
} else {
    $host     = '127.0.0.1';
    $port     = 3306;
    $dbname   = 'empresas_sst';
    $username = 'root';
    $password = '';
    $ssl      = false;
    echo "=== LOCAL ===\n";
}

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    if ($ssl) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = true;
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "Conexion OK\n\n";
} catch (Throwable $e) {
    echo "ERROR conexion: " . $e->getMessage() . "\n";
    exit(1);
}

// Definicion de los 2 cards
$items = [
    [
        'rol' => 'Consultor',
        'tipo_proceso' => 'Cumplimiento',
        'detalle' => 'Matriz IPEVR GTC 45',
        'descripcion' => 'Identificacion de peligros y valoracion de riesgos - IPEVR IPER IPERC GTC 45 peligros riesgos (requiere selector)',
        'accion_url' => '/ipevr/cliente/{id_cliente}',
        'orden' => 10,
        'categoria' => 'Operación por Cliente',
        'icono' => 'fas fa-shield-halved',
        'color_gradiente' => '#bd9751,#d4af37',
        'target_blank' => 0,
        'activo' => 1,
    ],
    [
        'rol' => 'Consultor',
        'tipo_proceso' => 'Cumplimiento',
        'detalle' => 'Maestros del Cliente (IPEVR)',
        'descripcion' => 'Procesos cargos tareas zonas reutilizables por cliente - IPEVR IPER IPERC GTC 45 (requiere selector)',
        'accion_url' => '/maestros-cliente/{id_cliente}',
        'orden' => 11,
        'categoria' => 'Operación por Cliente',
        'icono' => 'fas fa-database',
        'color_gradiente' => '#1c2437,#2c3e50',
        'target_blank' => 0,
        'activo' => 1,
    ],
];

echo "-- Upsert de cards en dashboard_items --\n";

$stmtCheck = $pdo->prepare("SELECT id FROM dashboard_items WHERE accion_url = ? AND rol = ?");
$stmtInsert = $pdo->prepare("
    INSERT INTO dashboard_items
    (rol, tipo_proceso, detalle, descripcion, accion_url, orden, categoria, icono, color_gradiente, target_blank, activo, creado_en, actualizado_en)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
");
$stmtUpdate = $pdo->prepare("
    UPDATE dashboard_items
    SET tipo_proceso=?, detalle=?, descripcion=?, orden=?, categoria=?, icono=?, color_gradiente=?, target_blank=?, activo=?, actualizado_en=NOW()
    WHERE id=?
");

foreach ($items as $it) {
    $stmtCheck->execute([$it['accion_url'], $it['rol']]);
    $existente = $stmtCheck->fetchColumn();

    if ($existente) {
        $stmtUpdate->execute([
            $it['tipo_proceso'], $it['detalle'], $it['descripcion'], $it['orden'],
            $it['categoria'], $it['icono'], $it['color_gradiente'], $it['target_blank'], $it['activo'],
            $existente,
        ]);
        echo "  UPD id={$existente}  {$it['detalle']}\n";
    } else {
        $stmtInsert->execute([
            $it['rol'], $it['tipo_proceso'], $it['detalle'], $it['descripcion'], $it['accion_url'],
            $it['orden'], $it['categoria'], $it['icono'], $it['color_gradiente'], $it['target_blank'], $it['activo'],
        ]);
        $newId = $pdo->lastInsertId();
        echo "  NEW id={$newId}  {$it['detalle']}\n";
    }
}

echo "\n-- Verificacion --\n";
foreach ($items as $it) {
    $stmtCheck->execute([$it['accion_url'], $it['rol']]);
    $id = $stmtCheck->fetchColumn();
    echo "  " . ($id ? "OK  id={$id}" : "MISS") . "  {$it['accion_url']}\n";
}

echo "\nFASE 3b DASHBOARD COMPLETADA OK\n";
exit(0);
