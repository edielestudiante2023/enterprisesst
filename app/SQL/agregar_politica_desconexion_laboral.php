<?php
/**
 * Script para agregar la Politica de Desconexion Laboral (2.1.1) a la BD
 *
 * Ejecutar LOCAL: php app/SQL/agregar_politica_desconexion_laboral.php
 * Ejecutar PROD:  php app/SQL/agregar_politica_desconexion_laboral.php --prod
 *
 * Este script agrega:
 * - politica_desconexion_laboral (basada en Ley 2191 de 2022)
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

// SQL para insertar el tipo de politica
$sqlTipo = <<<'SQL'
-- Insertar tipo de politica de desconexion laboral
INSERT INTO tbl_doc_tipo_configuracion
(tipo_documento, nombre, descripcion, estandar, flujo, categoria, icono, orden, activo)
VALUES
('politica_desconexion_laboral',
 'Politica de Desconexion Laboral',
 'Politica que establece el compromiso de la empresa con el derecho a la desconexion laboral, garantizando el equilibrio entre vida laboral y personal conforme a la Ley 2191 de 2022',
 '2.1.1',
 'secciones_ia',
 'politicas',
 'bi-power',
 16,
 1)
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    descripcion = VALUES(descripcion),
    activo = 1,
    updated_at = NOW();
SQL;

// SQL para insertar secciones de politica_desconexion_laboral
$sqlSecciones = <<<'SQL'
INSERT INTO tbl_doc_secciones_config
(id_tipo_config, numero, nombre, seccion_key, tipo_contenido, orden, prompt_ia)
SELECT tc.id_tipo_config, s.numero, s.nombre, s.seccion_key, 'texto', s.orden, s.prompt_ia
FROM tbl_doc_tipo_configuracion tc
CROSS JOIN (
    SELECT 1 as numero, 'Objetivo' as nombre, 'objetivo' as seccion_key, 1 as orden,
           'Genera el objetivo de la Politica de Desconexion Laboral. Debe expresar el compromiso con el derecho a la desconexion (Ley 2191 de 2022), equilibrio vida laboral-personal, salud mental. Maximo 2-3 parrafos.' as prompt_ia
    UNION SELECT 2, 'Alcance', 'alcance', 2,
           'Define el alcance: todos los trabajadores (presencial, teletrabajo, remoto, hibrido), todos los cargos, todas las comunicaciones (correo, WhatsApp, Teams, etc.), jornada laboral, dias de descanso, festivos y vacaciones.'
    UNION SELECT 3, 'Declaracion de la Politica', 'declaracion', 3,
           'Genera la declaracion formal de la politica. Compromiso con respetar desconexion fuera de horario, no exigir disponibilidad permanente, promover descanso, proteger salud mental. Formato: primera persona plural. Tono formal y empatico.'
    UNION SELECT 4, 'Definiciones', 'definiciones', 4,
           'Define: desconexion laboral, jornada laboral, herramientas digitales, excepciones, teletrabajo/trabajo remoto, derecho al descanso.'
    UNION SELECT 5, 'Horarios de Conexion y Desconexion', 'horarios', 5,
           'Define jornada laboral estandar, franjas de no contacto (noches, fines de semana, festivos), excepciones autorizadas (guardias, emergencias). Mencionar que horarios especificos estan en contrato.'
    UNION SELECT 6, 'Buenas Practicas', 'buenas_practicas', 6,
           'Lista buenas practicas: no enviar correos/mensajes fuera de horario, usar programacion de envio, evitar llamadas fuera de jornada, respetar descansos, no expectativas de respuesta inmediata, reuniones en horario laboral, uso responsable de WhatsApp.'
    UNION SELECT 7, 'Derechos del Trabajador', 'derechos', 7,
           'Describe derechos: no responder fuera de jornada, desactivar notificaciones, no ser sancionado, descanso efectivo, conciliar vida laboral-personal, proteccion contra represalias. Basarse en Ley 2191 de 2022.'
    UNION SELECT 8, 'Excepciones', 'excepciones', 8,
           'Define excepciones: emergencias operativas (compensadas), guardias pactadas y remuneradas, fuerza mayor, responsabilidades jerarquicas excepcionales (con acuerdo escrito). Deben ser justificadas, documentadas y compensadas.'
    UNION SELECT 9, 'Marco Legal', 'marco_legal', 9,
           'Lista normativa: Ley 2191 de 2022, Codigo Sustantivo del Trabajo, Decreto 1072 de 2015, Resolucion 0312 de 2019, Ley 1221 de 2008 (Teletrabajo), Ley 2088 de 2021 (Trabajo en casa), Constitucion Politica Art. 53, Resolucion 2646 de 2008.'
    UNION SELECT 10, 'Comunicacion y Divulgacion', 'comunicacion', 10,
           'Define comunicacion: COPASST/Vigia SST, publicacion en intranet/carteleras, induccion/reinduccion, capacitacion periodica, canales de reporte, revision anual, socializacion con lideres.'
) s
WHERE tc.tipo_documento = 'politica_desconexion_laboral'
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), prompt_ia = VALUES(prompt_ia);
SQL;

// SQL para insertar firmantes
$sqlFirmantes = <<<'SQL'
INSERT INTO tbl_doc_firmantes_config
(id_tipo_config, firmante_tipo, rol_display, columna_encabezado, orden, mostrar_licencia)
SELECT tc.id_tipo_config, f.firmante_tipo, f.rol_display, f.columna_encabezado, f.orden, f.mostrar_licencia
FROM tbl_doc_tipo_configuracion tc
CROSS JOIN (
    SELECT 'consultor_sst' as firmante_tipo, 'Elaboro' as rol_display, 'Elaboro / Consultor SST' as columna_encabezado, 1 as orden, 1 as mostrar_licencia
    UNION SELECT 'representante_legal', 'Aprobo', 'Aprobo / Representante Legal', 2, 0
) f
WHERE tc.tipo_documento = 'politica_desconexion_laboral'
ON DUPLICATE KEY UPDATE rol_display = VALUES(rol_display);
SQL;

// Funcion para ejecutar
function ejecutar($config, $sqls) {
    echo "\n========================================================\n";
    echo "  AGREGAR POLITICA DE DESCONEXION LABORAL A LA BD\n";
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
        $stmt = $pdo->query("SELECT tipo_documento, nombre FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'politica_desconexion_laboral'");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            echo "\nPolitica configurada:\n";
            echo "  - {$row['tipo_documento']}: {$row['nombre']}\n";
        } else {
            echo "\nERROR: No se encontro la politica en la BD\n";
        }

        $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_doc_secciones_config sc JOIN tbl_doc_tipo_configuracion tc ON sc.id_tipo_config = tc.id_tipo_config WHERE tc.tipo_documento = 'politica_desconexion_laboral'");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        echo "Secciones de la politica: $count\n";

        $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_doc_firmantes_config fc JOIN tbl_doc_tipo_configuracion tc ON fc.id_tipo_config = tc.id_tipo_config WHERE tc.tipo_documento = 'politica_desconexion_laboral'");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        echo "Firmantes de la politica: $count\n";

        echo "\n========================================================\n";
        echo "  COMPLETADO\n";
        echo "========================================================\n";

    } catch (PDOException $e) {
        echo "Error de conexion: " . $e->getMessage() . "\n";
    }
}

// Ejecutar
$sqls = [
    'Tipo de politica' => $sqlTipo,
    'Secciones: politica_desconexion_laboral' => $sqlSecciones,
    'Firmantes de la politica' => $sqlFirmantes
];

ejecutar($config, $sqls);
