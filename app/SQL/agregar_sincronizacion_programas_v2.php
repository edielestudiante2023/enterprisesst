<?php
/**
 * Script para agregar soporte de sincronizacion de PROGRAMAS (v2)
 * Compatible con MySQL 5.7+ y MySQL 8.0+
 *
 * Ejecutar: php app/SQL/agregar_sincronizacion_programas_v2.php
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
        'host' => getenv('DB_PROD_HOST') ?: 'TU_HOST_PRODUCCION',
        'port' => getenv('DB_PROD_PORT') ?: 25060,
        'database' => getenv('DB_PROD_DATABASE') ?: 'empresas_sst',
        'username' => getenv('DB_PROD_USERNAME') ?: 'TU_USUARIO',
        'password' => getenv('DB_PROD_PASSWORD') ?: 'TU_PASSWORD',
        'ssl' => true
    ]
];

function ejecutarEnEntorno($config, $nombreEntorno) {
    echo "\n========================================\n";
    echo "Ejecutando en: {$nombreEntorno}\n";
    echo "========================================\n";

    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        if ($config['ssl']) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = true;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        echo "Conectado exitosamente a {$nombreEntorno}\n\n";

        // 1. Agregar columna sincronizar_bd a tbl_doc_secciones_config
        echo "1. Verificando columna sincronizar_bd en secciones... ";
        $stmt = $pdo->query("SHOW COLUMNS FROM tbl_doc_secciones_config LIKE 'sincronizar_bd'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE tbl_doc_secciones_config ADD COLUMN sincronizar_bd VARCHAR(50) NULL COMMENT 'Tabla destino al aprobar: pta_cliente, indicadores_sst'");
            echo "AGREGADA\n";
        } else {
            echo "Ya existe (OK)\n";
        }

        // 2. Agregar columna id_documento_origen a tbl_pta_cliente
        echo "2. Verificando columna id_documento_origen en PTA... ";
        $stmt = $pdo->query("SHOW COLUMNS FROM tbl_pta_cliente LIKE 'id_documento_origen'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE tbl_pta_cliente ADD COLUMN id_documento_origen INT NULL COMMENT 'ID del documento SST que genero esta actividad'");
            echo "AGREGADA\n";
        } else {
            echo "Ya existe (OK)\n";
        }

        // 3. Agregar columna id_documento_origen a tbl_indicadores_sst
        echo "3. Verificando columna id_documento_origen en Indicadores... ";
        $stmt = $pdo->query("SHOW COLUMNS FROM tbl_indicadores_sst LIKE 'id_documento_origen'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE tbl_indicadores_sst ADD COLUMN id_documento_origen INT NULL COMMENT 'ID del documento SST que genero este indicador'");
            echo "AGREGADA\n";
        } else {
            echo "Ya existe (OK)\n";
        }

        // 4. Marcar tipos como programas
        echo "4. Marcando tipos de documento como programas... ";
        $pdo->exec("
            UPDATE tbl_doc_tipo_configuracion
            SET categoria = 'programas'
            WHERE tipo_documento IN (
                'programa_capacitacion',
                'programa_induccion_reinduccion',
                'programa_promocion_prevencion_salud',
                'programa_estilos_vida_saludables',
                'programa_seguridad',
                'programa_inspecciones',
                'programa_mantenimiento',
                'plan_emergencias',
                'programa_brigada'
            )
        ");
        echo "OK\n";

        // 5. Marcar secciones de actividades para sincronizar con PTA
        echo "5. Marcando secciones de actividades para PTA... ";
        $pdo->exec("
            UPDATE tbl_doc_secciones_config sc
            JOIN tbl_doc_tipo_configuracion tc ON sc.id_tipo_config = tc.id_tipo_config
            SET sc.sincronizar_bd = 'pta_cliente'
            WHERE tc.categoria = 'programas'
            AND sc.seccion_key IN ('cronograma', 'actividades', 'plan_trabajo')
        ");
        echo "OK\n";

        // 6. Marcar secciones de indicadores para sincronizar
        echo "6. Marcando secciones de indicadores... ";
        $pdo->exec("
            UPDATE tbl_doc_secciones_config sc
            JOIN tbl_doc_tipo_configuracion tc ON sc.id_tipo_config = tc.id_tipo_config
            SET sc.sincronizar_bd = 'indicadores_sst'
            WHERE tc.categoria = 'programas'
            AND sc.seccion_key = 'indicadores'
        ");
        echo "OK\n";

        // Verificacion
        echo "\n--- Verificacion ---\n";

        // Verificar programas
        $stmt = $pdo->query("
            SELECT tipo_documento, nombre, categoria, estandar
            FROM tbl_doc_tipo_configuracion
            WHERE categoria = 'programas'
            ORDER BY estandar
        ");
        $programas = $stmt->fetchAll();
        echo "\nProgramas configurados: " . count($programas) . "\n";
        foreach ($programas as $p) {
            echo "  - [{$p['estandar']}] {$p['tipo_documento']}: {$p['nombre']}\n";
        }

        // Verificar secciones con sincronizacion
        $stmt = $pdo->query("
            SELECT tc.tipo_documento, tc.estandar, sc.seccion_key, sc.sincronizar_bd
            FROM tbl_doc_secciones_config sc
            JOIN tbl_doc_tipo_configuracion tc ON sc.id_tipo_config = tc.id_tipo_config
            WHERE sc.sincronizar_bd IS NOT NULL
            ORDER BY tc.estandar, sc.orden
        ");
        $secciones = $stmt->fetchAll();
        echo "\nSecciones con sincronizacion: " . count($secciones) . "\n";
        foreach ($secciones as $s) {
            echo "  - [{$s['estandar']}] {$s['tipo_documento']}.{$s['seccion_key']} -> {$s['sincronizar_bd']}\n";
        }

        // Verificar todos los tipos de documento existentes
        $stmt = $pdo->query("
            SELECT tipo_documento, nombre, categoria, estandar
            FROM tbl_doc_tipo_configuracion
            ORDER BY estandar
        ");
        $todos = $stmt->fetchAll();
        echo "\nTodos los tipos de documento configurados: " . count($todos) . "\n";
        foreach ($todos as $t) {
            $cat = $t['categoria'] ?? 'sin categoria';
            echo "  - [{$t['estandar']}] {$t['tipo_documento']} ({$cat})\n";
        }

        return true;

    } catch (PDOException $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
        return false;
    }
}

// Ejecutar automaticamente en ambos entornos
echo "===========================================\n";
echo "AGREGAR SOPORTE SINCRONIZACION PROGRAMAS v2\n";
echo "===========================================\n";

// Ejecutar en LOCAL
ejecutarEnEntorno($conexiones['local'], 'LOCAL');

// Ejecutar en PRODUCCION
ejecutarEnEntorno($conexiones['produccion'], 'PRODUCCION');

echo "\n========================================\n";
echo "Proceso completado en AMBOS entornos\n";
echo "========================================\n";
