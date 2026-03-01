<?php
/**
 * Script para crear la tabla de Matriz de Comunicación SST
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
CREATE TABLE IF NOT EXISTS matriz_comunicacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL COMMENT 'FK a tbl_clientes.id_cliente',
    categoria VARCHAR(150) NOT NULL COMMENT 'Categoria: Accidentes de Trabajo, Incidentes, Convivencia Laboral, etc.',
    situacion_evento VARCHAR(500) NOT NULL COMMENT 'Descripcion del evento o situacion que activa la comunicacion',
    que_comunicar TEXT NOT NULL COMMENT 'Que informacion se debe transmitir',
    quien_comunica VARCHAR(255) NOT NULL COMMENT 'Rol o persona responsable de comunicar',
    a_quien_comunicar VARCHAR(255) NOT NULL COMMENT 'Destinatario(s) de la comunicacion',
    mecanismo_canal VARCHAR(255) NOT NULL COMMENT 'Medio o canal: escrito, verbal, correo, buzon, etc.',
    frecuencia_plazo VARCHAR(150) NOT NULL COMMENT 'Frecuencia o plazo: Inmediato, 24h, Mensual, etc.',
    registro_evidencia VARCHAR(255) NULL COMMENT 'Tipo de registro o evidencia generada',
    norma_aplicable VARCHAR(255) NULL COMMENT 'Norma legal que sustenta la obligacion',
    tipo ENUM('interna', 'externa', 'ambas') DEFAULT 'interna' COMMENT 'Tipo de comunicacion',
    estado ENUM('activo', 'inactivo') DEFAULT 'activo' COMMENT 'Estado del protocolo',
    generado_por_ia TINYINT(1) DEFAULT 0 COMMENT '1 si fue generado por IA',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_id_cliente (id_cliente),
    INDEX idx_categoria (categoria),
    INDEX idx_tipo (tipo),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Matriz de Comunicacion SST por cliente';
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
        echo "<p style='color: green;'>✓ Tabla 'matriz_comunicacion' creada/verificada correctamente en $nombre</p>";

        // Verificar estructura
        $stmt = $pdo->query("DESCRIBE matriz_comunicacion");
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
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM matriz_comunicacion");
        $count = $stmt->fetch();
        echo "<p>Registros actuales en la tabla: <strong>{$count['total']}</strong></p>";

        return true;

    } catch (PDOException $e) {
        echo "<p style='color: red;'>✗ Error en $nombre: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Ejecutar
echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Crear Tabla Matriz Comunicación</title>";
echo "<style>body{font-family:Arial,sans-serif;padding:20px;max-width:1200px;margin:0 auto;} h2{color:#1c2437;} details{margin:10px 0;padding:10px;background:#f5f5f5;border-radius:5px;}</style>";
echo "</head><body>";
echo "<h1>Creación de Tabla: Matriz de Comunicación SST</h1>";
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
    echo "<h3 style='color:#2e7d32;'>Tabla creada exitosamente en ambos entornos!</h3>";
    echo "<p>Ahora puedes acceder al módulo de Matriz de Comunicación SST.</p>";
    echo "</div>";
}

echo "</body></html>";
