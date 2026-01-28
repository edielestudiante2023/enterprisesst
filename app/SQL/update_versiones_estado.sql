-- Script para actualizar el campo 'estado' de las versiones existentes en tbl_doc_versiones_sst
-- Ejecutar una sola vez para corregir versiones antiguas sin estado

-- Primero, marcar todas las versiones sin estado como 'historico'
UPDATE tbl_doc_versiones_sst
SET estado = 'historico'
WHERE estado IS NULL OR estado = '';

-- Luego, para cada documento, marcar la versión más reciente (mayor id_version) como 'vigente'
-- pero solo si no está ya marcada como 'pendiente_firma'
UPDATE tbl_doc_versiones_sst v1
JOIN (
    SELECT id_documento, MAX(id_version) as max_version
    FROM tbl_doc_versiones_sst
    GROUP BY id_documento
) v2 ON v1.id_documento = v2.id_documento AND v1.id_version = v2.max_version
SET v1.estado = 'vigente'
WHERE v1.estado != 'pendiente_firma';

-- Verificar el resultado
SELECT id_documento, id_version, version_texto, estado
FROM tbl_doc_versiones_sst
ORDER BY id_documento, id_version DESC;
