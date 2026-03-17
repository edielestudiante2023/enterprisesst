<?php
/**
 * Fix: Corregir plazas del proceso COCOLAB id=2 (COMPAÑIA INTERAMERICANA DE FIANZAS SAS)
 * La empresa tiene 49 trabajadores → 1 principal + 1 suplente (escala 10-49)
 * El proceso fue creado con 4+4 incorrectamente.
 *
 * IMPORTANTE: También corrige los resultados del escrutinio ya calculados.
 */

function conectar(bool $produccion): PDO {
    if ($produccion) {
        $dsn = 'mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=empresas_sst;charset=utf8mb4';
        return new PDO($dsn, 'cycloid_userdb', 'AVNS_iDypWizlpMRwHIORJGG', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        ]);
    }
    return new PDO('mysql:host=127.0.0.1;dbname=empresas_sst;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
}

function ejecutar(PDO $pdo, string $entorno): void {
    $ID_PROCESO = 2;

    // 1. Ver estado actual
    $row = $pdo->query("SELECT id_proceso, tipo_comite, plazas_principales, plazas_suplentes, estado FROM tbl_procesos_electorales WHERE id_proceso = {$ID_PROCESO}")->fetch(PDO::FETCH_ASSOC);
    if (!$row) { echo "[{$entorno}] ERROR: proceso {$ID_PROCESO} no encontrado.\n"; return; }
    echo "[{$entorno}] Antes: plazas_principales={$row['plazas_principales']}, plazas_suplentes={$row['plazas_suplentes']}, estado={$row['estado']}\n";

    // 2. Corregir plazas del proceso
    $pdo->exec("UPDATE tbl_procesos_electorales SET plazas_principales = 1, plazas_suplentes = 1 WHERE id_proceso = {$ID_PROCESO}");
    echo "[{$entorno}] OK: plazas actualizadas a 1+1.\n";

    // 3. Corregir resultados del escrutinio si ya existen
    // Los primeros 1 candidatos por votos = principal, el siguiente = suplente
    // Reasignar tipo_plaza según nueva configuración 1+1
    // Top 1 por votos = principal, siguiente = suplente, resto = no_elegido
    $candidatos = $pdo->query(
        "SELECT id_candidato, votos_obtenidos, tipo_plaza, estado FROM tbl_candidatos_comite
         WHERE id_proceso = {$ID_PROCESO} AND representacion = 'trabajador'
         ORDER BY votos_obtenidos DESC, id_candidato ASC"
    )->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($candidatos)) {
        foreach ($candidatos as $i => $c) {
            if ($i === 0) {
                $pdo->exec("UPDATE tbl_candidatos_comite SET tipo_plaza = 'principal', estado = 'elegido' WHERE id_candidato = {$c['id_candidato']}");
                echo "[{$entorno}] Candidato id={$c['id_candidato']} ({$c['votos_obtenidos']} votos): principal / elegido\n";
            } elseif ($i === 1) {
                $pdo->exec("UPDATE tbl_candidatos_comite SET tipo_plaza = 'suplente', estado = 'elegido' WHERE id_candidato = {$c['id_candidato']}");
                echo "[{$entorno}] Candidato id={$c['id_candidato']} ({$c['votos_obtenidos']} votos): suplente / elegido\n";
            } else {
                $pdo->exec("UPDATE tbl_candidatos_comite SET estado = 'aprobado' WHERE id_candidato = {$c['id_candidato']}");
                echo "[{$entorno}] Candidato id={$c['id_candidato']} ({$c['votos_obtenidos']} votos): no elegido / aprobado\n";
            }
        }
    } else {
        echo "[{$entorno}] Sin candidatos que corregir.\n";
    }
}

// LOCAL
try {
    $pdo = conectar(false);
    ejecutar($pdo, 'LOCAL');
} catch (PDOException $e) {
    echo "[LOCAL] ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n";

// PRODUCCION
try {
    $pdo = conectar(true);
    ejecutar($pdo, 'PROD');
} catch (PDOException $e) {
    echo "[PROD] ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
