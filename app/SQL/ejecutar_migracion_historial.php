<?php
/**
 * Script CLI para migrar marco normativo a historial completo
 * Ejecuta primero en LOCAL, luego en PRODUCCIÓN
 *
 * Uso: php ejecutar_migracion_historial.php
 */

// Cargar SQL
$sqlFile = __DIR__ . '/migrar_marco_normativo_historial.sql';
if (!file_exists($sqlFile)) {
    die("❌ Error: No se encuentra el archivo SQL\n");
}

$sql = file_get_contents($sqlFile);
if (empty($sql)) {
    die("❌ Error: El archivo SQL está vacío\n");
}

echo "==========================================\n";
echo "MIGRACIÓN: Marco Normativo - Historial\n";
echo "==========================================\n\n";

// ============================================
// PASO 1: EJECUTAR EN LOCAL
// ============================================
echo "PASO 1: Ejecutando en BASE DE DATOS LOCAL...\n";
echo "--------------------------------------------\n";

$connLocal = new mysqli('localhost', 'root', '', 'empresas_sst', 3306);

if ($connLocal->connect_error) {
    die("❌ Error de conexión LOCAL: " . $connLocal->connect_error . "\n");
}

echo "✓ Conectado a LOCAL (localhost:3306/empresas_sst)\n";

// Ejecutar SQL en LOCAL
$connLocal->multi_query($sql);
do {
    if ($result = $connLocal->store_result()) {
        $result->free();
    }
} while ($connLocal->more_results() && $connLocal->next_result());

if ($connLocal->error) {
    echo "❌ Error en LOCAL: " . $connLocal->error . "\n";
    $connLocal->close();
    die("ABORTADO: No se ejecutará en PRODUCCIÓN\n");
}

echo "✓ Migración aplicada exitosamente en LOCAL\n";
$connLocal->close();

echo "\n";

// ============================================
// PASO 2: EJECUTAR EN PRODUCCIÓN
// ============================================
echo "PASO 2: Ejecutando en BASE DE DATOS PRODUCCIÓN...\n";
echo "----------------------------------------------------\n";

$connProd = new mysqli(
    'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
    'doadmin',
    'AVNS_iDypWizlpMRwHIORJGG',
    'empresas_sst',
    25060
);

// SSL obligatorio para DigitalOcean
$connProd->ssl_set(null, null, null, null, null);

if ($connProd->connect_error) {
    die("❌ Error de conexión PRODUCCIÓN: " . $connProd->connect_error . "\n");
}

echo "✓ Conectado a PRODUCCIÓN (DigitalOcean)\n";

// Ejecutar SQL en PRODUCCIÓN
$connProd->multi_query($sql);
do {
    if ($result = $connProd->store_result()) {
        $result->free();
    }
} while ($connProd->more_results() && $connProd->next_result());

if ($connProd->error) {
    echo "❌ Error en PRODUCCIÓN: " . $connProd->error . "\n";
    $connProd->close();
    die("FALLO EN PRODUCCIÓN - Revisar manualmente\n");
}

echo "✓ Migración aplicada exitosamente en PRODUCCIÓN\n";
$connProd->close();

echo "\n";
echo "==========================================\n";
echo "✅ MIGRACIÓN COMPLETADA EN AMBAS BASES\n";
echo "==========================================\n";
echo "\nResumen:\n";
echo "- Se eliminó constraint UNIQUE en tipo_documento\n";
echo "- Ahora se guarda historial completo de versiones\n";
echo "- Campo 'activo' marca la versión vigente (1 = actual)\n";
echo "- Se agregó índice compuesto para consultas eficientes\n";
