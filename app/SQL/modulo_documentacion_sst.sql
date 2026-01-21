-- ============================================================================
-- MÓDULO DE DOCUMENTACIÓN SST - EnterpriseSST
-- Basado en: Resolución 0312 de 2019
-- Fecha: Enero 2026
-- ============================================================================

-- ============================================================================
-- PARTE 1: CATÁLOGO DE ESTÁNDARES MÍNIMOS (Resolución 0312/2019)
-- ============================================================================

-- Tabla: Catálogo de los 60 estándares mínimos
CREATE TABLE `tbl_estandares_minimos` (
    `id_estandar` INT NOT NULL AUTO_INCREMENT,
    `item` VARCHAR(10) NOT NULL COMMENT 'Número del estándar: 1.1.1, 2.1.1, etc.',
    `nombre` VARCHAR(255) NOT NULL COMMENT 'Nombre del estándar',
    `ciclo_phva` ENUM('PLANEAR', 'HACER', 'VERIFICAR', 'ACTUAR') NOT NULL,
    `categoria` ENUM('I', 'II', 'III', 'IV', 'V', 'VI', 'VII') NOT NULL COMMENT 'I=Recursos, II=Gestión Integral, etc.',
    `categoria_nombre` VARCHAR(100) NOT NULL COMMENT 'Nombre de la categoría',
    `peso_porcentual` DECIMAL(4,2) NOT NULL COMMENT 'Peso en la calificación total',
    `aplica_7` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Aplica para 7 estándares',
    `aplica_21` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Aplica para 21 estándares',
    `aplica_60` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Aplica para 60 estándares',
    `documentos_sugeridos` TEXT NULL COMMENT 'JSON con tipos de documentos sugeridos',
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_estandar`),
    UNIQUE KEY `uk_item` (`item`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================================
-- PARTE 2: CONTEXTO EXTENDIDO DEL CLIENTE
-- ============================================================================

-- Tabla: Extensión de contexto SST del cliente
CREATE TABLE `tbl_cliente_contexto_sst` (
    `id_contexto` INT NOT NULL AUTO_INCREMENT,
    `id_cliente` INT NOT NULL,

    -- Clasificación empresarial
    `sector_economico` VARCHAR(100) NULL,
    `codigo_ciiu_secundario` VARCHAR(10) NULL,
    `nivel_riesgo_arl` ENUM('I', 'II', 'III', 'IV', 'V') NOT NULL DEFAULT 'I',
    `clase_riesgo_cotizacion` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1-5',
    `arl_actual` VARCHAR(100) NULL,

    -- Tamaño y estructura
    `total_trabajadores` INT NOT NULL DEFAULT 1,
    `trabajadores_directos` INT NOT NULL DEFAULT 1,
    `trabajadores_temporales` INT NOT NULL DEFAULT 0,
    `contratistas_permanentes` INT NOT NULL DEFAULT 0,
    `numero_sedes` INT NOT NULL DEFAULT 1,
    `turnos_trabajo` VARCHAR(255) NULL COMMENT 'JSON: ["diurno", "nocturno", "rotativo"]',

    -- Información SST
    `responsable_sgsst_nombre` VARCHAR(255) NULL,
    `responsable_sgsst_cargo` VARCHAR(100) NULL,
    `responsable_sgsst_cedula` VARCHAR(20) NULL,
    `licencia_sst_numero` VARCHAR(50) NULL,
    `licencia_sst_vigencia` DATE NULL,
    `tiene_copasst` TINYINT(1) NOT NULL DEFAULT 0,
    `tiene_vigia_sst` TINYINT(1) NOT NULL DEFAULT 0,
    `tiene_comite_convivencia` TINYINT(1) NOT NULL DEFAULT 0,
    `tiene_brigada_emergencias` TINYINT(1) NOT NULL DEFAULT 0,

    -- Peligros identificados (JSON array)
    `peligros_identificados` TEXT NULL COMMENT 'JSON array de peligros',

    -- Cálculo automático
    `estandares_aplicables` TINYINT NOT NULL DEFAULT 60 COMMENT '7, 21 o 60',

    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_contexto`),
    UNIQUE KEY `uk_cliente` (`id_cliente`),
    CONSTRAINT `fk_contexto_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes` (`id_cliente`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Tabla: Sedes del cliente
CREATE TABLE `tbl_cliente_sedes` (
    `id_sede` INT NOT NULL AUTO_INCREMENT,
    `id_cliente` INT NOT NULL,
    `nombre_sede` VARCHAR(255) NOT NULL,
    `direccion` VARCHAR(255) NOT NULL,
    `ciudad` VARCHAR(100) NOT NULL,
    `departamento` VARCHAR(100) NULL,
    `trabajadores_sede` INT NOT NULL DEFAULT 1,
    `actividades_principales` TEXT NULL,
    `es_sede_principal` TINYINT(1) NOT NULL DEFAULT 0,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_sede`),
    KEY `idx_cliente` (`id_cliente`),
    CONSTRAINT `fk_sede_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes` (`id_cliente`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Tabla: Historial de cambios de contexto
CREATE TABLE `tbl_cliente_contexto_historial` (
    `id_historial` INT NOT NULL AUTO_INCREMENT,
    `id_cliente` INT NOT NULL,
    `campo_modificado` VARCHAR(100) NOT NULL,
    `valor_anterior` VARCHAR(255) NULL,
    `valor_nuevo` VARCHAR(255) NULL,
    `impacto` VARCHAR(255) NULL COMMENT 'Ej: Cambio de 7 a 21 estándares',
    `usuario_id` INT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_historial`),
    KEY `idx_cliente` (`id_cliente`),
    CONSTRAINT `fk_historial_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes` (`id_cliente`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================================
-- PARTE 3: ESTRUCTURA DE CARPETAS
-- ============================================================================

-- Tabla: Estructura de carpetas por cliente
CREATE TABLE `tbl_doc_carpetas` (
    `id_carpeta` INT NOT NULL AUTO_INCREMENT,
    `id_cliente` INT NOT NULL,
    `id_carpeta_padre` INT NULL COMMENT 'NULL = carpeta raíz',
    `nombre` VARCHAR(255) NOT NULL,
    `codigo` VARCHAR(50) NULL COMMENT 'Ej: 1.1, 2.3.1',
    `orden` INT NOT NULL DEFAULT 0,
    `tipo` ENUM('phva', 'categoria', 'estandar', 'custom') NOT NULL DEFAULT 'custom',
    `id_estandar` INT NULL COMMENT 'Referencia al estándar si aplica',
    `icono` VARCHAR(50) NULL DEFAULT 'folder',
    `color` VARCHAR(20) NULL,
    `visible` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_carpeta`),
    KEY `idx_cliente` (`id_cliente`),
    KEY `idx_padre` (`id_carpeta_padre`),
    KEY `idx_estandar` (`id_estandar`),
    CONSTRAINT `fk_carpeta_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes` (`id_cliente`) ON DELETE CASCADE,
    CONSTRAINT `fk_carpeta_padre` FOREIGN KEY (`id_carpeta_padre`) REFERENCES `tbl_doc_carpetas` (`id_carpeta`) ON DELETE CASCADE,
    CONSTRAINT `fk_carpeta_estandar` FOREIGN KEY (`id_estandar`) REFERENCES `tbl_estandares_minimos` (`id_estandar`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================================
-- PARTE 4: TIPOS DE DOCUMENTO
-- ============================================================================

-- Tabla: Catálogo de tipos de documento
CREATE TABLE `tbl_doc_tipos` (
    `id_tipo` INT NOT NULL AUTO_INCREMENT,
    `codigo` VARCHAR(10) NOT NULL COMMENT 'PRG, PRO, POL, PLA, etc.',
    `nombre` VARCHAR(100) NOT NULL,
    `descripcion` TEXT NULL,
    `tiene_secciones` TINYINT(1) NOT NULL DEFAULT 1,
    `numero_secciones` INT NOT NULL DEFAULT 0,
    `estructura_secciones` TEXT NULL COMMENT 'JSON con estructura de secciones',
    `requiere_firma_cliente` TINYINT(1) NOT NULL DEFAULT 1,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_tipo`),
    UNIQUE KEY `uk_codigo` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================================
-- PARTE 5: DOCUMENTOS
-- ============================================================================

-- Tabla: Documentos
CREATE TABLE `tbl_doc_documentos` (
    `id_documento` INT NOT NULL AUTO_INCREMENT,
    `id_cliente` INT NOT NULL,
    `id_carpeta` INT NULL,
    `id_tipo` INT NOT NULL,

    -- Identificación
    `codigo` VARCHAR(50) NOT NULL COMMENT 'PRG-CAP-001',
    `nombre` VARCHAR(255) NOT NULL,
    `descripcion` TEXT NULL,

    -- Control documental
    `version_actual` VARCHAR(10) NOT NULL DEFAULT '1.0',
    `estado` ENUM('borrador', 'en_revision', 'pendiente_firma', 'aprobado', 'obsoleto') NOT NULL DEFAULT 'borrador',
    `fecha_emision` DATE NULL,
    `fecha_aprobacion` DATE NULL,
    `fecha_proxima_revision` DATE NULL,

    -- Responsables
    `elaboro_usuario_id` INT NULL,
    `reviso_usuario_id` INT NULL,
    `aprobo_contacto_id` INT NULL COMMENT 'Contacto del cliente que aprueba',

    -- Relación con estándares
    `estandares_relacionados` TEXT NULL COMMENT 'JSON array de id_estandar',

    -- Metadata
    `tags` VARCHAR(255) NULL,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_documento`),
    UNIQUE KEY `uk_cliente_codigo` (`id_cliente`, `codigo`),
    KEY `idx_cliente` (`id_cliente`),
    KEY `idx_carpeta` (`id_carpeta`),
    KEY `idx_tipo` (`id_tipo`),
    KEY `idx_estado` (`estado`),
    CONSTRAINT `fk_doc_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes` (`id_cliente`) ON DELETE CASCADE,
    CONSTRAINT `fk_doc_carpeta` FOREIGN KEY (`id_carpeta`) REFERENCES `tbl_doc_carpetas` (`id_carpeta`) ON DELETE SET NULL,
    CONSTRAINT `fk_doc_tipo` FOREIGN KEY (`id_tipo`) REFERENCES `tbl_doc_tipos` (`id_tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Tabla: Secciones del documento
CREATE TABLE `tbl_doc_secciones` (
    `id_seccion` INT NOT NULL AUTO_INCREMENT,
    `id_documento` INT NOT NULL,
    `numero_seccion` INT NOT NULL,
    `nombre_seccion` VARCHAR(255) NOT NULL,
    `contenido` LONGTEXT NULL,
    `contenido_html` LONGTEXT NULL COMMENT 'Contenido formateado para PDF',
    `contexto_adicional` TEXT NULL COMMENT 'Input del usuario para regenerar',
    `aprobado` TINYINT(1) NOT NULL DEFAULT 0,
    `fecha_aprobacion` DATETIME NULL,
    `regeneraciones` INT NOT NULL DEFAULT 0 COMMENT 'Veces que se ha regenerado con IA',
    `ultima_regeneracion` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_seccion`),
    UNIQUE KEY `uk_documento_seccion` (`id_documento`, `numero_seccion`),
    KEY `idx_documento` (`id_documento`),
    CONSTRAINT `fk_seccion_documento` FOREIGN KEY (`id_documento`) REFERENCES `tbl_doc_documentos` (`id_documento`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Tabla: Versiones del documento
CREATE TABLE `tbl_doc_versiones` (
    `id_version` INT NOT NULL AUTO_INCREMENT,
    `id_documento` INT NOT NULL,
    `version` VARCHAR(10) NOT NULL COMMENT '1.0, 1.1, 2.0',
    `tipo_cambio` ENUM('mayor', 'menor') NOT NULL DEFAULT 'menor',
    `descripcion_cambio` TEXT NOT NULL,
    `fecha` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `autorizado_por` VARCHAR(255) NULL,
    `archivo_pdf` VARCHAR(255) NULL COMMENT 'Ruta al PDF generado',
    `archivo_word` VARCHAR(255) NULL COMMENT 'Ruta al DOCX generado',
    `hash_documento` VARCHAR(64) NULL COMMENT 'SHA-256 del PDF',
    `estado` ENUM('vigente', 'obsoleto') NOT NULL DEFAULT 'vigente',
    `contenido_snapshot` LONGTEXT NULL COMMENT 'JSON con snapshot de todas las secciones',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_version`),
    KEY `idx_documento` (`id_documento`),
    KEY `idx_version` (`version`),
    CONSTRAINT `fk_version_documento` FOREIGN KEY (`id_documento`) REFERENCES `tbl_doc_documentos` (`id_documento`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================================
-- PARTE 6: FIRMA ELECTRÓNICA
-- ============================================================================

-- Tabla: Solicitudes de firma
CREATE TABLE `tbl_doc_firma_solicitudes` (
    `id_solicitud` INT NOT NULL AUTO_INCREMENT,
    `id_documento` INT NOT NULL,
    `id_version` INT NULL,

    -- Token de acceso
    `token` VARCHAR(64) NOT NULL COMMENT 'UUID para el link de firma',
    `estado` ENUM('pendiente', 'firmado', 'expirado', 'rechazado', 'cancelado') NOT NULL DEFAULT 'pendiente',

    -- Fechas
    `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fecha_expiracion` DATETIME NOT NULL,
    `fecha_firma` DATETIME NULL,

    -- Firmante
    `firmante_tipo` ENUM('elaboro', 'reviso', 'aprobo') NOT NULL,
    `firmante_interno_id` INT NULL COMMENT 'Usuario del sistema si es interno',
    `firmante_email` VARCHAR(255) NOT NULL,
    `firmante_nombre` VARCHAR(255) NOT NULL,
    `firmante_cargo` VARCHAR(100) NULL,
    `firmante_documento` VARCHAR(20) NULL COMMENT 'Cédula/NIT',

    -- Control
    `recordatorios_enviados` INT NOT NULL DEFAULT 0,
    `ultimo_recordatorio` DATETIME NULL,

    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_solicitud`),
    UNIQUE KEY `uk_token` (`token`),
    KEY `idx_documento` (`id_documento`),
    KEY `idx_estado` (`estado`),
    KEY `idx_email` (`firmante_email`),
    CONSTRAINT `fk_firma_documento` FOREIGN KEY (`id_documento`) REFERENCES `tbl_doc_documentos` (`id_documento`) ON DELETE CASCADE,
    CONSTRAINT `fk_firma_version` FOREIGN KEY (`id_version`) REFERENCES `tbl_doc_versiones` (`id_version`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Tabla: Evidencia de firma
CREATE TABLE `tbl_doc_firma_evidencias` (
    `id_evidencia` INT NOT NULL AUTO_INCREMENT,
    `id_solicitud` INT NOT NULL,

    -- Datos de la firma
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` TEXT NULL,
    `fecha_hora_utc` DATETIME NOT NULL,
    `geolocalizacion` VARCHAR(255) NULL COMMENT 'Lat,Lng si está disponible',

    -- Tipo de firma
    `tipo_firma` ENUM('draw', 'type', 'upload') NOT NULL COMMENT 'Dibujada, escrita, subida',
    `firma_imagen` LONGTEXT NULL COMMENT 'Base64 de la imagen de firma',
    `firma_texto` VARCHAR(255) NULL COMMENT 'Si es tipo "type"',

    -- Verificación
    `hash_documento` VARCHAR(64) NOT NULL COMMENT 'SHA-256 del PDF al firmar',
    `aceptacion_terminos` TINYINT(1) NOT NULL DEFAULT 1,

    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_evidencia`),
    KEY `idx_solicitud` (`id_solicitud`),
    CONSTRAINT `fk_evidencia_solicitud` FOREIGN KEY (`id_solicitud`) REFERENCES `tbl_doc_firma_solicitudes` (`id_solicitud`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Tabla: Log de auditoría de firmas
CREATE TABLE `tbl_doc_firma_audit_log` (
    `id_log` INT NOT NULL AUTO_INCREMENT,
    `id_solicitud` INT NOT NULL,
    `evento` VARCHAR(50) NOT NULL COMMENT 'email_enviado, link_abierto, firma_completada, etc.',
    `fecha_hora` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `ip_address` VARCHAR(45) NULL,
    `detalles` TEXT NULL COMMENT 'JSON con info adicional',
    PRIMARY KEY (`id_log`),
    KEY `idx_solicitud` (`id_solicitud`),
    KEY `idx_evento` (`evento`),
    CONSTRAINT `fk_audit_solicitud` FOREIGN KEY (`id_solicitud`) REFERENCES `tbl_doc_firma_solicitudes` (`id_solicitud`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================================
-- PARTE 7: CUMPLIMIENTO DE ESTÁNDARES POR CLIENTE
-- ============================================================================

-- Tabla: Estado de cumplimiento de estándares por cliente
CREATE TABLE `tbl_cliente_estandares` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `id_cliente` INT NOT NULL,
    `id_estandar` INT NOT NULL,
    `estado` ENUM('no_aplica', 'pendiente', 'en_proceso', 'cumple', 'no_cumple') NOT NULL DEFAULT 'pendiente',
    `id_documento` INT NULL COMMENT 'Documento que evidencia cumplimiento',
    `fecha_cumplimiento` DATE NULL,
    `observaciones` TEXT NULL,
    `verificado_por` INT NULL COMMENT 'Usuario que verificó',
    `fecha_verificacion` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_cliente_estandar` (`id_cliente`, `id_estandar`),
    KEY `idx_cliente` (`id_cliente`),
    KEY `idx_estandar` (`id_estandar`),
    KEY `idx_estado` (`estado`),
    CONSTRAINT `fk_ce_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes` (`id_cliente`) ON DELETE CASCADE,
    CONSTRAINT `fk_ce_estandar` FOREIGN KEY (`id_estandar`) REFERENCES `tbl_estandares_minimos` (`id_estandar`) ON DELETE CASCADE,
    CONSTRAINT `fk_ce_documento` FOREIGN KEY (`id_documento`) REFERENCES `tbl_doc_documentos` (`id_documento`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Tabla: Transiciones de nivel de estándares
CREATE TABLE `tbl_cliente_transiciones` (
    `id_transicion` INT NOT NULL AUTO_INCREMENT,
    `id_cliente` INT NOT NULL,
    `nivel_anterior` TINYINT NOT NULL COMMENT '7, 21 o 60',
    `nivel_nuevo` TINYINT NOT NULL COMMENT '7, 21 o 60',
    `motivo` VARCHAR(255) NOT NULL COMMENT 'Ej: Aumento de trabajadores de 8 a 35',
    `fecha_deteccion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fecha_completado` DATETIME NULL,
    `estado` ENUM('detectado', 'en_proceso', 'completado') NOT NULL DEFAULT 'detectado',
    `plan_generado` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_transicion`),
    KEY `idx_cliente` (`id_cliente`),
    CONSTRAINT `fk_trans_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes` (`id_cliente`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================================
-- PARTE 8: SEGUIMIENTO (Actividades e Indicadores de Programas)
-- ============================================================================

-- Tabla: Actividades extraídas de programas
CREATE TABLE `tbl_doc_actividades` (
    `id_actividad` INT NOT NULL AUTO_INCREMENT,
    `id_cliente` INT NOT NULL,
    `id_documento` INT NOT NULL COMMENT 'Programa que generó la actividad',
    `actividad` TEXT NOT NULL,
    `responsable` VARCHAR(255) NULL,
    `fecha_programada` DATE NULL,
    `fecha_ejecucion` DATE NULL,
    `estado` ENUM('pendiente', 'en_proceso', 'ejecutada', 'vencida', 'cancelada') NOT NULL DEFAULT 'pendiente',
    `porcentaje_avance` TINYINT NOT NULL DEFAULT 0,
    `evidencia` VARCHAR(255) NULL COMMENT 'Ruta al archivo de evidencia',
    `observaciones` TEXT NULL,
    `mes_programado` TINYINT NULL COMMENT '1-12',
    `trimestre` TINYINT NULL COMMENT '1-4',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_actividad`),
    KEY `idx_cliente` (`id_cliente`),
    KEY `idx_documento` (`id_documento`),
    KEY `idx_estado` (`estado`),
    KEY `idx_fecha` (`fecha_programada`),
    CONSTRAINT `fk_act_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes` (`id_cliente`) ON DELETE CASCADE,
    CONSTRAINT `fk_act_documento` FOREIGN KEY (`id_documento`) REFERENCES `tbl_doc_documentos` (`id_documento`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Tabla: Indicadores de programas
CREATE TABLE `tbl_doc_indicadores` (
    `id_indicador` INT NOT NULL AUTO_INCREMENT,
    `id_cliente` INT NOT NULL,
    `id_documento` INT NOT NULL COMMENT 'Programa que generó el indicador',
    `nombre` VARCHAR(255) NOT NULL,
    `tipo` ENUM('estructura', 'proceso', 'resultado') NOT NULL DEFAULT 'proceso',
    `formula` TEXT NOT NULL,
    `meta` VARCHAR(100) NOT NULL COMMENT 'Ej: >= 90%',
    `unidad` VARCHAR(50) NULL COMMENT 'Ej: %, días, número',
    `frecuencia` ENUM('mensual', 'bimestral', 'trimestral', 'semestral', 'anual') NOT NULL DEFAULT 'trimestral',
    `responsable` VARCHAR(255) NULL,
    `fuente_datos` TEXT NULL,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_indicador`),
    KEY `idx_cliente` (`id_cliente`),
    KEY `idx_documento` (`id_documento`),
    CONSTRAINT `fk_ind_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes` (`id_cliente`) ON DELETE CASCADE,
    CONSTRAINT `fk_ind_documento` FOREIGN KEY (`id_documento`) REFERENCES `tbl_doc_documentos` (`id_documento`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Tabla: Mediciones de indicadores
CREATE TABLE `tbl_doc_indicadores_mediciones` (
    `id_medicion` INT NOT NULL AUTO_INCREMENT,
    `id_indicador` INT NOT NULL,
    `periodo` VARCHAR(20) NOT NULL COMMENT '2026-Q1, 2026-01, etc.',
    `valor_numerador` DECIMAL(10,2) NULL,
    `valor_denominador` DECIMAL(10,2) NULL,
    `valor_obtenido` DECIMAL(10,2) NOT NULL,
    `cumple_meta` TINYINT(1) NOT NULL DEFAULT 0,
    `observaciones` TEXT NULL,
    `evidencia` VARCHAR(255) NULL,
    `registrado_por` INT NULL,
    `fecha_registro` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_medicion`),
    UNIQUE KEY `uk_indicador_periodo` (`id_indicador`, `periodo`),
    KEY `idx_indicador` (`id_indicador`),
    CONSTRAINT `fk_med_indicador` FOREIGN KEY (`id_indicador`) REFERENCES `tbl_doc_indicadores` (`id_indicador`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================================
-- PARTE 9: CONTACTOS DEL CLIENTE (Para firmas)
-- ============================================================================

-- Tabla: Contactos del cliente
CREATE TABLE `tbl_cliente_contactos` (
    `id_contacto` INT NOT NULL AUTO_INCREMENT,
    `id_cliente` INT NOT NULL,
    `nombre` VARCHAR(255) NOT NULL,
    `cargo` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `telefono` VARCHAR(50) NULL,
    `documento_identidad` VARCHAR(20) NULL,
    `tipo_documento` ENUM('CC', 'CE', 'NIT', 'PASAPORTE') NOT NULL DEFAULT 'CC',
    `puede_aprobar_documentos` TINYINT(1) NOT NULL DEFAULT 0,
    `es_representante_legal` TINYINT(1) NOT NULL DEFAULT 0,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_contacto`),
    KEY `idx_cliente` (`id_cliente`),
    KEY `idx_email` (`email`),
    CONSTRAINT `fk_contacto_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes` (`id_cliente`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================================
-- PARTE 10: PROMPTS DE IA
-- ============================================================================

-- Tabla: Plantillas de prompts para generación
CREATE TABLE `tbl_doc_prompts` (
    `id_prompt` INT NOT NULL AUTO_INCREMENT,
    `id_tipo_documento` INT NOT NULL,
    `numero_seccion` INT NOT NULL,
    `nombre_seccion` VARCHAR(255) NOT NULL,
    `prompt_template` LONGTEXT NOT NULL,
    `variables_requeridas` TEXT NULL COMMENT 'JSON con variables que usa el prompt',
    `temperatura` DECIMAL(2,1) NOT NULL DEFAULT 0.3,
    `max_tokens` INT NOT NULL DEFAULT 1500,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_prompt`),
    UNIQUE KEY `uk_tipo_seccion` (`id_tipo_documento`, `numero_seccion`),
    KEY `idx_tipo` (`id_tipo_documento`),
    CONSTRAINT `fk_prompt_tipo` FOREIGN KEY (`id_tipo_documento`) REFERENCES `tbl_doc_tipos` (`id_tipo`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================================
-- INSERTS INICIALES: TIPOS DE DOCUMENTO
-- ============================================================================

INSERT INTO `tbl_doc_tipos` (`codigo`, `nombre`, `descripcion`, `tiene_secciones`, `numero_secciones`, `estructura_secciones`, `requiere_firma_cliente`) VALUES
('POL', 'Política', 'Declaración de compromiso de la alta dirección', 1, 5, '["Declaración","Objetivos de la política","Alcance","Compromisos","Comunicación y revisión"]', 1),
('OBJ', 'Objetivos', 'Objetivos del SG-SST', 1, 3, '["Objetivo general","Objetivos específicos","Metas e indicadores"]', 1),
('PRG', 'Programa', 'Programa con estructura de 13 secciones', 1, 13, '["Introducción","Objetivos","Alcance","Marco normativo","Definiciones","Diagnóstico","Actividades","Cronograma","Indicadores","Responsables","Recursos","Seguimiento","Registros"]', 1),
('PLA', 'Plan', 'Plan de trabajo o acción', 1, 10, '["Introducción","Objetivos","Alcance","Marco normativo","Diagnóstico","Metas","Actividades y cronograma","Presupuesto","Indicadores","Seguimiento"]', 1),
('PRO', 'Procedimiento', 'Procedimiento operativo', 1, 8, '["Objetivo","Alcance","Definiciones","Responsables","Descripción del procedimiento","Documentos relacionados","Control de cambios","Anexos"]', 1),
('PRT', 'Protocolo', 'Protocolo de actuación', 1, 7, '["Objetivo","Alcance","Definiciones","Condiciones generales","Desarrollo","Registros","Referencias"]', 1),
('MAN', 'Manual', 'Manual del SG-SST', 1, 8, '["Información de la empresa","Política y objetivos","Organización del SG-SST","Planificación","Aplicación","Verificación","Mejora continua","Documentos y registros"]', 1),
('INF', 'Informe', 'Informe de gestión o auditoría', 1, 6, '["Introducción","Objetivos","Metodología","Resultados","Conclusiones","Recomendaciones"]', 0),
('FOR', 'Formato', 'Formato o registro', 0, 0, NULL, 0),
('MTZ', 'Matriz', 'Matriz de análisis', 0, 0, NULL, 0),
('ACT', 'Acta', 'Acta de reunión o comité', 1, 5, '["Información general","Asistentes","Orden del día","Desarrollo","Compromisos"]', 0),
('GUA', 'Guía', 'Guía o instructivo', 1, 5, '["Objetivo","Alcance","Instrucciones","Recomendaciones","Referencias"]', 0),
('INS', 'Instructivo', 'Instructivo de trabajo', 1, 4, '["Objetivo","Alcance","Instrucciones paso a paso","Precauciones"]', 0);


-- ============================================================================
-- NOTA FINAL
-- ============================================================================
--
-- Después de crear estas tablas, ejecutar:
-- 1. INSERT de los 60 estándares mínimos (archivo separado)
-- 2. INSERT de los prompts por sección (archivo separado)
--
-- Para generar la estructura de carpetas de un cliente:
-- CALL sp_generar_carpetas_cliente(id_cliente);
-- (Crear stored procedure en archivo separado)
--
