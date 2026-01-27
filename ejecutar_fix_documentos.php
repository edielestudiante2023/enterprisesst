<?php
/**
 * Script para agregar columnas faltantes a las tablas de documentos SST
 * Ejecuta en LOCAL y PRODUCCION
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

echo "<h2>Ejecutando correcciones en tablas de documentos SST</h2>";
echo "<pre>";

function ejecutarCorreccion($pdo, $nombre) {
    echo "\n========== {$nombre} ==========\n";

    try {
        // 1. Agregar columna codigo si no existe
        $stmt = $pdo->query("SHOW COLUMNS FROM tbl_documentos_sst LIKE 'codigo'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE tbl_documentos_sst ADD COLUMN `codigo` VARCHAR(50) NULL AFTER `tipo_documento`");
            echo "✓ Columna 'codigo' agregada a tbl_documentos_sst\n";
        } else {
            echo "• Columna 'codigo' ya existe\n";
        }

        // 2. Agregar columna fecha_aprobacion si no existe
        $stmt = $pdo->query("SHOW COLUMNS FROM tbl_documentos_sst LIKE 'fecha_aprobacion'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE tbl_documentos_sst ADD COLUMN `fecha_aprobacion` DATETIME NULL AFTER `estado`");
            echo "✓ Columna 'fecha_aprobacion' agregada\n";
        } else {
            echo "• Columna 'fecha_aprobacion' ya existe\n";
        }

        // 3. Agregar columna aprobado_por si no existe
        $stmt = $pdo->query("SHOW COLUMNS FROM tbl_documentos_sst LIKE 'aprobado_por'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE tbl_documentos_sst ADD COLUMN `aprobado_por` INT(11) NULL AFTER `fecha_aprobacion`");
            echo "✓ Columna 'aprobado_por' agregada\n";
        } else {
            echo "• Columna 'aprobado_por' ya existe\n";
        }

        // 4. Agregar columna motivo_version si no existe
        $stmt = $pdo->query("SHOW COLUMNS FROM tbl_documentos_sst LIKE 'motivo_version'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE tbl_documentos_sst ADD COLUMN `motivo_version` VARCHAR(255) NULL AFTER `aprobado_por`");
            echo "✓ Columna 'motivo_version' agregada\n";
        } else {
            echo "• Columna 'motivo_version' ya existe\n";
        }

        // 5. Verificar/crear tabla de versiones
        $stmt = $pdo->query("SHOW TABLES LIKE 'tbl_doc_versiones_sst'");
        if (!$stmt->fetch()) {
            $sql = "CREATE TABLE `tbl_doc_versiones_sst` (
                `id_version` INT(11) NOT NULL AUTO_INCREMENT,
                `id_documento` INT(11) NOT NULL,
                `id_cliente` INT(11) NULL,
                `codigo` VARCHAR(50) NULL,
                `titulo` VARCHAR(255) NULL,
                `anio` INT(4) NULL,
                `version` INT(11) NOT NULL,
                `version_texto` VARCHAR(10) NOT NULL,
                `tipo_cambio` ENUM('mayor', 'menor') NOT NULL DEFAULT 'menor',
                `descripcion_cambio` TEXT NOT NULL,
                `contenido_snapshot` LONGTEXT NULL,
                `estado` ENUM('vigente', 'obsoleto') NOT NULL DEFAULT 'vigente',
                `autorizado_por` VARCHAR(255) NULL,
                `autorizado_por_id` INT(11) NULL,
                `fecha_autorizacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `archivo_pdf` VARCHAR(255) NULL,
                `hash_documento` VARCHAR(64) NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_version`),
                KEY `idx_documento` (`id_documento`),
                KEY `idx_version` (`version`),
                KEY `idx_estado` (`estado`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            $pdo->exec($sql);
            echo "✓ Tabla 'tbl_doc_versiones_sst' creada\n";
        } else {
            echo "• Tabla 'tbl_doc_versiones_sst' ya existe\n";

            // Verificar columnas adicionales
            $columnas = ['id_cliente' => 'INT(11)', 'codigo' => 'VARCHAR(50)', 'titulo' => 'VARCHAR(255)', 'anio' => 'INT(4)'];
            foreach ($columnas as $col => $tipo) {
                $stmt = $pdo->query("SHOW COLUMNS FROM tbl_doc_versiones_sst LIKE '$col'");
                if (!$stmt->fetch()) {
                    $pdo->exec("ALTER TABLE tbl_doc_versiones_sst ADD COLUMN `$col` $tipo NULL");
                    echo "✓ Columna '$col' agregada a tbl_doc_versiones_sst\n";
                }
            }
        }

        echo "<strong style='color:green;'>✓ {$nombre}: Correcciones aplicadas</strong>\n";

    } catch (PDOException $e) {
        echo "<strong style='color:red;'>ERROR en {$nombre}: " . $e->getMessage() . "</strong>\n";
    }
}

// Ejecutar en ambos entornos
foreach ($conexiones as $nombre => $config) {
    try {
        $pdo = new PDO($config['dsn'], $config['user'], $config['pass'], $config['options']);
        echo "Conectado a {$nombre} OK\n";
        ejecutarCorreccion($pdo, $nombre);
    } catch (PDOException $e) {
        echo "<strong style='color:orange;'>No se pudo conectar a {$nombre}: " . $e->getMessage() . "</strong>\n";
    }
}

echo "\n==========================================\n";
echo "Proceso completado. Ahora puedes aprobar el documento.\n";
echo "</pre>";
echo "<br><a href='javascript:history.back()'>← Volver</a>";
