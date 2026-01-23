-- ============================================================================
-- INSERT: Plantillas completas para todos los tipos de documentos SST
-- Tabla: tbl_doc_plantillas
-- Fecha: Enero 2026
-- ============================================================================

-- REGLAMENTOS (id_tipo = 14)
INSERT INTO `tbl_doc_plantillas` (`id_tipo`, `nombre`, `descripcion`, `codigo_sugerido`, `estructura_json`, `prompts_json`, `variables_contexto`, `activo`, `orden`) VALUES
(14, 'Reglamento de Higiene y Seguridad Industrial', 'Reglamento obligatorio según Art. 349 CST', 'REG-HSI',
'["Objeto y campo de aplicación","Obligaciones del empleador","Obligaciones de los trabajadores","Prohibiciones","Normas de higiene","Normas de seguridad","Sanciones","Vigencia"]',
'{"intro":"Genera la introducción del Reglamento de Higiene y Seguridad Industrial para {empresa}, según la normatividad colombiana vigente."}',
'empresa,nit,actividad_economica,nivel_riesgo,total_trabajadores', 1, 1),

(14, 'Reglamento del COPASST', 'Reglamento interno del Comité Paritario', 'REG-COP',
'["Objeto","Conformación","Funciones","Reuniones","Quórum y decisiones","Actas","Vigencia"]',
'{"intro":"Genera el reglamento interno del COPASST para {empresa}."}',
'empresa,nit,total_trabajadores', 1, 2),

(14, 'Reglamento del Comité de Convivencia Laboral', 'Reglamento interno del CCL', 'REG-CCL',
'["Objeto","Marco legal","Conformación","Funciones","Reuniones","Confidencialidad","Procedimiento de quejas","Vigencia"]',
'{"intro":"Genera el reglamento del Comité de Convivencia Laboral para {empresa}."}',
'empresa,nit,total_trabajadores', 1, 3);

-- POLITICAS ADICIONALES (id_tipo = 1)
INSERT INTO `tbl_doc_plantillas` (`id_tipo`, `nombre`, `descripcion`, `codigo_sugerido`, `estructura_json`, `prompts_json`, `variables_contexto`, `activo`, `orden`) VALUES
(1, 'Política de Prevención de Acoso Laboral', 'Política según Ley 1010 de 2006', 'POL-ACO',
'["Declaración","Definiciones","Conductas prohibidas","Compromisos","Mecanismos de denuncia","Comunicación"]',
'{"intro":"Genera la política de prevención de acoso laboral para {empresa}."}',
'empresa,nit,representante_legal', 1, 3),

(1, 'Política de Seguridad Vial - PESV', 'Política del Plan Estratégico de Seguridad Vial', 'POL-PESV',
'["Declaración","Objetivos","Alcance","Compromisos de la dirección","Compromisos de los conductores","Comunicación"]',
'{"intro":"Genera la política de seguridad vial para {empresa}."}',
'empresa,nit,representante_legal', 1, 4),

(1, 'Política de Elementos de Protección Personal', 'Política de uso y dotación de EPP', 'POL-EPP',
'["Declaración","Objetivos","Responsabilidades","Compromisos","Comunicación"]',
'{"intro":"Genera la política de elementos de protección personal para {empresa}."}',
'empresa,nit,peligros_identificados', 1, 5);

-- PROGRAMAS ADICIONALES (id_tipo = 3)
INSERT INTO `tbl_doc_plantillas` (`id_tipo`, `nombre`, `descripcion`, `codigo_sugerido`, `estructura_json`, `prompts_json`, `variables_contexto`, `activo`, `orden`) VALUES
(3, 'Programa de Estilos de Vida Saludable', 'Promoción de hábitos saludables', 'PRG-EVS',
'["Introducción","Objetivos","Alcance","Marco normativo","Diagnóstico","Actividades","Cronograma","Indicadores","Responsables","Recursos","Seguimiento","Registros"]',
'{"intro":"Genera el programa de estilos de vida saludable para {empresa}."}',
'empresa,total_trabajadores,actividad_economica', 1, 3),

(3, 'Programa de Riesgo Psicosocial', 'Prevención de factores de riesgo psicosocial', 'PRG-PSI',
'["Introducción","Objetivos","Alcance","Marco normativo","Diagnóstico","Intervención","Cronograma","Indicadores","Responsables","Recursos","Seguimiento","Registros"]',
'{"intro":"Genera el programa de prevención de riesgo psicosocial para {empresa}, según Resolución 2646/2008."}',
'empresa,total_trabajadores,actividad_economica', 1, 4),

(3, 'Programa de Orden y Aseo', 'Metodología 5S y orden en el trabajo', 'PRG-OYA',
'["Introducción","Objetivos","Alcance","Metodología 5S","Actividades","Cronograma","Indicadores","Responsables","Seguimiento","Registros"]',
'{"intro":"Genera el programa de orden y aseo para {empresa}."}',
'empresa,numero_sedes', 1, 5),

(3, 'Programa de Mantenimiento Preventivo', 'Mantenimiento de equipos e instalaciones', 'PRG-MTO',
'["Introducción","Objetivos","Alcance","Inventario de equipos","Actividades de mantenimiento","Cronograma","Indicadores","Responsables","Recursos","Registros"]',
'{"intro":"Genera el programa de mantenimiento preventivo para {empresa}."}',
'empresa,actividad_economica', 1, 6);

-- PROCEDIMIENTOS ADICIONALES (id_tipo = 5)
INSERT INTO `tbl_doc_plantillas` (`id_tipo`, `nombre`, `descripcion`, `codigo_sugerido`, `estructura_json`, `prompts_json`, `variables_contexto`, `activo`, `orden`) VALUES
(5, 'Procedimiento de Auditorías Internas', 'Auditorías del SG-SST', 'PRO-AUD',
'["Objetivo","Alcance","Definiciones","Responsables","Planificación","Ejecución","Informe","Seguimiento","Documentos relacionados","Control de cambios"]',
'{"intro":"Genera el procedimiento de auditorías internas del SG-SST para {empresa}."}',
'empresa', 1, 3),

(5, 'Procedimiento de Acciones Correctivas y Preventivas', 'Gestión de no conformidades', 'PRO-ACP',
'["Objetivo","Alcance","Definiciones","Responsables","Identificación","Análisis de causas","Acciones","Seguimiento","Documentos relacionados","Control de cambios"]',
'{"intro":"Genera el procedimiento de acciones correctivas y preventivas para {empresa}."}',
'empresa', 1, 4),

(5, 'Procedimiento de Comunicaciones', 'Comunicación interna y externa SST', 'PRO-COM',
'["Objetivo","Alcance","Definiciones","Responsables","Comunicación interna","Comunicación externa","Medios","Registros","Control de cambios"]',
'{"intro":"Genera el procedimiento de comunicaciones del SG-SST para {empresa}."}',
'empresa,total_trabajadores', 1, 5),

(5, 'Procedimiento de Control Documental', 'Control de documentos y registros', 'PRO-DOC',
'["Objetivo","Alcance","Definiciones","Responsables","Elaboración","Revisión y aprobación","Distribución","Control de cambios","Conservación","Disposición"]',
'{"intro":"Genera el procedimiento de control documental del SG-SST para {empresa}."}',
'empresa', 1, 6);

-- PLANES ADICIONALES (id_tipo = 4)
INSERT INTO `tbl_doc_plantillas` (`id_tipo`, `nombre`, `descripcion`, `codigo_sugerido`, `estructura_json`, `prompts_json`, `variables_contexto`, `activo`, `orden`) VALUES
(4, 'Plan de Capacitación Anual', 'Cronograma de capacitaciones SST', 'PLA-CAP',
'["Introducción","Objetivos","Alcance","Diagnóstico de necesidades","Temas de capacitación","Cronograma","Metodología","Evaluación","Recursos","Indicadores"]',
'{"intro":"Genera el plan de capacitación anual en SST para {empresa}."}',
'empresa,total_trabajadores,peligros_identificados,nivel_riesgo', 1, 3),

(4, 'Plan de Mejoramiento', 'Acciones de mejora del SG-SST', 'PLA-MEJ',
'["Introducción","Diagnóstico","Hallazgos","Acciones de mejora","Responsables","Cronograma","Recursos","Seguimiento","Indicadores"]',
'{"intro":"Genera el plan de mejoramiento del SG-SST para {empresa}."}',
'empresa', 1, 4);

-- MANUALES (id_tipo = 7)
INSERT INTO `tbl_doc_plantillas` (`id_tipo`, `nombre`, `descripcion`, `codigo_sugerido`, `estructura_json`, `prompts_json`, `variables_contexto`, `activo`, `orden`) VALUES
(7, 'Manual del Sistema de Gestión SST', 'Manual principal del SG-SST', 'MAN-SST',
'["Información de la empresa","Política y objetivos","Organización del SG-SST","Planificación","Aplicación","Verificación","Mejora continua","Documentos y registros"]',
'{"intro":"Genera el manual del SG-SST para {empresa}, según Decreto 1072/2015."}',
'empresa,nit,actividad_economica,nivel_riesgo,total_trabajadores,representante_legal', 1, 1),

(7, 'Manual de Funciones y Responsabilidades SST', 'Roles y responsabilidades en SST', 'MAN-FUN',
'["Introducción","Responsabilidades de la alta dirección","Responsabilidades de mandos medios","Responsabilidades de trabajadores","Responsabilidades del COPASST","Responsabilidades del responsable SST"]',
'{"intro":"Genera el manual de funciones y responsabilidades SST para {empresa}."}',
'empresa,total_trabajadores', 1, 2),

(7, 'Manual de Contratistas y Proveedores', 'Requisitos SST para terceros', 'MAN-CON',
'["Objetivo","Alcance","Requisitos de SST","Documentación requerida","Evaluación","Seguimiento","Sanciones"]',
'{"intro":"Genera el manual de contratistas y proveedores para {empresa}."}',
'empresa,actividad_economica', 1, 3);

-- FORMATOS (id_tipo = 9)
INSERT INTO `tbl_doc_plantillas` (`id_tipo`, `nombre`, `descripcion`, `codigo_sugerido`, `estructura_json`, `prompts_json`, `variables_contexto`, `activo`, `orden`) VALUES
(9, 'Formato de Inspección de Seguridad', 'Lista de chequeo para inspecciones', 'FOR-INS',
'["Encabezado","Área inspeccionada","Items de verificación","Hallazgos","Acciones","Firmas"]',
'{"intro":"Genera un formato de inspección de seguridad para {empresa}."}',
'empresa,peligros_identificados', 1, 1),

(9, 'Formato de Reporte de Incidentes', 'Registro de incidentes y accidentes', 'FOR-INC',
'["Datos del evento","Descripción","Análisis de causas","Acciones correctivas","Seguimiento","Firmas"]',
'{"intro":"Genera un formato de reporte de incidentes para {empresa}."}',
'empresa', 1, 2),

(9, 'Formato de Entrega de EPP', 'Control de dotación de EPP', 'FOR-EPP',
'["Datos del trabajador","EPP entregado","Capacitación","Compromisos","Firmas"]',
'{"intro":"Genera un formato de entrega de EPP para {empresa}."}',
'empresa', 1, 3),

(9, 'Formato de Asistencia a Capacitación', 'Registro de asistencia', 'FOR-ASI',
'["Datos de la capacitación","Listado de asistentes","Evaluación","Firmas"]',
'{"intro":"Genera un formato de asistencia a capacitación para {empresa}."}',
'empresa', 1, 4);

-- ACTAS (id_tipo = 11)
INSERT INTO `tbl_doc_plantillas` (`id_tipo`, `nombre`, `descripcion`, `codigo_sugerido`, `estructura_json`, `prompts_json`, `variables_contexto`, `activo`, `orden`) VALUES
(11, 'Acta de Reunión del COPASST', 'Acta de reunión mensual', 'ACT-COP',
'["Información general","Asistentes","Verificación de quórum","Orden del día","Desarrollo","Compromisos","Firmas"]',
'{"intro":"Genera una plantilla de acta de reunión del COPASST para {empresa}."}',
'empresa', 1, 1),

(11, 'Acta de Reunión del Comité de Convivencia', 'Acta de reunión trimestral', 'ACT-CCL',
'["Información general","Asistentes","Verificación de quórum","Orden del día","Casos revisados","Compromisos","Firmas"]',
'{"intro":"Genera una plantilla de acta del Comité de Convivencia para {empresa}."}',
'empresa', 1, 2),

(11, 'Acta de Conformación de Brigada', 'Constitución de brigada de emergencias', 'ACT-BRI',
'["Información general","Asistentes","Elección de brigadistas","Funciones asignadas","Compromisos","Firmas"]',
'{"intro":"Genera un acta de conformación de brigada de emergencias para {empresa}."}',
'empresa,total_trabajadores', 1, 3);

-- INFORMES (id_tipo = 8)
INSERT INTO `tbl_doc_plantillas` (`id_tipo`, `nombre`, `descripcion`, `codigo_sugerido`, `estructura_json`, `prompts_json`, `variables_contexto`, `activo`, `orden`) VALUES
(8, 'Informe de Revisión por la Alta Dirección', 'Revisión anual del SG-SST', 'INF-RAD',
'["Introducción","Objetivos","Entradas de la revisión","Análisis de resultados","Conclusiones","Decisiones y acciones","Firmas"]',
'{"intro":"Genera un informe de revisión por la alta dirección del SG-SST para {empresa}."}',
'empresa,representante_legal', 1, 1),

(8, 'Informe de Auditoría Interna', 'Resultados de auditoría del SG-SST', 'INF-AUD',
'["Introducción","Objetivos","Alcance","Metodología","Hallazgos","No conformidades","Oportunidades de mejora","Conclusiones","Recomendaciones"]',
'{"intro":"Genera un informe de auditoría interna del SG-SST para {empresa}."}',
'empresa', 1, 2);

-- MATRICES (id_tipo = 10)
INSERT INTO `tbl_doc_plantillas` (`id_tipo`, `nombre`, `descripcion`, `codigo_sugerido`, `estructura_json`, `prompts_json`, `variables_contexto`, `activo`, `orden`) VALUES
(10, 'Matriz de Identificación de Peligros (IPEVR)', 'Matriz de peligros y riesgos', 'MTZ-IPE',
'["Proceso","Actividad","Peligro","Efectos posibles","Controles existentes","Evaluación del riesgo","Medidas de intervención"]',
'{"intro":"Genera la estructura de matriz IPEVR para {empresa}, según GTC 45."}',
'empresa,actividad_economica,peligros_identificados', 1, 1),

(10, 'Matriz de Requisitos Legales', 'Identificación de requisitos legales SST', 'MTZ-LEG',
'["Norma","Artículo aplicable","Requisito","Responsable","Evidencia de cumplimiento","Estado"]',
'{"intro":"Genera la estructura de matriz de requisitos legales SST para {empresa}."}',
'empresa,actividad_economica,nivel_riesgo', 1, 2);

-- OBJETIVOS (id_tipo = 2)
INSERT INTO `tbl_doc_plantillas` (`id_tipo`, `nombre`, `descripcion`, `codigo_sugerido`, `estructura_json`, `prompts_json`, `variables_contexto`, `activo`, `orden`) VALUES
(2, 'Objetivos del Sistema de Gestión SST', 'Objetivos e indicadores del SG-SST', 'OBJ-SST',
'["Objetivo general","Objetivos específicos","Metas","Indicadores","Responsables","Seguimiento"]',
'{"intro":"Genera los objetivos del SG-SST para {empresa}, alineados con la política y los peligros identificados."}',
'empresa,peligros_identificados,nivel_riesgo', 1, 1);

-- GUIAS (id_tipo = 12)
INSERT INTO `tbl_doc_plantillas` (`id_tipo`, `nombre`, `descripcion`, `codigo_sugerido`, `estructura_json`, `prompts_json`, `variables_contexto`, `activo`, `orden`) VALUES
(12, 'Guía de Trabajo Seguro', 'Instrucciones de seguridad para tareas críticas', 'GUA-SEG',
'["Objetivo","Alcance","EPP requerido","Instrucciones","Precauciones","Referencias"]',
'{"intro":"Genera una guía de trabajo seguro para {empresa}."}',
'empresa,peligros_identificados', 1, 1);

-- INSTRUCTIVOS (id_tipo = 13)
INSERT INTO `tbl_doc_plantillas` (`id_tipo`, `nombre`, `descripcion`, `codigo_sugerido`, `estructura_json`, `prompts_json`, `variables_contexto`, `activo`, `orden`) VALUES
(13, 'Instructivo de Uso de EPP', 'Instrucciones para uso correcto de EPP', 'INS-EPP',
'["Objetivo","Alcance","Instrucciones paso a paso","Precauciones","Mantenimiento"]',
'{"intro":"Genera un instructivo de uso de EPP para {empresa}."}',
'empresa', 1, 1);

-- PROTOCOLOS (id_tipo = 6)
INSERT INTO `tbl_doc_plantillas` (`id_tipo`, `nombre`, `descripcion`, `codigo_sugerido`, `estructura_json`, `prompts_json`, `variables_contexto`, `activo`, `orden`) VALUES
(6, 'Protocolo de Bioseguridad', 'Medidas de bioseguridad', 'PRT-BIO',
'["Objetivo","Alcance","Definiciones","Medidas de prevención","Procedimientos","Registros","Referencias"]',
'{"intro":"Genera un protocolo de bioseguridad para {empresa}."}',
'empresa,actividad_economica', 1, 1),

(6, 'Protocolo de Atención de Emergencias', 'Respuesta ante emergencias', 'PRT-EME',
'["Objetivo","Alcance","Tipos de emergencia","Procedimientos de respuesta","Evacuación","Comunicaciones","Referencias"]',
'{"intro":"Genera un protocolo de atención de emergencias para {empresa}."}',
'empresa,numero_sedes', 1, 2);
