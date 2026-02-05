<?php
/**
 * Script para agregar tipo de documento: Procedimiento Matriz de Requisitos Legales
 * Estandar: 2.7.1
 * Ejecutar: php app/SQL/agregar_procedimiento_matriz_legal.php
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
        'password' => 'AVNS_iDypWizlpMRwHIORJGG',
        'ssl' => true
    ]
];

// SQL para tipo de documento
$sqlTipo = <<<'SQL'
INSERT INTO tbl_doc_tipo_configuracion
(tipo_documento, nombre, descripcion, estandar, flujo, categoria, icono, orden)
VALUES
('procedimiento_matriz_legal',
 'Procedimiento para Identificacion de Requisitos Legales',
 'Establece la metodologia para identificar, acceder y mantener actualizados los requisitos legales y de otra indole aplicables en SST',
 '2.7.1',
 'secciones_ia',
 'procedimientos',
 'bi-journal-bookmark',
 15)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), descripcion = VALUES(descripcion), updated_at = NOW();
SQL;

// SQL para secciones
$sqlSecciones = <<<'SQL'
INSERT INTO tbl_doc_secciones_config
(id_tipo_config, numero, nombre, seccion_key, tipo_contenido, orden, prompt_ia)
SELECT tc.id_tipo_config, s.numero, s.nombre, s.seccion_key, s.tipo_contenido, s.orden, s.prompt_ia
FROM tbl_doc_tipo_configuracion tc
CROSS JOIN (
    SELECT 1 as numero, 'Objetivo' as nombre, 'objetivo' as seccion_key, 'texto' as tipo_contenido, 1 as orden,
           'Genera el objetivo del procedimiento para identificar requisitos legales en SST. Debe mencionar el cumplimiento del Decreto 1072 de 2015 y la Resolucion 0312 de 2019.' as prompt_ia
    UNION SELECT 2, 'Alcance', 'alcance', 'texto', 2,
           'Define el alcance del procedimiento. Aplica a todos los requisitos legales (leyes, decretos, resoluciones, circulares) y otros requisitos (normas tecnicas, acuerdos contractuales) relacionados con SST.'
    UNION SELECT 3, 'Definiciones', 'definiciones', 'texto', 3,
           'Define los terminos clave: Requisito Legal, Matriz Legal, Normativa Vigente, Requisitos de otra indole, Cumplimiento legal, Autoridad competente.'
    UNION SELECT 4, 'Responsabilidades', 'responsabilidades', 'texto', 4,
           'Define los responsables: Alta Direccion (asegurar recursos), Responsable del SG-SST (mantener actualizada la matriz, comunicar requisitos), Trabajadores (cumplir requisitos aplicables).'
    UNION SELECT 5, 'Metodologia de Identificacion', 'metodologia', 'texto', 5,
           'Describe como se identifican los requisitos legales: fuentes de informacion (diario oficial, ministerios, ARL), criterios de seleccion (aplicabilidad al sector, actividad economica, peligros presentes), frecuencia de revision.'
    UNION SELECT 6, 'Evaluacion del Cumplimiento', 'evaluacion_cumplimiento', 'texto', 6,
           'Describe como se evalua el cumplimiento de los requisitos identificados: periodicidad (minimo anual), responsable, metodo de evaluacion, registro de hallazgos, acciones ante incumplimiento.'
    UNION SELECT 7, 'Comunicacion de Requisitos', 'comunicacion', 'texto', 7,
           'Describe como se comunican los requisitos legales a los trabajadores y partes interesadas: medios de comunicacion, frecuencia, registros de divulgacion.'
    UNION SELECT 8, 'Actualizacion de la Matriz', 'actualizacion', 'texto', 8,
           'Describe el proceso de actualizacion: cuando se actualiza (nueva normativa, cambios en procesos, resultados de auditorias), quien la actualiza, como se registran los cambios.'
) s
WHERE tc.tipo_documento = 'procedimiento_matriz_legal'
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), prompt_ia = VALUES(prompt_ia);
SQL;

// SQL para firmantes
$sqlFirmantes = <<<'SQL'
INSERT INTO tbl_doc_firmantes_config
(id_tipo_config, firmante_tipo, rol_display, columna_encabezado, orden, mostrar_licencia)
SELECT tc.id_tipo_config, f.firmante_tipo, f.rol_display, f.columna_encabezado, f.orden, f.mostrar_licencia
FROM tbl_doc_tipo_configuracion tc
CROSS JOIN (
    SELECT 'responsable_sst' as firmante_tipo, 'Elaboro' as rol_display, 'Elaboro / Responsable del SG-SST' as columna_encabezado, 1 as orden, 1 as mostrar_licencia
    UNION SELECT 'representante_legal', 'Aprobo', 'Aprobo / Representante Legal', 2, 0
) f
WHERE tc.tipo_documento = 'procedimiento_matriz_legal'
ON DUPLICATE KEY UPDATE rol_display = VALUES(rol_display);
SQL;

// SQL para plantilla
$sqlPlantilla = <<<'SQL'
INSERT INTO tbl_doc_plantillas (id_tipo, nombre, codigo_sugerido, tipo_documento, version, activo)
SELECT 3, 'Procedimiento Matriz de Requisitos Legales', 'PRC-MRL', 'procedimiento_matriz_legal', '001', 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM tbl_doc_plantillas WHERE tipo_documento = 'procedimiento_matriz_legal')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);
SQL;

// SQL para mapeo carpeta
$sqlMapeoCarpeta = <<<'SQL'
INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
VALUES ('PRC-MRL', '2.7.1')
ON DUPLICATE KEY UPDATE codigo_carpeta = VALUES(codigo_carpeta);
SQL;

function ejecutarSQL($pdo, $sql, $nombre) {
    try {
        $pdo->exec($sql);
        echo "  [OK] $nombre\n";
        return true;
    } catch (PDOException $e) {
        echo "  [ERROR] $nombre: " . $e->getMessage() . "\n";
        return false;
    }
}

foreach ($conexiones as $entorno => $config) {
    echo "\n=== Ejecutando en $entorno ===\n";

    if ($entorno === 'produccion' && empty($config['password'])) {
        echo "  [SKIP] Sin credenciales de produccion\n";
        continue;
    }

    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];

        if ($config['ssl']) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = true;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        echo "  [OK] Conexion establecida\n";

        // Ejecutar SQLs en orden
        ejecutarSQL($pdo, $sqlTipo, 'Tipo de documento');
        ejecutarSQL($pdo, $sqlSecciones, 'Secciones');
        ejecutarSQL($pdo, $sqlFirmantes, 'Firmantes');
        ejecutarSQL($pdo, $sqlPlantilla, 'Plantilla');
        ejecutarSQL($pdo, $sqlMapeoCarpeta, 'Mapeo carpeta');

        // Verificar resultado
        $stmt = $pdo->query("SELECT id_tipo_config, nombre FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'procedimiento_matriz_legal'");
        $tipo = $stmt->fetch();
        if ($tipo) {
            echo "  [INFO] Tipo creado con ID: {$tipo['id_tipo_config']}\n";

            $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_doc_secciones_config WHERE id_tipo_config = {$tipo['id_tipo_config']}");
            $secciones = $stmt->fetch();
            echo "  [INFO] Secciones configuradas: {$secciones['total']}\n";
        }

    } catch (PDOException $e) {
        echo "  [ERROR] Conexion: " . $e->getMessage() . "\n";
    }
}

echo "\n=== Proceso completado ===\n";
