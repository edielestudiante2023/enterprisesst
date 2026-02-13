<?php
$pdo = new PDO(
    'mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=empresas_sst;charset=utf8mb4',
    'cycloid_userdb',
    'AVNS_iDypWizlpMRwHIORJGG',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false]
);

echo "=== TODOS LOS USUARIOS (tipo y estado) ===\n";
$stmt = $pdo->query("SELECT id_usuario, email, tipo_usuario, estado, id_entidad, created_at FROM tbl_usuarios ORDER BY id_usuario");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $u) {
    echo "  ID:{$u['id_usuario']} | {$u['tipo_usuario']} | {$u['estado']} | entidad:{$u['id_entidad']} | {$u['email']} | {$u['created_at']}\n";
}

echo "\n=== USUARIOS TIPO 'client' ===\n";
$stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_usuarios WHERE tipo_usuario = 'client'");
echo "Total: " . $stmt->fetchColumn() . "\n";

echo "\n=== USUARIOS TIPO 'miembro' ===\n";
$stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_usuarios WHERE tipo_usuario = 'miembro'");
echo "Total: " . $stmt->fetchColumn() . "\n";

echo "\n=== CI4 LOGS (verificar si hay archivos de log accesibles) ===\n";
$logDir = __DIR__ . '/../../writable/logs/';
if (is_dir($logDir)) {
    $files = glob($logDir . 'log-2026-02-*.log');
    if ($files) {
        $lastLog = end($files);
        echo "Ultimo log: " . basename($lastLog) . "\n";
        // Buscar lineas con ResponsablesSST o createUser
        $lines = file($lastLog);
        $relevant = array_filter($lines, function($line) {
            return stripos($line, 'ResponsablesSST') !== false
                || stripos($line, 'crear usuario') !== false
                || stripos($line, 'createUser') !== false
                || stripos($line, 'Error al crear') !== false;
        });
        if ($relevant) {
            echo "Lineas relevantes:\n";
            foreach ($relevant as $l) echo "  " . trim($l) . "\n";
        } else {
            echo "No hay lineas de ResponsablesSST en el log\n";
        }
    } else {
        echo "No hay logs de febrero 2026\n";
    }
} else {
    echo "Directorio de logs no accesible: {$logDir}\n";
}

echo "\n=== getLastErrors del UserModel - NO ES METODO DE CI4 ===\n";
echo "El ActasController usa getLastErrors() pero ese no es metodo del Model base.\n";
echo "Verificando si existe en UserModel...\n";

// Verificar si el método existe
$userModelPath = __DIR__ . '/../Models/UserModel.php';
$content = file_get_contents($userModelPath);
if (strpos($content, 'getLastErrors') !== false) {
    echo "SI existe getLastErrors() en UserModel\n";
} else {
    echo "NO existe getLastErrors() en UserModel - usaria errors() del Model base\n";
}
