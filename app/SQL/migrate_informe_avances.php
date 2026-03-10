<?php
/**
 * Migración: Crear tabla tbl_informe_avances
 * Módulo Informe de Avances (ex "Informe Cierre de Mes" de AppSheet)
 * Formato FT-SST-205
 *
 * Uso: php app/SQL/migrate_informe_avances.php [local|production]
 * Producción: DB_PROD_PASS=xxx php app/SQL/migrate_informe_avances.php production
 */

$env = $argv[1] ?? 'local';

$configs = [
    'local' => [
        'host' => 'localhost',
        'user' => 'root',
        'pass' => '',
        'db'   => 'propiedad_horizontal',
        'ssl'  => false,
    ],
    'production' => [
        'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port' => 25060,
        'user' => 'cycloid_userdb',
        'pass' => getenv('DB_PROD_PASS') ?: '',
        'db'   => 'propiedad_horizontal',
        'ssl'  => true,
    ],
];

if (!isset($configs[$env])) {
    echo "Uso: php migrate_informe_avances.php [local|production]\n";
    exit(1);
}

$cfg = $configs[$env];
echo "=== Migración tbl_informe_avances - Entorno: {$env} ===\n\n";

if ($env === 'production' && empty($cfg['pass'])) {
    echo "ERROR: Variable DB_PROD_PASS no definida.\n";
    echo "Uso: DB_PROD_PASS=xxx php app/SQL/migrate_informe_avances.php production\n";
    exit(1);
}

$conn = new mysqli();

if ($cfg['ssl'] ?? false) {
    $conn->ssl_set(null, null, null, null, null);
    $conn->real_connect($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['db'], $cfg['port'] ?? 3306, null, MYSQLI_CLIENT_SSL);
} else {
    $conn->real_connect($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['db'], $cfg['port'] ?? 3306);
}

if ($conn->connect_error) {
    echo "ERROR de conexión: " . $conn->connect_error . "\n";
    exit(1);
}

echo "Conectado a {$cfg['db']}@{$cfg['host']}\n\n";

$ok = 0;
$errors = 0;

// ─── 1. Crear tabla tbl_informe_avances ───
$result = $conn->query("SHOW TABLES LIKE 'tbl_informe_avances'");
if ($result && $result->num_rows > 0) {
    echo "[SKIP] La tabla tbl_informe_avances ya existe\n";
    $ok++;
} else {
    $sql = "CREATE TABLE tbl_informe_avances (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_cliente INT NOT NULL,
        id_consultor INT NOT NULL,

        -- Periodo
        fecha_desde DATE NOT NULL,
        fecha_hasta DATE NOT NULL,
        anio SMALLINT NOT NULL,

        -- Metricas (auto-calculadas al crear, almacenadas como snapshot)
        puntaje_anterior DECIMAL(5,2) NULL,
        puntaje_actual DECIMAL(5,2) NULL,
        diferencia_neta DECIMAL(5,2) NULL,
        estado_avance VARCHAR(60) NOT NULL DEFAULT 'ESTABLE',

        -- Indicadores (% auto-calculados)
        indicador_plan_trabajo DECIMAL(5,2) NULL,
        indicador_capacitacion DECIMAL(5,2) NULL,

        -- Imagenes opcionales (screenshots de dashboards)
        img_cumplimiento_estandares VARCHAR(500) NULL,
        img_indicador_plan_trabajo VARCHAR(500) NULL,
        img_indicador_capacitacion VARCHAR(500) NULL,

        -- Contenido
        resumen_avance LONGTEXT NULL,
        observaciones TEXT NULL,
        actividades_abiertas TEXT NULL,
        actividades_cerradas_periodo TEXT NULL,

        -- Enlaces
        enlace_dashboard VARCHAR(500) NULL,
        acta_visita_url VARCHAR(500) NULL,

        -- Soportes (hasta 4 pares texto+imagen)
        soporte_1_texto TEXT NULL,
        soporte_1_imagen VARCHAR(500) NULL,
        soporte_2_texto TEXT NULL,
        soporte_2_imagen VARCHAR(500) NULL,
        soporte_3_texto TEXT NULL,
        soporte_3_imagen VARCHAR(500) NULL,
        soporte_4_texto TEXT NULL,
        soporte_4_imagen VARCHAR(500) NULL,

        -- Sistema
        ruta_pdf VARCHAR(500) NULL,
        estado ENUM('borrador','completo') NOT NULL DEFAULT 'borrador',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        CONSTRAINT fk_infavance_cliente FOREIGN KEY (id_cliente)
            REFERENCES tbl_clientes(id_cliente) ON DELETE RESTRICT,
        CONSTRAINT fk_infavance_consultor FOREIGN KEY (id_consultor)
            REFERENCES tbl_consultor(id_consultor) ON DELETE RESTRICT,
        INDEX idx_infavance_cliente (id_cliente),
        INDEX idx_infavance_consultor (id_consultor),
        INDEX idx_infavance_estado (estado),
        INDEX idx_infavance_fecha (fecha_hasta)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    COMMENT='Informe de Avances (ex Cierre de Mes) - Formato FT-SST-205'";

    if ($conn->query($sql)) {
        echo "[OK] Tabla tbl_informe_avances creada exitosamente\n";
        $ok++;
    } else {
        echo "[ERROR] No se pudo crear la tabla: " . $conn->error . "\n";
        $errors++;
    }
}

// ─── 2. Insertar detail_report (id_detailreport = 37) ───
$result = $conn->query("SELECT id_detailreport FROM detail_report WHERE id_detailreport = 37");
if ($result && $result->num_rows > 0) {
    echo "[SKIP] detail_report id=37 ya existe\n";
    $ok++;
} else {
    $sql = "INSERT INTO detail_report (id_detailreport, detail_report)
            VALUES (37, 'INFORME DE AVANCES')";
    if ($conn->query($sql)) {
        echo "[OK] detail_report id=37 'INFORME DE AVANCES' insertado\n";
        $ok++;
    } else {
        echo "[ERROR] No se pudo insertar detail_report: " . $conn->error . "\n";
        $errors++;
    }
}

// ─── 3. Verificar estructura ───
$result = $conn->query("DESCRIBE tbl_informe_avances");
if ($result) {
    echo "\n--- Estructura de la tabla ---\n";
    while ($row = $result->fetch_assoc()) {
        echo "  {$row['Field']} ({$row['Type']})\n";
    }
} else {
    echo "[ERROR] No se pudo verificar la estructura: " . $conn->error . "\n";
    $errors++;
}

echo "\n=== Resultado: {$ok} OK, {$errors} errores ===\n";
$conn->close();

exit($errors > 0 ? 1 : 0);
