<?php
/**
 * Script para agregar tipo de documento: Programa de Mantenimiento Periodico
 * Estandar: 4.2.5
 * Ejecutar: php app/SQL/agregar_programa_mantenimiento_periodico.php
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
('programa_mantenimiento_periodico',
 'Programa de Mantenimiento Periodico de Instalaciones, Equipos, Maquinas y Herramientas',
 'Establece el programa de mantenimiento preventivo, correctivo y periodico de las instalaciones, equipos, maquinas y herramientas de la organizacion',
 '4.2.5',
 'secciones_ia',
 'programas',
 'bi-tools',
 22)
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
           'Genera el objetivo del Programa de Mantenimiento Periodico de Instalaciones, Equipos, Maquinas y Herramientas. Debe establecer lineamientos para garantizar el funcionamiento seguro y optimo de todos los activos, cumplimiento del Decreto 1072/2015 (art. 2.2.4.6.24) y Resolucion 0312/2019 (estandar 4.2.5). Mencionar prevencion de accidentes por fallas en equipos e instalaciones.' as prompt_ia
    UNION SELECT 2, 'Alcance', 'alcance', 'texto', 2,
           'Define el alcance del programa. Aplica a todas las instalaciones, equipos, maquinas y herramientas utilizadas en las actividades de la organizacion. Incluye equipos propios, en comodato y arrendados. Cubre mantenimiento preventivo, correctivo y predictivo.'
    UNION SELECT 3, 'Definiciones', 'definiciones', 'texto', 3,
           'Define terminos clave: Mantenimiento preventivo, Mantenimiento correctivo, Mantenimiento predictivo, Ficha tecnica de equipo, Hoja de vida de equipo, Inspeccion preoperacional, Vida util, Orden de trabajo, Programa de mantenimiento, Calibracion, Equipo critico, Indicador de disponibilidad. 12-14 definiciones.'
    UNION SELECT 4, 'Marco Legal', 'marco_legal', 'texto', 4,
           'Presenta el marco normativo: Decreto 1072/2015 art. 2.2.4.6.24 (mantenimiento instalaciones y equipos), Resolucion 0312/2019 est. 4.2.5 (mantenimiento periodico), Ley 9/1979 (condiciones sanitarias), Resolucion 2400/1979 (seguridad industrial), NTC-ISO 45001:2018 (clausula 8.1.4), Resolucion 2013/1986 (COPASST), normativas tecnicas especificas segun tipo de equipo. Formato tabla.'
    UNION SELECT 5, 'Responsabilidades', 'responsabilidades', 'texto', 5,
           'Define responsabilidades: Alta Direccion (aprobar programa, asignar recursos, garantizar condiciones seguras), Responsable SG-SST (disenar, implementar, evaluar, reportar), COPASST/Vigia (verificar cumplimiento, inspecciones), Personal de mantenimiento (ejecutar, registrar, reportar anomalias), Trabajadores (reportar fallas, inspecciones preoperacionales, uso adecuado).'
    UNION SELECT 6, 'Inventario de Activos y Equipos', 'inventario_activos', 'texto', 6,
           'Genera el inventario de activos susceptibles de mantenimiento de la empresa. IMPORTANTE: Utilizar el inventario de activos proporcionado por el consultor como base. Clasificar por categoria (instalaciones, equipos de computo, equipos industriales, herramientas, vehiculos, sistemas especiales). Para cada activo incluir: descripcion, ubicacion, marca/modelo, estado actual, nivel de criticidad (alto/medio/bajo), frecuencia de mantenimiento recomendada.'
    UNION SELECT 7, 'Tipos y Frecuencias de Mantenimiento', 'tipos_frecuencias_mantenimiento', 'texto', 7,
           'Define los tipos de mantenimiento aplicables a cada categoria de activo: Preventivo (rutinas periodicas, lubricacion, limpieza, ajuste), Correctivo (reparaciones, reemplazos), Predictivo (monitoreo condiciones, analisis vibraciones). Para cada tipo especificar: frecuencia (diario, semanal, mensual, trimestral, semestral, anual), responsable, formato de registro, criterios de aceptacion. Generar cronograma anual basado en el inventario.'
    UNION SELECT 8, 'Indicadores y Seguimiento', 'indicadores_seguimiento', 'texto', 8,
           'Genera indicadores: Estructura (programa aprobado, presupuesto ejecutado vs asignado), Proceso (% cumplimiento programa mantenimiento meta>=90%, % equipos con ficha tecnica meta=100%, % inspecciones realizadas meta=100%), Resultado (tasa fallas por mantenimiento inadecuado meta<=2, disponibilidad operativa meta>=95%, tasa accidentes por fallas de equipos meta=0%). Con formula, meta, frecuencia medicion. Incluir mecanismo de seguimiento y revision.'
) s
WHERE tc.tipo_documento = 'programa_mantenimiento_periodico'
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
WHERE tc.tipo_documento = 'programa_mantenimiento_periodico'
ON DUPLICATE KEY UPDATE rol_display = VALUES(rol_display);
SQL;

// SQL para plantilla
$sqlPlantilla = <<<'SQL'
INSERT INTO tbl_doc_plantillas (id_tipo, nombre, codigo_sugerido, tipo_documento, version, activo)
SELECT 3, 'Programa de Mantenimiento Periodico de Instalaciones, Equipos, Maquinas y Herramientas', 'PRG-MTP', 'programa_mantenimiento_periodico', '001', 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM tbl_doc_plantillas WHERE tipo_documento = 'programa_mantenimiento_periodico')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);
SQL;

// SQL para mapeo carpeta
$sqlMapeoCarpeta = <<<'SQL'
INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
VALUES ('PRG-MTP', '4.2.5')
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

$localExito = false;

foreach ($conexiones as $entorno => $config) {
    echo "\n=== Ejecutando en $entorno ===\n";

    // Solo ejecutar produccion si local fue exitoso
    if ($entorno === 'produccion' && !$localExito) {
        echo "  [SKIP] No se ejecuta en produccion porque LOCAL fallo\n";
        continue;
    }

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
        $ok = true;
        $ok = ejecutarSQL($pdo, $sqlTipo, 'Tipo de documento') && $ok;
        $ok = ejecutarSQL($pdo, $sqlSecciones, 'Secciones (8)') && $ok;
        $ok = ejecutarSQL($pdo, $sqlFirmantes, 'Firmantes') && $ok;
        $ok = ejecutarSQL($pdo, $sqlPlantilla, 'Plantilla') && $ok;
        $ok = ejecutarSQL($pdo, $sqlMapeoCarpeta, 'Mapeo carpeta') && $ok;

        // Verificar resultado
        $stmt = $pdo->query("SELECT id_tipo_config, nombre FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'programa_mantenimiento_periodico'");
        $tipo = $stmt->fetch();
        if ($tipo) {
            echo "  [INFO] Tipo creado con ID: {$tipo['id_tipo_config']}\n";

            $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_doc_secciones_config WHERE id_tipo_config = {$tipo['id_tipo_config']}");
            $secciones = $stmt->fetch();
            echo "  [INFO] Secciones configuradas: {$secciones['total']}\n";
        }

        if ($entorno === 'local') {
            $localExito = $ok;
        }

    } catch (PDOException $e) {
        echo "  [ERROR] Conexion: " . $e->getMessage() . "\n";
        if ($entorno === 'local') {
            $localExito = false;
        }
    }
}

echo "\n=== Proceso completado ===\n";
