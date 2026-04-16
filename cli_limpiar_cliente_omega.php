<?php
/**
 * LIMPIEZA TOTAL DEL CLIENTE OMEGA (id_cliente = 19)
 *
 * Uso previsto: re-limpiar periodicamente el cliente "EMPRESA OMEGA" antes de grabar
 * videos de TikTok, dejandolo virgen de data operativa/documental pero preservando
 * su contexto / responsables / contrato para no tener que re-crearlos.
 *
 * PRESERVA:
 *   tbl_cliente, tbl_clientes,
 *   tbl_cliente_contexto_sst, tbl_cliente_contexto_historial,
 *   tbl_cliente_responsables_sst, tbl_contratos
 *
 * Uso:
 *   php cli_limpiar_cliente_omega.php local --dry-run
 *   php cli_limpiar_cliente_omega.php local --apply
 *   php cli_limpiar_cliente_omega.php prod  --dry-run
 *   php cli_limpiar_cliente_omega.php prod  --apply
 *
 * Documentacion: BORRADO_OMEGA.md
 */

$entorno = $argv[1] ?? 'local';
$modo    = $argv[2] ?? '--dry-run';
$ID      = 19; // EMPRESA OMEGA

if (!in_array($entorno, ['local','prod']))      die("entorno invalido (local|prod)\n");
if (!in_array($modo,   ['--dry-run','--apply'])) die("modo invalido (--dry-run|--apply)\n");

$conexiones = [
    'local' => [
        'dsn'  => 'mysql:host=localhost;port=3306;dbname=empresas_sst;charset=utf8mb4',
        'user' => 'root', 'pass' => '',
        'opts' => [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
    ],
    'prod' => [
        'dsn'  => 'mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=empresas_sst;charset=utf8mb4',
        'user' => 'cycloid_userdb',
        'pass' => 'AVNS_MR2SLvzRh3i_7o9fEHN',
        'opts' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        ],
    ],
];

$c = $conexiones[$entorno];
echo "=== LIMPIEZA CLIENTE OMEGA (id={$ID}) ===\n";
echo "Entorno: {$entorno}\n";
echo "Modo:    {$modo}\n";
echo str_repeat('=', 60) . "\n\n";

$pdo = new PDO($c['dsn'], $c['user'], $c['pass'], $c['opts']);

// Verificacion de seguridad: el cliente debe existir y llamarse algo OMEGA
$stmt = $pdo->prepare("SELECT nombre_cliente FROM tbl_clientes WHERE id_cliente = ?");
$stmt->execute([$ID]);
$nombre = $stmt->fetchColumn();
if (!$nombre) {
    echo "[AVISO] Cliente id={$ID} no existe en {$entorno}. Nada que limpiar.\n";
    exit(0);
}
echo "Cliente: {$nombre}\n\n";
if (stripos($nombre, 'OMEGA') === false) {
    die("FATAL: cliente id={$ID} no es OMEGA (es '{$nombre}'). Abortando por seguridad.\n");
}

$plan = [];

// --- NIETAS (nivel 3) ---
$plan[] = ['tbl_doc_firma_evidencias (nieta)',
    "SELECT COUNT(*) FROM tbl_doc_firma_evidencias WHERE id_solicitud IN (SELECT id_solicitud FROM tbl_doc_firma_solicitudes WHERE id_documento IN (SELECT id_documento FROM tbl_documentos_sst WHERE id_cliente = ?))",
    "DELETE FROM tbl_doc_firma_evidencias WHERE id_solicitud IN (SELECT id_solicitud FROM tbl_doc_firma_solicitudes WHERE id_documento IN (SELECT id_documento FROM tbl_documentos_sst WHERE id_cliente = ?))"];

$plan[] = ['tbl_doc_firma_audit_log (nieta)',
    "SELECT COUNT(*) FROM tbl_doc_firma_audit_log WHERE id_solicitud IN (SELECT id_solicitud FROM tbl_doc_firma_solicitudes WHERE id_documento IN (SELECT id_documento FROM tbl_documentos_sst WHERE id_cliente = ?))",
    "DELETE FROM tbl_doc_firma_audit_log WHERE id_solicitud IN (SELECT id_solicitud FROM tbl_doc_firma_solicitudes WHERE id_documento IN (SELECT id_documento FROM tbl_documentos_sst WHERE id_cliente = ?))"];

$plan[] = ['tbl_presupuesto_detalle (nieta)',
    "SELECT COUNT(*) FROM tbl_presupuesto_detalle WHERE id_item IN (SELECT id_item FROM tbl_presupuesto_items WHERE id_presupuesto IN (SELECT id_presupuesto FROM tbl_presupuesto_sst WHERE id_cliente = ?))",
    "DELETE FROM tbl_presupuesto_detalle WHERE id_item IN (SELECT id_item FROM tbl_presupuesto_items WHERE id_presupuesto IN (SELECT id_presupuesto FROM tbl_presupuesto_sst WHERE id_cliente = ?))"];

// --- HIJAS (nivel 2) ---
$plan[] = ['tbl_doc_firma_solicitudes',
    "SELECT COUNT(*) FROM tbl_doc_firma_solicitudes WHERE id_documento IN (SELECT id_documento FROM tbl_documentos_sst WHERE id_cliente = ?)",
    "DELETE FROM tbl_doc_firma_solicitudes WHERE id_documento IN (SELECT id_documento FROM tbl_documentos_sst WHERE id_cliente = ?)"];

$plan[] = ['tbl_presupuesto_items',
    "SELECT COUNT(*) FROM tbl_presupuesto_items WHERE id_presupuesto IN (SELECT id_presupuesto FROM tbl_presupuesto_sst WHERE id_cliente = ?)",
    "DELETE FROM tbl_presupuesto_items WHERE id_presupuesto IN (SELECT id_presupuesto FROM tbl_presupuesto_sst WHERE id_cliente = ?)"];

$plan[] = ['tbl_acta_asistentes',
    "SELECT COUNT(*) FROM tbl_acta_asistentes WHERE id_acta IN (SELECT id_acta FROM tbl_actas WHERE id_cliente = ?)",
    "DELETE FROM tbl_acta_asistentes WHERE id_acta IN (SELECT id_acta FROM tbl_actas WHERE id_cliente = ?)"];

$plan[] = ['tbl_candidatos_comite',
    "SELECT COUNT(*) FROM tbl_candidatos_comite WHERE id_proceso IN (SELECT id_proceso FROM tbl_procesos_electorales WHERE id_cliente = ?)",
    "DELETE FROM tbl_candidatos_comite WHERE id_proceso IN (SELECT id_proceso FROM tbl_procesos_electorales WHERE id_cliente = ?)"];

$plan[] = ['tbl_votos_comite',
    "SELECT COUNT(*) FROM tbl_votos_comite WHERE id_proceso IN (SELECT id_proceso FROM tbl_procesos_electorales WHERE id_cliente = ?)",
    "DELETE FROM tbl_votos_comite WHERE id_proceso IN (SELECT id_proceso FROM tbl_procesos_electorales WHERE id_cliente = ?)"];

$plan[] = ['tbl_miembros_comite',
    "SELECT COUNT(*) FROM tbl_miembros_comite WHERE id_comite IN (SELECT id_comite FROM tbl_comites WHERE id_cliente = ?)",
    "DELETE FROM tbl_miembros_comite WHERE id_comite IN (SELECT id_comite FROM tbl_comites WHERE id_cliente = ?)"];

// Hijas de tbl_acta_visita (FK = id_acta_visita -> tbl_acta_visita.id)
$plan[] = ['tbl_acta_visita_integrantes',
    "SELECT COUNT(*) FROM tbl_acta_visita_integrantes WHERE id_acta_visita IN (SELECT id FROM tbl_acta_visita WHERE id_cliente = ?)",
    "DELETE FROM tbl_acta_visita_integrantes WHERE id_acta_visita IN (SELECT id FROM tbl_acta_visita WHERE id_cliente = ?)"];

$plan[] = ['tbl_acta_visita_temas',
    "SELECT COUNT(*) FROM tbl_acta_visita_temas WHERE id_acta_visita IN (SELECT id FROM tbl_acta_visita WHERE id_cliente = ?)",
    "DELETE FROM tbl_acta_visita_temas WHERE id_acta_visita IN (SELECT id FROM tbl_acta_visita WHERE id_cliente = ?)"];

$plan[] = ['tbl_acta_visita_fotos',
    "SELECT COUNT(*) FROM tbl_acta_visita_fotos WHERE id_acta_visita IN (SELECT id FROM tbl_acta_visita WHERE id_cliente = ?)",
    "DELETE FROM tbl_acta_visita_fotos WHERE id_acta_visita IN (SELECT id FROM tbl_acta_visita WHERE id_cliente = ?)"];

$plan[] = ['tbl_acta_visita_pta',
    "SELECT COUNT(*) FROM tbl_acta_visita_pta WHERE id_acta_visita IN (SELECT id FROM tbl_acta_visita WHERE id_cliente = ?)",
    "DELETE FROM tbl_acta_visita_pta WHERE id_acta_visita IN (SELECT id FROM tbl_acta_visita WHERE id_cliente = ?)"];

// --- PADRES (nivel 1) con id_cliente directo ---
$padres = [
    'tbl_doc_versiones_sst',
    'tbl_documentos_sst',
    'tbl_doc_carpetas',
    'tbl_cliente_estandares',
    'tbl_client_kpi',
    'tbl_reporte',
    'tbl_indicadores_sst',
    'tbl_votantes_proceso',
    'tbl_actas_notificaciones',
    'tbl_actas_tokens',
    'tbl_acta_compromisos',
    'tbl_actas',
    'tbl_acta_visita',          // NUEVA: acta de visita del consultor
    'tbl_pendientes',           // NUEVA: tareas pendientes del cliente
    'tbl_comite_miembros',
    'tbl_comites',
    'tbl_pta_cliente',
    'tbl_cronog_capacitacion',
    'tbl_vigias',
    'tbl_presupuesto_sst',
    'tbl_procesos_electorales',
];
foreach ($padres as $t) {
    $plan[] = [$t,
        "SELECT COUNT(*) FROM `{$t}` WHERE id_cliente = ?",
        "DELETE FROM `{$t}` WHERE id_cliente = ?"];
}

$totalEliminado = 0;
$errores = 0;

try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->beginTransaction();

    printf("%-45s %10s %10s\n", "TABLA", "ANTES", "BORRADAS");
    echo str_repeat('-', 70) . "\n";

    foreach ($plan as $item) {
        [$label, $sqlCount, $sqlDelete] = $item;
        try {
            $stmt = $pdo->prepare($sqlCount);
            $stmt->execute([$ID]);
            $antes = (int)$stmt->fetchColumn();

            $borradas = 0;
            if ($modo === '--apply' && $antes > 0) {
                $stmt = $pdo->prepare($sqlDelete);
                $stmt->execute([$ID]);
                $borradas = $stmt->rowCount();
                $totalEliminado += $borradas;
            }

            $marker = $antes > 0 ? '*' : ' ';
            printf("%s %-43s %10d %10d\n", $marker, $label, $antes, $borradas);
        } catch (PDOException $e) {
            $errores++;
            printf("  %-43s ERROR: %s\n", $label, $e->getMessage());
        }
    }

    echo str_repeat('-', 70) . "\n";

    if ($errores > 0) {
        $pdo->rollBack();
        echo "\n*** {$errores} ERRORES -> ROLLBACK ***\n";
        exit(1);
    }

    if ($modo === '--apply') {
        $pdo->commit();
        echo "\n*** COMMIT OK - Total borradas: {$totalEliminado} ***\n";
    } else {
        $pdo->rollBack();
        echo "\n*** DRY-RUN (no se aplicaron cambios) ***\n";
        echo "Para aplicar: php cli_limpiar_cliente_omega.php {$entorno} --apply\n";
    }

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo "\n=== VERIFICACION TABLAS PRESERVADAS ===\n";
    $preservar = ['tbl_cliente','tbl_clientes','tbl_cliente_contexto_sst','tbl_cliente_contexto_historial','tbl_cliente_responsables_sst','tbl_contratos'];
    foreach ($preservar as $t) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM `{$t}` WHERE id_cliente = ?");
            $stmt->execute([$ID]);
            printf("  %-40s : %d filas\n", $t, (int)$stmt->fetchColumn());
        } catch (PDOException $e) {}
    }

} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "FATAL: " . $e->getMessage() . "\n";
    exit(1);
}
