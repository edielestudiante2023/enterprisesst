<?php
/**
 * Migración: Agregar 11 campos faltantes de AppSheet a tbl_clientes
 *
 * Uso: php app/SQL/agregar_campos_clientes_appsheet.php [local|produccion]
 */

$entorno = $argv[1] ?? 'local';

if ($entorno === 'local') {
    $config = [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'database' => 'empresas_sst',
        'username' => 'root',
        'password' => '',
    ];
} elseif ($entorno === 'produccion') {
    $config = [
        'host'     => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port'     => 25060,
        'database' => 'empresas_sst',
        'username' => 'cycloid_userdb',
        'password' => 'AVNS_iDypWizlpMRwHIORJGG',
    ];
} else {
    echo "Uso: php app/SQL/agregar_campos_clientes_appsheet.php [local|produccion]\n";
    exit(1);
}

echo "=== Migración tbl_clientes - Campos AppSheet ===\n";
echo "Entorno: {$entorno}\n";
echo "Host: {$config['host']}\n\n";

$sslFlag = ($entorno === 'produccion') ? MYSQLI_CLIENT_SSL : 0;

$conn = mysqli_init();
if ($entorno === 'produccion') {
    mysqli_ssl_set($conn, null, null, null, null, null);
}
$connected = mysqli_real_connect(
    $conn,
    $config['host'],
    $config['username'],
    $config['password'],
    $config['database'],
    $config['port'],
    null,
    $sslFlag
);

if (!$connected) {
    echo "ERROR: No se pudo conectar - " . mysqli_connect_error() . "\n";
    exit(1);
}
echo "Conexión exitosa.\n\n";

// Verificar columnas existentes
$result = $conn->query("SHOW COLUMNS FROM tbl_clientes");
$existing = [];
while ($row = $result->fetch_assoc()) {
    $existing[] = $row['Field'];
}

$alteraciones = [
    ['vendedor',                     "VARCHAR(255) NULL DEFAULT NULL COMMENT 'Vendedor que registró el cliente'"],
    ['persona_contacto_operaciones', "VARCHAR(255) NULL DEFAULT NULL COMMENT 'Persona contacto operaciones'"],
    ['persona_contacto_pagos',       "VARCHAR(255) NULL DEFAULT NULL COMMENT 'Persona contacto pagos/tesorería'"],
    ['horarios_y_dias',              "TEXT NULL DEFAULT NULL COMMENT 'Horarios y días de atención'"],
    ['frecuencia_servicio',          "VARCHAR(100) NULL DEFAULT NULL COMMENT 'Mensual, Bimensual, Trimestral, Proyecto'"],
    ['plazo_cartera',                "VARCHAR(100) NULL DEFAULT NULL COMMENT 'Plazo de cartera (ej: PLAZO 8 DÍAS, PAGO INMEDIATO)'"],
    ['fecha_cierre_facturacion',     "INT(11) NULL DEFAULT NULL COMMENT 'Día del mes para cierre de facturación'"],
    ['rut_archivo',                  "VARCHAR(255) NULL DEFAULT NULL COMMENT 'Ruta archivo RUT'"],
    ['camara_comercio_archivo',      "VARCHAR(255) NULL DEFAULT NULL COMMENT 'Ruta archivo Cámara de Comercio'"],
    ['cedula_rep_legal_archivo',     "VARCHAR(255) NULL DEFAULT NULL COMMENT 'Ruta archivo cédula representante legal'"],
    ['oferta_comercial_archivo',     "VARCHAR(255) NULL DEFAULT NULL COMMENT 'Ruta archivo oferta comercial'"],
];

$ejecutadas = 0;
$omitidas = 0;

foreach ($alteraciones as [$campo, $definicion]) {
    if (in_array($campo, $existing)) {
        echo "  OMITIDA: '{$campo}' ya existe.\n";
        $omitidas++;
        continue;
    }

    $sql = "ALTER TABLE tbl_clientes ADD COLUMN `{$campo}` {$definicion}";
    echo "  Ejecutando: ADD COLUMN `{$campo}`... ";

    if ($conn->query($sql)) {
        echo "OK\n";
        $ejecutadas++;
    } else {
        echo "ERROR: " . $conn->error . "\n";
    }
}

echo "\n=== Resumen ===\n";
echo "Columnas agregadas: {$ejecutadas}\n";
echo "Columnas omitidas (ya existían): {$omitidas}\n";

// Verificación final
echo "\n=== Verificación final ===\n";
$result = $conn->query("SHOW COLUMNS FROM tbl_clientes");
echo "Columnas actuales de tbl_clientes:\n";
while ($row = $result->fetch_assoc()) {
    echo "  - {$row['Field']} ({$row['Type']})\n";
}

$conn->close();
echo "\nMigración completada.\n";
