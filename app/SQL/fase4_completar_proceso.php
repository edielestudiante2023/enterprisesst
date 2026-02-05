<?php
/**
 * Fase 4: Completar Proceso Electoral
 * Crea tabla de miembros del comite
 * Ejecuta en LOCAL y PRODUCCION
 */

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

echo "<h1>Fase 4: Completar Proceso Electoral</h1>";
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
        // Tabla: Miembros del Comite
        // =====================================================
        $sql1 = "
        CREATE TABLE IF NOT EXISTS `tbl_miembros_comite` (
            `id_miembro` INT AUTO_INCREMENT PRIMARY KEY,
            `id_comite` INT NOT NULL COMMENT 'FK a tbl_comites',
            `id_candidato` INT DEFAULT NULL COMMENT 'FK a tbl_candidatos_comite (si aplica)',

            -- Datos del miembro
            `nombres` VARCHAR(100) NOT NULL,
            `apellidos` VARCHAR(100) NOT NULL,
            `documento_identidad` VARCHAR(20) NOT NULL,
            `cargo` VARCHAR(100) DEFAULT NULL,
            `email` VARCHAR(150) DEFAULT NULL,
            `telefono` VARCHAR(20) DEFAULT NULL,
            `foto` VARCHAR(255) DEFAULT NULL,

            -- Rol en el comite
            `representacion` ENUM('trabajador', 'empleador') NOT NULL,
            `tipo_miembro` ENUM('principal', 'suplente') DEFAULT 'principal',
            `rol_comite` VARCHAR(50) DEFAULT NULL COMMENT 'Presidente, Secretario, etc.',

            -- Certificaciones
            `tiene_certificado_50h` TINYINT(1) DEFAULT 0,
            `archivo_certificado` VARCHAR(255) DEFAULT NULL,

            -- Estado
            `estado` ENUM('activo', 'inactivo', 'reemplazado') DEFAULT 'activo',
            `fecha_ingreso` DATE NOT NULL,
            `fecha_retiro` DATE DEFAULT NULL,
            `motivo_retiro` VARCHAR(255) DEFAULT NULL,

            -- Metadatos
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

            INDEX `idx_comite` (`id_comite`),
            INDEX `idx_documento` (`documento_identidad`),
            INDEX `idx_representacion` (`representacion`),
            INDEX `idx_estado` (`estado`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        COMMENT='Miembros activos de los comites conformados'
        ";

        $pdo->exec($sql1);
        echo "<p style='color:green'>tbl_miembros_comite: OK</p>";

        // =====================================================
        // Agregar columna id_comite a tbl_procesos_electorales si no existe
        // =====================================================
        try {
            $check = $pdo->query("SHOW COLUMNS FROM tbl_procesos_electorales LIKE 'id_comite'")->fetch();
            if (!$check) {
                $pdo->exec("ALTER TABLE tbl_procesos_electorales ADD COLUMN `id_comite` INT DEFAULT NULL COMMENT 'FK al comite conformado'");
                echo "<p style='color:green'>Columna id_comite agregada a tbl_procesos_electorales</p>";
            } else {
                echo "<p style='color:blue'>Columna id_comite ya existe</p>";
            }
        } catch (PDOException $e) {
            echo "<p style='color:orange'>id_comite: " . substr($e->getMessage(), 0, 80) . "</p>";
        }

        // =====================================================
        // Agregar columna fecha_escrutinio si no existe
        // =====================================================
        try {
            $check = $pdo->query("SHOW COLUMNS FROM tbl_procesos_electorales LIKE 'fecha_escrutinio'")->fetch();
            if (!$check) {
                $pdo->exec("ALTER TABLE tbl_procesos_electorales ADD COLUMN `fecha_escrutinio` DATETIME DEFAULT NULL");
                echo "<p style='color:green'>Columna fecha_escrutinio agregada</p>";
            } else {
                echo "<p style='color:blue'>Columna fecha_escrutinio ya existe</p>";
            }
        } catch (PDOException $e) {
            echo "<p style='color:orange'>fecha_escrutinio: " . substr($e->getMessage(), 0, 80) . "</p>";
        }

        // =====================================================
        // Agregar columna fecha_completado si no existe
        // =====================================================
        try {
            $check = $pdo->query("SHOW COLUMNS FROM tbl_procesos_electorales LIKE 'fecha_completado'")->fetch();
            if (!$check) {
                $pdo->exec("ALTER TABLE tbl_procesos_electorales ADD COLUMN `fecha_completado` DATETIME DEFAULT NULL");
                echo "<p style='color:green'>Columna fecha_completado agregada</p>";
            } else {
                echo "<p style='color:blue'>Columna fecha_completado ya existe</p>";
            }
        } catch (PDOException $e) {
            echo "<p style='color:orange'>fecha_completado: " . substr($e->getMessage(), 0, 80) . "</p>";
        }

        echo "<p style='color:green; font-weight:bold'>$nombre completado</p>";

    } catch (PDOException $e) {
        echo "<p style='color:red'>Error en $nombre: " . $e->getMessage() . "</p>";
    }

    echo "<hr>";
}

echo "<h2 style='color:green'>Fase 4: Tablas creadas exitosamente</h2>";
echo "<p>El sistema esta listo para completar procesos electorales.</p>";
?>
