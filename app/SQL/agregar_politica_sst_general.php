<?php
/**
 * Script para agregar módulo 2.1.1 - Política de Seguridad y Salud en el Trabajo (PILOTO)
 *
 * Ejecuta cambios en LOCAL y PRODUCCIÓN simultáneamente
 *
 * Ejecutar: php app/SQL/agregar_politica_sst_general.php
 *
 * Crea:
 * - Configuración en tbl_doc_tipo_configuracion
 * - Secciones en tbl_doc_secciones_config
 * - Firmantes en tbl_doc_firmantes_config
 * - Plantilla en tbl_doc_plantillas
 * - Mapeo en tbl_doc_plantilla_carpeta
 *
 * NOTA: Este es el piloto de las 5 políticas del numeral 2.1.1
 */

echo "=== MÓDULO 2.1.1 - POLÍTICA DE SEGURIDAD Y SALUD EN EL TRABAJO ===\n";
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
// SQL 1: Insertar tipo de documento en tbl_doc_tipo_configuracion
// ============================================================
$sqlTipoDocumento = <<<'SQL'
INSERT INTO tbl_doc_tipo_configuracion
(tipo_documento, nombre, descripcion, estandar, flujo, categoria, icono, orden, activo)
VALUES
('politica_sst_general',
 'Política de Seguridad y Salud en el Trabajo',
 'Política del SG-SST firmada, fechada y comunicada al COPASST/Vigía SST según Decreto 1072/2015 Art. 2.2.4.6.5 y Resolución 0312/2019',
 '2.1.1',
 'secciones_ia',
 'politicas',
 'bi-shield-check',
 1,
 1)
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    descripcion = VALUES(descripcion),
    updated_at = NOW();
SQL;

// ============================================================
// SQL 2: Insertar secciones del documento
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
    SELECT 1 as numero, 'Objetivo' as nombre, 'objetivo' as seccion_key, 'texto' as tipo_contenido,
           NULL as tabla_dinamica_tipo, NULL as sincronizar_bd, 1 as es_obligatoria, 1 as orden,
           'Genera el objetivo de la Política de Seguridad y Salud en el Trabajo para {empresa}. Debe expresar el compromiso de la alta dirección con la protección de la seguridad y salud de todos los trabajadores, la prevención de lesiones y el mejoramiento continuo del SG-SST. Máximo 2-3 párrafos.' as prompt_ia

    UNION SELECT 2, 'Alcance', 'alcance', 'texto', NULL, NULL, 1, 2,
           'Define el alcance de la Política de SST. Debe especificar que aplica a todos los trabajadores (directos, temporales), contratistas, subcontratistas, visitantes y todas las actividades y procesos de {empresa}. Personaliza según el número de trabajadores y sedes del contexto.'

    UNION SELECT 3, 'Declaración de la Política', 'declaracion', 'texto', NULL, NULL, 1, 3,
           'Genera la declaración formal de la Política de SST para {empresa}. Debe incluir compromiso explícito con: (1) Identificación de peligros y evaluación de riesgos, (2) Protección de seguridad y salud de trabajadores, (3) Cumplimiento de normatividad legal vigente, (4) Participación de trabajadores y sus representantes, (5) Mejora continua del SG-SST. Redacta en primera persona plural. Tono formal y comprometido.'

    UNION SELECT 4, 'Compromisos del Empleador', 'compromisos_empleador', 'texto', NULL, NULL, 1, 4,
           'Genera los compromisos específicos del empleador según Decreto 1072/2015 Art. 2.2.4.6.5: (1) Definir, firmar y divulgar la política, (2) Rendir cuentas sobre el SG-SST, (3) Cumplir requisitos normativos, (4) Gestionar peligros y riesgos, (5) Desarrollar plan de trabajo anual, (6) Prevenir lesiones y enfermedades, (7) Proteger a todos los trabajadores, (8) Promover participación, (9) Garantizar capacitación, (10) Asignar recursos. Para {estandares} estándares, prioriza los más relevantes.'

    UNION SELECT 5, 'Compromisos de los Trabajadores', 'compromisos_trabajadores', 'texto', NULL, NULL, 1, 5,
           'Genera los compromisos de los trabajadores según Ley 1562/2012 Art. 10. Incluir: cuidado integral de su salud, suministrar información veraz sobre estado de salud, cumplir normas e instrucciones del SG-SST, informar sobre peligros y riesgos, participar en capacitaciones, contribuir al cumplimiento de objetivos. Formato lista de viñetas, máximo 6-8 compromisos.'

    UNION SELECT 6, 'Marco Legal', 'marco_legal', 'texto', NULL, NULL, 1, 6,
           'Lista el marco normativo aplicable a la Política de SST. ESENCIAL: Constitución Política (Art. 25, 48, 49), Ley 9/1979, Ley 1562/2012, Decreto 1072/2015, Resolución 0312/2019. CANTIDAD: 5 normas para 7 estándares, 7 para 21 estándares, hasta 10 para 60 estándares. Formato lista con viñetas, NO usar tablas.'

    UNION SELECT 7, 'Comunicación y Divulgación', 'comunicacion', 'texto', NULL, NULL, 1, 7,
           'Define cómo se comunicará y divulgará la política. Incluir: (1) Comunicación al COPASST o Vigía SST según {estandares}, (2) Publicación en lugares visibles, (3) Inclusión en inducción y reinducción, (4) Entrega a contratistas, (5) Revisión anual. IMPORTANTE: Usar "Vigía SST" para 7 estándares, "COPASST" para 21 o más.'

) s
WHERE tc.tipo_documento = 'politica_sst_general'
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    prompt_ia = VALUES(prompt_ia);
SQL;

// ============================================================
// SQL 3: Insertar firmantes del documento
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
    SELECT 'consultor_sst' as firmante_tipo, 'Elaboró' as rol_display,
           'Elaboró / Consultor SST' as columna_encabezado, 1 as orden,
           1 as es_obligatorio, 1 as mostrar_licencia, 0 as mostrar_cedula
    UNION SELECT 'representante_legal', 'Aprobó',
           'Aprobó / Representante Legal', 2,
           1, 0, 1
) f
WHERE tc.tipo_documento = 'politica_sst_general'
ON DUPLICATE KEY UPDATE
    rol_display = VALUES(rol_display),
    columna_encabezado = VALUES(columna_encabezado);
SQL;

// ============================================================
// SQL 4: Insertar plantilla del documento
// ============================================================
$sqlPlantilla = <<<'SQL'
INSERT INTO tbl_doc_plantillas
(id_tipo, nombre, codigo_sugerido, tipo_documento, version, descripcion, activo, aplica_7, aplica_21, aplica_60)
SELECT
    t.id_tipo,
    'Política de Seguridad y Salud en el Trabajo' as nombre,
    'POL-SST' as codigo_sugerido,
    'politica_sst_general' as tipo_documento,
    '001' as version,
    'Política del SG-SST según Decreto 1072/2015 y Resolución 0312/2019' as descripcion,
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

// ============================================================
// SQL 5: Insertar mapeo plantilla-carpeta
// ============================================================
$sqlMapeoCarpeta = <<<'SQL'
INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
VALUES ('POL-SST', '2.1.1')
ON DUPLICATE KEY UPDATE codigo_carpeta = VALUES(codigo_carpeta);
SQL;

// ============================================================
// Función para ejecutar SQL
// ============================================================
function ejecutarSQL($pdo, $sql, $descripcion, $entorno) {
    try {
        $pdo->exec($sql);
        echo "  ✓ {$descripcion}\n";
        return true;
    } catch (PDOException $e) {
        echo "  ✗ {$descripcion}: " . $e->getMessage() . "\n";
        return false;
    }
}

// ============================================================
// Ejecutar en cada entorno
// ============================================================
foreach ($conexiones as $entorno => $config) {
    echo "▶ Procesando entorno: " . strtoupper($entorno) . "\n";
    echo str_repeat("-", 50) . "\n";

    try {
        // Configurar DSN
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
        $opciones = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        // Agregar SSL para producción
        if ($config['ssl']) {
            $opciones[PDO::MYSQL_ATTR_SSL_CA] = true;
            $opciones[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        $pdo = new PDO($dsn, $config['username'], $config['password'], $opciones);
        echo "  ✓ Conexión establecida\n\n";

        // Ejecutar SQLs
        ejecutarSQL($pdo, $sqlTipoDocumento, "Tipo de documento en tbl_doc_tipo_configuracion", $entorno);
        ejecutarSQL($pdo, $sqlSecciones, "Secciones en tbl_doc_secciones_config", $entorno);
        ejecutarSQL($pdo, $sqlFirmantes, "Firmantes en tbl_doc_firmantes_config", $entorno);
        ejecutarSQL($pdo, $sqlPlantilla, "Plantilla en tbl_doc_plantillas", $entorno);
        ejecutarSQL($pdo, $sqlMapeoCarpeta, "Mapeo en tbl_doc_plantilla_carpeta", $entorno);

        echo "\n";

    } catch (PDOException $e) {
        echo "  ✗ Error de conexión: " . $e->getMessage() . "\n\n";
    }
}

echo "\n=== PROCESO COMPLETADO ===\n";
echo "Ahora puedes acceder a la carpeta 2.1.1 y generar la Política SST.\n";
echo "URL: /documentos/generar/politica_sst_general/{id_cliente}\n";
