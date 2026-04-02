<?php
/**
 * Migración: Agregar columnas de categoría a dashboard_items + seed 52 items Consultor
 *
 * Uso: php app/SQL/migrate_dashboard_categorias.php [local|production]
 * Producción: DB_PROD_PASS=xxx php app/SQL/migrate_dashboard_categorias.php production
 */

$env = $argv[1] ?? 'local';

$configs = [
    'local' => [
        'host' => 'localhost',
        'user' => 'root',
        'pass' => '',
        'db'   => 'empresas_sst',
        'ssl'  => false,
    ],
    'production' => [
        'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port' => 25060,
        'user' => 'cycloid_userdb',
        'pass' => getenv('DB_PROD_PASS') ?: '',
        'db'   => 'empresas_sst',
        'ssl'  => true,
    ],
];

if (!isset($configs[$env])) {
    echo "Uso: php app/SQL/migrate_dashboard_categorias.php [local|production]\n";
    exit(1);
}

$cfg = $configs[$env];
echo "=== Migración dashboard_items categorías - Entorno: {$env} ===\n\n";

if ($env === 'production' && empty($cfg['pass'])) {
    echo "ERROR: Variable DB_PROD_PASS no definida.\n";
    exit(1);
}

$conn = new mysqli();
if ($cfg['ssl'] ?? false) {
    $conn->ssl_set(null, null, null, null, null);
    $conn->real_connect($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['db'], $cfg['port'] ?? 3306, null, MYSQLI_CLIENT_SSL);
} else {
    $conn->real_connect($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['db'], $cfg['port'] ?? 3306);
}

if ($conn->connect_error) {
    echo "ERROR de conexión: " . $conn->connect_error . "\n";
    exit(1);
}
$conn->set_charset('utf8mb4');
echo "Conectado a {$cfg['db']}@{$cfg['host']}\n\n";

$ok = 0;
$errors = 0;

// ─── 1. Agregar columnas si no existen ───
$columnas = [
    'categoria'        => "VARCHAR(100) DEFAULT NULL",
    'icono'            => "VARCHAR(100) DEFAULT NULL",
    'color_gradiente'  => "VARCHAR(100) DEFAULT NULL",
    'target_blank'     => "TINYINT(1) DEFAULT 1",
    'activo'           => "TINYINT(1) DEFAULT 1",
];

foreach ($columnas as $col => $tipo) {
    $check = $conn->query("SHOW COLUMNS FROM dashboard_items LIKE '{$col}'");
    if ($check && $check->num_rows > 0) {
        echo "[SKIP] Columna '{$col}' ya existe\n";
        $ok++;
    } else {
        $sql = "ALTER TABLE dashboard_items ADD COLUMN {$col} {$tipo}";
        if ($conn->query($sql)) {
            echo "[OK] Columna '{$col}' agregada\n";
            $ok++;
        } else {
            echo "[ERROR] {$col}: " . $conn->error . "\n";
            $errors++;
        }
    }
}

// ─── 2. Insertar seed data para Consultor (solo si no hay items de Consultor) ───
$checkConsultor = $conn->query("SELECT COUNT(*) as cnt FROM dashboard_items WHERE rol = 'Consultor'");
$row = $checkConsultor->fetch_assoc();

if (intval($row['cnt']) > 0) {
    echo "\n[SKIP] Ya existen " . $row['cnt'] . " items de Consultor. No se insertan seeds.\n";
} else {
    echo "\nInsertando items de Consultor...\n";

    $items = [
        // 1. IA y Asistencia
        ['Consultor','IA y Asistencia','Otto - Asistente IA','Chat con el asistente inteligente Otto','/agente-chat',1,'IA y Asistencia','fas fa-robot','#4facfe,#00f2fe',0,1],
        ['Consultor','IA y Asistencia','Monitor Otto','Monitoreo de conversaciones y logs de Otto','/otto-logs',2,'IA y Asistencia','fas fa-desktop','#1c2437,#2d3a52',1,1],
        // 2. Operación Diaria
        ['Consultor','Operación Diaria','Cronogramas Capacitación','Gestión cronogramas de capacitación','/listcronogCapacitacion',24,'Operación Diaria','fas fa-calendar-alt','#0d6efd,#0dcaf0',1,1],
        ['Consultor','Operación Diaria','Calificación Estándares Mínimos','Evaluación estándares mínimos','/listEvaluaciones',25,'Operación Diaria','fas fa-tasks','#e74c3c,#c0392b',1,1],
        ['Consultor','Operación Diaria','Plan de Trabajo Anual','Administración PTA','/pta-cliente-nueva/list',5,'Operación Diaria','fas fa-graduation-cap','#20c997,#13b397',1,1],
        ['Consultor','Operación Diaria','Pendientes','Tareas pendientes','/listPendientes',6,'Operación Diaria','fas fa-clipboard-check','#667eea,#764ba2',1,1],
        ['Consultor','Operación Diaria','Listado Mantenimientos','Gestión de mantenimientos','/vencimientos',40,'Operación Diaria','fas fa-tools','#f39c12,#e67e22',1,1],
        ['Consultor','Operación Diaria','Vigías','Gestión de vigías SST','/listVigias',23,'Operación Diaria','fas fa-hard-hat','#6f42c1,#9b59b6',1,1],
        // 3. Gestión Clientes
        ['Consultor','Gestión Clientes','Nuevo Cliente','Registrar un nuevo cliente en la plataforma','/clients/nuevo',1,'Gestión Clientes','fas fa-user-plus','#2d6a4f,#40916c',1,1],
        ['Consultor','Gestión Clientes','Ver Vista de Cliente','Previsualizar el portal como lo ve el cliente','/consultor/selector-cliente',2,'Gestión Clientes','fas fa-eye','#6366f1,#8b5cf6',1,1],
        // 4. Inspecciones y Auditoría
        ['Consultor','Inspecciones','Inspecciones SST','Módulo de inspecciones de seguridad y salud','/inspecciones',1,'Inspecciones y Auditoría','fas fa-clipboard-check','#0d6efd,#0dcaf0',1,1],
        // 5. Cumplimiento y Control
        ['Consultor','Cumplimiento','Acciones Correctivas','Gestión de hallazgos y acciones correctivas','/acciones-correctivas',1,'Cumplimiento y Control','fas fa-exclamation-triangle','#e74c3c,#c0392b',1,1],
        ['Consultor','Cumplimiento','Indicadores SST','Indicadores del sistema de gestión SST','/indicadores-sst',2,'Cumplimiento y Control','fas fa-chart-bar','#0d6efd,#0b5ed7',1,1],
        ['Consultor','Cumplimiento','Matriz Legal','Requisitos legales aplicables','/matriz-legal',3,'Cumplimiento y Control','fas fa-balance-scale','#e67e22,#f39c12',1,1],
        // 6. Planeación SST
        ['Consultor','Planeación','Presupuesto SST','Gestión del presupuesto de SST','/documentos-sst/presupuesto',1,'Planeación SST','fas fa-calculator','#11998e,#38ef7d',1,1],
        ['Consultor','Planeación','Acceso Rápido','Atajos a funciones frecuentes','/quick-access',2,'Planeación SST','fas fa-bolt','#bd9751,#d4af37',1,1],
        // 7. Dashboards Analíticos
        ['Consultor','Dashboards','Dashboard Estándares Mínimos','Tablero analítico de estándares mínimos','consultant/dashboard-estandares',1,'Dashboards Analíticos','fas fa-chart-pie','#667eea,#764ba2',1,1],
        ['Consultor','Dashboards','Dashboard Capacitaciones','Tablero analítico de capacitaciones','consultant/dashboard-capacitaciones',2,'Dashboards Analíticos','fas fa-graduation-cap','#f093fb,#f5576c',1,1],
        ['Consultor','Dashboards','Dashboard Plan de Trabajo','Tablero analítico del plan de trabajo','consultant/dashboard-plan-trabajo',3,'Dashboards Analíticos','fas fa-tasks','#4facfe,#00f2fe',1,1],
        ['Consultor','Dashboards','Dashboard Pendientes','Tablero analítico de pendientes','consultant/dashboard-pendientes',4,'Dashboards Analíticos','fas fa-clipboard-list','#fa709a,#fee140',1,1],
        ['Consultor','Dashboards','Informe de Avances','Informe consolidado de avances por cliente','informe-avances',5,'Dashboards Analíticos','fas fa-chart-line','#11998e,#38ef7d',1,1],
        ['Consultor','Dashboards','Dashboard Documentos SST','Tablero de documentos SST','admin/dashboard-documentos-sst',6,'Dashboards Analíticos','fas fa-file-alt','#0984e3,#6c5ce7',1,1],
        // 8. Gestión Documental
        ['Consultor','Gestión Documental','Firma Electrónica','Dashboard de firma electrónica','/firma/dashboard',1,'Gestión Documental','fas fa-signature','#6a11cb,#2575fc',1,1],
        ['Consultor','Gestión Documental','Documentos SST','Gestión de documentos del SG-SST','/documentacion',2,'Gestión Documental','fas fa-folder-open','#f093fb,#f5576c',1,1],
        ['Consultor','Gestión Documental','Marcos Normativos IA','Marcos normativos generados con IA','/documentos/marco-normativo-dashboard',3,'Gestión Documental','fas fa-book-open','#f093fb,#f5576c',1,1],
        ['Consultor','Gestión Documental','Editor Secciones','Editor de secciones de documentos','/admin/editor-secciones',4,'Gestión Documental','fas fa-edit','#e44d26,#f16529',1,1],
        ['Consultor','Gestión Documental','Actas de Comité','Actas de reunión COPASST/COCOLAB','/actas',5,'Gestión Documental','fas fa-gavel','#2d6a4f,#40916c',1,1],
        // 9. Contexto y Estándares
        ['Consultor','Contexto','Contexto SST','Definición del contexto organizacional','/contexto',1,'Contexto y Estándares','fas fa-sitemap','#667eea,#764ba2',1,1],
        ['Consultor','Contexto','Estándares','Gestión de estándares del sistema','/estandares',2,'Contexto y Estándares','fas fa-check-double','#f2994a,#f2c94c',1,1],
        ['Consultor','Contexto','Catálogo Estándares','Catálogo completo de estándares','/estandares/catalogo',3,'Contexto y Estándares','fas fa-list-ol','#11998e,#38ef7d',1,1],
        // 10. Usuarios y Accesos
        ['Consultor','Usuarios','Gestión Usuarios','Administración de usuarios del sistema','/admin/users',1,'Usuarios y Accesos','fas fa-users-cog','#667eea,#764ba2',1,1],
        ['Consultor','Usuarios','Consumo Plataforma','Métricas de uso de la plataforma','/admin/usage',2,'Usuarios y Accesos','fas fa-chart-line','#11998e,#38ef7d',1,1],
        // 11. Administración
        ['Consultor','Administración','Resetear Ciclo PHVA','Resetea evaluaciones de estándares mínimos anuales','#resetPHVAModal',1,'Administración','fas fa-redo-alt','#dc3545,#c82333',0,1],
    ];

    $stmt = $conn->prepare("INSERT INTO dashboard_items (rol, tipo_proceso, detalle, descripcion, accion_url, orden, categoria, icono, color_gradiente, target_blank, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $inserted = 0;
    foreach ($items as $item) {
        $stmt->bind_param('sssssisssii',
            $item[0], $item[1], $item[2], $item[3], $item[4],
            $item[5], $item[6], $item[7], $item[8], $item[9], $item[10]
        );
        if ($stmt->execute()) {
            $inserted++;
        } else {
            echo "[ERROR] Insert '{$item[2]}': " . $stmt->error . "\n";
            $errors++;
        }
    }
    $stmt->close();
    echo "[OK] {$inserted} items de Consultor insertados\n";
    $ok++;
}

echo "\n=== Resultado: {$ok} OK, {$errors} errores ===\n";
$conn->close();
