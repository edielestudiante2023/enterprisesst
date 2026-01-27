<?php
$pdo = new PDO('mysql:host=localhost;dbname=empresas_sst', 'root', '');

echo "=============================================================\n";
echo "ANÁLISIS DEL CONTENIDO GENERADO - EMPRESA 7 ESTÁNDARES\n";
echo "=============================================================\n\n";

// Contexto del cliente
$stmt = $pdo->query('SELECT * FROM tbl_cliente_contexto_sst WHERE id_cliente = 11');
$contexto = $stmt->fetch(PDO::FETCH_ASSOC);

echo "CONTEXTO DEL CLIENTE CYCLOID:\n";
echo str_repeat("-", 60) . "\n";
echo "- Estándares aplicables: " . $contexto['estandares_aplicables'] . "\n";
echo "- Total trabajadores: " . $contexto['total_trabajadores'] . "\n";
echo "- Nivel riesgo: " . $contexto['nivel_riesgo_arl'] . "\n";
echo "- Tiene COPASST: " . ($contexto['tiene_copasst'] ? 'Sí' : 'No') . "\n";
echo "- Tiene Vigía SST: " . ($contexto['tiene_vigia_sst'] ? 'Sí' : 'No') . "\n";
echo "- Observaciones: " . $contexto['observaciones_contexto'] . "\n";

echo "\n\n=============================================================\n";
echo "CONTENIDO COMPLETO DE CADA SECCIÓN\n";
echo "=============================================================\n\n";

$stmt = $pdo->query('SELECT * FROM tbl_doc_secciones WHERE id_documento = 1 ORDER BY numero_seccion');
$secciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($secciones as $sec) {
    echo "\n" . str_repeat("=", 70) . "\n";
    echo "SECCIÓN {$sec['numero_seccion']}: {$sec['nombre_seccion']}\n";
    echo str_repeat("=", 70) . "\n\n";
    echo $sec['contenido'] ?? '(vacío)';
    echo "\n";
}

// Análisis específico
echo "\n\n" . str_repeat("=", 70) . "\n";
echo "ANÁLISIS CRÍTICO PARA EMPRESA DE 7 ESTÁNDARES\n";
echo str_repeat("=", 70) . "\n\n";

echo "Según Resolución 0312/2019, empresas con 7 estándares:\n";
echo "- Menos de 10 trabajadores\n";
echo "- Riesgo I, II o III\n";
echo "- NO requieren licencia SST para el responsable\n";
echo "- Vigía SST en lugar de COPASST\n";
echo "- Documentación simplificada\n";
echo "- Plan anual de trabajo básico\n";
echo "- Sin Comité de Convivencia (< 10 trabajadores)\n";
