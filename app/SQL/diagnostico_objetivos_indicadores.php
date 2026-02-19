<?php
/**
 * DIAGNOSTICO: Mostrar qué se borraría y qué se conservaría
 */
$db = new mysqli("localhost", "root", "", "empresas_sst");

foreach ([12, 18] as $cid) {
    $nombre = $db->query("SELECT nombre_cliente FROM tbl_clientes WHERE id_cliente = $cid")->fetch_assoc();
    echo "\n=======================================================\n";
    echo "  CLIENTE $cid - " . ($nombre['nombre_cliente'] ?? '?') . "\n";
    echo "=======================================================\n";

    // 1. PTA Objetivos - TODOS se borran (son generados por Part 1)
    echo "\n--- PTA tipo_servicio='Objetivos SG-SST' (SE BORRARIAN TODOS) ---\n";
    $r = $db->query("SELECT id_ptacliente, LEFT(actividad_plandetrabajo,80) as act, estado_actividad, created_at FROM tbl_pta_cliente WHERE id_cliente = $cid AND tipo_servicio = 'Objetivos SG-SST' ORDER BY id_ptacliente");
    $total_pta = 0;
    while ($row = $r->fetch_assoc()) {
        $total_pta++;
        echo "  [ID:{$row['id_ptacliente']}] {$row['act']}... | created: {$row['created_at']}\n";
    }
    echo "  Total PTA a borrar: $total_pta\n";

    // 2. Indicadores con categoria objetivos_sgsst
    echo "\n--- INDICADORES categoria='objetivos_sgsst' ---\n";
    $r2 = $db->query("SELECT id_indicador, nombre_indicador, tipo_indicador, categoria, created_at FROM tbl_indicadores_sst WHERE id_cliente = $cid AND categoria = 'objetivos_sgsst' AND activo = 1 ORDER BY id_indicador");
    $total_ind = 0;
    while ($row2 = $r2->fetch_assoc()) {
        $total_ind++;
        echo "  [ID:{$row2['id_indicador']}] {$row2['nombre_indicador']} | tipo: {$row2['tipo_indicador']} | created: {$row2['created_at']}\n";
    }
    echo "  Total indicadores objetivos_sgsst: $total_ind\n";

    // 3. Indicadores con OTRAS categorias (estos NO se tocan)
    echo "\n--- INDICADORES OTRAS CATEGORIAS (NO SE TOCAN) ---\n";
    $r3 = $db->query("SELECT categoria, COUNT(*) as total FROM tbl_indicadores_sst WHERE id_cliente = $cid AND activo = 1 AND categoria != 'objetivos_sgsst' GROUP BY categoria ORDER BY categoria");
    while ($row3 = $r3->fetch_assoc()) {
        echo "  categoria='{$row3['categoria']}' → {$row3['total']} indicadores (INTACTOS)\n";
    }

    // 4. Verificar si hay indicadores "de ley" DENTRO de objetivos_sgsst
    echo "\n--- BUSCAR PATRON: indicadores objetivos_sgsst con IDs bajos vs altos ---\n";
    $r4 = $db->query("SELECT MIN(id_indicador) as min_id, MAX(id_indicador) as max_id, MIN(created_at) as primera_fecha, MAX(created_at) as ultima_fecha FROM tbl_indicadores_sst WHERE id_cliente = $cid AND categoria = 'objetivos_sgsst' AND activo = 1");
    $row4 = $r4->fetch_assoc();
    echo "  Rango IDs: {$row4['min_id']} - {$row4['max_id']}\n";
    echo "  Rango fechas: {$row4['primera_fecha']} - {$row4['ultima_fecha']}\n";

    // 5. Documento existente de plan_objetivos_metas
    echo "\n--- DOCUMENTO plan_objetivos_metas (se borraria para regenerar) ---\n";
    $r5 = $db->query("SELECT id_documento, tipo_documento, estado, anio, created_at FROM tbl_documentos_sst WHERE id_cliente = $cid AND tipo_documento = 'plan_objetivos_metas'");
    $total_doc = 0;
    while ($row5 = $r5->fetch_assoc()) {
        $total_doc++;
        echo "  [ID:{$row5['id_documento']}] estado: {$row5['estado']} | anio: {$row5['anio']} | created: {$row5['created_at']}\n";
    }
    if ($total_doc === 0) echo "  (ninguno)\n";
}

$db->close();
