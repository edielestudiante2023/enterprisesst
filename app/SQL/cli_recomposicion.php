<?php
/**
 * CLI Script - Módulo Recomposición de Comités
 *
 * Uso: php cli_recomposicion.php [local|prod]
 */

// Detectar entorno
$env = $argv[1] ?? 'local';

echo "\n========================================\n";
echo "  MÓDULO RECOMPOSICIÓN - MIGRACIÓN SQL\n";
echo "  Entorno: " . strtoupper($env) . "\n";
echo "========================================\n\n";

// Configuración de conexión
if ($env === 'prod') {
    $config = [
        'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port' => 25060,
        'database' => 'empresas_sst',
        'username' => 'cycloid_userdb',
        'password' => 'AVNS_iDypWizlpMRwHIORJGG',
        'ssl' => true
    ];
} else {
    $config = [
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'empresas_sst',
        'username' => 'root',
        'password' => '',
        'ssl' => false
    ];
}

// Conectar
echo "[*] Conectando a {$config['host']}:{$config['port']}...\n";

try {
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ];

    if ($config['ssl']) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = true;
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }

    $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
    echo "[OK] Conexión establecida\n\n";
} catch (PDOException $e) {
    echo "[ERROR] No se pudo conectar: " . $e->getMessage() . "\n";
    exit(1);
}

$results = [];
$errors = [];

// 1. Crear tabla de recomposiciones
echo "[*] Creando tabla tbl_recomposiciones_comite...\n";
try {
    $sql1 = "CREATE TABLE IF NOT EXISTS `tbl_recomposiciones_comite` (
        `id_recomposicion` INT AUTO_INCREMENT PRIMARY KEY,
        `id_proceso` INT NOT NULL COMMENT 'FK a tbl_procesos_electorales',
        `id_cliente` INT NOT NULL COMMENT 'FK a tbl_cliente',
        `fecha_recomposicion` DATE NOT NULL,
        `numero_recomposicion` INT DEFAULT 1,
        `id_candidato_saliente` INT NOT NULL,
        `motivo_salida` ENUM(
            'terminacion_contrato',
            'renuncia_voluntaria',
            'sancion_disciplinaria',
            'violacion_confidencialidad',
            'inasistencia_reiterada',
            'incumplimiento_funciones',
            'fallecimiento',
            'otro'
        ) NOT NULL,
        `motivo_detalle` TEXT DEFAULT NULL,
        `fecha_efectiva_salida` DATE NOT NULL,
        `id_candidato_entrante` INT DEFAULT NULL,
        `tipo_ingreso` ENUM(
            'siguiente_votacion',
            'designacion_empleador',
            'asamblea_extraordinaria'
        ) NOT NULL,
        `entrante_nombres` VARCHAR(100) DEFAULT NULL,
        `entrante_apellidos` VARCHAR(100) DEFAULT NULL,
        `entrante_documento` VARCHAR(20) DEFAULT NULL,
        `entrante_cargo` VARCHAR(100) DEFAULT NULL,
        `entrante_email` VARCHAR(150) DEFAULT NULL,
        `entrante_telefono` VARCHAR(20) DEFAULT NULL,
        `estado` ENUM('borrador', 'pendiente_firmas', 'firmado', 'cancelado') DEFAULT 'borrador',
        `id_documento` INT DEFAULT NULL,
        `observaciones` TEXT DEFAULT NULL,
        `justificacion_legal` TEXT DEFAULT NULL,
        `created_by` INT DEFAULT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX `idx_proceso` (`id_proceso`),
        INDEX `idx_cliente` (`id_cliente`),
        INDEX `idx_fecha` (`fecha_recomposicion`),
        INDEX `idx_estado` (`estado`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $pdo->exec($sql1);
    echo "[OK] Tabla tbl_recomposiciones_comite creada/verificada\n";
    $results[] = "Tabla tbl_recomposiciones_comite OK";
} catch (PDOException $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    $errors[] = $e->getMessage();
}

// 2. Agregar columnas a tbl_candidatos_comite
echo "\n[*] Verificando columnas en tbl_candidatos_comite...\n";

$columnas = [
    "estado_miembro" => "ADD COLUMN `estado_miembro` ENUM('activo', 'retirado', 'reemplazado') DEFAULT 'activo'",
    "fecha_ingreso_comite" => "ADD COLUMN `fecha_ingreso_comite` DATE DEFAULT NULL",
    "fecha_retiro_comite" => "ADD COLUMN `fecha_retiro_comite` DATE DEFAULT NULL",
    "es_recomposicion" => "ADD COLUMN `es_recomposicion` TINYINT(1) DEFAULT 0",
    "id_recomposicion_ingreso" => "ADD COLUMN `id_recomposicion_ingreso` INT DEFAULT NULL",
    "posicion_votacion" => "ADD COLUMN `posicion_votacion` INT DEFAULT NULL"
];

foreach ($columnas as $columna => $alterSql) {
    try {
        $check = $pdo->query("SHOW COLUMNS FROM `tbl_candidatos_comite` LIKE '{$columna}'")->fetchAll();
        if (count($check) == 0) {
            $pdo->exec("ALTER TABLE `tbl_candidatos_comite` {$alterSql}");
            echo "[OK] Columna '{$columna}' agregada\n";
            $results[] = "Columna {$columna} agregada";
        } else {
            echo "[--] Columna '{$columna}' ya existe\n";
        }
    } catch (PDOException $e) {
        echo "[ERROR] {$columna}: " . $e->getMessage() . "\n";
        $errors[] = $e->getMessage();
    }
}

// 3. Crear índices
echo "\n[*] Creando índices...\n";

$indices = [
    "idx_estado_miembro" => "CREATE INDEX `idx_estado_miembro` ON `tbl_candidatos_comite` (`id_proceso`, `estado_miembro`, `representacion`)",
    "idx_posicion_votacion" => "CREATE INDEX `idx_posicion_votacion` ON `tbl_candidatos_comite` (`id_proceso`, `representacion`, `posicion_votacion`)"
];

foreach ($indices as $nombre => $sql) {
    try {
        $pdo->exec($sql);
        echo "[OK] Índice '{$nombre}' creado\n";
        $results[] = "Índice {$nombre} creado";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate') !== false) {
            echo "[--] Índice '{$nombre}' ya existe\n";
        } else {
            echo "[ERROR] {$nombre}: " . $e->getMessage() . "\n";
            $errors[] = $e->getMessage();
        }
    }
}

// 4. Actualizar posición de votación
echo "\n[*] Actualizando posiciones de votación...\n";
try {
    $procesos = $pdo->query("SELECT DISTINCT id_proceso FROM tbl_candidatos_comite WHERE representacion = 'trabajador'")->fetchAll();
    $count = 0;

    foreach ($procesos as $proc) {
        $stmt = $pdo->prepare("SELECT id_candidato FROM tbl_candidatos_comite WHERE id_proceso = ? AND representacion = 'trabajador' ORDER BY votos_obtenidos DESC, id_candidato ASC");
        $stmt->execute([$proc['id_proceso']]);
        $candidatos = $stmt->fetchAll();

        $pos = 1;
        $updateStmt = $pdo->prepare("UPDATE tbl_candidatos_comite SET posicion_votacion = ? WHERE id_candidato = ?");
        foreach ($candidatos as $c) {
            $updateStmt->execute([$pos, $c['id_candidato']]);
            $pos++;
        }
        $count++;
    }
    echo "[OK] Posiciones actualizadas para {$count} procesos\n";
    $results[] = "Posiciones actualizadas: {$count} procesos";
} catch (PDOException $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    $errors[] = $e->getMessage();
}

// Resumen
echo "\n========================================\n";
echo "  RESUMEN\n";
echo "========================================\n";
echo "Exitosos: " . count($results) . "\n";
echo "Errores: " . count($errors) . "\n";

if (count($errors) > 0) {
    echo "\n[!] ERRORES ENCONTRADOS:\n";
    foreach ($errors as $e) {
        echo "    - {$e}\n";
    }
    exit(1);
}

echo "\n[OK] Migración completada exitosamente en " . strtoupper($env) . "\n\n";
exit(0);
