<?php
/**
 * Crear tabla de log para cambios de estado en procesos electorales
 */

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'empresas_sst';

echo "<h1>Crear Tabla de Log de Procesos</h1>";
echo "<style>body{font-family:Arial;padding:20px;} .ok{color:green;} .info{color:blue;}</style>";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Verificar si la tabla existe
    $tablaExiste = $pdo->query("SHOW TABLES LIKE 'tbl_log_procesos'")->rowCount() > 0;

    if ($tablaExiste) {
        echo "<p class='info'>La tabla 'tbl_log_procesos' ya existe.</p>";
    } else {
        // Crear tabla
        $sql = "CREATE TABLE tbl_log_procesos (
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

        $pdo->exec($sql);
        echo "<p class='ok'>Tabla 'tbl_log_procesos' creada exitosamente.</p>";
    }

    // Agregar columna observaciones a procesos electorales si no existe
    $columnas = $pdo->query("SHOW COLUMNS FROM tbl_procesos_electorales")->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('observaciones', $columnas)) {
        $pdo->exec("ALTER TABLE tbl_procesos_electorales ADD COLUMN observaciones TEXT AFTER fecha_completado");
        echo "<p class='ok'>Columna 'observaciones' agregada a tbl_procesos_electorales</p>";
    } else {
        echo "<p class='info'>Columna 'observaciones' ya existe en tbl_procesos_electorales</p>";
    }

    echo "<h2 style='color:green;'>Listo!</h2>";
    echo "<p><a href='<?= base_url('comites-elecciones/admin/procesos') ?>'>Ir a Administracion de Procesos</a></p>";

} catch (PDOException $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}
?>
