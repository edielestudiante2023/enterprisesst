USE empresas_sst;

DROP PROCEDURE IF EXISTS sp_crear_version_documento;

DELIMITER //

CREATE PROCEDURE sp_crear_version_documento(
    IN p_id_documento INT,
    IN p_tipo_cambio VARCHAR(10),
    IN p_descripcion_cambio TEXT,
    IN p_autorizado_por VARCHAR(255)
)
BEGIN
    DECLARE v_version_actual VARCHAR(10);
    DECLARE v_nueva_version VARCHAR(10);
    DECLARE v_parte_entera INT;
    DECLARE v_parte_decimal INT;

    SELECT version_actual INTO v_version_actual
    FROM tbl_doc_documentos
    WHERE id_documento = p_id_documento;

    SET v_parte_entera = CAST(SUBSTRING_INDEX(v_version_actual, '.', 1) AS UNSIGNED);
    SET v_parte_decimal = CAST(SUBSTRING_INDEX(v_version_actual, '.', -1) AS UNSIGNED);

    IF p_tipo_cambio = 'mayor' THEN
        SET v_nueva_version = CONCAT(v_parte_entera + 1, '.0');
    ELSE
        SET v_nueva_version = CONCAT(v_parte_entera, '.', v_parte_decimal + 1);
    END IF;

    UPDATE tbl_doc_versiones
    SET estado = 'obsoleto'
    WHERE id_documento = p_id_documento;

    INSERT INTO tbl_doc_versiones
    (id_documento, version, tipo_cambio, descripcion_cambio, autorizado_por, estado, contenido_snapshot)
    SELECT
        p_id_documento,
        v_nueva_version,
        p_tipo_cambio,
        p_descripcion_cambio,
        p_autorizado_por,
        'vigente',
        (SELECT JSON_ARRAYAGG(
            JSON_OBJECT(
                'numero_seccion', numero_seccion,
                'nombre_seccion', nombre_seccion,
                'contenido', contenido
            )
        ) FROM tbl_doc_secciones WHERE id_documento = p_id_documento)
    FROM DUAL;

    UPDATE tbl_doc_documentos
    SET version_actual = v_nueva_version,
        updated_at = NOW()
    WHERE id_documento = p_id_documento;

    SELECT v_nueva_version AS nueva_version, LAST_INSERT_ID() AS id_version;
END //

DELIMITER ;
