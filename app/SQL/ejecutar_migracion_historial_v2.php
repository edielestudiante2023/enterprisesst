<?php
/**
 * Script CLI para migrar marco normativo a historial completo
 * v2: Con configuración SSL correcta para DigitalOcean
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

// Intentar sin usuario explícito primero (DigitalOcean a veces usa el nombre de la BD)
$prodConfig = [
    'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
    'user' => 'doadmin', // Usuario por defecto de DigitalOcean managed databases
    'pass' => 'AVNS_iDypWizlpMRwHIORJGG',
    'db'   => 'empresas_sst',
    'port' => 25060
];

// Intentar conexión con mysqli_init para SSL
$connProd = mysqli_init();

if (!$connProd) {
    die("❌ Error: mysqli_init falló\n");
}

// Configurar SSL (DigitalOcean requiere SSL pero no verifica certificado)
mysqli_ssl_set($connProd, null, null, null, null, null);
mysqli_options($connProd, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);

// Intentar conexión
$connected = mysqli_real_connect(
    $connProd,
    $prodConfig['host'],
    $prodConfig['user'],
    $prodConfig['pass'],
    $prodConfig['db'],
    $prodConfig['port'],
    null,
    MYSQLI_CLIENT_SSL
);

if (!$connected) {
    echo "❌ Error de conexión PRODUCCIÓN: " . mysqli_connect_error() . "\n";
    echo "Código de error: " . mysqli_connect_errno() . "\n";

    // Mostrar información de debug
    echo "\nInformación de conexión:\n";
    echo "Host: " . $prodConfig['host'] . "\n";
    echo "Port: " . $prodConfig['port'] . "\n";
    echo "User: " . $prodConfig['user'] . "\n";
    echo "DB: " . $prodConfig['db'] . "\n";

    die("\nSi el error es de autenticación, verifica:\n1. Usuario correcto (puede ser diferente de 'doadmin')\n2. IP whitelisted en DigitalOcean\n");
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
