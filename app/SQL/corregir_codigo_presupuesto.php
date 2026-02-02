<?php
/**
 * Script para corregir el codigo de documentos de presupuesto SST
 *
 * Problema: Los documentos fueron creados con codigo FT-SST-004 (hardcodeado)
 * Solucion: Actualizar a FT-SST-001 (codigo dinamico correcto)
 *
 * Ejecutar via SqlRunnerController:
 * http://localhost/enterprisesst/public/sql-runner/corregir_codigo_presupuesto
 */

namespace App\SQL;

// Obtener conexion a BD
$db = \Config\Database::connect();

echo "<pre style='font-family: monospace; background: #1e1e1e; color: #d4d4d4; padding: 20px;'>";
echo "=== CORRECCION DE CODIGOS DE PRESUPUESTO SST ===\n\n";

// 1. Buscar documentos de presupuesto con codigo incorrecto
echo "1. Buscando documentos de presupuesto con codigo incorrecto...\n";

$documentos = $db->table('tbl_documentos_sst')
    ->where('tipo_documento', 'presupuesto_sst')
    ->get()
    ->getResultArray();

if (empty($documentos)) {
    echo "   No se encontraron documentos de presupuesto.\n";
} else {
    echo "   Encontrados: " . count($documentos) . " documento(s)\n\n";

    foreach ($documentos as $doc) {
        echo "   - ID: {$doc['id_documento']}, Cliente: {$doc['id_cliente']}, ";
        echo "Codigo actual: <span style='color: #ce9178;'>{$doc['codigo']}</span>, Anio: {$doc['anio']}\n";

        // El codigo correcto para presupuesto SST es FT-SST-001
        $codigoCorrecto = 'FT-SST-001';

        if ($doc['codigo'] !== $codigoCorrecto) {
            echo "     -> <span style='color: #4ec9b0;'>Actualizando de '{$doc['codigo']}' a '{$codigoCorrecto}'...</span>\n";

            $db->table('tbl_documentos_sst')
                ->where('id_documento', $doc['id_documento'])
                ->update([
                    'codigo' => $codigoCorrecto,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            // Tambien actualizar versiones
            $db->table('tbl_doc_versiones_sst')
                ->where('id_documento', $doc['id_documento'])
                ->update(['codigo' => $codigoCorrecto]);

            echo "     -> <span style='color: #6a9955;'>OK</span>\n";
        } else {
            echo "     -> <span style='color: #6a9955;'>Codigo ya es correcto</span>\n";
        }
    }
}

// 2. Verificar plantilla en tbl_doc_plantillas
echo "\n2. Verificando plantilla en tbl_doc_plantillas...\n";

$plantillas = $db->table('tbl_doc_plantillas')
    ->groupStart()
        ->where('tipo_documento', 'presupuesto_sst')
        ->orLike('codigo_sugerido', 'FT-SST')
        ->orWhere('id_estandar', 3)
    ->groupEnd()
    ->get()
    ->getResultArray();

if (empty($plantillas)) {
    echo "   No se encontro plantilla de presupuesto. Creando...\n";

    // Buscar id_estandar para 1.1.3
    $estandar = $db->table('tbl_estandar')
        ->where('codigo', '1.1.3')
        ->get()
        ->getRowArray();
    $idEstandar = $estandar ? $estandar['id_estandar'] : 3;

    $db->table('tbl_doc_plantillas')->insert([
        'tipo_documento' => 'presupuesto_sst',
        'nombre' => 'Asignacion de Recursos para el SG-SST',
        'descripcion' => 'Presupuesto anual de recursos financieros, tecnicos y humanos para el SG-SST',
        'codigo_sugerido' => 'FT-SST',
        'version' => '001',
        'id_estandar' => $idEstandar,
        'activo' => 1,
        'orden' => 100,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    echo "   -> <span style='color: #6a9955;'>Plantilla creada con codigo_sugerido = 'FT-SST'</span>\n";
} else {
    foreach ($plantillas as $pl) {
        echo "   - ID: {$pl['id_plantilla']}, Codigo: <span style='color: #ce9178;'>{$pl['codigo_sugerido']}</span>, ";
        echo "Tipo: {$pl['tipo_documento']}\n";

        // El codigo sugerido debe ser solo el prefijo 'FT-SST'
        if ($pl['codigo_sugerido'] !== 'FT-SST' && strpos($pl['codigo_sugerido'], 'FT-SST') !== false) {
            echo "     -> <span style='color: #4ec9b0;'>Actualizando codigo_sugerido de '{$pl['codigo_sugerido']}' a 'FT-SST'...</span>\n";

            $db->table('tbl_doc_plantillas')
                ->where('id_plantilla', $pl['id_plantilla'])
                ->update([
                    'codigo_sugerido' => 'FT-SST',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            echo "     -> <span style='color: #6a9955;'>OK</span>\n";
        }
    }
}

// 3. Resumen final
echo "\n<span style='color: #569cd6;'>=== RESUMEN ===</span>\n";
echo "- Documentos de presupuesto ahora tienen codigo: <span style='color: #6a9955;'>FT-SST-001</span>\n";
echo "- Plantilla usa codigo_sugerido: <span style='color: #6a9955;'>FT-SST</span> (prefijo base)\n";
echo "- El controlador genera codigo completo dinamicamente: <span style='color: #6a9955;'>FT-SST-001</span>\n";
echo "\n<span style='color: #6a9955;'>Proceso completado.</span>\n";
echo "</pre>";
