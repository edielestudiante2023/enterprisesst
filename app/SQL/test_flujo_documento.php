<?php
/**
 * Script de prueba para validar el flujo de generacion de documentos con IA
 * Prueba: Programa de Capacitacion PYP (estandar 1.2.1)
 */

// Conexion directa a BD
$pdo = new PDO('mysql:host=localhost;port=3306;dbname=empresas_sst;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== PRUEBA DE FLUJO: PROGRAMA DE CAPACITACION PYP ===\n\n";

// 1. Verificar plantilla
echo "1. VERIFICANDO PLANTILLA PRG-CAP...\n";
$stmt = $pdo->query("SELECT * FROM tbl_doc_plantillas WHERE codigo_sugerido = 'PRG-CAP'");
$plantilla = $stmt->fetch(PDO::FETCH_ASSOC);
if ($plantilla) {
    echo "   - ID: {$plantilla['id_plantilla']}\n";
    echo "   - Nombre: {$plantilla['nombre']}\n";
    $estructura = json_decode($plantilla['estructura_json'], true);
    echo "   - Secciones: " . count($estructura) . "\n";
    foreach ($estructura as $i => $seccion) {
        echo "      " . ($i+1) . ". $seccion\n";
    }
    $prompts = json_decode($plantilla['prompts_json'], true);
    echo "   - Prompts definidos: " . count($prompts) . "\n";
} else {
    echo "   ERROR: Plantilla no encontrada\n";
    exit(1);
}

// 2. Verificar cliente de prueba
echo "\n2. VERIFICANDO CLIENTE DE PRUEBA...\n";
$stmt = $pdo->query("
    SELECT c.*, ctx.*
    FROM tbl_clientes c
    LEFT JOIN tbl_cliente_contexto_sst ctx ON c.id_cliente = ctx.id_cliente
    WHERE c.estado = 'activo' AND ctx.id_contexto IS NOT NULL
    LIMIT 1
");
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if ($cliente) {
    echo "   - ID: {$cliente['id_cliente']}\n";
    echo "   - Nombre: {$cliente['nombre_cliente']}\n";
    echo "   - NIT: {$cliente['nit_cliente']}\n";
    echo "   - Codigo CIIU: " . ($cliente['codigo_actividad_economica'] ?? 'N/A') . "\n";
    echo "   - Nivel riesgo: " . ($cliente['niveles_riesgo_arl'] ?? $cliente['nivel_riesgo_arl'] ?? 'N/A') . "\n";
    echo "   - Trabajadores: " . ($cliente['total_trabajadores'] ?? 'N/A') . "\n";
    $peligros = json_decode($cliente['peligros_identificados'] ?? '[]', true);
    echo "   - Peligros identificados: " . (count($peligros) > 0 ? implode(', ', array_slice($peligros, 0, 5)) . '...' : 'Ninguno') . "\n";
} else {
    echo "   ERROR: No hay clientes con contexto SST\n";
    exit(1);
}

// 3. Verificar tipo de documento
echo "\n3. VERIFICANDO TIPO DE DOCUMENTO...\n";
$stmt = $pdo->query("SELECT * FROM tbl_doc_tipos WHERE id_tipo = {$plantilla['id_tipo']}");
$tipo = $stmt->fetch(PDO::FETCH_ASSOC);
if ($tipo) {
    echo "   - Tipo: {$tipo['nombre']} (codigo: {$tipo['codigo']})\n";
} else {
    echo "   - ERROR: Tipo no encontrado\n";
}

// 4. Verificar carpetas del cliente
echo "\n4. VERIFICANDO CARPETAS PHVA...\n";
$stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_doc_carpetas WHERE id_cliente = {$cliente['id_cliente']}");
$carpetas = $stmt->fetch(PDO::FETCH_ASSOC);
echo "   - Carpetas del cliente: {$carpetas['total']}\n";
if ($carpetas['total'] == 0) {
    echo "   - ADVERTENCIA: El cliente no tiene carpetas PHVA. Se deben generar primero.\n";
}

// 5. Verificar configuracion de IA
echo "\n5. VERIFICANDO CONFIGURACION DE IA...\n";
$envFile = __DIR__ . '/../../.env';
$apiKey = '';
if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    if (preg_match('/OPENAI_API_KEY\s*=\s*(.+)/', $envContent, $matches)) {
        $apiKey = trim($matches[1]);
    }
}

if (!empty($apiKey) && strlen($apiKey) > 10) {
    echo "   - API Key configurada: SI (****" . substr($apiKey, -4) . ")\n";
    echo "   - Listo para generar contenido con IA\n";
} else {
    echo "   - API Key configurada: NO\n";
    echo "   - ADVERTENCIA: Configurar OPENAI_API_KEY en .env\n";
}

// 6. Mostrar ejemplo de prompt
echo "\n6. EJEMPLO DE PROMPT (Seccion 1 - INTRODUCCION):\n";
echo "   " . str_replace("\n", "\n   ", substr($prompts['1'] ?? 'N/A', 0, 500)) . "...\n";

// 7. Mostrar rutas
echo "\n7. RUTAS PARA PROBAR EL FLUJO:\n";
$baseUrl = "http://localhost/enterprisesst";
echo "   - Dashboard cliente: {$baseUrl}/documentacion/{$cliente['id_cliente']}\n";
echo "   - Nuevo documento: {$baseUrl}/documentacion/nuevo/{$cliente['id_cliente']}\n";
echo "   - Directo a PRG-CAP: {$baseUrl}/documentacion/configurar/{$cliente['id_cliente']}?plantilla={$plantilla['id_plantilla']}\n";

echo "\n=== RESUMEN ===\n";
echo "Plantilla: OK ({$plantilla['nombre']})\n";
echo "Cliente: OK ({$cliente['nombre_cliente']})\n";
echo "Tipo documento: OK ({$tipo['nombre']})\n";
echo "Carpetas: " . ($carpetas['total'] > 0 ? "OK ({$carpetas['total']})" : "PENDIENTE") . "\n";
echo "API Key: " . (!empty($apiKey) ? "OK" : "PENDIENTE") . "\n";

echo "\n=== PRUEBA COMPLETADA ===\n";
