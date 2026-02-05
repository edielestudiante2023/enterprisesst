<?php
/**
 * Verificar candidatos del proceso 1
 */

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'empresas_sst';
$idProceso = 1;

echo "<h1>Verificar Candidatos - Proceso $idProceso</h1>";
echo "<style>body{font-family:Arial;padding:20px;} table{border-collapse:collapse;width:100%;} th,td{border:1px solid #ddd;padding:8px;} th{background:#333;color:white;}</style>";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Verificar proceso
    $proceso = $pdo->query("SELECT * FROM tbl_procesos_electorales WHERE id_proceso = $idProceso")->fetch(PDO::FETCH_ASSOC);
    echo "<h2>Proceso</h2>";
    echo "<p><strong>Tipo:</strong> {$proceso['tipo_comite']} | <strong>Estado:</strong> {$proceso['estado']} | <strong>Plazas:</strong> {$proceso['plazas_principales']} principales + {$proceso['plazas_suplentes']} suplentes</p>";

    // Candidatos en tbl_candidatos_comite (nueva)
    echo "<h2>Candidatos (tbl_candidatos_comite)</h2>";
    $candidatos = $pdo->query("SELECT * FROM tbl_candidatos_comite WHERE id_proceso = $idProceso ORDER BY votos_obtenidos DESC")->fetchAll(PDO::FETCH_ASSOC);

    if (empty($candidatos)) {
        echo "<p style='color:orange'>No hay candidatos en tbl_candidatos_comite</p>";
    } else {
        echo "<table><tr><th>ID</th><th>Nombre</th><th>Representacion</th><th>Estado</th><th>Votos</th></tr>";
        foreach ($candidatos as $c) {
            echo "<tr><td>{$c['id_candidato']}</td><td>{$c['nombres']} {$c['apellidos']}</td><td>{$c['representacion']}</td><td>{$c['estado']}</td><td><strong>{$c['votos_obtenidos']}</strong></td></tr>";
        }
        echo "</table>";
    }

    // Participantes en tbl_participantes_comite (legacy)
    echo "<h2>Participantes (tbl_participantes_comite - legacy)</h2>";
    $participantes = $pdo->query("SELECT * FROM tbl_participantes_comite WHERE id_proceso = $idProceso ORDER BY votos_obtenidos DESC")->fetchAll(PDO::FETCH_ASSOC);

    if (empty($participantes)) {
        echo "<p style='color:orange'>No hay participantes en tbl_participantes_comite</p>";
    } else {
        echo "<table><tr><th>ID</th><th>Nombre</th><th>Representacion</th><th>Estado</th><th>Votos</th></tr>";
        foreach ($participantes as $p) {
            echo "<tr><td>{$p['id_participante']}</td><td>{$p['nombre_completo']}</td><td>{$p['representacion']}</td><td>{$p['estado']}</td><td><strong>{$p['votos_obtenidos']}</strong></td></tr>";
        }
        echo "</table>";
    }

    // Votos
    echo "<h2>Votos (tbl_votos_comite)</h2>";
    $votos = $pdo->query("SELECT COUNT(*) as total FROM tbl_votos_comite WHERE id_proceso = $idProceso")->fetch(PDO::FETCH_ASSOC);
    echo "<p>Total votos registrados: <strong>{$votos['total']}</strong></p>";

    // Votantes
    echo "<h2>Votantes</h2>";
    $votantes = $pdo->query("SELECT COUNT(*) as total, SUM(ha_votado) as votaron FROM tbl_votantes_proceso WHERE id_proceso = $idProceso")->fetch(PDO::FETCH_ASSOC);
    echo "<p>Total votantes: <strong>{$votantes['total']}</strong> | Ya votaron: <strong>{$votantes['votaron']}</strong></p>";

} catch (PDOException $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}
?>
