USE empresas_sst;

-- ============================================================================
-- SP: Generar estructura de carpetas segun nivel de estandares del cliente
-- Resolucion 0312 de 2019 - 60 Estandares Minimos
-- Estructura: SG-SST [Ano] / PHVA / Estandar (cada estandar = carpeta)
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
        SET p_nivel_estandares = 60;
    END IF;

    -- Eliminar estructura anterior del mismo ano si existe
    DELETE FROM tbl_doc_carpetas
    WHERE id_cliente = p_id_cliente
    AND nombre LIKE CONCAT('SG-SST ', p_anio, '%');

    -- ========================================================================
    -- NIVEL 1: Carpeta Raiz
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
    -- NIVEL 3: Estandares directamente bajo cada ciclo PHVA
    -- Aplicabilidad verificada contra Res. 0312/2019 Arts. 3, 9 y 16
    -- ========================================================================

    -- ========================================================================
    -- PLANEAR - Categoria I: RECURSOS (10%)
    -- Estandares 1.1.1 a 1.2.3
    -- ========================================================================

    -- 1.1.1 - Aplica: 7, 21, 60
    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_planear, '1.1.1. Responsable del Sistema de Gestion de Seguridad y Salud en el Trabajo SG-SST', '1.1.1', 1, 'estandar', 'person-check');

    -- 1.1.2 - Aplica: 7, 21, 60
    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_planear, '1.1.2. Responsabilidades en el Sistema de Gestion de Seguridad y Salud en el Trabajo SG-SST', '1.1.2', 2, 'estandar', 'person-lines-fill');

    -- 1.1.3 - Aplica: 7, 21, 60
    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_planear, '1.1.3. Asignacion de recursos para el Sistema de Gestion en Seguridad y Salud en el Trabajo SG-SST', '1.1.3', 3, 'estandar', 'cash-stack');

    -- 1.1.4 - Aplica: 7, 21, 60
    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_planear, '1.1.4. Afiliacion al Sistema General de Riesgos Laborales', '1.1.4', 4, 'estandar', 'card-checklist');

    -- 1.1.5 - Aplica: 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_planear, '1.1.5. Identificacion de trabajadores de alto riesgo y cotizacion de pension especial', '1.1.5', 5, 'estandar', 'currency-dollar');
    END IF;

    -- 1.1.6 - Aplica: 21, 60
    IF p_nivel_estandares >= 21 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_planear, '1.1.6. Conformacion COPASST', '1.1.6', 6, 'estandar', 'people');
    END IF;

    -- 1.1.7 - Aplica: 7, 21, 60
    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_planear, '1.1.7. Capacitacion COPASST', '1.1.7', 7, 'estandar', 'mortarboard');

    -- 1.1.8 - Aplica: 7, 21, 60
    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_planear, '1.1.8. Conformacion Comite de Convivencia', '1.1.8', 8, 'estandar', 'hand-thumbs-up');

    -- 1.2.1 - Aplica: 7, 21, 60
    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_planear, '1.2.1. Programa Capacitacion promocion y prevencion PYP', '1.2.1', 9, 'estandar', 'book');

    -- 1.2.2 - Aplica: 7, 21, 60
    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_planear, '1.2.2. Induccion y Reinduccion en Sistema de Gestion de Seguridad y Salud en el Trabajo SG-SST, actividades de Promocion y Prevencion PyP', '1.2.2', 10, 'estandar', 'person-plus');

    -- 1.2.3 - Aplica: 7, 21, 60
    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_planear, '1.2.3. Responsables del Sistema de Gestion de Seguridad y Salud en el Trabajo SG-SST con curso virtual de 50 horas', '1.2.3', 11, 'estandar', 'award');

    -- ========================================================================
    -- PLANEAR - Categoria II: GESTION INTEGRAL DEL SG-SST (15%)
    -- Estandares 2.1.1 a 2.11.1
    -- ========================================================================

    -- 2.1.1 - Aplica: 7, 21, 60
    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_planear, '2.1.1. Politica del Sistema de Gestion de Seguridad y Salud en el Trabajo SG-SST firmada, fechada y comunicada al COPASST', '2.1.1', 12, 'estandar', 'file-earmark-text');

    -- 2.2.1 - Aplica: 7, 21, 60
    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_planear, '2.2.1. Objetivos definidos, claros, medibles, cuantificables, con metas, documentados, revisados del SG-SST', '2.2.1', 13, 'estandar', 'bullseye');

    -- 2.3.1 - Aplica: 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_planear, '2.3.1. Evaluacion e identificacion de prioridades', '2.3.1', 14, 'estandar', 'clipboard-check');
    END IF;

    -- 2.4.1 - Aplica: 7, 21, 60
    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_planear, '2.4.1. Plan que identifica objetivos, metas, responsabilidad, recursos con cronograma y firmado', '2.4.1', 15, 'estandar', 'calendar');

    -- 2.5.1 - Aplica: 7, 21, 60
    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_planear, '2.5.1. Archivo o retencion documental del Sistema de Gestion en Seguridad y Salud en el Trabajo SG-SST', '2.5.1', 16, 'estandar', 'folder2');

    -- 2.6.1 - Aplica: 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_planear, '2.6.1. Rendicion sobre el desempeno', '2.6.1', 17, 'estandar', 'bar-chart');
    END IF;

    -- 2.7.1 - Aplica: 7, 21, 60
    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_planear, '2.7.1. Matriz legal', '2.7.1', 18, 'estandar', 'book-half');

    -- 2.8.1 - Aplica: 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_planear, '2.8.1. Mecanismos de comunicacion, auto reporte en Sistema de Gestion de Seguridad y Salud en el Trabajo SG-SST', '2.8.1', 19, 'estandar', 'megaphone');
    END IF;

    -- 2.9.1 - Aplica: 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_planear, '2.9.1. Identificacion, evaluacion, para adquisicion de productos y servicios en Sistema de Gestion de Seguridad y Salud en el Trabajo SG-SST', '2.9.1', 20, 'estandar', 'cart');
    END IF;

    -- 2.10.1 - Aplica: 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_planear, '2.10.1. Evaluacion y seleccion de proveedores y contratistas', '2.10.1', 21, 'estandar', 'building');
    END IF;

    -- 2.11.1 - Aplica: 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_planear, '2.11.1. Evaluacion del impacto de cambios internos y externos en el Sistema de Gestion de Seguridad y Salud en el Trabajo SG-SST', '2.11.1', 22, 'estandar', 'arrow-repeat');
    END IF;

    -- ========================================================================
    -- HACER - Categoria III: GESTION DE LA SALUD (20%)
    -- Estandares 3.1.1 a 3.3.6
    -- ========================================================================

    -- 3.1.1 - Aplica: 21, 60
    IF p_nivel_estandares >= 21 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '3.1.1. Descripcion sociodemografica - Diagnostico de Condiciones de Salud', '3.1.1', 1, 'estandar', 'clipboard2-pulse');
    END IF;

    -- 3.1.2 - Aplica: 21, 60
    IF p_nivel_estandares >= 21 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '3.1.2. Actividades de Promocion y Prevencion en Salud', '3.1.2', 2, 'estandar', 'heart');
    END IF;

    -- 3.1.3 - Aplica: 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '3.1.3. Informacion al medico de los perfiles de cargo', '3.1.3', 3, 'estandar', 'file-medical');
    END IF;

    -- 3.1.4 - Aplica: 7, 21, 60
    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_hacer, '3.1.4. Realizacion de las evaluaciones medicas ocupacionales: Peligros- Periodicidad Comunicacion al Trabajador', '3.1.4', 4, 'estandar', 'clipboard-check');

    -- 3.1.5 - Aplica: 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '3.1.5. Custodia de Historias Clinicas', '3.1.5', 5, 'estandar', 'lock');
    END IF;

    -- 3.1.6 - Aplica: 21, 60
    IF p_nivel_estandares >= 21 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '3.1.6. Restricciones y recomendaciones medico-laborales', '3.1.6', 6, 'estandar', 'exclamation-circle');
    END IF;

    -- 3.1.7 - Aplica: 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '3.1.7. Estilos de vida y entornos saludables (controles tabaquismo, alcoholismo, farmacodependencia y otros)', '3.1.7', 7, 'estandar', 'emoji-smile');
    END IF;

    -- 3.1.8 - Aplica: 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '3.1.8. Agua potable, servicios sanitarios y disposicion de basuras', '3.1.8', 8, 'estandar', 'droplet');
    END IF;

    -- 3.1.9 - Aplica: 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '3.1.9. Eliminacion adecuada de residuos solidos, liquidos o gaseosos', '3.1.9', 9, 'estandar', 'recycle');
    END IF;

    -- 3.2.1 - Aplica: 7, 21, 60
    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_hacer, '3.2.1. Reporte de los accidentes de trabajo y enfermedad laboral a la ARL, EPS y Direccion Territorial del Ministerio de Trabajo', '3.2.1', 10, 'estandar', 'exclamation-triangle');

    -- 3.2.2 - Aplica: 7, 21, 60
    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_hacer, '3.2.2. Investigacion de Incidentes, Accidentes y Enfermedades Laborales', '3.2.2', 11, 'estandar', 'search');

    -- 3.2.3 - Aplica: 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '3.2.3. Registro y analisis estadistico de Accidentes y Enfermedades Laborales', '3.2.3', 12, 'estandar', 'graph-up');
    END IF;

    -- 3.3.1 - Aplica: 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '3.3.1. Medicion de la frecuencia de la accidentalidad', '3.3.1', 13, 'estandar', 'speedometer');
    END IF;

    -- 3.3.2 - Aplica: 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '3.3.2. Medicion de la severidad de la accidentalidad', '3.3.2', 14, 'estandar', 'clock-history');
    END IF;

    -- 3.3.3 - Aplica: 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '3.3.3. Medicion de la mortalidad por accidentes de trabajo', '3.3.3', 15, 'estandar', 'exclamation-diamond');
    END IF;

    -- 3.3.4 - Aplica: 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '3.3.4. Medicion de la prevalencia de Enfermedad Laboral', '3.3.4', 16, 'estandar', 'graph-down');
    END IF;

    -- 3.3.5 - Aplica: 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '3.3.5. Medicion de la incidencia de Enfermedad Laboral', '3.3.5', 17, 'estandar', 'graph-up-arrow');
    END IF;

    -- 3.3.6 - Aplica: 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '3.3.6. Medicion del ausentismo por causa medica', '3.3.6', 18, 'estandar', 'calendar-x');
    END IF;

    -- ========================================================================
    -- HACER - Categoria IV: GESTION DE PELIGROS Y RIESGOS (30%)
    -- Estandares 4.1.1 a 4.2.6
    -- ========================================================================

    -- 4.1.1 - Aplica: 7, 21, 60
    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_hacer, '4.1.1. Metodologia para la identificacion de peligros, evaluacion y valoracion de los riesgos', '4.1.1', 19, 'estandar', 'search');

    -- 4.1.2 - Aplica: 7, 21, 60
    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_hacer, '4.1.2. Identificacion de peligros con participacion de todos los niveles de la empresa', '4.1.2', 20, 'estandar', 'exclamation-triangle');

    -- 4.1.3 - Aplica: 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '4.1.3. Identificacion de sustancias catalogadas como cancerigenas o con toxicidad aguda', '4.1.3', 21, 'estandar', 'sort-numeric-down');
    END IF;

    -- 4.1.4 - Aplica: 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '4.1.4. Realizacion mediciones ambientales, quimicos, fisicos y biologicos', '4.1.4', 22, 'estandar', 'thermometer-half');
    END IF;

    -- 4.2.1 - Aplica: 7, 21, 60
    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_hacer, '4.2.1. Implementacion de medidas de prevencion y control frente a peligros/riesgos identificados', '4.2.1', 23, 'estandar', 'shield-check');

    -- 4.2.2 - Aplica: 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '4.2.2. Verificacion de aplicacion de medidas de prevencion y control por parte de los trabajadores', '4.2.2', 24, 'estandar', 'check2-circle');
    END IF;

    -- 4.2.3 - Aplica: 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '4.2.3. Elaboracion de procedimientos, instructivos, fichas, protocolos', '4.2.3', 25, 'estandar', 'journal-text');
    END IF;

    -- 4.2.4 - Aplica: 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '4.2.4. Realizacion de Inspecciones a instalaciones, maquinaria o equipos con participacion del COPASST', '4.2.4', 26, 'estandar', 'binoculars');
    END IF;

    -- 4.2.5 - Aplica: 21, 60
    IF p_nivel_estandares >= 21 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '4.2.5. Mantenimiento periodico de instalaciones, equipos, maquinas, herramientas', '4.2.5', 27, 'estandar', 'wrench');
    END IF;

    -- 4.2.6 - Aplica: 7, 21, 60
    INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
    VALUES (p_id_cliente, v_id_hacer, '4.2.6. Entrega de Elementos de Proteccion Personal EPP, se verifica con contratistas y subcontratistas', '4.2.6', 28, 'estandar', 'shield');

    -- ========================================================================
    -- HACER - Categoria V: GESTION DE AMENAZAS (10%)
    -- Estandares 5.1.1 a 5.1.2
    -- ========================================================================

    -- 5.1.1 - Aplica: 21, 60
    IF p_nivel_estandares >= 21 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '5.1.1. Se cuenta con el Plan de Prevencion, Preparacion y respuesta ante emergencias', '5.1.1', 29, 'estandar', 'exclamation-octagon');
    END IF;

    -- 5.1.2 - Aplica: 21, 60
    IF p_nivel_estandares >= 21 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_hacer, '5.1.2. Brigada de prevencion conformada, capacitada y dotada', '5.1.2', 30, 'estandar', 'people');
    END IF;

    -- ========================================================================
    -- VERIFICAR - Categoria VI: VERIFICACION DEL SG-SST (5%)
    -- Estandares 6.1.1 a 6.1.4
    -- ========================================================================

    -- 6.1.1 - Aplica: 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_verificar, '6.1.1. Definicion de Indicadores del SG-SST de acuerdo condiciones de la empresa', '6.1.1', 1, 'estandar', 'speedometer2');
    END IF;

    -- 6.1.2 - Aplica: 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_verificar, '6.1.2. La empresa adelanta auditoria por lo menos una vez al ano', '6.1.2', 2, 'estandar', 'journal-check');
    END IF;

    -- 6.1.3 - Aplica: 21, 60
    IF p_nivel_estandares >= 21 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_verificar, '6.1.3. Revision anual de la alta direccion, resultados de la auditoria', '6.1.3', 3, 'estandar', 'eye');
    END IF;

    -- 6.1.4 - Aplica: 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_verificar, '6.1.4. Planificacion auditorias con el COPASST', '6.1.4', 4, 'estandar', 'calendar-check');
    END IF;

    -- ========================================================================
    -- ACTUAR - Categoria VII: MEJORAMIENTO (10%)
    -- Estandares 7.1.1 a 7.1.4
    -- ========================================================================

    -- 7.1.1 - Aplica: 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_actuar, '7.1.1. Definicion de acciones preventivas y correctivas con base en resultados del SG-SST', '7.1.1', 1, 'estandar', 'lightning');
    END IF;

    -- 7.1.2 - Aplica: 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_actuar, '7.1.2. Acciones de mejora conforme a revision de la alta direccion', '7.1.2', 2, 'estandar', 'check-circle');
    END IF;

    -- 7.1.3 - Aplica: 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_actuar, '7.1.3. Acciones de mejora con base en investigaciones de accidentes de trabajo y enfermedades laborales', '7.1.3', 3, 'estandar', 'gear-wide-connected');
    END IF;

    -- 7.1.4 - Aplica: 60
    IF p_nivel_estandares = 60 THEN
        INSERT INTO tbl_doc_carpetas (id_cliente, id_carpeta_padre, nombre, codigo, orden, tipo, icono)
        VALUES (p_id_cliente, v_id_actuar, '7.1.4. Elaboracion Plan de mejoramiento, implementacion de medidas y acciones correctivas solicitadas por autoridades y ARL', '7.1.4', 4, 'estandar', 'building');
    END IF;

    -- Retornar ID de carpeta raiz
    SELECT v_id_raiz AS id_carpeta_raiz;

END //

DELIMITER ;

-- ============================================================================
-- Resumen de carpetas por nivel (verificado contra Res. 0312/2019):
-- ============================================================================
-- NIVEL 7 (Microempresa <=10 trab Riesgo I-III): 21 estandares
--   PLANEAR: 1.1.1, 1.1.2, 1.1.3, 1.1.4, 1.1.7, 1.1.8, 1.2.1, 1.2.2, 1.2.3,
--            2.1.1, 2.2.1, 2.4.1, 2.5.1, 2.7.1
--   HACER:   3.1.4, 3.2.1, 3.2.2, 4.1.1, 4.1.2, 4.2.1, 4.2.6
--   VERIFICAR: (vacio)
--   ACTUAR: (vacio)
--   TOTAL: 21 estandares + 4 PHVA + 1 raiz = 26 carpetas
--
-- NIVEL 21 (11-50 trab o <=10 Riesgo IV-V): 29 estandares
--   PLANEAR: los 14 del nivel 7 + 1.1.6
--   HACER:   los 7 del nivel 7 + 3.1.1, 3.1.2, 3.1.6, 4.2.5, 5.1.1, 5.1.2
--   VERIFICAR: 6.1.3
--   ACTUAR: (vacio)
--   TOTAL: 29 estandares + 4 PHVA + 1 raiz = 34 carpetas
--
-- NIVEL 60 (>50 trabajadores): 60 estandares
--   Todos los 60 estandares + 4 PHVA + 1 raiz = 65 carpetas
-- ============================================================================
