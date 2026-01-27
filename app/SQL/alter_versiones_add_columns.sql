-- Agregar columnas faltantes a tbl_doc_versiones_sst
-- id_cliente, codigo, titulo, anio (necesarias para aprobar documento)
ALTER TABLE tbl_doc_versiones_sst
    ADD COLUMN IF NOT EXISTS id_cliente INT(11) NULL AFTER id_documento,
    ADD COLUMN IF NOT EXISTS codigo VARCHAR(50) NULL AFTER id_cliente,
    ADD COLUMN IF NOT EXISTS titulo VARCHAR(255) NULL AFTER codigo,
    ADD COLUMN IF NOT EXISTS anio INT(4) NULL AFTER titulo;
