-- ============================================================================
-- FIX: Corregir caracteres mal codificados en tbl_doc_plantillas
-- Problema: Caracteres UTF-8 fueron insertados con encoding latin1
-- Fecha: Enero 2026
-- ============================================================================

SET NAMES utf8mb4;

-- Actualizar nombres con caracteres corruptos
UPDATE tbl_doc_plantillas SET nombre = CONVERT(CAST(CONVERT(nombre USING latin1) AS BINARY) USING utf8mb4)
WHERE nombre LIKE '%├%' OR nombre LIKE '%┬%' OR nombre LIKE '%ÔÇ%';

-- Actualizar descripciones con caracteres corruptos
UPDATE tbl_doc_plantillas SET descripcion = CONVERT(CAST(CONVERT(descripcion USING latin1) AS BINARY) USING utf8mb4)
WHERE descripcion LIKE '%├%' OR descripcion LIKE '%┬%' OR descripcion LIKE '%ÔÇ%';

-- Actualizar estructura_json con caracteres corruptos
UPDATE tbl_doc_plantillas SET estructura_json = CONVERT(CAST(CONVERT(estructura_json USING latin1) AS BINARY) USING utf8mb4)
WHERE estructura_json LIKE '%├%' OR estructura_json LIKE '%┬%' OR estructura_json LIKE '%ÔÇ%';

-- Correcciones específicas si la conversión automática no funciona
-- Estos son los reemplazos más comunes de caracteres mal codificados

-- Vocales acentuadas
UPDATE tbl_doc_plantillas SET nombre = REPLACE(nombre, '├│', 'ó') WHERE nombre LIKE '%├│%';
UPDATE tbl_doc_plantillas SET nombre = REPLACE(nombre, '├í', 'á') WHERE nombre LIKE '%├í%';
UPDATE tbl_doc_plantillas SET nombre = REPLACE(nombre, '├®', 'é') WHERE nombre LIKE '%├®%';
UPDATE tbl_doc_plantillas SET nombre = REPLACE(nombre, '├¡', 'í') WHERE nombre LIKE '%├¡%';
UPDATE tbl_doc_plantillas SET nombre = REPLACE(nombre, '├║', 'ú') WHERE nombre LIKE '%├║%';
UPDATE tbl_doc_plantillas SET nombre = REPLACE(nombre, '├▒', 'ñ') WHERE nombre LIKE '%├▒%';

UPDATE tbl_doc_plantillas SET descripcion = REPLACE(descripcion, '├│', 'ó') WHERE descripcion LIKE '%├│%';
UPDATE tbl_doc_plantillas SET descripcion = REPLACE(descripcion, '├í', 'á') WHERE descripcion LIKE '%├í%';
UPDATE tbl_doc_plantillas SET descripcion = REPLACE(descripcion, '├®', 'é') WHERE descripcion LIKE '%├®%';
UPDATE tbl_doc_plantillas SET descripcion = REPLACE(descripcion, '├¡', 'í') WHERE descripcion LIKE '%├¡%';
UPDATE tbl_doc_plantillas SET descripcion = REPLACE(descripcion, '├║', 'ú') WHERE descripcion LIKE '%├║%';
UPDATE tbl_doc_plantillas SET descripcion = REPLACE(descripcion, '├▒', 'ñ') WHERE descripcion LIKE '%├▒%';

-- Otros patrones comunes de corrupción UTF-8
UPDATE tbl_doc_plantillas SET nombre = REPLACE(nombre, 'Ã³', 'ó') WHERE nombre LIKE '%Ã³%';
UPDATE tbl_doc_plantillas SET nombre = REPLACE(nombre, 'Ã¡', 'á') WHERE nombre LIKE '%Ã¡%';
UPDATE tbl_doc_plantillas SET nombre = REPLACE(nombre, 'Ã©', 'é') WHERE nombre LIKE '%Ã©%';
UPDATE tbl_doc_plantillas SET nombre = REPLACE(nombre, 'Ã­', 'í') WHERE nombre LIKE '%Ã­%';
UPDATE tbl_doc_plantillas SET nombre = REPLACE(nombre, 'Ãº', 'ú') WHERE nombre LIKE '%Ãº%';
UPDATE tbl_doc_plantillas SET nombre = REPLACE(nombre, 'Ã±', 'ñ') WHERE nombre LIKE '%Ã±%';
UPDATE tbl_doc_plantillas SET nombre = REPLACE(nombre, 'Ã'', 'Ñ') WHERE nombre LIKE '%Ã'%';

UPDATE tbl_doc_plantillas SET descripcion = REPLACE(descripcion, 'Ã³', 'ó') WHERE descripcion LIKE '%Ã³%';
UPDATE tbl_doc_plantillas SET descripcion = REPLACE(descripcion, 'Ã¡', 'á') WHERE descripcion LIKE '%Ã¡%';
UPDATE tbl_doc_plantillas SET descripcion = REPLACE(descripcion, 'Ã©', 'é') WHERE descripcion LIKE '%Ã©%';
UPDATE tbl_doc_plantillas SET descripcion = REPLACE(descripcion, 'Ã­', 'í') WHERE descripcion LIKE '%Ã­%';
UPDATE tbl_doc_plantillas SET descripcion = REPLACE(descripcion, 'Ãº', 'ú') WHERE descripcion LIKE '%Ãº%';
UPDATE tbl_doc_plantillas SET descripcion = REPLACE(descripcion, 'Ã±', 'ñ') WHERE descripcion LIKE '%Ã±%';

-- Verificar resultados
SELECT id_plantilla, nombre, descripcion FROM tbl_doc_plantillas LIMIT 20;
