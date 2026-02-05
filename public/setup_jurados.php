<?php
/**
 * Crear tabla de jurados para procesos electorales
 * Ejecuta en LOCAL y PRODUCCION
 */

echo "<h1>🔧 Setup: Tabla Jurados de Votacion</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; max-width: 900px; margin: 0 auto; }
    .ok { color: green; font-weight: bold; }
    .error { color: red; }
    .info { color: blue; }
    .card { background: #f8f9fa; border-radius: 8px; padding: 15px; margin: 15px 0; border-left: 4px solid #0d6efd; }
    .card.success { border-left-color: #28a745; }
    .card.danger { border-left-color: #dc3545; }
    h2 { margin-top: 30px; padding-bottom: 10px; border-bottom: 2px solid #ddd; }
</style>";

// Configuraciones de bases de datos
$databases = [
    'LOCAL' => [
        'host' => 'localhost',
        'port' => 3306,
        'user' => 'root',
        'pass' => '',
        'dbname' => 'empresas_sst',
        'ssl' => false
    ],
    'PRODUCCION' => [
        'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port' => 25060,
        'user' => 'cycloid_userdb',
        'pass' => 'AVNS_iDypWizlpMRwHIORJGG',
        'dbname' => 'empresas_sst',
        'ssl' => true
    ]
];

// SQL para crear tabla de jurados
$sqlCrearTabla = "CREATE TABLE IF NOT EXISTS tbl_jurados_proceso (
    id_jurado INT AUTO_INCREMENT PRIMARY KEY,
    id_proceso INT NOT NULL,
    id_cliente INT NOT NULL,
    documento_identidad VARCHAR(20) NOT NULL,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    cargo VARCHAR(150),
    email VARCHAR(150),
    telefono VARCHAR(20),
    rol ENUM('presidente', 'secretario', 'escrutador', 'testigo') NOT NULL DEFAULT 'escrutador',
    firma_electronica TEXT COMMENT 'Base64 de la firma',
    fecha_firma DATETIME,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_proceso (id_proceso),
    INDEX idx_documento (documento_identidad),
    UNIQUE KEY uk_proceso_documento (id_proceso, documento_identidad)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Jurados de mesa para procesos de votacion de comites'";

foreach ($databases as $nombre => $config) {
    echo "<h2>📦 $nombre</h2>";
    echo "<div class='card'>";
    echo "<p><strong>Host:</strong> {$config['host']}:{$config['port']}</p>";
    echo "<p><strong>Database:</strong> {$config['dbname']}</p>";

    try {
        // Construir DSN
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset=utf8mb4";

        // Opciones PDO
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];

        // SSL para produccion
        if ($config['ssl']) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = true;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        $pdo = new PDO($dsn, $config['user'], $config['pass'], $options);
        echo "<p class='ok'>✅ Conexion exitosa</p>";

        // 1. Crear tabla tbl_jurados_proceso
        echo "<h4>1. Tabla tbl_jurados_proceso</h4>";
        $tablaExiste = $pdo->query("SHOW TABLES LIKE 'tbl_jurados_proceso'")->rowCount() > 0;

        if ($tablaExiste) {
            echo "<p class='info'>ℹ️ La tabla ya existe</p>";

            // Verificar estructura
            $columnas = $pdo->query("SHOW COLUMNS FROM tbl_jurados_proceso")->fetchAll(PDO::FETCH_COLUMN);
            echo "<p class='info'>Columnas existentes: " . implode(', ', $columnas) . "</p>";
        } else {
            $pdo->exec($sqlCrearTabla);
            echo "<p class='ok'>✅ Tabla creada exitosamente</p>";
        }

        // Verificar estructura final
        echo "<h4>2. Verificacion</h4>";
        $estructura = $pdo->query("DESCRIBE tbl_jurados_proceso")->fetchAll();
        echo "<p class='ok'>✅ Tabla tbl_jurados_proceso tiene " . count($estructura) . " columnas</p>";

        // Mostrar roles disponibles
        echo "<p class='info'>Roles disponibles: presidente, secretario, escrutador, testigo</p>";

        echo "</div>";
        echo "<div class='card success'>";
        echo "<p class='ok'>✅ $nombre configurado correctamente</p>";
        echo "</div>";

    } catch (PDOException $e) {
        echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
        echo "</div>";
        echo "<div class='card danger'>";
        echo "<p class='error'>❌ Fallo en $nombre</p>";
        echo "</div>";
    }
}

echo "<h2>🎉 Proceso Completado</h2>";
echo "<p>Ahora puedes agregar jurados desde la vista del proceso electoral.</p>";
echo "<p><a href='comites-elecciones/18/proceso/1' style='background:#0d6efd;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;display:inline-block;'>Ir al Proceso</a></p>";
?>
