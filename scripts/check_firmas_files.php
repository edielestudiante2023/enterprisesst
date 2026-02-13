#!/usr/bin/env php
<?php
/**
 * Script CLI para verificar archivos de firma en produccion
 * Se ejecuta DENTRO del contexto CodeIgniter para usar FCPATH
 * Uso: php scripts/check_firmas_files.php
 */

// Bootstrappear CodeIgniter
$_SERVER['CI_ENVIRONMENT'] = 'production';
define('FCPATH', realpath(__DIR__ . '/../public') . DIRECTORY_SEPARATOR);

echo "=== VERIFICACION DE ARCHIVOS DE FIRMA ===\n\n";
echo "FCPATH = " . FCPATH . "\n";
echo "DIRECTORY_SEPARATOR = " . DIRECTORY_SEPARATOR . "\n\n";

// 1. Verificar FIRMA_DIANITA.jpg
$firmaDianita = FCPATH . 'img' . DIRECTORY_SEPARATOR . 'FIRMA_DIANITA.jpg';
echo "--- FIRMA DIANITA (Cycloid) ---\n";
echo "Ruta: {$firmaDianita}\n";
echo "Existe: " . (file_exists($firmaDianita) ? 'SI (' . filesize($firmaDianita) . ' bytes)' : 'NO') . "\n\n";

// 2. Verificar firma representante legal del cliente
$firmaRepLegal = '1770992480_c7f4ff2860207cc661fc.png';
$rutaRepLegal = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . $firmaRepLegal;
echo "--- FIRMA REPRESENTANTE LEGAL (Cliente 19) ---\n";
echo "Archivo BD: {$firmaRepLegal}\n";
echo "Ruta completa: {$rutaRepLegal}\n";
echo "Existe: " . (file_exists($rutaRepLegal) ? 'SI (' . filesize($rutaRepLegal) . ' bytes)' : 'NO') . "\n\n";

// 3. Verificar firma consultor
$firmaConsultor = '1725641952_861b2f664c4839a3df12.png';
$rutaConsultor = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . $firmaConsultor;
echo "--- FIRMA CONSULTOR (ID 17) ---\n";
echo "Archivo BD: {$firmaConsultor}\n";
echo "Ruta completa: {$rutaConsultor}\n";
echo "Existe: " . (file_exists($rutaConsultor) ? 'SI (' . filesize($rutaConsultor) . ' bytes)' : 'NO') . "\n\n";

// 4. Verificar directorio uploads
$uploadsDir = FCPATH . 'uploads';
echo "--- DIRECTORIO UPLOADS ---\n";
echo "Ruta: {$uploadsDir}\n";
echo "Existe: " . (is_dir($uploadsDir) ? 'SI' : 'NO') . "\n";
if (is_dir($uploadsDir)) {
    $files = scandir($uploadsDir);
    $pngFiles = array_filter($files, function($f) { return pathinfo($f, PATHINFO_EXTENSION) === 'png'; });
    echo "Total archivos PNG: " . count($pngFiles) . "\n";
    // Mostrar primeros 5 PNG para referencia
    $i = 0;
    foreach ($pngFiles as $f) {
        if ($i >= 5) { echo "  ... y " . (count($pngFiles) - 5) . " mas\n"; break; }
        echo "  - {$f} (" . filesize($uploadsDir . DIRECTORY_SEPARATOR . $f) . " bytes)\n";
        $i++;
    }
}

// 5. Verificar logos del header (que sabemos que funcionan)
echo "\n--- LOGOS HEADER (referencia) ---\n";
$logoCycloid = FCPATH . 'uploads/logocycloidsinfondo.png';
$logoSST = FCPATH . 'uploads/logosst.png';
echo "logocycloidsinfondo.png: " . (file_exists($logoCycloid) ? 'SI (' . filesize($logoCycloid) . ' bytes)' : 'NO') . "\n";
echo "logosst.png: " . (file_exists($logoSST) ? 'SI (' . filesize($logoSST) . ' bytes)' : 'NO') . "\n";

echo "\n=== FIN VERIFICACION ===\n";
