<?php
/**
 * IPEVR GTC 45 - Fase 2: Maestros por cliente
 *
 * Crea 4 tablas vacias de maestros reutilizables por cliente:
 *   tbl_procesos_cliente, tbl_cargos_cliente, tbl_tareas_cliente, tbl_zonas_cliente
 *
 * Son reutilizables en otros modulos (PTA, indicadores) ademas de IPEVR.
 * Idempotente: CREATE TABLE IF NOT EXISTS.
 *
 * Uso:
 *   php scripts/ipevr_gtc45_fase2.php             # LOCAL
 *   php scripts/ipevr_gtc45_fase2.php --env=prod  # PRODUCCION
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

$run = function(string $label, string $sql) use ($pdo) {
    try {
        $pdo->exec($sql);
        echo "  OK  {$label}\n";
    } catch (Throwable $e) {
        echo "  ERR {$label}: " . $e->getMessage() . "\n";
    }
};

echo "-- Paso 1: CREATE TABLES maestros cliente --\n";

$run('tbl_procesos_cliente', "
CREATE TABLE IF NOT EXISTS tbl_procesos_cliente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    nombre_proceso VARCHAR(150) NOT NULL,
    tipo ENUM('estrategico','misional','apoyo','evaluacion') NULL,
    descripcion TEXT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_cliente (id_cliente),
    KEY idx_activo (activo),
    CONSTRAINT fk_procesos_cliente FOREIGN KEY (id_cliente)
        REFERENCES tbl_clientes(id_cliente) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$run('tbl_cargos_cliente', "
CREATE TABLE IF NOT EXISTS tbl_cargos_cliente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_proceso INT NULL,
    nombre_cargo VARCHAR(150) NOT NULL,
    num_ocupantes SMALLINT NOT NULL DEFAULT 0,
    descripcion TEXT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_cliente (id_cliente),
    KEY idx_proceso (id_proceso),
    KEY idx_activo (activo),
    CONSTRAINT fk_cargos_cliente FOREIGN KEY (id_cliente)
        REFERENCES tbl_clientes(id_cliente) ON DELETE CASCADE,
    CONSTRAINT fk_cargos_proceso FOREIGN KEY (id_proceso)
        REFERENCES tbl_procesos_cliente(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$run('tbl_tareas_cliente', "
CREATE TABLE IF NOT EXISTS tbl_tareas_cliente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_proceso INT NULL,
    nombre_tarea VARCHAR(200) NOT NULL,
    rutinaria TINYINT(1) NOT NULL DEFAULT 1,
    descripcion TEXT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_cliente (id_cliente),
    KEY idx_proceso (id_proceso),
    KEY idx_activo (activo),
    CONSTRAINT fk_tareas_cliente FOREIGN KEY (id_cliente)
        REFERENCES tbl_clientes(id_cliente) ON DELETE CASCADE,
    CONSTRAINT fk_tareas_proceso FOREIGN KEY (id_proceso)
        REFERENCES tbl_procesos_cliente(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$run('tbl_zonas_cliente', "
CREATE TABLE IF NOT EXISTS tbl_zonas_cliente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_sede INT NULL,
    nombre_zona VARCHAR(200) NOT NULL,
    descripcion TEXT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_cliente (id_cliente),
    KEY idx_sede (id_sede),
    KEY idx_activo (activo),
    CONSTRAINT fk_zonas_cliente FOREIGN KEY (id_cliente)
        REFERENCES tbl_clientes(id_cliente) ON DELETE CASCADE,
    CONSTRAINT fk_zonas_sede FOREIGN KEY (id_sede)
        REFERENCES tbl_cliente_sedes(id_sede) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

echo "\n-- Paso 2: VERIFICACION --\n";
$tablas = ['tbl_procesos_cliente','tbl_cargos_cliente','tbl_tareas_cliente','tbl_zonas_cliente'];
$todoOk = true;
foreach ($tablas as $t) {
    $existe = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($t))->fetchAll();
    if ($existe) {
        $c = (int)$pdo->query("SELECT COUNT(*) FROM {$t}")->fetchColumn();
        echo "  OK  {$t} (filas: {$c})\n";
    } else {
        echo "  ERR {$t} NO existe\n";
        $todoOk = false;
    }
}

echo "\n" . ($todoOk ? "FASE 2 COMPLETADA OK" : "FASE 2 CON ERRORES") . "\n";
exit($todoOk ? 0 : 1);
