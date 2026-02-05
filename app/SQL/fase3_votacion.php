<?php
/**
 * Fase 3: Sistema de Votacion Electronica
 * Crea tablas necesarias para el sistema de votacion
 * Ejecuta en LOCAL y PRODUCCION
 */

// Configuracion de bases de datos
$databases = [
    'local' => [
        'host' => 'localhost',
        'port' => 3306,
        'user' => 'root',
        'pass' => '',
        'dbname' => 'empresas_sst',
        'ssl' => false
    ],
    'produccion' => [
        'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port' => 25060,
        'user' => 'cycloid_userdb',
        'pass' => 'AVNS_iDypWizlpMRwHIORJGG',
        'dbname' => 'empresas_sst',
        'ssl' => true
    ]
];

echo "<h1>Fase 3: Sistema de Votacion Electronica</h1>";
echo "<p>Creando tablas en LOCAL y PRODUCCION...</p>";
echo "<hr>";

foreach ($databases as $nombre => $config) {
    echo "<h2>Base de datos: " . strtoupper($nombre) . "</h2>";

    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset=utf8mb4";
        $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

        if ($config['ssl']) {
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
            $options[PDO::MYSQL_ATTR_SSL_CA] = null;
        }

        $pdo = new PDO($dsn, $config['user'], $config['pass'], $options);
        echo "<p style='color:green'>Conexion OK</p>";

        // =====================================================
        // Tabla 1: Censo de votantes por proceso
        // =====================================================
        $sql1 = "
        CREATE TABLE IF NOT EXISTS `tbl_votantes_proceso` (
            `id_votante` INT AUTO_INCREMENT PRIMARY KEY,
            `id_proceso` INT NOT NULL COMMENT 'FK a tbl_procesos_electorales',
            `id_cliente` INT NOT NULL,

            -- Datos del votante
            `nombres` VARCHAR(100) NOT NULL,
            `apellidos` VARCHAR(100) NOT NULL,
            `documento_identidad` VARCHAR(20) NOT NULL,
            `email` VARCHAR(150) DEFAULT NULL,
            `telefono` VARCHAR(20) DEFAULT NULL,
            `cargo` VARCHAR(100) DEFAULT NULL,
            `area` VARCHAR(100) DEFAULT NULL,

            -- Token de acceso unico (24 horas)
            `token_acceso` VARCHAR(64) NOT NULL UNIQUE,
            `token_expira` DATETIME NOT NULL,

            -- Estado de votacion
            `ha_votado` TINYINT(1) DEFAULT 0,
            `fecha_voto` DATETIME DEFAULT NULL,
            `ip_voto` VARCHAR(45) DEFAULT NULL,
            `user_agent` VARCHAR(255) DEFAULT NULL,

            -- Metadatos
            `enviado_por_email` TINYINT(1) DEFAULT 0,
            `fecha_envio_email` DATETIME DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,

            INDEX `idx_proceso` (`id_proceso`),
            INDEX `idx_cliente` (`id_cliente`),
            INDEX `idx_token` (`token_acceso`),
            INDEX `idx_documento` (`documento_identidad`),
            UNIQUE KEY `uk_votante_proceso` (`id_proceso`, `documento_identidad`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        COMMENT='Censo de votantes habilitados por proceso electoral'
        ";

        $pdo->exec($sql1);
        echo "<p style='color:green'>tbl_votantes_proceso: OK</p>";

        // =====================================================
        // Tabla 2: Votos registrados (anonimos)
        // =====================================================
        $sql2 = "
        CREATE TABLE IF NOT EXISTS `tbl_votos_comite` (
            `id_voto` INT AUTO_INCREMENT PRIMARY KEY,
            `id_proceso` INT NOT NULL COMMENT 'FK a tbl_procesos_electorales',
            `id_candidato` INT NOT NULL COMMENT 'FK a tbl_candidatos_comite',

            -- Hash anonimo del votante (para evitar doble voto sin identificar)
            `hash_votante` VARCHAR(64) NOT NULL,

            -- Metadatos de auditoria
            `fecha_voto` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `ip_origen` VARCHAR(45) DEFAULT NULL,

            INDEX `idx_proceso` (`id_proceso`),
            INDEX `idx_candidato` (`id_candidato`),
            INDEX `idx_hash` (`hash_votante`),
            UNIQUE KEY `uk_voto_unico` (`id_proceso`, `hash_votante`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        COMMENT='Votos anonimos registrados por proceso'
        ";

        $pdo->exec($sql2);
        echo "<p style='color:green'>tbl_votos_comite: OK</p>";

        // =====================================================
        // Agregar columnas a tbl_procesos_electorales si no existen
        // =====================================================
        $columnasNuevas = [
            "ALTER TABLE tbl_procesos_electorales ADD COLUMN IF NOT EXISTS `enlace_votacion` VARCHAR(100) DEFAULT NULL COMMENT 'Codigo unico para enlace de votacion'",
            "ALTER TABLE tbl_procesos_electorales ADD COLUMN IF NOT EXISTS `fecha_inicio_votacion` DATETIME DEFAULT NULL",
            "ALTER TABLE tbl_procesos_electorales ADD COLUMN IF NOT EXISTS `fecha_fin_votacion` DATETIME DEFAULT NULL",
            "ALTER TABLE tbl_procesos_electorales ADD COLUMN IF NOT EXISTS `total_votantes` INT DEFAULT 0",
            "ALTER TABLE tbl_procesos_electorales ADD COLUMN IF NOT EXISTS `votos_emitidos` INT DEFAULT 0"
        ];

        foreach ($columnasNuevas as $sql) {
            try {
                $pdo->exec($sql);
            } catch (PDOException $e) {
                // Ignorar si la columna ya existe
                if (strpos($e->getMessage(), 'Duplicate') === false) {
                    echo "<p style='color:orange'>Columna: " . substr($e->getMessage(), 0, 80) . "</p>";
                }
            }
        }
        echo "<p style='color:green'>Columnas en tbl_procesos_electorales: OK</p>";

        echo "<p style='color:green; font-weight:bold'>$nombre completado</p>";

    } catch (PDOException $e) {
        echo "<p style='color:red'>Error en $nombre: " . $e->getMessage() . "</p>";
    }

    echo "<hr>";
}

echo "<h2 style='color:green'>Fase 3: Tablas creadas exitosamente</h2>";
echo "<p>Ahora puedes probar el sistema de votacion.</p>";
?>
