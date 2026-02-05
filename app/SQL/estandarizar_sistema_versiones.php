<?php
/**
 * SCRIPT: Estandarizar Sistema de Versiones de Documentos SST
 *
 * Este script realiza las siguientes actualizaciones:
 * 1. Agrega campo 'tipo_documento' a tbl_doc_versiones_sst
 * 2. Agrega campo 'tipo_cambio_pendiente' a tbl_documentos_sst
 * 3. Sincroniza tipo_documento en versiones existentes
 * 4. Valida integridad de datos
 *
 * Ejecutar desde navegador:
 * - Local: http://localhost/enterprisesst/public/index.php/sql-runner?file=estandarizar_sistema_versiones
 * - Produccion: Ejecutar directamente este archivo
 *
 * @author Claude AI
 * @date 2026-02-04
 */

// Configuracion de errores
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Tiempo limite extendido
set_time_limit(300);

// ============================================================================
// CONFIGURACIONES DE CONEXION
// ============================================================================

$conexiones = [
    'local' => [
        'host' => 'localhost',
        'username' => 'root',
        'password' => '',
        'database' => 'empresas_sst',
        'port' => 3306,
        'ssl' => false
    ],
    'produccion' => [
        'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'username' => 'cycloid_userdb',
        'password' => 'AVNS_iDypWizlpMRwHIORJGG',
        'database' => 'empresas_sst',
        'port' => 25060,
        'ssl' => true
    ]
];

// ============================================================================
// FUNCIONES AUXILIARES
// ============================================================================

function conectar($config) {
    $opciones = [];

    if ($config['ssl']) {
        $opciones = [
            MYSQLI_OPT_SSL_VERIFY_SERVER_CERT => false,
        ];
    }

    $conn = mysqli_init();

    if ($config['ssl']) {
        mysqli_ssl_set($conn, null, null, null, null, null);
    }

    $connected = mysqli_real_connect(
        $conn,
        $config['host'],
        $config['username'],
        $config['password'],
        $config['database'],
        $config['port'],
        null,
        $config['ssl'] ? MYSQLI_CLIENT_SSL : 0
    );

    if (!$connected) {
        throw new Exception("Error de conexion: " . mysqli_connect_error());
    }

    mysqli_set_charset($conn, 'utf8mb4');
    return $conn;
}

function ejecutarQuery($conn, $sql, $descripcion = '') {
    $resultado = mysqli_query($conn, $sql);
    if ($resultado === false) {
        throw new Exception("Error en '$descripcion': " . mysqli_error($conn));
    }
    return $resultado;
}

function columnaExiste($conn, $tabla, $columna) {
    $sql = "SHOW COLUMNS FROM `$tabla` LIKE '$columna'";
    $result = mysqli_query($conn, $sql);
    return mysqli_num_rows($result) > 0;
}

function output($mensaje, $tipo = 'info') {
    $colores = [
        'info' => '#17a2b8',
        'success' => '#28a745',
        'warning' => '#ffc107',
        'error' => '#dc3545',
        'title' => '#6c757d'
    ];
    $color = $colores[$tipo] ?? '#333';
    $icono = [
        'info' => 'ℹ️',
        'success' => '✅',
        'warning' => '⚠️',
        'error' => '❌',
        'title' => '📋'
    ][$tipo] ?? '•';

    echo "<div style='padding: 8px 12px; margin: 4px 0; border-left: 4px solid $color; background: #f8f9fa; font-family: monospace;'>";
    echo "<span style='color: $color; font-weight: bold;'>$icono $mensaje</span>";
    echo "</div>";
    flush();
}

// ============================================================================
// MIGRACIONES A EJECUTAR
// ============================================================================

function ejecutarMigraciones($conn, $nombreConexion) {
    $resultados = [
        'exitosos' => 0,
        'errores' => 0,
        'omitidos' => 0,
        'detalles' => []
    ];

    output("Procesando base de datos: $nombreConexion", 'title');

    // ========================================================================
    // MIGRACION 1: Agregar tipo_documento a tbl_doc_versiones_sst
    // ========================================================================
    output("Verificando campo 'tipo_documento' en tbl_doc_versiones_sst...", 'info');

    if (!columnaExiste($conn, 'tbl_doc_versiones_sst', 'tipo_documento')) {
        try {
            $sql = "ALTER TABLE `tbl_doc_versiones_sst`
                    ADD COLUMN `tipo_documento` VARCHAR(100) NULL
                    AFTER `id_cliente`";
            ejecutarQuery($conn, $sql, 'Agregar tipo_documento');
            output("Campo 'tipo_documento' agregado exitosamente", 'success');
            $resultados['exitosos']++;
            $resultados['detalles'][] = "Agregado: tipo_documento en tbl_doc_versiones_sst";
        } catch (Exception $e) {
            output("Error: " . $e->getMessage(), 'error');
            $resultados['errores']++;
        }
    } else {
        output("Campo 'tipo_documento' ya existe - omitido", 'warning');
        $resultados['omitidos']++;
    }

    // ========================================================================
    // MIGRACION 2: Agregar tipo_cambio_pendiente a tbl_documentos_sst
    // ========================================================================
    output("Verificando campo 'tipo_cambio_pendiente' en tbl_documentos_sst...", 'info');

    if (!columnaExiste($conn, 'tbl_documentos_sst', 'tipo_cambio_pendiente')) {
        try {
            $sql = "ALTER TABLE `tbl_documentos_sst`
                    ADD COLUMN `tipo_cambio_pendiente` ENUM('mayor', 'menor') NULL
                    AFTER `motivo_version`";
            ejecutarQuery($conn, $sql, 'Agregar tipo_cambio_pendiente');
            output("Campo 'tipo_cambio_pendiente' agregado exitosamente", 'success');
            $resultados['exitosos']++;
            $resultados['detalles'][] = "Agregado: tipo_cambio_pendiente en tbl_documentos_sst";
        } catch (Exception $e) {
            output("Error: " . $e->getMessage(), 'error');
            $resultados['errores']++;
        }
    } else {
        output("Campo 'tipo_cambio_pendiente' ya existe - omitido", 'warning');
        $resultados['omitidos']++;
    }

    // ========================================================================
    // MIGRACION 3: Sincronizar tipo_documento en versiones existentes
    // ========================================================================
    output("Sincronizando tipo_documento en versiones existentes...", 'info');

    try {
        $sql = "UPDATE tbl_doc_versiones_sst v
                INNER JOIN tbl_documentos_sst d ON v.id_documento = d.id_documento
                SET v.tipo_documento = d.tipo_documento
                WHERE v.tipo_documento IS NULL";

        ejecutarQuery($conn, $sql, 'Sincronizar tipo_documento');
        $affectedRows = mysqli_affected_rows($conn);

        if ($affectedRows > 0) {
            output("Sincronizados $affectedRows registros de versiones", 'success');
            $resultados['exitosos']++;
            $resultados['detalles'][] = "Sincronizados: $affectedRows versiones con tipo_documento";
        } else {
            output("No hay versiones pendientes de sincronizar", 'warning');
            $resultados['omitidos']++;
        }
    } catch (Exception $e) {
        output("Error al sincronizar: " . $e->getMessage(), 'error');
        $resultados['errores']++;
    }

    // ========================================================================
    // MIGRACION 4: Agregar indice para tipo_documento (mejora consultas)
    // ========================================================================
    output("Verificando indice para tipo_documento...", 'info');

    try {
        // Verificar si el indice existe
        $sql = "SHOW INDEX FROM tbl_doc_versiones_sst WHERE Key_name = 'idx_tipo_documento'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) == 0) {
            $sql = "ALTER TABLE `tbl_doc_versiones_sst`
                    ADD INDEX `idx_tipo_documento` (`tipo_documento`)";
            ejecutarQuery($conn, $sql, 'Crear indice tipo_documento');
            output("Indice idx_tipo_documento creado", 'success');
            $resultados['exitosos']++;
            $resultados['detalles'][] = "Creado: indice idx_tipo_documento";
        } else {
            output("Indice idx_tipo_documento ya existe - omitido", 'warning');
            $resultados['omitidos']++;
        }
    } catch (Exception $e) {
        output("Error al crear indice: " . $e->getMessage(), 'error');
        $resultados['errores']++;
    }

    // ========================================================================
    // MIGRACION 5: Crear indice compuesto para consultas frecuentes
    // ========================================================================
    output("Verificando indice compuesto id_documento + estado...", 'info');

    try {
        $sql = "SHOW INDEX FROM tbl_doc_versiones_sst WHERE Key_name = 'idx_doc_estado'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) == 0) {
            $sql = "ALTER TABLE `tbl_doc_versiones_sst`
                    ADD INDEX `idx_doc_estado` (`id_documento`, `estado`)";
            ejecutarQuery($conn, $sql, 'Crear indice compuesto');
            output("Indice idx_doc_estado creado", 'success');
            $resultados['exitosos']++;
            $resultados['detalles'][] = "Creado: indice idx_doc_estado";
        } else {
            output("Indice idx_doc_estado ya existe - omitido", 'warning');
            $resultados['omitidos']++;
        }
    } catch (Exception $e) {
        output("Error al crear indice compuesto: " . $e->getMessage(), 'error');
        $resultados['errores']++;
    }

    // ========================================================================
    // VALIDACION: Verificar integridad de datos
    // ========================================================================
    output("Validando integridad de datos...", 'info');

    try {
        // Contar documentos sin versiones (despues de aprobacion deberian tener)
        $sql = "SELECT COUNT(*) as total FROM tbl_documentos_sst
                WHERE estado = 'aprobado'
                AND id_documento NOT IN (SELECT DISTINCT id_documento FROM tbl_doc_versiones_sst)";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);

        if ($row['total'] > 0) {
            output("ATENCION: {$row['total']} documentos aprobados sin registro de version", 'warning');
            $resultados['detalles'][] = "Advertencia: {$row['total']} docs aprobados sin version";
        } else {
            output("Todos los documentos aprobados tienen registro de version", 'success');
        }

        // Verificar versiones huerfanas
        $sql = "SELECT COUNT(*) as total FROM tbl_doc_versiones_sst
                WHERE id_documento NOT IN (SELECT id_documento FROM tbl_documentos_sst)";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);

        if ($row['total'] > 0) {
            output("ATENCION: {$row['total']} versiones huerfanas (documento eliminado)", 'warning');
            $resultados['detalles'][] = "Advertencia: {$row['total']} versiones huerfanas";
        } else {
            output("No hay versiones huerfanas", 'success');
        }

        // Estadisticas generales
        $sql = "SELECT
                    COUNT(DISTINCT id_documento) as total_docs,
                    COUNT(*) as total_versiones,
                    SUM(CASE WHEN estado = 'vigente' THEN 1 ELSE 0 END) as vigentes,
                    SUM(CASE WHEN estado = 'obsoleto' THEN 1 ELSE 0 END) as obsoletas
                FROM tbl_doc_versiones_sst";
        $result = mysqli_query($conn, $sql);
        $stats = mysqli_fetch_assoc($result);

        output("Estadisticas: {$stats['total_docs']} documentos, {$stats['total_versiones']} versiones ({$stats['vigentes']} vigentes, {$stats['obsoletas']} obsoletas)", 'info');

    } catch (Exception $e) {
        output("Error en validacion: " . $e->getMessage(), 'error');
    }

    return $resultados;
}

// ============================================================================
// EJECUCION PRINCIPAL
// ============================================================================

// Cabecera HTML
echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Migracion: Sistema de Versiones</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 20px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #0d6efd; padding-bottom: 10px; }
        h2 { color: #555; margin-top: 30px; }
        .resumen { background: #e9ecef; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .resumen h3 { margin-top: 0; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; }
    </style>
</head>
<body>
<div class='container'>
<h1>🔄 Migracion: Estandarizar Sistema de Versiones</h1>
<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>
<hr>";

$resumenGlobal = [];

foreach ($conexiones as $nombre => $config) {
    echo "<h2>🗄️ Base de datos: " . strtoupper($nombre) . "</h2>";

    try {
        $conn = conectar($config);
        output("Conexion establecida a {$config['host']}:{$config['port']}", 'success');

        $resultados = ejecutarMigraciones($conn, $nombre);
        $resumenGlobal[$nombre] = $resultados;

        mysqli_close($conn);
        output("Conexion cerrada", 'info');

    } catch (Exception $e) {
        output("FALLO: " . $e->getMessage(), 'error');
        $resumenGlobal[$nombre] = [
            'exitosos' => 0,
            'errores' => 1,
            'omitidos' => 0,
            'detalles' => ["Error de conexion: " . $e->getMessage()]
        ];
    }

    echo "<hr>";
}

// ============================================================================
// RESUMEN FINAL
// ============================================================================

echo "<div class='resumen'>
<h3>📊 RESUMEN DE LA MIGRACION</h3>
<table>
<tr>
    <th>Base de Datos</th>
    <th>Exitosos</th>
    <th>Errores</th>
    <th>Omitidos</th>
    <th>Estado</th>
</tr>";

foreach ($resumenGlobal as $nombre => $res) {
    $estado = $res['errores'] == 0
        ? "<span class='success'>✅ COMPLETADO</span>"
        : "<span class='error'>❌ CON ERRORES</span>";

    echo "<tr>
        <td><strong>" . strtoupper($nombre) . "</strong></td>
        <td class='success'>{$res['exitosos']}</td>
        <td class='error'>{$res['errores']}</td>
        <td class='warning'>{$res['omitidos']}</td>
        <td>$estado</td>
    </tr>";
}

echo "</table>";

// Mostrar detalles
foreach ($resumenGlobal as $nombre => $res) {
    if (!empty($res['detalles'])) {
        echo "<h4>Detalles $nombre:</h4><ul>";
        foreach ($res['detalles'] as $detalle) {
            echo "<li>$detalle</li>";
        }
        echo "</ul>";
    }
}

echo "</div>";

echo "<p style='margin-top: 20px; padding: 10px; background: #d4edda; border-radius: 5px;'>
<strong>✅ Proceso completado.</strong> El sistema de versiones ha sido estandarizado.
</p>";

echo "</div></body></html>";
