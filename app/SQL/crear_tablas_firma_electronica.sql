-- ============================================================================
-- TABLAS PARA MÓDULO DE FIRMA ELECTRÓNICA
-- Proyecto: EnterpriseSST
-- Fecha: Enero 2026
-- Descripción: Tablas para firma electrónica tipo DocuSeal
-- ============================================================================

-- Tabla: Solicitudes de firma (adaptada a tbl_documentos_sst)
CREATE TABLE IF NOT EXISTS `tbl_doc_firma_solicitudes` (
    `id_solicitud` INT NOT NULL AUTO_INCREMENT,
    `id_documento` INT NOT NULL COMMENT 'FK a tbl_documentos_sst.id_documento',
    `id_version` INT NULL COMMENT 'FK a tbl_doc_versiones_sst.id_version',

    -- Token de acceso
    `token` VARCHAR(64) NOT NULL COMMENT 'Token único para link de firma',
    `estado` ENUM('pendiente', 'esperando', 'firmado', 'expirado', 'rechazado', 'cancelado') NOT NULL DEFAULT 'pendiente',

    -- Fechas
    `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fecha_expiracion` DATETIME NOT NULL,
    `fecha_firma` DATETIME NULL,

    -- Firmante
    `firmante_tipo` ENUM('elaboro', 'reviso', 'delegado_sst', 'representante_legal') NOT NULL,
    `orden_firma` TINYINT NOT NULL DEFAULT 1,
    `firmante_interno_id` INT NULL COMMENT 'ID usuario del sistema si es firma interna',
    `firmante_email` VARCHAR(255) NULL,
    `firmante_nombre` VARCHAR(255) NOT NULL,
    `firmante_cargo` VARCHAR(100) NULL,
    `firmante_documento` VARCHAR(20) NULL COMMENT 'Cédula o NIT del firmante',

    -- Control de recordatorios
    `recordatorios_enviados` INT NOT NULL DEFAULT 0,
    `ultimo_recordatorio` DATETIME NULL,

    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id_solicitud`),
    UNIQUE KEY `uk_token` (`token`),
    KEY `idx_documento` (`id_documento`),
    KEY `idx_estado` (`estado`),
    KEY `idx_email` (`firmante_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Tabla: Evidencia de firma
CREATE TABLE IF NOT EXISTS `tbl_doc_firma_evidencias` (
    `id_evidencia` INT NOT NULL AUTO_INCREMENT,
    `id_solicitud` INT NOT NULL,

    -- Datos del firmante al momento de firmar
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` TEXT NULL,
    `fecha_hora_utc` DATETIME NOT NULL,
    `geolocalizacion` VARCHAR(255) NULL COMMENT 'Lat,Lng si está disponible',

    -- Tipo y datos de firma
    `tipo_firma` ENUM('draw', 'upload', 'internal') NOT NULL COMMENT 'Dibujada, subida como imagen, o interna del sistema',
    `firma_imagen` LONGTEXT NULL COMMENT 'Base64 de la imagen de firma',

    -- Verificación e integridad
    `hash_documento` VARCHAR(64) NOT NULL COMMENT 'SHA-256 del contenido del documento al momento de firmar',
    `aceptacion_terminos` TINYINT(1) NOT NULL DEFAULT 1,

    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id_evidencia`),
    KEY `idx_solicitud` (`id_solicitud`),
    CONSTRAINT `fk_evidencia_solicitud` FOREIGN KEY (`id_solicitud`)
        REFERENCES `tbl_doc_firma_solicitudes` (`id_solicitud`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Tabla: Log de auditoría de firmas
CREATE TABLE IF NOT EXISTS `tbl_doc_firma_audit_log` (
    `id_log` INT NOT NULL AUTO_INCREMENT,
    `id_solicitud` INT NOT NULL,
    `evento` VARCHAR(50) NOT NULL COMMENT 'solicitud_creada, email_enviado, link_abierto, firma_completada, token_reenviado, solicitud_cancelada',
    `fecha_hora` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `ip_address` VARCHAR(45) NULL,
    `detalles` JSON NULL COMMENT 'Información adicional del evento',

    PRIMARY KEY (`id_log`),
    KEY `idx_solicitud` (`id_solicitud`),
    KEY `idx_evento` (`evento`),
    CONSTRAINT `fk_audit_solicitud` FOREIGN KEY (`id_solicitud`)
        REFERENCES `tbl_doc_firma_solicitudes` (`id_solicitud`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
