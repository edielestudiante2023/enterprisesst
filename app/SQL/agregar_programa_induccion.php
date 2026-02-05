<?php
/**
 * Script para agregar módulo 1.2.2 - Programa de Inducción y Reinducción
 *
 * Ejecuta cambios en LOCAL y PRODUCCIÓN simultáneamente
 *
 * Ejecutar: php app/SQL/agregar_programa_induccion.php
 *
 * Crea:
 * - Tabla tbl_induccion_etapas
 * - Configuración en tbl_doc_tipo_configuracion
 * - Secciones en tbl_doc_secciones_config
 * - Firmantes en tbl_doc_firmantes_config
 * - Plantilla en tbl_doc_plantillas
 * - Mapeo en tbl_doc_plantilla_carpeta
 */

echo "=== MÓDULO 1.2.2 - PROGRAMA DE INDUCCIÓN Y REINDUCCIÓN ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

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
        'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port' => 25060,
        'database' => 'empresas_sst',
        'username' => 'cycloid_userdb',
        'password' => 'AVNS_iDypWizlpMRwHIORJGG',
        'ssl' => true
    ]
];

// ============================================================
// SQL 1: Crear tabla tbl_induccion_etapas
// ============================================================
$sqlCrearTabla = <<<'SQL'
CREATE TABLE IF NOT EXISTS tbl_induccion_etapas (
    id_etapa INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    numero_etapa INT NOT NULL COMMENT '1-5: Introducción, SST, Relaciones Laborales, Recorrido, Entrenamiento',
    nombre_etapa VARCHAR(100) NOT NULL,
    descripcion_etapa TEXT,
    temas JSON NOT NULL COMMENT 'Array de temas: [{nombre, descripcion, es_personalizado, origen_peligro}]',
    duracion_estimada_minutos INT DEFAULT 30,
    responsable_sugerido VARCHAR(100),
    recursos_requeridos TEXT,
    es_personalizado TINYINT(1) DEFAULT 0 COMMENT 'Si los temas fueron personalizados por IA',
    anio INT NOT NULL,
    estado ENUM('borrador', 'aprobado', 'ejecutado') DEFAULT 'borrador',
    fecha_aprobacion DATETIME NULL,
    aprobado_por INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_cliente_etapa_anio (id_cliente, numero_etapa, anio),
    INDEX idx_cliente_anio (id_cliente, anio),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Etapas del proceso de inducción y reinducción por cliente';
SQL;

// ============================================================
// SQL 2: Insertar tipo de documento en tbl_doc_tipo_configuracion
// ============================================================
$sqlTipoDocumento = <<<'SQL'
INSERT INTO tbl_doc_tipo_configuracion
(tipo_documento, nombre, descripcion, estandar, flujo, categoria, icono, orden, activo)
VALUES
('programa_induccion_reinduccion',
 'Programa de Inducción y Reinducción en SG-SST',
 'Programa que establece el proceso de inducción y reinducción para todos los trabajadores, incluyendo identificación de peligros, evaluación de riesgos y controles para prevención de ATEL.',
 '1.2.2',
 'programa_con_pta',
 'programas',
 'bi-person-badge',
 2,
 1)
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    descripcion = VALUES(descripcion),
    updated_at = NOW();
SQL;

// ============================================================
// SQL 3: Insertar secciones del documento
// ============================================================
$sqlSecciones = <<<'SQL'
INSERT INTO tbl_doc_secciones_config
(id_tipo_config, numero, nombre, seccion_key, tipo_contenido, tabla_dinamica_tipo, sincronizar_bd, es_obligatoria, orden, prompt_ia, activo)
SELECT
    tc.id_tipo_config,
    s.numero,
    s.nombre,
    s.seccion_key,
    s.tipo_contenido,
    s.tabla_dinamica_tipo,
    s.sincronizar_bd,
    s.es_obligatoria,
    s.orden,
    s.prompt_ia,
    1 as activo
FROM tbl_doc_tipo_configuracion tc
CROSS JOIN (
    SELECT 1 as numero, 'Objetivo' as nombre, 'objetivo' as seccion_key, 'texto' as tipo_contenido, NULL as tabla_dinamica_tipo, NULL as sincronizar_bd, 1 as es_obligatoria, 1 as orden,
           'Genera el objetivo del programa de inducción y reinducción para {empresa}. Debe mencionar que busca facilitar el conocimiento global de la empresa y el SG-SST al trabajador, mediante información sobre objetivos, metas, reglamentaciones, procedimientos y valores. Usa los datos del contexto del cliente.' as prompt_ia

    UNION SELECT 2, 'Alcance', 'alcance', 'texto', NULL, NULL, 1, 2,
           'Define el alcance del programa de inducción. Debe aplicarse: (1) Antes de iniciar labores después de vinculación legal, (2) Personal con cambio de cargo, (3) Personal que requiera reinducción, (4) Post-incapacidad por accidente de trabajo. Personaliza según el tipo de empresa y número de trabajadores del contexto.'

    UNION SELECT 3, 'Requisitos Generales', 'requisitos_generales', 'texto', NULL, NULL, 1, 3,
           'Describe los requisitos generales del proceso de inducción como parte fundamental de la formación y desarrollo del personal. Menciona que es el complemento del proceso de selección y el inicio de la etapa de socialización. Usa el nombre de la empresa del contexto.'

    UNION SELECT 4, 'Contenido: Esquema General del Proceso', 'contenido_esquema', 'tabla_dinamica', 'etapas_induccion', 'induccion_etapas', 1, 4,
           'Esta sección muestra las 5 etapas del proceso de inducción con sus temas. Los datos vienen de la tabla tbl_induccion_etapas.'

    UNION SELECT 5, 'Etapa 1: Introducción a la Empresa', 'etapa_introduccion', 'texto', NULL, NULL, 1, 5,
           'Genera el contenido de la Etapa 1 - Introducción. Debe incluir: Historia de la empresa, Principios y Valores, Misión y Visión, Ubicación y objetivos, Organigrama. Personaliza según los datos del contexto del cliente (razón social, sector económico, ciudad).'

    UNION SELECT 6, 'Etapa 2: Seguridad y Salud en el Trabajo', 'etapa_sst', 'texto', NULL, NULL, 1, 6,
           'Genera el contenido de la Etapa 2 - SST. IMPORTANTE: Incluye temas BASE (Política SST, Reglamento higiene, Plan emergencia, Derechos y deberes) + temas PERSONALIZADOS según los peligros_identificados del cliente. Si tiene trabajo en alturas, incluye ese tema. Si tiene riesgo químico, incluye manejo de sustancias. Menciona si tiene COPASST o Vigía según tiene_copasst/tiene_vigia_sst del contexto.'

    UNION SELECT 7, 'Etapa 3: Relaciones Laborales', 'etapa_relaciones', 'texto', NULL, NULL, 1, 7,
           'Genera el contenido de la Etapa 3 - Relaciones Laborales. Incluye: Reglamento Interno de Trabajo, Explicación de pago salarial, Horario laboral según turnos_trabajo del contexto, Prestaciones legales y extralegales.'

    UNION SELECT 8, 'Etapa 4: Conocimiento y Recorrido de Instalaciones', 'etapa_recorrido', 'texto', NULL, NULL, 1, 8,
           'Genera el contenido de la Etapa 4 - Recorrido. Incluye: Presentación del equipo de trabajo, Áreas administrativas, Áreas operativas/producción, Rutas de evacuación, Puntos de encuentro. Si el cliente tiene múltiples sedes (numero_sedes > 1), menciona que el recorrido se hace en la sede asignada.'

    UNION SELECT 9, 'Etapa 5: Entrenamiento al Cargo', 'etapa_entrenamiento', 'texto', NULL, NULL, 1, 9,
           'Genera el contenido de la Etapa 5 - Entrenamiento. Describe el proceso de entrenamiento en el puesto de trabajo y área específica. Menciona que incluye: funciones del cargo, procedimientos operativos, uso de herramientas/equipos, EPP requeridos según los peligros_identificados del cliente.'

    UNION SELECT 10, 'Entrega de Memorias', 'entrega_memorias', 'texto', NULL, NULL, 1, 10,
           'Genera la sección de entrega de memorias/documentación. Incluye documentos digitales (Política SST, Política no alcohol/drogas, Reglamento higiene, Responsabilidades SST, Derechos y deberes, Reglamento Interno) y documentos físicos (Copia contrato, Afiliación EPS, Carné ARL).'

    UNION SELECT 11, 'Evaluación y Control', 'evaluacion_control', 'texto', NULL, NULL, 1, 11,
           'Genera la sección de evaluación y control. Menciona: Formato de Control y Evaluación del Proceso de Inducción, responsable de evaluar, archivo en hoja de vida, entrega de copia al empleado, uso para indicador de cobertura.'

    UNION SELECT 12, 'Indicadores del Programa', 'indicadores', 'tabla_dinamica', 'indicadores_induccion', 'indicadores_sst', 1, 12,
           'Esta sección muestra los indicadores del programa de inducción. Los datos vienen de tbl_indicadores_sst donde categoria = induccion.'

    UNION SELECT 13, 'Cronograma de Actividades', 'cronograma', 'tabla_dinamica', 'pta_induccion', 'pta_cliente', 1, 13,
           'Esta sección muestra el cronograma de actividades del PTA relacionadas con inducción. Los datos vienen de tbl_pta_cliente donde tipo_servicio = Programa Induccion.'

) s
WHERE tc.tipo_documento = 'programa_induccion_reinduccion'
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    prompt_ia = VALUES(prompt_ia);
SQL;

// ============================================================
// SQL 4: Insertar firmantes del documento
// ============================================================
$sqlFirmantes = <<<'SQL'
INSERT INTO tbl_doc_firmantes_config
(id_tipo_config, firmante_tipo, rol_display, columna_encabezado, orden, es_obligatorio, mostrar_licencia, mostrar_cedula, activo)
SELECT
    tc.id_tipo_config,
    f.firmante_tipo,
    f.rol_display,
    f.columna_encabezado,
    f.orden,
    f.es_obligatorio,
    f.mostrar_licencia,
    f.mostrar_cedula,
    1 as activo
FROM tbl_doc_tipo_configuracion tc
CROSS JOIN (
    SELECT 'responsable_sst' as firmante_tipo,
           'Elaboró' as rol_display,
           'Elaboró / Responsable del SG-SST' as columna_encabezado,
           1 as orden,
           1 as es_obligatorio,
           1 as mostrar_licencia,
           0 as mostrar_cedula
    UNION SELECT 'representante_legal', 'Aprobó', 'Aprobó / Representante Legal', 2, 1, 0, 1
) f
WHERE tc.tipo_documento = 'programa_induccion_reinduccion'
ON DUPLICATE KEY UPDATE
    rol_display = VALUES(rol_display),
    columna_encabezado = VALUES(columna_encabezado);
SQL;

// ============================================================
// SQL 5: Insertar plantilla
// ============================================================
$sqlPlantilla = <<<'SQL'
INSERT INTO tbl_doc_plantillas
(id_tipo, nombre, codigo_sugerido, tipo_documento, version, activo)
SELECT
    3 as id_tipo,
    'Programa de Inducción y Reinducción' as nombre,
    'PRG-IND' as codigo_sugerido,
    'programa_induccion_reinduccion' as tipo_documento,
    '001' as version,
    1 as activo
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM tbl_doc_plantillas WHERE codigo_sugerido = 'PRG-IND'
)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);
SQL;

// ============================================================
// SQL 6: Mapear plantilla a carpeta 1.2.2
// ============================================================
$sqlMapeoCarpeta = <<<'SQL'
INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
VALUES ('PRG-IND', '1.2.2')
ON DUPLICATE KEY UPDATE codigo_carpeta = VALUES(codigo_carpeta);
SQL;

// ============================================================
// SQL 7: Insertar configuración de tabla dinámica para etapas
// ============================================================
$sqlTablaDinamica = <<<'SQL'
INSERT INTO tbl_doc_tablas_dinamicas
(tabla_key, nombre, descripcion, query_base, columnas, filtro_cliente, estilo_encabezado, activo)
VALUES
('etapas_induccion',
 'Etapas del Proceso de Inducción',
 'Muestra las 5 etapas del proceso de inducción con sus temas',
 'SELECT numero_etapa, nombre_etapa, descripcion_etapa, temas, duracion_estimada_minutos, responsable_sugerido FROM tbl_induccion_etapas WHERE id_cliente = :id_cliente AND anio = :anio AND estado IN (\'aprobado\', \'borrador\') ORDER BY numero_etapa',
 '[{"key": "numero_etapa", "label": "Etapa", "width": "60px"}, {"key": "nombre_etapa", "label": "Nombre", "width": "150px"}, {"key": "temas", "label": "Temas a Desarrollar", "type": "json_list"}, {"key": "duracion_estimada_minutos", "label": "Duración", "width": "80px", "suffix": " min"}, {"key": "responsable_sugerido", "label": "Responsable", "width": "120px"}]',
 1,
 'primary',
 1)
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    query_base = VALUES(query_base),
    columnas = VALUES(columnas);
SQL;

// ============================================================
// Función para ejecutar SQL en una conexión
// ============================================================
function ejecutarSQL($pdo, $sql, $descripcion, $entorno) {
    try {
        $pdo->exec($sql);
        echo "  ✅ [$entorno] $descripcion\n";
        return true;
    } catch (PDOException $e) {
        echo "  ❌ [$entorno] $descripcion\n";
        echo "     Error: " . $e->getMessage() . "\n";
        return false;
    }
}

// ============================================================
// Ejecutar en ambos entornos
// ============================================================
$resultados = ['local' => [], 'produccion' => []];

foreach ($conexiones as $entorno => $config) {
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "EJECUTANDO EN: " . strtoupper($entorno) . "\n";
    echo str_repeat("=", 50) . "\n";

    try {
        // Construir DSN
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ];

        // Agregar SSL para producción
        if ($config['ssl']) {
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
            $options[PDO::MYSQL_ATTR_SSL_CA] = false;
        }

        $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        echo "✅ Conexión establecida\n\n";

        // Ejecutar cada SQL
        $resultados[$entorno]['tabla'] = ejecutarSQL($pdo, $sqlCrearTabla, "Crear tabla tbl_induccion_etapas", $entorno);
        $resultados[$entorno]['tipo'] = ejecutarSQL($pdo, $sqlTipoDocumento, "Insertar tipo de documento", $entorno);
        $resultados[$entorno]['secciones'] = ejecutarSQL($pdo, $sqlSecciones, "Insertar secciones (13)", $entorno);
        $resultados[$entorno]['firmantes'] = ejecutarSQL($pdo, $sqlFirmantes, "Insertar firmantes (2)", $entorno);
        $resultados[$entorno]['plantilla'] = ejecutarSQL($pdo, $sqlPlantilla, "Insertar plantilla PRG-IND", $entorno);
        $resultados[$entorno]['mapeo'] = ejecutarSQL($pdo, $sqlMapeoCarpeta, "Mapear a carpeta 1.2.2", $entorno);
        $resultados[$entorno]['tabla_dinamica'] = ejecutarSQL($pdo, $sqlTablaDinamica, "Insertar tabla dinámica etapas", $entorno);

        // Verificar que se creó correctamente
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_doc_secciones_config WHERE id_tipo_config = (SELECT id_tipo_config FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'programa_induccion_reinduccion')");
        $row = $stmt->fetch();
        echo "\n📊 Verificación: {$row['total']} secciones configuradas\n";

    } catch (PDOException $e) {
        echo "❌ Error de conexión: " . $e->getMessage() . "\n";
        $resultados[$entorno]['conexion'] = false;
    }
}

// ============================================================
// Resumen final
// ============================================================
echo "\n" . str_repeat("=", 50) . "\n";
echo "RESUMEN DE EJECUCIÓN\n";
echo str_repeat("=", 50) . "\n";

foreach ($resultados as $entorno => $resultado) {
    $exitosos = count(array_filter($resultado));
    $total = count($resultado);
    $estado = $exitosos === $total ? "✅ COMPLETO" : "⚠️ PARCIAL";
    echo "$estado $entorno: $exitosos/$total operaciones exitosas\n";
}

echo "\n🎉 Script finalizado\n";
echo "Siguiente paso: Ejecutar las modificaciones en los archivos PHP\n";
