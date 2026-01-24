-- ============================================================================
-- UPDATE: Configurar niveles de plantillas según Resolución 0312 de 2019
-- Basado en investigación detallada de GPT sobre estándares mínimos SST
-- Fecha: Enero 2026
-- ============================================================================
--
-- NIVELES según Res. 0312/2019:
-- - Nivel 7:  Microempresas (<=10 trabajadores) Riesgo I, II, III
-- - Nivel 21: Empresas 11-50 trabajadores o <=10 con Riesgo IV, V
-- - Nivel 60: Empresas >50 trabajadores (todos los estándares)
--
-- REGLA BASE: Por defecto todo aplica a nivel 60. Se va desactivando según nivel.
-- ============================================================================

-- Primero, asegurarse que las columnas existen
-- (Si ya ejecutaste alter_plantillas_nivel.sql, esto no es necesario)

-- ============================================================================
-- PASO 1: Resetear todo a aplica en todos los niveles
-- ============================================================================
UPDATE `tbl_doc_plantillas` SET
    `aplica_7` = 1,
    `aplica_21` = 1,
    `aplica_60` = 1;

-- ============================================================================
-- PASO 2: Configurar qué NO aplica para NIVEL 7 (Microempresas)
-- Según Res. 0312/2019, estándares mínimos simplificados
-- ============================================================================

-- COPASST: No aplica, tienen Vigía SST (< 10 trabajadores)
UPDATE `tbl_doc_plantillas` SET `aplica_7` = 0
WHERE `codigo_sugerido` IN ('REG-COP', 'ACT-COP');

-- Comité Convivencia: No obligatorio para < 10 trabajadores (Res 652/2012 modificada)
UPDATE `tbl_doc_plantillas` SET `aplica_7` = 0
WHERE `codigo_sugerido` IN ('REG-CCL', 'ACT-CCL');

-- Auditoría Interna: No obligatoria en nivel 7 (se usa autoevaluación)
UPDATE `tbl_doc_plantillas` SET `aplica_7` = 0
WHERE `codigo_sugerido` IN ('PRO-AUD', 'INF-AUD');

-- Procedimientos complejos: Simplificados o no requeridos en nivel 7
UPDATE `tbl_doc_plantillas` SET `aplica_7` = 0
WHERE `codigo_sugerido` IN (
    'PRO-ACP',      -- Acciones Correctivas (simplificado en plan de mejora básico)
    'PRO-COM',      -- Comunicaciones (simplificado)
    'PRO-DOC'       -- Control Documental (simplificado, un solo listado)
);

-- Manual completo del SG-SST: No obligatorio, se acepta cumplimiento directo de requisitos
UPDATE `tbl_doc_plantillas` SET `aplica_7` = 0
WHERE `codigo_sugerido` = 'MAN-SST';

-- Manual de Funciones completo: Simplificado (carta de designación y funciones básicas)
UPDATE `tbl_doc_plantillas` SET `aplica_7` = 0
WHERE `codigo_sugerido` = 'MAN-FUN';

-- Manual de Contratistas: Solo si tiene contratistas (regla condicional)
UPDATE `tbl_doc_plantillas` SET `aplica_7` = 0
WHERE `codigo_sugerido` = 'MAN-CON';

-- Informe de Revisión por Dirección: Simplificado (acta simple de seguimiento)
UPDATE `tbl_doc_plantillas` SET `aplica_7` = 0
WHERE `codigo_sugerido` = 'INF-RAD';

-- Matriz Legal completa: Simplificada para microempresas
UPDATE `tbl_doc_plantillas` SET `aplica_7` = 0
WHERE `codigo_sugerido` = 'MTZ-LEG';

-- Plan de Capacitación formal: Se acepta cronograma básico integrado al PTA
UPDATE `tbl_doc_plantillas` SET `aplica_7` = 0
WHERE `codigo_sugerido` = 'PLA-CAP';

-- Programa de Mantenimiento: Simplificado (registro básico)
UPDATE `tbl_doc_plantillas` SET `aplica_7` = 0
WHERE `codigo_sugerido` = 'PRG-MTO';

-- Programa de Riesgo Psicosocial formal: Solo si aplica por diagnóstico
-- (En microempresas generalmente no se exige programa formal)
UPDATE `tbl_doc_plantillas` SET `aplica_7` = 0
WHERE `codigo_sugerido` = 'PRG-PSI';

-- Acta de Brigada formal: Simplificado para microempresas
UPDATE `tbl_doc_plantillas` SET `aplica_7` = 0
WHERE `codigo_sugerido` = 'ACT-BRI';

-- ============================================================================
-- PASO 3: Configurar qué NO aplica para NIVEL 21 (Empresas medianas)
-- Tienen más requisitos que nivel 7, pero no todos los de nivel 60
-- ============================================================================

-- Auditoría Interna: Obligatoria anualmente pero puede ser simplificada
-- Dejamos activa pero el informe extenso puede ser opcional
UPDATE `tbl_doc_plantillas` SET `aplica_21` = 0
WHERE `codigo_sugerido` IN (
    'INF-AUD'       -- Informe extenso, puede ser simplificado
);

-- Procedimiento Control Documental extenso: Puede ser simplificado
UPDATE `tbl_doc_plantillas` SET `aplica_21` = 0
WHERE `codigo_sugerido` = 'PRO-DOC';

-- Manual de Contratistas: Solo si aplica (tiene contratistas frecuentes)
UPDATE `tbl_doc_plantillas` SET `aplica_21` = 0
WHERE `codigo_sugerido` = 'MAN-CON';

-- ============================================================================
-- PASO 4: NIVEL 60 - Todos los documentos aplican (ya están en 1)
-- Empresas >50 trabajadores deben cumplir todos los 60 estándares
-- ============================================================================

-- No se necesitan actualizaciones, ya quedó en 1 desde el inicio

-- ============================================================================
-- VERIFICACIÓN: Ver resultado de la configuración
-- ============================================================================
SELECT
    p.codigo_sugerido,
    p.nombre,
    t.nombre as tipo,
    p.aplica_7 as 'Nivel 7',
    p.aplica_21 as 'Nivel 21',
    p.aplica_60 as 'Nivel 60'
FROM tbl_doc_plantillas p
JOIN tbl_doc_tipos t ON t.id_tipo = p.id_tipo
WHERE p.activo = 1
ORDER BY
    p.aplica_7 ASC,
    p.aplica_21 ASC,
    t.nombre,
    p.orden;

-- ============================================================================
-- RESUMEN ESPERADO por nivel:
-- ============================================================================
--
-- NIVEL 7 (Microempresas): Documentos esenciales únicamente
--   - Política SST (POL-SST) ✓
--   - Objetivos SST (OBJ-SST) ✓
--   - Plan de Trabajo Anual (PLA-TRA) ✓
--   - Matriz IPEVR (MTZ-IPE) ✓
--   - Plan de Emergencias (PLA-EME) ✓
--   - Procedimiento Investigación AT (PRO-IAT) ✓
--   - Reglamento HSI (REG-HSI) ✓
--   - Programa Capacitación básico ✓
--   - Programa Inducción/Reinducción ✓
--   - Formatos básicos ✓
--
-- NIVEL 21 (Empresas medianas): Lo anterior +
--   - COPASST (REG-COP, ACT-COP) ✓
--   - Comité Convivencia (REG-CCL, ACT-CCL) ✓
--   - Procedimiento Auditoría (PRO-AUD) ✓
--   - Brigada de Emergencias (ACT-BRI) ✓
--   - Plan Capacitación formal (PLA-CAP) ✓
--   - Programa Psicosocial (PRG-PSI) ✓
--   - Más procedimientos
--
-- NIVEL 60 (Empresas grandes): TODOS los documentos
--   - Todo lo anterior +
--   - Manual SG-SST completo ✓
--   - Informe Auditoría (INF-AUD) ✓
--   - Control Documental (PRO-DOC) ✓
--   - Manual Contratistas (MAN-CON) ✓
--   - Todos los procedimientos, programas, formatos
--
-- ============================================================================
