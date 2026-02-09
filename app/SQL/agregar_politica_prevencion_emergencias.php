<?php
/**
 * Script para agregar: Política de Prevención y Respuesta ante Emergencias
 * Numeral 2.1.1 de la Resolución 0312/2019
 *
 * Ejecutar: php app/SQL/agregar_politica_prevencion_emergencias.php
 */

echo "=== POLÍTICA DE PREVENCIÓN Y RESPUESTA ANTE EMERGENCIAS (2.1.1) ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

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

// SQL 1: Tipo de documento
$sqlTipoDocumento = <<<'SQL'
INSERT INTO tbl_doc_tipo_configuracion
(tipo_documento, nombre, descripcion, estandar, flujo, categoria, icono, orden, activo)
VALUES
('politica_prevencion_emergencias',
 'Política de Prevención y Respuesta ante Emergencias',
 'Política que establece el compromiso de la empresa con la prevención, preparación y respuesta ante situaciones de emergencia',
 '2.1.1',
 'secciones_ia',
 'politicas',
 'bi-exclamation-triangle',
 6,
 1)
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    descripcion = VALUES(descripcion),
    updated_at = NOW();
SQL;

// SQL 2: Secciones
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
    SELECT 1 as numero, 'Objetivo' as nombre, 'objetivo' as seccion_key, 'texto' as tipo_contenido,
           NULL as tabla_dinamica_tipo, NULL as sincronizar_bd, 1 as es_obligatoria, 1 as orden,
           'Genera el objetivo de la Política de Prevención y Respuesta ante Emergencias para {empresa}. Debe expresar el compromiso con prevención, preparación, respuesta efectiva y recuperación ante emergencias.' as prompt_ia

    UNION SELECT 2, 'Alcance', 'alcance', 'texto', NULL, NULL, 1, 2,
           'Define el alcance de la Política de Emergencias. Aplica a todas las instalaciones, trabajadores, contratistas, visitantes. Incluye emergencias internas y externas.'

    UNION SELECT 3, 'Declaración de la Política', 'declaracion', 'texto', NULL, NULL, 1, 3,
           'Genera la declaración formal de la Política de Emergencias. Compromiso con: identificación de amenazas, implementación de medidas preventivas, brigadas de emergencia, equipos, simulacros, coordinación con entidades externas.'

    UNION SELECT 4, 'Compromisos de la Dirección', 'compromisos_direccion', 'texto', NULL, NULL, 1, 4,
           'Genera los compromisos de la dirección según Decreto 1072/2015 y Ley 1523/2012: asignar recursos, identificar amenazas, establecer PON, conformar brigadas, realizar simulacros, mantener equipos, coordinar con socorro.'

    UNION SELECT 5, 'Tipos de Emergencias Contempladas', 'tipos_emergencias', 'texto', NULL, NULL, 1, 5,
           'Lista los tipos de emergencias: Naturales (sismos, inundaciones, tormentas), Tecnológicas (incendios, explosiones, derrames), Sociales (atentados, robos). Personaliza según peligros identificados del cliente.'

    UNION SELECT 6, 'Organización para Emergencias', 'organizacion_emergencias', 'texto', NULL, NULL, 1, 6,
           'Describe estructura organizacional: Coordinador de emergencias, Brigada de emergencias (evacuación, primeros auxilios, incendios), funciones. Ajusta según tiene_brigada_emergencias del contexto.'

    UNION SELECT 7, 'Marco Legal', 'marco_legal', 'texto', NULL, NULL, 1, 7,
           'Marco normativo: Ley 9/1979, Ley 1523/2012, Decreto 1072/2015 Art. 2.2.4.6.25, Resolución 0312/2019, Resolución 2400/1979. Cantidad según estándares del cliente.'

    UNION SELECT 8, 'Comunicación y Divulgación', 'comunicacion', 'texto', NULL, NULL, 1, 8,
           'Define comunicación de la política: al COPASST/Vigía, socialización a trabajadores, publicación del Plan, señalización, capacitación en evacuación, simulacros anuales.'

) s
WHERE tc.tipo_documento = 'politica_prevencion_emergencias'
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    prompt_ia = VALUES(prompt_ia);
SQL;

// SQL 3: Firmantes
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
    SELECT 'consultor_sst' as firmante_tipo, 'Elaboró' as rol_display,
           'Elaboró / Consultor SST' as columna_encabezado, 1 as orden,
           1 as es_obligatorio, 1 as mostrar_licencia, 0 as mostrar_cedula
    UNION SELECT 'representante_legal', 'Aprobó',
           'Aprobó / Representante Legal', 2,
           1, 0, 1
) f
WHERE tc.tipo_documento = 'politica_prevencion_emergencias'
ON DUPLICATE KEY UPDATE
    rol_display = VALUES(rol_display),
    columna_encabezado = VALUES(columna_encabezado);
SQL;

// SQL 4: Plantilla
$sqlPlantilla = <<<'SQL'
INSERT INTO tbl_doc_plantillas
(id_tipo, nombre, codigo_sugerido, tipo_documento, version, descripcion, activo, aplica_7, aplica_21, aplica_60)
SELECT
    t.id_tipo,
    'Política de Prevención y Respuesta ante Emergencias' as nombre,
    'POL-EME' as codigo_sugerido,
    'politica_prevencion_emergencias' as tipo_documento,
    '001' as version,
    'Política de prevención, preparación y respuesta ante emergencias' as descripcion,
    1 as activo,
    1 as aplica_7,
    1 as aplica_21,
    1 as aplica_60
FROM tbl_doc_tipos t
WHERE t.codigo = 'POL' OR t.nombre LIKE '%Polít%'
LIMIT 1
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    descripcion = VALUES(descripcion);
SQL;

// SQL 5: Mapeo carpeta
$sqlMapeoCarpeta = <<<'SQL'
INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
VALUES ('POL-EME', '2.1.1')
ON DUPLICATE KEY UPDATE codigo_carpeta = VALUES(codigo_carpeta);
SQL;

// Función para ejecutar SQL
function ejecutarSQL($pdo, $sql, $descripcion) {
    try {
        $pdo->exec($sql);
        echo "  ✓ {$descripcion}\n";
        return true;
    } catch (PDOException $e) {
        echo "  ✗ {$descripcion}: " . $e->getMessage() . "\n";
        return false;
    }
}

// Ejecutar en cada entorno
foreach ($conexiones as $entorno => $config) {
    echo "▶ Procesando: " . strtoupper($entorno) . "\n";

    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
        $opciones = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        if ($config['ssl']) {
            $opciones[PDO::MYSQL_ATTR_SSL_CA] = true;
            $opciones[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        $pdo = new PDO($dsn, $config['username'], $config['password'], $opciones);
        echo "  ✓ Conexión OK\n";

        ejecutarSQL($pdo, $sqlTipoDocumento, "Tipo de documento");
        ejecutarSQL($pdo, $sqlSecciones, "Secciones (8)");
        ejecutarSQL($pdo, $sqlFirmantes, "Firmantes");
        ejecutarSQL($pdo, $sqlPlantilla, "Plantilla POL-EME");
        ejecutarSQL($pdo, $sqlMapeoCarpeta, "Mapeo carpeta 2.1.1");

        echo "\n";

    } catch (PDOException $e) {
        echo "  ✗ Error conexión: " . $e->getMessage() . "\n\n";
    }
}

echo "=== COMPLETADO ===\n";
echo "URL: /documentos/generar/politica_prevencion_emergencias/{id_cliente}\n";
