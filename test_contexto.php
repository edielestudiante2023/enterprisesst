<?php
require 'vendor/autoload.php';

// Definir constantes necesarias para CodeIgniter
define('ROOTPATH', __DIR__ . '/');
define('APPPATH', ROOTPATH . 'app/');
define('WRITEPATH', ROOTPATH . 'writable/');
define('SYSTEMPATH', ROOTPATH . 'vendor/codeigniter4/framework/system/');

// Cargar la configuración de base de datos
$_SERVER['CI_ENVIRONMENT'] = 'development';

use App\Libraries\DocumentosSSTTypes\ProgramaPromocionPrevencionSalud;
use App\Libraries\DocumentosSSTTypes\DocumentoSSTFactory;

echo "=== PRUEBA DE CONTEXTO PYP SALUD ===\n\n";

// Probar el Factory
echo "1. Probando Factory...\n";
try {
    $doc = DocumentoSSTFactory::crear('programa_promocion_prevencion_salud');
    echo "✅ Factory creó la instancia correctamente\n";
    echo "   Tipo: " . $doc->getTipoDocumento() . "\n";
    echo "   Nombre: " . $doc->getNombre() . "\n\n";
} catch (Exception $e) {
    echo "❌ Error en Factory: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Probar getContextoBase
echo "2. Probando getContextoBase con cliente 18...\n";

$cliente = [
    'id_cliente' => 18,
    'nombre_cliente' => 'CLIENTE DE VALIDACION'
];

$contexto = [
    'estandares_aplicables' => 7,
    'numero_trabajadores' => 5,
    'actividad_economica_principal' => 'Servicios',
    'nivel_riesgo' => 'I'
];

try {
    $contextoBase = $doc->getContextoBase($cliente, $contexto);

    // Verificar que contiene las secciones esperadas
    $tieneActividades = strpos($contextoBase, 'ACTIVIDADES DE PROMOCIÓN Y PREVENCIÓN EN SALUD') !== false;
    $tieneIndicadores = strpos($contextoBase, 'INDICADORES DE PROMOCIÓN Y PREVENCIÓN EN SALUD') !== false;

    echo "✅ Contexto generado (" . strlen($contextoBase) . " caracteres)\n";
    echo "   - Contiene sección de ACTIVIDADES: " . ($tieneActividades ? '✅ SÍ' : '❌ NO') . "\n";
    echo "   - Contiene sección de INDICADORES: " . ($tieneIndicadores ? '✅ SÍ' : '❌ NO') . "\n\n";

    // Mostrar extracto de actividades
    if ($tieneActividades) {
        $inicio = strpos($contextoBase, 'ACTIVIDADES DE PROMOCIÓN');
        $fin = strpos($contextoBase, 'INDICADORES DE PROMOCIÓN');
        $extracto = substr($contextoBase, $inicio, $fin - $inicio);
        echo "EXTRACTO DE ACTIVIDADES:\n";
        echo "----------------------------\n";
        echo substr($extracto, 0, 1500) . "...\n";
    }

} catch (Exception $e) {
    echo "❌ Error generando contexto: " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
}

// Eliminar este archivo
unlink(__FILE__);
