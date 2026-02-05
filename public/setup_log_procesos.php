<?php
/**
 * Crear tabla de log para cambios de estado en procesos electorales
 * Ejecuta en LOCAL y PRODUCCION
 */

echo "<h1>🔧 Setup: Tabla Log de Procesos Electorales</h1>";
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

// SQL para crear tabla
$sqlCrearTabla = "CREATE TABLE IF NOT EXISTS tbl_log_procesos (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    id_proceso INT NOT NULL,
    accion VARCHAR(50) NOT NULL COMMENT 'reabrir, cancelar, cambiar_estado, etc.',
    estado_anterior VARCHAR(50),
    estado_nuevo VARCHAR(50),
    observaciones TEXT,
    usuario_id INT,
    ip VARCHAR(45),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_proceso (id_proceso),
    INDEX idx_fecha (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

$sqlAgregarColumna = "ALTER TABLE tbl_procesos_electorales ADD COLUMN IF NOT EXISTS observaciones TEXT";

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

        // 1. Crear tabla tbl_log_procesos
        echo "<h4>1. Tabla tbl_log_procesos</h4>";
        $tablaExiste = $pdo->query("SHOW TABLES LIKE 'tbl_log_procesos'")->rowCount() > 0;

        if ($tablaExiste) {
            echo "<p class='info'>ℹ️ La tabla ya existe</p>";
        } else {
            $pdo->exec($sqlCrearTabla);
            echo "<p class='ok'>✅ Tabla creada exitosamente</p>";
        }

        // 2. Agregar columna observaciones a tbl_procesos_electorales
        echo "<h4>2. Columna 'observaciones' en tbl_procesos_electorales</h4>";
        $columnas = $pdo->query("SHOW COLUMNS FROM tbl_procesos_electorales")->fetchAll(PDO::FETCH_COLUMN);

        if (in_array('observaciones', $columnas)) {
            echo "<p class='info'>ℹ️ La columna ya existe</p>";
        } else {
            $pdo->exec("ALTER TABLE tbl_procesos_electorales ADD COLUMN observaciones TEXT AFTER fecha_completado");
            echo "<p class='ok'>✅ Columna agregada exitosamente</p>";
        }

        // Verificar estructura final
        echo "<h4>3. Verificacion</h4>";
        $estructura = $pdo->query("DESCRIBE tbl_log_procesos")->fetchAll();
        echo "<p class='ok'>✅ Tabla tbl_log_procesos tiene " . count($estructura) . " columnas</p>";

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
echo "<p><a href='" . str_replace('/public/setup_log_procesos.php', '', $_SERVER['REQUEST_URI']) . "/comites-elecciones/admin/procesos' class='btn' style='background:#0d6efd;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>Ir a Administracion de Procesos</a></p>";
?>
