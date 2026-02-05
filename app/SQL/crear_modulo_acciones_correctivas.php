<?php
/**
 * Script para crear el MÓDULO DE ACCIONES CORRECTIVAS
 * Numerales 7.1.1, 7.1.2, 7.1.3, 7.1.4 de la Resolución 0312 de 2019
 *
 * Ejecuta cambios en LOCAL y PRODUCCIÓN simultáneamente
 *
 * Ejecutar: php app/SQL/crear_modulo_acciones_correctivas.php
 *
 * Crea las siguientes tablas:
 * - tbl_acc_hallazgos (Orígenes/problemas detectados)
 * - tbl_acc_acciones (Acciones CAPA: Correctivas, Preventivas, Mejora)
 * - tbl_acc_seguimientos (Evidencias y avances)
 * - tbl_acc_verificaciones (Evaluación de efectividad)
 */

echo "=== MÓDULO DE ACCIONES CORRECTIVAS - NUMERALES 7.1.x ===\n";
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
// SQL 1: Crear tabla de HALLAZGOS (Orígenes/Problemas)
// ============================================================
$sqlHallazgos = <<<'SQL'
CREATE TABLE IF NOT EXISTS tbl_acc_hallazgos (
    id_hallazgo INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,

    -- Clasificación del hallazgo (mapeo a numerales 7.1.x)
    tipo_origen ENUM(
        'auditoria_interna',      -- 7.1.1 Auditoría interna del SG-SST
        'revision_direccion',     -- 7.1.1 Revisión por la alta dirección
        'inspeccion',             -- 7.1.1 Inspecciones de seguridad
        'indicador',              -- 7.1.1 Resultado de indicadores
        'evaluacion_estandares',  -- 7.1.1 Evaluación de estándares mínimos
        'medida_no_efectiva',     -- 7.1.2 Medida de prevención/control no efectiva
        'investigacion_incidente',-- 7.1.3 Investigación de incidente
        'investigacion_accidente',-- 7.1.3 Investigación de accidente de trabajo
        'investigacion_enfermedad',-- 7.1.3 Investigación de enfermedad laboral
        'requerimiento_arl',      -- 7.1.4 Recomendación/requerimiento de ARL
        'requerimiento_autoridad',-- 7.1.4 Requerimiento de autoridad (MinTrabajo)
        'copasst',                -- Observación del COPASST
        'trabajador',             -- Reporte de trabajador
        'otro'                    -- Otro origen
    ) NOT NULL,

    numeral_asociado ENUM('7.1.1', '7.1.2', '7.1.3', '7.1.4') NOT NULL,

    -- Información del hallazgo
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT NOT NULL,
    area_proceso VARCHAR(150),

    -- Severidad y prioridad
    severidad ENUM('critica', 'alta', 'media', 'baja') NOT NULL DEFAULT 'media',

    -- Fechas
    fecha_deteccion DATE NOT NULL,
    fecha_limite_accion DATE,

    -- Responsable que reporta
    reportado_por INT NOT NULL COMMENT 'id_usuario que reporta el hallazgo',

    -- Evidencia inicial (documento adjunto)
    evidencia_inicial VARCHAR(255) COMMENT 'Ruta del archivo adjunto inicial',

    -- Estado del hallazgo
    estado ENUM(
        'abierto',           -- Recién creado, sin acciones
        'en_tratamiento',    -- Tiene acciones asignadas en ejecución
        'en_verificacion',   -- Acciones ejecutadas, pendiente verificar efectividad
        'cerrado',           -- Todas las acciones cerradas efectivamente
        'cerrado_no_efectivo'-- Cerrado pero requiere nueva acción
    ) NOT NULL DEFAULT 'abierto',

    -- Metadatos
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,

    -- Índices
    INDEX idx_cliente (id_cliente),
    INDEX idx_numeral (numeral_asociado),
    INDEX idx_estado (estado),
    INDEX idx_fecha_deteccion (fecha_deteccion),
    INDEX idx_tipo_origen (tipo_origen),

    -- FK
    CONSTRAINT fk_hallazgo_cliente FOREIGN KEY (id_cliente)
        REFERENCES tbl_clientes(id_cliente) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Hallazgos/Orígenes que generan acciones correctivas - Numerales 7.1.x';
SQL;

// ============================================================
// SQL 2: Crear tabla de ACCIONES (CAPA)
// ============================================================
$sqlAcciones = <<<'SQL'
CREATE TABLE IF NOT EXISTS tbl_acc_acciones (
    id_accion INT AUTO_INCREMENT PRIMARY KEY,
    id_hallazgo INT NOT NULL,

    -- Tipo de acción
    tipo_accion ENUM('correctiva', 'preventiva', 'mejora') NOT NULL,

    -- Clasificación temporal
    clasificacion_temporal ENUM('inmediata', 'corto_plazo', 'mediano_plazo', 'largo_plazo') NOT NULL DEFAULT 'corto_plazo',

    -- Descripción de la acción
    descripcion_accion TEXT NOT NULL,

    -- Análisis de causa raíz (estructura JSON para almacenar el diálogo con IA)
    analisis_causa_raiz JSON COMMENT 'Almacena el diálogo socrático de análisis de causa raíz',
    causa_raiz_identificada TEXT COMMENT 'Resumen de la causa raíz identificada',

    -- Responsable de ejecutar
    responsable_id INT NOT NULL COMMENT 'id_usuario responsable de ejecutar la acción',
    responsable_nombre VARCHAR(150) COMMENT 'Nombre para mostrar (cache)',

    -- Fechas
    fecha_asignacion DATE NOT NULL,
    fecha_compromiso DATE NOT NULL,
    fecha_cierre_real DATE,

    -- Recursos
    recursos_requeridos TEXT,
    costo_estimado DECIMAL(15,2),

    -- Estado de la acción
    estado ENUM(
        'borrador',         -- Creada pero no asignada
        'asignada',         -- Asignada a responsable
        'en_ejecucion',     -- Responsable está trabajando
        'en_revision',      -- Esperando revisión de evidencias
        'en_verificacion',  -- Pendiente verificar efectividad
        'cerrada_efectiva', -- Cerrada con verificación efectiva
        'cerrada_no_efectiva', -- Cerrada pero no fue efectiva
        'reabierta',        -- Fue cerrada pero se reabrió
        'cancelada'         -- Cancelada (con justificación)
    ) NOT NULL DEFAULT 'borrador',

    -- Notas adicionales
    notas TEXT,
    motivo_cancelacion TEXT,

    -- Metadatos
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,

    -- Índices
    INDEX idx_hallazgo (id_hallazgo),
    INDEX idx_responsable (responsable_id),
    INDEX idx_estado (estado),
    INDEX idx_fecha_compromiso (fecha_compromiso),
    INDEX idx_tipo_accion (tipo_accion),

    -- FK
    CONSTRAINT fk_accion_hallazgo FOREIGN KEY (id_hallazgo)
        REFERENCES tbl_acc_hallazgos(id_hallazgo) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Acciones Correctivas, Preventivas y de Mejora (CAPA)';
SQL;

// ============================================================
// SQL 3: Crear tabla de SEGUIMIENTOS (Evidencias y Avances)
// ============================================================
$sqlSeguimientos = <<<'SQL'
CREATE TABLE IF NOT EXISTS tbl_acc_seguimientos (
    id_seguimiento INT AUTO_INCREMENT PRIMARY KEY,
    id_accion INT NOT NULL,

    -- Tipo de seguimiento
    tipo_seguimiento ENUM(
        'avance',           -- Reporte de avance
        'evidencia',        -- Carga de evidencia
        'comentario',       -- Comentario general
        'cambio_estado',    -- Cambio de estado automático
        'recordatorio',     -- Recordatorio enviado
        'solicitud_prorroga'-- Solicitud de extensión de fecha
    ) NOT NULL,

    -- Contenido
    descripcion TEXT NOT NULL,
    porcentaje_avance TINYINT UNSIGNED COMMENT 'Porcentaje de avance (0-100)',

    -- Archivo adjunto
    archivo_adjunto VARCHAR(255),
    nombre_archivo VARCHAR(150),
    tipo_archivo VARCHAR(50),

    -- Quién registra
    registrado_por INT NOT NULL COMMENT 'id_usuario que registra',
    registrado_por_nombre VARCHAR(150) COMMENT 'Nombre para mostrar (cache)',

    -- Metadatos
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Índices
    INDEX idx_accion (id_accion),
    INDEX idx_tipo (tipo_seguimiento),
    INDEX idx_fecha (created_at),

    -- FK
    CONSTRAINT fk_seguimiento_accion FOREIGN KEY (id_accion)
        REFERENCES tbl_acc_acciones(id_accion) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Seguimientos, evidencias y bitácora de acciones';
SQL;

// ============================================================
// SQL 4: Crear tabla de VERIFICACIONES (Efectividad)
// ============================================================
$sqlVerificaciones = <<<'SQL'
CREATE TABLE IF NOT EXISTS tbl_acc_verificaciones (
    id_verificacion INT AUTO_INCREMENT PRIMARY KEY,
    id_accion INT NOT NULL,

    -- Método de verificación
    metodo_verificacion ENUM(
        'inspeccion',       -- Inspección de verificación en sitio
        'documental',       -- Revisión de evidencia documental
        'indicador',        -- Verificación por mejora de indicador
        'observacion',      -- Observación directa del comportamiento
        'entrevista',       -- Entrevista a trabajadores
        'auditoria'         -- Verificación en auditoría
    ) NOT NULL,

    -- Resultado de la verificación
    resultado ENUM('efectiva', 'parcialmente_efectiva', 'no_efectiva') NOT NULL,

    -- Justificación detallada
    observaciones TEXT NOT NULL,
    evidencia_verificacion VARCHAR(255) COMMENT 'Archivo de evidencia de la verificación',

    -- Si no es efectiva, puede requerir nueva acción
    requiere_nueva_accion TINYINT(1) DEFAULT 0,
    id_nueva_accion INT COMMENT 'FK a la nueva acción generada si aplica',

    -- Quién verifica
    verificado_por INT NOT NULL COMMENT 'id_usuario que realiza la verificación',
    verificado_por_nombre VARCHAR(150) COMMENT 'Nombre para mostrar (cache)',

    -- Fechas
    fecha_verificacion DATE NOT NULL,
    fecha_proxima_verificacion DATE COMMENT 'Si es parcial, cuándo volver a verificar',

    -- Metadatos
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Índices
    INDEX idx_accion (id_accion),
    INDEX idx_resultado (resultado),
    INDEX idx_fecha (fecha_verificacion),

    -- FK
    CONSTRAINT fk_verificacion_accion FOREIGN KEY (id_accion)
        REFERENCES tbl_acc_acciones(id_accion) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Verificaciones de efectividad de acciones - Cumplimiento numeral 7.1.2';
SQL;

// ============================================================
// SQL 5: Crear tabla de CATÁLOGO DE ORÍGENES (para Select dinámico)
// ============================================================
$sqlCatalogoOrigenes = <<<'SQL'
CREATE TABLE IF NOT EXISTS tbl_acc_catalogo_origenes (
    id_catalogo INT AUTO_INCREMENT PRIMARY KEY,
    tipo_origen VARCHAR(50) NOT NULL UNIQUE,
    nombre_mostrar VARCHAR(150) NOT NULL,
    descripcion TEXT,
    numeral_default ENUM('7.1.1', '7.1.2', '7.1.3', '7.1.4') NOT NULL,
    icono VARCHAR(50) DEFAULT 'bi-exclamation-triangle',
    color VARCHAR(20) DEFAULT '#6c757d',
    orden INT DEFAULT 0,
    activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Catálogo de tipos de origen para hallazgos';
SQL;

// ============================================================
// SQL 6: Insertar datos en catálogo de orígenes
// ============================================================
$sqlInsertCatalogo = <<<'SQL'
INSERT INTO tbl_acc_catalogo_origenes
(tipo_origen, nombre_mostrar, descripcion, numeral_default, icono, color, orden) VALUES
('auditoria_interna', 'Auditoría Interna del SG-SST', 'Hallazgo identificado durante auditoría interna del Sistema de Gestión', '7.1.1', 'bi-clipboard-check', '#0d6efd', 1),
('revision_direccion', 'Revisión por la Alta Dirección', 'Hallazgo de la revisión anual por la alta dirección', '7.1.1', 'bi-building', '#6f42c1', 2),
('inspeccion', 'Inspección de Seguridad', 'Hallazgo durante inspecciones programadas o no programadas', '7.1.1', 'bi-search', '#fd7e14', 3),
('indicador', 'Resultado de Indicadores', 'Desviación o incumplimiento de indicadores del SG-SST', '7.1.1', 'bi-graph-up', '#20c997', 4),
('evaluacion_estandares', 'Evaluación de Estándares Mínimos', 'Incumplimiento identificado en evaluación de Resolución 0312', '7.1.1', 'bi-list-check', '#0dcaf0', 5),
('medida_no_efectiva', 'Medida de Control No Efectiva', 'Una medida de prevención o control no está funcionando', '7.1.2', 'bi-shield-x', '#dc3545', 6),
('investigacion_incidente', 'Investigación de Incidente', 'Hallazgo de investigación de incidente de trabajo', '7.1.3', 'bi-exclamation-diamond', '#ffc107', 7),
('investigacion_accidente', 'Investigación de Accidente de Trabajo', 'Hallazgo de investigación de accidente de trabajo', '7.1.3', 'bi-bandaid', '#dc3545', 8),
('investigacion_enfermedad', 'Investigación de Enfermedad Laboral', 'Hallazgo de investigación de enfermedad laboral', '7.1.3', 'bi-heart-pulse', '#e83e8c', 9),
('requerimiento_arl', 'Recomendación/Requerimiento ARL', 'Recomendación o requerimiento emitido por la ARL', '7.1.4', 'bi-shield-check', '#198754', 10),
('requerimiento_autoridad', 'Requerimiento de Autoridad', 'Requerimiento del Ministerio de Trabajo u otra autoridad', '7.1.4', 'bi-bank', '#212529', 11),
('copasst', 'Observación COPASST/Vigía', 'Observación o recomendación del COPASST o Vigía SST', '7.1.1', 'bi-people', '#6c757d', 12),
('trabajador', 'Reporte de Trabajador', 'Condición reportada por un trabajador', '7.1.1', 'bi-person-badge', '#17a2b8', 13),
('otro', 'Otro Origen', 'Otro tipo de origen no listado', '7.1.1', 'bi-question-circle', '#6c757d', 99)
ON DUPLICATE KEY UPDATE
    nombre_mostrar = VALUES(nombre_mostrar),
    descripcion = VALUES(descripcion),
    numeral_default = VALUES(numeral_default);
SQL;

// ============================================================
// SQL 7: Crear índice para búsquedas frecuentes
// ============================================================
$sqlIndicesAdicionales = <<<'SQL'
-- Índice compuesto para dashboard por cliente y estado
CREATE INDEX IF NOT EXISTS idx_hallazgo_cliente_estado
ON tbl_acc_hallazgos (id_cliente, estado, numeral_asociado);

-- Índice para acciones vencidas
CREATE INDEX IF NOT EXISTS idx_accion_vencimiento
ON tbl_acc_acciones (fecha_compromiso, estado);
SQL;

// ============================================================
// Función para ejecutar SQL
// ============================================================
function ejecutarSQL($pdo, $sql, $descripcion, $entorno) {
    try {
        $pdo->exec($sql);
        echo "  ✅ [$entorno] $descripcion\n";
        return true;
    } catch (PDOException $e) {
        // Ignorar error de índice ya existente
        if (strpos($e->getMessage(), 'Duplicate key name') !== false ||
            strpos($e->getMessage(), 'already exists') !== false) {
            echo "  ⚠️ [$entorno] $descripcion (ya existe)\n";
            return true;
        }
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
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "EJECUTANDO EN: " . strtoupper($entorno) . "\n";
    echo str_repeat("=", 60) . "\n";

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
        echo "--- Creando tablas ---\n";
        $resultados[$entorno]['hallazgos'] = ejecutarSQL($pdo, $sqlHallazgos, "Crear tabla tbl_acc_hallazgos", $entorno);
        $resultados[$entorno]['acciones'] = ejecutarSQL($pdo, $sqlAcciones, "Crear tabla tbl_acc_acciones", $entorno);
        $resultados[$entorno]['seguimientos'] = ejecutarSQL($pdo, $sqlSeguimientos, "Crear tabla tbl_acc_seguimientos", $entorno);
        $resultados[$entorno]['verificaciones'] = ejecutarSQL($pdo, $sqlVerificaciones, "Crear tabla tbl_acc_verificaciones", $entorno);
        $resultados[$entorno]['catalogo'] = ejecutarSQL($pdo, $sqlCatalogoOrigenes, "Crear tabla tbl_acc_catalogo_origenes", $entorno);

        echo "\n--- Insertando datos de catálogo ---\n";
        $resultados[$entorno]['datos_catalogo'] = ejecutarSQL($pdo, $sqlInsertCatalogo, "Insertar catálogo de orígenes (14 tipos)", $entorno);

        echo "\n--- Creando índices adicionales ---\n";
        // Ejecutar índices por separado para mejor manejo de errores
        try {
            $pdo->exec("CREATE INDEX idx_hallazgo_cliente_estado ON tbl_acc_hallazgos (id_cliente, estado, numeral_asociado)");
            echo "  ✅ [$entorno] Índice idx_hallazgo_cliente_estado\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                echo "  ⚠️ [$entorno] Índice idx_hallazgo_cliente_estado (ya existe)\n";
            }
        }

        try {
            $pdo->exec("CREATE INDEX idx_accion_vencimiento ON tbl_acc_acciones (fecha_compromiso, estado)");
            echo "  ✅ [$entorno] Índice idx_accion_vencimiento\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                echo "  ⚠️ [$entorno] Índice idx_accion_vencimiento (ya existe)\n";
            }
        }

        // Verificación final
        echo "\n--- Verificación ---\n";

        // Contar tablas creadas
        $tablas = ['tbl_acc_hallazgos', 'tbl_acc_acciones', 'tbl_acc_seguimientos', 'tbl_acc_verificaciones', 'tbl_acc_catalogo_origenes'];
        $tablasCreadas = 0;
        foreach ($tablas as $tabla) {
            $stmt = $pdo->query("SHOW TABLES LIKE '$tabla'");
            if ($stmt->rowCount() > 0) {
                $tablasCreadas++;
            }
        }
        echo "📊 Tablas creadas: $tablasCreadas/" . count($tablas) . "\n";

        // Verificar catálogo
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_acc_catalogo_origenes");
        $row = $stmt->fetch();
        echo "📊 Tipos de origen en catálogo: {$row['total']}\n";

    } catch (PDOException $e) {
        echo "❌ Error de conexión: " . $e->getMessage() . "\n";
        $resultados[$entorno]['conexion'] = false;
    }
}

// ============================================================
// Resumen final
// ============================================================
echo "\n" . str_repeat("=", 60) . "\n";
echo "RESUMEN DE EJECUCIÓN\n";
echo str_repeat("=", 60) . "\n";

foreach ($resultados as $entorno => $resultado) {
    $exitosos = count(array_filter($resultado));
    $total = count($resultado);
    $estado = ($exitosos >= 5) ? "✅ COMPLETO" : "⚠️ PARCIAL";
    echo "$estado " . strtoupper($entorno) . ": $exitosos operaciones exitosas\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "ESTRUCTURA DE TABLAS CREADAS\n";
echo str_repeat("=", 60) . "\n";
echo "
┌─────────────────────────────────────────────────────────────┐
│  tbl_acc_hallazgos                                          │
│  ├── Orígenes/problemas detectados                          │
│  ├── Mapeo a numerales 7.1.1, 7.1.2, 7.1.3, 7.1.4          │
│  └── Estados: abierto → en_tratamiento → cerrado            │
├─────────────────────────────────────────────────────────────┤
│  tbl_acc_acciones                                           │
│  ├── Acciones Correctivas, Preventivas, Mejora              │
│  ├── Análisis de causa raíz (JSON para diálogo IA)          │
│  └── Estados: borrador → asignada → en_ejecucion → cerrada  │
├─────────────────────────────────────────────────────────────┤
│  tbl_acc_seguimientos                                       │
│  ├── Evidencias y avances                                   │
│  ├── Bitácora automática                                    │
│  └── Archivos adjuntos                                      │
├─────────────────────────────────────────────────────────────┤
│  tbl_acc_verificaciones                                     │
│  ├── Evaluación de efectividad                              │
│  ├── Métodos: inspección, documental, indicador, etc.       │
│  └── Resultado: efectiva, parcial, no_efectiva              │
├─────────────────────────────────────────────────────────────┤
│  tbl_acc_catalogo_origenes                                  │
│  ├── Catálogo de tipos de origen                            │
│  └── 14 tipos predefinidos con numeral asociado             │
└─────────────────────────────────────────────────────────────┘
";

echo "\n🎉 Script finalizado\n";
echo "\n=== PRÓXIMOS PASOS ===\n";
echo "1. Crear controlador: app/Controllers/AccionesCorrectivasController.php\n";
echo "2. Crear modelos: app/Models/Acc*.php\n";
echo "3. Crear vistas de carpeta: app/Views/documentacion/_tipos/acciones_*.php\n";
echo "4. Agregar detección en DocumentacionController::determinarTipo()\n";
echo "5. Crear dashboard: app/Views/acciones_correctivas/dashboard.php\n";
echo "6. Agregar rutas en Routes.php\n";
echo "7. Agregar filtros de autenticación en Filters.php\n";
