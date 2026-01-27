<?php
/**
 * Script para verificar indicadores en ambos entornos
 */

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

echo "===========================================\n";
echo "VERIFICACION DE INDICADORES SST\n";
echo "===========================================\n\n";

foreach ($conexiones as $nombre => $config) {
    echo "--- {$nombre} ---\n";

    try {
        $pdo = new PDO($config['dsn'], $config['user'], $config['pass'], $config['options']);

        // Verificar estructura de la tabla
        $stmt = $pdo->query("DESCRIBE tbl_indicadores_sst");
        $columnas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "Columnas: " . implode(', ', $columnas) . "\n";

        // Contar indicadores por categoria
        $stmt = $pdo->query("SELECT categoria, COUNT(*) as total FROM tbl_indicadores_sst GROUP BY categoria");
        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($categorias)) {
            echo "No hay indicadores registrados\n";
        } else {
            echo "Indicadores por categoria:\n";
            foreach ($categorias as $cat) {
                echo "  - {$cat['categoria']}: {$cat['total']}\n";
            }
        }

        // Total de indicadores
        $stmt = $pdo->query("SELECT COUNT(*) FROM tbl_indicadores_sst");
        echo "Total indicadores: " . $stmt->fetchColumn() . "\n";

    } catch (PDOException $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }

    echo "\n";
}
