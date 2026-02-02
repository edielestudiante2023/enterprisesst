<?php
/**
 * Script para corregir plantillas faltantes y duplicados
 * Ejecutar: php app/SQL/corregir_plantillas_faltantes.php
 *
 * Correcciones:
 * 1. Crear plantillas RES-REP, RES-SST, RES-TRA (existen en mapeos pero no en tbl_doc_plantillas)
 * 2. Eliminar duplicado PRG-CAP (mantener ID 44, eliminar ID 3)
 */

// Configuracion - cambiar segun entorno
$entorno = 'PRODUCCION'; // Cambiar a 'LOCAL' para ejecutar en local

$config = [
    'LOCAL' => [
        'host' => 'localhost',
        'port' => '3306',
        'dbname' => 'empresas_sst',
        'username' => 'root',
        'password' => '',
        'ssl' => false
    ],
    'PRODUCCION' => [
        'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port' => '25060',
        'dbname' => 'empresas_sst',
        'username' => 'cycloid_userdb',
        'password' => 'AVNS_iDypWizlpMRwHIORJGG',
        'ssl' => true
    ]
];

$cfg = $config[$entorno];

try {
    $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['dbname']};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    if ($cfg['ssl']) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = true;
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }

    $pdo = new PDO($dsn, $cfg['username'], $cfg['password'], $options);
    echo "=== CORRECCION DE PLANTILLAS - $entorno ===\n\n";

    // ============================================
    // 1. Crear plantilla RES-REP (Responsabilidades Rep Legal)
    // ============================================
    echo "--- Creando RES-REP ---\n";
    $sql = "INSERT INTO tbl_doc_plantillas (
                id_tipo, id_estandar, nombre, descripcion, codigo_sugerido, version,
                activo, orden, aplica_7, aplica_21, aplica_60, created_at, updated_at
            )
            SELECT
                14, -- REG (Reglamento/Responsabilidades)
                NULL,
                'Responsabilidades del Representante Legal en el SG-SST',
                'Documento que establece las responsabilidades del representante legal frente al Sistema de Gestion de Seguridad y Salud en el Trabajo',
                'RES-REP',
                '001',
                1, 101, 1, 1, 1, NOW(), NOW()
            WHERE NOT EXISTS (
                SELECT 1 FROM tbl_doc_plantillas WHERE codigo_sugerido = 'RES-REP'
            )";
    $result = $pdo->exec($sql);
    echo "  RES-REP: " . ($result ? "CREADA" : "ya existia o sin cambios") . "\n";

    // ============================================
    // 2. Crear plantilla RES-SST (Responsabilidades Responsable SST)
    // ============================================
    echo "--- Creando RES-SST ---\n";
    $sql = "INSERT INTO tbl_doc_plantillas (
                id_tipo, id_estandar, nombre, descripcion, codigo_sugerido, version,
                activo, orden, aplica_7, aplica_21, aplica_60, created_at, updated_at
            )
            SELECT
                14, -- REG
                NULL,
                'Responsabilidades del Responsable del SG-SST',
                'Documento que establece las responsabilidades del responsable designado del Sistema de Gestion de Seguridad y Salud en el Trabajo',
                'RES-SST',
                '001',
                1, 102, 1, 1, 1, NOW(), NOW()
            WHERE NOT EXISTS (
                SELECT 1 FROM tbl_doc_plantillas WHERE codigo_sugerido = 'RES-SST'
            )";
    $result = $pdo->exec($sql);
    echo "  RES-SST: " . ($result ? "CREADA" : "ya existia o sin cambios") . "\n";

    // ============================================
    // 3. Crear plantilla RES-TRA (Responsabilidades Trabajadores)
    // ============================================
    echo "--- Creando RES-TRA ---\n";
    $sql = "INSERT INTO tbl_doc_plantillas (
                id_tipo, id_estandar, nombre, descripcion, codigo_sugerido, version,
                activo, orden, aplica_7, aplica_21, aplica_60, created_at, updated_at
            )
            SELECT
                14, -- REG
                NULL,
                'Responsabilidades de los Trabajadores en el SG-SST',
                'Documento que establece las responsabilidades de los trabajadores frente al Sistema de Gestion de Seguridad y Salud en el Trabajo',
                'RES-TRA',
                '001',
                1, 103, 1, 1, 1, NOW(), NOW()
            WHERE NOT EXISTS (
                SELECT 1 FROM tbl_doc_plantillas WHERE codigo_sugerido = 'RES-TRA'
            )";
    $result = $pdo->exec($sql);
    echo "  RES-TRA: " . ($result ? "CREADA" : "ya existia o sin cambios") . "\n";

    // ============================================
    // 4. Limpiar duplicado PRG-CAP
    // ============================================
    echo "\n--- Verificando duplicados PRG-CAP ---\n";
    $stmt = $pdo->query("SELECT id_plantilla, codigo_sugerido, nombre, created_at
                         FROM tbl_doc_plantillas
                         WHERE codigo_sugerido = 'PRG-CAP'
                         ORDER BY id_plantilla");
    $duplicados = $stmt->fetchAll();

    if (count($duplicados) > 1) {
        echo "  Encontrados " . count($duplicados) . " registros PRG-CAP:\n";
        foreach ($duplicados as $d) {
            echo "    ID: {$d['id_plantilla']} - {$d['nombre']} - Creado: {$d['created_at']}\n";
        }

        // Mantener el mas reciente (ID 44), eliminar el antiguo (ID 3)
        // Pero primero verificar que ID 44 existe
        $mantener = null;
        $eliminar = null;
        foreach ($duplicados as $d) {
            if ($d['id_plantilla'] == 44) {
                $mantener = 44;
            } else {
                $eliminar = $d['id_plantilla'];
            }
        }

        if ($mantener && $eliminar) {
            echo "  Manteniendo ID $mantener, eliminando ID $eliminar...\n";
            $pdo->exec("DELETE FROM tbl_doc_plantillas WHERE id_plantilla = $eliminar AND codigo_sugerido = 'PRG-CAP'");
            echo "  Duplicado eliminado.\n";
        } else {
            echo "  No se puede determinar cual eliminar. Verificar manualmente.\n";
        }
    } else {
        echo "  No hay duplicados de PRG-CAP.\n";
    }

    // ============================================
    // 5. Verificar resultado final
    // ============================================
    echo "\n--- VERIFICACION FINAL ---\n";
    $sql = "SELECT id_plantilla, codigo_sugerido, nombre, version
            FROM tbl_doc_plantillas
            WHERE codigo_sugerido IN ('RES-REP', 'RES-SST', 'RES-TRA', 'PRG-CAP', 'ASG-RES', 'FT-SST-004')
            ORDER BY codigo_sugerido";
    $stmt = $pdo->query($sql);
    echo sprintf("  %-4s | %-12s | %-5s | %s\n", "ID", "Codigo", "Ver", "Nombre");
    echo "  " . str_repeat("-", 70) . "\n";
    while ($row = $stmt->fetch()) {
        echo sprintf("  %-4s | %-12s | %-5s | %s\n",
            $row['id_plantilla'],
            $row['codigo_sugerido'],
            $row['version'] ?? '?',
            substr($row['nombre'], 0, 45)
        );
    }

    // ============================================
    // 6. Verificar mapeos completos
    // ============================================
    echo "\n--- MAPEOS ACTUALIZADOS ---\n";
    $sql = "SELECT pc.codigo_carpeta, pc.codigo_plantilla,
                   CASE WHEN p.id_plantilla IS NOT NULL THEN 'SI' ELSE 'NO' END as plantilla_existe
            FROM tbl_doc_plantilla_carpeta pc
            LEFT JOIN tbl_doc_plantillas p ON pc.codigo_plantilla = p.codigo_sugerido
            WHERE pc.codigo_carpeta IN ('1.1.1', '1.1.2', '1.1.3', '1.2.1')
            ORDER BY pc.codigo_carpeta, pc.codigo_plantilla";
    $stmt = $pdo->query($sql);
    echo sprintf("  %-8s | %-12s | %s\n", "Carpeta", "Plantilla", "Existe");
    echo "  " . str_repeat("-", 40) . "\n";
    while ($row = $stmt->fetch()) {
        $status = $row['plantilla_existe'] == 'SI' ? '✓' : '✗';
        echo sprintf("  %-8s | %-12s | %s\n",
            $row['codigo_carpeta'],
            $row['codigo_plantilla'],
            $status
        );
    }

    echo "\n=== CORRECCION COMPLETADA EN $entorno ===\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
