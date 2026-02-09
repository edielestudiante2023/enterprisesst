<?php
/**
 * Script para agregar tipo de documento: Mecanismos de Comunicación, Auto Reporte en SG-SST
 * Estándar: 2.8.1 de la Resolución 0312/2019
 *
 * Ejecutar: php app/SQL/agregar_mecanismos_comunicacion_sgsst.php
 *
 * @author Enterprise SST
 * @version 1.0
 * @date Febrero 2026
 */

echo "=== Agregando Mecanismos de Comunicación, Auto Reporte en SG-SST (2.8.1) ===\n\n";

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

// ============================================
// SQL para tipo de documento
// ============================================
$sqlTipo = <<<'SQL'
INSERT INTO tbl_doc_tipo_configuracion
(tipo_documento, nombre, descripcion, estandar, flujo, categoria, icono, orden)
VALUES
('mecanismos_comunicacion_sgsst',
 'Mecanismos de Comunicación, Auto Reporte en SG-SST',
 'Establece los canales y procedimientos para la comunicación interna, externa y auto reporte de condiciones de trabajo y salud en el SG-SST',
 '2.8.1',
 'secciones_ia',
 'procedimientos',
 'bi-megaphone',
 15)
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    descripcion = VALUES(descripcion),
    updated_at = NOW();
SQL;

// ============================================
// SQL para secciones del documento
// ============================================
$sqlSecciones = <<<'SQL'
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
    SELECT 1 as numero,
           'Objetivo' as nombre,
           'objetivo' as seccion_key,
           'texto' as tipo_contenido,
           NULL as tabla_dinamica_tipo,
           1 as orden,
           'Genera el objetivo del procedimiento de Mecanismos de Comunicación, Auto Reporte en SG-SST. Debe establecer el propósito de garantizar canales efectivos de comunicación interna y externa, así como mecanismos para el auto reporte de condiciones de trabajo y salud. Referencia al estándar 2.8.1 de la Resolución 0312/2019. Máximo 2 párrafos.' as prompt_ia

    UNION SELECT 2,
           'Alcance',
           'alcance',
           'texto',
           NULL,
           2,
           'Define el alcance del procedimiento. Debe especificar que aplica a todos los trabajadores, contratistas, proveedores y partes interesadas. Incluir los tipos de comunicación cubiertos (interna, externa, auto reporte). Máximo 2 párrafos.'

    UNION SELECT 3,
           'Definiciones',
           'definiciones',
           'texto',
           NULL,
           3,
           'Define los términos clave para el procedimiento de comunicación. INCLUIR: Comunicación interna, Comunicación externa, Auto reporte, Condiciones de trabajo, Condiciones de salud, Canal de comunicación, Buzón de sugerencias, Reporte de peligros, Partes interesadas. Máximo 10 definiciones en formato lista con negrita para el término.'

    UNION SELECT 4,
           'Responsabilidades',
           'responsabilidades',
           'texto',
           NULL,
           4,
           'Define las responsabilidades de cada actor en el proceso de comunicación. INCLUIR: Alta dirección (asignar recursos, aprobar canales), Responsable SG-SST (gestionar comunicaciones, atender reportes), COPASST/Vigía (canalizar inquietudes, verificar respuestas), Trabajadores (usar canales, reportar condiciones). Formato: nombre del rol en negrita seguido de lista de responsabilidades.'

    UNION SELECT 5,
           'Mecanismos de Comunicación Interna',
           'comunicacion_interna',
           'texto',
           NULL,
           5,
           'Describe los canales y mecanismos de comunicación INTERNA del SG-SST. INCLUIR: Carteleras informativas, Correo electrónico institucional, Reuniones periódicas, Capacitaciones, Boletines SST, Grupos de WhatsApp/Teams, Intranet. Para cada canal indicar: propósito, frecuencia de uso, responsable de actualización. Formato estructurado con subtítulos.'

    UNION SELECT 6,
           'Mecanismos de Comunicación Externa',
           'comunicacion_externa',
           'texto',
           NULL,
           6,
           'Describe los canales y mecanismos de comunicación EXTERNA relacionados con SST. INCLUIR: Comunicación con ARL, Comunicación con EPS/IPS, Reportes a autoridades (MinTrabajo), Comunicación con proveedores y contratistas, Atención a quejas de la comunidad. Para cada canal indicar: tipo de información, medio utilizado, frecuencia, responsable.'

    UNION SELECT 7,
           'Procedimiento de Auto Reporte',
           'auto_reporte',
           'texto',
           NULL,
           7,
           'Describe el procedimiento detallado para que los trabajadores reporten condiciones de trabajo y salud. INCLUIR: Qué reportar (peligros, incidentes, condiciones inseguras, síntomas de salud), Cómo reportar (formatos, canales), A quién reportar, Tiempos de respuesta esperados, Confidencialidad del reporte, Protección contra represalias. Incluir diagrama de flujo textual del proceso.'

    UNION SELECT 8,
           'Canales de Recepción de Inquietudes',
           'canales_inquietudes',
           'texto',
           NULL,
           8,
           'Describe los canales disponibles para recibir inquietudes, ideas y aportes de los trabajadores en SST. INCLUIR: Buzón físico de sugerencias (ubicación, frecuencia de revisión), Buzón virtual/correo electrónico, Línea telefónica o extensión, Reuniones del COPASST/Vigía, Formatos de reporte de peligros. Indicar para cada canal: cómo acceder, quién revisa, tiempo de respuesta.'

    UNION SELECT 9,
           'Registro y Seguimiento',
           'registro_seguimiento',
           'texto',
           NULL,
           9,
           'Describe cómo se documentan, registran y dan seguimiento a las comunicaciones y reportes recibidos. INCLUIR: Formato de registro de comunicaciones, Trazabilidad de reportes, Indicadores de gestión (tiempo de respuesta, % de casos cerrados), Informes periódicos al COPASST/Vigía, Retroalimentación al reportante. Incluir ejemplo de tabla de seguimiento.'
) s
WHERE tc.tipo_documento = 'mecanismos_comunicacion_sgsst'
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    prompt_ia = VALUES(prompt_ia);
SQL;

// ============================================
// SQL para firmantes
// ============================================
$sqlFirmantes = <<<'SQL'
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
    SELECT 'responsable_sst' as firmante_tipo,
           'Elaboró' as rol_display,
           'Elaboró / Responsable del SG-SST' as columna_encabezado,
           1 as orden,
           1 as mostrar_licencia
    UNION SELECT 'representante_legal',
           'Aprobó',
           'Aprobó / Representante Legal',
           2,
           0
) f
WHERE tc.tipo_documento = 'mecanismos_comunicacion_sgsst'
ON DUPLICATE KEY UPDATE
    rol_display = VALUES(rol_display),
    columna_encabezado = VALUES(columna_encabezado);
SQL;

// ============================================
// SQL para plantilla (opcional, solo si la tabla existe y tiene tipos)
// ============================================
$sqlPlantilla = <<<'SQL'
INSERT INTO tbl_doc_plantillas (
    id_tipo, nombre, codigo_sugerido, tipo_documento, version, activo
)
SELECT
    COALESCE(
        (SELECT id_tipo FROM tbl_doc_tipos WHERE codigo = 'procedimientos' LIMIT 1),
        (SELECT id_tipo FROM tbl_doc_tipos WHERE nombre LIKE '%procedimiento%' LIMIT 1),
        (SELECT id_tipo FROM tbl_doc_tipos ORDER BY id_tipo LIMIT 1)
    ),
    'Mecanismos de Comunicación, Auto Reporte en SG-SST',
    'MEC-COM',
    'mecanismos_comunicacion_sgsst',
    '001',
    1
FROM DUAL
WHERE EXISTS (SELECT 1 FROM tbl_doc_tipos LIMIT 1)
  AND NOT EXISTS (SELECT 1 FROM tbl_doc_plantillas WHERE codigo_sugerido = 'MEC-COM')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);
SQL;

// ============================================
// SQL para mapeo plantilla-carpeta
// ============================================
$sqlMapeoCarpeta = <<<'SQL'
INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
VALUES ('MEC-COM', '2.8.1')
ON DUPLICATE KEY UPDATE codigo_carpeta = VALUES(codigo_carpeta);
SQL;

// ============================================
// Función para ejecutar en una conexión
// ============================================
function ejecutarEnConexion($config, $nombre, $sqlTipo, $sqlSecciones, $sqlFirmantes, $sqlPlantilla, $sqlMapeoCarpeta) {
    echo "Conectando a {$nombre}...\n";

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
        echo "  ✓ Conexión exitosa\n";

        // 1. Insertar tipo de documento
        echo "  → Insertando tipo de documento...\n";
        $pdo->exec($sqlTipo);
        echo "    ✓ Tipo de documento insertado/actualizado\n";

        // 2. Insertar secciones
        echo "  → Insertando secciones...\n";
        $pdo->exec($sqlSecciones);
        $countSecciones = $pdo->query("SELECT COUNT(*) FROM tbl_doc_secciones_config WHERE id_tipo_config = (SELECT id_tipo_config FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'mecanismos_comunicacion_sgsst')")->fetchColumn();
        echo "    ✓ {$countSecciones} secciones configuradas\n";

        // 3. Insertar firmantes
        echo "  → Insertando firmantes...\n";
        $pdo->exec($sqlFirmantes);
        echo "    ✓ Firmantes configurados\n";

        // 4. Insertar plantilla (si la tabla existe)
        echo "  → Verificando tabla de plantillas...\n";
        $tablaExists = $pdo->query("SHOW TABLES LIKE 'tbl_doc_plantillas'")->rowCount() > 0;
        if ($tablaExists) {
            $pdo->exec($sqlPlantilla);
            echo "    ✓ Plantilla insertada/actualizada\n";
        } else {
            echo "    ⚠ Tabla tbl_doc_plantillas no existe, omitiendo\n";
        }

        // 5. Insertar mapeo carpeta (si la tabla existe)
        echo "  → Verificando tabla de mapeo...\n";
        $tablaMapeoExists = $pdo->query("SHOW TABLES LIKE 'tbl_doc_plantilla_carpeta'")->rowCount() > 0;
        if ($tablaMapeoExists) {
            $pdo->exec($sqlMapeoCarpeta);
            echo "    ✓ Mapeo a carpeta 2.8.1 configurado\n";
        } else {
            echo "    ⚠ Tabla tbl_doc_plantilla_carpeta no existe, omitiendo\n";
        }

        echo "  ✓ {$nombre} completado exitosamente\n\n";
        return true;

    } catch (PDOException $e) {
        echo "  ✗ Error en {$nombre}: " . $e->getMessage() . "\n\n";
        return false;
    }
}

// ============================================
// Ejecutar en LOCAL
// ============================================
$resultadoLocal = ejecutarEnConexion(
    $conexiones['local'],
    'LOCAL',
    $sqlTipo,
    $sqlSecciones,
    $sqlFirmantes,
    $sqlPlantilla,
    $sqlMapeoCarpeta
);

// ============================================
// Ejecutar en producción automáticamente si local fue exitoso
// ============================================
if ($resultadoLocal) {
    echo "LOCAL exitoso. Ejecutando en PRODUCCIÓN automáticamente...\n\n";
    ejecutarEnConexion(
        $conexiones['produccion'],
        'PRODUCCIÓN',
        $sqlTipo,
        $sqlSecciones,
        $sqlFirmantes,
        $sqlPlantilla,
        $sqlMapeoCarpeta
    );
}

echo "=== Proceso completado ===\n";
echo "\nPróximos pasos:\n";
echo "1. Verificar que existe la clase: app/Libraries/DocumentosSSTTypes/MecanismosComunicacionSgsst.php\n";
echo "2. Verificar registro en: app/Libraries/DocumentosSSTTypes/DocumentoSSTFactory.php\n";
echo "3. Verificar vista: app/Views/documentacion/_tipos/mecanismos_comunicacion_sgsst.php\n";
echo "4. Probar generación en: /documentos/generar/mecanismos_comunicacion_sgsst/{id_cliente}\n";
