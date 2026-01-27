-- =====================================================
-- Agregar columna id_plantilla a tbl_doc_documentos
-- Ejecutar en LOCAL y PRODUCTION
-- Fecha: 2026-01-24
-- =====================================================

-- Verificar si la columna ya existe antes de agregarla
SET @dbname = DATABASE();
SET @tablename = 'tbl_doc_documentos';
SET @columnname = 'id_plantilla';

SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname
   AND TABLE_NAME = @tablename
   AND COLUMN_NAME = @columnname) > 0,
  'SELECT "Column already exists" AS message',
  'ALTER TABLE tbl_doc_documentos ADD COLUMN id_plantilla INT NULL AFTER id_tipo'
));

PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Agregar FK si no existe (opcional, si tbl_doc_plantillas existe)
-- ALTER TABLE tbl_doc_documentos
-- ADD CONSTRAINT fk_doc_documentos_plantilla
-- FOREIGN KEY (id_plantilla) REFERENCES tbl_doc_plantillas(id_plantilla) ON DELETE SET NULL;

-- Verificar resultado
SELECT
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'tbl_doc_documentos'
AND COLUMN_NAME = 'id_plantilla';
