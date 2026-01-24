-- ============================================================================
-- MIGRACIÓN PRODUCCIÓN: Módulo Documentación SST
-- Fecha: Enero 2026
--
-- INSTRUCCIONES:
-- 1. Hacer backup de la base de datos antes de ejecutar
-- 2. Ejecutar en orden (ALTER, UPDATE, CREATE)
-- 3. Verificar resultados con los SELECT al final
-- ============================================================================

-- ============================================================================
-- PARTE 1: ALTER TABLE - Agregar campos de nivel a plantillas
-- ============================================================================

-- Verificar si las columnas ya existen antes de agregarlas
-- Si ya existen, estos comandos darán error pero puedes ignorarlos

-- Opción A: Ejecutar uno por uno (ignora errores si ya existe)
ALTER TABLE `tbl_doc_plantillas`
ADD COLUMN `aplica_7` TINYINT(1) NOT NULL DEFAULT 1
    COMMENT 'Aplica para empresas con 7 estándares (Microempresas <=10 trab Riesgo I-III)' AFTER `orden`;

ALTER TABLE `tbl_doc_plantillas`
ADD COLUMN `aplica_21` TINYINT(1) NOT NULL DEFAULT 1
    COMMENT 'Aplica para empresas con 21 estándares (11-50 trab o <=10 Riesgo IV-V)' AFTER `aplica_7`;

ALTER TABLE `tbl_doc_plantillas`
ADD COLUMN `aplica_60` TINYINT(1) NOT NULL DEFAULT 1
    COMMENT 'Aplica para empresas con 60 estándares (>50 trabajadores)' AFTER `aplica_21`;

-- ============================================================================
-- PARTE 2: UPDATE - Configurar niveles según Resolución 0312 de 2019
-- ============================================================================

-- Resetear todo a aplica en todos los niveles
UPDATE `tbl_doc_plantillas` SET
    `aplica_7` = 1,
    `aplica_21` = 1,
    `aplica_60` = 1;

-- ----------------------------------------
-- NIVEL 7: Qué NO aplica para microempresas
-- ----------------------------------------

-- COPASST: No aplica, tienen Vigía SST (< 10 trabajadores)
UPDATE `tbl_doc_plantillas` SET `aplica_7` = 0
WHERE `codigo_sugerido` IN ('REG-COP', 'ACT-COP');

-- Comité Convivencia: No obligatorio para < 10 trabajadores
UPDATE `tbl_doc_plantillas` SET `aplica_7` = 0
WHERE `codigo_sugerido` IN ('REG-CCL', 'ACT-CCL');

-- Auditoría Interna: No obligatoria en nivel 7
UPDATE `tbl_doc_plantillas` SET `aplica_7` = 0
WHERE `codigo_sugerido` IN ('PRO-AUD', 'INF-AUD');

-- Procedimientos complejos: Simplificados o no requeridos
UPDATE `tbl_doc_plantillas` SET `aplica_7` = 0
WHERE `codigo_sugerido` IN ('PRO-ACP', 'PRO-COM', 'PRO-DOC');

-- Manual completo del SG-SST: No obligatorio
UPDATE `tbl_doc_plantillas` SET `aplica_7` = 0
WHERE `codigo_sugerido` = 'MAN-SST';

-- Manual de Funciones completo: Simplificado
UPDATE `tbl_doc_plantillas` SET `aplica_7` = 0
WHERE `codigo_sugerido` = 'MAN-FUN';

-- Manual de Contratistas: Solo si tiene contratistas
UPDATE `tbl_doc_plantillas` SET `aplica_7` = 0
WHERE `codigo_sugerido` = 'MAN-CON';

-- Informe de Revisión por Dirección: Simplificado
UPDATE `tbl_doc_plantillas` SET `aplica_7` = 0
WHERE `codigo_sugerido` = 'INF-RAD';

-- Matriz Legal completa: Simplificada
UPDATE `tbl_doc_plantillas` SET `aplica_7` = 0
WHERE `codigo_sugerido` = 'MTZ-LEG';

-- Plan de Capacitación formal: Integrado al PTA
UPDATE `tbl_doc_plantillas` SET `aplica_7` = 0
WHERE `codigo_sugerido` = 'PLA-CAP';

-- Programa de Mantenimiento: Simplificado
UPDATE `tbl_doc_plantillas` SET `aplica_7` = 0
WHERE `codigo_sugerido` = 'PRG-MTO';

-- Programa Psicosocial: Solo si aplica por diagnóstico
UPDATE `tbl_doc_plantillas` SET `aplica_7` = 0
WHERE `codigo_sugerido` = 'PRG-PSI';

-- Acta de Brigada formal: Simplificado
UPDATE `tbl_doc_plantillas` SET `aplica_7` = 0
WHERE `codigo_sugerido` = 'ACT-BRI';

-- ----------------------------------------
-- NIVEL 21: Qué NO aplica para empresas medianas
-- ----------------------------------------

-- Informe extenso auditoría: Puede ser simplificado
UPDATE `tbl_doc_plantillas` SET `aplica_21` = 0
WHERE `codigo_sugerido` = 'INF-AUD';

-- Control Documental extenso: Puede ser simplificado
UPDATE `tbl_doc_plantillas` SET `aplica_21` = 0
WHERE `codigo_sugerido` = 'PRO-DOC';

-- Manual de Contratistas: Solo si aplica
UPDATE `tbl_doc_plantillas` SET `aplica_21` = 0
WHERE `codigo_sugerido` = 'MAN-CON';

-- ============================================================================
-- PARTE 3: TABLA DE MAPEO - Plantillas a Carpetas
-- ============================================================================

DROP TABLE IF EXISTS tbl_doc_plantilla_carpeta;

CREATE TABLE tbl_doc_plantilla_carpeta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo_plantilla VARCHAR(50) NOT NULL COMMENT 'Código de la plantilla (ej: POL-SST)',
    codigo_carpeta VARCHAR(10) NOT NULL COMMENT 'Código de carpeta destino (ej: 1.2)',
    descripcion VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_plantilla_carpeta (codigo_plantilla)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta, descripcion) VALUES
-- PLANEAR (1.x)
('DESIG-RESP-SST', '1.1', 'Designación responsable SST'),
('ASIG-RECURSOS', '1.1', 'Asignación de recursos'),
('POL-SST', '1.2', 'Política SST'),
('OBJ-SST', '1.2', 'Objetivos SST'),
('POL-ACO', '1.2', 'Política prevención acoso'),
('POL-PESV', '1.2', 'Política seguridad vial'),
('POL-EPP', '1.2', 'Política EPP'),
('EVAL-INICIAL', '1.3', 'Evaluación inicial SG-SST'),
('PLA-TRA', '1.4', 'Plan de trabajo anual'),
('MTZ-IPE', '1.5', 'Matriz IPEVR'),
('MTZ-LEG', '1.6', 'Matriz requisitos legales'),
('COM-SST', '1.7', 'Mecanismos comunicación'),
('PRO-COM', '1.7', 'Procedimiento comunicaciones'),
('PROC-COMPRA-SST', '1.8', 'Procedimiento compras SST'),
('PROC-CONTRATISTAS', '1.8', 'Procedimiento contratistas'),
('MAN-CON', '1.8', 'Manual contratistas'),
('REND-CUENTAS', '1.10', 'Rendición de cuentas'),
('RET-DOC', '1.11', 'Retención documental'),
('PRO-DOC', '1.11', 'Procedimiento control documental'),
-- HACER (2.x)
('PRG-PVE', '2.1', 'Programa vigilancia epidemiológica'),
('PRG-PSI', '2.2', 'Programa riesgo psicosocial'),
('PVE-PSICO', '2.2', 'PVE psicosocial'),
('REG-CCL', '2.3', 'Reglamento comité convivencia'),
('ACT-CCL', '2.3', 'Acta comité convivencia'),
('PRG-CAP', '2.4', 'Programa capacitación'),
('PLA-CAP', '2.4', 'Plan capacitación anual'),
('CAP-ANUAL', '2.4', 'Cronograma capacitación'),
('FOR-ASI', '2.4', 'Formato asistencia capacitación'),
('INDU-REINDU', '2.5', 'Programa inducción/reinducción'),
('PRG-MTO', '2.9', 'Programa mantenimiento'),
('PRG-OYA', '2.8', 'Programa orden y aseo'),
('FOR-INS', '2.8', 'Formato inspección seguridad'),
('PRG-EPP', '2.7', 'Programa EPP'),
('FOR-EPP', '2.7', 'Formato entrega EPP'),
('INS-EPP', '2.7', 'Instructivo uso EPP'),
('PLAN-EMER', '2.10', 'Plan de emergencias'),
('PRT-EME', '2.10', 'Protocolo emergencias'),
('ACT-BRI', '2.11', 'Acta conformación brigada'),
('REG-COP', '2.12', 'Reglamento COPASST'),
('ACT-COP', '2.12', 'Acta COPASST'),
('PROC-INV-AT', '2.13', 'Procedimiento investigación AT'),
('FOR-INC', '2.13', 'Formato reporte incidentes'),
('REG-HSI', '2.14', 'Reglamento higiene y seguridad'),
-- VERIFICAR (3.x)
('IND-SST', '3.1', 'Indicadores SG-SST'),
('FICHA-INDICADORES', '3.1', 'Fichas indicadores'),
('PRO-AUD', '3.2', 'Procedimiento auditoría'),
('AUD-INTERNA-PLAN', '3.2', 'Plan auditoría interna'),
('INF-AUD', '3.2', 'Informe auditoría'),
('INF-RAD', '3.3', 'Informe revisión alta dirección'),
('REVISION-DIRECCION-ACTA', '3.3', 'Acta revisión dirección'),
('FORM-INSPECCION-LOC', '3.4', 'Formato inspección locativa'),
-- ACTUAR (4.x)
('PLA-MEJ', '4.1', 'Plan de mejoramiento'),
('PLAN-MEJORA', '4.1', 'Plan mejora continua'),
('PRO-ACP', '4.2', 'Procedimiento acciones correctivas'),
('REG-ACCIONES-CORRECTIVAS', '4.2', 'Registro acciones correctivas'),
('MAN-SST', '1.2', 'Manual SG-SST'),
('MAN-FUN', '1.1', 'Manual funciones SST'),
('GUA-SEG', '2.6', 'Guía trabajo seguro'),
('PRT-BIO', '2.6', 'Protocolo bioseguridad'),
('PRG-EVS', '2.1', 'Programa estilos vida saludable');

-- ============================================================================
-- PARTE 4: STORED PROCEDURE - Estructura carpetas según nivel de estándares
-- Crea solo las carpetas que aplican según el nivel del cliente (7, 21 o 60)
-- Basado en Resolución 0312 de 2019
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
        SET p_nivel_estandares = 60;
    END IF;

    -- Eliminar estructura anterior del mismo año
    DELETE FROM tbl_doc_carpetas
    WHERE id_cliente = p_id_cliente
    AND nombre LIKE CONCAT('SG-SST ', p_anio, '%');

    -- NIVEL 1: Carpeta Raíz
    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, NULL, CONCAT('SG-SST ', p_anio), NULL, 1, 'raiz', 'folder-root');
    SET v_id_raiz = LAST_INSERT_ID();

    -- NIVEL 2: Ciclo PHVA (siempre se crean las 4)
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

    -- PLANEAR --
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

    -- 1.6 a 1.11 - Solo nivel 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_planear, '1.6. Requisitos Legales', '1.6', 6, 'categoria', 'book-open');
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_planear, '1.7. Mecanismos de Comunicacion', '1.7', 7, 'categoria', 'message-circle');
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_planear, '1.8. Adquisiciones y Contratacion', '1.8', 8, 'categoria', 'shopping-cart');
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_planear, '1.9. Gestion del Cambio', '1.9', 9, 'categoria', 'refresh-cw');
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_planear, '1.10. Rendicion de Cuentas', '1.10', 10, 'categoria', 'bar-chart');
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_planear, '1.11. Control Documental', '1.11', 11, 'categoria', 'folder');
    END IF;

    -- HACER --
    -- 2.1 Condiciones de Salud - Aplica a 21 y 60
    IF p_nivel_estandares >= 21 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '2.1. Condiciones de Salud', '2.1', 1, 'categoria', 'heart');
    END IF;

    -- 2.2 y 2.3 - Solo 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '2.2. Riesgo Psicosocial', '2.2', 2, 'categoria', 'brain');
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

    -- 2.6 Medidas de Prevención - Solo 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '2.6. Medidas de Prevencion y Control', '2.6', 6, 'categoria', 'shield');
    END IF;

    -- 2.7 EPP - Aplica a 21 y 60
    IF p_nivel_estandares >= 21 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '2.7. EPP', '2.7', 7, 'categoria', 'hard-hat');
    END IF;

    -- 2.8 y 2.9 - Solo 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '2.8. Inspecciones de Seguridad', '2.8', 8, 'categoria', 'search');
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

    -- 2.12 COPASST - Aplica a 21 y 60
    IF p_nivel_estandares >= 21 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '2.12. COPASST / Vigia SST', '2.12', 12, 'categoria', 'users');
    END IF;

    -- 2.13 y 2.14 - Solo 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '2.13. Investigacion AT/EL', '2.13', 13, 'categoria', 'file-text');
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '2.14. Reglamentos', '2.14', 14, 'categoria', 'file');
    END IF;

    -- VERIFICAR --
    -- 3.1 Indicadores - Aplica a TODOS (7, 21, 60)
    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_verificar, '3.1. Indicadores del SG-SST', '3.1', 1, 'categoria', 'activity');

    -- 3.2 a 3.4 - Solo 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_verificar, '3.2. Auditoria Interna', '3.2', 2, 'categoria', 'clipboard');
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_verificar, '3.3. Revision por la Alta Direccion', '3.3', 3, 'categoria', 'eye');
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_verificar, '3.4. Investigacion de AT/EL (Informes)', '3.4', 4, 'categoria', 'file-text');
    END IF;

    -- 3.5 Cumplimiento Legal - Aplica a TODOS (7, 21, 60)
    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_verificar, '3.5. Cumplimiento de Requisitos Legales', '3.5', 5, 'categoria', 'check-square');

    -- ACTUAR --
    -- 4.1 Plan de Mejoramiento - Aplica a TODOS (7, 21, 60)
    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_actuar, '4.1. Plan de Mejoramiento', '4.1', 1, 'categoria', 'trending-up');

    -- 4.2 Acciones Correctivas - Aplica a 21 y 60
    IF p_nivel_estandares >= 21 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_actuar, '4.2. Acciones Correctivas y Preventivas', '4.2', 2, 'categoria', 'check-circle');
    END IF;

    -- 4.3 Mejora Continua - Solo 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_actuar, '4.3. Mejora Continua', '4.3', 3, 'categoria', 'refresh-cw');
    END IF;

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
--   TOTAL: 6 carpetas de categoría + 4 PHVA + 1 raíz = 11 carpetas
--
-- NIVEL 21 (11-50 trab o ≤10 Riesgo IV-V):
--   PLANEAR: 1.1, 1.2, 1.4, 1.5
--   HACER: 2.1, 2.4, 2.5, 2.7, 2.10, 2.11, 2.12
--   VERIFICAR: 3.1, 3.5
--   ACTUAR: 4.1, 4.2
--   TOTAL: 15 carpetas de categoría + 4 PHVA + 1 raíz = 20 carpetas
--
-- NIVEL 60 (>50 trabajadores):
--   Todas las carpetas (33 categorías + 4 PHVA + 1 raíz = 38 carpetas)
-- ============================================================================

-- ============================================================================
-- PARTE 5: FUNCIÓN - Obtener carpeta destino de un documento
-- ============================================================================

DROP FUNCTION IF EXISTS fn_get_carpeta_documento;

DELIMITER //

CREATE FUNCTION fn_get_carpeta_documento(
    p_id_cliente INT,
    p_codigo_plantilla VARCHAR(50)
) RETURNS INT
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE v_codigo_carpeta VARCHAR(10);
    DECLARE v_id_carpeta INT DEFAULT NULL;

    SELECT codigo_carpeta INTO v_codigo_carpeta
    FROM tbl_doc_plantilla_carpeta
    WHERE codigo_plantilla = p_codigo_plantilla
    LIMIT 1;

    IF v_codigo_carpeta IS NULL THEN
        RETURN NULL;
    END IF;

    SELECT id_carpeta INTO v_id_carpeta
    FROM tbl_doc_carpetas
    WHERE id_cliente = p_id_cliente
    AND codigo = v_codigo_carpeta
    LIMIT 1;

    RETURN v_id_carpeta;
END //

DELIMITER ;

-- ============================================================================
-- VERIFICACIÓN: Consultas para confirmar que todo está correcto
-- ============================================================================

-- Ver plantillas por nivel
SELECT
    codigo_sugerido AS codigo,
    nombre,
    aplica_7 AS 'Nivel 7',
    aplica_21 AS 'Nivel 21',
    aplica_60 AS 'Nivel 60'
FROM tbl_doc_plantillas
WHERE activo = 1
ORDER BY aplica_7 ASC, aplica_21 ASC, codigo_sugerido;

-- Conteo de plantillas por nivel
SELECT
    SUM(aplica_7) AS 'Total Nivel 7',
    SUM(aplica_21) AS 'Total Nivel 21',
    SUM(aplica_60) AS 'Total Nivel 60',
    COUNT(*) AS 'Total Plantillas'
FROM tbl_doc_plantillas
WHERE activo = 1;

-- Ver mapeo plantilla-carpeta
SELECT * FROM tbl_doc_plantilla_carpeta ORDER BY codigo_carpeta;

-- ============================================================================
-- FIN DE LA MIGRACIÓN
-- ============================================================================
