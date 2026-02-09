<?php
/**
 * Script para agregar el documento Plan de Objetivos y Metas SG-SST
 * Estándar 2.2.1 - Resolución 0312/2019
 *
 * Uso LOCAL:  php app/SQL/agregar_objetivos_sgsst.php
 * Uso PROD:   php app/SQL/agregar_objetivos_sgsst.php --prod
 */

$isProd = in_array('--prod', $argv ?? []);

if ($isProd) {
    echo "=== MODO PRODUCCIÓN (DigitalOcean) ===\n";
    $config = [
        'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port' => 25060,
        'database' => 'empresas_sst',
        'username' => 'cycloid_userdb',
        'password' => 'AVNS_iDypWizlpMRwHIORJGG'
    ];

    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_SSL_CA => true,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
    ];
} else {
    echo "=== MODO LOCAL (XAMPP) ===\n";
    $config = [
        'host' => 'localhost',
        'database' => 'empresas_sst',
        'username' => 'root',
        'password' => ''
    ];

    $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset=utf8mb4";
    $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
}

try {
    $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
    echo "Conexión exitosa a: {$config['database']}\n\n";
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage() . "\n");
}

// =========================================================================
// 1. AGREGAR DOCUMENTO EN tbl_doc_plantillas
// =========================================================================
echo "1. Verificando documento plan_objetivos_metas en tbl_doc_plantillas...\n";

$stmt = $pdo->prepare("SELECT id_plantilla FROM tbl_doc_plantillas WHERE tipo_documento = ?");
$stmt->execute(['plan_objetivos_metas']);
$existe = $stmt->fetch();

if (!$existe) {
    $pdo->exec("
        INSERT INTO tbl_doc_plantillas (
            id_tipo,
            tipo_documento,
            nombre,
            descripcion,
            codigo_sugerido,
            version,
            activo,
            aplica_7,
            aplica_21,
            aplica_60,
            created_at
        ) VALUES (
            4,
            'plan_objetivos_metas',
            'Plan de Objetivos y Metas del SG-SST',
            'Define los objetivos del Sistema de Gestión de Seguridad y Salud en el Trabajo, con sus metas e indicadores de medición. Estándar 2.2.1 Resolución 0312/2019.',
            'POM-SST',
            '1.0',
            1,
            1,
            1,
            1,
            NOW()
        )
    ");
    echo "   ✓ Documento 'plan_objetivos_metas' creado\n";
} else {
    echo "   → Ya existe (ID: {$existe['id_plantilla']})\n";
}

// =========================================================================
// 2. VERIFICAR COLUMNA categoria EN tbl_indicadores_sst
// =========================================================================
echo "\n2. Verificando columna 'categoria' en tbl_indicadores_sst...\n";

$stmt = $pdo->query("SHOW COLUMNS FROM tbl_indicadores_sst LIKE 'categoria'");
if ($stmt->fetch()) {
    echo "   ✓ Columna 'categoria' existe\n";
} else {
    echo "   → Agregando columna 'categoria'...\n";
    try {
        $pdo->exec("ALTER TABLE tbl_indicadores_sst ADD COLUMN categoria VARCHAR(50) DEFAULT 'general' AFTER tipo_indicador");
        echo "   ✓ Columna 'categoria' agregada\n";
    } catch (Exception $e) {
        echo "   ⚠ Error: " . $e->getMessage() . "\n";
    }
}

// =========================================================================
// 3. VERIFICAR COLUMNA tipo_servicio EN tbl_pta_cliente
// =========================================================================
echo "\n3. Verificando columna 'tipo_servicio' en tbl_pta_cliente...\n";

$stmt = $pdo->query("SHOW COLUMNS FROM tbl_pta_cliente LIKE 'tipo_servicio'");
if ($stmt->fetch()) {
    echo "   ✓ Columna 'tipo_servicio' existe\n";
} else {
    echo "   ⚠ ADVERTENCIA: Columna 'tipo_servicio' NO existe - requerida para el módulo\n";
}

// =========================================================================
// RESUMEN
// =========================================================================
echo "\n" . str_repeat("=", 60) . "\n";
echo "RESUMEN - " . ($isProd ? "PRODUCCIÓN" : "LOCAL") . "\n";
echo str_repeat("=", 60) . "\n";
echo "✓ Documento plan_objetivos_metas configurado en tbl_doc_plantillas\n";
echo "✓ Estructura de tablas verificada\n";
echo "\nNOTA: Los prompts para secciones están definidos en PlanObjetivosMetas.php\n";
echo "      (método getPromptEstatico como fallback)\n";

if (!$isProd) {
    echo "\n⚠ Para ejecutar en PRODUCCIÓN:\n";
    echo "   php app/SQL/agregar_objetivos_sgsst.php --prod\n";
}

echo "\n✅ Script completado exitosamente\n";
