-- =====================================================
-- TABLA PARA DOCUMENTOS DEL SG-SST
-- Almacena documentos generados por el sistema
-- =====================================================

CREATE TABLE IF NOT EXISTS `tbl_documentos_sst` (
    `id_documento` INT(11) NOT NULL AUTO_INCREMENT,
    `id_cliente` INT(11) NOT NULL,
    `tipo_documento` VARCHAR(100) NOT NULL COMMENT 'programa_capacitacion, politica_sst, objetivos_sst, etc.',
    `titulo` VARCHAR(255) NOT NULL,
    `anio` INT(4) NOT NULL,
    `contenido` LONGTEXT NULL COMMENT 'Contenido JSON del documento',
    `version` INT(11) DEFAULT 1,
    `estado` ENUM('borrador', 'generado', 'aprobado', 'obsoleto') DEFAULT 'borrador',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `created_by` INT(11) NULL,
    `updated_by` INT(11) NULL,
    PRIMARY KEY (`id_documento`),
    KEY `idx_cliente` (`id_cliente`),
    KEY `idx_tipo` (`tipo_documento`),
    KEY `idx_anio` (`anio`),
    UNIQUE KEY `uk_cliente_tipo_anio` (`id_cliente`, `tipo_documento`, `anio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
