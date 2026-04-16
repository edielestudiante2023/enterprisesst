<?php
/**
 * SEED LOCAL: Contexto + Maestros del cliente 14 (COSMONOVA COLOMBIA SAS)
 * Importadora/comercializadora de cosmeticos.
 *
 * Idempotente: borra todo del cliente 14 antes de insertar.
 * Solo LOCAL.
 */

$pdo = new PDO(
    'mysql:host=127.0.0.1;dbname=empresas_sst;charset=utf8mb4',
    'root', '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$ID = 14;
echo "=== Seed COSMONOVA - Cliente {$ID} ===\n";

// Limpieza
$pdo->exec("DELETE FROM tbl_zonas_cliente    WHERE id_cliente={$ID}");
$pdo->exec("DELETE FROM tbl_tareas_cliente   WHERE id_cliente={$ID}");
$pdo->exec("DELETE FROM tbl_cargos_cliente   WHERE id_cliente={$ID}");
$pdo->exec("DELETE FROM tbl_procesos_cliente WHERE id_cliente={$ID}");
$pdo->exec("DELETE FROM tbl_cliente_contexto_sst WHERE id_cliente={$ID}");
echo "Limpieza OK\n\n";

// ============ CONTEXTO ============
$peligros = json_encode([
    'Fisicos' => ['ruido','iluminacion'],
    'Quimicos' => ['gases_vapores','liquidos_quimicos','material_particulado'],
    'Biologicos' => ['virus','bacterias'],
    'Biomecanicos' => ['postura_prolongada','movimiento_repetitivo','manipulacion_cargas'],
    'Psicosociales' => ['gestion_organizacional','condiciones_tarea','jornada_trabajo'],
    'Condiciones de Seguridad' => ['mecanico','locativo','accidentes_transito','publicos'],
    'Fenomenos Naturales' => ['sismo'],
]);

$stmt = $pdo->prepare("INSERT INTO tbl_cliente_contexto_sst (
    id_cliente, sector_economico, nivel_riesgo_arl, clase_riesgo_cotizacion,
    arl_actual, total_trabajadores, trabajadores_directos, trabajadores_temporales,
    contratistas_permanentes, numero_sedes, turnos_trabajo,
    horario_lunes_viernes, horario_sabado, trabaja_domingos_festivos,
    tiene_copasst, tiene_vigia_sst, tiene_comite_convivencia, tiene_brigada_emergencias,
    requiere_delegado_sst, estandares_aplicables,
    peligros_identificados, observaciones_contexto,
    accidentes_ultimo_anio, tasa_ausentismo, numero_pisos, tiene_ascensor,
    sustancias_quimicas,
    representante_legal_nombre, representante_legal_cargo,
    responsable_sgsst_nombre, responsable_sgsst_cargo,
    created_at, updated_at
) VALUES (
    :id, 'Comercio al por mayor y al por menor', 'II', 2,
    'Sura', 35, 28, 4,
    3, 1, :turnos,
    '8:00 AM - 6:00 PM', '8:00 AM - 1:00 PM', 'no',
    0, 1, 1, 1,
    0, 60,
    :peligros,
    :obs,
    1, 3.10, 2, 0,
    'Productos cosmeticos importados (cremas, lociones, shampoos, fragancias), material de empaque, alcohol cetilico, solventes en pequenas cantidades para muestras, tintas de impresion para etiquetas',
    'Luis Fernando Rojas', 'Gerente General',
    'Carolina Mendez', 'Coordinadora SG-SST',
    NOW(), NOW()
)");
$stmt->execute([
    'id' => $ID,
    'turnos' => '["Diurno"]',
    'peligros' => $peligros,
    'obs' => 'Importadora y comercializadora de cosmeticos (maquillaje, cuidado capilar, fragancias, skincare). Opera bodega con almacenamiento de productos terminados importados, zona de recepcion de contenedores, area de re-etiquetado y re-empaque, picking y despacho a cadenas de retail y droguerias. Oficinas administrativas con area comercial, importaciones, contabilidad y gerencia. Flota de 3 vehiculos para distribuccion local. Riesgo quimico bajo (no transforman producto, solo almacenan). Riesgo ergonomico principal en bodega (estibas, carga manual, posturas en picking). Riesgo psicosocial en area comercial por metas de ventas.',
]);
echo "Contexto insertado\n";

// ============ PROCESOS (8) ============
$procesos = [
    ['Direccion y Planeacion',          'estrategico', 'Definicion de metas, presupuesto, expansion de lineas de producto.'],
    ['Importaciones y Comercio Exterior','misional',   'Compras internacionales, tramites aduaneros, INVIMA, registro sanitario.'],
    ['Comercial y Ventas',              'misional',    'Gestion de clientes, asesoria cosmetica, visitas a cadenas, eventos de marca.'],
    ['Almacen y Logistica',             'misional',    'Recepcion de contenedores, almacenamiento, picking, despacho, inventarios.'],
    ['Re-etiquetado y Re-empaque',      'misional',    'Adecuacion de etiquetas INVIMA, re-empaque de kits promocionales.'],
    ['Financiero y Contable',           'apoyo',       'Contabilidad, costos de importacion, cartera, tesoreria, impuestos.'],
    ['Talento Humano y SG-SST',         'apoyo',       'Seleccion, nomina, bienestar, SG-SST, capacitaciones.'],
    ['Control de Calidad',              'evaluacion',  'Verificacion de lotes importados, registros INVIMA, vencimientos, trazabilidad.'],
];
$stmtP = $pdo->prepare("INSERT INTO tbl_procesos_cliente (id_cliente,nombre_proceso,tipo,descripcion,activo) VALUES (?,?,?,?,1)");
$mapProc = [];
foreach ($procesos as $p) {
    $stmtP->execute([$ID, $p[0], $p[1], $p[2]]);
    $mapProc[$p[0]] = (int)$pdo->lastInsertId();
}
echo "Procesos: " . count($procesos) . "\n";

// ============ CARGOS (22) ============
$cargos = [
    ['Direccion y Planeacion',           'Gerente General',                  1],
    ['Direccion y Planeacion',           'Asistente de Gerencia',            1],
    ['Importaciones y Comercio Exterior','Jefe de Importaciones',            1],
    ['Importaciones y Comercio Exterior','Analista de Comercio Exterior',    2],
    ['Importaciones y Comercio Exterior','Auxiliar de Importaciones',        1],
    ['Comercial y Ventas',               'Director Comercial',               1],
    ['Comercial y Ventas',               'Asesor Comercial',                 4],
    ['Comercial y Ventas',               'Community Manager',                1],
    ['Almacen y Logistica',              'Jefe de Bodega',                   1],
    ['Almacen y Logistica',              'Auxiliar de Bodega',               4],
    ['Almacen y Logistica',              'Conductor Repartidor',             3],
    ['Almacen y Logistica',              'Operador de Montacargas',          1],
    ['Re-etiquetado y Re-empaque',       'Supervisor de Re-empaque',         1],
    ['Re-etiquetado y Re-empaque',       'Operario de Re-etiquetado',        3],
    ['Financiero y Contable',            'Director Financiero',              1],
    ['Financiero y Contable',            'Contador',                         1],
    ['Financiero y Contable',            'Auxiliar Contable',                1],
    ['Financiero y Contable',            'Analista de Cartera',              1],
    ['Talento Humano y SG-SST',          'Coordinadora SG-SST',              1],
    ['Talento Humano y SG-SST',          'Analista de Nomina',               1],
    ['Control de Calidad',               'Jefe de Control de Calidad',       1],
    ['Control de Calidad',               'Inspector de Calidad',             1],
];
$stmtC = $pdo->prepare("INSERT INTO tbl_cargos_cliente (id_cliente,id_proceso,nombre_cargo,num_ocupantes,activo) VALUES (?,?,?,?,1)");
foreach ($cargos as $c) $stmtC->execute([$ID, $mapProc[$c[0]], $c[1], $c[2]]);
echo "Cargos: " . count($cargos) . "\n";

// ============ TAREAS (25) ============
$tareas = [
    ['Direccion y Planeacion',            'Reuniones de planeacion estrategica',              0, 'Trimestral, revision de metas y presupuesto.'],
    ['Direccion y Planeacion',            'Revision de informes financieros',                 1, 'Trabajo en oficina frente a pantalla.'],
    ['Importaciones y Comercio Exterior', 'Tramitar declaraciones de importacion',            1, 'Gestion documental con DIAN y SIA.'],
    ['Importaciones y Comercio Exterior', 'Gestionar registros sanitarios INVIMA',            0, 'Tramites con ente regulador.'],
    ['Importaciones y Comercio Exterior', 'Coordinar nacionalizacion de contenedores',        1, 'Comunicacion con agentes y puertos.'],
    ['Comercial y Ventas',                'Visitar cadenas de retail y droguerias',            1, 'Desplazamiento en vehiculo por la ciudad.'],
    ['Comercial y Ventas',                'Atencion telefonica y virtual a clientes',          1, 'Posturas prolongadas, uso de pantalla.'],
    ['Comercial y Ventas',                'Participar en ferias y eventos cosmeticos',         0, 'Montaje de stands, carga de material POP.'],
    ['Almacen y Logistica',               'Recepcion y descargue de contenedores',             1, 'Manipulacion manual de cajas (5-20 kg).'],
    ['Almacen y Logistica',               'Almacenamiento en estanterias con estibas',         1, 'Trabajo con montacargas y escaleras.'],
    ['Almacen y Logistica',               'Picking de pedidos para despacho',                  1, 'Caminata prolongada, posturas flexionadas.'],
    ['Almacen y Logistica',               'Cargue de vehiculos de reparto',                    1, 'Manipulacion de cajas, subir/bajar de plataforma.'],
    ['Almacen y Logistica',               'Conducir vehiculo de reparto',                      1, 'Riesgo vial, jornada prolongada.'],
    ['Almacen y Logistica',               'Realizar inventarios fisicos',                      0, 'Conteo en estanterias altas, escaleras.'],
    ['Re-etiquetado y Re-empaque',        'Re-etiquetar productos con informacion INVIMA',     1, 'Movimientos repetitivos manos y brazos.'],
    ['Re-etiquetado y Re-empaque',        'Armar kits promocionales',                          1, 'Posturas sostenidas, manipulacion fina.'],
    ['Re-etiquetado y Re-empaque',        'Manipular solventes para limpieza de etiquetas',    0, 'Exposicion a vapores quimicos.'],
    ['Financiero y Contable',             'Registrar costos de importacion',                   1, 'Trabajo prolongado frente a pantalla.'],
    ['Financiero y Contable',             'Gestion de cobro de cartera',                       1, 'Llamadas, estres por metas de recaudo.'],
    ['Talento Humano y SG-SST',           'Digitacion de nomina',                              1, 'Postura prolongada, pantalla.'],
    ['Talento Humano y SG-SST',           'Realizar inspecciones SST en bodega',               0, 'Recorrido en planta, escaleras.'],
    ['Talento Humano y SG-SST',           'Coordinar capacitaciones SST',                      0, 'Logistica, desplazamientos.'],
    ['Control de Calidad',                'Inspeccion visual de lotes recibidos',               1, 'Verificar empaques, fechas, etiquetas.'],
    ['Control de Calidad',                'Toma de muestras de productos',                     1, 'Contacto con productos cosmeticos.'],
    ['Control de Calidad',                'Revision de registros INVIMA y trazabilidad',        1, 'Trabajo en oficina frente a pantalla.'],
];
$stmtT = $pdo->prepare("INSERT INTO tbl_tareas_cliente (id_cliente,id_proceso,nombre_tarea,rutinaria,descripcion,activo) VALUES (?,?,?,?,?,1)");
foreach ($tareas as $t) $stmtT->execute([$ID, $mapProc[$t[0]], $t[1], $t[2], $t[3]]);
echo "Tareas: " . count($tareas) . "\n";

// ============ ZONAS (10) ============
$zonas = [
    ['Oficina Gerencia',                'Piso 2, area administrativa.'],
    ['Oficina Comercial',               'Puestos asesores, sala de reuniones con clientes.'],
    ['Oficina Importaciones',           'Puestos analistas, archivo de documentos aduaneros.'],
    ['Oficina Contabilidad',            'Puestos contador y auxiliares.'],
    ['Bodega Principal',                'Estanterias de 4 niveles, pasillos de picking, zona de estibas.'],
    ['Zona de Recepcion / Descargue',   'Muelle de carga, acceso de camiones y contenedores.'],
    ['Zona de Re-etiquetado y Re-empaque','Mesas de trabajo, pistolas de calor, dispensadores de etiquetas.'],
    ['Cuarto de Almacenamiento Quimico', 'Solventes, material de limpieza industrial (ventilado).'],
    ['Parqueadero y Patio de Maniobras', 'Vehiculos de reparto, montacargas.'],
    ['Cafeteria / Comedor',             'Area de descanso para empleados.'],
];
$stmtZ = $pdo->prepare("INSERT INTO tbl_zonas_cliente (id_cliente,id_sede,nombre_zona,descripcion,activo) VALUES (?,NULL,?,?,1)");
foreach ($zonas as $z) $stmtZ->execute([$ID, $z[0], $z[1]]);
echo "Zonas: " . count($zonas) . "\n";

echo "\n=== RESUMEN ===\n";
echo "Contexto: " . $pdo->query("SELECT COUNT(*) FROM tbl_cliente_contexto_sst WHERE id_cliente={$ID}")->fetchColumn() . "\n";
foreach (['tbl_procesos_cliente','tbl_cargos_cliente','tbl_tareas_cliente','tbl_zonas_cliente'] as $t)
    echo "{$t}: " . $pdo->query("SELECT COUNT(*) FROM {$t} WHERE id_cliente={$ID}")->fetchColumn() . "\n";
echo "\nSEED COSMONOVA OK\n";
