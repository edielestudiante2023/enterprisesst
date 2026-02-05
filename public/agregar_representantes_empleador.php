<?php
/**
 * Agregar representantes del empleador al proceso 1
 * 4 principales + 4 suplentes
 */

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'empresas_sst';

$idProceso = 1;
$idCliente = 18;

echo "<h1>Agregar Representantes del Empleador</h1>";
echo "<style>body{font-family:Arial;padding:20px;} .ok{color:green;} table{border-collapse:collapse;width:100%;margin:15px 0;} th,td{border:1px solid #ddd;padding:10px;} th{background:#333;color:white;} .principal{background:#d4edda;} .suplente{background:#cce5ff;}</style>";

// Representantes del empleador (cargos directivos típicos)
$representantes = [
    // Principales (4)
    ['Gerente General', 'Roberto Carlos', 'Mendez Villanueva', '80123456', 'principal'],
    ['Director de RRHH', 'Patricia Elena', 'Suarez Montoya', '80234567', 'principal'],
    ['Director Financiero', 'Andres Felipe', 'Gutierrez Parra', '80345678', 'principal'],
    ['Director de Operaciones', 'Carolina', 'Betancourt Rios', '80456789', 'principal'],
    // Suplentes (4)
    ['Jefe de Produccion', 'Luis Fernando', 'Cardona Mesa', '80567890', 'suplente'],
    ['Jefe de Calidad', 'Diana Marcela', 'Ospina Velez', '80678901', 'suplente'],
    ['Jefe Administrativo', 'Jorge Eduardo', 'Aristizabal Gomez', '80789012', 'suplente'],
    ['Coordinador SST', 'Sandra Patricia', 'Valencia Duque', '80890123', 'suplente'],
];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Verificar proceso
    $proceso = $pdo->query("SELECT * FROM tbl_procesos_electorales WHERE id_proceso = $idProceso")->fetch(PDO::FETCH_ASSOC);
    echo "<p><strong>Proceso:</strong> {$proceso['tipo_comite']} {$proceso['anio']} | <strong>Estado:</strong> {$proceso['estado']}</p>";
    echo "<p><strong>Plazas empleador:</strong> {$proceso['plazas_principales']} principales + {$proceso['plazas_suplentes']} suplentes</p>";

    // Verificar si ya hay representantes del empleador
    $existentes = $pdo->query("SELECT COUNT(*) FROM tbl_candidatos_comite WHERE id_proceso = $idProceso AND representacion = 'empleador'")->fetchColumn();

    if ($existentes > 0) {
        echo "<p style='color:orange'>Ya hay $existentes representantes del empleador registrados.</p>";

        // Mostrar existentes
        $lista = $pdo->query("SELECT * FROM tbl_candidatos_comite WHERE id_proceso = $idProceso AND representacion = 'empleador' ORDER BY tipo_plaza, id_candidato")->fetchAll(PDO::FETCH_ASSOC);
        echo "<table><tr><th>#</th><th>Nombre</th><th>Cargo</th><th>Tipo</th><th>Estado</th></tr>";
        $i = 1;
        foreach ($lista as $r) {
            $clase = $r['tipo_plaza'] == 'principal' ? 'principal' : 'suplente';
            echo "<tr class='$clase'><td>$i</td><td>{$r['nombres']} {$r['apellidos']}</td><td>{$r['cargo']}</td><td>{$r['tipo_plaza']}</td><td>{$r['estado']}</td></tr>";
            $i++;
        }
        echo "</table>";

        echo "<p><a href='comites-elecciones/$idCliente/proceso/$idProceso'>Ir al proceso</a></p>";
        exit;
    }

    // Insertar representantes
    $stmt = $pdo->prepare("INSERT INTO tbl_candidatos_comite
        (id_proceso, id_cliente, documento_identidad, nombres, apellidos, cargo, representacion, tipo_plaza, estado, created_at)
        VALUES (?, ?, ?, ?, ?, ?, 'empleador', ?, 'designado', NOW())");

    echo "<h2>Registrando representantes:</h2>";
    echo "<table><tr><th>#</th><th>Nombre</th><th>Cargo</th><th>Tipo</th><th>Estado</th></tr>";

    $i = 1;
    foreach ($representantes as $r) {
        $stmt->execute([$idProceso, $idCliente, $r[3], $r[1], $r[2], $r[0], $r[4]]);
        $clase = $r[4] == 'principal' ? 'principal' : 'suplente';
        echo "<tr class='$clase'><td>$i</td><td>{$r[1]} {$r[2]}</td><td>{$r[0]}</td><td>{$r[4]}</td><td class='ok'>Registrado</td></tr>";
        $i++;
    }
    echo "</table>";

    echo "<h2 style='color:green'>✅ " . count($representantes) . " representantes del empleador agregados</h2>";
    echo "<p><a href='comites-elecciones/$idCliente/proceso/$idProceso' style='background:#0d6efd;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>Ir al Proceso</a></p>";

} catch (PDOException $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}
?>
