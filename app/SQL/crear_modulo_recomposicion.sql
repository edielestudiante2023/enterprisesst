-- =====================================================
-- Sistema de Conformación de Comités SST
-- MÓDULO DE RECOMPOSICIÓN DE COMITÉS
-- =====================================================
-- Aplica a: COPASST, COCOLAB
-- Permite registrar cambios de miembros sin nuevo proceso electoral
-- =====================================================

-- 1. TABLA PRINCIPAL DE RECOMPOSICIONES
CREATE TABLE IF NOT EXISTS `tbl_recomposiciones_comite` (
    `id_recomposicion` INT AUTO_INCREMENT PRIMARY KEY,
    `id_proceso` INT NOT NULL COMMENT 'FK a tbl_procesos_electorales (proceso original)',
    `id_cliente` INT NOT NULL COMMENT 'FK a tbl_cliente',

    -- Fecha de la recomposición
    `fecha_recomposicion` DATE NOT NULL,
    `numero_recomposicion` INT DEFAULT 1 COMMENT 'Número secuencial de recomposición en este proceso',

    -- Miembro saliente
    `id_candidato_saliente` INT NOT NULL COMMENT 'FK a tbl_candidatos_comite',
    `motivo_salida` ENUM(
        'terminacion_contrato',
        'renuncia_voluntaria',
        'sancion_disciplinaria',
        'violacion_confidencialidad',
        'inasistencia_reiterada',
        'incumplimiento_funciones',
        'fallecimiento',
        'otro'
    ) NOT NULL,
    `motivo_detalle` TEXT DEFAULT NULL COMMENT 'Descripción adicional del motivo',
    `fecha_efectiva_salida` DATE NOT NULL,

    -- Miembro entrante
    `id_candidato_entrante` INT DEFAULT NULL COMMENT 'FK a tbl_candidatos_comite (si existe)',
    `tipo_ingreso` ENUM(
        'siguiente_votacion',      -- Automático: siguiente en orden de votación
        'designacion_empleador',   -- Empleador designa nuevo representante
        'asamblea_extraordinaria'  -- Se convocó nueva asamblea
    ) NOT NULL,

    -- Datos del entrante si no existe en candidatos (para empleador)
    `entrante_nombres` VARCHAR(100) DEFAULT NULL,
    `entrante_apellidos` VARCHAR(100) DEFAULT NULL,
    `entrante_documento` VARCHAR(20) DEFAULT NULL,
    `entrante_cargo` VARCHAR(100) DEFAULT NULL,
    `entrante_email` VARCHAR(150) DEFAULT NULL,
    `entrante_telefono` VARCHAR(20) DEFAULT NULL,

    -- Estado de la recomposición
    `estado` ENUM('borrador', 'pendiente_firmas', 'firmado', 'cancelado') DEFAULT 'borrador',

    -- Documento generado
    `id_documento` INT DEFAULT NULL COMMENT 'FK a tbl_documentos_sst cuando se genera',

    -- Observaciones
    `observaciones` TEXT DEFAULT NULL,
    `justificacion_legal` TEXT DEFAULT NULL COMMENT 'Texto de justificación para el acta',

    -- Metadatos
    `created_by` INT DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Índices
    INDEX `idx_proceso` (`id_proceso`),
    INDEX `idx_cliente` (`id_cliente`),
    INDEX `idx_fecha` (`fecha_recomposicion`),
    INDEX `idx_estado` (`estado`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Registro de recomposiciones de comités SST';

-- 2. AGREGAR COLUMNAS A tbl_candidatos_comite PARA TRACKING DE ESTADO
ALTER TABLE `tbl_candidatos_comite`
    ADD COLUMN IF NOT EXISTS `estado_miembro` ENUM('activo', 'retirado', 'reemplazado') DEFAULT 'activo'
        COMMENT 'Estado actual como miembro del comité' AFTER `estado`,
    ADD COLUMN IF NOT EXISTS `fecha_ingreso_comite` DATE DEFAULT NULL
        COMMENT 'Fecha de ingreso al comité (inicial o por recomposición)' AFTER `estado_miembro`,
    ADD COLUMN IF NOT EXISTS `fecha_retiro_comite` DATE DEFAULT NULL
        COMMENT 'Fecha de retiro del comité' AFTER `fecha_ingreso_comite`,
    ADD COLUMN IF NOT EXISTS `es_recomposicion` TINYINT(1) DEFAULT 0
        COMMENT 'Si ingresó por recomposición' AFTER `fecha_retiro_comite`,
    ADD COLUMN IF NOT EXISTS `id_recomposicion_ingreso` INT DEFAULT NULL
        COMMENT 'FK a tbl_recomposiciones_comite si aplica' AFTER `es_recomposicion`,
    ADD COLUMN IF NOT EXISTS `posicion_votacion` INT DEFAULT NULL
        COMMENT 'Posición en la votación original (1, 2, 3...)' AFTER `votos_obtenidos`;

-- 3. ÍNDICE PARA BÚSQUEDA DE MIEMBROS ACTIVOS
CREATE INDEX IF NOT EXISTS `idx_estado_miembro`
ON `tbl_candidatos_comite` (`id_proceso`, `estado_miembro`, `representacion`);

-- 4. ÍNDICE PARA POSICIÓN DE VOTACIÓN (para saber quién sigue)
CREATE INDEX IF NOT EXISTS `idx_posicion_votacion`
ON `tbl_candidatos_comite` (`id_proceso`, `representacion`, `posicion_votacion`);
