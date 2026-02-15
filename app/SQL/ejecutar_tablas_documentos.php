#!/usr/bin/env php
<?php
/**
 * Script CLI para crear/verificar tablas de documentos SST
 * Ejecuta en LOCAL primero, luego en PRODUCCIÓN
 *
 * Uso: php ejecutar_tablas_documentos.php
 */

define('FCPATH', __DIR__ . '/../../public/');

// Cargar framework
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

// Bootstrap de CodeIgniter
$pathsConfig = require dirname(__DIR__, 2) . '/app/Config/Paths.php';
$paths = new \Config\Paths();
$bootstrap = rtrim($paths->systemDirectory, '\\/ ') . '/bootstrap.php';
require realpath($bootstrap) ?: $bootstrap;

$app = \Config\Services::codeigniter();
$app->initialize();

// ==================== CONFIGURACIÓN ====================

$entornos = [
    'local' => [
        'nombre' => 'LOCAL',
        'host' => 'localhost',
        'username' => 'root',
        'password' => '',
        'database' => 'empresas_sst',
        'port' => 3306,
    ],
    'produccion' => [
        'nombre' => 'PRODUCCIÓN',
        'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'username' => 'cycloid_userdb',
        'password' => 'AVNS_iDypWizlpMRwHIORJGG',
        'database' => 'empresas_sst',
        'port' => 25060,
        'ssl' => true,
    ],
];

// ==================== SQL STATEMENTS ====================

$sqlStatements = [
    // 1. Crear tabla tbl_documentos_sst
    'tbl_documentos_sst' => "
CREATE TABLE IF NOT EXISTS `tbl_documentos_sst` (
    `id_documento` INT(11) NOT NULL AUTO_INCREMENT,
    `id_cliente` INT(11) NOT NULL,
    `tipo_documento` VARCHAR(100) NOT NULL COMMENT 'programa_capacitacion, politica_sst, objetivos_sst, etc.',
    `titulo` VARCHAR(255) NOT NULL,
    `anio` INT(4) NOT NULL,
    `contenido` LONGTEXT NULL COMMENT 'Contenido JSON del documento',
    `version` INT(11) DEFAULT 1,
    `estado` ENUM('borrador', 'generado', 'aprobado', 'obsoleto') DEFAULT 'borrador',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `created_by` INT(11) NULL,
    `updated_by` INT(11) NULL,
    PRIMARY KEY (`id_documento`),
    KEY `idx_cliente` (`id_cliente`),
    KEY `idx_tipo` (`tipo_documento`),
    KEY `idx_anio` (`anio`),
    UNIQUE KEY `uk_cliente_tipo_anio` (`id_cliente`, `tipo_documento`, `anio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    // 2. Agregar columnas adicionales a tbl_documentos_sst
    'alter_documentos_sst' => "
ALTER TABLE `tbl_documentos_sst`
ADD COLUMN IF NOT EXISTS `fecha_aprobacion` DATETIME NULL AFTER `estado`,
ADD COLUMN IF NOT EXISTS `aprobado_por` INT(11) NULL AFTER `fecha_aprobacion`,
ADD COLUMN IF NOT EXISTS `motivo_version` VARCHAR(255) NULL AFTER `aprobado_por`,
ADD COLUMN IF NOT EXISTS `tipo_cambio_pendiente` ENUM('mayor', 'menor') NULL AFTER `motivo_version`;
    ",

    // 3. Crear tabla tbl_doc_versiones_sst
    'tbl_doc_versiones_sst' => "
CREATE TABLE IF NOT EXISTS `tbl_doc_versiones_sst` (
    `id_version` INT(11) NOT NULL AUTO_INCREMENT,
    `id_documento` INT(11) NOT NULL,
    `version` INT(11) NOT NULL COMMENT 'Numero de version: 1, 2, 3...',
    `version_texto` VARCHAR(10) NOT NULL COMMENT 'Version texto: 1.0, 1.1, 2.0',
    `tipo_cambio` ENUM('mayor', 'menor') NOT NULL DEFAULT 'menor' COMMENT 'Mayor: X+1.0, Menor: X.Y+1',
    `descripcion_cambio` TEXT NOT NULL COMMENT 'Descripcion del cambio realizado',
    `contenido_snapshot` LONGTEXT NULL COMMENT 'JSON con snapshot del contenido al momento de aprobar',
    `estado` ENUM('vigente', 'obsoleto') NOT NULL DEFAULT 'vigente',
    `autorizado_por` VARCHAR(255) NULL COMMENT 'Nombre de quien autorizo',
    `autorizado_por_id` INT(11) NULL COMMENT 'ID del usuario que autorizo',
    `fecha_autorizacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `archivo_pdf` VARCHAR(255) NULL COMMENT 'Ruta al PDF generado de esta version',
    `hash_documento` VARCHAR(64) NULL COMMENT 'SHA-256 del PDF para integridad',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_version`),
    KEY `idx_documento` (`id_documento`),
    KEY `idx_version` (`version`),
    KEY `idx_estado` (`estado`),
    CONSTRAINT `fk_version_documento_sst` FOREIGN KEY (`id_documento`)
        REFERENCES `tbl_documentos_sst` (`id_documento`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",
];

// ==================== FUNCIONES ====================

function conectar($config) {
    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        if (!empty($config['ssl'])) {
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        echo "✓ Conexión exitosa a {$config['nombre']}\n";
        return $pdo;
    } catch (PDOException $e) {
        echo "✗ Error conectando a {$config['nombre']}: {$e->getMessage()}\n";
        return null;
    }
}

function ejecutarSQL($pdo, $nombre, $sql) {
    try {
        $pdo->exec($sql);
        echo "  ✓ {$nombre} ejecutado correctamente\n";
        return true;
    } catch (PDOException $e) {
        echo "  ✗ Error en {$nombre}: {$e->getMessage()}\n";
        return false;
    }
}

function verificarTabla($pdo, $tabla) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '{$tabla}'");
        $existe = $stmt->rowCount() > 0;

        if ($existe) {
            echo "  ℹ  Tabla {$tabla} ya existe\n";
        } else {
            echo "  ⚠  Tabla {$tabla} no existe, será creada\n";
        }

        return $existe;
    } catch (PDOException $e) {
        echo "  ✗ Error verificando tabla {$tabla}: {$e->getMessage()}\n";
        return false;
    }
}

// ==================== EJECUCIÓN ====================

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  SCRIPT DE CREACIÓN DE TABLAS DOCUMENTOS SST              ║\n";
echo "║  Fecha: " . date('Y-m-d H:i:s') . "                              ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";

$resultados = [];

foreach ($entornos as $key => $config) {
    echo "──────────────────────────────────────────────────────────\n";
    echo "ENTORNO: {$config['nombre']}\n";
    echo "──────────────────────────────────────────────────────────\n";

    $pdo = conectar($config);

    if (!$pdo) {
        $resultados[$key] = false;
        echo "✗ Saltando {$config['nombre']} debido a error de conexión\n\n";

        // Si falla LOCAL, no continuar a PRODUCCIÓN
        if ($key === 'local') {
            echo "✗ ERROR CRÍTICO: LOCAL falló. No se ejecutará en PRODUCCIÓN.\n";
            exit(1);
        }
        continue;
    }

    echo "\nVerificando tablas existentes...\n";
    verificarTabla($pdo, 'tbl_documentos_sst');
    verificarTabla($pdo, 'tbl_doc_versiones_sst');

    echo "\nEjecutando SQL statements...\n";
    $todoOk = true;

    foreach ($sqlStatements as $nombre => $sql) {
        if (!ejecutarSQL($pdo, $nombre, $sql)) {
            $todoOk = false;
        }
    }

    $resultados[$key] = $todoOk;

    if ($todoOk) {
        echo "\n✓ Todas las operaciones en {$config['nombre']} completadas exitosamente\n";
    } else {
        echo "\n✗ Algunas operaciones en {$config['nombre']} fallaron\n";

        // Si falla LOCAL, no continuar
        if ($key === 'local') {
            echo "✗ ERROR: LOCAL tuvo errores. No se ejecutará en PRODUCCIÓN.\n";
            exit(1);
        }
    }

    echo "\n";
}

// ==================== RESUMEN FINAL ====================

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  RESUMEN FINAL                                             ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";

foreach ($resultados as $key => $resultado) {
    $nombre = $entornos[$key]['nombre'];
    $icono = $resultado ? '✓' : '✗';
    $texto = $resultado ? 'ÉXITO' : 'FALLÓ';
    echo "{$icono} {$nombre}: {$texto}\n";
}

if ($resultados['local'] && $resultados['produccion']) {
    echo "\n✓ Tablas creadas/verificadas exitosamente en ambos entornos\n";
    exit(0);
} else {
    echo "\n✗ Hubo errores en la ejecución. Revisar log arriba.\n";
    exit(1);
}
