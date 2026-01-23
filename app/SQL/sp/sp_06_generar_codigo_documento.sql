USE empresas_sst;

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

    SELECT COALESCE(MAX(
        CAST(SUBSTRING_INDEX(codigo, '-', -1) AS UNSIGNED)
    ), 0) + 1
    INTO v_consecutivo
    FROM tbl_doc_documentos
    WHERE id_cliente = p_id_cliente
    AND codigo LIKE CONCAT(p_codigo_tipo, '-', p_codigo_tema, '-%');

    SET p_codigo_generado = CONCAT(p_codigo_tipo, '-', p_codigo_tema, '-', LPAD(v_consecutivo, 3, '0'));
END //

DELIMITER ;
