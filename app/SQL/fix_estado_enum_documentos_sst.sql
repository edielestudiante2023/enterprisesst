-- =====================================================
-- Extender ENUM estado de tbl_documentos_sst
-- para soportar flujo de firma electronica
-- =====================================================

USE empresas_sst;

-- Agregar estados: en_revision, pendiente_firma, firmado
ALTER TABLE tbl_documentos_sst
MODIFY COLUMN estado ENUM('borrador', 'generado', 'en_revision', 'pendiente_firma', 'aprobado', 'firmado', 'obsoleto') NOT NULL DEFAULT 'borrador';
