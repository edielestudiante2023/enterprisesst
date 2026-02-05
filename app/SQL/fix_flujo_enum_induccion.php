<?php
/**
 * Script para corregir el ENUM flujo y reinsertar tipo de documento
 * Ejecutar: php app/SQL/fix_flujo_enum_induccion.php
 */

echo "=== FIX ENUM FLUJO - MÓDULO INDUCCIÓN ===\n";
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

// SQL para modificar el ENUM y agregar 'programa_con_pta'
$sqlAlterEnum = <<<'SQL'
ALTER TABLE tbl_doc_tipo_configuracion
MODIFY COLUMN flujo ENUM('secciones_ia', 'formulario', 'carga_archivo', 'mixto', 'programa_con_pta') DEFAULT 'secciones_ia';
SQL;

// SQL para insertar/actualizar el tipo de documento
$sqlTipoDocumento = <<<'SQL'
INSERT INTO tbl_doc_tipo_configuracion
(tipo_documento, nombre, descripcion, estandar, flujo, categoria, icono, orden, activo)
VALUES
('programa_induccion_reinduccion',
 'Programa de Inducción y Reinducción en SG-SST',
 'Programa que establece el proceso de inducción y reinducción para todos los trabajadores, incluyendo identificación de peligros, evaluación de riesgos y controles para prevención de ATEL.',
 '1.2.2',
 'programa_con_pta',
 'programas',
 'bi-person-badge',
 2,
 1)
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    descripcion = VALUES(descripcion),
    flujo = VALUES(flujo),
    updated_at = NOW();
SQL;

// SQL para reinsertar secciones (por si no se insertaron por falta del tipo)
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
    SELECT 1 as numero, 'Objetivo' as nombre, 'objetivo' as seccion_key, 'texto' as tipo_contenido, NULL as tabla_dinamica_tipo, NULL as sincronizar_bd, 1 as es_obligatoria, 1 as orden,
           'Genera el objetivo del programa de inducción y reinducción para {empresa}. Debe mencionar que busca facilitar el conocimiento global de la empresa y el SG-SST al trabajador, mediante información sobre objetivos, metas, reglamentaciones, procedimientos y valores. Usa los datos del contexto del cliente.' as prompt_ia

    UNION SELECT 2, 'Alcance', 'alcance', 'texto', NULL, NULL, 1, 2,
           'Define el alcance del programa de inducción. Debe aplicarse: (1) Antes de iniciar labores después de vinculación legal, (2) Personal con cambio de cargo, (3) Personal que requiera reinducción, (4) Post-incapacidad por accidente de trabajo. Personaliza según el tipo de empresa y número de trabajadores del contexto.'

    UNION SELECT 3, 'Requisitos Generales', 'requisitos_generales', 'texto', NULL, NULL, 1, 3,
           'Describe los requisitos generales del proceso de inducción como parte fundamental de la formación y desarrollo del personal. Menciona que es el complemento del proceso de selección y el inicio de la etapa de socialización. Usa el nombre de la empresa del contexto.'

    UNION SELECT 4, 'Contenido: Esquema General del Proceso', 'contenido_esquema', 'tabla_dinamica', 'etapas_induccion', 'induccion_etapas', 1, 4,
           'Esta sección muestra las 5 etapas del proceso de inducción con sus temas. Los datos vienen de la tabla tbl_induccion_etapas.'

    UNION SELECT 5, 'Etapa 1: Introducción a la Empresa', 'etapa_introduccion', 'texto', NULL, NULL, 1, 5,
           'Genera el contenido de la Etapa 1 - Introducción. Debe incluir: Historia de la empresa, Principios y Valores, Misión y Visión, Ubicación y objetivos, Organigrama. Personaliza según los datos del contexto del cliente (razón social, sector económico, ciudad).'

    UNION SELECT 6, 'Etapa 2: Seguridad y Salud en el Trabajo', 'etapa_sst', 'texto', NULL, NULL, 1, 6,
           'Genera el contenido de la Etapa 2 - SST. IMPORTANTE: Incluye temas BASE (Política SST, Reglamento higiene, Plan emergencia, Derechos y deberes) + temas PERSONALIZADOS según los peligros_identificados del cliente. Si tiene trabajo en alturas, incluye ese tema. Si tiene riesgo químico, incluye manejo de sustancias. Menciona si tiene COPASST o Vigía según tiene_copasst/tiene_vigia_sst del contexto.'

    UNION SELECT 7, 'Etapa 3: Relaciones Laborales', 'etapa_relaciones', 'texto', NULL, NULL, 1, 7,
           'Genera el contenido de la Etapa 3 - Relaciones Laborales. Incluye: Reglamento Interno de Trabajo, Explicación de pago salarial, Horario laboral según turnos_trabajo del contexto, Prestaciones legales y extralegales.'

    UNION SELECT 8, 'Etapa 4: Conocimiento y Recorrido de Instalaciones', 'etapa_recorrido', 'texto', NULL, NULL, 1, 8,
           'Genera el contenido de la Etapa 4 - Recorrido. Incluye: Presentación del equipo de trabajo, Áreas administrativas, Áreas operativas/producción, Rutas de evacuación, Puntos de encuentro. Si el cliente tiene múltiples sedes (numero_sedes > 1), menciona que el recorrido se hace en la sede asignada.'

    UNION SELECT 9, 'Etapa 5: Entrenamiento al Cargo', 'etapa_entrenamiento', 'texto', NULL, NULL, 1, 9,
           'Genera el contenido de la Etapa 5 - Entrenamiento. Describe el proceso de entrenamiento en el puesto de trabajo y área específica. Menciona que incluye: funciones del cargo, procedimientos operativos, uso de herramientas/equipos, EPP requeridos según los peligros_identificados del cliente.'

    UNION SELECT 10, 'Entrega de Memorias', 'entrega_memorias', 'texto', NULL, NULL, 1, 10,
           'Genera la sección de entrega de memorias/documentación. Incluye documentos digitales (Política SST, Política no alcohol/drogas, Reglamento higiene, Responsabilidades SST, Derechos y deberes, Reglamento Interno) y documentos físicos (Copia contrato, Afiliación EPS, Carné ARL).'

    UNION SELECT 11, 'Evaluación y Control', 'evaluacion_control', 'texto', NULL, NULL, 1, 11,
           'Genera la sección de evaluación y control. Menciona: Formato de Control y Evaluación del Proceso de Inducción, responsable de evaluar, archivo en hoja de vida, entrega de copia al empleado, uso para indicador de cobertura.'

    UNION SELECT 12, 'Indicadores del Programa', 'indicadores', 'tabla_dinamica', 'indicadores_induccion', 'indicadores_sst', 1, 12,
           'Esta sección muestra los indicadores del programa de inducción. Los datos vienen de tbl_indicadores_sst donde categoria = induccion.'

    UNION SELECT 13, 'Cronograma de Actividades', 'cronograma', 'tabla_dinamica', 'pta_induccion', 'pta_cliente', 1, 13,
           'Esta sección muestra el cronograma de actividades del PTA relacionadas con inducción. Los datos vienen de tbl_pta_cliente donde tipo_servicio = Programa Induccion.'

) s
WHERE tc.tipo_documento = 'programa_induccion_reinduccion'
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    prompt_ia = VALUES(prompt_ia);
SQL;

// SQL para firmantes
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
    SELECT 'responsable_sst' as firmante_tipo,
           'Elaboró' as rol_display,
           'Elaboró / Responsable del SG-SST' as columna_encabezado,
           1 as orden,
           1 as es_obligatorio,
           1 as mostrar_licencia,
           0 as mostrar_cedula
    UNION SELECT 'representante_legal', 'Aprobó', 'Aprobó / Representante Legal', 2, 1, 0, 1
) f
WHERE tc.tipo_documento = 'programa_induccion_reinduccion'
ON DUPLICATE KEY UPDATE
    rol_display = VALUES(rol_display),
    columna_encabezado = VALUES(columna_encabezado);
SQL;

foreach ($conexiones as $entorno => $config) {
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "EJECUTANDO EN: " . strtoupper($entorno) . "\n";
    echo str_repeat("=", 50) . "\n";

    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        if ($config['ssl']) {
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        echo "✅ Conexión establecida\n\n";

        // 1. Verificar ENUM actual
        $stmt = $pdo->query("SHOW COLUMNS FROM tbl_doc_tipo_configuracion WHERE Field = 'flujo'");
        $col = $stmt->fetch();
        echo "📋 ENUM actual: {$col['Type']}\n";

        // 2. Modificar ENUM si es necesario
        if (strpos($col['Type'], 'programa_con_pta') === false) {
            echo "🔧 Modificando ENUM para agregar 'programa_con_pta'...\n";
            try {
                $pdo->exec($sqlAlterEnum);
                echo "  ✅ ENUM modificado\n";
            } catch (PDOException $e) {
                echo "  ❌ Error: " . $e->getMessage() . "\n";
            }
        } else {
            echo "  ✅ ENUM ya tiene 'programa_con_pta'\n";
        }

        // 3. Insertar tipo de documento
        echo "\n🔧 Insertando tipo de documento...\n";
        try {
            $pdo->exec($sqlTipoDocumento);
            echo "  ✅ Tipo de documento insertado/actualizado\n";
        } catch (PDOException $e) {
            echo "  ❌ Error: " . $e->getMessage() . "\n";
        }

        // 4. Insertar secciones
        echo "\n🔧 Insertando secciones...\n";
        try {
            $pdo->exec($sqlSecciones);
            echo "  ✅ Secciones insertadas\n";
        } catch (PDOException $e) {
            echo "  ❌ Error: " . $e->getMessage() . "\n";
        }

        // 5. Insertar firmantes
        echo "\n🔧 Insertando firmantes...\n";
        try {
            $pdo->exec($sqlFirmantes);
            echo "  ✅ Firmantes insertados\n";
        } catch (PDOException $e) {
            echo "  ❌ Error: " . $e->getMessage() . "\n";
        }

        // 6. Verificar
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_doc_secciones_config WHERE id_tipo_config = (SELECT id_tipo_config FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'programa_induccion_reinduccion')");
        $row = $stmt->fetch();
        echo "\n📊 Verificación: {$row['total']} secciones configuradas\n";

        $stmt = $pdo->query("SELECT id_tipo_config, tipo_documento, flujo FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'programa_induccion_reinduccion'");
        $tipo = $stmt->fetch();
        if ($tipo) {
            echo "📊 Tipo: ID={$tipo['id_tipo_config']}, flujo={$tipo['flujo']}\n";
        }

    } catch (PDOException $e) {
        echo "❌ Error de conexión: " . $e->getMessage() . "\n";
    }
}

echo "\n🎉 Script de corrección finalizado\n";
