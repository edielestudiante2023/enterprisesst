-- =====================================================
-- SQL para registrar la plantilla "Asignacion de Responsable del SG-SST"
-- Ejecutar en la base de datos de produccion
-- =====================================================

-- 1. Verificar/crear el tipo de documento "Acta" si no existe
INSERT INTO tbl_doc_tipos (nombre, codigo, descripcion, activo, created_at)
SELECT 'Acta', 'ACT', 'Actas y asignaciones formales del SG-SST', 1, NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM tbl_doc_tipos WHERE codigo = 'ACT');

-- 2. Obtener el id_tipo para "Acta"
SET @id_tipo_acta = (SELECT id_tipo FROM tbl_doc_tipos WHERE codigo = 'ACT' LIMIT 1);

-- 3. Insertar la plantilla de Asignacion de Responsable
INSERT INTO tbl_doc_plantillas (
    id_tipo, nombre, descripcion, codigo_sugerido,
    aplica_7, aplica_21, aplica_60,
    activo, orden, created_at
)
SELECT
    @id_tipo_acta,
    'Asignacion de Responsable del SG-SST',
    'Acta de asignacion del responsable del diseno e implementacion del Sistema de Gestion de Seguridad y Salud en el Trabajo (Estandar 1.1.1)',
    'ASG-RES',
    1, 1, 1,  -- Aplica a todos los niveles (7, 21 y 60 estandares)
    1, 1, NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM tbl_doc_plantillas WHERE codigo_sugerido = 'ASG-RES');

-- 4. Mapear la plantilla a la carpeta del estandar 1.1.1
INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
SELECT 'ASG-RES', '1.1.1'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM tbl_doc_plantilla_carpeta
    WHERE codigo_plantilla = 'ASG-RES' AND codigo_carpeta = '1.1.1'
);

-- 5. Verificar la insercion
SELECT 'Plantillas insertadas:' as info;
SELECT p.*, t.nombre as tipo_nombre
FROM tbl_doc_plantillas p
JOIN tbl_doc_tipos t ON t.id_tipo = p.id_tipo
WHERE p.codigo_sugerido = 'ASG-RES';

SELECT 'Mapeo plantilla-carpeta:' as info;
SELECT * FROM tbl_doc_plantilla_carpeta WHERE codigo_plantilla = 'ASG-RES';
