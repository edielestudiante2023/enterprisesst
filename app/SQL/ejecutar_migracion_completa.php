<?php
/**
 * Migración completa: LOCAL + PRODUCCIÓN
 * Ejecutar: php app/SQL/ejecutar_migracion_completa.php
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

// SQL: Crear tablas
$sqlTablas = <<<'SQL'
CREATE TABLE IF NOT EXISTS tbl_doc_tipo_configuracion (
    id_tipo_config INT AUTO_INCREMENT PRIMARY KEY,
    tipo_documento VARCHAR(100) NOT NULL UNIQUE,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    estandar VARCHAR(20),
    flujo ENUM('secciones_ia', 'formulario', 'carga_archivo', 'mixto') DEFAULT 'secciones_ia',
    categoria VARCHAR(50),
    icono VARCHAR(50) DEFAULT 'bi-file-text',
    orden INT DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tipo_documento (tipo_documento),
    INDEX idx_categoria (categoria),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;

$sqlTablas2 = <<<'SQL'
CREATE TABLE IF NOT EXISTS tbl_doc_secciones_config (
    id_seccion_config INT AUTO_INCREMENT PRIMARY KEY,
    id_tipo_config INT NOT NULL,
    numero INT NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    seccion_key VARCHAR(100) NOT NULL,
    prompt_ia TEXT,
    tipo_contenido ENUM('texto', 'tabla_dinamica', 'lista', 'mixto') DEFAULT 'texto',
    tabla_dinamica_tipo VARCHAR(50),
    es_obligatoria TINYINT(1) DEFAULT 1,
    orden INT NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_tipo_seccion (id_tipo_config, seccion_key),
    INDEX idx_orden (id_tipo_config, orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;

$sqlTablas3 = <<<'SQL'
CREATE TABLE IF NOT EXISTS tbl_doc_firmantes_config (
    id_firmante_config INT AUTO_INCREMENT PRIMARY KEY,
    id_tipo_config INT NOT NULL,
    firmante_tipo ENUM('representante_legal', 'responsable_sst', 'consultor_sst', 'delegado_sst', 'vigia_sst', 'copasst', 'trabajador') NOT NULL,
    rol_display VARCHAR(100) NOT NULL,
    columna_encabezado VARCHAR(100) NOT NULL,
    orden INT NOT NULL,
    es_obligatorio TINYINT(1) DEFAULT 1,
    mostrar_licencia TINYINT(1) DEFAULT 0,
    mostrar_cedula TINYINT(1) DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_tipo_firmante (id_tipo_config, firmante_tipo),
    INDEX idx_orden (id_tipo_config, orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;

$sqlTablas4 = <<<'SQL'
CREATE TABLE IF NOT EXISTS tbl_doc_tablas_dinamicas (
    id_tabla_dinamica INT AUTO_INCREMENT PRIMARY KEY,
    tabla_key VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    query_base TEXT NOT NULL,
    columnas JSON NOT NULL,
    filtro_cliente TINYINT(1) DEFAULT 1,
    estilo_encabezado VARCHAR(50) DEFAULT 'primary',
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;

function ejecutar($nombre, $config, $sqls) {
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "EJECUTANDO: $nombre\n";
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

        foreach ($sqls as $desc => $sql) {
            echo "$desc... ";
            try {
                $pdo->exec($sql);
                echo "OK\n";
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'already exists') !== false || strpos($e->getMessage(), 'Duplicate') !== false) {
                    echo "(ya existe)\n";
                } else {
                    echo "ERROR: " . $e->getMessage() . "\n";
                }
            }
        }

        // Verificar
        echo "\nVerificacion:\n";
        $tablas = ['tbl_doc_tipo_configuracion', 'tbl_doc_secciones_config', 'tbl_doc_firmantes_config', 'tbl_doc_tablas_dinamicas'];
        foreach ($tablas as $t) {
            try {
                $stmt = $pdo->query("SELECT COUNT(*) as c FROM $t");
                echo "  $t: " . $stmt->fetch()['c'] . " registros\n";
            } catch (Exception $e) {
                echo "  $t: NO EXISTE\n";
            }
        }

        return $pdo;
    } catch (PDOException $e) {
        echo "ERROR conexion: " . $e->getMessage() . "\n";
        return null;
    }
}

function insertarDatos($pdo, $nombre) {
    echo "\nInsertando datos en $nombre...\n";

    // Tipos de documento
    $tipos = [
        ['procedimiento_control_documental', 'Procedimiento de Control Documental del SG-SST', 'Establece las directrices para control documental', '2.5.1', 'secciones_ia', 'procedimientos', 'bi-folder-check', 1],
        ['programa_capacitacion', 'Programa de Capacitación en SST', 'Documento formal del programa de capacitación', '3.1.1', 'secciones_ia', 'programas', 'bi-mortarboard', 2],
        ['politica_sst', 'Política de Seguridad y Salud en el Trabajo', 'Declaración de la alta dirección sobre SST', '1.1.1', 'secciones_ia', 'politicas', 'bi-shield-check', 3],
        ['matriz_requisitos_legales', 'Matriz de Requisitos Legales', 'Identificación de requisitos legales aplicables', '1.2.1', 'formulario', 'matrices', 'bi-list-check', 4],
        ['plan_emergencias', 'Plan de Prevención, Preparación y Respuesta ante Emergencias', 'Procedimientos para atención de emergencias', '5.1.1', 'secciones_ia', 'planes', 'bi-exclamation-triangle', 5],
        ['procedimiento_investigacion_incidentes', 'Procedimiento de Investigación de Incidentes y Accidentes', 'Metodología para investigar incidentes', '4.2.1', 'secciones_ia', 'procedimientos', 'bi-search', 6],
        ['reglamento_higiene_seguridad', 'Reglamento de Higiene y Seguridad Industrial', 'Reglamento interno de higiene y seguridad', '1.1.2', 'secciones_ia', 'reglamentos', 'bi-clipboard-check', 7],
    ];

    $stmt = $pdo->prepare("INSERT INTO tbl_doc_tipo_configuracion (tipo_documento, nombre, descripcion, estandar, flujo, categoria, icono, orden) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE nombre = VALUES(nombre)");

    foreach ($tipos as $t) {
        $stmt->execute($t);
    }
    echo "  Tipos de documento: OK\n";

    // Obtener IDs
    $idControlDoc = $pdo->query("SELECT id_tipo_config FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'procedimiento_control_documental'")->fetch()['id_tipo_config'];
    $idCapacitacion = $pdo->query("SELECT id_tipo_config FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'programa_capacitacion'")->fetch()['id_tipo_config'];

    // Secciones procedimiento_control_documental
    $seccionesCD = [
        [1, 'Objetivo', 'objetivo', 'texto', null, 'Genera el objetivo del procedimiento de control documental.'],
        [2, 'Alcance', 'alcance', 'texto', null, 'Define el alcance del procedimiento.'],
        [3, 'Definiciones', 'definiciones', 'texto', null, 'Genera las definiciones clave para control documental.'],
        [4, 'Marco Normativo', 'marco_normativo', 'texto', null, 'Lista el marco normativo aplicable.'],
        [5, 'Responsabilidades', 'responsabilidades', 'texto', null, 'Define responsabilidades en el control documental.'],
        [6, 'Tipos de Documentos del SG-SST', 'tipos_documentos', 'mixto', 'tipos_documento', 'Genera párrafo introductorio. NO generes tabla.'],
        [7, 'Estructura y Codificación', 'codificacion', 'mixto', 'plantillas', 'Genera párrafo explicando la codificación. NO generes ejemplos.'],
        [8, 'Elaboración de Documentos', 'elaboracion', 'texto', null, 'Genera el procedimiento para elaborar documentos.'],
        [9, 'Revisión y Aprobación', 'revision_aprobacion', 'texto', null, 'Genera el procedimiento de revisión y aprobación.'],
        [10, 'Distribución y Acceso', 'distribucion', 'texto', null, 'Genera el procedimiento de distribución.'],
        [11, 'Control de Cambios', 'control_cambios', 'texto', null, 'Genera el procedimiento de control de cambios.'],
        [12, 'Conservación y Retención', 'conservacion', 'texto', null, 'Genera el procedimiento de conservación (20 años).'],
        [13, 'Listado Maestro de Documentos', 'listado_maestro', 'mixto', 'listado_maestro', 'Genera párrafo introductorio sobre el listado maestro.'],
        [14, 'Disposición Final', 'disposicion_final', 'texto', null, 'Genera el procedimiento de disposición final.'],
    ];

    $stmtSec = $pdo->prepare("INSERT INTO tbl_doc_secciones_config (id_tipo_config, numero, nombre, seccion_key, tipo_contenido, tabla_dinamica_tipo, orden, prompt_ia) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE nombre = VALUES(nombre)");

    foreach ($seccionesCD as $s) {
        $stmtSec->execute([$idControlDoc, $s[0], $s[1], $s[2], $s[3], $s[4], $s[0], $s[5]]);
    }
    echo "  Secciones control documental: OK\n";

    // Secciones programa_capacitacion
    $seccionesCap = [
        [1, 'Introducción', 'introduccion', 'texto', null],
        [2, 'Objetivo General', 'objetivo_general', 'texto', null],
        [3, 'Objetivos Específicos', 'objetivos_especificos', 'texto', null],
        [4, 'Alcance', 'alcance', 'texto', null],
        [5, 'Marco Legal', 'marco_legal', 'texto', null],
        [6, 'Definiciones', 'definiciones', 'texto', null],
        [7, 'Responsabilidades', 'responsabilidades', 'texto', null],
        [8, 'Metodología', 'metodologia', 'texto', null],
        [9, 'Cronograma de Capacitaciones', 'cronograma', 'mixto', 'cronograma_capacitacion'],
        [10, 'Plan de Trabajo Anual', 'plan_trabajo', 'mixto', 'plan_trabajo'],
        [11, 'Indicadores', 'indicadores', 'mixto', 'indicadores_capacitacion'],
        [12, 'Recursos', 'recursos', 'texto', null],
        [13, 'Evaluación y Seguimiento', 'evaluacion', 'texto', null],
    ];

    foreach ($seccionesCap as $s) {
        $stmtSec->execute([$idCapacitacion, $s[0], $s[1], $s[2], $s[3], $s[4], $s[0], null]);
    }
    echo "  Secciones programa capacitacion: OK\n";

    // Firmantes
    $stmtFirm = $pdo->prepare("INSERT INTO tbl_doc_firmantes_config (id_tipo_config, firmante_tipo, rol_display, columna_encabezado, orden, mostrar_licencia) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE rol_display = VALUES(rol_display)");

    // Firmantes control documental (solo 2)
    $stmtFirm->execute([$idControlDoc, 'responsable_sst', 'Elaboró', 'Elaboró / Responsable del SG-SST', 1, 1]);
    $stmtFirm->execute([$idControlDoc, 'representante_legal', 'Aprobó', 'Aprobó / Representante Legal', 2, 0]);

    // Firmantes programa capacitacion (3)
    $stmtFirm->execute([$idCapacitacion, 'responsable_sst', 'Elaboró', 'Elaboró / Responsable del SG-SST', 1, 1]);
    $stmtFirm->execute([$idCapacitacion, 'delegado_sst', 'Revisó', 'Revisó / Delegado SST', 2, 0]);
    $stmtFirm->execute([$idCapacitacion, 'representante_legal', 'Aprobó', 'Aprobó / Representante Legal', 3, 0]);
    echo "  Firmantes: OK\n";

    // Tablas dinámicas
    $tablas = [
        ['tipos_documento', 'Tipos de Documentos', 'SELECT prefijo, nombre, descripcion FROM tbl_doc_tipos WHERE activo = 1 ORDER BY id_tipo', '[{"key":"prefijo","titulo":"Prefijo","ancho":"70px","alineacion":"center"},{"key":"nombre","titulo":"Tipo de Documento","ancho":"auto","alineacion":"left"},{"key":"descripcion","titulo":"Descripción","ancho":"auto","alineacion":"left"}]', 0, 'primary'],
        ['plantillas', 'Códigos de Documentos', 'SELECT codigo_sugerido, nombre FROM tbl_doc_plantillas WHERE activo = 1 AND tipo_documento IS NOT NULL ORDER BY codigo_sugerido', '[{"key":"codigo_sugerido","titulo":"Código","ancho":"100px","alineacion":"center"},{"key":"nombre","titulo":"Nombre del Documento","ancho":"auto","alineacion":"left"}]', 0, 'primary'],
        ['listado_maestro', 'Listado Maestro de Documentos', 'SELECT codigo, titulo, version, estado, created_at FROM tbl_documentos_sst WHERE id_cliente = :id_cliente AND estado IN ("aprobado","firmado","generado") ORDER BY codigo', '[{"key":"codigo","titulo":"Código","ancho":"80px","alineacion":"center"},{"key":"titulo","titulo":"Título","ancho":"auto","alineacion":"left"},{"key":"version","titulo":"Versión","ancho":"50px","alineacion":"center"},{"key":"estado","titulo":"Estado","ancho":"70px","alineacion":"center"},{"key":"created_at","titulo":"Fecha","ancho":"75px","alineacion":"center"}]', 1, 'success'],
        ['cronograma_capacitacion', 'Cronograma de Capacitaciones', 'SELECT tema, responsable_capacitacion as responsable, DATE_FORMAT(fecha_programada, "%d/%m/%Y") as fecha, duracion FROM tbl_cronograma_capacitacion WHERE id_cliente = :id_cliente AND anio = YEAR(CURDATE()) ORDER BY fecha_programada', '[{"key":"tema","titulo":"Tema","ancho":"auto","alineacion":"left"},{"key":"responsable","titulo":"Responsable","ancho":"100px","alineacion":"left"},{"key":"fecha","titulo":"Fecha","ancho":"80px","alineacion":"center"},{"key":"duracion","titulo":"Duración","ancho":"60px","alineacion":"center"}]', 1, 'info'],
        ['plan_trabajo', 'Plan de Trabajo Anual', 'SELECT actividad, responsable, ciclo_phva, estado FROM tbl_pta WHERE id_cliente = :id_cliente AND anio = YEAR(CURDATE()) ORDER BY fecha_inicio', '[{"key":"actividad","titulo":"Actividad","ancho":"auto","alineacion":"left"},{"key":"responsable","titulo":"Responsable","ancho":"100px","alineacion":"left"},{"key":"ciclo_phva","titulo":"Ciclo","ancho":"50px","alineacion":"center"},{"key":"estado","titulo":"Estado","ancho":"70px","alineacion":"center"}]', 1, 'warning'],
        ['indicadores_capacitacion', 'Indicadores de Capacitación', 'SELECT nombre, formula, meta, periodicidad FROM tbl_indicadores_sst WHERE id_cliente = :id_cliente AND categoria = "capacitacion" AND activo = 1', '[{"key":"nombre","titulo":"Indicador","ancho":"auto","alineacion":"left"},{"key":"formula","titulo":"Fórmula","ancho":"150px","alineacion":"left"},{"key":"meta","titulo":"Meta","ancho":"60px","alineacion":"center"},{"key":"periodicidad","titulo":"Periodicidad","ancho":"80px","alineacion":"center"}]', 1, 'success'],
    ];

    $stmtTabla = $pdo->prepare("INSERT INTO tbl_doc_tablas_dinamicas (tabla_key, nombre, query_base, columnas, filtro_cliente, estilo_encabezado) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE query_base = VALUES(query_base)");

    foreach ($tablas as $t) {
        $stmtTabla->execute($t);
    }
    echo "  Tablas dinamicas: OK\n";

    echo "\nDatos insertados correctamente en $nombre\n";
}

// ========== EJECUCIÓN ==========
echo "\n";
echo "========================================================\n";
echo "  MIGRACION COMPLETA: LOCAL + PRODUCCION\n";
echo "========================================================\n";

$sqlsTablas = [
    'Tabla tbl_doc_tipo_configuracion' => $sqlTablas,
    'Tabla tbl_doc_secciones_config' => $sqlTablas2,
    'Tabla tbl_doc_firmantes_config' => $sqlTablas3,
    'Tabla tbl_doc_tablas_dinamicas' => $sqlTablas4,
];

// LOCAL
$pdoLocal = ejecutar('LOCAL', $conexiones['local'], $sqlsTablas);
if ($pdoLocal) insertarDatos($pdoLocal, 'LOCAL');

// PRODUCCIÓN
$pdoProd = ejecutar('PRODUCCION', $conexiones['produccion'], $sqlsTablas);
if ($pdoProd) insertarDatos($pdoProd, 'PRODUCCION');

echo "\n========================================================\n";
echo "  MIGRACION COMPLETADA\n";
echo "========================================================\n";
