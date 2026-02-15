<?php
/**
 * Script para registrar plantillas de comites electorales y migrar documentos existentes
 * Ejecutar: php app/SQL/ejecutar_plantillas_comites.php [local|produccion]
 */

$entorno = $argv[1] ?? 'local';

if ($entorno === 'produccion') {
    $dsn = 'mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=empresas_sst;charset=utf8mb4';
    $user = 'cycloid_userdb';
    $pass = 'AVNS_iDypWizlpMRwHIORJGG';
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

    // =========================================================================
    // PASO 1: Insertar plantillas (ignorar si ya existen)
    // =========================================================================
    echo "--- Paso 1: Insertar plantillas en tbl_doc_plantillas ---\n";

    $plantillas = [
        ['acta_constitucion_copasst', 'Acta de Constitucion COPASST', 'FT-SST-013', 'Acta de constitucion del Comite Paritario de Seguridad y Salud en el Trabajo'],
        ['acta_constitucion_cocolab', 'Acta de Constitucion Comite Convivencia Laboral', 'FT-SST-013', 'Acta de constitucion del Comite de Convivencia Laboral'],
        ['acta_constitucion_brigada', 'Acta de Constitucion Brigada Emergencias', 'FT-SST-013', 'Acta de constitucion de la Brigada de Emergencias'],
        ['acta_constitucion_vigia', 'Acta de Constitucion Vigia SST', 'FT-SST-013', 'Acta de constitucion del Vigia de Seguridad y Salud en el Trabajo'],
        ['acta_recomposicion_copasst', 'Acta de Recomposicion COPASST', 'FT-SST-156', 'Acta de recomposicion del COPASST por cambio de miembros'],
        ['acta_recomposicion_cocolab', 'Acta de Recomposicion Comite Convivencia Laboral', 'FT-SST-155', 'Acta de recomposicion del Comite de Convivencia Laboral por cambio de miembros'],
        ['acta_recomposicion_brigada', 'Acta de Recomposicion Brigada Emergencias', 'FT-SST-156', 'Acta de recomposicion de la Brigada de Emergencias por cambio de miembros'],
        ['acta_recomposicion_vigia', 'Acta de Recomposicion Vigia SST', 'FT-SST-156', 'Acta de recomposicion del Vigia SST por cambio de designacion'],
    ];

    // id_tipo = 11 corresponde a 'Acta' en tbl_doc_tipos (FK obligatorio)
    $ID_TIPO_ACTA = 11;

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tbl_doc_plantillas WHERE tipo_documento = ?");
    $insert = $pdo->prepare("INSERT INTO tbl_doc_plantillas (id_tipo, tipo_documento, nombre, codigo_sugerido, descripcion, activo) VALUES (?, ?, ?, ?, ?, 1)");

    $insertados = 0;
    $existentes = 0;
    foreach ($plantillas as $p) {
        $stmt->execute([$p[0]]);
        if ($stmt->fetchColumn() > 0) {
            echo "  Ya existe: {$p[0]}\n";
            $existentes++;
            continue;
        }
        $insert->execute([$ID_TIPO_ACTA, $p[0], $p[1], $p[2], $p[3]]);
        echo "  Insertado: {$p[0]}\n";
        $insertados++;
    }
    echo "Plantillas: {$insertados} insertadas, {$existentes} ya existian\n\n";

    // =========================================================================
    // PASO 2: Migrar documentos existentes al sistema de versionamiento
    // =========================================================================
    echo "--- Paso 2: Migrar documentos de comites a tbl_doc_versiones_sst ---\n";

    $sqlBuscar = "
        SELECT d.id_documento, d.id_cliente, d.tipo_documento, d.codigo, d.titulo, d.anio,
               d.contenido, d.created_at
        FROM tbl_documentos_sst d
        LEFT JOIN tbl_doc_versiones_sst v ON v.id_documento = d.id_documento
        WHERE (d.tipo_documento LIKE 'acta_constitucion_%' OR d.tipo_documento LIKE 'acta_recomposicion_%')
          AND v.id_version IS NULL
    ";

    $docs = $pdo->query($sqlBuscar)->fetchAll(PDO::FETCH_ASSOC);
    echo "Documentos sin version encontrados: " . count($docs) . "\n";

    $insertVersion = $pdo->prepare("
        INSERT INTO tbl_doc_versiones_sst
        (id_documento, id_cliente, tipo_documento, codigo, titulo, anio, version, version_texto,
         tipo_cambio, descripcion_cambio, contenido_snapshot, estado, autorizado_por, fecha_autorizacion, created_at)
        VALUES (?, ?, ?, ?, ?, ?, 1, '1.0', 'mayor', 'Migracion: documento preexistente integrado al sistema de versionamiento',
                ?, 'vigente', 'Sistema (migracion)', ?, NOW())
    ");

    $updateEstado = $pdo->prepare("
        UPDATE tbl_documentos_sst SET estado = 'aprobado', fecha_aprobacion = created_at, updated_at = NOW()
        WHERE id_documento = ? AND estado = 'generado'
    ");

    $migrados = 0;
    foreach ($docs as $doc) {
        $insertVersion->execute([
            $doc['id_documento'],
            $doc['id_cliente'],
            $doc['tipo_documento'],
            $doc['codigo'],
            $doc['titulo'],
            $doc['anio'],
            $doc['contenido'],
            $doc['created_at'] ?? date('Y-m-d H:i:s')
        ]);
        $updateEstado->execute([$doc['id_documento']]);
        echo "  Migrado: [{$doc['id_documento']}] {$doc['tipo_documento']} - {$doc['titulo']}\n";
        $migrados++;
    }
    echo "Documentos migrados: {$migrados}\n\n";

    // =========================================================================
    // VERIFICACION
    // =========================================================================
    echo "--- Verificacion ---\n";

    $totalPlantillas = $pdo->query("SELECT COUNT(*) FROM tbl_doc_plantillas WHERE tipo_documento LIKE 'acta_constitucion_%' OR tipo_documento LIKE 'acta_recomposicion_%'")->fetchColumn();
    echo "Plantillas de comites en BD: {$totalPlantillas}/8\n";

    $totalVersiones = $pdo->query("SELECT COUNT(*) FROM tbl_doc_versiones_sst WHERE tipo_documento LIKE 'acta_constitucion_%' OR tipo_documento LIKE 'acta_recomposicion_%'")->fetchColumn();
    echo "Versiones de comites en BD: {$totalVersiones}\n";

    $sinVersion = $pdo->query("
        SELECT COUNT(*) FROM tbl_documentos_sst d
        LEFT JOIN tbl_doc_versiones_sst v ON v.id_documento = d.id_documento
        WHERE (d.tipo_documento LIKE 'acta_constitucion_%' OR d.tipo_documento LIKE 'acta_recomposicion_%')
          AND v.id_version IS NULL
    ")->fetchColumn();
    echo "Documentos de comites SIN version: {$sinVersion}\n";

    echo "\nCompletado exitosamente.\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
