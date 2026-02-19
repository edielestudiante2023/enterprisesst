<?php
$db = new mysqli("localhost", "root", "", "empresas_sst");

foreach ([12, 18] as $cid) {
    $nombre = $db->query("SELECT nombre_cliente FROM tbl_clientes WHERE id_cliente = $cid")->fetch_assoc();
    echo "\n=============================================\n";
    echo "  CLIENTE $cid - " . ($nombre['nombre_cliente'] ?? '?') . "\n";
    echo "  PLAN DE TRABAJO (tipo_servicio='Objetivos SG-SST')\n";
    echo "=============================================\n";

    $r = $db->query("SELECT id_ptacliente, actividad_plandetrabajo, phva_plandetrabajo, responsable_sugerido_plandetrabajo, estado_actividad, DATE_FORMAT(fecha_propuesta,'%Y-%m') as mes FROM tbl_pta_cliente WHERE id_cliente = $cid AND tipo_servicio = 'Objetivos SG-SST' ORDER BY id_ptacliente");
    $i = 0;
    while ($row = $r->fetch_assoc()) {
        $i++;
        echo "\n$i. [ID:{$row['id_ptacliente']}] {$row['actividad_plandetrabajo']}\n";
        echo "   PHVA: {$row['phva_plandetrabajo']} | Resp: {$row['responsable_sugerido_plandetrabajo']} | Estado: {$row['estado_actividad']} | Mes: {$row['mes']}\n";
    }
    echo "\nTotal PTA: $i\n";

    echo "\n=============================================\n";
    echo "  CLIENTE $cid - INDICADORES (categoria='objetivos_sgsst')\n";
    echo "=============================================\n";

    $r2 = $db->query("SELECT id_indicador, nombre_indicador, tipo_indicador, formula, meta, unidad_medida, periodicidad FROM tbl_indicadores_sst WHERE id_cliente = $cid AND categoria = 'objetivos_sgsst' AND activo = 1 ORDER BY tipo_indicador, nombre_indicador");
    $j = 0;
    while ($row2 = $r2->fetch_assoc()) {
        $j++;
        echo "\n$j. [ID:{$row2['id_indicador']}] {$row2['nombre_indicador']}\n";
        echo "   Tipo: {$row2['tipo_indicador']} | Formula: {$row2['formula']}\n";
        echo "   Meta: {$row2['meta']}{$row2['unidad_medida']} | Periodicidad: {$row2['periodicidad']}\n";
    }
    echo "\nTotal indicadores: $j\n";
}
$db->close();
