-- ============================================================================
-- INSERT: 60 ESTÁNDARES MÍNIMOS - Resolución 0312 de 2019
-- Ministerio del Trabajo de Colombia
-- ============================================================================

-- Limpiar tabla antes de insertar (opcional, comentar si no se desea)
-- TRUNCATE TABLE `tbl_estandares_minimos`;

INSERT INTO `tbl_estandares_minimos`
(`item`, `nombre`, `ciclo_phva`, `categoria`, `categoria_nombre`, `peso_porcentual`, `aplica_7`, `aplica_21`, `aplica_60`, `documentos_sugeridos`)
VALUES

-- ============================================================================
-- CICLO PLANEAR - CATEGORÍA I: RECURSOS (10%)
-- ============================================================================

('1.1.1', 'Responsable del Sistema de Gestión de Seguridad y Salud en el Trabajo SG-SST', 'PLANEAR', 'I', 'Recursos', 0.50, 1, 1, 1, '["ACT-RSP","FOR-DES"]'),
('1.1.2', 'Responsabilidades en el Sistema de Gestión de Seguridad y Salud en el Trabajo – SG-SST', 'PLANEAR', 'I', 'Recursos', 0.50, 1, 1, 1, '["DOC-RSP","MAN-SST"]'),
('1.1.3', 'Asignación de recursos para el Sistema de Gestión en Seguridad y Salud en el Trabajo – SG-SST', 'PLANEAR', 'I', 'Recursos', 0.50, 1, 1, 1, '["FOR-PRE","ACT-PRE"]'),
('1.1.4', 'Afiliación al Sistema General de Riesgos Laborales', 'PLANEAR', 'I', 'Recursos', 0.50, 1, 1, 1, '["FOR-AFI"]'),
('1.1.5', 'Pago de pensión trabajadores de alto riesgo', 'PLANEAR', 'I', 'Recursos', 0.50, 0, 0, 1, '["FOR-PEN"]'),
('1.1.6', 'Conformación COPASST', 'PLANEAR', 'I', 'Recursos', 0.50, 0, 1, 1, '["ACT-COP","FOR-VOT","FOR-INS"]'),
('1.1.7', 'Capacitación COPASST', 'PLANEAR', 'I', 'Recursos', 0.50, 0, 1, 1, '["FOR-CAP","FOR-ASI"]'),
('1.1.8', 'Conformación Comité de Convivencia', 'PLANEAR', 'I', 'Recursos', 0.50, 0, 1, 1, '["ACT-CON","FOR-VOT"]'),
('1.2.1', 'Programa Capacitación promoción y prevención PYP', 'PLANEAR', 'I', 'Recursos', 2.00, 1, 1, 1, '["PRG-CAP","FOR-CRO"]'),
('1.2.2', 'Capacitación, Inducción y Reinducción en Sistema de Gestión de Seguridad y Salud en el Trabajo SG-SST, actividades de Promoción y Prevención PyP', 'PLANEAR', 'I', 'Recursos', 2.00, 1, 1, 1, '["PRO-IND","FOR-ASI","FOR-EVA"]'),
('1.2.3', 'Responsables del Sistema de Gestión de Seguridad y Salud en el Trabajo SG-SST con curso (50 horas)', 'PLANEAR', 'I', 'Recursos', 2.00, 0, 1, 1, '["FOR-CER"]'),

-- ============================================================================
-- CICLO PLANEAR - CATEGORÍA II: GESTIÓN INTEGRAL DEL SG-SST (15%)
-- ============================================================================

('2.1.1', 'Política del Sistema de Gestión de Seguridad y Salud en el Trabajo SG-SST firmada, fechada y comunicada al COPASST', 'PLANEAR', 'II', 'Gestión Integral del Sistema de Gestión de la Seguridad y la Salud en el Trabajo', 1.00, 1, 1, 1, '["POL-SST"]'),
('2.2.1', 'Objetivos definidos, claros, medibles, cuantificables, con metas, documentados, revisados del SG-SST', 'PLANEAR', 'II', 'Gestión Integral del Sistema de Gestión de la Seguridad y la Salud en el Trabajo', 1.00, 0, 1, 1, '["OBJ-SST"]'),
('2.3.1', 'Evaluación e identificación de prioridades', 'PLANEAR', 'II', 'Gestión Integral del Sistema de Gestión de la Seguridad y la Salud en el Trabajo', 1.00, 0, 0, 1, '["FOR-EVI","INF-EVI"]'),
('2.4.1', 'Plan que identifica objetivos, metas, responsabilidad, recursos con cronograma y firmado', 'PLANEAR', 'II', 'Gestión Integral del Sistema de Gestión de la Seguridad y la Salud en el Trabajo', 2.00, 1, 1, 1, '["PLA-TRA"]'),
('2.5.1', 'Archivo o retención documental del Sistema de Gestión en Seguridad y Salud en el Trabajo SG-SST', 'PLANEAR', 'II', 'Gestión Integral del Sistema de Gestión de la Seguridad y la Salud en el Trabajo', 0.00, 0, 0, 1, '["PRO-DOC","FOR-LMD"]'),
('2.6.1', 'Rendición sobre el desempeño', 'PLANEAR', 'II', 'Gestión Integral del Sistema de Gestión de la Seguridad y la Salud en el Trabajo', 1.00, 0, 0, 1, '["INF-RDC","FOR-RDC"]'),
('2.7.1', 'Matriz legal', 'PLANEAR', 'II', 'Gestión Integral del Sistema de Gestión de la Seguridad y la Salud en el Trabajo', 2.00, 0, 1, 1, '["MTZ-LEG"]'),
('2.8.1', 'Mecanismos de comunicación, auto reporte en Sistema de Gestión de Seguridad y Salud en el Trabajo SG-SST', 'PLANEAR', 'II', 'Gestión Integral del Sistema de Gestión de la Seguridad y la Salud en el Trabajo', 1.00, 0, 0, 1, '["PRO-COM","FOR-AUT"]'),
('2.9.1', 'Identificación, evaluación, para adquisición de productos y servicios en Sistema de Gestión de Seguridad y Salud en el Trabajo SG-SST', 'PLANEAR', 'II', 'Gestión Integral del Sistema de Gestión de la Seguridad y la Salud en el Trabajo', 1.00, 0, 0, 1, '["PRO-ADQ","FOR-EVP"]'),
('2.10.1', 'Evaluación y selección de proveedores y contratistas', 'PLANEAR', 'II', 'Gestión Integral del Sistema de Gestión de la Seguridad y la Salud en el Trabajo', 2.00, 0, 0, 1, '["PRO-CON","MTZ-CON","FOR-EVC"]'),
('2.11.1', 'Gestión del cambio', 'PLANEAR', 'II', 'Gestión Integral del Sistema de Gestión de la Seguridad y la Salud en el Trabajo', 1.00, 0, 0, 1, '["PRO-CAM","FOR-GDC"]'),

-- ============================================================================
-- CICLO HACER - CATEGORÍA III: GESTIÓN DE LA SALUD (20%)
-- ============================================================================

('3.1.1', 'Evaluación Médica Ocupacional', 'HACER', 'III', 'Gestión de la Salud', 1.00, 0, 1, 1, '["PRO-EMO","FOR-PRO"]'),
('3.1.2', 'Actividades de Promoción y Prevención en Salud', 'HACER', 'III', 'Gestión de la Salud', 1.00, 0, 1, 1, '["PRG-MED","PRG-PYP"]'),
('3.1.3', 'Información al médico de los perfiles de cargo', 'HACER', 'III', 'Gestión de la Salud', 1.00, 0, 0, 1, '["FOR-PRO","MTZ-PRO"]'),
('3.1.4', 'Realización de los exámenes médicos ocupacionales: pre ingreso, periódicos', 'HACER', 'III', 'Gestión de la Salud', 1.00, 0, 1, 1, '["FOR-CRO","FOR-APT"]'),
('3.1.5', 'Custodia de Historias Clínicas', 'HACER', 'III', 'Gestión de la Salud', 1.00, 0, 1, 1, '["FOR-CUS","ACT-CUS"]'),
('3.1.6', 'Restricciones y recomendaciones médico laborales', 'HACER', 'III', 'Gestión de la Salud', 1.00, 0, 0, 1, '["FOR-REC","FOR-SEG"]'),
('3.1.7', 'Estilos de vida y entornos saludables (controles tabaquismo, alcoholismo, farmacodependencia y otros)', 'HACER', 'III', 'Gestión de la Salud', 1.00, 0, 1, 1, '["PRG-EVS","POL-ADI"]'),
('3.1.8', 'Agua potable, servicios sanitarios y disposición de basuras', 'HACER', 'III', 'Gestión de la Salud', 1.00, 0, 1, 1, '["FOR-INS","FOR-HIG"]'),
('3.1.9', 'Eliminación adecuada de residuos sólidos, líquidos o gaseosos', 'HACER', 'III', 'Gestión de la Salud', 1.00, 0, 0, 1, '["PLA-RES","FOR-RES"]'),
('3.2.1', 'Reporte de los accidentes de trabajo y enfermedad laboral a la ARL, EPS y Dirección Territorial del Ministerio de Trabajo', 'HACER', 'III', 'Gestión de la Salud', 2.00, 0, 1, 1, '["PRO-REP","FOR-FUR"]'),
('3.2.2', 'Investigación de Accidentes, Incidentes y Enfermedad Laboral', 'HACER', 'III', 'Gestión de la Salud', 2.00, 0, 1, 1, '["PRO-INV","FOR-INV"]'),
('3.2.3', 'Registro y análisis estadístico de Incidentes, Accidentes de Trabajo y Enfermedad Laboral', 'HACER', 'III', 'Gestión de la Salud', 1.00, 0, 0, 1, '["FOR-EST","MTZ-EST"]'),
('3.3.1', 'Medición de la severidad de los Accidentes de Trabajo y Enfermedad Laboral', 'HACER', 'III', 'Gestión de la Salud', 1.00, 0, 0, 1, '["FOR-IND"]'),
('3.3.2', 'Medición de la frecuencia de los Incidentes, Accidentes de Trabajo y Enfermedad Laboral', 'HACER', 'III', 'Gestión de la Salud', 1.00, 0, 0, 1, '["FOR-IND"]'),
('3.3.3', 'Medición de la mortalidad de Accidentes de Trabajo y Enfermedad Laboral', 'HACER', 'III', 'Gestión de la Salud', 1.00, 0, 0, 1, '["FOR-IND"]'),
('3.3.4', 'Medición de la prevalencia de incidentes, Accidentes de Trabajo y Enfermedad Laboral', 'HACER', 'III', 'Gestión de la Salud', 1.00, 0, 0, 1, '["FOR-IND"]'),
('3.3.5', 'Medición de la incidencia de Incidentes, Accidentes de Trabajo y Enfermedad Laboral', 'HACER', 'III', 'Gestión de la Salud', 1.00, 0, 0, 1, '["FOR-IND"]'),
('3.3.6', 'Medición del ausentismo por incidentes, Accidentes de Trabajo y Enfermedad Laboral', 'HACER', 'III', 'Gestión de la Salud', 1.00, 0, 0, 1, '["FOR-AUS","MTZ-AUS"]'),

-- ============================================================================
-- CICLO HACER - CATEGORÍA IV: GESTIÓN DE PELIGROS Y RIESGOS (30%)
-- ============================================================================

('4.1.1', 'Metodología para la identificación, evaluación y valoración de peligros', 'HACER', 'IV', 'Gestión de Peligros y Riesgos', 4.00, 0, 1, 1, '["PRO-IPR","GUA-GTC"]'),
('4.1.2', 'Identificación de peligros con participación de todos los niveles de la empresa', 'HACER', 'IV', 'Gestión de Peligros y Riesgos', 4.00, 1, 1, 1, '["MTZ-PEL","FOR-REP"]'),
('4.1.3', 'Identificación y priorización de la naturaleza de los peligros (Metodología adicional, cancerígenos y otros)', 'HACER', 'IV', 'Gestión de Peligros y Riesgos', 3.00, 0, 0, 1, '["MTZ-PRI","INF-PRI"]'),
('4.1.4', 'Realización mediciones ambientales, químicos, físicos y biológicos', 'HACER', 'IV', 'Gestión de Peligros y Riesgos', 4.00, 0, 0, 1, '["INF-MED","FOR-MED"]'),
('4.2.1', 'Se implementan las medidas de prevención y control de peligros', 'HACER', 'IV', 'Gestión de Peligros y Riesgos', 2.50, 1, 1, 1, '["MTZ-CON","FOR-SEG"]'),
('4.2.2', 'Se verifica aplicación de las medidas de prevención y control', 'HACER', 'IV', 'Gestión de Peligros y Riesgos', 2.50, 0, 1, 1, '["FOR-VER","INF-VER"]'),
('4.2.3', 'Hay procedimientos, instructivos, fichas, protocolos', 'HACER', 'IV', 'Gestión de Peligros y Riesgos', 2.50, 0, 0, 1, '["PRO-SEG","INS-SEG","PRT-SEG"]'),
('4.2.4', 'Inspección con el COPASST o Vigía', 'HACER', 'IV', 'Gestión de Peligros y Riesgos', 2.50, 0, 1, 1, '["PRG-INS","FOR-INS"]'),
('4.2.5', 'Mantenimiento periódico de instalaciones, equipos, máquinas, herramientas', 'HACER', 'IV', 'Gestión de Peligros y Riesgos', 2.50, 0, 1, 1, '["PRG-MAN","FOR-MAN"]'),
('4.2.6', 'Entrega de Elementos de Protección Persona EPP, se verifica con contratistas y subcontratistas', 'HACER', 'IV', 'Gestión de Peligros y Riesgos', 2.50, 1, 1, 1, '["MTZ-EPP","FOR-ENT","PRO-EPP"]'),

-- ============================================================================
-- CICLO HACER - CATEGORÍA V: GESTIÓN DE AMENAZAS (10%)
-- ============================================================================

('5.1.1', 'Se cuenta con el Plan de Prevención y Preparación ante emergencias', 'HACER', 'V', 'Gestión de Amenazas', 5.00, 1, 1, 1, '["PLA-EME","MTZ-AME"]'),
('5.1.2', 'Brigada de prevención conformada, capacitada y dotada', 'HACER', 'V', 'Gestión de Amenazas', 5.00, 0, 1, 1, '["ACT-BRI","FOR-DOT","PRG-BRI"]'),

-- ============================================================================
-- CICLO VERIFICAR - CATEGORÍA VI: VERIFICACIÓN DEL SG-SST (5%)
-- ============================================================================

('6.1.1', 'Indicadores estructura, proceso y resultado', 'VERIFICAR', 'VI', 'Verificación del SG-SST', 1.25, 0, 0, 1, '["MTZ-IND","FOR-FIC"]'),
('6.1.2', 'Las empresa adelanta auditoría por lo menos una vez al año', 'VERIFICAR', 'VI', 'Verificación del SG-SST', 1.25, 0, 0, 1, '["PLA-AUD","INF-AUD","FOR-AUD"]'),
('6.1.3', 'Revisión anual por la alta dirección, resultados y alcance de la auditoría', 'VERIFICAR', 'VI', 'Verificación del SG-SST', 1.25, 0, 0, 1, '["ACT-REV","INF-REV"]'),
('6.1.4', 'Planificar auditoría con el COPASST', 'VERIFICAR', 'VI', 'Verificación del SG-SST', 1.25, 0, 0, 1, '["ACT-PLA","FOR-PLA"]'),

-- ============================================================================
-- CICLO ACTUAR - CATEGORÍA VII: MEJORAMIENTO (10%)
-- ============================================================================

('7.1.1', 'Definir acciones de Promoción y Prevención con base en resultados del Sistema de Gestión de Seguridad y Salud en el Trabajo SG-SST', 'ACTUAR', 'VII', 'Mejoramiento', 2.50, 0, 0, 1, '["PLA-ACC","FOR-ACC"]'),
('7.1.2', 'Toma de medidas correctivas, preventivas y de mejora', 'ACTUAR', 'VII', 'Mejoramiento', 2.50, 0, 0, 1, '["FOR-ACC","MTZ-ACC"]'),
('7.1.3', 'Ejecución de acciones preventivas, correctivas y de mejora de la investigación de incidentes, accidentes de trabajo y enfermedad laboral', 'ACTUAR', 'VII', 'Mejoramiento', 2.50, 0, 0, 1, '["FOR-SEG","INF-SEG"]'),
('7.1.4', 'Implementar medidas y acciones correctivas de autoridades y de ARL', 'ACTUAR', 'VII', 'Mejoramiento', 2.50, 0, 0, 1, '["PLA-MEJ","FOR-MEJ"]');


-- ============================================================================
-- VERIFICACIÓN DE DATOS INSERTADOS
-- ============================================================================

-- Verificar totales
SELECT
    ciclo_phva,
    COUNT(*) as total_estandares,
    SUM(peso_porcentual) as peso_total
FROM tbl_estandares_minimos
GROUP BY ciclo_phva
ORDER BY FIELD(ciclo_phva, 'PLANEAR', 'HACER', 'VERIFICAR', 'ACTUAR');

-- Verificar por categoría
SELECT
    categoria,
    categoria_nombre,
    COUNT(*) as total_estandares,
    SUM(peso_porcentual) as peso_total
FROM tbl_estandares_minimos
GROUP BY categoria, categoria_nombre
ORDER BY categoria;

-- Verificar aplicabilidad
SELECT
    SUM(aplica_7) as estandares_7,
    SUM(aplica_21) as estandares_21,
    SUM(aplica_60) as estandares_60
FROM tbl_estandares_minimos;

-- Debe mostrar: 7, 21, 60 (o valores cercanos según interpretación de la norma)
