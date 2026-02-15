<?php
// Script temporal para consultar marco normativo

$host = 'localhost';
$db = 'empresas_sst';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("
        SELECT
            tipo_documento,
            fecha_actualizacion,
            metodo_actualizacion,
            activo,
            LENGTH(marco_normativo_texto) AS longitud_caracteres,
            marco_normativo_texto
        FROM tbl_marco_normativo
        WHERE tipo_documento = 'politica_alcohol_drogas'
          AND activo = 1
        LIMIT 1
    ");

    $stmt->execute();
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resultado) {
        echo "═══════════════════════════════════════════════════════════\n";
        echo "MARCO NORMATIVO - POLÍTICA ALCOHOL Y DROGAS\n";
        echo "═══════════════════════════════════════════════════════════\n\n";
        echo "Tipo documento: " . $resultado['tipo_documento'] . "\n";
        echo "Fecha actualización: " . $resultado['fecha_actualizacion'] . "\n";
        echo "Método: " . $resultado['metodo_actualizacion'] . "\n";
        echo "Activo: " . $resultado['activo'] . "\n";
        echo "Longitud (caracteres): " . $resultado['longitud_caracteres'] . "\n";
        echo "\n───────────────────────────────────────────────────────────\n";
        echo "TEXTO COMPLETO:\n";
        echo "───────────────────────────────────────────────────────────\n\n";
        echo $resultado['marco_normativo_texto'];
        echo "\n\n═══════════════════════════════════════════════════════════\n";
    } else {
        echo "No se encontró marco normativo para politica_alcohol_drogas\n";
    }

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
