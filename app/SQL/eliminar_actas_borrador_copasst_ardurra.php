<?php
/**
 * Elimina las 3 actas COPASST en estado 'borrador' del comite 9 (ARDURRA COLOMBIA SAS)
 * creadas por error durante el bug de "enviar a firmas".
 *
 * Actas a eliminar (SOLO si estado = 'borrador'):
 *   - ACT-COPASST-2026-002
 *   - ACT-COPASST-2026-003
 *   - ACT-COPASST-2026-004
 *
 * NO se tocan: 2026-001 (firmada) ni 2026-005 (pendiente_firma).
 *
 * Orden: LOCAL primero, PRODUCCION solo si LOCAL OK.
 * Ejecutar: php app/SQL/eliminar_actas_borrador_copasst_ardurra.php
 */

$conexiones = [
    'local' => [
        'host' => 'localhost', 'port' => 3306,
        'database' => 'empresas_sst', 'username' => 'root', 'password' => '', 'ssl' => false
    ],
    'produccion' => [
        'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com', 'port' => 25060,
        'database' => 'empresas_sst', 'username' => 'cycloid_userdb',
        'password' => 'AVNS_MR2SLvzRh3i_7o9fEHN', 'ssl' => true
    ]
];

$ID_COMITE = 9;
$NUMEROS = ['ACT-COPASST-2026-002', 'ACT-COPASST-2026-003', 'ACT-COPASST-2026-004'];
$ESTADO_PERMITIDO = 'borrador';

$localExito = false;

foreach ($conexiones as $entorno => $config) {
    echo "\n=== Ejecutando en $entorno ===\n";

    if ($entorno === 'produccion' && !$localExito) {
        echo "  [SKIP] No se ejecuta en produccion porque LOCAL fallo\n";
        continue;
    }

    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
        $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC];
        if ($config['ssl']) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = true;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }
        $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        echo "  [OK] Conexion establecida\n";

        // 1. Buscar actas candidatas con guardia estricta (id_comite + numero_acta + estado)
        $placeholders = implode(',', array_fill(0, count($NUMEROS), '?'));
        $stmt = $pdo->prepare(
            "SELECT id_acta, numero_acta, estado, fecha_reunion, id_comite
             FROM tbl_actas
             WHERE id_comite = ?
               AND estado = ?
               AND numero_acta IN ($placeholders)"
        );
        $stmt->execute(array_merge([$ID_COMITE, $ESTADO_PERMITIDO], $NUMEROS));
        $actas = $stmt->fetchAll();

        echo "  Encontradas " . count($actas) . " actas candidatas:\n";
        foreach ($actas as $a) {
            echo "    - id_acta={$a['id_acta']} | {$a['numero_acta']} | estado={$a['estado']} | fecha={$a['fecha_reunion']}\n";
        }

        if (empty($actas)) {
            echo "  [INFO] Nada que eliminar en $entorno\n";
            if ($entorno === 'local') $localExito = true;
            continue;
        }

        $ids = array_column($actas, 'id_acta');
        $idsPh = implode(',', array_fill(0, count($ids), '?'));

        $pdo->beginTransaction();
        try {
            // Orden de borrado (hijos -> padres)
            $borrados = [];

            // Compromisos
            $s = $pdo->prepare("DELETE FROM tbl_acta_compromisos WHERE id_acta IN ($idsPh)");
            $s->execute($ids);
            $borrados['tbl_acta_compromisos'] = $s->rowCount();

            // Asistentes
            $s = $pdo->prepare("DELETE FROM tbl_acta_asistentes WHERE id_acta IN ($idsPh)");
            $s->execute($ids);
            $borrados['tbl_acta_asistentes'] = $s->rowCount();

            // Tokens (existe tbl_actas_tokens)
            try {
                $s = $pdo->prepare("DELETE FROM tbl_actas_tokens WHERE id_acta IN ($idsPh)");
                $s->execute($ids);
                $borrados['tbl_actas_tokens'] = $s->rowCount();
            } catch (Exception $e) {
                echo "    [WARN] tbl_actas_tokens: " . $e->getMessage() . "\n";
            }

            // Notificaciones (existe tbl_actas_notificaciones)
            try {
                $s = $pdo->prepare("DELETE FROM tbl_actas_notificaciones WHERE id_acta IN ($idsPh)");
                $s->execute($ids);
                $borrados['tbl_actas_notificaciones'] = $s->rowCount();
            } catch (Exception $e) {
                echo "    [WARN] tbl_actas_notificaciones: " . $e->getMessage() . "\n";
            }

            // Solicitudes reapertura (por si alguna tiene)
            try {
                $s = $pdo->prepare("DELETE FROM tbl_acta_solicitudes_reapertura WHERE id_acta IN ($idsPh)");
                $s->execute($ids);
                $borrados['tbl_acta_solicitudes_reapertura'] = $s->rowCount();
            } catch (Exception $e) {
                echo "    [WARN] tbl_acta_solicitudes_reapertura: " . $e->getMessage() . "\n";
            }

            // Actas principales (con doble guardia en WHERE: id + comite + estado)
            $guardIds = implode(',', array_fill(0, count($ids), '?'));
            $s = $pdo->prepare(
                "DELETE FROM tbl_actas
                 WHERE id_acta IN ($guardIds)
                   AND id_comite = ?
                   AND estado = ?"
            );
            $params = array_merge($ids, [$ID_COMITE, $ESTADO_PERMITIDO]);
            $s->execute($params);
            $borrados['tbl_actas'] = $s->rowCount();

            if ($borrados['tbl_actas'] !== count($actas)) {
                throw new Exception("Mismatch: esperado borrar " . count($actas) . " actas, se borraron " . $borrados['tbl_actas']);
            }

            $pdo->commit();
            echo "  [OK] Transaccion confirmada. Filas borradas:\n";
            foreach ($borrados as $tabla => $n) {
                echo "    $tabla: $n\n";
            }

            // Verificacion final
            $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM tbl_actas WHERE id_comite = ? AND numero_acta IN ($placeholders)");
            $stmt->execute(array_merge([$ID_COMITE], $NUMEROS));
            $quedan = (int) $stmt->fetch()['c'];
            echo "  Verificacion: quedan $quedan actas con esos numeros (debe ser 0)\n";

            if ($quedan !== 0) {
                throw new Exception("Verificacion fallo: aun quedan $quedan actas con los numeros objetivo");
            }

            if ($entorno === 'local') $localExito = true;

        } catch (Exception $e) {
            $pdo->rollBack();
            echo "  [ERROR] Rollback: " . $e->getMessage() . "\n";
        }

    } catch (PDOException $e) {
        echo "  [ERROR] Conexion: " . $e->getMessage() . "\n";
    }
}

echo "\n=== FIN ===\n";
