<?php
/**
 * Perfiles de Cargo - Fase 2: Config del motor de documentos
 *
 * 1. ALTER TABLE tbl_doc_tipo_configuracion    → agrega 'perfil_cargo' al ENUM flujo
 * 2. ALTER TABLE tbl_doc_firmantes_config      → agrega 'aprobador_perfil' al ENUM firmante_tipo
 * 3. INSERT tipo_documento='perfil_cargo' en tbl_doc_tipo_configuracion
 * 4. INSERT aprobador_perfil en tbl_doc_firmantes_config
 *
 * Operaciones aditivas puras. No destructivas. Idempotente.
 *
 * Uso:
 *   php scripts/perfil_cargo_config.php             # LOCAL
 *   php scripts/perfil_cargo_config.php --env=prod  # PRODUCCION (solo si LOCAL OK)
 *
 * Ver: docs/MODULO_PERFILES_CARGO/ARQUITECTURA.md §7
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

echo "-- Paso 1: ALTER ENUMs (aditivo) --\n";

$run('flujo ENUM += perfil_cargo', "
ALTER TABLE tbl_doc_tipo_configuracion
MODIFY COLUMN flujo
ENUM('secciones_ia','formulario','carga_archivo','mixto','programa_con_pta','perfil_cargo')
DEFAULT 'secciones_ia'");

$run('firmante_tipo ENUM += aprobador_perfil', "
ALTER TABLE tbl_doc_firmantes_config
MODIFY COLUMN firmante_tipo
ENUM('representante_legal','responsable_sst','consultor_sst','delegado_sst','vigia_sst','copasst','trabajador','aprobador_perfil')
NOT NULL");

echo "\n-- Paso 2: INSERT tipo_documento --\n";

$run('tbl_doc_tipo_configuracion / perfil_cargo', "
INSERT INTO tbl_doc_tipo_configuracion
    (tipo_documento, nombre, descripcion, estandar, flujo, categoria, icono, orden, activo)
VALUES
    ('perfil_cargo',
     'Perfil del Cargo',
     'Perfil del cargo con objetivo, requisitos, riesgos, formacion, experiencia, competencias, funciones (especificas del cargo + SST + Talento Humano transversales del cliente) e indicadores. Documento con aprobacion y acuse individual firmado por cada trabajador asignado al cargo.',
     NULL,
     'perfil_cargo',
     'talento_humano',
     'bi-person-badge',
     99,
     1)
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    descripcion = VALUES(descripcion),
    flujo = VALUES(flujo),
    categoria = VALUES(categoria),
    icono = VALUES(icono),
    updated_at = CURRENT_TIMESTAMP");

echo "\n-- Paso 3: INSERT firmante aprobador_perfil --\n";

$run('tbl_doc_firmantes_config / aprobador_perfil', "
INSERT INTO tbl_doc_firmantes_config
    (id_tipo_config, firmante_tipo, rol_display, columna_encabezado, orden, es_obligatorio, mostrar_licencia, mostrar_cedula, activo)
SELECT
    tc.id_tipo_config,
    'aprobador_perfil',
    'Aprobo',
    'Aprobado por',
    1,
    1,
    0,
    1,
    1
FROM tbl_doc_tipo_configuracion tc
WHERE tc.tipo_documento = 'perfil_cargo'
  AND NOT EXISTS (
      SELECT 1 FROM tbl_doc_firmantes_config f
      WHERE f.id_tipo_config = tc.id_tipo_config
        AND f.firmante_tipo = 'aprobador_perfil'
  )");

echo "\n-- Paso 4: VERIFICACION --\n";

$stmt = $pdo->query("
    SELECT id_tipo_config, tipo_documento, nombre, flujo, categoria, activo
    FROM tbl_doc_tipo_configuracion
    WHERE tipo_documento = 'perfil_cargo'
");
$tipo = $stmt->fetch(PDO::FETCH_ASSOC);

$todoOk = true;
if ($tipo) {
    echo "  OK  tipo_documento registrado:\n";
    echo "      id_tipo_config = {$tipo['id_tipo_config']}\n";
    echo "      nombre         = {$tipo['nombre']}\n";
    echo "      flujo          = {$tipo['flujo']}\n";
    echo "      categoria      = {$tipo['categoria']}\n";
    echo "      activo         = {$tipo['activo']}\n";

    $stmt = $pdo->query("
        SELECT firmante_tipo, rol_display, orden, es_obligatorio
        FROM tbl_doc_firmantes_config
        WHERE id_tipo_config = {$tipo['id_tipo_config']}
        ORDER BY orden
    ");
    $firmantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "  OK  firmantes configurados: " . count($firmantes) . "\n";
    foreach ($firmantes as $f) {
        echo "      [{$f['orden']}] {$f['firmante_tipo']} → {$f['rol_display']} (obligatorio={$f['es_obligatorio']})\n";
    }
    if (count($firmantes) !== 1) {
        echo "  WARN  Se esperaba exactamente 1 firmante (aprobador_perfil)\n";
        $todoOk = false;
    }
} else {
    echo "  ERR tipo_documento NO registrado\n";
    $todoOk = false;
}

echo "\n" . ($todoOk ? "FASE 2 COMPLETA\n" : "FASE 2 CON ERRORES\n");
exit($todoOk ? 0 : 1);
