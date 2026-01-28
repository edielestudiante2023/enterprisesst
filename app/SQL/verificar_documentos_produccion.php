<?php
/**
 * Script para verificar documentos y versiones en produccion
 */

$config = [
    'host'     => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
    'port'     => 25060,
    'database' => 'empresas_sst',
    'username' => 'cycloid_userdb',
    'password' => 'AVNS_iDypWizlpMRwHIORJGG',
];

echo "=== Verificacion de Documentos SST en Produccion ===\n\n";

try {
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_SSL_CA => true,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ];

    $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
    echo "[OK] Conexion establecida\n\n";

    // 1. Verificar documentos en tbl_documentos_sst
    echo "--- Documentos en tbl_documentos_sst ---\n";
    $stmt = $pdo->query("SELECT id_documento, id_cliente, codigo, titulo, tipo_documento, anio, version, estado, created_at FROM tbl_documentos_sst ORDER BY id_cliente, anio DESC");
    $documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($documentos)) {
        echo "  (No hay documentos)\n";
    } else {
        foreach ($documentos as $d) {
            echo "  ID:{$d['id_documento']} | Cliente:{$d['id_cliente']} | {$d['codigo']} | v{$d['version']} | {$d['estado']} | {$d['anio']}\n";
        }
    }
    echo "\n";

    // 2. Verificar versiones en tbl_doc_versiones_sst
    echo "--- Versiones en tbl_doc_versiones_sst ---\n";
    $stmt = $pdo->query("SELECT * FROM tbl_doc_versiones_sst ORDER BY id_documento, id_version DESC");
    $versiones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($versiones)) {
        echo "  (No hay versiones registradas)\n";
    } else {
        foreach ($versiones as $v) {
            echo "  Doc:{$v['id_documento']} | v{$v['version_texto']} | {$v['estado']} | {$v['descripcion_cambio']}\n";
        }
    }
    echo "\n";

    // 3. Si hay documentos sin versiones, crear la version inicial
    if (!empty($documentos) && empty($versiones)) {
        echo "--- Creando versiones iniciales para documentos existentes ---\n";

        $insertStmt = $pdo->prepare("
            INSERT INTO tbl_doc_versiones_sst
            (id_documento, id_cliente, codigo, titulo, anio, version, version_texto, tipo_cambio, descripcion_cambio, estado, autorizado_por, fecha_autorizacion, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'mayor', 'Version inicial del documento', 'vigente', 'Sistema', NOW(), NOW())
        ");

        foreach ($documentos as $d) {
            $versionTexto = $d['version'] . '.0';
            $insertStmt->execute([
                $d['id_documento'],
                $d['id_cliente'],
                $d['codigo'],
                $d['titulo'],
                $d['anio'],
                $d['version'],
                $versionTexto
            ]);
            echo "  Creada version inicial para documento {$d['id_documento']} ({$d['codigo']})\n";
        }

        echo "\n--- Versiones despues de la creacion ---\n";
        $stmt = $pdo->query("SELECT id_documento, version_texto, estado, descripcion_cambio FROM tbl_doc_versiones_sst ORDER BY id_documento, id_version DESC");
        $versionesNuevas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($versionesNuevas as $v) {
            echo "  Doc:{$v['id_documento']} | v{$v['version_texto']} | {$v['estado']}\n";
        }
    }

    echo "\n=== Verificacion completada ===\n";

} catch (PDOException $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
