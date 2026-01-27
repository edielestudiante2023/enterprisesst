<?php
/**
 * Fix: Actualizar tabla tbl_doc_firma_solicitudes con columnas y ENUMs faltantes
 * Ejecutar en LOCAL y PRODUCCION
 */

$conexiones = [
    'LOCAL' => [
        'dsn' => 'mysql:host=localhost;port=3306;dbname=empresas_sst;charset=utf8mb4',
        'user' => 'root',
        'pass' => '',
        'options' => [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
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

foreach ($conexiones as $entorno => $config) {
    echo "--- {$entorno} ---\n";

    try {
        $pdo = new PDO($config['dsn'], $config['user'], $config['pass'], $config['options']);
        echo "  Conectado OK\n";

        $tabla = 'tbl_doc_firma_solicitudes';

        // 1. Agregar columna orden_firma si no existe
        $check = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = 'orden_firma'");
        $check->execute([$tabla]);
        if ($check->fetchColumn() == 0) {
            $pdo->exec("ALTER TABLE `{$tabla}` ADD COLUMN `orden_firma` TINYINT NOT NULL DEFAULT 1 AFTER `firmante_tipo`");
            echo "  + Columna 'orden_firma' agregada\n";
        } else {
            echo "  = Columna 'orden_firma' ya existe\n";
        }

        // 2. Actualizar ENUM de 'estado' para incluir 'esperando' y 'cancelado'
        $colInfo = $pdo->query("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$tabla}' AND COLUMN_NAME = 'estado'")->fetch(PDO::FETCH_ASSOC);
        $tipoActual = $colInfo['COLUMN_TYPE'] ?? '';
        echo "  Estado actual ENUM: {$tipoActual}\n";

        if (strpos($tipoActual, 'esperando') === false || strpos($tipoActual, 'cancelado') === false) {
            $pdo->exec("ALTER TABLE `{$tabla}` MODIFY COLUMN `estado` ENUM('pendiente', 'esperando', 'firmado', 'expirado', 'rechazado', 'cancelado') NOT NULL DEFAULT 'pendiente'");
            echo "  + ENUM 'estado' actualizado con 'esperando' y 'cancelado'\n";
        } else {
            echo "  = ENUM 'estado' ya tiene todos los valores\n";
        }

        // 3. Actualizar ENUM de 'firmante_tipo' para incluir 'delegado_sst' y 'representante_legal'
        $colInfo = $pdo->query("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$tabla}' AND COLUMN_NAME = 'firmante_tipo'")->fetch(PDO::FETCH_ASSOC);
        $tipoActual = $colInfo['COLUMN_TYPE'] ?? '';
        echo "  Firmante_tipo actual ENUM: {$tipoActual}\n";

        if (strpos($tipoActual, 'delegado_sst') === false || strpos($tipoActual, 'representante_legal') === false) {
            $pdo->exec("ALTER TABLE `{$tabla}` MODIFY COLUMN `firmante_tipo` ENUM('elaboro', 'reviso', 'aprobo', 'delegado_sst', 'representante_legal') NOT NULL");
            echo "  + ENUM 'firmante_tipo' actualizado con 'delegado_sst' y 'representante_legal'\n";
        } else {
            echo "  = ENUM 'firmante_tipo' ya tiene todos los valores\n";
        }

        // 4. Verificar y agregar columna 'aceptacion_terminos' en evidencias si falta
        $tablaEv = 'tbl_doc_firma_evidencias';
        $check = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = 'aceptacion_terminos'");
        $check->execute([$tablaEv]);
        if ($check->fetchColumn() == 0) {
            $pdo->exec("ALTER TABLE `{$tablaEv}` ADD COLUMN `aceptacion_terminos` TINYINT(1) NOT NULL DEFAULT 1 AFTER `hash_documento`");
            echo "  + Columna 'aceptacion_terminos' agregada en evidencias\n";
        } else {
            echo "  = Columna 'aceptacion_terminos' ya existe en evidencias\n";
        }

        // 5. Verificar ENUM tipo_firma en evidencias
        $colInfo = $pdo->query("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$tablaEv}' AND COLUMN_NAME = 'tipo_firma'")->fetch(PDO::FETCH_ASSOC);
        $tipoActual = $colInfo['COLUMN_TYPE'] ?? '';
        echo "  Tipo_firma evidencias ENUM: {$tipoActual}\n";

        if (strpos($tipoActual, 'internal') === false) {
            $pdo->exec("ALTER TABLE `{$tablaEv}` MODIFY COLUMN `tipo_firma` ENUM('draw', 'upload', 'internal') NOT NULL");
            echo "  + ENUM 'tipo_firma' actualizado con 'internal'\n";
        } else {
            echo "  = ENUM 'tipo_firma' ya tiene todos los valores\n";
        }

        echo "  COMPLETADO\n\n";

    } catch (PDOException $e) {
        echo "  ERROR: " . $e->getMessage() . "\n\n";
    }
}

// Verificacion final
echo "=== VERIFICACION FINAL (LOCAL) ===\n";
try {
    $pdo = new PDO($conexiones['LOCAL']['dsn'], $conexiones['LOCAL']['user'], $conexiones['LOCAL']['pass'], $conexiones['LOCAL']['options']);

    $stmt = $pdo->query("DESCRIBE tbl_doc_firma_solicitudes");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  " . str_pad($row['Field'], 25) . $row['Type'] . "\n";
    }
} catch (PDOException $e) {
    echo "  Error: " . $e->getMessage() . "\n";
}

echo "\nFix completado.\n";
