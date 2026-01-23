-- Script SQL para Módulo de Firma Electrónica
-- Ejecutar en la base de datos enterprisesst

-- 1. Agregar campo orden_firma a la tabla de solicitudes
ALTER TABLE `tbl_doc_firma_solicitudes`
ADD COLUMN `orden_firma` TINYINT(1) NOT NULL DEFAULT 1 AFTER `firmante_documento`;

-- 2. Agregar campos de firmantes a contexto del cliente (si no existen)
-- Primero verificamos si ya existen las columnas

-- Campos del Delegado SST
ALTER TABLE `tbl_cliente_contexto_sst`
ADD COLUMN IF NOT EXISTS `requiere_delegado_sst` TINYINT(1) NOT NULL DEFAULT 0,
ADD COLUMN IF NOT EXISTS `delegado_sst_nombre` VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS `delegado_sst_cargo` VARCHAR(100) NULL,
ADD COLUMN IF NOT EXISTS `delegado_sst_email` VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS `delegado_sst_cedula` VARCHAR(20) NULL;

-- Campos del Representante Legal
ALTER TABLE `tbl_cliente_contexto_sst`
ADD COLUMN IF NOT EXISTS `representante_legal_nombre` VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS `representante_legal_cargo` VARCHAR(100) NULL DEFAULT 'Representante Legal',
ADD COLUMN IF NOT EXISTS `representante_legal_email` VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS `representante_legal_cedula` VARCHAR(20) NULL;

-- 3. Actualizar el estado 'esperando' para firmas en cadena
-- El estado 'esperando' significa que esta firma esta en cola, esperando que la anterior se complete
-- Estados posibles: pendiente, esperando, firmado, expirado, cancelado

-- 4. Indice para mejorar rendimiento de busquedas por documento y estado
ALTER TABLE `tbl_doc_firma_solicitudes`
ADD INDEX IF NOT EXISTS `idx_doc_estado` (`id_documento`, `estado`);

ALTER TABLE `tbl_doc_firma_solicitudes`
ADD INDEX IF NOT EXISTS `idx_orden` (`id_documento`, `orden_firma`);
