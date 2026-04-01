<?php
/**
 * Script CLI: Insertar 3 indicadores base de capacitacion para todos los clientes activos
 *
 * Indicadores segun Decreto 1072/2015 (Art. 2.2.4.6.20-22):
 * 1. Cobertura de la capacitacion (resultado)
 * 2. Cumplimiento del plan de capacitacion (proceso)
 * 3. Eficacia de las capacitaciones (resultado)
 *
 * Uso: php cli_indicadores_base_capacitacion.php [local|prod]
 */

$env = $argv[1] ?? 'local';

$configs = [
    'local' => [
        'host'   => 'localhost',
        'user'   => 'root',
        'pass'   => '',
        'db'     => 'empresas_sst',
        'port'   => 3306,
        'ssl'    => false,
    ],
    'prod' => [
        'host'   => getenv('DB_PROD_HOST') ?: 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'user'   => getenv('DB_PROD_USER') ?: 'cycloid_userdb',
        'pass'   => getenv('DB_PROD_PASS') ?: '',
        'db'     => getenv('DB_PROD_NAME') ?: 'empresas_sst',
        'port'   => (int)(getenv('DB_PROD_PORT') ?: 25060),
        'ssl'    => true,
    ],
];

if (!isset($configs[$env])) {
    echo "Uso: php cli_indicadores_base_capacitacion.php [local|prod]\n";
    exit(1);
}

$cfg = $configs[$env];
echo "=== Indicadores Base Capacitacion - Entorno: {$env} ===\n\n";

// Conectar
$db = new mysqli($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['db'], $cfg['port']);
if ($db->connect_error) {
    echo "ERROR conexion: {$db->connect_error}\n";
    exit(1);
}
if ($cfg['ssl']) {
    $db->ssl_set(NULL, NULL, NULL, NULL, NULL);
    $db->real_connect($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['db'], $cfg['port'], null, MYSQLI_CLIENT_SSL);
}
$db->set_charset('utf8mb4');
echo "Conectado a {$cfg['host']}:{$cfg['port']}/{$cfg['db']}\n\n";

// 3 indicadores base de capacitacion (Decreto 1072/2015)
$indicadoresBase = [
    [
        'nombre_indicador'   => 'Cobertura de la capacitacion',
        'tipo_indicador'     => 'resultado',
        'formula'            => '(Numero de trabajadores capacitados / Total de trabajadores a capacitar) x 100',
        'meta'               => 90,
        'unidad_medida'      => '%',
        'periodicidad'       => 'trimestral',
        'phva'               => 'verificar',
        'definicion'         => 'Mide el alcance de la formacion: que porcentaje de la poblacion objetivo efectivamente recibio capacitacion en SST.',
        'interpretacion'     => 'Un resultado >= 90% indica buena cobertura. Si es menor, revisar convocatoria, horarios y modalidad de las capacitaciones.',
        'origen_datos'       => 'Registros de asistencia a capacitaciones, listado de trabajadores activos',
        'cargo_responsable'  => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, COPASST, Responsable SST',
    ],
    [
        'nombre_indicador'   => 'Cumplimiento del plan de capacitacion',
        'tipo_indicador'     => 'proceso',
        'formula'            => '(Numero de capacitaciones ejecutadas / Total de capacitaciones programadas) x 100',
        'meta'               => 90,
        'unidad_medida'      => '%',
        'periodicidad'       => 'trimestral',
        'phva'               => 'verificar',
        'definicion'         => 'Mide el porcentaje de ejecucion del cronograma de capacitacion: cuantas de las actividades planeadas se realizaron efectivamente.',
        'interpretacion'     => 'Un resultado >= 90% indica cumplimiento adecuado del cronograma. Si es menor, identificar causas de reprogramacion o cancelacion.',
        'origen_datos'       => 'Cronograma de capacitacion, actas y registros de ejecucion',
        'cargo_responsable'  => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, COPASST, Responsable SST',
    ],
    [
        'nombre_indicador'   => 'Eficacia de las capacitaciones',
        'tipo_indicador'     => 'resultado',
        'formula'            => '(Numero de trabajadores que aprobaron evaluacion / Total de trabajadores capacitados) x 100',
        'meta'               => 80,
        'unidad_medida'      => '%',
        'periodicidad'       => 'trimestral',
        'phva'               => 'verificar',
        'definicion'         => 'Evalua si la capacitacion logro transferir conocimiento: que porcentaje de los capacitados demostro comprension del tema.',
        'interpretacion'     => 'Un resultado >= 80% indica que la capacitacion fue efectiva. Si es menor, revisar metodologia, contenido y perfil del capacitador.',
        'origen_datos'       => 'Evaluaciones post-capacitacion, registros de asistencia',
        'cargo_responsable'  => 'Responsable del SG-SST',
        'cargos_conocer_resultado' => 'Gerencia, COPASST, Responsable SST',
    ],
];

// Obtener clientes activos
$result = $db->query("SELECT id_cliente, nombre_cliente FROM tbl_clientes WHERE estado = 'activo' ORDER BY id_cliente");
if (!$result || $result->num_rows === 0) {
    echo "No se encontraron clientes activos.\n";
    exit(0);
}

$clientes = [];
while ($row = $result->fetch_assoc()) {
    $clientes[] = $row;
}
echo "Clientes activos encontrados: " . count($clientes) . "\n\n";

$totalInsertados = 0;
$totalYaExisten = 0;
$errores = 0;

foreach ($clientes as $cliente) {
    $idCliente = (int)$cliente['id_cliente'];
    $nombre = $cliente['nombre_cliente'];
    echo "--- Cliente #{$idCliente}: {$nombre}\n";

    foreach ($indicadoresBase as $ind) {
        // Verificar si ya existe (por nombre similar)
        $nombreBuscar = $db->real_escape_string($ind['nombre_indicador']);
        $check = $db->query("
            SELECT COUNT(*) as total FROM tbl_indicadores_sst
            WHERE id_cliente = {$idCliente}
            AND activo = 1
            AND (
                nombre_indicador = '{$nombreBuscar}'
                OR nombre_indicador LIKE '%{$nombreBuscar}%'
            )
        ");
        $existe = $check ? (int)$check->fetch_assoc()['total'] : 0;

        if ($existe > 0) {
            echo "    [EXISTE] {$ind['nombre_indicador']}\n";
            $totalYaExisten++;
            continue;
        }

        // Insertar
        $campos = [
            'id_cliente'               => $idCliente,
            'nombre_indicador'         => $ind['nombre_indicador'],
            'tipo_indicador'           => $ind['tipo_indicador'],
            'categoria'                => 'capacitacion',
            'formula'                  => $ind['formula'],
            'meta'                     => $ind['meta'],
            'unidad_medida'            => $ind['unidad_medida'],
            'periodicidad'             => $ind['periodicidad'],
            'numeral_resolucion'       => '1.2.1',
            'phva'                     => $ind['phva'],
            'definicion'               => $ind['definicion'],
            'interpretacion'           => $ind['interpretacion'],
            'origen_datos'             => $ind['origen_datos'],
            'cargo_responsable'        => $ind['cargo_responsable'],
            'cargos_conocer_resultado' => $ind['cargos_conocer_resultado'],
            'es_minimo_obligatorio'    => 1,
            'activo'                   => 1,
        ];

        $columnas = implode(', ', array_keys($campos));
        $valores = implode(', ', array_map(function($v) use ($db) {
            if (is_int($v) || is_float($v)) return $v;
            return "'" . $db->real_escape_string($v) . "'";
        }, array_values($campos)));

        $sql = "INSERT INTO tbl_indicadores_sst ({$columnas}) VALUES ({$valores})";
        if ($db->query($sql)) {
            echo "    [CREADO] {$ind['nombre_indicador']}\n";
            $totalInsertados++;
        } else {
            echo "    [ERROR]  {$ind['nombre_indicador']}: {$db->error}\n";
            $errores++;
        }
    }
}

echo "\n=== RESUMEN ===\n";
echo "Indicadores creados:    {$totalInsertados}\n";
echo "Ya existian:            {$totalYaExisten}\n";
echo "Errores:                {$errores}\n";

$db->close();
echo "\nListo.\n";
