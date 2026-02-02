<?php
/**
 * Script de migración para tablas de Presupuesto SST
 * Ejecuta en LOCAL y PRODUCCIÓN
 *
 * Uso: php ejecutar_presupuesto_sst.php
 */

echo "==============================================\n";
echo "MIGRACIÓN: TABLAS DE PRESUPUESTO SST (1.1.3)\n";
echo "==============================================\n\n";

// Configuración LOCAL
$localConfig = [
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'empresas_sst',
    'username' => 'root',
    'password' => '',
    'ssl' => false
];

// Configuración PRODUCCIÓN (DigitalOcean)
$prodConfig = [
    'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
    'port' => 25060,
    'database' => 'empresas_sst',
    'username' => 'cycloid_userdb',
    'password' => 'AVNS_iDypWizlpMRwHIORJGG',
    'ssl' => true
];

// SQL para ejecutar
$sqlStatements = [
    // Tabla de categorías maestras
    "tabla_categorias" => "
        CREATE TABLE IF NOT EXISTS tbl_presupuesto_categorias (
            id_categoria INT AUTO_INCREMENT PRIMARY KEY,
            codigo VARCHAR(10) NOT NULL,
            nombre VARCHAR(100) NOT NULL,
            orden INT DEFAULT 0,
            activo TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uk_codigo (codigo)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",

    // Tabla principal de presupuesto
    "tabla_presupuesto" => "
        CREATE TABLE IF NOT EXISTS tbl_presupuesto_sst (
            id_presupuesto INT AUTO_INCREMENT PRIMARY KEY,
            id_cliente INT NOT NULL,
            anio INT NOT NULL,
            mes_inicio INT DEFAULT 1 COMMENT '1=Enero, 2=Febrero, etc.',
            estado ENUM('borrador', 'aprobado', 'cerrado') DEFAULT 'borrador',
            firmado_por VARCHAR(200) NULL,
            fecha_aprobacion DATETIME NULL,
            observaciones TEXT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uk_cliente_anio (id_cliente, anio),
            INDEX idx_cliente (id_cliente),
            INDEX idx_anio (anio)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",

    // Tabla de ítems
    "tabla_items" => "
        CREATE TABLE IF NOT EXISTS tbl_presupuesto_items (
            id_item INT AUTO_INCREMENT PRIMARY KEY,
            id_presupuesto INT NOT NULL,
            id_categoria INT NOT NULL,
            codigo_item VARCHAR(10) NOT NULL COMMENT 'Ej: 1.1, 3.2, 4.1',
            actividad VARCHAR(200) NOT NULL,
            descripcion TEXT NULL,
            orden INT DEFAULT 0,
            activo TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (id_presupuesto) REFERENCES tbl_presupuesto_sst(id_presupuesto) ON DELETE CASCADE,
            FOREIGN KEY (id_categoria) REFERENCES tbl_presupuesto_categorias(id_categoria),
            INDEX idx_presupuesto (id_presupuesto),
            INDEX idx_categoria (id_categoria)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",

    // Tabla de detalle mensual
    "tabla_detalle" => "
        CREATE TABLE IF NOT EXISTS tbl_presupuesto_detalle (
            id_detalle INT AUTO_INCREMENT PRIMARY KEY,
            id_item INT NOT NULL,
            mes INT NOT NULL COMMENT '1-12 para meses del año',
            anio INT NOT NULL,
            presupuestado DECIMAL(15,2) DEFAULT 0.00,
            ejecutado DECIMAL(15,2) DEFAULT 0.00,
            notas VARCHAR(255) NULL,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (id_item) REFERENCES tbl_presupuesto_items(id_item) ON DELETE CASCADE,
            UNIQUE KEY uk_item_mes_anio (id_item, mes, anio),
            INDEX idx_item (id_item),
            INDEX idx_mes_anio (mes, anio)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",

    // Categorías maestras
    "datos_categorias" => "
        INSERT INTO tbl_presupuesto_categorias (codigo, nombre, orden) VALUES
        ('1', 'Talento Humano de SST', 1),
        ('2', 'Capacitación y Formación', 2),
        ('3', 'Salud en el Trabajo', 3),
        ('4', 'Seguridad Industrial', 4),
        ('5', 'Medio Ambiente y Saneamiento Básico', 5),
        ('6', 'Otros Gastos SST', 6)
        ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), orden = VALUES(orden)
    ",

    // Plantilla de documento
    "plantilla_documento" => "
        INSERT INTO tbl_plantillas_documentos_sst
        (id_estandar, codigo, nombre_documento, descripcion, tipo_documento, patron, tiene_plantilla, campos_requeridos, orden)
        VALUES
        (3, 'FT-SST-004', 'Asignación de recursos para el SG-SST',
        'Presupuesto anual de recursos económicos, técnicos, humanos y de otra índole requeridos para el SG-SST',
        'formato', 'B', 1,
        '{\"campos\": [\"anio\", \"categorias\", \"items\", \"montos_mensuales\"]}',
        4)
        ON DUPLICATE KEY UPDATE
        nombre_documento = VALUES(nombre_documento),
        descripcion = VALUES(descripcion)
    "
];

/**
 * Ejecuta migración en una base de datos
 */
function ejecutarMigracion($config, $nombre, $sqlStatements) {
    echo "--- {$nombre} ---\n";

    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];

        // SSL para producción
        if ($config['ssl']) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = true;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        echo "✓ Conexión exitosa\n";

        foreach ($sqlStatements as $nombre_sql => $sql) {
            try {
                $pdo->exec($sql);
                echo "  ✓ {$nombre_sql}\n";
            } catch (PDOException $e) {
                // Ignorar errores de duplicados en INSERT
                if (strpos($e->getMessage(), 'Duplicate') !== false) {
                    echo "  ~ {$nombre_sql} (ya existe)\n";
                } else {
                    echo "  ✗ {$nombre_sql}: " . $e->getMessage() . "\n";
                }
            }
        }

        // Verificar tablas creadas
        $stmt = $pdo->query("SHOW TABLES LIKE 'tbl_presupuesto%'");
        $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "\nTablas de presupuesto encontradas: " . count($tablas) . "\n";
        foreach ($tablas as $tabla) {
            echo "  - {$tabla}\n";
        }

        // Verificar categorías
        $stmt = $pdo->query("SELECT COUNT(*) FROM tbl_presupuesto_categorias");
        $count = $stmt->fetchColumn();
        echo "\nCategorías en catálogo: {$count}\n";

        echo "\n✓ Migración {$nombre} completada\n\n";
        return true;

    } catch (PDOException $e) {
        echo "✗ Error de conexión: " . $e->getMessage() . "\n\n";
        return false;
    }
}

// Ejecutar en LOCAL
echo "\n========== BASE DE DATOS LOCAL ==========\n";
$localOk = ejecutarMigracion($localConfig, 'LOCAL', $sqlStatements);

// Ejecutar en PRODUCCIÓN
echo "\n========== BASE DE DATOS PRODUCCIÓN ==========\n";
$prodOk = ejecutarMigracion($prodConfig, 'PRODUCCIÓN', $sqlStatements);

// Resumen
echo "\n==============================================\n";
echo "RESUMEN DE MIGRACIÓN\n";
echo "==============================================\n";
echo "LOCAL:      " . ($localOk ? "✓ OK" : "✗ ERROR") . "\n";
echo "PRODUCCIÓN: " . ($prodOk ? "✓ OK" : "✗ ERROR") . "\n";
echo "\nTablas creadas:\n";
echo "  - tbl_presupuesto_categorias (catálogo de categorías)\n";
echo "  - tbl_presupuesto_sst (presupuesto por cliente/año)\n";
echo "  - tbl_presupuesto_items (ítems del presupuesto)\n";
echo "  - tbl_presupuesto_detalle (detalle mensual P vs E)\n";
echo "\n";
