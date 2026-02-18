<?php
/**
 * Verificación final del estado de carpetas 1.2.4 en LOCAL y PRODUCCIÓN.
 */

function verificar(PDO $pdo, string $entorno): void
{
    echo "=== {$entorno} ===\n";

    // Total PLANEAR existentes
    $totalPlanear = $pdo->query("
        SELECT COUNT(*) FROM tbl_doc_carpetas c
        JOIN tbl_doc_carpetas raiz ON raiz.id_carpeta = c.id_carpeta_padre
        WHERE c.codigo = '1' AND raiz.tipo = 'raiz'
    ")->fetchColumn();

    // PLANEAR que tienen 1.2.4
    $conCarpeta = $pdo->query("
        SELECT COUNT(*) FROM tbl_doc_carpetas c
        JOIN tbl_doc_carpetas raiz ON raiz.id_carpeta = c.id_carpeta_padre
        WHERE c.codigo = '1' AND raiz.tipo = 'raiz'
          AND EXISTS (
              SELECT 1 FROM tbl_doc_carpetas h
              WHERE h.id_carpeta_padre = c.id_carpeta AND h.codigo = '1.2.4'
          )
    ")->fetchColumn();

    // 2.1.1 con orden incorrecto (< 13) pero que tienen hermano 1.2.4
    $ordenMal = $pdo->query("
        SELECT COUNT(*) FROM tbl_doc_carpetas c211
        WHERE c211.codigo = '2.1.1' AND c211.orden < 13
          AND EXISTS (
              SELECT 1 FROM tbl_doc_carpetas c124
              WHERE c124.id_carpeta_padre = c211.id_carpeta_padre
                AND c124.codigo = '1.2.4'
          )
    ")->fetchColumn();

    // Detalle por cliente
    $detalle = $pdo->query("
        SELECT c124.id_cliente, c124.id_carpeta_padre AS planear_id,
               raiz.nombre AS ano,
               c124.orden AS orden_124,
               COALESCE(c211.orden, 'N/A') AS orden_211
        FROM tbl_doc_carpetas c124
        JOIN tbl_doc_carpetas raiz_child ON raiz_child.id_carpeta = c124.id_carpeta_padre
        JOIN tbl_doc_carpetas raiz ON raiz.id_carpeta = raiz_child.id_carpeta_padre
        LEFT JOIN tbl_doc_carpetas c211 ON c211.id_carpeta_padre = c124.id_carpeta_padre AND c211.codigo = '2.1.1'
        WHERE c124.codigo = '1.2.4'
        ORDER BY c124.id_cliente, c124.id_carpeta
    ")->fetchAll(PDO::FETCH_ASSOC);

    echo "  Total PLANEAR:          {$totalPlanear}\n";
    echo "  PLANEAR con 1.2.4:      {$conCarpeta}  " . ($conCarpeta == $totalPlanear ? "✓ OK" : "✗ INCOMPLETO") . "\n";
    echo "  2.1.1 con orden < 13:   {$ordenMal}  " . ($ordenMal == 0 ? "✓ OK" : "✗ HAY PROBLEMAS") . "\n";
    echo "  Detalle:\n";
    echo "    cliente | PLANEAR | año           | orden 1.2.4 | orden 2.1.1\n";
    foreach ($detalle as $d) {
        $ok124 = $d['orden_124'] == 12 ? '✓' : '✗';
        $ok211 = $d['orden_211'] >= 13 ? '✓' : '✗';
        echo "    {$d['id_cliente']}       | {$d['planear_id']}       | {$d['ano']}  | {$d['orden_124']} {$ok124}         | {$d['orden_211']} {$ok211}\n";
    }

    // SP
    $spBody = $pdo->query("SELECT ROUTINE_DEFINITION FROM information_schema.ROUTINES WHERE ROUTINE_NAME = 'sp_generar_carpetas_por_nivel' AND ROUTINE_SCHEMA = DATABASE()")->fetchColumn();
    $sp124  = strpos($spBody, '1.2.4') !== false ? '✓ SI' : '✗ NO';
    echo "  SP contiene 1.2.4:      {$sp124}\n";
}

$cfgLocal = ['host'=>'127.0.0.1','port'=>'3306','db'=>'empresas_sst','user'=>'root','pass'=>'','ssl'=>false];
$cfgProd  = ['host'=>'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com','port'=>'25060','db'=>'empresas_sst','user'=>'cycloid_userdb','pass'=>'AVNS_iDypWizlpMRwHIORJGG','ssl'=>true];

function conn(array $c): PDO {
    $o = [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION];
    if ($c['ssl']) { $o[PDO::MYSQL_ATTR_SSL_CA]=true; $o[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT]=false; }
    return new PDO("mysql:host={$c['host']};port={$c['port']};dbname={$c['db']};charset=utf8mb4", $c['user'], $c['pass'], $o);
}

verificar(conn($cfgLocal), 'LOCAL');
echo "\n";
verificar(conn($cfgProd), 'PRODUCCIÓN');
