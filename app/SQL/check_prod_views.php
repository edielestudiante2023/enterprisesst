<?php
$m = mysqli_init();
mysqli_ssl_set($m, null, null, null, null, null);
mysqli_real_connect($m,'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com','cycloid_userdb',getenv('DB_PROD_PASS'),'empresas_sst',25060,null,MYSQLI_CLIENT_SSL);

// Vistas Otto esperadas
$expected = [
    'v_pta_cliente','v_pendientes','v_indicadores_sst','v_indicadores_mediciones',
    'v_cronog_capacitacion','v_evaluacion_inicial','v_cliente_estandares',
    'v_reportes','v_documentos_sst','v_doc_versiones_sst',
    'v_inspeccion_botiquin','v_inspeccion_extintores','v_inspeccion_locativa',
    'v_inspeccion_senalizacion','v_acta_visita','v_acc_hallazgos','v_acc_acciones',
    'v_acc_seguimientos','v_actas_comite','v_acta_compromisos','v_comite_miembros',
    'v_cliente_contexto','v_responsables_sst','v_contratos',
    'v_presupuesto','v_presupuesto_detalle','v_matrices',
    'v_vencimientos_mantenimientos','v_vigias','v_procesos_electorales',
    'v_candidatos_comite','v_induccion_etapas','v_lookerstudio',
];

$ok = 0; $missing = [];
foreach ($expected as $v) {
    $r = @mysqli_query($m, "SELECT COUNT(*) FROM `$v`");
    if ($r) {
        $n = mysqli_fetch_row($r)[0];
        echo "  ✓ $v ($n filas)\n"; $ok++;
    } else {
        echo "  ✗ $v — " . mysqli_error($m) . "\n"; $missing[] = $v;
    }
}
echo "\n$ok/" . count($expected) . " vistas OK\n";
if ($missing) echo "FALTAN: " . implode(', ', $missing) . "\n";
