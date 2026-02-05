<?php
/**
 * Script para agregar 20 votantes de prueba al proceso electoral
 * Usar solo para desarrollo/pruebas
 */

$host = 'localhost';
$port = 3306;
$user = 'root';
$pass = '';
$dbname = 'empresas_sst';

// Proceso a usar (cambiar segun necesidad)
$idProceso = 1;

echo "<h1>Agregar Votantes de Prueba</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    .success { color: green; }
    .error { color: red; }
    table { border-collapse: collapse; width: 100%; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background: #333; color: white; }
    tr:nth-child(even) { background: #f2f2f2; }
</style>";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Verificar que el proceso existe
    $proceso = $pdo->query("SELECT * FROM tbl_procesos_electorales WHERE id_proceso = $idProceso")->fetch(PDO::FETCH_ASSOC);
    if (!$proceso) {
        die("<p class='error'>No existe el proceso ID $idProceso</p>");
    }

    echo "<p><strong>Proceso:</strong> {$proceso['tipo_comite']} {$proceso['anio']} - Estado: {$proceso['estado']}</p>";
    echo "<p><strong>Enlace votacion:</strong> {$proceso['enlace_votacion']}</p>";
    echo "<hr>";

    // Datos de prueba para 20 votantes
    $votantes = [
        ['nombres' => 'Andres Felipe', 'apellidos' => 'Ramirez Torres', 'documento' => '1001234567', 'cargo' => 'Operario de Produccion'],
        ['nombres' => 'Daniela', 'apellidos' => 'Vargas Hernandez', 'documento' => '1001234568', 'cargo' => 'Auxiliar Administrativa'],
        ['nombres' => 'Santiago', 'apellidos' => 'Moreno Castro', 'documento' => '1001234569', 'cargo' => 'Tecnico de Mantenimiento'],
        ['nombres' => 'Valentina', 'apellidos' => 'Ortiz Mendez', 'documento' => '1001234570', 'cargo' => 'Coordinadora de Calidad'],
        ['nombres' => 'Sebastian', 'apellidos' => 'Gutierrez Diaz', 'documento' => '1001234571', 'cargo' => 'Supervisor de Planta'],
        ['nombres' => 'Camila Andrea', 'apellidos' => 'Rodriguez Pena', 'documento' => '1001234572', 'cargo' => 'Analista de Procesos'],
        ['nombres' => 'Juan Pablo', 'apellidos' => 'Martinez Luna', 'documento' => '1001234573', 'cargo' => 'Operario de Maquinaria'],
        ['nombres' => 'Laura Sofia', 'apellidos' => 'Lopez Rios', 'documento' => '1001234574', 'cargo' => 'Recepcionista'],
        ['nombres' => 'Nicolas', 'apellidos' => 'Sanchez Vega', 'documento' => '1001234575', 'cargo' => 'Tecnico Electricista'],
        ['nombres' => 'Isabella', 'apellidos' => 'Garcia Molina', 'documento' => '1001234576', 'cargo' => 'Auxiliar Contable'],
        ['nombres' => 'Mateo', 'apellidos' => 'Herrera Nunez', 'documento' => '1001234577', 'cargo' => 'Operario de Logistica'],
        ['nombres' => 'Sofia', 'apellidos' => 'Cruz Reyes', 'documento' => '1001234578', 'cargo' => 'Asistente de RRHH'],
        ['nombres' => 'Samuel', 'apellidos' => 'Perez Aguilar', 'documento' => '1001234579', 'cargo' => 'Tecnico de Seguridad'],
        ['nombres' => 'Mariana', 'apellidos' => 'Gomez Jimenez', 'documento' => '1001234580', 'cargo' => 'Coordinadora de Compras'],
        ['nombres' => 'Emmanuel', 'apellidos' => 'Torres Ospina', 'documento' => '1001234581', 'cargo' => 'Operario de Empaque'],
        ['nombres' => 'Paula Andrea', 'apellidos' => 'Rojas Cardenas', 'documento' => '1001234582', 'cargo' => 'Auxiliar de Bodega'],
        ['nombres' => 'David', 'apellidos' => 'Caicedo Ruiz', 'documento' => '1001234583', 'cargo' => 'Tecnico de Sistemas'],
        ['nombres' => 'Juliana', 'apellidos' => 'Beltran Sierra', 'documento' => '1001234584', 'cargo' => 'Analista de Inventarios'],
        ['nombres' => 'Jose Miguel', 'apellidos' => 'Montoya Vargas', 'documento' => '1001234585', 'cargo' => 'Supervisor de Calidad'],
        ['nombres' => 'Carolina', 'apellidos' => 'Arias Mejia', 'documento' => '1001234586', 'cargo' => 'Asistente de Gerencia'],
    ];

    // Insertar votantes
    $stmt = $pdo->prepare("INSERT INTO tbl_votantes_proceso
        (id_proceso, documento_identidad, nombres, apellidos, cargo, email, token_acceso, token_expira, ha_votado, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())
        ON DUPLICATE KEY UPDATE nombres = VALUES(nombres)");

    $insertados = 0;
    $tokenExpira = date('Y-m-d H:i:s', strtotime('+7 days'));

    echo "<h3>Votantes Agregados:</h3>";
    echo "<table>";
    echo "<tr><th>#</th><th>Documento</th><th>Nombre</th><th>Cargo</th><th>Token</th></tr>";

    foreach ($votantes as $i => $v) {
        $token = bin2hex(random_bytes(16)); // Token individual de 32 caracteres
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

        $num = $i + 1;
        echo "<tr><td>$num</td><td>{$v['documento']}</td><td>{$v['nombres']} {$v['apellidos']}</td><td>{$v['cargo']}</td><td><code>$token</code></td></tr>";
        $insertados++;
    }

    echo "</table>";

    // Actualizar contador del proceso
    $pdo->exec("UPDATE tbl_procesos_electorales SET total_votantes = (SELECT COUNT(*) FROM tbl_votantes_proceso WHERE id_proceso = $idProceso) WHERE id_proceso = $idProceso");

    $total = $pdo->query("SELECT COUNT(*) FROM tbl_votantes_proceso WHERE id_proceso = $idProceso")->fetchColumn();

    echo "<hr>";
    echo "<h3 class='success'>Insertados: $insertados votantes</h3>";
    echo "<p><strong>Total votantes en el proceso:</strong> $total</p>";

    // Mostrar enlace de prueba
    echo "<h3>Para probar la votacion:</h3>";
    echo "<ol>";
    echo "<li>Abre el enlace de votacion: <a href='http://localhost/enterprisesst/public/votar/{$proceso['enlace_votacion']}' target='_blank'>http://localhost/enterprisesst/public/votar/{$proceso['enlace_votacion']}</a></li>";
    echo "<li>Ingresa uno de estos documentos para probar:</li>";
    echo "<ul>";
    foreach (array_slice($votantes, 0, 5) as $v) {
        echo "<li><strong>{$v['documento']}</strong> - {$v['nombres']} {$v['apellidos']}</li>";
    }
    echo "</ul>";
    echo "</ol>";

} catch (PDOException $e) {
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
}
?>
