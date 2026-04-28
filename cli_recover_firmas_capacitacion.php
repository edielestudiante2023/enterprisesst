<?php
/**
 * RECOVERY: Firmas de Acta de Capacitacion huerfanas
 *
 * Detecta archivos PNG en uploads/inspecciones/firmas_capacitacion/ cuyo asistente
 * fue borrado de tbl_acta_capacitacion_asistente (bug del Guardar borrador).
 *
 * MODOS:
 *   1) Diagnostico (default): solo lista huerfanas vs validas
 *      php cli_recover_firmas_capacitacion.php
 *
 *   2) Asociar a un acta especifica: re-inserta registros placeholder con el
 *      firma_path correspondiente. El usuario despues debe completar nombre, etc.
 *      php cli_recover_firmas_capacitacion.php --acta=ID_ACTA
 *
 *   3) Ambiente: --env=local (default) o --env=prod
 *
 * Variables de entorno para prod (DigitalOcean/MySQL SSL):
 *   DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT, DB_SSL_CA
 */

// === Parseo de args ===
$args = [];
foreach ($argv as $a) {
    if (preg_match('/^--([a-z_]+)(?:=(.*))?$/', $a, $m)) {
        $args[$m[1]] = $m[2] ?? true;
    }
}
$env  = $args['env'] ?? 'local';
$acta = isset($args['acta']) ? (int)$args['acta'] : null;
$apply = !empty($args['apply']);

// === Conexion BD ===
function dbConnect(string $env): mysqli {
    if ($env === 'prod') {
        $host = getenv('DB_HOST') ?: '';
        $user = getenv('DB_USER') ?: '';
        $pass = getenv('DB_PASS') ?: '';
        $name = getenv('DB_NAME') ?: '';
        $port = (int)(getenv('DB_PORT') ?: 25060);
        $sslCa = getenv('DB_SSL_CA') ?: null;
        if (!$host || !$user || !$name) {
            fwrite(STDERR, "[ERROR] Faltan variables de entorno DB_HOST/DB_USER/DB_NAME para prod\n");
            exit(1);
        }
        $mysqli = mysqli_init();
        if ($sslCa && file_exists($sslCa)) {
            $mysqli->ssl_set(null, null, $sslCa, null, null);
        }
        $mysqli->real_connect($host, $user, $pass, $name, $port, null,
            $sslCa ? MYSQLI_CLIENT_SSL : 0);
    } else {
        $mysqli = new mysqli('localhost', 'root', '', 'empresas_sst', 3306);
    }
    if ($mysqli->connect_error) {
        fwrite(STDERR, "[ERROR] Conexion: " . $mysqli->connect_error . "\n");
        exit(1);
    }
    $mysqli->set_charset('utf8mb4');
    return $mysqli;
}

$mysqli = dbConnect($env);
echo "Conectado a BD ($env): " . $mysqli->host_info . "\n\n";

// === Escaneo de archivos ===
$dir = __DIR__ . '/public/uploads/inspecciones/firmas_capacitacion/';
if (!is_dir($dir)) {
    echo "[INFO] Directorio no existe: $dir\n";
    echo "       (Aun no se han registrado firmas en este ambiente)\n";
    exit(0);
}

$files = glob($dir . 'firma_cap_*.png');
echo "Archivos PNG encontrados en disco: " . count($files) . "\n\n";

if (empty($files)) {
    echo "[OK] No hay archivos. Nada que recuperar.\n";
    exit(0);
}

// === Cruce con BD ===
$huerfanas = [];
$validas   = [];
foreach ($files as $f) {
    $base = basename($f);
    if (!preg_match('/^firma_cap_(\d+)_(\d+)\.png$/', $base, $m)) continue;
    $idAsistente = (int)$m[1];
    $tsFirma     = (int)$m[2];
    $relPath     = 'uploads/inspecciones/firmas_capacitacion/' . $base;

    $stmt = $mysqli->prepare("SELECT id, id_acta_capacitacion, nombre_completo, firma_path FROM tbl_acta_capacitacion_asistente WHERE id = ?");
    $stmt->bind_param('i', $idAsistente);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $info = [
        'file'        => $base,
        'idAsistente' => $idAsistente,
        'tsFirma'     => $tsFirma,
        'fechaFirma'  => date('Y-m-d H:i:s', $tsFirma),
        'relPath'     => $relPath,
    ];

    if (!$row) {
        $huerfanas[] = $info;
    } else {
        $info['record'] = $row;
        $validas[] = $info;
    }
}

// === Reporte ===
echo "=== HUERFANAS (archivo en disco, asistente borrado de BD) ===\n";
if (empty($huerfanas)) {
    echo "  [OK] Ninguna.\n";
} else {
    foreach ($huerfanas as $h) {
        printf("  ID=%d  archivo=%s  firmado=%s\n", $h['idAsistente'], $h['file'], $h['fechaFirma']);
    }
}

echo "\n=== VALIDAS (archivo + registro en BD) ===\n";
if (empty($validas)) {
    echo "  [INFO] Ninguna.\n";
} else {
    foreach ($validas as $v) {
        printf("  ID=%d  archivo=%s  acta=%d  nombre=%s\n",
            $v['idAsistente'], $v['file'],
            $v['record']['id_acta_capacitacion'],
            $v['record']['nombre_completo']);
    }
}

// === Modo recovery (re-asociar a un acta) ===
if ($acta && !empty($huerfanas)) {
    echo "\n=== RECOVERY a acta ID=$acta ===\n";

    // Verificar acta
    $stmt = $mysqli->prepare("SELECT id, tema, fecha_capacitacion FROM tbl_acta_capacitacion WHERE id = ?");
    $stmt->bind_param('i', $acta);
    $stmt->execute();
    $actaRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$actaRow) {
        echo "[ERROR] Acta ID=$acta no existe.\n";
        exit(1);
    }
    echo "Acta destino: '{$actaRow['tema']}' fecha {$actaRow['fecha_capacitacion']}\n\n";

    if (!$apply) {
        echo "[DRY-RUN] Se reinsertarian " . count($huerfanas) . " asistentes placeholder. Agregar --apply para ejecutar.\n";
        exit(0);
    }

    foreach ($huerfanas as $i => $h) {
        $orden    = $i + 1;
        $nombre   = '[Recuperado] Asistente firma #' . $h['idAsistente'];
        $firmaAt  = $h['fechaFirma'];
        $firmaRel = $h['relPath'];

        // Forzar id original para que firma_path no se rompa
        $stmt = $mysqli->prepare("INSERT INTO tbl_acta_capacitacion_asistente
            (id, id_acta_capacitacion, nombre_completo, tipo_documento, orden,
             firma_path, firmado_at, created_at)
            VALUES (?, ?, ?, 'CC', ?, ?, ?, NOW())");
        $stmt->bind_param('iisiss', $h['idAsistente'], $acta, $nombre, $orden, $firmaRel, $firmaAt);
        if ($stmt->execute()) {
            echo "  [OK]    ID={$h['idAsistente']} reasociado al acta $acta\n";
        } else {
            echo "  [FAIL]  ID={$h['idAsistente']}: " . $stmt->error . "\n";
        }
        $stmt->close();
    }

    echo "\n[OK] Recovery completado. Edita el acta para completar nombres reales.\n";
}

$mysqli->close();
