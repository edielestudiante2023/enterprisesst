<?php
/**
 * Script diagnóstico: Verificar configuración BD del reglamento_higiene_seguridad
 * Verifica tbl_doc_tipo_configuracion, tbl_doc_secciones_config, tbl_doc_firmantes_config
 */

$host = 'localhost';
$db   = 'empresas_sst';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage() . "\n");
}

echo "=== DIAGNOSTICO: reglamento_higiene_seguridad ===\n\n";

// 1. Verificar tbl_doc_tipo_configuracion
echo "1. tbl_doc_tipo_configuracion:\n";
$stmt = $pdo->query("SELECT * FROM tbl_doc_tipo_configuracion WHERE tipo_documento = 'reglamento_higiene_seguridad'");
$tipo = $stmt->fetch(PDO::FETCH_ASSOC);
if ($tipo) {
    echo "   ✅ EXISTE - id_tipo_config: {$tipo['id_tipo_config']}\n";
    echo "   nombre: {$tipo['nombre']}\n";
    echo "   flujo: {$tipo['flujo']}\n";
    echo "   estandar: {$tipo['estandar']}\n";
    echo "   categoria: {$tipo['categoria']}\n";
    echo "   activo: {$tipo['activo']}\n";
    $idTipoConfig = $tipo['id_tipo_config'];
} else {
    echo "   ❌ NO EXISTE\n";
    echo "\n=== RESULTADO: Falta registro en tbl_doc_tipo_configuracion ===\n";
    exit;
}

// 2. Verificar tbl_doc_secciones_config
echo "\n2. tbl_doc_secciones_config (id_tipo_config={$idTipoConfig}):\n";
$stmt = $pdo->prepare("SELECT id_seccion_config, numero, nombre, seccion_key, tipo_contenido, prompt_ia FROM tbl_doc_secciones_config WHERE id_tipo_config = ? ORDER BY orden");
$stmt->execute([$idTipoConfig]);
$secciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (count($secciones) > 0) {
    echo "   ✅ " . count($secciones) . " secciones encontradas:\n";
    foreach ($secciones as $s) {
        $tienePrompt = !empty($s['prompt_ia']) ? '✅' : '❌';
        echo "   {$s['numero']}. [{$s['seccion_key']}] {$s['nombre']} (prompt: {$tienePrompt})\n";
    }
} else {
    echo "   ❌ 0 SECCIONES - La vista generar_con_ia.php mostrará 0 secciones\n";
}

// 3. Verificar tbl_doc_firmantes_config
echo "\n3. tbl_doc_firmantes_config (id_tipo_config={$idTipoConfig}):\n";
$stmt = $pdo->prepare("SELECT firmante_tipo, rol_display, columna_encabezado, orden FROM tbl_doc_firmantes_config WHERE id_tipo_config = ? ORDER BY orden");
$stmt->execute([$idTipoConfig]);
$firmantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (count($firmantes) > 0) {
    echo "   ✅ " . count($firmantes) . " firmantes encontrados:\n";
    foreach ($firmantes as $f) {
        echo "   {$f['orden']}. [{$f['firmante_tipo']}] {$f['rol_display']} - {$f['columna_encabezado']}\n";
    }
} else {
    echo "   ❌ 0 FIRMANTES - La vista web no mostrará firmantes correctos\n";
}

// 4. Verificar marco normativo
echo "\n4. tbl_marco_normativo (tipo: reglamento_higiene_seguridad):\n";
try {
    $stmt = $pdo->query("SELECT id, tipo_documento, metodo, DATE(created_at) as fecha, LENGTH(contenido) as chars FROM tbl_marco_normativo WHERE tipo_documento = 'reglamento_higiene_seguridad' ORDER BY created_at DESC LIMIT 1");
    $marco = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($marco) {
        echo "   ✅ EXISTE - metodo: {$marco['metodo']}, fecha: {$marco['fecha']}, chars: {$marco['chars']}\n";
    } else {
        echo "   ⚠️ NO EXISTE - El SweetAlert mostrará 'No hay marco normativo'\n";
    }
} catch (Exception $e) {
    echo "   ⚠️ Tabla no existe o error: " . $e->getMessage() . "\n";
}

echo "\n=== RESUMEN ===\n";
$problemas = [];
if (count($secciones) === 0) $problemas[] = "Sin secciones en tbl_doc_secciones_config";
if (count($firmantes) === 0) $problemas[] = "Sin firmantes en tbl_doc_firmantes_config";

if (empty($problemas)) {
    echo "✅ Configuración completa\n";
} else {
    echo "❌ Problemas encontrados:\n";
    foreach ($problemas as $p) {
        echo "   - $p\n";
    }
}
