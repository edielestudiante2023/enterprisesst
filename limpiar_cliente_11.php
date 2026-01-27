<?php
/**
 * Script para eliminar documentación corrupta del cliente 11
 * Las versiones están mal (saltan a 4.0 sin 1, 2, 3)
 */

// Configuración de conexiones
$conexiones = [
    'LOCAL' => [
        'dsn' => 'mysql:host=localhost;port=3306;dbname=empresas_sst;charset=utf8mb4',
        'user' => 'root',
        'pass' => '',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    ],
    'PRODUCCION' => [
        'dsn' => 'mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=empresas_sst;charset=utf8mb4',
        'user' => 'cycloid_userdb',
        'pass' => 'AVNS_iDypWizlpMRwHIORJGG',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
        ]
    ]
];

$id_cliente = 11;

echo "<h2>Limpiando documentación corrupta del cliente {$id_cliente}</h2>";
echo "<pre>";

function limpiarCliente($pdo, $nombre, $id_cliente) {
    echo "\n========== {$nombre} ==========\n";

    try {
        $pdo->beginTransaction();

        // 1. Ver qué hay antes de borrar
        $stmt = $pdo->prepare("SELECT id_documento, titulo, version, estado FROM tbl_documentos_sst WHERE id_cliente = ?");
        $stmt->execute([$id_cliente]);
        $docs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "Documentos encontrados: " . count($docs) . "\n";
        foreach ($docs as $doc) {
            echo "  - ID: {$doc['id_documento']}, Título: {$doc['titulo']}, Versión: {$doc['version']}, Estado: {$doc['estado']}\n";
        }

        // 2. Ver versiones
        $stmt = $pdo->prepare("SELECT id_version, id_documento, version, version_texto, tipo_cambio FROM tbl_doc_versiones_sst WHERE id_cliente = ?");
        $stmt->execute([$id_cliente]);
        $versiones = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "\nVersiones encontradas: " . count($versiones) . "\n";
        foreach ($versiones as $v) {
            echo "  - ID Version: {$v['id_version']}, Doc: {$v['id_documento']}, Version: {$v['version']} ({$v['version_texto']}), Tipo: {$v['tipo_cambio']}\n";
        }

        // 3. Eliminar versiones
        $stmt = $pdo->prepare("DELETE FROM tbl_doc_versiones_sst WHERE id_cliente = ?");
        $stmt->execute([$id_cliente]);
        $versionesEliminadas = $stmt->rowCount();
        echo "\n✓ Versiones eliminadas: {$versionesEliminadas}\n";

        // 4. Eliminar documentos
        $stmt = $pdo->prepare("DELETE FROM tbl_documentos_sst WHERE id_cliente = ?");
        $stmt->execute([$id_cliente]);
        $docsEliminados = $stmt->rowCount();
        echo "✓ Documentos eliminados: {$docsEliminados}\n";

        $pdo->commit();
        echo "\n<strong style='color:green;'>✓ {$nombre}: Limpieza completada exitosamente</strong>\n";

    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "<strong style='color:red;'>ERROR en {$nombre}: " . $e->getMessage() . "</strong>\n";
    }
}

// Ejecutar en ambos entornos
foreach ($conexiones as $nombre => $config) {
    try {
        $pdo = new PDO($config['dsn'], $config['user'], $config['pass'], $config['options']);
        echo "Conectado a {$nombre} OK\n";
        limpiarCliente($pdo, $nombre, $id_cliente);
    } catch (PDOException $e) {
        echo "<strong style='color:orange;'>No se pudo conectar a {$nombre}: " . $e->getMessage() . "</strong>\n";
    }
}

echo "\n==========================================\n";
echo "Limpieza completada. El cliente {$id_cliente} puede empezar de nuevo.\n";
echo "</pre>";
echo "<br><a href='javascript:history.back()'>← Volver</a>";
