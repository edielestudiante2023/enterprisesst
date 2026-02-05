<?php
/**
 * Agregar votantes de prueba - Accesible desde public
 */

$host = 'localhost';
$port = 3306;
$user = 'root';
$pass = '';
$dbname = 'empresas_sst';

echo "<h1>Agregar Votantes de Prueba</h1>";
echo "<style>body{font-family:Arial;padding:20px;} .ok{color:green;} .error{color:red;} table{border-collapse:collapse;margin:10px 0;} th,td{border:1px solid #ddd;padding:8px;} th{background:#333;color:white;}</style>";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Buscar proceso en votacion
    $proceso = $pdo->query("SELECT id_proceso, id_cliente, tipo_comite, anio, enlace_votacion FROM tbl_procesos_electorales WHERE estado = 'votacion' ORDER BY id_proceso DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

    if (!$proceso) {
        echo "<p class='error'>No hay procesos en estado 'votacion'</p>";

        // Mostrar todos los procesos
        echo "<h3>Procesos existentes:</h3>";
        $todos = $pdo->query("SELECT id_proceso, tipo_comite, anio, estado FROM tbl_procesos_electorales ORDER BY id_proceso DESC")->fetchAll(PDO::FETCH_ASSOC);
        echo "<table><tr><th>ID</th><th>Tipo</th><th>Año</th><th>Estado</th></tr>";
        foreach ($todos as $p) {
            echo "<tr><td>{$p['id_proceso']}</td><td>{$p['tipo_comite']}</td><td>{$p['anio']}</td><td>{$p['estado']}</td></tr>";
        }
        echo "</table>";
        exit;
    }

    $idProceso = $proceso['id_proceso'];
    echo "<p class='ok'>Proceso encontrado: ID $idProceso - {$proceso['tipo_comite']} {$proceso['anio']}</p>";
    echo "<p>Enlace votacion: <code>{$proceso['enlace_votacion']}</code></p>";

    // Verificar votantes existentes
    $countVotantes = $pdo->query("SELECT COUNT(*) FROM tbl_votantes_proceso WHERE id_proceso = $idProceso")->fetchColumn();
    echo "<p>Votantes actuales: $countVotantes</p>";

    // Agregar votantes
    $tokenExpira = date('Y-m-d H:i:s', strtotime('+7 days'));
    $votantesData = [
        ['1001001001', 'Carlos Alberto', 'Gomez Rodriguez', 'Operario de Produccion'],
        ['1001001002', 'Maria Fernanda', 'Lopez Hernandez', 'Auxiliar Administrativa'],
        ['1001001003', 'Juan David', 'Martinez Perez', 'Tecnico de Mantenimiento'],
    ];

    $stmt = $pdo->prepare("INSERT INTO tbl_votantes_proceso
        (id_proceso, documento_identidad, nombres, apellidos, cargo, email, token_acceso, token_expira, ha_votado, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())
        ON DUPLICATE KEY UPDATE token_acceso = VALUES(token_acceso), token_expira = VALUES(token_expira)");

    echo "<h3>Agregando/Actualizando votantes:</h3>";
    echo "<table><tr><th>Documento</th><th>Nombre</th><th>Estado</th></tr>";

    foreach ($votantesData as $v) {
        $token = bin2hex(random_bytes(16));
        $email = strtolower(str_replace(' ', '', $v[1])) . '@test.com';

        $stmt->execute([$idProceso, $v[0], $v[1], $v[2], $v[3], $email, $token, $tokenExpira]);
        echo "<tr><td><strong>{$v[0]}</strong></td><td>{$v[1]} {$v[2]}</td><td class='ok'>OK</td></tr>";
    }
    echo "</table>";

    // Actualizar contador
    $total = $pdo->query("SELECT COUNT(*) FROM tbl_votantes_proceso WHERE id_proceso = $idProceso")->fetchColumn();
    $pdo->exec("UPDATE tbl_procesos_electorales SET total_votantes = $total WHERE id_proceso = $idProceso");

    echo "<h2 class='ok'>¡Listo! Total votantes: $total</h2>";
    echo "<h3>Prueba con estos documentos:</h3>";
    echo "<ul style='font-size:1.2em;'>";
    echo "<li><strong>1001001001</strong> - Carlos Alberto Gomez</li>";
    echo "<li><strong>1001001002</strong> - Maria Fernanda Lopez</li>";
    echo "<li><strong>1001001003</strong> - Juan David Martinez</li>";
    echo "</ul>";

    echo "<p><a href='votar/{$proceso['enlace_votacion']}' target='_blank' style='font-size:1.2em;'>→ Ir a votar</a></p>";

} catch (PDOException $e) {
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
}
?>
