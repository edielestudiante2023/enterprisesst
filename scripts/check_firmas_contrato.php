#!/usr/bin/env php
<?php
/**
 * Script CLI para diagnosticar firmas en contratos
 * Uso: php scripts/check_firmas_contrato.php
 */

try {
    $dsn = "mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=empresas_sst;charset=utf8mb4";
    $opts = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 10,
        PDO::MYSQL_ATTR_SSL_CA => true,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ];
    $pdo = new PDO($dsn, 'cycloid_userdb', 'AVNS_iDypWizlpMRwHIORJGG', $opts);
    echo "[OK] Conectado a produccion\n\n";
} catch (PDOException $e) {
    echo "[ERROR] No se pudo conectar: " . $e->getMessage() . "\n";
    exit(1);
}

echo "=== DIAGNOSTICO DE FIRMAS EN CONTRATO (produccion) ===\n\n";

// 1. Datos del cliente 19
echo "--- CLIENTE ID 19 ---\n";
$stmt = $pdo->prepare("SELECT id_cliente, nombre_cliente, nit_cliente, firma_representante_legal, id_consultor FROM tbl_clientes WHERE id_cliente = 19");
$stmt->execute();
$client = $stmt->fetch(PDO::FETCH_ASSOC);

if ($client) {
    echo "Nombre: {$client['nombre_cliente']}\n";
    echo "NIT: {$client['nit_cliente']}\n";
    echo "firma_representante_legal: " . ($client['firma_representante_legal'] ?: '(VACIO)') . "\n";
    echo "id_consultor: {$client['id_consultor']}\n";
} else {
    echo "[ERROR] Cliente 19 no encontrado\n";
    exit(1);
}

// 2. Consultor asignado al cliente
echo "\n--- CONSULTOR DEL CLIENTE ---\n";
$stmt = $pdo->prepare("SELECT id_consultor, nombre_consultor, firma_consultor FROM tbl_consultor WHERE id_consultor = ?");
$stmt->execute([$client['id_consultor']]);
$consultor = $stmt->fetch(PDO::FETCH_ASSOC);

if ($consultor) {
    echo "ID Consultor: {$consultor['id_consultor']}\n";
    echo "Nombre: {$consultor['nombre_consultor']}\n";
    echo "firma_consultor: " . ($consultor['firma_consultor'] ?: '(VACIO)') . "\n";
} else {
    echo "[WARN] Consultor no encontrado\n";
}

// 3. Contratos del cliente 19
echo "\n--- CONTRATOS DEL CLIENTE 19 ---\n";
$stmt = $pdo->prepare("SELECT id_contrato, numero_contrato, id_consultor_responsable, contrato_generado, ruta_pdf_contrato FROM tbl_contratos WHERE id_cliente = 19 ORDER BY id_contrato DESC");
$stmt->execute();
$contracts = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($contracts as $c) {
    echo "Contrato #{$c['id_contrato']} ({$c['numero_contrato']})\n";
    echo "  id_consultor_responsable: " . ($c['id_consultor_responsable'] ?: '(VACIO/NULL)') . "\n";
    echo "  contrato_generado: {$c['contrato_generado']}\n";
    echo "  ruta_pdf: " . ($c['ruta_pdf_contrato'] ?: '(VACIO)') . "\n";

    if (!empty($c['id_consultor_responsable'])) {
        $stmt2 = $pdo->prepare("SELECT nombre_consultor, firma_consultor FROM tbl_consultor WHERE id_consultor = ?");
        $stmt2->execute([$c['id_consultor_responsable']]);
        $resp = $stmt2->fetch(PDO::FETCH_ASSOC);
        if ($resp) {
            echo "  Consultor responsable: {$resp['nombre_consultor']}\n";
            echo "  firma_consultor: " . ($resp['firma_consultor'] ?: '(VACIO)') . "\n";
        }
    }
    echo "\n";
}

// 4. Simulacion getContractWithClient(8)
echo "--- SIMULACION getContractWithClient(8) ---\n";
$sql = "SELECT tbl_contratos.id_contrato, tbl_contratos.id_consultor_responsable,
               tbl_clientes.firma_representante_legal,
               tbl_consultor.nombre_consultor,
               tbl_consultor.firma_consultor
        FROM tbl_contratos
        JOIN tbl_clientes ON tbl_clientes.id_cliente = tbl_contratos.id_cliente
        LEFT JOIN tbl_consultor ON tbl_consultor.id_consultor = tbl_contratos.id_consultor_responsable
        WHERE tbl_contratos.id_contrato = 8";
$stmt = $pdo->query($sql);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result) {
    echo "id_consultor_responsable: " . ($result['id_consultor_responsable'] ?: '(NULL)') . "\n";
    echo "firma_representante_legal: " . ($result['firma_representante_legal'] ?: '(VACIO)') . "\n";
    echo "nombre_consultor (JOIN): " . ($result['nombre_consultor'] ?: '(NULL - no hay JOIN)') . "\n";
    echo "firma_consultor (JOIN): " . ($result['firma_consultor'] ?: '(VACIO)') . "\n";
} else {
    echo "[ERROR] Contrato 8 no encontrado\n";
}

// 5. Todos los consultores
echo "\n--- TODOS LOS CONSULTORES ---\n";
$stmt = $pdo->query("SELECT id_consultor, nombre_consultor, firma_consultor FROM tbl_consultor ORDER BY id_consultor");
$consultores = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($consultores as $c) {
    echo "  [{$c['id_consultor']}] {$c['nombre_consultor']} -> firma: " . ($c['firma_consultor'] ?: '(VACIO)') . "\n";
}

echo "\n=== FIN DIAGNOSTICO ===\n";
