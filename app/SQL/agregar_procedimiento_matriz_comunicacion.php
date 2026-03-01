<?php
/**
 * Script para agregar tipo de documento: Procedimiento de Matriz de Comunicacion SST
 * Estandar: 2.8.1
 * Ejecutar: php app/SQL/agregar_procedimiento_matriz_comunicacion.php
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
('procedimiento_matriz_comunicacion',
 'Procedimiento de Matriz de Comunicacion SST',
 'Establece la metodologia para identificar, documentar y mantener los protocolos de comunicacion interna y externa del SG-SST',
 '2.8.1',
 'secciones_ia',
 'procedimientos',
 'bi-diagram-3',
 16)
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
           'Genera el objetivo del procedimiento de Matriz de Comunicacion SST. Debe establecer el proposito de identificar y documentar todos los protocolos de comunicacion interna y externa requeridos por el SG-SST. Referencia Decreto 1072/2015 articulo 2.2.4.6.14 y Resolucion 0312/2019 estandar 2.8.1.' as prompt_ia
    UNION SELECT 2, 'Alcance', 'alcance', 'texto', 2,
           'Define el alcance: aplica a todas las comunicaciones internas (entre trabajadores, niveles jerarquicos, comites) y externas (ARL, EPS, autoridades, contratistas) relacionadas con SST en todas las sedes y centros de trabajo de la organizacion.'
    UNION SELECT 3, 'Definiciones', 'definiciones', 'texto', 3,
           'Define terminos clave: Matriz de Comunicacion, Protocolo de Comunicacion, Comunicacion Interna, Comunicacion Externa, Canal de Comunicacion, Auto Reporte de Condiciones, Trazabilidad, Mecanismo de Comunicacion, Registro de Comunicacion.'
    UNION SELECT 4, 'Responsabilidades', 'responsabilidades', 'texto', 4,
           'Define responsables: Alta Direccion (garantizar canales de comunicacion), Responsable SG-SST (mantener la matriz, verificar cumplimiento de protocolos), COPASST/Vigia (participar en reuniones, comunicar recomendaciones), Comite de Convivencia (gestionar quejas de acoso), Trabajadores (reportar condiciones, auto-reportar situaciones de salud).'
    UNION SELECT 5, 'Estructura de la Matriz', 'estructura_matriz', 'texto', 5,
           'Describe la estructura de la matriz de comunicacion: columnas obligatorias (categoria, situacion/evento, que comunicar, quien comunica, a quien, canal/mecanismo, frecuencia/plazo, registro/evidencia, norma aplicable, tipo interna/externa). Explica los criterios de clasificacion por categorias (accidentes, incidentes, emergencias, convivencia laboral, peligros, auditorias, cambios normativos, capacitaciones, COPASST, comunicacion externa).'
    UNION SELECT 6, 'Procedimiento de Actualizacion', 'actualizacion', 'texto', 6,
           'Describe cuando y como se actualiza la matriz: ante nuevas normas, cambios organizacionales, resultados de auditorias, eventos no contemplados, nuevos peligros identificados. Frecuencia de revision minima anual. Responsable de la actualizacion. Control de cambios con fecha, descripcion del cambio y responsable.'
    UNION SELECT 7, 'Canales y Mecanismos', 'canales_mecanismos', 'texto', 7,
           'Describe los canales disponibles en la organizacion: correo electronico institucional, carteleras informativas, reuniones periodicas, intranet o plataforma digital, sistema de alarma, radio/megafono, buzon de sugerencias/denuncias, WhatsApp empresarial. Para cada canal indicar: tipo (formal/informal), registro que genera, responsable de gestion, frecuencia de uso.'
    UNION SELECT 8, 'Indicadores y Seguimiento', 'indicadores', 'texto', 8,
           'Define indicadores de gestion de la comunicacion SST: porcentaje de protocolos ejecutados segun la matriz, tiempo promedio de comunicacion vs plazo establecido, numero de auto-reportes recibidos, porcentaje de registros de comunicacion completos, cumplimiento de reuniones programadas (COPASST, Comite Convivencia). Formula de calculo, meta, frecuencia de medicion y responsable de cada indicador.'
) s
WHERE tc.tipo_documento = 'procedimiento_matriz_comunicacion'
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
WHERE tc.tipo_documento = 'procedimiento_matriz_comunicacion'
ON DUPLICATE KEY UPDATE rol_display = VALUES(rol_display);
SQL;

// SQL para plantilla
$sqlPlantilla = <<<'SQL'
INSERT INTO tbl_doc_plantillas (id_tipo, nombre, codigo_sugerido, tipo_documento, version, activo)
SELECT 3, 'Procedimiento Matriz de Comunicacion SST', 'PRC-MCO', 'procedimiento_matriz_comunicacion', '001', 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM tbl_doc_plantillas WHERE tipo_documento = 'procedimiento_matriz_comunicacion')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);
SQL;

// SQL para mapeo carpeta
$sqlMapeoCarpeta = <<<'SQL'
INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
VALUES ('PRC-MCO', '2.8.1')
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
        $stmt = $pdo->query("SELECT id_tipo_config, nombre FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'procedimiento_matriz_comunicacion'");
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
