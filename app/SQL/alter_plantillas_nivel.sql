-- ============================================================================
-- ALTER: Agregar campos de nivel a plantillas de documentos
-- Permite filtrar qué documentos aplican según el nivel del cliente (7, 21, 60)
-- Fecha: Enero 2026
-- ============================================================================

-- Agregar columnas de nivel a plantillas
ALTER TABLE `tbl_doc_plantillas`
ADD COLUMN `aplica_7` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Aplica para empresas con 7 estándares' AFTER `orden`,
ADD COLUMN `aplica_21` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Aplica para empresas con 21 estándares' AFTER `aplica_7`,
ADD COLUMN `aplica_60` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Aplica para empresas con 60 estándares' AFTER `aplica_21`;

-- ============================================================================
-- Configurar qué documentos aplican a cada nivel según Res. 0312/2019
-- ============================================================================

-- NIVEL 7 (Microempresas riesgo I, II, III con 10 o menos trabajadores)
-- Documentos mínimos requeridos:
-- - Política SST
-- - Objetivos SST (pueden ser básicos)
-- - Responsable SST (designación)
-- - Afiliaciones al SGSS
-- - Identificación de peligros básica
-- - Capacitación básica

-- Por defecto todas aplican. Desactivamos las que NO aplican para nivel 7:

-- Documentos que NO aplican para nivel 7 (microempresas):
UPDATE `tbl_doc_plantillas` SET `aplica_7` = 0 WHERE `codigo_sugerido` IN (
    'REG-COP',      -- Reglamento COPASST (no tienen, tienen vigía)
    'REG-CCL',      -- Reglamento Comité Convivencia (no obligatorio < 10)
    'PRG-PSI',      -- Programa Riesgo Psicosocial (simplificado)
    'PRG-MTO',      -- Programa Mantenimiento (simplificado)
    'PRO-AUD',      -- Procedimiento Auditorías (no obligatorio)
    'PRO-ACP',      -- Procedimiento Acciones Correctivas (simplificado)
    'PRO-COM',      -- Procedimiento Comunicaciones (simplificado)
    'PRO-DOC',      -- Procedimiento Control Documental (simplificado)
    'PLA-CAP',      -- Plan Capacitación (básico, no requiere documento formal)
    'MAN-SST',      -- Manual SG-SST (no obligatorio, solo requisitos básicos)
    'MAN-FUN',      -- Manual Funciones (simplificado)
    'MAN-CON',      -- Manual Contratistas (si no hay contratistas)
    'INF-RAD',      -- Informe Revisión Alta Dirección (anual simplificado)
    'INF-AUD',      -- Informe Auditoría (no obligatorio)
    'MTZ-LEG',      -- Matriz Requisitos Legales (simplificada)
    'ACT-COP',      -- Acta COPASST (no tienen COPASST)
    'ACT-CCL',      -- Acta Comité Convivencia (no obligatorio)
    'ACT-BRI'       -- Acta Brigada (simplificado)
);

-- NIVEL 21 (Empresas con 11-50 trabajadores o riesgo IV-V con 10 o menos)
-- Aplican más documentos pero no todos los 60

UPDATE `tbl_doc_plantillas` SET `aplica_21` = 0 WHERE `codigo_sugerido` IN (
    'PRO-AUD',      -- Procedimiento Auditorías (simplificado)
    'PRO-DOC',      -- Procedimiento Control Documental (simplificado)
    'INF-AUD',      -- Informe Auditoría (anual)
    'MAN-CON'       -- Manual Contratistas (si aplica)
);

-- NIVEL 60 (Empresas con más de 50 trabajadores)
-- Aplican TODOS los documentos (ya están en 1 por defecto)

-- ============================================================================
-- Verificación
-- ============================================================================
SELECT
    codigo_sugerido,
    nombre,
    aplica_7,
    aplica_21,
    aplica_60
FROM tbl_doc_plantillas
ORDER BY aplica_7 DESC, aplica_21 DESC, codigo_sugerido;
