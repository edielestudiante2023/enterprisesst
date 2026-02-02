<?php
/**
 * Ecosistema de Actas SST - COPASST, COCOLAB, Brigada, Generales
 * Ejecuta en LOCAL y PRODUCCIÓN
 */

$environments = [
    'LOCAL' => [
        'host' => 'localhost',
        'port' => 3306,
        'dbname' => 'empresas_sst',
        'user' => 'root',
        'pass' => '',
        'ssl' => false
    ],
    'PRODUCTION' => [
        'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port' => 25060,
        'dbname' => 'empresas_sst',
        'user' => 'cycloid_userdb',
        'pass' => 'AVNS_iDypWizlpMRwHIORJGG',
        'ssl' => true
    ]
];

// SQL Statements
$sqlStatements = [

    // 1. Tipos de Comité (catálogo)
    "CREATE_tbl_tipos_comite" => "
        CREATE TABLE IF NOT EXISTS tbl_tipos_comite (
            id_tipo TINYINT PRIMARY KEY AUTO_INCREMENT,
            codigo VARCHAR(20) NOT NULL UNIQUE,
            nombre VARCHAR(100) NOT NULL,
            descripcion TEXT,
            periodicidad_dias INT DEFAULT 30,
            dia_limite_mes TINYINT DEFAULT 10,
            requiere_quorum TINYINT(1) DEFAULT 1,
            quorum_minimo_porcentaje TINYINT DEFAULT 50,
            vigencia_periodo_meses INT DEFAULT 24,
            activo TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ",

    // 2. Datos iniciales de tipos de comité
    "INSERT_tipos_comite" => "
        INSERT IGNORE INTO tbl_tipos_comite (codigo, nombre, descripcion, periodicidad_dias, dia_limite_mes, vigencia_periodo_meses) VALUES
        ('COPASST', 'Comité Paritario de Seguridad y Salud en el Trabajo', 'Reuniones mensuales obligatorias según Resolución 2013/1986', 30, 10, 24),
        ('COCOLAB', 'Comité de Convivencia Laboral', 'Reuniones trimestrales mínimo según Resolución 652/2012', 90, 15, 24),
        ('BRIGADA', 'Brigada de Emergencias', 'Reuniones mensuales de entrenamiento y coordinación', 30, NULL, NULL),
        ('GENERAL', 'Actas Generales SST', 'Actas de reuniones varias del SG-SST', NULL, NULL, NULL)
    ",

    // 3. Comités por Cliente (instancia)
    "CREATE_tbl_comites" => "
        CREATE TABLE IF NOT EXISTS tbl_comites (
            id_comite INT PRIMARY KEY AUTO_INCREMENT,
            id_cliente INT NOT NULL,
            id_tipo TINYINT NOT NULL,

            fecha_conformacion DATE NOT NULL,
            fecha_vencimiento DATE,
            acta_conformacion_id INT,
            reglamento_documento_id INT,

            periodicidad_personalizada INT,
            dia_reunion_preferido TINYINT,
            hora_reunion_preferida TIME,
            lugar_habitual VARCHAR(200),

            estado ENUM('activo', 'vencido', 'renovado', 'inactivo') DEFAULT 'activo',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            INDEX idx_cliente (id_cliente),
            INDEX idx_tipo (id_tipo),
            INDEX idx_estado (estado)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ",

    // 4. Miembros del Comité
    "CREATE_tbl_comite_miembros" => "
        CREATE TABLE IF NOT EXISTS tbl_comite_miembros (
            id_miembro INT PRIMARY KEY AUTO_INCREMENT,
            id_comite INT NOT NULL,
            id_cliente INT NOT NULL,

            nombre_completo VARCHAR(200) NOT NULL,
            tipo_documento ENUM('CC', 'CE', 'PA', 'TI', 'NIT') DEFAULT 'CC',
            numero_documento VARCHAR(20) NOT NULL,
            cargo VARCHAR(100),
            area_dependencia VARCHAR(100),
            email VARCHAR(150) NOT NULL,
            telefono VARCHAR(20),

            representacion ENUM('empleador', 'trabajador', 'brigadista', 'invitado') NOT NULL,
            tipo_miembro ENUM('principal', 'suplente') DEFAULT 'principal',
            rol_comite ENUM('presidente', 'secretario', 'miembro', 'coordinador', 'lider') DEFAULT 'miembro',

            puede_crear_actas TINYINT(1) DEFAULT 0,
            puede_cerrar_actas TINYINT(1) DEFAULT 0,

            fecha_ingreso DATE NOT NULL,
            fecha_retiro DATE,
            motivo_retiro VARCHAR(200),

            firma_imagen LONGTEXT,

            estado ENUM('activo', 'inactivo', 'retirado') DEFAULT 'activo',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            INDEX idx_comite (id_comite),
            INDEX idx_cliente (id_cliente),
            INDEX idx_email (email),
            INDEX idx_documento (numero_documento),
            INDEX idx_estado (estado)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ",

    // 5. Actas (unificada para todos los tipos)
    "CREATE_tbl_actas" => "
        CREATE TABLE IF NOT EXISTS tbl_actas (
            id_acta INT PRIMARY KEY AUTO_INCREMENT,
            id_comite INT NOT NULL,
            id_cliente INT NOT NULL,

            numero_acta VARCHAR(30) NOT NULL,
            consecutivo_anual SMALLINT NOT NULL,
            anio YEAR NOT NULL,
            tipo_acta ENUM('ordinaria', 'extraordinaria', 'conformacion') DEFAULT 'ordinaria',

            fecha_reunion DATE NOT NULL,
            hora_inicio TIME,
            hora_fin TIME,
            lugar VARCHAR(200),
            modalidad ENUM('presencial', 'virtual', 'mixta') DEFAULT 'presencial',
            enlace_virtual VARCHAR(500),

            quorum_requerido TINYINT,
            quorum_presente TINYINT,
            hay_quorum TINYINT(1) DEFAULT 0,

            orden_del_dia JSON,
            desarrollo JSON,
            conclusiones TEXT,
            observaciones TEXT,

            proxima_reunion_fecha DATE,
            proxima_reunion_hora TIME,
            proxima_reunion_lugar VARCHAR(200),

            estado ENUM('borrador', 'en_edicion', 'pendiente_firma', 'firmada', 'anulada') DEFAULT 'borrador',
            total_firmantes TINYINT DEFAULT 0,
            firmantes_completados TINYINT DEFAULT 0,
            fecha_cierre DATETIME,
            cerrada_por INT,

            codigo_verificacion VARCHAR(20),

            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            UNIQUE KEY uk_acta_consecutivo (id_comite, anio, consecutivo_anual),
            INDEX idx_cliente (id_cliente),
            INDEX idx_comite (id_comite),
            INDEX idx_fecha (fecha_reunion),
            INDEX idx_estado (estado)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ",

    // 6. Asistentes al Acta (con flujo de firmas)
    "CREATE_tbl_acta_asistentes" => "
        CREATE TABLE IF NOT EXISTS tbl_acta_asistentes (
            id_asistente INT PRIMARY KEY AUTO_INCREMENT,
            id_acta INT NOT NULL,
            id_miembro INT,

            nombre_completo VARCHAR(200) NOT NULL,
            numero_documento VARCHAR(20),
            cargo VARCHAR(100),
            email VARCHAR(150),

            tipo_asistente ENUM('miembro', 'invitado', 'ausente_justificado', 'ausente') NOT NULL DEFAULT 'miembro',
            justificacion_ausencia TEXT,
            asistio TINYINT(1) DEFAULT 1,

            orden_firma TINYINT,
            token_firma VARCHAR(64) UNIQUE,
            token_expira DATETIME,
            estado_firma ENUM('no_requerida', 'pendiente', 'firmado', 'rechazado') DEFAULT 'pendiente',

            firma_imagen LONGTEXT,
            firma_ip VARCHAR(45),
            firma_user_agent VARCHAR(500),
            firma_fecha DATETIME,
            firma_observacion TEXT,

            notificacion_enviada_at DATETIME,
            recordatorio_enviado_at DATETIME,

            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

            INDEX idx_acta (id_acta),
            INDEX idx_miembro (id_miembro),
            INDEX idx_token (token_firma),
            INDEX idx_estado_firma (estado_firma)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ",

    // 7. Compromisos/Tareas
    "CREATE_tbl_acta_compromisos" => "
        CREATE TABLE IF NOT EXISTS tbl_acta_compromisos (
            id_compromiso INT PRIMARY KEY AUTO_INCREMENT,
            id_acta INT NOT NULL,
            id_comite INT NOT NULL,
            id_cliente INT NOT NULL,

            numero_compromiso SMALLINT NOT NULL,
            descripcion TEXT NOT NULL,
            punto_orden_del_dia TINYINT,

            responsable_nombre VARCHAR(200) NOT NULL,
            responsable_email VARCHAR(150),
            responsable_id_miembro INT,

            fecha_compromiso DATE NOT NULL,
            fecha_vencimiento DATE NOT NULL,
            fecha_cierre_efectiva DATE,

            estado ENUM('pendiente', 'en_proceso', 'cumplido', 'vencido', 'cancelado') DEFAULT 'pendiente',
            porcentaje_avance TINYINT DEFAULT 0,
            prioridad ENUM('baja', 'media', 'alta', 'urgente') DEFAULT 'media',

            evidencia_descripcion TEXT,
            evidencia_archivo VARCHAR(500),
            cerrado_por VARCHAR(100),
            cerrado_at DATETIME,

            token_actualizacion VARCHAR(64) UNIQUE,
            ultima_notificacion_at DATETIME,
            total_notificaciones TINYINT DEFAULT 0,

            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            INDEX idx_acta (id_acta),
            INDEX idx_comite (id_comite),
            INDEX idx_cliente (id_cliente),
            INDEX idx_responsable (responsable_email),
            INDEX idx_estado (estado),
            INDEX idx_vencimiento (fecha_vencimiento)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ",

    // 8. Tokens de acceso (para firmas y actualizaciones sin login)
    "CREATE_tbl_actas_tokens" => "
        CREATE TABLE IF NOT EXISTS tbl_actas_tokens (
            id_token INT PRIMARY KEY AUTO_INCREMENT,
            token VARCHAR(64) NOT NULL UNIQUE,

            tipo ENUM('firmar_acta', 'ver_acta', 'actualizar_tarea', 'acceso_miembro') NOT NULL,

            id_acta INT,
            id_compromiso INT,
            id_miembro INT,
            id_asistente INT,
            id_cliente INT NOT NULL,

            email VARCHAR(150) NOT NULL,
            nombre VARCHAR(200),

            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at DATETIME NOT NULL,
            usado_at DATETIME,

            ip_uso VARCHAR(45),
            intentos_uso TINYINT DEFAULT 0,
            max_usos TINYINT DEFAULT 1,
            usos_actuales TINYINT DEFAULT 0,

            INDEX idx_token (token),
            INDEX idx_tipo (tipo),
            INDEX idx_email (email),
            INDEX idx_expira (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ",

    // 9. Cola de notificaciones
    "CREATE_tbl_actas_notificaciones" => "
        CREATE TABLE IF NOT EXISTS tbl_actas_notificaciones (
            id_notificacion INT PRIMARY KEY AUTO_INCREMENT,
            id_cliente INT NOT NULL,

            tipo ENUM(
                'firma_solicitada',
                'firma_recordatorio',
                'firma_completada',
                'acta_firmada_completa',
                'tarea_asignada',
                'tarea_por_vencer',
                'tarea_vencida',
                'recordatorio_reunion',
                'alerta_sin_acta',
                'resumen_semanal'
            ) NOT NULL,

            id_acta INT,
            id_compromiso INT,
            id_asistente INT,
            id_miembro INT,

            destinatario_email VARCHAR(150) NOT NULL,
            destinatario_nombre VARCHAR(200),
            destinatario_tipo ENUM('miembro', 'consultor', 'cliente', 'responsable') DEFAULT 'miembro',

            asunto VARCHAR(200),
            cuerpo TEXT,

            estado ENUM('pendiente', 'enviado', 'fallido', 'cancelado') DEFAULT 'pendiente',
            programado_para DATETIME NOT NULL,
            enviado_at DATETIME,
            error_mensaje TEXT,
            intentos TINYINT DEFAULT 0,

            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

            INDEX idx_estado (estado),
            INDEX idx_programado (programado_para),
            INDEX idx_cliente (id_cliente),
            INDEX idx_tipo (tipo)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ",

    // 10. Configuración de alertas por cliente
    "CREATE_tbl_actas_config_alertas" => "
        CREATE TABLE IF NOT EXISTS tbl_actas_config_alertas (
            id_config INT PRIMARY KEY AUTO_INCREMENT,
            id_cliente INT NOT NULL,
            id_tipo_comite TINYINT,

            alerta_sin_acta_activa TINYINT(1) DEFAULT 1,
            alerta_sin_acta_dia INT DEFAULT 10,
            alerta_sin_acta_hora TIME DEFAULT '08:00:00',

            alerta_tareas_activa TINYINT(1) DEFAULT 1,
            alerta_tareas_dias_antes INT DEFAULT 7,
            alerta_tareas_dia_semana TINYINT DEFAULT 1,
            alerta_tareas_hora TIME DEFAULT '08:00:00',

            resumen_semanal_activo TINYINT(1) DEFAULT 1,
            resumen_semanal_dia TINYINT DEFAULT 1,
            resumen_semanal_hora TIME DEFAULT '07:00:00',

            recordatorio_firma_horas INT DEFAULT 48,
            recordatorio_firma_urgente_dias INT DEFAULT 7,

            email_consultor VARCHAR(150),
            emails_adicionales JSON,

            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            UNIQUE KEY uk_cliente_tipo (id_cliente, id_tipo_comite),
            INDEX idx_cliente (id_cliente)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ",

    // 11. Plantillas de orden del día
    "CREATE_tbl_actas_plantillas_orden" => "
        CREATE TABLE IF NOT EXISTS tbl_actas_plantillas_orden (
            id_plantilla INT PRIMARY KEY AUTO_INCREMENT,
            id_tipo_comite TINYINT NOT NULL,

            nombre VARCHAR(100) NOT NULL,
            es_default TINYINT(1) DEFAULT 0,

            puntos JSON NOT NULL,

            activo TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

            INDEX idx_tipo (id_tipo_comite)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ",

    // 12. Plantillas de orden del día - datos iniciales
    "INSERT_plantillas_orden" => "
        INSERT IGNORE INTO tbl_actas_plantillas_orden (id_tipo_comite, nombre, es_default, puntos) VALUES
        (1, 'COPASST - Reunión Ordinaria', 1, '[
            {\"punto\": 1, \"tema\": \"Verificación de quórum\", \"fijo\": true},
            {\"punto\": 2, \"tema\": \"Lectura y aprobación del acta anterior\", \"fijo\": true},
            {\"punto\": 3, \"tema\": \"Seguimiento de compromisos pendientes\", \"fijo\": true},
            {\"punto\": 4, \"tema\": \"Revisión de accidentes e incidentes del período\", \"fijo\": false},
            {\"punto\": 5, \"tema\": \"Inspecciones de seguridad realizadas\", \"fijo\": false},
            {\"punto\": 6, \"tema\": \"Capacitaciones del período\", \"fijo\": false},
            {\"punto\": 7, \"tema\": \"Proposiciones y varios\", \"fijo\": false},
            {\"punto\": 8, \"tema\": \"Programación próxima reunión\", \"fijo\": true}
        ]'),
        (2, 'COCOLAB - Reunión Ordinaria', 1, '[
            {\"punto\": 1, \"tema\": \"Verificación de quórum\", \"fijo\": true},
            {\"punto\": 2, \"tema\": \"Lectura y aprobación del acta anterior\", \"fijo\": true},
            {\"punto\": 3, \"tema\": \"Seguimiento de compromisos pendientes\", \"fijo\": true},
            {\"punto\": 4, \"tema\": \"Revisión de casos recibidos en el período\", \"fijo\": false},
            {\"punto\": 5, \"tema\": \"Seguimiento a casos en trámite\", \"fijo\": false},
            {\"punto\": 6, \"tema\": \"Actividades de prevención realizadas\", \"fijo\": false},
            {\"punto\": 7, \"tema\": \"Proposiciones y varios\", \"fijo\": false},
            {\"punto\": 8, \"tema\": \"Programación próxima reunión\", \"fijo\": true}
        ]'),
        (3, 'Brigada - Reunión Ordinaria', 1, '[
            {\"punto\": 1, \"tema\": \"Verificación de asistencia\", \"fijo\": true},
            {\"punto\": 2, \"tema\": \"Lectura del acta anterior\", \"fijo\": true},
            {\"punto\": 3, \"tema\": \"Seguimiento de compromisos\", \"fijo\": true},
            {\"punto\": 4, \"tema\": \"Revisión de equipos de emergencia\", \"fijo\": false},
            {\"punto\": 5, \"tema\": \"Capacitaciones y entrenamientos\", \"fijo\": false},
            {\"punto\": 6, \"tema\": \"Simulacros realizados o programados\", \"fijo\": false},
            {\"punto\": 7, \"tema\": \"Proposiciones y varios\", \"fijo\": false},
            {\"punto\": 8, \"tema\": \"Próxima reunión\", \"fijo\": true}
        ]'),
        (4, 'Acta General', 1, '[
            {\"punto\": 1, \"tema\": \"Verificación de asistencia\", \"fijo\": true},
            {\"punto\": 2, \"tema\": \"Objetivo de la reunión\", \"fijo\": false},
            {\"punto\": 3, \"tema\": \"Desarrollo de la reunión\", \"fijo\": false},
            {\"punto\": 4, \"tema\": \"Compromisos y tareas\", \"fijo\": false},
            {\"punto\": 5, \"tema\": \"Cierre\", \"fijo\": true}
        ]')
    ",

    // 13. Votaciones en actas (para COPASST principalmente)
    "CREATE_tbl_acta_votaciones" => "
        CREATE TABLE IF NOT EXISTS tbl_acta_votaciones (
            id_votacion INT PRIMARY KEY AUTO_INCREMENT,
            id_acta INT NOT NULL,

            punto_orden_del_dia TINYINT,
            tema_votacion VARCHAR(300) NOT NULL,
            descripcion TEXT,

            votos_favor TINYINT DEFAULT 0,
            votos_contra TINYINT DEFAULT 0,
            abstenciones TINYINT DEFAULT 0,

            resultado ENUM('aprobado', 'rechazado', 'empate', 'pendiente') DEFAULT 'pendiente',
            observaciones TEXT,

            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

            INDEX idx_acta (id_acta)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ",

    // 14. Anexos de actas
    "CREATE_tbl_acta_anexos" => "
        CREATE TABLE IF NOT EXISTS tbl_acta_anexos (
            id_anexo INT PRIMARY KEY AUTO_INCREMENT,
            id_acta INT NOT NULL,

            nombre_archivo VARCHAR(200) NOT NULL,
            nombre_original VARCHAR(200),
            ruta_archivo VARCHAR(500) NOT NULL,
            tipo_mime VARCHAR(100),
            tamano_bytes INT,
            descripcion VARCHAR(300),

            uploaded_by INT,
            uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

            INDEX idx_acta (id_acta)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ",

    // 15. Historial de acciones (auditoría)
    "CREATE_tbl_actas_auditoria" => "
        CREATE TABLE IF NOT EXISTS tbl_actas_auditoria (
            id_log INT PRIMARY KEY AUTO_INCREMENT,

            tabla_afectada VARCHAR(50) NOT NULL,
            id_registro INT NOT NULL,
            accion ENUM('crear', 'editar', 'eliminar', 'firmar', 'cerrar', 'anular') NOT NULL,

            datos_anteriores JSON,
            datos_nuevos JSON,

            id_usuario INT,
            tipo_usuario VARCHAR(20),
            email_usuario VARCHAR(150),
            ip_address VARCHAR(45),
            user_agent VARCHAR(500),

            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

            INDEX idx_tabla_registro (tabla_afectada, id_registro),
            INDEX idx_fecha (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    "
];

// Ejecutar en ambos entornos
foreach ($environments as $envName => $config) {
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "  {$envName}\n";
    echo str_repeat("=", 60) . "\n";

    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];

        if ($config['ssl']) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = true;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        $pdo = new PDO($dsn, $config['user'], $config['pass'], $options);
        echo "✓ Conectado a {$envName}\n\n";

        $exitosos = 0;
        $errores = 0;
        $omitidos = 0;

        foreach ($sqlStatements as $nombre => $sql) {
            try {
                $pdo->exec($sql);

                if (strpos($nombre, 'CREATE') === 0) {
                    echo "  ✓ Tabla creada/verificada: " . str_replace('CREATE_', '', $nombre) . "\n";
                } elseif (strpos($nombre, 'INSERT') === 0) {
                    echo "  ✓ Datos insertados: " . str_replace('INSERT_', '', $nombre) . "\n";
                } else {
                    echo "  ✓ {$nombre}\n";
                }
                $exitosos++;

            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'Duplicate') !== false ||
                    strpos($e->getMessage(), 'already exists') !== false) {
                    echo "  ○ Ya existe: {$nombre}\n";
                    $omitidos++;
                } else {
                    echo "  ✗ ERROR en {$nombre}: " . $e->getMessage() . "\n";
                    $errores++;
                }
            }
        }

        echo "\n  Resumen {$envName}: {$exitosos} exitosos, {$omitidos} omitidos, {$errores} errores\n";

    } catch (PDOException $e) {
        echo "✗ ERROR de conexión {$envName}: " . $e->getMessage() . "\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "  ECOSISTEMA DE ACTAS - COMPLETADO\n";
echo str_repeat("=", 60) . "\n\n";

echo "Tablas creadas:\n";
echo "  - tbl_tipos_comite (catálogo COPASST, COCOLAB, Brigada, General)\n";
echo "  - tbl_comites (instancias por cliente)\n";
echo "  - tbl_comite_miembros (integrantes con roles)\n";
echo "  - tbl_actas (actas unificadas)\n";
echo "  - tbl_acta_asistentes (asistencia + firmas)\n";
echo "  - tbl_acta_compromisos (tareas con seguimiento)\n";
echo "  - tbl_actas_tokens (acceso sin login)\n";
echo "  - tbl_actas_notificaciones (cola de emails)\n";
echo "  - tbl_actas_config_alertas (configuración por cliente)\n";
echo "  - tbl_actas_plantillas_orden (plantillas de orden del día)\n";
echo "  - tbl_acta_votaciones (votaciones en reuniones)\n";
echo "  - tbl_acta_anexos (archivos adjuntos)\n";
echo "  - tbl_actas_auditoria (historial de acciones)\n";
echo "\n";
