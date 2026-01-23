USE empresas_sst;

DROP FUNCTION IF EXISTS fn_get_carpetas_json;

DELIMITER //

CREATE FUNCTION fn_get_carpetas_json(p_id_cliente INT)
RETURNS JSON
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE v_resultado JSON;

    SELECT JSON_ARRAYAGG(
        JSON_OBJECT(
            'id', id_carpeta,
            'nombre', nombre,
            'codigo', codigo,
            'tipo', tipo,
            'icono', icono,
            'color', color
        )
    ) INTO v_resultado
    FROM tbl_doc_carpetas
    WHERE id_cliente = p_id_cliente
    AND id_carpeta_padre IS NULL;

    RETURN v_resultado;
END //

DELIMITER ;
