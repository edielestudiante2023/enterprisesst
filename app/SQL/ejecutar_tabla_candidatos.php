<?php
/**
 * Ejecutar SQL para crear tabla tbl_candidatos_comite
 * Sistema de Conformación de Comités SST - Fase 2
 *
 * Ejecutar desde navegador:
 * https://tu-dominio.com/app/SQL/ejecutar_tabla_candidatos.php
 */

// Credenciales de producción DigitalOcean
$host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
$port = 25060;
$user = 'cycloid_userdb';
$pass = 'AVNS_iDypWizlpMRwHIORJGG';
$dbname = 'empresas_sst';

echo "<h1>🗄️ Creación de Tabla: tbl_candidatos_comite</h1>";
echo "<p>Sistema de Conformación de Comités SST - Fase 2</p>";
echo "<hr>";

try {
    // Conexión con SSL requerido
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        PDO::MYSQL_ATTR_SSL_CA => null,
    ];

    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "<p style='color: green;'>✓ Conexión a base de datos establecida</p>";

    // SQL para crear la tabla
    $sql = "
    CREATE TABLE IF NOT EXISTS `tbl_candidatos_comite` (
        `id_candidato` INT AUTO_INCREMENT PRIMARY KEY,
        `id_proceso` INT NOT NULL COMMENT 'FK a tbl_procesos_electorales',
        `id_cliente` INT NOT NULL COMMENT 'FK a tbl_cliente',

        `nombres` VARCHAR(100) NOT NULL,
        `apellidos` VARCHAR(100) NOT NULL,
        `documento_identidad` VARCHAR(20) NOT NULL,
        `tipo_documento` ENUM('CC', 'CE', 'TI', 'PA', 'PEP') DEFAULT 'CC',
        `cargo` VARCHAR(100) NOT NULL COMMENT 'Cargo en la empresa',
        `area` VARCHAR(100) DEFAULT NULL COMMENT 'Area o departamento',
        `email` VARCHAR(150) DEFAULT NULL,
        `telefono` VARCHAR(20) DEFAULT NULL,

        `foto` VARCHAR(255) DEFAULT NULL COMMENT 'Ruta de la foto',

        `representacion` ENUM('trabajador', 'empleador') NOT NULL COMMENT 'A quien representa',
        `tipo_plaza` ENUM('principal', 'suplente') DEFAULT 'principal',

        `estado` ENUM('inscrito', 'aprobado', 'rechazado', 'elegido', 'no_elegido', 'designado') DEFAULT 'inscrito',
        `motivo_rechazo` TEXT DEFAULT NULL,

        `votos_obtenidos` INT DEFAULT 0,
        `porcentaje_votos` DECIMAL(5,2) DEFAULT 0.00,

        `tiene_certificado_50h` TINYINT(1) DEFAULT 0,
        `archivo_certificado_50h` VARCHAR(255) DEFAULT NULL,
        `fecha_certificado_50h` DATE DEFAULT NULL,
        `institucion_certificado` VARCHAR(200) DEFAULT NULL,

        `observaciones` TEXT DEFAULT NULL,
        `inscrito_por` INT DEFAULT NULL COMMENT 'ID del usuario que inscribio',
        `fecha_inscripcion` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `fecha_aprobacion` DATETIME DEFAULT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_proceso` (`id_proceso`),
        INDEX `idx_cliente` (`id_cliente`),
        INDEX `idx_documento` (`documento_identidad`),
        INDEX `idx_estado` (`estado`),
        INDEX `idx_representacion` (`representacion`),

        UNIQUE KEY `uk_candidato_proceso` (`id_proceso`, `documento_identidad`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    COMMENT='Candidatos para procesos electorales de comites SST'
    ";

    $pdo->exec($sql);
    echo "<h2 style='color: green;'>✓ Tabla tbl_candidatos_comite creada exitosamente</h2>";

    // Crear índice adicional
    $sqlIndex = "
    CREATE INDEX `idx_proceso_representacion`
    ON `tbl_candidatos_comite` (`id_proceso`, `representacion`, `estado`)
    ";

    try {
        $pdo->exec($sqlIndex);
        echo "<p style='color: green;'>✓ Índice idx_proceso_representacion creado</p>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate') !== false) {
            echo "<p style='color: orange;'>⚠ Índice ya existe (OK)</p>";
        } else {
            echo "<p style='color: orange;'>⚠ " . $e->getMessage() . "</p>";
        }
    }

    // Verificar estructura de la tabla
    $stmt = $pdo->query("DESCRIBE tbl_candidatos_comite");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h3>Estructura de la tabla:</h3>";
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse; font-family: monospace;'>";
    echo "<tr style='background: #333; color: white;'><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    $i = 0;
    foreach ($columns as $col) {
        $bg = $i % 2 == 0 ? '#f9f9f9' : '#ffffff';
        echo "<tr style='background: $bg;'>";
        echo "<td><strong>{$col['Field']}</strong></td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "</tr>";
        $i++;
    }
    echo "</table>";

    // Contar registros
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_candidatos_comite");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Registros actuales: <strong>{$count['total']}</strong></p>";

    echo "<hr>";
    echo "<h2 style='color: green;'>✅ Fase 2 del Sistema de Conformación de Comités LISTA</h2>";
    echo "<p>Ya puedes probar el módulo de inscripción de candidatos.</p>";

} catch (PDOException $e) {
    echo "<h2 style='color: red;'>✗ Error</h2>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
    echo "<p>Verifica las credenciales y la conexión SSL.</p>";
}
?>
