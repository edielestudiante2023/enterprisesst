USE empresas_sst;

DROP PROCEDURE IF EXISTS sp_detectar_cambio_nivel;

DELIMITER //

CREATE PROCEDURE sp_detectar_cambio_nivel(
    IN p_id_cliente INT,
    IN p_nuevo_total_trabajadores INT,
    IN p_nuevo_nivel_riesgo VARCHAR(5)
)
BEGIN
    DECLARE v_nivel_anterior INT;
    DECLARE v_nivel_nuevo INT;
    DECLARE v_total_anterior INT;
    DECLARE v_riesgo_anterior VARCHAR(5);

    SELECT estandares_aplicables, total_trabajadores, nivel_riesgo_arl
    INTO v_nivel_anterior, v_total_anterior, v_riesgo_anterior
    FROM tbl_cliente_contexto_sst
    WHERE id_cliente = p_id_cliente;

    CALL sp_calcular_nivel_estandares(p_nuevo_total_trabajadores, p_nuevo_nivel_riesgo, v_nivel_nuevo);

    IF v_nivel_anterior IS NOT NULL AND v_nivel_anterior != v_nivel_nuevo THEN
        INSERT INTO tbl_cliente_transiciones
        (id_cliente, nivel_anterior, nivel_nuevo, motivo, estado)
        VALUES (
            p_id_cliente,
            v_nivel_anterior,
            v_nivel_nuevo,
            CONCAT('Cambio de ', v_total_anterior, ' a ', p_nuevo_total_trabajadores, ' trabajadores. Riesgo: ', v_riesgo_anterior, ' -> ', p_nuevo_nivel_riesgo),
            'detectado'
        );

        SELECT
            'CAMBIO_DETECTADO' AS alerta,
            v_nivel_anterior AS nivel_anterior,
            v_nivel_nuevo AS nivel_nuevo,
            (v_nivel_nuevo - v_nivel_anterior) AS estandares_nuevos,
            LAST_INSERT_ID() AS id_transicion;
    ELSE
        SELECT 'SIN_CAMBIO' AS alerta, v_nivel_anterior AS nivel_actual;
    END IF;
END //

DELIMITER ;
