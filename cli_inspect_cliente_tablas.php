<?php
/**
 * Inspecciona tbl_cliente vs tbl_clientes y busca FK hacia tablas a borrar.
 * SOLO LECTURA.
 */
$pdo = new PDO('mysql:host=localhost;port=3306;dbname=empresas_sst;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "=== tbl_cliente vs tbl_clientes ===\n\n";

foreach (['tbl_cliente', 'tbl_clientes'] as $t) {
    echo "--- {$t} ---\n";
    try {
        $cols = $pdo->query("SHOW COLUMNS FROM `{$t}`")->fetchAll(PDO::FETCH_ASSOC);
        echo "Columnas (" . count($cols) . "): ";
        echo implode(', ', array_column($cols, 'Field')) . "\n";

        $total = (int)$pdo->query("SELECT COUNT(*) FROM `{$t}`")->fetchColumn();
        echo "Total filas: {$total}\n";

        $stmt = $pdo->prepare("SELECT * FROM `{$t}` WHERE id_cliente = ? LIMIT 1");
        $stmt->execute([11]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            echo "Fila cliente 11:\n";
            foreach ($row as $k => $v) {
                $v = is_null($v) ? 'NULL' : (strlen($v) > 60 ? substr($v,0,60).'...' : $v);
                echo "  {$k} = {$v}\n";
            }
        }
    } catch (PDOException $e) {
        echo "ERR: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

// 2) Buscar FK hacia tablas que vamos a borrar
$tablasBorrar = [
    'tbl_cliente_estandares','tbl_doc_carpetas','tbl_documentos_sst','tbl_doc_versiones_sst',
    'tbl_client_kpi','tbl_reporte','tbl_indicadores_sst','tbl_votantes_proceso',
    'tbl_actas_notificaciones','tbl_pta_cliente','tbl_acta_compromisos','tbl_comite_miembros',
    'tbl_cronog_capacitacion','tbl_actas','tbl_comites','tbl_vigias','tbl_actas_tokens',
    'tbl_presupuesto_sst','tbl_procesos_electorales'
];

echo "=== FK que APUNTAN a tablas a borrar (tablas hijas) ===\n";
$inList = "'" . implode("','", $tablasBorrar) . "'";
$sql = "SELECT TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME, CONSTRAINT_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
          AND REFERENCED_TABLE_NAME IN ({$inList})
        ORDER BY REFERENCED_TABLE_NAME, TABLE_NAME";
$fks = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
echo "Total FK encontradas: " . count($fks) . "\n\n";
foreach ($fks as $fk) {
    echo "  {$fk['TABLE_NAME']}.{$fk['COLUMN_NAME']} -> {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}\n";
}

// 3) Buscar tablas cuyo nombre sugiera hijas de las tablas a borrar (por prefijo)
echo "\n=== Tablas con prefijo similar a las tablas a borrar (posibles hijas sin FK formal) ===\n";
$prefijos = ['tbl_acta_','tbl_actas_','tbl_doc_','tbl_pta_','tbl_indicadores_','tbl_comite_','tbl_comites_','tbl_votacion','tbl_eleccion','tbl_proceso_electoral','tbl_presupuesto_','tbl_cronog_','tbl_kpi_','tbl_client_kpi_','tbl_reporte_'];
$allTablas = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
foreach ($allTablas as $t) {
    foreach ($prefijos as $p) {
        if (strpos($t, $p) === 0) {
            echo "  {$t}\n";
            break;
        }
    }
}
