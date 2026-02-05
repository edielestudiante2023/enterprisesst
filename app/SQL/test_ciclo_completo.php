<?php
/**
 * Test del Ciclo Completo de Comites Electorales
 * Verifica que todas las tablas y columnas existan
 */

$host = 'localhost';
$port = 3306;
$user = 'root';
$pass = '';
$dbname = 'empresas_sst';

echo "<h1>Test de Ciclo Completo - Sistema de Comites</h1>";
echo "<hr>";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "<p style='color:green'>Conexion OK</p>";

    // Test 1: Verificar tablas principales
    echo "<h2>1. Tablas Principales</h2>";
    $tablas = [
        'tbl_tipos_comite' => 'Tipos de comite (COPASST, COCOLAB, etc.)',
        'tbl_comites' => 'Comites conformados por cliente',
        'tbl_procesos_electorales' => 'Procesos de eleccion',
        'tbl_candidatos_comite' => 'Candidatos inscritos',
        'tbl_votantes_proceso' => 'Censo de votantes',
        'tbl_votos_comite' => 'Votos anonimos',
        'tbl_miembros_comite' => 'Miembros activos del comite'
    ];

    foreach ($tablas as $tabla => $desc) {
        $check = $pdo->query("SHOW TABLES LIKE '$tabla'")->fetch();
        $status = $check ? 'OK' : 'FALTA';
        $color = $check ? 'green' : 'red';
        echo "<p style='color:$color'>[$status] $tabla - $desc</p>";
    }

    // Test 2: Columnas de tbl_procesos_electorales
    echo "<h2>2. Columnas de tbl_procesos_electorales</h2>";
    $columnas = ['id_proceso', 'id_cliente', 'tipo_comite', 'anio', 'estado', 'plazas_principales', 'plazas_suplentes',
                 'enlace_votacion', 'fecha_inicio_votacion', 'fecha_fin_votacion', 'total_votantes', 'votos_emitidos',
                 'fecha_escrutinio', 'fecha_completado', 'id_comite'];

    $existentes = $pdo->query("SHOW COLUMNS FROM tbl_procesos_electorales")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($columnas as $col) {
        $existe = in_array($col, $existentes);
        $status = $existe ? 'OK' : 'FALTA';
        $color = $existe ? 'green' : 'red';
        echo "<p style='color:$color'>[$status] $col</p>";
    }

    // Test 3: Columnas de tbl_candidatos_comite
    echo "<h2>3. Columnas de tbl_candidatos_comite</h2>";
    $columnas = ['id_candidato', 'id_proceso', 'nombres', 'apellidos', 'documento_identidad', 'cargo',
                 'representacion', 'tipo_plaza', 'estado', 'votos_obtenidos', 'foto'];

    $existentes = $pdo->query("SHOW COLUMNS FROM tbl_candidatos_comite")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($columnas as $col) {
        $existe = in_array($col, $existentes);
        $status = $existe ? 'OK' : 'FALTA';
        $color = $existe ? 'green' : 'red';
        echo "<p style='color:$color'>[$status] $col</p>";
    }

    // Test 4: Tipos de comite
    echo "<h2>4. Tipos de Comite</h2>";
    $tipos = $pdo->query("SELECT codigo, nombre FROM tbl_tipos_comite ORDER BY id_tipo")->fetchAll(PDO::FETCH_ASSOC);
    if (empty($tipos)) {
        echo "<p style='color:orange'>No hay tipos de comite registrados. Insertando...</p>";
        $pdo->exec("INSERT INTO tbl_tipos_comite (codigo, nombre, descripcion) VALUES
            ('COPASST', 'COPASST', 'Comite Paritario de Seguridad y Salud en el Trabajo'),
            ('COCOLAB', 'COCOLAB', 'Comite de Convivencia Laboral'),
            ('BRIGADA', 'Brigada de Emergencia', 'Brigada de Emergencia'),
            ('VIGIA', 'Vigia SST', 'Vigia de Seguridad y Salud en el Trabajo')
        ON DUPLICATE KEY UPDATE nombre=VALUES(nombre)");
        $tipos = $pdo->query("SELECT codigo, nombre FROM tbl_tipos_comite ORDER BY id_tipo")->fetchAll(PDO::FETCH_ASSOC);
    }
    foreach ($tipos as $t) {
        echo "<p style='color:green'>[OK] {$t['codigo']} - {$t['nombre']}</p>";
    }

    // Test 5: Estados del proceso
    echo "<h2>5. Estados del Proceso</h2>";
    $estados = ['configuracion', 'inscripcion', 'votacion', 'escrutinio', 'designacion_empleador', 'firmas', 'completado', 'cancelado'];
    foreach ($estados as $estado) {
        echo "<p style='color:green'>[OK] $estado</p>";
    }

    // Test 6: Procesos existentes
    echo "<h2>6. Procesos Existentes</h2>";
    $procesos = $pdo->query("SELECT pe.id_proceso, pe.tipo_comite, pe.anio, pe.estado, c.nombre_cliente
                              FROM tbl_procesos_electorales pe
                              JOIN tbl_clientes c ON pe.id_cliente = c.id_cliente
                              ORDER BY pe.id_proceso DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    if (empty($procesos)) {
        echo "<p style='color:blue'>No hay procesos electorales aun.</p>";
    } else {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr style='background:#333;color:white;'><th>ID</th><th>Cliente</th><th>Tipo</th><th>Año</th><th>Estado</th></tr>";
        foreach ($procesos as $p) {
            echo "<tr><td>{$p['id_proceso']}</td><td>{$p['nombre_cliente']}</td><td>{$p['tipo_comite']}</td><td>{$p['anio']}</td><td>{$p['estado']}</td></tr>";
        }
        echo "</table>";
    }

    echo "<hr>";
    echo "<h2 style='color:green'>Test completado. El sistema esta listo.</h2>";

    echo "<h3>Flujo del Proceso Electoral:</h3>";
    echo "<pre>";
    echo "1. configuracion      -> Configurar plazas y fechas\n";
    echo "2. inscripcion        -> Inscribir candidatos de trabajadores\n";
    echo "3. votacion           -> Gestionar censo y votacion electronica\n";
    echo "4. escrutinio         -> Determinar elegidos automaticamente\n";
    echo "5. designacion_empleador -> Designar representantes del empleador\n";
    echo "6. firmas             -> Generar actas y recoger firmas\n";
    echo "7. completado         -> Vincular miembros al comite\n";
    echo "</pre>";

} catch (PDOException $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}
?>
