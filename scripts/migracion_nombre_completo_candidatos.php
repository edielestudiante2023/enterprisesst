<?php
/**
 * Migración: Agregar columna nombre_completo a tbl_candidatos_comite
 * y poblarla desde nombres + apellidos.
 *
 * Uso:
 *   php scripts/migracion_nombre_completo_candidatos.php            # LOCAL
 *   php scripts/migracion_nombre_completo_candidatos.php --env=prod  # PRODUCCIÓN
 */

$esProduccion = in_array('--env=prod', $argv ?? []);

if ($esProduccion) {
    $host     = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port     = 25060;
    $dbname   = 'empresas_sst';
    $username = 'cycloid_userdb';
    $password = getenv('DB_PROD_PASS') ?: 'AVNS_iDypWizlpMRwHIORJGG';
    $ssl      = true;
    echo "=== PRODUCCIÓN ===\n";
} else {
    $host     = '127.0.0.1';
    $port     = 3306;
    $dbname   = 'empresas_sst';
    $username = 'root';
    $password = '';
    $ssl      = false;
    echo "=== LOCAL ===\n";
}

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    if ($ssl) {
        $options[PDO::MYSQL_ATTR_SSL_CA]     = true;
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "Conexión OK\n\n";

    // Paso 1: Verificar si columna nombre_completo ya existe
    $existe = $pdo->query("SHOW COLUMNS FROM tbl_candidatos_comite LIKE 'nombre_completo'")->fetchAll();
    if (empty($existe)) {
        echo "Agregando columna nombre_completo...\n";
        $pdo->exec("ALTER TABLE tbl_candidatos_comite
                    ADD COLUMN nombre_completo VARCHAR(200) NOT NULL DEFAULT ''
                    AFTER apellidos");
        echo "  ✓ Columna nombre_completo agregada\n";
    } else {
        echo "  Columna nombre_completo ya existe\n";
    }

    // Paso 2: Poblar nombre_completo desde nombres + apellidos
    $sql = "UPDATE tbl_candidatos_comite
            SET nombre_completo = TRIM(
                CASE WHEN apellidos != '' AND apellidos IS NOT NULL
                    THEN CONCAT(nombres, ' ', apellidos)
                    ELSE nombres
                END
            )
            WHERE nombre_completo = '' OR nombre_completo IS NULL";
    $actualizados = $pdo->exec($sql);
    echo "  ✓ Registros migrados: {$actualizados}\n";

    // Paso 3: Verificar que no queden vacíos
    $vacios = $pdo->query("SELECT COUNT(*) FROM tbl_candidatos_comite WHERE nombre_completo = '' OR nombre_completo IS NULL")->fetchColumn();
    if ($vacios > 0) {
        echo "  ✗ ERROR: Quedan {$vacios} registros sin nombre_completo\n";
        exit(1);
    }

    // Paso 4: Mostrar muestra de resultados
    $muestra = $pdo->query("SELECT id_candidato, nombre_completo, nombres, apellidos FROM tbl_candidatos_comite LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    echo "\nMuestra de resultados:\n";
    foreach ($muestra as $row) {
        echo "  ID {$row['id_candidato']}: nombre_completo='{$row['nombre_completo']}' | nombres='{$row['nombres']}' | apellidos='{$row['apellidos']}'\n";
    }

    echo "\n✓ Migración completada exitosamente.\n";

} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
