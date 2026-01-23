USE empresas_sst;

DROP PROCEDURE IF EXISTS sp_calcular_cumplimiento;

DELIMITER //

CREATE PROCEDURE sp_calcular_cumplimiento(
    IN p_id_cliente INT
)
BEGIN
    SELECT
        ce.estado,
        COUNT(*) AS cantidad,
        SUM(em.peso_porcentual) AS peso_total,
        ROUND(SUM(em.peso_porcentual) / (SELECT SUM(peso_porcentual) FROM tbl_estandares_minimos WHERE aplica_60 = 1) * 100, 2) AS porcentaje
    FROM tbl_cliente_estandares ce
    JOIN tbl_estandares_minimos em ON ce.id_estandar = em.id_estandar
    WHERE ce.id_cliente = p_id_cliente
    AND ce.estado != 'no_aplica'
    GROUP BY ce.estado

    UNION ALL

    SELECT
        'TOTAL' AS estado,
        COUNT(*) AS cantidad,
        SUM(CASE WHEN ce.estado = 'cumple' THEN em.peso_porcentual ELSE 0 END) AS peso_total,
        ROUND(SUM(CASE WHEN ce.estado = 'cumple' THEN em.peso_porcentual ELSE 0 END), 2) AS porcentaje
    FROM tbl_cliente_estandares ce
    JOIN tbl_estandares_minimos em ON ce.id_estandar = em.id_estandar
    WHERE ce.id_cliente = p_id_cliente
    AND ce.estado != 'no_aplica';
END //

DELIMITER ;
