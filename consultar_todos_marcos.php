<?php
// Script temporal para consultar TODOS los marcos normativos

$host = 'localhost';
$db = 'empresas_sst';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "═══════════════════════════════════════════════════════════\n";
    echo "MARCOS NORMATIVOS EN BASE DE DATOS\n";
    echo "═══════════════════════════════════════════════════════════\n\n";

    // Contar total
    $stmtCount = $pdo->query("SELECT COUNT(*) AS total FROM tbl_marco_normativo WHERE activo = 1");
    $total = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];

    echo "Total de marcos normativos activos: $total / 36\n\n";

    if ($total == 0) {
        echo "⚠️  NO HAY MARCOS NORMATIVOS EN LA BASE DE DATOS\n";
        echo "Todos los 36 módulos necesitan generar su marco normativo.\n\n";
        exit;
    }

    echo "───────────────────────────────────────────────────────────\n";
    echo "LISTADO COMPLETO:\n";
    echo "───────────────────────────────────────────────────────────\n\n";

    $stmt = $pdo->query("
        SELECT
            tipo_documento,
            fecha_actualizacion,
            metodo_actualizacion,
            DATEDIFF(NOW(), fecha_actualizacion) AS dias_transcurridos,
            LENGTH(marco_normativo_texto) AS caracteres,
            IF(DATEDIFF(NOW(), fecha_actualizacion) <= vigencia_dias, 'Vigente', 'Vencido') AS estado
        FROM tbl_marco_normativo
        WHERE activo = 1
        ORDER BY tipo_documento
    ");

    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $row) {
        $icono = $row['estado'] === 'Vigente' ? '✅' : '⚠️ ';

        echo "{$icono} {$row['tipo_documento']}\n";
        echo "   Estado: {$row['estado']} ({$row['dias_transcurridos']} días)\n";
        echo "   Fecha: {$row['fecha_actualizacion']}\n";
        echo "   Método: {$row['metodo_actualizacion']}\n";
        echo "   Tamaño: {$row['caracteres']} caracteres\n";
        echo "\n";
    }

    echo "───────────────────────────────────────────────────────────\n";
    echo "TIPOS FALTANTES:\n";
    echo "───────────────────────────────────────────────────────────\n\n";

    // Todos los tipos que deberían existir
    $tiposEsperados = [
        // 1.1 - Requisitos Legales
        'identificacion_alto_riesgo',

        // 2.1 - Políticas
        'politica_sst_general',
        'politica_alcohol_drogas',
        'politica_acoso_laboral',
        'politica_violencias_genero',
        'politica_discriminacion',
        'politica_prevencion_emergencias',

        // 2.2 - Planificación
        'plan_objetivos_metas',
        'programa_capacitacion',

        // 2.8 - Comunicación
        'mecanismos_comunicacion_sgsst',

        // 2.9 - Adquisiciones
        'procedimiento_adquisiciones',
        'procedimiento_evaluacion_proveedores',

        // 2.10 - Gestión del Cambio
        'procedimiento_gestion_cambio',

        // 2.11 - Control Documental
        'procedimiento_control_documental',
        'procedimiento_matriz_legal',

        // 3.1 - Promoción y Prevención
        'programa_promocion_prevencion_salud',
        'programa_induccion_reinduccion',
        'procedimiento_evaluaciones_medicas',
        'programa_evaluaciones_medicas_ocupacionales',
        'programa_estilos_vida_saludable',

        // 3.2 - Investigación de Incidentes
        'procedimiento_investigacion_accidentes',
        'procedimiento_investigacion_incidentes',

        // 4.1 - Identificación de Peligros
        'metodologia_identificacion_peligros',
        'identificacion_sustancias_cancerigenas',

        // 4.2 - Programas de Vigilancia
        'pve_riesgo_biomecanico',
        'pve_riesgo_psicosocial',
        'programa_mantenimiento_periodico',

        // Comités
        'manual_convivencia_laboral',

        // Actas Constitución
        'acta_constitucion_copasst',
        'acta_constitucion_cocolab',
        'acta_constitucion_brigada',
        'acta_constitucion_vigia',

        // Actas Recomposición
        'acta_recomposicion_copasst',
        'acta_recomposicion_cocolab',
        'acta_recomposicion_brigada',
        'acta_recomposicion_vigia',
    ];

    $tiposEnBD = array_column($resultados, 'tipo_documento');
    $tiposFaltantes = array_diff($tiposEsperados, $tiposEnBD);

    if (count($tiposFaltantes) === 0) {
        echo "✅ ¡Todos los 36 tipos tienen marco normativo en BD!\n\n";
    } else {
        echo "Total faltantes: " . count($tiposFaltantes) . " / 36\n\n";
        foreach ($tiposFaltantes as $tipo) {
            echo "❌ {$tipo}\n";
        }
        echo "\n";
    }

    echo "═══════════════════════════════════════════════════════════\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
