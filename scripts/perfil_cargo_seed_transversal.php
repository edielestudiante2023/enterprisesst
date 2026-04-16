<?php
/**
 * Perfiles de Cargo - Fase 3: Seed funciones transversales SST y TH
 *
 * Para cada cliente con estado='activo' que NO tenga datos, inserta:
 *   - Las frases SST en tbl_perfil_cargo_funcion_sst_cliente
 *   - Las frases TH en tbl_perfil_cargo_funcion_th_cliente
 *
 * Idempotente: no toca clientes que ya tengan al menos una fila.
 * Fuente: docs/Perfil Analista Contable.docx (seccion 2.1 - HSE y TALENTO HUMANO)
 *
 * Uso:
 *   php scripts/perfil_cargo_seed_transversal.php             # LOCAL
 *   php scripts/perfil_cargo_seed_transversal.php --env=prod  # PROD (solo si LOCAL OK)
 *
 * Ver: docs/MODULO_PERFILES_CARGO/ARQUITECTURA.md §10
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

// Fuente: docx Perfil Analista Contable, seccion "SEGURIDAD Y SALUD EN EL TRABAJO"
// (25 items — el docx numera 1-26 pero omite el #6)
$frasesSST = [
    'Acatamiento y seguimiento a recomendaciones de HSE.',
    'Conoce cuales son los aspectos e impactos ambientales asociados a sus actividades.',
    'Conocer el reglamento interno de trabajo, el reglamento de higiene y seguridad industrial y acoso laboral.',
    'Conocer y aplicar la mision, vision y politicas organizacionales de la organizacion.',
    'Conocer y aplicar la politica de HSE.',
    'Conocer y cumplir con los Derechos y Deberes en el sistema General de Riesgos Profesionales.',
    'Conocimiento y cumplimiento de politicas, normas y procedimientos en HSE.',
    'Cuida los elementos, equipos y dispositivos que estan a su cargo para desempenar su labor en la empresa.',
    'Cumplir con las reglas de seguridad establecidas en la empresa.',
    'Dar aviso inmediatamente a sus superiores y/o a SST sobre la existencia de riesgos que puedan producir accidentes e incidentes.',
    'Dispone adecuadamente los residuos generados resultado de sus actividades.',
    'Identificar donde se encuentran los equipos para atencion de emergencias (extintores, botiquines, camillas entre otros).',
    'Llegar al trabajo libre de influencia de alcohol, sustancias estupefacientes, enervantes o alucinogenas.',
    'Mantiene limpio y ordenado su sitio de trabajo.',
    'Participa en actividades ecologicas y cumple con las reglas establecidas de interaccion con el medio ambiente.',
    'Participacion en auditorias SST.',
    'Participacion en las actividades de SST.',
    'Participar activamente en las actividades de integracion y prevencion de factores de riesgo psicosocial en el trabajo (reuniones, comites, paseos, celebraciones y capacitaciones).',
    'Practicar el auto-cuidado e higiene personal.',
    'Realizar las actividades de forma segura, promoviendo el autocuidado, evitando generar impacto al medio ambiente, preservando los bienes de la Compania y manteniendo una actitud preventiva y proactiva.',
    'Reporte de accidentes e incidentes de trabajo oportunamente.',
    'Sabe donde se encuentran los equipos para atencion de emergencias (extintores, botiquines, camillas entre otros).',
    'Utiliza racionalmente los recursos naturales - Agua y Energia.',
    'Utilizar la dotacion, los elementos de proteccion personal y el equipo de seguridad de acuerdo a las actividades a ejecutar.',
    'Utilizar las pasarelas, escaleras y demas, que no ofrezcan peligro.',
];

// Fuente: docx Perfil Analista Contable, seccion "TALENTO HUMANO"
$frasesTH = [
    'Hacer uso de la moral y buenas costumbres.',
    'Hacer entrega de la informacion de residencia y numero telefonico actualizado cuando la empresa solicite la actualizacion de estos datos.',
    'Informar inmediatamente las incapacidades, calamidad domestica, o caso fortuito que le impida asistir a laborar, reportar al jefe inmediato.',
    'Se prohibe el uso de apodos o trato discriminatorio de cualquier forma.',
    'No usar piercings o perforaciones en el rostro. En el caso de tener expansiones en las orejas cubrirlas con cinta Micropore.',
    'Las personas tatuadas deben procurar no hacerlos visibles dentro de la empresa.',
    'Los cursos de formacion y capacitacion al interior son de estricto cumplimiento.',
];

echo "-- Fuente de datos --\n";
echo "  Frases SST: " . count($frasesSST) . "\n";
echo "  Frases TH : " . count($frasesTH) . "\n\n";

// Clientes activos
$stmt = $pdo->query("
    SELECT id_cliente, nombre_cliente
    FROM tbl_clientes
    WHERE estado = 'activo'
    ORDER BY id_cliente
");
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "-- Clientes activos encontrados: " . count($clientes) . " --\n\n";

$insertSst = $pdo->prepare("
    INSERT INTO tbl_perfil_cargo_funcion_sst_cliente (id_cliente, orden, texto, activo)
    VALUES (?, ?, ?, 1)
");
$insertTh = $pdo->prepare("
    INSERT INTO tbl_perfil_cargo_funcion_th_cliente (id_cliente, orden, texto, activo)
    VALUES (?, ?, ?, 1)
");

$checkSst = $pdo->prepare("SELECT COUNT(*) FROM tbl_perfil_cargo_funcion_sst_cliente WHERE id_cliente = ?");
$checkTh  = $pdo->prepare("SELECT COUNT(*) FROM tbl_perfil_cargo_funcion_th_cliente  WHERE id_cliente = ?");

$resumen = [
    'sst_poblados' => 0, 'sst_saltados' => 0, 'sst_filas' => 0,
    'th_poblados'  => 0, 'th_saltados'  => 0, 'th_filas'  => 0,
];

foreach ($clientes as $c) {
    $id  = (int)$c['id_cliente'];
    $nom = substr($c['nombre_cliente'], 0, 40);
    echo "  Cliente {$id} — {$nom}\n";

    // SST
    $checkSst->execute([$id]);
    $existentesSst = (int)$checkSst->fetchColumn();
    if ($existentesSst > 0) {
        echo "      SST: ya tiene {$existentesSst} filas → skip\n";
        $resumen['sst_saltados']++;
    } else {
        $pdo->beginTransaction();
        try {
            foreach ($frasesSST as $i => $texto) {
                $insertSst->execute([$id, $i + 1, $texto]);
            }
            $pdo->commit();
            $n = count($frasesSST);
            echo "      SST: insertadas {$n}\n";
            $resumen['sst_poblados']++;
            $resumen['sst_filas'] += $n;
        } catch (Throwable $e) {
            $pdo->rollBack();
            echo "      SST: ERR " . $e->getMessage() . "\n";
        }
    }

    // TH
    $checkTh->execute([$id]);
    $existentesTh = (int)$checkTh->fetchColumn();
    if ($existentesTh > 0) {
        echo "      TH : ya tiene {$existentesTh} filas → skip\n";
        $resumen['th_saltados']++;
    } else {
        $pdo->beginTransaction();
        try {
            foreach ($frasesTH as $i => $texto) {
                $insertTh->execute([$id, $i + 1, $texto]);
            }
            $pdo->commit();
            $n = count($frasesTH);
            echo "      TH : insertadas {$n}\n";
            $resumen['th_poblados']++;
            $resumen['th_filas'] += $n;
        } catch (Throwable $e) {
            $pdo->rollBack();
            echo "      TH : ERR " . $e->getMessage() . "\n";
        }
    }
}

echo "\n-- Resumen --\n";
echo "  SST: {$resumen['sst_poblados']} clientes poblados ({$resumen['sst_filas']} filas), {$resumen['sst_saltados']} saltados\n";
echo "  TH : {$resumen['th_poblados']} clientes poblados ({$resumen['th_filas']} filas), {$resumen['th_saltados']} saltados\n";

echo "\nFASE 3 COMPLETA\n";
