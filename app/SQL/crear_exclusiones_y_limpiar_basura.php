<?php
/**
 * Script: Crear tabla tbl_doc_exclusiones_cliente + limpiar registro basura
 * Ejecutar: php app/SQL/crear_exclusiones_y_limpiar_basura.php
 */

echo "=== CREAR EXCLUSIONES + LIMPIAR BASURA ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

$conexiones = [
    'local' => [
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'empresas_sst',
        'username' => 'root',
        'password' => '',
        'ssl' => false
    ],
    'produccion' => [
        'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port' => 25060,
        'database' => 'empresas_sst',
        'username' => 'cycloid_userdb',
        'password' => 'AVNS_MR2SLvzRh3i_7o9fEHN',
        'ssl' => true
    ]
];

// 1. Crear tabla de exclusiones
$sqlCrearTabla = <<<'SQL'
CREATE TABLE IF NOT EXISTS tbl_doc_exclusiones_cliente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    tipo_documento VARCHAR(100) NOT NULL,
    motivo VARCHAR(255) NULL COMMENT 'Razón por la que no aplica',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT NULL,
    UNIQUE KEY uk_cliente_tipo (id_cliente, tipo_documento),
    INDEX idx_cliente (id_cliente),
    INDEX idx_tipo (tipo_documento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Documentos marcados como No Aplica por cliente';
SQL;

// 2. Eliminar registro basura "Mi Nuevo Documento"
$sqlLimpiar = <<<'SQL'
DELETE FROM tbl_doc_tipo_configuracion WHERE nombre LIKE '%Mi Nuevo Documento%'
SQL;

// 3. Verificar también secciones y firmantes huérfanos del registro basura
$sqlLimpiarSecciones = <<<'SQL'
DELETE sc FROM tbl_doc_secciones_config sc
LEFT JOIN tbl_doc_tipo_configuracion tc ON sc.id_tipo_config = tc.id_tipo_config
WHERE tc.id_tipo_config IS NULL
SQL;

$sqlLimpiarFirmantes = <<<'SQL'
DELETE fc FROM tbl_doc_firmantes_config fc
LEFT JOIN tbl_doc_tipo_configuracion tc ON fc.id_tipo_config = tc.id_tipo_config
WHERE tc.id_tipo_config IS NULL
SQL;

$localOk = false;

foreach ($conexiones as $entorno => $config) {
    if ($entorno === 'produccion' && !$localOk) {
        echo "\n⛔ LOCAL falló — NO se ejecuta en producción\n";
        break;
    }

    echo "\n" . str_repeat("=", 50) . "\n";
    echo "EJECUTANDO EN: " . strtoupper($entorno) . "\n";
    echo str_repeat("=", 50) . "\n";

    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
        $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
        if ($config['ssl']) {
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        echo "✅ Conexión establecida\n\n";

        // Paso 1: Crear tabla exclusiones
        echo "1) Creando tabla tbl_doc_exclusiones_cliente...\n";
        try {
            $pdo->exec($sqlCrearTabla);
            $stmt = $pdo->query("SELECT COUNT(*) FROM tbl_doc_exclusiones_cliente");
            echo "   ✅ Tabla lista (registros existentes: " . $stmt->fetchColumn() . ")\n";
        } catch (PDOException $e) {
            echo "   ❌ Error: " . $e->getMessage() . "\n";
        }

        // Paso 2: Buscar y eliminar registro basura
        echo "\n2) Buscando registro basura 'Mi Nuevo Documento'...\n";
        try {
            $stmt = $pdo->query("SELECT id_tipo_config, tipo_documento, nombre FROM tbl_doc_tipo_configuracion WHERE nombre LIKE '%Mi Nuevo Documento%' OR nombre LIKE '%mi_nuevo%'");
            $basura = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($basura) > 0) {
                echo "   Encontrados " . count($basura) . " registros basura:\n";
                foreach ($basura as $b) {
                    echo "     - ID={$b['id_tipo_config']} tipo={$b['tipo_documento']} nombre={$b['nombre']}\n";
                }
                $pdo->exec($sqlLimpiar);
                // También buscar por tipo_documento genérico
                $pdo->exec("DELETE FROM tbl_doc_tipo_configuracion WHERE tipo_documento LIKE '%mi_nuevo%' OR tipo_documento LIKE '%nuevo_documento%'");
                echo "   ✅ Registros basura eliminados\n";
            } else {
                echo "   ✅ No se encontró basura\n";
            }
        } catch (PDOException $e) {
            echo "   ❌ Error: " . $e->getMessage() . "\n";
        }

        // Paso 3: Limpiar secciones/firmantes huérfanos
        echo "\n3) Limpiando secciones/firmantes huérfanos...\n";
        try {
            $stmt = $pdo->exec($sqlLimpiarSecciones);
            echo "   Secciones huérfanas eliminadas: {$stmt}\n";
            $stmt = $pdo->exec($sqlLimpiarFirmantes);
            echo "   Firmantes huérfanos eliminados: {$stmt}\n";
            echo "   ✅ Limpieza completada\n";
        } catch (PDOException $e) {
            echo "   ❌ Error: " . $e->getMessage() . "\n";
        }

        // Verificación final
        echo "\n--- Verificación final ---\n";
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_doc_tipo_configuracion WHERE activo = 1");
        echo "   Tipos de documento activos: " . $stmt->fetch()['total'] . "\n";

        $stmt = $pdo->query("SHOW TABLES LIKE 'tbl_doc_exclusiones_cliente'");
        echo "   Tabla exclusiones existe: " . ($stmt->rowCount() > 0 ? 'SI' : 'NO') . "\n";

        if ($entorno === 'local') {
            $localOk = true;
            echo "\n✅ LOCAL OK — procediendo a producción\n";
        }

    } catch (PDOException $e) {
        echo "❌ Error de conexión: " . $e->getMessage() . "\n";
        if ($entorno === 'local') {
            $localOk = false;
        }
    }
}

echo "\n🎉 Script finalizado\n";
