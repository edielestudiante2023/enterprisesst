<?php
/**
 * Perfiles de Cargo - Fase 1: Schema
 *
 * Crea 7 tablas nuevas:
 *   tbl_trabajadores                        (censo central del cliente)
 *   tbl_perfil_cargo                        (plantilla del perfil, 1 por cargo)
 *   tbl_perfil_cargo_competencia            (pivot competencias requeridas)
 *   tbl_perfil_cargo_indicador              (indicadores del cargo)
 *   tbl_perfil_cargo_funcion_sst_cliente    (funciones SST transversales)
 *   tbl_perfil_cargo_funcion_th_cliente     (funciones TH transversales)
 *   tbl_perfil_cargo_acuse                  (firmas individuales por trabajador)
 *
 * Dependencias (deben existir):
 *   tbl_clientes, tbl_cargos_cliente, tbl_competencia_cliente,
 *   tbl_documentos_sst, tbl_doc_versiones_sst
 *
 * Idempotente: CREATE TABLE IF NOT EXISTS, try/catch por sentencia.
 *
 * Uso:
 *   php scripts/perfil_cargo_schema.php             # LOCAL
 *   php scripts/perfil_cargo_schema.php --env=prod  # PRODUCCION (solo si LOCAL OK)
 *
 * Ver: docs/MODULO_PERFILES_CARGO/ARQUITECTURA.md §4
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
$deps = [
    'tbl_clientes'            => null,
    'tbl_cargos_cliente'      => 'scripts/ipevr_gtc45_fase2.php',
    'tbl_competencia_cliente' => 'scripts/diccionario_competencias_schema.php',
    'tbl_documentos_sst'      => null,
    'tbl_doc_versiones_sst'   => null,
];
foreach ($deps as $dep => $hint) {
    $existe = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($dep))->fetchAll();
    if (!$existe) {
        echo "  ERR Falta tabla requerida: {$dep}\n";
        if ($hint) echo "      Ejecuta primero: php {$hint}\n";
        exit(1);
    }
    echo "  OK  {$dep} existe\n";
}
echo "\n";

echo "-- Paso 1: CREATE TABLES perfiles de cargo --\n";

// 1. Censo central de trabajadores
$run('tbl_trabajadores', "
CREATE TABLE IF NOT EXISTS tbl_trabajadores (
    id_trabajador INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_cargo_cliente INT NULL,
    nombres VARCHAR(150) NOT NULL,
    apellidos VARCHAR(150) NOT NULL,
    tipo_documento ENUM('CC','CE','PA','TI','PEP') NOT NULL DEFAULT 'CC',
    cedula VARCHAR(30) NOT NULL,
    email VARCHAR(150) NULL,
    telefono VARCHAR(30) NULL,
    fecha_ingreso DATE NULL,
    fecha_retiro DATE NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_cliente_cedula (id_cliente, cedula),
    KEY idx_cliente (id_cliente),
    KEY idx_cargo (id_cargo_cliente),
    KEY idx_activo (activo),
    CONSTRAINT fk_trab_cliente FOREIGN KEY (id_cliente)
        REFERENCES tbl_clientes(id_cliente) ON DELETE CASCADE,
    CONSTRAINT fk_trab_cargo FOREIGN KEY (id_cargo_cliente)
        REFERENCES tbl_cargos_cliente(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 2. Plantilla del perfil del cargo
$run('tbl_perfil_cargo', "
CREATE TABLE IF NOT EXISTS tbl_perfil_cargo (
    id_perfil_cargo INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_cargo_cliente INT NOT NULL,
    objetivo_cargo TEXT NULL,
    reporta_a VARCHAR(150) NULL,
    colaboradores_a_cargo VARCHAR(150) NULL,
    condiciones_laborales JSON NULL,
    edad_min VARCHAR(20) NULL,
    estado_civil ENUM('soltero','casado','indiferente') NULL,
    genero ENUM('masculino','femenino','indiferente') NULL,
    factores_riesgo JSON NULL,
    formacion_educacion JSON NULL,
    conocimiento_complementario JSON NULL,
    experiencia_laboral JSON NULL,
    validacion_educacion_experiencia TEXT NULL,
    funciones_especificas JSON NULL,
    aprobador_nombre VARCHAR(150) NULL,
    aprobador_cargo VARCHAR(150) NULL,
    aprobador_cedula VARCHAR(30) NULL,
    fecha_aprobacion DATE NULL,
    version_actual INT NOT NULL DEFAULT 1,
    estado ENUM('borrador','generado','aprobado','firmado','obsoleto') NOT NULL DEFAULT 'borrador',
    id_documento_sst INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_cargo_perfil (id_cargo_cliente),
    KEY idx_cliente (id_cliente),
    KEY idx_cargo (id_cargo_cliente),
    KEY idx_estado (estado),
    KEY idx_documento (id_documento_sst),
    CONSTRAINT fk_pc_cliente FOREIGN KEY (id_cliente)
        REFERENCES tbl_clientes(id_cliente) ON DELETE CASCADE,
    CONSTRAINT fk_pc_cargo FOREIGN KEY (id_cargo_cliente)
        REFERENCES tbl_cargos_cliente(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 3. Pivot competencias
$run('tbl_perfil_cargo_competencia', "
CREATE TABLE IF NOT EXISTS tbl_perfil_cargo_competencia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_perfil_cargo INT NOT NULL,
    id_competencia INT NOT NULL,
    nivel_requerido TINYINT NOT NULL,
    observacion TEXT NULL,
    orden INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_perfil_competencia (id_perfil_cargo, id_competencia),
    KEY idx_perfil (id_perfil_cargo),
    KEY idx_competencia (id_competencia),
    CONSTRAINT fk_pcc_perfil FOREIGN KEY (id_perfil_cargo)
        REFERENCES tbl_perfil_cargo(id_perfil_cargo) ON DELETE CASCADE,
    CONSTRAINT fk_pcc_competencia FOREIGN KEY (id_competencia)
        REFERENCES tbl_competencia_cliente(id_competencia) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 4. Indicadores
$run('tbl_perfil_cargo_indicador', "
CREATE TABLE IF NOT EXISTS tbl_perfil_cargo_indicador (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_perfil_cargo INT NOT NULL,
    objetivo_proceso TEXT NULL,
    nombre_indicador VARCHAR(200) NOT NULL,
    formula TEXT NULL,
    periodicidad ENUM('mensual','bimestral','trimestral','semestral','anual') NULL,
    meta VARCHAR(200) NULL,
    ponderacion VARCHAR(20) NULL,
    objetivo_calidad_impacta VARCHAR(200) NULL,
    generado_ia TINYINT(1) NOT NULL DEFAULT 0,
    orden INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_perfil (id_perfil_cargo),
    CONSTRAINT fk_pci_perfil FOREIGN KEY (id_perfil_cargo)
        REFERENCES tbl_perfil_cargo(id_perfil_cargo) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 5. Funciones SST transversales por cliente
$run('tbl_perfil_cargo_funcion_sst_cliente', "
CREATE TABLE IF NOT EXISTS tbl_perfil_cargo_funcion_sst_cliente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    orden INT NOT NULL DEFAULT 0,
    texto TEXT NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_cliente (id_cliente),
    KEY idx_activo (activo),
    KEY idx_cliente_orden (id_cliente, orden),
    CONSTRAINT fk_pcfsst_cliente FOREIGN KEY (id_cliente)
        REFERENCES tbl_clientes(id_cliente) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 6. Funciones TH transversales por cliente
$run('tbl_perfil_cargo_funcion_th_cliente', "
CREATE TABLE IF NOT EXISTS tbl_perfil_cargo_funcion_th_cliente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    orden INT NOT NULL DEFAULT 0,
    texto TEXT NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_cliente (id_cliente),
    KEY idx_activo (activo),
    KEY idx_cliente_orden (id_cliente, orden),
    CONSTRAINT fk_pcfth_cliente FOREIGN KEY (id_cliente)
        REFERENCES tbl_clientes(id_cliente) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 7. Acuses individuales (firma por trabajador)
$run('tbl_perfil_cargo_acuse', "
CREATE TABLE IF NOT EXISTS tbl_perfil_cargo_acuse (
    id_acuse INT AUTO_INCREMENT PRIMARY KEY,
    id_perfil_cargo INT NOT NULL,
    id_version INT NULL,
    id_trabajador INT NOT NULL,
    nombre_trabajador VARCHAR(150) NOT NULL,
    cedula_trabajador VARCHAR(30) NOT NULL,
    cargo_trabajador VARCHAR(150) NULL,
    email_trabajador VARCHAR(150) NULL,
    estado ENUM('pendiente','enviado','firmado','rechazado') NOT NULL DEFAULT 'pendiente',
    token_firma VARCHAR(64) NOT NULL,
    fecha_envio DATETIME NULL,
    fecha_firma DATETIME NULL,
    firma_imagen LONGTEXT NULL,
    ip_firma VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    pdf_acuse VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_perfil_version_trab (id_perfil_cargo, id_version, id_trabajador),
    UNIQUE KEY uq_token (token_firma),
    KEY idx_perfil (id_perfil_cargo),
    KEY idx_trabajador (id_trabajador),
    KEY idx_version (id_version),
    KEY idx_estado (estado),
    CONSTRAINT fk_pca_perfil FOREIGN KEY (id_perfil_cargo)
        REFERENCES tbl_perfil_cargo(id_perfil_cargo) ON DELETE CASCADE,
    CONSTRAINT fk_pca_version FOREIGN KEY (id_version)
        REFERENCES tbl_doc_versiones_sst(id_version) ON DELETE SET NULL,
    CONSTRAINT fk_pca_trabajador FOREIGN KEY (id_trabajador)
        REFERENCES tbl_trabajadores(id_trabajador) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

echo "\n-- Paso 2: VERIFICACION --\n";
$tablas = [
    'tbl_trabajadores',
    'tbl_perfil_cargo',
    'tbl_perfil_cargo_competencia',
    'tbl_perfil_cargo_indicador',
    'tbl_perfil_cargo_funcion_sst_cliente',
    'tbl_perfil_cargo_funcion_th_cliente',
    'tbl_perfil_cargo_acuse',
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
