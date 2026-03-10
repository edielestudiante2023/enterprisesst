<?php
/**
 * FIX URGENTE: Extender token_expira de votantes del proceso electoral
 *
 * Problema: Los tokens individuales expiran a 48h/7dias pero el proceso sigue activo
 * Solucion: Actualizar token_expira para que coincida con fecha_fin_votacion del proceso
 *
 * USO: php app/SQL/fix_tokens_votacion.php [local|produccion]
 */

$env = $argv[1] ?? 'local';

if ($env === 'produccion') {
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port = 25060;
    $user = 'cycloid_userdb';
    $pass = 'AVNS_iDypWizlpMRwHIORJGG';
    $db   = 'empresas_sst';
    $ssl  = true;
} else {
    $host = '127.0.0.1';
    $port = 3306;
    $user = 'root';
    $pass = '';
    $db   = 'empresas_sst';
    $ssl  = false;
}

echo "=== FIX TOKENS VOTACION ===\n";
echo "Entorno: " . strtoupper($env) . "\n";
echo "Host: $host\n\n";

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    if ($ssl) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = true;
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "Conectado OK\n\n";

    // 1. Ver procesos electorales activos en fase votacion
    echo "--- PROCESOS EN FASE VOTACION ---\n";
    $stmt = $pdo->query("
        SELECT pe.id_proceso, pe.id_cliente, pe.tipo_comite, pe.anio, pe.estado,
               pe.fecha_inicio_votacion, pe.fecha_fin_votacion,
               pe.id_cliente as cliente_id
        FROM tbl_procesos_electorales pe
        WHERE pe.estado = 'votacion'
        ORDER BY pe.id_proceso
    ");
    $procesos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($procesos)) {
        echo "No hay procesos en fase votacion.\n";
        exit(0);
    }

    foreach ($procesos as $p) {
        echo "Proceso #{$p['id_proceso']} | Cliente #{$p['id_cliente']} | {$p['tipo_comite']} {$p['anio']}\n";
        echo "  Votacion: {$p['fecha_inicio_votacion']} -> {$p['fecha_fin_votacion']}\n";

        // Ver tokens expirados
        $stmt2 = $pdo->prepare("
            SELECT COUNT(*) as total,
                   SUM(CASE WHEN token_expira < NOW() THEN 1 ELSE 0 END) as expirados,
                   SUM(CASE WHEN ha_votado = 1 THEN 1 ELSE 0 END) as ya_votaron,
                   MIN(token_expira) as min_expira,
                   MAX(token_expira) as max_expira
            FROM tbl_votantes_proceso
            WHERE id_proceso = ?
        ");
        $stmt2->execute([$p['id_proceso']]);
        $stats = $stmt2->fetch(PDO::FETCH_ASSOC);

        echo "  Votantes: {$stats['total']} | Expirados: {$stats['expirados']} | Ya votaron: {$stats['ya_votaron']}\n";
        echo "  Rango tokens: {$stats['min_expira']} -> {$stats['max_expira']}\n";

        // Si hay tokens expirados, actualizarlos
        $expirados = (int)$stats['expirados'];
        if ($expirados > 0) {
            $fechaFin = $p['fecha_fin_votacion'];
            echo "\n  >> ACTUALIZANDO $expirados tokens expirados -> nueva expiracion: $fechaFin\n";

            $update = $pdo->prepare("
                UPDATE tbl_votantes_proceso
                SET token_expira = ?
                WHERE id_proceso = ?
                  AND token_expira < NOW()
                  AND ha_votado = 0
            ");
            $update->execute([$fechaFin, $p['id_proceso']]);
            $affected = $update->rowCount();
            echo "  >> Actualizados: $affected registros\n";
        } else {
            echo "  >> No hay tokens expirados, todo OK.\n";
        }
        echo "\n";
    }

    echo "=== COMPLETADO ===\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
