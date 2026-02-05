<?php
/**
 * Simular votación completa para testing
 * - 50 votantes
 * - 10 ya votaron
 * - Votos distribuidos para elegir comité
 */

$host = 'localhost';
$port = 3306;
$user = 'root';
$pass = '';
$dbname = 'empresas_sst';

$idProceso = 1;

echo "<h1>Simulacion de Votacion - Proceso $idProceso</h1>";
echo "<style>body{font-family:Arial;padding:20px;} .ok{color:green;} .info{color:blue;} table{border-collapse:collapse;margin:10px 0;width:100%;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#333;color:white;}</style>";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Verificar proceso
    $proceso = $pdo->query("SELECT * FROM tbl_procesos_electorales WHERE id_proceso = $idProceso")->fetch(PDO::FETCH_ASSOC);
    if (!$proceso) {
        die("<p style='color:red'>Proceso $idProceso no encontrado</p>");
    }

    echo "<p class='ok'>Proceso: {$proceso['tipo_comite']} {$proceso['anio']} - Estado: {$proceso['estado']}</p>";

    // Limpiar votantes anteriores (para prueba limpia)
    $pdo->exec("DELETE FROM tbl_votantes_proceso WHERE id_proceso = $idProceso");
    $pdo->exec("DELETE FROM tbl_votos_comite WHERE id_proceso = $idProceso");
    echo "<p class='info'>Limpiados votantes y votos anteriores</p>";

    // Verificar candidatos
    $candidatos = $pdo->query("SELECT * FROM tbl_candidatos_comite WHERE id_proceso = $idProceso AND representacion = 'trabajador' AND estado = 'aprobado'")->fetchAll(PDO::FETCH_ASSOC);

    if (empty($candidatos)) {
        echo "<p style='color:orange'>No hay candidatos. Creando 5 candidatos de prueba...</p>";

        $candidatosData = [
            ['Juan Carlos', 'Rodriguez Perez', '1111111111', 'Operario de Produccion'],
            ['Maria Elena', 'Lopez Garcia', '2222222222', 'Auxiliar Administrativa'],
            ['Pedro Antonio', 'Martinez Silva', '3333333333', 'Tecnico de Mantenimiento'],
            ['Ana Lucia', 'Gonzalez Ruiz', '4444444444', 'Coordinadora de Calidad'],
            ['Carlos Eduardo', 'Sanchez Mora', '5555555555', 'Supervisor de Planta'],
        ];

        $stmtCand = $pdo->prepare("INSERT INTO tbl_candidatos_comite
            (id_proceso, nombres, apellidos, documento_identidad, cargo, representacion, tipo_plaza, estado, votos_obtenidos, created_at)
            VALUES (?, ?, ?, ?, ?, 'trabajador', 'principal', 'aprobado', 0, NOW())");

        foreach ($candidatosData as $c) {
            $stmtCand->execute([$idProceso, $c[0], $c[1], $c[2], $c[3]]);
        }

        $candidatos = $pdo->query("SELECT * FROM tbl_candidatos_comite WHERE id_proceso = $idProceso AND representacion = 'trabajador' AND estado = 'aprobado'")->fetchAll(PDO::FETCH_ASSOC);
    }

    echo "<h3>Candidatos ({$proceso['plazas_principales']} principales + {$proceso['plazas_suplentes']} suplentes):</h3>";
    echo "<table><tr><th>ID</th><th>Nombre</th><th>Cargo</th></tr>";
    foreach ($candidatos as $c) {
        echo "<tr><td>{$c['id_candidato']}</td><td>{$c['nombres']} {$c['apellidos']}</td><td>{$c['cargo']}</td></tr>";
    }
    echo "</table>";

    // Resetear votos de candidatos
    $pdo->exec("UPDATE tbl_candidatos_comite SET votos_obtenidos = 0 WHERE id_proceso = $idProceso");

    // Crear 50 votantes
    echo "<h3>Creando 50 votantes...</h3>";

    $nombres = ['Andres', 'Daniela', 'Santiago', 'Valentina', 'Sebastian', 'Camila', 'Nicolas', 'Isabella', 'Mateo', 'Sofia',
                'Samuel', 'Mariana', 'Emmanuel', 'Paula', 'David', 'Juliana', 'Jose', 'Carolina', 'Luis', 'Andrea',
                'Felipe', 'Laura', 'Diego', 'Natalia', 'Oscar', 'Diana', 'Ricardo', 'Monica', 'Fernando', 'Claudia',
                'Alejandro', 'Patricia', 'Gabriel', 'Adriana', 'Jorge', 'Sandra', 'Raul', 'Liliana', 'Sergio', 'Gloria',
                'Eduardo', 'Rosa', 'Mauricio', 'Carmen', 'Hector', 'Lucia', 'Jaime', 'Elena', 'Cesar', 'Beatriz'];

    $apellidos = ['Garcia', 'Rodriguez', 'Martinez', 'Lopez', 'Gonzalez', 'Hernandez', 'Perez', 'Sanchez', 'Ramirez', 'Torres',
                  'Flores', 'Rivera', 'Gomez', 'Diaz', 'Reyes', 'Cruz', 'Morales', 'Ortiz', 'Gutierrez', 'Chavez'];

    $cargos = ['Operario', 'Auxiliar', 'Tecnico', 'Analista', 'Asistente', 'Coordinador', 'Supervisor', 'Inspector', 'Conductor', 'Almacenista'];

    $tokenExpira = date('Y-m-d H:i:s', strtotime('+7 days'));
    $stmtVotante = $pdo->prepare("INSERT INTO tbl_votantes_proceso
        (id_proceso, id_cliente, documento_identidad, nombres, apellidos, cargo, email, token_acceso, token_expira, ha_votado, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())");

    $votantesCreados = [];

    for ($i = 1; $i <= 50; $i++) {
        $documento = '100' . str_pad($i, 7, '0', STR_PAD_LEFT);
        $nombre = $nombres[$i - 1];
        $apellido = $apellidos[($i - 1) % 20] . ' ' . $apellidos[($i + 5) % 20];
        $cargo = $cargos[($i - 1) % 10];
        $email = strtolower($nombre) . $i . '@empresa.com';
        $token = bin2hex(random_bytes(16));

        $stmtVotante->execute([
            $idProceso,
            $proceso['id_cliente'],
            $documento,
            $nombre,
            $apellido,
            $cargo,
            $email,
            $token,
            $tokenExpira
        ]);

        $votantesCreados[] = [
            'id' => $pdo->lastInsertId(),
            'documento' => $documento,
            'nombre' => "$nombre $apellido"
        ];
    }

    echo "<p class='ok'>50 votantes creados</p>";

    // Simular 10 votos
    echo "<h3>Simulando 10 votos...</h3>";

    // Distribucion de votos para que haya ganadores claros
    // Candidato 1: 4 votos, Candidato 2: 3 votos, Candidato 3: 2 votos, Candidato 4: 1 voto, Candidato 5: 0 votos
    $distribucionVotos = [];
    if (count($candidatos) >= 1) $distribucionVotos[$candidatos[0]['id_candidato']] = 4;
    if (count($candidatos) >= 2) $distribucionVotos[$candidatos[1]['id_candidato']] = 3;
    if (count($candidatos) >= 3) $distribucionVotos[$candidatos[2]['id_candidato']] = 2;
    if (count($candidatos) >= 4) $distribucionVotos[$candidatos[3]['id_candidato']] = 1;

    $stmtVoto = $pdo->prepare("INSERT INTO tbl_votos_comite
        (id_proceso, id_candidato, hash_votante, fecha_voto)
        VALUES (?, ?, ?, NOW())");

    $votanteIndex = 0;
    foreach ($distribucionVotos as $idCandidato => $numVotos) {
        for ($v = 0; $v < $numVotos; $v++) {
            $votante = $votantesCreados[$votanteIndex];
            $hashVotante = hash('sha256', $votante['documento'] . $idProceso . time() . $v);

            $stmtVoto->execute([$idProceso, $idCandidato, $hashVotante]);

            // Marcar votante como que ya voto
            $pdo->exec("UPDATE tbl_votantes_proceso SET ha_votado = 1, fecha_voto = NOW() WHERE id_votante = {$votante['id']}");

            // Actualizar votos del candidato
            $pdo->exec("UPDATE tbl_candidatos_comite SET votos_obtenidos = votos_obtenidos + 1 WHERE id_candidato = $idCandidato");

            $votanteIndex++;
        }
    }

    // Actualizar contadores del proceso
    $pdo->exec("UPDATE tbl_procesos_electorales SET total_votantes = 50, votos_emitidos = 10 WHERE id_proceso = $idProceso");

    echo "<p class='ok'>10 votos registrados</p>";

    // Mostrar resultados
    echo "<h3>Resultados actuales:</h3>";
    $resultados = $pdo->query("SELECT * FROM tbl_candidatos_comite WHERE id_proceso = $idProceso AND representacion = 'trabajador' ORDER BY votos_obtenidos DESC")->fetchAll(PDO::FETCH_ASSOC);

    echo "<table><tr><th>#</th><th>Candidato</th><th>Votos</th><th>Proyeccion</th></tr>";
    $plazasPrincipales = $proceso['plazas_principales'];
    $plazasSuplentes = $proceso['plazas_suplentes'];
    $pos = 1;
    foreach ($resultados as $r) {
        $proyeccion = '';
        if ($pos <= $plazasPrincipales) {
            $proyeccion = '<span style="color:green;font-weight:bold;">PRINCIPAL</span>';
        } elseif ($pos <= $plazasPrincipales + $plazasSuplentes) {
            $proyeccion = '<span style="color:blue;">Suplente</span>';
        } else {
            $proyeccion = '<span style="color:gray;">No elegido</span>';
        }
        echo "<tr><td>$pos</td><td>{$r['nombres']} {$r['apellidos']}</td><td><strong>{$r['votos_obtenidos']}</strong></td><td>$proyeccion</td></tr>";
        $pos++;
    }
    echo "</table>";

    // Estadisticas
    echo "<h3>Estadisticas:</h3>";
    echo "<ul>";
    echo "<li><strong>Total votantes:</strong> 50</li>";
    echo "<li><strong>Ya votaron:</strong> 10 (20%)</li>";
    echo "<li><strong>Pendientes:</strong> 40</li>";
    echo "<li><strong>Plazas principales:</strong> {$proceso['plazas_principales']}</li>";
    echo "<li><strong>Plazas suplentes:</strong> {$proceso['plazas_suplentes']}</li>";
    echo "</ul>";

    echo "<h2 class='ok'>¡Simulacion completada!</h2>";
    echo "<p>Ahora puedes:</p>";
    echo "<ul>";
    echo "<li><a href='comites-elecciones/{$proceso['id_cliente']}/proceso/$idProceso'>Ver proceso</a></li>";
    echo "<li><a href='comites-elecciones/proceso/$idProceso/censo'>Ver censo de votantes</a></li>";
    echo "<li><a href='comites-elecciones/proceso/$idProceso/resultados'>Ver resultados</a></li>";
    echo "<li><a href='votar/{$proceso['enlace_votacion']}'>Probar votacion</a> (usa documento 1000000011 a 1000000050)</li>";
    echo "</ul>";

} catch (PDOException $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}
?>
