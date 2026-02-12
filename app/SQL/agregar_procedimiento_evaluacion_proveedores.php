<?php
/**
 * Script para agregar tipo de documento: Procedimiento de Evaluacion y Seleccion de Proveedores y Contratistas
 * Estandar: 2.10.1
 * Ejecutar: php app/SQL/agregar_procedimiento_evaluacion_proveedores.php
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
('procedimiento_evaluacion_proveedores',
 'Procedimiento de Evaluacion y Seleccion de Proveedores y Contratistas',
 'Establece los criterios y metodologia para evaluar y seleccionar proveedores y contratistas considerando aspectos de SST y cumplimiento de estandares minimos',
 '2.10.1',
 'secciones_ia',
 'procedimientos',
 'bi-people-fill',
 17)
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
           'Genera el objetivo del procedimiento de evaluacion y seleccion de proveedores y contratistas en materia de SST. Debe mencionar el cumplimiento del Decreto 1072 de 2015 (art. 2.2.4.6.28) y la Resolucion 0312 de 2019 (estandar 2.10.1).' as prompt_ia
    UNION SELECT 2, 'Alcance', 'alcance', 'texto', 2,
           'Define el alcance del procedimiento. Aplica a todos los proveedores, contratistas y subcontratistas que presten servicios a la organizacion, desde la seleccion hasta la finalizacion del contrato.'
    UNION SELECT 3, 'Definiciones', 'definiciones', 'texto', 3,
           'Define los terminos clave: Proveedor, Contratista, Subcontratista, Evaluacion de proveedores, Estandares Minimos SST, SG-SST, Riesgo laboral, Actividades de alto riesgo.'
    UNION SELECT 4, 'Marco Legal', 'marco_legal', 'texto', 4,
           'Lista el marco legal aplicable: Decreto 1072/2015 art. 2.2.4.6.28, Resolucion 0312/2019 est. 2.10.1, Ley 1562/2012, Decreto 723/2013, Resolucion 1409/2012. Organizar por tipo de norma.'
    UNION SELECT 5, 'Responsabilidades', 'responsabilidades', 'texto', 5,
           'Define responsabilidades: Alta Direccion (recursos, aprobar procedimiento), Responsable SST (verificar cumplimiento, evaluar documentacion), Area Compras (incluir criterios SST), COPASST (verificar condiciones), Contratistas (cumplir requisitos, reportar accidentes).'
    UNION SELECT 6, 'Criterios de Evaluacion SST', 'criterios_evaluacion_sst', 'texto', 6,
           'Describe criterios de evaluacion SST: obligatorios (afiliacion ARL, pagos seguridad social, estandares minimos) y ponderables (SG-SST documentado, matriz peligros, programa capacitacion, EPP, accidentalidad, procedimientos trabajo seguro, plan emergencias). Presentar como checklist.'
    UNION SELECT 7, 'Proceso de Seleccion', 'proceso_seleccion', 'texto', 7,
           'Describe paso a paso: Fase 1 solicitud documentacion, Fase 2 evaluacion documental, Fase 3 calificacion y puntaje (Aprobado >80%, Condicionado 60-80%, No aprobado <60%), Fase 4 comunicacion resultados, Fase 5 registro proveedores calificados.'
    UNION SELECT 8, 'Requisitos SST para Contratistas', 'requisitos_sst_contratistas', 'texto', 8,
           'Describe requisitos SST: antes de iniciar (certificados ARL, SG-SST, induccion, competencias), durante ejecucion (cumplir normas, EPP, reportar incidentes, participar simulacros), al finalizar (informe SST, estado salud trabajadores). Segun Decreto 1072 art. 2.2.4.6.28.'
    UNION SELECT 9, 'Seguimiento y Reevaluacion', 'seguimiento_reevaluacion', 'texto', 9,
           'Describe seguimiento durante contrato (inspecciones, indicadores, verificacion pagos) y reevaluacion periodica (anual, criterios cumplimiento). Acciones ante incumplimiento: amonestacion, suspension, terminacion, exclusion listado.'
    UNION SELECT 10, 'Registros y Formatos', 'registros_formatos', 'texto', 10,
           'Lista registros y formatos: FT-SST-EP01 Evaluacion Inicial, FT-SST-EP02 Lista Verificacion, FT-SST-EP03 Reevaluacion, FT-SST-EP04 Acta Inspeccion, FT-SST-EP05 Registro Induccion, FT-SST-EP06 Control Documentacion. Tiempo retencion 20 anos.'
) s
WHERE tc.tipo_documento = 'procedimiento_evaluacion_proveedores'
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), prompt_ia = VALUES(prompt_ia);
SQL;

// SQL para firmantes (3 firmantes: consultor, responsable SST, representante legal)
$sqlFirmantes = <<<'SQL'
INSERT INTO tbl_doc_firmantes_config
(id_tipo_config, firmante_tipo, rol_display, columna_encabezado, orden, mostrar_licencia)
SELECT tc.id_tipo_config, f.firmante_tipo, f.rol_display, f.columna_encabezado, f.orden, f.mostrar_licencia
FROM tbl_doc_tipo_configuracion tc
CROSS JOIN (
    SELECT 'consultor_sst' as firmante_tipo, 'Elaboro' as rol_display, 'Elaboro / Consultor SST' as columna_encabezado, 1 as orden, 1 as mostrar_licencia
    UNION SELECT 'responsable_sst', 'Reviso', 'Reviso / Responsable del SG-SST', 2, 1
    UNION SELECT 'representante_legal', 'Aprobo', 'Aprobo / Representante Legal', 3, 0
) f
WHERE tc.tipo_documento = 'procedimiento_evaluacion_proveedores'
ON DUPLICATE KEY UPDATE rol_display = VALUES(rol_display);
SQL;

// SQL para plantilla
$sqlPlantilla = <<<'SQL'
INSERT INTO tbl_doc_plantillas (id_tipo, nombre, codigo_sugerido, tipo_documento, version, activo)
SELECT 3, 'Procedimiento de Evaluacion y Seleccion de Proveedores y Contratistas', 'PRC-EVP', 'procedimiento_evaluacion_proveedores', '001', 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM tbl_doc_plantillas WHERE tipo_documento = 'procedimiento_evaluacion_proveedores')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);
SQL;

// SQL para mapeo carpeta
$sqlMapeoCarpeta = <<<'SQL'
INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
VALUES ('PRC-EVP', '2.10.1')
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
        $ok = true;
        $ok = ejecutarSQL($pdo, $sqlTipo, 'Tipo de documento') && $ok;
        $ok = ejecutarSQL($pdo, $sqlSecciones, 'Secciones') && $ok;
        $ok = ejecutarSQL($pdo, $sqlFirmantes, 'Firmantes') && $ok;
        $ok = ejecutarSQL($pdo, $sqlPlantilla, 'Plantilla') && $ok;
        $ok = ejecutarSQL($pdo, $sqlMapeoCarpeta, 'Mapeo carpeta') && $ok;

        // Verificar resultado
        $stmt = $pdo->query("SELECT id_tipo_config, nombre FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'procedimiento_evaluacion_proveedores'");
        $tipo = $stmt->fetch();
        if ($tipo) {
            echo "  [INFO] Tipo creado con ID: {$tipo['id_tipo_config']}\n";

            $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_doc_secciones_config WHERE id_tipo_config = {$tipo['id_tipo_config']}");
            $secciones = $stmt->fetch();
            echo "  [INFO] Secciones configuradas: {$secciones['total']}\n";

            $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_doc_firmantes_config WHERE id_tipo_config = {$tipo['id_tipo_config']}");
            $firmantes = $stmt->fetch();
            echo "  [INFO] Firmantes configurados: {$firmantes['total']}\n";
        }

        if ($entorno === 'local' && !$ok) {
            echo "\n  [ABORT] Errores en LOCAL - NO se ejecutara en produccion\n";
            break;
        }

    } catch (PDOException $e) {
        echo "  [ERROR] Conexion: " . $e->getMessage() . "\n";
        if ($entorno === 'local') {
            echo "\n  [ABORT] Error en LOCAL - NO se ejecutara en produccion\n";
            break;
        }
    }
}

echo "\n=== Proceso completado ===\n";
