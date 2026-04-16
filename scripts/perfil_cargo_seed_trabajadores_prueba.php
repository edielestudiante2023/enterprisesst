<?php
/**
 * Seed de trabajadores de prueba para cliente 18 (CLIENTE DE VALIDACION) en LOCAL.
 *
 * Inserta 8 trabajadores ficticios asignados a cargos distintos del cliente 18,
 * para habilitar pruebas end-to-end del flujo de acuses y firmas.
 *
 * SOLO LOCAL. Cliente 18 en PROD es otra empresa (INMOBILIARIA VIRVIESCAS).
 *
 * Idempotente: no duplica si la cedula ya existe para el cliente.
 *
 * Uso:
 *   php scripts/perfil_cargo_seed_trabajadores_prueba.php
 */

$ID_CLIENTE = 18;

try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=empresas_sst;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "Conexion LOCAL OK\n\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

// Verificar cliente destino
$cliente = $pdo->query("SELECT id_cliente, nombre_cliente FROM tbl_clientes WHERE id_cliente={$ID_CLIENTE}")->fetch(PDO::FETCH_ASSOC);
if (!$cliente) {
    echo "ERROR: Cliente {$ID_CLIENTE} no existe\n";
    exit(1);
}
echo "Cliente destino: {$ID_CLIENTE} — {$cliente['nombre_cliente']}\n\n";

// Resolver cargos disponibles del cliente (por nombre para no depender de IDs)
$cargosMap = [];
foreach ($pdo->query("SELECT id, nombre_cargo FROM tbl_cargos_cliente WHERE id_cliente={$ID_CLIENTE} AND activo=1") as $r) {
    $cargosMap[$r['nombre_cargo']] = (int)$r['id'];
}
echo "Cargos disponibles: " . count($cargosMap) . "\n\n";

// 8 trabajadores con datos colombianos realistas
$trabajadores = [
    ['CC','1023456781','María Fernanda','Rodríguez Gómez','mfrodriguez@afiancol-test.com','3001234567','2023-03-15','Analista Contable'],
    ['CC','79854321','Carlos Andrés','Martínez Díaz',   'camartinez@afiancol-test.com', '3012345678','2022-07-01','Contador'],
    ['CC','1098765432','Laura Patricia','Ramírez López', 'lpramirez@afiancol-test.com', '3023456789','2024-01-10','Analista de Credito'],
    ['CC','52123456','Jorge Iván',     'Castaño Pérez',  'jcastano@afiancol-test.com',  '3034567890','2021-11-22','Gestor de Cobranza'],
    ['CC','43987654','Diana Marcela',  'Ospina Vargas',  'dospina@afiancol-test.com',   '3045678901','2023-09-05','Analista de Nomina'],
    ['CC','80111222','Andrés Felipe',  'Quintero Bolaños','afquintero@afiancol-test.com','3056789012','2022-02-14','Analista de Soporte'],
    ['CC','1144556677','Sandra Milena', 'Torres Cárdenas','storres@afiancol-test.com',   '3067890123','2024-05-20','Asesor Comercial Ahorro'],
    ['CC','91234567','Juan Carlos',    'Mejía Restrepo', 'jcmejia@afiancol-test.com',   '3078901234','2020-08-30','Director de Riesgos'],
];

$creados = 0; $actualizados = 0; $sinCargo = 0;
$chk = $pdo->prepare("SELECT id_trabajador FROM tbl_trabajadores WHERE id_cliente=? AND cedula=? LIMIT 1");
$ins = $pdo->prepare("INSERT INTO tbl_trabajadores
    (id_cliente, id_cargo_cliente, nombres, apellidos, tipo_documento, cedula, email, telefono, fecha_ingreso, activo)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");

echo "-- Insertando trabajadores --\n";
foreach ($trabajadores as $t) {
    [$tipoDoc, $cedula, $nombres, $apellidos, $email, $tel, $fecha, $cargoNombre] = $t;
    $idCargo = $cargosMap[$cargoNombre] ?? null;
    if ($idCargo === null) {
        echo "  ??  Cargo '{$cargoNombre}' no encontrado para {$nombres} {$apellidos}\n";
        $sinCargo++;
        continue;
    }
    $chk->execute([$ID_CLIENTE, $cedula]);
    if ($chk->fetch(PDO::FETCH_ASSOC)) {
        echo "  SKIP {$cedula} {$nombres} {$apellidos} — ya existe\n";
        continue;
    }
    try {
        $ins->execute([$ID_CLIENTE, $idCargo, $nombres, $apellidos, $tipoDoc, $cedula, $email, $tel, $fecha]);
        $creados++;
        echo "  OK   {$cedula} {$nombres} {$apellidos} → {$cargoNombre} (cargo_id={$idCargo})\n";
    } catch (Throwable $e) {
        echo "  ERR  {$cedula}: " . $e->getMessage() . "\n";
    }
}

echo "\n-- Resumen --\n";
echo "  Creados: {$creados}\n";
echo "  Sin cargo (no encontrado): {$sinCargo}\n";

$total = (int)$pdo->query("SELECT COUNT(*) FROM tbl_trabajadores WHERE id_cliente={$ID_CLIENTE} AND activo=1")->fetchColumn();
echo "  Total trabajadores activos cliente {$ID_CLIENTE}: {$total}\n";

echo "\nLISTO\n";
