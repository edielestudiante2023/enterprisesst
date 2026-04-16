<?php
/**
 * Diccionario de Competencias - Fase 2: Registro en motor de documentos
 *
 * Inserta el documento 'diccionario_competencias_cliente' en:
 *   tbl_doc_tipo_configuracion
 *   tbl_doc_secciones_config
 *   tbl_doc_firmantes_config
 *
 * Idempotente: si ya existe el tipo_documento, NO sobreescribe (protege prompts
 * editados por el admin via /listSeccionesConfig). Para forzar re-seed, pasar --force.
 *
 * Uso:
 *   php scripts/diccionario_competencias_config.php             # LOCAL
 *   php scripts/diccionario_competencias_config.php --env=prod  # PRODUCCION
 *   php scripts/diccionario_competencias_config.php --force     # borrar y reinsertar
 *
 * Ver: docs/MODULO_DICCIONARIO_COMPETENCIAS/ARQUITECTURA.md §5
 */

$esProduccion = in_array('--env=prod', $argv ?? []);
$forzar       = in_array('--force', $argv ?? []);

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
if ($forzar) echo "(modo --force: reemplaza secciones/firmantes existentes)\n";

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

// ---------------------- DEFINICIONES ----------------------

$TIPO = 'diccionario_competencias_cliente';

$tipoConfig = [
    'tipo_documento' => $TIPO,
    'nombre'         => 'Diccionario de Competencias',
    'descripcion'    => 'Catalogo de competencias del cliente con escala, rubricas por nivel y matriz de asignacion por cargo.',
    'estandar'       => null,
    'flujo'          => 'secciones_ia',
    'categoria'      => 'talento_humano',
    'icono'          => 'bi-person-check',
    'orden'          => 999,
    'activo'         => 1,
];

$secciones = [
    [
        'numero'           => 1,
        'seccion_key'      => 'objetivo',
        'nombre'           => 'Objetivo',
        'tipo_contenido'   => 'texto',
        'sincronizar_bd'   => null,
        'es_obligatoria'   => 1,
        'prompt_ia'        => 'Redacta el objetivo del Diccionario de Competencias del cliente {nombre_empresa}. Debe explicar que el documento define las competencias requeridas para el desempeno de los cargos de la organizacion y sirve como marco para procesos de seleccion, evaluacion, desarrollo y plan de formacion. Maximo 1 parrafo, tono formal institucional.',
    ],
    [
        'numero'           => 2,
        'seccion_key'      => 'alcance',
        'nombre'           => 'Alcance',
        'tipo_contenido'   => 'texto',
        'sincronizar_bd'   => null,
        'es_obligatoria'   => 1,
        'prompt_ia'        => 'Redacta el alcance del Diccionario de Competencias para {nombre_empresa}. Aclara que aplica a todos los cargos definidos en la estructura organizacional del cliente y que su uso es transversal a talento humano, SST y gerencia. 1 parrafo corto.',
    ],
    [
        'numero'           => 3,
        'seccion_key'      => 'marco_conceptual',
        'nombre'           => 'Marco conceptual de competencias',
        'tipo_contenido'   => 'texto',
        'sincronizar_bd'   => null,
        'es_obligatoria'   => 1,
        'prompt_ia'        => 'Redacta un marco conceptual breve (3-4 parrafos) que explique: (1) que es una competencia (saber + saber hacer + ser), (2) por que se agrupan en familias (logro, ayuda y servicio, influencia, gerenciales, cognitivas, eficacia personal), (3) para que sirve la escala de dominio 1-5, (4) como se interpreta la matriz de competencias por cargo. Tono didactico pero formal.',
    ],
    [
        'numero'           => 4,
        'seccion_key'      => 'escala_evaluacion',
        'nombre'           => 'Escala de evaluacion (1-5)',
        'tipo_contenido'   => 'tabla_dinamica',
        'sincronizar_bd'   => 'competencia_escala_cliente',
        'es_obligatoria'   => 1,
        'prompt_ia'        => null,
    ],
    [
        'numero'           => 5,
        'seccion_key'      => 'catalogo_competencias',
        'nombre'           => 'Catalogo de competencias',
        'tipo_contenido'   => 'tabla_dinamica',
        'sincronizar_bd'   => 'competencia_cliente',
        'es_obligatoria'   => 1,
        'prompt_ia'        => null,
    ],
    [
        'numero'           => 6,
        'seccion_key'      => 'matriz_cargo_competencia',
        'nombre'           => 'Matriz de competencias por cargo',
        'tipo_contenido'   => 'tabla_dinamica',
        'sincronizar_bd'   => 'cliente_competencia_cargo',
        'es_obligatoria'   => 1,
        'prompt_ia'        => null,
    ],
    [
        'numero'           => 7,
        'seccion_key'      => 'responsabilidades',
        'nombre'           => 'Responsabilidades',
        'tipo_contenido'   => 'texto',
        'sincronizar_bd'   => null,
        'es_obligatoria'   => 1,
        'prompt_ia'        => 'Redacta las responsabilidades frente al Diccionario de Competencias en {nombre_empresa}. Incluye: (a) Representante Legal/Gerencia: aprobar y garantizar aplicacion, (b) Talento Humano: mantener y actualizar diccionario y matriz, (c) Lideres de area: aplicar en seleccion y evaluacion, (d) Trabajadores: conocer las competencias requeridas de su cargo. Formato de lista con vinetas.',
    ],
];

$firmantes = [
    ['firmante_tipo' => 'representante_legal', 'rol_display' => 'Representante Legal', 'columna_encabezado' => 'Aprueba',   'orden' => 1, 'es_obligatorio' => 1],
    ['firmante_tipo' => 'responsable_sst',     'rol_display' => 'Responsable SST',     'columna_encabezado' => 'Revisa',    'orden' => 2, 'es_obligatorio' => 1],
    ['firmante_tipo' => 'consultor_sst',       'rol_display' => 'Consultor SST',       'columna_encabezado' => 'Elabora',   'orden' => 3, 'es_obligatorio' => 1],
];

// ---------------------- EJECUCION ----------------------

echo "-- Paso 1: Upsert tipo_documento --\n";

$stmt = $pdo->prepare("SELECT id_tipo_config FROM tbl_doc_tipo_configuracion WHERE tipo_documento = ?");
$stmt->execute([$TIPO]);
$idTipo = $stmt->fetchColumn();

if ($idTipo && !$forzar) {
    echo "  SKIP tipo_documento ya existe (id_tipo_config={$idTipo}). Usa --force para reemplazar secciones/firmantes.\n";
} else {
    try {
        $pdo->beginTransaction();

        if ($idTipo && $forzar) {
            $pdo->prepare("DELETE FROM tbl_doc_secciones_config WHERE id_tipo_config = ?")->execute([$idTipo]);
            $pdo->prepare("DELETE FROM tbl_doc_firmantes_config WHERE id_tipo_config = ?")->execute([$idTipo]);
            $pdo->prepare("UPDATE tbl_doc_tipo_configuracion SET nombre=?, descripcion=?, estandar=?, flujo=?, categoria=?, icono=?, orden=?, activo=? WHERE id_tipo_config=?")
                ->execute([
                    $tipoConfig['nombre'], $tipoConfig['descripcion'], $tipoConfig['estandar'],
                    $tipoConfig['flujo'], $tipoConfig['categoria'], $tipoConfig['icono'],
                    $tipoConfig['orden'], $tipoConfig['activo'], $idTipo,
                ]);
            echo "  OK  tipo_documento actualizado (id={$idTipo})\n";
        } else {
            $pdo->prepare("INSERT INTO tbl_doc_tipo_configuracion (tipo_documento,nombre,descripcion,estandar,flujo,categoria,icono,orden,activo) VALUES (?,?,?,?,?,?,?,?,?)")
                ->execute([
                    $tipoConfig['tipo_documento'], $tipoConfig['nombre'], $tipoConfig['descripcion'],
                    $tipoConfig['estandar'], $tipoConfig['flujo'], $tipoConfig['categoria'],
                    $tipoConfig['icono'], $tipoConfig['orden'], $tipoConfig['activo'],
                ]);
            $idTipo = (int)$pdo->lastInsertId();
            echo "  OK  tipo_documento insertado (id={$idTipo})\n";
        }

        echo "\n-- Paso 2: Secciones --\n";
        $sqlSec = "INSERT INTO tbl_doc_secciones_config (id_tipo_config,numero,nombre,seccion_key,prompt_ia,tipo_contenido,sincronizar_bd,es_obligatoria,orden,activo) VALUES (?,?,?,?,?,?,?,?,?,1)";
        $stmtSec = $pdo->prepare($sqlSec);
        foreach ($secciones as $s) {
            $stmtSec->execute([
                $idTipo, $s['numero'], $s['nombre'], $s['seccion_key'], $s['prompt_ia'],
                $s['tipo_contenido'], $s['sincronizar_bd'], $s['es_obligatoria'], $s['numero'],
            ]);
            echo "  OK  seccion {$s['numero']}. {$s['seccion_key']}\n";
        }

        echo "\n-- Paso 3: Firmantes --\n";
        $sqlFir = "INSERT INTO tbl_doc_firmantes_config (id_tipo_config,firmante_tipo,rol_display,columna_encabezado,orden,es_obligatorio,activo) VALUES (?,?,?,?,?,?,1)";
        $stmtFir = $pdo->prepare($sqlFir);
        foreach ($firmantes as $f) {
            $stmtFir->execute([
                $idTipo, $f['firmante_tipo'], $f['rol_display'], $f['columna_encabezado'],
                $f['orden'], $f['es_obligatorio'],
            ]);
            echo "  OK  firmante {$f['firmante_tipo']}\n";
        }

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        echo "ERROR: " . $e->getMessage() . "\n";
        exit(1);
    }
}

echo "\n-- Verificacion --\n";
$q = $pdo->prepare("SELECT COUNT(*) FROM tbl_doc_secciones_config WHERE id_tipo_config=?");
$q->execute([$idTipo]);
$cntSec = (int)$q->fetchColumn();
$q = $pdo->prepare("SELECT COUNT(*) FROM tbl_doc_firmantes_config WHERE id_tipo_config=?");
$q->execute([$idTipo]);
$cntFir = (int)$q->fetchColumn();
echo "  tipo={$TIPO} id={$idTipo} secciones={$cntSec} firmantes={$cntFir}\n";

$ok = ($cntSec === count($secciones)) && ($cntFir === count($firmantes));
echo "\n" . ($ok ? "FASE 2 COMPLETA\n" : "FASE 2 CON DIFERENCIAS\n");
exit($ok ? 0 : 1);
