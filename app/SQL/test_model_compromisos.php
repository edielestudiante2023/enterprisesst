<?php
/**
 * Test para verificar que el modelo retorna compromisos correctamente
 */
require_once __DIR__ . '/../../vendor/autoload.php';

// Bootstrap CodeIgniter
$_SERVER['CI_ENVIRONMENT'] = 'development';
define('FCPATH', __DIR__ . '/../../public/');
$paths = require FCPATH . '../app/Config/Paths.php';
require $paths->systemDirectory . '/bootstrap.php';

// Cargar el modelo
$compromisosModel = new \App\Models\ActaCompromisoModel();

echo "=== TEST getByActa(3) ===\n\n";
$compromisos = $compromisosModel->getByActa(3);

echo "Total compromisos: " . count($compromisos) . "\n\n";

foreach ($compromisos as $c) {
    echo "ID: {$c['id_compromiso']} | {$c['descripcion']} | {$c['responsable_nombre']} | fecha_limite: " . ($c['fecha_limite'] ?? 'NULL') . "\n";
}

echo "\n=== ESTRUCTURA DEL PRIMER COMPROMISO ===\n";
if (!empty($compromisos)) {
    print_r(array_keys($compromisos[0]));
}
