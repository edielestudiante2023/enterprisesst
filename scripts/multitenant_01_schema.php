<?php
/**
 * Multi-tenant Fase 1: Schema + seed + backfill
 *
 * Operaciones (idempotentes):
 *  1) CREATE TABLE tbl_empresa_consultora
 *  2) ALTER TABLE tbl_consultor ADD id_empresa_consultora
 *  3) ALTER TABLE tbl_usuarios MODIFY tipo_usuario ENUM(..., 'superadmin')
 *  4) INSERT empresa #1 = Cycloid Talent SAS (si no existe)
 *  5) UPDATE tbl_consultor SET id_empresa_consultora = 1 WHERE IS NULL
 *  6) ADD INDEX en tbl_consultor(id_empresa_consultora)
 *
 * Uso:
 *   php scripts/multitenant_01_schema.php             # LOCAL
 *   php scripts/multitenant_01_schema.php --env=prod  # PRODUCCION
 *
 * Ver: docs/MULTI_TENANT_EMPRESA_CONSULTORA/01_ARQUITECTURA.md
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
        return true;
    } catch (Throwable $e) {
        echo "  ERR {$label}: " . $e->getMessage() . "\n";
        return false;
    }
};

// ---------- Prechecks ----------
echo "-- Precheck: dependencias --\n";
foreach (['tbl_consultor', 'tbl_usuarios', 'tbl_clientes'] as $dep) {
    $existe = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($dep))->fetchAll();
    if (!$existe) {
        echo "  ERR Falta tabla requerida: {$dep}\n";
        exit(1);
    }
    echo "  OK  {$dep} existe\n";
}

$countConsultoresAntes = (int)$pdo->query("SELECT COUNT(*) FROM tbl_consultor")->fetchColumn();
$countUsuariosAntes    = (int)$pdo->query("SELECT COUNT(*) FROM tbl_usuarios")->fetchColumn();
echo "  Info: tbl_consultor filas={$countConsultoresAntes}, tbl_usuarios filas={$countUsuariosAntes}\n\n";

// ---------- Paso 1: CREATE TABLE tbl_empresa_consultora ----------
echo "-- Paso 1: CREATE TABLE tbl_empresa_consultora --\n";
$run('tbl_empresa_consultora', "
CREATE TABLE IF NOT EXISTS tbl_empresa_consultora (
    id_empresa_consultora INT AUTO_INCREMENT PRIMARY KEY,
    razon_social VARCHAR(200) NOT NULL,
    nit VARCHAR(50) NULL,
    direccion VARCHAR(255) NULL,
    telefono VARCHAR(50) NULL,
    correo VARCHAR(150) NULL,
    logo VARCHAR(255) NULL,
    color_primario VARCHAR(20) NULL,
    estado ENUM('activo','inactivo','suspendido') NOT NULL DEFAULT 'activo',
    fecha_inicio_contrato DATE NULL,
    plan VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// ---------- Paso 2: ALTER tbl_consultor ADD id_empresa_consultora ----------
echo "\n-- Paso 2: ALTER tbl_consultor ADD id_empresa_consultora --\n";
$colExiste = $pdo->query("
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tbl_consultor'
      AND COLUMN_NAME = 'id_empresa_consultora'
")->fetchColumn();

if ($colExiste) {
    echo "  OK  columna id_empresa_consultora ya existe (skip)\n";
} else {
    $run('ADD COLUMN id_empresa_consultora', "
        ALTER TABLE tbl_consultor
        ADD COLUMN id_empresa_consultora INT NULL AFTER rol
    ");
}

// ---------- Paso 3: ALTER tbl_usuarios ENUM superadmin ----------
echo "\n-- Paso 3: ALTER tbl_usuarios tipo_usuario ENUM (+ superadmin) --\n";
$colDef = $pdo->query("
    SELECT COLUMN_TYPE
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tbl_usuarios'
      AND COLUMN_NAME = 'tipo_usuario'
")->fetchColumn();

echo "  Info: definicion actual = {$colDef}\n";

if ($colDef && stripos($colDef, 'superadmin') !== false) {
    echo "  OK  tipo_usuario ya incluye 'superadmin' (skip)\n";
} else {
    // Se preserva el orden admin, consultant, client, miembro y se agrega superadmin
    $run('MODIFY tipo_usuario ENUM', "
        ALTER TABLE tbl_usuarios
        MODIFY COLUMN tipo_usuario ENUM('admin','consultant','client','miembro','superadmin') NOT NULL
    ");
}

// ---------- Paso 4: INSERT empresa #1 Cycloid Talent SAS ----------
echo "\n-- Paso 4: Seed empresa #1 = Cycloid Talent SAS --\n";
$existeCycloid = (int)$pdo->query("SELECT COUNT(*) FROM tbl_empresa_consultora WHERE id_empresa_consultora = 1")->fetchColumn();
if ($existeCycloid > 0) {
    echo "  OK  empresa id=1 ya existe (skip)\n";
} else {
    $run('INSERT Cycloid Talent SAS', "
        INSERT INTO tbl_empresa_consultora
            (id_empresa_consultora, razon_social, estado, plan)
        VALUES
            (1, 'Cycloid Talent SAS', 'activo', 'owner')
    ");
}

// ---------- Paso 5: Backfill consultores existentes ----------
echo "\n-- Paso 5: Backfill tbl_consultor.id_empresa_consultora = 1 --\n";
$sinEmpresa = (int)$pdo->query("SELECT COUNT(*) FROM tbl_consultor WHERE id_empresa_consultora IS NULL")->fetchColumn();
echo "  Info: consultores sin empresa = {$sinEmpresa}\n";
if ($sinEmpresa > 0) {
    $run('UPDATE consultores sin empresa', "
        UPDATE tbl_consultor
        SET id_empresa_consultora = 1
        WHERE id_empresa_consultora IS NULL
    ");
}

// ---------- Paso 6: INDEX ----------
echo "\n-- Paso 6: INDEX tbl_consultor(id_empresa_consultora) --\n";
$idxExiste = $pdo->query("
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tbl_consultor'
      AND INDEX_NAME = 'idx_consultor_empresa'
")->fetchColumn();
if ($idxExiste) {
    echo "  OK  idx_consultor_empresa ya existe (skip)\n";
} else {
    $run('ADD INDEX idx_consultor_empresa', "
        ALTER TABLE tbl_consultor
        ADD INDEX idx_consultor_empresa (id_empresa_consultora)
    ");
}

// ---------- Verificacion ----------
echo "\n-- VERIFICACION --\n";
$todoOk = true;

// 1. Tabla empresa_consultora existe y tiene >=1 fila
$c1 = (int)$pdo->query("SELECT COUNT(*) FROM tbl_empresa_consultora")->fetchColumn();
if ($c1 >= 1) { echo "  OK  tbl_empresa_consultora (filas: {$c1})\n"; }
else { echo "  ERR tbl_empresa_consultora vacia\n"; $todoOk = false; }

// 2. Columna en consultor
$col = (int)$pdo->query("
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tbl_consultor'
      AND COLUMN_NAME = 'id_empresa_consultora'
")->fetchColumn();
if ($col) { echo "  OK  tbl_consultor.id_empresa_consultora existe\n"; }
else { echo "  ERR columna ausente\n"; $todoOk = false; }

// 3. Backfill completo
$sinEmpresaPost = (int)$pdo->query("SELECT COUNT(*) FROM tbl_consultor WHERE id_empresa_consultora IS NULL")->fetchColumn();
if ($sinEmpresaPost === 0) { echo "  OK  backfill completo (0 consultores sin empresa)\n"; }
else { echo "  ERR quedan {$sinEmpresaPost} consultores sin empresa\n"; $todoOk = false; }

// 4. ENUM superadmin
$enumDef = $pdo->query("
    SELECT COLUMN_TYPE
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tbl_usuarios'
      AND COLUMN_NAME = 'tipo_usuario'
")->fetchColumn();
if (stripos($enumDef, 'superadmin') !== false) { echo "  OK  tipo_usuario incluye 'superadmin'\n"; }
else { echo "  ERR tipo_usuario sin 'superadmin': {$enumDef}\n"; $todoOk = false; }

// 5. Consultores en empresa 1
$enEmpresa1 = (int)$pdo->query("SELECT COUNT(*) FROM tbl_consultor WHERE id_empresa_consultora = 1")->fetchColumn();
echo "  Info: consultores en empresa 1 = {$enEmpresa1} / total {$countConsultoresAntes}\n";

echo "\n" . ($todoOk ? "FASE 1 COMPLETA\n" : "FASE 1 CON ERRORES\n");
exit($todoOk ? 0 : 1);
