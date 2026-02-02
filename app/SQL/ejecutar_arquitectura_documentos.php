<?php
/**
 * Script para ejecutar la migración de arquitectura de documentos SST
 *
 * Ejecutar desde navegador:
 *   http://localhost/enterprisesst/public/index.php/sql-runner?file=ejecutar_arquitectura_documentos
 *
 * O desde línea de comandos:
 *   cd c:\xampp\htdocs\enterprisesst
 *   php public/index.php sql-runner ejecutar_arquitectura_documentos
 */

// Si se ejecuta directamente, cargar el framework
if (!defined('FCPATH')) {
    require_once dirname(__DIR__, 2) . '/public/index.php';
}

$db = \Config\Database::connect();

echo "<h2>Migración: Arquitectura Escalable de Documentos SST</h2>\n";
echo "<hr>\n";

try {
    // Leer y ejecutar el SQL
    $sqlFile = __DIR__ . '/crear_arquitectura_documentos_sst.sql';

    if (!file_exists($sqlFile)) {
        throw new Exception("Archivo SQL no encontrado: $sqlFile");
    }

    $sql = file_get_contents($sqlFile);

    // Dividir en sentencias individuales (manejo básico)
    $statements = array_filter(
        preg_split('/;\s*[\r\n]+/', $sql),
        fn($s) => !empty(trim($s)) && !str_starts_with(trim($s), '--')
    );

    $ejecutados = 0;
    $errores = [];

    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement) || str_starts_with($statement, '--')) continue;

        try {
            $db->query($statement);
            $ejecutados++;

            // Mostrar progreso para CREATE TABLE
            if (stripos($statement, 'CREATE TABLE') !== false) {
                preg_match('/CREATE TABLE.*?(\w+)/i', $statement, $m);
                echo "✅ Tabla creada: <strong>" . ($m[1] ?? 'desconocida') . "</strong><br>\n";
            } elseif (stripos($statement, 'INSERT INTO') !== false) {
                preg_match('/INSERT INTO\s+(\w+)/i', $statement, $m);
                echo "✅ Datos insertados en: <strong>" . ($m[1] ?? 'desconocida') . "</strong><br>\n";
            }
        } catch (\Exception $e) {
            // Ignorar errores de "ya existe"
            if (stripos($e->getMessage(), 'already exists') === false &&
                stripos($e->getMessage(), 'Duplicate') === false) {
                $errores[] = $e->getMessage();
            }
        }
    }

    echo "<hr>\n";
    echo "<h3>Resumen</h3>\n";
    echo "<p>Sentencias ejecutadas: <strong>$ejecutados</strong></p>\n";

    if (!empty($errores)) {
        echo "<h4>Errores (no críticos):</h4>\n<ul>\n";
        foreach (array_unique($errores) as $error) {
            echo "<li style='color: orange;'>$error</li>\n";
        }
        echo "</ul>\n";
    }

    // Verificar tablas creadas
    echo "<h3>Verificación de tablas</h3>\n";

    $tablas = [
        'tbl_doc_tipo_configuracion' => 'Tipos de documento',
        'tbl_doc_secciones_config' => 'Secciones por tipo',
        'tbl_doc_firmantes_config' => 'Firmantes por tipo',
        'tbl_doc_tablas_dinamicas' => 'Tablas dinámicas'
    ];

    foreach ($tablas as $tabla => $desc) {
        $existe = $db->tableExists($tabla);
        $count = $existe ? $db->table($tabla)->countAllResults() : 0;

        echo $existe
            ? "✅ <strong>$tabla</strong> ($desc): $count registros<br>\n"
            : "❌ <strong>$tabla</strong> ($desc): NO EXISTE<br>\n";
    }

    echo "<hr>\n";
    echo "<h3 style='color: green;'>✅ Migración completada</h3>\n";
    echo "<p>La arquitectura escalable está lista. Ahora puedes:</p>\n";
    echo "<ol>\n";
    echo "<li>Agregar nuevos tipos de documento desde la BD</li>\n";
    echo "<li>Usar <code>DocumentoConfigService</code> en lugar de constantes</li>\n";
    echo "<li>Usar <code>FirmanteService</code> para manejar firmantes</li>\n";
    echo "<li>Usar los componentes de vista reutilizables</li>\n";
    echo "</ol>\n";

} catch (\Exception $e) {
    echo "<h3 style='color: red;'>❌ Error en migración</h3>\n";
    echo "<p>" . $e->getMessage() . "</p>\n";
}
