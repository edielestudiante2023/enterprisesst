<?php
/**
 * IPEVR GTC 45 - Fase 3a: Tablas operacionales
 *
 * Crea las 3 tablas operacionales de la matriz IPEVR:
 *   - tbl_ipevr_matriz          (header por cliente, versionamiento)
 *   - tbl_ipevr_fila            (22 columnas GTC 45 por fila)
 *   - tbl_ipevr_control_cambios (historial de versiones)
 *
 * Reutiliza:
 *   - tbl_clientes (FK id_cliente)
 *   - Catalogos GTC 45 de Fase 1 (FK id_nd, id_ne, id_nc, id_clasificacion, id_peligro_catalogo, id_nivel_riesgo)
 *   - Maestros cliente de Fase 2 (FK id_proceso, id_zona, id_tarea)
 *
 * NO crea tbl_ipevr_firmas: se reutiliza infraestructura existente de firmas.
 *
 * Uso:
 *   php scripts/ipevr_gtc45_fase3a.php             # LOCAL
 *   php scripts/ipevr_gtc45_fase3a.php --env=prod  # PRODUCCION
 */

$esProduccion = in_array('--env=prod', $argv ?? []);

if ($esProduccion) {
    $host     = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port     = 25060;
    $dbname   = 'empresas_sst';
    $username = 'cycloid_userdb';
    $password = getenv('DB_PROD_PASS') ?: 'AVNS_MR2SLvzRh3i_7o9fEHN';
    $ssl      = true;
    echo "=== PRODUCCION ===\n";
} else {
    $host     = '127.0.0.1';
    $port     = 3306;
    $dbname   = 'empresas_sst';
    $username = 'root';
    $password = '';
    $ssl      = false;
    echo "=== LOCAL ===\n";
}

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    if ($ssl) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = true;
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "Conexion OK\n\n";
} catch (Throwable $e) {
    echo "ERROR conexion: " . $e->getMessage() . "\n";
    exit(1);
}

$run = function(string $label, string $sql) use ($pdo) {
    try {
        $pdo->exec($sql);
        echo "  OK  {$label}\n";
    } catch (Throwable $e) {
        echo "  ERR {$label}: " . $e->getMessage() . "\n";
    }
};

echo "-- Paso 1: CREATE TABLES operacionales IPEVR --\n";

$run('tbl_ipevr_matriz', "
CREATE TABLE IF NOT EXISTS tbl_ipevr_matriz (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    nombre VARCHAR(200) NOT NULL DEFAULT 'Matriz IPEVR GTC 45',
    version VARCHAR(10) NOT NULL DEFAULT '001',
    estado ENUM('borrador','revision','aprobada','vigente','historica') NOT NULL DEFAULT 'borrador',
    id_consultor INT NULL,
    elaborado_por VARCHAR(150) NULL,
    revisado_por  VARCHAR(150) NULL,
    aprobado_por  VARCHAR(150) NULL,
    fecha_creacion DATE NULL,
    fecha_aprobacion DATE NULL,
    fecha_proxima_revision DATE NULL,
    observaciones TEXT NULL,
    snapshot_json LONGTEXT NULL COMMENT 'Snapshot al pasar a vigente (versionamiento)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_cliente (id_cliente),
    KEY idx_estado (estado),
    KEY idx_cliente_version (id_cliente, version),
    CONSTRAINT fk_ipevr_matriz_cliente FOREIGN KEY (id_cliente)
        REFERENCES tbl_clientes(id_cliente) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$run('tbl_ipevr_fila', "
CREATE TABLE IF NOT EXISTS tbl_ipevr_fila (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_matriz INT NOT NULL,
    orden INT NOT NULL DEFAULT 0,

    -- Seccion 1: Informacion proceso/actividad/tarea
    id_proceso INT NULL,
    proceso_texto VARCHAR(200) NULL COMMENT 'Texto libre fallback si no hay maestro',
    id_zona INT NULL,
    zona_texto VARCHAR(200) NULL,
    actividad VARCHAR(300) NULL,
    id_tarea INT NULL,
    tarea_texto VARCHAR(300) NULL,
    rutinaria TINYINT(1) NOT NULL DEFAULT 1,
    cargos_expuestos JSON NULL COMMENT 'Array de nombres o IDs de cargos',
    num_expuestos SMALLINT NOT NULL DEFAULT 0,

    -- Seccion 2: Identificacion de peligros
    id_peligro_catalogo INT NULL,
    descripcion_peligro TEXT NULL,
    id_clasificacion INT NULL,
    efectos_posibles TEXT NULL,

    -- Seccion 3: Controles existentes
    control_fuente TEXT NULL,
    control_medio TEXT NULL,
    control_individuo TEXT NULL,

    -- Seccion 4: Evaluacion del riesgo
    id_nd INT NULL,
    id_ne INT NULL,
    np SMALLINT NULL COMMENT 'Nivel Probabilidad = ND*NE (calc)',
    id_np INT NULL COMMENT 'Interpretacion NP (FK a nivel_probabilidad)',
    id_nc INT NULL,
    nr SMALLINT NULL COMMENT 'Nivel Riesgo = NP*NC (calc)',
    id_nivel_riesgo INT NULL COMMENT 'Interpretacion NR (I-IV)',
    aceptabilidad VARCHAR(80) NULL,

    -- Seccion 5: Criterios
    peor_consecuencia TEXT NULL,
    requisito_legal TEXT NULL,

    -- Seccion 6: Medidas de intervencion
    medida_eliminacion TEXT NULL,
    medida_sustitucion TEXT NULL,
    medida_ingenieria TEXT NULL,
    medida_administrativa TEXT NULL,
    medida_epp TEXT NULL,

    -- Metadata
    origen_fila ENUM('ia','manual','importada') NOT NULL DEFAULT 'manual',
    estado_fila ENUM('borrador','completa','revisada') NOT NULL DEFAULT 'borrador',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    KEY idx_matriz (id_matriz),
    KEY idx_matriz_orden (id_matriz, orden),
    KEY idx_proceso (id_proceso),
    KEY idx_zona (id_zona),
    KEY idx_tarea (id_tarea),
    KEY idx_peligro (id_peligro_catalogo),
    KEY idx_clasif (id_clasificacion),
    KEY idx_nd (id_nd),
    KEY idx_ne (id_ne),
    KEY idx_nc (id_nc),
    KEY idx_nr (id_nivel_riesgo),

    CONSTRAINT fk_ipevr_fila_matriz FOREIGN KEY (id_matriz)
        REFERENCES tbl_ipevr_matriz(id) ON DELETE CASCADE,
    CONSTRAINT fk_ipevr_fila_proceso FOREIGN KEY (id_proceso)
        REFERENCES tbl_procesos_cliente(id) ON DELETE SET NULL,
    CONSTRAINT fk_ipevr_fila_zona FOREIGN KEY (id_zona)
        REFERENCES tbl_zonas_cliente(id) ON DELETE SET NULL,
    CONSTRAINT fk_ipevr_fila_tarea FOREIGN KEY (id_tarea)
        REFERENCES tbl_tareas_cliente(id) ON DELETE SET NULL,
    CONSTRAINT fk_ipevr_fila_peligro FOREIGN KEY (id_peligro_catalogo)
        REFERENCES tbl_gtc45_peligro_catalogo(id) ON DELETE SET NULL,
    CONSTRAINT fk_ipevr_fila_clasif FOREIGN KEY (id_clasificacion)
        REFERENCES tbl_gtc45_clasificacion_peligro(id) ON DELETE SET NULL,
    CONSTRAINT fk_ipevr_fila_nd FOREIGN KEY (id_nd)
        REFERENCES tbl_gtc45_nivel_deficiencia(id) ON DELETE SET NULL,
    CONSTRAINT fk_ipevr_fila_ne FOREIGN KEY (id_ne)
        REFERENCES tbl_gtc45_nivel_exposicion(id) ON DELETE SET NULL,
    CONSTRAINT fk_ipevr_fila_np FOREIGN KEY (id_np)
        REFERENCES tbl_gtc45_nivel_probabilidad(id) ON DELETE SET NULL,
    CONSTRAINT fk_ipevr_fila_nc FOREIGN KEY (id_nc)
        REFERENCES tbl_gtc45_nivel_consecuencia(id) ON DELETE SET NULL,
    CONSTRAINT fk_ipevr_fila_nr FOREIGN KEY (id_nivel_riesgo)
        REFERENCES tbl_gtc45_nivel_riesgo(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$run('tbl_ipevr_control_cambios', "
CREATE TABLE IF NOT EXISTS tbl_ipevr_control_cambios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_matriz INT NOT NULL,
    version VARCHAR(10) NOT NULL,
    descripcion TEXT NOT NULL,
    fecha DATE NOT NULL,
    usuario VARCHAR(150) NULL,
    id_usuario INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_matriz (id_matriz),
    KEY idx_matriz_version (id_matriz, version),
    CONSTRAINT fk_ipevr_cc_matriz FOREIGN KEY (id_matriz)
        REFERENCES tbl_ipevr_matriz(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

echo "\n-- Paso 2: VERIFICACION --\n";
$tablas = ['tbl_ipevr_matriz','tbl_ipevr_fila','tbl_ipevr_control_cambios'];
$todoOk = true;
foreach ($tablas as $t) {
    $existe = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($t))->fetchAll();
    if ($existe) {
        $c = (int)$pdo->query("SELECT COUNT(*) FROM {$t}")->fetchColumn();
        $cols = (int)$pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=" . $pdo->quote($t))->fetchColumn();
        echo "  OK  {$t} (filas: {$c}, columnas: {$cols})\n";
    } else {
        echo "  ERR {$t} NO existe\n";
        $todoOk = false;
    }
}

echo "\n" . ($todoOk ? "FASE 3a COMPLETADA OK" : "FASE 3a CON ERRORES") . "\n";
exit($todoOk ? 0 : 1);
