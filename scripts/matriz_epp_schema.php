<?php
/**
 * Matriz EPP - Fase 1: Schema
 *
 * Crea 3 tablas:
 *   tbl_epp_categoria  (GLOBAL, editable)
 *   tbl_epp_maestro    (GLOBAL, catalogo universo de EPP/dotacion)
 *   tbl_epp_cliente    (snapshot por cliente, editable inline)
 *
 * Idempotente: CREATE TABLE IF NOT EXISTS.
 *
 * Uso:
 *   php scripts/matriz_epp_schema.php             # LOCAL
 *   php scripts/matriz_epp_schema.php --env=prod  # PRODUCCION
 *
 * Ver: docs/MODULO_MATRIZ_EPP/ARQUITECTURA.md §3
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

echo "-- Precheck: dependencias --\n";
foreach (['tbl_clientes'] as $dep) {
    $existe = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($dep))->fetchAll();
    if (!$existe) {
        echo "  ERR Falta tabla requerida: {$dep}\n";
        exit(1);
    }
    echo "  OK  {$dep} existe\n";
}
echo "\n";

echo "-- Paso 1: CREATE TABLES matriz EPP --\n";

$run('tbl_epp_categoria', "
CREATE TABLE IF NOT EXISTS tbl_epp_categoria (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    tipo VARCHAR(20) NOT NULL,
    orden INT NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_tipo (tipo),
    KEY idx_activo (activo),
    KEY idx_orden (orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$run('tbl_epp_maestro', "
CREATE TABLE IF NOT EXISTS tbl_epp_maestro (
    id_epp INT AUTO_INCREMENT PRIMARY KEY,
    id_categoria INT NOT NULL,
    elemento VARCHAR(200) NOT NULL,
    norma TEXT NULL,
    mantenimiento TEXT NULL,
    frecuencia_cambio VARCHAR(200) NULL,
    motivos_cambio TEXT NULL,
    momentos_uso TEXT NULL,
    foto_path VARCHAR(255) NULL,
    ia_generado TINYINT(1) NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_categoria (id_categoria),
    KEY idx_activo (activo),
    KEY idx_elemento (elemento),
    CONSTRAINT fk_epp_maestro_categoria FOREIGN KEY (id_categoria)
        REFERENCES tbl_epp_categoria(id_categoria) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$run('tbl_epp_cliente', "
CREATE TABLE IF NOT EXISTS tbl_epp_cliente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_epp INT NOT NULL,
    orden INT NOT NULL DEFAULT 0,
    elemento VARCHAR(200) NOT NULL,
    norma TEXT NULL,
    mantenimiento TEXT NULL,
    frecuencia_cambio VARCHAR(200) NULL,
    motivos_cambio TEXT NULL,
    momentos_uso TEXT NULL,
    observacion_cliente TEXT NULL,
    sincronizado_maestro TINYINT(1) NOT NULL DEFAULT 1,
    fecha_ultima_sync TIMESTAMP NULL DEFAULT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_cliente_epp (id_cliente, id_epp),
    KEY idx_cliente (id_cliente),
    KEY idx_epp (id_epp),
    KEY idx_sincronizado (sincronizado_maestro),
    CONSTRAINT fk_epp_cliente_cliente FOREIGN KEY (id_cliente)
        REFERENCES tbl_clientes(id_cliente) ON DELETE CASCADE,
    CONSTRAINT fk_epp_cliente_maestro FOREIGN KEY (id_epp)
        REFERENCES tbl_epp_maestro(id_epp) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

echo "\n-- Paso 2: VERIFICACION --\n";
$tablas = ['tbl_epp_categoria', 'tbl_epp_maestro', 'tbl_epp_cliente'];
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

echo "\n" . ($todoOk ? "FASE 1 COMPLETA\n" : "FASE 1 CON ERRORES\n");
exit($todoOk ? 0 : 1);
