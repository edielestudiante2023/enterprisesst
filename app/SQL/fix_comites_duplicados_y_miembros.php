<?php
/**
 * FIX: Limpiar comités duplicados de Ardurra (cliente 15) y poblar miembros
 *
 * 1. Eliminar comités COCOLAB duplicados (dejar solo 1)
 * 2. Insertar miembros de comité desde responsables SST
 *
 * USO: php app/SQL/fix_comites_duplicados_y_miembros.php [local|produccion]
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

echo "=== FIX COMITES DUPLICADOS + POBLAR MIEMBROS ===\n";
echo "Entorno: " . strtoupper($env) . "\n\n";

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    if ($ssl) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = true;
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "Conectado OK\n\n";

    $idCliente = 15;

    // ==========================================
    // PASO 1: Limpiar comités duplicados
    // ==========================================
    echo "--- PASO 1: LIMPIAR COMITES DUPLICADOS ---\n";

    // Obtener comités agrupados por tipo
    $stmt = $pdo->prepare("
        SELECT id_comite, id_tipo, fecha_conformacion, created_at,
               (SELECT COUNT(*) FROM tbl_miembros_comite mc WHERE mc.id_comite = c.id_comite) as num_miembros,
               (SELECT COUNT(*) FROM tbl_actas a WHERE a.id_comite = c.id_comite) as num_actas
        FROM tbl_comites c
        WHERE id_cliente = ?
        ORDER BY id_tipo, id_comite
    ");
    $stmt->execute([$idCliente]);
    $comites = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Agrupar por tipo
    $porTipo = [];
    foreach ($comites as $c) {
        $porTipo[$c['id_tipo']][] = $c;
    }

    $comitesAEliminar = [];
    $comitesConservados = []; // tipo => id_comite

    foreach ($porTipo as $tipo => $lista) {
        $tipoNombre = $tipo == 1 ? 'COPASST' : ($tipo == 2 ? 'COCOLAB' : "TIPO $tipo");
        echo "\n  Tipo $tipoNombre: " . count($lista) . " comite(s)\n";

        if (count($lista) <= 1) {
            echo "    OK, solo hay 1\n";
            $comitesConservados[$tipo] = $lista[0]['id_comite'];
            continue;
        }

        // Conservar el que tenga actas o miembros, si no el primero
        $conservar = $lista[0]; // por defecto el primero
        foreach ($lista as $c) {
            if ($c['num_actas'] > 0 || $c['num_miembros'] > 0) {
                $conservar = $c;
                break;
            }
        }

        $comitesConservados[$tipo] = $conservar['id_comite'];
        echo "    Conservar: ID {$conservar['id_comite']} (actas:{$conservar['num_actas']}, miembros:{$conservar['num_miembros']})\n";

        foreach ($lista as $c) {
            if ($c['id_comite'] != $conservar['id_comite']) {
                if ($c['num_actas'] > 0 || $c['num_miembros'] > 0) {
                    echo "    SKIP: ID {$c['id_comite']} tiene datos (actas:{$c['num_actas']}, miembros:{$c['num_miembros']}) - NO SE ELIMINA\n";
                } else {
                    $comitesAEliminar[] = $c['id_comite'];
                    echo "    Eliminar: ID {$c['id_comite']} (vacio)\n";
                }
            }
        }
    }

    if (!empty($comitesAEliminar)) {
        $ids = implode(',', $comitesAEliminar);
        $pdo->exec("DELETE FROM tbl_comites WHERE id_comite IN ($ids)");
        echo "\n  >> Eliminados " . count($comitesAEliminar) . " comites duplicados vacios\n";
    } else {
        echo "\n  >> No hay duplicados que eliminar\n";
    }

    // ==========================================
    // PASO 2: Poblar miembros de comité
    // ==========================================
    echo "\n--- PASO 2: POBLAR MIEMBROS DE COMITE ---\n";

    // Mapeo: tipo_rol de responsable → tipo de comité
    $mapeoRolTipo = [
        'copasst_presidente' => 1,
        'copasst_secretario' => 1,
        'copasst_representante_empleador' => 1,
        'copasst_representante_trabajadores' => 1,
        'copasst_suplente_empleador' => 1,
        'copasst_suplente_trabajadores' => 1,
        'comite_convivencia_presidente' => 2,
        'comite_convivencia_secretario' => 2,
        'comite_convivencia_representante_empleador' => 2,
        'comite_convivencia_representante_trabajadores' => 2,
        'comite_convivencia_suplente_empleador' => 2,
        'comite_convivencia_suplente_trabajadores' => 2,
    ];

    // Mapeo: tipo_rol → rol_comite en tbl_miembros_comite
    $mapeoRolComite = [
        'copasst_presidente' => 'presidente',
        'copasst_secretario' => 'secretario',
        'copasst_representante_empleador' => 'miembro',
        'copasst_representante_trabajadores' => 'miembro',
        'copasst_suplente_empleador' => 'suplente',
        'copasst_suplente_trabajadores' => 'suplente',
        'comite_convivencia_presidente' => 'presidente',
        'comite_convivencia_secretario' => 'secretario',
        'comite_convivencia_representante_empleador' => 'miembro',
        'comite_convivencia_representante_trabajadores' => 'miembro',
        'comite_convivencia_suplente_empleador' => 'suplente',
        'comite_convivencia_suplente_trabajadores' => 'suplente',
    ];

    // Mapeo: tipo_rol → representacion (ENUM: 'trabajador','empleador')
    $mapeoRepresentacion = [
        'copasst_presidente' => 'empleador',
        'copasst_secretario' => 'trabajador',
        'copasst_representante_empleador' => 'empleador',
        'copasst_representante_trabajadores' => 'trabajador',
        'copasst_suplente_empleador' => 'empleador',
        'copasst_suplente_trabajadores' => 'trabajador',
        'comite_convivencia_presidente' => 'empleador',
        'comite_convivencia_secretario' => 'trabajador',
        'comite_convivencia_representante_empleador' => 'empleador',
        'comite_convivencia_representante_trabajadores' => 'trabajador',
        'comite_convivencia_suplente_empleador' => 'empleador',
        'comite_convivencia_suplente_trabajadores' => 'trabajador',
    ];

    // Mapeo: tipo_rol → tipo_miembro (ENUM: 'principal','suplente')
    $mapeoTipoMiembro = [
        'copasst_presidente' => 'principal',
        'copasst_secretario' => 'principal',
        'copasst_representante_empleador' => 'principal',
        'copasst_representante_trabajadores' => 'principal',
        'copasst_suplente_empleador' => 'suplente',
        'copasst_suplente_trabajadores' => 'suplente',
        'comite_convivencia_presidente' => 'principal',
        'comite_convivencia_secretario' => 'principal',
        'comite_convivencia_representante_empleador' => 'principal',
        'comite_convivencia_representante_trabajadores' => 'principal',
        'comite_convivencia_suplente_empleador' => 'suplente',
        'comite_convivencia_suplente_trabajadores' => 'suplente',
    ];

    // Obtener responsables de comité
    $stmt = $pdo->prepare("
        SELECT * FROM tbl_cliente_responsables_sst
        WHERE id_cliente = ? AND activo = 1 AND tipo_rol IN ('" . implode("','", array_keys($mapeoRolTipo)) . "')
    ");
    $stmt->execute([$idCliente]);
    $responsables = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "  Responsables de comite encontrados: " . count($responsables) . "\n";

    $insertados = 0;
    $yaExisten = 0;

    foreach ($responsables as $resp) {
        $tipoComite = $mapeoRolTipo[$resp['tipo_rol']];
        $idComite = $comitesConservados[$tipoComite] ?? null;

        if (!$idComite) {
            echo "  SKIP: No hay comite tipo $tipoComite para {$resp['nombre_completo']}\n";
            continue;
        }

        // Verificar si ya existe
        $existe = $pdo->prepare("
            SELECT id_miembro FROM tbl_miembros_comite
            WHERE id_comite = ? AND email = ?
        ");
        $existe->execute([$idComite, $resp['email']]);

        if ($existe->fetch()) {
            $yaExisten++;
            continue;
        }

        // Insertar miembro
        $insert = $pdo->prepare("
            INSERT INTO tbl_miembros_comite
            (id_comite, nombres, apellidos, documento_identidad, cargo, email, telefono,
             representacion, tipo_miembro, rol_comite, estado, fecha_ingreso, created_at)
            VALUES (?, ?, '', ?, ?, ?, ?, ?, ?, ?, 'activo', CURDATE(), NOW())
        ");
        $insert->execute([
            $idComite,
            $resp['nombre_completo'],
            $resp['numero_documento'],
            $resp['cargo'],
            $resp['email'],
            $resp['telefono'],
            $mapeoRepresentacion[$resp['tipo_rol']],
            $mapeoTipoMiembro[$resp['tipo_rol']],
            $mapeoRolComite[$resp['tipo_rol']]
        ]);

        $tipoNombre = $tipoComite == 1 ? 'COPASST' : 'COCOLAB';
        echo "  + {$resp['nombre_completo']} -> {$tipoNombre} (comite #{$idComite}) como {$mapeoRolComite[$resp['tipo_rol']]}\n";
        $insertados++;
    }

    echo "\n  >> Insertados: $insertados | Ya existian: $yaExisten\n";

    // Verificar resultado final
    echo "\n--- RESULTADO FINAL ---\n";
    $stmt = $pdo->prepare("
        SELECT c.id_comite, c.id_tipo,
               (SELECT COUNT(*) FROM tbl_miembros_comite mc WHERE mc.id_comite = c.id_comite) as miembros
        FROM tbl_comites c
        WHERE c.id_cliente = ?
        ORDER BY c.id_tipo
    ");
    $stmt->execute([$idCliente]);
    foreach ($stmt as $r) {
        $tipo = $r['id_tipo'] == 1 ? 'COPASST' : ($r['id_tipo'] == 2 ? 'COCOLAB' : "TIPO {$r['id_tipo']}");
        echo "  Comite #{$r['id_comite']} ({$tipo}): {$r['miembros']} miembros\n";
    }

    echo "\n=== COMPLETADO ===\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
