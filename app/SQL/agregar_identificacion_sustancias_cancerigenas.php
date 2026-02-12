<?php
/**
 * Script para agregar tipo de documento: Identificacion de Sustancias Cancerigenas o con Toxicidad Aguda
 * Estandar: 4.1.3
 * Ejecutar: php app/SQL/agregar_identificacion_sustancias_cancerigenas.php
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
('identificacion_sustancias_cancerigenas',
 'Identificacion de Sustancias Cancerigenas o con Toxicidad Aguda',
 'Identifica si la empresa procesa, manipula o trabaja con agentes o sustancias catalogadas como cancerigenas o con toxicidad aguda, causantes de enfermedades incluidas en la tabla de enfermedades laborales, priorizando los riesgos asociados y estableciendo acciones de prevencion e intervencion',
 '4.1.3',
 'secciones_ia',
 'procedimientos',
 'bi-radioactive',
 21)
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
           'Genera el objetivo del documento de Identificacion de Sustancias Cancerigenas o con Toxicidad Aguda. Debe mencionar: identificar si la empresa procesa, manipula o trabaja con agentes o sustancias catalogadas como cancerigenas o con toxicidad aguda incluidas en la Tabla de Enfermedades Laborales (Decreto 1477/2014), priorizar los riesgos asociados, y establecer acciones de prevencion e intervencion. Cumplimiento del Decreto 1072/2015 art. 2.2.4.6.15 y 2.2.4.6.23, Res. 0312/2019 estandar 4.1.3.' as prompt_ia
    UNION SELECT 2, 'Alcance', 'alcance', 'texto', 2,
           'Define el alcance: aplica a todos los procesos, actividades, areas y puestos de trabajo donde se procesen, manipulen, almacenen, transporten o se tenga contacto con sustancias catalogadas como cancerigenas (grupos IARC 1, 2A, 2B) o con toxicidad aguda. Incluye trabajadores directos, contratistas, subcontratistas y temporales. Cubre todas las sedes, centros de trabajo y actividades externas.'
    UNION SELECT 3, 'Definiciones', 'definiciones', 'texto', 3,
           'Define terminos clave: Sustancia cancerigena, Toxicidad aguda, Agente quimico, IARC (Agencia Internacional para la Investigacion del Cancer), Grupo 1 IARC (cancerigeno confirmado), Grupo 2A IARC (probablemente cancerigeno), Grupo 2B IARC (posiblemente cancerigeno), Sistema Globalmente Armonizado (SGA), Ficha de Datos de Seguridad (FDS/SDS), Valor Limite Permisible (TLV), Tabla de Enfermedades Laborales, Exposicion ocupacional, Vigilancia epidemiologica, Pictograma de peligro, Categoria de toxicidad aguda (1-4 SGA). 14-16 definiciones.'
    UNION SELECT 4, 'Marco Legal', 'marco_legal', 'texto', 4,
           'Marco normativo: Decreto 1072/2015 art. 2.2.4.6.15 y 2.2.4.6.23, Res. 0312/2019 estandar 4.1.3, Decreto 1477/2014 (Tabla de Enfermedades Laborales - Seccion I Grupo I: agentes cancerigenos), Decreto 1496/2018 (adopcion SGA sexta edicion revisada), Ley 55/1993 (Convenio OIT 170 sobre seguridad quimicos), Res. 2400/1979, Res. 2346/2007 (evaluaciones medicas), Res. 1792/1990 (valores limites), Ley 1562/2012. Formato tabla.'
    UNION SELECT 5, 'Responsabilidades', 'responsabilidades', 'texto', 5,
           'Responsabilidades: Alta Direccion (garantizar identificacion, recursos, priorizar riesgos, implementar controles), Responsable SST (inventariar sustancias, evaluar exposicion, articular con PVE, capacitar, actualizar), COPASST/Vigia (participar en identificacion, verificar controles, proponer medidas), Jefes de Area (reportar sustancias en su area, implementar controles, verificar FDS), Trabajadores (reportar exposicion, usar EPP, cumplir procedimientos, participar en capacitaciones).'
    UNION SELECT 6, 'Inventario de Sustancias Cancerigenas y con Toxicidad Aguda', 'inventario_sustancias', 'texto', 6,
           'Describe como se realiza el inventario: 1) Revision de materias primas, insumos, productos intermedios y finales, residuos y subproductos, 2) Consulta de FDS (16 secciones segun SGA), 3) Cruce con Tabla de Enfermedades Laborales (Decreto 1477/2014 Seccion I Grupo I), 4) Consulta clasificacion IARC (grupos 1, 2A, 2B), 5) Clasificacion SGA por toxicidad aguda (categorias 1-4: mortal, mortal, toxico, nocivo). Formato de inventario: sustancia, CAS, clasificacion IARC, categoria SGA, area de uso, trabajadores expuestos, FDS disponible.'
    UNION SELECT 7, 'Clasificacion segun SGA y IARC', 'clasificacion_sga_iarc', 'texto', 7,
           'Describe los sistemas de clasificacion: IARC: Grupo 1 (cancerigeno confirmado, ej: asbesto, benceno, formaldehido), Grupo 2A (probablemente cancerigeno, ej: glifosato), Grupo 2B (posiblemente cancerigeno). SGA Toxicidad aguda: Categoria 1 (mortal via oral <=5 mg/kg), Categoria 2 (mortal), Categoria 3 (toxico), Categoria 4 (nocivo). Pictogramas: calavera y tibias cruzadas (cat 1-3), signo admiracion (cat 4), peligro para la salud (cancerigeno). Etiquetado segun Decreto 1496/2018.'
    UNION SELECT 8, 'Evaluacion y Priorizacion de Riesgos Asociados', 'evaluacion_priorizacion', 'texto', 8,
           'Describe evaluacion de riesgos: 1) Identificar peligro quimico en matriz (GTC 45), 2) Evaluar exposicion (concentracion vs TLV, tiempo, frecuencia, via de ingreso), 3) Determinar nivel de riesgo (NR), 4) Priorizar: SIEMPRE no aceptable para cancerigenos Grupo 1 IARC, 5) Considerar: numero de expuestos, vulnerabilidad (embarazadas, menores), simultaneidad de exposiciones, condiciones ambientales. Los riesgos de sustancias cancerigenas deben priorizarse sobre otros riesgos quimicos. Estandar 4.1.3 exige priorizacion explicita.'
    UNION SELECT 9, 'Medidas de Prevencion e Intervencion', 'medidas_prevencion', 'texto', 9,
           'Medidas segun jerarquia de controles: 1) Eliminacion (sustituir proceso que use cancerigeno), 2) Sustitucion (reemplazar sustancia por una menos peligrosa), 3) Controles ingenieria (ventilacion localizada, sistemas cerrados, extractores, cabinas), 4) Controles administrativos (procedimientos, senalizacion SGA, rotacion, permisos de trabajo, restriccion acceso, FDS accesibles, capacitacion especifica), 5) EPP (especifico para quimicos: respiradores con filtro, guantes quimicos, gafas hermeticas, overol). Para cancerigenos: prioridad SIEMPRE eliminacion o sustitucion. Protocolo derrames y emergencias quimicas.'
    UNION SELECT 10, 'Vigilancia de la Salud de Trabajadores Expuestos', 'vigilancia_salud', 'texto', 10,
           'Describe vigilancia: 1) Evaluaciones medicas especificas (ingreso, periodicas segun exposicion, egreso con seguimiento post-ocupacional), 2) Biomarcadores de exposicion y efecto segun sustancia, 3) Programa de Vigilancia Epidemiologica para expuestos a cancerigenos, 4) Comunicacion de resultados al trabajador (Res. 2346/2007), 5) Seguimiento post-ocupacional (cancer latencia 10-30 anos), 6) Articulacion con EPS y ARL, 7) Registro historico de exposicion (minimo 30 anos para cancerigenos). Frecuencia: semestral o segun concepto medico ocupacional.'
    UNION SELECT 11, 'Indicadores de Gestion', 'indicadores', 'texto', 11,
           'Genera indicadores: Proceso (% sustancias inventariadas con FDS meta=100%, % trabajadores expuestos con evaluacion medica especifica meta=100%, % capacitaciones ejecutadas meta>=90%, % mediciones ambientales realizadas meta>=90%), Resultado (% sustancias cancerigenas sustituidas o eliminadas meta creciente, niveles de exposicion dentro de TLV meta=100%, cero enfermedades laborales por cancerigenos meta=0, % controles implementados meta>=90%). Con formula, meta, frecuencia.'
    UNION SELECT 12, 'Registros y Evidencias', 'registros_evidencias', 'texto', 12,
           'Registros obligatorios: Inventario de sustancias cancerigenas y con toxicidad aguda, Fichas de Datos de Seguridad (FDS) actualizadas, Matriz de peligros actualizada (peligro quimico priorizado), Evaluaciones medicas especificas, Resultados de mediciones ambientales (higiene industrial), Certificados de capacitacion en riesgo quimico, Registros de entrega y uso de EPP quimico, Procedimientos de manejo seguro, Plan de emergencia quimica, Programa de sustitucion. Conservacion: 30 anos para registros de exposicion a cancerigenos (Res. 0312/2019). Codificacion: FT-SST-ISC-01 a FT-SST-ISC-04.'
) s
WHERE tc.tipo_documento = 'identificacion_sustancias_cancerigenas'
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
WHERE tc.tipo_documento = 'identificacion_sustancias_cancerigenas'
ON DUPLICATE KEY UPDATE rol_display = VALUES(rol_display);
SQL;

// SQL para plantilla
$sqlPlantilla = <<<'SQL'
INSERT INTO tbl_doc_plantillas (id_tipo, nombre, codigo_sugerido, tipo_documento, version, activo)
SELECT 3, 'Identificacion de Sustancias Cancerigenas o con Toxicidad Aguda', 'PRC-ISC', 'identificacion_sustancias_cancerigenas', '001', 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM tbl_doc_plantillas WHERE tipo_documento = 'identificacion_sustancias_cancerigenas')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);
SQL;

// SQL para mapeo carpeta
$sqlMapeoCarpeta = <<<'SQL'
INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
VALUES ('PRC-ISC', '4.1.3')
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
        $ok = ejecutarSQL($pdo, $sqlSecciones, 'Secciones (12)') && $ok;
        $ok = ejecutarSQL($pdo, $sqlFirmantes, 'Firmantes (3)') && $ok;
        $ok = ejecutarSQL($pdo, $sqlPlantilla, 'Plantilla') && $ok;
        $ok = ejecutarSQL($pdo, $sqlMapeoCarpeta, 'Mapeo carpeta') && $ok;

        // Verificar resultado
        $stmt = $pdo->query("SELECT id_tipo_config, nombre FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'identificacion_sustancias_cancerigenas'");
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
