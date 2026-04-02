<?php
/**
 * GOLD STANDARD — Script SQL para agregar un programa Tipo B completo.
 * Usar como referencia para crear nuevos programas.
 * Guia: docs/MODULO_NUMERALES_SGSST/03_MODULO_3_PARTES/ZZ_98_COMO_AGREGAR_PROGRAMA.md
 *
 * Script para agregar tipo de documento: Programa de Inspecciones
 * Estandar: 4.2.4
 * Ejecutar: php app/SQL/agregar_programa_inspecciones.php
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
('programa_inspecciones',
 'Programa de Inspecciones',
 'Programa de inspecciones a instalaciones, maquinaria o equipos con participacion del COPASST o Vigia SST, segun Resolucion 0312/2019 estandar 4.2.4 y Decreto 1072/2015',
 '4.2.4',
 'programa_con_pta',
 'programas',
 'bi-search',
 21)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), descripcion = VALUES(descripcion), updated_at = NOW();
SQL;

// SQL para secciones (10 secciones)
$sqlSecciones = <<<'SQL'
INSERT INTO tbl_doc_secciones_config
(id_tipo_config, numero, nombre, seccion_key, tipo_contenido, orden, prompt_ia)
SELECT tc.id_tipo_config, s.numero, s.nombre, s.seccion_key, s.tipo_contenido, s.orden, s.prompt_ia
FROM tbl_doc_tipo_configuracion tc
CROSS JOIN (
    SELECT 1 as numero, 'Objetivo' as nombre, 'objetivo' as seccion_key, 'texto' as tipo_contenido, 1 as orden,
           'Genera el objetivo general y objetivos especificos del Programa de Inspecciones a instalaciones, maquinaria o equipos. Objetivo general: garantizar condiciones seguras en las instalaciones, maquinaria y equipos mediante inspecciones sistematicas con participacion del COPASST o Vigia SST. Objetivos especificos: identificar condiciones inseguras y actos inseguros, verificar el cumplimiento de controles existentes, priorizar acciones correctivas segun nivel de riesgo, promover la cultura preventiva. Marco normativo: Resolucion 0312/2019 estandar 4.2.4, Decreto 1072/2015 art. 2.2.4.6.12 numeral 9, NTC 4114.' as prompt_ia
    UNION SELECT 2, 'Alcance', 'alcance', 'texto', 2,
           'Define el alcance del programa de inspecciones. Aplica a todas las sedes, areas, instalaciones locativas, maquinaria, equipos, herramientas y elementos de proteccion personal de la empresa. Incluye trabajadores directos, contratistas y visitantes. Cubre inspecciones planeadas y no planeadas. Especifica las areas criticas segun actividad economica de la empresa: areas operativas, administrativas, almacenamiento, zonas comunes, areas de emergencia.'
    UNION SELECT 3, 'Marco Normativo', 'marco_normativo', 'texto', 3,
           'Presenta el marco normativo aplicable al programa de inspecciones: Resolucion 0312/2019 estandar 4.2.4 (inspecciones a instalaciones con COPASST), Decreto 1072/2015 art. 2.2.4.6.12 numeral 9 (inspecciones sistematicas), Resolucion 2400/1979 (condiciones locativas, iluminacion, ventilacion, instalaciones sanitarias), NTC 4114 (inspecciones planeadas de seguridad), Ley 9/1979 Codigo Sanitario, Resolucion 2013/1986 (funcionamiento COPASST). Formato tabla con norma, articulo/estandar, descripcion y aplicacion al programa.'
    UNION SELECT 4, 'Definiciones', 'definiciones', 'texto', 4,
           'Define terminos clave del programa: Inspeccion planeada, Inspeccion no planeada, Inspeccion general, Inspeccion especifica, Inspeccion pre-operacional, Condicion insegura, Acto inseguro, Hallazgo, Accion correctiva, Accion preventiva, Lista de verificacion (checklist), Peligro, Riesgo, Control, COPASST, Vigia SST, Instalacion locativa, Maquinaria, Equipo, EPP, Equipo de emergencia. Minimo 15 definiciones claras y aplicables al contexto de la empresa.'
    UNION SELECT 5, 'Responsabilidades', 'responsabilidades', 'texto', 5,
           'Define responsabilidades por rol: Representante Legal (aprobar programa, asignar recursos, implementar correctivos), Responsable SST (disenar, coordinar y ejecutar programa, elaborar cronograma, capacitar inspectores, consolidar hallazgos, hacer seguimiento), COPASST o Vigia SST (participar en inspecciones como lo exige el estandar 4.2.4, verificar cierre de hallazgos, proponer mejoras), Supervisores/Lideres de area (facilitar acceso, implementar correctivos en su area, reportar novedades), Trabajadores (mantener orden y aseo, reportar condiciones inseguras, participar en inspecciones de su area), ARL (asesoria tecnica, acompanamiento en inspecciones especiales).'
    UNION SELECT 6, 'Tipos de Inspecciones', 'tipos_inspecciones', 'texto', 6,
           'Describe los tipos de inspecciones del programa: 1) Inspecciones generales (recorrido completo por areas, frecuencia mensual/trimestral), 2) Inspecciones especificas (maquinaria, equipos criticos, areas confinadas, trabajo en alturas), 3) Inspecciones pre-operacionales (antes de usar maquinaria/equipo, diaria/por turno), 4) Inspecciones de EPP (inventario, estado, entrega, uso correcto), 5) Inspecciones de equipos de emergencia (extintores, camillas, botiquines, senalizacion, alarmas, rutas de evacuacion), 6) Inspecciones de orden y aseo (5S, almacenamiento, manejo de residuos). Para cada tipo: que se inspecciona, frecuencia, responsable, formato/lista de verificacion a usar. Adaptar segun actividad economica de la empresa.'
    UNION SELECT 7, 'Metodologia de Inspeccion', 'metodologia', 'texto', 7,
           'Describe la metodologia paso a paso: 1) Preparacion (revisar hallazgos anteriores, seleccionar lista de verificacion, coordinar con COPASST), 2) Ejecucion (recorrido sistematico, observacion de condiciones y comportamientos, dialogo con trabajadores, registro fotografico), 3) Registro de hallazgos (descripcion, ubicacion, clasificacion de severidad: critico/mayor/menor, evidencia), 4) Clasificacion y priorizacion (matriz de priorizacion segun probabilidad e impacto), 5) Asignacion de acciones (responsable, plazo, recurso), 6) Seguimiento y cierre (verificacion de implementacion, eficacia del control). Incluir criterios de clasificacion de hallazgos y tiempos de respuesta segun severidad: critico 24-48h, mayor 1 semana, menor 1 mes.'
    UNION SELECT 8, 'Cronograma de Inspecciones', 'cronograma_inspecciones', 'texto', 8,
           'Genera el cronograma anual de inspecciones distribuido por meses. IMPORTANTE: debe referenciar las actividades del Plan de Trabajo Anual (PTA) del programa. Incluir para cada mes: tipo de inspeccion, area/equipo a inspeccionar, responsable (siempre con COPASST segun 4.2.4), formato a utilizar. Distribucion sugerida: Ene planificacion, Feb capacitacion COPASST, Mar-Jul primer ciclo de inspecciones (locativas, maquinaria, emergencia, EPP, almacenamiento), Ago seguimiento, Sep-Oct segundo ciclo, Nov evaluacion, Dic informe anual. Presentar en formato tabla con columnas: Mes, Actividad, Tipo de inspeccion, Area, Responsable, Registro.'
    UNION SELECT 9, 'Hallazgos y Acciones Correctivas', 'hallazgos_acciones', 'texto', 9,
           'Describe el sistema de gestion de hallazgos: 1) Registro (formato estandarizado con: fecha, area, descripcion, clasificacion, evidencia fotografica, responsable de cierre), 2) Clasificacion de severidad (Critico: riesgo inminente de accidente grave, cierre inmediato 24-48h; Mayor: riesgo significativo, cierre en 1 semana; Menor: riesgo bajo, cierre en 1 mes), 3) Plan de accion (accion correctiva, responsable, fecha compromiso, recurso requerido), 4) Seguimiento (verificacion de implementacion, registro de avance, escalamiento si hay incumplimiento), 5) Cierre (verificacion de eficacia, antes/despues, firma de responsable y COPASST), 6) Analisis de tendencias (areas con mas hallazgos, tipos recurrentes, efectividad de acciones). El COPASST debe participar en la verificacion de cierre segun estandar 4.2.4.'
    UNION SELECT 10, 'Indicadores de Gestion', 'indicadores_gestion', 'texto', 10,
           'Genera indicadores para medir el programa de inspecciones. IMPORTANTE: debe referenciar los indicadores configurados en el modulo de indicadores del programa. Incluir indicadores de proceso: cumplimiento del cronograma de inspecciones (meta 100%), participacion del COPASST (meta 90%), cobertura de areas inspeccionadas (meta 100%), cierre oportuno de hallazgos (meta 80%). Indicadores de resultado: eficacia de acciones correctivas (meta 85%), reduccion de condiciones inseguras (meta 10% reduccion), indice de condiciones inseguras por area (meta <15%). Para cada indicador: nombre, formula, meta, periodicidad de medicion, fuente de datos, responsable de medicion. Presentar en formato tabla.'
) s
WHERE tc.tipo_documento = 'programa_inspecciones'
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
WHERE tc.tipo_documento = 'programa_inspecciones'
ON DUPLICATE KEY UPDATE rol_display = VALUES(rol_display);
SQL;

// SQL para plantilla
$sqlPlantilla = <<<'SQL'
INSERT INTO tbl_doc_plantillas (id_tipo, nombre, codigo_sugerido, tipo_documento, version, activo)
SELECT 3, 'Programa de Inspecciones', 'PRG-INS-001', 'programa_inspecciones', '001', 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM tbl_doc_plantillas WHERE tipo_documento = 'programa_inspecciones')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);
SQL;

// SQL para mapeo carpeta
$sqlMapeoCarpeta = <<<'SQL'
INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
VALUES ('PRG-INS-001', '4.2.4')
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
        $ok = ejecutarSQL($pdo, $sqlSecciones, 'Secciones (10)') && $ok;
        $ok = ejecutarSQL($pdo, $sqlFirmantes, 'Firmantes') && $ok;
        $ok = ejecutarSQL($pdo, $sqlPlantilla, 'Plantilla') && $ok;
        $ok = ejecutarSQL($pdo, $sqlMapeoCarpeta, 'Mapeo carpeta') && $ok;

        // Verificar resultado
        $stmt = $pdo->query("SELECT id_tipo_config, nombre FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'programa_inspecciones'");
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
