<?php
/**
 * Migración: Crear tbl_ciclos_visita
 *
 * Uso:
 *   Local:      php app/SQL/migrate_ciclos_visita.php
 *   Producción: php app/SQL/migrate_ciclos_visita.php production
 */

$isProduction = ($argv[1] ?? '') === 'production';

if ($isProduction) {
    $pass = getenv('DB_PROD_PASS');
    $db = mysqli_init();
    $db->ssl_set(null, null, null, null, null);
    $db->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
    $db->real_connect(
        'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'cycloid_userdb', $pass, 'empresas_sst', 25060,
        null, MYSQLI_CLIENT_SSL
    );
    echo "=== Conectado a PRODUCCIÓN ===\n";
} else {
    $db = new mysqli('localhost', 'root', '', 'empresas_sst');
    echo "=== Conectado a LOCAL ===\n";
}

if ($db->connect_error) {
    echo "ERROR de conexión: " . $db->connect_error . "\n";
    exit(1);
}

$db->set_charset('utf8mb4');

// ─── PASO 1: Crear tabla tbl_ciclos_visita ───
echo "\n[1/3] Creando tabla tbl_ciclos_visita...\n";

$result = $db->query("SHOW TABLES LIKE 'tbl_ciclos_visita'");
if ($result->num_rows > 0) {
    echo "  Tabla ya existe. Saltando creación.\n";
} else {
    $sql = "CREATE TABLE tbl_ciclos_visita (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_cliente INT NOT NULL,
        id_consultor INT NOT NULL,
        anio INT NOT NULL,
        mes_esperado INT NOT NULL,
        estandar VARCHAR(50) NULL,
        fecha_agendada DATE NULL,
        id_agendamiento INT NULL,
        fecha_acta DATE NULL,
        id_acta INT NULL,
        estatus_agenda ENUM('pendiente','cumple','incumple') DEFAULT 'pendiente',
        estatus_mes ENUM('pendiente','cumple','incumple') DEFAULT 'pendiente',
        alerta_enviada TINYINT(1) DEFAULT 0,
        confirmacion_enviada TINYINT(1) DEFAULT 0,
        observaciones TEXT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_cliente (id_cliente),
        INDEX idx_consultor (id_consultor),
        INDEX idx_mes_anio (mes_esperado, anio),
        INDEX idx_estatus_agenda (estatus_agenda),
        INDEX idx_estatus_mes (estatus_mes)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    if ($db->query($sql)) {
        echo "  Tabla creada exitosamente.\n";
    } else {
        echo "  ERROR al crear tabla: " . $db->error . "\n";
        exit(1);
    }
}

// ─── PASO 2: Verificar si tbl_acta_visita existe ───
echo "\n[2/3] Verificando tabla tbl_acta_visita...\n";
$actaTable = $db->query("SHOW TABLES LIKE 'tbl_acta_visita'");
if ($actaTable->num_rows === 0) {
    echo "  AVISO: tbl_acta_visita no existe aún. Ejecuta migrate_acta_visita.php primero.\n";
} else {
    echo "  tbl_acta_visita OK.\n";
}

// ─── PASO 3: Resumen ───
echo "\n[3/3] Resumen final...\n";
$total = $db->query("SELECT COUNT(*) as cnt FROM tbl_ciclos_visita")->fetch_assoc()['cnt'];
echo "  Total registros en tbl_ciclos_visita: {$total}\n";

$db->close();
echo "\n=== Listo ===\n";
