<?php
/**
 * Script CLI para crear tablas del módulo de Investigación de Accidentes e Incidentes
 *
 * Uso LOCAL:       php app/SQL/crear_modulo_investigacion_accidente.php
 * Uso PRODUCCION:  php app/SQL/crear_modulo_investigacion_accidente.php --prod
 */

$isProd = in_array('--prod', $argv ?? []);

if ($isProd) {
    $host     = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port     = 25060;
    $dbname   = 'empresas_sst';
    $user     = 'cycloid_userdb';
    $pass     = 'AVNS_MR2SLvzRh3i_7o9fEHN';
    $sslMode  = true;
    echo "=== PRODUCCION (DigitalOcean) ===\n";
} else {
    $host     = 'localhost';
    $port     = 3306;
    $dbname   = 'empresas_sst';
    $user     = 'root';
    $pass     = '';
    $sslMode  = false;
    echo "=== LOCAL ===\n";
}

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    if ($sslMode) {
        $opts[PDO::MYSQL_ATTR_SSL_CA] = true;
        $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
    $pdo = new PDO($dsn, $user, $pass, $opts);
    echo "Conexion OK\n\n";
} catch (PDOException $e) {
    die("Error conexion: " . $e->getMessage() . "\n");
}

$sentencias = [];

// 1. Insertar detail_report para investigación AT/IT
$sentencias['INSERT detail_report (id=38)'] = "
    INSERT IGNORE INTO detail_report (id_detailreport, detail_report)
    VALUES (38, 'Investigación de Accidentes e Incidentes de Trabajo')
";

// 2. Tabla principal
$sentencias['CREATE tbl_investigacion_accidente'] = "
    CREATE TABLE IF NOT EXISTS tbl_investigacion_accidente (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_cliente INT NOT NULL,
        id_consultor INT NULL,
        id_miembro INT NULL,
        creado_por_tipo ENUM('consultor','miembro') DEFAULT 'consultor',

        -- Tipo y clasificación del evento
        tipo_evento ENUM('accidente','incidente') NOT NULL,
        gravedad ENUM('leve','grave','mortal') NULL COMMENT 'Solo aplica a accidentes',

        -- Datos del evento (Art. 9-10 Res. 1401/2007)
        fecha_evento DATE NOT NULL,
        hora_evento TIME NULL,
        lugar_exacto VARCHAR(500) NULL,
        descripcion_detallada TEXT NULL COMMENT 'Relato completo: cuando, donde, que, por que, con quien',
        fecha_investigacion DATE NULL COMMENT 'Max 15 dias despues del evento',

        -- Datos del trabajador (lesionado en accidente, involucrado en incidente)
        nombre_trabajador VARCHAR(255) NULL,
        documento_trabajador VARCHAR(50) NULL,
        cargo_trabajador VARCHAR(150) NULL,
        area_trabajador VARCHAR(150) NULL,
        antiguedad_trabajador VARCHAR(100) NULL,
        tipo_vinculacion ENUM('directo','contratista','temporal','cooperativa') NULL,
        jornada_habitual VARCHAR(100) NULL,

        -- Datos de lesión - SOLO accidentes (Res. 156/2005 FURAT)
        parte_cuerpo_lesionada VARCHAR(255) NULL COMMENT 'Codigos Res.156/2005',
        tipo_lesion VARCHAR(255) NULL COMMENT 'Codigos Res.156/2005',
        agente_accidente TEXT NULL COMMENT 'Tipo, marca, modelo, velocidades',
        mecanismo_accidente VARCHAR(255) NULL COMMENT 'Codigos Res.156/2005',
        dias_incapacidad INT NULL COMMENT 'Solo accidentes',

        -- Potencial de daño - SOLO incidentes
        potencial_danio TEXT NULL COMMENT 'Que pudo haber pasado - solo incidentes',

        -- Análisis causal (Art. 11 Res. 1401/2007)
        actos_substandar TEXT NULL COMMENT 'Causas inmediatas - actos inseguros',
        condiciones_substandar TEXT NULL COMMENT 'Causas inmediatas - condiciones inseguras',
        factores_personales TEXT NULL COMMENT 'Causas basicas - factores personales',
        factores_trabajo TEXT NULL COMMENT 'Causas basicas - factores del trabajo',
        metodologia_analisis ENUM('arbol_causas','espina_pescado','5_porques','otra') DEFAULT 'arbol_causas',
        descripcion_analisis TEXT NULL,

        -- Equipo investigador (Art. 7 Res. 1401/2007)
        investigador_jefe_nombre VARCHAR(255) NULL COMMENT 'Jefe inmediato o supervisor',
        investigador_jefe_cargo VARCHAR(150) NULL,
        investigador_copasst_nombre VARCHAR(255) NULL COMMENT 'Representante COPASST',
        investigador_copasst_cargo VARCHAR(150) NULL,
        investigador_sst_nombre VARCHAR(255) NULL COMMENT 'Responsable SG-SST',
        investigador_sst_cargo VARCHAR(150) NULL,

        -- Firmas (patron acta_visita)
        firma_jefe VARCHAR(255) NULL,
        firma_copasst VARCHAR(255) NULL,
        firma_sst VARCHAR(255) NULL,

        -- Token firma remota (patron acta_visita)
        token_firma_remota VARCHAR(64) NULL,
        token_firma_tipo VARCHAR(20) NULL,
        token_firma_expiracion DATETIME NULL,

        -- Referencia FURAT - solo accidentes
        numero_furat VARCHAR(50) NULL COMMENT 'Numero de radicado FURAT ante ARL',

        observaciones TEXT NULL,
        ruta_pdf VARCHAR(255) NULL,
        estado ENUM('borrador','pendiente_firma','completo') DEFAULT 'borrador',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX idx_cliente (id_cliente),
        INDEX idx_consultor (id_consultor),
        INDEX idx_miembro (id_miembro),
        INDEX idx_tipo_evento (tipo_evento),
        INDEX idx_estado (estado),
        INDEX idx_fecha_evento (fecha_evento)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
";

// 3. Tabla de testigos
$sentencias['CREATE tbl_investigacion_testigos'] = "
    CREATE TABLE IF NOT EXISTS tbl_investigacion_testigos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_investigacion INT NOT NULL,
        nombre VARCHAR(255) NOT NULL,
        cargo VARCHAR(150) NULL,
        declaracion TEXT NULL,
        FOREIGN KEY (id_investigacion) REFERENCES tbl_investigacion_accidente(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
";

// 4. Tabla de evidencia fotográfica
$sentencias['CREATE tbl_investigacion_evidencia'] = "
    CREATE TABLE IF NOT EXISTS tbl_investigacion_evidencia (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_investigacion INT NOT NULL,
        descripcion VARCHAR(500) NULL,
        imagen VARCHAR(255) NULL,
        orden INT DEFAULT 1,
        FOREIGN KEY (id_investigacion) REFERENCES tbl_investigacion_accidente(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
";

// 5. Tabla de medidas correctivas / plan de acción (Art. 12 Res. 1401/2007)
$sentencias['CREATE tbl_investigacion_medidas'] = "
    CREATE TABLE IF NOT EXISTS tbl_investigacion_medidas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_investigacion INT NOT NULL,
        tipo_medida ENUM('fuente','medio','trabajador') NOT NULL,
        descripcion TEXT NOT NULL,
        responsable VARCHAR(255) NULL,
        fecha_cumplimiento DATE NULL,
        estado ENUM('pendiente','en_proceso','cumplida') DEFAULT 'pendiente',
        FOREIGN KEY (id_investigacion) REFERENCES tbl_investigacion_accidente(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
";

// Ejecutar
$ok = 0;
$fail = 0;
foreach ($sentencias as $nombre => $sql) {
    try {
        $pdo->exec($sql);
        echo "[OK] $nombre\n";
        $ok++;
    } catch (PDOException $e) {
        echo "[FAIL] $nombre => " . $e->getMessage() . "\n";
        $fail++;
    }
}

echo "\nResultado: $ok exitosos, $fail fallidos\n";
