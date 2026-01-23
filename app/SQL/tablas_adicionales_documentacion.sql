-- ============================================================================
-- TABLAS ADICIONALES PARA MÓDULO DOCUMENTACIÓN SST
-- Ejecutar después de: modulo_documentacion_sst.sql
-- ============================================================================

USE empresas_sst;

-- ============================================================================
-- TABLA: Plantillas de documentos
-- ============================================================================

CREATE TABLE IF NOT EXISTS `tbl_doc_plantillas` (
    `id_plantilla` INT NOT NULL AUTO_INCREMENT,
    `id_tipo` INT NOT NULL,
    `nombre` VARCHAR(255) NOT NULL,
    `descripcion` TEXT NULL,
    `codigo_sugerido` VARCHAR(20) NULL COMMENT 'Prefijo sugerido para código',
    `estructura_json` LONGTEXT NULL COMMENT 'JSON con estructura de secciones',
    `prompts_json` LONGTEXT NULL COMMENT 'JSON con prompts por sección',
    `variables_contexto` TEXT NULL COMMENT 'JSON con variables requeridas del contexto',
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `orden` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_plantilla`),
    KEY `idx_tipo` (`id_tipo`),
    CONSTRAINT `fk_plantilla_tipo` FOREIGN KEY (`id_tipo`) REFERENCES `tbl_doc_tipos` (`id_tipo`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================================
-- TABLA: Relación entre documentos y estándares
-- ============================================================================

CREATE TABLE IF NOT EXISTS `tbl_doc_estandar_documentos` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `id_documento` INT NOT NULL,
    `id_estandar` INT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_doc_estandar` (`id_documento`, `id_estandar`),
    KEY `idx_documento` (`id_documento`),
    KEY `idx_estandar` (`id_estandar`),
    CONSTRAINT `fk_de_documento` FOREIGN KEY (`id_documento`) REFERENCES `tbl_doc_documentos` (`id_documento`) ON DELETE CASCADE,
    CONSTRAINT `fk_de_estandar` FOREIGN KEY (`id_estandar`) REFERENCES `tbl_estandares_minimos` (`id_estandar`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================================
-- TABLA: Historial de exportaciones
-- ============================================================================

CREATE TABLE IF NOT EXISTS `tbl_doc_exportaciones` (
    `id_exportacion` INT NOT NULL AUTO_INCREMENT,
    `id_documento` INT NOT NULL,
    `formato` ENUM('pdf', 'word', 'excel', 'zip') NOT NULL DEFAULT 'pdf',
    `exportado_por` INT NULL,
    `ip_address` VARCHAR(45) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_exportacion`),
    KEY `idx_documento` (`id_documento`),
    CONSTRAINT `fk_exp_documento` FOREIGN KEY (`id_documento`) REFERENCES `tbl_doc_documentos` (`id_documento`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================================
-- INSERTS: Plantillas básicas de documentos SST
-- ============================================================================

INSERT INTO `tbl_doc_plantillas` (`id_tipo`, `nombre`, `descripcion`, `codigo_sugerido`, `estructura_json`, `activo`, `orden`) VALUES
-- Políticas
(1, 'Política de Seguridad y Salud en el Trabajo', 'Política principal del SG-SST según Decreto 1072/2015', 'POL-SST',
'[{"numero":1,"nombre":"Declaración de compromiso","descripcion":"Declaración de la alta dirección"},{"numero":2,"nombre":"Objetivos de la política","descripcion":"Objetivos que busca alcanzar"},{"numero":3,"nombre":"Alcance","descripcion":"A quién aplica la política"},{"numero":4,"nombre":"Compromisos específicos","descripcion":"Compromisos de la empresa"},{"numero":5,"nombre":"Comunicación y revisión","descripcion":"Cómo se comunica y revisa"}]',
1, 1),

(1, 'Política de No Alcohol, Drogas y Tabaco', 'Política de prevención de consumo', 'POL-ADT',
'[{"numero":1,"nombre":"Declaración","descripcion":"Posición de la empresa"},{"numero":2,"nombre":"Objetivos","descripcion":"Objetivos de la política"},{"numero":3,"nombre":"Alcance","descripcion":"Aplicabilidad"},{"numero":4,"nombre":"Prohibiciones","descripcion":"Conductas prohibidas"},{"numero":5,"nombre":"Medidas de prevención","descripcion":"Acciones preventivas"},{"numero":6,"nombre":"Consecuencias","descripcion":"Sanciones aplicables"}]',
1, 2),

-- Programas
(3, 'Programa de Capacitación y Entrenamiento SST', 'Programa de formación del personal', 'PRG-CAP',
'[{"numero":1,"nombre":"Introducción","descripcion":"Contexto del programa"},{"numero":2,"nombre":"Objetivos","descripcion":"Objetivo general y específicos"},{"numero":3,"nombre":"Alcance","descripcion":"Personal cubierto"},{"numero":4,"nombre":"Marco normativo","descripcion":"Normatividad aplicable"},{"numero":5,"nombre":"Definiciones","descripcion":"Términos clave"},{"numero":6,"nombre":"Diagnóstico de necesidades","descripcion":"Identificación de necesidades de formación"},{"numero":7,"nombre":"Contenidos temáticos","descripcion":"Temas a desarrollar"},{"numero":8,"nombre":"Metodología","descripcion":"Métodos de capacitación"},{"numero":9,"nombre":"Cronograma","descripcion":"Programación de actividades"},{"numero":10,"nombre":"Indicadores","descripcion":"Métricas de seguimiento"},{"numero":11,"nombre":"Responsables","descripcion":"Roles y responsabilidades"},{"numero":12,"nombre":"Recursos","descripcion":"Recursos necesarios"},{"numero":13,"nombre":"Registros","descripcion":"Documentación de soporte"}]',
1, 3),

(3, 'Programa de Vigilancia Epidemiológica', 'PVE para riesgos prioritarios', 'PRG-PVE',
'[{"numero":1,"nombre":"Introducción","descripcion":"Justificación del programa"},{"numero":2,"nombre":"Objetivos","descripcion":"Objetivo general y específicos"},{"numero":3,"nombre":"Alcance","descripcion":"Población objeto"},{"numero":4,"nombre":"Marco normativo","descripcion":"Normatividad aplicable"},{"numero":5,"nombre":"Definiciones","descripcion":"Términos clave"},{"numero":6,"nombre":"Diagnóstico","descripcion":"Condiciones de salud identificadas"},{"numero":7,"nombre":"Actividades de intervención","descripcion":"Acciones preventivas y correctivas"},{"numero":8,"nombre":"Cronograma","descripcion":"Programación anual"},{"numero":9,"nombre":"Indicadores","descripcion":"Métricas de seguimiento"},{"numero":10,"nombre":"Responsables","descripcion":"Roles y responsabilidades"},{"numero":11,"nombre":"Recursos","descripcion":"Recursos necesarios"},{"numero":12,"nombre":"Seguimiento y evaluación","descripcion":"Mecanismos de control"},{"numero":13,"nombre":"Registros","descripcion":"Documentación de soporte"}]',
1, 4),

-- Planes
(4, 'Plan de Trabajo Anual SST', 'Plan de trabajo según Art. 2.2.4.6.8 Decreto 1072', 'PLA-PTA',
'[{"numero":1,"nombre":"Introducción","descripcion":"Presentación del plan"},{"numero":2,"nombre":"Objetivos","descripcion":"Objetivos del plan anual"},{"numero":3,"nombre":"Alcance","descripcion":"Cobertura del plan"},{"numero":4,"nombre":"Marco normativo","descripcion":"Base legal"},{"numero":5,"nombre":"Diagnóstico inicial","descripcion":"Estado actual del SG-SST"},{"numero":6,"nombre":"Metas","descripcion":"Metas para el período"},{"numero":7,"nombre":"Actividades y cronograma","descripcion":"Detalle de actividades por mes"},{"numero":8,"nombre":"Presupuesto","descripcion":"Recursos financieros"},{"numero":9,"nombre":"Indicadores","descripcion":"Métricas de cumplimiento"},{"numero":10,"nombre":"Seguimiento","descripcion":"Mecanismos de verificación"}]',
1, 5),

(4, 'Plan de Emergencias', 'Plan de preparación y respuesta ante emergencias', 'PLA-EME',
'[{"numero":1,"nombre":"Información general","descripcion":"Datos de la empresa"},{"numero":2,"nombre":"Objetivos","descripcion":"Objetivos del plan"},{"numero":3,"nombre":"Alcance","descripcion":"Cobertura"},{"numero":4,"nombre":"Marco normativo","descripcion":"Normatividad aplicable"},{"numero":5,"nombre":"Análisis de amenazas y vulnerabilidad","descripcion":"Identificación de riesgos"},{"numero":6,"nombre":"Organización para emergencias","descripcion":"Estructura de respuesta"},{"numero":7,"nombre":"Procedimientos operativos","descripcion":"Protocolos de actuación"},{"numero":8,"nombre":"Plan de evacuación","descripcion":"Rutas y puntos de encuentro"},{"numero":9,"nombre":"Simulacros","descripcion":"Programación de ejercicios"},{"numero":10,"nombre":"Recursos","descripcion":"Equipos y materiales"}]',
1, 6),

-- Procedimientos
(5, 'Procedimiento de Identificación de Peligros y Valoración de Riesgos', 'IPVR según GTC 45', 'PRO-IPVR',
'[{"numero":1,"nombre":"Objetivo","descripcion":"Propósito del procedimiento"},{"numero":2,"nombre":"Alcance","descripcion":"Aplicabilidad"},{"numero":3,"nombre":"Definiciones","descripcion":"Términos clave"},{"numero":4,"nombre":"Responsables","descripcion":"Roles involucrados"},{"numero":5,"nombre":"Metodología","descripcion":"Pasos para identificación y valoración"},{"numero":6,"nombre":"Criterios de valoración","descripcion":"Escalas y criterios"},{"numero":7,"nombre":"Documentos relacionados","descripcion":"Formatos y registros"},{"numero":8,"nombre":"Control de cambios","descripcion":"Historial de versiones"}]',
1, 7),

(5, 'Procedimiento de Reporte e Investigación de Incidentes y Accidentes', 'Investigación según Resolución 1401/2007', 'PRO-INV',
'[{"numero":1,"nombre":"Objetivo","descripcion":"Propósito del procedimiento"},{"numero":2,"nombre":"Alcance","descripcion":"Tipos de eventos cubiertos"},{"numero":3,"nombre":"Definiciones","descripcion":"Términos clave"},{"numero":4,"nombre":"Responsables","descripcion":"Roles involucrados"},{"numero":5,"nombre":"Procedimiento de reporte","descripcion":"Pasos para reportar"},{"numero":6,"nombre":"Procedimiento de investigación","descripcion":"Metodología de investigación"},{"numero":7,"nombre":"Documentos relacionados","descripcion":"Formatos FURAT, informes"},{"numero":8,"nombre":"Control de cambios","descripcion":"Historial de versiones"}]',
1, 8);


-- ============================================================================
-- MENSAJE FINAL
-- ============================================================================
SELECT 'Tablas adicionales creadas exitosamente' AS mensaje;
SELECT COUNT(*) AS plantillas_insertadas FROM tbl_doc_plantillas;
