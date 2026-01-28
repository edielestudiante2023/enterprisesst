<?php
/**
 * Script para actualizar el campo 'estado' de las versiones existentes en tbl_doc_versiones_sst
 * Ejecutar una sola vez para corregir versiones antiguas sin estado
 *
 * USO: php app/SQL/ejecutar_update_versiones_estado.php
 */

// Configuracion de produccion
$config = [
    'host'     => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
    'port'     => 25060,
    'database' => 'empresas_sst',
    'username' => 'cycloid_userdb',
    'password' => 'AVNS_iDypWizlpMRwHIORJGG',
];

echo "=== Actualizacion de Estados de Versiones en Produccion ===\n\n";

try {
    // Conectar con SSL requerido
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_SSL_CA => true,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ];

    $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
    echo "[OK] Conexion establecida a produccion\n\n";

    // 1. Verificar estado actual
    echo "--- Estado ANTES de la actualizacion ---\n";
    $stmt = $pdo->query("SELECT id_documento, id_version, version_texto, estado FROM tbl_doc_versiones_sst ORDER BY id_documento, id_version DESC");
    $versionesAntes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($versionesAntes)) {
        echo "No hay versiones en la tabla tbl_doc_versiones_sst\n";
        exit(0);
    }

    foreach ($versionesAntes as $v) {
        $estado = $v['estado'] ?: '(vacio)';
        echo "  Doc {$v['id_documento']} - v{$v['version_texto']} - Estado: {$estado}\n";
    }
    echo "\n";

    // 2. Marcar todas las versiones sin estado como 'historico'
    echo "--- Actualizando versiones sin estado a 'historico' ---\n";
    $stmt = $pdo->prepare("UPDATE tbl_doc_versiones_sst SET estado = 'historico' WHERE estado IS NULL OR estado = ''");
    $stmt->execute();
    $afectadas1 = $stmt->rowCount();
    echo "  Versiones actualizadas a 'historico': {$afectadas1}\n\n";

    // 3. Para cada documento, marcar la version mas reciente como 'vigente' (si no es pendiente_firma)
    echo "--- Marcando version mas reciente de cada documento como 'vigente' ---\n";

    // Obtener la version mas reciente de cada documento
    $stmt = $pdo->query("
        SELECT id_documento, MAX(id_version) as max_version
        FROM tbl_doc_versiones_sst
        GROUP BY id_documento
    ");
    $maxVersiones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $afectadas2 = 0;
    foreach ($maxVersiones as $mv) {
        $stmtUpdate = $pdo->prepare("
            UPDATE tbl_doc_versiones_sst
            SET estado = 'vigente'
            WHERE id_documento = ? AND id_version = ? AND estado != 'pendiente_firma'
        ");
        $stmtUpdate->execute([$mv['id_documento'], $mv['max_version']]);
        $afectadas2 += $stmtUpdate->rowCount();
    }
    echo "  Versiones actualizadas a 'vigente': {$afectadas2}\n\n";

    // 4. Verificar resultado final
    echo "--- Estado DESPUES de la actualizacion ---\n";
    $stmt = $pdo->query("SELECT id_documento, id_version, version_texto, estado FROM tbl_doc_versiones_sst ORDER BY id_documento, id_version DESC");
    $versionesDespues = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($versionesDespues as $v) {
        $estado = $v['estado'] ?: '(vacio)';
        echo "  Doc {$v['id_documento']} - v{$v['version_texto']} - Estado: {$estado}\n";
    }

    echo "\n=== Actualizacion completada exitosamente ===\n";

} catch (PDOException $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
