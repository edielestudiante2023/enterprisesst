<?php
/**
 * Script para agregar tipo de documento: Identificación de Trabajadores de Alto Riesgo
 * Estándar: 1.1.5
 *
 * Ejecutar: php app/SQL/agregar_identificacion_alto_riesgo.php
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

function ejecutar($nombre, $config) {
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "EJECUTANDO EN: $nombre\n";
    echo str_repeat("=", 60) . "\n";

    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
        $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

        if ($config['ssl']) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = true;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        echo "Conectado a {$config['host']}\n\n";

        // 1. Insertar tipo de documento
        echo "1. Insertando tipo de documento... ";
        $pdo->exec("
            INSERT INTO tbl_doc_tipo_configuracion
            (tipo_documento, nombre, descripcion, estandar, flujo, categoria, icono, orden)
            VALUES
            ('identificacion_alto_riesgo',
             'Identificación de Trabajadores de Alto Riesgo y Cotización de Pensión Especial',
             'Metodología para identificar trabajadores que desarrollen actividades clasificadas como de alto riesgo conforme al Decreto 2090 de 2003',
             '1.1.5',
             'secciones_ia',
             'procedimientos',
             'bi-exclamation-diamond',
             9)
            ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), updated_at = NOW()
        ");
        echo "OK\n";

        // 2. Obtener ID del tipo
        $idTipo = $pdo->query("SELECT id_tipo_config FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'identificacion_alto_riesgo'")->fetchColumn();
        echo "   ID tipo: $idTipo\n";

        // 3. Insertar secciones
        echo "2. Insertando secciones... ";

        $secciones = [
            [1, 'Objetivo', 'objetivo', 'texto', null,
             'Genera el objetivo del procedimiento para identificar trabajadores de alto riesgo conforme al Decreto 2090 de 2003 y garantizar la cotización a pensión especial cuando aplique. Debe ser conciso y referir la normativa.'],

            [2, 'Alcance', 'alcance', 'texto', null,
             'Define el alcance: aplica a todos los cargos y puestos de trabajo, personal directo, temporal o tercerizado cuando aplique.'],

            [3, 'Marco Normativo', 'marco_normativo', 'texto', null,
             'Lista el marco normativo: Decreto 2090 de 2003, Resolución 0312 de 2019, Decreto 1072 de 2015. Incluir nota: este procedimiento no crea requisitos adicionales a la norma.'],

            [4, 'Definiciones Clave', 'definiciones', 'texto', null,
             'Define: Actividad de alto riesgo (según Decreto 2090/2003), Pensión especial (cotización adicional para trabajadores de alto riesgo). Máximo 4-5 definiciones relevantes.'],

            [5, 'Responsabilidades', 'responsabilidades', 'texto', null,
             'Define responsabilidades por rol: Empleador (garantizar cumplimiento), Responsable SG-SST (ejecutar identificación), Talento Humano/Nómina (aplicar cotización), ARL (emitir conceptos técnicos). Usar formato de tabla o lista.'],

            [6, 'Identificación de Cargos y Actividades', 'identificacion_cargos', 'texto', null,
             'Paso 1: Describir revisión de organigrama, perfiles de cargo y descripción real de actividades. Indicar evidencias obligatorias: listado de cargos analizados, perfil de cargo.'],

            [7, 'Análisis frente al Decreto 2090 de 2003', 'analisis_decreto', 'texto', null,
             'Paso 2: Describir cómo el responsable SG-SST compara actividades reales con las clasificadas como alto riesgo en el Decreto 2090/2003. Indicar que el análisis es por actividad, no solo por nombre del cargo. Evidencias: matriz de análisis normativo.'],

            [8, 'Determinación de Aplicabilidad', 'determinacion', 'texto', null,
             'Paso 3: Describir cómo se concluye si aplica o no alto riesgo. Indicar evidencias: documento técnico de identificación con conclusión justificada, firma y fecha.'],

            [9, 'Gestión de Cotización Especial', 'cotizacion_especial', 'texto', null,
             'Paso 4: Describir proceso cuando se identifican trabajadores de alto riesgo: informar a Talento Humano, verificar cotización PILA, validar afiliación al fondo de pensiones. Evidencias: soporte PILA, certificación fondo, listado de trabajadores.'],

            [10, 'Registros y Evidencias', 'registros', 'texto', null,
             'Resume todos los registros y evidencias que genera este procedimiento: listado de cargos, matriz de análisis, documento técnico de identificación, soportes PILA (si aplica).'],
        ];

        $stmt = $pdo->prepare("
            INSERT INTO tbl_doc_secciones_config
            (id_tipo_config, numero, nombre, seccion_key, tipo_contenido, tabla_dinamica_tipo, orden, prompt_ia)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), prompt_ia = VALUES(prompt_ia)
        ");

        foreach ($secciones as $s) {
            $stmt->execute([$idTipo, $s[0], $s[1], $s[2], $s[3], $s[4], $s[0], $s[5]]);
        }
        echo "OK (" . count($secciones) . " secciones)\n";

        // 4. Insertar firmantes
        echo "3. Insertando firmantes... ";

        $firmantes = [
            ['responsable_sst', 'Elaboró', 'Elaboró / Responsable del SG-SST', 1, 1],
            ['representante_legal', 'Aprobó', 'Aprobó / Representante Legal', 2, 0],
        ];

        $stmtF = $pdo->prepare("
            INSERT INTO tbl_doc_firmantes_config
            (id_tipo_config, firmante_tipo, rol_display, columna_encabezado, orden, mostrar_licencia)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE rol_display = VALUES(rol_display)
        ");

        foreach ($firmantes as $f) {
            $stmtF->execute([$idTipo, $f[0], $f[1], $f[2], $f[3], $f[4]]);
        }
        echo "OK (" . count($firmantes) . " firmantes)\n";

        // 5. Insertar plantilla
        echo "4. Insertando plantilla... ";
        $pdo->exec("
            INSERT INTO tbl_doc_plantillas (id_tipo, nombre, codigo_sugerido, tipo_documento, version, activo)
            VALUES (1, 'Identificación de Trabajadores de Alto Riesgo', 'PR-SST-AR', 'identificacion_alto_riesgo', '001', 1)
            ON DUPLICATE KEY UPDATE nombre = VALUES(nombre)
        ");
        echo "OK\n";

        // 6. Mapear a carpeta
        echo "5. Mapeando a carpeta 1.1.5... ";
        $pdo->exec("
            INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
            VALUES ('PR-SST-AR', '1.1.5')
            ON DUPLICATE KEY UPDATE codigo_carpeta = VALUES(codigo_carpeta)
        ");
        echo "OK\n";

        // Verificar
        echo "\nVerificación:\n";
        $count = $pdo->query("SELECT COUNT(*) FROM tbl_doc_secciones_config WHERE id_tipo_config = $idTipo")->fetchColumn();
        echo "  - Secciones: $count\n";
        $count = $pdo->query("SELECT COUNT(*) FROM tbl_doc_firmantes_config WHERE id_tipo_config = $idTipo")->fetchColumn();
        echo "  - Firmantes: $count\n";

        echo "\n✅ Documento 'identificacion_alto_riesgo' creado en $nombre\n";
        return true;

    } catch (PDOException $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
        return false;
    }
}

echo "\n";
echo "========================================================\n";
echo "  CREAR: Identificación de Trabajadores de Alto Riesgo\n";
echo "  Estándar: 1.1.5 | Código: PR-SST-AR\n";
echo "========================================================\n";

// Ejecutar en LOCAL
$resultLocal = ejecutar('LOCAL', $conexiones['local']);

// Ejecutar en PRODUCCIÓN
$resultProd = ejecutar('PRODUCCIÓN', $conexiones['produccion']);

echo "\n========================================================\n";
echo "RESUMEN\n";
echo "========================================================\n";
echo "LOCAL:      " . ($resultLocal ? "✅ OK" : "❌ FALLÓ") . "\n";
echo "PRODUCCIÓN: " . ($resultProd ? "✅ OK" : "❌ FALLÓ") . "\n";
echo "========================================================\n";

// Limpiar credenciales para el commit
echo "\n⚠️  IMPORTANTE: Recuerda limpiar las credenciales antes de hacer commit.\n";
