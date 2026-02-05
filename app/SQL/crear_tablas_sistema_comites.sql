-- =====================================================
-- SISTEMA DE CONFORMACIÓN DE COMITÉS SST
-- Fecha: 2026-02-03
-- Autor: Claude Code
-- =====================================================

-- Tabla principal de procesos electorales/de conformación
CREATE TABLE IF NOT EXISTS tbl_procesos_electorales (
    id_proceso INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_comite INT NULL,
    tipo_comite ENUM('COPASST', 'COCOLAB', 'BRIGADA', 'VIGIA') NOT NULL,
    anio INT NOT NULL,
    estado ENUM('configuracion', 'inscripcion', 'votacion', 'escrutinio',
                'designacion_empleador', 'firmas', 'completado', 'cancelado')
                DEFAULT 'configuracion',

    -- Configuración del proceso
    plazas_principales INT NOT NULL DEFAULT 2,
    plazas_suplentes INT NOT NULL DEFAULT 2,

    -- Fechas del proceso
    fecha_inicio_inscripcion DATETIME NULL,
    fecha_fin_inscripcion DATETIME NULL,
    fecha_inicio_votacion DATETIME NULL,
    fecha_fin_votacion DATETIME NULL,
    fecha_escrutinio DATETIME NULL,
    fecha_completado DATETIME NULL,

    -- Token para enlace de votación (24 horas)
    token_votacion VARCHAR(64) UNIQUE NULL,

    -- Período del comité
    fecha_inicio_periodo DATE NULL,
    fecha_fin_periodo DATE NULL,

    -- Auditoría
    id_consultor INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_cliente (id_cliente),
    INDEX idx_comite (id_comite),
    INDEX idx_estado (estado),
    INDEX idx_token (token_votacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla unificada de participantes (candidatos trabajadores + designados empleador)
CREATE TABLE IF NOT EXISTS tbl_participantes_comite (
    id_participante INT AUTO_INCREMENT PRIMARY KEY,
    id_proceso INT NOT NULL,

    -- Datos personales (comunes a todos)
    nombre_completo VARCHAR(200) NOT NULL,
    tipo_documento VARCHAR(10) DEFAULT 'CC',
    numero_documento VARCHAR(20) NOT NULL,
    cargo VARCHAR(100) NOT NULL,
    area_dependencia VARCHAR(100) NULL,
    email VARCHAR(150) NOT NULL,
    telefono VARCHAR(20) NULL,
    foto_url VARCHAR(500) NOT NULL,

    -- Certificación 50 horas (obligatorio por ley para COPASST)
    certificado_50_horas_url VARCHAR(500) NULL,
    certificado_50_horas_fecha DATE NULL,
    certificado_50_horas_institucion VARCHAR(200) NULL,

    -- Tipo de participación
    representacion ENUM('empleador', 'trabajador') NOT NULL,
    origen ENUM('votacion', 'designacion') NOT NULL,

    -- Campos para votación (NULL si es designación)
    votos_obtenidos INT DEFAULT 0,
    es_ganador TINYINT(1) DEFAULT 0,
    orden_resultado INT NULL,

    -- Asignación final
    tipo_miembro ENUM('principal', 'suplente', 'reserva', 'no_electo', 'pendiente') DEFAULT 'pendiente',

    -- Roles especiales del comité
    rol_comite ENUM('presidente', 'secretario', 'miembro') DEFAULT 'miembro',

    -- Transferencia a comité
    transferido_a_comite TINYINT(1) DEFAULT 0,
    id_miembro_comite INT NULL,
    fecha_transferencia DATETIME NULL,

    -- Estado
    estado ENUM('inscrito', 'activo', 'retirado') DEFAULT 'inscrito',
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_proceso (id_proceso),
    INDEX idx_representacion (representacion),
    INDEX idx_tipo_miembro (tipo_miembro),
    UNIQUE KEY unique_participante_proceso (id_proceso, numero_documento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de jurados de votación
CREATE TABLE IF NOT EXISTS tbl_jurados_eleccion (
    id_jurado INT AUTO_INCREMENT PRIMARY KEY,
    id_proceso INT NOT NULL,

    -- Datos del jurado
    nombre_completo VARCHAR(200) NOT NULL,
    tipo_documento VARCHAR(10) DEFAULT 'CC',
    numero_documento VARCHAR(20) NOT NULL,
    cargo VARCHAR(100) NOT NULL,
    area_dependencia VARCHAR(100) NULL,
    email VARCHAR(150) NOT NULL,

    -- Rol en el proceso
    rol ENUM('presidente_mesa', 'secretario_mesa', 'testigo') DEFAULT 'testigo',

    -- Firma
    ha_firmado TINYINT(1) DEFAULT 0,
    fecha_firma DATETIME NULL,

    -- Estado
    estado ENUM('activo', 'retirado') DEFAULT 'activo',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_proceso (id_proceso),
    UNIQUE KEY unique_jurado_proceso (id_proceso, numero_documento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de votos (anónimo pero auditable)
CREATE TABLE IF NOT EXISTS tbl_votos_eleccion (
    id_voto INT AUTO_INCREMENT PRIMARY KEY,
    id_proceso INT NOT NULL,
    id_participante INT NOT NULL,

    -- Validación del votante (hash para evitar duplicados sin revelar identidad)
    hash_votante VARCHAR(64) NOT NULL,

    -- Auditoría
    ip_votante VARCHAR(45) NULL,
    user_agent TEXT NULL,
    fecha_voto DATETIME DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_proceso (id_proceso),
    INDEX idx_participante (id_participante),
    UNIQUE KEY unique_votante_proceso (id_proceso, hash_votante)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de voluntarios para brigada de emergencias
CREATE TABLE IF NOT EXISTS tbl_voluntarios_brigada (
    id_voluntario INT AUTO_INCREMENT PRIMARY KEY,
    id_proceso INT NOT NULL,

    -- Datos
    nombre_completo VARCHAR(200) NOT NULL,
    tipo_documento VARCHAR(10) DEFAULT 'CC',
    numero_documento VARCHAR(20) NOT NULL,
    cargo VARCHAR(100) NOT NULL,
    area_dependencia VARCHAR(100) NULL,
    email VARCHAR(150) NOT NULL,
    telefono VARCHAR(20) NULL,
    foto_url VARCHAR(500) NULL,

    -- Certificaciones de brigada
    certificado_brigada_url VARCHAR(500) NULL,
    certificado_brigada_fecha DATE NULL,

    -- Designación
    fue_designado TINYINT(1) DEFAULT 0,
    rol_brigada VARCHAR(100) NULL,
    fecha_designacion DATETIME NULL,

    -- Estado
    estado ENUM('voluntario', 'designado', 'retirado') DEFAULT 'voluntario',
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_proceso (id_proceso),
    UNIQUE KEY unique_voluntario_proceso (id_proceso, numero_documento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de documentos generados en cada proceso
CREATE TABLE IF NOT EXISTS tbl_documentos_proceso_electoral (
    id_documento_proceso INT AUTO_INCREMENT PRIMARY KEY,
    id_proceso INT NOT NULL,

    -- Tipo de documento
    tipo_documento ENUM(
        'acta_apertura',
        'registro_votantes',
        'acta_cierre',
        'resultados_votacion',
        'acta_constitucion',
        'acta_designacion_brigada',
        'acta_designacion_vigia'
    ) NOT NULL,

    -- Archivo
    archivo_pdf VARCHAR(500) NULL,
    archivo_word VARCHAR(500) NULL,

    -- Estado de firmas
    estado_firmas ENUM('pendiente', 'en_proceso', 'completado') DEFAULT 'pendiente',
    id_solicitud_firma INT NULL,

    -- Timestamps
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_proceso (id_proceso),
    INDEX idx_tipo (tipo_documento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de registro de empates resueltos (para auditoría)
CREATE TABLE IF NOT EXISTS tbl_empates_resueltos (
    id_empate INT AUTO_INCREMENT PRIMARY KEY,
    id_proceso INT NOT NULL,

    -- Participantes empatados (JSON array de ids)
    participantes_empatados JSON NOT NULL,

    -- Resolución
    metodo_resolucion ENUM('concertacion', 'sorteo', 'antiguedad') DEFAULT 'concertacion',
    participante_ganador INT NOT NULL,
    acta_resolucion TEXT NULL,

    -- Auditoría
    resuelto_por INT NULL,
    fecha_resolucion DATETIME DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_proceso (id_proceso)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- VERIFICACIÓN DE TABLAS CREADAS
-- =====================================================
SELECT 'tbl_procesos_electorales' as tabla, COUNT(*) as registros FROM tbl_procesos_electorales
UNION ALL
SELECT 'tbl_participantes_comite', COUNT(*) FROM tbl_participantes_comite
UNION ALL
SELECT 'tbl_jurados_eleccion', COUNT(*) FROM tbl_jurados_eleccion
UNION ALL
SELECT 'tbl_votos_eleccion', COUNT(*) FROM tbl_votos_eleccion
UNION ALL
SELECT 'tbl_voluntarios_brigada', COUNT(*) FROM tbl_voluntarios_brigada
UNION ALL
SELECT 'tbl_documentos_proceso_electoral', COUNT(*) FROM tbl_documentos_proceso_electoral
UNION ALL
SELECT 'tbl_empates_resueltos', COUNT(*) FROM tbl_empates_resueltos;
