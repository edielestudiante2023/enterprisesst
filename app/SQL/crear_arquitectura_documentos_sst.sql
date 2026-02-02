-- ============================================================
-- ARQUITECTURA ESCALABLE PARA DOCUMENTOS SST
-- Migración de configuración hardcodeada a Base de Datos
-- ============================================================

-- 1. TABLA: Configuración de Tipos de Documento
-- Reemplaza la constante TIPOS_DOCUMENTO del controlador
CREATE TABLE IF NOT EXISTS tbl_doc_tipo_configuracion (
    id_tipo_config INT AUTO_INCREMENT PRIMARY KEY,
    tipo_documento VARCHAR(100) NOT NULL UNIQUE COMMENT 'Identificador único (ej: procedimiento_control_documental)',
    nombre VARCHAR(255) NOT NULL COMMENT 'Nombre para mostrar',
    descripcion TEXT COMMENT 'Descripción del documento',
    estandar VARCHAR(20) COMMENT 'Estándar de la Res. 0312 (ej: 2.5.1)',
    flujo ENUM('secciones_ia', 'formulario', 'carga_archivo', 'mixto') DEFAULT 'secciones_ia' COMMENT 'Tipo de flujo de generación',
    categoria VARCHAR(50) COMMENT 'Categoría para agrupar (ej: procedimientos, programas, formatos)',
    icono VARCHAR(50) DEFAULT 'bi-file-text' COMMENT 'Icono Bootstrap',
    orden INT DEFAULT 0 COMMENT 'Orden de aparición en menús',
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tipo_documento (tipo_documento),
    INDEX idx_categoria (categoria),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. TABLA: Secciones por Tipo de Documento
-- Define las secciones que tiene cada documento
CREATE TABLE IF NOT EXISTS tbl_doc_secciones_config (
    id_seccion_config INT AUTO_INCREMENT PRIMARY KEY,
    id_tipo_config INT NOT NULL,
    numero INT NOT NULL COMMENT 'Número de sección (1, 2, 3...)',
    nombre VARCHAR(255) NOT NULL COMMENT 'Nombre de la sección',
    seccion_key VARCHAR(100) NOT NULL COMMENT 'Key identificador (ej: objetivo, alcance)',
    prompt_ia TEXT COMMENT 'Prompt para generación con IA',
    tipo_contenido ENUM('texto', 'tabla_dinamica', 'lista', 'mixto') DEFAULT 'texto',
    tabla_dinamica_tipo VARCHAR(50) COMMENT 'Si es tabla_dinamica: tipos_documento, plantillas, listado_maestro, etc.',
    es_obligatoria TINYINT(1) DEFAULT 1,
    orden INT NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_tipo_config) REFERENCES tbl_doc_tipo_configuracion(id_tipo_config) ON DELETE CASCADE,
    UNIQUE KEY uk_tipo_seccion (id_tipo_config, seccion_key),
    INDEX idx_orden (id_tipo_config, orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. TABLA: Configuración de Firmantes por Tipo de Documento
-- Define quiénes deben firmar cada tipo de documento
CREATE TABLE IF NOT EXISTS tbl_doc_firmantes_config (
    id_firmante_config INT AUTO_INCREMENT PRIMARY KEY,
    id_tipo_config INT NOT NULL,
    firmante_tipo ENUM('representante_legal', 'responsable_sst', 'consultor_sst', 'delegado_sst', 'vigia_sst', 'copasst', 'trabajador') NOT NULL,
    rol_display VARCHAR(100) NOT NULL COMMENT 'Texto a mostrar (ej: Elaboró / Responsable del SG-SST)',
    columna_encabezado VARCHAR(100) NOT NULL COMMENT 'Encabezado de columna en tabla de firmas',
    orden INT NOT NULL COMMENT 'Orden de aparición (1=izquierda, 2=centro, 3=derecha)',
    es_obligatorio TINYINT(1) DEFAULT 1,
    mostrar_licencia TINYINT(1) DEFAULT 0 COMMENT 'Mostrar número de licencia SST',
    mostrar_cedula TINYINT(1) DEFAULT 0 COMMENT 'Mostrar número de cédula',
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_tipo_config) REFERENCES tbl_doc_tipo_configuracion(id_tipo_config) ON DELETE CASCADE,
    UNIQUE KEY uk_tipo_firmante (id_tipo_config, firmante_tipo),
    INDEX idx_orden (id_tipo_config, orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. TABLA: Configuración de Tablas Dinámicas
-- Define las tablas dinámicas disponibles para usar en secciones
CREATE TABLE IF NOT EXISTS tbl_doc_tablas_dinamicas (
    id_tabla_dinamica INT AUTO_INCREMENT PRIMARY KEY,
    tabla_key VARCHAR(50) NOT NULL UNIQUE COMMENT 'Identificador (ej: tipos_documento, plantillas)',
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    query_base TEXT NOT NULL COMMENT 'Query SQL base para obtener datos',
    columnas JSON NOT NULL COMMENT 'Definición de columnas [{key, titulo, ancho, alineacion}]',
    filtro_cliente TINYINT(1) DEFAULT 1 COMMENT 'Si filtra por id_cliente',
    estilo_encabezado VARCHAR(50) DEFAULT 'primary' COMMENT 'Color de encabezado: primary, success, etc.',
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DATOS INICIALES: Migrar procedimiento_control_documental
-- ============================================================

-- Insertar tipo de documento
INSERT INTO tbl_doc_tipo_configuracion
(tipo_documento, nombre, descripcion, estandar, flujo, categoria, icono, orden)
VALUES
('procedimiento_control_documental',
 'Procedimiento de Control Documental del SG-SST',
 'Establece las directrices para la elaboración, revisión, aprobación, distribución y conservación de documentos del SG-SST',
 '2.5.1',
 'secciones_ia',
 'procedimientos',
 'bi-folder-check',
 1)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- Obtener el ID del tipo insertado
SET @id_control_documental = (SELECT id_tipo_config FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'procedimiento_control_documental');

-- Insertar secciones del procedimiento de control documental
INSERT INTO tbl_doc_secciones_config
(id_tipo_config, numero, nombre, seccion_key, tipo_contenido, tabla_dinamica_tipo, orden, prompt_ia)
VALUES
(@id_control_documental, 1, 'Objetivo', 'objetivo', 'texto', NULL, 1,
 'Genera el objetivo del procedimiento de control documental del SG-SST para {nombre_cliente}. Debe establecer el propósito de controlar la documentación del sistema.'),

(@id_control_documental, 2, 'Alcance', 'alcance', 'texto', NULL, 2,
 'Genera el alcance del procedimiento de control documental. Debe indicar a qué documentos aplica y quiénes son responsables.'),

(@id_control_documental, 3, 'Definiciones', 'definiciones', 'texto', NULL, 3,
 'Genera las definiciones clave para el control documental: documento, registro, versión, documento controlado, documento obsoleto, etc.'),

(@id_control_documental, 4, 'Marco Normativo', 'marco_normativo', 'texto', NULL, 4,
 'Genera el marco normativo aplicable: Decreto 1072 de 2015, Resolución 0312 de 2019, y otras normas relevantes para control documental.'),

(@id_control_documental, 5, 'Responsabilidades', 'responsabilidades', 'texto', NULL, 5,
 'Genera las responsabilidades de: Alta Dirección, Responsable del SG-SST, COPASST/Vigía, y trabajadores en el control documental.'),

(@id_control_documental, 6, 'Tipos de Documentos del SG-SST', 'tipos_documentos', 'mixto', 'tipos_documento', 6,
 'Genera un párrafo introductorio sobre los tipos de documentos del SG-SST. IMPORTANTE: NO generes una tabla con prefijos o códigos. Solo el texto introductorio.'),

(@id_control_documental, 7, 'Estructura y Codificación', 'codificacion', 'mixto', 'plantillas', 7,
 'Genera un párrafo explicando el sistema de codificación. IMPORTANTE: NO generes ejemplos de códigos. Solo el texto explicativo.'),

(@id_control_documental, 8, 'Elaboración de Documentos', 'elaboracion', 'texto', NULL, 8,
 'Genera el procedimiento para elaborar documentos: identificación de necesidad, redacción, formato, revisión inicial.'),

(@id_control_documental, 9, 'Revisión y Aprobación', 'revision_aprobacion', 'texto', NULL, 9,
 'Genera el procedimiento de revisión y aprobación: quién revisa, quién aprueba, criterios, registro de aprobación.'),

(@id_control_documental, 10, 'Distribución y Acceso', 'distribucion', 'texto', NULL, 10,
 'Genera el procedimiento de distribución: cómo se distribuyen los documentos, control de copias, acceso electrónico.'),

(@id_control_documental, 11, 'Control de Cambios', 'control_cambios', 'texto', NULL, 11,
 'Genera el procedimiento de control de cambios: solicitud, evaluación, aprobación, actualización de versión.'),

(@id_control_documental, 12, 'Conservación y Retención', 'conservacion', 'texto', NULL, 12,
 'Genera el procedimiento de conservación: tiempo de retención (20 años según normativa), almacenamiento, protección.'),

(@id_control_documental, 13, 'Listado Maestro de Documentos', 'listado_maestro', 'mixto', 'listado_maestro', 13,
 'Genera un párrafo introductorio sobre el listado maestro. La tabla de documentos se genera automáticamente.'),

(@id_control_documental, 14, 'Disposición Final', 'disposicion_final', 'texto', NULL, 14,
 'Genera el procedimiento de disposición final de documentos obsoletos: evaluación, autorización, destrucción segura.')

ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), prompt_ia = VALUES(prompt_ia);

-- Insertar firmantes del procedimiento de control documental
INSERT INTO tbl_doc_firmantes_config
(id_tipo_config, firmante_tipo, rol_display, columna_encabezado, orden, mostrar_licencia)
VALUES
(@id_control_documental, 'responsable_sst', 'Elaboró', 'Elaboró / Responsable del SG-SST', 1, 1),
(@id_control_documental, 'representante_legal', 'Aprobó', 'Aprobó / Representante Legal', 2, 0)
ON DUPLICATE KEY UPDATE rol_display = VALUES(rol_display);

-- Insertar tablas dinámicas disponibles
INSERT INTO tbl_doc_tablas_dinamicas
(tabla_key, nombre, descripcion, query_base, columnas, filtro_cliente, estilo_encabezado)
VALUES
('tipos_documento',
 'Tipos de Documentos',
 'Tabla con los tipos de documentos del SG-SST',
 'SELECT prefijo, nombre, descripcion FROM tbl_doc_tipos WHERE activo = 1 ORDER BY id_tipo',
 '[{"key": "prefijo", "titulo": "Prefijo", "ancho": "70px", "alineacion": "center"}, {"key": "nombre", "titulo": "Tipo de Documento", "ancho": "auto", "alineacion": "left"}, {"key": "descripcion", "titulo": "Descripción", "ancho": "auto", "alineacion": "left"}]',
 0, 'primary'),

('plantillas',
 'Códigos de Documentos',
 'Tabla con los códigos de plantillas del sistema',
 'SELECT codigo_sugerido, nombre FROM tbl_doc_plantillas WHERE activo = 1 AND tipo_documento IS NOT NULL ORDER BY codigo_sugerido',
 '[{"key": "codigo_sugerido", "titulo": "Código", "ancho": "100px", "alineacion": "center"}, {"key": "nombre", "titulo": "Nombre del Documento", "ancho": "auto", "alineacion": "left"}]',
 0, 'primary'),

('listado_maestro',
 'Listado Maestro de Documentos',
 'Tabla con los documentos del cliente',
 'SELECT codigo, titulo, version, estado, created_at FROM tbl_documentos_sst WHERE id_cliente = :id_cliente AND estado IN ("aprobado", "firmado", "generado") ORDER BY codigo',
 '[{"key": "codigo", "titulo": "Código", "ancho": "80px", "alineacion": "center"}, {"key": "titulo", "titulo": "Título", "ancho": "auto", "alineacion": "left"}, {"key": "version", "titulo": "Versión", "ancho": "50px", "alineacion": "center"}, {"key": "estado", "titulo": "Estado", "ancho": "70px", "alineacion": "center"}, {"key": "created_at", "titulo": "Fecha", "ancho": "75px", "alineacion": "center"}]',
 1, 'success')

ON DUPLICATE KEY UPDATE query_base = VALUES(query_base);

-- ============================================================
-- DATOS INICIALES: Programa de Capacitación (ejemplo adicional)
-- ============================================================

INSERT INTO tbl_doc_tipo_configuracion
(tipo_documento, nombre, descripcion, estandar, flujo, categoria, icono, orden)
VALUES
('programa_capacitacion',
 'Programa de Capacitación en SST',
 'Define las actividades de capacitación y entrenamiento en seguridad y salud en el trabajo',
 '3.1.1',
 'secciones_ia',
 'programas',
 'bi-mortarboard',
 2)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

SELECT 'Tablas creadas y datos iniciales insertados correctamente' AS resultado;
