<?php
/**
 * Diagnostico del sistema de votacion
 */

$host = 'localhost';
$port = 3306;
$user = 'root';
$pass = '';
$dbname = 'empresas_sst';

echo "<h1>Diagnostico de Votacion</h1>";
echo "<style>body{font-family:Arial;padding:20px;} .ok{color:green;} .error{color:red;} table{border-collapse:collapse;margin:10px 0;} th,td{border:1px solid #ddd;padding:8px;} th{background:#333;color:white;}</style>";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // 1. Ver procesos existentes
    echo "<h2>1. Procesos Electorales</h2>";
    $procesos = $pdo->query("SELECT id_proceso, id_cliente, tipo_comite, anio, estado, enlace_votacion, total_votantes FROM tbl_procesos_electorales ORDER BY id_proceso DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

    if (empty($procesos)) {
        echo "<p class='error'>No hay procesos electorales</p>";
    } else {
        echo "<table><tr><th>ID</th><th>Cliente</th><th>Tipo</th><th>Año</th><th>Estado</th><th>Enlace</th><th>Votantes</th></tr>";
        foreach ($procesos as $p) {
            echo "<tr><td>{$p['id_proceso']}</td><td>{$p['id_cliente']}</td><td>{$p['tipo_comite']}</td><td>{$p['anio']}</td><td>{$p['estado']}</td><td>{$p['enlace_votacion']}</td><td>{$p['total_votantes']}</td></tr>";
        }
        echo "</table>";
    }

    // 2. Ver votantes del proceso más reciente en votación
    echo "<h2>2. Votantes en el Censo</h2>";
    $procesoVotacion = $pdo->query("SELECT id_proceso, enlace_votacion FROM tbl_procesos_electorales WHERE estado = 'votacion' ORDER BY id_proceso DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

    if (!$procesoVotacion) {
        echo "<p class='error'>No hay procesos en estado 'votacion'</p>";
        // Buscar cualquier proceso
        $procesoVotacion = $pdo->query("SELECT id_proceso, enlace_votacion, estado FROM tbl_procesos_electorales ORDER BY id_proceso DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        if ($procesoVotacion) {
            echo "<p>Proceso más reciente: ID {$procesoVotacion['id_proceso']} - Estado: {$procesoVotacion['estado']}</p>";
        }
    }

    if ($procesoVotacion) {
        $idProceso = $procesoVotacion['id_proceso'];
        echo "<p class='ok'>Proceso en votación: ID $idProceso - Enlace: {$procesoVotacion['enlace_votacion']}</p>";

        $votantes = $pdo->query("SELECT id_votante, documento_identidad, nombres, apellidos, ha_votado FROM tbl_votantes_proceso WHERE id_proceso = $idProceso LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);

        if (empty($votantes)) {
            echo "<p class='error'>NO HAY VOTANTES en este proceso. Agregando 3 votantes de prueba...</p>";

            // Agregar votantes
            $tokenExpira = date('Y-m-d H:i:s', strtotime('+7 days'));
            $votantesData = [
                ['1001001001', 'Carlos Alberto', 'Gomez Rodriguez', 'Operario'],
                ['1001001002', 'Maria Fernanda', 'Lopez Hernandez', 'Auxiliar'],
                ['1001001003', 'Juan David', 'Martinez Perez', 'Tecnico'],
            ];

            $stmt = $pdo->prepare("INSERT INTO tbl_votantes_proceso (id_proceso, documento_identidad, nombres, apellidos, cargo, email, token_acceso, token_expira, ha_votado, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())");

            foreach ($votantesData as $v) {
                $token = bin2hex(random_bytes(16));
                $stmt->execute([$idProceso, $v[0], $v[1], $v[2], $v[3], strtolower(str_replace(' ','',$v[1])).'@test.com', $token, $tokenExpira]);
                echo "<p class='ok'>Agregado: {$v[0]} - {$v[1]} {$v[2]}</p>";
            }

            $pdo->exec("UPDATE tbl_procesos_electorales SET total_votantes = 3 WHERE id_proceso = $idProceso");
            echo "<h3 class='ok'>¡Votantes agregados! Prueba ahora con estos documentos:</h3>";
            echo "<ul><li><strong>1001001001</strong></li><li><strong>1001001002</strong></li><li><strong>1001001003</strong></li></ul>";

        } else {
            echo "<table><tr><th>ID</th><th>Documento</th><th>Nombre</th><th>Votó</th></tr>";
            foreach ($votantes as $v) {
                $voto = $v['ha_votado'] ? 'SI' : 'NO';
                echo "<tr><td>{$v['id_votante']}</td><td><strong>{$v['documento_identidad']}</strong></td><td>{$v['nombres']} {$v['apellidos']}</td><td>$voto</td></tr>";
            }
            echo "</table>";
        }
    }

    // 3. Candidatos
    echo "<h2>3. Candidatos del Proceso</h2>";
    if ($procesoVotacion) {
        $candidatos = $pdo->query("SELECT id_candidato, nombres, apellidos, representacion, estado, votos_obtenidos FROM tbl_candidatos_comite WHERE id_proceso = {$procesoVotacion['id_proceso']}")->fetchAll(PDO::FETCH_ASSOC);
        if (empty($candidatos)) {
            echo "<p class='error'>No hay candidatos</p>";
        } else {
            echo "<table><tr><th>ID</th><th>Nombre</th><th>Representación</th><th>Estado</th><th>Votos</th></tr>";
            foreach ($candidatos as $c) {
                echo "<tr><td>{$c['id_candidato']}</td><td>{$c['nombres']} {$c['apellidos']}</td><td>{$c['representacion']}</td><td>{$c['estado']}</td><td>{$c['votos_obtenidos']}</td></tr>";
            }
            echo "</table>";
        }
    }

} catch (PDOException $e) {
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
}
?>
