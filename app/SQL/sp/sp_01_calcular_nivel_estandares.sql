USE empresas_sst;

DROP PROCEDURE IF EXISTS sp_calcular_nivel_estandares;

DELIMITER //

CREATE PROCEDURE sp_calcular_nivel_estandares(
    IN p_total_trabajadores INT,
    IN p_nivel_riesgo VARCHAR(5),
    OUT p_estandares_aplicables INT
)
BEGIN
    IF p_total_trabajadores <= 10 AND p_nivel_riesgo IN ('I', 'II', 'III') THEN
        SET p_estandares_aplicables = 7;
    ELSEIF p_total_trabajadores >= 11 AND p_total_trabajadores <= 50 AND p_nivel_riesgo IN ('I', 'II', 'III') THEN
        SET p_estandares_aplicables = 21;
    ELSE
        SET p_estandares_aplicables = 60;
    END IF;
END //

DELIMITER ;
