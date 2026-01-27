-- =====================================================
-- EJECUTAR EN phpMyAdmin o MySQL CLI
-- =====================================================

USE empresas_sst;

-- 1. Agregar campo codigo a la tabla (si no existe)
ALTER TABLE tbl_documentos_sst
ADD COLUMN IF NOT EXISTS codigo VARCHAR(50) NULL COMMENT 'Codigo del documento (PRG-CAP-001)' AFTER titulo;

-- 2. Crear indice (ignorar error si ya existe)
-- ALTER TABLE tbl_documentos_sst ADD INDEX idx_codigo (codigo);

-- 3. Actualizar documentos existentes con codigo
UPDATE tbl_documentos_sst
SET codigo = CONCAT('PRG-CAP-', LPAD(id_documento, 3, '0'))
WHERE tipo_documento = 'programa_capacitacion'
AND (codigo IS NULL OR codigo = '');

-- 4. Crear/actualizar el Stored Procedure
DROP PROCEDURE IF EXISTS sp_generar_codigo_documento;

DELIMITER //
CREATE PROCEDURE sp_generar_codigo_documento(
    IN p_id_cliente INT,
    IN p_codigo_tipo VARCHAR(10),
    IN p_codigo_tema VARCHAR(10),
    OUT p_codigo_generado VARCHAR(50)
)
BEGIN
    DECLARE v_consecutivo INT;
    DECLARE v_prefijo VARCHAR(20);

    SET v_prefijo = CONCAT(p_codigo_tipo, '-', p_codigo_tema, '-');

    SELECT COALESCE(MAX(
        CAST(SUBSTRING_INDEX(codigo, '-', -1) AS UNSIGNED)
    ), 0) + 1
    INTO v_consecutivo
    FROM tbl_documentos_sst
    WHERE id_cliente = p_id_cliente
    AND codigo LIKE CONCAT(v_prefijo, '%');

    SET p_codigo_generado = CONCAT(v_prefijo, LPAD(v_consecutivo, 3, '0'));
END //
DELIMITER ;

-- 5. Verificar
SELECT id_documento, id_cliente, codigo, tipo_documento, titulo FROM tbl_documentos_sst;

-- Probar el SP
CALL sp_generar_codigo_documento(11, 'PRG', 'CAP', @codigo);
SELECT @codigo as nuevo_codigo;
