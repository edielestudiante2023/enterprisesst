-- =====================================================
-- MIGRACION: Agregar campo codigo a tbl_documentos_sst
-- Fecha: Enero 2026
-- Descripcion: Agrega el campo codigo para identificar
--              documentos con formato PRG-CAP-001
-- =====================================================

USE empresas_sst;

-- Agregar campo codigo si no existe
SET @dbname = 'empresas_sst';
SET @tablename = 'tbl_documentos_sst';
SET @columnname = 'codigo';
SET @preparedStatement = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = @dbname
     AND TABLE_NAME = @tablename
     AND COLUMN_NAME = @columnname) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(50) NULL COMMENT "Codigo del documento (PRG-CAP-001)" AFTER titulo')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Agregar indice al campo codigo
SET @preparedStatement = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
     WHERE TABLE_SCHEMA = @dbname
     AND TABLE_NAME = @tablename
     AND INDEX_NAME = 'idx_codigo') > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE ', @tablename, ' ADD INDEX idx_codigo (codigo)')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Actualizar documentos existentes con codigo basado en tipo
-- PRG-CAP para programa_capacitacion
UPDATE tbl_documentos_sst
SET codigo = CONCAT('PRG-CAP-', LPAD(id_documento, 3, '0'))
WHERE tipo_documento = 'programa_capacitacion'
AND (codigo IS NULL OR codigo = '');

-- Mensaje de confirmacion
SELECT 'Migracion completada: Campo codigo agregado a tbl_documentos_sst' AS mensaje;
SELECT COUNT(*) AS documentos_actualizados FROM tbl_documentos_sst WHERE codigo IS NOT NULL;
