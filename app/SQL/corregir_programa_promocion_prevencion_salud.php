<?php
/**
 * Script para CORREGIR el flujo del Programa de Promoción y Prevención en Salud (3.1.2)
 *
 * PROBLEMA: Se configuró inicialmente como secciones_ia puras
 * CORRECCIÓN: Debe ser programa_con_pta con fases:
 *   1. Actividades PyP → van al PTA
 *   2. Indicadores PyP → van a tbl_indicadores_sst
 *   3. Documento IA → se alimenta de la BD
 *
 * Ejecutar: php app/SQL/corregir_programa_promocion_prevencion_salud.php
 */

echo "=== CORRECCIÓN MÓDULO 3.1.2 - FLUJO CON FASES ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Configuración de conexiones
$conexiones = [
    'local' => [
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'empresas_sst',
        'username' => 'root',
        'password' => '',
        'ssl' => false
    ],
    'produccion' => [
        'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port' => 25060,
        'database' => 'empresas_sst',
        'username' => 'cycloid_userdb',
        'password' => 'AVNS_iDypWizlpMRwHIORJGG',
        'ssl' => true
    ]
];

// ============================================================
// SQL 1: Cambiar flujo del tipo de documento
// ============================================================
$sqlCambiarFlujo = <<<'SQL'
UPDATE tbl_doc_tipo_configuracion
SET flujo = 'programa_con_pta',
    descripcion = 'Programa que establece las actividades de promoción de la salud y prevención de enfermedades laborales. FLUJO: 1) Diseñar actividades PyP en PTA, 2) Generar indicadores, 3) Generar documento con IA alimentado de BD.',
    updated_at = NOW()
WHERE tipo_documento = 'programa_promocion_prevencion_salud';
SQL;

// ============================================================
// SQL 2: Actualizar sección de Cronograma para que lea del PTA
// ============================================================
$sqlActualizarCronograma = <<<'SQL'
UPDATE tbl_doc_secciones_config sc
JOIN tbl_doc_tipo_configuracion tc ON sc.id_tipo_config = tc.id_tipo_config
SET sc.tipo_contenido = 'tabla_dinamica',
    sc.tabla_dinamica_tipo = 'pta_pyp_salud',
    sc.sincronizar_bd = NULL,
    sc.prompt_ia = 'Esta sección muestra el cronograma de actividades de Promoción y Prevención en Salud. Los datos se obtienen automáticamente del Plan de Trabajo Anual (PTA) donde tipo_servicio = "Programa PyP Salud". NO generar contenido - solo mostrar datos de BD.'
WHERE tc.tipo_documento = 'programa_promocion_prevencion_salud'
AND sc.seccion_key = 'cronograma';
SQL;

// ============================================================
// SQL 3: Actualizar sección de Indicadores para que lea de BD
// ============================================================
$sqlActualizarIndicadores = <<<'SQL'
UPDATE tbl_doc_secciones_config sc
JOIN tbl_doc_tipo_configuracion tc ON sc.id_tipo_config = tc.id_tipo_config
SET sc.tipo_contenido = 'tabla_dinamica',
    sc.tabla_dinamica_tipo = 'indicadores_pyp_salud',
    sc.sincronizar_bd = NULL,
    sc.prompt_ia = 'Esta sección muestra los indicadores del Programa de Promoción y Prevención en Salud. Los datos se obtienen automáticamente de tbl_indicadores_sst donde categoria = "pyp_salud". NO generar contenido - solo mostrar datos de BD.'
WHERE tc.tipo_documento = 'programa_promocion_prevencion_salud'
AND sc.seccion_key = 'indicadores';
SQL;

// ============================================================
// SQL 4: Insertar configuración de tabla dinámica para PTA PyP
// ============================================================
$sqlTablaDinamicaPTA = <<<'SQL'
INSERT INTO tbl_doc_tablas_dinamicas
(tabla_key, nombre, descripcion, query_base, columnas, filtro_cliente, estilo_encabezado, activo)
VALUES
('pta_pyp_salud',
 'Actividades de Promoción y Prevención en Salud',
 'Muestra las actividades del PTA relacionadas con PyP en Salud',
 'SELECT
    actividad_plandetrabajo as actividad,
    responsable_actividad as responsable,
    DATE_FORMAT(fecha_propuesta, "%M") as mes_programado,
    ciclo_phva as phva,
    estado_actividad as estado
  FROM tbl_pta_cliente
  WHERE id_cliente = :id_cliente
  AND YEAR(fecha_propuesta) = :anio
  AND (tipo_servicio LIKE "%PyP Salud%" OR tipo_servicio LIKE "%Promocion%" OR tipo_servicio LIKE "%Prevencion%")
  ORDER BY fecha_propuesta',
 '[{"key": "actividad", "label": "Actividad", "width": "40%"}, {"key": "responsable", "label": "Responsable", "width": "20%"}, {"key": "mes_programado", "label": "Mes", "width": "15%"}, {"key": "phva", "label": "PHVA", "width": "10%"}, {"key": "estado", "label": "Estado", "width": "15%"}]',
 1,
 'success',
 1)
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    query_base = VALUES(query_base),
    columnas = VALUES(columnas);
SQL;

// ============================================================
// SQL 5: Insertar configuración de tabla dinámica para Indicadores PyP
// ============================================================
$sqlTablaDinamicaIndicadores = <<<'SQL'
INSERT INTO tbl_doc_tablas_dinamicas
(tabla_key, nombre, descripcion, query_base, columnas, filtro_cliente, estilo_encabezado, activo)
VALUES
('indicadores_pyp_salud',
 'Indicadores de Promoción y Prevención en Salud',
 'Muestra los indicadores configurados para PyP en Salud',
 'SELECT
    nombre_indicador as nombre,
    formula,
    meta,
    periodicidad,
    COALESCE(valor_actual, "Pendiente") as valor_actual
  FROM tbl_indicadores_sst
  WHERE id_cliente = :id_cliente
  AND activo = 1
  AND (categoria = "pyp_salud" OR categoria = "promocion_prevencion" OR nombre_indicador LIKE "%salud%" OR nombre_indicador LIKE "%médico%" OR nombre_indicador LIKE "%enfermedad%")
  ORDER BY nombre_indicador',
 '[{"key": "nombre", "label": "Indicador", "width": "30%"}, {"key": "formula", "label": "Fórmula", "width": "25%"}, {"key": "meta", "label": "Meta", "width": "15%"}, {"key": "periodicidad", "label": "Periodicidad", "width": "15%"}, {"key": "valor_actual", "label": "Valor Actual", "width": "15%"}]',
 1,
 'primary',
 1)
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    query_base = VALUES(query_base),
    columnas = VALUES(columnas);
SQL;

// ============================================================
// SQL 6: Actualizar mapeo de documentos
// ============================================================
$sqlActualizarMapeo = <<<'SQL'
UPDATE tbl_doc_plantilla_carpeta
SET codigo_carpeta = '3.1.2'
WHERE codigo_plantilla = 'PRG-PPS';
SQL;

// ============================================================
// Función para ejecutar SQL
// ============================================================
function ejecutarSQL($pdo, $sql, $descripcion, $entorno) {
    try {
        $pdo->exec($sql);
        echo "  ✅ [$entorno] $descripcion\n";
        return true;
    } catch (PDOException $e) {
        echo "  ❌ [$entorno] $descripcion\n";
        echo "     Error: " . $e->getMessage() . "\n";
        return false;
    }
}

// ============================================================
// Ejecutar en ambos entornos
// ============================================================
$resultados = ['local' => [], 'produccion' => []];

foreach ($conexiones as $entorno => $config) {
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "EJECUTANDO EN: " . strtoupper($entorno) . "\n";
    echo str_repeat("=", 50) . "\n";

    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ];

        if ($config['ssl']) {
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
            $options[PDO::MYSQL_ATTR_SSL_CA] = false;
        }

        $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        echo "✅ Conexión establecida\n\n";

        // Ejecutar correcciones
        $resultados[$entorno]['flujo'] = ejecutarSQL($pdo, $sqlCambiarFlujo, "Cambiar flujo a programa_con_pta", $entorno);
        $resultados[$entorno]['cronograma'] = ejecutarSQL($pdo, $sqlActualizarCronograma, "Actualizar sección cronograma → tabla_dinamica", $entorno);
        $resultados[$entorno]['indicadores'] = ejecutarSQL($pdo, $sqlActualizarIndicadores, "Actualizar sección indicadores → tabla_dinamica", $entorno);
        $resultados[$entorno]['tabla_pta'] = ejecutarSQL($pdo, $sqlTablaDinamicaPTA, "Crear tabla dinámica PTA PyP", $entorno);
        $resultados[$entorno]['tabla_ind'] = ejecutarSQL($pdo, $sqlTablaDinamicaIndicadores, "Crear tabla dinámica Indicadores PyP", $entorno);
        $resultados[$entorno]['mapeo'] = ejecutarSQL($pdo, $sqlActualizarMapeo, "Verificar mapeo carpeta", $entorno);

        // Verificar resultado
        echo "\n📊 Verificación:\n";

        $stmt = $pdo->query("
            SELECT tipo_documento, flujo, categoria
            FROM tbl_doc_tipo_configuracion
            WHERE tipo_documento = 'programa_promocion_prevencion_salud'
        ");
        $tipo = $stmt->fetch();
        echo "   Flujo: {$tipo['flujo']}\n";

        $stmt = $pdo->query("
            SELECT seccion_key, tipo_contenido, tabla_dinamica_tipo
            FROM tbl_doc_secciones_config sc
            JOIN tbl_doc_tipo_configuracion tc ON sc.id_tipo_config = tc.id_tipo_config
            WHERE tc.tipo_documento = 'programa_promocion_prevencion_salud'
            AND seccion_key IN ('cronograma', 'indicadores')
        ");
        $secciones = $stmt->fetchAll();
        foreach ($secciones as $s) {
            echo "   Sección {$s['seccion_key']}: {$s['tipo_contenido']} → {$s['tabla_dinamica_tipo']}\n";
        }

    } catch (PDOException $e) {
        echo "❌ Error de conexión: " . $e->getMessage() . "\n";
        $resultados[$entorno]['conexion'] = false;
    }
}

// ============================================================
// Resumen
// ============================================================
echo "\n" . str_repeat("=", 50) . "\n";
echo "RESUMEN DE CORRECCIONES\n";
echo str_repeat("=", 50) . "\n";

foreach ($resultados as $entorno => $resultado) {
    $exitosos = count(array_filter($resultado));
    $total = count($resultado);
    $estado = $exitosos === $total ? "✅ COMPLETO" : "⚠️ PARCIAL";
    echo "$estado $entorno: $exitosos/$total operaciones exitosas\n";
}

echo "\n🎉 BD corregida.\n";
echo "Siguiente paso: Agregar fases en FasesDocumentoService.php\n";
