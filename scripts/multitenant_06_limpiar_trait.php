<?php
/**
 * Limpieza: remueve el trait TenantScopedModel de los modelos cuyas tablas
 * NO tienen columna id_cliente (el trait no tenia efecto ahi).
 *
 * Esta lista fue producida por el diagnostico multitenant_05_diagnostico.php.
 *
 * Uso:
 *   php scripts/multitenant_06_limpiar_trait.php          # dry-run
 *   php scripts/multitenant_06_limpiar_trait.php --apply  # aplicar
 */

$apply = in_array('--apply', $argv ?? []);
$modelsDir = __DIR__ . '/../app/Models';

$modelosALimpiar = [
    'AccAccionesModel.php',
    'AccVerificacionesModel.php',
    'CompetenciaNivelClienteModel.php',
    'DocFirmaModel.php',
    'HistorialEstandaresModel.php',
    'HistorialPlanTrabajoModel.php',
    'PtaTransicionesModel.php',
];

$traitUse = 'use App\\Models\\Traits\\TenantScopedModel;';
$traitInClass = 'use TenantScopedModel;';

echo $apply ? "=== APLICANDO ===\n\n" : "=== DRY-RUN ===\n\n";

$modificados = 0;
foreach ($modelosALimpiar as $nombre) {
    $archivo = $modelsDir . '/' . $nombre;
    if (!file_exists($archivo)) {
        echo "  SKIP {$nombre} (no existe)\n";
        continue;
    }

    $contenido = file_get_contents($archivo);
    $original = $contenido;

    // Remover la linea "use App\Models\Traits\TenantScopedModel;" con su eventual newline
    $contenido = preg_replace('/\s*use App\\\\Models\\\\Traits\\\\TenantScopedModel;\s*\n/', "\n", $contenido, 1);

    // Remover la linea "use TenantScopedModel;" dentro de la clase
    $contenido = preg_replace('/\s*use TenantScopedModel;\s*\n/', "\n", $contenido, 1);

    if ($contenido === $original) {
        echo "  SKIP {$nombre} (sin trait, nada que hacer)\n";
        continue;
    }

    if ($apply) {
        file_put_contents($archivo, $contenido);
        echo "  OK   {$nombre}\n";
    } else {
        echo "  WILL {$nombre}\n";
    }
    $modificados++;
}

echo "\nTotal: {$modificados} modelos " . ($apply ? 'modificados' : 'a modificar') . "\n";
if (!$apply) echo "Ejecuta con --apply para aplicar.\n";
