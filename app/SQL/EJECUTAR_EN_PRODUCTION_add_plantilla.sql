-- =====================================================
-- EJECUTAR EN PRODUCCIÓN (DigitalOcean)
-- Agregar columna id_plantilla a tbl_doc_documentos
-- Fecha: 2026-01-24
-- =====================================================
--
-- Opciones para ejecutar:
-- 1. Via phpMyAdmin
-- 2. Via SSH: mysql -u enterprisesst -p enterprisesst < este_archivo.sql
-- 3. Via consola MySQL
-- =====================================================

-- Verificar si la columna ya existe
SELECT COUNT(*) as existe FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'enterprisesst'
AND TABLE_NAME = 'tbl_doc_documentos'
AND COLUMN_NAME = 'id_plantilla';

-- Agregar la columna (ejecutar solo si no existe)
ALTER TABLE tbl_doc_documentos ADD COLUMN id_plantilla INT NULL AFTER id_tipo;

-- Verificar que se agregó correctamente
DESCRIBE tbl_doc_documentos;
