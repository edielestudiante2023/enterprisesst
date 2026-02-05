<?php
/**
 * Simular elección completa y realista
 * ~80% participación con distribución natural de votos
 */

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'empresas_sst';

$idProceso = 1;

echo "<h1>🗳️ Simulacion de Eleccion Completa</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; max-width: 900px; margin: 0 auto; }
    .ok { color: green; }
    .info { color: blue; }
    table { border-collapse: collapse; margin: 15px 0; width: 100%; }
    th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
    th { background: #333; color: white; }
    tr:nth-child(even) { background: #f9f9f9; }
    .principal { background: #d4edda !important; font-weight: bold; }
    .suplente { background: #cce5ff !important; }
    .bar { height: 20px; background: #0d6efd; border-radius: 3px; }
    .progress-container { background: #e9ecef; border-radius: 3px; width: 150px; display: inline-block; }
</style>";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Obtener proceso
    $proceso = $pdo->query("SELECT * FROM tbl_procesos_electorales WHERE id_proceso = $idProceso")->fetch(PDO::FETCH_ASSOC);
    if (!$proceso) {
        die("<p style='color:red'>Proceso $idProceso no encontrado</p>");
    }

    echo "<p><strong>Proceso:</strong> {$proceso['tipo_comite']} {$proceso['anio']}</p>";
    echo "<p><strong>Plazas:</strong> {$proceso['plazas_principales']} principales + {$proceso['plazas_suplentes']} suplentes</p>";

    // Obtener candidatos
    $candidatos = $pdo->query("
        SELECT * FROM tbl_candidatos_comite
        WHERE id_proceso = $idProceso
        AND representacion = 'trabajador'
        AND estado = 'aprobado'
        ORDER BY id_candidato
    ")->fetchAll(PDO::FETCH_ASSOC);

    if (count($candidatos) < 2) {
        die("<p style='color:red'>Se necesitan al menos 2 candidatos para simular</p>");
    }

    // Obtener votantes
    $votantes = $pdo->query("
        SELECT * FROM tbl_votantes_proceso
        WHERE id_proceso = $idProceso
        ORDER BY id_votante
    ")->fetchAll(PDO::FETCH_ASSOC);

    $totalVotantes = count($votantes);
    echo "<p><strong>Votantes en censo:</strong> $totalVotantes</p>";
    echo "<p><strong>Candidatos:</strong> " . count($candidatos) . "</p>";

    // Resetear votos anteriores
    $pdo->exec("DELETE FROM tbl_votos_comite WHERE id_proceso = $idProceso");
    $pdo->exec("UPDATE tbl_candidatos_comite SET votos_obtenidos = 0 WHERE id_proceso = $idProceso");
    $pdo->exec("UPDATE tbl_votantes_proceso SET ha_votado = 0, fecha_voto = NULL WHERE id_proceso = $idProceso");

    echo "<p class='info'>Votos anteriores limpiados...</p>";

    // Calcular participación (~75-85%)
    $participacion = rand(75, 85) / 100;
    $votantesQueVotan = (int)($totalVotantes * $participacion);

    echo "<h2>Simulando $votantesQueVotan votos ({$participacion}% participación)...</h2>";

    // Crear distribución de votos con curva natural
    // Los primeros candidatos tienden a tener más votos (efecto posición + popularidad)
    $numCandidatos = count($candidatos);
    $pesos = [];
    $sumaPesos = 0;

    foreach ($candidatos as $i => $c) {
        // Fórmula que da más peso a los primeros pero con variación
        $peso = max(1, $numCandidatos - $i + rand(-2, 3));
        $pesos[$c['id_candidato']] = $peso;
        $sumaPesos += $peso;
    }

    // Distribuir votos según pesos
    $votosPorCandidato = [];
    $votosAsignados = 0;

    foreach ($pesos as $idCandidato => $peso) {
        $proporcion = $peso / $sumaPesos;
        $votos = (int)round($votantesQueVotan * $proporcion);
        $votosPorCandidato[$idCandidato] = $votos;
        $votosAsignados += $votos;
    }

    // Ajustar diferencia
    $diferencia = $votantesQueVotan - $votosAsignados;
    if ($diferencia != 0) {
        // Agregar/quitar del candidato con más votos
        $maxCandidato = array_keys($votosPorCandidato, max($votosPorCandidato))[0];
        $votosPorCandidato[$maxCandidato] += $diferencia;
    }

    // Registrar votos
    $stmtVoto = $pdo->prepare("INSERT INTO tbl_votos_comite (id_proceso, id_candidato, hash_votante, fecha_voto) VALUES (?, ?, ?, NOW())");
    $stmtActualizarVotante = $pdo->prepare("UPDATE tbl_votantes_proceso SET ha_votado = 1, fecha_voto = NOW() WHERE id_votante = ?");

    $votanteIndex = 0;
    $totalVotosRegistrados = 0;

    foreach ($votosPorCandidato as $idCandidato => $numVotos) {
        for ($v = 0; $v < $numVotos; $v++) {
            if ($votanteIndex >= count($votantes)) break;

            $votante = $votantes[$votanteIndex];
            $hash = hash('sha256', $votante['documento_identidad'] . $idProceso . microtime() . rand());

            $stmtVoto->execute([$idProceso, $idCandidato, $hash]);
            $stmtActualizarVotante->execute([$votante['id_votante']]);

            $votanteIndex++;
            $totalVotosRegistrados++;
        }

        // Actualizar contador del candidato
        $pdo->exec("UPDATE tbl_candidatos_comite SET votos_obtenidos = $numVotos WHERE id_candidato = $idCandidato");
    }

    // Actualizar proceso
    $pdo->exec("UPDATE tbl_procesos_electorales SET votos_emitidos = $totalVotosRegistrados WHERE id_proceso = $idProceso");

    echo "<p class='ok'>✓ $totalVotosRegistrados votos registrados</p>";

    // Mostrar resultados
    echo "<h2>📊 Resultados de la Votación</h2>";

    $resultados = $pdo->query("
        SELECT * FROM tbl_candidatos_comite
        WHERE id_proceso = $idProceso
        AND representacion = 'trabajador'
        ORDER BY votos_obtenidos DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $maxVotos = max(array_column($resultados, 'votos_obtenidos'));
    $plazasPrincipales = $proceso['plazas_principales'];
    $plazasSuplentes = $proceso['plazas_suplentes'];

    echo "<table>";
    echo "<tr><th>#</th><th>Candidato</th><th>Cargo</th><th>Votos</th><th>%</th><th>Gráfico</th><th>Resultado</th></tr>";

    $pos = 1;
    foreach ($resultados as $r) {
        $porcentaje = $totalVotosRegistrados > 0 ? round(($r['votos_obtenidos'] / $totalVotosRegistrados) * 100, 1) : 0;
        $barWidth = $maxVotos > 0 ? ($r['votos_obtenidos'] / $maxVotos) * 100 : 0;

        if ($pos <= $plazasPrincipales) {
            $clase = 'principal';
            $resultado = '⭐ PRINCIPAL';
        } elseif ($pos <= $plazasPrincipales + $plazasSuplentes) {
            $clase = 'suplente';
            $resultado = '📋 Suplente';
        } else {
            $clase = '';
            $resultado = '<span style="color:#999">No elegido</span>';
        }

        echo "<tr class='$clase'>";
        echo "<td>$pos</td>";
        echo "<td>{$r['nombres']} {$r['apellidos']}</td>";
        echo "<td><small>{$r['cargo']}</small></td>";
        echo "<td><strong>{$r['votos_obtenidos']}</strong></td>";
        echo "<td>{$porcentaje}%</td>";
        echo "<td><div class='progress-container'><div class='bar' style='width:{$barWidth}%'></div></div></td>";
        echo "<td>$resultado</td>";
        echo "</tr>";
        $pos++;
    }
    echo "</table>";

    // Estadísticas finales
    $yaVotaron = $pdo->query("SELECT COUNT(*) FROM tbl_votantes_proceso WHERE id_proceso = $idProceso AND ha_votado = 1")->fetchColumn();
    $pendientes = $totalVotantes - $yaVotaron;
    $participacionReal = round(($yaVotaron / $totalVotantes) * 100, 1);

    echo "<h2>📈 Estadísticas Finales</h2>";
    echo "<table style='width:auto;'>";
    echo "<tr><td>Total votantes en censo</td><td><strong>$totalVotantes</strong></td></tr>";
    echo "<tr><td>Votos emitidos</td><td><strong style='color:green'>$yaVotaron</strong></td></tr>";
    echo "<tr><td>Pendientes por votar</td><td><strong style='color:orange'>$pendientes</strong></td></tr>";
    echo "<tr><td>Participación</td><td><strong>{$participacionReal}%</strong></td></tr>";
    echo "<tr><td>Plazas principales</td><td><strong>{$plazasPrincipales}</strong></td></tr>";
    echo "<tr><td>Plazas suplentes</td><td><strong>{$plazasSuplentes}</strong></td></tr>";
    echo "</table>";

    echo "<h2>✅ Simulación Completada</h2>";
    echo "<p>Ahora puedes:</p>";
    echo "<ul>";
    echo "<li><a href='comites-elecciones/{$proceso['id_cliente']}/proceso/$idProceso'>Ver proceso</a></li>";
    echo "<li><a href='comites-elecciones/proceso/$idProceso/resultados'>Ver resultados oficiales</a></li>";
    echo "<li>Hacer clic en <strong>'Cerrar Votación e Iniciar Escrutinio'</strong> para finalizar</li>";
    echo "</ul>";

} catch (PDOException $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}
?>
