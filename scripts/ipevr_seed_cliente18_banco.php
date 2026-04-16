<?php
/**
 * SEED LOCAL: Maestros del cliente 18 (CLIENTE DE VALIDACION)
 * simulando la estructura de un banco comercial colombiano.
 *
 * Idempotente: borra TODOS los maestros del cliente 18 antes de insertar.
 * Solo para LOCAL — no subir a produccion.
 */

$pdo = new PDO(
    'mysql:host=127.0.0.1;dbname=empresas_sst;charset=utf8mb4',
    'root',
    '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$ID_CLIENTE = 18;

echo "=== Seed Banco - Cliente {$ID_CLIENTE} ===\n";

// 1) Limpieza previa (orden inverso por FK)
$pdo->exec("DELETE FROM tbl_zonas_cliente    WHERE id_cliente={$ID_CLIENTE}");
$pdo->exec("DELETE FROM tbl_tareas_cliente   WHERE id_cliente={$ID_CLIENTE}");
$pdo->exec("DELETE FROM tbl_cargos_cliente   WHERE id_cliente={$ID_CLIENTE}");
$pdo->exec("DELETE FROM tbl_procesos_cliente WHERE id_cliente={$ID_CLIENTE}");
echo "Limpieza OK\n\n";

// 2) Procesos (10)
$procesos = [
    ['Direccionamiento Estrategico',        'estrategico', 'Planeacion, definicion de metas corporativas, gestion con la junta directiva.'],
    ['Gestion Integral de Riesgos',         'estrategico', 'Riesgo de credito, mercado, liquidez, operacional, SARLAFT.'],
    ['Captacion - Productos de Ahorro',     'misional',    'Apertura y gestion de cuentas de ahorro, corriente, CDT y fondos.'],
    ['Colocacion - Credito y Cartera',      'misional',    'Originacion, desembolso y cobranza de creditos consumo, comercial, hipotecario.'],
    ['Servicios Transaccionales',           'misional',    'Caja, transferencias, pagos, canjes, giros nacionales e internacionales.'],
    ['Talento Humano',                      'apoyo',       'Seleccion, contratacion, nomina, SG-SST, bienestar.'],
    ['Tecnologia e Informacion',            'apoyo',       'Infraestructura TI, desarrollo, ciberseguridad, soporte usuario.'],
    ['Seguridad Fisica y Electronica',      'apoyo',       'Vigilancia, CCTV, custodia de valores, protocolos antiasalto.'],
    ['Financiero y Contable',               'apoyo',       'Contabilidad, tesoreria, impuestos, reportes a la Superfinanciera.'],
    ['Auditoria y Control Interno',         'evaluacion',  'Auditoria interna, SARO, cumplimiento normativo.'],
];

$stmtP = $pdo->prepare("
    INSERT INTO tbl_procesos_cliente (id_cliente, nombre_proceso, tipo, descripcion, activo)
    VALUES (?, ?, ?, ?, 1)
");
$mapProc = [];
foreach ($procesos as $p) {
    $stmtP->execute([$ID_CLIENTE, $p[0], $p[1], $p[2]]);
    $mapProc[$p[0]] = (int)$pdo->lastInsertId();
}
echo "Procesos insertados: " . count($procesos) . "\n";

// 3) Cargos (distribuidos por proceso)
$cargos = [
    // Direccionamiento Estrategico
    ['Direccionamiento Estrategico', 'Gerente General',            1],
    ['Direccionamiento Estrategico', 'Subgerente Comercial',       1],
    ['Direccionamiento Estrategico', 'Asistente de Gerencia',      1],
    // Gestion Integral de Riesgos
    ['Gestion Integral de Riesgos',  'Director de Riesgos',        1],
    ['Gestion Integral de Riesgos',  'Analista de Riesgo Credito', 3],
    ['Gestion Integral de Riesgos',  'Oficial de Cumplimiento SARLAFT', 1],
    // Captacion
    ['Captacion - Productos de Ahorro', 'Jefe de Captacion',       1],
    ['Captacion - Productos de Ahorro', 'Asesor Comercial Ahorro', 6],
    // Colocacion
    ['Colocacion - Credito y Cartera', 'Jefe de Credito',          1],
    ['Colocacion - Credito y Cartera', 'Analista de Credito',      4],
    ['Colocacion - Credito y Cartera', 'Gestor de Cobranza',       3],
    // Servicios Transaccionales
    ['Servicios Transaccionales', 'Jefe de Oficina',               1],
    ['Servicios Transaccionales', 'Cajero Principal',              2],
    ['Servicios Transaccionales', 'Cajero Auxiliar',               5],
    // Talento Humano
    ['Talento Humano', 'Director de Talento Humano',               1],
    ['Talento Humano', 'Coordinador SG-SST',                       1],
    ['Talento Humano', 'Analista de Nomina',                       2],
    // Tecnologia
    ['Tecnologia e Informacion', 'CIO',                             1],
    ['Tecnologia e Informacion', 'Administrador de Infraestructura', 2],
    ['Tecnologia e Informacion', 'Analista de Soporte',             3],
    ['Tecnologia e Informacion', 'Oficial de Ciberseguridad',       1],
    // Seguridad fisica
    ['Seguridad Fisica y Electronica', 'Jefe de Seguridad',        1],
    ['Seguridad Fisica y Electronica', 'Vigilante',                 6],
    ['Seguridad Fisica y Electronica', 'Escolta de Valores',        2],
    // Financiero
    ['Financiero y Contable', 'Director Financiero',                1],
    ['Financiero y Contable', 'Contador',                           1],
    ['Financiero y Contable', 'Analista Contable',                  2],
    // Auditoria
    ['Auditoria y Control Interno', 'Auditor Interno',              1],
    ['Auditoria y Control Interno', 'Auditor Senior',               2],
];
$stmtC = $pdo->prepare("
    INSERT INTO tbl_cargos_cliente (id_cliente, id_proceso, nombre_cargo, num_ocupantes, activo)
    VALUES (?, ?, ?, ?, 1)
");
foreach ($cargos as $c) {
    $stmtC->execute([$ID_CLIENTE, $mapProc[$c[0]], $c[1], $c[2]]);
}
echo "Cargos insertados: " . count($cargos) . "\n";

// 4) Tareas (rutinarias y no rutinarias por proceso)
$tareas = [
    // Direccionamiento Estrategico
    ['Direccionamiento Estrategico', 'Reuniones de junta directiva',           0, 'Mensual, con presentacion de resultados.'],
    ['Direccionamiento Estrategico', 'Revisar informes financieros mensuales', 1, 'Analisis de estados financieros consolidados.'],
    // Gestion Riesgos
    ['Gestion Integral de Riesgos',  'Analizar solicitudes de credito',        1, 'Aplicar scoring y politica de riesgo.'],
    ['Gestion Integral de Riesgos',  'Monitorear transacciones SARLAFT',       1, 'Seguimiento continuo con sistema de alertas.'],
    ['Gestion Integral de Riesgos',  'Responder requerimientos UIAF',          0, 'Respuestas ROS y reportes de operaciones inusuales.'],
    // Captacion
    ['Captacion - Productos de Ahorro', 'Abrir cuentas de ahorro a clientes',  1, 'Verificacion documental y sistema core.'],
    ['Captacion - Productos de Ahorro', 'Asesorar productos CDT',              1, 'Calculo de tasas y condiciones.'],
    // Colocacion
    ['Colocacion - Credito y Cartera', 'Recepcion de solicitudes de credito',   1, 'Revision de documentos del solicitante.'],
    ['Colocacion - Credito y Cartera', 'Desembolso de credito aprobado',        1, 'Transferencia a cuenta del cliente.'],
    ['Colocacion - Credito y Cartera', 'Gestion telefonica de cobranza',        1, 'Llamadas a deudores en mora.'],
    ['Colocacion - Credito y Cartera', 'Visita domiciliaria de cobranza',       0, 'Gestion en terreno con clientes de alta mora.'],
    // Servicios Transaccionales
    ['Servicios Transaccionales', 'Atencion en ventanilla de caja',            1, 'Recibir y pagar, manejo de efectivo.'],
    ['Servicios Transaccionales', 'Cuadre de caja al final del dia',           1, 'Conciliacion de valores manejados.'],
    ['Servicios Transaccionales', 'Recibir y transportar valores a boveda',    1, 'Movimiento interno de efectivo.'],
    ['Servicios Transaccionales', 'Atencion al publico en plataforma',         1, 'Resolver consultas y reclamos.'],
    // Talento Humano
    ['Talento Humano', 'Digitacion de nomina',                                 1, 'Registro en sistema de nomina.'],
    ['Talento Humano', 'Realizar induccion SST a nuevos empleados',            0, 'Presencial o virtual.'],
    ['Talento Humano', 'Coordinar examenes medicos ocupacionales',             0, 'Periodicos y de ingreso.'],
    // Tecnologia
    ['Tecnologia e Informacion', 'Administrar servidores y data center',      1, 'Monitoreo 24/7 de infraestructura critica.'],
    ['Tecnologia e Informacion', 'Atencion de tickets de soporte usuario',    1, 'Trabajo frente a pantalla.'],
    ['Tecnologia e Informacion', 'Desarrollo de aplicaciones core',           1, 'Digitacion prolongada, posturas sostenidas.'],
    // Seguridad Fisica
    ['Seguridad Fisica y Electronica', 'Vigilancia perimetral',               1, 'Turnos de 8 o 12 horas.'],
    ['Seguridad Fisica y Electronica', 'Escoltar traslado de valores',        1, 'Riesgo publico elevado.'],
    ['Seguridad Fisica y Electronica', 'Revisar CCTV sala de control',        1, 'Turnos continuos frente a monitores.'],
    // Financiero
    ['Financiero y Contable', 'Registrar asientos contables',                 1, 'Trabajo extendido frente a pantalla.'],
    ['Financiero y Contable', 'Elaborar reportes regulatorios',               0, 'Mensual/trimestral a Superfinanciera.'],
    // Auditoria
    ['Auditoria y Control Interno', 'Ejecutar pruebas de auditoria',          0, 'Revision documental y en sistemas.'],
    ['Auditoria y Control Interno', 'Elaborar informes de hallazgos',         0, 'Redaccion y digitacion.'],
];
$stmtT = $pdo->prepare("
    INSERT INTO tbl_tareas_cliente (id_cliente, id_proceso, nombre_tarea, rutinaria, descripcion, activo)
    VALUES (?, ?, ?, ?, ?, 1)
");
foreach ($tareas as $t) {
    $stmtT->execute([$ID_CLIENTE, $mapProc[$t[0]], $t[1], $t[2], $t[3]]);
}
echo "Tareas insertadas: " . count($tareas) . "\n";

// 5) Zonas (sin id_sede, texto libre)
$zonas = [
    ['Sala de Juntas',                  'Piso ejecutivo, reuniones de direccion.'],
    ['Oficina de Gerencia',             'Despacho del gerente general.'],
    ['Plataforma de Servicio al Cliente', 'Puestos de asesores comerciales.'],
    ['Caja General',                    'Area con ventanillas y mamparas blindadas.'],
    ['Boveda Principal',                'Cuarto blindado para custodia de efectivo.'],
    ['Oficina de Credito',              'Puestos de analistas y jefe de credito.'],
    ['Sala de Espera',                  'Recepcion de clientes, filas de atencion.'],
    ['Cuarto de Servidores / Data Center', 'Aire acondicionado, acceso restringido.'],
    ['Oficina de Talento Humano',       'Puestos administrativos y sala de induccion.'],
    ['Oficina de Contabilidad',         'Puestos de analistas y contador.'],
    ['Sala de Control CCTV',            'Monitores de vigilancia.'],
    ['Parqueadero de valores',          'Acceso blindado para transportadoras.'],
    ['Archivo fisico',                  'Custodia de documentos fisicos.'],
    ['Cafeteria / Comedor empleados',   'Area de descanso y alimentacion.'],
];
$stmtZ = $pdo->prepare("
    INSERT INTO tbl_zonas_cliente (id_cliente, id_sede, nombre_zona, descripcion, activo)
    VALUES (?, NULL, ?, ?, 1)
");
foreach ($zonas as $z) {
    $stmtZ->execute([$ID_CLIENTE, $z[0], $z[1]]);
}
echo "Zonas insertadas: " . count($zonas) . "\n";

echo "\n=== RESUMEN FINAL ===\n";
foreach (['tbl_procesos_cliente','tbl_cargos_cliente','tbl_tareas_cliente','tbl_zonas_cliente'] as $t) {
    $n = $pdo->query("SELECT COUNT(*) FROM {$t} WHERE id_cliente={$ID_CLIENTE}")->fetchColumn();
    echo "  {$t}: {$n}\n";
}
echo "\nSEED BANCO OK\n";
