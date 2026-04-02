<?php
/**
 * Script para regularizar 3 programas que tienen Services de actividades PTA
 * pero su flujo en tbl_doc_tipo_configuracion dice 'secciones_ia' en vez de 'programa_con_pta'.
 *
 * Programas afectados:
 * - programa_estilos_vida_saludable (3.1.7)
 * - programa_evaluaciones_medicas_ocupacionales (3.1.4)
 * - programa_mantenimiento_periodico (4.2.5)
 *
 * Ejecutar: php app/SQL/regularizar_programas_huerfanos.php
 */

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

$programas = [
    'programa_estilos_vida_saludable',
    'programa_evaluaciones_medicas_ocupacionales',
    'programa_mantenimiento_periodico',
];

$sqlUpdate = "
    UPDATE tbl_doc_tipo_configuracion
    SET flujo = 'programa_con_pta', updated_at = NOW()
    WHERE tipo_documento = :tipo
    AND flujo = 'secciones_ia'
";

$sqlVerify = "
    SELECT tipo_documento, nombre, flujo
    FROM tbl_doc_tipo_configuracion
    WHERE tipo_documento IN ('" . implode("','", $programas) . "')
    ORDER BY tipo_documento
";

function conectar(array $config): PDO
{
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ];
    if ($config['ssl']) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = true;
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
    return new PDO($dsn, $config['username'], $config['password'], $options);
}

$localExito = false;

foreach ($conexiones as $entorno => $config) {
    echo "\n=== $entorno ===\n";

    if ($entorno === 'produccion' && !$localExito) {
        echo "  [SKIP] No se ejecuta en produccion porque LOCAL fallo\n";
        continue;
    }

    try {
        $pdo = conectar($config);
        echo "  [OK] Conexion establecida\n";

        // Mostrar estado ANTES
        echo "\n  Estado ANTES:\n";
        $rows = $pdo->query($sqlVerify)->fetchAll();
        foreach ($rows as $r) {
            $estado = $r['flujo'] === 'programa_con_pta' ? 'OK' : 'INCORRECTO';
            echo "    {$r['tipo_documento']} => flujo={$r['flujo']} ({$estado})\n";
        }

        // Ejecutar updates
        $totalAfectados = 0;
        $stmt = $pdo->prepare($sqlUpdate);
        foreach ($programas as $tipo) {
            $stmt->execute([':tipo' => $tipo]);
            $affected = $stmt->rowCount();
            $totalAfectados += $affected;
            $label = $affected > 0 ? 'ACTUALIZADO' : 'sin cambios (ya estaba correcto)';
            echo "  [OK] $tipo => $label\n";
        }

        // Mostrar estado DESPUES
        echo "\n  Estado DESPUES:\n";
        $rows = $pdo->query($sqlVerify)->fetchAll();
        foreach ($rows as $r) {
            $estado = $r['flujo'] === 'programa_con_pta' ? 'OK' : 'INCORRECTO';
            echo "    {$r['tipo_documento']} => flujo={$r['flujo']} ({$estado})\n";
        }

        echo "\n  Total filas actualizadas: $totalAfectados\n";

        if ($entorno === 'local') {
            $localExito = true;
        }

    } catch (PDOException $e) {
        echo "  [ERROR] " . $e->getMessage() . "\n";
        if ($entorno === 'local') {
            $localExito = false;
        }
    }
}

echo "\n=== Proceso completado ===\n";
