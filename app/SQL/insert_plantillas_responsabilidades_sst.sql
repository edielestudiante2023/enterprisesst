-- =====================================================
-- PLANTILLAS PARA DOCUMENTOS 1.1.2 RESPONSABILIDADES SG-SST
-- Resolucion 0312/2019 - Estandar 1.1.2
--
-- Se crean 4 documentos separados:
-- 1. Responsabilidades del Representante Legal (firma digital)
-- 2. Responsabilidades del Responsable SG-SST (firma consultor)
-- 3. Responsabilidades de Trabajadores (formato imprimible)
-- 4. Responsabilidades del Vigia SST (solo 7 estandares, firma digital)
-- =====================================================

-- Verificar que existe la carpeta padre "Planear" o crearla si no existe
INSERT INTO tbl_doc_carpetas (nombre, descripcion, orden, padre_id, activo, created_at)
SELECT 'Planear', 'Documentos de la fase PLANEAR del ciclo PHVA', 1, NULL, 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM tbl_doc_carpetas WHERE nombre = 'Planear');

-- Obtener el ID de la carpeta Planear
SET @id_carpeta_planear = (SELECT id_carpeta FROM tbl_doc_carpetas WHERE nombre = 'Planear' LIMIT 1);

-- Verificar que existe la subcarpeta "Recursos" o crearla si no existe
INSERT INTO tbl_doc_carpetas (nombre, descripcion, orden, padre_id, activo, created_at)
SELECT 'Recursos', 'Gestion de recursos del SG-SST', 1, @id_carpeta_planear, 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM tbl_doc_carpetas WHERE nombre = 'Recursos' AND padre_id = @id_carpeta_planear);

SET @id_carpeta_recursos = (SELECT id_carpeta FROM tbl_doc_carpetas WHERE nombre = 'Recursos' AND padre_id = @id_carpeta_planear LIMIT 1);

-- =====================================================
-- 1. PLANTILLA: Responsabilidades del Representante Legal
-- =====================================================
INSERT INTO tbl_doc_plantillas_sst (
    tipo_documento,
    nombre,
    descripcion,
    id_carpeta,
    estandares_aplicables,
    patron_generacion,
    requiere_firma_digital,
    firmante_tipo,
    orden,
    activo,
    created_at
) VALUES (
    'responsabilidades_rep_legal_sgsst',
    'Responsabilidades del Representante Legal en el SG-SST',
    'Documento que establece las responsabilidades del Representante Legal segun Decreto 1072/2015 Art. 2.2.4.6.8. Requiere firma digital del Rep. Legal.',
    @id_carpeta_recursos,
    '7,21,60',
    'B',
    1,
    'representante_legal',
    2,
    1,
    NOW()
) ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    descripcion = VALUES(descripcion),
    updated_at = NOW();

-- =====================================================
-- 2. PLANTILLA: Responsabilidades del Responsable SG-SST
-- =====================================================
INSERT INTO tbl_doc_plantillas_sst (
    tipo_documento,
    nombre,
    descripcion,
    id_carpeta,
    estandares_aplicables,
    patron_generacion,
    requiere_firma_digital,
    firmante_tipo,
    orden,
    activo,
    created_at
) VALUES (
    'responsabilidades_responsable_sgsst',
    'Responsabilidades del Responsable del SG-SST',
    'Documento que establece las responsabilidades del profesional responsable del SG-SST. Usa firma automatica del consultor desde su perfil.',
    @id_carpeta_recursos,
    '7,21,60',
    'B',
    0,
    'consultor_sst',
    3,
    1,
    NOW()
) ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    descripcion = VALUES(descripcion),
    updated_at = NOW();

-- =====================================================
-- 3. PLANTILLA: Responsabilidades de Trabajadores
-- =====================================================
INSERT INTO tbl_doc_plantillas_sst (
    tipo_documento,
    nombre,
    descripcion,
    id_carpeta,
    estandares_aplicables,
    patron_generacion,
    requiere_firma_digital,
    firmante_tipo,
    orden,
    activo,
    created_at
) VALUES (
    'responsabilidades_trabajadores_sgsst',
    'Responsabilidades de Trabajadores y Contratistas en el SG-SST',
    'Documento con responsabilidades de trabajadores segun Decreto 1072/2015 Art. 2.2.4.6.10. FORMATO IMPRIMIBLE con hoja de firmas para induccion.',
    @id_carpeta_recursos,
    '7,21,60',
    'B',
    0,
    'firma_fisica',
    4,
    1,
    NOW()
) ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    descripcion = VALUES(descripcion),
    updated_at = NOW();

-- =====================================================
-- 4. PLANTILLA: Responsabilidades del Vigia SST
-- IMPORTANTE: Solo aplica para 7 estandares
-- =====================================================
INSERT INTO tbl_doc_plantillas_sst (
    tipo_documento,
    nombre,
    descripcion,
    id_carpeta,
    estandares_aplicables,
    patron_generacion,
    requiere_firma_digital,
    firmante_tipo,
    orden,
    activo,
    created_at
) VALUES (
    'responsabilidades_vigia_sgsst',
    'Responsabilidades del Vigia de Seguridad y Salud en el Trabajo',
    'Documento exclusivo para empresas con 7 estandares (<10 trabajadores, Riesgo I-III). Establece responsabilidades del Vigia SST. Requiere firma digital del Vigia.',
    @id_carpeta_recursos,
    '7',
    'B',
    1,
    'vigia_sst',
    5,
    1,
    NOW()
) ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    descripcion = VALUES(descripcion),
    estandares_aplicables = VALUES(estandares_aplicables),
    updated_at = NOW();

-- =====================================================
-- VERIFICACION
-- =====================================================
SELECT
    tipo_documento,
    nombre,
    estandares_aplicables,
    CASE requiere_firma_digital WHEN 1 THEN 'Digital' ELSE 'Fisica/Auto' END AS tipo_firma,
    firmante_tipo
FROM tbl_doc_plantillas_sst
WHERE tipo_documento LIKE 'responsabilidades_%'
ORDER BY orden;
