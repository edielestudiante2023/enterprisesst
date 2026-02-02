<?php
/**
 * Script para agregar tipos de documento adicionales a la BD
 *
 * Ejecutar: php app/SQL/agregar_tipos_documento_bd.php
 *
 * Este script agrega:
 * - programa_capacitacion (con sus 13 secciones)
 * - Otros tipos de documento comunes del SG-SST
 */

// Configuración de conexiones
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

// SQL para insertar programa_capacitacion
$sqlProgramaCapacitacion = <<<'SQL'
-- Insertar tipo de documento: programa_capacitacion
INSERT INTO tbl_doc_tipo_configuracion
(tipo_documento, nombre, descripcion, estandar, flujo, categoria, icono, orden)
VALUES
('programa_capacitacion',
 'Programa de Capacitación en SST',
 'Documento formal del programa de capacitación y entrenamiento en Seguridad y Salud en el Trabajo',
 '3.1.1',
 'secciones_ia',
 'programas',
 'bi-mortarboard',
 2)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), updated_at = NOW();
SQL;

$sqlSeccionesCapacitacion = <<<'SQL'
-- Insertar secciones del programa de capacitación
INSERT INTO tbl_doc_secciones_config
(id_tipo_config, numero, nombre, seccion_key, tipo_contenido, tabla_dinamica_tipo, orden, prompt_ia)
SELECT
    tc.id_tipo_config,
    s.numero,
    s.nombre,
    s.seccion_key,
    s.tipo_contenido,
    s.tabla_dinamica_tipo,
    s.orden,
    s.prompt_ia
FROM tbl_doc_tipo_configuracion tc
CROSS JOIN (
    SELECT 1 as numero, 'Introducción' as nombre, 'introduccion' as seccion_key, 'texto' as tipo_contenido, NULL as tabla_dinamica_tipo, 1 as orden, 'Genera una introducción para el Programa de Capacitación en SST que incluya: justificación de por qué la empresa necesita este programa, contexto de la actividad económica y sus riesgos, mención del marco normativo (Decreto 1072/2015, Resolución 0312/2019), compromiso de la alta dirección. Ajusta la extensión según el tamaño de empresa.' as prompt_ia
    UNION SELECT 2, 'Objetivo General', 'objetivo_general', 'texto', NULL, 2, 'Genera el objetivo general del Programa de Capacitación. Debe ser un objetivo SMART (específico, medible, alcanzable, relevante, temporal) relacionado con la capacitación en SST.'
    UNION SELECT 3, 'Objetivos Específicos', 'objetivos_especificos', 'texto', NULL, 3, 'Genera los objetivos específicos del programa. Para 7 estándares: 2-3 objetivos básicos. Para 21 estándares: 3-4 objetivos. Para 60 estándares: 4-5 objetivos. Deben ser SMART y relacionados con los peligros identificados.'
    UNION SELECT 4, 'Alcance', 'alcance', 'texto', NULL, 4, 'Define el alcance del programa especificando: a quién aplica (trabajadores directos, contratistas), áreas o procesos cubiertos, sedes incluidas. Máximo 5-6 ítems para 7 est, 8 ítems para 21 est, 10 ítems para 60 est.'
    UNION SELECT 5, 'Marco Legal', 'marco_legal', 'texto', NULL, 5, 'Lista el marco normativo aplicable al programa. Para 7 estándares: MÁXIMO 4-5 normas. Para 21 estándares: MÁXIMO 6-8 normas. Para 60 estándares: según aplique. NO uses tablas Markdown.'
    UNION SELECT 6, 'Definiciones', 'definiciones', 'texto', NULL, 6, 'Genera un glosario de términos técnicos. Para 7 estándares: MÁXIMO 8 términos esenciales. Para 21 estándares: MÁXIMO 12 términos. Para 60 estándares: 12-15 términos. Definiciones basadas en normativa colombiana.'
    UNION SELECT 7, 'Responsabilidades', 'responsabilidades', 'texto', NULL, 7, 'Define los roles y responsabilidades. Para 7 estándares: SOLO 3-4 roles (Representante Legal, Responsable SST, VIGÍA SST -no COPASST-, Trabajadores). Para 21 estándares: 5-6 roles (incluye COPASST). Para 60 estándares: todos los roles necesarios. Si son 7 estándares, NUNCA mencionar COPASST.'
    UNION SELECT 8, 'Metodología', 'metodologia', 'texto', NULL, 8, 'Describe la metodología de capacitación incluyendo: tipos de capacitación (teórica, práctica), métodos de enseñanza, materiales y recursos, evaluación del aprendizaje.'
    UNION SELECT 9, 'Cronograma de Capacitaciones', 'cronograma', 'mixto', 'cronograma_capacitacion', 9, 'Genera un párrafo introductorio sobre el cronograma de capacitaciones. NO generes la tabla, se inserta automáticamente desde el sistema.'
    UNION SELECT 10, 'Plan de Trabajo Anual', 'plan_trabajo', 'mixto', 'plan_trabajo', 10, 'Resume las actividades del Plan de Trabajo Anual relacionadas con capacitación. NO generes tabla, se inserta automáticamente.'
    UNION SELECT 11, 'Indicadores', 'indicadores', 'mixto', 'indicadores_capacitacion', 11, 'Genera un párrafo introductorio sobre los indicadores. Para 7 estándares: 2-3 indicadores simples. Para 21 estándares: 4-5 indicadores. Para 60 estándares: 6-8 indicadores. NO generes tabla.'
    UNION SELECT 12, 'Recursos', 'recursos', 'texto', NULL, 12, 'Identifica los recursos necesarios para el programa. Para 7 estándares: recursos MÍNIMOS. Para 21 estándares: recursos moderados. Para 60 estándares: recursos completos. Categorías: Humanos, Físicos, Financieros.'
    UNION SELECT 13, 'Evaluación y Seguimiento', 'evaluacion', 'texto', NULL, 13, 'Define el mecanismo de seguimiento y evaluación. Para 7 estándares: seguimiento TRIMESTRAL o SEMESTRAL. Para 21 estándares: seguimiento BIMESTRAL o TRIMESTRAL. Para 60 estándares: según complejidad. Incluye criterios de evaluación y responsables.'
) s
WHERE tc.tipo_documento = 'programa_capacitacion'
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), prompt_ia = VALUES(prompt_ia);
SQL;

$sqlFirmantesCapacitacion = <<<'SQL'
-- Insertar firmantes del programa de capacitación
INSERT INTO tbl_doc_firmantes_config
(id_tipo_config, firmante_tipo, rol_display, columna_encabezado, orden, mostrar_licencia)
SELECT
    tc.id_tipo_config,
    f.firmante_tipo,
    f.rol_display,
    f.columna_encabezado,
    f.orden,
    f.mostrar_licencia
FROM tbl_doc_tipo_configuracion tc
CROSS JOIN (
    SELECT 'responsable_sst' as firmante_tipo, 'Elaboró' as rol_display, 'Elaboró / Responsable del SG-SST' as columna_encabezado, 1 as orden, 1 as mostrar_licencia
    UNION SELECT 'delegado_sst', 'Revisó', 'Revisó / Delegado SST', 2, 0
    UNION SELECT 'representante_legal', 'Aprobó', 'Aprobó / Representante Legal', 3, 0
) f
WHERE tc.tipo_documento = 'programa_capacitacion'
ON DUPLICATE KEY UPDATE rol_display = VALUES(rol_display), columna_encabezado = VALUES(columna_encabezado);
SQL;

// SQL para insertar tablas dinámicas adicionales
$sqlTablasDinamicas = <<<'SQL'
-- Insertar tablas dinámicas para programa de capacitación
INSERT INTO tbl_doc_tablas_dinamicas
(tabla_key, nombre, descripcion, query_base, columnas, filtro_cliente, estilo_encabezado)
VALUES
('cronograma_capacitacion',
 'Cronograma de Capacitaciones',
 'Tabla con el cronograma de capacitaciones del cliente',
 'SELECT tema, responsable_capacitacion as responsable, DATE_FORMAT(fecha_programada, "%d/%m/%Y") as fecha_programada, duracion, publico_objetivo FROM tbl_cronograma_capacitacion WHERE id_cliente = :id_cliente AND anio = YEAR(CURDATE()) ORDER BY fecha_programada',
 '[{"key": "tema", "titulo": "Tema", "ancho": "auto", "alineacion": "left"}, {"key": "responsable", "titulo": "Responsable", "ancho": "100px", "alineacion": "left"}, {"key": "fecha_programada", "titulo": "Fecha", "ancho": "80px", "alineacion": "center"}, {"key": "duracion", "titulo": "Duración", "ancho": "60px", "alineacion": "center"}, {"key": "publico_objetivo", "titulo": "Dirigido a", "ancho": "100px", "alineacion": "left"}]',
 1, 'info'),
('plan_trabajo',
 'Plan de Trabajo Anual',
 'Actividades del PTA relacionadas con capacitación',
 'SELECT actividad, responsable, ciclo_phva, estado FROM tbl_pta WHERE id_cliente = :id_cliente AND anio = YEAR(CURDATE()) AND categoria = "capacitacion" ORDER BY fecha_inicio',
 '[{"key": "actividad", "titulo": "Actividad", "ancho": "auto", "alineacion": "left"}, {"key": "responsable", "titulo": "Responsable", "ancho": "100px", "alineacion": "left"}, {"key": "ciclo_phva", "titulo": "Ciclo", "ancho": "50px", "alineacion": "center"}, {"key": "estado", "titulo": "Estado", "ancho": "70px", "alineacion": "center", "format": "estado"}]',
 1, 'warning'),
('indicadores_capacitacion',
 'Indicadores de Capacitación',
 'Indicadores de gestión para capacitación',
 'SELECT nombre, formula, meta, periodicidad FROM tbl_indicadores_sst WHERE id_cliente = :id_cliente AND categoria = "capacitacion" AND activo = 1 ORDER BY nombre',
 '[{"key": "nombre", "titulo": "Indicador", "ancho": "auto", "alineacion": "left"}, {"key": "formula", "titulo": "Fórmula", "ancho": "150px", "alineacion": "left"}, {"key": "meta", "titulo": "Meta", "ancho": "60px", "alineacion": "center"}, {"key": "periodicidad", "titulo": "Periodicidad", "ancho": "80px", "alineacion": "center"}]',
 1, 'success')
ON DUPLICATE KEY UPDATE query_base = VALUES(query_base), columnas = VALUES(columnas);
SQL;

// SQL para insertar más tipos de documentos comunes del SG-SST
$sqlMasTipos = <<<'SQL'
-- Insertar más tipos de documentos del SG-SST
INSERT INTO tbl_doc_tipo_configuracion
(tipo_documento, nombre, descripcion, estandar, flujo, categoria, icono, orden)
VALUES
('politica_sst',
 'Política de Seguridad y Salud en el Trabajo',
 'Declaración de la alta dirección sobre su compromiso con la SST',
 '1.1.1',
 'secciones_ia',
 'politicas',
 'bi-shield-check',
 3),
('matriz_requisitos_legales',
 'Matriz de Requisitos Legales',
 'Identificación y evaluación de requisitos legales aplicables al SG-SST',
 '1.2.1',
 'formulario',
 'matrices',
 'bi-list-check',
 4),
('plan_emergencias',
 'Plan de Prevención, Preparación y Respuesta ante Emergencias',
 'Documento que establece los procedimientos para la atención de emergencias',
 '5.1.1',
 'secciones_ia',
 'planes',
 'bi-exclamation-triangle',
 5),
('procedimiento_investigacion_incidentes',
 'Procedimiento de Investigación de Incidentes y Accidentes',
 'Metodología para investigar y analizar incidentes, accidentes y enfermedades laborales',
 '4.2.1',
 'secciones_ia',
 'procedimientos',
 'bi-search',
 6),
('reglamento_higiene_seguridad',
 'Reglamento de Higiene y Seguridad Industrial',
 'Reglamento interno que establece las normas de higiene y seguridad',
 '1.1.2',
 'secciones_ia',
 'reglamentos',
 'bi-clipboard-check',
 7)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), updated_at = NOW();
SQL;

// Función para ejecutar migración
function ejecutarMigracion($nombre, $config, $sqls) {
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "EJECUTANDO: $nombre\n";
    echo str_repeat("=", 60) . "\n";

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
        echo "Conectado a {$config['host']}:{$config['port']}\n\n";

        foreach ($sqls as $descripcion => $sql) {
            echo "Ejecutando: $descripcion... ";
            try {
                $pdo->exec($sql);
                echo "OK\n";
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'already exists') !== false ||
                    strpos($e->getMessage(), 'Duplicate') !== false) {
                    echo "(ya existe)\n";
                } else {
                    echo "ERROR: " . $e->getMessage() . "\n";
                }
            }
        }

        // Verificar datos insertados
        echo "\nVerificacion:\n";

        $stmt = $pdo->query("SELECT tipo_documento, nombre FROM tbl_doc_tipo_configuracion WHERE activo = 1 ORDER BY orden");
        echo "\nTipos de documento configurados:\n";
        while ($row = $stmt->fetch()) {
            echo "  - {$row['tipo_documento']}: {$row['nombre']}\n";
        }

        $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_doc_secciones_config");
        $count = $stmt->fetch()['total'];
        echo "\nTotal secciones configuradas: $count\n";

        $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_doc_firmantes_config");
        $count = $stmt->fetch()['total'];
        echo "Total firmantes configurados: $count\n";

        $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_doc_tablas_dinamicas");
        $count = $stmt->fetch()['total'];
        echo "Total tablas dinamicas: $count\n";

        echo "\nMigracion de $nombre COMPLETADA\n";
        return true;

    } catch (PDOException $e) {
        echo "Error de conexion: " . $e->getMessage() . "\n";
        return false;
    }
}

// Array de SQLs a ejecutar
$sqls = [
    'Tipo: programa_capacitacion' => $sqlProgramaCapacitacion,
    'Secciones: programa_capacitacion' => $sqlSeccionesCapacitacion,
    'Firmantes: programa_capacitacion' => $sqlFirmantesCapacitacion,
    'Tablas dinamicas adicionales' => $sqlTablasDinamicas,
    'Mas tipos de documentos' => $sqlMasTipos
];

echo "\n";
echo "========================================================\n";
echo "  AGREGAR TIPOS DE DOCUMENTO A LA BD v1.0\n";
echo "========================================================\n";

// Ejecutar en LOCAL
$resultadoLocal = ejecutarMigracion('LOCAL', $conexiones['local'], $sqls);

// Preguntar si ejecutar en producción
echo "\n";
echo "========================================================\n";
echo "RESUMEN\n";
echo "========================================================\n";
echo "LOCAL: " . ($resultadoLocal ? "OK" : "FALLO") . "\n";

if ($resultadoLocal) {
    echo "\nPara ejecutar en PRODUCCION, configure las variables de entorno:\n";
    echo "  DB_PROD_HOST, DB_PROD_PORT, DB_PROD_DATABASE, DB_PROD_USERNAME, DB_PROD_PASSWORD\n";
    echo "Y ejecute el script nuevamente.\n";
}
