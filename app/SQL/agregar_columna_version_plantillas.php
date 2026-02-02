<?php
/**
 * Script para crear tabla tbl_plantillas_documentos_sst con columna 'version'
 * Esta tabla centraliza el control documental para documentos SST
 * Ejecuta en LOCAL y PRODUCTION
 *
 * Uso: php app/SQL/agregar_columna_version_plantillas.php
 */

// Configuracion LOCAL
$localConfig = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'db'   => 'empresas_sst'
];

// Configuracion PRODUCTION (DigitalOcean) - YA EJECUTADO
$prodConfig = [
    'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
    'port' => 25060,
    'user' => 'cycloid_userdb',
    'pass' => '*** REMOVIDO POR SEGURIDAD ***', // Credenciales removidas despues de ejecucion
    'db'   => 'empresas_sst',
    'ssl'  => true
];

function ejecutarSQL($config, $nombre) {
    echo "\n=== Ejecutando en {$nombre} ===\n";

    try {
        $dsn = "mysql:host={$config['host']};dbname={$config['db']}";
        if (isset($config['port'])) {
            $dsn .= ";port={$config['port']}";
        }

        $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

        if (!empty($config['ssl'])) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = true;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        $pdo = new PDO($dsn, $config['user'], $config['pass'], $options);
        echo "Conectado a {$nombre}\n";

        // 1. Crear tabla si no existe
        $createTable = "
            CREATE TABLE IF NOT EXISTS tbl_plantillas_documentos_sst (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_estandar INT NOT NULL COMMENT 'FK a tbl_estandares_minimos_0312',
                codigo VARCHAR(20) NOT NULL COMMENT 'Codigo del documento ej: FT-SST-004',
                version VARCHAR(10) DEFAULT '001' COMMENT 'Version del documento',
                nombre_documento VARCHAR(255) NOT NULL COMMENT 'Nombre del documento',
                descripcion TEXT NULL COMMENT 'Descripcion del documento',
                tipo_documento VARCHAR(50) DEFAULT 'formato' COMMENT 'Tipo: formato, procedimiento, programa, etc',
                activo TINYINT(1) DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY idx_estandar_codigo (id_estandar, codigo)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ";

        try {
            $pdo->exec($createTable);
            echo "OK: Tabla tbl_plantillas_documentos_sst creada/verificada\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'already exists') !== false) {
                echo "SKIP: Tabla ya existe\n";
            } else {
                echo "ERROR creando tabla: {$e->getMessage()}\n";
            }
        }

        // 2. Insertar documento del presupuesto (id_estandar = 3 para 1.1.3)
        $insertPresupuesto = "
            INSERT INTO tbl_plantillas_documentos_sst
            (id_estandar, codigo, version, nombre_documento, descripcion, tipo_documento)
            VALUES
            (3, 'FT-SST-004', '001', 'Asignacion de Recursos para el SG-SST',
             'Presupuesto anual de recursos financieros, tecnicos y humanos para el SG-SST', 'formato')
            ON DUPLICATE KEY UPDATE
                nombre_documento = VALUES(nombre_documento),
                descripcion = VALUES(descripcion),
                updated_at = CURRENT_TIMESTAMP
        ";

        try {
            $pdo->exec($insertPresupuesto);
            echo "OK: Documento FT-SST-004 (Presupuesto) insertado/actualizado\n";
        } catch (PDOException $e) {
            echo "ERROR insertando presupuesto: {$e->getMessage()}\n";
        }

        // 3. Verificar estructura actual
        $stmt = $pdo->query("DESCRIBE tbl_plantillas_documentos_sst");
        echo "\nEstructura de tbl_plantillas_documentos_sst:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  - {$row['Field']} ({$row['Type']})\n";
        }

        // 4. Verificar datos insertados
        $stmt = $pdo->query("SELECT id_estandar, codigo, version, nombre_documento FROM tbl_plantillas_documentos_sst");
        echo "\nDatos en tabla:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  - [{$row['id_estandar']}] {$row['codigo']} v{$row['version']}: {$row['nombre_documento']}\n";
        }

        echo "\nMigracion completada en {$nombre}\n";
        return true;

    } catch (PDOException $e) {
        echo "ERROR de conexion en {$nombre}: {$e->getMessage()}\n";
        return false;
    }
}

// Ejecutar
echo "=====================================================\n";
echo "MIGRACION: Crear tabla plantillas_documentos_sst\n";
echo "           con columna 'version' incluida\n";
echo "=====================================================\n";

// LOCAL
ejecutarSQL($localConfig, 'LOCAL');

// PRODUCTION
ejecutarSQL($prodConfig, 'PRODUCTION');

echo "\n=== MIGRACION FINALIZADA ===\n";
