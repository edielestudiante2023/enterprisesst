<?php
/**
 * Actualizar códigos de actas de constitución de comités
 * COPASST: FT-SST-013 (se queda)
 * COCOLAB: FT-SST-013 → FT-SST-015
 * BRIGADA: FT-SST-013 → FT-SST-016
 * VIGIA:   FT-SST-013 → FT-SST-017
 *
 * Ejecutar: php app/SQL/actualizar_codigos_actas_comites.php [local|produccion]
 */

$entorno = $argv[1] ?? 'local';

if ($entorno === 'produccion') {
    $dsn = 'mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=empresas_sst;charset=utf8mb4';
    $user = 'cycloid_userdb';
    $pass = 'AVNS_MR2SLvzRh3i_7o9fEHN';
    $opciones = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
    ];
    echo "=== PRODUCCION ===\n";
} else {
    $dsn = 'mysql:host=localhost;port=3306;dbname=empresas_sst;charset=utf8mb4';
    $user = 'root';
    $pass = '';
    $opciones = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ];
    echo "=== LOCAL ===\n";
}

try {
    $pdo = new PDO($dsn, $user, $pass, $opciones);
    echo "Conectado correctamente\n\n";

    $cambios = [
        'acta_constitucion_cocolab' => 'FT-SST-015',
        'acta_constitucion_brigada' => 'FT-SST-016',
        'acta_constitucion_vigia'   => 'FT-SST-017',
    ];

    // PASO 1: Actualizar tbl_documentos_sst
    echo "--- Paso 1: Actualizar tbl_documentos_sst ---\n";
    foreach ($cambios as $tipoDoc => $nuevoCodigo) {
        $stmt = $pdo->prepare("UPDATE tbl_documentos_sst SET codigo = ? WHERE tipo_documento = ? AND codigo = 'FT-SST-013'");
        $stmt->execute([$nuevoCodigo, $tipoDoc]);
        $count = $stmt->rowCount();
        echo "  {$tipoDoc} → {$nuevoCodigo}: {$count} registros\n";
    }

    // PASO 2: Actualizar tbl_doc_plantillas
    echo "\n--- Paso 2: Actualizar tbl_doc_plantillas ---\n";
    foreach ($cambios as $tipoDoc => $nuevoCodigo) {
        $stmt = $pdo->prepare("UPDATE tbl_doc_plantillas SET codigo_sugerido = ? WHERE tipo_documento = ? AND codigo_sugerido = 'FT-SST-013'");
        $stmt->execute([$nuevoCodigo, $tipoDoc]);
        $count = $stmt->rowCount();
        echo "  {$tipoDoc} → {$nuevoCodigo}: {$count} registros\n";
    }

    // VERIFICACION
    echo "\n--- Verificacion ---\n";
    $stmt = $pdo->query("SELECT tipo_documento, codigo, COUNT(*) as total FROM tbl_documentos_sst WHERE tipo_documento LIKE 'acta_constitucion_%' GROUP BY tipo_documento, codigo ORDER BY tipo_documento");
    echo "\ntbl_documentos_sst:\n";
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        echo "  {$row['tipo_documento']}: {$row['codigo']} ({$row['total']} docs)\n";
    }

    $stmt = $pdo->query("SELECT tipo_documento, codigo_sugerido as codigo FROM tbl_doc_plantillas WHERE tipo_documento LIKE 'acta_constitucion_%' ORDER BY tipo_documento");
    echo "\ntbl_doc_plantillas:\n";
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        echo "  {$row['tipo_documento']}: {$row['codigo']}\n";
    }

    echo "\nListo.\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
