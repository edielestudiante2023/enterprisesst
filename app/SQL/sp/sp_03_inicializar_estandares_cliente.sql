USE empresas_sst;

DROP PROCEDURE IF EXISTS sp_inicializar_estandares_cliente;

DELIMITER //

CREATE PROCEDURE sp_inicializar_estandares_cliente(
    IN p_id_cliente INT
)
BEGIN
    DECLARE v_estandares_aplicables INT;
    DECLARE v_total_trabajadores INT;
    DECLARE v_nivel_riesgo VARCHAR(5);

    SELECT total_trabajadores, nivel_riesgo_arl, estandares_aplicables
    INTO v_total_trabajadores, v_nivel_riesgo, v_estandares_aplicables
    FROM tbl_cliente_contexto_sst
    WHERE id_cliente = p_id_cliente;

    IF v_estandares_aplicables IS NULL THEN
        SET v_estandares_aplicables = 60;
    END IF;

    DELETE FROM tbl_cliente_estandares WHERE id_cliente = p_id_cliente;

    IF v_estandares_aplicables = 7 THEN
        INSERT INTO tbl_cliente_estandares (id_cliente, id_estandar, estado)
        SELECT p_id_cliente, id_estandar,
               CASE WHEN aplica_7 = 1 THEN 'pendiente' ELSE 'no_aplica' END
        FROM tbl_estandares_minimos;
    ELSEIF v_estandares_aplicables = 21 THEN
        INSERT INTO tbl_cliente_estandares (id_cliente, id_estandar, estado)
        SELECT p_id_cliente, id_estandar,
               CASE WHEN aplica_21 = 1 THEN 'pendiente' ELSE 'no_aplica' END
        FROM tbl_estandares_minimos;
    ELSE
        INSERT INTO tbl_cliente_estandares (id_cliente, id_estandar, estado)
        SELECT p_id_cliente, id_estandar, 'pendiente'
        FROM tbl_estandares_minimos;
    END IF;

    SELECT
        v_estandares_aplicables AS nivel_estandares,
        COUNT(*) AS total_registros,
        SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) AS pendientes,
        SUM(CASE WHEN estado = 'no_aplica' THEN 1 ELSE 0 END) AS no_aplica
    FROM tbl_cliente_estandares
    WHERE id_cliente = p_id_cliente;
END //

DELIMITER ;
