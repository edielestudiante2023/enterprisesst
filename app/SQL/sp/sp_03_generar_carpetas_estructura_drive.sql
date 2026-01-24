USE empresas_sst;

-- ============================================================================
-- SP: Generar estructura de carpetas como se usa en Drive/SharePoint
-- Estructura: SG-SST [Año] / PHVA / Categoría / [Documentos van aquí]
-- Basado en la estructura real de consultores SST
-- Fecha: Enero 2026
-- ============================================================================

DROP PROCEDURE IF EXISTS sp_generar_carpetas_estructura_drive;

DELIMITER //

CREATE PROCEDURE sp_generar_carpetas_estructura_drive(
    IN p_id_cliente INT,
    IN p_anio INT
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
    -- NIVEL 2: Ciclo PHVA
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
    -- NIVEL 3: Categorías dentro de PLANEAR
    -- ========================================================================
    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono) VALUES
    (p_id_cliente, v_id_planear, '1.1. Recursos (Responsable, Presupuesto, Afiliaciones)', '1.1', 1, 'categoria', 'users'),
    (p_id_cliente, v_id_planear, '1.2. Política y Objetivos SST', '1.2', 2, 'categoria', 'target'),
    (p_id_cliente, v_id_planear, '1.3. Evaluación Inicial', '1.3', 3, 'categoria', 'clipboard-check'),
    (p_id_cliente, v_id_planear, '1.4. Plan de Trabajo Anual', '1.4', 4, 'categoria', 'calendar'),
    (p_id_cliente, v_id_planear, '1.5. Identificación de Peligros (IPEVR)', '1.5', 5, 'categoria', 'alert-triangle'),
    (p_id_cliente, v_id_planear, '1.6. Requisitos Legales', '1.6', 6, 'categoria', 'book-open'),
    (p_id_cliente, v_id_planear, '1.7. Mecanismos de Comunicación', '1.7', 7, 'categoria', 'message-circle'),
    (p_id_cliente, v_id_planear, '1.8. Adquisiciones y Contratación', '1.8', 8, 'categoria', 'shopping-cart'),
    (p_id_cliente, v_id_planear, '1.9. Gestión del Cambio', '1.9', 9, 'categoria', 'refresh-cw'),
    (p_id_cliente, v_id_planear, '1.10. Rendición de Cuentas', '1.10', 10, 'categoria', 'bar-chart'),
    (p_id_cliente, v_id_planear, '1.11. Control Documental', '1.11', 11, 'categoria', 'folder');

    -- ========================================================================
    -- NIVEL 3: Categorías dentro de HACER
    -- ========================================================================
    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono) VALUES
    (p_id_cliente, v_id_hacer, '2.1. Condiciones de Salud (Exámenes, PVE)', '2.1', 1, 'categoria', 'heart'),
    (p_id_cliente, v_id_hacer, '2.2. Riesgo Psicosocial', '2.2', 2, 'categoria', 'brain'),
    (p_id_cliente, v_id_hacer, '2.3. Acoso Laboral y Convivencia', '2.3', 3, 'categoria', 'users'),
    (p_id_cliente, v_id_hacer, '2.4. Capacitación SST', '2.4', 4, 'categoria', 'book'),
    (p_id_cliente, v_id_hacer, '2.5. Inducción y Reinducción', '2.5', 5, 'categoria', 'user-plus'),
    (p_id_cliente, v_id_hacer, '2.6. Medidas de Prevención y Control', '2.6', 6, 'categoria', 'shield'),
    (p_id_cliente, v_id_hacer, '2.7. EPP (Elementos de Protección Personal)', '2.7', 7, 'categoria', 'hard-hat'),
    (p_id_cliente, v_id_hacer, '2.8. Inspecciones de Seguridad', '2.8', 8, 'categoria', 'search'),
    (p_id_cliente, v_id_hacer, '2.9. Mantenimiento', '2.9', 9, 'categoria', 'tool'),
    (p_id_cliente, v_id_hacer, '2.10. Plan de Emergencias', '2.10', 10, 'categoria', 'alert-circle'),
    (p_id_cliente, v_id_hacer, '2.11. Brigada de Emergencias', '2.11', 11, 'categoria', 'users'),
    (p_id_cliente, v_id_hacer, '2.12. COPASST / Vigía SST', '2.12', 12, 'categoria', 'users'),
    (p_id_cliente, v_id_hacer, '2.13. Investigación AT/EL', '2.13', 13, 'categoria', 'file-text'),
    (p_id_cliente, v_id_hacer, '2.14. Reglamentos (HSI, COPASST, CCL)', '2.14', 14, 'categoria', 'file');

    -- ========================================================================
    -- NIVEL 3: Categorías dentro de VERIFICAR
    -- ========================================================================
    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono) VALUES
    (p_id_cliente, v_id_verificar, '3.1. Indicadores del SG-SST', '3.1', 1, 'categoria', 'activity'),
    (p_id_cliente, v_id_verificar, '3.2. Auditoría Interna', '3.2', 2, 'categoria', 'clipboard'),
    (p_id_cliente, v_id_verificar, '3.3. Revisión por la Alta Dirección', '3.3', 3, 'categoria', 'eye'),
    (p_id_cliente, v_id_verificar, '3.4. Investigación de AT/EL (Informes)', '3.4', 4, 'categoria', 'file-text'),
    (p_id_cliente, v_id_verificar, '3.5. Cumplimiento de Requisitos Legales', '3.5', 5, 'categoria', 'check-square');

    -- ========================================================================
    -- NIVEL 3: Categorías dentro de ACTUAR
    -- ========================================================================
    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono) VALUES
    (p_id_cliente, v_id_actuar, '4.1. Plan de Mejoramiento', '4.1', 1, 'categoria', 'trending-up'),
    (p_id_cliente, v_id_actuar, '4.2. Acciones Correctivas y Preventivas', '4.2', 2, 'categoria', 'check-circle'),
    (p_id_cliente, v_id_actuar, '4.3. Mejora Continua', '4.3', 3, 'categoria', 'refresh-cw');

    -- Retornar ID de carpeta raíz
    SELECT v_id_raiz AS id_carpeta_raiz;

END //

DELIMITER ;

-- ============================================================================
-- Tabla de mapeo: Qué plantillas van en qué carpeta
-- Esto permite ubicar automáticamente los documentos generados
-- ============================================================================

DROP TABLE IF EXISTS tbl_doc_plantilla_carpeta;

CREATE TABLE IF NOT EXISTS tbl_doc_plantilla_carpeta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo_plantilla VARCHAR(50) NOT NULL COMMENT 'Código de la plantilla (ej: POL-SST)',
    codigo_carpeta VARCHAR(10) NOT NULL COMMENT 'Código de carpeta destino (ej: 1.2)',
    descripcion VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_plantilla_carpeta (codigo_plantilla)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- INSERT: Mapeo de plantillas a carpetas
-- ============================================================================

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
-- Función para obtener la carpeta destino de un documento
-- ============================================================================

DROP FUNCTION IF EXISTS fn_get_carpeta_documento;

DELIMITER //

CREATE FUNCTION fn_get_carpeta_documento(
    p_id_cliente INT,
    p_codigo_plantilla VARCHAR(20)
) RETURNS INT
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE v_codigo_carpeta VARCHAR(10);
    DECLARE v_id_carpeta INT DEFAULT NULL;

    -- Obtener código de carpeta para esta plantilla
    SELECT codigo_carpeta INTO v_codigo_carpeta
    FROM tbl_doc_plantilla_carpeta
    WHERE codigo_plantilla = p_codigo_plantilla
    LIMIT 1;

    IF v_codigo_carpeta IS NULL THEN
        RETURN NULL;
    END IF;

    -- Buscar la carpeta del cliente con ese código
    SELECT id_carpeta INTO v_id_carpeta
    FROM tbl_doc_carpetas
    WHERE id_cliente = p_id_cliente
    AND codigo = v_codigo_carpeta
    LIMIT 1;

    RETURN v_id_carpeta;
END //

DELIMITER ;
