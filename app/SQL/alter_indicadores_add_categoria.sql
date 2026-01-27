-- =====================================================
-- Agregar campo categoria a tbl_indicadores_sst
-- Para organizar indicadores por programa/área
-- =====================================================

ALTER TABLE `tbl_indicadores_sst`
ADD COLUMN `categoria` VARCHAR(50) DEFAULT 'capacitacion'
AFTER `tipo_indicador`,
ADD INDEX `idx_categoria` (`categoria`);

-- Actualizar indicadores existentes a categoria 'capacitacion'
UPDATE `tbl_indicadores_sst` SET `categoria` = 'capacitacion' WHERE `categoria` IS NULL OR `categoria` = '';
