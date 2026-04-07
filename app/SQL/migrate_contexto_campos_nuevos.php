<?php
/**
 * Migración: Campos nuevos para tbl_cliente_contexto_sst
 * 18 campos: horarios, seguridad social, operacionales, historial SST, infraestructura
 * Uso: php migrate_contexto_campos_nuevos.php [local|production]
 */

if (php_sapi_name() !== 'cli') {
    die('Este script solo puede ejecutarse desde la línea de comandos.');
}

$env = $argv[1] ?? 'local';

if ($env === 'local') {
    $config = [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'root',
        'password' => '',
        'database' => 'empresas_sst',
        'ssl'      => false,
    ];
} elseif ($env === 'production') {
    $config = [
        'host'     => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port'     => 25060,
        'user'     => 'cycloid_userdb',
        'password' => getenv('DB_PROD_PASS') ?: 'AVNS_MR2SLvzRh3i_7o9fEHN',
        'database' => 'empresas_sst',
        'ssl'      => true,
    ];
} else {
    die("Uso: php migrate_contexto_campos_nuevos.php [local|production]\n");
}

echo "=== Migración: Campos nuevos Contexto SST ===\n";
echo "Entorno: " . strtoupper($env) . "\n";
echo "Host: {$config['host']}:{$config['port']}\n";
echo "---\n";

$mysqli = mysqli_init();

if ($config['ssl']) {
    $mysqli->ssl_set(null, null, null, null, null);
    $mysqli->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
}

$connected = @$mysqli->real_connect(
    $config['host'],
    $config['user'],
    $config['password'],
    $config['database'],
    $config['port'],
    null,
    $config['ssl'] ? MYSQLI_CLIENT_SSL : 0
);

if (!$connected) {
    die("[ERROR] No se pudo conectar: " . $mysqli->connect_error . "\n");
}

echo "[OK] Conectado.\n\n";

// Verificar qué columnas ya existen para ser idempotente
$existingColumns = [];
$result = $mysqli->query("SHOW COLUMNS FROM tbl_cliente_contexto_sst");
while ($row = $result->fetch_assoc()) {
    $existingColumns[] = $row['Field'];
}

$alterStatements = [
    // A. Horarios y Jornada Laboral
    'horario_lunes_viernes'       => "ADD COLUMN horario_lunes_viernes VARCHAR(100) NULL AFTER turnos_trabajo",
    'horario_sabado'              => "ADD COLUMN horario_sabado VARCHAR(100) NULL AFTER horario_lunes_viernes",
    'trabaja_domingos_festivos'   => "ADD COLUMN trabaja_domingos_festivos ENUM('si','no','ocasional') DEFAULT 'no' AFTER horario_sabado",
    'descripcion_turnos'          => "ADD COLUMN descripcion_turnos TEXT NULL AFTER trabaja_domingos_festivos",

    // B. Seguridad Social
    'eps_principales'             => "ADD COLUMN eps_principales VARCHAR(500) NULL AFTER descripcion_turnos",
    'afp_principales'             => "ADD COLUMN afp_principales VARCHAR(500) NULL AFTER eps_principales",
    'caja_compensacion'           => "ADD COLUMN caja_compensacion VARCHAR(100) NULL AFTER afp_principales",
    'tasa_cotizacion_arl'         => "ADD COLUMN tasa_cotizacion_arl DECIMAL(5,4) NULL AFTER caja_compensacion",
    'manejo_incapacidades'        => "ADD COLUMN manejo_incapacidades TEXT NULL AFTER tasa_cotizacion_arl",

    // C. Datos Operacionales
    'actividades_alto_riesgo'     => "ADD COLUMN actividades_alto_riesgo JSON NULL AFTER manejo_incapacidades",
    'epp_por_cargo'               => "ADD COLUMN epp_por_cargo TEXT NULL AFTER actividades_alto_riesgo",
    'vehiculos_maquinaria'        => "ADD COLUMN vehiculos_maquinaria TEXT NULL AFTER epp_por_cargo",

    // D. Historial SST
    'accidentes_ultimo_anio'      => "ADD COLUMN accidentes_ultimo_anio INT DEFAULT 0 AFTER vehiculos_maquinaria",
    'tasa_ausentismo'             => "ADD COLUMN tasa_ausentismo DECIMAL(5,2) NULL AFTER accidentes_ultimo_anio",
    'enfermedades_laborales_activas' => "ADD COLUMN enfermedades_laborales_activas TEXT NULL AFTER tasa_ausentismo",

    // E. Infraestructura
    'numero_pisos'                => "ADD COLUMN numero_pisos INT DEFAULT 1 AFTER enfermedades_laborales_activas",
    'tiene_ascensor'              => "ADD COLUMN tiene_ascensor TINYINT(1) DEFAULT 0 AFTER numero_pisos",
    'sustancias_quimicas'         => "ADD COLUMN sustancias_quimicas TEXT NULL AFTER tiene_ascensor",
];

$added = 0;
$skipped = 0;

foreach ($alterStatements as $column => $sql) {
    if (in_array($column, $existingColumns)) {
        echo "[SKIP] {$column} ya existe.\n";
        $skipped++;
        continue;
    }

    $fullSql = "ALTER TABLE tbl_cliente_contexto_sst {$sql}";
    if ($mysqli->query($fullSql)) {
        echo "[OK] {$column} agregada.\n";
        $added++;
    } else {
        echo "[ERROR] {$column}: " . $mysqli->error . "\n";
    }
}

$mysqli->close();
echo "\n=== Migración completada: {$added} agregadas, {$skipped} ya existían ===\n";
