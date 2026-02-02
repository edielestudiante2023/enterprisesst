<?php
/**
 * Script de prueba para verificar DocumentoConfigService
 *
 * Ejecutar: php app/SQL/test_config_service.php
 */

// Cargar CodeIgniter
require_once __DIR__ . '/../../vendor/autoload.php';

// Configurar base de datos directa (sin CI)
$dsn = 'mysql:host=localhost;port=3306;dbname=empresas_sst;charset=utf8mb4';
$pdo = new PDO($dsn, 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

echo "\n";
echo "=================================================\n";
echo "  PRUEBA: DocumentoConfigService\n";
echo "=================================================\n\n";

// 1. Verificar tipos de documento
echo "1. TIPOS DE DOCUMENTO CONFIGURADOS:\n";
echo str_repeat("-", 50) . "\n";

$stmt = $pdo->query("
    SELECT tipo_documento, nombre, estandar, flujo, categoria
    FROM tbl_doc_tipo_configuracion
    WHERE activo = 1
    ORDER BY orden
");

$tipos = $stmt->fetchAll();
foreach ($tipos as $t) {
    echo "  [{$t['tipo_documento']}]\n";
    echo "    Nombre: {$t['nombre']}\n";
    echo "    Estándar: {$t['estandar']}, Flujo: {$t['flujo']}, Cat: {$t['categoria']}\n\n";
}

// 2. Verificar secciones de programa_capacitacion
echo "\n2. SECCIONES DE programa_capacitacion:\n";
echo str_repeat("-", 50) . "\n";

$stmt = $pdo->query("
    SELECT sc.numero, sc.nombre, sc.seccion_key, sc.tipo_contenido, sc.tabla_dinamica_tipo
    FROM tbl_doc_secciones_config sc
    JOIN tbl_doc_tipo_configuracion tc ON sc.id_tipo_config = tc.id_tipo_config
    WHERE tc.tipo_documento = 'programa_capacitacion'
    AND sc.activo = 1
    ORDER BY sc.orden
");

$secciones = $stmt->fetchAll();
foreach ($secciones as $s) {
    $tipo = $s['tipo_contenido'];
    $tabla = $s['tabla_dinamica_tipo'] ? " [Tabla: {$s['tabla_dinamica_tipo']}]" : "";
    echo "  {$s['numero']}. {$s['nombre']} ({$s['seccion_key']}) - {$tipo}{$tabla}\n";
}

// 3. Verificar firmantes de procedimiento_control_documental
echo "\n3. FIRMANTES DE procedimiento_control_documental:\n";
echo str_repeat("-", 50) . "\n";

$stmt = $pdo->query("
    SELECT fc.firmante_tipo, fc.rol_display, fc.columna_encabezado, fc.mostrar_licencia
    FROM tbl_doc_firmantes_config fc
    JOIN tbl_doc_tipo_configuracion tc ON fc.id_tipo_config = tc.id_tipo_config
    WHERE tc.tipo_documento = 'procedimiento_control_documental'
    AND fc.activo = 1
    ORDER BY fc.orden
");

$firmantes = $stmt->fetchAll();
foreach ($firmantes as $f) {
    $licencia = $f['mostrar_licencia'] ? " [Mostrar licencia]" : "";
    echo "  - {$f['firmante_tipo']}: {$f['rol_display']} ({$f['columna_encabezado']}){$licencia}\n";
}

// 4. Verificar tablas dinámicas
echo "\n4. TABLAS DINÁMICAS CONFIGURADAS:\n";
echo str_repeat("-", 50) . "\n";

$stmt = $pdo->query("
    SELECT tabla_key, nombre, filtro_cliente
    FROM tbl_doc_tablas_dinamicas
    WHERE activo = 1
");

$tablas = $stmt->fetchAll();
foreach ($tablas as $t) {
    $filtro = $t['filtro_cliente'] ? " [Requiere cliente]" : " [Global]";
    echo "  - {$t['tabla_key']}: {$t['nombre']}{$filtro}\n";
}

echo "\n=================================================\n";
echo "  PRUEBA COMPLETADA\n";
echo "=================================================\n";
