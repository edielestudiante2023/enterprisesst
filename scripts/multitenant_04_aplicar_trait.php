<?php
/**
 * Multi-tenant: aplica TenantScopedModel trait a todos los modelos
 * que tienen columna id_cliente en sus allowedFields.
 *
 * Excluye: ClientModel (ya tiene su propio override), ConsultantModel, TenantScopedModel.
 *
 * Idempotente: si el trait ya esta, no lo agrega de nuevo.
 *
 * Uso:
 *   php scripts/multitenant_04_aplicar_trait.php          # preview (dry-run)
 *   php scripts/multitenant_04_aplicar_trait.php --apply   # aplicar cambios
 */

$apply = in_array('--apply', $argv ?? []);
$modelsDir = __DIR__ . '/../app/Models';
$traitUse = 'use App\\Models\\Traits\\TenantScopedModel;';
$traitInClass = 'use TenantScopedModel;';

$excluir = ['ClientModel.php', 'ConsultantModel.php', 'EmpresaConsultoraModel.php'];

$archivos = glob($modelsDir . '/*.php');
$modificados = 0;
$yaConTrait = 0;
$sinIdCliente = 0;

echo $apply ? "=== APLICANDO CAMBIOS ===\n\n" : "=== DRY-RUN (preview) ===\n\n";

foreach ($archivos as $archivo) {
    $nombre = basename($archivo);

    // Excluir archivos especificos
    if (in_array($nombre, $excluir)) continue;

    $contenido = file_get_contents($archivo);

    // Solo modelos que tienen id_cliente en allowedFields o como propiedad
    if (strpos($contenido, 'id_cliente') === false) {
        $sinIdCliente++;
        continue;
    }

    // Ya tiene el trait?
    if (strpos($contenido, 'TenantScopedModel') !== false) {
        echo "  SKIP {$nombre} (ya tiene trait)\n";
        $yaConTrait++;
        continue;
    }

    // Verificar que es un Model de CI4
    if (strpos($contenido, 'extends Model') === false) {
        echo "  SKIP {$nombre} (no extiende Model)\n";
        continue;
    }

    // Agregar el use import despues del ultimo use ... ;
    // Buscar la posicion del ultimo "use " statement antes de "class "
    $classPos = strpos($contenido, 'class ');
    if ($classPos === false) continue;

    $beforeClass = substr($contenido, 0, $classPos);

    // Insertar el import del trait si no existe
    if (strpos($beforeClass, $traitUse) === false) {
        // Encontrar el ultimo "use " import
        $lastUsePos = strrpos($beforeClass, "use ");
        if ($lastUsePos !== false) {
            // Encontrar el ; despues de ese use
            $semiPos = strpos($contenido, ";", $lastUsePos);
            if ($semiPos !== false) {
                $contenido = substr($contenido, 0, $semiPos + 1) . "\n" . $traitUse . substr($contenido, $semiPos + 1);
            }
        }
    }

    // Insertar "use TenantScopedModel;" dentro de la clase, despues de {
    // Buscar "class XXX extends Model" seguido de "{"
    if (preg_match('/class\s+\w+\s+extends\s+Model\s*\{/', $contenido, $matches, PREG_OFFSET_CAPTURE)) {
        $openBracePos = strpos($contenido, '{', $matches[0][1]);
        if ($openBracePos !== false) {
            $insert = "\n    {$traitInClass}\n";
            $contenido = substr($contenido, 0, $openBracePos + 1) . $insert . substr($contenido, $openBracePos + 1);
        }
    }

    if ($apply) {
        file_put_contents($archivo, $contenido);
        echo "  OK   {$nombre}\n";
    } else {
        echo "  WILL {$nombre}\n";
    }
    $modificados++;
}

echo "\n--- Resumen ---\n";
echo "  Modelos con id_cliente a modificar: {$modificados}\n";
echo "  Ya con trait: {$yaConTrait}\n";
echo "  Sin id_cliente (ignorados): {$sinIdCliente}\n";

if (!$apply && $modificados > 0) {
    echo "\nEjecuta con --apply para aplicar los cambios.\n";
}
