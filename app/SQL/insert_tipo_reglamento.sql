-- ============================================================================
-- INSERT: Agregar tipo de documento Reglamento
-- Tabla: tbl_doc_tipos
-- Fecha: Enero 2026
-- ============================================================================

-- Verificar si ya existe antes de insertar
INSERT INTO `tbl_doc_tipos` (`codigo`, `nombre`, `descripcion`, `tiene_secciones`, `numero_secciones`, `estructura_secciones`, `requiere_firma_cliente`)
SELECT 'REG', 'Reglamento', 'Reglamento interno de la empresa', 1, 6,
       '["Objeto","Alcance","Definiciones","Disposiciones generales","Obligaciones y prohibiciones","Sanciones y vigencia"]', 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `tbl_doc_tipos` WHERE `codigo` = 'REG');

-- Si prefieres un INSERT directo (asegúrate de que no exista):
-- INSERT INTO `tbl_doc_tipos` (`codigo`, `nombre`, `descripcion`, `tiene_secciones`, `numero_secciones`, `estructura_secciones`, `requiere_firma_cliente`) VALUES
-- ('REG', 'Reglamento', 'Reglamento interno de la empresa', 1, 6, '["Objeto","Alcance","Definiciones","Disposiciones generales","Obligaciones y prohibiciones","Sanciones y vigencia"]', 1);
