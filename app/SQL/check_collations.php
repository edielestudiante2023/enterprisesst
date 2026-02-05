<?php
$pdo = new PDO('mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=empresas_sst;charset=utf8mb4', 'cycloid_userdb', 'AVNS_iDypWizlpMRwHIORJGG', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false]);

echo "<h2>Columnas de JOIN relevantes</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Tabla.Columna</th><th>Collation</th><th>Estado</th></tr>";

$columnas = [
    ['tbl_procesos_electorales', 'tipo_comite'],
    ['tbl_tipos_comite', 'codigo'],
    ['tbl_procesos_electorales', 'id_cliente'],
    ['tbl_clientes', 'id_cliente'],
    ['tbl_comites', 'id_tipo_comite'],
    ['tbl_tipos_comite', 'id_tipo_comite'],
    ['tbl_candidatos_comite', 'id_proceso'],
    ['tbl_procesos_electorales', 'id_proceso'],
];

foreach ($columnas as $c) {
    $stmt = $pdo->query("
        SELECT COLUMN_NAME, COLLATION_NAME
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = 'empresas_sst'
        AND TABLE_NAME = '{$c[0]}'
        AND COLUMN_NAME = '{$c[1]}'
    ");
    $col = $stmt->fetch(PDO::FETCH_ASSOC);
    $collation = $col ? ($col['COLLATION_NAME'] ?? 'NULL') : 'NO ENCONTRADA';
    $status = ($collation === 'utf8mb4_general_ci' || $collation === 'NULL' || $collation === null) ? 'OK' : 'PROBLEMA';
    $color = $status === 'OK' ? 'green' : 'red';
    echo "<tr><td>{$c[0]}.{$c[1]}</td><td>$collation</td><td style='color:$color'>$status</td></tr>";
}
echo "</table>";

echo "<h2>Tablas con 'client' en el nombre</h2>";
$stmt = $pdo->query("SELECT TABLE_NAME, TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'empresas_sst' AND TABLE_NAME LIKE '%client%'");
$tablas = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<ul>";
foreach ($tablas as $t) {
    echo "<li>{$t['TABLE_NAME']} = {$t['TABLE_COLLATION']}</li>";
}
echo "</ul>";

// Probar la consulta que falla
echo "<h2>Prueba de consulta del controlador</h2>";
try {
    $stmt = $pdo->query("
        SELECT pe.*, tc.nombre as nombre_comite
        FROM tbl_procesos_electorales pe
        LEFT JOIN tbl_tipos_comite tc ON pe.tipo_comite = tc.codigo
        WHERE pe.id_cliente = 1
        LIMIT 1
    ");
    echo "<p style='color:green'>Consulta ejecutada correctamente</p>";
} catch (PDOException $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}
?>
