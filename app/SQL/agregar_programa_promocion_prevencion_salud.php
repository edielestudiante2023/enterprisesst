<?php
/**
 * Script para agregar módulo 3.1.2 - Programa de Promoción y Prevención en Salud
 *
 * Ejecuta cambios en LOCAL y PRODUCCIÓN simultáneamente
 *
 * Ejecutar: php app/SQL/agregar_programa_promocion_prevencion_salud.php
 *
 * Crea:
 * - Configuración en tbl_doc_tipo_configuracion
 * - Secciones en tbl_doc_secciones_config (12 secciones estándar de programa)
 * - Firmantes en tbl_doc_firmantes_config
 * - Plantilla en tbl_doc_plantillas
 * - Mapeo en tbl_doc_plantilla_carpeta
 */

echo "=== MÓDULO 3.1.2 - PROGRAMA DE PROMOCIÓN Y PREVENCIÓN EN SALUD ===\n";
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
('programa_promocion_prevencion_salud',
 'Programa de Promoción y Prevención en Salud',
 'Programa que establece las actividades de promoción de la salud y prevención de enfermedades laborales, incluyendo estilos de vida saludables, pausas activas, exámenes médicos ocupacionales y control de riesgos derivados de las condiciones de trabajo.',
 '3.1.2',
 'secciones_ia',
 'programas',
 'bi-heart-pulse',
 15,
 1)
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    descripcion = VALUES(descripcion),
    categoria = VALUES(categoria),
    updated_at = NOW();
SQL;

// ============================================================
// SQL 2: Insertar secciones del documento (12 secciones estándar)
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
    SELECT 1 as numero,
           'Introducción' as nombre,
           'introduccion' as seccion_key,
           'texto' as tipo_contenido,
           NULL as tabla_dinamica_tipo,
           NULL as sincronizar_bd,
           1 as es_obligatoria,
           1 as orden,
           'Genera una introducción para el Programa de Promoción y Prevención en Salud de la empresa {EMPRESA}.
Contexto: La empresa pertenece al sector {SECTOR_ECONOMICO} con clase de riesgo {CLASE_RIESGO}.
Incluye:
- Importancia de la promoción de la salud en el ámbito laboral
- Marco conceptual de la prevención primaria, secundaria y terciaria
- Beneficios de implementar un programa de PyP en salud
- Compromiso de la organización con el bienestar de los trabajadores
Máximo 3 párrafos.' as prompt_ia

    UNION SELECT 2,
           'Objetivo General',
           'objetivo_general',
           'texto',
           NULL,
           NULL,
           1,
           2,
           'Genera el objetivo general del Programa de Promoción y Prevención en Salud para {EMPRESA}.
El objetivo debe:
- Ser medible y alcanzable
- Orientarse a promover la salud y prevenir enfermedades laborales
- Incluir el bienestar físico, mental y social de los trabajadores
- Alinearse con la política de SST de la empresa
Un solo párrafo conciso.'

    UNION SELECT 3,
           'Objetivos Específicos',
           'objetivos_especificos',
           'texto',
           NULL,
           NULL,
           1,
           3,
           'Genera los objetivos específicos del Programa de Promoción y Prevención en Salud para {EMPRESA}.
Incluir mínimo 5 objetivos que aborden:
- Identificación de condiciones de salud de los trabajadores
- Implementación de actividades de promoción de estilos de vida saludables
- Prevención de enfermedades derivadas de las condiciones de trabajo
- Seguimiento a casos de salud identificados
- Cumplimiento de exámenes médicos ocupacionales
- Capacitación en autocuidado y hábitos saludables
Presentar en formato de lista numerada.'

    UNION SELECT 4,
           'Alcance',
           'alcance',
           'texto',
           NULL,
           NULL,
           1,
           4,
           'Define el alcance del Programa de Promoción y Prevención en Salud para {EMPRESA}.
Especificar:
- A quiénes aplica (todos los trabajadores, contratistas, visitantes)
- Áreas o procesos cubiertos
- Tipos de actividades incluidas (promoción, prevención, vigilancia)
- Frecuencia de aplicación
Un párrafo claro y preciso.'

    UNION SELECT 5,
           'Marco Legal',
           'marco_legal',
           'texto',
           NULL,
           NULL,
           1,
           5,
           'Genera el marco legal aplicable al Programa de Promoción y Prevención en Salud en Colombia.
Incluir las principales normas:
- Ley 1562 de 2012 (Sistema de Riesgos Laborales)
- Decreto 1072 de 2015 (Decreto Único Reglamentario del Sector Trabajo)
- Resolución 0312 de 2019 (Estándares Mínimos del SG-SST)
- Resolución 2346 de 2007 (Evaluaciones Médicas Ocupacionales)
- Resolución 2646 de 2008 (Riesgo Psicosocial)
- Otras normas relacionadas con exámenes médicos, PVE, promoción de la salud
Presentar en formato de lista con breve descripción de cada norma.'

    UNION SELECT 6,
           'Definiciones',
           'definiciones',
           'texto',
           NULL,
           NULL,
           1,
           6,
           'Define los términos clave del Programa de Promoción y Prevención en Salud.
Incluir definiciones de:
- Promoción de la salud
- Prevención de la enfermedad
- Enfermedad laboral
- Condiciones de salud
- Programa de Vigilancia Epidemiológica (PVE)
- Exámenes médicos ocupacionales (ingreso, periódicos, egreso)
- Perfil sociodemográfico
- Diagnóstico de condiciones de salud
- Estilo de vida saludable
- Autocuidado
Presentar en formato de lista con definiciones concisas.'

    UNION SELECT 7,
           'Responsabilidades',
           'responsabilidades',
           'texto',
           NULL,
           NULL,
           1,
           7,
           'Define las responsabilidades de cada rol en el Programa de Promoción y Prevención en Salud para {EMPRESA}.
Incluir responsabilidades de:
**Alta Dirección:**
- Asignar recursos para el programa
- Aprobar el programa y sus actividades

**Responsable del SG-SST:**
- Coordinar la ejecución del programa
- Gestionar exámenes médicos ocupacionales
- Implementar actividades de promoción y prevención
- Hacer seguimiento a indicadores

**COPASST o Vigía SST:**
- Participar en actividades de promoción
- Verificar cumplimiento del programa

**Trabajadores:**
- Participar activamente en las actividades
- Reportar condiciones de salud
- Asistir a exámenes médicos programados
- Practicar el autocuidado

Presentar en formato estructurado por rol.'

    UNION SELECT 8,
           'Metodología',
           'metodologia',
           'texto',
           NULL,
           NULL,
           1,
           8,
           'Describe la metodología del Programa de Promoción y Prevención en Salud para {EMPRESA}.
Incluir las fases:
**Fase 1 - Diagnóstico:**
- Análisis del perfil sociodemográfico
- Revisión del diagnóstico de condiciones de salud
- Identificación de grupos de riesgo

**Fase 2 - Planeación:**
- Definición de actividades según necesidades identificadas
- Establecimiento de metas e indicadores
- Asignación de recursos y responsables

**Fase 3 - Ejecución:**
- Realización de exámenes médicos ocupacionales
- Capacitaciones en promoción de la salud
- Actividades de estilos de vida saludables
- Pausas activas
- Campañas de salud

**Fase 4 - Seguimiento y Evaluación:**
- Monitoreo de indicadores
- Seguimiento a casos identificados
- Evaluación de efectividad del programa
- Mejora continua

Desarrollar cada fase con detalle práctico.'

    UNION SELECT 9,
           'Cronograma de Actividades',
           'cronograma',
           'texto',
           NULL,
           'pta_cliente',
           1,
           9,
           'Genera el cronograma de actividades del Programa de Promoción y Prevención en Salud para {EMPRESA} para el año {ANIO}.
Incluir actividades como:
- Exámenes médicos de ingreso (continuo)
- Exámenes médicos periódicos (según programación)
- Capacitación en estilos de vida saludables (trimestral)
- Pausas activas (diario/semanal)
- Campaña de salud cardiovascular (semestre 1)
- Campaña de salud visual (semestre 2)
- Semana de la salud (anual)
- Vacunación (según disponibilidad)
- Seguimiento a casos de salud (mensual)
- Actualización perfil sociodemográfico (anual)
- Evaluación del programa (semestral)

Presentar en formato tabla markdown:
| Actividad | Responsable | Frecuencia | Ene | Feb | Mar | Abr | May | Jun | Jul | Ago | Sep | Oct | Nov | Dic |
Marcar con X los meses programados.'

    UNION SELECT 10,
           'Indicadores',
           'indicadores',
           'texto',
           NULL,
           'indicadores_sst',
           1,
           10,
           'Define los indicadores del Programa de Promoción y Prevención en Salud para {EMPRESA}.
Incluir mínimo 5 indicadores:

**Indicador 1: Cobertura de Exámenes Médicos Periódicos**
- Fórmula: (Exámenes realizados / Exámenes programados) x 100
- Meta: >= 90%
- Periodicidad: Semestral

**Indicador 2: Cumplimiento de Actividades de PyP**
- Fórmula: (Actividades ejecutadas / Actividades programadas) x 100
- Meta: >= 85%
- Periodicidad: Trimestral

**Indicador 3: Participación en Capacitaciones de Salud**
- Fórmula: (Trabajadores capacitados / Total trabajadores) x 100
- Meta: >= 80%
- Periodicidad: Semestral

**Indicador 4: Tasa de Enfermedad Laboral**
- Fórmula: (Casos de enfermedad laboral / Total trabajadores) x 100
- Meta: < 1%
- Periodicidad: Anual

**Indicador 5: Seguimiento a Casos de Salud**
- Fórmula: (Casos con seguimiento / Casos identificados) x 100
- Meta: 100%
- Periodicidad: Trimestral

Presentar cada indicador con nombre, fórmula, meta y periodicidad.'

    UNION SELECT 11,
           'Recursos',
           'recursos',
           'texto',
           NULL,
           NULL,
           1,
           11,
           'Define los recursos necesarios para el Programa de Promoción y Prevención en Salud de {EMPRESA}.
Incluir:
**Recursos Humanos:**
- Responsable del SG-SST
- Médico ocupacional o IPS contratada
- Personal de apoyo para actividades

**Recursos Físicos:**
- Espacio para capacitaciones
- Consultorio médico (si aplica)
- Equipos para actividades (colchonetas, elementos deportivos)

**Recursos Tecnológicos:**
- Software de gestión de exámenes médicos
- Plataforma para capacitaciones virtuales
- Sistema de seguimiento de indicadores

**Recursos Financieros:**
- Presupuesto para exámenes médicos
- Presupuesto para capacitaciones
- Presupuesto para campañas de salud
- Presupuesto para elementos de promoción

Presentar en formato estructurado.'

    UNION SELECT 12,
           'Evaluación y Seguimiento',
           'evaluacion_seguimiento',
           'texto',
           NULL,
           NULL,
           1,
           12,
           'Describe el proceso de evaluación y seguimiento del Programa de Promoción y Prevención en Salud para {EMPRESA}.
Incluir:
**Seguimiento Periódico:**
- Revisión mensual de avance de actividades
- Seguimiento trimestral de indicadores
- Reunión semestral de evaluación del programa

**Evaluación Anual:**
- Análisis de cumplimiento de objetivos
- Revisión de efectividad de actividades
- Comparativo de indicadores vs metas
- Identificación de oportunidades de mejora

**Acciones de Mejora:**
- Documentación de lecciones aprendidas
- Ajuste de actividades según resultados
- Actualización de metas e indicadores
- Incorporación de nuevas necesidades identificadas

**Documentación:**
- Registro de actividades ejecutadas
- Informes de indicadores
- Actas de reuniones de seguimiento
- Plan de mejora continua

Presentar de forma clara y práctica.'

) s
WHERE tc.tipo_documento = 'programa_promocion_prevencion_salud'
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    prompt_ia = VALUES(prompt_ia),
    sincronizar_bd = VALUES(sincronizar_bd);
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
    SELECT 'responsable_sst' as firmante_tipo,
           'Elaboró' as rol_display,
           'Elaboró / Responsable del SG-SST' as columna_encabezado,
           1 as orden,
           1 as es_obligatorio,
           1 as mostrar_licencia,
           0 as mostrar_cedula
    UNION SELECT 'representante_legal', 'Aprobó', 'Aprobó / Representante Legal', 2, 1, 0, 1
) f
WHERE tc.tipo_documento = 'programa_promocion_prevencion_salud'
ON DUPLICATE KEY UPDATE
    rol_display = VALUES(rol_display),
    columna_encabezado = VALUES(columna_encabezado);
SQL;

// ============================================================
// SQL 4: Insertar plantilla
// ============================================================
$sqlPlantilla = <<<'SQL'
INSERT INTO tbl_doc_plantillas
(id_tipo, nombre, codigo_sugerido, tipo_documento, version, activo)
SELECT
    3 as id_tipo,
    'Programa de Promoción y Prevención en Salud' as nombre,
    'PRG-PPS' as codigo_sugerido,
    'programa_promocion_prevencion_salud' as tipo_documento,
    '001' as version,
    1 as activo
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM tbl_doc_plantillas WHERE codigo_sugerido = 'PRG-PPS'
)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);
SQL;

// ============================================================
// SQL 5: Mapear plantilla a carpeta 3.1.2
// ============================================================
$sqlMapeoCarpeta = <<<'SQL'
INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
VALUES ('PRG-PPS', '3.1.2')
ON DUPLICATE KEY UPDATE codigo_carpeta = VALUES(codigo_carpeta);
SQL;

// ============================================================
// Función para ejecutar SQL en una conexión
// ============================================================
function ejecutarSQL($pdo, $sql, $descripcion, $entorno) {
    try {
        $pdo->exec($sql);
        echo "  ✅ [$entorno] $descripcion\n";
        return true;
    } catch (PDOException $e) {
        echo "  ❌ [$entorno] $descripcion\n";
        echo "     Error: " . $e->getMessage() . "\n";
        return false;
    }
}

// ============================================================
// Ejecutar en ambos entornos
// ============================================================
$resultados = ['local' => [], 'produccion' => []];

foreach ($conexiones as $entorno => $config) {
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "EJECUTANDO EN: " . strtoupper($entorno) . "\n";
    echo str_repeat("=", 50) . "\n";

    try {
        // Construir DSN
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ];

        // Agregar SSL para producción
        if ($config['ssl']) {
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
            $options[PDO::MYSQL_ATTR_SSL_CA] = false;
        }

        $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        echo "✅ Conexión establecida\n\n";

        // Ejecutar cada SQL
        $resultados[$entorno]['tipo'] = ejecutarSQL($pdo, $sqlTipoDocumento, "Insertar tipo de documento", $entorno);
        $resultados[$entorno]['secciones'] = ejecutarSQL($pdo, $sqlSecciones, "Insertar secciones (12)", $entorno);
        $resultados[$entorno]['firmantes'] = ejecutarSQL($pdo, $sqlFirmantes, "Insertar firmantes (2)", $entorno);
        $resultados[$entorno]['plantilla'] = ejecutarSQL($pdo, $sqlPlantilla, "Insertar plantilla PRG-PPS", $entorno);
        $resultados[$entorno]['mapeo'] = ejecutarSQL($pdo, $sqlMapeoCarpeta, "Mapear a carpeta 3.1.2", $entorno);

        // Verificar que se creó correctamente
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_doc_secciones_config WHERE id_tipo_config = (SELECT id_tipo_config FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'programa_promocion_prevencion_salud')");
        $row = $stmt->fetch();
        echo "\n📊 Verificación: {$row['total']} secciones configuradas\n";

        // Mostrar detalles del tipo
        $stmt = $pdo->query("SELECT tipo_documento, nombre, estandar, categoria FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'programa_promocion_prevencion_salud'");
        $tipo = $stmt->fetch();
        if ($tipo) {
            echo "   Tipo: {$tipo['tipo_documento']}\n";
            echo "   Nombre: {$tipo['nombre']}\n";
            echo "   Estándar: {$tipo['estandar']}\n";
            echo "   Categoría: {$tipo['categoria']}\n";
        }

    } catch (PDOException $e) {
        echo "❌ Error de conexión: " . $e->getMessage() . "\n";
        $resultados[$entorno]['conexion'] = false;
    }
}

// ============================================================
// Resumen final
// ============================================================
echo "\n" . str_repeat("=", 50) . "\n";
echo "RESUMEN DE EJECUCIÓN\n";
echo str_repeat("=", 50) . "\n";

foreach ($resultados as $entorno => $resultado) {
    $exitosos = count(array_filter($resultado));
    $total = count($resultado);
    $estado = $exitosos === $total ? "✅ COMPLETO" : "⚠️ PARCIAL";
    echo "$estado $entorno: $exitosos/$total operaciones exitosas\n";
}

echo "\n🎉 Script finalizado\n";
echo "Siguiente paso: Crear vista app/Views/documentacion/_tipos/promocion_prevencion_salud.php\n";
echo "               y agregar detección en DocumentacionController::determinarTipoCarpetaFases()\n";
