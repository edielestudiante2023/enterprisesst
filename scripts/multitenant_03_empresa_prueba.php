<?php
/**
 * Multi-tenant Fase 3: crear empresa de prueba (SOLO LOCAL)
 *
 * Este script crea datos ficticios para probar el aislamiento:
 *   - tbl_empresa_consultora: "TEST Colega SAS"
 *   - tbl_consultor:          "TEST Consultor Colega"
 *   - tbl_clientes:           "TEST Cliente del Colega"
 *   - tbl_usuarios:           admin del colega -> colega.test@example.com / colega123
 *
 * Todos los nombres usan prefijo "TEST_" / "TEST " para ser faciles de borrar.
 *
 * Uso:
 *   php scripts/multitenant_03_empresa_prueba.php          # LOCAL (por defecto)
 *   php scripts/multitenant_03_empresa_prueba.php --rollback  # Borrar todos los TEST_
 *
 * NO SE EJECUTA EN PRODUCCION. El parametro --env=prod no esta habilitado aqui a proposito.
 */

$rollback = in_array('--rollback', $argv ?? []);

$host     = '127.0.0.1';
$port     = 3306;
$dbname   = 'empresas_sst';
$username = 'root';
$password = '';

echo "=== LOCAL (empresa de prueba) ===\n";

try {
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
        $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "Conexion OK\n\n";
} catch (Throwable $e) {
    echo "ERROR conexion: " . $e->getMessage() . "\n";
    exit(1);
}

// ---------- ROLLBACK ----------
if ($rollback) {
    echo "-- ROLLBACK: borrando datos TEST_ --\n";
    try {
        $pdo->beginTransaction();

        // Orden: usuarios -> clientes -> consultor -> empresa
        $pdo->exec("DELETE FROM tbl_usuarios WHERE email LIKE 'colega.test@%'");
        echo "  OK tbl_usuarios (colega.test@*)\n";

        $pdo->exec("DELETE FROM tbl_clientes WHERE nombre_cliente LIKE 'TEST %'");
        echo "  OK tbl_clientes (TEST *)\n";

        $pdo->exec("DELETE FROM tbl_consultor WHERE nombre_consultor LIKE 'TEST %'");
        echo "  OK tbl_consultor (TEST *)\n";

        $pdo->exec("DELETE FROM tbl_empresa_consultora WHERE razon_social LIKE 'TEST %'");
        echo "  OK tbl_empresa_consultora (TEST *)\n";

        $pdo->commit();
        echo "\nROLLBACK COMPLETO\n";
        exit(0);
    } catch (Throwable $e) {
        $pdo->rollBack();
        echo "ERR rollback: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// ---------- CREATE ----------
try {
    $pdo->beginTransaction();

    // 1) Empresa
    echo "-- 1) Empresa 'TEST Colega SAS' --\n";
    $existe = (int)$pdo->query("SELECT COUNT(*) FROM tbl_empresa_consultora WHERE razon_social = 'TEST Colega SAS'")->fetchColumn();
    if ($existe) {
        $idEmpresa = (int)$pdo->query("SELECT id_empresa_consultora FROM tbl_empresa_consultora WHERE razon_social = 'TEST Colega SAS' LIMIT 1")->fetchColumn();
        echo "  OK ya existe (id={$idEmpresa})\n";
    } else {
        $pdo->prepare("
            INSERT INTO tbl_empresa_consultora (razon_social, nit, estado, plan)
            VALUES ('TEST Colega SAS', '900000000-1', 'activo', 'test')
        ")->execute();
        $idEmpresa = (int)$pdo->lastInsertId();
        echo "  OK creada id={$idEmpresa}\n";
    }

    // 2) Consultor
    echo "\n-- 2) Consultor 'TEST Consultor Colega' --\n";
    $existeC = (int)$pdo->query("SELECT COUNT(*) FROM tbl_consultor WHERE nombre_consultor = 'TEST Consultor Colega'")->fetchColumn();
    if ($existeC) {
        $idConsultor = (int)$pdo->query("SELECT id_consultor FROM tbl_consultor WHERE nombre_consultor = 'TEST Consultor Colega' LIMIT 1")->fetchColumn();
        echo "  OK ya existe (id={$idConsultor})\n";
        $pdo->prepare("UPDATE tbl_consultor SET id_empresa_consultora = ? WHERE id_consultor = ?")
            ->execute([$idEmpresa, $idConsultor]);
    } else {
        $pdo->prepare("
            INSERT INTO tbl_consultor (nombre_consultor, cedula_consultor, usuario, password, correo_consultor, telefono_consultor, id_empresa_consultora, rol)
            VALUES ('TEST Consultor Colega', '1000000001', 'colega_test', ?, 'colega.test@example.com', '3000000000', ?, 'consultant')
        ")->execute([password_hash('colega123', PASSWORD_BCRYPT), $idEmpresa]);
        $idConsultor = (int)$pdo->lastInsertId();
        echo "  OK creado id={$idConsultor}\n";
    }

    // 3) Cliente
    echo "\n-- 3) Cliente 'TEST Cliente del Colega' --\n";
    $existeCli = (int)$pdo->query("SELECT COUNT(*) FROM tbl_clientes WHERE nombre_cliente = 'TEST Cliente del Colega'")->fetchColumn();
    if ($existeCli) {
        $idCliente = (int)$pdo->query("SELECT id_cliente FROM tbl_clientes WHERE nombre_cliente = 'TEST Cliente del Colega' LIMIT 1")->fetchColumn();
        echo "  OK ya existe (id={$idCliente})\n";
        $pdo->prepare("UPDATE tbl_clientes SET id_consultor = ? WHERE id_cliente = ?")
            ->execute([$idConsultor, $idCliente]);
    } else {
        $pdo->prepare("
            INSERT INTO tbl_clientes (
                fecha_ingreso, nit_cliente, nombre_cliente, usuario, password,
                correo_cliente, telefono_1_cliente, direccion_cliente,
                nombre_rep_legal, cedula_rep_legal, ciudad_cliente, estado, id_consultor, estandares
            )
            VALUES (
                CURDATE(), '800111222-3', 'TEST Cliente del Colega', 'cliente_colega_test', ?,
                'cliente.colega.test@example.com', '3111111111', 'Calle TEST',
                'Rep Legal TEST', '1111111111', 'Bogota', 'activo', ?, '7'
            )
        ")->execute([password_hash('cliente123', PASSWORD_BCRYPT), $idConsultor]);
        $idCliente = (int)$pdo->lastInsertId();
        echo "  OK creado id={$idCliente}\n";
    }

    // 4) Usuario admin del colega en tbl_usuarios
    echo "\n-- 4) Usuario admin 'colega.test@example.com' --\n";
    $existeU = (int)$pdo->query("SELECT COUNT(*) FROM tbl_usuarios WHERE email = 'colega.test@example.com'")->fetchColumn();
    if ($existeU) {
        $idUsuario = (int)$pdo->query("SELECT id_usuario FROM tbl_usuarios WHERE email = 'colega.test@example.com' LIMIT 1")->fetchColumn();
        echo "  OK ya existe (id={$idUsuario})\n";
        $pdo->prepare("UPDATE tbl_usuarios SET id_entidad = ?, tipo_usuario = 'admin', estado = 'activo' WHERE id_usuario = ?")
            ->execute([$idConsultor, $idUsuario]);
    } else {
        $pdo->prepare("
            INSERT INTO tbl_usuarios (email, password, nombre_completo, tipo_usuario, id_entidad, estado, created_at, updated_at)
            VALUES ('colega.test@example.com', ?, 'TEST Colega Admin', 'admin', ?, 'activo', NOW(), NOW())
        ")->execute([password_hash('colega123', PASSWORD_BCRYPT), $idConsultor]);
        $idUsuario = (int)$pdo->lastInsertId();
        echo "  OK creado id={$idUsuario}\n";
    }

    $pdo->commit();

    echo "\n=== RESUMEN ===\n";
    echo "  empresa_consultora: id={$idEmpresa}  razon_social='TEST Colega SAS'\n";
    echo "  consultor:          id={$idConsultor}\n";
    echo "  cliente:            id={$idCliente}  nombre='TEST Cliente del Colega'\n";
    echo "  usuario login:      email='colega.test@example.com' password='colega123'\n";
    echo "\nListo para probar aislamiento en LOCAL.\n";
    exit(0);

} catch (Throwable $e) {
    $pdo->rollBack();
    echo "ERR: " . $e->getMessage() . "\n";
    exit(1);
}
