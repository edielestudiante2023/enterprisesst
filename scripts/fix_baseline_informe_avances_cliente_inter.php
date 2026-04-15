#!/usr/bin/env php
<?php
/**
 * Inserta un snapshot baseline en tbl_informe_avances para el cliente del informe id=2,
 * de modo que el informe id=2 lea puntaje_anterior=30.45 y muestre diferencia_neta=+10.80.
 *
 * Uso: php scripts/fix_baseline_informe_avances_cliente_inter.php
 */

$BASELINE_PUNTAJE     = 30.45;
$DIFERENCIA_ESPERADA  = 10.80;
$ID_INFORME_OBJETIVO  = 2;

try {
    $dsn = "mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=empresas_sst;charset=utf8mb4";
    $opts = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 10,
        PDO::MYSQL_ATTR_SSL_CA => true,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ];
    $pdo = new PDO($dsn, 'cycloid_userdb', 'AVNS_MR2SLvzRh3i_7o9fEHN', $opts);
    echo "[OK] Conectado a produccion\n\n";
} catch (PDOException $e) {
    echo "[ERROR] Conexion: " . $e->getMessage() . "\n";
    exit(1);
}

// 1. Leer informe objetivo
echo "--- LEYENDO INFORME id={$ID_INFORME_OBJETIVO} ---\n";
try {
    $stmt = $pdo->prepare("SELECT id, id_cliente, id_consultor, anio, fecha_desde, fecha_hasta, puntaje_anterior, puntaje_actual, diferencia_neta, estado FROM tbl_informe_avances WHERE id = ?");
    $stmt->execute([$ID_INFORME_OBJETIVO]);
    $informe = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$informe) {
        echo "[ERROR] No existe informe id={$ID_INFORME_OBJETIVO}\n";
        exit(1);
    }
    print_r($informe);
} catch (PDOException $e) {
    echo "[ERROR] SELECT informe: " . $e->getMessage() . "\n";
    exit(1);
}

$idCliente   = (int) $informe['id_cliente'];
$idConsultor = (int) $informe['id_consultor'];
$anio        = (int) $informe['anio'];
$fechaDesde2 = $informe['fecha_desde'];

// 2. Verificar que NO exista ya un informe previo completo en el mismo año
echo "\n--- VERIFICANDO INFORMES PREVIOS COMPLETOS (cliente={$idCliente}, anio={$anio}) ---\n";
try {
    $stmt = $pdo->prepare("SELECT id, fecha_desde, fecha_hasta, puntaje_actual, estado FROM tbl_informe_avances WHERE id_cliente = ? AND anio = ? AND estado = 'completo' AND id <> ? ORDER BY fecha_hasta DESC");
    $stmt->execute([$idCliente, $anio, $ID_INFORME_OBJETIVO]);
    $previos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($previos) > 0) {
        echo "[ABORT] Ya existen " . count($previos) . " informe(s) completo(s) previo(s). No se inserta baseline.\n";
        print_r($previos);
        exit(1);
    }
    echo "[OK] No hay previos completos. Se puede insertar baseline.\n";
} catch (PDOException $e) {
    echo "[ERROR] SELECT previos: " . $e->getMessage() . "\n";
    exit(1);
}

// 3. Calcular fechas del baseline
$fechaDesdeBaseline = "{$anio}-01-01";
$fechaHastaBaseline = (new DateTime($fechaDesde2))->modify('-1 day')->format('Y-m-d');
echo "\n--- BASELINE A INSERTAR ---\n";
echo "id_cliente: {$idCliente}\n";
echo "id_consultor: {$idConsultor}\n";
echo "anio: {$anio}\n";
echo "fecha_desde: {$fechaDesdeBaseline}\n";
echo "fecha_hasta: {$fechaHastaBaseline}\n";
echo "puntaje_anterior: 0\n";
echo "puntaje_actual: {$BASELINE_PUNTAJE}\n";
echo "diferencia_neta: {$BASELINE_PUNTAJE}\n";
echo "estado: completo\n";

// 4. INSERT baseline
try {
    $sql = "INSERT INTO tbl_informe_avances
        (id_cliente, id_consultor, fecha_desde, fecha_hasta, anio,
         puntaje_anterior, puntaje_actual, diferencia_neta, estado_avance,
         estado, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, 0, ?, ?, 'AVANCE SIGNIFICATIVO', 'completo', NOW(), NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idCliente, $idConsultor, $fechaDesdeBaseline, $fechaHastaBaseline, $anio, $BASELINE_PUNTAJE, $BASELINE_PUNTAJE]);
    $newId = $pdo->lastInsertId();
    echo "\n[OK] Baseline insertado con id={$newId}\n";
} catch (PDOException $e) {
    echo "[ERROR] INSERT baseline: " . $e->getMessage() . "\n";
    exit(1);
}

// 5. Actualizar valores almacenados del informe id=2 para coherencia
try {
    $stmt = $pdo->prepare("UPDATE tbl_informe_avances SET puntaje_anterior = ?, diferencia_neta = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$BASELINE_PUNTAJE, $DIFERENCIA_ESPERADA, $ID_INFORME_OBJETIVO]);
    echo "[OK] Informe id={$ID_INFORME_OBJETIVO} actualizado: puntaje_anterior={$BASELINE_PUNTAJE}, diferencia_neta={$DIFERENCIA_ESPERADA}\n";
} catch (PDOException $e) {
    echo "[ERROR] UPDATE informe objetivo: " . $e->getMessage() . "\n";
}

// 6. Verificacion final
echo "\n--- VERIFICACION FINAL ---\n";
try {
    $stmt = $pdo->prepare("SELECT id, fecha_desde, fecha_hasta, puntaje_anterior, puntaje_actual, diferencia_neta, estado FROM tbl_informe_avances WHERE id_cliente = ? AND anio = ? ORDER BY fecha_hasta ASC");
    $stmt->execute([$idCliente, $anio]);
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    echo "[ERROR] verificacion: " . $e->getMessage() . "\n";
}

echo "\n[DONE]\n";
