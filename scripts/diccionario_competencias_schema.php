<?php
/**
 * Diccionario de Competencias - Fase 1: Schema
 *
 * Crea 4 tablas nuevas (todas scoped por id_cliente):
 *   tbl_competencia_escala_cliente
 *   tbl_competencia_cliente
 *   tbl_competencia_nivel_cliente
 *   tbl_cliente_competencia_cargo
 *
 * Reutiliza tbl_cargos_cliente (creada en scripts/ipevr_gtc45_fase2.php).
 * Idempotente: CREATE TABLE IF NOT EXISTS.
 *
 * Uso:
 *   php scripts/diccionario_competencias_schema.php             # LOCAL
 *   php scripts/diccionario_competencias_schema.php --env=prod  # PRODUCCION
 *
 * Ver: docs/MODULO_DICCIONARIO_COMPETENCIAS/ARQUITECTURA.md §3
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
foreach (['tbl_clientes', 'tbl_cargos_cliente'] as $dep) {
    $existe = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($dep))->fetchAll();
    if (!$existe) {
        echo "  ERR Falta tabla requerida: {$dep}\n";
        if ($dep === 'tbl_cargos_cliente') {
            echo "      Ejecuta primero: php scripts/ipevr_gtc45_fase2.php\n";
        }
        exit(1);
    }
    echo "  OK  {$dep} existe\n";
}
echo "\n";

echo "-- Paso 1: CREATE TABLES diccionario competencias --\n";

$run('tbl_competencia_escala_cliente', "
CREATE TABLE IF NOT EXISTS tbl_competencia_escala_cliente (
    id_escala INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    nivel TINYINT NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    etiqueta VARCHAR(100) NULL,
    descripcion TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_cliente_nivel (id_cliente, nivel),
    KEY idx_cliente (id_cliente),
    CONSTRAINT fk_comp_escala_cliente FOREIGN KEY (id_cliente)
        REFERENCES tbl_clientes(id_cliente) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$run('tbl_competencia_cliente', "
CREATE TABLE IF NOT EXISTS tbl_competencia_cliente (
    id_competencia INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    numero INT NOT NULL DEFAULT 0,
    codigo VARCHAR(10) NULL,
    nombre VARCHAR(150) NOT NULL,
    definicion TEXT NOT NULL,
    pregunta_clave TEXT NULL,
    familia VARCHAR(60) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_cliente (id_cliente),
    KEY idx_activo (activo),
    KEY idx_familia (familia),
    KEY idx_cliente_numero (id_cliente, numero),
    CONSTRAINT fk_comp_cliente FOREIGN KEY (id_cliente)
        REFERENCES tbl_clientes(id_cliente) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$run('tbl_competencia_nivel_cliente', "
CREATE TABLE IF NOT EXISTS tbl_competencia_nivel_cliente (
    id_competencia_nivel INT AUTO_INCREMENT PRIMARY KEY,
    id_competencia INT NOT NULL,
    nivel_numero TINYINT NOT NULL,
    titulo_corto VARCHAR(255) NULL,
    descripcion_conducta TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_competencia_nivel (id_competencia, nivel_numero),
    KEY idx_competencia (id_competencia),
    CONSTRAINT fk_comp_nivel_competencia FOREIGN KEY (id_competencia)
        REFERENCES tbl_competencia_cliente(id_competencia) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$run('tbl_cliente_competencia_cargo', "
CREATE TABLE IF NOT EXISTS tbl_cliente_competencia_cargo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_cargo_cliente INT NOT NULL,
    id_competencia INT NOT NULL,
    nivel_requerido TINYINT NOT NULL,
    observacion TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_cargo_competencia (id_cargo_cliente, id_competencia),
    KEY idx_cliente (id_cliente),
    KEY idx_cargo (id_cargo_cliente),
    KEY idx_competencia (id_competencia),
    CONSTRAINT fk_ccc_cliente FOREIGN KEY (id_cliente)
        REFERENCES tbl_clientes(id_cliente) ON DELETE CASCADE,
    CONSTRAINT fk_ccc_cargo FOREIGN KEY (id_cargo_cliente)
        REFERENCES tbl_cargos_cliente(id) ON DELETE CASCADE,
    CONSTRAINT fk_ccc_competencia FOREIGN KEY (id_competencia)
        REFERENCES tbl_competencia_cliente(id_competencia) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

echo "\n-- Paso 2: VERIFICACION --\n";
$tablas = [
    'tbl_competencia_escala_cliente',
    'tbl_competencia_cliente',
    'tbl_competencia_nivel_cliente',
    'tbl_cliente_competencia_cargo',
];
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
