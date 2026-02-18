-- ============================================================================
-- Migracion: Agregar sub-carpeta "Documentos Externos" dentro de 2.5.1
-- Para clientes existentes que ya tienen la carpeta 2.5.1
-- Fecha: 2026-02-17
-- ============================================================================

USE empresas_sst;

-- Insertar sub-carpeta 2.5.1.1 para cada cliente que tenga la carpeta 2.5.1
-- y que NO tenga ya la sub-carpeta 2.5.1.1
INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
SELECT
    c.id_cliente,
    c.id_carpeta,
    '2.5.1.1. Listado Maestro de Documentos Externos',
    '2.5.1.1',
    1,
    'estandar',
    'file-earmark-arrow-up'
FROM tbl_doc_carpetas c
WHERE c.codigo = '2.5.1'
AND NOT EXISTS (
    SELECT 1 FROM tbl_doc_carpetas sub
    WHERE sub.id_carpeta_padre = c.id_carpeta
    AND sub.codigo = '2.5.1.1'
);

-- Verificar resultados
SELECT
    c.id_carpeta AS id_padre,
    c.id_cliente,
    c.nombre AS carpeta_padre,
    sub.id_carpeta AS id_sub,
    sub.nombre AS subcarpeta
FROM tbl_doc_carpetas c
LEFT JOIN tbl_doc_carpetas sub ON sub.id_carpeta_padre = c.id_carpeta AND sub.codigo = '2.5.1.1'
WHERE c.codigo = '2.5.1'
ORDER BY c.id_cliente;
