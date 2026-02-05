<?php
/**
 * Agregar 3 votantes de prueba para testing
 */

$host = 'localhost';
$port = 3306;
$user = 'root';
$pass = '';
$dbname = 'empresas_sst';

$idProceso = 1;

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $tokenExpira = date('Y-m-d H:i:s', strtotime('+7 days'));

    $votantes = [
        ['documento' => '1001001001', 'nombres' => 'Carlos Alberto', 'apellidos' => 'Gomez Rodriguez', 'cargo' => 'Operario de Produccion'],
        ['documento' => '1001001002', 'nombres' => 'Maria Fernanda', 'apellidos' => 'Lopez Hernandez', 'cargo' => 'Auxiliar Administrativa'],
        ['documento' => '1001001003', 'nombres' => 'Juan David', 'apellidos' => 'Martinez Perez', 'cargo' => 'Tecnico de Mantenimiento'],
    ];

    $stmt = $pdo->prepare("INSERT INTO tbl_votantes_proceso
        (id_proceso, documento_identidad, nombres, apellidos, cargo, email, token_acceso, token_expira, ha_votado, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())
        ON DUPLICATE KEY UPDATE nombres = VALUES(nombres), token_acceso = VALUES(token_acceso), token_expira = VALUES(token_expira)");

    echo "<h2>Votantes agregados al proceso $idProceso:</h2>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr style='background:#333;color:white;'><th>Documento</th><th>Nombre</th><th>Cargo</th></tr>";

    foreach ($votantes as $v) {
        $token = bin2hex(random_bytes(16));
        $email = strtolower(str_replace(' ', '.', $v['nombres'])) . '@empresa.com';

        $stmt->execute([
            $idProceso,
            $v['documento'],
            $v['nombres'],
            $v['apellidos'],
            $v['cargo'],
            $email,
            $token,
            $tokenExpira
        ]);

        echo "<tr><td><strong>{$v['documento']}</strong></td><td>{$v['nombres']} {$v['apellidos']}</td><td>{$v['cargo']}</td></tr>";
    }

    echo "</table>";

    // Actualizar contador
    $pdo->exec("UPDATE tbl_procesos_electorales SET total_votantes = (SELECT COUNT(*) FROM tbl_votantes_proceso WHERE id_proceso = $idProceso) WHERE id_proceso = $idProceso");

    echo "<h3 style='color:green;'>Listo! Usa estos documentos para probar.</h3>";

} catch (PDOException $e) {
    echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
}
?>
