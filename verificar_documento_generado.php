<?php
$pdo = new PDO('mysql:host=localhost;dbname=empresas_sst', 'root', '');

echo "=============================================================\n";
echo "DOCUMENTO GENERADO - ID 1\n";
echo "=============================================================\n\n";

// Obtener el documento
$stmt = $pdo->query('SELECT * FROM tbl_doc_documentos WHERE id_documento = 1');
$documento = $stmt->fetch(PDO::FETCH_ASSOC);

if ($documento) {
    echo "DATOS DEL DOCUMENTO:\n";
    echo str_repeat("-", 60) . "\n";
    echo "ID: " . $documento['id_documento'] . "\n";
    echo "Cliente ID: " . $documento['id_cliente'] . "\n";
    echo "Código: " . $documento['codigo'] . "\n";
    echo "Nombre: " . $documento['nombre'] . "\n";
    echo "Estado: " . $documento['estado'] . "\n";
    echo "Plantilla ID: " . ($documento['id_plantilla'] ?? 'N/A') . "\n";
    echo "Tipo ID: " . $documento['id_tipo'] . "\n";
    echo "Carpeta ID: " . ($documento['id_carpeta'] ?? 'N/A') . "\n";
    echo "Versión: " . $documento['version_actual'] . "\n";
    echo "Creado: " . $documento['created_at'] . "\n";
    echo "Actualizado: " . $documento['updated_at'] . "\n";
} else {
    echo "No se encontró el documento con ID 1\n";
}

echo "\n\n=============================================================\n";
echo "SECCIONES DEL DOCUMENTO\n";
echo "=============================================================\n\n";

$stmt = $pdo->query('SELECT * FROM tbl_doc_secciones WHERE id_documento = 1 ORDER BY numero_seccion');
$secciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($secciones) {
    foreach ($secciones as $sec) {
        echo str_repeat("=", 60) . "\n";
        echo "SECCIÓN {$sec['numero_seccion']}: {$sec['nombre_seccion']}\n";
        echo str_repeat("-", 60) . "\n";
        echo "ID Sección: {$sec['id_seccion']}\n";
        echo "Aprobada: " . ($sec['aprobado'] ? 'SÍ' : 'NO') . "\n";
        echo "Regeneraciones: " . ($sec['regeneraciones'] ?? 0) . "\n";
        echo "Contexto adicional: " . ($sec['contexto_adicional'] ?? 'N/A') . "\n";
        echo "\nCONTENIDO (primeros 500 caracteres):\n";
        echo str_repeat("-", 40) . "\n";
        $contenido = $sec['contenido'] ?? '';
        echo substr($contenido, 0, 500);
        if (strlen($contenido) > 500) echo "\n...[truncado]";
        echo "\n\n";
    }

    echo "\n=============================================================\n";
    echo "RESUMEN: " . count($secciones) . " secciones encontradas\n";
    $aprobadas = array_filter($secciones, fn($s) => $s['aprobado']);
    echo "Aprobadas: " . count($aprobadas) . " de " . count($secciones) . "\n";
    echo "=============================================================\n";
} else {
    echo "No se encontraron secciones para el documento ID 1\n";
}

// También mostrar la plantilla usada
echo "\n\n=============================================================\n";
echo "PLANTILLA USADA\n";
echo "=============================================================\n\n";

if ($documento && $documento['id_plantilla']) {
    $stmt = $pdo->prepare('SELECT * FROM tbl_doc_plantillas WHERE id_plantilla = ?');
    $stmt->execute([$documento['id_plantilla']]);
    $plantilla = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($plantilla) {
        echo "Nombre: " . $plantilla['nombre'] . "\n";
        echo "Código sugerido: " . $plantilla['codigo_sugerido'] . "\n";
        echo "Tipo ID: " . $plantilla['id_tipo'] . "\n";
        echo "Estándares: 7=" . $plantilla['aplica_7'] . ", 21=" . $plantilla['aplica_21'] . ", 60=" . $plantilla['aplica_60'] . "\n";
    }
}
