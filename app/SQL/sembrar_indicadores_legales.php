<?php
/**
 * Migración: Sembrar 18 Indicadores Legales Obligatorios en TODOS los clientes
 * Decreto 1072/2015 (Arts. 2.2.4.6.19-22) + Resolución 0312/2019 (Art. 30)
 *
 * - Detecta duplicados por keywords (no duplica si ya existe uno similar)
 * - Corrige tipo_indicador y categoría de los mal clasificados
 * - Marca es_minimo_obligatorio=1 en los 6 mínimos (IF, IS, PATM, PEL, IEL, ACM)
 *
 * Ejecutar: php app/SQL/sembrar_indicadores_legales.php
 */

$conexiones = [
    'local' => [
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'empresas_sst',
        'username' => 'root',
        'password' => '',
        'ssl' => false
    ],
    'produccion' => [
        'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port' => 25060,
        'database' => 'empresas_sst',
        'username' => 'cycloid_userdb',
        'password' => 'AVNS_iDypWizlpMRwHIORJGG',
        'ssl' => true
    ]
];

/**
 * Los 18 indicadores legales obligatorios
 * keywords: para detectar si ya existe un indicador similar
 */
$indicadoresLegales = [
    // ESTRUCTURA (Art. 2.2.4.6.19) — PLANEAR
    [
        'nombre_indicador'      => 'Disponibilidad de Recursos del SG-SST',
        'tipo_indicador'        => 'estructura',
        'categoria'             => 'objetivos_sgsst',
        'formula'               => '(Recursos disponibles / Recursos planeados) × 100',
        'meta'                  => 100,
        'unidad_medida'         => '%',
        'periodicidad'          => 'anual',
        'numeral_resolucion'    => 'Art. 2.2.4.6.19 D.1072',
        'phva'                  => 'planear',
        'es_minimo_obligatorio' => 0,
        'keywords'              => ['estructura', 'recursos', 'disponibilidad']
    ],
    // PROCESO (Art. 2.2.4.6.20) — HACER
    [
        'nombre_indicador'      => 'Evaluación Inicial del SG-SST',
        'tipo_indicador'        => 'proceso',
        'categoria'             => 'pta',
        'formula'               => '(Ítems evaluados cumplidos / Total ítems evaluados) × 100',
        'meta'                  => 100,
        'unidad_medida'         => '%',
        'periodicidad'          => 'anual',
        'numeral_resolucion'    => 'Art. 2.2.4.6.16 D.1072',
        'phva'                  => 'hacer',
        'es_minimo_obligatorio' => 0,
        'keywords'              => ['evaluación inicial', 'evaluacion inicial']
    ],
    [
        'nombre_indicador'      => 'Cumplimiento del Plan de Trabajo Anual',
        'tipo_indicador'        => 'proceso',
        'categoria'             => 'pta',
        'formula'               => '(Actividades ejecutadas / Actividades programadas PTA) × 100',
        'meta'                  => 100,
        'unidad_medida'         => '%',
        'periodicidad'          => 'trimestral',
        'numeral_resolucion'    => 'Art. 2.2.4.6.20 D.1072',
        'phva'                  => 'hacer',
        'es_minimo_obligatorio' => 0,
        'keywords'              => ['plan de trabajo', 'pta', 'plan anual']
    ],
    [
        'nombre_indicador'      => 'Cumplimiento del Programa de Capacitación',
        'tipo_indicador'        => 'proceso',
        'categoria'             => 'capacitacion',
        'formula'               => '(Capacitaciones ejecutadas / Capacitaciones programadas) × 100',
        'meta'                  => 100,
        'unidad_medida'         => '%',
        'periodicidad'          => 'trimestral',
        'numeral_resolucion'    => 'Art. 2.2.4.6.11 D.1072',
        'phva'                  => 'hacer',
        'es_minimo_obligatorio' => 0,
        'keywords'              => ['programa de capacitación', 'programa de capacitacion', 'cronograma de capacitación', 'cronograma de capacitacion']
    ],
    [
        'nombre_indicador'      => 'Intervención de Peligros Identificados (Matriz IPVR)',
        'tipo_indicador'        => 'proceso',
        'categoria'             => 'riesgos',
        'formula'               => '(Peligros intervenidos / Peligros identificados) × 100',
        'meta'                  => 100,
        'unidad_medida'         => '%',
        'periodicidad'          => 'semestral',
        'numeral_resolucion'    => 'Art. 2.2.4.6.23 D.1072',
        'phva'                  => 'hacer',
        'es_minimo_obligatorio' => 0,
        'keywords'              => ['peligros identificados', 'matriz ipvr', 'intervención de peligros', 'intervencion de peligros']
    ],
    [
        'nombre_indicador'      => 'Cumplimiento Programas de Vigilancia Epidemiológica',
        'tipo_indicador'        => 'proceso',
        'categoria'             => 'vigilancia',
        'formula'               => '(Actividades PVE ejecutadas / Actividades PVE programadas) × 100',
        'meta'                  => 100,
        'unidad_medida'         => '%',
        'periodicidad'          => 'trimestral',
        'numeral_resolucion'    => 'Art. 2.2.4.6.24 D.1072',
        'phva'                  => 'hacer',
        'es_minimo_obligatorio' => 0,
        'keywords'              => ['vigilancia epidemiológica', 'vigilancia epidemiologica', 'pve']
    ],
    [
        'nombre_indicador'      => 'Eficacia de Acciones Preventivas, Correctivas y de Mejora',
        'tipo_indicador'        => 'proceso',
        'categoria'             => 'objetivos_sgsst',
        'formula'               => '(Acciones cerradas eficazmente / Total acciones generadas) × 100',
        'meta'                  => 90,
        'unidad_medida'         => '%',
        'periodicidad'          => 'trimestral',
        'numeral_resolucion'    => 'Art. 2.2.4.6.33 D.1072',
        'phva'                  => 'actuar',
        'es_minimo_obligatorio' => 0,
        'keywords'              => ['acciones preventivas', 'acciones correctivas', 'eficacia de acciones']
    ],
    [
        'nombre_indicador'      => 'Investigación de Incidentes y Accidentes de Trabajo',
        'tipo_indicador'        => 'proceso',
        'categoria'             => 'accidentalidad',
        'formula'               => '(Incidentes/accidentes investigados / Total reportados) × 100',
        'meta'                  => 100,
        'unidad_medida'         => '%',
        'periodicidad'          => 'trimestral',
        'numeral_resolucion'    => 'Art. 2.2.4.6.32 D.1072',
        'phva'                  => 'hacer',
        'es_minimo_obligatorio' => 0,
        'keywords'              => ['investigación de incidentes', 'investigacion de incidentes', 'investigación de accidentes', 'investigacion de accidentes', 'reporte e investigación']
    ],
    // RESULTADO (Art. 2.2.4.6.21) — VERIFICAR/ACTUAR
    [
        'nombre_indicador'      => 'Cumplimiento de Objetivos del SG-SST',
        'tipo_indicador'        => 'resultado',
        'categoria'             => 'objetivos_sgsst',
        'formula'               => '(Objetivos cumplidos / Total objetivos definidos) × 100',
        'meta'                  => 100,
        'unidad_medida'         => '%',
        'periodicidad'          => 'anual',
        'numeral_resolucion'    => 'Art. 2.2.4.6.18 D.1072',
        'phva'                  => 'verificar',
        'es_minimo_obligatorio' => 0,
        'keywords'              => ['cumplimiento de objetivos', 'objetivos del sg-sst', 'objetivos del sgsst']
    ],
    [
        'nombre_indicador'      => 'Cumplimiento de Requisitos Legales Aplicables',
        'tipo_indicador'        => 'resultado',
        'categoria'             => 'objetivos_sgsst',
        'formula'               => '(Requisitos legales cumplidos / Total requisitos identificados) × 100',
        'meta'                  => 100,
        'unidad_medida'         => '%',
        'periodicidad'          => 'semestral',
        'numeral_resolucion'    => 'Art. 2.2.4.6.8 D.1072',
        'phva'                  => 'verificar',
        'es_minimo_obligatorio' => 0,
        'keywords'              => ['requisitos legales', 'cumplimiento legal', 'matriz legal']
    ],
    [
        'nombre_indicador'      => 'Resultados de Programas de Rehabilitación',
        'tipo_indicador'        => 'resultado',
        'categoria'             => 'vigilancia',
        'formula'               => '(Trabajadores reintegrados exitosamente / Total en rehabilitación) × 100',
        'meta'                  => 100,
        'unidad_medida'         => '%',
        'periodicidad'          => 'semestral',
        'numeral_resolucion'    => 'Art. 2.2.4.6.22 D.1072',
        'phva'                  => 'verificar',
        'es_minimo_obligatorio' => 0,
        'keywords'              => ['rehabilitación', 'rehabilitacion', 'reintegro']
    ],
    // 6 MÍNIMOS OBLIGATORIOS — Res. 0312/2019 Art. 30
    [
        'nombre_indicador'      => 'Índice de Frecuencia de Accidentes de Trabajo (IF)',
        'tipo_indicador'        => 'resultado',
        'categoria'             => 'accidentalidad',
        'formula'               => '(N° accidentes de trabajo en el periodo / HHT en el periodo) × 240.000',
        'meta'                  => null,
        'unidad_medida'         => 'por 240.000 HHT',
        'periodicidad'          => 'mensual',
        'numeral_resolucion'    => 'Art. 30 Res. 0312/2019',
        'phva'                  => 'verificar',
        'es_minimo_obligatorio' => 1,
        'keywords'              => ['frecuencia de accidentes', 'índice de frecuencia', 'indice de frecuencia', 'IF ']
    ],
    [
        'nombre_indicador'      => 'Índice de Severidad de Accidentes de Trabajo (IS)',
        'tipo_indicador'        => 'resultado',
        'categoria'             => 'accidentalidad',
        'formula'               => '(N° días perdidos y cargados por AT / HHT en el periodo) × 240.000',
        'meta'                  => null,
        'unidad_medida'         => 'por 240.000 HHT',
        'periodicidad'          => 'mensual',
        'numeral_resolucion'    => 'Art. 30 Res. 0312/2019',
        'phva'                  => 'verificar',
        'es_minimo_obligatorio' => 1,
        'keywords'              => ['severidad de accidentes', 'índice de severidad', 'indice de severidad', 'IS ']
    ],
    [
        'nombre_indicador'      => 'Proporción de Accidentes de Trabajo Mortales (PATM)',
        'tipo_indicador'        => 'resultado',
        'categoria'             => 'accidentalidad',
        'formula'               => '(N° accidentes de trabajo mortales / Total accidentes de trabajo) × 100',
        'meta'                  => 0,
        'unidad_medida'         => '%',
        'periodicidad'          => 'anual',
        'numeral_resolucion'    => 'Art. 30 Res. 0312/2019',
        'phva'                  => 'verificar',
        'es_minimo_obligatorio' => 1,
        'keywords'              => ['accidentes mortales', 'mortalidad', 'proporción de accidentes de trabajo mortales', 'PATM']
    ],
    [
        'nombre_indicador'      => 'Prevalencia de Enfermedad Laboral (PEL)',
        'tipo_indicador'        => 'resultado',
        'categoria'             => 'vigilancia',
        'formula'               => '(N° casos nuevos y antiguos de EL / N° promedio trabajadores año) × 100.000',
        'meta'                  => null,
        'unidad_medida'         => 'por 100.000',
        'periodicidad'          => 'anual',
        'numeral_resolucion'    => 'Art. 30 Res. 0312/2019',
        'phva'                  => 'verificar',
        'es_minimo_obligatorio' => 1,
        'keywords'              => ['prevalencia', 'enfermedad laboral', 'PEL']
    ],
    [
        'nombre_indicador'      => 'Incidencia de Enfermedad Laboral (IEL)',
        'tipo_indicador'        => 'resultado',
        'categoria'             => 'vigilancia',
        'formula'               => '(N° casos nuevos de EL en el periodo / N° promedio trabajadores año) × 100.000',
        'meta'                  => null,
        'unidad_medida'         => 'por 100.000',
        'periodicidad'          => 'anual',
        'numeral_resolucion'    => 'Art. 30 Res. 0312/2019',
        'phva'                  => 'verificar',
        'es_minimo_obligatorio' => 1,
        'keywords'              => ['incidencia', 'enfermedad laboral', 'IEL']
    ],
    [
        'nombre_indicador'      => 'Ausentismo por Causa Médica (ACM)',
        'tipo_indicador'        => 'resultado',
        'categoria'             => 'ausentismo',
        'formula'               => '(N° días de ausencia por causa médica / N° días de trabajo programados) × 100',
        'meta'                  => null,
        'unidad_medida'         => '%',
        'periodicidad'          => 'mensual',
        'numeral_resolucion'    => 'Art. 30 Res. 0312/2019',
        'phva'                  => 'verificar',
        'es_minimo_obligatorio' => 1,
        'keywords'              => ['ausentismo', 'causa médica', 'causa medica', 'ACM']
    ],
];

function ejecutarSiembra($nombre, $config, $indicadoresLegales) {
    echo "\n" . str_repeat("=", 70) . "\n";
    echo "SEMBRANDO INDICADORES LEGALES: $nombre\n";
    echo str_repeat("=", 70) . "\n";

    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];

        if ($config['ssl']) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = true;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        echo "  Conectado a {$config['host']}:{$config['port']}\n\n";

        // Ampliar numeral_resolucion si es varchar(20)
        $stmtCol = $pdo->query("SHOW COLUMNS FROM tbl_indicadores_sst WHERE Field = 'numeral_resolucion'");
        $col = $stmtCol->fetch();
        if ($col && strpos($col['Type'], 'varchar(20)') !== false) {
            echo "  Ampliando numeral_resolucion de varchar(20) a varchar(50)... ";
            $pdo->exec("ALTER TABLE tbl_indicadores_sst MODIFY COLUMN numeral_resolucion VARCHAR(50) DEFAULT NULL");
            echo "OK\n\n";
        }

        // Obtener TODOS los clientes activos
        $stmtClientes = $pdo->query("SELECT id_cliente, nombre_cliente FROM tbl_clientes WHERE estado = 'activo' ORDER BY id_cliente");
        $clientes = $stmtClientes->fetchAll();
        $totalClientes = count($clientes);

        echo "  Clientes activos encontrados: $totalClientes\n\n";

        if ($totalClientes === 0) {
            echo "  No hay clientes activos. Nada que hacer.\n";
            return true;
        }

        $totalCreados = 0;
        $totalCorregidos = 0;
        $totalExistentes = 0;

        foreach ($clientes as $cliente) {
            $idCliente = $cliente['id_cliente'];
            $nombreCliente = $cliente['nombre_cliente'];

            echo "  ── Cliente #{$idCliente}: {$nombreCliente}\n";

            // Obtener indicadores existentes de este cliente
            $stmtExist = $pdo->prepare("SELECT id_indicador, nombre_indicador, tipo_indicador, categoria, es_minimo_obligatorio FROM tbl_indicadores_sst WHERE id_cliente = ? AND activo = 1");
            $stmtExist->execute([$idCliente]);
            $existentes = $stmtExist->fetchAll();

            $nombresExistentes = [];
            foreach ($existentes as $ind) {
                $nombresExistentes[] = [
                    'id'     => $ind['id_indicador'],
                    'nombre' => mb_strtolower($ind['nombre_indicador']),
                    'tipo'   => $ind['tipo_indicador'],
                    'cat'    => $ind['categoria'],
                    'min'    => $ind['es_minimo_obligatorio'],
                ];
            }

            $creados = 0;
            $corregidos = 0;
            $yaExisten = 0;

            foreach ($indicadoresLegales as $legal) {
                $keywords = $legal['keywords'] ?? [];
                $encontrado = null;

                // Buscar duplicado por keyword
                foreach ($nombresExistentes as $ex) {
                    foreach ($keywords as $kw) {
                        if (mb_stripos($ex['nombre'], mb_strtolower($kw)) !== false) {
                            $encontrado = $ex;
                            break 2;
                        }
                    }
                }

                if ($encontrado) {
                    // Ya existe: verificar si necesita corrección
                    $updates = [];

                    if ($encontrado['tipo'] !== $legal['tipo_indicador']) {
                        $updates[] = "tipo_indicador = " . $pdo->quote($legal['tipo_indicador']);
                    }
                    if ($encontrado['cat'] !== $legal['categoria']) {
                        $updates[] = "categoria = " . $pdo->quote($legal['categoria']);
                    }
                    if ($legal['es_minimo_obligatorio'] && !$encontrado['min']) {
                        $updates[] = "es_minimo_obligatorio = 1";
                    }

                    if (!empty($updates)) {
                        $sql = "UPDATE tbl_indicadores_sst SET " . implode(', ', $updates) . " WHERE id_indicador = " . (int)$encontrado['id'];
                        $pdo->exec($sql);
                        $corregidos++;
                    } else {
                        $yaExisten++;
                    }
                } else {
                    // No existe: crear
                    $stmt = $pdo->prepare("INSERT INTO tbl_indicadores_sst (id_cliente, nombre_indicador, tipo_indicador, categoria, formula, meta, unidad_medida, periodicidad, numeral_resolucion, phva, es_minimo_obligatorio, activo, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())");
                    $stmt->execute([
                        $idCliente,
                        $legal['nombre_indicador'],
                        $legal['tipo_indicador'],
                        $legal['categoria'],
                        $legal['formula'],
                        $legal['meta'],
                        $legal['unidad_medida'],
                        $legal['periodicidad'],
                        $legal['numeral_resolucion'],
                        $legal['phva'],
                        $legal['es_minimo_obligatorio'],
                    ]);
                    $creados++;
                }
            }

            echo "     Creados: $creados | Corregidos: $corregidos | Ya existían: $yaExisten\n";
            $totalCreados += $creados;
            $totalCorregidos += $corregidos;
            $totalExistentes += $yaExisten;
        }

        echo "\n" . str_repeat("-", 50) . "\n";
        echo "  RESUMEN $nombre:\n";
        echo "    Clientes procesados: $totalClientes\n";
        echo "    Indicadores CREADOS:    $totalCreados\n";
        echo "    Indicadores CORREGIDOS: $totalCorregidos\n";
        echo "    Ya existían:            $totalExistentes\n";

        // Verificación final
        $stmtVerif = $pdo->query("SELECT COUNT(*) as total FROM tbl_indicadores_sst WHERE activo = 1");
        echo "    Total indicadores activos en BD: " . $stmtVerif->fetch()['total'] . "\n";

        $stmtMin = $pdo->query("SELECT COUNT(*) as total FROM tbl_indicadores_sst WHERE es_minimo_obligatorio = 1 AND activo = 1");
        echo "    Total marcados como mínimo obligatorio: " . $stmtMin->fetch()['total'] . "\n";

        echo "\n  $nombre COMPLETADO\n";
        return true;

    } catch (PDOException $e) {
        echo "  ERROR: " . $e->getMessage() . "\n";
        return false;
    }
}

echo "\n";
echo str_repeat("*", 70) . "\n";
echo "  MIGRACIÓN: Siembra de 18 Indicadores Legales Obligatorios\n";
echo "  Decreto 1072/2015 + Resolución 0312/2019\n";
echo "  Aplica a TODOS los clientes activos\n";
echo str_repeat("*", 70) . "\n";

// 1) LOCAL primero
$resultadoLocal = ejecutarSiembra('LOCAL', $conexiones['local'], $indicadoresLegales);

if (!$resultadoLocal) {
    echo "\n  LOCAL FALLÓ - NO se ejecutará en PRODUCCIÓN\n";
    exit(1);
}

// 2) PRODUCCIÓN solo si LOCAL OK
$resultadoProduccion = ejecutarSiembra('PRODUCCION', $conexiones['produccion'], $indicadoresLegales);

// Resumen final
echo "\n" . str_repeat("=", 70) . "\n";
echo "RESUMEN FINAL\n";
echo str_repeat("=", 70) . "\n";
echo "LOCAL:      " . ($resultadoLocal ? "OK" : "FALLO") . "\n";
echo "PRODUCCION: " . ($resultadoProduccion ? "OK" : "FALLO") . "\n";
echo str_repeat("=", 70) . "\n";
