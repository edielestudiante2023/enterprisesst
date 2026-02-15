-- ============================================================================
-- Migracion: crear version 1.0 para documentos de comites que ya existen
-- en tbl_documentos_sst pero no tienen registro en tbl_doc_versiones_sst
-- ============================================================================

-- Insertar version 1.0 para documentos de comite existentes sin version
INSERT INTO tbl_doc_versiones_sst (
    id_documento,
    id_cliente,
    tipo_documento,
    codigo,
    titulo,
    anio,
    version,
    version_texto,
    tipo_cambio,
    descripcion_cambio,
    contenido_snapshot,
    estado,
    autorizado_por,
    fecha_autorizacion,
    created_at
)
SELECT
    d.id_documento,
    d.id_cliente,
    d.tipo_documento,
    d.codigo,
    d.titulo,
    d.anio,
    1,
    '1.0',
    'mayor',
    'Migracion: documento preexistente integrado al sistema de versionamiento',
    d.contenido,
    'vigente',
    'Sistema (migracion)',
    COALESCE(d.created_at, NOW()),
    NOW()
FROM tbl_documentos_sst d
LEFT JOIN tbl_doc_versiones_sst v ON v.id_documento = d.id_documento
WHERE d.tipo_documento LIKE 'acta_constitucion_%'
   OR d.tipo_documento LIKE 'acta_recomposicion_%'
HAVING COUNT(v.id_version) = 0;

-- Actualizar estado a 'aprobado' para documentos migrados que estaban en 'generado'
UPDATE tbl_documentos_sst d
INNER JOIN tbl_doc_versiones_sst v ON v.id_documento = d.id_documento AND v.descripcion_cambio LIKE 'Migracion:%'
SET d.estado = 'aprobado',
    d.fecha_aprobacion = d.created_at,
    d.updated_at = NOW()
WHERE (d.tipo_documento LIKE 'acta_constitucion_%' OR d.tipo_documento LIKE 'acta_recomposicion_%')
  AND d.estado = 'generado';
