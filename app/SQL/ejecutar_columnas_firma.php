<?php
/**
 * Script para agregar columnas de firma digital al presupuesto
 * Ejecuta en LOCAL y PRODUCTION
 *
 * Uso: php app/SQL/ejecutar_columnas_firma.php
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
    'pass' => '*** REMOVIDO ***', // Credenciales removidas por seguridad
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

        // Ejecutar cada ALTER por separado para tbl_presupuesto_sst
        $queries = [
            "ALTER TABLE tbl_presupuesto_sst ADD COLUMN token_firma VARCHAR(64) NULL",
            "ALTER TABLE tbl_presupuesto_sst ADD COLUMN token_expiracion DATETIME NULL",
            "ALTER TABLE tbl_presupuesto_sst ADD COLUMN cedula_firmante VARCHAR(20) NULL",
            "ALTER TABLE tbl_presupuesto_sst ADD COLUMN firma_imagen VARCHAR(255) NULL",
            "ALTER TABLE tbl_presupuesto_sst ADD COLUMN ip_firma VARCHAR(45) NULL",
            "ALTER TABLE tbl_presupuesto_sst ADD COLUMN token_consulta VARCHAR(32) NULL",
        ];

        foreach ($queries as $sql) {
            try {
                $pdo->exec($sql);
                echo "OK: " . substr($sql, 0, 70) . "...\n";
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                    echo "SKIP (ya existe): " . substr($sql, 0, 55) . "...\n";
                } else {
                    echo "ERROR: {$e->getMessage()}\n";
                }
            }
        }

        // NOTA: Las columnas delegado_sst_email y representante_legal_email
        // ya existen en tbl_cliente_contexto_sst (ver ClienteContextoSstModel.php)

        echo "Migracion completada en {$nombre}\n";
        return true;

    } catch (PDOException $e) {
        echo "ERROR de conexion en {$nombre}: {$e->getMessage()}\n";
        return false;
    }
}

// Ejecutar
echo "===========================================\n";
echo "MIGRACION: Columnas de firma presupuesto\n";
echo "===========================================\n";

// LOCAL
ejecutarSQL($localConfig, 'LOCAL');

// PRODUCTION
ejecutarSQL($prodConfig, 'PRODUCTION');

echo "\n=== MIGRACION FINALIZADA ===\n";
