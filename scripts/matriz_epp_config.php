<?php
/**
 * Matriz EPP - Fase 2: Registro en motor de documentos
 *
 * Inserta el documento 'matriz_epp' (Tipo A, flujo secciones_ia) en:
 *   tbl_doc_tipo_configuracion
 *   tbl_doc_secciones_config
 *   tbl_doc_firmantes_config
 *
 * Idempotente: si ya existe el tipo_documento, SKIP (protege prompts editados).
 * Pasar --force para borrar y reinsertar secciones/firmantes.
 *
 * Uso:
 *   php scripts/matriz_epp_config.php             # LOCAL
 *   php scripts/matriz_epp_config.php --env=prod  # PRODUCCION
 *   php scripts/matriz_epp_config.php --force     # borrar y reinsertar
 *
 * Ver: docs/MODULO_MATRIZ_EPP/ARQUITECTURA.md §4
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

$TIPO = 'matriz_epp';

$tipoConfig = [
    'tipo_documento' => $TIPO,
    'nombre'         => 'Matriz de Elementos de Proteccion Personal y Dotacion',
    'descripcion'    => 'Matriz por cliente con EPP y dotacion asignados, normas, mantenimiento, frecuencia de cambio, motivos de cambio anticipado y momentos de uso. Basado en SST-MT-G-003.',
    'estandar'       => null,
    'flujo'          => 'secciones_ia',
    'categoria'      => 'talento_humano',
    'icono'          => 'bi-shield-check',
    'orden'          => 999,
    'activo'         => 1,
];

$secciones = [
    [
        'numero'         => 1,
        'seccion_key'    => 'objetivo',
        'nombre'         => 'Objetivo',
        'tipo_contenido' => 'texto',
        'sincronizar_bd' => null,
        'es_obligatoria' => 1,
        'prompt_ia'      => 'Redacta el objetivo de la Matriz de Elementos de Proteccion Personal y Dotacion del cliente {nombre_empresa}. Debe explicar que el documento define los EPP y prendas de dotacion requeridos segun los riesgos y actividades de la organizacion, con sus normas tecnicas, frecuencia de cambio y momentos de uso. Maximo 1 parrafo, tono formal institucional.',
    ],
    [
        'numero'         => 2,
        'seccion_key'    => 'alcance',
        'nombre'         => 'Alcance',
        'tipo_contenido' => 'texto',
        'sincronizar_bd' => null,
        'es_obligatoria' => 1,
        'prompt_ia'      => 'Redacta el alcance de la Matriz de EPP y Dotacion para {nombre_empresa}. Aclara que aplica a todos los trabajadores de la empresa, contratistas y visitantes cuando corresponda, y a todas las areas operativas y administrativas donde se identifiquen riesgos que requieran proteccion personal o dotacion. 1 parrafo corto.',
    ],
    [
        'numero'         => 3,
        'seccion_key'    => 'marco_legal',
        'nombre'         => 'Marco legal y normativo',
        'tipo_contenido' => 'texto',
        'sincronizar_bd' => null,
        'es_obligatoria' => 1,
        'prompt_ia'      => 'Redacta un marco legal breve (2-3 parrafos) sobre EPP y dotacion en Colombia. Cita: Codigo Sustantivo del Trabajo Art. 230 (dotacion), Ley 9 de 1979, Resolucion 2400 de 1979, Decreto 1072 de 2015, Resolucion 0312 de 2019 (estandares minimos SG-SST), y normas tecnicas aplicables (NTC, ANSI, ISO). Tono formal.',
    ],
    [
        'numero'         => 4,
        'seccion_key'    => 'responsabilidades',
        'nombre'         => 'Responsabilidades',
        'tipo_contenido' => 'texto',
        'sincronizar_bd' => null,
        'es_obligatoria' => 1,
        'prompt_ia'      => 'Redacta las responsabilidades frente a la Matriz de EPP en {nombre_empresa}. Incluye: (a) Empleador: suministrar EPP sin costo, garantizar calidad y reposicion, (b) Responsable SST: seleccionar EPP segun riesgo, capacitar, verificar uso, (c) Lideres de area: supervisar uso correcto, (d) Trabajadores: usar los EPP asignados, reportar deterioro o perdida. Formato de lista con vinetas.',
    ],
    [
        'numero'         => 5,
        'seccion_key'    => 'matriz_epp',
        'nombre'         => 'Matriz de EPP',
        'tipo_contenido' => 'tabla_dinamica',
        'sincronizar_bd' => 'epp_cliente_epp',
        'es_obligatoria' => 1,
        'prompt_ia'      => null,
    ],
    [
        'numero'         => 6,
        'seccion_key'    => 'matriz_dotacion',
        'nombre'         => 'Matriz de Dotacion',
        'tipo_contenido' => 'tabla_dinamica',
        'sincronizar_bd' => 'epp_cliente_dotacion',
        'es_obligatoria' => 1,
        'prompt_ia'      => null,
    ],
    [
        'numero'         => 7,
        'seccion_key'    => 'entrega_reposicion',
        'nombre'         => 'Criterios de entrega y reposicion',
        'tipo_contenido' => 'texto',
        'sincronizar_bd' => null,
        'es_obligatoria' => 1,
        'prompt_ia'      => 'Redacta los criterios de entrega y reposicion de EPP y dotacion para {nombre_empresa}. Incluye: entrega inicial al ingresar, registro en formato de entrega individual, reposicion por desgaste normal segun frecuencia, reposicion anticipada por deterioro/dano/perdida, capacitacion en uso, y firma de constancia. 2-3 parrafos formales.',
    ],
];

$firmantes = [
    ['firmante_tipo' => 'representante_legal', 'rol_display' => 'Representante Legal', 'columna_encabezado' => 'Aprueba', 'orden' => 1, 'es_obligatorio' => 1],
    ['firmante_tipo' => 'responsable_sst',     'rol_display' => 'Responsable SST',     'columna_encabezado' => 'Revisa',  'orden' => 2, 'es_obligatorio' => 1],
    ['firmante_tipo' => 'consultor_sst',       'rol_display' => 'Consultor SST',       'columna_encabezado' => 'Elabora', 'orden' => 3, 'es_obligatorio' => 1],
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
