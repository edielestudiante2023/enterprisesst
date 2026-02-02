<?php
/**
 * Script para agregar compromisos de prueba al acta 3
 */
$pdo = new PDO('mysql:host=localhost;dbname=empresas_sst;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== AGREGANDO COMPROMISOS DE PRUEBA AL ACTA 3 ===\n\n";

$compromisos = [
    [
        'descripcion' => 'Realizar inspección de puestos de trabajo',
        'responsable_nombre' => 'Diana Cuestas',
        'fecha_vencimiento' => '2026-02-15',
        'estado' => 'pendiente',
        'prioridad' => 'alta'
    ],
    [
        'descripcion' => 'Actualizar matriz de peligros del área administrativa',
        'responsable_nombre' => 'SOLANGEL CUERVO PERDOMO',
        'fecha_vencimiento' => '2026-02-28',
        'estado' => 'pendiente',
        'prioridad' => 'media'
    ],
    [
        'descripcion' => 'Programar capacitación de primeros auxilios',
        'responsable_nombre' => 'Diana Cuestas',
        'fecha_vencimiento' => '2026-03-10',
        'estado' => 'pendiente',
        'prioridad' => 'media'
    ]
];

$stmt = $pdo->prepare("
    INSERT INTO tbl_acta_compromisos
    (id_acta, id_comite, id_cliente, numero_compromiso, descripcion, responsable_nombre, fecha_vencimiento, estado, prioridad, created_at)
    VALUES (3, 2, 11, ?, ?, ?, ?, ?, ?, NOW())
");

foreach ($compromisos as $i => $c) {
    $stmt->execute([
        $i + 1,
        $c['descripcion'],
        $c['responsable_nombre'],
        $c['fecha_vencimiento'],
        $c['estado'],
        $c['prioridad']
    ]);
    echo "✓ Compromiso " . ($i + 1) . ": {$c['descripcion']}\n";
}

echo "\n=== VERIFICACIÓN ===\n";
$stmt = $pdo->query("SELECT id_compromiso, numero_compromiso, descripcion, responsable_nombre, fecha_vencimiento FROM tbl_acta_compromisos WHERE id_acta = 3");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $c) {
    echo "#{$c['numero_compromiso']}: {$c['descripcion']} | {$c['responsable_nombre']} | {$c['fecha_vencimiento']}\n";
}

echo "\nCOMPLETADO.\n";
