USE empresas_sst;

DROP PROCEDURE IF EXISTS sp_generar_carpetas_cliente;

DELIMITER //

CREATE PROCEDURE sp_generar_carpetas_cliente(
    IN p_id_cliente INT,
    IN p_anio INT
)
BEGIN
    DECLARE v_id_raiz INT;
    DECLARE v_id_planear INT;
    DECLARE v_id_hacer INT;
    DECLARE v_id_verificar INT;
    DECLARE v_id_actuar INT;
    DECLARE v_id_recursos INT;
    DECLARE v_id_gestion_integral INT;
    DECLARE v_id_gestion_salud INT;
    DECLARE v_id_gestion_peligros INT;
    DECLARE v_id_gestion_amenazas INT;
    DECLARE v_id_verificacion INT;
    DECLARE v_id_mejoramiento INT;

    IF NOT EXISTS (SELECT 1 FROM tbl_clientes WHERE id_cliente = p_id_cliente) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cliente no existe';
    END IF;

    DELETE FROM tbl_doc_carpetas
    WHERE id_cliente = p_id_cliente
    AND nombre LIKE CONCAT('SG-SST ', p_anio, '%');

    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, NULL, CONCAT('SG-SST ', p_anio), NULL, 1, 'phva', 'folder-root');
    SET v_id_raiz = LAST_INSERT_ID();

    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono, color)
    VALUES (p_id_cliente, v_id_raiz, '1. PLANEAR', '1', 1, 'phva', 'clipboard-list', '#3B82F6');
    SET v_id_planear = LAST_INSERT_ID();

    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono, color)
    VALUES (p_id_cliente, v_id_raiz, '2. HACER', '2', 2, 'phva', 'play-circle', '#10B981');
    SET v_id_hacer = LAST_INSERT_ID();

    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono, color)
    VALUES (p_id_cliente, v_id_raiz, '3. VERIFICAR', '3', 3, 'phva', 'check-circle', '#F59E0B');
    SET v_id_verificar = LAST_INSERT_ID();

    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono, color)
    VALUES (p_id_cliente, v_id_raiz, '4. ACTUAR', '4', 4, 'phva', 'refresh', '#EF4444');
    SET v_id_actuar = LAST_INSERT_ID();

    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_planear, '1.1 Recursos', '1.1', 1, 'categoria', 'users');
    SET v_id_recursos = LAST_INSERT_ID();

    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_planear, '1.2 Gestión Integral del SG-SST', '1.2', 2, 'categoria', 'settings');
    SET v_id_gestion_integral = LAST_INSERT_ID();

    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_hacer, '2.1 Gestión de la Salud', '2.1', 1, 'categoria', 'heart');
    SET v_id_gestion_salud = LAST_INSERT_ID();

    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_hacer, '2.2 Gestión de Peligros y Riesgos', '2.2', 2, 'categoria', 'alert-triangle');
    SET v_id_gestion_peligros = LAST_INSERT_ID();

    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_hacer, '2.3 Gestión de Amenazas', '2.3', 3, 'categoria', 'shield');
    SET v_id_gestion_amenazas = LAST_INSERT_ID();

    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_verificar, '3.1 Verificación del SG-SST', '3.1', 1, 'categoria', 'search');
    SET v_id_verificacion = LAST_INSERT_ID();

    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_actuar, '4.1 Mejoramiento', '4.1', 1, 'categoria', 'trending-up');
    SET v_id_mejoramiento = LAST_INSERT_ID();

    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, id_estandar)
    SELECT p_id_cliente, v_id_recursos, CONCAT(item, ' ', SUBSTRING(nombre, 1, 50)),
           item, CAST(SUBSTRING_INDEX(item, '.', -1) AS UNSIGNED), 'estandar', id_estandar
    FROM tbl_estandares_minimos WHERE categoria = 'I' ORDER BY item;

    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, id_estandar)
    SELECT p_id_cliente, v_id_gestion_integral, CONCAT(item, ' ', SUBSTRING(nombre, 1, 50)),
           item, CAST(SUBSTRING_INDEX(item, '.', -1) AS UNSIGNED), 'estandar', id_estandar
    FROM tbl_estandares_minimos WHERE categoria = 'II' ORDER BY item;

    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, id_estandar)
    SELECT p_id_cliente, v_id_gestion_salud, CONCAT(item, ' ', SUBSTRING(nombre, 1, 50)),
           item, CAST(SUBSTRING_INDEX(item, '.', -1) AS UNSIGNED), 'estandar', id_estandar
    FROM tbl_estandares_minimos WHERE categoria = 'III' ORDER BY item;

    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, id_estandar)
    SELECT p_id_cliente, v_id_gestion_peligros, CONCAT(item, ' ', SUBSTRING(nombre, 1, 50)),
           item, CAST(SUBSTRING_INDEX(item, '.', -1) AS UNSIGNED), 'estandar', id_estandar
    FROM tbl_estandares_minimos WHERE categoria = 'IV' ORDER BY item;

    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, id_estandar)
    SELECT p_id_cliente, v_id_gestion_amenazas, CONCAT(item, ' ', SUBSTRING(nombre, 1, 50)),
           item, CAST(SUBSTRING_INDEX(item, '.', -1) AS UNSIGNED), 'estandar', id_estandar
    FROM tbl_estandares_minimos WHERE categoria = 'V' ORDER BY item;

    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, id_estandar)
    SELECT p_id_cliente, v_id_verificacion, CONCAT(item, ' ', SUBSTRING(nombre, 1, 50)),
           item, CAST(SUBSTRING_INDEX(item, '.', -1) AS UNSIGNED), 'estandar', id_estandar
    FROM tbl_estandares_minimos WHERE categoria = 'VI' ORDER BY item;

    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, id_estandar)
    SELECT p_id_cliente, v_id_mejoramiento, CONCAT(item, ' ', SUBSTRING(nombre, 1, 50)),
           item, CAST(SUBSTRING_INDEX(item, '.', -1) AS UNSIGNED), 'estandar', id_estandar
    FROM tbl_estandares_minimos WHERE categoria = 'VII' ORDER BY item;

    SELECT v_id_raiz AS id_carpeta_raiz;
END //

DELIMITER ;
