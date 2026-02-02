<?php
/**
 * Script para migrar arquitectura de documentos SST
 * Ejecuta en LOCAL y PRODUCCIÓN
 *
 * Ejecutar: php app/SQL/migrar_arquitectura_local_y_produccion.php
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

// SQL de migración
$sqlMigracion = <<<'SQL'
-- 1. TABLA: Configuración de Tipos de Documento
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. TABLA: Secciones por Tipo de Documento
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. TABLA: Configuración de Firmantes por Tipo de Documento
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. TABLA: Configuración de Tablas Dinámicas
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

$sqlDatos = <<<'SQL'
-- Insertar tipo de documento: procedimiento_control_documental
INSERT INTO tbl_doc_tipo_configuracion
(tipo_documento, nombre, descripcion, estandar, flujo, categoria, icono, orden)
VALUES
('procedimiento_control_documental',
 'Procedimiento de Control Documental del SG-SST',
 'Establece las directrices para la elaboración, revisión, aprobación, distribución y conservación de documentos del SG-SST',
 '2.5.1',
 'secciones_ia',
 'procedimientos',
 'bi-folder-check',
 1)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), updated_at = NOW();
SQL;

$sqlSecciones = <<<'SQL'
-- Insertar secciones del procedimiento de control documental
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
    SELECT 1 as numero, 'Objetivo' as nombre, 'objetivo' as seccion_key, 'texto' as tipo_contenido, NULL as tabla_dinamica_tipo, 1 as orden, 'Genera el objetivo del procedimiento de control documental del SG-SST.' as prompt_ia
    UNION SELECT 2, 'Alcance', 'alcance', 'texto', NULL, 2, 'Genera el alcance del procedimiento de control documental.'
    UNION SELECT 3, 'Definiciones', 'definiciones', 'texto', NULL, 3, 'Genera las definiciones clave para el control documental.'
    UNION SELECT 4, 'Marco Normativo', 'marco_normativo', 'texto', NULL, 4, 'Genera el marco normativo aplicable.'
    UNION SELECT 5, 'Responsabilidades', 'responsabilidades', 'texto', NULL, 5, 'Genera las responsabilidades en el control documental.'
    UNION SELECT 6, 'Tipos de Documentos del SG-SST', 'tipos_documentos', 'mixto', 'tipos_documento', 6, 'Genera un párrafo introductorio sobre los tipos de documentos. NO generes tabla.'
    UNION SELECT 7, 'Estructura y Codificación', 'codificacion', 'mixto', 'plantillas', 7, 'Genera un párrafo explicando la codificación. NO generes ejemplos.'
    UNION SELECT 8, 'Elaboración de Documentos', 'elaboracion', 'texto', NULL, 8, 'Genera el procedimiento para elaborar documentos.'
    UNION SELECT 9, 'Revisión y Aprobación', 'revision_aprobacion', 'texto', NULL, 9, 'Genera el procedimiento de revisión y aprobación.'
    UNION SELECT 10, 'Distribución y Acceso', 'distribucion', 'texto', NULL, 10, 'Genera el procedimiento de distribución.'
    UNION SELECT 11, 'Control de Cambios', 'control_cambios', 'texto', NULL, 11, 'Genera el procedimiento de control de cambios.'
    UNION SELECT 12, 'Conservación y Retención', 'conservacion', 'texto', NULL, 12, 'Genera el procedimiento de conservación (20 años).'
    UNION SELECT 13, 'Listado Maestro de Documentos', 'listado_maestro', 'mixto', 'listado_maestro', 13, 'Genera un párrafo introductorio sobre el listado maestro.'
    UNION SELECT 14, 'Disposición Final', 'disposicion_final', 'texto', NULL, 14, 'Genera el procedimiento de disposición final.'
) s
WHERE tc.tipo_documento = 'procedimiento_control_documental'
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), prompt_ia = VALUES(prompt_ia);
SQL;

$sqlFirmantes = <<<'SQL'
-- Insertar firmantes del procedimiento de control documental
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
    UNION SELECT 'representante_legal', 'Aprobó', 'Aprobó / Representante Legal', 2, 0
) f
WHERE tc.tipo_documento = 'procedimiento_control_documental'
ON DUPLICATE KEY UPDATE rol_display = VALUES(rol_display), columna_encabezado = VALUES(columna_encabezado);
SQL;

$sqlTablasDinamicas = <<<'SQL'
-- Insertar tablas dinámicas disponibles
INSERT INTO tbl_doc_tablas_dinamicas
(tabla_key, nombre, descripcion, query_base, columnas, filtro_cliente, estilo_encabezado)
VALUES
('tipos_documento',
 'Tipos de Documentos',
 'Tabla con los tipos de documentos del SG-SST',
 'SELECT prefijo, nombre, descripcion FROM tbl_doc_tipos WHERE activo = 1 ORDER BY id_tipo',
 '[{"key": "prefijo", "titulo": "Prefijo", "ancho": "70px", "alineacion": "center"}, {"key": "nombre", "titulo": "Tipo de Documento", "ancho": "auto", "alineacion": "left"}, {"key": "descripcion", "titulo": "Descripción", "ancho": "auto", "alineacion": "left"}]',
 0, 'primary'),
('plantillas',
 'Códigos de Documentos',
 'Tabla con los códigos de plantillas del sistema',
 'SELECT codigo_sugerido, nombre FROM tbl_doc_plantillas WHERE activo = 1 AND tipo_documento IS NOT NULL ORDER BY codigo_sugerido',
 '[{"key": "codigo_sugerido", "titulo": "Código", "ancho": "100px", "alineacion": "center"}, {"key": "nombre", "titulo": "Nombre del Documento", "ancho": "auto", "alineacion": "left"}]',
 0, 'primary'),
('listado_maestro',
 'Listado Maestro de Documentos',
 'Tabla con los documentos del cliente',
 'SELECT codigo, titulo, version, estado, created_at FROM tbl_documentos_sst WHERE id_cliente = :id_cliente AND estado IN (\"aprobado\", \"firmado\", \"generado\") ORDER BY codigo',
 '[{"key": "codigo", "titulo": "Código", "ancho": "80px", "alineacion": "center"}, {"key": "titulo", "titulo": "Título", "ancho": "auto", "alineacion": "left"}, {"key": "version", "titulo": "Versión", "ancho": "50px", "alineacion": "center"}, {"key": "estado", "titulo": "Estado", "ancho": "70px", "alineacion": "center"}, {"key": "created_at", "titulo": "Fecha", "ancho": "75px", "alineacion": "center"}]',
 1, 'success')
ON DUPLICATE KEY UPDATE query_base = VALUES(query_base), columnas = VALUES(columnas);
SQL;

// Función para ejecutar migración
function ejecutarMigracion($nombre, $config, $sqls) {
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "MIGRANDO: $nombre\n";
    echo str_repeat("=", 60) . "\n";

    try {
        // Opciones de conexión
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];

        // SSL para producción
        if ($config['ssl']) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = true;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        echo "✅ Conectado a {$config['host']}:{$config['port']}\n\n";

        // Ejecutar cada SQL
        foreach ($sqls as $descripcion => $sql) {
            echo "Ejecutando: $descripcion... ";
            try {
                $pdo->exec($sql);
                echo "✅\n";
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'already exists') !== false ||
                    strpos($e->getMessage(), 'Duplicate') !== false) {
                    echo "⚠️ (ya existe)\n";
                } else {
                    echo "❌ " . $e->getMessage() . "\n";
                }
            }
        }

        // Verificar tablas
        echo "\nVerificación de tablas:\n";
        $tablas = [
            'tbl_doc_tipo_configuracion',
            'tbl_doc_secciones_config',
            'tbl_doc_firmantes_config',
            'tbl_doc_tablas_dinamicas'
        ];

        foreach ($tablas as $tabla) {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM $tabla");
            $count = $stmt->fetch()['total'];
            echo "  ✅ $tabla: $count registros\n";
        }

        echo "\n✅ Migración de $nombre COMPLETADA\n";
        return true;

    } catch (PDOException $e) {
        echo "❌ Error de conexión: " . $e->getMessage() . "\n";
        return false;
    }
}

// Array de SQLs a ejecutar
$sqls = [
    'Crear tablas' => $sqlMigracion,
    'Insertar tipo documento' => $sqlDatos,
    'Insertar secciones' => $sqlSecciones,
    'Insertar firmantes' => $sqlFirmantes,
    'Insertar tablas dinámicas' => $sqlTablasDinamicas
];

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  MIGRACIÓN: Arquitectura Escalable Documentos SST v2.0    ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";

// Ejecutar en LOCAL
$resultadoLocal = ejecutarMigracion('LOCAL', $conexiones['local'], $sqls);

// Ejecutar en PRODUCCIÓN
$resultadoProduccion = ejecutarMigracion('PRODUCCIÓN', $conexiones['produccion'], $sqls);

// Resumen final
echo "\n" . str_repeat("=", 60) . "\n";
echo "RESUMEN FINAL\n";
echo str_repeat("=", 60) . "\n";
echo "LOCAL:      " . ($resultadoLocal ? "✅ OK" : "❌ FALLÓ") . "\n";
echo "PRODUCCIÓN: " . ($resultadoProduccion ? "✅ OK" : "❌ FALLÓ") . "\n";
echo str_repeat("=", 60) . "\n";
