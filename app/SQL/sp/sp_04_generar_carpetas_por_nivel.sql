USE empresas_sst;

-- ============================================================================
-- SP: Generar estructura de carpetas según nivel de estándares del cliente
-- Estructura: SG-SST [Año] / PHVA / Categoría (solo las que aplican)
-- Basado en Resolución 0312 de 2019
-- Fecha: Enero 2026
-- ============================================================================

DROP PROCEDURE IF EXISTS sp_generar_carpetas_por_nivel;

DELIMITER //

CREATE PROCEDURE sp_generar_carpetas_por_nivel(
    IN p_id_cliente INT,
    IN p_anio INT,
    IN p_nivel_estandares INT  -- 7, 21 o 60
)
BEGIN
    DECLARE v_id_raiz INT;
    DECLARE v_id_planear INT;
    DECLARE v_id_hacer INT;
    DECLARE v_id_verificar INT;
    DECLARE v_id_actuar INT;

    -- Validar cliente existe
    IF NOT EXISTS (SELECT 1 FROM tbl_clientes WHERE id_cliente = p_id_cliente) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cliente no existe';
    END IF;

    -- Validar nivel
    IF p_nivel_estandares NOT IN (7, 21, 60) THEN
        SET p_nivel_estandares = 60; -- Default a nivel completo
    END IF;

    -- Eliminar estructura anterior del mismo año si existe
    DELETE FROM tbl_doc_carpetas
    WHERE id_cliente = p_id_cliente
    AND nombre LIKE CONCAT('SG-SST ', p_anio, '%');

    -- ========================================================================
    -- NIVEL 1: Carpeta Raíz
    -- ========================================================================
    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, NULL, CONCAT('SG-SST ', p_anio), NULL, 1, 'raiz', 'folder-root');
    SET v_id_raiz = LAST_INSERT_ID();

    -- ========================================================================
    -- NIVEL 2: Ciclo PHVA (siempre se crean las 4)
    -- ========================================================================
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

    -- ========================================================================
    -- NIVEL 3: Categorías según nivel de estándares
    -- ========================================================================

    -- ========================================================================
    -- PLANEAR - Categorías
    -- ========================================================================

    -- 1.1 Recursos - Aplica a TODOS (7, 21, 60)
    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_planear, '1.1. Recursos', '1.1', 1, 'categoria', 'users');

    -- 1.2 Política y Objetivos SST - Aplica a 21 y 60
    IF p_nivel_estandares >= 21 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_planear, '1.2. Politica y Objetivos SST', '1.2', 2, 'categoria', 'target');
    END IF;

    -- 1.3 Evaluación Inicial - Solo 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_planear, '1.3. Evaluacion Inicial', '1.3', 3, 'categoria', 'clipboard-check');
    END IF;

    -- 1.4 Plan de Trabajo Anual - Aplica a 21 y 60
    IF p_nivel_estandares >= 21 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_planear, '1.4. Plan de Trabajo Anual', '1.4', 4, 'categoria', 'calendar');
    END IF;

    -- 1.5 Identificación de Peligros (IPEVR) - Aplica a 21 y 60
    IF p_nivel_estandares >= 21 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_planear, '1.5. Identificacion de Peligros (IPEVR)', '1.5', 5, 'categoria', 'alert-triangle');
    END IF;

    -- 1.6 Requisitos Legales - Solo 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_planear, '1.6. Requisitos Legales', '1.6', 6, 'categoria', 'book-open');
    END IF;

    -- 1.7 Mecanismos de Comunicación - Solo 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_planear, '1.7. Mecanismos de Comunicacion', '1.7', 7, 'categoria', 'message-circle');
    END IF;

    -- 1.8 Adquisiciones y Contratación - Solo 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_planear, '1.8. Adquisiciones y Contratacion', '1.8', 8, 'categoria', 'shopping-cart');
    END IF;

    -- 1.9 Gestión del Cambio - Solo 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_planear, '1.9. Gestion del Cambio', '1.9', 9, 'categoria', 'refresh-cw');
    END IF;

    -- 1.10 Rendición de Cuentas - Solo 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_planear, '1.10. Rendicion de Cuentas', '1.10', 10, 'categoria', 'bar-chart');
    END IF;

    -- 1.11 Control Documental - Solo 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_planear, '1.11. Control Documental', '1.11', 11, 'categoria', 'folder');
    END IF;

    -- ========================================================================
    -- HACER - Categorías
    -- ========================================================================

    -- 2.1 Condiciones de Salud - Aplica a 21 y 60
    IF p_nivel_estandares >= 21 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '2.1. Condiciones de Salud', '2.1', 1, 'categoria', 'heart');
    END IF;

    -- 2.2 Riesgo Psicosocial - Solo 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '2.2. Riesgo Psicosocial', '2.2', 2, 'categoria', 'brain');
    END IF;

    -- 2.3 Acoso Laboral y Convivencia - Solo 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '2.3. Acoso Laboral y Convivencia', '2.3', 3, 'categoria', 'users');
    END IF;

    -- 2.4 Capacitación SST - Aplica a TODOS (7, 21, 60)
    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_hacer, '2.4. Capacitacion SST', '2.4', 4, 'categoria', 'book');

    -- 2.5 Inducción y Reinducción - Aplica a 21 y 60
    IF p_nivel_estandares >= 21 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '2.5. Induccion y Reinduccion', '2.5', 5, 'categoria', 'user-plus');
    END IF;

    -- 2.6 Medidas de Prevención y Control - Solo 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '2.6. Medidas de Prevencion y Control', '2.6', 6, 'categoria', 'shield');
    END IF;

    -- 2.7 EPP - Aplica a 21 y 60
    IF p_nivel_estandares >= 21 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '2.7. EPP', '2.7', 7, 'categoria', 'hard-hat');
    END IF;

    -- 2.8 Inspecciones de Seguridad - Solo 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '2.8. Inspecciones de Seguridad', '2.8', 8, 'categoria', 'search');
    END IF;

    -- 2.9 Mantenimiento - Solo 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '2.9. Mantenimiento', '2.9', 9, 'categoria', 'tool');
    END IF;

    -- 2.10 Plan de Emergencias - Aplica a 21 y 60
    IF p_nivel_estandares >= 21 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '2.10. Plan de Emergencias', '2.10', 10, 'categoria', 'alert-circle');
    END IF;

    -- 2.11 Brigada de Emergencias - Aplica a TODOS (7, 21, 60)
    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_hacer, '2.11. Brigada de Emergencias', '2.11', 11, 'categoria', 'users');

    -- 2.12 COPASST / Vigía SST - Aplica a 21 y 60
    IF p_nivel_estandares >= 21 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '2.12. COPASST / Vigia SST', '2.12', 12, 'categoria', 'users');
    END IF;

    -- 2.13 Investigación AT/EL - Solo 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '2.13. Investigacion AT/EL', '2.13', 13, 'categoria', 'file-text');
    END IF;

    -- 2.14 Reglamentos - Solo 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '2.14. Reglamentos', '2.14', 14, 'categoria', 'file');
    END IF;

    -- ========================================================================
    -- VERIFICAR - Categorías
    -- ========================================================================

    -- 3.1 Indicadores del SG-SST - Aplica a TODOS (7, 21, 60)
    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_verificar, '3.1. Indicadores del SG-SST', '3.1', 1, 'categoria', 'activity');

    -- 3.2 Auditoría Interna - Solo 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_verificar, '3.2. Auditoria Interna', '3.2', 2, 'categoria', 'clipboard');
    END IF;

    -- 3.3 Revisión por la Alta Dirección - Solo 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_verificar, '3.3. Revision por la Alta Direccion', '3.3', 3, 'categoria', 'eye');
    END IF;

    -- 3.4 Investigación de AT/EL (Informes) - Solo 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_verificar, '3.4. Investigacion de AT/EL (Informes)', '3.4', 4, 'categoria', 'file-text');
    END IF;

    -- 3.5 Cumplimiento de Requisitos Legales - Aplica a TODOS (7, 21, 60) - Ítems 3.1.1 y 3.1.2
    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_verificar, '3.5. Cumplimiento de Requisitos Legales', '3.5', 5, 'categoria', 'check-square');

    -- ========================================================================
    -- ACTUAR - Categorías
    -- ========================================================================

    -- 4.1 Plan de Mejoramiento - Aplica a TODOS (7, 21, 60)
    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_actuar, '4.1. Plan de Mejoramiento', '4.1', 1, 'categoria', 'trending-up');

    -- 4.2 Acciones Correctivas y Preventivas - Aplica a 21 y 60
    IF p_nivel_estandares >= 21 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_actuar, '4.2. Acciones Correctivas y Preventivas', '4.2', 2, 'categoria', 'check-circle');
    END IF;

    -- 4.3 Mejora Continua - Solo 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_actuar, '4.3. Mejora Continua', '4.3', 3, 'categoria', 'refresh-cw');
    END IF;

    -- Retornar ID de carpeta raíz
    SELECT v_id_raiz AS id_carpeta_raiz;

END //

DELIMITER ;

-- ============================================================================
-- Resumen de carpetas por nivel:
-- ============================================================================
-- NIVEL 7 (Microempresa ≤10 trab Riesgo I-III):
--   PLANEAR: 1.1 Recursos
--   HACER: 2.4 Capacitación, 2.11 Brigada
--   VERIFICAR: 3.1 Indicadores, 3.5 Cumplimiento Legal
--   ACTUAR: 4.1 Plan de Mejoramiento
--   TOTAL: 6 carpetas de categoría
--
-- NIVEL 21 (11-50 trab o ≤10 Riesgo IV-V):
--   PLANEAR: 1.1, 1.2, 1.4, 1.5
--   HACER: 2.1, 2.4, 2.5, 2.7, 2.10, 2.11, 2.12
--   VERIFICAR: 3.1, 3.5
--   ACTUAR: 4.1, 4.2
--   TOTAL: 15 carpetas de categoría
--
-- NIVEL 60 (>50 trabajadores):
--   Todas las carpetas (33 categorías)
-- ============================================================================
