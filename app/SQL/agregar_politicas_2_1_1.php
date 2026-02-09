<?php
/**
 * Script para agregar las politicas del numeral 2.1.1 a la BD
 *
 * Ejecutar LOCAL: php app/SQL/agregar_politicas_2_1_1.php
 * Ejecutar PROD:  php app/SQL/agregar_politicas_2_1_1.php --prod
 *
 * Este script agrega:
 * - politica_alcohol_drogas
 * - politica_acoso_laboral
 * - politica_violencias_genero
 * - politica_discriminacion
 */

// Detectar entorno
$isProd = in_array('--prod', $argv ?? []);

if ($isProd) {
    // Configuracion PRODUCCION (DigitalOcean)
    $config = [
        'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port' => 25060,
        'database' => 'empresas_sst',
        'username' => 'cycloid_userdb',
        'password' => 'AVNS_iDypWizlpMRwHIORJGG',
        'ssl' => true
    ];
    echo "*** MODO PRODUCCION ***\n";
} else {
    // Configuracion LOCAL
    $config = [
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'empresas_sst',
        'username' => 'root',
        'password' => '',
        'ssl' => false
    ];
    echo "*** MODO LOCAL ***\n";
}

// SQL para insertar los tipos de politica
$sqlTipos = <<<'SQL'
-- Insertar tipos de politica para numeral 2.1.1
INSERT INTO tbl_doc_tipo_configuracion
(tipo_documento, nombre, descripcion, estandar, flujo, categoria, icono, orden, activo)
VALUES
('politica_alcohol_drogas',
 'Politica de Prevencion del Consumo de Alcohol, Tabaco y SPA',
 'Politica que establece el compromiso de la empresa con la prevencion del consumo de alcohol, tabaco y sustancias psicoactivas',
 '2.1.1',
 'secciones_ia',
 'politicas',
 'bi-cup-straw',
 12,
 1),
('politica_acoso_laboral',
 'Politica de Prevencion del Acoso Laboral',
 'Politica que establece el compromiso de la empresa con la prevencion y sancion del acoso laboral conforme a la Ley 1010 de 2006',
 '2.1.1',
 'secciones_ia',
 'politicas',
 'bi-person-x',
 13,
 1),
('politica_violencias_genero',
 'Politica de Prevencion del Acoso Sexual y Violencias de Genero',
 'Politica que establece el compromiso de la empresa con la prevencion del acoso sexual y violencias de genero',
 '2.1.1',
 'secciones_ia',
 'politicas',
 'bi-gender-ambiguous',
 14,
 1),
('politica_discriminacion',
 'Politica de Prevencion de la Discriminacion, Maltrato y Violencia',
 'Politica que establece el compromiso de la empresa con la prevencion de la discriminacion, maltrato y violencia laboral',
 '2.1.1',
 'secciones_ia',
 'politicas',
 'bi-people',
 15,
 1)
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    descripcion = VALUES(descripcion),
    activo = 1,
    updated_at = NOW();
SQL;

// SQL para insertar secciones de politica_alcohol_drogas
$sqlSeccionesAlcohol = <<<'SQL'
INSERT INTO tbl_doc_secciones_config
(id_tipo_config, numero, nombre, seccion_key, tipo_contenido, orden, prompt_ia)
SELECT tc.id_tipo_config, s.numero, s.nombre, s.seccion_key, 'texto', s.orden, s.prompt_ia
FROM tbl_doc_tipo_configuracion tc
CROSS JOIN (
    SELECT 1 as numero, 'Objetivo' as nombre, 'objetivo' as seccion_key, 1 as orden, 'Genera el objetivo de la Politica de Prevencion del Consumo de Alcohol, Tabaco y SPA. Maximo 2-3 parrafos.' as prompt_ia
    UNION SELECT 2, 'Alcance', 'alcance', 2, 'Define el alcance de la politica.'
    UNION SELECT 3, 'Declaracion de la Politica', 'declaracion', 3, 'Genera la declaracion formal de la politica.'
    UNION SELECT 4, 'Definiciones', 'definiciones', 4, 'Define los terminos clave: alcohol, tabaco, SPA, etc.'
    UNION SELECT 5, 'Prohibiciones', 'prohibiciones', 5, 'Lista las prohibiciones especificas.'
    UNION SELECT 6, 'Programa de Prevencion', 'programa_prevencion', 6, 'Describe el programa de prevencion.'
    UNION SELECT 7, 'Sanciones', 'sanciones', 7, 'Describe las sanciones aplicables.'
    UNION SELECT 8, 'Marco Legal', 'marco_legal', 8, 'Lista el marco normativo aplicable.'
    UNION SELECT 9, 'Comunicacion y Divulgacion', 'comunicacion', 9, 'Define como se comunicara la politica.'
) s
WHERE tc.tipo_documento = 'politica_alcohol_drogas'
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);
SQL;

// SQL para insertar secciones de politica_acoso_laboral
$sqlSeccionesAcoso = <<<'SQL'
INSERT INTO tbl_doc_secciones_config
(id_tipo_config, numero, nombre, seccion_key, tipo_contenido, orden, prompt_ia)
SELECT tc.id_tipo_config, s.numero, s.nombre, s.seccion_key, 'texto', s.orden, s.prompt_ia
FROM tbl_doc_tipo_configuracion tc
CROSS JOIN (
    SELECT 1 as numero, 'Objetivo' as nombre, 'objetivo' as seccion_key, 1 as orden, 'Genera el objetivo de la Politica de Prevencion del Acoso Laboral.' as prompt_ia
    UNION SELECT 2, 'Alcance', 'alcance', 2, 'Define el alcance de la politica.'
    UNION SELECT 3, 'Declaracion de la Politica', 'declaracion', 3, 'Genera la declaracion formal de la politica.'
    UNION SELECT 4, 'Definiciones y Modalidades de Acoso', 'definiciones', 4, 'Define el acoso laboral y sus modalidades segun Ley 1010.'
    UNION SELECT 5, 'Conductas Constitutivas de Acoso Laboral', 'conductas', 5, 'Lista las conductas que constituyen acoso laboral.'
    UNION SELECT 6, 'Conductas NO Constitutivas de Acoso', 'conductas_no_acoso', 6, 'Lista las conductas que NO constituyen acoso laboral.'
    UNION SELECT 7, 'Mecanismos de Prevencion', 'mecanismos_prevencion', 7, 'Describe los mecanismos de prevencion.'
    UNION SELECT 8, 'Procedimiento de Denuncia', 'procedimiento_denuncia', 8, 'Describe el procedimiento para denunciar.'
    UNION SELECT 9, 'Marco Legal', 'marco_legal', 9, 'Lista el marco normativo aplicable.'
    UNION SELECT 10, 'Comunicacion y Divulgacion', 'comunicacion', 10, 'Define como se comunicara la politica.'
) s
WHERE tc.tipo_documento = 'politica_acoso_laboral'
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);
SQL;

// SQL para insertar secciones de politica_violencias_genero
$sqlSeccionesViolencias = <<<'SQL'
INSERT INTO tbl_doc_secciones_config
(id_tipo_config, numero, nombre, seccion_key, tipo_contenido, orden, prompt_ia)
SELECT tc.id_tipo_config, s.numero, s.nombre, s.seccion_key, 'texto', s.orden, s.prompt_ia
FROM tbl_doc_tipo_configuracion tc
CROSS JOIN (
    SELECT 1 as numero, 'Objetivo' as nombre, 'objetivo' as seccion_key, 1 as orden, 'Genera el objetivo de la Politica de Prevencion del Acoso Sexual y Violencias de Genero.' as prompt_ia
    UNION SELECT 2, 'Alcance', 'alcance', 2, 'Define el alcance de la politica.'
    UNION SELECT 3, 'Declaracion de la Politica', 'declaracion', 3, 'Genera la declaracion formal de la politica.'
    UNION SELECT 4, 'Definiciones', 'definiciones', 4, 'Define los conceptos clave segun Ley 1257 de 2008.'
    UNION SELECT 5, 'Conductas Prohibidas', 'conductas_prohibidas', 5, 'Lista las conductas prohibidas.'
    UNION SELECT 6, 'Mecanismos de Prevencion', 'mecanismos_prevencion', 6, 'Describe los mecanismos de prevencion.'
    UNION SELECT 7, 'Procedimiento de Denuncia y Atencion', 'procedimiento', 7, 'Describe el procedimiento de denuncia.'
    UNION SELECT 8, 'Sanciones', 'sanciones', 8, 'Describe las sanciones aplicables.'
    UNION SELECT 9, 'Marco Legal', 'marco_legal', 9, 'Lista el marco normativo aplicable.'
    UNION SELECT 10, 'Comunicacion y Divulgacion', 'comunicacion', 10, 'Define como se comunicara la politica.'
) s
WHERE tc.tipo_documento = 'politica_violencias_genero'
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);
SQL;

// SQL para insertar secciones de politica_discriminacion
$sqlSeccionesDiscriminacion = <<<'SQL'
INSERT INTO tbl_doc_secciones_config
(id_tipo_config, numero, nombre, seccion_key, tipo_contenido, orden, prompt_ia)
SELECT tc.id_tipo_config, s.numero, s.nombre, s.seccion_key, 'texto', s.orden, s.prompt_ia
FROM tbl_doc_tipo_configuracion tc
CROSS JOIN (
    SELECT 1 as numero, 'Objetivo' as nombre, 'objetivo' as seccion_key, 1 as orden, 'Genera el objetivo de la Politica de Prevencion de la Discriminacion.' as prompt_ia
    UNION SELECT 2, 'Alcance', 'alcance', 2, 'Define el alcance de la politica.'
    UNION SELECT 3, 'Declaracion de la Politica', 'declaracion', 3, 'Genera la declaracion formal de la politica.'
    UNION SELECT 4, 'Definiciones', 'definiciones', 4, 'Define los conceptos clave relacionados con discriminacion.'
    UNION SELECT 5, 'Principios Rectores', 'principios', 5, 'Describe los principios rectores de la politica.'
    UNION SELECT 6, 'Conductas Prohibidas', 'conductas_prohibidas', 6, 'Lista las conductas prohibidas.'
    UNION SELECT 7, 'Mecanismos de Prevencion', 'mecanismos_prevencion', 7, 'Describe los mecanismos de prevencion.'
    UNION SELECT 8, 'Procedimiento de Denuncia', 'procedimiento', 8, 'Describe el procedimiento de denuncia.'
    UNION SELECT 9, 'Marco Legal', 'marco_legal', 9, 'Lista el marco normativo aplicable.'
    UNION SELECT 10, 'Comunicacion y Divulgacion', 'comunicacion', 10, 'Define como se comunicara la politica.'
) s
WHERE tc.tipo_documento = 'politica_discriminacion'
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);
SQL;

// SQL para insertar firmantes de las politicas (igual para todas)
$sqlFirmantes = <<<'SQL'
INSERT INTO tbl_doc_firmantes_config
(id_tipo_config, firmante_tipo, rol_display, columna_encabezado, orden, mostrar_licencia)
SELECT tc.id_tipo_config, f.firmante_tipo, f.rol_display, f.columna_encabezado, f.orden, f.mostrar_licencia
FROM tbl_doc_tipo_configuracion tc
CROSS JOIN (
    SELECT 'consultor_sst' as firmante_tipo, 'Elaboro' as rol_display, 'Elaboro / Consultor SST' as columna_encabezado, 1 as orden, 1 as mostrar_licencia
    UNION SELECT 'representante_legal', 'Aprobo', 'Aprobo / Representante Legal', 2, 0
) f
WHERE tc.tipo_documento IN ('politica_alcohol_drogas', 'politica_acoso_laboral', 'politica_violencias_genero', 'politica_discriminacion')
ON DUPLICATE KEY UPDATE rol_display = VALUES(rol_display);
SQL;

// Funcion para ejecutar
function ejecutar($config, $sqls) {
    echo "\n========================================================\n";
    echo "  AGREGAR POLITICAS 2.1.1 A LA BD\n";
    echo "========================================================\n";

    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ];
        // SSL para produccion
        if (!empty($config['ssl'])) {
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
            $options[PDO::MYSQL_ATTR_SSL_CA] = false;
        }
        $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        echo "Conectado a {$config['host']}:{$config['port']}\n\n";

        foreach ($sqls as $descripcion => $sql) {
            echo "Ejecutando: $descripcion... ";
            try {
                $pdo->exec($sql);
                echo "OK\n";
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'Duplicate') !== false) {
                    echo "(ya existe)\n";
                } else {
                    echo "ERROR: " . $e->getMessage() . "\n";
                }
            }
        }

        // Verificar
        echo "\n--- Verificacion ---\n";
        $stmt = $pdo->query("SELECT tipo_documento, nombre FROM tbl_doc_tipo_configuracion WHERE tipo_documento LIKE 'politica_%' AND activo = 1 ORDER BY orden");
        echo "\nPoliticas configuradas:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  - {$row['tipo_documento']}\n";
        }

        $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_doc_secciones_config sc JOIN tbl_doc_tipo_configuracion tc ON sc.id_tipo_config = tc.id_tipo_config WHERE tc.tipo_documento LIKE 'politica_%'");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        echo "\nTotal secciones de politicas: $count\n";

        $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_doc_firmantes_config fc JOIN tbl_doc_tipo_configuracion tc ON fc.id_tipo_config = tc.id_tipo_config WHERE tc.tipo_documento LIKE 'politica_%'");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        echo "Total firmantes de politicas: $count\n";

        echo "\n========================================================\n";
        echo "  COMPLETADO\n";
        echo "========================================================\n";

    } catch (PDOException $e) {
        echo "Error de conexion: " . $e->getMessage() . "\n";
    }
}

// Ejecutar
$sqls = [
    'Tipos de politica' => $sqlTipos,
    'Secciones: politica_alcohol_drogas' => $sqlSeccionesAlcohol,
    'Secciones: politica_acoso_laboral' => $sqlSeccionesAcoso,
    'Secciones: politica_violencias_genero' => $sqlSeccionesViolencias,
    'Secciones: politica_discriminacion' => $sqlSeccionesDiscriminacion,
    'Firmantes de politicas' => $sqlFirmantes
];

ejecutar($config, $sqls);
