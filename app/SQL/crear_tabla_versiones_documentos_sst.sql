-- =====================================================
-- TABLA PARA VERSIONES DE DOCUMENTOS SST
-- Almacena historial de versiones de documentos
-- =====================================================

-- Agregar columnas de aprobación a tbl_documentos_sst si no existen
ALTER TABLE `tbl_documentos_sst`
ADD COLUMN IF NOT EXISTS `fecha_aprobacion` DATETIME NULL AFTER `estado`,
ADD COLUMN IF NOT EXISTS `aprobado_por` INT(11) NULL AFTER `fecha_aprobacion`,
ADD COLUMN IF NOT EXISTS `motivo_version` VARCHAR(255) NULL AFTER `aprobado_por`,
ADD COLUMN IF NOT EXISTS `tipo_cambio_pendiente` ENUM('mayor', 'menor') NULL AFTER `motivo_version`;

-- Crear tabla de versiones
CREATE TABLE IF NOT EXISTS `tbl_doc_versiones_sst` (
    `id_version` INT(11) NOT NULL AUTO_INCREMENT,
    `id_documento` INT(11) NOT NULL,
    `version` INT(11) NOT NULL COMMENT 'Numero de version: 1, 2, 3...',
    `version_texto` VARCHAR(10) NOT NULL COMMENT 'Version texto: 1.0, 1.1, 2.0',
    `tipo_cambio` ENUM('mayor', 'menor') NOT NULL DEFAULT 'menor' COMMENT 'Mayor: X+1.0, Menor: X.Y+1',
    `descripcion_cambio` TEXT NOT NULL COMMENT 'Descripcion del cambio realizado',
    `contenido_snapshot` LONGTEXT NULL COMMENT 'JSON con snapshot del contenido al momento de aprobar',
    `estado` ENUM('vigente', 'obsoleto') NOT NULL DEFAULT 'vigente',
    `autorizado_por` VARCHAR(255) NULL COMMENT 'Nombre de quien autorizo',
    `autorizado_por_id` INT(11) NULL COMMENT 'ID del usuario que autorizo',
    `fecha_autorizacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `archivo_pdf` VARCHAR(255) NULL COMMENT 'Ruta al PDF generado de esta version',
    `hash_documento` VARCHAR(64) NULL COMMENT 'SHA-256 del PDF para integridad',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_version`),
    KEY `idx_documento` (`id_documento`),
    KEY `idx_version` (`version`),
    KEY `idx_estado` (`estado`),
    CONSTRAINT `fk_version_documento_sst` FOREIGN KEY (`id_documento`)
        REFERENCES `tbl_documentos_sst` (`id_documento`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
