-- Tabla para gestionar indicadores del SG-SST
-- Resolución 0312/2019 - Indicadores según estándares aplicables

CREATE TABLE IF NOT EXISTS `tbl_indicadores_sst` (
    `id_indicador` INT(11) NOT NULL AUTO_INCREMENT,
    `id_cliente` INT(11) NOT NULL,
    `id_actividad_pta` INT(11) NULL COMMENT 'FK a tbl_pta_cliente si el indicador deriva de una actividad',

    -- Información del indicador
    `nombre_indicador` VARCHAR(255) NOT NULL,
    `tipo_indicador` ENUM('estructura', 'proceso', 'resultado') NOT NULL DEFAULT 'proceso',
    `formula` VARCHAR(500) NULL COMMENT 'Fórmula del indicador',
    `meta` DECIMAL(10,2) NULL COMMENT 'Meta del indicador (porcentaje o valor)',
    `unidad_medida` VARCHAR(50) DEFAULT '%' COMMENT 'Porcentaje, cantidad, días, etc.',
    `periodicidad` ENUM('mensual', 'trimestral', 'semestral', 'anual') DEFAULT 'trimestral',

    -- Numerales de la Resolución 0312
    `numeral_resolucion` VARCHAR(20) NULL COMMENT 'Ej: 2.11.1, 3.1.4',
    `phva` ENUM('planear', 'hacer', 'verificar', 'actuar') DEFAULT 'verificar',

    -- Valores de medición
    `valor_numerador` DECIMAL(10,2) NULL,
    `valor_denominador` DECIMAL(10,2) NULL,
    `valor_resultado` DECIMAL(10,2) NULL,
    `fecha_medicion` DATE NULL,

    -- Estado y seguimiento
    `cumple_meta` TINYINT(1) NULL COMMENT '1=Sí cumple, 0=No cumple, NULL=Sin medir',
    `observaciones` TEXT NULL,
    `acciones_mejora` TEXT NULL COMMENT 'Acciones cuando no cumple meta',

    -- Auditoría
    `activo` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `created_by` INT(11) NULL,
    `updated_by` INT(11) NULL,

    PRIMARY KEY (`id_indicador`),
    KEY `idx_cliente` (`id_cliente`),
    KEY `idx_actividad` (`id_actividad_pta`),
    KEY `idx_tipo` (`tipo_indicador`),
    KEY `idx_activo` (`activo`),
    KEY `idx_fecha_medicion` (`fecha_medicion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para histórico de mediciones de indicadores
CREATE TABLE IF NOT EXISTS `tbl_indicadores_sst_mediciones` (
    `id_medicion` INT(11) NOT NULL AUTO_INCREMENT,
    `id_indicador` INT(11) NOT NULL,
    `periodo` VARCHAR(20) NOT NULL COMMENT 'Ej: 2024-Q1, 2024-01',
    `valor_numerador` DECIMAL(10,2) NULL,
    `valor_denominador` DECIMAL(10,2) NULL,
    `valor_resultado` DECIMAL(10,2) NULL,
    `cumple_meta` TINYINT(1) NULL,
    `observaciones` TEXT NULL,
    `fecha_registro` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `registrado_por` INT(11) NULL,

    PRIMARY KEY (`id_medicion`),
    KEY `idx_indicador` (`id_indicador`),
    KEY `idx_periodo` (`periodo`),
    CONSTRAINT `fk_medicion_indicador` FOREIGN KEY (`id_indicador`)
        REFERENCES `tbl_indicadores_sst` (`id_indicador`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
