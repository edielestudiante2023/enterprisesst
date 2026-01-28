<?php
$config = [
    'host'     => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
    'port'     => 25060,
    'database' => 'empresas_sst',
    'username' => 'cycloid_userdb',
    'password' => 'AVNS_iDypWizlpMRwHIORJGG',
];

echo "=== Corrigiendo Stored Procedures con COLLATE (PRODUCCION) ===\n\n";

try {
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_SSL_CA => true,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ];

    $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
    $pdo->exec("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci'");
    echo "[OK] Conexion establecida\n\n";

    // 1. Corregir sp_generar_codigo_documento
    echo "--- Corrigiendo sp_generar_codigo_documento ---\n";
    $pdo->exec("DROP PROCEDURE IF EXISTS sp_generar_codigo_documento");

    $sql = "
    CREATE PROCEDURE sp_generar_codigo_documento(
        IN p_id_cliente INT,
        IN p_codigo_tipo VARCHAR(10),
        IN p_codigo_tema VARCHAR(10),
        OUT p_codigo_generado VARCHAR(50)
    )
    BEGIN
        DECLARE v_consecutivo INT;

        SELECT COALESCE(MAX(
            CAST(SUBSTRING_INDEX(codigo, '-', -1) AS UNSIGNED)
        ), 0) + 1
        INTO v_consecutivo
        FROM tbl_doc_documentos
        WHERE id_cliente = p_id_cliente
        AND codigo COLLATE utf8mb4_general_ci LIKE CONCAT(p_codigo_tipo, '-', p_codigo_tema, '-%') COLLATE utf8mb4_general_ci;

        SET p_codigo_generado = CONCAT(p_codigo_tipo, '-', p_codigo_tema, '-', LPAD(v_consecutivo, 3, '0'));
    END
    ";
    $pdo->exec($sql);
    echo "[OK] sp_generar_codigo_documento corregido\n\n";

    // 2. Verificar otros SPs con LIKE
    echo "--- Verificando otros SPs con LIKE ---\n";
    $spsToFix = ['sp_generar_carpetas_cliente', 'sp_generar_carpetas_estructura_drive', 'sp_generar_carpetas_por_nivel'];

    foreach ($spsToFix as $spName) {
        echo "  Verificando {$spName}... ";

        $stmt = $pdo->query("SHOW CREATE PROCEDURE {$spName}");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && isset($result['Create Procedure'])) {
            $def = $result['Create Procedure'];

            // Verificar si tiene LIKE sin COLLATE
            if (preg_match('/LIKE\s+(?!.*COLLATE)/i', $def)) {
                echo "NECESITA CORRECCION\n";
                // Mostrar la parte del LIKE
                preg_match('/LIKE[^;]+/i', $def, $matches);
                echo "    LIKE encontrado: " . ($matches[0] ?? 'N/A') . "\n";
            } else {
                echo "OK\n";
            }
        } else {
            echo "NO ENCONTRADO\n";
        }
    }

    echo "\n=== Completado ===\n";

} catch (PDOException $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
