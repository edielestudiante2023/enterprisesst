<?php
/**
 * migrar_dashboard_tarjetas.php
 *
 * Migra la tabla dashboard_items para el rediseño de tarjetas agrupadas:
 * 1. Asigna categoría, icono y color a los 40 registros existentes
 * 2. Marca inactivos los IDs 14-22 (indicadores config)
 * 3. Inserta los accesos hardcodeados que faltan (verificando por accion_url)
 *
 * Uso: php migrar_dashboard_tarjetas.php [--prod]
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

echo "=== Migración Dashboard Tarjetas — $label ===\n\n";

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
    // PASO 1: Asignar categoría, icono y color a registros existentes
    // ─────────────────────────────────────────────
    echo "--- PASO 1: Asignar categorías a registros existentes ---\n";

    $actualizaciones = [
        // Gestión Documental (IDs 1,2,3,13,12,11)
        ['ids' => [1,2,3],    'categoria' => 'Gestión Documental',         'icono' => 'fas fa-folder-open',        'color' => '#e44d26,#f16529',  'orden_base' => 1],
        ['ids' => [13],       'categoria' => 'Gestión Documental',         'icono' => 'fas fa-file-contract',      'color' => '#e44d26,#f16529',  'orden_base' => 4],
        ['ids' => [12],       'categoria' => 'Gestión Documental',         'icono' => 'fas fa-pen-fancy',          'color' => '#e44d26,#f16529',  'orden_base' => 5],
        ['ids' => [11],       'categoria' => 'Gestión Documental',         'icono' => 'fas fa-code-branch',        'color' => '#e44d26,#f16529',  'orden_base' => 6],

        // Capacitación y Planificación (IDs 4,5,24,25,23)
        ['ids' => [4],        'categoria' => 'Capacitación y Planificación', 'icono' => 'fas fa-chalkboard-teacher', 'color' => '#00b894,#00cec9', 'orden_base' => 1],
        ['ids' => [5],        'categoria' => 'Capacitación y Planificación', 'icono' => 'fas fa-calendar-alt',      'color' => '#00b894,#00cec9', 'orden_base' => 2],
        ['ids' => [24],       'categoria' => 'Capacitación y Planificación', 'icono' => 'fas fa-clipboard-list',    'color' => '#00b894,#00cec9', 'orden_base' => 3],
        ['ids' => [25],       'categoria' => 'Capacitación y Planificación', 'icono' => 'fas fa-tasks',             'color' => '#00b894,#00cec9', 'orden_base' => 4],
        ['ids' => [23],       'categoria' => 'Capacitación y Planificación', 'icono' => 'fas fa-user-shield',       'color' => '#00b894,#00cec9', 'orden_base' => 5],

        // Cumplimiento SST (ID 6)
        ['ids' => [6],        'categoria' => 'Cumplimiento SST - Res. 0312', 'icono' => 'fas fa-clipboard-check',  'color' => '#6a11cb,#2575fc', 'orden_base' => 7],

        // Gestión de Usuarios → Administración del Sistema (IDs 7,8)
        ['ids' => [7],        'categoria' => 'Administración del Sistema',  'icono' => 'fas fa-building',           'color' => '#2c3e50,#3498db', 'orden_base' => 2],
        ['ids' => [8],        'categoria' => 'Administración del Sistema',  'icono' => 'fas fa-user-tie',           'color' => '#2c3e50,#3498db', 'orden_base' => 3],

        // Plataformas Colaborativas (IDs 9,26,27)
        ['ids' => [9],        'categoria' => 'Plataformas Colaborativas',   'icono' => 'fas fa-project-diagram',    'color' => '#0984e3,#74b9ff', 'orden_base' => 1],
        ['ids' => [26],       'categoria' => 'Plataformas Colaborativas',   'icono' => 'fas fa-th',                 'color' => '#0984e3,#74b9ff', 'orden_base' => 2],
        ['ids' => [27],       'categoria' => 'Plataformas Colaborativas',   'icono' => 'fas fa-clock',              'color' => '#0984e3,#74b9ff', 'orden_base' => 3],

        // Dashboards y Reportes (ID 10)
        ['ids' => [10],       'categoria' => 'Dashboards y Reportes',       'icono' => 'fas fa-chart-bar',          'color' => '#667eea,#764ba2', 'orden_base' => 7],

        // Indicadores Config → INACTIVOS (IDs 14-22)
        ['ids' => [14],       'categoria' => 'Indicadores (Config)',        'icono' => 'fas fa-chart-line',         'color' => '#636e72,#b2bec3', 'orden_base' => 1],
        ['ids' => [15],       'categoria' => 'Indicadores (Config)',        'icono' => 'fas fa-tags',               'color' => '#636e72,#b2bec3', 'orden_base' => 2],
        ['ids' => [16],       'categoria' => 'Indicadores (Config)',        'icono' => 'fas fa-book',               'color' => '#636e72,#b2bec3', 'orden_base' => 3],
        ['ids' => [17],       'categoria' => 'Indicadores (Config)',        'icono' => 'fas fa-signature',          'color' => '#636e72,#b2bec3', 'orden_base' => 4],
        ['ids' => [18],       'categoria' => 'Indicadores (Config)',        'icono' => 'fas fa-user-tag',           'color' => '#636e72,#b2bec3', 'orden_base' => 5],
        ['ids' => [19],       'categoria' => 'Indicadores (Config)',        'icono' => 'fas fa-superscript',        'color' => '#636e72,#b2bec3', 'orden_base' => 6],
        ['ids' => [20],       'categoria' => 'Indicadores (Config)',        'icono' => 'fas fa-subscript',          'color' => '#636e72,#b2bec3', 'orden_base' => 7],
        ['ids' => [21],       'categoria' => 'Indicadores (Config)',        'icono' => 'fas fa-bullseye',           'color' => '#636e72,#b2bec3', 'orden_base' => 8],
        ['ids' => [22],       'categoria' => 'Indicadores (Config)',        'icono' => 'fas fa-file-alt',           'color' => '#636e72,#b2bec3', 'orden_base' => 9],

        // Carga Masiva CSV (IDs 28-34, 39)
        ['ids' => [28],       'categoria' => 'Carga Masiva CSV',            'icono' => 'fas fa-file-csv',           'color' => '#fdcb6e,#e17055', 'orden_base' => 1],
        ['ids' => [29],       'categoria' => 'Carga Masiva CSV',            'icono' => 'fas fa-file-csv',           'color' => '#fdcb6e,#e17055', 'orden_base' => 2],
        ['ids' => [30],       'categoria' => 'Carga Masiva CSV',            'icono' => 'fas fa-file-csv',           'color' => '#fdcb6e,#e17055', 'orden_base' => 3],
        ['ids' => [31],       'categoria' => 'Carga Masiva CSV',            'icono' => 'fas fa-file-csv',           'color' => '#fdcb6e,#e17055', 'orden_base' => 4],
        ['ids' => [32],       'categoria' => 'Carga Masiva CSV',            'icono' => 'fas fa-file-csv',           'color' => '#fdcb6e,#e17055', 'orden_base' => 5],
        ['ids' => [33],       'categoria' => 'Carga Masiva CSV',            'icono' => 'fas fa-file-csv',           'color' => '#fdcb6e,#e17055', 'orden_base' => 6],
        ['ids' => [34],       'categoria' => 'Carga Masiva CSV',            'icono' => 'fas fa-file-csv',           'color' => '#fdcb6e,#e17055', 'orden_base' => 7],
        ['ids' => [39],       'categoria' => 'Carga Masiva CSV',            'icono' => 'fas fa-file-csv',           'color' => '#fdcb6e,#e17055', 'orden_base' => 8],

        // Administración del Sistema (IDs 35,36,37,38,40)
        ['ids' => [35],       'categoria' => 'Administración del Sistema',  'icono' => 'fas fa-th-list',            'color' => '#2c3e50,#3498db', 'orden_base' => 7],
        ['ids' => [36],       'categoria' => 'Administración del Sistema',  'icono' => 'fas fa-key',                'color' => '#2c3e50,#3498db', 'orden_base' => 8],
        ['ids' => [37],       'categoria' => 'Administración del Sistema',  'icono' => 'fas fa-shield-alt',         'color' => '#2c3e50,#3498db', 'orden_base' => 9],
        ['ids' => [38],       'categoria' => 'Administración del Sistema',  'icono' => 'fas fa-list-alt',           'color' => '#2c3e50,#3498db', 'orden_base' => 10],
        ['ids' => [40],       'categoria' => 'Administración del Sistema',  'icono' => 'fas fa-cogs',               'color' => '#2c3e50,#3498db', 'orden_base' => 11],
    ];

    $totalUpdated = 0;
    foreach ($actualizaciones as $upd) {
        $ids = implode(',', $upd['ids']);
        $cat = $db->real_escape_string($upd['categoria']);
        $ico = $db->real_escape_string($upd['icono']);
        $col = $db->real_escape_string($upd['color']);
        $ord = (int)$upd['orden_base'];

        $sql = "UPDATE dashboard_items SET categoria='$cat', icono='$ico', color_gradiente='$col', orden=$ord WHERE id IN ($ids)";
        if (!$db->query($sql)) {
            echo "  ✗ Error actualizando IDs $ids: " . $db->error . "\n";
        } else {
            $totalUpdated += $db->affected_rows;
        }
    }
    echo "  ✓ $totalUpdated registros actualizados con categoría/icono/color\n\n";

    // ─────────────────────────────────────────────
    // PASO 2: Marcar inactivos IDs 14-22
    // ─────────────────────────────────────────────
    echo "--- PASO 2: Marcar inactivos IDs 14-22 (Indicadores Config) ---\n";

    $sql = "UPDATE dashboard_items SET activo = 0 WHERE id BETWEEN 14 AND 22";
    if (!$db->query($sql)) {
        echo "  ✗ Error: " . $db->error . "\n";
    } else {
        echo "  ✓ " . $db->affected_rows . " registros marcados como inactivos\n\n";
    }

    // ─────────────────────────────────────────────
    // PASO 3: Insertar accesos hardcodeados faltantes
    // ─────────────────────────────────────────────
    echo "--- PASO 3: Insertar accesos hardcodeados ---\n";

    $nuevos = [
        // Operación por Cliente (requieren selector)
        ['rol' => 'Consultor', 'tipo_proceso' => 'Operación por Cliente', 'detalle' => 'Actas de Reunión',          'descripcion' => 'Gestión de actas por cliente (requiere selector)',                   'accion_url' => '/actas/{id_cliente}',                       'categoria' => 'Operación por Cliente', 'icono' => 'fas fa-clipboard-list',    'color' => '#00b894,#00cec9',  'orden' => 1, 'target_blank' => 0],
        ['rol' => 'Consultor', 'tipo_proceso' => 'Operación por Cliente', 'detalle' => 'Conformación de Comités',   'descripcion' => 'COPASST, COCOLAB, Brigada, Vigía (requiere selector)',                'accion_url' => '/comites-elecciones/{id_cliente}',          'categoria' => 'Operación por Cliente', 'icono' => 'fas fa-vote-yea',          'color' => '#6c5ce7,#a29bfe',  'orden' => 2, 'target_blank' => 0],
        ['rol' => 'Consultor', 'tipo_proceso' => 'Operación por Cliente', 'detalle' => 'Acciones Correctivas',      'descripcion' => 'Numerales 7.1.1, 7.1.2, 7.1.3, 7.1.4 (requiere selector)',          'accion_url' => '/acciones-correctivas/{id_cliente}',        'categoria' => 'Operación por Cliente', 'icono' => 'fas fa-exclamation-triangle', 'color' => '#e74c3c,#c0392b', 'orden' => 3, 'target_blank' => 0],
        ['rol' => 'Consultor', 'tipo_proceso' => 'Operación por Cliente', 'detalle' => 'Indicadores SST',           'descripcion' => 'Dashboard de indicadores: Estructura, Proceso y Resultado',          'accion_url' => '/indicadores-sst/{id_cliente}/dashboard',   'categoria' => 'Operación por Cliente', 'icono' => 'fas fa-chart-line',        'color' => '#f39c12,#e67e22',  'orden' => 4, 'target_blank' => 0],
        ['rol' => 'Consultor', 'tipo_proceso' => 'Operación por Cliente', 'detalle' => 'Documentos SST por Cliente','descripcion' => '36 documentos del SG-SST: Políticas, Programas, Procedimientos',     'accion_url' => '/documentos-sst/{id_cliente}/lista',        'categoria' => 'Operación por Cliente', 'icono' => 'fas fa-file-alt',          'color' => '#667eea,#764ba2',  'orden' => 5, 'target_blank' => 0],
        ['rol' => 'Consultor', 'tipo_proceso' => 'Operación por Cliente', 'detalle' => 'Firmas del Cliente',        'descripcion' => 'Solicitudes de firma, estado y seguimiento',                         'accion_url' => '/firma/dashboard/{id_cliente}',             'categoria' => 'Operación por Cliente', 'icono' => 'fas fa-pen-nib',           'color' => '#1e3a5f,#2d5a87',  'orden' => 6, 'target_blank' => 0],
        ['rol' => 'Consultor', 'tipo_proceso' => 'Operación por Cliente', 'detalle' => 'Inspecciones SST',          'descripcion' => 'Módulo PWA de Inspecciones SST',                                     'accion_url' => '/inspecciones',                             'categoria' => 'Operación por Cliente', 'icono' => 'fas fa-hard-hat',          'color' => '#1c2437,#bd9751',  'orden' => 7, 'target_blank' => 0],

        // Dashboards y Reportes
        ['rol' => 'Consultor', 'tipo_proceso' => 'Dashboards',            'detalle' => 'Dashboard Estándares Mínimos',  'descripcion' => 'Panel analítico de estándares mínimos por cliente',              'accion_url' => 'consultant/dashboard-estandares',           'categoria' => 'Dashboards y Reportes', 'icono' => 'fas fa-chart-pie',         'color' => '#667eea,#764ba2',  'orden' => 1, 'target_blank' => 1],
        ['rol' => 'Consultor', 'tipo_proceso' => 'Dashboards',            'detalle' => 'Dashboard Capacitaciones',      'descripcion' => 'Panel analítico de capacitaciones por cliente',                  'accion_url' => 'consultant/dashboard-capacitaciones',       'categoria' => 'Dashboards y Reportes', 'icono' => 'fas fa-graduation-cap',    'color' => '#f093fb,#f5576c',  'orden' => 2, 'target_blank' => 1],
        ['rol' => 'Consultor', 'tipo_proceso' => 'Dashboards',            'detalle' => 'Dashboard Plan de Trabajo',     'descripcion' => 'Panel analítico del plan de trabajo por cliente',                'accion_url' => 'consultant/dashboard-plan-trabajo',         'categoria' => 'Dashboards y Reportes', 'icono' => 'fas fa-tasks',             'color' => '#4facfe,#00f2fe',  'orden' => 3, 'target_blank' => 1],
        ['rol' => 'Consultor', 'tipo_proceso' => 'Dashboards',            'detalle' => 'Dashboard Pendientes',          'descripcion' => 'Panel analítico de pendientes por cliente',                     'accion_url' => 'consultant/dashboard-pendientes',           'categoria' => 'Dashboards y Reportes', 'icono' => 'fas fa-clipboard-list',    'color' => '#fa709a,#fee140',  'orden' => 4, 'target_blank' => 1],
        ['rol' => 'Consultor', 'tipo_proceso' => 'Dashboards',            'detalle' => 'Monitor Documentos SST',        'descripcion' => 'Monitor de documentos SST de todos los clientes',               'accion_url' => 'admin/dashboard-documentos-sst',            'categoria' => 'Dashboards y Reportes', 'icono' => 'fas fa-file-alt',          'color' => '#0984e3,#6c5ce7',  'orden' => 5, 'target_blank' => 1],
        ['rol' => 'Consultor', 'tipo_proceso' => 'Dashboards',            'detalle' => 'Informe de Avances',            'descripcion' => 'Informe de avances consolidado',                                'accion_url' => '/informe-avances',                          'categoria' => 'Dashboards y Reportes', 'icono' => 'fas fa-chart-line',        'color' => '#00b894,#00cec9',  'orden' => 6, 'target_blank' => 1],
        ['rol' => 'Consultor', 'tipo_proceso' => 'Dashboards',            'detalle' => 'Acceso Rápido',                 'descripcion' => 'Acceso rápido a funcionalidades frecuentes',                    'accion_url' => '/quick-access',                             'categoria' => 'Dashboards y Reportes', 'icono' => 'fas fa-bolt',              'color' => '#bd9751,#d4af37',  'orden' => 8, 'target_blank' => 1],

        // Herramientas IA
        ['rol' => 'Consultor', 'tipo_proceso' => 'Herramientas IA',       'detalle' => 'Otto Asistente',                'descripcion' => 'Asistente de inteligencia artificial',                          'accion_url' => '/agente-chat',                              'categoria' => 'Herramientas IA',       'icono' => 'fas fa-robot',             'color' => '#3a7bd5,#00d2ff',  'orden' => 1, 'target_blank' => 0],
        ['rol' => 'Consultor', 'tipo_proceso' => 'Herramientas IA',       'detalle' => 'Editor Secciones',              'descripcion' => 'Editor de secciones de documentos IA',                          'accion_url' => '/admin/editor-secciones',                   'categoria' => 'Herramientas IA',       'icono' => 'fas fa-edit',              'color' => '#e44d26,#f16529',  'orden' => 2, 'target_blank' => 1],
        ['rol' => 'Consultor', 'tipo_proceso' => 'Herramientas IA',       'detalle' => 'Marcos Normativos IA',          'descripcion' => 'Dashboard de marcos normativos generados por IA',               'accion_url' => '/documentos/marco-normativo-dashboard',     'categoria' => 'Herramientas IA',       'icono' => 'fas fa-book-open',         'color' => '#f093fb,#f5576c',  'orden' => 3, 'target_blank' => 1],

        // Cumplimiento SST - Res. 0312
        ['rol' => 'Consultor', 'tipo_proceso' => 'Cumplimiento SST',      'detalle' => 'Instructivo SST',               'descripcion' => 'Instructivo del Sistema de Gestión SST',                        'accion_url' => 'documentacion/instructivo',                 'categoria' => 'Cumplimiento SST - Res. 0312', 'icono' => 'fas fa-book-reader',  'color' => '#ff6b6b,#feca57',  'orden' => 1, 'target_blank' => 1],
        ['rol' => 'Consultor', 'tipo_proceso' => 'Cumplimiento SST',      'detalle' => 'Contexto Cliente',              'descripcion' => 'Configuración de contexto por cliente',                         'accion_url' => 'contexto',                                  'categoria' => 'Cumplimiento SST - Res. 0312', 'icono' => 'fas fa-cog',          'color' => '#667eea,#764ba2',  'orden' => 2, 'target_blank' => 1],
        ['rol' => 'Consultor', 'tipo_proceso' => 'Cumplimiento SST',      'detalle' => 'Cumplimiento PHVA',             'descripcion' => 'Evaluación del ciclo PHVA por estándares',                     'accion_url' => 'estandares',                                'categoria' => 'Cumplimiento SST - Res. 0312', 'icono' => 'fas fa-sync-alt',     'color' => '#f2994a,#f2c94c',  'orden' => 3, 'target_blank' => 1],
        ['rol' => 'Consultor', 'tipo_proceso' => 'Cumplimiento SST',      'detalle' => 'Documentación',                 'descripcion' => 'Gestión de documentación del SG-SST',                          'accion_url' => 'documentacion',                             'categoria' => 'Cumplimiento SST - Res. 0312', 'icono' => 'fas fa-file-alt',     'color' => '#6a11cb,#2575fc',  'orden' => 4, 'target_blank' => 1],
        ['rol' => 'Consultor', 'tipo_proceso' => 'Cumplimiento SST',      'detalle' => 'Catálogo 60 Estándares',        'descripcion' => 'Catálogo completo de los 60 estándares mínimos',              'accion_url' => 'estandares/catalogo',                       'categoria' => 'Cumplimiento SST - Res. 0312', 'icono' => 'fas fa-book',         'color' => '#11998e,#38ef7d',  'orden' => 5, 'target_blank' => 1],
        ['rol' => 'Consultor', 'tipo_proceso' => 'Cumplimiento SST',      'detalle' => 'Matriz Legal',                  'descripcion' => 'Matriz de requisitos legales aplicables',                       'accion_url' => 'matriz-legal',                              'categoria' => 'Cumplimiento SST - Res. 0312', 'icono' => 'fas fa-balance-scale', 'color' => '#e74c3c,#c0392b', 'orden' => 6, 'target_blank' => 1],

        // Administración del Sistema (hardcodeados)
        ['rol' => 'Administrador', 'tipo_proceso' => 'Administración',    'detalle' => 'Gestión de Usuarios',           'descripcion' => 'Administración de usuarios de la plataforma',                  'accion_url' => '/admin/users',                              'categoria' => 'Administración del Sistema', 'icono' => 'fas fa-users-cog',   'color' => '#2c3e50,#3498db',  'orden' => 1, 'target_blank' => 1],
        ['rol' => 'Consultor', 'tipo_proceso' => 'Administración',        'detalle' => 'Consumo Plataforma',            'descripcion' => 'Estadísticas de uso y consumo de la plataforma',               'accion_url' => '/admin/usage',                              'categoria' => 'Administración del Sistema', 'icono' => 'fas fa-chart-line',  'color' => '#2c3e50,#3498db',  'orden' => 4, 'target_blank' => 1],
        ['rol' => 'Consultor', 'tipo_proceso' => 'Administración',        'detalle' => 'Vista del Cliente',             'descripcion' => 'Ver la plataforma como la ve el cliente',                       'accion_url' => '/consultor/selector-cliente',               'categoria' => 'Administración del Sistema', 'icono' => 'fas fa-eye',         'color' => '#2c3e50,#3498db',  'orden' => 5, 'target_blank' => 1],
    ];

    $inserted = 0;
    $skipped = 0;
    foreach ($nuevos as $item) {
        // Verificar duplicado por accion_url
        $urlEsc = $db->real_escape_string($item['accion_url']);
        $check = $db->query("SELECT id FROM dashboard_items WHERE accion_url = '$urlEsc' LIMIT 1");
        if ($check && $check->num_rows > 0) {
            $skipped++;
            continue;
        }

        $rol  = $db->real_escape_string($item['rol']);
        $tp   = $db->real_escape_string($item['tipo_proceso']);
        $det  = $db->real_escape_string($item['detalle']);
        $desc = $db->real_escape_string($item['descripcion']);
        $cat  = $db->real_escape_string($item['categoria']);
        $ico  = $db->real_escape_string($item['icono']);
        $col  = $db->real_escape_string($item['color']);
        $ord  = (int)$item['orden'];
        $tb   = (int)$item['target_blank'];

        $sql = "INSERT INTO dashboard_items (rol, tipo_proceso, detalle, descripcion, accion_url, categoria, icono, color_gradiente, orden, target_blank, activo, creado_en)
                VALUES ('$rol', '$tp', '$det', '$desc', '$urlEsc', '$cat', '$ico', '$col', $ord, $tb, 1, NOW())";

        if (!$db->query($sql)) {
            echo "  ✗ Error insertando '$det': " . $db->error . "\n";
        } else {
            $inserted++;
            echo "  ✓ Insertado: $det → $cat\n";
        }
    }
    echo "\n  Resumen: $inserted insertados, $skipped omitidos (ya existían)\n\n";

    // ─────────────────────────────────────────────
    // PASO 4: Resumen final
    // ─────────────────────────────────────────────
    echo "--- RESUMEN FINAL ---\n";
    $r = $db->query("SELECT categoria, COUNT(*) as total, SUM(activo) as activos FROM dashboard_items GROUP BY categoria ORDER BY categoria");
    echo str_pad('Categoría', 40) . str_pad('Total', 8) . "Activos\n";
    echo str_repeat('-', 56) . "\n";
    while ($row = $r->fetch_assoc()) {
        $cat = $row['categoria'] ?: '(sin categoría)';
        echo str_pad($cat, 40) . str_pad($row['total'], 8) . $row['activos'] . "\n";
    }

    $total = $db->query("SELECT COUNT(*) as t FROM dashboard_items")->fetch_assoc()['t'];
    $activos = $db->query("SELECT COUNT(*) as t FROM dashboard_items WHERE activo = 1")->fetch_assoc()['t'];
    echo "\nTotal: $total registros ($activos activos)\n";

    $db->close();
    echo "\n=== Migración completada en $label ===\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
