<?php
/**
 * Script para agregar tipo de documento: Procedimiento de Adquisiciones en SST
 * Estandar: 2.9.1
 * Ejecutar: php app/SQL/agregar_procedimiento_adquisiciones.php
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
('procedimiento_adquisiciones',
 'Procedimiento de Adquisiciones en SST',
 'Establece los criterios de seguridad y salud en el trabajo para la adquisicion de productos y contratacion de servicios',
 '2.9.1',
 'secciones_ia',
 'procedimientos',
 'bi-cart-check',
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
           'Genera el objetivo del Procedimiento de Adquisiciones en SST. Debe mencionar el cumplimiento del Decreto 1072 de 2015 (articulo 2.2.4.6.27) y la Resolucion 0312 de 2019 (estandar 2.9.1). Enfocarse en garantizar que las compras y contrataciones cumplan especificaciones SST.' as prompt_ia
    UNION SELECT 2, 'Alcance', 'alcance', 'texto', 2,
           'Define el alcance del procedimiento. Aplica a todas las compras de productos, equipos, materiales, insumos, sustancias quimicas y EPP, asi como a la contratacion de servicios, contratistas y subcontratistas.'
    UNION SELECT 3, 'Definiciones', 'definiciones', 'texto', 3,
           'Define los terminos clave: Adquisicion, Especificaciones tecnicas en SST, Proveedor, Contratista, Subcontratista, Ficha de Datos de Seguridad (FDS), EPP, Evaluacion de proveedores, Sustancia quimica peligrosa, Gestion del cambio. 10-12 definiciones.'
    UNION SELECT 4, 'Marco Legal', 'marco_legal', 'texto', 4,
           'Presenta el marco normativo: Decreto 1072/2015 art. 2.2.4.6.27 (Adquisiciones) y art. 2.2.4.6.28 (Contratacion), Resolucion 0312/2019 est. 2.9.1, Ley 55/1993 (productos quimicos), Decreto 1496/2018 (SGA), Resolucion 0773/2021 (EPP). Formato tabla.'
    UNION SELECT 5, 'Responsabilidades', 'responsabilidades', 'texto', 5,
           'Define responsabilidades: Alta Direccion (aprobar, recursos), Responsable SG-SST (especificaciones, verificacion), Area de Compras (incluir SST en ordenes, verificar), COPASST/Vigia (revision criterios), Contratistas y Proveedores (cumplir, documentacion).'
    UNION SELECT 6, 'Criterios SST para Adquisiciones de Productos', 'criterios_adquisiciones_productos', 'texto', 6,
           'Genera criterios SST por categoria: Equipos y herramientas (certificaciones, manuales), EPP (normas tecnicas, certificados, compatibilidad), Sustancias quimicas (FDS, etiquetado SGA, compatibilidad), Materiales generales (manipulacion, disposicion).'
    UNION SELECT 7, 'Criterios SST para Contratacion de Servicios', 'criterios_contratacion_servicios', 'texto', 7,
           'Genera criterios SST para contratacion: Requisitos previos (SG-SST, afiliaciones, certificaciones), Requisitos contractuales (clausulas SST, reportar AT), Trabajos alto riesgo (permisos, certificaciones alturas/confinados), Seguimiento (inspecciones).'
    UNION SELECT 8, 'Procedimiento de Evaluacion y Seleccion', 'procedimiento_evaluacion_seleccion', 'texto', 8,
           'Genera procedimiento paso a paso: 1) Identificacion necesidades y peligros, 2) Busqueda y preseleccion con documentacion SST, 3) Evaluacion con criterios ponderados, 4) Seleccion e inclusion clausulas SST, 5) Reevaluacion periodica anual.'
    UNION SELECT 9, 'Seguimiento y Control', 'seguimiento_control', 'texto', 9,
           'Genera mecanismos: Indicadores (% compras con SST verificadas, % contratistas con SG-SST), Verificacion (inspeccion recepcion, auditorias proveedores criticos), Acciones ante incumplimiento (devolucion, suspension, correctiva).'
    UNION SELECT 10, 'Registros y Evidencias', 'registros_evidencias', 'texto', 10,
           'Lista de registros: Formato especificaciones SST, ordenes con clausulas SST, FDS, certificados EPP, evaluacion contratistas, contratos con SST, verificacion afiliaciones, base proveedores aprobados. Retencion minima 20 anos (Decreto 1072/2015 art. 2.2.4.6.13).'
) s
WHERE tc.tipo_documento = 'procedimiento_adquisiciones'
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
WHERE tc.tipo_documento = 'procedimiento_adquisiciones'
ON DUPLICATE KEY UPDATE rol_display = VALUES(rol_display);
SQL;

// SQL para plantilla
$sqlPlantilla = <<<'SQL'
INSERT INTO tbl_doc_plantillas (id_tipo, nombre, codigo_sugerido, tipo_documento, version, activo)
SELECT 3, 'Procedimiento de Adquisiciones en SST', 'PRC-ADQ', 'procedimiento_adquisiciones', '001', 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM tbl_doc_plantillas WHERE tipo_documento = 'procedimiento_adquisiciones')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);
SQL;

// SQL para mapeo carpeta
$sqlMapeoCarpeta = <<<'SQL'
INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
VALUES ('PRC-ADQ', '2.9.1')
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
        $stmt = $pdo->query("SELECT id_tipo_config, nombre FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'procedimiento_adquisiciones'");
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
