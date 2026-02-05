<?php
/**
 * Simulacion del Ciclo Completo del Proceso Electoral
 *
 * Este script simula todo el flujo:
 * 1. Crear proceso (configuracion)
 * 2. Inscribir candidatos
 * 3. Aprobar candidatos
 * 4. Cambiar a votacion
 * 5. Crear censo de votantes
 * 6. Simular votos
 * 7. Pasar a escrutinio (determina elegidos)
 * 8. Designar representantes del empleador
 * 9. Completar proceso
 */

$host = 'localhost';
$port = 3306;
$user = 'root';
$pass = '';
$dbname = 'empresas_sst';

echo "<h1>Simulacion de Ciclo Completo</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    .step { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; }
    .step h3 { margin-top: 0; }
    .success { background: #d4edda; border-color: #28a745; }
    .info { background: #cce5ff; border-color: #0056b3; }
    pre { background: #f8f9fa; padding: 10px; overflow-x: auto; }
</style>";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Obtener un cliente para la prueba
    $cliente = $pdo->query("SELECT id_cliente, nombre_cliente FROM tbl_clientes LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if (!$cliente) {
        die("<p style='color:red'>No hay clientes en la base de datos</p>");
    }

    $idCliente = $cliente['id_cliente'];
    echo "<p><strong>Cliente de prueba:</strong> {$cliente['nombre_cliente']} (ID: $idCliente)</p>";
    echo "<hr>";

    // =====================================================
    // PASO 1: Crear proceso electoral
    // =====================================================
    echo "<div class='step success'><h3>Paso 1: Crear Proceso Electoral</h3>";

    $stmt = $pdo->prepare("INSERT INTO tbl_procesos_electorales
        (id_cliente, tipo_comite, anio, estado, plazas_principales, plazas_suplentes,
         fecha_inicio_periodo, fecha_fin_periodo, created_at)
        VALUES (?, 'COPASST', 2026, 'configuracion', 2, 2, '2026-02-01', '2028-01-31', NOW())");
    $stmt->execute([$idCliente]);
    $idProceso = $pdo->lastInsertId();
    echo "<p>Proceso creado: ID $idProceso - COPASST 2026</p>";
    echo "<p>Plazas: 2 principales + 2 suplentes</p>";
    echo "</div>";

    // =====================================================
    // PASO 2: Cambiar a inscripcion
    // =====================================================
    echo "<div class='step success'><h3>Paso 2: Cambiar a Inscripcion</h3>";
    $pdo->exec("UPDATE tbl_procesos_electorales SET estado = 'inscripcion' WHERE id_proceso = $idProceso");
    echo "<p>Estado cambiado a: <strong>inscripcion</strong></p>";
    echo "</div>";

    // =====================================================
    // PASO 3: Inscribir candidatos de trabajadores
    // =====================================================
    echo "<div class='step success'><h3>Paso 3: Inscribir Candidatos de Trabajadores</h3>";

    $candidatos = [
        ['nombres' => 'Juan Carlos', 'apellidos' => 'Rodriguez Perez', 'documento' => '1234567890', 'cargo' => 'Operario'],
        ['nombres' => 'Maria Elena', 'apellidos' => 'Lopez Garcia', 'documento' => '1234567891', 'cargo' => 'Auxiliar'],
        ['nombres' => 'Pedro Antonio', 'apellidos' => 'Martinez Silva', 'documento' => '1234567892', 'cargo' => 'Tecnico'],
        ['nombres' => 'Ana Lucia', 'apellidos' => 'Gonzalez Ruiz', 'documento' => '1234567893', 'cargo' => 'Coordinadora'],
        ['nombres' => 'Carlos Eduardo', 'apellidos' => 'Sanchez Mora', 'documento' => '1234567894', 'cargo' => 'Supervisor'],
    ];

    $stmt = $pdo->prepare("INSERT INTO tbl_candidatos_comite
        (id_proceso, nombres, apellidos, documento_identidad, cargo, representacion, tipo_plaza, estado, tiene_certificado_50h, created_at)
        VALUES (?, ?, ?, ?, ?, 'trabajador', 'principal', 'inscrito', 1, NOW())");

    foreach ($candidatos as $c) {
        $stmt->execute([$idProceso, $c['nombres'], $c['apellidos'], $c['documento'], $c['cargo']]);
        echo "<p>Inscrito: {$c['nombres']} {$c['apellidos']} - {$c['cargo']}</p>";
    }
    echo "</div>";

    // =====================================================
    // PASO 4: Aprobar candidatos
    // =====================================================
    echo "<div class='step success'><h3>Paso 4: Aprobar Candidatos</h3>";
    $pdo->exec("UPDATE tbl_candidatos_comite SET estado = 'aprobado' WHERE id_proceso = $idProceso AND representacion = 'trabajador'");
    $aprobados = $pdo->query("SELECT COUNT(*) FROM tbl_candidatos_comite WHERE id_proceso = $idProceso AND estado = 'aprobado'")->fetchColumn();
    echo "<p>Candidatos aprobados: <strong>$aprobados</strong></p>";
    echo "</div>";

    // =====================================================
    // PASO 5: Cambiar a votacion e iniciar sistema
    // =====================================================
    echo "<div class='step success'><h3>Paso 5: Iniciar Sistema de Votacion</h3>";

    $enlaceVotacion = bin2hex(random_bytes(16));
    $stmt = $pdo->prepare("UPDATE tbl_procesos_electorales SET
        estado = 'votacion',
        enlace_votacion = ?,
        fecha_inicio_votacion = NOW(),
        fecha_fin_votacion = DATE_ADD(NOW(), INTERVAL 24 HOUR)
        WHERE id_proceso = ?");
    $stmt->execute([$enlaceVotacion, $idProceso]);

    echo "<p>Estado: <strong>votacion</strong></p>";
    echo "<p>Enlace de votacion: <code>$enlaceVotacion</code></p>";
    echo "</div>";

    // =====================================================
    // PASO 6: Crear censo de votantes
    // =====================================================
    echo "<div class='step success'><h3>Paso 6: Crear Censo de Votantes</h3>";

    $votantes = [
        ['nombres' => 'Empleado 1', 'apellidos' => 'Test Uno', 'documento' => '9990001'],
        ['nombres' => 'Empleado 2', 'apellidos' => 'Test Dos', 'documento' => '9990002'],
        ['nombres' => 'Empleado 3', 'apellidos' => 'Test Tres', 'documento' => '9990003'],
        ['nombres' => 'Empleado 4', 'apellidos' => 'Test Cuatro', 'documento' => '9990004'],
        ['nombres' => 'Empleado 5', 'apellidos' => 'Test Cinco', 'documento' => '9990005'],
        ['nombres' => 'Empleado 6', 'apellidos' => 'Test Seis', 'documento' => '9990006'],
        ['nombres' => 'Empleado 7', 'apellidos' => 'Test Siete', 'documento' => '9990007'],
        ['nombres' => 'Empleado 8', 'apellidos' => 'Test Ocho', 'documento' => '9990008'],
        ['nombres' => 'Empleado 9', 'apellidos' => 'Test Nueve', 'documento' => '9990009'],
        ['nombres' => 'Empleado 10', 'apellidos' => 'Test Diez', 'documento' => '9990010'],
    ];

    $stmt = $pdo->prepare("INSERT INTO tbl_votantes_proceso
        (id_proceso, id_cliente, nombres, apellidos, documento_identidad, token_acceso, token_expira, created_at)
        VALUES (?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 48 HOUR), NOW())");

    $tokens = [];
    foreach ($votantes as $v) {
        $token = bin2hex(random_bytes(32));
        $tokens[] = ['documento' => $v['documento'], 'token' => $token];
        $stmt->execute([$idProceso, $idCliente, $v['nombres'], $v['apellidos'], $v['documento'], $token]);
    }

    $pdo->exec("UPDATE tbl_procesos_electorales SET total_votantes = 10 WHERE id_proceso = $idProceso");
    echo "<p>Votantes registrados: <strong>10</strong></p>";
    echo "</div>";

    // =====================================================
    // PASO 7: Simular votacion
    // =====================================================
    echo "<div class='step success'><h3>Paso 7: Simular Votacion</h3>";

    // Obtener candidatos para votar
    $candidatosVotar = $pdo->query("SELECT id_candidato, nombres, apellidos FROM tbl_candidatos_comite
        WHERE id_proceso = $idProceso AND representacion = 'trabajador' AND estado = 'aprobado'")->fetchAll(PDO::FETCH_ASSOC);

    // Distribuir votos: Candidato 1 = 4 votos, Candidato 2 = 3 votos, Candidato 3 = 2 votos, Candidato 4 = 1 voto, Candidato 5 = 0 votos
    $distribucionVotos = [4, 3, 2, 1, 0];
    $votosEmitidos = 0;
    $votanteIdx = 0;

    $stmtVoto = $pdo->prepare("INSERT INTO tbl_votos_comite (id_proceso, id_candidato, hash_votante, fecha_voto) VALUES (?, ?, ?, NOW())");
    $stmtMarcar = $pdo->prepare("UPDATE tbl_votantes_proceso SET ha_votado = 1, fecha_voto = NOW() WHERE id_proceso = ? AND documento_identidad = ?");
    $stmtConteo = $pdo->prepare("UPDATE tbl_candidatos_comite SET votos_obtenidos = votos_obtenidos + 1 WHERE id_candidato = ?");

    for ($i = 0; $i < count($candidatosVotar) && $i < count($distribucionVotos); $i++) {
        $numVotos = $distribucionVotos[$i];
        $candidato = $candidatosVotar[$i];

        for ($j = 0; $j < $numVotos && $votanteIdx < count($tokens); $j++) {
            $hashVotante = hash('sha256', $tokens[$votanteIdx]['documento'] . $idProceso . time() . $j);
            $stmtVoto->execute([$idProceso, $candidato['id_candidato'], $hashVotante]);
            $stmtMarcar->execute([$idProceso, $tokens[$votanteIdx]['documento']]);
            $stmtConteo->execute([$candidato['id_candidato']]);
            $votanteIdx++;
            $votosEmitidos++;
        }

        if ($numVotos > 0) {
            echo "<p>{$candidato['nombres']} {$candidato['apellidos']}: <strong>$numVotos votos</strong></p>";
        }
    }

    $pdo->exec("UPDATE tbl_procesos_electorales SET votos_emitidos = $votosEmitidos WHERE id_proceso = $idProceso");
    echo "<p>Total votos emitidos: <strong>$votosEmitidos</strong> de 10 votantes</p>";
    echo "</div>";

    // =====================================================
    // PASO 8: Cambiar a escrutinio (determina elegidos)
    // =====================================================
    echo "<div class='step success'><h3>Paso 8: Escrutinio - Determinar Elegidos</h3>";

    // Cambiar estado
    $pdo->exec("UPDATE tbl_procesos_electorales SET estado = 'escrutinio', fecha_escrutinio = NOW() WHERE id_proceso = $idProceso");

    // Determinar elegidos automaticamente (ordenados por votos)
    $candidatosOrdenados = $pdo->query("SELECT id_candidato, nombres, apellidos, votos_obtenidos
        FROM tbl_candidatos_comite
        WHERE id_proceso = $idProceso AND representacion = 'trabajador' AND estado = 'aprobado'
        ORDER BY votos_obtenidos DESC")->fetchAll(PDO::FETCH_ASSOC);

    $plazasPrincipales = 2;
    $plazasSuplentes = 2;
    $i = 0;

    foreach ($candidatosOrdenados as $c) {
        if ($i < $plazasPrincipales) {
            $pdo->exec("UPDATE tbl_candidatos_comite SET estado = 'elegido', tipo_plaza = 'principal' WHERE id_candidato = {$c['id_candidato']}");
            echo "<p><strong>PRINCIPAL:</strong> {$c['nombres']} {$c['apellidos']} ({$c['votos_obtenidos']} votos)</p>";
        } elseif ($i < ($plazasPrincipales + $plazasSuplentes)) {
            $pdo->exec("UPDATE tbl_candidatos_comite SET estado = 'elegido', tipo_plaza = 'suplente' WHERE id_candidato = {$c['id_candidato']}");
            echo "<p><strong>SUPLENTE:</strong> {$c['nombres']} {$c['apellidos']} ({$c['votos_obtenidos']} votos)</p>";
        } else {
            $pdo->exec("UPDATE tbl_candidatos_comite SET estado = 'no_elegido' WHERE id_candidato = {$c['id_candidato']}");
            echo "<p style='color:gray'>No elegido: {$c['nombres']} {$c['apellidos']} ({$c['votos_obtenidos']} votos)</p>";
        }
        $i++;
    }
    echo "</div>";

    // =====================================================
    // PASO 9: Designar representantes del empleador
    // =====================================================
    echo "<div class='step success'><h3>Paso 9: Designar Representantes del Empleador</h3>";

    $pdo->exec("UPDATE tbl_procesos_electorales SET estado = 'designacion_empleador' WHERE id_proceso = $idProceso");

    $empleadores = [
        ['nombres' => 'Gerente', 'apellidos' => 'General', 'documento' => '8880001', 'cargo' => 'Gerente General', 'tipo' => 'principal'],
        ['nombres' => 'Director', 'apellidos' => 'RRHH', 'documento' => '8880002', 'cargo' => 'Director RRHH', 'tipo' => 'principal'],
        ['nombres' => 'Jefe', 'apellidos' => 'Operaciones', 'documento' => '8880003', 'cargo' => 'Jefe de Operaciones', 'tipo' => 'suplente'],
        ['nombres' => 'Coordinador', 'apellidos' => 'SST', 'documento' => '8880004', 'cargo' => 'Coordinador SST', 'tipo' => 'suplente'],
    ];

    $stmt = $pdo->prepare("INSERT INTO tbl_candidatos_comite
        (id_proceso, nombres, apellidos, documento_identidad, cargo, representacion, tipo_plaza, estado, tiene_certificado_50h, created_at)
        VALUES (?, ?, ?, ?, ?, 'empleador', ?, 'designado', 1, NOW())");

    foreach ($empleadores as $e) {
        $stmt->execute([$idProceso, $e['nombres'], $e['apellidos'], $e['documento'], $e['cargo'], $e['tipo']]);
        echo "<p><strong>" . strtoupper($e['tipo']) . ":</strong> {$e['nombres']} {$e['apellidos']} - {$e['cargo']}</p>";
    }
    echo "</div>";

    // =====================================================
    // PASO 10: Cambiar a firmas
    // =====================================================
    echo "<div class='step success'><h3>Paso 10: Estado de Firmas</h3>";
    $pdo->exec("UPDATE tbl_procesos_electorales SET estado = 'firmas' WHERE id_proceso = $idProceso");
    echo "<p>Estado cambiado a: <strong>firmas</strong></p>";
    echo "<p><em>En este punto se generarian las actas para firma electronica</em></p>";
    echo "</div>";

    // =====================================================
    // PASO 11: Completar proceso y vincular al comite
    // =====================================================
    echo "<div class='step success'><h3>Paso 11: Completar Proceso - Crear Comite</h3>";

    // Obtener tipo de comite
    $tipoComite = $pdo->query("SELECT id_tipo FROM tbl_tipos_comite WHERE codigo = 'COPASST'")->fetch(PDO::FETCH_ASSOC);
    $idTipo = $tipoComite['id_tipo'];

    // Crear o actualizar comite
    $comiteExiste = $pdo->query("SELECT id_comite FROM tbl_comites WHERE id_cliente = $idCliente AND id_tipo = $idTipo")->fetch(PDO::FETCH_ASSOC);

    if ($comiteExiste) {
        $idComite = $comiteExiste['id_comite'];
        echo "<p>Comite existente actualizado: ID $idComite</p>";
    } else {
        $stmt = $pdo->prepare("INSERT INTO tbl_comites (id_cliente, id_tipo, estado, fecha_conformacion, fecha_vencimiento, created_at)
            VALUES (?, ?, 'activo', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 2 YEAR), NOW())");
        $stmt->execute([$idCliente, $idTipo]);
        $idComite = $pdo->lastInsertId();
        echo "<p>Comite creado: ID $idComite</p>";
    }

    // Actualizar proceso con id_comite
    $pdo->exec("UPDATE tbl_procesos_electorales SET id_comite = $idComite, estado = 'completado', fecha_completado = NOW() WHERE id_proceso = $idProceso");

    // Limpiar miembros anteriores
    $pdo->exec("DELETE FROM tbl_miembros_comite WHERE id_comite = $idComite");

    // Insertar miembros elegidos
    $elegidos = $pdo->query("SELECT * FROM tbl_candidatos_comite WHERE id_proceso = $idProceso AND estado IN ('elegido', 'designado')")->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("INSERT INTO tbl_miembros_comite
        (id_comite, id_candidato, nombres, apellidos, documento_identidad, cargo, representacion, tipo_miembro, tiene_certificado_50h, estado, fecha_ingreso, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'activo', CURDATE(), NOW())");

    echo "<h4>Miembros del Comite COPASST:</h4>";
    echo "<table border='1' cellpadding='5' style='margin:10px 0;'>";
    echo "<tr style='background:#28a745;color:white;'><th>Nombre</th><th>Cargo</th><th>Representacion</th><th>Tipo</th></tr>";

    foreach ($elegidos as $e) {
        $stmt->execute([
            $idComite, $e['id_candidato'], $e['nombres'], $e['apellidos'],
            $e['documento_identidad'], $e['cargo'], $e['representacion'],
            $e['tipo_plaza'], $e['tiene_certificado_50h']
        ]);
        $color = $e['representacion'] === 'empleador' ? '#0056b3' : '#28a745';
        echo "<tr><td>{$e['nombres']} {$e['apellidos']}</td><td>{$e['cargo']}</td><td style='background:$color;color:white;'>{$e['representacion']}</td><td>{$e['tipo_plaza']}</td></tr>";
    }
    echo "</table>";
    echo "<p><strong>Total miembros:</strong> " . count($elegidos) . "</p>";
    echo "</div>";

    // =====================================================
    // RESUMEN FINAL
    // =====================================================
    echo "<div class='step info'><h3>Resumen del Ciclo</h3>";
    echo "<pre>";
    echo "Proceso ID: $idProceso\n";
    echo "Tipo: COPASST 2026\n";
    echo "Estado final: completado\n";
    echo "Comite ID: $idComite\n";
    echo "\n--- Resultados ---\n";
    echo "Candidatos inscritos: 5\n";
    echo "Votantes en censo: 10\n";
    echo "Votos emitidos: $votosEmitidos\n";
    echo "Elegidos trabajadores: 4 (2 principales, 2 suplentes)\n";
    echo "Designados empleador: 4 (2 principales, 2 suplentes)\n";
    echo "Total miembros comite: 8\n";
    echo "</pre>";

    echo "<h4>Enlaces para probar:</h4>";
    echo "<p><a href='/enterprisesst/public/comites-elecciones/$idCliente/proceso/$idProceso' target='_blank'>Ver Proceso Completado</a></p>";
    echo "</div>";

    echo "<hr>";
    echo "<h2 style='color:green'>Simulacion completada exitosamente</h2>";
    echo "<p>El sistema de comites funciona correctamente.</p>";

} catch (PDOException $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
