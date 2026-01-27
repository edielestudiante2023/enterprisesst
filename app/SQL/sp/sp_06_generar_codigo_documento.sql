USE empresas_sst;

DROP PROCEDURE IF EXISTS sp_generar_codigo_documento;

DELIMITER //

-- ============================================================================
-- SP: sp_generar_codigo_documento
-- Descripcion: Genera codigo unico para documentos SST
-- Formato: TIPO-TEMA-XXX (ej: PRG-CAP-001)
--
-- Parametros:
--   p_id_cliente: ID del cliente
--   p_codigo_tipo: Tipo de documento (PRG, POL, PLA, PRO, etc.)
--   p_codigo_tema: Tema del documento (CAP, EMO, EME, etc.)
--   p_codigo_generado: Variable OUT con el codigo generado
--
-- Uso:
--   CALL sp_generar_codigo_documento(11, 'PRG', 'CAP', @codigo);
--   SELECT @codigo; -- Retorna: PRG-CAP-001
-- ============================================================================
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

    -- Buscar en tbl_documentos_sst (tabla principal de documentos SST)
    SELECT COALESCE(MAX(
        CAST(SUBSTRING_INDEX(codigo, '-', -1) AS UNSIGNED)
    ), 0) + 1
    INTO v_consecutivo
    FROM tbl_documentos_sst
    WHERE id_cliente = p_id_cliente
    AND codigo LIKE CONCAT(v_prefijo, '%');

    -- Si no hay registros en tbl_documentos_sst, buscar en tbl_doc_documentos (legacy)
    IF v_consecutivo = 1 THEN
        SELECT COALESCE(MAX(
            CAST(SUBSTRING_INDEX(codigo, '-', -1) AS UNSIGNED)
        ), 0) + 1
        INTO v_consecutivo
        FROM tbl_doc_documentos
        WHERE id_cliente = p_id_cliente
        AND codigo LIKE CONCAT(v_prefijo, '%');
    END IF;

    SET p_codigo_generado = CONCAT(v_prefijo, LPAD(v_consecutivo, 3, '0'));
END //

DELIMITER ;
