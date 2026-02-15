#!/usr/bin/env php
<?php
/**
 * Script para verificar qué documentos SST tienen rutas faltantes
 */

// Metadata de los 36 documentos
$documentos = [
    ['tipo' => 'identificacion_alto_riesgo', 'numeral' => '1.1.5', 'nombre' => 'Identificación de Trabajadores de Alto Riesgo'],
    ['tipo' => 'politica_sst_general', 'numeral' => '2.1.1', 'nombre' => 'Política de Seguridad y Salud en el Trabajo'],
    ['tipo' => 'politica_alcohol_drogas', 'numeral' => '2.1.2', 'nombre' => 'Política de Prevención del Consumo de Alcohol y Drogas'],
    ['tipo' => 'politica_acoso_laboral', 'numeral' => '2.1.3', 'nombre' => 'Política de Prevención del Acoso Laboral'],
    ['tipo' => 'politica_violencias_genero', 'numeral' => '2.1.4', 'nombre' => 'Política de Prevención de Violencias de Género'],
    ['tipo' => 'politica_discriminacion', 'numeral' => '2.1.5', 'nombre' => 'Política de No Discriminación'],
    ['tipo' => 'politica_prevencion_emergencias', 'numeral' => '2.1.6', 'nombre' => 'Política de Prevención y Preparación ante Emergencias'],
    ['tipo' => 'plan_objetivos_metas', 'numeral' => '2.2.1', 'nombre' => 'Plan de Objetivos y Metas del SG-SST'],
    ['tipo' => 'programa_capacitacion', 'numeral' => '2.2.2', 'nombre' => 'Programa de Capacitación en SST'],
    ['tipo' => 'mecanismos_comunicacion_sgsst', 'numeral' => '2.8.1', 'nombre' => 'Mecanismos de Comunicación del SG-SST'],
    ['tipo' => 'procedimiento_adquisiciones', 'numeral' => '2.9.1', 'nombre' => 'Procedimiento de Adquisiciones en SST'],
    ['tipo' => 'procedimiento_evaluacion_proveedores', 'numeral' => '2.10.1', 'nombre' => 'Procedimiento de Evaluación de Proveedores'],
    ['tipo' => 'procedimiento_gestion_cambio', 'numeral' => '2.11.1', 'nombre' => 'Procedimiento de Gestión del Cambio'],
    ['tipo' => 'procedimiento_control_documental', 'numeral' => '2.5.1', 'nombre' => 'Procedimiento de Control Documental del SG-SST'],
    ['tipo' => 'procedimiento_matriz_legal', 'numeral' => '2.5.2', 'nombre' => 'Procedimiento de Matriz Legal'],
    ['tipo' => 'programa_promocion_prevencion_salud', 'numeral' => '3.1.1', 'nombre' => 'Programa de Promoción y Prevención de la Salud'],
    ['tipo' => 'programa_induccion_reinduccion', 'numeral' => '3.1.2', 'nombre' => 'Programa de Inducción y Reinducción en SST'],
    ['tipo' => 'procedimiento_evaluaciones_medicas', 'numeral' => '3.1.3', 'nombre' => 'Procedimiento de Evaluaciones Médicas Ocupacionales'],
    ['tipo' => 'programa_evaluaciones_medicas_ocupacionales', 'numeral' => '3.1.4', 'nombre' => 'Programa de Evaluaciones Médicas Ocupacionales'],
    ['tipo' => 'programa_estilos_vida_saludable', 'numeral' => '3.1.7', 'nombre' => 'Programa de Estilos de Vida Saludable'],
    ['tipo' => 'procedimiento_investigacion_accidentes', 'numeral' => '3.2.1', 'nombre' => 'Procedimiento de Investigación de Accidentes de Trabajo'],
    ['tipo' => 'procedimiento_investigacion_incidentes', 'numeral' => '3.2.2', 'nombre' => 'Procedimiento de Investigación de Incidentes'],
    ['tipo' => 'metodologia_identificacion_peligros', 'numeral' => '4.1.1', 'nombre' => 'Metodología de Identificación de Peligros'],
    ['tipo' => 'identificacion_sustancias_cancerigenas', 'numeral' => '4.1.3', 'nombre' => 'Identificación de Sustancias Cancerígenas'],
    ['tipo' => 'pve_riesgo_biomecanico', 'numeral' => '4.2.3', 'nombre' => 'PVE Riesgo Biomecánico'],
    ['tipo' => 'pve_riesgo_psicosocial', 'numeral' => '4.2.4', 'nombre' => 'PVE Riesgo Psicosocial'],
    ['tipo' => 'programa_mantenimiento_periodico', 'numeral' => '4.2.5', 'nombre' => 'Programa de Mantenimiento Periódico'],
    ['tipo' => 'manual_convivencia_laboral', 'numeral' => '1.1.8', 'nombre' => 'Manual de Convivencia Laboral'],
    ['tipo' => 'acta_constitucion_copasst', 'numeral' => '1.1.1', 'nombre' => 'Acta de Constitución COPASST'],
    ['tipo' => 'acta_constitucion_cocolab', 'numeral' => '1.1.8', 'nombre' => 'Acta de Constitución COCOLAB'],
    ['tipo' => 'acta_constitucion_brigada', 'numeral' => '1.1.2', 'nombre' => 'Acta de Constitución Brigada de Emergencia'],
    ['tipo' => 'acta_constitucion_vigia', 'numeral' => '1.1.1', 'nombre' => 'Acta de Constitución Vigía SST'],
    ['tipo' => 'acta_recomposicion_copasst', 'numeral' => '1.1.1', 'nombre' => 'Acta de Recomposición COPASST'],
    ['tipo' => 'acta_recomposicion_cocolab', 'numeral' => '1.1.8', 'nombre' => 'Acta de Recomposición COCOLAB'],
    ['tipo' => 'acta_recomposicion_brigada', 'numeral' => '1.1.2', 'nombre' => 'Acta de Recomposición Brigada de Emergencia'],
    ['tipo' => 'acta_recomposicion_vigia', 'numeral' => '1.1.1', 'nombre' => 'Acta de Recomposición Vigía SST'],
];

// Leer Routes.php
$routesFile = __DIR__ . '/../Config/Routes.php';
$routesContent = file_get_contents($routesFile);

echo "\n";
echo "╔════════════════════════════════════════════════════════════════════════╗\n";
echo "║  VERIFICACIÓN DE RUTAS DE DOCUMENTOS SST                              ║\n";
echo "╚════════════════════════════════════════════════════════════════════════╝\n";
echo "\n";

$conRuta = [];
$sinRuta = [];

foreach ($documentos as $doc) {
    $tipoKebab = str_replace('_', '-', $doc['tipo']);
    // Buscar patrón flexible: /documentos-sst/(:num)/tipo-kebab/(:num)
    $patron = "/documentos-sst/(:num)/{$tipoKebab}/(:num)";

    if (strpos($routesContent, $patron) !== false) {
        $conRuta[] = $doc;
    } else {
        $sinRuta[] = $doc;
    }
}

// Mostrar resultados
echo "✅ DOCUMENTOS CON RUTA (" . count($conRuta) . "):\n";
echo str_repeat("─", 74) . "\n";
foreach ($conRuta as $doc) {
    $tipoKebab = str_replace('_', '-', $doc['tipo']);
    echo "  ✓ {$doc['numeral']} - {$doc['nombre']}\n";
    echo "    URL: /documentos-sst/(:num)/{$tipoKebab}/(:num)\n";
}

echo "\n";
echo "❌ DOCUMENTOS SIN RUTA (" . count($sinRuta) . "):\n";
echo str_repeat("─", 74) . "\n";
foreach ($sinRuta as $doc) {
    $tipoKebab = str_replace('_', '-', $doc['tipo']);
    echo "  ✗ {$doc['numeral']} - {$doc['nombre']}\n";
    echo "    Falta: /documentos-sst/(:num)/{$tipoKebab}/(:num)\n";
}

echo "\n";
echo "╔════════════════════════════════════════════════════════════════════════╗\n";
echo "║  RESUMEN                                                               ║\n";
echo "╚════════════════════════════════════════════════════════════════════════╝\n";
echo "  Total documentos: " . count($documentos) . "\n";
echo "  ✅ Con ruta:      " . count($conRuta) . "\n";
echo "  ❌ Sin ruta:      " . count($sinRuta) . "\n";
echo "  📊 Completitud:   " . round((count($conRuta) / count($documentos)) * 100, 1) . "%\n";
echo "\n";

if (count($sinRuta) > 0) {
    echo "⚠️  Hay " . count($sinRuta) . " documentos sin ruta configurada.\n";
    echo "💡 Estos documentos mostrarán error 404 al intentar ver la vista previa.\n";
    exit(1);
} else {
    echo "✅ Todos los documentos tienen ruta configurada.\n";
    exit(0);
}
