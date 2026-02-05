<?php
/**
 * Script para crear la tabla de Matriz Legal
 * Ejecuta en LOCAL y PRODUCCIÓN
 */

// Configuración LOCAL
$localConfig = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'empresas_sst',
    'port' => 3306
];

// Configuración PRODUCCIÓN
$prodConfig = [
    'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
    'username' => 'cycloid_userdb',
    'password' => 'AVNS_iDypWizlpMRwHIORJGG',
    'database' => 'empresas_sst',
    'port' => 25060,
    'ssl' => true
];

// SQL para crear la tabla
$sqlCreateTable = "
CREATE TABLE IF NOT EXISTS matriz_legal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sector VARCHAR(100) DEFAULT 'General' COMMENT 'Sector económico: General, Salud, Construcción, etc.',
    tema VARCHAR(255) NOT NULL COMMENT 'Tema principal de la norma',
    subtema VARCHAR(255) NULL COMMENT 'Subtema específico',
    tipo_norma VARCHAR(100) NOT NULL COMMENT 'Tipo: Resolución, Decreto, Ley, Circular, etc.',
    id_norma_legal VARCHAR(50) NOT NULL COMMENT 'Número identificador de la norma',
    anio INT NOT NULL COMMENT 'Año de expedición',
    descripcion_norma TEXT NULL COMMENT 'Descripción general de la norma',
    autoridad_emisora VARCHAR(255) NULL COMMENT 'Entidad que emite la norma',
    referente_nacional VARCHAR(10) DEFAULT '' COMMENT 'Marca X si es referente nacional',
    referente_internacional VARCHAR(10) DEFAULT '' COMMENT 'Marca X si es referente internacional',
    articulos_aplicables TEXT NULL COMMENT 'Artículos específicos que aplican',
    parametros LONGTEXT NULL COMMENT 'Parámetros y detalles extensos',
    notas_vigencia TEXT NULL COMMENT 'Notas sobre vigencia y observaciones',
    estado ENUM('activa', 'derogada', 'modificada') DEFAULT 'activa' COMMENT 'Estado de la norma',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_sector (sector),
    INDEX idx_tema (tema),
    INDEX idx_tipo_norma (tipo_norma),
    INDEX idx_anio (anio),
    INDEX idx_estado (estado),
    INDEX idx_id_norma (id_norma_legal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Matriz Legal SST';
";

// Función para conectar y ejecutar
function ejecutarSQL($config, $sql, $nombre) {
    echo "<h3>Conectando a: $nombre</h3>";

    try {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ];

        // SSL para producción
        if (isset($config['ssl']) && $config['ssl']) {
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']}";
        $pdo = new PDO($dsn, $config['username'], $config['password'], $options);

        echo "<p style='color: green;'>✓ Conexión exitosa a $nombre</p>";

        // Ejecutar SQL
        $pdo->exec($sql);
        echo "<p style='color: green;'>✓ Tabla 'matriz_legal' creada/verificada correctamente en $nombre</p>";

        // Verificar estructura
        $stmt = $pdo->query("DESCRIBE matriz_legal");
        $columns = $stmt->fetchAll();

        echo "<details><summary>Ver estructura de la tabla (click para expandir)</summary>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #333; color: white;'><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Key</th><th>Default</th></tr>";
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td>{$col['Field']}</td>";
            echo "<td>{$col['Type']}</td>";
            echo "<td>{$col['Null']}</td>";
            echo "<td>{$col['Key']}</td>";
            echo "<td>{$col['Default']}</td>";
            echo "</tr>";
        }
        echo "</table></details>";

        // Contar registros
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM matriz_legal");
        $count = $stmt->fetch();
        echo "<p>Registros actuales en la tabla: <strong>{$count['total']}</strong></p>";

        return true;

    } catch (PDOException $e) {
        echo "<p style='color: red;'>✗ Error en $nombre: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Ejecutar
echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Crear Tabla Matriz Legal</title>";
echo "<style>body{font-family:Arial,sans-serif;padding:20px;max-width:1200px;margin:0 auto;} h2{color:#1c2437;} details{margin:10px 0;padding:10px;background:#f5f5f5;border-radius:5px;}</style>";
echo "</head><body>";
echo "<h1>Creación de Tabla: Matriz Legal</h1>";
echo "<hr>";

// LOCAL
echo "<div style='background:#e8f5e9;padding:20px;margin:20px 0;border-radius:10px;'>";
echo "<h2>Entorno LOCAL</h2>";
$localOk = ejecutarSQL($localConfig, $sqlCreateTable, 'LOCAL');
echo "</div>";

// PRODUCCIÓN
echo "<div style='background:#e3f2fd;padding:20px;margin:20px 0;border-radius:10px;'>";
echo "<h2>Entorno PRODUCCIÓN</h2>";
$prodOk = ejecutarSQL($prodConfig, $sqlCreateTable, 'PRODUCCIÓN');
echo "</div>";

// Resumen
echo "<hr>";
echo "<h2>Resumen</h2>";
echo "<ul>";
echo "<li>LOCAL: " . ($localOk ? "OK" : "ERROR") . "</li>";
echo "<li>PRODUCCIÓN: " . ($prodOk ? "OK" : "ERROR") . "</li>";
echo "</ul>";

if ($localOk && $prodOk) {
    echo "<div style='background:#c8e6c9;padding:20px;border-radius:10px;text-align:center;'>";
    echo "<h3 style='color:#2e7d32;'>Tablas creadas exitosamente en ambos entornos!</h3>";
    echo "<p>Ahora puedes acceder al módulo de Matriz Legal.</p>";
    echo "</div>";
}

echo "</body></html>";
