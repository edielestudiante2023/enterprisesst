<?php
/**
 * limpiar_dashboard_tarjetas.php
 *
 * Limpia duplicados de la migración y estandariza categorías.
 * 1. Elimina IDs 74-86 (duplicados de la segunda migración)
 * 2. Reasigna categorías correctas a IDs 41-73
 * 3. Actualiza URLs de módulos con selector para usar {id_cliente}
 *
 * Uso: php limpiar_dashboard_tarjetas.php [--prod]
 */

$isProd = in_array('--prod', $argv ?? []);

if ($isProd) {
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $user = 'cycloid_userdb';
    $pass = 'AVNS_MR2SLvzRh3i_7o9fEHN';
    $dbname = 'empresas_sst';
    $port = 25060;
    $label = 'PRODUCCIÓN';
} else {
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $dbname = 'empresas_sst';
    $port = 3306;
    $label = 'LOCAL';
}

echo "=== Limpieza Dashboard Tarjetas — $label ===\n\n";

try {
    $db = new mysqli($host, $user, $pass, $dbname, $port);
    if ($isProd) {
        mysqli_ssl_set($db, NULL, NULL, NULL, NULL, NULL);
    }
    if ($db->connect_error) {
        throw new Exception("Error de conexión: " . $db->connect_error);
    }
    $db->set_charset('utf8mb4');
    echo "✓ Conectado a $label\n\n";

    // ─────────────────────────────────────────────
    // PASO 1: Eliminar duplicados (IDs 74-86)
    // ─────────────────────────────────────────────
    echo "--- PASO 1: Eliminar duplicados IDs >= 74 ---\n";
    $db->query("DELETE FROM dashboard_items WHERE id >= 74");
    echo "  ✓ " . $db->affected_rows . " registros eliminados\n\n";

    // ─────────────────────────────────────────────
    // PASO 2: Reasignar categorías a IDs 41-73
    // ─────────────────────────────────────────────
    echo "--- PASO 2: Reasignar categorías a IDs 41-73 ---\n";

    $updates = [
        // Herramientas IA
        41 => ['categoria' => 'Herramientas IA',              'icono' => 'fas fa-robot',                'color' => '#3a7bd5,#00d2ff',  'orden' => 1, 'target_blank' => 0],
        42 => ['categoria' => 'Herramientas IA',              'icono' => 'fas fa-terminal',             'color' => '#3a7bd5,#00d2ff',  'orden' => 4, 'target_blank' => 1],
        65 => ['categoria' => 'Herramientas IA',              'icono' => 'fas fa-book-open',            'color' => '#f093fb,#f5576c',  'orden' => 2, 'target_blank' => 1],
        66 => ['categoria' => 'Herramientas IA',              'icono' => 'fas fa-edit',                 'color' => '#e44d26,#f16529',  'orden' => 3, 'target_blank' => 1],

        // Dashboards y Reportes
        57 => ['categoria' => 'Dashboards y Reportes',        'icono' => 'fas fa-chart-pie',            'color' => '#667eea,#764ba2',  'orden' => 1, 'target_blank' => 1],
        58 => ['categoria' => 'Dashboards y Reportes',        'icono' => 'fas fa-graduation-cap',       'color' => '#f093fb,#f5576c',  'orden' => 2, 'target_blank' => 1],
        59 => ['categoria' => 'Dashboards y Reportes',        'icono' => 'fas fa-tasks',                'color' => '#4facfe,#00f2fe',  'orden' => 3, 'target_blank' => 1],
        60 => ['categoria' => 'Dashboards y Reportes',        'icono' => 'fas fa-clipboard-list',       'color' => '#fa709a,#fee140',  'orden' => 4, 'target_blank' => 1],
        61 => ['categoria' => 'Dashboards y Reportes',        'icono' => 'fas fa-chart-line',           'color' => '#00b894,#00cec9',  'orden' => 5, 'target_blank' => 1],
        62 => ['categoria' => 'Dashboards y Reportes',        'icono' => 'fas fa-file-alt',             'color' => '#0984e3,#6c5ce7',  'orden' => 6, 'target_blank' => 1],
        56 => ['categoria' => 'Dashboards y Reportes',        'icono' => 'fas fa-bolt',                 'color' => '#bd9751,#d4af37',  'orden' => 7, 'target_blank' => 1],

        // Operación por Cliente (con selector — URLs con {id_cliente})
        67 => ['categoria' => 'Operación por Cliente',        'icono' => 'fas fa-clipboard-list',       'color' => '#00b894,#00cec9',  'orden' => 1, 'target_blank' => 0,
               'accion_url' => '/actas/{id_cliente}',         'detalle' => 'Actas de Reunión',          'descripcion' => 'Gestión de actas por cliente (requiere selector)'],
        52 => ['categoria' => 'Operación por Cliente',        'icono' => 'fas fa-vote-yea',             'color' => '#6c5ce7,#a29bfe',  'orden' => 2, 'target_blank' => 0,
               'accion_url' => '/comites-elecciones/{id_cliente}', 'detalle' => 'Conformación de Comités', 'descripcion' => 'COPASST, COCOLAB, Brigada, Vigía (requiere selector)'],
        53 => ['categoria' => 'Operación por Cliente',        'icono' => 'fas fa-exclamation-triangle', 'color' => '#e74c3c,#c0392b',  'orden' => 3, 'target_blank' => 0,
               'accion_url' => '/acciones-correctivas/{id_cliente}', 'detalle' => 'Acciones Correctivas', 'descripcion' => 'Numerales 7.1.1, 7.1.2, 7.1.3, 7.1.4 (requiere selector)'],
        // ID 53 was Indicadores, need a new record for that... Wait, 53 was "Indicadores SST" in the previous migration
        // Let me re-check: 52=Acciones Correctivas, 53=Indicadores SST
        // I'll reassign 52 to Comités and 53 to Indicadores

        63 => ['categoria' => 'Operación por Cliente',        'icono' => 'fas fa-pen-nib',              'color' => '#1e3a5f,#2d5a87',  'orden' => 6, 'target_blank' => 0,
               'accion_url' => '/firma/dashboard/{id_cliente}', 'detalle' => 'Firmas del Cliente',      'descripcion' => 'Solicitudes de firma, estado y seguimiento'],
        64 => ['categoria' => 'Operación por Cliente',        'icono' => 'fas fa-file-alt',             'color' => '#667eea,#764ba2',  'orden' => 5, 'target_blank' => 0,
               'accion_url' => '/documentos-sst/{id_cliente}/lista', 'detalle' => 'Documentos SST por Cliente', 'descripcion' => '36 documentos del SG-SST: Políticas, Programas, Procedimientos'],
        51 => ['categoria' => 'Operación por Cliente',        'icono' => 'fas fa-hard-hat',             'color' => '#1c2437,#bd9751',  'orden' => 7, 'target_blank' => 0],

        // Cumplimiento SST - Res. 0312
        68 => ['categoria' => 'Cumplimiento SST - Res. 0312', 'icono' => 'fas fa-cog',                 'color' => '#667eea,#764ba2',  'orden' => 1, 'target_blank' => 1,
               'detalle' => 'Contexto Cliente'],
        69 => ['categoria' => 'Cumplimiento SST - Res. 0312', 'icono' => 'fas fa-sync-alt',            'color' => '#f2994a,#f2c94c',  'orden' => 2, 'target_blank' => 1,
               'detalle' => 'Cumplimiento PHVA'],
        70 => ['categoria' => 'Cumplimiento SST - Res. 0312', 'icono' => 'fas fa-book',                'color' => '#11998e,#38ef7d',  'orden' => 3, 'target_blank' => 1,
               'detalle' => 'Catálogo 60 Estándares'],
        54 => ['categoria' => 'Cumplimiento SST - Res. 0312', 'icono' => 'fas fa-balance-scale',       'color' => '#e74c3c,#c0392b',  'orden' => 4, 'target_blank' => 1],
        44 => ['categoria' => 'Cumplimiento SST - Res. 0312', 'icono' => 'fas fa-clipboard-check',     'color' => '#6a11cb,#2575fc',  'orden' => 5, 'target_blank' => 1,
               'detalle' => 'Evaluación Estándares Mínimos'],

        // Capacitación y Planificación
        43 => ['categoria' => 'Capacitación y Planificación', 'icono' => 'fas fa-calendar-alt',        'color' => '#00b894,#00cec9',  'orden' => 1, 'target_blank' => 1],
        45 => ['categoria' => 'Capacitación y Planificación', 'icono' => 'fas fa-clipboard-list',      'color' => '#00b894,#00cec9',  'orden' => 2, 'target_blank' => 1],
        46 => ['categoria' => 'Capacitación y Planificación', 'icono' => 'fas fa-tasks',               'color' => '#00b894,#00cec9',  'orden' => 3, 'target_blank' => 1],
        47 => ['categoria' => 'Capacitación y Planificación', 'icono' => 'fas fa-wrench',              'color' => '#00b894,#00cec9',  'orden' => 4, 'target_blank' => 1],
        48 => ['categoria' => 'Capacitación y Planificación', 'icono' => 'fas fa-user-shield',         'color' => '#00b894,#00cec9',  'orden' => 5, 'target_blank' => 1],
        55 => ['categoria' => 'Capacitación y Planificación', 'icono' => 'fas fa-money-bill-wave',     'color' => '#00b894,#00cec9',  'orden' => 6, 'target_blank' => 1],

        // Administración del Sistema
        49 => ['categoria' => 'Administración del Sistema',   'icono' => 'fas fa-user-plus',           'color' => '#2c3e50,#3498db',  'orden' => 1, 'target_blank' => 1],
        50 => ['categoria' => 'Administración del Sistema',   'icono' => 'fas fa-eye',                 'color' => '#2c3e50,#3498db',  'orden' => 2, 'target_blank' => 1],
        71 => ['categoria' => 'Administración del Sistema',   'icono' => 'fas fa-users-cog',           'color' => '#2c3e50,#3498db',  'orden' => 3, 'target_blank' => 1],
        72 => ['categoria' => 'Administración del Sistema',   'icono' => 'fas fa-chart-bar',           'color' => '#2c3e50,#3498db',  'orden' => 4, 'target_blank' => 1],
        73 => ['categoria' => 'Administración del Sistema',   'icono' => 'fas fa-redo',                'color' => '#2c3e50,#3498db',  'orden' => 5, 'target_blank' => 0],
    ];

    $totalUpdated = 0;
    foreach ($updates as $id => $fields) {
        $sets = [];
        // Map short keys to real column names
        $colMap = ['color' => 'color_gradiente'];
        foreach ($fields as $col => $val) {
            $realCol = $colMap[$col] ?? $col;
            if (is_int($val)) {
                $sets[] = "$realCol = $val";
            } else {
                $sets[] = "$realCol = '" . $db->real_escape_string($val) . "'";
            }
        }
        $sql = "UPDATE dashboard_items SET " . implode(', ', $sets) . " WHERE id = $id";
        if (!$db->query($sql)) {
            echo "  ✗ Error ID $id: " . $db->error . "\n";
        } else {
            $totalUpdated += $db->affected_rows;
        }
    }
    echo "  ✓ $totalUpdated registros actualizados\n\n";

    // ─────────────────────────────────────────────
    // PASO 3: Fix ID 53 — era Indicadores SST, lo reasigno correctamente
    // En paso 2 lo puse como Comités pero 52=Acciones Correctivas, 53=Indicadores
    // ─────────────────────────────────────────────
    echo "--- PASO 3: Corregir ID 52 y 53 ---\n";

    // ID 52 queda como Acciones Correctivas → Operación por Cliente
    $db->query("UPDATE dashboard_items SET
        categoria = 'Operación por Cliente',
        icono = 'fas fa-exclamation-triangle',
        color_gradiente = '#e74c3c,#c0392b',
        orden = 3,
        target_blank = 0,
        accion_url = '/acciones-correctivas/{id_cliente}',
        detalle = 'Acciones Correctivas',
        descripcion = 'Numerales 7.1.1, 7.1.2, 7.1.3, 7.1.4 (requiere selector)'
        WHERE id = 52");
    echo "  ✓ ID 52 → Acciones Correctivas (Operación por Cliente)\n";

    // ID 53 queda como Indicadores SST → Operación por Cliente
    $db->query("UPDATE dashboard_items SET
        categoria = 'Operación por Cliente',
        icono = 'fas fa-chart-line',
        color_gradiente = '#f39c12,#e67e22',
        orden = 4,
        target_blank = 0,
        accion_url = '/indicadores-sst/{id_cliente}/dashboard',
        detalle = 'Indicadores SST',
        descripcion = 'Dashboard de indicadores: Estructura, Proceso y Resultado (requiere selector)'
        WHERE id = 53");
    echo "  ✓ ID 53 → Indicadores SST (Operación por Cliente)\n";

    // ID 67 → Actas (ya se hizo en paso 2, pero verifico que tenga Comités separado)
    // Necesitamos insertar Comités como nuevo registro si no existe
    $check = $db->query("SELECT id FROM dashboard_items WHERE accion_url = '/comites-elecciones/{id_cliente}' LIMIT 1");
    if ($check->num_rows === 0) {
        $db->query("INSERT INTO dashboard_items (rol, tipo_proceso, detalle, descripcion, accion_url, categoria, icono, color_gradiente, orden, target_blank, activo, creado_en)
            VALUES ('Consultor', 'Operación por Cliente', 'Conformación de Comités', 'COPASST, COCOLAB, Brigada, Vigía (requiere selector)', '/comites-elecciones/{id_cliente}', 'Operación por Cliente', 'fas fa-vote-yea', '#6c5ce7,#a29bfe', 2, 0, 1, NOW())");
        echo "  ✓ Insertado: Conformación de Comités\n";
    } else {
        echo "  ✓ Conformación de Comités ya existe\n";
    }

    // Insertar Instructivo SST y Documentación si faltan en categoría Cumplimiento
    $faltantes = [
        ['detalle' => 'Instructivo SST',  'url' => 'documentacion/instructivo', 'icono' => 'fas fa-book-reader', 'color' => '#ff6b6b,#feca57', 'orden' => 6],
        ['detalle' => 'Documentación',    'url' => 'documentacion',            'icono' => 'fas fa-file-alt',    'color' => '#6a11cb,#2575fc', 'orden' => 7],
    ];
    foreach ($faltantes as $f) {
        $urlEsc = $db->real_escape_string($f['url']);
        $check = $db->query("SELECT id FROM dashboard_items WHERE accion_url = '$urlEsc' AND categoria = 'Cumplimiento SST - Res. 0312' LIMIT 1");
        if ($check->num_rows === 0) {
            // Check if exists with different category
            $existing = $db->query("SELECT id FROM dashboard_items WHERE accion_url = '$urlEsc' LIMIT 1");
            if ($existing->num_rows > 0) {
                $row = $existing->fetch_assoc();
                $db->query("UPDATE dashboard_items SET categoria = 'Cumplimiento SST - Res. 0312', icono = '" . $db->real_escape_string($f['icono']) . "', color_gradiente = '" . $db->real_escape_string($f['color']) . "', orden = " . $f['orden'] . " WHERE id = " . $row['id']);
                echo "  ✓ Reasignado '{$f['detalle']}' (ID {$row['id']}) → Cumplimiento SST\n";
            } else {
                $det = $db->real_escape_string($f['detalle']);
                $db->query("INSERT INTO dashboard_items (rol, tipo_proceso, detalle, descripcion, accion_url, categoria, icono, color_gradiente, orden, target_blank, activo, creado_en)
                    VALUES ('Consultor', 'Cumplimiento SST', '$det', '$det', '$urlEsc', 'Cumplimiento SST - Res. 0312', '" . $db->real_escape_string($f['icono']) . "', '" . $db->real_escape_string($f['color']) . "', " . $f['orden'] . ", 1, 1, NOW())");
                echo "  ✓ Insertado: {$f['detalle']}\n";
            }
        }
    }
    echo "\n";

    // ─────────────────────────────────────────────
    // PASO 4: Resumen final
    // ─────────────────────────────────────────────
    echo "--- RESUMEN FINAL ---\n";
    $r = $db->query("SELECT categoria, COUNT(*) as total, SUM(activo) as activos FROM dashboard_items GROUP BY categoria ORDER BY categoria");
    echo str_pad('Categoría', 40) . str_pad('Total', 8) . "Activos\n";
    echo str_repeat('-', 56) . "\n";
    $grandTotal = 0;
    $grandActive = 0;
    while ($row = $r->fetch_assoc()) {
        $cat = $row['categoria'] ?: '(sin categoría)';
        echo str_pad($cat, 40) . str_pad($row['total'], 8) . $row['activos'] . "\n";
        $grandTotal += $row['total'];
        $grandActive += $row['activos'];
    }
    echo "\nTotal: $grandTotal registros ($grandActive activos)\n";

    $db->close();
    echo "\n=== Limpieza completada en $label ===\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
