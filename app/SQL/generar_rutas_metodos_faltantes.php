#!/usr/bin/env php
<?php
/**
 * Script para generar rutas y métodos faltantes para documentos SST
 */

$documentosFaltantes = [
    ['tipo' => 'politica_alcohol_drogas', 'metodo' => 'politicaAlcoholDrogas', 'nombre' => 'Política de Prevención del Consumo de Alcohol y Drogas'],
    ['tipo' => 'politica_acoso_laboral', 'metodo' => 'politicaAcosoLaboral', 'nombre' => 'Política de Prevención del Acoso Laboral'],
    ['tipo' => 'politica_violencias_genero', 'metodo' => 'politicaViolenciasGenero', 'nombre' => 'Política de Prevención de Violencias de Género'],
    ['tipo' => 'politica_discriminacion', 'metodo' => 'politicaDiscriminacion', 'nombre' => 'Política de No Discriminación'],
    ['tipo' => 'mecanismos_comunicacion_sgsst', 'metodo' => 'mecanismosComunicacionSgsst', 'nombre' => 'Mecanismos de Comunicación del SG-SST'],
    ['tipo' => 'acta_constitucion_copasst', 'metodo' => 'actaConstitucionCopasst', 'nombre' => 'Acta de Constitución COPASST'],
    ['tipo' => 'acta_constitucion_cocolab', 'metodo' => 'actaConstitucionCocolab', 'nombre' => 'Acta de Constitución COCOLAB'],
    ['tipo' => 'acta_constitucion_brigada', 'metodo' => 'actaConstitucionBrigada', 'nombre' => 'Acta de Constitución Brigada de Emergencia'],
    ['tipo' => 'acta_constitucion_vigia', 'metodo' => 'actaConstitucionVigia', 'nombre' => 'Acta de Constitución Vigía SST'],
    ['tipo' => 'acta_recomposicion_copasst', 'metodo' => 'actaRecomposicionCopasst', 'nombre' => 'Acta de Recomposición COPASST'],
    ['tipo' => 'acta_recomposicion_cocolab', 'metodo' => 'actaRecomposicionCocolab', 'nombre' => 'Acta de Recomposición COCOLAB'],
    ['tipo' => 'acta_recomposicion_brigada', 'metodo' => 'actaRecomposicionBrigada', 'nombre' => 'Acta de Recomposición Brigada de Emergencia'],
    ['tipo' => 'acta_recomposicion_vigia', 'metodo' => 'actaRecomposicionVigia', 'nombre' => 'Acta de Recomposición Vigía SST'],
];

echo "\n";
echo "╔════════════════════════════════════════════════════════════════════════╗\n";
echo "║  GENERADOR DE RUTAS Y MÉTODOS FALTANTES                               ║\n";
echo "╚════════════════════════════════════════════════════════════════════════╝\n";
echo "\n";

// ==================== GENERAR RUTAS ====================
echo "📋 RUTAS PARA Routes.php:\n";
echo str_repeat("─", 74) . "\n";
foreach ($documentosFaltantes as $doc) {
    $tipoKebab = str_replace('_', '-', $doc['tipo']);
    echo "\$routes->get('/documentos-sst/(:num)/{$tipoKebab}/(:num)', 'DocumentosSSTController::{$doc['metodo']}/\$1/\$2');\n";
}

echo "\n";

// ==================== GENERAR MÉTODOS ====================
echo "📋 MÉTODOS PARA DocumentosSSTController.php:\n";
echo str_repeat("─", 74) . "\n";
foreach ($documentosFaltantes as $doc) {
    $tipoSnake = $doc['tipo'];
    $metodo = $doc['metodo'];
    $nombre = $doc['nombre'];

    echo <<<PHP

    /**
     * Vista previa: {$nombre}
     */
    public function {$metodo}(int \$idCliente, int \$anio)
    {
        return \$this->verDocumentoGenerico(\$idCliente, '{$tipoSnake}', \$anio);
    }

PHP;
}

echo "\n";
echo "✅ Generación completada!\n";
echo "📝 Copia las rutas y métodos generados arriba.\n";
echo "\n";
