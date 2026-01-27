<?php
/**
 * Script para crear tabla de versiones en LOCAL y PRODUCCION
 * Uso: php ejecutar_versiones_sst.php
 */

// Configuracion de conexiones
$conexiones = [
    'LOCAL' => [
        'dsn' => 'mysql:host=localhost;port=3306;dbname=empresas_sst;charset=utf8mb4',
        'user' => 'root',
        'pass' => '',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    ],
    'PRODUCCION' => [
        'dsn' => 'mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=empresas_sst;charset=utf8mb4',
        'user' => 'cycloid_userdb',
        'pass' => 'AVNS_iDypWizlpMRwHIORJGG',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
        ]
    ]
];

// SQL para agregar columnas a tbl_documentos_sst
$sqlAlterTable = "
ALTER TABLE `tbl_documentos_sst`
ADD COLUMN IF NOT EXISTS `fecha_aprobacion` DATETIME NULL AFTER `estado`,
ADD COLUMN IF NOT EXISTS `aprobado_por` INT(11) NULL AFTER `fecha_aprobacion`,
ADD COLUMN IF NOT EXISTS `motivo_version` VARCHAR(255) NULL AFTER `aprobado_por`
";

// SQL para crear tabla de versiones
$sqlCreateTable = "
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
";

echo "===========================================\n";
echo "CREACION DE TABLA DE VERSIONES DOCUMENTOS SST\n";
echo "===========================================\n\n";

foreach ($conexiones as $nombre => $config) {
    echo "--- {$nombre} ---\n";

    try {
        $pdo = new PDO($config['dsn'], $config['user'], $config['pass'], $config['options']);
        echo "Conectado OK\n";

        // Paso 1: Agregar columnas a tbl_documentos_sst
        echo "Agregando columnas a tbl_documentos_sst...\n";
        try {
            $pdo->exec($sqlAlterTable);
            echo "  Columnas agregadas OK\n";
        } catch (PDOException $e) {
            // Intentar agregar columnas una por una si falla
            $columnas = [
                "ALTER TABLE `tbl_documentos_sst` ADD COLUMN `fecha_aprobacion` DATETIME NULL",
                "ALTER TABLE `tbl_documentos_sst` ADD COLUMN `aprobado_por` INT(11) NULL",
                "ALTER TABLE `tbl_documentos_sst` ADD COLUMN `motivo_version` VARCHAR(255) NULL"
            ];
            foreach ($columnas as $sql) {
                try {
                    $pdo->exec($sql);
                } catch (PDOException $e2) {
                    // Columna ya existe, ignorar
                    if (strpos($e2->getMessage(), 'Duplicate column') === false) {
                        echo "  Nota: " . $e2->getMessage() . "\n";
                    }
                }
            }
            echo "  Columnas verificadas OK\n";
        }

        // Paso 2: Crear tabla de versiones
        echo "Creando tabla tbl_doc_versiones_sst...\n";
        $pdo->exec($sqlCreateTable);
        echo "  Tabla creada OK\n";

        // Verificar
        $stmt = $pdo->query("SHOW TABLES LIKE 'tbl_doc_versiones_sst'");
        if ($stmt->fetch()) {
            echo "  Verificacion: Tabla existe OK\n";
        }

        echo "Completado OK\n\n";

    } catch (PDOException $e) {
        echo "ERROR: " . $e->getMessage() . "\n\n";
    }
}

echo "===========================================\n";
echo "Proceso completado\n";
echo "===========================================\n";
