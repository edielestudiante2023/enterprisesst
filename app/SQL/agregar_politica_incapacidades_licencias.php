<?php
/**
 * Script para agregar la Politica de Gestion de Incapacidades y Licencias (2.1.1) a la BD
 *
 * Ejecutar LOCAL: php app/SQL/agregar_politica_incapacidades_licencias.php
 * Ejecutar PROD:  php app/SQL/agregar_politica_incapacidades_licencias.php --prod
 *
 * Este script agrega:
 * - politica_incapacidades_licencias (basada en Ley 2466 de 2025 + CST + normativa complementaria)
 */

// Detectar entorno
$isProd = in_array('--prod', $argv ?? []);

if ($isProd) {
    // Configuracion PRODUCCION (DigitalOcean)
    $config = [
        'host'     => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port'     => 25060,
        'database' => 'empresas_sst',
        'username' => 'cycloid_userdb',
        'password' => 'AVNS_iDypWizlpMRwHIORJGG',
        'ssl'      => true
    ];
    echo "*** MODO PRODUCCION ***\n";
} else {
    // Configuracion LOCAL
    $config = [
        'host'     => 'localhost',
        'port'     => 3306,
        'database' => 'empresas_sst',
        'username' => 'root',
        'password' => '',
        'ssl'      => false
    ];
    echo "*** MODO LOCAL ***\n";
}

// SQL para insertar el tipo de politica
$sqlTipo = <<<'SQL'
-- Insertar tipo de politica de incapacidades y licencias
INSERT INTO tbl_doc_tipo_configuracion
(tipo_documento, nombre, descripcion, estandar, flujo, categoria, icono, orden, activo)
VALUES
('politica_incapacidades_licencias',
 'Politica de Gestion de Incapacidades y Licencias',
 'Politica que establece los lineamientos para la gestion responsable de incapacidades y licencias, garantizando los derechos de los trabajadores conforme a la Ley 2466 de 2025 y la normativa complementaria',
 '2.1.1',
 'secciones_ia',
 'politicas',
 'fas fa-file-medical',
 17,
 1)
ON DUPLICATE KEY UPDATE
    nombre      = VALUES(nombre),
    descripcion = VALUES(descripcion),
    activo      = 1,
    updated_at  = NOW();
SQL;

// SQL para insertar secciones
$sqlSecciones = <<<'SQL'
INSERT INTO tbl_doc_secciones_config
(id_tipo_config, numero, nombre, seccion_key, tipo_contenido, orden, prompt_ia)
SELECT tc.id_tipo_config, s.numero, s.nombre, s.seccion_key, 'texto', s.orden, s.prompt_ia
FROM tbl_doc_tipo_configuracion tc
CROSS JOIN (
    SELECT 1 as numero, 'Objetivo' as nombre, 'objetivo' as seccion_key, 1 as orden,
           'Genera el objetivo de la Politica de Gestion de Incapacidades y Licencias. Compromiso con: gestion responsable de incapacidades y licencias, cumplimiento Ley 2466/2025 y normativa complementaria, proteger derechos de trabajadores durante incapacidad o licencia, promover reintegro seguro. Maximo 2-3 parrafos.' as prompt_ia
    UNION SELECT 2, 'Alcance', 'alcance', 2,
           'Define el alcance: todos los trabajadores (contrato a termino fijo, indefinido, temporal), todos los tipos de incapacidad (EPS/ARL), todas las licencias (maternidad, paternidad, luto, citas medicas, obligaciones escolares, dia bicicleta - Ley 2466/2025), personal de RR.HH. y jefes inmediatos.'
    UNION SELECT 3, 'Declaracion de la Politica', 'declaracion', 3,
           'Genera la declaracion formal de la politica en primera persona plural. Compromiso con: pago oportuno, conceder licencias legales (incluyendo nuevas Ley 2466/2025), prohibicion de despedir por enfermedad o salud mental (Art. 17 Ley 2466/2025), confidencialidad diagnosticos, facilitar reintegro digno.'
    UNION SELECT 4, 'Definiciones', 'definiciones', 4,
           'Define: incapacidad temporal, incapacidad de origen comun (EPS), incapacidad de origen laboral (ARL), licencia remunerada, licencia no remunerada, reintegro laboral, ajuste razonable para reintegro progresivo.'
    UNION SELECT 5, 'Tipos de Licencias y Permisos', 'tipos_licencias', 5,
           'Describe en tabla: Maternidad (18 semanas, EPS), Paternidad (2 semanas, EPS), Luto (5 dias habiles, Ley 1280/2009), Calamidad domestica (hasta 5 dias), Cita medica (nueva Ley 2466/2025 - tiempo necesario con certificado), Obligacion escolar (nueva Ley 2466/2025), Dia de bicicleta (nueva Ley 2466/2025, 1 dia/semestre), Citaciones judiciales/administrativas.'
    UNION SELECT 6, 'Manejo de Incapacidades', 'manejo_incapacidades', 6,
           'Tres escenarios: 1) EPS: dias 1-3 empleador paga 66.67%, dias 4-180 EPS paga 66.67%, dias 181+ Fondo Pension. 2) ARL: desde dia 1 ARL paga 100%, reportar en 2 dias habiles. 3) Prohibiciones: no descontar incapacidades de vacaciones, no exigir reintegro sin alta medica, no despedir por enfermedad (Art. 17 Ley 2466/2025).'
    UNION SELECT 7, 'Derechos y Obligaciones', 'derechos_obligaciones', 7,
           'DERECHOS TRABAJADOR: pago oportuno, no ser despedido por enfermedad o salud mental (Art. 17 Ley 2466/2025), recibir licencias legales, reintegrarse al cargo, confidencialidad diagnostico, ajuste razonable, proteccion discapacidad (Ley 361/1997). OBLIGACIONES EMPLEADOR: pagar dias 1-3, reportar ARL en 2 dias, conceder licencias, mantener contrato. OBLIGACIONES TRABAJADOR: informar oportunamente, presentar certificado en 2 dias habiles, seguir tratamiento.'
    UNION SELECT 8, 'Procedimiento de Reporte y Gestion', 'procedimiento', 8,
           'Flujo 7 pasos: 1) Trabajador avisa al jefe y RR.HH. 2) Entrega certificado en max 2 dias habiles. 3) RR.HH. registra en nomina. 4) Gestion segun origen (EPS dias 1-3 empleador, ARL reportar en 48h). 5) Comunicacion interna. 6) Certificado de alta al reintegro. 7) Seguimiento incapacidades prolongadas.'
    UNION SELECT 9, 'Marco Legal', 'marco_legal', 9,
           'Normativa: Constitucion Art. 49, CST Arts. 227-228 (incapacidades), CST Arts. 236-238 (maternidad), Ley 100/1993, Decreto 1295/1994 (ARL), Ley 361/1997 (discapacidad), Ley 1280/2009 (luto), Ley 1780/2016 (paternidad), Decreto 1072/2015, Resolucion 0312/2019, Ley 2338/2023 (endometriosis), Ley 2466/2025 (Reforma Laboral Art. 15, 17, 45).'
    UNION SELECT 10, 'Comunicacion y Divulgacion', 'comunicacion', 10,
           'Define comunicacion: COPASST/Vigia SST segun estandares, publicacion en carteleras e intranet, inclusion en induccion/reinduccion, socializacion con jefes y nomina, capacitacion anual, canal de consultas en RR.HH., revision anual o cuando cambie normativa.'
) s
WHERE tc.tipo_documento = 'politica_incapacidades_licencias'
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), prompt_ia = VALUES(prompt_ia);
SQL;

// SQL para insertar firmantes
$sqlFirmantes = <<<'SQL'
INSERT INTO tbl_doc_firmantes_config
(id_tipo_config, firmante_tipo, rol_display, columna_encabezado, orden, mostrar_licencia)
SELECT tc.id_tipo_config, f.firmante_tipo, f.rol_display, f.columna_encabezado, f.orden, f.mostrar_licencia
FROM tbl_doc_tipo_configuracion tc
CROSS JOIN (
    SELECT 'consultor_sst'       as firmante_tipo, 'Elaboro'  as rol_display, 'Elaboro / Consultor SST'         as columna_encabezado, 1 as orden, 1 as mostrar_licencia
    UNION SELECT 'representante_legal', 'Aprobo', 'Aprobo / Representante Legal', 2, 0
) f
WHERE tc.tipo_documento = 'politica_incapacidades_licencias'
ON DUPLICATE KEY UPDATE rol_display = VALUES(rol_display);
SQL;

// Funcion para ejecutar
function ejecutar($config, $sqls) {
    echo "\n========================================================\n";
    echo "  AGREGAR POLITICA DE INCAPACIDADES Y LICENCIAS A LA BD\n";
    echo "========================================================\n";

    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
        $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
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

        // Verificacion
        echo "\n--- Verificacion ---\n";
        $stmt = $pdo->query("SELECT tipo_documento, nombre FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'politica_incapacidades_licencias'");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            echo "Politica configurada:\n";
            echo "  - {$row['tipo_documento']}: {$row['nombre']}\n";
        } else {
            echo "ERROR: No se encontro la politica en la BD\n";
        }

        $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_doc_secciones_config sc JOIN tbl_doc_tipo_configuracion tc ON sc.id_tipo_config = tc.id_tipo_config WHERE tc.tipo_documento = 'politica_incapacidades_licencias'");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        echo "Secciones de la politica: $count (esperado: 10)\n";

        $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_doc_firmantes_config fc JOIN tbl_doc_tipo_configuracion tc ON fc.id_tipo_config = tc.id_tipo_config WHERE tc.tipo_documento = 'politica_incapacidades_licencias'");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        echo "Firmantes de la politica: $count (esperado: 2)\n";

        echo "\n========================================================\n";
        echo "  COMPLETADO\n";
        echo "========================================================\n";

    } catch (PDOException $e) {
        echo "Error de conexion: " . $e->getMessage() . "\n";
    }
}

$sqls = [
    'Tipo de politica'                           => $sqlTipo,
    'Secciones: politica_incapacidades_licencias' => $sqlSecciones,
    'Firmantes de la politica'                   => $sqlFirmantes
];

ejecutar($config, $sqls);
